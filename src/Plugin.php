<?php
/**
 * Pronamic WordPress Updater
 *
 * @package   PronamicWordPressUpdater
 * @author    Pronamic
 * @copyright 2023 Pronamic
 */

namespace Pronamic\WordPress\Updater;

/**
 * Pronamic WordPress Updater class
 */
class Plugin {
	/**
	 * Instance.
	 *
	 * @var self
	 */
	protected static $instance = null;

	/**
	 * Return instance of this class.
	 *
	 * @return self A single instance of this class.
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Setup.
	 * 
	 * @return void
	 */
	public function setup() {
		if ( \has_filter( 'http_response', [ $this, 'http_response' ] ) ) {
			return;
		}

		\add_filter( 'http_response', [ $this, 'http_response' ], 10, 3 );
	}

	/**
	 * HTTP Response.
	 *
	 * @link https://github.com/WordPress/WordPress/blob/5.5/wp-includes/class-http.php#L437-L446
	 * @param array  $response    HTTP response.
	 * @param array  $parsed_args HTTP request arguments.
	 * @param string $url         The request URL.
	 * @return array
	 */
	public function http_response( $response, $parsed_args, $url ) {
		if ( ! \array_key_exists( 'method', $parsed_args ) ) {
			return $response;
		}

		if ( 'POST' !== $parsed_args['method'] ) {
			return $response;
		}

		if ( \str_contains( $url, '//api.wordpress.org/plugins/update-check/' ) ) {
			$response = $this->extend_response_with_pronamic( $response, $parsed_args, 'plugins' );
		}

		if ( \str_contains( $url, '//api.wordpress.org/themes/update-check/' ) ) {
			$response = $this->extend_response_with_pronamic( $response, $parsed_args, 'themes' );
		}

		return $response;
	}

	/**
	 * Extends WordPress.org API repsonse with Pronamic API response.
	 *
	 * @param array  $response    HTTP response.
	 * @param array  $parsed_args HTTP request arguments.
	 * @param string $type        Type.
	 * @return array
	 */
	public function extend_response_with_pronamic( $response, $parsed_args, $type ) {
		$data = \json_decode( \wp_remote_retrieve_body( $response ), true );

		if ( ! is_array( $data ) ) {
			return $response;
		}

		$pronamic_data = false;

		switch ( $type ) {
			case 'plugins':
				$pronamic_data = $this->request_update_check( 'https://api.pronamic.eu/plugins/update-check/1.2/', $parsed_args );

				break;
			case 'themes':
				$pronamic_data = $this->request_update_check( 'https://api.pronamic.eu/themes/update-check/1.2/', $parsed_args );

				break;
		}

		if ( false === $pronamic_data ) {
			return $response;
		}

		if ( ! array_key_exists( $type, $data ) ) {
			$data[ $type ] = [];
		}

		if ( \is_array( $pronamic_data[ $type ] ) ) {
			$data[ $type ] = array_merge( $data[ $type ], $pronamic_data[ $type ] );
		}

		$response['body'] = \wp_json_encode( $data );

		return $response;
	}

	/**
	 * Request plugins update check.
	 *
	 * @param string $url         URL.
	 * @param array  $parsed_args HTTP request arguments.
	 * @return array
	 */
	private function request_update_check( $url, $parsed_args ) {
		$keys = [
			'body',
			'timeout',
			'user-agent',
			'headers',
		];

		$args = [];

		foreach ( $keys as $key ) {
			if ( \array_key_exists( $key, $parsed_args ) ) {
				$args[ $key ] = $parsed_args[ $key ];
			}
		}

		$response = \wp_remote_post( $url, $args );

		if ( \is_wp_error( $response ) || '200' !== (string) \wp_remote_retrieve_response_code( $response ) ) {
			return false;
		}

		$data = \json_decode( \wp_remote_retrieve_body( $response ), true );

		return $data;
	}
}
