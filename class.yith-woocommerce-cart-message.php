<?php //phpcs:ignore WordPress.Files.FileName.InvalidClassFileName

/**
 * Implements features of FREE version of Yit WooCommerce Cart Messages
 *
 * @class   YWCM_Cart_Message
 * @package YITH
 * @since   1.0.0
 * @author  Your Inspiration Themes
 */

if ( ! defined( 'ABSPATH' ) || ! defined( 'YITH_YWCM_VERSION' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'YWCM_Cart_Message' ) ) {

	/**
	 * Class YWCM_Cart_Message
	 */
	class YWCM_Cart_Message {

		/**
		 * The single instance of the class
		 *
		 * @var object
		 * @since 1.0
		 */
		protected static $instance = null;

		/**
		 * Post type name
		 *
		 * @var string
		 */
		public $post_type_name = 'ywcm_message';


		/**
		 * Main plugin Instance
		 *
		 * @static
		 * @return object Main instance
		 *
		 * @since  1.0
		 * @author Antonino Scarfì <antonino.scarfi@yithemes.com>
		 */
		public static function instance() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}

			return self::$instance;
		}


		/**
		 * Constructor
		 *
		 * Initialize plugin and registers actions and filters to be used
		 *
		 * @since  1.0
		 * @author Emanuela Castorina
		 */
		public function __construct() {

			add_action( 'init', array( $this, 'message_post_type' ), 0 );

			add_filter( 'manage_edit-' . $this->post_type_name . '_columns', array( $this, 'edit_columns' ) );
			add_action( 'manage_' . $this->post_type_name . '_posts_custom_column', array( $this, 'custom_columns' ), 10, 2 );
			// register metabox to cart_messages.
			add_action( 'admin_init', array( $this, 'add_metabox' ), 1 );
			add_filter( 'yith_plugin_fw_metabox_class', array( $this, 'add_custom_metabox_class' ), 10, 2 );

		}

		/**
		 * Add new plugin-fw style.
		 *
		 * @param string  $class Class.
		 * @param WP_Post $post Post.
		 *
		 * @return string
		 */
		public function add_custom_metabox_class( $class, $post ) {

			$allow_post_types = array( $this->post_type_name );

			if ( in_array( $post->post_type, $allow_post_types, true ) ) {
				$class .= ' ' . yith_set_wrapper_class();
			}
			return $class;
		}

		/**
		 * Register Custom Post Type.
		 **/
		public function message_post_type() {

			$labels = array(
				'name'               => esc_html_x( 'YITH Cart Messages', 'Post Type General Name', 'yith-woocommerce-cart-messages' ),
				'singular_name'      => esc_html_x( 'YITH Cart Message', 'Post Type Singular Name', 'yith-woocommerce-cart-messages' ),
				'menu_name'          => esc_html__( 'Cart Message', 'yith-woocommerce-cart-messages' ),
				'parent_item_colon'  => esc_html__( 'Parent Item:', 'yith-woocommerce-cart-messages' ),
				'all_items'          => esc_html__( 'All Messages', 'yith-woocommerce-cart-messages' ),
				'view_item'          => esc_html__( 'View Messages', 'yith-woocommerce-cart-messages' ),
				'add_new_item'       => esc_html__( 'Add New Message', 'yith-woocommerce-cart-messages' ),
				'add_new'            => esc_html__( 'Add New Message', 'yith-woocommerce-cart-messages' ),
				'edit_item'          => esc_html__( 'Edit Message', 'yith-woocommerce-cart-messages' ),
				'update_item'        => esc_html__( 'Update Message', 'yith-woocommerce-cart-messages' ),
				'search_items'       => esc_html__( 'Search Message', 'yith-woocommerce-cart-messages' ),
				'not_found'          => esc_html__( 'Not found', 'yith-woocommerce-cart-messages' ),
				'not_found_in_trash' => esc_html__( 'Not found in Trash', 'yith-woocommerce-cart-messages' ),
			);
			$args   = array(
				'label'               => esc_html__( 'ywcm_message', 'yith-woocommerce-cart-messages' ),
				'description'         => esc_html__( 'YITH Cart Message Description', 'yith-woocommerce-cart-messages' ),
				'labels'              => $labels,
				'supports'            => array( 'title' ),
				'hierarchical'        => false,
				'public'              => true,
				'show_ui'             => true,
				'show_in_menu'        => false,
				'show_in_nav_menus'   => false,
				'show_in_admin_bar'   => false,
				'menu_position'       => 5,
				'can_export'          => true,
				'has_archive'         => false,
				'exclude_from_search' => true,
				'publicly_queryable'  => false,
				'capability_type'     => 'post',
			);
			register_post_type( $this->post_type_name, $args );

		}


		/**
		 * Add the metabox on product.
		 */
		public function add_metabox() {

			global $pagenow;

			$post = isset( $_REQUEST['post'] ) ? $_REQUEST['post'] : ( isset( $_REQUEST['post_ID'] ) ? $_REQUEST['post_ID'] : 0 ); //phpcs:ignore
			$post = get_post( $post );

			if ( ( $post && $post->post_type === $this->post_type_name ) || ( $pagenow === 'post-new.php' && isset( $_REQUEST['post_type'] ) && $this->post_type_name === $_REQUEST['post_type']  ) ) { //phpcs:ignore
				$args = require_once 'plugin-options/metabox/ywcm_metabox.php';
				if ( ! function_exists( 'YIT_Metabox' ) ) {
					require_once 'plugin-fw/yit-plugin.php';
				}
				$metabox = YIT_Metabox( 'yit-cart-messages-info' );
				$metabox->init( $args );
			}

		}


		/**
		 * Get all messages.
		 *
		 * @param array $args Argument list.
		 * @return mixed|void
		 */
		public function get_messages( $args = array() ) {

			$defaults = array(
				'post_type'        => $this->post_type_name,
				'post_status'      => 'publish',
				'posts_per_page'   => -1,
				'suppress_filters' => false,
			);

			$args = wp_parse_args( $args, $defaults );

			return apply_filters( 'ywcm_get_messages', get_posts( $args ), $args );
		}


		/**
		 * Edit Columns.
		 *
		 * @param array $columns Columns of list table.
		 * @return array
		 */
		public function edit_columns( $columns ) {

			$columns = array(
				'cb'          => '<input type="checkbox" />',
				'title'       => __( 'Title', 'yith-woocommerce-cart-messages' ),
				'type'        => __( 'Type', 'yith-woocommerce-cart-messages' ),
				'message'     => __( 'Message', 'yith-woocommerce-cart-messages' ),
				'button_text' => __( 'Button Text', 'yith-woocommerce-cart-messages' ),
				'button_url'  => __( 'Button Url', 'yith-woocommerce-cart-messages' ),
				'date'        => __( 'Date', 'yith-woocommerce-cart-messages' ),
			);

			return $columns;
		}

		/**
		 * Custom columns.
		 *
		 * @param array $column Columns of list table.
		 * @param int   $post_id Post id.
		 */
		public function custom_columns( $column, $post_id ) {

			$type = get_post_meta( $post_id, '_ywcm_message_type', true );

			switch ( $column ) {
				case 'type':
					$types = $this->get_types();
					if ( isset( $types[ $type ] ) ) {
						echo esc_html( $types[ $type ] );
					}
					break;
				case 'message':
					$message = get_post_meta( $post_id, '_ywcm_message_' . $type . '_text', true );
					if ( is_string( $message ) ) {
						echo wp_kses_post( $message );
					}
					break;
				case 'button_text':
					$button_text = get_post_meta( $post_id, '_ywcm_message_button', true );
					if ( is_string( $button_text ) ) {
						echo wp_kses_post( $button_text );
					}
					break;
				case 'button_url':
					$button_url = get_post_meta( $post_id, '_ywcm_message_button_url', true );
					if ( is_string( $button_url ) ) {
						echo esc_url( $button_url );
					}
					break;
			}
		}

		/**
		 * Return the list of cart messages types.
		 *
		 * @return mixed|void
		 */
		public function get_types() {
			$types = array(
				'products_cart'   => __( 'Products in Cart', 'yith-woocommerce-cart-messages' ),
				'categories_cart' => __( 'Categories in Cart', 'yith-woocommerce-cart-messages' ),
				'simple_message'  => __( 'Simple Message', 'yith-woocommerce-cart-messages' ),
			);

			if ( defined( 'YITH_YWCM_PREMIUM' ) ) {
				$types['minimum_amount'] = __( 'Minimum Amount', 'yith-woocommerce-cart-messages' );
				$types['deadline']       = __( 'Deadline', 'yith-woocommerce-cart-messages' );
				$types['referer']        = __( 'Referer', 'yith-woocommerce-cart-messages' );
			}

			return apply_filters( 'ywcm_cart_message_type', $types );
		}

	}

	/**
	 * Main instance of plugin
	 *
	 * @return object
	 * @since  1.0
	 * @author Emanuela Castorina <emanuela.castorina@yithemes.it>
	 */
	function YWCM_Cart_Message() { //phpcs:ignore
		return YWCM_Cart_Message::instance();
	}
}

