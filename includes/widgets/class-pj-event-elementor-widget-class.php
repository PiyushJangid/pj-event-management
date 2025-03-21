<?php
/**
 * PJ Event Elementor Widget Class
 */

// Exit if accessed directly
if (!defined("ABSPATH")) {
    exit;
}

/**
 * Class PJ_Event_Elementor_Widget
 */
class PJ_Event_Elementor_Widget extends \Elementor\Widget_Base {
    /**
     * Get widget name.
     *
     * @return string Widget name.
     */
    public function get_name() {
        return "pj_event_list";
    }

    /**
     * Get widget title.
     *
     * @return string Widget title.
     */
    public function get_title() {
        return __("PJ Events", "pj-event-management");
    }

    /**
     * Get widget icon.
     *
     * @return string Widget icon.
     */
    public function get_icon() {
        return "eicon-calendar";
    }

    /**
     * Get widget categories.
     *
     * @return array Widget categories.
     */
    public function get_categories() {
        return ["general"];
    }
    
    /**
     * Get widget keywords.
     *
     * @return array Widget keywords.
     */
    public function get_keywords() {
        return ["event", "events", "calendar", "schedule", "pj"];
    }

    /**
     * Register widget controls.
     */
    protected function register_controls() {
        // Content Section
        $this->start_controls_section(
            "content_section",
            [
                "label" => __("Content", "pj-event-management"),
                "tab" => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            "date_filter",
            [
                "label" => __("Date Filter", "pj-event-management"),
                "type" => \Elementor\Controls_Manager::SELECT,
                "default" => "upcoming",
                "options" => [
                    "upcoming" => __("Upcoming Events", "pj-event-management"),
                    "past" => __("Past Events", "pj-event-management"),
                    "all" => __("All Events", "pj-event-management"),
                ],
            ]
        );

        $this->add_control(
            "show_filter_toggle",
            [
                "label" => __("Show Filter Toggle", "pj-event-management"),
                "type" => \Elementor\Controls_Manager::SWITCHER,
                "label_on" => __("Show", "pj-event-management"),
                "label_off" => __("Hide", "pj-event-management"),
                "return_value" => "yes",
                "default" => "no",
                "description" => __("Show filter buttons to toggle between upcoming, past, and all events", "pj-event-management"),
            ]
        );

        $this->add_control(
            "events_count",
            [
                "label" => __("Number of Events", "pj-event-management"),
                "type" => \Elementor\Controls_Manager::NUMBER,
                "min" => 1,
                "max" => 50,
                "step" => 1,
                "default" => 9,
            ]
        );

        $this->add_control(
            "columns",
            [
                "label" => __("Columns", "pj-event-management"),
                "type" => \Elementor\Controls_Manager::SELECT,
                "default" => "3",
                "options" => [
                    "1" => __("1 Column", "pj-event-management"),
                    "2" => __("2 Columns", "pj-event-management"),
                    "3" => __("3 Columns", "pj-event-management"),
                    "4" => __("4 Columns", "pj-event-management"),
                ],
            ]
        );

        $this->add_control(
            "pagination_type",
            [
                "label" => __("Pagination", "pj-event-management"),
                "type" => \Elementor\Controls_Manager::SELECT,
                "default" => "none",
                "options" => [
                    "none" => __("None", "pj-event-management"),
                    "standard" => __("Standard", "pj-event-management"),
                    "infinite" => __("Infinite Scroll", "pj-event-management"),
                ],
            ]
        );

        $this->add_control(
            "show_date",
            [
                "label" => __("Show Date", "pj-event-management"),
                "type" => \Elementor\Controls_Manager::SWITCHER,
                "label_on" => __("Show", "pj-event-management"),
                "label_off" => __("Hide", "pj-event-management"),
                "return_value" => "yes",
                "default" => "yes",
            ]
        );

        $this->add_control(
            "show_time",
            [
                "label" => __("Show Time", "pj-event-management"),
                "type" => \Elementor\Controls_Manager::SWITCHER,
                "label_on" => __("Show", "pj-event-management"),
                "label_off" => __("Hide", "pj-event-management"),
                "return_value" => "yes",
                "default" => "yes",
            ]
        );

        $this->add_control(
            "show_location",
            [
                "label" => __("Show Location", "pj-event-management"),
                "type" => \Elementor\Controls_Manager::SWITCHER,
                "label_on" => __("Show", "pj-event-management"),
                "label_off" => __("Hide", "pj-event-management"),
                "return_value" => "yes",
                "default" => "yes",
            ]
        );

        $this->add_control(
            "show_excerpt",
            [
                "label" => __("Show Excerpt", "pj-event-management"),
                "type" => \Elementor\Controls_Manager::SWITCHER,
                "label_on" => __("Show", "pj-event-management"),
                "label_off" => __("Hide", "pj-event-management"),
                "return_value" => "yes",
                "default" => "yes",
            ]
        );

        $this->end_controls_section();

        // Style Section
        $this->start_controls_section(
            "style_section",
            [
                "label" => __("Style", "pj-event-management"),
                "tab" => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            "title_color",
            [
                "label" => __("Title Color", "pj-event-management"),
                "type" => \Elementor\Controls_Manager::COLOR,
                "default" => "",
                "selectors" => [
                    "{{WRAPPER}} .pj-event-widget-title" => "color: {{VALUE}};",
                ],
            ]
        );

        $this->add_control(
            "event_title_color",
            [
                "label" => __("Event Title Color", "pj-event-management"),
                "type" => \Elementor\Controls_Manager::COLOR,
                "default" => "",
                "selectors" => [
                    "{{WRAPPER}} .pj-event-title" => "color: {{VALUE}};",
                ],
            ]
        );

        $this->add_control(
            "meta_color",
            [
                "label" => __("Meta Info Color", "pj-event-management"),
                "type" => \Elementor\Controls_Manager::COLOR,
                "default" => "",
                "selectors" => [
                    "{{WRAPPER}} .pj-event-meta" => "color: {{VALUE}};",
                ],
            ]
        );

        $this->add_control(
            "button_color",
            [
                "label" => __("Button Color", "pj-event-management"),
                "type" => \Elementor\Controls_Manager::COLOR,
                "default" => "",
                "selectors" => [
                    "{{WRAPPER}} .pj-event-button" => "background-color: {{VALUE}};",
                ],
            ]
        );

        $this->add_control(
            "button_text_color",
            [
                "label" => __("Button Text Color", "pj-event-management"),
                "type" => \Elementor\Controls_Manager::COLOR,
                "default" => "",
                "selectors" => [
                    "{{WRAPPER}} .pj-event-button" => "color: {{VALUE}};",
                ],
            ]
        );

        $this->add_control(
            "event_spacing",
            [
                "label" => __("Spacing Between Events", "pj-event-management"),
                "type" => \Elementor\Controls_Manager::SLIDER,
                "size_units" => ["px"],
                "range" => [
                    "px" => [
                        "min" => 0,
                        "max" => 100,
                        "step" => 1,
                    ],
                ],
                "default" => [
                    "unit" => "px",
                    "size" => 20,
                ],
                "selectors" => [
                    "{{WRAPPER}} .pj-event-item" => "margin-bottom: {{SIZE}}{{UNIT}};",
                ],
            ]
        );

        $this->end_controls_section();
    }

    /**
     * Render widget output on the frontend.
     */
    protected function render() {
        $settings = $this->get_settings_for_display();
        
        // Build the shortcode attributes array
        $attributes = [
            "title" => isset($settings["title"]) ? $settings["title"] : "",
            "count" => isset($settings["events_count"]) ? $settings["events_count"] : 9,
            "columns" => isset($settings["columns"]) ? $settings["columns"] : "3",
            "date_filter" => isset($settings["date_filter"]) ? $settings["date_filter"] : "upcoming",
            "pagination" => isset($settings["pagination_type"]) ? $settings["pagination_type"] : "none",
            "show_date" => isset($settings["show_date"]) ? $settings["show_date"] : "yes",
            "show_time" => isset($settings["show_time"]) ? $settings["show_time"] : "yes",
            "show_location" => isset($settings["show_location"]) ? $settings["show_location"] : "yes",
            "show_excerpt" => isset($settings["show_excerpt"]) ? $settings["show_excerpt"] : "yes",
        ];
        
        // Add filter toggle option if enabled
        if (isset($settings["show_filter_toggle"]) && $settings["show_filter_toggle"] === "yes") {
            $attributes["show_filter_toggle"] = "yes";
        } else {
            $attributes["show_filter_toggle"] = "no";
        }
        
        // Convert attributes to shortcode string
        $shortcode_attributes = "";
        foreach ($attributes as $key => $value) {
            if (!empty($value)) {
                $shortcode_attributes .= " " . $key . "=\"" . esc_attr($value) . "\"";
            }
        }
        
        // Output the shortcode
        echo do_shortcode("[pj_upcoming_events" . $shortcode_attributes . "]");
    }
    
    /**
     * Render plain content (used for static content or migration)
     */
    public function render_plain_content() {
        $settings = $this->get_settings_for_display();
        
        // Build the shortcode attributes array
        $attributes = [
            "title" => isset($settings["title"]) ? $settings["title"] : "",
            "count" => isset($settings["events_count"]) ? $settings["events_count"] : 9,
            "columns" => isset($settings["columns"]) ? $settings["columns"] : "3",
            "date_filter" => isset($settings["date_filter"]) ? $settings["date_filter"] : "upcoming",
            "pagination" => isset($settings["pagination_type"]) ? $settings["pagination_type"] : "none",
            "show_date" => isset($settings["show_date"]) ? $settings["show_date"] : "yes",
            "show_time" => isset($settings["show_time"]) ? $settings["show_time"] : "yes",
            "show_location" => isset($settings["show_location"]) ? $settings["show_location"] : "yes",
            "show_excerpt" => isset($settings["show_excerpt"]) ? $settings["show_excerpt"] : "yes",
        ];
        
        // Add filter toggle option if enabled
        if (isset($settings["show_filter_toggle"]) && $settings["show_filter_toggle"] === "yes") {
            $attributes["show_filter_toggle"] = "yes";
        } else {
            $attributes["show_filter_toggle"] = "no";
        }
        
        // Convert attributes to shortcode string
        $shortcode_attributes = "";
        foreach ($attributes as $key => $value) {
            if (!empty($value)) {
                $shortcode_attributes .= " " . $key . "=\"" . esc_attr($value) . "\"";
            }
        }
        
        // Output the shortcode as text
        echo "[pj_upcoming_events" . $shortcode_attributes . "]";
    }
}