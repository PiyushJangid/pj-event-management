<?php
/**
 * Handle admin functionality for the Event Management plugin.
 */
class PJ_Event_Admin {

    /**
     * Initialize admin functionality.
     */
    public function init() {
        // Add user meta field for event management authorization
        add_action('show_user_profile', array($this, 'add_event_management_field'));
        add_action('edit_user_profile', array($this, 'add_event_management_field'));
        
        // Save user meta field
        add_action('personal_options_update', array($this, 'save_event_management_field'));
        add_action('edit_user_profile_update', array($this, 'save_event_management_field'));
        
        // Admin menu and settings
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'register_settings'));
        
        // Add custom capabilities to admin users by default
        add_action('admin_init', array($this, 'add_caps_to_admin'));
        
        // Add cache clear handling
        add_action('admin_init', array($this, 'handle_cache_clear'));
    }
    
    /**
     * Add event management field to user profile.
     *
     * @param WP_User $user User object.
     */
    public function add_event_management_field($user) {
        // Check if current user can edit this user
        if (!current_user_can('edit_user', $user->ID)) {
            return;
        }
        
        // Get current authorization status
        $authorized = get_user_meta($user->ID, '_pj_event_management_authorized', true);
        ?>
        <h3><?php _e('Event Management Authorization', 'pj-event-management'); ?></h3>
        <table class="form-table">
            <tr>
                <th><label for="pj_event_management_authorized"><?php _e('Authorized', 'pj-event-management'); ?></label></th>
                <td>
                    <input type="checkbox" name="pj_event_management_authorized" id="pj_event_management_authorized" value="1" <?php checked($authorized, '1'); ?> />
                    <span class="description"><?php _e('Authorize this user to manage events from the frontend.', 'pj-event-management'); ?></span>
                </td>
            </tr>
        </table>
        <?php
    }
    
    /**
     * Save event management field.
     *
     * @param int $user_id User ID.
     */
    public function save_event_management_field($user_id) {
        // Check if current user can edit this user
        if (!current_user_can('edit_user', $user_id)) {
            return;
        }
        
        // Update the user meta
        update_user_meta(
            $user_id,
            '_pj_event_management_authorized',
            isset($_POST['pj_event_management_authorized']) ? '1' : '0'
        );
    }
    
    /**
     * Add admin menu for plugin settings.
     */
    public function add_admin_menu() {
        add_submenu_page(
            'edit.php?post_type=pj_event',
            __('Event Management Settings', 'pj-event-management'),
            __('Settings', 'pj-event-management'),
            'manage_options',
            'pj-event-management-settings',
            array($this, 'settings_page')
        );
    }
    
    /**
     * Register settings for the plugin.
     */
    public function register_settings() {
        register_setting(
            'pj_event_management_settings',
            'pj_event_management_options',
            array(
                'sanitize_callback' => array($this, 'sanitize_settings'),
                'default' => array(
                    'events_per_page' => 10,
                    'enable_caching' => 1
                )
            )
        );
        
        add_settings_section(
            'pj_event_management_general',
            __('General Settings', 'pj-event-management'),
            array($this, 'general_settings_section_callback'),
            'pj-event-management-settings'
        );
        
        add_settings_field(
            'events_per_page',
            __('Events Per Page', 'pj-event-management'),
            array($this, 'events_per_page_callback'),
            'pj-event-management-settings',
            'pj_event_management_general'
        );
        
        add_settings_field(
            'enable_caching',
            __('Enable Caching', 'pj-event-management'),
            array($this, 'enable_caching_callback'),
            'pj-event-management-settings',
            'pj_event_management_general'
        );
    }
    
    /**
     * General settings section callback.
     */
    public function general_settings_section_callback() {
        echo '<p>' . __('Configure general settings for the Event Management plugin.', 'pj-event-management') . '</p>';
    }
    
    /**
     * Events per page callback.
     */
    public function events_per_page_callback() {
        $options = get_option('pj_event_management_options', array());
        $events_per_page = isset($options['events_per_page']) ? $options['events_per_page'] : 10;
        ?>
        <input type="number" name="pj_event_management_options[events_per_page]" min="1" max="100" value="<?php echo esc_attr($events_per_page); ?>" />
        <?php
    }
    
    /**
     * Enable caching callback.
     */
    public function enable_caching_callback() {
        $options = get_option('pj_event_management_options', array());
        $enable_caching = isset($options['enable_caching']) ? (bool) $options['enable_caching'] : true;
        ?>
        <input type="checkbox" name="pj_event_management_options[enable_caching]" value="1" <?php checked($enable_caching, true); ?> id="pj_enable_caching" />
        <label for="pj_enable_caching"><?php _e('Enable caching for better performance', 'pj-event-management'); ?></label>
        
        <p class="description"><?php _e('When enabled, plugin will cache event queries to improve performance. Disabling will ensure real-time data but may impact performance.', 'pj-event-management'); ?></p>
        
        <p class="description cache-status">
            <?php if ($enable_caching) : ?>
                <span class="cache-enabled-indicator enabled"></span>
                <?php _e('Caching is currently enabled.', 'pj-event-management'); ?>
            <?php else : ?>
                <span class="cache-enabled-indicator disabled"></span>
                <?php _e('Caching is currently disabled.', 'pj-event-management'); ?>
            <?php endif; ?>
        </p>
        <?php
    }
    
    /**
     * Validate settings.
     * 
     * @param array $input Settings input.
     * @return array Sanitized settings.
     */
    public function sanitize_settings($input) {
        $sanitized = array();
        
        // Sanitize events per page
        if (isset($input['events_per_page'])) {
            $sanitized['events_per_page'] = absint($input['events_per_page']);
            if ($sanitized['events_per_page'] < 1) {
                $sanitized['events_per_page'] = 10; // Default if invalid
            }
        }
        
        // Sanitize enable caching (checkbox)
        $sanitized['enable_caching'] = isset($input['enable_caching']) ? 1 : 0;
        
        // Clear cache if caching setting was changed
        $old_options = get_option('pj_event_management_options', array());
        $old_caching = isset($old_options['enable_caching']) ? (bool) $old_options['enable_caching'] : true;
        $new_caching = (bool) $sanitized['enable_caching'];
        
        if ($old_caching !== $new_caching) {
            $this->clear_events_cache();
        }
        
        return $sanitized;
    }
    
    /**
     * Clear events cache.
     */
    private function clear_events_cache() {
        global $wpdb;
        
        // Clear caches
        $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '%_transient_pj_events_%'");
        $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '%_transient_timeout_pj_events_%'");
        
        // Add an admin notice
        add_action('admin_notices', array($this, 'cache_cleared_notice'));
    }
    
    /**
     * Display cache cleared notice.
     */
    public function cache_cleared_notice() {
        ?>
        <div class="notice notice-success is-dismissible">
            <p><?php _e('Events cache has been cleared.', 'pj-event-management'); ?></p>
        </div>
        <?php
    }
    
    /**
     * Handle cache clearing via admin_init.
     */
    public function handle_cache_clear() {
        if (isset($_GET['action']) && 'clear_cache' === $_GET['action'] && 
            isset($_GET['_wpnonce']) && wp_verify_nonce($_GET['_wpnonce'], 'pj_clear_cache')) {
            $this->clear_events_cache();
            
            // Use wp_safe_redirect instead of wp_redirect for better security
            wp_safe_redirect(add_query_arg('cache-cleared', '1', remove_query_arg(array('action', '_wpnonce'))));
            exit;
        }
    }
    
    /**
     * Settings page callback.
     */
    public function settings_page() {
        // Show cache cleared message if needed
        if (isset($_GET['cache-cleared']) && '1' === $_GET['cache-cleared']) {
            ?>
            <div class="notice notice-success is-dismissible">
                <p><?php _e('Events cache has been cleared successfully.', 'pj-event-management'); ?></p>
            </div>
            <?php
        }
        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            
            <div class="pj-admin-actions">
                <a href="<?php echo wp_nonce_url(add_query_arg('action', 'clear_cache'), 'pj_clear_cache'); ?>" class="button">
                    <?php _e('Clear Events Cache', 'pj-event-management'); ?>
                </a>
            </div>
            
            <form action="options.php" method="post">
                <?php
                settings_fields('pj_event_management_settings');
                do_settings_sections('pj-event-management-settings');
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }
    
    /**
     * Add capabilities to admin users by default.
     */
    public function add_caps_to_admin() {
        $admin_role = get_role('administrator');
        
        if ($admin_role) {
            $admin_role->add_cap('create_pj_events');
            $admin_role->add_cap('edit_pj_events');
            $admin_role->add_cap('delete_pj_events');
            $admin_role->add_cap('publish_pj_events');
            $admin_role->add_cap('edit_others_pj_events');
            $admin_role->add_cap('delete_others_pj_events');
        }
    }
} 