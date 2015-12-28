<?php
//TODO: Añadir tinymce al front cuando se incluya este campo
?>
<div class="form-group">
    <label for="field-<?=$field->alias;?>"><?=$field->name;?></label>
    <textarea
        <?=$field->getExtraAttributes();?>
        class="form-control"
        id="field-<?=$field->alias;?>"
        name="field-<?=$field->alias;?>"><?=$field->getOldValue();?></textarea>
</div>