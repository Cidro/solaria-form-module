<div class="alert alert-success">
    <?php if(gettype($success) == 'array'): ?>
        <div><?= implode('</div><div>', $success); ?></div>
    <?php else: ?>
        <?= $success; ?>
    <?php endif; ?>
</div>