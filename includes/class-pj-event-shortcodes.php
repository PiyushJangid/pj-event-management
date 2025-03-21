<?php
/**
 * Handle shortcodes for the Event Management plugin.
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class PJ_Event_Shortcodes {

    /**
     * Register all shortcodes
     */
    public function register_shortcodes() {
        PJ_Event_Compatibility::add_shortcode('pj_events', array($this, 'all_events_shortcode'));
        PJ_Event_Compatibility::add_shortcode('pj_all_events_management', array($this, 'all_events_management_shortcode'));
        PJ_Event_Compatibility::add_shortcode('pj_add_edit_event', array($this, 'add_edit_event_shortcode'));
    }
    
    /**
     * Shortcode to display upcoming events.
     *
     * @param array $atts Shortcode attributes.
     * @return string HTML output for upcoming events.
     */
    public function all_events_shortcode($atts) {
        // Get the default posts per page from options
        $options = PJ_Event_Compatibility::get_option('pj_event_management_options', array());
        $default_per_page = isset($options['events_per_page']) ? intval($options['events_per_page']) : '';
        
        $atts = PJ_Event_Compatibility::shortcode_atts(array(
            'per_page' => $default_per_page,
            'title' => '',
            'show_date' => 'yes',
            'show_time' => 'yes',
            'show_location' => 'yes',
            'show_excerpt' => 'yes',
            'columns' => 3,
            'date_format' => PJ_Event_Compatibility::get_option('date_format'),
            'time_format' => PJ_Event_Compatibility::get_option('time_format'),
            'date_filter' => 'upcoming',
            'pagination' => 'standard',
            'show_filter_toggle' => 'no'
        ), $atts);
        
        // Ensure per_page is an integer and has a valid value
        $atts['per_page'] = intval($atts['per_page']);
        if ($atts['per_page'] < 1) {
            $atts['per_page'] = $default_per_page;
        }
        
        // Generate a unique ID for this instance
        $unique_id = 'pj-events-' . uniqid();
        
        // Process date filter from URL if present
        if (isset($_GET['event_filter'])) {
            $atts['date_filter'] = PJ_Event_Compatibility::sanitize_text_field($_GET['event_filter']);
        }
        
        // Current page for pagination
        $paged = (PJ_Event_Compatibility::get_query_var('paged')) ? PJ_Event_Compatibility::get_query_var('paged') : 1;
        
        // Get today's date in Y-m-d format
        $today = date('Y-m-d');
        
        // Query args
        $args = array(
            'post_type' => 'pj_event',
            'posts_per_page' => $atts['per_page'],
            'paged' => $paged,
            'orderby' => 'date',
            'post_status' => 'publish'
        );
        
        // Add date filter
        if ($atts['date_filter'] === 'upcoming') {
            $args['order'] = 'DESC';
            $args['meta_query'] = array(
                array(
                    'key' => '_pj_event_date',
                    'value' => $today,
                    'compare' => '>=',
                    'type' => 'DATE'
                )
            );
            // If title is empty, set a default based on filter
            if (empty($atts['title'])) {
                $atts['title'] = PJ_Event_Compatibility::__('Upcoming Events', 'pj-event-management');
            }
        } elseif ($atts['date_filter'] === 'past') {
            $args['order'] = 'DESC';
            $args['meta_query'] = array(
                array(
                    'key' => '_pj_event_date',
                    'value' => $today,
                    'compare' => '<',
                    'type' => 'DATE'
                )
            );
            // If title is empty, set a default based on filter
            if (empty($atts['title'])) {
                $atts['title'] = PJ_Event_Compatibility::__('Past Events', 'pj-event-management');
            }
        } else {
            $args['order'] = 'DESC';
            // If title is empty, set a default based on filter
            if (empty($atts['title'])) {
                $atts['title'] = PJ_Event_Compatibility::__('All Events', 'pj-event-management');
            }
        }
        
        // Use custom query class if WP_Query is not available
        $query_class = PJ_Event_Compatibility::get_wp_query_class();
        if ($query_class) {
            $query = new $query_class($args);
            $events = $query->posts;
        } else {
            // Fallback
            $custom_query_class = PJ_Event_Compatibility::get_custom_query_class();
            $query = new $custom_query_class($args);
            $events = $query->posts;
        }
        
        ob_start();
        ?>
        <div id="<?php echo PJ_Event_Compatibility::esc_attr($unique_id); ?>" class="pj-upcoming-events">

            
            <?php if ($atts['show_filter_toggle'] === 'yes') : ?>
                <div class="pj-event-toggle-filter" role="group" aria-labelledby="<?php echo PJ_Event_Compatibility::esc_attr($unique_id . '-title'); ?>">
                    <?php 
                    // Get current URL without event_filter parameter
                    $base_url = PJ_Event_Compatibility::remove_query_arg('event_filter');
                    ?>
                    <div class="pj-events-filters">
                        <form method="get" class="pj-event-filter-form">
                            <label for="pj-event-filter-select" class="screen-reader-text"><?php PJ_Event_Compatibility::_e('Filter Events', 'pj-event-management'); ?></label>
                            <select id="pj-event-filter-select" name="event_filter" class="pj-event-filter-select">
                                <option value="upcoming" <?php echo $atts['date_filter'] === 'upcoming' ? 'selected' : ''; ?>><?php PJ_Event_Compatibility::_e('Upcoming Events', 'pj-event-management'); ?></option>
                                <option value="past" <?php echo $atts['date_filter'] === 'past' ? 'selected' : ''; ?>><?php PJ_Event_Compatibility::_e('Past Events', 'pj-event-management'); ?></option>
                                <option value="all" <?php echo $atts['date_filter'] === 'all' ? 'selected' : ''; ?>><?php PJ_Event_Compatibility::_e('All Events', 'pj-event-management'); ?></option>
                            </select>
                            <button type="submit" class="pj-event-filter-button">
                                <i class="dashicons dashicons-filter" aria-hidden="true"></i>
                                <?php PJ_Event_Compatibility::_e('Filter', 'pj-event-management'); ?>
                            </button>
                        </form>
                    </div>
                </div>
            <?php endif; ?>
            
            <div class="pj-events-grid-container">
                <?php if (empty($events)) : ?>
                    <p class="pj-no-events"><?php PJ_Event_Compatibility::_e('No events found.', 'pj-event-management'); ?></p>
                <?php else : ?>
                    <div class="pj-events-grid pj-grid-<?php echo intval($atts['columns']); ?>-col" role="list">
                        <?php foreach ($events as $event) : 
                            $event_date = PJ_Event_Compatibility::get_post_meta($event->ID, '_pj_event_date', true);
                            $event_time = PJ_Event_Compatibility::get_post_meta($event->ID, '_pj_event_time', true);
                            $event_location = PJ_Event_Compatibility::get_post_meta($event->ID, '_pj_event_location', true);
                            
                            // Format date and time
                            $formatted_date = !empty($event_date) ? PJ_Event_Compatibility::date_i18n($atts['date_format'], strtotime($event_date)) : '';
                            $formatted_time = !empty($event_time) ? PJ_Event_Compatibility::date_i18n($atts['time_format'], strtotime($event_time)) : '';

                            // Calculate if event is upcoming, ongoing or past
                            $today = date('Y-m-d');
                            $event_status = '';
                            
                            if (!empty($event_date)) {
                                if ($event_date > $today) {
                                    $event_status = 'upcoming';
                                    $status_label = PJ_Event_Compatibility::__('Upcoming', 'pj-event-management');
                                    $status_color = '#34a853'; // Green
                                } elseif ($event_date < $today) {
                                    $event_status = 'past';
                                    $status_label = PJ_Event_Compatibility::__('Past', 'pj-event-management');
                                    $status_color = '#ea4335'; // Red
                                } else {
                                    $event_status = 'today';
                                    $status_label = PJ_Event_Compatibility::__('Today', 'pj-event-management');
                                    $status_color = '#4285f4'; // Blue
                                }
                            }
                            
                            // Calculate days until event
                            $days_text = '';
                            if (!empty($event_date) && $event_date >= $today) {
                                $event_timestamp = strtotime($event_date);
                                $current_timestamp = time();
                                $seconds_diff = $event_timestamp - $current_timestamp;
                                $days_diff = floor($seconds_diff / 86400); // 86400 seconds in a day
                                
                                if ($days_diff > 0) {
                                    // Use simple string instead of _n() translation function
                                    $days_text = $days_diff == 1 ? 
                                        PJ_Event_Compatibility::__('1 day away', 'pj-event-management') : 
                                        $days_diff . ' ' . PJ_Event_Compatibility::__('days away', 'pj-event-management');
                                } elseif ($days_diff == 0) {
                                    $days_text = PJ_Event_Compatibility::__('Today', 'pj-event-management');
                                }
                            }
                        ?>
                            <div class="pj-event-card" role="listitem" data-event-id="<?php echo PJ_Event_Compatibility::esc_attr($event->ID); ?>">
                                <div class="pj-event-card-inner">
                                    <?php if (PJ_Event_Compatibility::has_post_thumbnail($event->ID)) : ?>
                                        <div class="pj-event-thumbnail">
                                            <a href="<?php echo PJ_Event_Compatibility::esc_url(PJ_Event_Compatibility::get_permalink($event->ID)); ?>" aria-label="<?php echo PJ_Event_Compatibility::esc_attr(sprintf(PJ_Event_Compatibility::__('View details for event: %s', 'pj-event-management'), $event->post_title)); ?>">
                                                <?php echo PJ_Event_Compatibility::get_the_post_thumbnail($event->ID, 'medium', array('class' => 'pj-card-image', 'alt' => PJ_Event_Compatibility::esc_attr($event->post_title))); ?>
                                                <?php if (!empty($days_text)) : ?>
                                                <span class="pj-event-days-badge"><?php echo PJ_Event_Compatibility::esc_html($days_text); ?></span>
                                                <?php endif; ?>
                                            </a>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <div class="pj-event-content">
                                        <?php if (!empty($event_status)) : ?>
                                        <div class="pj-event-status" style="background-color: <?php echo PJ_Event_Compatibility::esc_attr($status_color); ?>; width: fit-content;">
                                            <?php echo PJ_Event_Compatibility::esc_html($status_label); ?>
                                        </div>
                                        <?php endif; ?>

                                        <h3 class="pj-event-title">
                                            <a href="<?php echo PJ_Event_Compatibility::esc_url(PJ_Event_Compatibility::get_permalink($event->ID)); ?>"><?php echo PJ_Event_Compatibility::esc_html($event->post_title); ?></a>
                                        </h3>
                                        
                                        <div class="pj-event-meta">
                                            <?php if ('yes' === $atts['show_date'] && !empty($formatted_date)) : ?>
                                                <div class="pj-event-date" aria-label="<?php PJ_Event_Compatibility::esc_attr_e('Event date', 'pj-event-management'); ?>">
                                                    <i class="dashicons dashicons-calendar-alt" aria-hidden="true"></i> 
                                                    <span class="meta-value"><?php echo PJ_Event_Compatibility::esc_html($formatted_date); ?></span>
                                                </div>
                                            <?php endif; ?>
                                            
                                            <?php if ('yes' === $atts['show_time'] && !empty($formatted_time)) : ?>
                                                <div class="pj-event-time" aria-label="<?php PJ_Event_Compatibility::esc_attr_e('Event time', 'pj-event-management'); ?>">
                                                    <i class="dashicons dashicons-clock" aria-hidden="true"></i> 
                                                    <span class="meta-value"><?php echo PJ_Event_Compatibility::esc_html($formatted_time); ?></span>
                                                </div>
                                            <?php endif; ?>
                                            
                                            <?php if ('yes' === $atts['show_location'] && !empty($event_location)) : ?>
                                                <div class="pj-event-location" aria-label="<?php PJ_Event_Compatibility::esc_attr_e('Event location', 'pj-event-management'); ?>">
                                                    <i class="dashicons dashicons-location" aria-hidden="true"></i> 
                                                    <span class="meta-value"><?php echo PJ_Event_Compatibility::esc_html($event_location); ?></span>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        
                                        <?php if ('yes' === $atts['show_excerpt']) : ?>
                                            <div class="pj-event-excerpt">
                                                <?php echo PJ_Event_Compatibility::wp_trim_words(PJ_Event_Compatibility::get_the_excerpt($event), 15); ?>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <a href="<?php echo PJ_Event_Compatibility::esc_url(PJ_Event_Compatibility::get_permalink($event->ID)); ?>" class="pj-event-readmore">
                                            <span class="pj-readmore-text"><?php PJ_Event_Compatibility::_e('View Details', 'pj-event-management'); ?></span>
                                            <i class="dashicons dashicons-arrow-right-alt" aria-hidden="true"></i>
                                            <span class="screen-reader-text"><?php echo PJ_Event_Compatibility::esc_html(sprintf(PJ_Event_Compatibility::__('View details for %s', 'pj-event-management'), $event->post_title)); ?></span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                
                <?php if ($atts['pagination'] === 'standard' && $query->max_num_pages > 1) : ?>
                    <div class="pj-events-pagination" role="navigation" aria-label="<?php PJ_Event_Compatibility::esc_attr_e('Events pagination', 'pj-event-management'); ?>">
                        <?php
                        $big = 999999999; // need an unlikely integer
                        echo PJ_Event_Compatibility::paginate_links(array(
                            'base' => str_replace($big, '%#%', PJ_Event_Compatibility::esc_url(PJ_Event_Compatibility::get_pagenum_link($big))),
                            'format' => '?paged=%#%',
                            'current' => max(1, $paged),
                            'total' => $query->max_num_pages,
                            'prev_text' => '<span aria-hidden="true">&laquo;</span> <span class="screen-reader-text">' . PJ_Event_Compatibility::__('Previous page', 'pj-event-management') . '</span>',
                            'next_text' => '<span class="screen-reader-text">' . PJ_Event_Compatibility::__('Next page', 'pj-event-management') . '</span> <span aria-hidden="true">&raquo;</span>',
                            'aria_current' => 'page',
                        ));
                        ?>
                    </div>
                <?php elseif ($atts['pagination'] === 'infinite' && $query->max_num_pages > 1) : ?>
                    <div class="pj-events-infinite-scroll" data-page="<?php echo PJ_Event_Compatibility::esc_attr($paged); ?>" data-max="<?php echo PJ_Event_Compatibility::esc_attr($query->max_num_pages); ?>" data-loading="false">
                        <div class="pj-infinite-scroll-status">
                            <div class="pj-infinite-scroll-request">
                                <span class="spinner-text"><?php PJ_Event_Compatibility::_e('Loading more events...', 'pj-event-management'); ?></span>
                            </div>
                            <div class="pj-infinite-scroll-last"><?php PJ_Event_Compatibility::_e('No more events to display', 'pj-event-management'); ?></div>
                            <div class="pj-infinite-scroll-error"><?php PJ_Event_Compatibility::_e('Error loading events. Please refresh the page.', 'pj-event-management'); ?></div>
                        </div>
                    </div>
                    <noscript>
                        <div class="pj-events-pagination" role="navigation" aria-label="<?php PJ_Event_Compatibility::esc_attr_e('Events pagination', 'pj-event-management'); ?>">
                            <?php
                            $big = 999999999; // need an unlikely integer
                            echo PJ_Event_Compatibility::paginate_links(array(
                                'base' => str_replace($big, '%#%', PJ_Event_Compatibility::esc_url(PJ_Event_Compatibility::get_pagenum_link($big))),
                                'format' => '?paged=%#%',
                                'current' => max(1, $paged),
                                'total' => $query->max_num_pages,
                                'prev_text' => '<span aria-hidden="true">&laquo;</span> <span class="screen-reader-text">' . PJ_Event_Compatibility::__('Previous page', 'pj-event-management') . '</span>',
                                'next_text' => '<span class="screen-reader-text">' . PJ_Event_Compatibility::__('Next page', 'pj-event-management') . '</span> <span aria-hidden="true">&raquo;</span>',
                                'aria_current' => 'page',
                            ));
                            ?>
                        </div>
                    </noscript>
                <?php endif; ?>
            </div>
        </div>
        <?php
        
        // Add inline script to ensure proper dashicon styling
        $output = ob_get_clean();
        $output .= '<style>
        /* Fix dashicon underlines */
        .dashicons, 
        .dashicons-before:before,
        i.dashicons,
        span.dashicons,
        a .dashicons, 
        a:hover .dashicons,
        a:focus .dashicons,
        a:active .dashicons,
        .pj-event-readmore .dashicons,
        .pj-event-meta .dashicons {
            text-decoration: none !important;
            box-shadow: none !important;
            border-bottom: none !important;
        }
        
        /* Fix links with dashicons on hover */
        a:hover .dashicons,
        a:focus .dashicons,
        a:active .dashicons,
        .pj-event-readmore:hover .dashicons,
        .pj-event-readmore:focus .dashicons {
            text-decoration: none !important;
            box-shadow: none !important;
        }
        
        /* Status badge fix */
        .pj-event-status {
            display: inline-block !important;
            width: fit-content !important;
        }
        </style>';
        
        // Add inline script to help debug delete functionality
        $output .= '<script>
        jQuery(document).ready(function($) {
            // Debug delete button click
            $(document).on("click", ".pj-delete-event", function(e) {
                e.preventDefault();
                var eventId = $(this).data("id");
                console.log("Delete button clicked for event ID:", eventId);
                
                if (!eventId) {
                    console.error("No event ID found on delete button");
                    $("#pj-debug-info").html("Error: No event ID found on delete button").show();
                    return;
                }
                
                // Show delete modal
                $("#pj-delete-event-modal").fadeIn(200);
                window.PJEvents.eventToDelete = eventId;
            });
            
            // Debug confirm delete button
            $(document).on("click", "#pj-confirm-delete", function() {
                var eventId = window.PJEvents.eventToDelete;
                console.log("Confirm delete clicked for event ID:", eventId);
                
                if (!eventId) {
                    console.error("No event ID stored for deletion");
                    $("#pj-debug-info").html("Error: No event ID stored for deletion").show();
                    return;
                }
                
                // Show loading
                var $button = $(this);
                $button.prop("disabled", true).text("Deleting...");
                
                // AJAX delete request
                $.ajax({
                    url: pj_event_management.ajax_url,
                    type: "POST",
                    data: {
                        action: "pj_delete_event",
                        post_id: eventId,
                        nonce: pj_event_management.nonce
                    },
                    success: function(response) {
                        console.log("Delete response:", response);
                        $("#pj-delete-event-modal").fadeOut(200);
                        
                        if (response.success) {
                            // Remove the row
                            var $row = $("tr[data-event-id=\"" + eventId + "\"]");
                            if ($row.length) {
                                $row.fadeOut(300, function() {
                                    $(this).remove();
                                    // Check if table is empty
                                    if ($(".pj-events-table tbody tr").length === 0) {
                                        $(".pj-events-table").replaceWith("<p class=\"pj-no-events\">No events found.</p>");
                                    }
                                });
                            } else {
                                // If row not found, reload page
                                alert("Event deleted successfully. Refreshing page to update the list.");
                                window.location.reload();
                            }
                        } else {
                            alert(response.data.message || "Error deleting event");
                        }
                        
                        // Reset button and stored ID
                        $button.prop("disabled", false).text("Delete");
                        window.PJEvents.eventToDelete = null;
                    },
                    error: function(xhr, status, error) {
                        console.error("Delete error:", error);
                        $("#pj-delete-event-modal").fadeOut(200);
                        alert("Error deleting event: " + error);
                        $button.prop("disabled", false).text("Delete");
                        window.PJEvents.eventToDelete = null;
                    }
                });
            });
            
            // Handle cancel button
            $(document).on("click", "#pj-cancel-delete", function() {
                $("#pj-delete-event-modal").fadeOut(200);
                window.PJEvents.eventToDelete = null;
            });
            
            // Close modal when clicking outside
            $(document).on("click", "#pj-delete-event-modal", function(e) {
                if (e.target === this) {
                    $(this).fadeOut(200);
                    window.PJEvents.eventToDelete = null;
                }
            });
        });
        </script>';
        
        return $output;
    }
    
    /**
     * Shortcode to display all events with management options.
     *
     * @return string HTML output for events management.
     */
    public function all_events_management_shortcode($atts) {
        // Set 10 posts per page for this shortcode
        $atts = PJ_Event_Compatibility::shortcode_atts(array(
            'per_page' => 10,
            'title' => PJ_Event_Compatibility::__('Manage Events', 'pj-event-management'),
            'pagination' => 'standard'
        ), $atts);
        
        $frontend = new PJ_Event_Frontend();
        
        // Check if user is authorized
        if (!$frontend->is_user_authorized()) {
            return '<p>' . PJ_Event_Compatibility::__('You are not authorized to manage events.', 'pj-event-management') . '</p>';
        }
        
        // Current page
        $paged = (PJ_Event_Compatibility::get_query_var('paged')) ? PJ_Event_Compatibility::get_query_var('paged') : 1;
        
        // Get all events
        $args = array(
            'post_type' => 'pj_event',
            'posts_per_page' => $atts['per_page'],
            'paged' => $paged,
            'orderby' => 'date',
            'order' => 'DESC'
        );
        
        // Use custom query class if WP_Query is not available
        $query_class = PJ_Event_Compatibility::get_wp_query_class();
        if ($query_class) {
            $query = new $query_class($args);
            $events = $query->posts;
        } else {
            // Fallback
            $custom_query_class = PJ_Event_Compatibility::get_custom_query_class();
            $query = new $custom_query_class($args);
            $events = $query->posts;
        }
        
        ob_start();
        ?>
        <div class="pj-events-management">
            <?php if (!empty($atts['title'])) : ?>
                <h2 class="pj-events-title"><?php echo PJ_Event_Compatibility::esc_html($atts['title']); ?></h2>
            <?php endif; ?>
            
            <div class="pj-events-actions">
                <a href="<?php echo PJ_Event_Compatibility::esc_url(PJ_Event_Compatibility::get_permalink(PJ_Event_Compatibility::get_page_by_path('add-event'))); ?>" class="pj-add-event-button">
                    <i class="dashicons dashicons-plus-alt2"></i> <?php PJ_Event_Compatibility::_e('Add New Event', 'pj-event-management'); ?>
                </a>
            </div>
            
            <?php if (empty($events)) : ?>
                <div class="pj-no-events">
                    <i class="dashicons dashicons-calendar-alt"></i>
                    <p><?php PJ_Event_Compatibility::_e('No events found.', 'pj-event-management'); ?></p>
                    <a href="<?php echo PJ_Event_Compatibility::esc_url(PJ_Event_Compatibility::get_permalink(PJ_Event_Compatibility::get_page_by_path('add-event'))); ?>" class="pj-create-event-link">
                        <?php PJ_Event_Compatibility::_e('Create your first event', 'pj-event-management'); ?>
                    </a>
                </div>
            <?php else : ?>
                <div class="pj-events-table-container">
                    <table class="pj-events-table">
                        <thead>
                            <tr>
                                <th><?php PJ_Event_Compatibility::_e('Event', 'pj-event-management'); ?></th>
                                <th><?php PJ_Event_Compatibility::_e('Date', 'pj-event-management'); ?></th>
                                <th><?php PJ_Event_Compatibility::_e('Time', 'pj-event-management'); ?></th>
                                <th><?php PJ_Event_Compatibility::_e('Location', 'pj-event-management'); ?></th>
                                <th><?php PJ_Event_Compatibility::_e('Actions', 'pj-event-management'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($events as $event) : 
                                $event_date = PJ_Event_Compatibility::get_post_meta($event->ID, '_pj_event_date', true);
                                $event_time = PJ_Event_Compatibility::get_post_meta($event->ID, '_pj_event_time', true);
                                $event_location = PJ_Event_Compatibility::get_post_meta($event->ID, '_pj_event_location', true);
                                
                                // Format date and time
                                $formatted_date = !empty($event_date) ? PJ_Event_Compatibility::date_i18n(PJ_Event_Compatibility::get_option('date_format'), strtotime($event_date)) : '-';
                                $formatted_time = !empty($event_time) ? PJ_Event_Compatibility::date_i18n(PJ_Event_Compatibility::get_option('time_format'), strtotime($event_time)) : '-';
                                
                                // Calculate if event is upcoming, ongoing or past
                                $today = date('Y-m-d');
                                $event_status = '';
                                $status_class = '';
                                $status_label = '';
                                
                                if (!empty($event_date)) {
                                    if ($event_date > $today) {
                                        $event_status = 'upcoming';
                                        $status_class = 'pj-status-upcoming';
                                        $status_label = PJ_Event_Compatibility::__('Upcoming', 'pj-event-management');
                                    } elseif ($event_date < $today) {
                                        $event_status = 'past';
                                        $status_class = 'pj-status-past';
                                        $status_label = PJ_Event_Compatibility::__('Past', 'pj-event-management');
                                    } else {
                                        $event_status = 'today';
                                        $status_class = 'pj-status-today';
                                        $status_label = PJ_Event_Compatibility::__('Today', 'pj-event-management');
                                    }
                                }
                            ?>
                                <tr data-event-id="<?php echo PJ_Event_Compatibility::esc_attr($event->ID); ?>" class="<?php echo $status_class; ?>">
                                    <td class="pj-event-title-cell">
                                        <a href="<?php echo PJ_Event_Compatibility::esc_url(PJ_Event_Compatibility::get_permalink($event->ID)); ?>" class="pj-event-title-link">
                                            <?php echo PJ_Event_Compatibility::esc_html($event->post_title); ?>
                                        </a>
                                        <?php if (!empty($status_label)) : ?>
                                            <span class="pj-event-status-badge <?php echo $status_class; ?>"><?php echo $status_label; ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo PJ_Event_Compatibility::esc_html($formatted_date); ?></td>
                                    <td><?php echo PJ_Event_Compatibility::esc_html($formatted_time); ?></td>
                                    <td><?php echo PJ_Event_Compatibility::esc_html($event_location); ?></td>
                                    <td class="pj-event-actions">
                                        <a href="<?php echo PJ_Event_Compatibility::esc_url(PJ_Event_Compatibility::add_query_arg('event_id', $event->ID, PJ_Event_Compatibility::get_permalink(PJ_Event_Compatibility::get_page_by_path('add-event')))); ?>" class="pj-edit-button" aria-label="<?php echo PJ_Event_Compatibility::esc_attr(sprintf(PJ_Event_Compatibility::__('Edit %s', 'pj-event-management'), $event->post_title)); ?>">
                                            <i class="dashicons dashicons-edit"></i> <span class="button-text"><?php PJ_Event_Compatibility::_e('Edit', 'pj-event-management'); ?></span>
                                        </a>
                                        <a href="javascript:void(0);" class="pj-delete-button pj-delete-direct" onclick="pjDeleteEventDirect(<?php echo PJ_Event_Compatibility::esc_attr($event->ID); ?>, '<?php echo PJ_Event_Compatibility::esc_attr($event->post_title); ?>')" aria-label="<?php echo PJ_Event_Compatibility::esc_attr(sprintf(PJ_Event_Compatibility::__('Delete %s', 'pj-event-management'), $event->post_title)); ?>">
                                            <i class="dashicons dashicons-trash"></i> <span class="button-text"><?php PJ_Event_Compatibility::_e('Delete', 'pj-event-management'); ?></span>
                                        </a>
                                        <a href="<?php echo PJ_Event_Compatibility::esc_url(PJ_Event_Compatibility::get_permalink($event->ID)); ?>" class="pj-view-button" aria-label="<?php echo PJ_Event_Compatibility::esc_attr(sprintf(PJ_Event_Compatibility::__('View %s', 'pj-event-management'), $event->post_title)); ?>">
                                            <i class="dashicons dashicons-visibility"></i> <span class="button-text"><?php PJ_Event_Compatibility::_e('View', 'pj-event-management'); ?></span>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <?php if ($atts['pagination'] === 'standard' && $query->max_num_pages > 1) : ?>
                    <div class="pj-events-pagination" role="navigation" aria-label="<?php PJ_Event_Compatibility::esc_attr_e('Events pagination', 'pj-event-management'); ?>">
                        <?php
                        $big = 999999999; // need an unlikely integer
                        echo PJ_Event_Compatibility::paginate_links(array(
                            'base' => str_replace($big, '%#%', PJ_Event_Compatibility::esc_url(PJ_Event_Compatibility::get_pagenum_link($big))),
                            'format' => '?paged=%#%',
                            'current' => max(1, $paged),
                            'total' => $query->max_num_pages,
                            'prev_text' => '<i class="dashicons dashicons-arrow-left-alt2"></i><span class="screen-reader-text">' . PJ_Event_Compatibility::__('Previous page', 'pj-event-management') . '</span>',
                            'next_text' => '<span class="screen-reader-text">' . PJ_Event_Compatibility::__('Next page', 'pj-event-management') . '</span><i class="dashicons dashicons-arrow-right-alt2"></i>',
                            'aria_current' => 'page',
                        ));
                        ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
        
        <!-- Diagnostic script to check AJAX configuration -->
        <script type="text/javascript">
        jQuery(document).ready(function($) {
            console.log('Diagnostic script running');
            console.log('AJAX configuration:', window.pj_event_management);
            
            if (typeof window.pj_event_management === 'undefined') {
                console.error('AJAX configuration is missing! Adding fallback.');
                // Create fallback configuration
                window.pj_event_management = {
                    ajax_url: '<?php echo PJ_Event_Compatibility::admin_url('admin-ajax.php'); ?>',
                    nonce: '<?php echo PJ_Event_Compatibility::wp_create_nonce('pj-event-management-nonce'); ?>'
                };
                console.log('Created fallback configuration:', window.pj_event_management);
            }
            
            // Check for duplicate modals
            var $modals = $('#pj-delete-event-modal');
            console.log('Found ' + $modals.length + ' delete modals on page');
            
            if ($modals.length > 1) {
                console.error('Multiple delete modals found - this may cause issues');
                // Keep only the first one
                $modals.not(':first').remove();
                console.log('Removed duplicate modals, kept only the first one');
            }
            
            // Check for delete buttons
            var $deleteButtons = $('.pj-delete-event');
            console.log('Found ' + $deleteButtons.length + ' delete buttons');
            
            $deleteButtons.each(function(index) {
                var eventId = $(this).data('id');
                console.log('Button ' + index + ' has event ID: ' + eventId);
            });
        });
        </script>
        
        <div id="pj-delete-event-modal" style="display:none;">
            <div class="pj-modal-content">
                <h3><i class="dashicons dashicons-warning"></i> <?php PJ_Event_Compatibility::_e('Confirm Deletion', 'pj-event-management'); ?></h3>
                <p><?php PJ_Event_Compatibility::_e('Are you sure you want to delete this event? This action cannot be undone.', 'pj-event-management'); ?></p>
                <div class="pj-modal-actions">
                    <button id="pj-confirm-delete" class="button button-primary"><?php PJ_Event_Compatibility::_e('Delete', 'pj-event-management'); ?></button>
                    <button id="pj-cancel-delete" class="button"><?php PJ_Event_Compatibility::_e('Cancel', 'pj-event-management'); ?></button>
                </div>
                <?php PJ_Event_Compatibility::wp_nonce_field('pj-event-management-nonce', 'pj_event_nonce'); ?>
            </div>
        </div>
        
        <!-- Debug info for AJAX operations -->
        <div id="pj-debug-info" style="display:none;"></div>
        
        <!-- Dedicated inline script for delete functionality -->
        <script type="text/javascript">
        jQuery(document).ready(function($) {
            // Initialize event delete functionality
            console.log('Initializing event delete functionality');
            
            // Store the event ID to delete - ensure it's created before use
            if (typeof window.PJEvents === 'undefined') {
                window.PJEvents = {};
            }
            
            // Handle delete button click
            $(document).on('click', '.pj-delete-event', function(e) {
                e.preventDefault();
                var eventId = $(this).data('id');
                console.log('Delete button clicked for event ID:', eventId);
                
                if (!eventId) {
                    console.error('No event ID found on delete button');
                    alert('Error: Unable to identify the event to delete');
                    return;
                }
                
                // Store event ID and show modal
                window.PJEvents.eventToDelete = eventId;
                
                // Make sure modal exists and is properly shown
                var $modal = $('#pj-delete-event-modal');
                console.log('Modal element:', $modal.length ? 'Found' : 'Not found', $modal);
                
                if ($modal.length) {
                    // Force display style
                    $modal.css('display', 'flex').show();
                    console.log('Modal should now be visible');
                } else {
                    console.error('Delete modal not found in DOM');
                    alert('Error: Delete confirmation dialog not found');
                }
            });
            
            // Handle confirm delete
            $(document).on('click', '#pj-confirm-delete', function() {
                var eventId = window.PJEvents.eventToDelete;
                console.log('Confirm delete clicked for event ID:', eventId);
                
                if (!eventId) {
                    console.error('No event ID stored for deletion');
                    alert('Error: Unable to identify the event to delete');
                    return;
                }
                
                // Check if AJAX configuration is available
                if (typeof window.pj_event_management === 'undefined' || !window.pj_event_management.ajax_url) {
                    console.error('AJAX configuration not found. Plugin script not properly loaded.');
                    console.log('pj_event_management object:', window.pj_event_management);
                    alert('Error: Plugin AJAX configuration not found.');
                    return;
                }
                
                // Show loading
                var $button = $(this);
                $button.prop('disabled', true).text('Deleting...');
                
                // Send delete request
                $.ajax({
                    url: window.pj_event_management.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'pj_delete_event',
                        post_id: eventId,
                        nonce: window.pj_event_management.nonce
                    },
                    success: function(response) {
                        console.log('Delete response:', response);
                        
                        // Hide modal
                        $('#pj-delete-event-modal').fadeOut(200);
                        
                        if (response.success) {
                            // Find and remove the table row
                            var $row = $('tr[data-event-id="' + eventId + '"]');
                            
                            if ($row.length) {
                                $row.fadeOut(300, function() {
                                    $(this).remove();
                                    
                                    // If no events left, show empty message
                                    if ($('.pj-events-table tbody tr').length === 0) {
                                        $('.pj-events-table').replaceWith('<p class="pj-no-events">No events found.</p>');
                                    }
                                });
                            } else {
                                console.warn('Row not found for ID:', eventId);
                                alert('Event deleted successfully. Refreshing page to update the list.');
                                window.location.reload();
                            }
                        } else {
                            var errorMsg = response.data && response.data.message ? response.data.message : 'Error deleting event';
                            alert(errorMsg);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Delete error:', error);
                        console.log('XHR response:', xhr.responseText);
                        
                        // Hide modal
                        $('#pj-delete-event-modal').fadeOut(200);
                        
                        // Show error message
                        alert('Error deleting event: ' + error);
                    },
                    complete: function() {
                        // Reset button state
                        $button.prop('disabled', false).text('Delete');
                        window.PJEvents.eventToDelete = null;
                    }
                });
            });
            
            // Handle cancel button
            $(document).on('click', '#pj-cancel-delete', function() {
                $('#pj-delete-event-modal').fadeOut(200);
                window.PJEvents.eventToDelete = null;
            });
            
            // Close modal when clicking outside
            $(document).on('click', '#pj-delete-event-modal', function(e) {
                if (e.target === this) {
                    $(this).fadeOut(200);
                    window.PJEvents.eventToDelete = null;
                }
            });
        });
        </script>
        <?php
        
        // Add inline script to ensure proper dashicon styling and fix delete event functionality
        $output = ob_get_clean();
        $output .= '<style>
        /* Event Management Styles */
        .pj-events-management {
            font-family: var(--pj-font-family);
            max-width: 100%;
            margin-bottom: 2rem;
        }
        
        .pj-events-title {
            margin-bottom: 25px;
            font-size: 28px;
            font-weight: 600;
            position: relative;
            padding-bottom: 10px;
            color: var(--pj-text);
        }
        
        .pj-events-title:after {
            content: "";
            position: absolute;
            left: 0;
            bottom: 0;
            width: 50px;
            height: 3px;
            background-color: var(--pj-primary);
        }
        
        .pj-events-actions {
            margin-bottom: 25px;
            display: flex;
            justify-content: flex-start;
            gap: 10px;
            flex-wrap: wrap;
        }
        
        .pj-add-event-button {
            background-color: var(--pj-primary);
            color: white;
            border: none;
            font-size: 15px;
            font-weight: 500;
            border-radius: var(--pj-radius-sm);
            padding: 12px 18px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: var(--pj-transition);
            box-shadow: var(--pj-shadow);
            line-height: 1.4;
        }
        
        .pj-add-event-button:hover {
            background-color: var(--pj-primary-hover);
            color: white;
            transform: translateY(-2px);
            box-shadow: var(--pj-shadow-hover);
        }
        
        .pj-add-event-button .dashicons {
            font-size: 18px;
            width: 18px;
            height: 18px;
        }
        
        /* No events styling */
        .pj-no-events {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 40px 20px;
            background-color: var(--pj-background-light);
            border-radius: var(--pj-radius);
            text-align: center;
            margin: 0;
            border: 1px dashed var(--pj-border);
        }
        
        .pj-no-events .dashicons {
            font-size: 48px;
            width: 48px;
            height: 48px;
            color: var(--pj-text-light);
            margin-bottom: 15px;
        }
        
        .pj-no-events p {
            font-size: 16px;
            color: var(--pj-text-light);
            margin: 0 0 20px;
        }
        
        .pj-create-event-link {
            display: inline-flex;
            align-items: center;
            padding: 10px 18px;
            background-color: var(--pj-primary);
            color: white;
            text-decoration: none;
            border-radius: var(--pj-radius-sm);
            font-weight: 500;
            transition: var(--pj-transition);
        }
        
        .pj-create-event-link:hover {
            background-color: var(--pj-primary-hover);
            color: white;
            transform: translateY(-2px);
            box-shadow: var(--pj-shadow-hover);
        }
        
        /* Table styling */
        .pj-events-table-container {
            position: relative;
            margin-bottom: 30px;
            overflow-x: auto;
            border-radius: var(--pj-radius, 8px);
        }
        
        .pj-events-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            margin: 0;
            box-shadow: var(--pj-shadow, 0 2px 10px rgba(0,0,0,0.05));
            border-radius: var(--pj-radius, 8px);
            overflow: hidden;
        }
        
        .pj-events-table th {
            background: var(--pj-primary);
            color: white;
            font-weight: 600;
            text-align: left;
            padding: 15px 18px;
            font-size: 14px;
            letter-spacing: 0.05em;
            text-transform: uppercase;
            border-bottom: none;
            position: relative;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            text-shadow: 0 1px 1px rgba(0,0,0,0.2);
        }
        
        .pj-events-table th:first-child {
            border-top-left-radius: var(--pj-radius, 8px);
            padding-left: 20px;
        }
        
        .pj-events-table th:last-child {
            border-top-right-radius: var(--pj-radius, 8px);
        }
        
        .pj-events-table th:not(:last-child):after {
            content: "";
            position: absolute;
            right: 0;
            top: 25%;
            height: 50%;
            width: 1px;
            background-color: rgba(255, 255, 255, 0.2);
        }
        
        .pj-events-table td {
            padding: 16px 18px;
            border-bottom: 1px solid var(--pj-border, #e9ecef);
            vertical-align: middle;
            transition: background-color 0.2s ease;
            font-size: 14px;
            color: var(--pj-text-dark, #333);
        }
        
        .pj-events-table tr:last-child td {
            border-bottom: none;
        }
        
        .pj-events-table tr:nth-child(even) td {
            background-color: var(--pj-background-light, #f8f9fa);
        }
        
        
        .pj-events-table td:first-child {
            padding-left: 20px;
        }
        
        /* Event title and status */
        .pj-event-title-cell {
            position: relative;
        }
        
        .pj-event-title-link {
            font-weight: 600;
            color: var(--pj-text-dark, #333);
            text-decoration: none;
            transition: color 0.2s ease, transform 0.2s ease;
            display: inline-block;
            margin-bottom: 5px;
        }
        
        .pj-event-title-link:hover {
            color: var(--pj-primary, #4285f4);
            transform: translateX(2px);
        }
        
        .pj-event-status-badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
            color: white;
            margin-left: 8px;
        }
        
        .pj-status-upcoming.pj-event-status-badge {
            background-color: var(--pj-success);
        }
        
        .pj-status-past.pj-event-status-badge {
            background-color: var(--pj-danger);
        }
        
        .pj-status-today.pj-event-status-badge {
            background-color: var(--pj-primary);
        }
        
        .pj-status-upcoming td:first-child {
            border-left: 3px solid var(--pj-success);
        }
        
        .pj-status-past td:first-child {
            border-left: 3px solid var(--pj-danger);
        }
        
        .pj-status-today td:first-child {
            border-left: 3px solid var(--pj-primary);
        }
        
        /* Action buttons */
        .pj-event-actions {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            justify-content: flex-start;
        }
        
        .pj-edit-button,
        .pj-delete-button,
        .pj-view-button {
            padding: 8px 12px;
            font-size: 13px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            border-radius: var(--pj-radius-sm);
            text-decoration: none;
            transition: all 0.3s ease;
            font-weight: 500;
            box-shadow: var(--pj-shadow);
        }
        
        .pj-edit-button {
            background-color: var(--pj-primary-light);
            color: var(--pj-primary);
            border: 1px solid transparent;
        }
        
        .pj-edit-button:hover {
            background-color: var(--pj-primary);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(66, 133, 244, 0.25);
        }
        
        .pj-delete-button {
            background-color: #fff5f5;
            color: var(--pj-danger);
            border: 1px solid transparent;
        }
        
        .pj-delete-button:hover {
            background-color: var(--pj-danger);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(220, 53, 69, 0.25);
        }
        
        .pj-view-button {
            background-color: #f8f9fa;
            color: var(--pj-text);
            border: 1px solid transparent;
        }
        
        .pj-view-button:hover {
            background-color: #e9ecef;
            color: var(--pj-text-dark);
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        
        .pj-edit-button .dashicons,
        .pj-delete-button .dashicons,
        .pj-view-button .dashicons {
            font-size: 16px;
            width: 16px;
            height: 16px;
            transition: transform 0.2s ease;
        }
        
        .pj-edit-button:hover .dashicons {
            transform: rotate(15deg);
        }
        
        .pj-delete-button:hover .dashicons {
            transform: rotate(-15deg);
        }
        
        .pj-view-button:hover .dashicons {
            transform: scale(1.2);
        }
        
        /* Delete Modal */
        #pj-delete-event-modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.7);
            z-index: 99999;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .pj-modal-content {
            background-color: #fff;
            border-radius: var(--pj-radius, 8px);
            max-width: 500px;
            width: 90%;
            padding: 25px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            position: relative;
            z-index: 100000;
        }
        
        .pj-modal-content h3 {
            display: flex;
            align-items: center;
            margin-top: 0;
            color: #d63638;
        }
        
        .pj-modal-content h3 .dashicons {
            margin-right: 10px;
            color: #d63638;
        }
        
        .pj-modal-actions {
            display: flex;
            justify-content: flex-end;
            gap: 15px;
            margin-top: 20px;
        }
        
        .pj-modal-actions .button {
            cursor: pointer;
        }
        
        #pj-confirm-delete {
            background-color: #d63638;
            border-color: #d63638;
        }
        
        #pj-confirm-delete:hover {
            background-color: #b32d2e;
            border-color: #b32d2e;
        }
        
        /* Fix dashicon underlines */
        .dashicons, 
        .dashicons-before:before,
        i.dashicons,
        span.dashicons,
        a .dashicons, 
        a:hover .dashicons,
        a:focus .dashicons,
        a:active .dashicons,
        .pj-event-readmore .dashicons,
        .pj-event-meta .dashicons {
            text-decoration: none !important;
            box-shadow: none !important;
            border-bottom: none !important;
        }
        
        /* Responsive improvements */
        @media (max-width: 768px) {
            .pj-event-actions {
                flex-direction: column;
                gap: 8px;
            }
            
            .pj-edit-button, 
            .pj-delete-button, 
            .pj-view-button {
                width: 32px;
                height: 32px;
                padding: 0;
                justify-content: center;
            }
            
            .button-text {
                display: none;
            }
            
            .pj-edit-button .dashicons, 
            .pj-delete-button .dashicons, 
            .pj-view-button .dashicons {
                margin: 0;
            }
        }
        
        @media (max-width: 576px) {
            .pj-events-table th:nth-child(3),
            .pj-events-table td:nth-child(3),
            .pj-events-table th:nth-child(4),
            .pj-events-table td:nth-child(4) {
                display: none;
            }
            
            .pj-events-title {
                font-size: 24px;
            }
            
            .pj-modal-content {
                padding: 20px;
                width: 95%;
            }
        }
        </style>';
        
        // Add direct delete function - completely standalone
        $output .= '<script type="text/javascript">
        function pjDeleteEventDirect(eventId, eventTitle) {
            if (!eventId) {
                alert("Error: Unable to identify the event to delete");
                return;
            }
            
            // Confirm deletion
            if (!confirm("Are you sure you want to delete \"" + eventTitle + "\"? This action cannot be undone.")) {
                return;
            }
            
            console.log("Deleting event ID: " + eventId);
            
            // Get AJAX URL from page
            var ajaxUrl = "' . PJ_Event_Compatibility::admin_url('admin-ajax.php') . '";
            var nonce = "' . PJ_Event_Compatibility::wp_create_nonce('pj-event-management-nonce') . '";
            
            console.log("AJAX URL: " + ajaxUrl);
            console.log("Nonce: " + nonce);
            
            // Send AJAX request
            var xhr = new XMLHttpRequest();
            xhr.open("POST", ajaxUrl, true);
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
            
            xhr.onreadystatechange = function() {
                if (xhr.readyState === 4) {
                    console.log("Response received: ", xhr.responseText);
                    
                    try {
                        var response = JSON.parse(xhr.responseText);
                        
                        if (response.success) {
                            // Remove row or reload page
                            var row = document.querySelector("tr[data-event-id=\"" + eventId + "\"]");
                            if (row) {
                                row.style.display = "none";
                            } else {
                                window.location.reload();
                            }
                        } else {
                            var message = response.data && response.data.message ? response.data.message : "Error deleting event";
                            alert("Error: " + message);
                        }
                    } catch (e) {
                        console.error("Error parsing response", e);
                        alert("Error deleting event: Invalid response from server");
                    }
                }
            };
            
            xhr.onerror = function() {
                console.error("Request failed");
                alert("Error: Network request failed");
            };
            
            // Send the request
            xhr.send("action=pj_delete_event&post_id=" + eventId + "&nonce=" + nonce);
        }
        </script>';
        
        return $output;
    }
    
    /**
     * Shortcode to display add/edit event form.
     *
     * @return string HTML output for add/edit event form.
     */
    public function add_edit_event_shortcode() {
        $frontend = new PJ_Event_Frontend();
        
        // Check if user is authorized
        if (!$frontend->is_user_authorized()) {
            return '<p>' . PJ_Event_Compatibility::__('You are not authorized to manage events.', 'pj-event-management') . '</p>';
        }
        
        // Check if editing an existing event
        $event_id = isset($_GET['event_id']) ? intval($_GET['event_id']) : 0;
        $event = null;
        $is_edit = false;
        
        if ($event_id) {
            $event = PJ_Event_Compatibility::get_post($event_id);
            
            // Check if event exists and is of the correct type
            if (!$event || 'pj_event' !== $event->post_type) {
                return '<p>' . PJ_Event_Compatibility::__('Event not found.', 'pj-event-management') . '</p>';
            }
            
            // Check if user can edit this event
            if ($event->post_author != PJ_Event_Compatibility::get_current_user_id() && !PJ_Event_Compatibility::current_user_can('edit_others_posts')) {
                return '<p>' . PJ_Event_Compatibility::__('You are not authorized to edit this event.', 'pj-event-management') . '</p>';
            }
            
            $is_edit = true;
        }
        
        // Get event data if editing
        $event_title = $is_edit ? $event->post_title : '';
        $event_content = $is_edit ? $event->post_content : '';
        $event_date = $is_edit ? PJ_Event_Compatibility::get_post_meta($event_id, '_pj_event_date', true) : '';
        $event_time = $is_edit ? PJ_Event_Compatibility::get_post_meta($event_id, '_pj_event_time', true) : '';
        $event_location = $is_edit ? PJ_Event_Compatibility::get_post_meta($event_id, '_pj_event_location', true) : '';
        
        ob_start();
        ?>
        <div class="pj-event-form-container">
            <h2><?php echo $is_edit ? PJ_Event_Compatibility::__('Edit Event', 'pj-event-management') : PJ_Event_Compatibility::__('Add New Event', 'pj-event-management'); ?></h2>
            
            <form id="pj-event-form" class="pj-event-form">
                <?php PJ_Event_Compatibility::wp_nonce_field('pj-event-management-nonce', 'pj_event_nonce'); ?>
                <?php if ($is_edit) : ?>
                    <input type="hidden" name="post_id" value="<?php echo PJ_Event_Compatibility::esc_attr($event_id); ?>" />
                <?php endif; ?>
                
                <div class="pj-form-field">
                    <label for="pj_event_title"><?php PJ_Event_Compatibility::_e('Event Title', 'pj-event-management'); ?> <span class="required">*</span></label>
                    <input type="text" id="pj_event_title" name="title" value="<?php echo PJ_Event_Compatibility::esc_attr($event_title); ?>" required placeholder="<?php PJ_Event_Compatibility::esc_attr_e('Enter event title', 'pj-event-management'); ?>" />
                </div>
                
                <div class="pj-form-group">
                    <div class="pj-form-field">
                        <label for="pj_event_date"><?php PJ_Event_Compatibility::_e('Event Date', 'pj-event-management'); ?> <span class="required">*</span></label>
                        <input type="date" id="pj_event_date" name="date" value="<?php echo PJ_Event_Compatibility::esc_attr($event_date); ?>" required />
                    </div>
                    
                    <div class="pj-form-field">
                        <label for="pj_event_time"><?php PJ_Event_Compatibility::_e('Event Time', 'pj-event-management'); ?></label>
                        <input type="time" id="pj_event_time" name="time" value="<?php echo PJ_Event_Compatibility::esc_attr($event_time); ?>" />
                    </div>
                </div>
                
                <div class="pj-form-field">
                    <label for="pj_event_location"><?php PJ_Event_Compatibility::_e('Event Location', 'pj-event-management'); ?></label>
                    <input type="text" id="pj_event_location" name="location" value="<?php echo PJ_Event_Compatibility::esc_attr($event_location); ?>" placeholder="<?php PJ_Event_Compatibility::esc_attr_e('Enter event location', 'pj-event-management'); ?>" />
                </div>
                
                <div class="pj-form-field">
                    <label for="pj_event_content"><?php PJ_Event_Compatibility::_e('Event Description', 'pj-event-management'); ?></label>
                    <?php 
                    PJ_Event_Compatibility::wp_editor($event_content, 'pj_event_content', array(
                        'textarea_name' => 'content',
                        'media_buttons' => true,
                        'textarea_rows' => 10
                    )); 
                    ?>
                </div>
                
                <div class="pj-form-actions">
                    <button type="submit" id="pj-submit-event" class="button button-primary">
                        <i class="dashicons dashicons-saved"></i>
                        <?php echo $is_edit ? PJ_Event_Compatibility::__('Update Event', 'pj-event-management') : PJ_Event_Compatibility::__('Add Event', 'pj-event-management'); ?>
                    </button>
                    <a href="<?php echo PJ_Event_Compatibility::esc_url(PJ_Event_Compatibility::get_permalink(PJ_Event_Compatibility::get_page_by_path('all-events'))); ?>" class="button">
                        <i class="dashicons dashicons-no-alt"></i>
                        <?php PJ_Event_Compatibility::_e('Cancel', 'pj-event-management'); ?>
                    </a>
                </div>
                
                <div id="pj-form-response" class="pj-form-response" style="display:none;"></div>
            </form>
        </div>
        <?php
        
        return ob_get_clean();
    }
} 