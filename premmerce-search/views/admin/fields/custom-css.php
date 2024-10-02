<?php if ( ! defined('WPINC')) {
    die;
} ?>

<textarea id="premmerce-custom-css" class="large-text code" name="premmerce_search_custom_css" cols="70" rows="8"><?php echo esc_textarea( $data ); ?></textarea>
<p class="description"><?php esc_html_e('Add your CSS rules here', 'premmerce-search'); ?></p>