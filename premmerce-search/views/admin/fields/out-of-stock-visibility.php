<?php if ( ! defined('WPINC')) {
    die;
}
$outOfStockSearchChecked = !empty($data['outOfStock']) ? checked($data['outOfStock'], true, false) : '';
?>

<fieldset>
	<label>
		<input id="out-of-stock-visibility" type="checkbox" name="premmerce_out_of_stock_search[outOfStock]" value="1" <?php echo $outOfStockSearchChecked ?> >
		<?php esc_html_e('Hide out of stock items from search', 'premmerce-search'); ?>
	</label>
</fieldset>
