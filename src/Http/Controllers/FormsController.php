<?php

namespace Asimov\Solaria\Modules\Forms\Http\Controllers;

use Asimov\Solaria\Modules\Forms\Models\Form;
use Illuminate\Http\Request;
use Solaria\Http\Controllers\Backend\BackendController;

class FormsController extends BackendController {
    public function getIndex(){
        view()->share([
            'forms' => Form::with('fields')->get()
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
        $form->site_id = $this->site->id;

        $form->save();
        $form->updateFormFields($request->input('fields'));
        return response(url('backend/modules/forms'));
    }

    public function getResults(){
        $tabs = [
            'active' => 'results',
            'content' => view('moduleforms::backend.results')
        ];
        $data['content'] = view('moduleforms::backend.index', $tabs);
        return view($this->layout, $data);
    }
}