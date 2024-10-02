<?php if(! defined('WPINC')) die;

$skuSearchFieldsChecked      = !empty($data['sku']) ? checked($data['sku'], true, false) : '';
$excerptSearchFieldsChecked  = !empty($data['excerpt']) ? checked($data['excerpt'], true, false) : '';
$tagSearchFieldsChecked      = !empty($data['tag']) ? checked($data['tag'], true, false) : '';
$categorySearchFieldsChecked = !empty($data['category']) ? checked($data['category'], true, false) : '';
?>

<fieldset>
	<label>
		<input id="where-to-search-sku" type="checkbox" name="premmerce_search_fields[sku]" value="1" <?php echo $skuSearchFieldsChecked; ?> >
		<?php _e('Products SKU', 'premmerce-search'); ?>
	</label>

    <br>

	<label>
		<input id="where-to-search-excerpt" type="checkbox" name="premmerce_search_fields[excerpt]" value="1" <?php echo $excerptSearchFieldsChecked; ?> >
		<?php _e('Short description', 'premmerce-search'); ?>
	</label>

    <br>

	<label>
		<input id="where-to-search-tag" type="checkbox" name="premmerce_search_fields[tag]" value="1" <?php echo $tagSearchFieldsChecked; ?> >
		<?php _e('Products tags', 'premmerce-search'); ?>
	</label>

    <br>

	<label>
		<input id="where-to-search-category" type="checkbox" name="premmerce_search_fields[category]" value="1" <?php echo $categorySearchFieldsChecked; ?> >
		<?php _e('Products categories', 'premmerce-search'); ?>
	</label>
</fieldset>
