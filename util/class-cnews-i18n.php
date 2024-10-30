<?php
/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       https://casperschultz.dk
 * @since      1.0.0
 *
 * @package    cnews
 * @subpackage cnews/util
 */

/**
 * Define the internationalization functionality.
 *
 * @since      1.0.0
 * @package    cnews
 * @subpackage cnews/includes
 * @author     Casper Schultz <casper@casperschultz.dk>
 */
class CNewsI18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'cnews',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}
}
