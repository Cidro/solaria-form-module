<?php
namespace Asimov\Solaria\Modules\Forms;

use Solaria\Modules\SolariaModule;

class Forms implements SolariaModule {

    protected $name = 'Forms';

    public function getName() {
        return $this->name;
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
}