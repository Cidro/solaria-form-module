<?php
namespace Asimov\Solaria\Modules\Forms\Models;

use DB;
use Illuminate\Database\Eloquent\Model;
use Log;


class Form extends Model {

    protected $table = 'module_forms';

    public function fields(){
        return $this->hasMany('Asimov\Solaria\Modules\Forms\Models\FormField', 'form_id', 'id');
    }

    public function site(){
        return $this->belongsTo('Solaria\Models\Site', 'site_id', 'id');
    }

    public function updateFormFields($fieldsConfig){
        DB::beginTransaction();
        try {
            $this->fields()->delete();
            foreach ($fieldsConfig as $fieldConfig) {
                $field = new FormField();
                $field->form_id = $this->id;
                $field->name = array_get($fieldConfig, 'name', '');
                $field->alias = array_get($fieldConfig, 'alias', '');
                $field->type = array_get($fieldConfig, 'type.type', '');
                $field->config = array_get($fieldConfig,'config', null);
                $field->save();
            }
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error($e->getMessage());
        }
        DB::commit();
    }
}