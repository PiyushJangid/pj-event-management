<?php
/**
 * Register the Events custom post type.
 */
class PJ_Event_CPT {

    /**
     * Register the custom post type.
     */
    public function register() {
        // Register Events post type
        add_action('init', array($this, 'register_events_post_type'));
        
        // Register Events meta boxes
        add_action('add_meta_boxes', array($this, 'register_meta_boxes'));
        
        // Save post meta
        add_action('save_post', array($this, 'save_event_meta'));
    }
    
    /**
     * Register the Events custom post type.
     */
    public function register_events_post_type() {
        $labels = array(
            'name'               => _x('Events', 'post type general name', 'pj-event-management'),
            'singular_name'      => _x('Event', 'post type singular name', 'pj-event-management'),
            'menu_name'          => _x('Events', 'admin menu', 'pj-event-management'),
            'name_admin_bar'     => _x('Event', 'add new on admin bar', 'pj-event-management'),
            'add_new'            => _x('Add New', 'event', 'pj-event-management'),
            'add_new_item'       => __('Add New Event', 'pj-event-management'),
            'new_item'           => __('New Event', 'pj-event-management'),
            'edit_item'          => __('Edit Event', 'pj-event-management'),
            'view_item'          => __('View Event', 'pj-event-management'),
            'all_items'          => __('All Events', 'pj-event-management'),
            'search_items'       => __('Search Events', 'pj-event-management'),
            'parent_item_colon'  => __('Parent Events:', 'pj-event-management'),
            'not_found'          => __('No events found.', 'pj-event-management'),
            'not_found_in_trash' => __('No events found in Trash.', 'pj-event-management')
        );
        
        $args = array(
            'labels'             => $labels,
            'description'        => __('Events post type for event management.', 'pj-event-management'),
            'public'             => true,
            'publicly_queryable' => true,
            'show_ui'            => true,
            'show_in_menu'       => true,
            'query_var'          => true,
            'rewrite'            => array('slug' => 'event'),
            'capability_type'    => 'post',
            'has_archive'        => true,
            'hierarchical'       => false,
            'menu_position'      => 5,
            'menu_icon'          => 'dashicons-calendar-alt',
            'supports'           => array('title', 'editor', 'thumbnail', 'excerpt', 'comments')
        );
        
        register_post_type('pj_event', $args);
    }
    
    /**
     * Register meta boxes for event details.
     */
    public function register_meta_boxes() {
        add_meta_box(
            'pj_event_details',
            __('Event Details', 'pj-event-management'),
            array($this, 'event_details_meta_box_callback'),
            'pj_event',
            'normal',
            'high'
        );
    }
    
    /**
     * Meta box callback for event details.
     *
     * @param WP_Post $post The post object.
     */
    public function event_details_meta_box_callback($post) {
        // Add nonce for security
        wp_nonce_field('pj_event_details_meta_box', 'pj_event_details_meta_box_nonce');
        
        // Retrieve current values
        $event_date = get_post_meta($post->ID, '_pj_event_date', true);
        $event_time = get_post_meta($post->ID, '_pj_event_time', true);
        $event_location = get_post_meta($post->ID, '_pj_event_location', true);
        
        ?>
        <div class="pj-event-meta-box">
            <div class="pj-event-meta-field">
                <label for="pj_event_date"><?php _e('Event Date', 'pj-event-management'); ?></label>
                <input type="date" id="pj_event_date" name="pj_event_date" value="<?php echo esc_attr($event_date); ?>" />
            </div>
            
            <div class="pj-event-meta-field">
                <label for="pj_event_time"><?php _e('Event Time', 'pj-event-management'); ?></label>
                <input type="time" id="pj_event_time" name="pj_event_time" value="<?php echo esc_attr($event_time); ?>" />
            </div>
            
            <div class="pj-event-meta-field">
                <label for="pj_event_location"><?php _e('Event Location', 'pj-event-management'); ?></label>
                <input type="text" id="pj_event_location" name="pj_event_location" value="<?php echo esc_attr($event_location); ?>" />
            </div>
        </div>
        <?php
    }
    
    /**
     * Save event meta when the post is saved.
     *
     * @param int $post_id The ID of the post being saved.
     */
    public function save_event_meta($post_id) {
        // Check if our nonce is set and verify it
        if (!isset($_POST['pj_event_details_meta_box_nonce']) || 
            !wp_verify_nonce($_POST['pj_event_details_meta_box_nonce'], 'pj_event_details_meta_box')) {
            return;
        }
        
        // Check the user's permissions
        if (isset($_POST['post_type']) && 'pj_event' == $_POST['post_type']) {
            if (!current_user_can('edit_posts')) {
                return;
            }
        }
        
        // Don't save during autosave
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        
        // Save event date
        if (isset($_POST['pj_event_date'])) {
            update_post_meta($post_id, '_pj_event_date', sanitize_text_field($_POST['pj_event_date']));
        }
        
        // Save event time
        if (isset($_POST['pj_event_time'])) {
            update_post_meta($post_id, '_pj_event_time', sanitize_text_field($_POST['pj_event_time']));
        }
        
        // Save event location
        if (isset($_POST['pj_event_location'])) {
            update_post_meta($post_id, '_pj_event_location', sanitize_text_field($_POST['pj_event_location']));
        }
    }
} 