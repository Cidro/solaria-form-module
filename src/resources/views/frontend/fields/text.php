<div class="form-group <?=$field->hasErrors() ? 'has-feedback has-error' : '';?>">
    <label class="control-label" for="field-<?=$field->alias;?>"><?=lang('module_forms.' . $field->alias, $field->name)?></label>
    <input
        class="form-control"
        type="text"
        id="field-<?=$field->alias;?>"
        name="field-<?=$field->alias;?>"
        value="<?=$field->getOldValue();?>"
        <?= $field->getAttributes(); ?>
        placeholder="<?=$field->getConfig('placeholder');?>"/>
    <?php if($field->hasErrors()): ?>
        <div class="help-block with-errors"><?=$field->getErrors();?></div>
    <?php endif; ?>
</div>