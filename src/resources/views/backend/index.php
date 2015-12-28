<div>
    <ul class="nav nav-tabs" role="tablist">
        <li role="presentation" class="<?=$active=='forms' ? 'active' : '';?>">
            <a href="<?=url('backend/modules/forms');?>"><span class="glyphicon glyphicon-list-alt"></span> Formularios</a>
        </li>
        <li role="presentation" class="<?=$active=='results' ? 'active' : '';?>">
            <a href="<?=url('backend/modules/forms/results');?>"><span class="glyphicon glyphicon-stats"></span> Registros</a>
        </li>
    </ul>
    <div class="tab-content">
        <?=$content;?>
    </div>
</div>