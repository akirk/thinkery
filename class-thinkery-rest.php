<?php
/**
 * Thinkery REST
 *
 * This contains the functions for REST.
 *
 * @package Thinkery
 */

/**
 * This is the class for the REST part of the Thinkery Plugin.
 *
 * @since 0.6
 *
 * @package Thinkery
 * @author Alex Kirk
 */
class Thinkery_Rest {
	const PREFIX = 'thinkery/v1';
	/**
	 * Contains a reference to the Thinkery class.
	 *
	 * @var Thinkery
	 */
	private $thinkery;

	/**
	 * Constructor
	 *
	 * @param Thinkery $thinkery A reference to the Thinkery object.
	 */
	public function __construct( Thinkery $thinkery ) {
		$this->thinkery = $thinkery;
		$this->register_hooks();
	}

	/**
	 * Register the WordPress hooks
	 */
	private function register_hooks() {
		add_action( 'rest_api_init', array( $this, 'add_rest_routes' ) );
	}

	/**
	 * Add the REST API to send and receive friend requests
	 */
	public function add_rest_routes() {
		register_rest_route(
			self::PREFIX,
			'test',
			array(
				'methods'  => 'GET',
				'callback' => array( $this, 'rest_test' ),
			)
		);
	}

	/**
	 * Test
	 *
	 * @param  WP_REST_Request $request The incoming request.
	 * @return array The array to be returned via the REST API.
	 */
	public function rest_test( WP_REST_Request $request ) {
		$current_user = wp_get_current_user();

		return array(
			'user' => $current_user->user_login,
		);
	}
}
