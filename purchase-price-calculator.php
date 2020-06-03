<?php
/**
 * Plugin Name: Purchase Price Calculator
 * Plugin URI: https://wahyuwibowo.com/projects/purchase-price-calculator/
 * Description: Help you calculate the estimated home price that you can purchase.
 * Author: Wahyu Wibowo
 * Author URI: https://wahyuwibowo.com
 * Version: 1.0
 * Text Domain: purchase-price-calculator
 * Domain Path: languages
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) {
    exit;
}

class Purchase_Price_Calculator {
    
    private static $_instance = NULL;
    
    /**
     * Initialize all variables, filters and actions
     */
    public function __construct() {
        add_action( 'init',               array( $this, 'load_plugin_textdomain' ), 0 );
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
        add_filter( 'http_request_args',  array( $this, 'dont_update_plugin' ), 5, 2 );
        
        add_shortcode( 'purchase_price_calculator', array( $this, 'add_shortcode' ) );
    }
    
    /**
     * retrieve singleton class instance
     * @return instance reference to plugin
     */
    public static function instance() {
        if ( NULL === self::$_instance ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }
    
    public function load_plugin_textdomain() {
        $locale = is_admin() && function_exists( 'get_user_locale' ) ? get_user_locale() : get_locale();
        $locale = apply_filters( 'plugin_locale', $locale, 'purchase-price-calculator' );
        
        unload_textdomain( 'purchase-price-calculator' );
        load_textdomain( 'purchase-price-calculator', WP_LANG_DIR . '/purchase-price-calculator/purchase-price-calculator-' . $locale . '.mo' );
        load_plugin_textdomain( 'purchase-price-calculator', false, plugin_basename( dirname( __FILE__ ) ) . '/languages' );
    }
    
    public function dont_update_plugin( $r, $url ) {
        if ( 0 !== strpos( $url, 'https://api.wordpress.org/plugins/update-check/1.1/' ) ) {
            return $r; // Not a plugin update request. Bail immediately.
        }
        
        $plugins = json_decode( $r['body']['plugins'], true );
        unset( $plugins['plugins'][plugin_basename( __FILE__ )] );
        $r['body']['plugins'] = json_encode( $plugins );
        
        return $r;
    }
    
    public function enqueue_scripts() {
        wp_enqueue_script( 'purchase-price-calculator-frontend', plugin_dir_url( __FILE__ ) . 'assets/js/frontend.js', array( 'jquery' ) );
        wp_enqueue_style( 'purchase-price-calculator-frontend', plugin_dir_url( __FILE__ ) . 'assets/css/frontend.css' );
        
        wp_localize_script( 'purchase-price-calculator-frontend', 'Purchase_Price_Calculator', array(
            'ajaxurl' => admin_url( 'admin-ajax.php' ),
            'nonce'   => wp_create_nonce( 'purchase_price_calculator' ),
            'loading' => __( 'Loading...', 'purchase-price-calculator' )
        ) );
    }
    
    public function add_shortcode() {
        $output = 'calculator';
        
        return $output;
    }

}

Purchase_Price_Calculator::instance();
