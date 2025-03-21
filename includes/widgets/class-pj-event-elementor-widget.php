<?php
/**
 * PJ Event Elementor Widget
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Register the widget with Elementor safely
 */
function pj_event_register_elementor_widget() {
    // Only register the widget if Elementor is available
    if (PJ_Event_Compatibility::did_action('elementor/loaded') && class_exists('\Elementor\Widget_Base')) {
        // For Elementor versions before 3.5.0
        PJ_Event_Compatibility::add_action('elementor/widgets/widgets_registered', function() {
            // Register the widget
            if (class_exists('\Elementor\Plugin')) {
                \Elementor\Plugin::instance()->widgets_manager->register_widget_type(new PJ_Event_Elementor_Widget());
            }
        });
        
        // For Elementor 3.5.0 and above
        PJ_Event_Compatibility::add_action('elementor/widgets/register', function($widgets_manager) {
            // Register the widget with the new method
            $widgets_manager->register(new PJ_Event_Elementor_Widget());
        });
    }
}
pj_event_register_elementor_widget();

/**
 * Class PJ_Event_Elementor_Widget
 * This only runs if Elementor is active
 */
class PJ_Event_Elementor_Widget extends \Elementor\Widget_Base {
    /**
     * Get widget name.
     *
     * @return string Widget name.
     */
    public function get_name() {
        return 'pj_event_list';
    }

    /**
     * Get widget title.
     *
     * @return string Widget title.
     */
    public function get_title() {
        return PJ_Event_Compatibility::__('PJ Events', 'pj-event-management');
    }

    /**
     * Get widget icon.
     *
     * @return string Widget icon.
     */
    public function get_icon() {
        return 'eicon-calendar';
    }

    /**
     * Get widget categories.
     *
     * @return array Widget categories.
     */
    public function get_categories() {
        return ['general'];
    }
    
    /**
     * Get widget keywords.
     *
     * @return array Widget keywords.
     */
    public function get_keywords() {
        return ['event', 'events', 'calendar', 'schedule', 'pj'];
    }

    public function __construct( $data = [], $args = null ) {
        parent::__construct( $data, $args );
        
        // Enqueue custom styles for Elementor widget
        wp_enqueue_style( 
            'pj-event-elementor', 
            PJ_EVENT_MANAGEMENT_URL . 'assets/css/pj-event-elementor.css',
            array(),
            PJ_EVENT_MANAGEMENT_VERSION
        );
    }

    /**
     * Register widget controls.
     */
    protected function register_controls() {
        // Check if we're in a valid Elementor context
        if (!method_exists($this, 'start_controls_section') || !class_exists('\Elementor\Controls_Manager')) {
            return;
        }
        
        // Get the default posts per page from options
        $options = PJ_Event_Compatibility::get_option('pj_event_management_options', array());
        $default_per_page = isset($options['events_per_page']) ? intval($options['events_per_page']) : 6;
        
        // Content Section
        $this->start_controls_section(
            'content_section',
            [
                'label' => PJ_Event_Compatibility::__('Content', 'pj-event-management'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'date_filter',
            [
                'label' => PJ_Event_Compatibility::__('Date Filter', 'pj-event-management'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => 'upcoming',
                'options' => [
                    'upcoming' => PJ_Event_Compatibility::__('Upcoming Events', 'pj-event-management'),
                    'past' => PJ_Event_Compatibility::__('Past Events', 'pj-event-management'),
                    'all' => PJ_Event_Compatibility::__('All Events', 'pj-event-management'),
                ],
            ]
        );

        $this->add_control(
            'show_filter_toggle',
            [
                'label' => PJ_Event_Compatibility::__('Show Filter Toggle', 'pj-event-management'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => PJ_Event_Compatibility::__('Yes', 'pj-event-management'),
                'label_off' => PJ_Event_Compatibility::__('No', 'pj-event-management'),
                'return_value' => 'yes',
                'default' => 'Yes',
            ]
        );

        $this->add_control(
            'per_page',
            [
                'label' => PJ_Event_Compatibility::__('Posts Per Page', 'pj-event-management'),
                'type' => \Elementor\Controls_Manager::NUMBER,
                'min' => 1,
                'max' => 100,
                'step' => 1,
                'default' => 6,
                'description' => PJ_Event_Compatibility::__('Number of events to display per page. Leave empty to use the default value from settings.', 'pj-event-management'),
            ]
        );

        $this->add_control(
            'columns',
            [
                'label' => PJ_Event_Compatibility::__('Columns', 'pj-event-management'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => '3',
                'options' => [
                    '1' => PJ_Event_Compatibility::__('1 Column', 'pj-event-management'),
                    '2' => PJ_Event_Compatibility::__('2 Columns', 'pj-event-management'),
                    '3' => PJ_Event_Compatibility::__('3 Columns', 'pj-event-management'),
                    '4' => PJ_Event_Compatibility::__('4 Columns', 'pj-event-management'),
                ],
            ]
        );

        $this->add_control(
            'pagination_type',
            [
                'label' => PJ_Event_Compatibility::__('Pagination', 'pj-event-management'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => 'none',
                'options' => [
                    'none' => PJ_Event_Compatibility::__('None', 'pj-event-management'),
                    'standard' => PJ_Event_Compatibility::__('Standard', 'pj-event-management'),
                    'infinite' => PJ_Event_Compatibility::__('Infinite Scroll', 'pj-event-management'),
                ],
                'description' => PJ_Event_Compatibility::__('Select pagination type. Infinite scroll will automatically load more events when users scroll.', 'pj-event-management'),
            ]
        );

        $this->add_control(
            'show_date',
            [
                'label' => PJ_Event_Compatibility::__('Show Date', 'pj-event-management'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => PJ_Event_Compatibility::__('Show', 'pj-event-management'),
                'label_off' => PJ_Event_Compatibility::__('Hide', 'pj-event-management'),
                'return_value' => 'yes',
                'default' => 'yes',
            ]
        );

        $this->add_control(
            'show_time',
            [
                'label' => PJ_Event_Compatibility::__('Show Time', 'pj-event-management'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => PJ_Event_Compatibility::__('Show', 'pj-event-management'),
                'label_off' => PJ_Event_Compatibility::__('Hide', 'pj-event-management'),
                'return_value' => 'yes',
                'default' => 'yes',
            ]
        );

        $this->add_control(
            'show_location',
            [
                'label' => PJ_Event_Compatibility::__('Show Location', 'pj-event-management'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => PJ_Event_Compatibility::__('Show', 'pj-event-management'),
                'label_off' => PJ_Event_Compatibility::__('Hide', 'pj-event-management'),
                'return_value' => 'yes',
                'default' => 'yes',
            ]
        );

        $this->add_control(
            'show_excerpt',
            [
                'label' => PJ_Event_Compatibility::__('Show Excerpt', 'pj-event-management'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => PJ_Event_Compatibility::__('Show', 'pj-event-management'),
                'label_off' => PJ_Event_Compatibility::__('Hide', 'pj-event-management'),
                'return_value' => 'yes',
                'default' => 'yes',
            ]
        );

        $this->end_controls_section();

        // Style Section
        $this->start_controls_section(
            'style_section',
            [
                'label' => PJ_Event_Compatibility::__('Style', 'pj-event-management'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'title_color',
            [
                'label' => PJ_Event_Compatibility::__('Title Color', 'pj-event-management'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '',
                'selectors' => [
                    '{{WRAPPER}} .pj-events-title' => 'color: {{VALUE}} !important;',
                    '{{WRAPPER}} .pj-archive-title' => 'color: {{VALUE}} !important;',
                    '{{WRAPPER}} .pj-related-title' => 'color: {{VALUE}} !important;',
                ],
            ]
        );

        $this->add_control(
            'event_title_color',
            [
                'label' => PJ_Event_Compatibility::__('Event Title Color', 'pj-event-management'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '',
                'selectors' => [
                    '{{WRAPPER}} .pj-event-title a' => 'color: {{VALUE}} !important;',
                ],
            ]
        );

        $this->add_control(
            'meta_color',
            [
                'label' => PJ_Event_Compatibility::__('Meta Info Color', 'pj-event-management'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '',
                'selectors' => [
                    '{{WRAPPER}} .pj-event-meta' => 'color: {{VALUE}} !important;',
                    '{{WRAPPER}} .pj-event-meta .meta-value' => 'color: {{VALUE}} !important;',
                ],
            ]
        );

        $this->add_control(
            'button_color',
            [
                'label' => PJ_Event_Compatibility::__('Button Color', 'pj-event-management'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '',
                'selectors' => [
                    '{{WRAPPER}} .pj-event-readmore' => 'background-color: {{VALUE}} !important;',
                ],
            ]
        );

        $this->add_control(
            'button_text_color',
            [
                'label' => PJ_Event_Compatibility::__('Button Text Color', 'pj-event-management'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '',
                'selectors' => [
                    '{{WRAPPER}} .pj-event-readmore' => 'color: {{VALUE}} !important;',
                    '{{WRAPPER}} .pj-event-readmore .pj-readmore-text' => 'color: {{VALUE}} !important;',
                ],
            ]
        );

        $this->add_control(
            'event_spacing',
            [
                'label' => PJ_Event_Compatibility::__('Spacing Between Events', 'pj-event-management'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 100,
                        'step' => 1,
                    ],
                ],
                'default' => [
                    'unit' => 'px',
                    'size' => 20,
                ],
                'selectors' => [
                    '{{WRAPPER}} .pj-events-grid' => 'grid-gap: {{SIZE}}{{UNIT}} !important;',
                ],
            ]
        );

        $this->end_controls_section();
    }

    /**
     * Render the widget output on the frontend.
     */
    protected function render() {
        $settings = $this->get_settings_for_display();
        
        // Build the shortcode
        $shortcode = '[pj_events';
        
        // Build the shortcode attributes
        $attrs = array(
            'per_page'             => !empty($settings['per_page']) ? $settings['per_page'] : '',
            'title'                => !empty($settings['title']) ? $settings['title'] : '',
            'columns'              => !empty($settings['columns']) ? $settings['columns'] : 3,
            'show_date'            => !empty($settings['show_date']) ? 'yes' : 'no',
            'show_time'            => !empty($settings['show_time']) ? 'yes' : 'no',
            'show_location'        => !empty($settings['show_location']) ? 'yes' : 'no',
            'show_excerpt'         => !empty($settings['show_excerpt']) ? 'yes' : 'no',
            'date_filter'          => !empty($settings['date_filter']) ? $settings['date_filter'] : 'upcoming',
            'pagination'           => !empty($settings['pagination_type']) ? $settings['pagination_type'] : 'infinite',
            'show_filter_toggle'   => !empty($settings['show_filter_toggle']) ? 'yes' : 'no',
        );
        
        // Build the shortcode string
        foreach ($attrs as $key => $value) {
            if ($value !== '') {
                $shortcode .= ' ' . $key . '="' . esc_attr($value) . '"';
            }
        }
        $shortcode .= ']';
        
        // Apply inline styles to ensure color controls work on this specific widget instance
        $widget_id = $this->get_id();
        
        // Get style settings
        $title_color = !empty($settings['title_color']) ? $settings['title_color'] : '';
        $event_title_color = !empty($settings['event_title_color']) ? $settings['event_title_color'] : '';
        $meta_color = !empty($settings['meta_color']) ? $settings['meta_color'] : '';
        $button_color = !empty($settings['button_color']) ? $settings['button_color'] : '';
        $button_text_color = !empty($settings['button_text_color']) ? $settings['button_text_color'] : '';
        $event_spacing = !empty($settings['event_spacing']['size']) ? $settings['event_spacing']['size'] : '';
        
        echo '<div class="pj-elementor-events-widget elementor-element-' . esc_attr($widget_id) . '">';
        
        // Apply inline styles for the specific widget instance
        if ($title_color || $event_title_color || $meta_color || $button_color || $button_text_color || $event_spacing) {
            echo '<style>
                /* Custom styling for this widget instance */
                .elementor-element-' . $widget_id . ' .pj-events-title,
                .elementor-element-' . $widget_id . ' .pj-archive-title,
                .elementor-element-' . $widget_id . ' .pj-related-title {
                    ' . ($title_color ? 'color: ' . $title_color . ' !important;' : '') . '
                }
                .elementor-element-' . $widget_id . ' .pj-event-title a {
                    ' . ($event_title_color ? 'color: ' . $event_title_color . ' !important;' : '') . '
                }
                .elementor-element-' . $widget_id . ' .pj-event-meta,
                .elementor-element-' . $widget_id . ' .pj-event-meta .meta-value,
                .elementor-element-' . $widget_id . ' .pj-event-meta i.dashicons {
                    ' . ($meta_color ? 'color: ' . $meta_color . ' !important;' : '') . '
                }
                .elementor-element-' . $widget_id . ' .pj-event-readmore {
                    ' . ($button_color ? 'background-color: ' . $button_color . ' !important;' : '') . '
                    ' . ($button_text_color ? 'color: ' . $button_text_color . ' !important;' : '') . '
                }
                .elementor-element-' . $widget_id . ' .pj-event-readmore .pj-readmore-text {
                    ' . ($button_text_color ? 'color: ' . $button_text_color . ' !important;' : '') . '
                }
                .elementor-element-' . $widget_id . ' .pj-events-grid {
                    ' . ($event_spacing ? 'grid-gap: ' . $event_spacing . 'px !important;' : '') . '
                }
            </style>';
        }
        
        // Output the widget
        echo PJ_Event_Compatibility::do_shortcode($shortcode);
        
        echo '</div>';
        
        // Add custom CSS for column responsiveness
        echo '<style>
        .pj-elementor-events-widget.elementor-element-' . $widget_id . ' {
            width: 100%;
        }
        
        @media (max-width: 768px) {
            .pj-elementor-events-widget.elementor-element-' . $widget_id . ' .pj-events-grid {
                grid-template-columns: 1fr !important;
            }
            
            .pj-elementor-events-widget.elementor-element-' . $widget_id . ' .pj-event-readmore {
                width: 100%;
                display: flex;
                justify-content: center;
            }
        }
        </style>';
    }
    
    /**
     * Render plain content (used for static content or migration)
     */
    public function render_plain_content() {
        $settings = $this->get_settings_for_display();
        
        // Build the shortcode attributes array
        $attributes = [
            'title' => $settings['title'],
            'per_page' => !empty($settings['per_page']) ? intval($settings['per_page']) : '',
            'columns' => $settings['columns'],
            'date_filter' => $settings['date_filter'],
            'pagination' => $settings['pagination_type'],
            'show_date' => $settings['show_date'],
            'show_time' => $settings['show_time'],
            'show_location' => $settings['show_location'],
            'show_excerpt' => $settings['show_excerpt'],
            'show_filter_toggle' => $settings['show_filter_toggle']
        ];
        
        // Convert attributes to shortcode string
        $shortcode_attributes = '';
        foreach ($attributes as $key => $value) {
            if (!empty($value) || $value === 0) {
                $shortcode_attributes .= ' ' . $key . '="' . PJ_Event_Compatibility::esc_attr($value) . '"';
            }
        }
        
        // Output the shortcode as text
        echo '[pj_events' . $shortcode_attributes . ']';
    }
} 