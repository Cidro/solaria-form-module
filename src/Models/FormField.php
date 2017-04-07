<?php

namespace Asimov\Solaria\Modules\Forms\Models;


use Illuminate\Database\Eloquent\Model;
use Request;
use Session;

class FormField extends  Model {

    protected $table = 'module_form_fields';

    /**
     * @param $value
     */
    public function setConfigAttribute($value){
        $this->attributes['config'] = json_encode($value, JSON_FORCE_OBJECT | JSON_UNESCAPED_UNICODE);
    }

    /**
     * @param $value
     * @return mixed
     */
    public function getConfigAttribute($value){
        return json_decode($value);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function form(){
        return $this->belongsTo('Asimov\Solaria\Modules\Forms\Models\Form', 'form_id', 'id');
    }

    /**
     * @return string
     */
    public function getOldValue(){
        $value = $this->type == 'file' ? request()->old('hidden-field-' . $this->alias) : request()->old('field-' . $this->alias);
        if(empty($value)){
            if($this->type == 'hidden' && object_get($this->config, 'dataType') == 'json')
                return htmlentities(request()->get($this->alias));
            return request()->get($this->alias);
        }
        return $value;
    }

    /**
     * @return bool
     */
    public function hasErrors(){
        if(!Session::has('errors'))
            return false;
        return Session::get('errors')->has($this->alias);
    }

    /**
     * @return string
     */
    public function getErrors(){
        $result = '';
        $errors = Session::get('errors')->get($this->alias);
        foreach ($errors as $error) {
            $result .= '<p>' . $error . '</p>';
        }
        return $result;
    }

    /**
     * @return string
     */
    public function getValidations(){
        return object_get($this, 'config.validations', '');
    }

    /**
     * @return string
     */
    public function getConfig($item){
        return isset($this->config->{$item}) ? $this->config->{$item} : '';
    }

    /**
     * @return string
     */
    public function getAttributes(){
        $result = [];
        if($attributes = object_get($this->config, 'attributes')){
            //Readonly
            if(object_get($attributes, 'readonly')){
                $result[] = 'readonly="' . object_get($attributes, 'readonly') . '"';
            }
        }
        if($maxLength = object_get($this->config, 'maxLength')) {
            $result[] = 'maxlength=' . $maxLength;
        }
        $validationsRules = explode('|', $this->getValidations());
        if(in_array('rut', $validationsRules)) {
            $result[] = 'data-validation="rut"';
        }
        return implode(' ', $result);
    }

    /**
     * @return string
     */
    public function getOptions(){
        $options = [];
        if(object_get($this->config, 'options', null)){
            foreach ($this->config->options as $option) {
                $options[$option->value] = $option->name;
            }
        }
        return $options;
    }
}