<div class="form-group <?=$field->hasErrors() ? 'has-feedback has-error' : '';?>">
    <label class="control-label" for="field-<?=$field->alias;?>"><?=lang('module_forms.' . $field->alias, $field->name)?></label>
    <div>
        <div class="btn btn-primary fileinput-button">
            <span class="upload-loader glyphicon glyphicon-refresh hidden"></span> Selecciona el archivo
            <input
                class="form-control"
                type="file"
                id="field-<?=$field->alias;?>"
                name="field-<?=$field->alias;?>"
                value="<?=$field->getOldValue();?>"
                data-url="<?=url('modules/forms/process-file-upload');?>"/>
            <input type="hidden"
                id="hidden-field-<?=$field->alias;?>"
                name="hidden-field-<?=$field->alias;?>"
                value="<?=$field->getOldValue();?>"/>
        </div>
    </div>
    <?php if($field->hasErrors()): ?>
        <div class="help-block with-errors"><?=$field->getErrors();?></div>
    <?php endif; ?>
</div>