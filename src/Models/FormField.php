<?php

namespace Asimov\Solaria\Modules\Forms\Models;


use Illuminate\Database\Eloquent\Model;
use Session;

class FormField extends  Model {

    protected $table = 'module_form_fields';

    protected $casts = [
        'config' => 'object',
    ];

    public function form(){
        return $this->belongsTo('Asimov\Solaria\Modules\Forms\Models\Form', 'form_id', 'id');
    }

    /**
     * @return string
     */
    public function getOldValue(){
        return Session::get('moduleforms::' . $this->alias);
    }

    /**
     * Obtiene un listado de atributos asociados al campo
     * @return string
     */
    public function getExtraAttributes(){
        $attributes = [];
        if(is_null($this->config))
            return '';

        if(isset($this->config->required) && $this->config->required)
            $attributes[] = 'required';

        return implode(' ', $attributes);
    }
}