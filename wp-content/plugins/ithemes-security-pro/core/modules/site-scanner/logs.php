<?php

class ITSEC_Site_Scanner_Logs {
	public function __construct() {
		add_filter( 'itsec_logs_prepare_site-scanner_entry_for_list_display', array( $this, 'filter_entry_for_list_display' ) );
		add_filter( 'itsec_logs_prepare_site-scanner_entry_for_details_display', array( $this, 'filter_entry_for_details_display' ), 10, 4 );
		add_filter( 'itsec_highlighted_log_site-scanner-report_notice_title', array( $this, 'filter_highlight_title' ), 10, 2 );
		add_filter( 'itsec_highlighted_log_site-scanner-report_notice_message', array( $this, 'filter_highlight_message' ), 10, 2 );

		if ( did_action( 'admin_enqueue_scripts' ) ) {
			$this->enqueue();
		} else {
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue' ) );
		}
	}

	public function filter_entry_for_list_display( $entry ) {
		$entry['module_display'] = esc_html__( 'Site Scan', 'it-l10n-ithemes-security-pro' );

		if ( 'scan' === $entry['code'] ) {
			if ( 'process-start' === $entry['type'] ) {
				$entry['description'] = esc_html__( 'Scan Performance', 'it-l10n-ithemes-security-pro' );
			} else {
				$entry['description'] = esc_html__( 'Scan', 'it-l10n-ithemes-security-pro' );
			}
		} else {
			require_once( dirname( __FILE__ ) . '/util.php' );
			$entry['description'] = ITSEC_Site_Scanner_Util::get_scan_code_description( $entry['code'] );

			if ( ! $entry['description'] ) {
				$entry['description'] = $entry['code'];
			}
		}

		return $entry;
	}

	public function filter_entry_for_details_display( $details, $entry ) {
		require_once( dirname( __FILE__ ) . '/template.php' );

		$entry = $this->filter_entry_for_list_display( $entry );

		$details['module']['content']      = $entry['module_display'];
		$details['description']['content'] = $entry['description'];

		if ( ! in_array( $entry['type'], [ 'process-start', 'process-update', 'process-stop' ], true ) ) {
			$details['results'] = array(
				'header'  => esc_html__( 'Results', 'it-l10n-ithemes-security-pro' ),
				'content' => ITSEC_Site_Scanner_Template::get_html( $entry['data']['results'] ),
			);
		}

		return $details;
	}

	public function filter_highlight_title( $title, $entry ) {
		return esc_html__( 'iThemes Security suspects your site may have been compromised.', 'it-l10n-ithemes-security-pro' );
	}

	public function filter_highlight_message( $title, $entry ) {
		return sprintf(
			esc_html__( 'Please %1$sreview the logs%2$s to make sure your system has not been compromised.', 'it-l10n-ithemes-security-pro' ),
			'<a href="{{ $view }}">',
			'</a>'
		);
	}

	public function enqueue() {
		wp_enqueue_script( 'itsec-site-scanner-scan-settings' );
		wp_enqueue_style( 'itsec-core-packages-components-site-scan-results-style' );
	}
}

new ITSEC_Site_Scanner_Logs();
