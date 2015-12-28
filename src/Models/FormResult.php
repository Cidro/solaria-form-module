<?php

namespace Asimov\Solaria\Modules\Forms\Models;


use Illuminate\Database\Eloquent\Model;
use Mail;
use Solaria\Models\User;

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
     * @param string $to
     * @param string $template
     */
    protected function sendEmail($to, $template = 'user'){
        $form = $this->form;
        $site = $this->form->site;
        $fields = [];

        foreach ($form->fields as $field){
            if(isset($this->results->{$field->alias}))
                $fields[$field->alias] = ['name' => $field->name, 'value' => $this->results->{$field->alias}];
        }

        $mail_view = 'moduleforms::' . $site->alias . '.' . $form->alias . '.emails.' . $template;
        $mail_data = ['site' => $site, 'form' => $form, 'fields' => $fields];
        Mail::send($mail_view, $mail_data, function($message) use ($form, $to){
            $message->to($to);
        });
    }

}