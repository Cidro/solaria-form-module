<div class="form-group <?=$field->hasErrors() ? 'has-feedback has-error' : '';?>">
    <label class="control-label" for="field-<?=$field->alias;?>"><?=lang('module_forms.' . $field->alias, $field->name)?></label>
    <select class="form-control" name="field-<?=$field->alias;?>" id="field-<?=$field->alias;?>">
        <?php foreach ($field->config->options as $option): ?>
            <option <?= $field->getOldValue() == $option->value ? 'selected="true"' : '' ?> value="<?=$option->value;?>"><?=lang('module_forms.' . $option->value, $option->name);?></option>
        <?php endforeach ?>
    </select>
    <?php if($field->hasErrors()): ?>
        <div class="help-block with-errors"><?=$field->getErrors();?></div>
    <?php endif; ?>
</div>