<?php

namespace Asimov\Solaria\Modules\Forms\Models\Twig;

use \Asimov\Solaria\Modules\Forms\Models\Form as FormModel;
use Session;
use Solaria\Models\Site;

class Form {

    /**
     * @var FormModel
     */
    protected $form;

    /**
     * @var bool
     */
    protected $isAjax;

    /**
     * @var mixed
     */
    protected $successRedirect;

    /**
     * @var Site
     */
    protected $site;

    /**
     * Form constructor.
     * @param FormModel $form
     * @param Site $site
     * @param bool $isAjax
     * @param null $successRedirect
     */
    public function __construct(FormModel $form, Site $site, $isAjax = false, $successRedirect = null) {
        $this->form = $form;
        $this->site = $site;
        $this->isAjax = $isAjax;
        $this->successRedirect = $successRedirect;
    }

    function __get($name) {
        switch ($name){
            case 'wasSent':
                return Session::has('success');
            case 'success':
                return $this->successMessage();
            case 'open':
                return $this->openForm();
            case 'close':
                return $this->closeForm();
            case 'fields':
                return $this->getFields();
            case 'fieldsData':
                return $this->getFieldsData();
        }
        return null;
    }

    function __call($name, $arguments) {
        switch ($name) {
            case 'open':
                return $this->openForm($arguments);
            case 'close':
                return $this->closeForm($arguments);
        }
    }

    function __isset($name) {
        if(!$this->form)
            return false;
        return in_array($name, ['open', 'close', 'wasSent', 'success', 'fields', 'fieldsData']);
    }

    private function openForm($options = null){
        $options = array_get($options, 0, []);

        $attributes = [
            'class' => 'solaria-form form-' . $this->form->alias . ' ' . array_get($options, 'class', ''),
            'method' => array_get($options, 'method', 'post'),
            'action' => url('modules/forms/process-form')
        ];

        unset($options['class']);
        unset($options['method']);

        $customAttributes = [];
        foreach ($options as $attrName => $attrValue) {
            $customAttributes[] = $attrName . '="' . $attrValue . '"';
        }

        return view('moduleforms::frontend.partials.open', [
            'form' => $this->form,
            'isAjax' => $this->isAjax,
            'attr' => $attributes,
            'customAttr' => implode(' ', $customAttributes)
        ]);
    }

    private function closeForm($options = []){
        $options = array_get($options, 0, []);
        $showButton = array_get($options, 'showButton', true);
        return view('moduleforms::frontend.partials.close', ['form' => $this->form, 'site' => $this->site, 'showButton' => $showButton, 'successRedirect' => $this->successRedirect]);
    }

    private function successMessage() {
        return view('moduleforms::frontend.partials.success', ['success' => Session::get('success', null)]);
    }

    private function getFields(){
        $fieldsViews = [];
        foreach ($this->form->fields as $field) {
            $fieldsViews[$field->alias] = view('moduleforms::frontend.fields.' . $field->type, ['form' => $this->form, 'field' => $field]);
        }
        return $fieldsViews;
    }

    private function getFieldsData() {
        $fields = $this->form->fields;
        $fieldsArray = [];
        foreach ($fields as $field) {
            $fieldsArray[$field['alias']] = new FormField($field);
        }
        return $fieldsArray;
    }
}