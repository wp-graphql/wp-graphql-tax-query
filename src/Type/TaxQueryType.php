<?php
namespace WPGraphQL\TaxQuery\Type;

use WPGraphQL\Type\WPEnumType;
use WPGraphQL\Type\WPInputObjectType;
use WPGraphQL\Types;

class TaxQueryType extends WPInputObjectType {

	protected static $fields;

	/**
	 * TaxQueryType constructor.
	 */
	public function __construct() {
		$config = [
			'name'        => 'taxQuery',
			'description' => __( 'Query objects based on taxonomy parameters', 'wp-graphql' ),
			'fields'      => self::fields(),
		];
		parent::__construct( $config );
	}

	/**
	 * @return array|null
	 */
	protected static function fields() {

		if ( null === self::$fields ) :

			self::$fields = [
				'relation' => [
					'type' => Types::relation_enum(),
				],
				'taxArray' => Types::list_of( self::tax_array() ),
			];

		endif;
		return ! empty( self::$fields ) ? self::$fields : null;
	}

	/**
	 * @return WPInputObjectType
	 */
	protected static function tax_array() {

		return new WPInputObjectType( [
			'name'   => 'taxArray',
			'fields' => function() {
				$fields = [
					'taxonomy'        => [
						'name' => 'taxonomy',
						'type' => Types::taxonomy_enum(),
					],
					'field'           => [
						'type' => new WPEnumType([
							'name' => 'taxQueryField',
							'description' => __( 'Which field to select taxonomy term by. Default value is "term_id"', 'wp-graphql' ),
							'values' => [
								[
									'name'  => 'ID',
									'value' => 'term_id',
								],
								[
									'name'  => 'NAME',
									'value' => 'name',
								],
								[
									'name'  => 'SLUG',
									'value' => 'slug',
								],
								[
									'name'  => 'TAXONOMY_ID',
									'value' => 'term_taxonomy_id',
								],
							],
						]),
					],
					'terms'           => [
						'type'        => Types::list_of( Types::string() ),
						'description' => __( 'A list of term slugs', 'wp-graphql' ),
					],
					'includeChildren' => [
						'type'        => Types::boolean(),
						'description' => __( 'Whether or not to include children for hierarchical 
										taxonomies. Defaults to true', 'wp-graphql' ),
					],
					'operator'        => [
						'type' => new WPEnumType([
							'name' => 'taxQueryOperator',
							'values' => [
								[
									'name'  => 'IN',
									'value' => 'IN',
								],
								[
									'name'  => 'NOT_IN',
									'value' => 'NOT IN',
								],
								[
									'name'  => 'AND',
									'value' => 'AND',
								],
								[
									'name'  => 'EXISTS',
									'value' => 'EXISTS',
								],
								[
									'name'  => 'NOT_EXISTS',
									'value' => 'NOT EXISTS',
								],
							],
						]),
					],
				];
				return $fields;
			},
		] );

	}
}
