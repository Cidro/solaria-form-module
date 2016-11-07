<?php

namespace Asimov\Solaria\Modules\Forms\Http\Controllers;

use Asimov\Solaria\Modules\Forms\Models\Form;
use Asimov\Solaria\Modules\Forms\Models\FormField;
use Asimov\Solaria\Modules\Forms\Models\FormResult;
use Auth;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Classes\LaravelExcelWorksheet;
use Maatwebsite\Excel\Facades\Excel;
use PHPExcel;
use PHPExcel_IOFactory;
use PHPExcel_Reader_HTML;
use Solaria\Http\Controllers\Backend\BackendController;
use Solaria\Models\Page;
use Solaria\Models\User;

class FormsController extends BackendController {
    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function getIndex(){
        $this->authorize('module_forms_manage_forms');

        view()->share([
            'forms' => Form::with('fields')->where('site_id', $this->site->id)->get(),
            'pages' => Page::where(['site_id' => $this->site->id])->get(),
            'users' => User::all()
        ]);
        $tabs = [
            'active' => 'forms',
            'content' => view('moduleforms::backend.forms')
        ];
        $data['content'] = view('moduleforms::backend.index', $tabs);
        return view($this->layout, $data);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function postIndex(Request $request){
        $this->authorize('module_forms_manage_forms');

        $form_id = $request->input('id', null);

        /** @var Form $form */
        if($form_id){
            $form = Form::find($form_id);
        } else {
            $form = new Form();
        }

        $this->validate($request, [
            'name' => 'required',
            'alias' => 'required',
            'default_assigned_user_id' => 'required'
        ], [], [
            'default_assigned_user_id' => 'Usuario asignado'
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
        return response()->json($form->toArray());
    }

    /**
     * @param $form_id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getDeleteForm($form_id){
        $this->authorize('module_forms_delete_forms');
        /** @var Form $form */
        $form = Form::find($form_id);
        $form->delete();

        return response()->json(['message' => 'Formulario eliminado exitosamente.']);
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function getResults(){
        $this->authorize('module_forms_view_results');
        $users = User::where('site_id', $this->site->id)
            ->orWhereNull('site_id')
            ->orderBy('first_name', 'asc')
            ->get();

        view()->share([
            'forms' => Form::with('fields')->where(['site_id' => $this->site->id])->get(),
            'users' => $users
        ]);

        $tabs = [
            'active' => 'results',
            'content' => view('moduleforms::backend.results')
        ];
        $data['content'] = view('moduleforms::backend.index', $tabs);
        return view($this->layout, $data);
    }

    /**
     * @param $form_id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getResultsContents($form_id){
        $this->authorize('module_forms_view_results');

        $titles = $sanitizedResults = [];

        /** @var Form $form */
        $form = Form::find($form_id);

        $results = $form->resultsForUser(Auth::user());

        foreach ($form->fields as $field) {
            if(!is_null($field->config)
                && count($field->config)
                && property_exists($field->config, 'showColumn')
                && $field->config->showColumn)
                $titles[] = $field->toArray();
        }

        foreach ($results as $key => $result) {
            $sanitizedResult = [
                'id' => $result->id,
                'ip' => $result->ip,
                'user_agent' => $result->user_agent,
                'fecha' => $result['created_at']->format('d-m-Y H:i:s'),
                'user' => $result->user ? $result->user->toArray() : null
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
        $user = User::find($request->input('user_id'));
        $results = FormResult::find($request->input('results'));

        $this->authorize('module_forms_assign_user_results', $results);

        foreach ($results as $result) {
            /** @var FormResult $result */
            $result->assign($user);
            $result->save();
        }

        return response()->json(['message' => 'El usario ha sido asignado exitosamente.']);
    }

    /**
     * Se eliminan resultados
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function postDeleteResults(Request $request){
        $this->authorize('module_forms_delete_form_results');

        $results = FormResult::find($request->input('results'));
        foreach ($results as $result) {
            $result->delete();
        }
        return response()->json(['message' => 'Resultados eliminados exitosamente.']);
    }

    /**
     * Descarga de archivo adjunto
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function getGetFile(Request $request){
        $filePath = config('filesystems.disks.modules.root') . '/moduleforms/' . $request->get('file-name');
        return response()->download($filePath);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function getDownloadResults(Request $request, $formId){
        /** @var Form $form */
        $form = Form::find($formId);

        $data['headers'] = FormField::where(['form_id' => $formId])->get();
        $data['results'] = $form->resultsForUser(Auth::user());
        $data['tzoffset'] = intval($request->get('tzoffset', 0)) * -1;

        Excel::create('resultados', function($excel) use($data){
            $excel->getDefaultStyle()->getAlignment()->setWrapText(true);
            $excel->sheet('Hoja 1', function(LaravelExcelWorksheet $sheet) use ($data) {
                $contents = [];
                foreach ($data['results'] as $key => $result) {
                    $row = [];
                    foreach ($data['headers'] as $header) {
                        if($header->type == 'hidden' && object_get($header->config, 'dataType') == 'json'){
                            $row[$header->name] = json_encode(object_get($result->results, $header->alias), JSON_UNESCAPED_UNICODE);
                        } else {
                            $row[$header->name] = $this->remove_emoji(object_get($result->results, $header->alias));
                        }
                        if(gettype($row[$header->name]) === 'array'){
                            $row[$header->name] = implode(',', $row[$header->name]);
                        }
                        if(substr($row[$header->name], 0, 1) === '='){
                            $row[$header->name] = ' ' . $row[$header->name];
                        }
                    }
                    $row['Fecha'] = $result->created_at->addMinutes($data['tzoffset']);
                    $contents[] = $row;
                }

                $sheet->fromArray($contents);
            });
        })->export('xls');
    }

    /**
     * @param $text
     * @return mixed|string
     */
    protected function remove_emoji($text) {
        $clean_text = "";

        // Match Emoticons
        $regexEmoticons = '/[\x{1F600}-\x{1F64F}]/u';
        $clean_text = preg_replace($regexEmoticons, '', $text);

        // Match Miscellaneous Symbols and Pictographs
        $regexSymbols = '/[\x{1F300}-\x{1F5FF}]/u';
        $clean_text = preg_replace($regexSymbols, '', $clean_text);

        // Match Transport And Map Symbols
        $regexTransport = '/[\x{1F680}-\x{1F6FF}]/u';
        $clean_text = preg_replace($regexTransport, '', $clean_text);

        // Match Miscellaneous Symbols
        $regexMisc = '/[\x{2600}-\x{26FF}]/u';
        $clean_text = preg_replace($regexMisc, '', $clean_text);

        // Match Dingbats
        $regexDingbats = '/[\x{2700}-\x{27BF}]/u';
        $clean_text = preg_replace($regexDingbats, '', $clean_text);

        return $clean_text;
    }

}