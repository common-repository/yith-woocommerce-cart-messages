<?php
/**
 * Plugin Name: YITH WooCommerce Cart Messages
 * Plugin URI: https://yithemes.com/themes/plugins/yith-woocommerce-cart-messages
 * Description: <code><strong>YITH WooCommerce Cart Messages</strong></code> allows making your offers clearly visible by showing users a message at the very moment they pay the utmost attention, on the cart page. It's perfect to increase the total amount of every purchase. <a href="https://yithemes.com/" target="_blank">Get more plugins for your e-commerce shop on <strong>YITH</strong></a>.
 * Author: YITH
 * Text Domain: yith-woocommerce-cart-messages
 * Version: 1.8.0
 * Author URI: https://yithemes.com/
 * WC requires at least: 5.3
 * WC tested up to: 5.8
 *
 * @package YITH Woocommerce Cart Messages
 * @since   1.0.0
 * @author  YITH
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly.

if ( ! function_exists( 'is_plugin_active' ) ) {
	require_once ABSPATH . 'wp-admin/includes/plugin.php';
}

if ( ! defined( 'YITH_YWCM_DIR' ) ) {
	define( 'YITH_YWCM_DIR', plugin_dir_path( __FILE__ ) );
}

/* Plugin Framework Version Check */
if ( ! function_exists( 'yit_maybe_plugin_fw_loader' ) && file_exists( YITH_YWCM_DIR . 'plugin-fw/init.php' ) ) {
	require_once YITH_YWCM_DIR . 'plugin-fw/init.php';
}
yit_maybe_plugin_fw_loader( YITH_YWCM_DIR );


if ( defined( 'YITH_YWCM_PREMIUM' ) ) {
	/**
	 * Trigger a notice if the Premium version is installed
	 */
	function yith_ywcm_install_free_admin_notice() {
		?>
		<div class="error">
			<p><?php esc_html_e( 'You can\'t activate the free version of YITH WooCommerce Cart Messages while you are using the premium one.', 'yith-woocommerce-cart-messages' ); ?></p>
		</div>
		<?php
	}

	add_action( 'admin_notices', 'yith_ywcm_install_free_admin_notice' );

	deactivate_plugins( plugin_basename( __FILE__ ) );
	return;
}

if ( ! function_exists( 'yith_plugin_registration_hook' ) ) {
	require_once 'plugin-fw/yit-plugin-registration-hook.php';
}
register_activation_hook( __FILE__, 'yith_plugin_registration_hook' );


if ( defined( 'YITH_YWCM_VERSION' ) ) {
	return;
} else {
	define( 'YITH_YWCM_VERSION', '1.8.0' );
}

if ( ! defined( 'YITH_YWCM_SUFFIX' ) ) {
	$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
	define( 'YITH_YWCM_SUFFIX', $suffix );
}

if ( ! defined( 'YITH_YWCM_FREE_INIT' ) ) {
	define( 'YITH_YWCM_FREE_INIT', plugin_basename( __FILE__ ) );
}

if ( ! defined( 'YITH_YWCM_FILE' ) ) {
	define( 'YITH_YWCM_FILE', __FILE__ );
}

if ( ! defined( 'YITH_YWCM_SLUG' ) ) {
	define( 'YITH_YWCM_SLUG', 'yith-woocommerce-cart-messages' );
}

if ( ! defined( 'YITH_YWCM_URL' ) ) {
	define( 'YITH_YWCM_URL', plugins_url( '/', __FILE__ ) );
}

if ( ! defined( 'YITH_YWCM_ASSETS_URL' ) ) {
	define( 'YITH_YWCM_ASSETS_URL', YITH_YWCM_URL . 'assets' );
}

if ( ! defined( 'YITH_YWCM_TEMPLATE_PATH' ) ) {
	define( 'YITH_YWCM_TEMPLATE_PATH', YITH_YWCM_DIR . 'templates' );
}

/**
 * Load required classes and functions
 */
function yith_ywcm_constructor() {
	// Woocommerce installation check _________________________.
	if ( ! function_exists( 'WC' ) ) {
		/**
		 * Trigger a notice if WooCommerce is not installed
		 */
		function yith_ywcm_install_woocommerce_admin_notice() {
			?>
			<div class="error">
				<p><?php esc_html_e( 'YITH WooCommerce Cart Messages is enabled but not effective. It requires WooCommerce in order to work.', 'yith-woocommerce-cart-messages' ); ?></p>
			</div>
			<?php
		}

		add_action( 'admin_notices', 'yith_ywcm_install_woocommerce_admin_notice' );
		return;
	}

	/* Load YITH_YWCM text domain */
	load_plugin_textdomain( 'yith-woocommerce-cart-messages', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

	// Load required classes and functions.
	require_once YITH_YWCM_DIR . 'yith-cart-messages-functions.php';
	require_once YITH_YWCM_DIR . 'class.yith-woocommerce-cart-message.php';
	require_once YITH_YWCM_DIR . 'class.yith-woocommerce-cart-messages.php';

	global $YWCM_Instance; //phpcs:ignore
	$YWCM_Instance = new YWCM_Cart_Messages(); //phpcs:ignore
}

add_action( 'plugins_loaded', 'yith_ywcm_constructor' );
