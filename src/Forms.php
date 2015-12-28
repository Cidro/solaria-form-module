<?php
namespace Asimov\Solaria\Modules\Forms;

use Asimov\Solaria\Modules\Forms\Models\Form;
use Solaria\Modules\SolariaModule;

class Forms implements SolariaModule {

    protected $name = 'Forms';

    protected $menu_name = 'Relación con clientes';

    public function getName() {
        return $this->name;
    }

    public function getMenuName() {
        return $this->menu_name;
    }

    public function getBackendMenuUrl() {
        return url('backend/modules/forms');
    }

    public function getBackendStyles() {
        return [asset('modules/forms/css/forms-module.css')];
    }

    public function getFrontendStyles() {
        // TODO: Implement getFrontendStyles() method.
    }

    public function getBackendScripts() {
        return [asset('modules/forms/js/forms-module.js')];
    }

    public function getFrontendScripts() {
        // TODO: Implement getFrontendScripts() method.
    }

    public function getCustomFields() {
        // TODO: Implement getCustomFields() method.
    }

    public function render($form_alias){
        $fields_views = [];

        /** @var Form $form */
        $form = Form::where('alias', $form_alias)->first();

        if(!$form)
            return '';

        foreach ($form->fields as $field) {
            $fields_views[] = view('moduleforms::frontend.fields.' . $field->type, ['form' => $form, 'field' => $field]);
        }

        $view = 'moduleforms::frontend.form';
        $custom_view = 'moduleforms::' . $form->site->alias . '.' . $form->alias . '.form';

        if(view()->exists($custom_view))
            $view = $custom_view;

        return view($view, ['fields_views' => $fields_views, 'form' => $form]);
    }
}