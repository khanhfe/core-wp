<?php

class ITSEC_Settings_Page_Sidebar_Widget_Site_Scanner extends ITSEC_Settings_Page_Sidebar_Widget {
	public function __construct() {
		$this->id       = 'site-scanner';
		$this->title    = __( 'Site Scan', 'it-l10n-ithemes-security-pro' );
		$this->priority = 8;

		parent::__construct();
	}

	public function enqueue_scripts_and_styles() {
		wp_enqueue_script( 'itsec-site-scanner-scan-settings' );
		wp_enqueue_style( 'itsec-core-packages-components-site-scan-results-style' );
	}

	public function handle_ajax_request( $data ) {
		$results = ITSEC_Site_Scanner_API::scan();

		if ( is_wp_error( $results ) && $results->get_error_message( 'itsec-temporary-server-error' ) ) {
			ITSEC_Response::add_warning( __( 'Malware scanning is temporarily unavailable, please try again later.', 'it-l10n-ithemes-security-pro' ) );
		} else {
			ITSEC_Response::set_response( ITSEC_Site_Scanner_Template::get_html( $results, true ) );
		}
	}

	public function render( $form ) {
		$button      = __( 'Scan Now', 'it-l10n-ithemes-security-pro' );
		$module      = 'site-scanner';
		$description = __( 'This %1$ssite scan is powered by iThemes%2$s. We use several datapoints to check for known malware, blacklisting status, website errors and out-of-date software. These datapoints are not 100%% accurate, but we try our best to provide thorough results.', 'it-l10n-ithemes-security-pro' );
		$description = sprintf( $description, '<a href="https://help.ithemes.com/hc/en-us/articles/360046334433" target="_blank">', '</a>' );

		?>
		<p><?php echo $description; ?></p>
		<p><?php printf( __( 'Results of previous scans can be found on the <a href="%s">logs page</a>.', 'it-l10n-ithemes-security-pro' ), ITSEC_Core::get_logs_page_url( $module ) ); ?></p>
		<div class="itsec-site-scanner-scan-results-wrapper"></div>
		<?php $form->add_button( 'start', array( 'value' => $button, 'class' => 'button-primary' ) ); ?>
		<?php
	}

	protected function save( $data ) {
	}
}

new ITSEC_Settings_Page_Sidebar_Widget_Site_Scanner();
