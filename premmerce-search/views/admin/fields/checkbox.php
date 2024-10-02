<?php if ( ! defined('WPINC')) {
    die;
} ?>

<label for="<?php echo $name ?>">
    <input type="checkbox" id="<?php echo $name ?>" name="<?php echo $name ?>" <?php echo empty($value) ? '' : 'checked'; ?>>
    <?php if (!empty($description)): ?>
        <?php echo $description ?>
    <?php endif; ?>
</label>
