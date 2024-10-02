<?php if(! defined('WPINC')) die;

$imageSearchAutocompleteFieldsChecked = !empty($data['image']) ? checked($data['image'], true, false) : '';
$priceSearchAutocompleteFieldsChecked = !empty($data['price']) ? checked($data['price'], true, false) : '';
$btnSearchAutocompleteFieldsChecked   = !empty($data['btn']) ? checked($data['btn'], true, false) : '';
?>

<fieldset>
	<label>
		<input id="autocomplete-fields-image" type="checkbox" name="premmerce_search_autocomplete_fields[image]" value="1" <?php echo $imageSearchAutocompleteFieldsChecked; ?>>
		<?php esc_html_e('Show products image', 'premmerce-search'); ?>
	</label>

    <br>

	<label>
		<input id="autocomplete-fields-price" type="checkbox" name="premmerce_search_autocomplete_fields[price]" value="1" <?php echo $priceSearchAutocompleteFieldsChecked; ?>>
		<?php esc_html_e('Show products price', 'premmerce-search'); ?>
	</label>

    <br>

	<label>
		<input id="autocomplete-fields-btn" type="checkbox" name="premmerce_search_autocomplete_fields[btn]" value="1" <?php echo $btnSearchAutocompleteFieldsChecked; ?>>
		<?php esc_html_e('Show products add to cart button', 'premmerce-search'); ?>
	</label>
</fieldset>
