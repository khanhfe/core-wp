<?php

class ITSEC_Site_Scanner_Util {

	const GRANT = 'itsec-site-scanner-muted-issues';

	/**
	 * Get the log code for a scan result.
	 *
	 * @param array|WP_Error $results
	 *
	 * @return string
	 */
	public static function get_scan_result_code( $results ) {
		if ( is_wp_error( $results ) ) {
			if ( 'internal_server_error' === $results->get_error_code() ) {
				return 'scan-failure-server-error';
			}
			if (
				'http_request_failed' === $results->get_error_code() &&
				false !== strpos( $results->get_error_message(), 'cURL error 52:' )
			) {
				return 'scan-failure-server-error';
			}

			return 'scan-failure-client-error';
		}

		$codes = array();

		if ( ! empty( $results['entries']['malware'] ) ) {
			$codes[] = 'found-malware';
		}

		if ( isset( $results['entries']['blacklist'] ) ) {
			foreach ( $results['entries']['blacklist'] as $blacklist ) {
				if ( 'blacklisted' === $blacklist['status'] ) {
					$codes[] = 'on-blacklist';
					break;
				}
			}
		}

		if ( ! empty( $results['entries']['vulnerabilities'] ) ) {
			$codes[] = 'vulnerable-software';
		}

		if ( $codes ) {
			if ( ! empty( $results['errors'] ) ) {
				$codes[] = 'has-error';
			}

			return implode( '--', $codes );
		}

		if ( ! empty( $results['errors'] ) ) {
			return 'error';
		}

		return 'clean';
	}

	public static function get_scan_code_description( $code ) {
		switch ( $code ) {
			case 'scan-failure-server-error':
			case 'scan-failure-client-error':
			case 'error':
				return esc_html__( 'Scan Error', 'it-l10n-ithemes-security-pro' );
			case 'clean':
				return esc_html__( 'Clean', 'it-l10n-ithemes-security-pro' );
			default:
				return wp_sprintf( '%l', self::translate_findings_code( $code ) );
		}
	}

	public static function translate_findings_code( $code ) {
		$part_labels = array();

		if ( is_string( $code ) ) {
			$parts = explode( '--', $code );
		} else {
			$parts = $code;
		}

		foreach ( $parts as $part ) {
			switch ( $part ) {
				case 'found-malware':
					$part_labels[] = esc_html__( 'Found Malware', 'it-l10n-ithemes-security-pro' );
					break;
				case 'on-blacklist':
					$part_labels[] = esc_html__( 'On Blacklist', 'it-l10n-ithemes-security-pro' );
					break;
				case 'vulnerable-software':
					$part_labels[] = esc_html__( 'Vulnerable Software', 'it-l10n-ithemes-security-pro' );
					break;
				case 'has-error':
					$part_labels[] = esc_html__( 'Scan Error', 'it-l10n-ithemes-security-pro' );
					break;
				default:
					$part_labels[] = $part;
					break;
			}
		}

		return $part_labels;
	}

	/**
	 * Generates a muted issues auth token.
	 *
	 * @param WP_User|null $user
	 *
	 * @return string|WP_Error
	 */
	public static function generate_muted_issues_auth_token( WP_User $user = null ) {
		$user = $user ?: wp_get_current_user();

		$payload = [
			'nbf'   => ITSEC_Core::get_current_time_gmt(),
			'iat'   => ITSEC_Core::get_current_time_gmt(),
			'exp'   => ITSEC_Core::get_current_time_gmt() + WEEK_IN_SECONDS,
			'grant' => self::GRANT,
			'user'  => $user->ID,
		];

		return ITSEC_Lib_JWT::encode( $payload, wp_salt() );
	}

	/**
	 * Validates the muted issues auth token.
	 *
	 * @param string $jwt The JWT
	 *
	 * @return WP_User|WP_Error
	 */
	public static function validate_muted_issues_auth_token( $jwt ) {
		$decoded = ITSEC_Lib_JWT::decode( $jwt, wp_salt(), [ 'HS256' ] );

		if ( is_wp_error( $decoded ) ) {
			return $decoded;
		}

		if ( ! isset( $decoded->grant ) ) {
			return new WP_Error( 'itsec_site_scanner_muted_auth_missing_grant', __( 'Malformed token.', 'it-l10n-ithemes-security-pro' ) );
		}

		if ( $decoded->grant !== self::GRANT ) {
			return new WP_Error( 'itsec_site_scanner_muted_auth_invalid_grant', __( 'Malformed token.', 'it-l10n-ithemes-security-pro' ) );
		}

		if ( empty( $decoded->user ) ) {
			return new WP_Error( 'itsec_site_scanner_muted_auth_missing_user', __( 'Malformed token.', 'it-l10n-ithemes-security-pro' ) );
		}

		$user = get_userdata( $decoded->user );

		if ( ! $user instanceof WP_User ) {
			return new WP_Error( 'itsec_site_scanner_muted_auth_invalid_user', __( 'Malformed token.', 'it-l10n-ithemes-security-pro' ) );
		}

		return $user;
	}

	/**
	 * Adds a URL parameter to give authentication for muted issues.
	 *
	 * @param string  $link
	 * @param WP_User $user
	 *
	 * @return string
	 */
	public static function authenticate_vulnerability_link( $link, WP_User $user = null ) {
		$user = $user ?: wp_get_current_user();

		if ( user_can( $user, ITSEC_Core::get_required_cap() ) ) {
			$token = self::generate_muted_issues_auth_token( $user );

			if ( ! is_wp_error( $token ) ) {
				$link = add_query_arg( 'token', rawurlencode( $token ), $link );
			}
		}

		return $link;
	}

	/**
	 * Mutes an issue.
	 *
	 * @param string $issue_id
	 * @param array  $args
	 *
	 * @return array|WP_Error
	 */
	public static function mute_issue( $issue_id, array $args = [] ) {
		if ( static::is_issue_muted( $issue_id ) ) {
			return new \WP_Error( 'itsec_site_scanner_issue_already_muted', __( 'This issue has already been muted.', 'it-l10n-ithemes-security-pro' ), array( 'status' => \WP_Http::BAD_REQUEST ) );
		}

		$issue    = array_merge( [
			'id'       => $issue_id,
			'muted_by' => get_current_user_id(),
			'muted_at' => ITSEC_Core::get_current_time_gmt(),
		], $args );
		$issues   = static::get_muted_issues();
		$issues[] = $issue;

		$updated = ITSEC_Modules::set_setting( 'site-scanner', 'muted_issues', $issues );
		$error   = ITSEC_Lib::updated_settings_to_wp_error( $updated );

		if ( is_wp_error( $error ) ) {
			return $error;
		}

		return $issue;
	}

	/**
	 * Is the given issue muted.
	 *
	 * @param string $issue_id
	 *
	 * @return bool
	 */
	public static function is_issue_muted( $issue_id ) {
		return (bool) static::get_muted_issue( $issue_id );
	}

	/**
	 * Unmute an issue.
	 *
	 * @param string $issue_id
	 *
	 * @return bool|WP_Error
	 */
	public static function unmute_issue( $issue_id ) {
		if ( ! static::is_issue_muted( $issue_id ) ) {
			return new \WP_Error( 'itsec_site_scanner_issue_not_muted', __( 'This issue is not muted.', 'it-l10n-ithemes-security-pro' ), array( 'status' => \WP_Http::BAD_REQUEST ) );
		}

		$issues = static::get_muted_issues();
		$issues = wp_list_filter( $issues, [ 'id' => $issue_id ], 'NOT' );

		$updated = ITSEC_Modules::set_setting( 'site-scanner', 'muted_issues', $issues );
		$error   = ITSEC_Lib::updated_settings_to_wp_error( $updated );

		return is_wp_error( $error ) ? $error : true;
	}

	/**
	 * Get a muted issue.
	 *
	 * @param string $issue_id
	 *
	 * @return array|null
	 */
	public static function get_muted_issue( $issue_id ) {
		foreach ( static::get_muted_issues() as $issue ) {
			if ( $issue['id'] === $issue_id ) {
				return $issue;
			}
		}

		return null;
	}

	/**
	 * Gets a list of all the muted issues.
	 *
	 * @return array[]
	 */
	public static function get_muted_issues() {
		return ITSEC_Modules::get_setting( 'site-scanner', 'muted_issues', [] );
	}
}
