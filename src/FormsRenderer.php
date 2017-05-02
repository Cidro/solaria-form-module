<?php

namespace Asimov\Solaria\Modules\Forms;

use App;
use Asimov\Solaria\Modules\Forms\Models\FormResult;
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
     * @var mixed|null
     */
    protected $successRedirect = null;

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
            $params = array_get($options, 1, false);
            if(gettype($params) == 'boolean') {
                $this->isAjax = $params;
            } elseif(gettype($params) == 'array') {
                $this->isAjax = array_get($params, 'is_ajas', false);
                $this->successRedirect = array_get($params, 'success_redirect', null);
            }
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

    function __call($name, $arguments) {
        if($name == 'results')
            return $this->getFormResults($arguments);
        return null;
    }

    function __isset($name) {
        return in_array($name, ['formBuilder', 'successMessage', 'results']);
    }


    function __toString() {
        $fieldsViews = [];
        $fieldsData = [];

        if (!$this->form)
            return '';

        foreach ($this->form->fields as $field) {
            $fieldsData[$field->alias] = $field;
            $fieldsViews[$field->alias] = view('moduleforms::frontend.fields.' . $field->type, ['form' => $this->form, 'field' => $field]);
        }

        $attributes = [
            'class' => 'solaria-form form-' . $this->form->alias,
            'method' => 'post',
            'action' => url('modules/forms/process-form')
        ];

        $successView = view('moduleforms::frontend.partials.success', ['success' => Session::get('success', null)]);
        $openView = view('moduleforms::frontend.partials.open', ['form' => $this->form, 'isAjax' => $this->isAjax, 'attr' => $attributes]);
        $closeView = view('moduleforms::frontend.partials.close', ['form' => $this->form, 'site' => $this->site, 'showButton' => true]);

        $formView = 'moduleforms::frontend.form';
        $customFormView = 'moduleforms::' . $this->form->site->alias . '.' . $this->form->alias . '.form';

        if (view()->exists($customFormView))
            $formView = $customFormView;

        return view($formView, [
            'open_view' => $openView,
            'close_view' => $closeView,
            'success_view' => $successView,
            'fields_views' => $fieldsViews,
            'fields_data' => $fieldsData,
            'was_sent' => Session::has('success'),
            'form' => $this->form,
            'site' => $this->site,
            'isAjax' => $this->isAjax
        ])->render();
    }

    private function getTwigForm() {
        $form = FormModel::where(['alias' => $this->form_alias, 'site_id' => $this->site->id])->first();
        return new FormTwig($form, $this->site, $this->isAjax, $this->successRedirect);
    }

    private function getSuccessMessage() {
        $success = session('success', '');
        if (is_array($success)) {
            $success = '</div>' . implode('</div><div>', $success) . '</div>';
        }
        return $success;
    }

    private function getFormResults($arguments) {
        $options = array_get($arguments, 0, []);
        $id = array_get($options, 'id', null);
        $formId = array_get($options, 'form_id', null);

        if($id) {
            $formResult = FormResult::find($id);
            if($formResult) {
                return $formResult->results;
            }
            return [];
        }

        if($formId) {
            $formResults = FormResult::where(['form_id' => $formId])->get();
            if($formResults) {
                $results = [];
                foreach ($formResults as $formResult) {
                    $results[] = $formResult->results;
                }
                return $results;
            }
            return [];
        }

        $formResults = FormResult::all();
        if($formResults) {
            $results = [];
            foreach ($formResults as $formResult) {
                $results[] = $formResult->results;
            }
            return $results;
        }
        return [];
    }
}
