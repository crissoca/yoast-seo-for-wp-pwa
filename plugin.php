<?php

/**
 * Plugin Name: Yoast SEO for WordPress PWA
 * Description: Makes Yoast SEO settings available to WordPress PWA using the REST API.
 * Author: Pablo Postigo, Luis Herranz, Niels Garve, Tedy Warsitha, Charlie Francis
 * Version: 2.0-RC2
 * Plugin URI: https://github.com/wp-pwa/yoast-seo-for-wp-pwa
 */

if ( class_exists( 'Yoast\WP\SEO\Main' ) ) {

	class Yoast_To_REST_API {

		/**
		 * Instance of this class.
		 *
		 * @var object
		 */
		public static $instance;

		function __construct() {

			add_action( 'rest_api_init', [
				$this,
				'add_yoast_data'
			], 20 );

		}

		/**
		 * Get the singleton instance of this class.
		 *
		 * @return Yoast_To_REST_API
		 */
		public static function get_instance() {
			if ( ! ( self::$instance instanceof self ) ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		function add_yoast_data() {

			// Custom post types (including posts and pages):
			$types = get_post_types( [ 'public' => true ] );

			foreach ( $types as $key => $type ) {
				register_rest_field(
					$type,
					'yoast_meta',
					[
						'get_callback'    => [
							$this,
							'encode_post'
						],
						'update_callback' => null,
						'schema'          => null,
					]
				);
			}

			// Category and Tag
			register_rest_field( [
				'category',
				'tag',
				'user'
			],
				'yoast_meta',
				[
					'get_callback'    => [
						$this,
						'encode_archives'
					],
					'update_callback' => null,
					'schema'          => null,
				]
			);

		}


		public function encode_post( $post, $field_name, WP_REST_Request $request ) {

			$parameters = $request->get_query_params();

			if ( is_admin() ) {
				return false;
			}

			if ( true === isset( $parameters['per_page'] ) && 1 !== $parameters['per_page'] ) {
				return false;
			}

			$yst_obj = YoastSEO()->meta->for_post( $post['id'] );

			return [
				'title'     => html_entity_decode( $yst_obj->title, ENT_QUOTES, "UTF-8" ),
				'metadesc'  => html_entity_decode( $yst_obj->description, ENT_QUOTES, "UTF-8" ),
				'canonical' => $yst_obj->canonical,
				'locale'    => $yst_obj->open_graph_locale,
			];

		}

		public function encode_archives( $archive, $field_name, WP_REST_Request $request ) {

			$route      = $request->get_route();
			$parameters = $request->get_query_params();

			if ( is_admin() ) {
				return false;
			}

			if ( true === isset( $parameters['hide_empty'] ) ) {
				return false;
			}

			switch ( $route ) :

				case "/wp/v2/categories" :
				case "/wp/v2/tags" :

					$yst_obj = YoastSEO()->meta->for_term( $archive['id'] );

					return [
						'title'     => html_entity_decode( $yst_obj->title, ENT_QUOTES, "UTF-8" ),
						'metadesc'  => html_entity_decode( $yst_obj->description, ENT_QUOTES, "UTF-8" ),
						'canonical' => $yst_obj->canonical,
						'locale'    => $yst_obj->open_graph_locale,
					];

					break;

				case "/wp/v2/users" :

					$yst_obj = YoastSEO()->meta->for_author( $archive['id'] );

					return [
						'title'     => html_entity_decode( $yst_obj->title, ENT_QUOTES, "UTF-8" ),
						'metadesc'  => html_entity_decode( $yst_obj->description, ENT_QUOTES, "UTF-8" ),
						'canonical' => $yst_obj->canonical,
						'locale'    => $yst_obj->open_graph_locale,
					];

					break;

			endswitch;

			return false;

		}

	}
}

function WPAPIYoast_init() {

	if ( class_exists( 'Yoast\WP\SEO\Main' ) ) {

		Yoast_To_REST_API::get_instance();
	}
}

add_action( 'rest_api_init', 'WPAPIYoast_init' );
