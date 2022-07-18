<?php
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

// BEGIN ENQUEUE PARENT ACTION
// AUTO GENERATED - Do not modify or remove comment markers above or below:

if ( !function_exists( 'chld_thm_cfg_locale_css' ) ):
    function chld_thm_cfg_locale_css( $uri ){
        if ( empty( $uri ) && is_rtl() && file_exists( get_template_directory() . '/rtl.css' ) )
            $uri = get_template_directory_uri() . '/rtl.css';
        return $uri;
    }
endif;
add_filter( 'locale_stylesheet_uri', 'chld_thm_cfg_locale_css' );

// END ENQUEUE PARENT ACTION

/**
 * Simple product setting.
 */
function ace_add_stock_inventory_multiplier_setting() {

	?><div class='options_group'><?php

		woocommerce_wp_text_input( array(
			'id'				=> '_stock_multiplier',
			'label'				=> __( 'Inventory reduction per quantity sold', 'woocommerce' ),
			'desc_tip'			=> 'true',
			'description'		=> __( 'Enter the quantity multiplier used for reducing stock levels when purchased.', 'woocommerce' ),
			'type' 				=> 'number',
			'custom_attributes'	=> array(
				'min'	=> '1',
				'step'	=> '1',
			),
		) );

	?></div><?php

}
add_action( 'woocommerce_product_options_inventory_product_data', 'ace_add_stock_inventory_multiplier_setting' );

/**
 * Add variable setting.
 *
 * @param $loop
 * @param $variation_data
 * @param $variation
 */
function ace_add_variation_stock_inventory_multiplier_setting( $loop, $variation_data, $variation ) {

	$variation = wc_get_product( $variation );
	woocommerce_wp_text_input( array(
		'id'				=> "stock_multiplier{$loop}",
		'name'				=> "stock_multiplier[{$loop}]",
		'value'				=> $variation->get_meta( '_stock_multiplier' ),
		'label'				=> __( 'Inventory reduction per quantity sold', 'woocommerce' ),
		'desc_tip'			=> 'true',
		'description'		=> __( 'Enter the quantity multiplier used for reducing stock levels when purchased.', 'woocommerce' ),
		'type' 				=> 'number',
		'custom_attributes'	=> array(
			'min'	=> '1',
			'step'	=> '1',
		),
	) );

}
add_action( 'woocommerce_variation_options_pricing', 'ace_add_variation_stock_inventory_multiplier_setting', 50, 3 );

/**
 * Save the custom fields.
 *
 * @param WC_Product $product
 */
function ace_save_custom_stock_reduction_setting( $product ) {

	if ( ! empty( $_POST['_stock_multiplier'] ) ) {
		$product->update_meta_data( '_stock_multiplier', absint( $_POST['_stock_multiplier'] ) );
	}
}
add_action( 'woocommerce_admin_process_product_object', 'ace_save_custom_stock_reduction_setting'  );

/**
 * Save custom variable fields.
 *
 * @param int $variation_id
 * @param $i
 */
function ace_save_variable_custom_stock_reduction_setting( $variation_id, $i ) {
    $variation = wc_get_product( $variation_id );
	if ( ! empty( $_POST['stock_multiplier'] ) && ! empty( $_POST['stock_multiplier'][ $i ] ) ) {
		$variation->update_meta_data( '_stock_multiplier', absint( $_POST['stock_multiplier'][ $i ] ) );
		$variation->save();
	}
}
add_action( 'woocommerce_save_product_variation', 'ace_save_variable_custom_stock_reduction_setting', 10, 2 );

/**
 * Reduce with custom stock quantity based on the settings.
 *
 * @param $quantity
 * @param $order
 * @param $item
 * @return mixed
 */
function ace_custom_stock_reduction( $quantity, $order, $item ) {

	/** @var WC_Order_Item_Product $product */
	$multiplier = $item->get_product()->get_meta( '_stock_multiplier' );

	if ( empty( $multiplier ) && $item->get_product()->is_type( 'variation' ) ) {
		$product = wc_get_product( $item->get_product()->get_parent_id() );
		$multiplier = $product->get_meta( '_stock_multiplier' );
	}

	if ( ! empty( $multiplier ) ) {
		$quantity = $multiplier * $quantity;
	}

	return $quantity;
}
add_filter( 'woocommerce_order_item_quantity', 'ace_custom_stock_reduction', 10, 3 );
add_filter( 'woocommerce_ship_to_different_address_checked', '__return_false' );
