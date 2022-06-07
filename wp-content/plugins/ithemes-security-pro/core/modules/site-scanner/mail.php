<?php

class ITSEC_Site_Scanner_Mail {

	/**
	 * Sends a notification about the results of the scan.
	 *
	 * @param array $results
	 *
	 * @return bool
	 */
	public static function send( $results ) {
		$code = ITSEC_Site_Scanner_Util::get_scan_result_code( $results );

		if ( 'clean' === $code ) {
			return true;
		}

		$nc = ITSEC_Core::get_notification_center();

		$mail = $nc->mail();
		$mail->set_subject( static::get_scan_subject( $code ) );
		$mail->set_recipients( $nc->get_recipients( 'malware-scheduling' ) );

		$mail->add_header(
			esc_html__( 'Site Scan', 'it-l10n-ithemes-security-pro' ),
			sprintf(
				esc_html__( 'Site Scan for %s', 'it-l10n-ithemes-security-pro' ),
				'<b>' . ITSEC_Lib::date_format_i18n_and_local_timezone( time(), get_option( 'date_format' ) ) . '</b>'
			)
		);
		static::format_scan_body( $mail, $results );
		$mail->add_footer();

		return $nc->send( 'malware-scheduling', $mail );
	}

	/**
	 * Get the subject line for a site scan result.
	 *
	 * @param string $code
	 *
	 * @return string
	 */
	public static function get_scan_subject( $code ) {

		switch ( $code ) {
			case 'scan-failure-server-error':
			case 'scan-failure-client-error':
			case 'error':
				return esc_html__( 'Scheduled site scan resulted in an error', 'it-l10n-ithemes-security-pro' );
			case 'clean':
				return esc_html__( 'Scheduled site scan found no issues.', 'it-l10n-ithemes-security-pro' );
			default:
				require_once( dirname( __FILE__ ) . '/util.php' );

				if ( $codes = ITSEC_Site_Scanner_Util::translate_findings_code( $code ) ) {
					return wp_sprintf( esc_html__( 'Scheduled site scan report: %l', 'it-l10n-ithemes-security-pro' ), $codes );
				}

				return wp_sprintf( esc_html__( 'Scheduled site scan found warnings', 'it-l10n-ithemes-security-pro' ) );
		}
	}

	/**
	 * Format the scan results into the mail object.
	 *
	 * @param ITSEC_Mail     $mail
	 * @param array|WP_Error $results
	 */
	public static function format_scan_body( ITSEC_Mail $mail, $results ) {
		if ( is_wp_error( $results ) ) {
			$mail->add_list( array(
				/* translators: 1. Site name. */
				sprintf( esc_html__( 'An error occurred while running the scheduled site scan on %s:', 'it-l10n-ithemes-security-pro' ), get_bloginfo( 'name', 'display' ) ),
				sprintf( esc_html__( 'Error Message: %s', 'it-l10n-ithemes-security-pro' ), $results->get_error_message() ),
				sprintf( esc_html__( 'Error Code: %s', 'it-l10n-ithemes-security-pro' ), '<code>' . esc_html( $results->get_error_code() ) . '</code>' ),
			), true );

			return;
		}

		$mail->start_group( 'report' );

		$issues = 0;
		$issues += self::format_malware_details( $mail, $results );
		$issues += self::format_blacklist_details( $mail, $results );
		$issues += self::format_known_vulnerabilities_details( $mail, $results );
		$errors = self::format_error_details( $mail, $results );

		$mail->end_group();

		$lead = '';

		if ( $issues ) {
			$lead = sprintf( esc_html(
				_n(
					'The scheduled site scan found %1$d issue when scanning %2$s.',
					'The scheduled site scan found %1$d issues when scanning %2$s.',
					$issues,
					'it-l10n-ithemes-security-pro'
				)
			), number_format_i18n( $issues ), $results['url'] );
		}

		if ( $errors ) {
			if ( $lead ) {
				$lead .= ' ' . sprintf( esc_html(
						_n(
							'The scanner encountered %d additional error.',
							'The scanner encountered %d additional errors.',
							$errors,
							'it-l10n-ithemes-security-pro'
						)
					), number_format_i18n( $errors ) );
			} else {
				$lead = sprintf( esc_html(
					_n(
						'The scheduled site scan encountered %1$d error when scanning %2$s.',
						'The scheduled site scan encountered %1$d errors when scanning %2$s.',
						$errors,
						'it-l10n-ithemes-security-pro'
					)
				), number_format_i18n( $errors ), $results['url'] );
			}
		}

		$mail->insert_before( 'report', $mail->get_text( $lead ) );

		$mail->add_button(
			esc_html__( 'View Report', 'it-l10n-ithemes-security-pro' ),
			ITSEC_Mail::filter_admin_page_url( ITSEC_Core::get_logs_page_url( 'malware' ) )
		);
	}

	private static function format_malware_details( ITSEC_Mail $mail, $results ) {
		if ( empty( $results['entries']['malware'] ) ) {
			return 0;
		}

		$mail->add_section_heading( esc_html__( 'Malware Found', 'it-l10n-ithemes-security-pro' ) );
		$mail->add_list( array_map( 'esc_html', wp_list_pluck( $results['entries']['malware'], 'message' ) ) );

		return count( $results['entries']['malware'] );
	}

	private static function format_blacklist_details( ITSEC_Mail $mail, $results ) {
		$blacklisted = array();

		foreach ( $results['entries']['blacklist'] as $blacklist ) {
			if ( 'blacklisted' === $blacklist['status'] ) {
				$blacklisted[] = sprintf(
					esc_html__( 'Domain blacklisted by %1$s %2$s(details)%3$s', 'it-l10n-ithemes-security-pro' ),
					esc_html( $blacklist['vendor']['label'] ),
					'<a href="' . esc_url( $blacklist['report_details'] ) . '">',
					'</a>'
				);
			}
		}

		if ( ! $blacklisted ) {
			return 0;
		}

		$mail->add_section_heading( esc_html__( 'Site Blacklisted', 'it-l10n-ithemes-security-pro' ) );
		$mail->add_list( $blacklisted );

		return count( $blacklisted );
	}

	private static function format_known_vulnerabilities_details( ITSEC_Mail $mail, $results ) {
		if ( empty( $results['entries']['vulnerabilities'] ) ) {
			return 0;
		}

		$vulns = array();

		foreach ( $results['entries']['vulnerabilities'] as $vulnerability ) {
			foreach ( $vulnerability['issues'] as $issue ) {
				$vulns[] = '<a href="' . esc_url( $vulnerability['link'] ) . '">' . esc_html( $issue['title'] ) . '</a>';
			}
		}

		$mail->add_section_heading( esc_html__( 'Known Vulnerabilities', 'it-l10n-ithemes-security-pro' ) );
		$mail->add_list( $vulns );

		return count( $vulns );
	}

	private static function format_error_details( ITSEC_Mail $mail, $results ) {
		if ( empty( $results['errors'] ) ) {
			return 0;
		}

		$mail->add_section_heading( esc_html__( 'Scan Errors', 'it-l10n-ithemes-security-pro' ) );
		$mail->add_list( array_map( 'esc_html', wp_list_pluck( $results['errors'], 'message' ) ) );

		return count( $results['errors'] );
	}
}
