<?php
/**
 * The main plugin class that loads and initializes all components.
 */
class PJ_Event_Management {

    /**
     * Initialize the plugin.
     */
    public function __construct() {
        $this->load_dependencies();
    }

    /**
     * Load required dependencies.
     */
    private function load_dependencies() {
        // Load compatibility class first
        require_once PJ_EVENT_MANAGEMENT_PATH . 'includes/class-pj-event-compatibility.php';
        
        // Load CPT
        require_once PJ_EVENT_MANAGEMENT_PATH . 'includes/class-pj-event-cpt.php';
        
        // Load admin functionality
        require_once PJ_EVENT_MANAGEMENT_PATH . 'includes/admin/class-pj-event-admin.php';
        
        // Load frontend functionality
        require_once PJ_EVENT_MANAGEMENT_PATH . 'includes/frontend/class-pj-event-frontend.php';
        
        // Load shortcodes
        require_once PJ_EVENT_MANAGEMENT_PATH . 'includes/class-pj-event-shortcodes.php';
    }

    /**
     * Run the plugin.
     */
    public function run() {
        // Initialize custom post type
        $cpt = new PJ_Event_CPT();
        $cpt->register();
        
        // Initialize admin functionality
        $admin = new PJ_Event_Admin();
        $admin->init();
        
        // Initialize frontend functionality
        $frontend = new PJ_Event_Frontend();
        $frontend->init();
        
        // Initialize shortcodes
        $shortcodes = new PJ_Event_Shortcodes();
        $shortcodes->register_shortcodes();
        
        // Register scripts and styles
        PJ_Event_Compatibility::add_action('wp_enqueue_scripts', array($this, 'register_assets'));
        PJ_Event_Compatibility::add_action('admin_enqueue_scripts', array($this, 'register_admin_assets'));
        
        // Add post title suffix for plugin pages
        PJ_Event_Compatibility::add_action('the_title', array($this, 'add_plugin_page_suffix'), 10, 2);
        PJ_Event_Compatibility::add_action('display_post_states', array($this, 'add_plugin_page_state'), 10, 2);
        
        // Check if Elementor is active
        if (PJ_Event_Compatibility::did_action('elementor/loaded')) {
            // Register hook for Elementor integration
            $this->register_elementor_integration();
        }
    }
    
    /**
     * Register Elementor integration hooks
     */
    private function register_elementor_integration() {
        // For Elementor versions before 3.5.0
        PJ_Event_Compatibility::add_action('elementor/widgets/widgets_registered', array($this, 'register_elementor_widget_legacy'));
        
        // For Elementor 3.5.0 and above
        PJ_Event_Compatibility::add_action('elementor/widgets/register', array($this, 'register_elementor_widget'));
    }
    
    /**
     * Register Elementor widget for Elementor 3.5.0+
     *
     * @param \Elementor\Widgets_Manager $widgets_manager Elementor widgets manager.
     */
    public function register_elementor_widget($widgets_manager) {
        // Include the widget file
        require_once PJ_EVENT_MANAGEMENT_PATH . 'includes/widgets/class-pj-event-elementor-widget.php';
        
        // Register the widget if the class exists
        if (PJ_Event_Compatibility::class_exists('PJ_Event_Elementor_Widget')) {
            // Check if widgets_manager has register method (for Elementor 3.5.0+)
            if (method_exists($widgets_manager, 'register')) {
                $widgets_manager->register(new PJ_Event_Elementor_Widget());
            }
        }
    }
    
    /**
     * Legacy method to register Elementor widget for versions before 3.5.0
     */
    public function register_elementor_widget_legacy() {
        // Include the widget file
        require_once PJ_EVENT_MANAGEMENT_PATH . 'includes/widgets/class-pj-event-elementor-widget.php';
        
        // Use a callback for safer Elementor integration
        if (PJ_Event_Compatibility::class_exists('PJ_Event_Elementor_Widget')) {
            // Create a safe callback that will run only if Elementor is available
            $register_callback = function($widgets_manager) {
                if (method_exists($widgets_manager, 'register_widget_type')) {
                    $widgets_manager->register_widget_type(new PJ_Event_Elementor_Widget());
                }
            };
            
            // Let Elementor handle the actual registration if it exists
            if (PJ_Event_Compatibility::did_action('elementor/widgets/widgets_registered')) {
                // Try to access widgets manager safely
                global $elementor;
                if (isset($elementor) && isset($elementor->widgets_manager)) {
                    $register_callback($elementor->widgets_manager);
                }
            }
        }
    }
    
    /**
     * Register frontend scripts and styles.
     */
    public function register_assets() {
        PJ_Event_Compatibility::wp_enqueue_style(
            'pj-event-management-css',
            PJ_EVENT_MANAGEMENT_URL . 'assets/css/pj-event-management.css',
            array(),
            PJ_EVENT_MANAGEMENT_VERSION
        );
        
        // Add accessibility CSS
        PJ_Event_Compatibility::wp_enqueue_style(
            'pj-event-accessibility-css',
            PJ_EVENT_MANAGEMENT_URL . 'assets/css/pj-event-accessibility.css',
            array('pj-event-management-css'),
            PJ_EVENT_MANAGEMENT_VERSION
        );
        
        PJ_Event_Compatibility::wp_enqueue_script(
            'pj-event-management-js',
            PJ_EVENT_MANAGEMENT_URL . 'assets/js/pj-event-management.js',
            array('jquery'),
            PJ_EVENT_MANAGEMENT_VERSION,
            true
        );
        
        // Localize script for AJAX
        PJ_Event_Compatibility::wp_localize_script(
            'pj-event-management-js',
            'pj_event_management',
            array(
                'ajax_url' => PJ_Event_Compatibility::admin_url('admin-ajax.php'),
                'nonce' => PJ_Event_Compatibility::wp_create_nonce('pj-event-management-nonce')
            )
        );
    }
    
    /**
     * Register admin scripts and styles.
     */
    public function register_admin_assets() {
        PJ_Event_Compatibility::wp_enqueue_style(
            'pj-event-management-admin-css',
            PJ_EVENT_MANAGEMENT_URL . 'assets/css/pj-event-management-admin.css',
            array(),
            PJ_EVENT_MANAGEMENT_VERSION
        );
        
        PJ_Event_Compatibility::wp_enqueue_script(
            'pj-event-management-admin-js',
            PJ_EVENT_MANAGEMENT_URL . 'assets/js/pj-event-management-admin.js',
            array('jquery'),
            PJ_EVENT_MANAGEMENT_VERSION,
            true
        );
    }
    
    /**
     * Create default pages for the plugin.
     */
    public function create_default_pages() {
        $pages = array(
            'events' => array(
                'title' => 'Events',
                'content' => '[pj_events columns="3" pagination="standard" show_filter_toggle="yes"]',
                'meta' => array(
                    '_pj_event_plugin_page' => 'events',
                )
            ),
            'events-management' => array(
                'title' => 'Event Management',
                'content' => '[pj_all_events_management]',
                'meta' => array(
                    '_pj_event_plugin_page' => 'management',
                )
            ),
            'add-event' => array(
                'title' => 'Add Event',
                'content' => '[pj_add_edit_event]',
                'meta' => array(
                    '_pj_event_plugin_page' => 'form',
                )
            )
        );
        
        foreach ($pages as $slug => $page_data) {
            // Check if page already exists
            $existing_page = PJ_Event_Compatibility::get_page_by_path($slug);
            
            if (!$existing_page) {
                // Create the page
                $page_id = PJ_Event_Compatibility::wp_insert_post(array(
                    'post_title'     => $page_data['title'],
                    'post_name'      => $slug,
                    'post_content'   => $page_data['content'],
                    'post_status'    => 'publish',
                    'post_type'      => 'page',
                    'comment_status' => 'closed'
                ));
                
                // Add page meta
                if ($page_id && !PJ_Event_Compatibility::is_wp_error($page_id)) {
                    foreach ($page_data['meta'] as $meta_key => $meta_value) {
                        PJ_Event_Compatibility::update_post_meta($page_id, $meta_key, $meta_value);
                    }
                }
            }
        }
    }
    
    /**
     * Add a suffix to plugin page titles in the admin.
     * 
     * @param string $title The page title
     * @param int $post_id The post ID
     * @return string Modified title
     */
    public function add_plugin_page_suffix($title, $post_id = 0) {
        // Simply return the original title without any modification
        return $title;
    }
    
    /**
     * Add post state for plugin pages in admin list.
     * 
     * @param array $post_states Array of post states
     * @param WP_Post $post Current post object
     * @return array Modified post states
     */
    public function add_plugin_page_state($post_states, $post) {
        if (PJ_Event_Compatibility::get_post_type($post->ID) !== 'page') {
            return $post_states;
        }
        
        $plugin_page = PJ_Event_Compatibility::get_post_meta($post->ID, '_pj_event_plugin_page', true);
        
        if (!empty($plugin_page)) {
            $post_states['pj_event_page'] = PJ_Event_Compatibility::__('PJ Events', 'pj-event-management');
        }
        
        return $post_states;
    }
} 