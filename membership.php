<?php
/**
 * Plugin Name: Membership
 * Plugin URI: https://github.com/tarosky/membership
 * Description: Add membership feature.
 * Version: 0.0.1
 * PHP Version: 5.4.0
 * Author: Tarosky INC.
 * Author URI: https://tarosky.co.jp/
 * License: GPL3 or later
 * Text Domain: membership
 *
 * @package membership
 */

defined( 'ABSPATH' ) || die();

// Bootstrap
add_action( 'after_setup_theme', function() {
	// Activate taxonomy.
	\Tarosky\Membership\Controller\Taxonomy::get_instance();
} );
