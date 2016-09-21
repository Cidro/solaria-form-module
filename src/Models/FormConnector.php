<?php
namespace Asimov\Solaria\Modules\Forms\Models;

use Illuminate\Database\Eloquent\Model;


class FormConnector extends Model {

    protected $fillable = ['site_id', 'form_id', 'content_type', 'event'];

    protected $table = 'module_form_connectors';

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function site(){
        return $this->belongsTo('Solaria\Models\Site', 'site_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function form(){
        return $this->belongsTo('Asimov\Solaria\Modules\Forms\Models\Form', 'form_id', 'id');
    }

}