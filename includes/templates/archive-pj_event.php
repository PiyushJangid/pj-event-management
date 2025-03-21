<?php
/**
 * Template for displaying event archives
 *
 * @package PJ_Event_Management
 */

get_header();
?>

<div class="pj-events-container">
    <div class="pj-events-header">
        <h1 class="pj-archive-title"><?php _e('Events', 'pj-event-management'); ?></h1>
    </div>

    <?php if (have_posts()) : ?>
        <div class="pj-events-grid pj-grid-3-col">
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
                
                // Calculate days until event
                $days_text = '';
                if (!empty($event_date)) {
                    $event_timestamp = strtotime($event_date);
                    $current_timestamp = current_time('timestamp');
                    $seconds_diff = $event_timestamp - $current_timestamp;
                    $days_diff = floor($seconds_diff / 86400); // 86400 seconds in a day
                    
                    if ($days_diff > 0) {
                        // Use simple string instead of _n() translation function
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
                        <?php if (has_post_thumbnail()) : ?>
                            <div class="pj-event-thumbnail">
                                <a href="<?php the_permalink(); ?>" aria-label="<?php echo esc_attr(get_the_title()); ?>">
                                    <?php the_post_thumbnail('medium', array('class' => 'pj-card-image')); ?>
                                    <?php if (!empty($days_text)) : ?>
                                    <span class="pj-event-days-badge"><?php echo esc_html($days_text); ?></span>
                                    <?php endif; ?>
                                </a>
                            </div>
                        <?php endif; ?>
                        
                        <div class="pj-event-content">
                            <?php if (!empty($event_status)) : ?>
                            <div class="pj-event-status" style="background-color: <?php echo esc_attr($status_color); ?>; width: fit-content;">
                                <?php echo esc_html($status_label); ?>
                            </div>
                            <?php endif; ?>
                            
                            <h2 class="pj-event-title">
                                <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                            </h2>
                            
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
                            
                            <div class="pj-event-excerpt">
                                <?php the_excerpt(); ?>
                            </div>
                            
                            <a href="<?php the_permalink(); ?>" class="pj-event-readmore">
                                <span class="pj-readmore-text"><?php _e('View Details', 'pj-event-management'); ?></span>
                                <i class="dashicons dashicons-arrow-right-alt" aria-hidden="true"></i>
                                <span class="screen-reader-text"><?php echo sprintf(__('View details for %s', 'pj-event-management'), get_the_title()); ?></span>
                            </a>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
        
        <div class="pj-events-pagination">
            <h2 class="screen-reader-text"><?php PJ_Event_Compatibility::_e('Events Navigation', 'pj-event-management'); ?></h2>
            <?php 
            echo PJ_Event_Compatibility::paginate_links(array(
                'type'         => 'list',
                'prev_text'    => '<span class="nav-title"><i class="dashicons dashicons-arrow-left-alt" aria-hidden="true"></i> ' . PJ_Event_Compatibility::__('Previous', 'pj-event-management') . '</span>',
                'next_text'    => '<span class="nav-title">' . PJ_Event_Compatibility::__('Next', 'pj-event-management') . ' <i class="dashicons dashicons-arrow-right-alt" aria-hidden="true"></i></span>',
                'end_size'     => 2,
                'mid_size'     => 1,
                'before_page_number' => '<span class="meta-nav screen-reader-text">' . PJ_Event_Compatibility::__('Page', 'pj-event-management') . ' </span>',
            ));
            ?>
        </div>
        
    <?php else : ?>
        <div class="pj-no-events">
            <p><?php _e('No events found.', 'pj-event-management'); ?></p>
        </div>
    <?php endif; ?>
</div>

<style>
/* Styles moved to main CSS file in assets/css/pj-event-management.css */
</style>

<?php
get_footer(); 