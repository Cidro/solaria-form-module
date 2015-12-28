<div class="form-group">
    <label for="field-<?=$field->alias;?>"><?=$field->name;?></label>
    <input
        <?=$field->getExtraAttributes();?>
        class="form-control"
        type="text"
        id="field-<?=$field->alias;?>"
        name="field-<?=$field->alias;?>"
        value="<?=$field->getOldValue();?>" />
</div>