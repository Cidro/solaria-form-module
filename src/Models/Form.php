<?php
namespace Asimov\Solaria\Modules\Forms\Models;

use DB;
use Illuminate\Database\Eloquent\Model;
use Log;
use Storage;


class Form extends Model {

    protected $table = 'module_forms';

    public $client_email_template = '';

    public $user_email_template = '';

    public $old_alias = '';

    protected $appends = ['client_email_template', 'user_email_template'];

    protected $casts = [
        'config' => 'object',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function fields(){
        return $this->hasMany('Asimov\Solaria\Modules\Forms\Models\FormField', 'form_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function results(){
        return $this->hasMany('Asimov\Solaria\Modules\Forms\Models\FormResult', 'form_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function site(){
        return $this->belongsTo('Solaria\Models\Site', 'site_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user(){
        return $this->belongsTo('Solaria\Models\User', 'default_assigned_user_id', 'id');
    }

    /**
     * Guarda el nombre anterior del layout en caso de que se cambie
     * @param $alias
     */
    public function setAliasAttribute($alias){
        $this->old_alias = $this->alias;
        $this->attributes['alias'] = $alias;
    }

    /**
     * Obtiene el contenido de la plantilla de correo para el cliente asociada al formulario
     * @return string
     */
    public function getClientEmailTemplateAttribute(){
        return Storage::drive('vendor_views')->get($this->getEmailTemplateFolderName() . '/client.' . config('twigbridge.twig.extension', 'twig'));
    }

    /**
     * Obtiene el contenido de la plantilla de correo para el ejecutivo asociada al formulario
     * @return string
     */
    public function getUserEmailTemplateAttribute(){
        return Storage::drive('vendor_views')->get($this->getEmailTemplateFolderName() . '/user.' . config('twigbridge.twig.extension', 'twig'));
    }

    /**
     * Obtiene la ruta donde se guardan las plantillas de correo
     * @param bool|false $old
     * @return string
     */
    public function getEmailTemplateFolderName($old = false){
        return '/moduleforms/' . $this->site->alias . '/' . ($old ? $this->old_alias : $this->alias) . '/emails';
    }

    /**
     * Actualiza los campos del formulario
     * @param $fieldsConfig
     */
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

    /**
     * Graba el formulario y sus plantillas asociadas
     * @param array $options
     * @return bool
     */
    public function save(array $options = []) {
        $client_email_template_filename = 'client.' . config('twigbridge.twig.extension', 'twig');
        $user_email_template_filename = 'user.' . config('twigbridge.twig.extension', 'twig');

        if($this->exists && $this->alias != $this->old_alias)
            Storage::drive('vendor_views')->move($this->getEmailTemplateFolderName(true), $this->getEmailTemplateFolderName());

        Storage::drive('vendor_views')->put($this->getEmailTemplateFolderName() . '/' . $client_email_template_filename , $this->client_email_template);
        Storage::drive('vendor_views')->put($this->getEmailTemplateFolderName() . '/' . $user_email_template_filename , $this->user_email_template);

        try {
            chmod(config('filesystems.disks.vendor_views.root') . '/' . $this->getEmailTemplateFolderName(), 0775);
            chmod(config('filesystems.disks.vendor_views.root') . '/' . $this->getEmailTemplateFolderName() . '/' . $client_email_template_filename, 0644);
            chmod(config('filesystems.disks.vendor_views.root') . '/' . $this->getEmailTemplateFolderName() . '/' . $user_email_template_filename, 0644);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
        }

        return parent::save($options);
    }


}