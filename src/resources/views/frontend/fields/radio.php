<div class="form-group <?=$field->hasErrors() ? 'has-feedback has-error' : '';?>">
    <label class="control-label" for="field-<?=$field->alias;?>"><?=lang('module_forms.' . $field->alias, $field->name)?></label>
    <?php $options =  object_get($field->config, 'options', []); ?>
    <?php foreach ($options as $option): ?>
        <div class="radio">
            <label>
                <input type="radio"
                       id="field-<?=$field->alias;?>-<?=$option->value;?>"
                       name="field-<?=$field->alias;?>"
                       value="<?=$option->value;?>"
                       <?= $field->getOldValue() == $option->value ? 'checked="true"' : '' ?>/>
                <?=lang('module_forms.' . $option->value, $option->name);?>
            </label>
        </div>
    <?php endforeach ?>
    <?php if($field->hasErrors()): ?>
        <div class="help-block with-errors"><?=$field->getErrors();?></div>
    <?php endif; ?>
</div>