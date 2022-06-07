<?php

class ITSEC_Site_Scanner_Command extends WP_CLI_Command {

	/**
	 * Perform a scan.
	 *
	 * ## OPTIONS
	 *
	 * [<site_id>]
	 * : Optionally, scan a site other than the main site in a multisite setup.
	 *
	 * [--format=<format>]
	 * : Render output in a particular format.
	 * ---
	 * default: description
	 * options:
	 *   - code
	 *   - description
	 *   - json
	 * ---
	 */
	public function scan( $args, $assoc_args ) {

		if ( isset( $args[0] ) ) {
			$site_id = (int) $args[0];
		} else {
			$site_id = 0;
		}

		if ( $site_id && ! is_multisite() ) {
			WP_CLI::error( 'Specifying a site ID is only supported on multisite.' );
		}

		ITSEC_Modules::load_module_file( 'api.php', 'site-scanner' );
		ITSEC_Modules::load_module_file( 'util.php', 'site-scanner' );
		$results = ITSEC_Site_Scanner_API::scan( $site_id );

		if ( is_wp_error( $results ) ) {
			WP_CLI::error( $results );
		}

		$code = ITSEC_Site_Scanner_Util::get_scan_result_code( $results );

		switch ( \WP_CLI\Utils\get_flag_value( $assoc_args, 'format', 'description' ) ) {
			case 'code':
				if ( 'clean' === $code ) {
					WP_CLI::success( $code );
				} else {
					WP_CLI::warning( $code );
				}
				break;
			case 'description':
				$description = ITSEC_Site_Scanner_Util::get_scan_code_description( $code );

				if ( 'clean' === $code ) {
					WP_CLI::success( $description );
				} else {
					WP_CLI::warning( $description );
				}
				break;
			case 'json':
				WP_CLI::line( json_encode( $results ) );
				break;
			default:
				WP_CLI::error( 'Invalid format.' );
		}

	}
}

WP_CLI::add_command( 'itsec site-scanner', 'ITSEC_Site_Scanner_Command' );
