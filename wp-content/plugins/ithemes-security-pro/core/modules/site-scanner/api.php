<?php

class ITSEC_Site_Scanner_API {

	const HOST = 'https://itsec-site-scanner.ithemes.com/';
	const ACCEPT = 'application/vnd.site-scanner.ithemes;v=1.0';

	/**
	 * Performs the site scan.
	 *
	 * @param int $site_id The site ID to scan. Accepts 0 to scan the main site in a multisite network.
	 *
	 * @return array|WP_Error
	 */
	public static function scan( $site_id = 0 ) {

		$pid = ITSEC_Log::add_process_start( 'site-scanner', 'scan', compact( 'site_id' ) );

		if ( $site_id && ! is_main_site( $site_id ) ) {
			$scan = self::scan_sub_site( $pid, $site_id );
		} else {
			$scan = self::scan_main_site( $pid );
		}

		/** @var array|WP_Error $results */
		$results = $scan['response'];
		$cached  = $scan['cached'];

		if ( self::is_temporary_server_error( $results ) ) {
			$results->add( 'itsec-temporary-server-error', __( 'Site Scanning is temporarily unavailable, please try again later.' ) );
		}

		ITSEC_Log::add_process_stop( $pid, compact( 'results', 'cached' ) );

		/**
		 * Fires after a site scan has completed.
		 *
		 * @param array $scan
		 * @param int   $site_id
		 */
		do_action( 'itsec_site_scanner_scan_complete', $scan, $site_id );

		if ( $cached ) {
			return $results;
		}

		require_once( dirname( __FILE__ ) . '/util.php' );
		$code = ITSEC_Site_Scanner_Util::get_scan_result_code( $results );

		if ( is_wp_error( $results ) ) {
			ITSEC_Log::add_warning( 'site-scanner', $code, compact( 'results', 'cached' ) );

			return $results;
		}

		if ( 'error' === $code ) {
			ITSEC_Log::add_warning( 'site-scanner', $code, compact( 'results', 'cached' ) );
		} elseif ( 'clean' === $code ) {
			ITSEC_Log::add_notice( 'site-scanner', $code, compact( 'results', 'cached' ) );
		} else {
			ITSEC_Log::add_critical_issue( 'site-scanner', $code, compact( 'results', 'cached' ) );
		}

		return $results;
	}

	/**
	 * Scan the main site.
	 *
	 * @param array $pid
	 *
	 * @return array
	 */
	private static function scan_main_site( array $pid ) {

		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$body = array();

		if ( ITSEC_Core::is_licensed() ) {
			$plugins = $themes = array();

			list( $wp_version ) = explode( '-', $GLOBALS['wp_version'] );

			foreach ( get_plugins() as $file => $data ) {
				if ( ! empty( $data['Version'] ) ) {
					$plugins[ dirname( $file ) ] = $data['Version'];
				}
			}

			foreach ( wp_get_themes() as $theme ) {
				$themes[ $theme->get_stylesheet() ] = $theme->get( 'Version' );
			}

			$body['wordpress']   = $wp_version;
			$body['plugins']     = $plugins;
			$body['themes']      = $themes;
			$body['mutedIssues'] = wp_list_pluck( ITSEC_Site_Scanner_Util::get_muted_issues(), 'id' );
		} else {
			$body['scan'] = array(
				'url'          => self::clean_url( network_home_url() ),
				'keyPair'      => self::generate_key_pair(),
				'verifyTarget' => rest_url( 'ithemes-security/v1/site-scanner/verify-scan' ),
			);
		}

		return self::make_request( 'api/scan', 'POST', $body, $pid );
	}

	/**
	 * Scan a sub site.
	 *
	 * @param array $pid     The process id for logging.
	 * @param int   $site_id The site ID to scan.
	 *
	 * @return array
	 */
	private static function scan_sub_site( array $pid, $site_id ) {
		return self::make_request( 'api/scan', 'POST', array(
			'scan' => array(
				'url'          => self::clean_url( get_home_url( $site_id ) ),
				'keyPair'      => self::generate_key_pair(),
				'verifyTarget' => get_rest_url( $site_id, 'ithemes-security/v1/site-scanner/verify-scan' ),
			)
		), $pid );
	}

	/**
	 * Make a request to the site scanner API.
	 *
	 * @param string $route  Route to call.
	 * @param string $method HTTP method to use.
	 * @param array  $body   Data to be encoded as json.
	 * @param array  $pid    Process ID to continue making log updates.
	 *
	 * @return array Array of response and cache status.
	 */
	private static function make_request( $route, $method, array $body, array $pid = null ) {
		$json      = wp_json_encode( $body );
		$headers   = array(
			'Content-Type' => 'application/json',
			'Accept'       => self::ACCEPT,
		);
		$signature = self::generate_signature( $json );

		if ( is_wp_error( $signature ) ) {
			if ( $signature->get_error_code() !== 'non_active_license' ) {
				return array(
					'cached'   => false,
					'response' => $signature,
				);
			}
		} else {
			$headers['Authorization'] = $signature;
		}

		if ( $pid ) {
			ITSEC_Log::add_process_update( $pid, compact( 'route', 'method', 'body', 'signature' ) );
		}

		$cache_key = self::build_cache_key( $route, $method, $body );
		$cached    = true;

		if ( ( $parsed = get_site_transient( $cache_key ) ) === false ) {
			$cached   = false;
			$response = self::call_api( $route, array(), array(
				'body'    => $json,
				'method'  => $method,
				'timeout' => 300,
				'headers' => $headers,
			) );

			if ( is_wp_error( $response ) ) {
				return compact( 'cached', 'response' );
			}

			$parsed = self::parse_response( $response );
			self::maybe_cache( $pid, $cache_key, $response, $parsed );
		}

		return array( 'cached' => $cached, 'response' => $parsed );
	}

	/**
	 * Sign the given request data.
	 *
	 * @param string $json Request body to sign.
	 *
	 * @return string|WP_Error
	 */
	private static function generate_signature( $json ) {
		if ( ! ITSEC_Core::is_pro() ) {
			return new WP_Error( 'non_active_license', __( 'Not an iThemes Security Pro install.', 'it-l10n-ithemes-security-pro' ) );
		}

		if ( ! isset( $GLOBALS['ithemes_updater_path'] ) ) {
			return new WP_Error( 'updater_not_available', __( 'Could not find the iThemes updater.', 'it-l10n-ithemes-security-pro' ) );
		}

		require_once( $GLOBALS['ithemes_updater_path'] . '/keys.php' );
		require_once( $GLOBALS['ithemes_updater_path'] . '/packages.php' );

		$keys = Ithemes_Updater_Keys::get( array( 'ithemes-security-pro' ) );

		if ( empty( $keys['ithemes-security-pro'] ) ) {
			return new WP_Error( 'non_active_license', __( 'iThemes Security Pro is not activated.', 'it-l10n-ithemes-security-pro' ) );
		}

		$signature = hash_hmac( 'sha1', $json, $keys['ithemes-security-pro'] );

		if ( ! $signature ) {
			return new WP_Error( 'hmac_failed', __( 'Failed to calculate hmac.', 'it-l10n-ithemes-security-pro' ) );
		}

		$package_details = Ithemes_Updater_Packages::get_full_details();

		if ( empty( $package_details['packages']['ithemes-security-pro/ithemes-security-pro.php']['user'] ) ) {
			return new WP_Error( 'non_active_license', __( 'iThemes Security Pro is not activated.', 'it-l10n-ithemes-security-pro' ) );
		}

		$user = $package_details['packages']['ithemes-security-pro/ithemes-security-pro.php']['user'];

		return sprintf( 'X-KeySignature signature="%s" username="%s" site="%s"', $signature, $user, self::clean_url( network_home_url() ) );
	}

	/**
	 * Cleans a URL.
	 *
	 * @param string $url
	 *
	 * @return string
	 */
	private static function clean_url( $url ) {
		return preg_replace( '|/$|', '', $url );
	}

	/**
	 * Parse a response into a WP_Error or the result.
	 *
	 * @param array $response WP_Http response.
	 *
	 * @return mixed|null|WP_Error
	 */
	private static function parse_response( $response ) {
		$parsed = self::parse_response_body( $response );
		$code   = wp_remote_retrieve_response_code( $response );

		if ( $code >= 400 ) {
			if ( is_wp_error( $parsed ) ) {
				return $parsed;
			}

			if ( ! is_array( $parsed ) ) {
				return new WP_Error( 'invalid_json', __( 'Invalid JSON.', 'it-l10n-ithemes-security-pro' ), wp_remote_retrieve_body( $response ) );
			}

			return new WP_Error(
				isset( $parsed['code'] ) ? $parsed['code'] : 'unknown_error',
				isset( $parsed['message'] ) ? $parsed['message'] : __( 'Unknown Error', 'it-l10n-ithemes-security-pro' ),
				isset( $parsed['data'] ) ? $parsed['data'] : array()
			);
		}

		return $parsed;
	}

	/**
	 * Parse the response body out of the response object.
	 *
	 * @param $response
	 *
	 * @return mixed|null|WP_Error
	 */
	private static function parse_response_body( $response ) {
		$body         = wp_remote_retrieve_body( $response );
		$code         = wp_remote_retrieve_response_code( $response );
		$content_type = wp_remote_retrieve_header( $response, 'content-type' );

		if ( 204 === $code ) {
			return null;
		}

		if ( ! $body ) {
			return new WP_Error( 'empty_response_body', __( 'Empty response body.', 'it-l10n-ithemes-security-pro' ) );
		}

		if ( 'application/json' === $content_type ) {
			$decoded = json_decode( $body, true );

			if ( json_last_error() !== JSON_ERROR_NONE ) {
				return new WP_Error( 'invalid_json', sprintf( __( 'Invalid JSON: %s.', 'it-l10n-ithemes-security-pro' ), json_last_error_msg() ) );
			}

			return $decoded;
		}

		return $body;
	}

	/**
	 * Builds the cache key based on the selected route.
	 *
	 * @param string $route
	 * @param string $method
	 * @param array  $body
	 *
	 * @return string
	 */
	private static function build_cache_key( $route, $method, array $body ) {
		switch ( $route ) {
			case 'api/scan':
				unset( $body['scan']['keyPair'] );
				break;
		}

		return 'itsec-site-scanner-' . md5( $route . $method . wp_json_encode( $body ) );
	}

	/**
	 * Maybe cache the response if the cache control allows it.
	 *
	 * @param array  $pid
	 * @param string $cache_key
	 * @param array  $response
	 * @param array  $cache
	 */
	private static function maybe_cache( $pid, $cache_key, $response, $cache ) {
		$cache_control = wp_remote_retrieve_header( $response, 'cache-control' );

		if ( ! $cache_control ) {
			return;
		}

		$keywords = array_map( 'trim', explode( ',', $cache_control ) );

		$mapped = array();

		foreach ( $keywords as $keyword ) {
			if ( false === strpos( $keyword, '=' ) ) {
				$mapped[ $keyword ] = true;
			} else {
				list( $key, $value ) = explode( '=', $keyword, 2 );
				$mapped[ $key ] = $value;
			}
		}

		if ( isset( $mapped['max-age'] ) ) {
			$cached = set_site_transient( $cache_key, $cache, (int) $mapped['max-age'] );

			if ( $cached ) {
				ITSEC_Log::add_process_update( $pid, array( 'action' => 'caching-response', 'mapped' => $mapped, 'cache_key' => $cache_key ) );
			} else {
				ITSEC_Log::add_process_update( $pid, array( 'action' => 'caching-response-failed', 'mapped' => $mapped ) );
			}
		}
	}

	/**
	 * Call the API.
	 *
	 * @param string $route Route to call.
	 * @param array  $query Query Args.
	 * @param array  $args  Arguments to pass to {@see wp_remote_request()}.
	 *
	 * @return array|WP_Error
	 */
	private static function call_api( $route, $query, $args ) {
		$url = self::HOST . $route;

		if ( $query ) {
			$url = add_query_arg( $query, $url );
		}

		$url  = apply_filters( 'itsec_site_scanner_api_request_url', $url, $route, $query, $args );
		$args = apply_filters( 'itsec_site_scanner_api_request_args', $args, $url, $route, $query );

		return wp_remote_request( $url, $args );
	}

	/**
	 * Generate a public secret key pair for a sub-site site scan.
	 *
	 * @return array
	 */
	public static function generate_key_pair() {
		$public = wp_generate_password( 64, false );
		$secret = wp_generate_password( 64, false );

		ITSEC_Modules::set_setting( 'site-scanner', 'public_key', $public );
		ITSEC_Modules::set_setting( 'site-scanner', 'secret_key', $secret );
		ITSEC_Storage::save();

		return compact( 'public', 'secret' );
	}

	/**
	 * Clear the key pair.
	 */
	public static function clear_key_pair() {
		ITSEC_Modules::set_setting( 'site-scanner', 'public_key', '' );
		ITSEC_Modules::set_setting( 'site-scanner', 'secret_key', '' );
	}

	/**
	 * Get the key pair.
	 *
	 * @return array
	 */
	public static function get_key_pair() {
		return array(
			'public' => ITSEC_Modules::get_setting( 'site-scanner', 'public_key' ),
			'secret' => ITSEC_Modules::get_setting( 'site-scanner', 'secret_key' ),
		);
	}

	/**
	 * Check if this is a temporary server error, in which case we should retry the scan at a later point in time,
	 * or if this is an issue with the client that needs to be fixed.
	 *
	 * @param array|WP_Error $results The parsed results from the scan.
	 *
	 * @return bool
	 */
	private static function is_temporary_server_error( $results ) {
		if ( ! is_wp_error( $results ) ) {
			return false;
		}

		$code = $results->get_error_code();

		if ( 'http_request_failed' === $code && strpos( $results->get_error_message(), 'cURL error 52:' ) !== false ) {
			return true;
		}

		$codes = [
			'empty_response_body',
			'invalid_json',
			'internal_server_error',
		];

		return in_array( $code, $codes, true );
	}
}
