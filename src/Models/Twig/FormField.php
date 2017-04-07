<?php

namespace Asimov\Solaria\Modules\Forms\Models\Twig;

use Asimov\Solaria\Modules\Forms\Models\FormField as FormFieldModel;

class FormField {

    /**
     * @var FormFieldModel
     */
    private $formFieldModel;

    private $methods = ['getOldValue', 'hasErrors', 'getErrors', 'getValidations', 'getAttributes', 'getOptions'];

    private $properties = ['options', 'oldValue', 'errors', 'config', 'name', 'alias', 'type'];

    /**
     * Field constructor.
     * @param FormFieldModel $formFieldModel
     */
    public function __construct(FormFieldModel $formFieldModel) {
        $this->formFieldModel = $formFieldModel;
    }

    function __get($name) {
        switch ($name){
            case 'options':
                return $this->formFieldModel->getOptions();
            case 'oldValue':
                return $this->formFieldModel->getOldValue();
            case 'errors';
                return $this->formFieldModel->getErrors();
            case 'config':
                return $this->formFieldModel->config;
            case 'name':
                return $this->formFieldModel->name;
            case 'alias':
                return $this->formFieldModel->alias;
            case 'type':
                return $this->formFieldModel->type;
        }
        return null;
    }

    function __call($name, $arguments) {
        switch ($name) {
            case 'getOldValue':
                return $this->formFieldModel->getOldValue();
            case 'hasErrors':
                return $this->formFieldModel->hasErrors();
            case 'getErrors':
                return $this->formFieldModel->getErrors();
            case 'getValidations':
                return $this->formFieldModel->getValidations();
            case 'getConfig':
                return $this->formFieldModel->getConfig(array_get($arguments, 0, ''));
            case 'getAttributes':
                return $this->formFieldModel->getAttributes();
            case 'getOptions':
                return $this->formFieldModel->getOptions();
        }
    }

    function __isset($name) {
        if(!$this->formFieldModel)
            return false;
        return in_array($name, $this->methods) || in_array($name, $this->properties);
    }
}