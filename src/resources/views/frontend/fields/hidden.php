<input
    type="hidden"
    id="field-<?=$field->alias;?>"
    name="field-<?=$field->alias;?>"
    value="<?=$field->getOldValue();?>"
    <?= $field->getAttributes(); ?> />