<?php
/**
 * Plugin Name: Onex Custom Popup Builder
 * Description: The advanced plugin for creating popups with Elementor
 * Version:     1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'Custom_Popup_Builder' ) ) {

	class Custom_Popup_Builder {

		private static $instance = null;

		private $version = '1.0.0';

		private $plugin_url = null;

		private $plugin_path = null;

		public $assets = null;

		public $post_type = null;

		public $settings = null;

		public $export_import = null;

		public $conditions = null;

		public $extensions = null;

		public $integration = null;

		public $generator = null;

		public $ajax_handlers = null;

		public $elementor_finder = null;

		public function __construct() {
		
			add_action( 'init', array( $this, 'init' ), -999 );
			if ( ! did_action( 'elementor/loaded' ) ) {
				add_action( 'admin_notices', [ $this, 'admin_notice_missing_main_plugin' ] );
				return;
			}
			register_activation_hook( __FILE__, array( $this, 'activation' ) );
		}

		public function get_version() {
			return $this->version;
		}

		public function has_elementor() {
			return defined( 'ELEMENTOR_VERSION' );
		}

		public function elementor() {
			return \Elementor\Plugin::$instance;
		}

		public function init() {

			if ( ! $this->has_elementor() ) {
				return;
			}

			$this->load_files();

			$this->assets = new Custom_Popup_Builder_Assets();

			$this->post_type = new Custom_Popup_Builder_Post_Type();

			$this->settings = new Custom_Popup_Builder_Settings();

			$this->export_import = new Custom_Export_Import();

			$this->conditions = new Custom_Popup_Builder_Conditions_Manager();

			$this->extensions = new Custom_Popup_Builder_Element_Extensions();

			$this->integration = new Custom_Popup_Builder_Integration();

			$this->generator = new Custom_Popup_Builder_Generator();

			$this->ajax_handlers = new Custom_Popup_Builder_Ajax_Handlers();

			$this->elementor_finder = new Custom_Elementor_Finder();

		}

		public function load_files() {
			require $this->plugin_path( 'includes/assets.php' );
			require $this->plugin_path( 'includes/admin-ajax-handlers.php' );
			require $this->plugin_path( 'includes/ajax-handlers.php' );
			require $this->plugin_path( 'includes/post-type.php' );
			require $this->plugin_path( 'includes/settings.php' );
			require $this->plugin_path( 'includes/export-import.php' );
			require $this->plugin_path( 'includes/utils.php' );
			require $this->plugin_path( 'includes/conditions/manager.php' );
			require $this->plugin_path( 'includes/extension.php' );
			require $this->plugin_path( 'includes/integration.php' );
			require $this->plugin_path( 'includes/generator.php' );
			require $this->plugin_path( 'includes/elementor-finder/elementor-finder.php' );
		}

		public function plugin_path( $path = null ) {

			if ( ! $this->plugin_path ) {
				$this->plugin_path = trailingslashit( plugin_dir_path( __FILE__ ) );
			}

			return $this->plugin_path . $path;
		}
	
		public function plugin_url( $path = null ) {

			if ( ! $this->plugin_url ) {
				$this->plugin_url = trailingslashit( plugin_dir_url( __FILE__ ) );
			}

			return $this->plugin_url . $path;
		}

		public function template_path() {
			return apply_filters( 'custom-popup-builder/template-path', 'custom-popup-builder/' );
		}

		public function get_template( $name = null ) {

			$template = locate_template( $this->template_path() . $name );

			if ( ! $template ) {
				$template = $this->plugin_path( 'templates/' . $name );
			}

			if ( file_exists( $template ) ) {
				return $template;
			} else {
				return false;
			}
		}

		public function admin_notice_missing_main_plugin() {

			if ( isset( $_GET['activate'] ) ) unset( $_GET['activate'] );

			$elementor_link = sprintf(
				'<a href="%1$s">%2$s</a>',
				admin_url() . 'plugin-install.php?s=elementor&tab=search&type=term',
				'<strong>' . esc_html__( 'Elementor', 'custom-popup-builder' ) . '</strong>'
			);

			$message = sprintf(
				esc_html__( '"%1$s" requires "%2$s" to be installed and activated.', 'custom-popup-builder' ),
				'<strong>' . esc_html__( 'Custom Popup Builder', 'custom-popup-builder' ) . '</strong>',
				$elementor_link
			);

			printf( '<div class="notice notice-warning is-dismissible"><p>%1$s</p></div>', $message );
		}

		public function activation() {
			
			require $this->plugin_path( 'includes/post-type.php' );

			Custom_Popup_Builder_Post_Type::register_post_type();

			flush_rewrite_rules();
		}

		public static function get_instance() {
			if ( null == self::$instance ) {
				self::$instance = new self;
			}
			return self::$instance;
		}
	}
}

if ( ! function_exists( 'custom_popup_builder' ) ) {

	function custom_popup_builder() {
		return Custom_Popup_Builder::get_instance();
	}
}

custom_popup_builder();
