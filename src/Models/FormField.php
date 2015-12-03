<?php

namespace Asimov\Solaria\Modules\Forms\Models;


use Illuminate\Database\Eloquent\Model;

class FormField extends  Model {

    protected $table = 'module_form_fields';

    protected $casts = [
        'config' => 'object',
    ];

    public function form(){
        return $this->belongsTo('Asimov\Solaria\Modules\Forms\Models\Form', 'form_id', 'id');
    }
}