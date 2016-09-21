<?php

namespace Asimov\Solaria\Modules\Forms\Models;


use App;
use Illuminate\Database\Eloquent\Model;
use Log;
use Mail;
use Solaria\Models\User;
use Solaria\Models\Twig\Site as TwigSite;

class FormResult extends  Model {

    protected $table = 'module_form_results';

    protected $casts = [
        'results' => 'object',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function form(){
        return $this->belongsTo('Asimov\Solaria\Modules\Forms\Models\Form', 'form_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user(){
        return $this->belongsTo('Solaria\Models\User', 'assigned_user_id', 'id');
    }

    /**
     * Asigna un resultado a un usuario
     * @param User $user
     */
    public function assign(User $user){
        $this->user()->associate($user);
        $this->sendEmail($user->email);
    }

    /**
     * @param $email_fields
     */
    public function notifyClient($email_fields) {
        if(isset($this->results->{$email_fields})){
            $email = $this->results->{$email_fields};
            $this->sendEmail($email, 'client');
        }
    }

    /**
     * @param $notificationRules
     */
    public function notifyUsers($notificationRules){
        $emails = [];
        foreach ($notificationRules as $notificationRule) {
            if($this->matchNotificationRule($notificationRule->fields)){
                $emails = array_merge($emails, $notificationRule->users);
            }
        }
        $this->sendEmail($emails);
    }

    /**
     * @param $rules
     * @return bool
     */
    public function matchNotificationRule($rules){
        foreach($rules as $rule){
            if($rule->alias == null && $rule->value == '*')
                return true;

            if($rule->operation == '='){
                if(!isset($this->results->{$rule->alias})
                    || !in_array($rule->value, [$this->results->{$rule->alias}, '*']))
                    return false;
            }

            if($rule->operation == '!='){
                if(!isset($this->results->{$rule->alias})
                    || in_array($rule->value, [$this->results->{$rule->alias}, '*']))
                    return false;
            }
        }
        return true;
    }

    /**
     * @param string $to
     * @param string $template
     */
    protected function sendEmail($to, $template = 'user'){
        $form = $this->form;
        $site = $this->form->site;
        $fields = [];

        foreach ($form->fields as $field){
            if(isset($this->results->{$field->alias})){
                if(gettype($this->results->{$field->alias}) == 'array' && !($field->type == 'hidden' && object_get($field->config, 'dataType', '') == 'json'))
                    $value = implode(',', $this->results->{$field->alias});
                else
                    $value = $this->results->{$field->alias};

                $fields[$field->alias] = [
                    'name' => $field->name,
                    'alias' => $field->alias,
                    'value' => $value,
                    'options' => $field->getOptions()
                ];
            }
        }

        view()->share([
            'site' => new TwigSite(App::make('site')),
        ]);

        $subject = $this->getSubject($template);
        $mail_view = 'moduleforms::' . $site->alias . '.' . $form->alias . '.emails.' . $template;
        $mail_data = ['form' => $form, 'fields' => $fields];
        try {
            Mail::send($mail_view, $mail_data, function($message) use ($form, $to, $subject){
                $message->subject($subject);
                $message->from(env('MAIL_FROM'), env('MAIL_FROMNAME'));
                $message->to($to);
            });
        } catch (\Exception $e ){
            Log::error($e->getMessage());
        }
    }

    /**
     * @param $template
     * @return string
     */
    protected function getSubject($template){
        $alias = str_slug($this->form->alias, '_');
        return lang('module_forms.subject_' . $template . '_' . $alias);
    }

    public function save(array $options = []) {
        if(!$this->exists){
            $this->ip = request()->ip();
            $this->user_agent = request()->server('HTTP_USER_AGENT');
        }
        return parent::save($options);
    }


}