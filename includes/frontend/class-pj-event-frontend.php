<?php
/**
 * Handle frontend functionality for the Event Management plugin.
 */
class PJ_Event_Frontend {

    /**
     * Initialize frontend functionality.
     */
    public function init() {
        // Add AJAX handlers for frontend event management
        PJ_Event_Compatibility::add_action('wp_ajax_pj_add_event', array($this, 'handle_add_event'));
        PJ_Event_Compatibility::add_action('wp_ajax_pj_edit_event', array($this, 'handle_edit_event'));
        PJ_Event_Compatibility::add_action('wp_ajax_pj_delete_event', array($this, 'handle_delete_event'));
        
        // Add template redirects for custom pages
        PJ_Event_Compatibility::add_action('template_redirect', array($this, 'handle_template_redirects'));
        
        // Add custom templates
        PJ_Event_Compatibility::add_filter('single_template', array($this, 'load_single_event_template'));
        PJ_Event_Compatibility::add_filter('archive_template', array($this, 'load_archive_event_template'));
        
        // Add body classes for grid layouts
        PJ_Event_Compatibility::add_filter('body_class', array($this, 'add_event_body_classes'));
        
        // Enqueue scripts and styles
        PJ_Event_Compatibility::add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
    }
    
    /**
     * Enqueue scripts and styles for the frontend.
     */
    public function enqueue_scripts() {
        // Always load styles
        PJ_Event_Compatibility::wp_enqueue_style('pj-event-management', 
            PJ_Event_Compatibility::plugin_dir_url(dirname(dirname(__FILE__))) . 'assets/css/pj-event-management.css', 
            array(), PJ_EVENT_MANAGEMENT_VERSION);
        
        // Check if the script is already registered/enqueued by the main plugin file
        // to prevent duplicate event handlers causing double form submissions
        if (!PJ_Event_Compatibility::wp_script_is('pj-event-management', 'registered') && 
            !PJ_Event_Compatibility::wp_script_is('pj-event-management', 'enqueued')) {
            
            PJ_Event_Compatibility::wp_enqueue_script('pj-event-management', 
                PJ_Event_Compatibility::plugin_dir_url(dirname(dirname(__FILE__))) . 'assets/js/pj-event-management.js', 
                array('jquery'), PJ_EVENT_MANAGEMENT_VERSION, true);
            
            // If we're enqueueing the script here, also localize it
            PJ_Event_Compatibility::wp_localize_script(
                'pj-event-management',
                'pj_event_management',
                array(
                    'ajax_url' => PJ_Event_Compatibility::admin_url('admin-ajax.php'),
                    'nonce' => PJ_Event_Compatibility::wp_create_nonce('pj-event-management-nonce')
                )
            );
        }
    }
    
    /**
     * Check if current page is an Elementor page with our widget
     *
     * @return bool True if page contains our Elementor widget
     */
    private function is_elementor_page_with_widget() {
        if (!class_exists('\Elementor\Plugin')) {
            return false;
        }
        
        // If we're not on a singular page, return false
        if (!is_singular()) {
            return false;
        }
        
        // Get the current post ID
        $post_id = get_the_ID();
        
        // Check if this is an Elementor-edited page
        if (!\Elementor\Plugin::$instance->db->is_built_with_elementor($post_id)) {
            return false;
        }
        
        // Get the Elementor data for this post
        $document = \Elementor\Plugin::$instance->documents->get($post_id);
        if (!$document) {
            return false;
        }
        
        $elementor_data = $document->get_elements_data();
        
        // Recursively search for our widget in the Elementor data
        return $this->find_widget_in_elements($elementor_data, 'pj_event_list');
    }
    
    /**
     * Recursively find a widget in Elementor elements data
     *
     * @param array $elements The elements to search in
     * @param string $widget_name The widget name to find
     * @return bool True if widget is found, false otherwise
     */
    private function find_widget_in_elements($elements, $widget_name) {
        if (!is_array($elements)) {
            return false;
        }
        
        foreach ($elements as $element) {
            if (isset($element['widgetType']) && $element['widgetType'] === $widget_name) {
                return true;
            }
            
            if (isset($element['elements']) && is_array($element['elements'])) {
                if ($this->find_widget_in_elements($element['elements'], $widget_name)) {
                    return true;
                }
            }
        }
        
        return false;
    }
    
    /**
     * Add body classes for event pages.
     *
     * @param array $classes Existing body classes.
     * @return array Modified body classes.
     */
    public function add_event_body_classes($classes) {
        if (is_singular('pj_event')) {
            $classes[] = 'pj-single-event-page';
        } elseif (is_post_type_archive('pj_event')) {
            $classes[] = 'pj-events-archive-page';
        }
        
        return $classes;
    }
    
    /**
     * Load custom template for single event.
     *
     * @param string $template Current template path.
     * @return string Modified template path.
     */
    public function load_single_event_template($template) {
        global $post;
        
        if ('pj_event' === $post->post_type) {
            $custom_template = plugin_dir_path(dirname(dirname(__FILE__))) . 'includes/templates/single-pj_event.php';
            
            if (file_exists($custom_template)) {
                return $custom_template;
            }
        }
        
        return $template;
    }
    
    /**
     * Load custom template for event archive.
     *
     * @param string $template Current template path.
     * @return string Modified template path.
     */
    public function load_archive_event_template($template) {
        if (is_post_type_archive('pj_event')) {
            $custom_template = plugin_dir_path(dirname(dirname(__FILE__))) . 'includes/templates/archive-pj_event.php';
            
            if (file_exists($custom_template)) {
                return $custom_template;
            }
        }
        
        return $template;
    }
    
    /**
     * Get upcoming events with optional caching.
     *
     * @param int $count Number of events to retrieve.
     * @return array Array of event posts.
     */
    public function get_upcoming_events($count = 5) {
        $options = get_option('pj_event_management_options', array());
        $enable_caching = isset($options['enable_caching']) ? (bool) $options['enable_caching'] : true;
        $cache_key = 'pj_events_' . $count;
        
        // Try to get from cache first
        if ($enable_caching) {
            $cached_events = get_transient($cache_key);
            if (false !== $cached_events) {
                return $cached_events;
            }
        }
        
        // Get current date in Y-m-d format
        $today = date('Y-m-d');
        
        // Query args for upcoming events
        $args = array(
            'post_type' => 'pj_event',
            'posts_per_page' => $count,
            'post_status' => 'publish',
            'orderby' => 'date',
            'order' => 'DESC',
            'meta_query' => array(
                array(
                    'key' => '_pj_event_date',
                    'value' => $today,
                    'compare' => '>=',
                    'type' => 'DATE'
                )
            )
        );
        
        $query = new WP_Query($args);
        $events = $query->posts;
        
        // Cache the results if caching is enabled
        if ($enable_caching) {
            set_transient($cache_key, $events, 6 * HOUR_IN_SECONDS);
        }
        
        return $events;
    }
    
    /**
     * Handle template redirects for custom pages.
     */
    public function handle_template_redirects() {
        global $post;
        
        if (is_singular('page') && $post) {
            // Check if current page is the all events page
            if (has_shortcode($post->post_content, 'pj_all_events')) {
                // Check if user is authorized to manage events
                if (!$this->is_user_authorized()) {
                    // Use wp_safe_redirect for better security
                    wp_safe_redirect(home_url());
                    exit;
                }
            }
            
            // Check if current page is the add/edit event page
            if (has_shortcode($post->post_content, 'pj_add_edit_event')) {
                // Check if user is authorized to manage events
                if (!$this->is_user_authorized()) {
                    // Use wp_safe_redirect for better security
                    wp_safe_redirect(home_url());
                    exit;
                }
            }
        }
    }
    
    /**
     * Check if current user is authorized to manage events.
     *
     * @return bool True if user is authorized, false otherwise.
     */
    public function is_user_authorized() {
        // Admins are always authorized
        if (current_user_can('manage_options')) {
            return true;
        }
        
        // Check user meta for authorization
        $user_id = get_current_user_id();
        if ($user_id) {
            $authorized = get_user_meta($user_id, '_pj_event_management_authorized', true);
            return '1' === $authorized;
        }
        
        return false;
    }
    
    /**
     * Handle adding a new event via AJAX.
     */
    public function handle_add_event() {
        // Check nonce
        if (!check_ajax_referer('pj-event-management-nonce', 'nonce', false)) {
            wp_send_json_error(array('message' => __('Security check failed.', 'pj-event-management')));
        }
        
        // Check authorization
        if (!$this->is_user_authorized()) {
            wp_send_json_error(array('message' => __('You are not authorized to add events.', 'pj-event-management')));
        }
        
        // Get form data
        $title = isset($_POST['title']) ? sanitize_text_field($_POST['title']) : '';
        $content = isset($_POST['content']) ? wp_kses_post($_POST['content']) : '';
        $date = isset($_POST['date']) ? sanitize_text_field($_POST['date']) : '';
        $time = isset($_POST['time']) ? sanitize_text_field($_POST['time']) : '';
        $location = isset($_POST['location']) ? sanitize_text_field($_POST['location']) : '';
        
        // Validate required fields
        if (empty($title) || empty($date)) {
            wp_send_json_error(array('message' => __('Title and date are required fields.', 'pj-event-management')));
        }
        
        // Create event post
        $post_data = array(
            'post_title' => $title,
            'post_content' => $content,
            'post_status' => 'publish',
            'post_type' => 'pj_event',
            'post_author' => get_current_user_id()
        );
        
        $post_id = wp_insert_post($post_data);
        
        if (is_wp_error($post_id)) {
            wp_send_json_error(array('message' => $post_id->get_error_message()));
        }
        
        // Add post meta
        update_post_meta($post_id, '_pj_event_date', $date);
        update_post_meta($post_id, '_pj_event_time', $time);
        update_post_meta($post_id, '_pj_event_location', $location);
        
        // Clear cache
        $this->clear_events_cache();
        
        wp_send_json_success(array(
            'message' => __('Event created successfully.', 'pj-event-management'),
            'post_id' => $post_id
        ));
    }
    
    /**
     * Handle editing an event via AJAX.
     */
    public function handle_edit_event() {
        // Check nonce
        if (!check_ajax_referer('pj-event-management-nonce', 'nonce', false)) {
            wp_send_json_error(array('message' => __('Security check failed.', 'pj-event-management')));
        }
        
        // Check authorization
        if (!$this->is_user_authorized()) {
            wp_send_json_error(array('message' => __('You are not authorized to edit events.', 'pj-event-management')));
        }
        
        // Get form data
        $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
        $title = isset($_POST['title']) ? sanitize_text_field($_POST['title']) : '';
        $content = isset($_POST['content']) ? wp_kses_post($_POST['content']) : '';
        $date = isset($_POST['date']) ? sanitize_text_field($_POST['date']) : '';
        $time = isset($_POST['time']) ? sanitize_text_field($_POST['time']) : '';
        $location = isset($_POST['location']) ? sanitize_text_field($_POST['location']) : '';
        
        // Validate required fields
        if (empty($post_id) || empty($title) || empty($date)) {
            wp_send_json_error(array('message' => __('Post ID, title, and date are required fields.', 'pj-event-management')));
        }
        
        // Check if post exists and is of the correct type
        $post = get_post($post_id);
        if (!$post || 'pj_event' !== $post->post_type) {
            wp_send_json_error(array('message' => __('Event not found.', 'pj-event-management')));
        }
        
        // Check if user can edit this post
        if ($post->post_author != get_current_user_id() && !current_user_can('edit_others_posts')) {
            wp_send_json_error(array('message' => __('You are not authorized to edit this event.', 'pj-event-management')));
        }
        
        // Update post
        $post_data = array(
            'ID' => $post_id,
            'post_title' => $title,
            'post_content' => $content
        );
        
        $updated = wp_update_post($post_data);
        
        if (is_wp_error($updated)) {
            wp_send_json_error(array('message' => $updated->get_error_message()));
        }
        
        // Update post meta
        update_post_meta($post_id, '_pj_event_date', $date);
        update_post_meta($post_id, '_pj_event_time', $time);
        update_post_meta($post_id, '_pj_event_location', $location);
        
        // Clear cache
        $this->clear_events_cache();
        
        wp_send_json_success(array(
            'message' => __('Event updated successfully.', 'pj-event-management'),
            'post_id' => $post_id
        ));
    }
    
    /**
     * Handle deleting an event via AJAX.
     */
    public function handle_delete_event() {
        // Check nonce
        if (!check_ajax_referer('pj-event-management-nonce', 'nonce', false)) {
            wp_send_json_error(array('message' => __('Security check failed.', 'pj-event-management')));
        }
        
        // Check authorization
        if (!$this->is_user_authorized()) {
            wp_send_json_error(array(
                'message' => __('You are not authorized to delete events.', 'pj-event-management'),
                'debug' => 'User authorization failed'
            ));
        }
        
        // Get post ID
        $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
        
        if (empty($post_id)) {
            wp_send_json_error(array(
                'message' => __('Post ID is required.', 'pj-event-management'),
                'debug' => 'Empty post ID'
            ));
        }
        
        // Check if post exists and is of the correct type
        $post = get_post($post_id);
        if (!$post) {
            wp_send_json_error(array(
                'message' => __('Event not found. Invalid post ID.', 'pj-event-management'),
                'debug' => 'Post not found',
                'post_id' => $post_id
            ));
        }
        
        if ('pj_event' !== $post->post_type) {
            wp_send_json_error(array(
                'message' => __('Invalid post type. Expected pj_event but got: ', 'pj-event-management') . $post->post_type,
                'debug' => 'Invalid post type',
                'post_type' => $post->post_type
            ));
        }
        
        // Check if user can delete this post
        if ($post->post_author != get_current_user_id() && !current_user_can('delete_others_posts')) {
            wp_send_json_error(array(
                'message' => __('You are not authorized to delete this event.', 'pj-event-management'),
                'debug' => 'User not authorized to delete this post',
                'author' => $post->post_author,
                'current_user' => get_current_user_id()
            ));
        }
        
        // Delete the post
        $deleted = wp_delete_post($post_id, true);
        
        if (!$deleted) {
            wp_send_json_error(array(
                'message' => __('Failed to delete event. WordPress returned a falsy result.', 'pj-event-management'),
                'debug' => 'wp_delete_post returned false',
                'post_id' => $post_id
            ));
        }
        
        // Clear cache
        $this->clear_events_cache();
        
        wp_send_json_success(array(
            'message' => __('Event deleted successfully.', 'pj-event-management'),
            'post_id' => $post_id
        ));
    }
    
    /**
     * Clear events cache.
     */
    private function clear_events_cache() {
        global $wpdb;
        
        $options = get_option('pj_event_management_options', array());
        $enable_caching = isset($options['enable_caching']) ? (bool) $options['enable_caching'] : true;
        
        // Even if caching is disabled, we should clear any existing cache
        // Delete all transients related to events
        $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '%_transient_pj_events_%'");
        $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '%_transient_timeout_pj_events_%'");
    }
} 