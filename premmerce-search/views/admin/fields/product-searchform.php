<?php if ( ! defined('WPINC')) {
    die;
} ?>

<input id="input-placeholder-text" type="text" name="premmerce_search_template_rewrite[placeholderText]" value="<?php echo !empty($data['placeholderText']) ? $data['placeholderText'] : '' ?>">
<p class="description"><?php esc_html_e('Replace input placeholder text from search form', 'premmerce-search'); ?></p>

<br>

<input id="button-text" type="text" name="premmerce_search_template_rewrite[buttonText]" value="<?php echo !empty($data['buttonText']) ? $data['buttonText'] : '' ?>">
<p class="description"><?php esc_html_e('Replace button text from search form', 'premmerce-search'); ?></p>
