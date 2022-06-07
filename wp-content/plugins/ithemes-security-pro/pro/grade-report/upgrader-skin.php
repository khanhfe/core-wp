<?php

final class ITSEC_Upgrader_Skin extends Automatic_Upgrader_Skin {
	public function error( $errors ) {
		if ( ! empty( $this->options['add_to_response'] ) ) {
			if ( ! empty( $this->plugin_info ) ) {
				ITSEC_Response::add_error(
					new WP_Error( 'itsec-grading-system-plugin-update-failed', sprintf(
						__( 'Unable to update the %1$s plugin. %2$s', 'it-l10n-ithemes-security-pro' ),
						$this->plugin_info['Name'],
						wp_sprintf('%l', ITSEC_Response::get_error_strings( $errors ) )
					) )
				);
			}
		}
	}

	public function request_filesystem_credentials( $error = false, $context = '', $allow_relaxed_file_ownership = false ) {
		if ( ! function_exists( 'submit_button' ) ) {
			require_once( ABSPATH . 'wp-admin/includes/template.php' );
		}

		$r = parent::request_filesystem_credentials( $error, $context, $allow_relaxed_file_ownership );

		if ( false === $r ) {
			$this->error( __( 'Could not request filesystem credentials.', 'it-l10n-ithemes-security-pro' ) );
		}

		return $r;
	}
}
