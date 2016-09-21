<form
    class="<?= $attr['class']; ?>"
    method="<?= $attr['method']; ?>"
    action="<?= $attr['action']; ?>"
    <?= $isAjax ? 'data-ajax-form="true"' : ''; ?>
    <?= isset($customAttr) ? $customAttr : ''; ?>>
