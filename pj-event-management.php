<?php
/**
 * Plugin Name: PJ Event Management
 * Plugin URI: https://piyushjangid.in
 * Description: A comprehensive event management plugin with custom post type, frontend/backend management, Elementor widget, and shortcode functionality.
 * Version: 1.0
 * Author: Piyush Jangid
 * Author URI: https://piyushjangid.in
 * Text Domain: pj-event-management
 * Domain Path: /languages
 */

// If this file is called directly, abort.
if (!defined('ABSPATH')) {
    exit;
}

// Load compatibility class first
require_once dirname(__FILE__) . '/includes/class-pj-event-compatibility.php';

// Initialize compatibility class
$compatibility = new PJ_Event_Compatibility();
$compatibility->init();

// Define plugin constants
define('PJ_EVENT_MANAGEMENT_VERSION', '1.0');
define('PJ_EVENT_MANAGEMENT_PATH', PJ_Event_Compatibility::plugin_dir_path(__FILE__));
define('PJ_EVENT_MANAGEMENT_URL', PJ_Event_Compatibility::plugin_dir_url(__FILE__));
define('PJ_EVENT_MANAGEMENT_BASENAME', PJ_Event_Compatibility::plugin_basename(__FILE__));
define('PJ_EVENT_MANAGEMENT_FILE', __FILE__);

// Initialize the plugin when WordPress loads
PJ_Event_Compatibility::add_action('plugins_loaded', function() {
    // Include main plugin class
    require_once PJ_EVENT_MANAGEMENT_PATH . 'includes/class-pj-event-management.php';
    
    // Initialize the plugin
    $plugin = new PJ_Event_Management();
    $plugin->run();
});

// Activation hook
PJ_Event_Compatibility::register_activation_hook(__FILE__, 'pj_event_management_activate');
function pj_event_management_activate() {
    // Create default pages
    require_once PJ_EVENT_MANAGEMENT_PATH . 'includes/class-pj-event-management.php';
    $plugin = new PJ_Event_Management();
    $plugin->create_default_pages();
    
    // Flush rewrite rules after registering custom post type
    PJ_Event_Compatibility::flush_rewrite_rules();
}

// Deactivation hook
PJ_Event_Compatibility::register_deactivation_hook(__FILE__, 'pj_event_management_deactivate');
function pj_event_management_deactivate() {
    // Flush rewrite rules
    PJ_Event_Compatibility::flush_rewrite_rules();
}

// Uninstall hook
PJ_Event_Compatibility::register_uninstall_hook(__FILE__, 'pj_event_management_uninstall');
function pj_event_management_uninstall() {
    // Clean up if needed
}

/**
 * Create plugin pages manually if activation hook didn't work.
 * This can be called manually if needed.
 */
function pj_create_plugin_pages() {
    if (class_exists('PJ_Event_Management')) {
        $plugin = new PJ_Event_Management();
        $plugin->create_default_pages();
        return true;
    }
    return false;
}

// Enqueue scripts and styles
PJ_Event_Compatibility::add_action( 'wp_enqueue_scripts', 'pj_event_management_enqueue_scripts' );
function pj_event_management_enqueue_scripts() {
    PJ_Event_Compatibility::wp_enqueue_style( 'pj-event-management', PJ_EVENT_MANAGEMENT_URL . 'assets/css/pj-event-management.css', array(), PJ_EVENT_MANAGEMENT_VERSION );
    PJ_Event_Compatibility::wp_enqueue_script( 'pj-event-management', PJ_EVENT_MANAGEMENT_URL . 'assets/js/pj-event-management.js', array( 'jquery' ), PJ_EVENT_MANAGEMENT_VERSION, true );
    
    // Localize script for AJAX
    PJ_Event_Compatibility::wp_localize_script(
        'pj-event-management',
        'pj_event_management',
        array(
            'ajax_url' => PJ_Event_Compatibility::admin_url('admin-ajax.php'),
            'nonce' => PJ_Event_Compatibility::wp_create_nonce('pj-event-management-nonce')
        )
    );
    
    // Enqueue Elementor-specific styles if Elementor is active
    if ( PJ_Event_Compatibility::did_action( 'elementor/loaded' ) ) {
        PJ_Event_Compatibility::wp_enqueue_style( 'pj-event-elementor', PJ_EVENT_MANAGEMENT_URL . 'assets/css/pj-event-elementor.css', array(), PJ_EVENT_MANAGEMENT_VERSION );
    }
}

// Modify query for event archives to order by post date
PJ_Event_Compatibility::add_action( 'pre_get_posts', 'pj_event_management_modify_query' );
function pj_event_management_modify_query( $query ) {
    // Only modify main query on frontend for event archives
    if ( !PJ_Event_Compatibility::is_admin() && $query->is_main_query() && isset($query->query['post_type']) && $query->query['post_type'] === 'pj_event' ) {
        // Set orderby to post date
        $query->set( 'orderby', 'date' );
        // Default to descending order (newest first)
        if ( !isset($query->query['order']) ) {
            $query->set( 'order', 'DESC' );
        }
    }
} 