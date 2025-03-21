<?php
/**
 * Template for displaying single events
 *
 * @package PJ_Event_Management
 */

// Note: All styles for this template have been moved to the main CSS file at 
// assets/css/pj-event-management.css in the "Single Event Template Styles" section

get_header();
?>

<div class="pj-single-event-container">
    <?php while (have_posts()) : the_post();
        // Get event meta
        $event_date = get_post_meta(get_the_ID(), '_pj_event_date', true);
        $event_time = get_post_meta(get_the_ID(), '_pj_event_time', true);
        $event_location = get_post_meta(get_the_ID(), '_pj_event_location', true);
        
        // Format date and time
        $formatted_date = !empty($event_date) ? date_i18n(get_option('date_format'), strtotime($event_date)) : '';
        $formatted_time = !empty($event_time) ? date_i18n(get_option('time_format'), strtotime($event_time)) : '';
        
        // Calculate if event is upcoming, ongoing or past
        $today = current_time('Y-m-d');
        $event_status = '';
        
        if (!empty($event_date)) {
            if ($event_date > $today) {
                $event_status = 'upcoming';
                $status_label = __('Upcoming', 'pj-event-management');
                $status_color = '#34a853'; // Green
            } elseif ($event_date < $today) {
                $event_status = 'past';
                $status_label = __('Past', 'pj-event-management');
                $status_color = '#ea4335'; // Red
            } else {
                $event_status = 'today';
                $status_label = __('Today', 'pj-event-management');
                $status_color = '#4285f4'; // Blue
            }
        }
        
        // Get organizer information if available
        $event_organizer = get_post_meta(get_the_ID(), '_pj_event_organizer', true);
        $event_email = get_post_meta(get_the_ID(), '_pj_event_email', true);
        $event_phone = get_post_meta(get_the_ID(), '_pj_event_phone', true);
        $event_website = get_post_meta(get_the_ID(), '_pj_event_website', true);
    ?>
        <article id="post-<?php the_ID(); ?>" <?php post_class('pj-single-event'); ?>>
            <div class="pj-event-header-wrapper">
            <header class="pj-event-header">
                    <?php if (!empty($event_status)) : ?>
                    <div class="pj-event-status" style="background-color: <?php echo esc_attr($status_color); ?>; width: fit-content;">
                        <?php echo esc_html($status_label); ?>
                    </div>
                    <?php endif; ?>
                    
                <h1 class="pj-event-title"><?php the_title(); ?></h1>
                
                <div class="pj-event-meta">
                        <div class="pj-event-meta-primary">
                    <?php if (!empty($formatted_date)) : ?>
                        <div class="pj-event-date">
                                    <i class="dashicons dashicons-calendar-alt" aria-hidden="true"></i> 
                            <span class="meta-label"><?php _e('Date:', 'pj-event-management'); ?></span>
                                    <span class="meta-value"><?php echo esc_html($formatted_date); ?></span>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($formatted_time)) : ?>
                        <div class="pj-event-time">
                                    <i class="dashicons dashicons-clock" aria-hidden="true"></i> 
                            <span class="meta-label"><?php _e('Time:', 'pj-event-management'); ?></span>
                                    <span class="meta-value"><?php echo esc_html($formatted_time); ?></span>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($event_location)) : ?>
                        <div class="pj-event-location">
                                    <i class="dashicons dashicons-location" aria-hidden="true"></i> 
                            <span class="meta-label"><?php _e('Location:', 'pj-event-management'); ?></span>
                                    <span class="meta-value"><?php echo esc_html($event_location); ?></span>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <?php if (!empty($event_organizer) || !empty($event_email) || !empty($event_phone) || !empty($event_website)) : ?>
                        <div class="pj-event-meta-secondary">
                            <?php if (!empty($event_organizer)) : ?>
                                <div class="pj-event-organizer">
                                    <i class="dashicons dashicons-businessman" aria-hidden="true"></i> 
                                    <span class="meta-label"><?php _e('Organizer:', 'pj-event-management'); ?></span>
                                    <span class="meta-value"><?php echo esc_html($event_organizer); ?></span>
                                </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($event_email)) : ?>
                                <div class="pj-event-email">
                                    <i class="dashicons dashicons-email" aria-hidden="true"></i> 
                                    <span class="meta-label"><?php _e('Email:', 'pj-event-management'); ?></span>
                                    <span class="meta-value"><a href="mailto:<?php echo esc_attr($event_email); ?>"><?php echo esc_html($event_email); ?></a></span>
                                </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($event_phone)) : ?>
                                <div class="pj-event-phone">
                                    <i class="dashicons dashicons-phone" aria-hidden="true"></i> 
                                    <span class="meta-label"><?php _e('Phone:', 'pj-event-management'); ?></span>
                                    <span class="meta-value"><a href="tel:<?php echo esc_attr($event_phone); ?>"><?php echo esc_html($event_phone); ?></a></span>
                                </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($event_website)) : ?>
                                <div class="pj-event-website">
                                    <i class="dashicons dashicons-admin-links" aria-hidden="true"></i> 
                                    <span class="meta-label"><?php _e('Website:', 'pj-event-management'); ?></span>
                                    <span class="meta-value"><a href="<?php echo esc_url($event_website); ?>" target="_blank" rel="noopener"><?php echo esc_html($event_website); ?></a></span>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </header>

            <?php if (has_post_thumbnail()) : ?>
                <div class="pj-event-featured-image">
                        <?php the_post_thumbnail('large', array('class' => 'pj-event-image')); ?>
                </div>
            <?php endif; ?>
            </div>
            
            <div class="pj-event-content-wrapper">
            <div class="pj-event-content">
                <?php the_content(); ?>
            </div>
                
                <?php 
                // Show sharing links if social sharing is enabled
                $show_sharing = apply_filters('pj_event_show_sharing', true);
                if ($show_sharing) : 
                ?>
                <div class="pj-event-sharing">
                    <h3><?php _e('Share This Event', 'pj-event-management'); ?></h3>
                    <div class="pj-event-share-buttons">
                        <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode(get_permalink()); ?>" target="_blank" rel="noopener" class="pj-share-facebook">
                            <i class="dashicons dashicons-facebook-alt" aria-hidden="true"></i>
                            <span class="screen-reader-text"><?php _e('Share on Facebook', 'pj-event-management'); ?></span>
                        </a>
                        <a href="https://twitter.com/intent/tweet?url=<?php echo urlencode(get_permalink()); ?>&text=<?php echo urlencode(get_the_title()); ?>" target="_blank" rel="noopener" class="pj-share-twitter">
                            <i class="dashicons dashicons-twitter" aria-hidden="true"></i>
                            <span class="screen-reader-text"><?php _e('Share on Twitter', 'pj-event-management'); ?></span>
                        </a>
                        <a href="https://www.linkedin.com/shareArticle?mini=true&url=<?php echo urlencode(get_permalink()); ?>&title=<?php echo urlencode(get_the_title()); ?>" target="_blank" rel="noopener" class="pj-share-linkedin">
                            <i class="dashicons dashicons-linkedin" aria-hidden="true"></i>
                            <span class="screen-reader-text"><?php _e('Share on LinkedIn', 'pj-event-management'); ?></span>
                        </a>
                        <a href="mailto:?subject=<?php echo urlencode(get_the_title()); ?>&body=<?php echo urlencode(get_permalink()); ?>" class="pj-share-email">
                            <i class="dashicons dashicons-email-alt" aria-hidden="true"></i>
                            <span class="screen-reader-text"><?php _e('Share via Email', 'pj-event-management'); ?></span>
                        </a>
                    </div>
                </div>
                <?php endif; ?>
            
            <footer class="pj-event-footer">
                <?php
                // Show event navigation
                the_post_navigation(array(
                        'prev_text' => '<span class="nav-subtitle">' . __('Previous Event', 'pj-event-management') . '</span><span class="nav-title"><i class="dashicons dashicons-arrow-left-alt" aria-hidden="true"></i> %title</span>',
                        'next_text' => '<span class="nav-subtitle">' . __('Next Event', 'pj-event-management') . '</span><span class="nav-title">%title <i class="dashicons dashicons-arrow-right-alt" aria-hidden="true"></i></span>',
                ));
                ?>
            </footer>
            </div>
        </article>
    <?php endwhile; ?>
    
    <?php
    // Get related/upcoming events
    $frontend = new PJ_Event_Frontend();
    $upcoming_events = $frontend->get_upcoming_events(4);
    
    // Exclude current event
    $current_event_id = get_the_ID();
    $filtered_events = array();
    
    foreach ($upcoming_events as $event) {
        if ($event->ID != $current_event_id) {
            $filtered_events[] = $event;
        }
    }
    
    // Limit to 3 events
    $filtered_events = array_slice($filtered_events, 0, 3);
    
    if (!empty($filtered_events)) :
    ?>
        <div class="pj-related-events">
            <h2 class="pj-related-title"><?php _e('Related Events', 'pj-event-management'); ?></h2>
            
            <div class="pj-events-grid pj-grid-3-col">
                <?php foreach ($filtered_events as $event) : 
                    // Get event meta
                    $event_date = get_post_meta($event->ID, '_pj_event_date', true);
                    $event_time = get_post_meta($event->ID, '_pj_event_time', true);
                    $event_location = get_post_meta($event->ID, '_pj_event_location', true);
                    
                    // Format date and time
                    $formatted_date = !empty($event_date) ? date_i18n(get_option('date_format'), strtotime($event_date)) : '';
                    $formatted_time = !empty($event_time) ? date_i18n(get_option('time_format'), strtotime($event_time)) : '';
                    
                    // Calculate days until event
                    $days_text = '';
                    if (!empty($event_date)) {
                        $event_timestamp = strtotime($event_date);
                        $current_timestamp = current_time('timestamp');
                        $seconds_diff = $event_timestamp - $current_timestamp;
                        $days_diff = floor($seconds_diff / 86400); // 86400 seconds in a day
                        
                        if ($days_diff > 0) {
                            $days_text = $days_diff == 1 ? 
                                __('1 day away', 'pj-event-management') : 
                                $days_diff . ' ' . __('days away', 'pj-event-management');
                        } elseif ($days_diff == 0) {
                            $days_text = __('Today', 'pj-event-management');
                        }
                    }
                ?>
                    <div class="pj-event-card">
                        <div class="pj-event-card-inner">
                            <?php if (has_post_thumbnail($event->ID)) : ?>
                                <div class="pj-event-thumbnail">
                                    <a href="<?php echo get_permalink($event->ID); ?>" aria-label="<?php echo esc_attr(get_the_title($event->ID)); ?>">
                                        <?php echo get_the_post_thumbnail($event->ID, 'medium', array('class' => 'pj-card-image')); ?>
                                        <?php if (!empty($days_text)) : ?>
                                        <span class="pj-event-days-badge"><?php echo esc_html($days_text); ?></span>
                                        <?php endif; ?>
                                    </a>
                                </div>
                            <?php endif; ?>
                            
                            <div class="pj-event-content">
                                <h3 class="pj-event-title">
                                    <a href="<?php echo get_permalink($event->ID); ?>"><?php echo get_the_title($event->ID); ?></a>
                                </h3>
                                
                                <div class="pj-event-meta">
                                    <?php if (!empty($formatted_date)) : ?>
                                        <div class="pj-event-date">
                                            <i class="dashicons dashicons-calendar-alt" aria-hidden="true"></i> 
                                            <span class="meta-value"><?php echo esc_html($formatted_date); ?></span>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <?php if (!empty($formatted_time)) : ?>
                                        <div class="pj-event-time">
                                            <i class="dashicons dashicons-clock" aria-hidden="true"></i> 
                                            <span class="meta-value"><?php echo esc_html($formatted_time); ?></span>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <?php if (!empty($event_location)) : ?>
                                        <div class="pj-event-location">
                                            <i class="dashicons dashicons-location" aria-hidden="true"></i> 
                                            <span class="meta-value"><?php echo esc_html($event_location); ?></span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                
                                <a href="<?php echo get_permalink($event->ID); ?>" class="pj-event-readmore">
                                    <span class="pj-readmore-text"><?php _e('View Details', 'pj-event-management'); ?></span>
                                    <i class="dashicons dashicons-arrow-right-alt" aria-hidden="true"></i>
                                    <span class="screen-reader-text"><?php echo sprintf(__('View details for %s', 'pj-event-management'), $event->post_title); ?></span>
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php
get_footer(); 