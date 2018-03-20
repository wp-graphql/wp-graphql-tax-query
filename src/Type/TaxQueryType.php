<?php
namespace WPGraphQL\TaxQuery\Type;

use WPGraphQL\Type\WPEnumType;
use WPGraphQL\Type\WPInputObjectType;
use WPGraphQL\Types;

class TaxQueryType extends WPInputObjectType {

	protected static $fields;
	protected static $tax_array;

	/**
	 * TaxQueryType constructor.
	 */
	public function __construct( $type_name ) {
		$config = [
			'name'        => $type_name . 'TaxQuery',
			'description' => __( 'Query objects based on taxonomy parameters', 'wp-graphql' ),
			'fields'      => self::fields( $type_name ),
		];
		parent::__construct( $config );
	}

	/**
	 * @return array|null
	 */
	protected static function fields( $type_name ) {

		if ( empty( self::$fields[ $type_name ] ) ) :

			self::$fields[ $type_name ] = [
				'relation' => [
					'type' => Types::relation_enum(),
				],
				'taxArray' => Types::list_of( self::tax_array( $type_name ) ),
			];

		endif;
		return ! empty( self::$fields[ $type_name ] ) ? self::$fields[ $type_name ] : null;
	}

	/**
	 * @return WPInputObjectType
	 */
	protected static function tax_array( $type_name ) {

		if ( empty( self::$tax_array[ $type_name ] ) ) {
			self::$tax_array[ $type_name ] = new WPInputObjectType( [
				'name'   => $type_name . 'TaxArray',
				'fields' => function() use ( $type_name ) {
					$fields = [
						'taxonomy'        => [
							'name' => 'taxonomy',
							'type' => Types::taxonomy_enum(),
						],
						'field'           => [
							'type' => new WPEnumType( [
								'name'        => $type_name . 'TaxQueryField',
								'description' => __( 'Which field to select taxonomy term by. Default value is "term_id"', 'wp-graphql' ),
								'values'      => [
									'ID'          => [
										'name'  => 'ID',
										'value' => 'term_id',
									],
									'NAME'        => [
										'name'  => 'NAME',
										'value' => 'name',
									],
									'SLUG'        => [
										'name'  => 'SLUG',
										'value' => 'slug',
									],
									'TAXONOMY_ID' => [
										'name'  => 'TAXONOMY_ID',
										'value' => 'term_taxonomy_id',
									],
								],
							] ),
						],
						'terms'           => [
							'type'        => Types::list_of( Types::string() ),
							'description' => __( 'A list of term slugs', 'wp-graphql' ),
						],
						'includeChildren' => [
							'type'        => Types::boolean(),
							'description' => __( 'Whether or not to include children for hierarchical 
											taxonomies. Defaults to false to improve performance (note that
											this is opposite of the default for WP_Query).', 'wp-graphql' ),
						],
						'operator'        => [
							'type' => new WPEnumType( [
								'name'   => $type_name . 'TaxQueryOperator',
								'values' => [
									'IN'         => [
										'name'  => 'IN',
										'value' => 'IN',
									],
									'NOT_IN'     => [
										'name'  => 'NOT_IN',
										'value' => 'NOT IN',
									],
									'AND'        => [
										'name'  => 'AND',
										'value' => 'AND',
									],
									'EXISTS'     => [
										'name'  => 'EXISTS',
										'value' => 'EXISTS',
									],
									'NOT_EXISTS' => [
										'name'  => 'NOT_EXISTS',
										'value' => 'NOT EXISTS',
									],
								],
							] ),
						],
					];

					return $fields;
				},
			] );
		}
		return ! empty( self::$tax_array[ $type_name ] ) ? self::$tax_array[ $type_name ] : null;

	}
}
