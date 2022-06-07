<?php

class ITSEC_Site_Scanner_Validator extends ITSEC_Validator {
	public function get_id() {
		return 'site-scanner';
	}

	protected function sanitize_settings() {
		$this->sanitize_setting( 'string', 'public_key', __( 'Public Key', 'it-l10n-ithemes-security-pro' ) );
		$this->sanitize_setting( 'string', 'secret_key', __( 'Secret Key', 'it-l10n-ithemes-security-pro' ) );
		$this->sanitize_setting( 'array', 'vulnerabilities', __( 'Vulnerabilities', 'it-l10n-ithemes-security-pro' ) );
		$this->sanitize_setting( 'array', 'muted_issues', __( 'Muted Issues', 'it-l10n-ithemes-security-pro' ) );
		$this->sanitize_setting( 'cb-items:validate_muted_issue', 'muted_issues', __( 'Muted Issues', 'it-l10n-ithemes-security-pro' ) );
	}

	protected function validate_muted_issue( $issue ) {
		$schema = [
			'type'                 => 'object',
			'required'             => [ 'id', 'muted_at', 'muted_by' ],
			'properties'           => [
				'id'       => [
					'type' => 'string',
				],
				'muted_at' => [
					'type'    => 'integer',
					'minimum' => 0,
				],
				'muted_by' => [
					'type'    => 'integer',
					'minimum' => 0,
				],
			],
			'additionalProperties' => false,
		];

		$valid = rest_validate_value_from_schema( $issue, $schema );

		if ( is_wp_error( $valid ) ) {
			return $valid;
		}

		return rest_sanitize_value_from_schema( $issue, $schema );
	}
}

ITSEC_Modules::register_validator( new ITSEC_Site_Scanner_Validator() );
