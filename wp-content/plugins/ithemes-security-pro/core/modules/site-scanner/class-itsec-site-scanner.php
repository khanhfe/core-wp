<?php

class ITSEC_Site_Scanner {

	public function run() {
		add_action( 'rest_api_init', array( $this, 'register_routes' ) );
		add_action( 'itsec_register_highlighted_logs', array( $this, 'register_highlight' ) );
		add_action( 'itsec_site_scanner_scan_complete', array( $this, 'extract_vulnerabilities_from_scan' ), 10, 2 );
		add_action( 'admin_enqueue_scripts', array( $this, 'register_scripts' ) );
	}

	public function register_routes() {
		( new \iThemesSecurity\Site_Scanner\REST\Muted_Issues() )->register_routes();
		register_rest_route( 'ithemes-security/v1', 'site-scanner/verify-scan', [
			'methods'             => 'POST',
			'callback'            => [ $this, 'handle_verification_request' ],
			'permission_callback' => '__return_true',
			'args'                => [
				'secret' => [
					'type'      => 'string',
					'required'  => true,
					'minLength' => 64,
					'maxLength' => 64,
				],
			],
		] );
	}

	public function handle_verification_request( WP_REST_Request $request ) {
		require_once dirname( __FILE__ ) . '/api.php';

		$key_pair = ITSEC_Site_Scanner_API::get_key_pair();

		if ( ! $key_pair['secret'] || ! hash_equals( $key_pair['secret'], $request['secret'] ) ) {
			return new WP_Error( 'invalid_secret', __( 'Secret did not match', 'it-l10n-ithemes-security-pro' ), [ 'status' => WP_Http::OK ] );
		}

		return new WP_REST_Response( [ 'public' => $key_pair['public'] ] );
	}

	public function register_highlight() {
		ITSEC_Lib_Highlighted_Logs::register_dynamic_highlight( 'site-scanner-report', array(
			'module' => 'site-scanner',
			'type'   => 'critical-issue',
		) );
	}

	public function extract_vulnerabilities_from_scan( $scan, $site_id ) {
		if ( $site_id && ! is_main_site( $site_id ) ) {
			return; // Vulnerabilities aren't checked on sub site scans.
		}

		$results = $scan['response'];
		$cached  = $scan['cached'];

		if ( $cached || is_wp_error( $results ) ) {
			return;
		}

		$vulnerabilities = array();

		if ( ! empty( $results['entries']['vulnerabilities'] ) ) {
			foreach ( $results['entries']['vulnerabilities'] as $vulnerability ) {
				foreach ( $vulnerability['issues'] as $i => $issue ) {
					$vulnerability['issues'][ $i ] = array(
						'title'    => $issue['title'],
						'fixed_in' => $issue['fixed_in'],
					);
				}

				$vulnerabilities[] = $vulnerability;
			}
		}

		$existing = ITSEC_Modules::get_setting( 'site-scanner', 'vulnerabilities' );

		if ( $existing !== $vulnerabilities ) {
			ITSEC_Modules::set_setting( 'site-scanner', 'vulnerabilities', $vulnerabilities );

			/**
			 * Fires when the detected software vulnerabilities have changed.
			 *
			 * @param array $vulnerabilities The new vulnerabilities set.
			 * @param array $existing        The existing vulnerabilities.
			 */
			do_action( 'itsec_software_vulnerabilities_changed', $vulnerabilities, $existing );
		}
	}

	/**
	 * Registers scripts for the site scanner.
	 */
	public function register_scripts() {
		wp_register_script( 'itsec-site-scanner-scan-settings', plugins_url( 'js/scanner.js', __FILE__ ), array( 'jquery', 'wp-i18n', 'itsec-util' ), 1, true );
	}
}
