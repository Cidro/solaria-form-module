<div class="form-group">
    <label for="field-<?=$field->alias;?>"><?=$field->name;?></label>
    <?php foreach ($field->config->options as $option): ?>
        <div class="checkbox">
            <label>
                <input type="checkbox"
                       id="field-<?=$field->alias;?>-<?=$option->value;?>"
                       name="field-<?=$field->alias;?>[]"
                       value="<?=$option->value;?>" />
                <?=$option->name;?>
            </label>
        </div>
    <?php endforeach ?>
</div>