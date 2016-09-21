<?php

namespace Asimov\Solaria\Modules\Forms;

use App;
use Asimov\Solaria\Modules\Forms\Models\Twig\Form as FormTwig;
use Asimov\Solaria\Modules\Forms\Models\Form as FormModel;
use Session;
use Solaria\Models\Site;

class FormsRenderer {

    /**
     * @var string
     */
    protected $form_alias = null;

    /**
     * @var bool|mixed
     */
    protected $isAjax = false;

    /**
     * @var Site
     */
    protected $site;

    /**
     * @var FormModel
     */
    protected $form;

    /**
     * FormsRenderer constructor.
     * @param array $options
     */
    public function __construct($options = []) {
        $this->site = App::make('site');

        if (gettype($options) == 'array') {
            $this->form_alias = array_get($options, 0, '');
            $this->isAjax = array_get($options, 1, false);
        } else {
            $this->form_alias = $options;
        }

        $this->form = FormModel::where(['alias' => $this->form_alias, 'site_id' => $this->site->id])->first();
    }

    function __get($name) {
        if ($name == 'formBuilder')
            return $this->getTwigForm();
        if ($name == 'successMessage')
            return $this->getSuccessMessage();
        return null;
    }

    function __isset($name) {
        return in_array($name, ['formBuilder', 'successMessage']);
    }


    function __toString() {
        $fieldsViews = [];

        if (!$this->form)
            return '';

        foreach ($this->form->fields as $field) {
            $fieldsViews[$field->alias] = view('moduleforms::frontend.fields.' . $field->type, ['form' => $this->form, 'field' => $field]);
        }

        $attributes = [
            'class' => 'solaria-form form-' . $this->form->alias,
            'method' => 'post',
            'action' => url('modules/forms/process-form')
        ];

        $successView = view('moduleforms::frontend.partials.success', ['success' => Session::get('success', null)]);
        $openView = view('moduleforms::frontend.partials.open', ['form' => $this->form, 'isAjax' => $this->isAjax, 'attr' => $attributes]);
        $closeView = view('moduleforms::frontend.partials.close', ['form' => $this->form, 'site' => $this->site]);

        $formView = 'moduleforms::frontend.form';
        $customFormView = 'moduleforms::' . $this->form->site->alias . '.' . $this->form->alias . '.form';

        if (view()->exists($customFormView))
            $formView = $customFormView;

        return view($formView, [
            'open_view' => $openView,
            'close_view' => $closeView,
            'success_view' => $successView,
            'fields_views' => $fieldsViews,
            'was_sent' => Session::has('success'),
            'form' => $this->form,
            'site' => $this->site,
            'isAjax' => $this->isAjax
        ])->render();
    }

    private function getTwigForm() {
        $form = FormModel::where(['alias' => $this->form_alias, 'site_id' => $this->site->id])->first();
        return new FormTwig($form, $this->site, $this->isAjax);
    }

    private function getSuccessMessage() {
        $scucess = session('success', '');
        if (is_array($scucess)) {
            $scucess = array_get($scucess, 0);
        }
        return $scucess;
    }
}