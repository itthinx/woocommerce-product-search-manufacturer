<?php
/**
 * woocommerce-product-search-manufacturer.php
 *
 * Copyright (c) 2023 www.itthinx.com
 *
 * This code is released under the GNU General Public License.
 * See COPYRIGHT.txt and LICENSE.txt.
 *
 * This code is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * This header and all notices must be kept intact.
 *
 * @author itthinx
 * @package woocommerce-product-search-manufacturer
 * @since 1.0.0
 *
 * Plugin Name: WooCommerce Product Search Manufacturer
 * Plugin URI: https://github.com/itthinx/woocommerce-product-search-manufacturer
 * Description: Extends <a href="https://woocommerce.com/products/woocommerce-product-search/">WooCommerce Product Search</a> - Keyword searches for terms in a Manufacturer custom taxonomy will result in matching products. 
 * Version: 1.0.0
 * Author: itthinx
 * Author URI: https://www.itthinx.com
 */

if ( !defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Uses the WooCommerce Product Search API to implement keyword searches for terms in a Manufacturer custom taxonomy that will result in matching products.
 *
 * The custom taxonomy assumed is registered in the 'WooCommerce Manufacturer' plugin: https://github.com/itthinx/woocommerce-manufacturer
 */
class WooCommerce_Product_Search_Manufacturer {

	/**
	 * Manufacturer taxonomy
	 *
	 * @var string
	 */
	private static $manufacturer_taxonomy = 'manufacturer';

	/**
	 * Boot this ...
	 */
	public static function init() {
		add_filter( 'woocommerce_product_search_process_query_product_taxonomies', array( __CLASS__, 'woocommerce_product_search_process_query_product_taxonomies' ), 10, 2 );
		add_filter( 'woocommerce_product_search_indexer_filter_content', array( __CLASS__, 'woocommerce_product_search_indexer_filter_content' ), 10, 3 );
	}

	/**
	 * Add the manufacturer taxonomy to handled taxonomies.
	 *
	 * @param string[] $product_taxonomies
	 * @param WP_Query $wp_query
	 *
	 * return string[]
	 */
	public static function woocommerce_product_search_process_query_product_taxonomies( $product_taxonomies, $wp_query ) {
		if ( is_array( $product_taxonomies ) && !in_array( self::$manufacturer_taxonomy, $product_taxonomies ) ) {
			$product_taxonomies[] = self::$manufacturer_taxonomy;
		}
		return $product_taxonomies;
	}

	/**
	 * Add the manufacturer to content indexing so product searches for the manufacturer include related products.
	 *
	 * @param string $content
	 * @param string $context
	 * @param int $post_id
	 *
	 * @return string
	 */
	public static function woocommerce_product_search_indexer_filter_content( $content, $context, $post_id ) {
		if ( taxonomy_exists( self::$manufacturer_taxonomy ) ) {
			if ( $context === 'post_content' ) {

				$manufacturers = null;
				$product = wc_get_product( $post_id );
				if ( $product->is_type( 'variation' ) ) {
					$post_id = $product->get_parent_id();
				}
				$terms = get_the_terms( $post_id, self::$manufacturer_taxonomy );
				if ( !is_wp_error( $terms ) && !empty( $terms ) && is_array( $terms ) ) {
					$manufacturers = array();
					foreach ( $terms as $term ) {
						$manufacturers[] = $term->name;
					}
					$manufacturers = implode( ' ', $manufacturers );
				}
				if ( $manufacturers !== null && is_string( $manufacturers ) ) {
					$content .= ' ' . $manufacturers;
				}
			}
		}
		return $content;
	}
}

WooCommerce_Product_Search_Manufacturer::init();

