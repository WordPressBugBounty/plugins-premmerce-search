<?php if ( ! defined('WPINC')) {
    die;
} ?>

<input type="text" name="<?php echo $name ?>" value="<?php echo $value; ?>">

<?php if (!empty($description)): ?>
    <p class="description"><?php echo $description ?></p>
<?php endif; ?>