<?php if(session('success')): ?>
    <div class="alert alert-success">
        <?=session('success');?>
    </div>
<?php else: ?>
    <form class="form-<?=$form->alias;?>" method="post" action="<?=url('modules/forms/process-form');?>">
        <?php foreach ($fields_views as $field_view): ?>
            <?=$field_view;?>
        <?php endforeach ?>
        <?php echo csrf_field(); ?>
        <input type="hidden" name="form_id" id="form_id" value="<?=$form->id;?>" />
        <button class="btn btn-default" type="submit">Enviar</button>
    </form>
<?php endif; ?>