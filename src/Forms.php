<?php
namespace Asimov\Solaria\Modules\Forms;

use Auth;
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
        if(Auth::user()->can('module_forms_manage_forms'))
            return url('backend/modules/forms');
        if(Auth::user()->can('module_forms_view_results'))
            return url('backend/modules/forms/results');
    }

    public function getBackendStyles() {
        return [asset_versioned('modules/forms/css/forms-module.css')];
    }

    public function getFrontendStyles() {
        return [asset_versioned('vendor/blueimp-file-upload/css/jquery.fileupload.css')];
    }

    public function getBackendScripts() {
        return [asset_versioned('modules/forms/js/forms-module.js')];
    }

    public function getFrontendScripts() {
        return [
            asset_versioned('vendor/blueimp-file-upload/js/vendor/jquery.ui.widget.js'),
            asset_versioned('vendor/blueimp-file-upload/js/jquery.fileupload.js'),
            asset_versioned('modules/forms/js/public-forms-module.js')
        ];
    }

    public function getCustomFields() {
        // TODO: Implement getCustomFields() method.
    }

    public function render($options){
        return new FormsRenderer($options);
    }
}