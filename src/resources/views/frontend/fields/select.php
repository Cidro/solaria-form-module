<div class="form-group">
    <label for="field-<?=$field->alias;?>"><?=$field->name;?></label>
    <select <?=$field->getExtraAttributes();?> class="form-control" name="field-<?=$field->alias;?>" id="field-<?=$field->alias;?>">
        <?php foreach ($field->config->options as $option): ?>
            <option value="<?=$option->value;?>"><?=$option->name;?></option>
        <?php endforeach ?>
    </select>
</div>