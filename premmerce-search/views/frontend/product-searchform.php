<?php

use Premmerce\Search\SearchPlugin;
/**
 * The template for displaying product search form
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
$data        = get_option( SearchPlugin::OPTIONS['templateRewrite'] );
$placeholder = ! empty( $data['placeholderText'] ) ? $data['placeholderText'] : esc_attr__( 'Search products&hellip;', 'woocommerce' );
$button      = ! empty( $data['buttonText'] ) ? $data['buttonText'] : esc_html_x( 'Search', 'submit button', 'woocommerce' );


?>
<form role="search" method="get" class="woocommerce-product-search" action="<?php echo esc_url( home_url( '/' ) ); ?>">
    <label class="screen-reader-text" for="woocommerce-product-search-field-<?php echo isset( $index ) ? absint( $index ) : 0; ?>"><?php esc_html_e( 'Search for:', 'woocommerce' ); ?></label>
    <input type="search" id="woocommerce-product-search-field-<?php echo isset( $index ) ? absint( $index ) : 0; ?>" class="search-field" placeholder="<?php echo $placeholder; ?>" value="<?php echo get_search_query(); ?>" name="s">
    <button type="submit" value="<?php echo esc_attr_x( 'Search', 'submit button', 'woocommerce' ); ?>"><?php echo $button; ?></button>
    <input type="hidden" name="post_type" value="product">
</form>
