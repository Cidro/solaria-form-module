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
     * @var Site
     */
    protected $site;

    /**
     * Form constructor.
     * @param FormModel $form
     * @param Site $site
     * @param bool $isAjax
     */
    public function __construct(FormModel $form, Site $site, $isAjax = false) {
        $this->form = $form;
        $this->site = $site;
        $this->isAjax = $isAjax;
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
        }
        return null;
    }

    function __call($name, $arguments) {
        switch ($name) {
            case 'open':
                return $this->openForm($arguments);
        }
    }


    function __isset($name) {
        if(!$this->form)
            return false;
        return in_array($name, ['open', 'close', 'fields', 'success']);
    }

    private function openForm($options = null){
        $options = array_get($options, 0);

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

    private function closeForm(){
        return view('moduleforms::frontend.partials.close', ['form' => $this->form, 'site' => $this->site]);
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
}