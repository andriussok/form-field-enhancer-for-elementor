<?php
/**
 * Plugin Name: Form Field Enhancer for Elementor
 * Description: FFEE adds functionality to Elementor forms by offering features like length validation with a character counter, pattern validation, readonly field switcher, and sub-labels.
 * Plugin URI: #
 * Version: 1.0.0
 * Requires PHP: 7.4
 * Author: Andrius Sok
 * Author URI: https://andriuss.lt
 * Developer: Andrius Sok
 * License: GPLv3
 * Text Domain: ffee-lang
 **/

/**
 * -----------------------------------
 * Idea borrowed from Raz Ohad, source - https://github.com/elementor/elementor/issues/9382
 * -----------------------------------
 **/

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

// Form Field Enhancer for Elementor
final class FFCC_Controls {

  /**
   * Plugin Version control
   *
   * @since 1.0.0
   * @var version.
   */
  const FFEE_VERSION = '1.0.0';
  const MIN_PHP_VERSION = '7.0';

  public function __construct() {

    // Load translation
    add_action( 'init', array( $this, 'i18n' ) );

    // Init Plugin
    add_action( 'plugins_loaded', array( $this, 'init' ) );

    // Include assets
    add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ), 1000 );

  }

  /**
   * Include assets
   *
   * Load plugin asset files.
   *
   * @since 1.0
   */
  public function enqueue_scripts() {
    wp_enqueue_script(
        'ffee-scripts',
        plugin_dir_url( __FILE__ ) . 'assets/ffee-scripts.js',
        [ 'jquery' ], // Dependencies
        '1.0.0',
        true // Enqueue the script in the footer
    );
  }


  /**
   * Load Textdomain
   *
   * Load plugin localization files.
   * Fired by `init` action hook.
   *
   * @since 1.0
   */
  public function i18n() {
    load_plugin_textdomain( 'ffee-lang' );
  }

  /**
   * Initialize the plugin
   *
   * Validates that Elementor is already loaded.
   * Checks for basic plugin requirements, if one check fail don't continue,
   * if all check have passed include the plugin class.
   *
   * Fired by `plugins_loaded` action hook.
   *
   * @since 1.0.0
   */
  public function init() {

    // Check if Elementor installed and activated
    if ( ! did_action( 'elementor/loaded' ) ) {
      add_action( 'admin_notices', array( $this, 'admin_notice_missing_main_plugin' ) );
      return;
    }

    // Check for required PHP version
    if ( version_compare( PHP_VERSION, self::MIN_PHP_VERSION, '<' ) ) {
      add_action( 'admin_notices', array( $this, 'admin_notice_minimum_php_version' ) );
      return;
    }

    // Add Files
    require_once plugin_dir_path( __FILE__ ) . 'classes/read-only.php';
    require_once plugin_dir_path( __FILE__ ) . 'classes/sub-label.php';
    require_once plugin_dir_path( __FILE__ ) . 'classes/length-validation.php';
    require_once plugin_dir_path( __FILE__ ) . 'classes/pattern-validation.php';
    
    // Init Features
    new FFEE_Forms_Readonly();
    new FFEE_Forms_Sub_Label();
    new FFEE_Forms_Length_Validation();
    new FFEE_Forms_Patterns_Validation();
  }

  /**
   * Admin notice
   *
   * Warning when the site doesn't have Elementor installed or activated.
   *
   * @since 1.0.0
   */
  public function admin_notice_missing_main_plugin() {
    if ( isset( $_GET['activate'] ) ) {
      unset( $_GET['activate'] );
    }

    $message = sprintf(
    /* translators: 1: Plugin name 2: Elementor */
      esc_html__( '"%1$s" requires "%2$s" to be installed and activated.', 'ffee-lang' ),
      '<strong>' . esc_html__( 'Form Field Enhancer for Elementor', 'ffee-lang' ) . '</strong>',
      '<strong>' . esc_html__( 'Elementor & Elementor pro', 'ffee-lang' ) . '</strong>'
    );

    printf( '<div class="notice notice-warning is-dismissible"><p>%1$s</p></div>', $message );
  }

  /**
   * Admin notice
   *
   * Warning when the site doesn't have a minimum required PHP version.
   *
   * @since 1.0
   */
  public function admin_notice_minimum_php_version() {
    if ( isset( $_GET['activate'] ) ) {
      unset( $_GET['activate'] );
    }

    $message = sprintf(
    /* translators: 1: Plugin name 2: PHP 3: Required PHP version */
      esc_html__( '"%1$s" requires "%2$s" version %3$s or greater.', 'ffee-lang' ),
      '<strong>' . esc_html__( 'Form Field Enhancer for Elementor', 'ffee-lang' ) . '</strong>',
      '<strong>' . esc_html__( 'PHP', 'ffee-lang' ) . '</strong>',
      self::MIN_PHP_VERSION
    );

    printf( '<div class="notice notice-warning is-dismissible"><p>%1$s</p></div>', $message );
  }

}
new FFCC_Controls();