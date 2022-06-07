<?php

class ITSEC_Site_Scanner_Template {

	private static $instance_id = 0;

	public static function get_html( $results, $show_error_details = true ) {

		$html = '<div class="itsec-site-scan-results">';

		if ( ! is_wp_error( $results ) && self::show_site_url( $results ) ) {
			$html .= '<h4>' . sprintf( esc_html__( 'Site: %s', 'it-l10n-ithemes-security-pro' ), $results['url'] ) . '</h4>';
		}

		if ( is_wp_error( $results ) ) {
			$html .= self::render_wp_error_details( $results, $show_error_details );
		} else {
			$html .= self::render_system_error_details( $results );
			$html .= self::render_known_vulnerabilities( $results );
			$html .= self::render_malware_details( $results );
			$html .= self::render_blacklist_details( $results );
		}

		$html .= '</div>';

		return $html;
	}

	/**
	 * Render details for the blacklist.
	 *
	 * @param array $results
	 *
	 * @return string
	 */
	private static function render_blacklist_details( $results ) {
		$blacklisted = $results['entries']['blacklist'];
		usort( $blacklisted, array( __CLASS__, '_sort_blacklist' ) );

		$children = '';

		foreach ( $blacklisted as $blacklist ) {
			$status   = $blacklist['status'] === 'blacklisted' ? 'warn' : 'clean';
			$children .= '<li class="itsec-site-scan__detail itsec-site-scan__detail--' . $status . '"><span>';
			$children .= '<a href="' . esc_url( $blacklist['report_details'] ) . '">';

			if ( 'blacklisted' === $blacklist['status'] ) {
				$children .= sprintf( esc_html__( 'Domain blacklisted by %s', 'it-l10n-ithemes-security-pro' ), esc_html( $blacklist['vendor']['label'] ) );
			} else {
				$children .= sprintf( esc_html__( 'Domain clean by %s', 'it-l10n-ithemes-security-pro' ), esc_html( $blacklist['vendor']['label'] ) );
			}

			$children .= '</a>';
			$children .= '</span></li>';
		}

		return self::render_wrapped_section( array(
			'type'        => 'blacklist',
			'status'      => ( isset( $blacklisted[0] ) && $blacklisted[0]['status'] ) === 'blacklisted' ? 'warn' : 'clean',
			'description' => esc_html__( 'Blacklist', 'it-l10n-ithemes-security-pro' ),
			'children'    => $children,
		) );
	}

	/**
	 * Render details for found malware.
	 *
	 * @param array $results
	 *
	 * @return string
	 */
	private static function render_malware_details( $results ) {
		if ( ! isset( $results['entries']['malware'] ) ) {
			return '';
		}

		if ( ! $results['entries']['malware'] ) {
			return self::render_wrapped_section( array(
				'type'        => 'malware',
				'status'      => 'clean',
				'description' => esc_html__( 'Malware', 'it-l10n-ithemes-security-pro' ),
			) );
		}

		$children = '';

		foreach ( $results['entries']['malware'] as $malware ) {
			$children .= '<li class="itsec-site-scan__detail itsec-site-scan__detail--warn"><span>';
			$children .= esc_html( $malware['message'] );

			if ( ! empty( $malware['location'] ) ) {
				$children .= '<br>';
				$children .= esc_html__( 'Infected URL:', 'it-l10n-ithemes-security-pro' );
				$children .= '<a href="' . esc_url( $malware['location'] ) . '" target="_blank" rel="noopener noreferrer">';
				$children .= esc_html( $malware['location'] );
				$children .= '</a>';
			}

			if ( 'other' !== $malware['type']['slug'] ) {
				$children .= '<br>';
				$children .= sprintf( esc_html__( 'Type: %s', 'it-l10n-ithemes-security-pro' ), esc_html( $malware['type']['label'] ) );

				if ( ! empty( $malware['documentation'] ) ) {
					$children .= '<br>';
					$children .= esc_html__( 'Documentation:', 'it-l10n-ithemes-security-pro' );
					$children .= '<a href="' . esc_url( $malware['documentation'] ) . '" rel="noopener noreferrer">';
					$children .= esc_html( $malware['documentation'] );
					$children .= '</a>';
				}
			}

			if ( ! empty( $malware['payload'] ) ) {
				$children .= '<br>';
				$children .= esc_html__( 'Payload:', 'it-l10n-ithemes-security-pro' );
				$children .= '<pre>' . esc_html( $malware['payload'] ) . '</pre>';
			}

			$children .= '</span></li>';
		}

		return self::render_wrapped_section( array(
			'type'        => 'malware',
			'status'      => 'warn',
			'description' => esc_html__( 'Malware', 'it-l10n-ithemes-security-pro' ),
			'children'    => $children,
		) );
	}

	/**
	 * Render details for known vulnerabilities.
	 *
	 * @param array $results
	 *
	 * @return string
	 */
	private static function render_known_vulnerabilities( $results ) {
		if ( ! isset( $results['entries']['vulnerabilities'] ) ) {
			return '';
		}

		if ( ! $results['entries']['vulnerabilities'] ) {
			return self::render_wrapped_section( array(
				'type'        => 'vulnerabilities',
				'status'      => 'clean',
				'description' => esc_html__( 'Known Vulnerabilities', 'it-l10n-ithemes-security-pro' ),
			) );
		}

		$children = '';

		foreach ( $results['entries']['vulnerabilities'] as $vulnerability ) {
			$link = ITSEC_Site_Scanner_Util::authenticate_vulnerability_link( $vulnerability['link'] );

			foreach ( $vulnerability['issues'] as $issue ) {
				$children .= '<li class="itsec-site-scan__detail itsec-site-scan__detail--warn"><span>';
				$children .= '<a href="' . esc_url( $link ) . '">';
				$children .= esc_html( $issue['title'] );
				$children .= '</a>';
				$children .= '</span></li>';
			}
		}

		return self::render_wrapped_section( array(
			'type'        => 'vulnerabilities',
			'status'      => 'warn',
			'description' => esc_html__( 'Known Vulnerabilities', 'it-l10n-ithemes-security-pro' ),
			'children'    => $children,
		) );
	}

	/**
	 * Render details for a system error.
	 *
	 * @param array $results
	 *
	 * @return string
	 */
	private static function render_system_error_details( $results ) {
		if ( empty( $results['errors'] ) ) {
			return '';
		}

		$children = '';

		foreach ( $results['errors'] as $error ) {
			$children .= '<li class="itsec-site-scan__detail itsec-site-scan__detail--error"><span>' . esc_html( $error['message'] ) . '</span></li>';
		}

		return self::render_wrapped_section( array(
			'children'    => $children,
			'type'        => 'system-error',
			'status'      => 'error',
			'description' => esc_html__( 'The scan failed to properly scan the site.', 'it-l10n-ithemes-security-pro' ),
		) );
	}

	/**
	 * Render details for a WP_Error.
	 *
	 * @param WP_Error $results
	 * @param bool     $show_error_details
	 *
	 * @return string
	 */
	private static function render_wp_error_details( $results, $show_error_details ) {
		$html = '<p>' . sprintf( esc_html__( 'Error Message: %s', 'it-l10n-ithemes-security-pro' ), $results->get_error_message() ) . '</p>';
		$html .= '<p>' . sprintf( esc_html__( 'Error Code: %s', 'it-l10n-ithemes-security-pro' ), $results->get_error_code() ) . '</p>';

		if ( $show_error_details && $results->get_error_data() ) {
			$html .= '<p>' . esc_html__( 'If you contact support about this error, please provide the following debug details:', 'it-l10n-ithemes-security-pro' ) . '</p>';
			$html .= ITSEC_Debug::print_r( array(
				'code' => $results->get_error_code(),
				'data' => $results->get_error_data(),
			), [], false );
		}

		return self::render_wrapped_section( array(
			'children'    => $html,
			'type'        => 'wp-error',
			'status'      => 'error',
			'description' => esc_html__( 'The scan failed to properly scan the site.', 'it-l10n-ithemes-security-pro' ),
		) );
	}

	/**
	 * Render wrapped section HTML.
	 *
	 * @param array $args
	 *
	 * @return string
	 */
	private static function render_wrapped_section( $args ) {
		$i_id = self::$instance_id ++;

		switch ( $args['status'] ) {
			case 'clean':
				$status_text = __( 'Clean', 'it-l10n-ithemes-security-pro' );
				break;
			case 'warn':
				$status_text = __( 'Warn', 'it-l10n-ithemes-security-pro' );
				break;
			case 'error':
				$status_text = __( 'Error', 'it-l10n-ithemes-security-pro' );
				break;
			default:
				$status_text = $args['status'];
				break;
		}

		$status_el = '<span class="itsec-site-scan__status itsec-site-scan__status--' . esc_attr( $args['status'] ) . '">' . $status_text . '</span>';

		$html = '<div class="itsec-site-scan-results-section itsec-site-scan-results-' . esc_attr( $args['type'] ) . '-section">';

		if ( empty( $args['children'] ) ) {
			$html .= '<p>' . $status_el . ' ' . esc_html( $args['description'] ) . '</p>';
		} else {
			$html .= '<p>';
			$html .= $status_el;
			$html .= esc_html( $args['description'] );

			$id = 'itsec-site-scan__details--' . $i_id;

			$html .= '<button type="button" class="itsec-site-scan-toggle-details button-link" aria-expanded="false" aria-controls="' . esc_attr( $id ) . '">';
			$html .= esc_html__( 'Show Details', 'it-l10n-ithemes-security-pro' );
			$html .= '</button>';
			$html .= '</p>';

			$html .= '<div class="itsec-site-scan__details hidden" id="' . esc_attr( $id ) . '">';
			$html .= '<ul>';
			$html .= $args['children'];
			$html .= '</ul>';
			$html .= '</div>';
		}

		$html .= '</div>';

		return $html;
	}

	/**
	 * Should the site URL be showed.
	 *
	 * @param array $results
	 *
	 * @return bool
	 */
	private static function show_site_url( $results ) {
		$cleaned_scan = preg_replace( '/https?:\/\//', '', $results['url'] );
		$cleaned_home = preg_replace( '/https?:\/\//', '', network_home_url() );

		return $cleaned_scan !== $cleaned_home;
	}

	public static function _sort_blacklist( $a, $b ) {
		if ( $a['status'] === 'blacklisted' && $b['status'] !== 'blacklisted' ) {
			return - 1;
		}

		if ( $a['status'] !== 'blacklisted' && $b['status'] === 'blacklisted' ) {
			return 1;
		}

		return 0;
	}
}
