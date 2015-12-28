<?php

namespace Asimov\Solaria\Modules\Forms\Http\Controllers;

use Asimov\Solaria\Modules\Forms\Models\Form;
use Asimov\Solaria\Modules\Forms\Models\FormResult;
use Illuminate\Http\Request;
use Mail;
use Solaria\Http\Controllers\Backend\BackendController;
use Solaria\Models\User;

class FormsController extends BackendController {
    public function getIndex(){
        view()->share([
            'forms' => Form::with('fields')->get(),
            'users' => User::all()
        ]);
        $tabs = [
            'active' => 'forms',
            'content' => view('moduleforms::backend.forms')
        ];
        $data['content'] = view('moduleforms::backend.index', $tabs);
        return view($this->layout, $data);
    }

    public function postIndex(Request $request){
        $form_id = $request->input('id', null);

        /** @var Form $form */
        if($form_id){
            $form = Form::find($form_id);
        } else {
            $form = new Form();
        }

        $this->authorize('edit', $form);

        $this->validate($request, [
            'name' => 'required',
            'alias' => 'required'
        ]);

        $form->name = $request->input('name');
        $form->alias = $request->input('alias');
        $form->config = $request->input('config');
        $form->default_assigned_user_id = $request->input('default_assigned_user_id');
        $form->client_email_template = $request->input('client_email_template');
        $form->user_email_template = $request->input('user_email_template');
        $form->site_id = $this->site->id;

        $form->save();
        $form->updateFormFields($request->input('fields'));
        return response(url('backend/modules/forms'));
    }

    public function getResults(){
        view()->share([
            'forms' => Form::with('fields')->get(),
            'users' => User::all()
        ]);
        $tabs = [
            'active' => 'results',
            'content' => view('moduleforms::backend.results')
        ];
        $data['content'] = view('moduleforms::backend.index', $tabs);
        return view($this->layout, $data);
    }

    public function getResultsContents($form_id){
        $titles = $sanitizedResults = [];
        $form = Form::find($form_id);
        $results = $form->results;

        foreach ($form->fields as $field) {
            if(!is_null($field->config)
                && property_exists($field->config, 'showColumn')
                && $field->config->showColumn)
                $titles[] = $field->toArray();
        }

        foreach ($results as $key => $result) {
            $sanitizedResult = [
                'id' => $result->id,
                'fecha' => $result['created_at']->format('d-m-Y H:i:s')
            ];
            $sanitizedResult = array_merge($sanitizedResult, (array)$result['results']);
            $sanitizedResults[] = $sanitizedResult;
        }

        return response()->json(['titles' => $titles, 'results' => $sanitizedResults]);
    }

    /**
     * Asigna un usuario a resultados
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function postAssignUserResults(Request $request){
        $user = User::find($request->input('user_id'))->first();
        $results = FormResult::find($request->input('results'));
        foreach ($results as $result) {
            $result->assign($user);
            $result->save();
        }
        return response()->json(['message' => 'El usario ha sido asignado con exito.']);
    }

    /**
     * Se eliminan resultados
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function postDeleteResults(Request $request){
        $results = FormResult::find($request->input('results'));
        foreach ($results as $result) {
            $result->delete();
        }
        return response()->json(['message' => 'Resultados eliminados con exito.']);
    }

    /**
     * Procesa el envio de formularios desde el sitio publico
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postProcessForm(Request $request){
        /** @var Form $form */
        $form = Form::find($request->input('form_id'));

        $form_results = new FormResult();
        $form_results->form_id = $form->id;
        $form_results->assigned_user_id = $form->user->id;
        $results = [];

        foreach ($form->fields as $field) {
            $results[$field->alias] = $request->input('field-' . $field->alias, '');
        }

        $form_results->results = $results;
        if($form->user){
            $form_results->assign($form->user);
        }
        if($form->config->client_email_field){
            $form_results->notifyClient($form->config->client_email_field);
        }
        $form_results->save();

        $success_message = isset($form->config->success_message) ? $form->config->success_message : 'Success';

        return response()->redirectTo($request->header('referer'))->with('success', $success_message);
    }
}