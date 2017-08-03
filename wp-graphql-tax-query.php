<?php
/**
 * Plugin Name: WP GraphQL Tax Query
 * Plugin URI: https://github.com/wp-graphql/wp-graphql-tax-query
 * Description: Tax_Query support for the WPGraphQL plugin. Requires WPGraphQL version 0.0.15 or newer.
 * Author: Digital First Media, Jason Bahl
 * Author URI: http://www.wpgraphql.com
 * Version: 0.0.2
 * Text Domain: wp-graphql-tax-query
 * Requires at least: 4.7.0
 * Tested up to: 4.7.1
 *
 * @package WPGraphQLTaxQuery
 * @category Core
 * @author Digital First Media, Jason Bahl
 * @version 0.0.5
 */
namespace WPGraphQL;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use WPGraphQL\TaxQuery\Type\TaxQueryType;

class TaxQuery {

	/**
	 * This holds the TaxQuery input type object
	 * @var $tax_query
	 */
	private static $tax_query;

	/**
	 * TaxQuery constructor.
	 *
	 * This hooks the plugin into the WPGraphQL Plugin
	 *
	 * @since 0.0.1
	 */
	public function __construct() {

		/**
		 * Setup plugin constants
		 * @since 0.0.1
		 */
		$this->setup_constants();

		/**
		 * Included required files
		 * @since 0.0.1
		 */
		$this->includes();

		/**
		 * Filter the query_args for the PostObjectQueryArgsType
		 * @since 0.0.1
		 */
		add_filter( 'graphql_queryArgs_fields', [ $this, 'add_input_fields' ], 10, 1 );

		/**
		 * Filter the $allowed_custom_args for the PostObjectsConnectionResolver to map the
		 * taxQuery input to WP_Query terms
		 * @since 0.0.1
		 */
		add_filter( 'graphql_map_input_fields_to_wp_query', [ $this, 'map_input_fields' ], 10, 2 );

	}

	/**
	 * Setup plugin constants.
	 *
	 * @access private
	 * @since 0.0.1
	 * @return void
	 */
	private function setup_constants() {

		// Plugin version.
		if ( ! defined( 'WPGRAPHQL_TAXQUERY_VERSION' ) ) {
			define( 'WPGRAPHQL_TAXQUERY_VERSION', '0.0.2' );
		}

		// Plugin Folder Path.
		if ( ! defined( 'WPGRAPHQL_TAXQUERY_PLUGIN_DIR' ) ) {
			define( 'WPGRAPHQL_TAXQUERY_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
		}

		// Plugin Folder URL.
		if ( ! defined( 'WPGRAPHQL_TAXQUERY_PLUGIN_URL' ) ) {
			define( 'WPGRAPHQL_TAXQUERY_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
		}

		// Plugin Root File.
		if ( ! defined( 'WPGRAPHQL_TAXQUERY_PLUGIN_FILE' ) ) {
			define( 'WPGRAPHQL_TAXQUERY_PLUGIN_FILE', __FILE__ );
		}

	}

	/**
	 * Include required files.
	 *
	 * Uses composer's autoload
	 *
	 * @access private
	 * @since 0.0.1
	 * @return void
	 */
	private function includes() {
		// Autoload Required Classes
		require_once( WPGRAPHQL_TAXQUERY_PLUGIN_DIR . 'vendor/autoload.php' );
	}

	/**
	 * add_input_fields
	 *
	 * This adds the taxQuery input fields
	 *
	 * @param $fields
	 *
	 * @return mixed
	 * @since 0.0.1
	 */
	public function add_input_fields( $fields ) {

		/**
		 * TaxQuery $args
		 * @see: https://codex.wordpress.org/Class_Reference/WP_Query#Taxonomy_Parameters
		 * @since 0.0.1
		 */
		$fields['taxQuery'] = self::tax_query();

		return $fields;

	}

	/**
	 * map_input_fields
	 *
	 * This maps the taxQuery input fields to the WP_Query
	 *
	 * @param $query_args
	 * @param $input_args
	 *
	 * @return mixed
	 * @since 0.0.1
	 */
	public function map_input_fields( $query_args, $input_args ) {

		/**
		 * TaxQuery $args
		 * This maps the GraphQL taxQuery input to the WP_Query tax_query format
		 * @since 0.0.5
		 */
		$tax_query = null;
		if ( ! empty( $input_args['taxQuery'] ) ) {

			// Get the taxQuery input
			$tax_query = $input_args['taxQuery'];

			// If the taxArray was entered
			if ( ! empty( $tax_query['taxArray'] ) && is_array( $tax_query['taxArray'] ) ) {

				// If less than 2 taxArray objects were passed through, we don't need the "relation" field
				// to be passed to WP_Query, so we'll unset it now
				if ( 2 < count( $tax_query['taxArray'] ) ) {
					unset( $tax_query['relation'] );
				}

				// Loop through the taxArray
				foreach ( $tax_query['taxArray'] as $tax_array_key => $value ) {

					// If the "field" option was selected to be "term_id" or "term_taxonomy_id" we need to convert
					// the values of the "terms" array from strings to integers.
					if ( ! empty( $value['terms'] ) ) {
						if ( ! empty( $value['field'] ) && ( 'term_id' === $value['field'] || 'term_taxonomy_id' === $value['field'] ) ) {
							$formatted_terms = [];
							foreach ( $value['terms'] as $term ) {
								$formatted_terms = intval( $term );
							}
							$value['terms'] = $formatted_terms;
						}
					}

					$tax_query[] = [
						$tax_array_key => $value,
					];
				}
			}
			unset( $tax_query['taxArray'] );
		} // End if().

		if ( ! empty( $tax_query ) ) {
			$query_args['tax_query'] = $tax_query;
		}

		unset( $query_args['taxQuery'] );

		/**
		 * Retrun the $query_args
		 * @since 0.0.1
		 */
		return $query_args;

	}

	/**
	 * tax_query
	 * This returns the definition for the TaxQueryType
	 * @return TaxQueryType
	 * @since 0.0.1
	 */
	public static function tax_query() {
		return self::$tax_query ? : ( self::$tax_query = new TaxQueryType() );
	}

}

/**
 * Instantiate the TaxQuery class on graphql_init
 * @return TaxQuery
 */
function graphql_init_tax_query() {
	return new \WPGraphQL\TaxQuery();
}

add_action( 'graphql_generate_schema', '\WPGraphql\graphql_init_tax_query' );
