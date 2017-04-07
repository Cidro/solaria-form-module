    <?php echo csrf_field(); ?>
    <input type="hidden" name="form_id" id="form_id" value="<?=$form->id;?>" />
    <input type="hidden" name="language_id" value="<?=$site->getLanguage()->id?>">
    <?php if(isset($successRedirect) && $successRedirect): ?>
        <input type="hidden" name="success_redirect" value="<?= $successRedirect ?>">
    <?php endif; ?>
    <?php if($showButton): ?>
        <button class="btn btn-default" type="submit">Enviar</button>
    <?php endif; ?>
</form>