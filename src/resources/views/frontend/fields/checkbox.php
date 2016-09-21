<div class="form-group <?=$field->hasErrors() ? 'has-feedback has-error' : '';?>">
    <label class="control-label" for="field-<?=$field->alias;?>"><?=lang('module_forms.' . $field->alias, $field->name)?></label>
    <?php foreach ($field->config->options as $option): ?>
        <div class="checkbox">
            <label>
                <input type="checkbox"
                       id="field-<?=$field->alias;?>-<?=$option->value;?>"
                       name="field-<?=$field->alias;?>[]"
                       value="<?=$option->value;?>"
                       <?= (!is_null($field->getOldValue()) && in_array($option->value, $field->getOldValue())) ? 'checked' : '' ?>/>
                <?=lang('module_forms.' . $option->value, $option->name);?>
            </label>
        </div>
    <?php endforeach ?>
    <?php if($field->hasErrors()): ?>
        <div class="help-block with-errors"><?=$field->getErrors();?></div>
    <?php endif; ?>
</div>