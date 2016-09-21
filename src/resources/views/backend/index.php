<div>
    <ul class="nav nav-tabs" role="tablist">
        <?php if(Auth::user()->can('module_forms_manage_forms', null)): ?>
        <li role="presentation" class="<?=$active=='forms' ? 'active' : '';?>">
            <a href="<?=url('backend/modules/forms');?>"><span class="glyphicon glyphicon-list-alt"></span> Formularios</a>
        </li>
        <?php endif; ?>
        <?php if(Auth::user()->can('module_forms_view_results', null)): ?>
        <li role="presentation" class="<?=$active=='results' ? 'active' : '';?>">
            <a href="<?=url('backend/modules/forms/results');?>"><span class="glyphicon glyphicon-stats"></span> Registros</a>
        </li>
        <?php endif; ?>
    </ul>
    <div class="tab-content">
        <?=$content;?>
    </div>
</div>