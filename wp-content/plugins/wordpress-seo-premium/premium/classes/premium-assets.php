<?php
/**
 * WPSEO Premium plugin file.
 *
 * @package WPSEO\Premium\Classes
 */

/**
 * Loads the Premium assets.
 */
class WPSEO_Premium_Assets implements WPSEO_WordPress_Integration {

	/**
	 * Registers the hooks.
	 *
	 * @codeCoverageIgnore Method relies on a WordPress function.
	 *
	 * @return void
	 */
	public function register_hooks() {
		add_action( 'admin_init', [ $this, 'register_assets' ] );
	}

	/**
	 * Registers the assets for premium.
	 *
	 * @return void
	 */
	public function register_assets() {
		$version = $this->get_version();
		$scripts = $this->get_scripts( $version );
		$styles  = $this->get_styles( $version );

		array_walk( $scripts, [ $this, 'register_script' ] );
		array_walk( $styles, [ $this, 'register_style' ] );
	}

	/**
	 * Retrieves a flatten version.
	 *
	 * @codeCoverageIgnore Method uses a dependency.
	 *
	 * @return string The flatten version.
	 */
	protected function get_version() {
		$asset_manager = new WPSEO_Admin_Asset_Manager();
		return $asset_manager->flatten_version( WPSEO_VERSION );
	}

	/**
	 * Retrieves an array of script to register.
	 *
	 * @codeCoverageIgnore Returns a simple dataset.
	 *
	 * @param string $version Current version number.
	 *
	 * @return array The scripts.
	 */
	protected function get_scripts( $version ) {
		return [
			[
				'name'         => 'yoast-seo-premium-commons',
				'path'         => 'assets/js/dist/',
				'filename'     => 'commons-premium-' . $version . WPSEO_CSSJS_SUFFIX . '.js',
				'dependencies' => [],
			],
			[
				'name'         => WPSEO_Admin_Asset_Manager::PREFIX . 'premium-metabox',
				'path'         => 'assets/js/dist/',
				'filename'     => 'wp-seo-premium-metabox-' . $version . WPSEO_CSSJS_SUFFIX . '.js',
				'dependencies' => [
					'jquery',
					'wp-util',
					'underscore',
					'wp-element',
					'wp-i18n',
					'wp-data',
					'wp-components',
					'yoast-seo-premium-commons',
					WPSEO_Admin_Asset_Manager::PREFIX . 'components',
					WPSEO_Admin_Asset_Manager::PREFIX . 'analysis',
					WPSEO_Admin_Asset_Manager::PREFIX . 'help-scout-beacon',
				],
			],
			[
				'name'         => 'yoast-social-preview',
				'path'         => 'assets/js/dist/',
				'filename'     => 'yoast-premium-social-preview-' . $version . WPSEO_CSSJS_SUFFIX . '.js',
				'dependencies' => [ 'jquery', 'jquery-ui-core', 'yoast-seo-premium-commons', WPSEO_Admin_Asset_Manager::PREFIX . 'analysis' ],
			],
			[
				'name'         => 'wp-seo-premium-custom-fields-plugin',
				'path'         => 'assets/js/dist/',
				'filename'     => 'wp-seo-premium-custom-fields-plugin-' . $version . WPSEO_CSSJS_SUFFIX . '.js',
				'dependencies' => [ 'jquery', 'yoast-seo-premium-commons' ],
			],
			[
				'name'         => 'wp-seo-premium-quickedit-notification',
				'path'         => 'assets/js/dist/',
				'filename'     => 'wp-seo-premium-quickedit-notification-' . $version . WPSEO_CSSJS_SUFFIX . '.js',
				'dependencies' => [
					'jquery',
					'wp-api',
					'wp-api-fetch',
					'yoast-seo-premium-commons',
				],
			],
			[
				'name'         => 'wp-seo-premium-redirect-notifications',
				'path'         => 'assets/js/dist/',
				'filename'     => 'wp-seo-premium-redirect-notifications-' . $version . WPSEO_CSSJS_SUFFIX . '.js',
				'dependencies' => [
					'jquery',
					'wp-api',
					'wp-api-fetch',
					'yoast-seo-premium-commons',
				],
			],
			[
				'name'         => 'wp-seo-premium-redirect-notifications-gutenberg',
				'path'         => 'assets/js/dist/',
				'filename'     => 'wp-seo-premium-redirect-notifications-gutenberg-' . $version . WPSEO_CSSJS_SUFFIX . '.js',
				'dependencies' => [
					WPSEO_Admin_Asset_Manager::PREFIX . 'components',
					'wp-plugins',
				],
			],
		];
	}

	/**
	 * Retrieves an array of styles to register.
	 *
	 * @codeCoverageIgnore Returns a simple dataset.
	 *
	 * @param string $version Current version number.
	 *
	 * @return array The styles.
	 */
	protected function get_styles( $version ) {
		return [
			[
				'name'         => WPSEO_Admin_Asset_Manager::PREFIX . 'premium-metabox',
				'source'       => 'assets/css/dist/premium-metabox-' . $version . '.css',
				'dependencies' => [],
			],
			[
				'name'         => 'yoast-social-preview-css',
				'source'       => 'assets/dist/social_preview/yoast-social-preview-390.min.css',
				'dependencies' => [
					WPSEO_Admin_Asset_Manager::PREFIX . 'metabox-css',
				],
			],
			[
				'name'         => 'yoast-premium-social-preview',
				'source'       => 'assets/css/dist/premium-social-preview-' . $version . '.css',
				'dependencies' => [
					WPSEO_Admin_Asset_Manager::PREFIX . 'metabox-css',
				],
			],
		];
	}

	/**
	 * Registers the given script to WordPress.
	 *
	 * @codeCoverageIgnore Method calls a WordPress function.
	 *
	 * @param array $script The script to register.
	 *
	 * @return void
	 */
	protected function register_script( $script ) {
		$url = plugin_dir_url( WPSEO_PREMIUM_FILE ) . $script['path'] . $script['filename'];

		if ( defined( 'YOAST_SEO_PREMIUM_DEV_SERVER' ) && YOAST_SEO_PREMIUM_DEV_SERVER ) {
			$url = 'http://localhost:8081/' . $script['filename'];
		}

		wp_register_script(
			$script['name'],
			$url,
			$script['dependencies'],
			WPSEO_VERSION
		);
	}

	/**
	 * Registers the given style to WordPress.
	 *
	 * @codeCoverageIgnore Method calls a WordPress function.
	 *
	 * @param array $style The style to register.
	 *
	 * @return void
	 */
	protected function register_style( $style ) {
		wp_register_style(
			$style['name'],
			plugin_dir_url( WPSEO_PREMIUM_FILE ) . $style['source'],
			$style['dependencies'],
			WPSEO_VERSION
		);
	}
}
