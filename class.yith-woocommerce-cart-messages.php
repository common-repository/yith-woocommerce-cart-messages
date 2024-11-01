<?php //phpcs:ignore WordPress.Files.FileName.InvalidClassFileName
/**
 * Implements features of FREE version of Yit WooCommerce Cart Messages
 *
 * @class   YWCM_Cart_Messages
 * @package YITH
 * @since   1.0.0
 * @author  Your Inspiration Themes
 */

if ( ! defined( 'ABSPATH' ) || ! defined( 'YITH_YWCM_VERSION' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'YWCM_Cart_Messages' ) ) {

	/**
	 * Class YWCM_Cart_Messages
	 */
	class YWCM_Cart_Messages {

		/**
		 * Panel Object
		 *
		 * @var Object
		 */
		protected $panel;

		/**
		 * Premium tab template file name
		 *
		 * @var string
		 */
		protected $premium = 'premium.php';

		/**
		 * Premium version landing link
		 *
		 * @var string
		 */
		protected $premium_landing = 'https://yithemes.com/themes/plugins/yith-woocommerce-cart-messages/';


		/**
		 * Panel page
		 *
		 * @var string
		 */
		protected $panel_page = 'yith_woocommerce_cart_messages';

		/**
		 * List of messages
		 *
		 * @var string
		 */
		protected $messages = array();

		/**
		 * Constructor
		 *
		 * Initialize plugin and registers actions and filters to be used
		 *
		 * @since  1.0
		 */
		public function __construct() {

			$this->create_menu_items();
			if ( ! is_admin() ) {
				add_action( 'wp_loaded', array( $this, 'load_messages' ), 30 );
			}

			// Add action links.
			add_filter( 'plugin_action_links_' . plugin_basename( YITH_YWCM_DIR . '/' . basename( YITH_YWCM_FILE ) ), array( $this, 'action_links' ) );
			add_filter( 'yith_show_plugin_row_meta', array( $this, 'plugin_row_meta' ), 10, 5 );

			if ( 'yes' === get_option( 'ywcm_show_in_cart' ) ) {
				add_action( 'woocommerce_before_cart_contents', array( $this, 'print_messages' ) );
			}

			if ( 'yes' === get_option( 'ywcm_show_in_checkout' ) ) {
				add_action( 'woocommerce_before_checkout_form', array( $this, 'print_messages' ) );
			}

			add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ), 20 );
		}


		/**
		 * Load the messages
		 *
		 * @since  1.1.3
		 * @author Emanuela Castorina
		 */
		public function load_messages() {
			$this->messages = YWCM_Cart_Message()->get_messages();
		}

		/**
		 * Create Menu Items
		 *
		 * Print admin menu items
		 *
		 * @since  1.0
		 * @author Emanuela Castorina
		 */
		private function create_menu_items() {
			add_action( 'plugins_loaded', array( $this, 'plugin_fw_loader' ), 15 );
			add_action( 'after_setup_theme', array( $this, 'call_instance_object' ), 5 );

			// Add a panel under YITH Plugins tab.
			add_action( 'admin_menu', array( $this, 'register_panel' ), 5 );
			add_action( 'yith_woocommerce_cart_messages_premium', array( $this, 'premium_tab' ) );
		}

		/**
		 * Enqueue style scripts in administrator
		 *
		 * Enqueue style and scripts files
		 *
		 * @return  void
		 * @author   Emanuela Castorina <emanuela.castorina@yithemes.com>
		 * @since    1.0
		 */
		public function admin_enqueue_scripts() {

			if ( get_post_type() !== 'ywcm_message' ) {
				return;
			}
			wp_enqueue_style( 'yith_ywcm', YITH_YWCM_ASSETS_URL . '/css/admin.css', array(), YITH_YWCM_VERSION );
			wp_enqueue_script( 'jquery-ui-datepicker' );
			wp_enqueue_script( 'jquery-ui-slider' );
			wp_enqueue_script( 'ywcm_timepicker', YITH_YWCM_ASSETS_URL . '/js/jquery-ui-timepicker-addon.min.js', array( 'jquery' ), YITH_YWCM_VERSION, true );
			wp_enqueue_script( 'yith_ywcm_admin', YITH_YWCM_ASSETS_URL . '/js/ywcm-admin' . YITH_YWCM_SUFFIX . '.js', array( 'ywcm_timepicker' ), YITH_YWCM_VERSION, true );

			if ( ! wp_script_is( 'selectWoo' ) ) {
				wp_enqueue_script( 'selectWoo' );
				wp_enqueue_script( 'wc-enhanced-select' );
			}

		}

		/**
		 * Load YIT Plugin Framework
		 *
		 * @return void
		 * @since  1.0
		 * @access public
		 * @author Andrea Grillo <andrea.grillo@yithemes.com>
		 */
		public function plugin_fw_loader() {
			if ( ! defined( 'YIT_CORE_PLUGIN' ) ) {
				global $plugin_fw_data;
				if ( ! empty( $plugin_fw_data ) ) {
					$plugin_fw_file = array_shift( $plugin_fw_data );
					require_once $plugin_fw_file;
				}
			}
		}

		/**
		 * Call instance object
		 *
		 * Call the instance of YWMC_Cart_Message.
		 *
		 * @return void
		 * @since  1.0
		 * @access public
		 * @author Emanuela Castorina
		 */
		public function call_instance_object() {
			YWCM_Cart_Message();
		}


		/**
		 * Add a panel under YITH Plugins tab
		 *
		 * @return   void
		 * @since    1.0
		 * @author   Andrea Grillo <andrea.grillo@yithemes.com>
		 * @use      /Yit_Plugin_Panel class
		 * @see      plugin-fw/lib/yit-plugin-panel.php
		 */
		public function register_panel() {

			if ( ! empty( $this->panel ) ) {
				return;
			}

			$admin_tabs = array(
				'cart-messages' => __( 'Cart Messages', 'yith-woocommerce-cart-messages' ),
				'settings'      => __( 'Settings', 'yith-woocommerce-cart-messages' ),
			);

			if ( defined( 'YITH_YWCM_FREE_INIT' ) ) {
				$admin_tabs['premium'] = __( 'Premium Version', 'yith-woocommerce-cart-messages' );
			}

			if ( defined( 'YITH_YWCM_PREMIUM' ) ) {
				$admin_tabs['layout'] = __( 'Layouts', 'yith-woocommerce-cart-messages' );

			}

			$args = array(
				'create_menu_page' => true,
				'parent_slug'      => '',
				'plugin_slug'      => YITH_YWCM_SLUG,
				'page_title'       => __( 'YITH WooCommerce Cart Messages', 'yith-woocommerce-cart-messages' ),
				'menu_title'       => __( 'Cart Messages', 'yith-woocommerce-cart-messages' ),
				'capability'       => 'manage_options',
				'parent'           => '',
				'parent_page'      => 'yith_plugin_panel',
				'page'             => $this->panel_page,
				'admin-tabs'       => $admin_tabs,
				'class'            => yith_set_wrapper_class(),
				'options-path'     => YITH_YWCM_DIR . 'plugin-options',
			);

			/* === Fixed: not updated theme  === */
			if ( ! class_exists( 'YIT_Plugin_Panel_WooCommerce' ) ) {
				require_once 'plugin-fw/lib/yit-plugin-panel-wc.php';
			}

			$this->panel = new YIT_Plugin_Panel_WooCommerce( $args );
		}

		/**
		 * Premium Tab Template
		 *
		 * Load the premium tab template on admin page
		 *
		 * @return   void
		 * @since    1.0
		 * @author   Andrea Grillo <andrea.grillo@yithemes.com>
		 */
		public function premium_tab() {
			$premium_tab_template = YITH_YWCM_TEMPLATE_PATH . '/admin/' . $this->premium;
			if ( file_exists( $premium_tab_template ) ) {
				include_once $premium_tab_template;
			}
		}


		/**
		 * Action Links
		 *
		 * Add the action links to plugin admin page.
		 *
		 * @param string $links Links plugin array.
		 *
		 * @return mixed
		 * @use      plugin_action_links_{$plugin_file_name}
		 * @author   Andrea Grillo <andrea.grillo@yithemes.com>
		 * @since    1.0
		 */
		public function action_links( $links ) {
			$links = yith_add_action_links( $links, $this->panel_page, false );
			return $links;
		}

		/**
		 * Print Messages
		 *
		 * Print all message in cart and checkout
		 *
		 * @return   void
		 * @throws Exception Get the error.
		 * @author   Emanuela Castorina <emanuela.castorina@yithemes.com>
		 * @since    1.0
		 */
		public function print_messages() {
			foreach ( $this->messages as $message ) {
				if ( apply_filters( 'yith_ywcm_is_valid_message', $this->is_valid( $message->ID ), $message->ID ) ) {
					$message_type = get_post_meta( $message->ID, '_ywcm_message_type', true );
					$layout       = 'layout';
					$args         = ( method_exists( $this, 'get_' . $message_type . '_args' ) ) ? $this->{'get_' . $message_type . '_args'}( $message ) : false;
					if ( $args ) {
						yit_plugin_get_template( YITH_YWCM_DIR, '/layouts/' . $layout . '.php', $args );
					}
				}
			}
		}


		/**
		 * Get Product Cart
		 *
		 * Return an array with the args to print into message or false if the message can't be print
		 *
		 * @param string $message Message.
		 * @return   mixed array || bool if the message can't be print
		 * @author   Emanuela Castorina <emanuela.castorina@yithemes.com>
		 * @since    1.0
		 */
		public function get_products_cart_args( $message ) {

			$args = array();

			$args['text'] = get_post_meta( $message->ID, '_ywcm_message_products_cart_text', true );

			if ( '' === $args['text'] ) {
				return false;
			}

			$minimum_quantity   = get_post_meta( $message->ID, '_ywcm_message_products_cart_minimum', true );
			$threshold_quantity = get_post_meta( $message->ID, '_ywcm_products_cart_threshold_quantity', true );
			$products           = get_post_meta( $message->ID, '_ywcm_products_cart_products', true );

			if ( '' === $products ) {
				return false;
			}

			$product_in_cart_quantity = 0;
			$products_in_cart         = array();
			$products_in_cart_titles  = array();

			if ( WC()->cart ) {

				foreach ( WC()->cart->get_cart() as $cart_item_key => $values ) {
					$product    = $values['data'];
					$product_id = $product->get_id();
					$parent_id  = yit_get_base_product_id( $product );

					if ( in_array( $product_id, $products ) || in_array( $parent_id, $products ) ) { //phpcs:ignore
						$products_in_cart[] = $values['data'];

						$products_in_cart_titles[ $product_id ] = $product->get_title();
						$product_in_cart_quantity              += $values['quantity'];
					}
				}
			}

			if ( empty( $products_in_cart_titles ) ) {
				return false;
			}

			if ( '' !== $minimum_quantity && $product_in_cart_quantity >= $minimum_quantity ) {
				return false;
			}
			if ( '' !== $threshold_quantity && $product_in_cart_quantity < $threshold_quantity ) {
				return false;
			}

			$remaining_quantity = $minimum_quantity - $product_in_cart_quantity;
			$titles             = implode( ', ', $products_in_cart_titles );

			$args['text'] = str_replace( '{remaining_quantity}', '<strong>' . $remaining_quantity . '</strong>', $args['text'] );
			$args['text'] = str_replace( '{products}', $titles, $args['text'] );
			$args['text'] = str_replace( '{quantity}', $product_in_cart_quantity, $args['text'] );
			$args['text'] = str_replace( '{required_quantity}', $minimum_quantity, $args['text'] );

			$args['button'] = $this->get_button_options( $message->ID );
			$args['slug']   = $message->post_name;

			return $args;

		}

		/**
		 * Get Categories Cart
		 *
		 * Return an array with the args to print into message or false if the message can't be print
		 *
		 * @param string $message Message.
		 * @return   mixed array || bool if the message can't be print
		 * @author   Emanuela Castorina <emanuela.castorina@yithemes.com>
		 * @since    1.0
		 */
		public function get_categories_cart_args( $message ) {

			if ( empty( WC()->cart->cart_contents ) ) {
				return false;
			}

			$args = array();

			$args['text'] = get_post_meta( $message->ID, '_ywcm_message_categories_cart_text', true );
			if ( '' === $args['text'] ) {
				return false;
			}

			$categories = get_post_meta( $message->ID, '_ywcm_message_category_cart_categories', true );
			if ( '' === $categories ) {
				return false;
			}

			$products_in_cart_titles = array();
			$category_in_cart        = array();

			foreach ( $categories as $category ) {

				if ( WC()->cart ) {
					foreach ( WC()->cart->get_cart() as $values ) {
						$product = $values['data'];
						$parent  = yit_get_base_product_id( $product );
						if ( has_term( $category, 'product_cat', $product->get_id() ) || has_term( $category, 'product_cat', $parent ) ) {
							if ( ! in_array( $category, $category_in_cart ) ) { //phpcs:ignore
								$category_in_cart[] = $category;
							}
							$products_in_cart_titles[ $product->get_id() ] = $product->get_title();
						}
					}
				}
			}

			if ( empty( $category_in_cart ) ) {
				return false;
			}

			$titles       = implode( ', ', $products_in_cart_titles );
			$args['text'] = str_replace( '{products}', $titles, $args['text'] );

			$cat_names = array();
			// get categories names.
			foreach ( $categories as $category ) {
				$term = get_term_by( 'slug', $category, 'product_cat' );
				if ( ! empty( $term ) ) {
					$cat_names[] = $term->name;
				}
			}
			$categories_titles = implode( ', ', $cat_names );
			$args['text']      = str_replace( '{categories}', $categories_titles, $args['text'] );
			$args['button']    = $this->get_button_options( $message->ID );
			$args['slug']      = $message->post_name;
			return $args;

		}


		/**
		 * Get simple message
		 *
		 * Return an array with the args to print into message or false if the message can't be print.
		 *
		 * @param string $message String message.
		 * @return   mixed array || bool if the message can't be print
		 * @author   Emanuela Castorina <emanuela.castorina@yithemes.com>
		 * @since    1.0
		 */
		public function get_simple_message_args( $message ) {

			$args         = array();
			$args['text'] = get_post_meta( $message->ID, '_ywcm_message_simple_message_text', true );
			if ( '' === $args['text'] ) {
				return false;
			}

			$args['slug']   = $message->post_name;
			$args['button'] = $this->get_button_options( $message->ID );
			return $args;

		}


		/**
		 * Get button option
		 *
		 * Return the button option of message.
		 *
		 * @param int $message_id Message id.
		 * @return   string
		 * @author   Emanuela Castorina <emanuela.castorina@yithemes.com>
		 * @since    1.0
		 */
		public function get_button_options( $message_id ) {

			$button   = '';
			$btn_text = get_post_meta( $message_id, '_ywcm_message_button', true );
			$btn_url  = get_post_meta( $message_id, '_ywcm_message_button_url', true );

			if ( '' !== $btn_text && '' !== $btn_url ) {
				$button = ' <a class="button" href="' . esc_url( $btn_url ) . '" rel="nofollow">' . $btn_text . '</a>';
			}

			return $button;
		}

		/**
		 * Is a valid message
		 *
		 * Return a boolean if the message is valid or is expired.
		 *
		 * @param int $message_id Message id.
		 * @return   string
		 * @throws Exception Return an error.
		 * @author   Emanuela Castorina <emanuela.castorina@yithemes.com>
		 * @since    1.0
		 */
		public function is_valid( $message_id ) {
			$expire = get_post_meta( $message_id, '_ywcm_message_expire', true );
			if ( '' === $expire ) {
				return true;
			}
			$today_dt  = new DateTime();
			$expire_dt = new DateTime( $expire );
			if ( $expire_dt > $today_dt ) {
				return true;
			}

			return false;
		}

		/**
		 * Add the action links to plugin admin page
		 *
		 * @param   string $new_row_meta_args  Plugin Meta New args.
		 * @param   string $plugin_meta        Plugin Meta.
		 * @param   string $plugin_file        Plugin file.
		 * @param   array  $plugin_data        Plugin data.
		 * @param   string $status             Status.
		 * @param   string $init_file          Init file.
		 *
		 * @return   Array
		 * @since    1.0
		 * @author   Andrea Grillo <andrea.grillo@yithemes.com>
		 * @use      plugin_row_meta
		 */
		public function plugin_row_meta( $new_row_meta_args, $plugin_meta, $plugin_file, $plugin_data, $status, $init_file = 'YITH_YWCM_FREE_INIT' ) {
			if ( defined( $init_file ) && constant( $init_file ) === $plugin_file ) {
				$new_row_meta_args['slug'] = YITH_YWCM_SLUG;
			}

			return $new_row_meta_args;
		}


		/**
		 * Get the premium landing uri
		 *
		 * @return  string The premium landing link
		 * @author  Andrea Grillo <andrea.grillo@yithemes.com>
		 * @since   1.0.0
		 */
		public function get_premium_landing_uri() {
			return $this->premium_landing;
		}

	}
}

