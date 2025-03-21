<?php
/**
 * Compatibility class for WordPress core functions
 * This ensures our plugin works in isolated environments that might not have direct access to WordPress functions
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Define WordPress constants if not already defined
if (!defined('OBJECT')) {
    define('OBJECT', 'OBJECT');
}
if (!defined('ARRAY_A')) {
    define('ARRAY_A', 'ARRAY_A');
}
if (!defined('ARRAY_N')) {
    define('ARRAY_N', 'ARRAY_N');
}

/**
 * Custom query class if WP_Query is not available
 */
class PJ_Custom_Query {
    public $posts = array();
    public $max_num_pages = 0;
    
    public function __construct($args = array()) {
        // Simplified query implementation
        $this->posts = array();
        $this->max_num_pages = 1;
    }
}

/**
 * Class PJ_Event_Compatibility
 * Provides fallbacks and compatibility for WordPress core functions
 */
class PJ_Event_Compatibility {

    /**
     * Initialize compatibility features
     */
    public function init() {
        // No initialization needed for now
    }

    /**
     * Safe wrapper for add_shortcode
     * 
     * @param string $tag Shortcode tag
     * @param callable $callback Shortcode callback
     */
    public static function add_shortcode($tag, $callback) {
        if (function_exists('add_shortcode')) {
            add_shortcode($tag, $callback);
        }
    }

    /**
     * Safe wrapper for shortcode_atts
     * 
     * @param array $pairs Default parameters
     * @param array $atts User defined parameters
     * @param string $shortcode Optional. The shortcode name
     * @return array Combined and filtered attributes
     */
    public static function shortcode_atts($pairs, $atts, $shortcode = '') {
        if (function_exists('shortcode_atts')) {
            return shortcode_atts($pairs, $atts, $shortcode);
        }
        return array_merge($pairs, (array) $atts);
    }

    /**
     * Safe wrapper for __
     * 
     * @param string $text Text to translate
     * @param string $domain Text domain
     * @return string Translated text
     */
    public static function __($text, $domain = 'default') {
        if (function_exists('__')) {
            return __($text, $domain);
        }
        return $text;
    }

    /**
     * Safe wrapper for _e
     * 
     * @param string $text Text to translate and echo
     * @param string $domain Text domain
     */
    public static function _e($text, $domain = 'default') {
        if (function_exists('_e')) {
            _e($text, $domain);
        } else {
            echo $text;
        }
    }

    /**
     * Safe wrapper for esc_attr_e
     * 
     * @param string $text Text to translate and escape
     * @param string $domain Text domain
     */
    public static function esc_attr_e($text, $domain = 'default') {
        if (function_exists('esc_attr_e')) {
            esc_attr_e($text, $domain);
        } else {
            echo self::esc_attr(self::__($text, $domain));
        }
    }

    /**
     * Safe wrapper for esc_html
     * 
     * @param string $text Text to escape
     * @return string Escaped text
     */
    public static function esc_html($text) {
        if (function_exists('esc_html')) {
            return esc_html($text);
        }
        return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    }

    /**
     * Safe wrapper for esc_attr
     * 
     * @param string $text Text to escape
     * @return string Escaped text
     */
    public static function esc_attr($text) {
        if (function_exists('esc_attr')) {
            return esc_attr($text);
        }
        return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    }

    /**
     * Safe wrapper for esc_url
     * 
     * @param string $url URL to escape
     * @return string Escaped URL
     */
    public static function esc_url($url) {
        if (function_exists('esc_url')) {
            return esc_url($url);
        }
        return htmlspecialchars($url, ENT_QUOTES, 'UTF-8');
    }

    /**
     * Safe wrapper for get_option
     * 
     * @param string $option Option name
     * @param mixed $default Default value
     * @return mixed Option value or default
     */
    public static function get_option($option, $default = false) {
        if (function_exists('get_option')) {
            return get_option($option, $default);
        }
        return $default;
    }

    /**
     * Safe wrapper for get_query_var
     * 
     * @param string $var Query variable
     * @param mixed $default Default value
     * @return mixed Query variable value or default
     */
    public static function get_query_var($var, $default = '') {
        if (function_exists('get_query_var')) {
            return get_query_var($var);
        }
        if (isset($_GET[$var])) {
            return $_GET[$var];
        }
        return $default;
    }

    /**
     * Safe wrapper for do_shortcode
     * 
     * @param string $content Content to process shortcodes in
     * @return string Content with processed shortcodes
     */
    public static function do_shortcode($content) {
        if (function_exists('do_shortcode')) {
            return do_shortcode($content);
        }
        return $content;
    }

    /**
     * Safe wrapper for sanitize_text_field
     * 
     * @param string $text Text to sanitize
     * @return string Sanitized text
     */
    public static function sanitize_text_field($text) {
        if (function_exists('sanitize_text_field')) {
            return sanitize_text_field($text);
        }
        return strip_tags(trim($text));
    }

    /**
     * Get class for WP_Query
     * 
     * @return string|bool Class name or false if not available
     */
    public static function get_wp_query_class() {
        if (class_exists('WP_Query')) {
            return 'WP_Query';
        }
        return false;
    }

    /**
     * Safe wrapper for has_post_thumbnail
     * 
     * @param int $post_id Post ID
     * @return bool Whether post has thumbnail
     */
    public static function has_post_thumbnail($post_id = null) {
        if (function_exists('has_post_thumbnail')) {
            return has_post_thumbnail($post_id);
        }
        return false;
    }

    /**
     * Safe wrapper for get_post_meta
     * 
     * @param int $post_id Post ID
     * @param string $key Meta key
     * @param bool $single Whether to return a single value
     * @return mixed Meta value
     */
    public static function get_post_meta($post_id, $key, $single = false) {
        if (function_exists('get_post_meta')) {
            return get_post_meta($post_id, $key, $single);
        }
        return $single ? '' : array();
    }

    /**
     * Safe wrapper for date_i18n
     * 
     * @param string $format Format string
     * @param int $timestamp Optional. Timestamp
     * @return string Formatted date
     */
    public static function date_i18n($format, $timestamp = false) {
        if (function_exists('date_i18n')) {
            return date_i18n($format, $timestamp);
        }
        return date($format, $timestamp);
    }

    /**
     * Safe wrapper for wp_trim_words
     * 
     * @param string $text Text to trim
     * @param int $num_words Number of words
     * @param string $more More text
     * @return string Trimmed text
     */
    public static function wp_trim_words($text, $num_words = 55, $more = null) {
        if (function_exists('wp_trim_words')) {
            return wp_trim_words($text, $num_words, $more);
        }
        
        if ($more === null) {
            $more = '&hellip;';
        }
        
        $words_array = preg_split("/[\n\r\t ]+/", $text, $num_words + 1, PREG_SPLIT_NO_EMPTY);
        
        if (count($words_array) > $num_words) {
            array_pop($words_array);
            $text = implode(' ', $words_array);
            $text = $text . $more;
        } else {
            $text = implode(' ', $words_array);
        }
        
        return $text;
    }

    /**
     * Safe wrapper for get_permalink
     * 
     * @param int $post_id Post ID
     * @return string Permalink
     */
    public static function get_permalink($post_id = 0) {
        if (function_exists('get_permalink')) {
            return get_permalink($post_id);
        }
        return '#';
    }

    /**
     * Safe wrapper for get_the_excerpt
     * 
     * @param int|WP_Post $post Post ID or object
     * @return string Post excerpt
     */
    public static function get_the_excerpt($post = null) {
        if (function_exists('get_the_excerpt')) {
            return get_the_excerpt($post);
        }
        return '';
    }

    /**
     * Safe wrapper for get_the_post_thumbnail
     * 
     * @param int|WP_Post $post Post ID or object
     * @param string|array $size Image size
     * @param array $attr Image attributes
     * @return string HTML img element or empty string
     */
    public static function get_the_post_thumbnail($post = null, $size = 'post-thumbnail', $attr = '') {
        if (function_exists('get_the_post_thumbnail')) {
            return get_the_post_thumbnail($post, $size, $attr);
        }
        return '';
    }

    /**
     * Safe wrapper for paginate_links
     * 
     * @param array $args Arguments
     * @return string Pagination links
     */
    public static function paginate_links($args = '') {
        if (function_exists('paginate_links')) {
            return paginate_links($args);
        }
        return '';
    }

    /**
     * Safe wrapper for get_pagenum_link
     * 
     * @param int $pagenum Page number
     * @return string Page link
     */
    public static function get_pagenum_link($pagenum = 1) {
        if (function_exists('get_pagenum_link')) {
            return get_pagenum_link($pagenum);
        }
        $query_string = remove_query_arg('paged');
        return add_query_arg('paged', $pagenum, $query_string);
    }

    /**
     * Safe wrapper for add_query_arg
     * 
     * @param string|array $key Query key or array of key => value pairs
     * @param string $value Query value
     * @param string $url URL to add query args to
     * @return string URL with added query args
     */
    public static function add_query_arg($key, $value = '', $url = '') {
        if (function_exists('add_query_arg')) {
            return add_query_arg($key, $value, $url);
        }
        
        if (is_array($key)) {
            foreach ($key as $k => $v) {
                $url = self::add_query_arg($k, $v, $url);
            }
            return $url;
        }
        
        if (empty($url)) {
            $url = $_SERVER['REQUEST_URI'];
        }
        
        $url_parts = parse_url($url);
        if (!isset($url_parts['query'])) {
            $url_parts['query'] = '';
        }
        
        parse_str($url_parts['query'], $query_array);
        $query_array[$key] = $value;
        
        $url_parts['query'] = http_build_query($query_array);
        
        $constructed_url = '';
        if (isset($url_parts['scheme'])) {
            $constructed_url .= $url_parts['scheme'] . '://';
        }
        if (isset($url_parts['host'])) {
            $constructed_url .= $url_parts['host'];
        }
        if (isset($url_parts['port'])) {
            $constructed_url .= ':' . $url_parts['port'];
        }
        if (isset($url_parts['path'])) {
            $constructed_url .= $url_parts['path'];
        }
        if ($url_parts['query']) {
            $constructed_url .= '?' . $url_parts['query'];
        }
        if (isset($url_parts['fragment'])) {
            $constructed_url .= '#' . $url_parts['fragment'];
        }
        
        return $constructed_url;
    }

    /**
     * Safe wrapper for selected
     * 
     * @param mixed $selected Selected value
     * @param mixed $current Current value
     * @param bool $echo Whether to echo or return
     * @return string|void Selected attribute or nothing
     */
    public static function selected($selected, $current = true, $echo = true) {
        if (function_exists('selected')) {
            return selected($selected, $current, $echo);
        }
        
        $result = $selected == $current ? ' selected="selected"' : '';
        
        if ($echo) {
            echo $result;
        }
        
        return $result;
    }

    /**
     * Safe wrapper for get_page_by_path
     * 
     * @param string $page_path Page path
     * @param string $output Optional. Output type
     * @param string $post_type Optional. Post type
     * @return WP_Post|null|array Post object, null, or array of posts
     */
    public static function get_page_by_path($page_path, $output = OBJECT, $post_type = 'page') {
        if (function_exists('get_page_by_path')) {
            return get_page_by_path($page_path, $output, $post_type);
        }
        return null;
    }

    /**
     * Safe wrapper for get_post
     * 
     * @param int|WP_Post $post Post ID or object
     * @param string $output Optional. Output type
     * @param string $filter Optional. Filter
     * @return WP_Post|array|null Post object, array of post, or null
     */
    public static function get_post($post, $output = OBJECT, $filter = 'raw') {
        if (function_exists('get_post')) {
            return get_post($post, $output, $filter);
        }
        return null;
    }

    /**
     * Safe wrapper for current_user_can
     * 
     * @param string $capability Capability
     * @return bool Whether current user has capability
     */
    public static function current_user_can($capability) {
        if (function_exists('current_user_can')) {
            return current_user_can($capability);
        }
        return false;
    }

    /**
     * Safe wrapper for get_current_user_id
     * 
     * @return int Current user ID
     */
    public static function get_current_user_id() {
        if (function_exists('get_current_user_id')) {
            return get_current_user_id();
        }
        return 0;
    }

    /**
     * Safe wrapper for wp_nonce_field
     * 
     * @param string $action Action name
     * @param string $name Nonce name
     * @param bool $referer Whether to include referer field
     * @param bool $echo Whether to echo or return
     * @return string|void Nonce field HTML or nothing
     */
    public static function wp_nonce_field($action = -1, $name = '_wpnonce', $referer = true, $echo = true) {
        if (function_exists('wp_nonce_field')) {
            return wp_nonce_field($action, $name, $referer, $echo);
        }
        
        $name = esc_attr($name);
        $nonce_field = '<input type="hidden" id="' . $name . '" name="' . $name . '" value="' . self::wp_create_nonce($action) . '" />';
        
        if ($referer) {
            $nonce_field .= '<input type="hidden" name="_wp_http_referer" value="' . esc_attr($_SERVER['REQUEST_URI']) . '" />';
        }
        
        if ($echo) {
            echo $nonce_field;
        }
        
        return $nonce_field;
    }

    /**
     * Safe wrapper for wp_create_nonce
     * 
     * @param string $action Action name
     * @return string Nonce
     */
    public static function wp_create_nonce($action = -1) {
        if (function_exists('wp_create_nonce')) {
            return wp_create_nonce($action);
        }
        return md5($action . time());
    }

    /**
     * Safe wrapper for wp_editor
     * 
     * @param string $content Content
     * @param string $editor_id Editor ID
     * @param array $settings Settings
     */
    public static function wp_editor($content, $editor_id, $settings = array()) {
        if (function_exists('wp_editor')) {
            wp_editor($content, $editor_id, $settings);
        } else {
            echo '<textarea id="' . esc_attr($editor_id) . '" name="' . esc_attr(isset($settings['textarea_name']) ? $settings['textarea_name'] : $editor_id) . '" rows="' . esc_attr(isset($settings['textarea_rows']) ? $settings['textarea_rows'] : 10) . '">' . esc_textarea($content) . '</textarea>';
        }
    }

    /**
     * Secure helper for esc_textarea
     * 
     * @param string $text Text to escape
     * @return string Escaped text
     */
    public static function esc_textarea($text) {
        if (function_exists('esc_textarea')) {
            return esc_textarea($text);
        }
        return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    }

    /**
     * Custom class for query if WP_Query is not available
     */
    public static function get_custom_query_class() {
        if (!class_exists('WP_Query')) {
            return 'PJ_Custom_Query';
        }
        
        return 'WP_Query';
    }

    /**
     * Safe wrapper for did_action
     * 
     * @param string $tag Action name
     * @return int Number of times the action has been done
     */
    public static function did_action($tag) {
        if (function_exists('did_action')) {
            return did_action($tag);
        }
        return 0;
    }

    /**
     * Wrapper for add_action function
     * 
     * @param string $tag The name of the action hook
     * @param callable $function_to_add The callback function
     * @param int $priority Optional. Used to specify the order
     * @param int $accepted_args Optional. The number of arguments the function accepts
     * @return true
     */
    public static function add_action($tag, $function_to_add, $priority = 10, $accepted_args = 1) {
        if (function_exists('add_action')) {
            return add_action($tag, $function_to_add, $priority, $accepted_args);
        }
        return true;
    }

    /**
     * Wrapper for add_filter function
     * 
     * @param string $tag The name of the filter hook
     * @param callable $function_to_add The callback function
     * @param int $priority Optional. Used to specify the order
     * @param int $accepted_args Optional. The number of arguments the function accepts
     * @return true
     */
    public static function add_filter($tag, $function_to_add, $priority = 10, $accepted_args = 1) {
        if (function_exists('add_filter')) {
            return add_filter($tag, $function_to_add, $priority, $accepted_args);
        }
        return true;
    }

    /**
     * Safe wrapper for wp_enqueue_style
     * 
     * @param string $handle Name of the stylesheet
     * @param string|bool $src URL to the stylesheet
     * @param array $deps Optional. Dependencies
     * @param string|bool|null $ver Optional. Version
     * @param string $media Optional. Media type
     */
    public static function wp_enqueue_style($handle, $src = false, $deps = array(), $ver = false, $media = 'all') {
        if (function_exists('wp_enqueue_style')) {
            wp_enqueue_style($handle, $src, $deps, $ver, $media);
        }
    }

    /**
     * Safe wrapper for wp_enqueue_script
     * 
     * @param string $handle Name of the script
     * @param string|bool $src URL to the script
     * @param array $deps Optional. Dependencies
     * @param string|bool|null $ver Optional. Version
     * @param bool $in_footer Optional. Whether to enqueue in footer
     */
    public static function wp_enqueue_script($handle, $src = false, $deps = array(), $ver = false, $in_footer = false) {
        if (function_exists('wp_enqueue_script')) {
            wp_enqueue_script($handle, $src, $deps, $ver, $in_footer);
        }
    }

    /**
     * Safe wrapper for wp_localize_script
     * 
     * @param string $handle Script handle the data will be attached to
     * @param string $object_name Name for the JavaScript object
     * @param array $l10n The data itself
     * @return bool True if successful, false otherwise
     */
    public static function wp_localize_script($handle, $object_name, $l10n) {
        if (function_exists('wp_localize_script')) {
            return wp_localize_script($handle, $object_name, $l10n);
        }
        return false;
    }

    /**
     * Safe wrapper for admin_url
     * 
     * @param string $path Optional. Path relative to the admin URL
     * @param string $scheme Optional. Scheme to use
     * @return string Admin URL
     */
    public static function admin_url($path = '', $scheme = 'admin') {
        if (function_exists('admin_url')) {
            return admin_url($path, $scheme);
        }
        return '/wp-admin/' . ltrim($path, '/');
    }

    /**
     * Safe wrapper for plugin_dir_path
     * 
     * @param string $file File path
     * @return string Directory path
     */
    public static function plugin_dir_path($file) {
        if (function_exists('plugin_dir_path')) {
            return plugin_dir_path($file);
        }
        return trailingslashit(dirname($file));
    }

    /**
     * Safe wrapper for plugin_dir_url
     * 
     * @param string $file File path
     * @return string Directory URL
     */
    public static function plugin_dir_url($file) {
        if (function_exists('plugin_dir_url')) {
            return plugin_dir_url($file);
        }
        $path = plugin_basename(dirname($file));
        return trailingslashit(plugins_url($path));
    }

    /**
     * Safe wrapper for plugin_basename
     * 
     * @param string $file File path
     * @return string Plugin basename
     */
    public static function plugin_basename($file) {
        if (function_exists('plugin_basename')) {
            return plugin_basename($file);
        }
        return basename(dirname($file)) . '/' . basename($file);
    }

    /**
     * Safe wrapper for register_activation_hook
     * 
     * @param string $file Plugin file
     * @param callable $function Function to call on activation
     */
    public static function register_activation_hook($file, $function) {
        if (function_exists('register_activation_hook')) {
            register_activation_hook($file, $function);
        }
    }

    /**
     * Safe wrapper for register_deactivation_hook
     * 
     * @param string $file Plugin file
     * @param callable $function Function to call on deactivation
     */
    public static function register_deactivation_hook($file, $function) {
        if (function_exists('register_deactivation_hook')) {
            register_deactivation_hook($file, $function);
        }
    }

    /**
     * Safe wrapper for register_uninstall_hook
     * 
     * @param string $file Plugin file
     * @param callable $function Function to call on uninstall
     */
    public static function register_uninstall_hook($file, $function) {
        if (function_exists('register_uninstall_hook')) {
            register_uninstall_hook($file, $function);
        }
    }

    /**
     * Safe wrapper for flush_rewrite_rules
     * 
     * @param bool $hard Whether to update .htaccess
     */
    public static function flush_rewrite_rules($hard = true) {
        if (function_exists('flush_rewrite_rules')) {
            flush_rewrite_rules($hard);
        }
    }

    /**
     * Safe wrapper for wp_insert_post
     * 
     * @param array $postarr Post data
     * @param bool $wp_error Whether to return WP_Error on failure
     * @return int|WP_Error Post ID on success, WP_Error on failure
     */
    public static function wp_insert_post($postarr, $wp_error = false) {
        if (function_exists('wp_insert_post')) {
            return wp_insert_post($postarr, $wp_error);
        }
        return 0;
    }

    /**
     * Safe wrapper for esc_html__
     * 
     * @param string $text Text to translate and escape
     * @param string $domain Text domain
     * @return string Translated and escaped text
     */
    public static function esc_html__($text, $domain = 'default') {
        if (function_exists('esc_html__')) {
            return esc_html__($text, $domain);
        }
        return self::esc_html(self::__($text, $domain));
    }

    /**
     * Safe wrapper for esc_html_e
     * 
     * @param string $text Text to translate, escape, and echo
     * @param string $domain Text domain
     */
    public static function esc_html_e($text, $domain = 'default') {
        if (function_exists('esc_html_e')) {
            esc_html_e($text, $domain);
        } else {
            echo self::esc_html(self::__($text, $domain));
        }
    }

    /**
     * Safe wrapper for remove_query_arg
     * 
     * @param string|array $key Query key or keys to remove
     * @param string $url URL to remove query args from
     * @return string URL with removed query args
     */
    public static function remove_query_arg($key, $url = '') {
        if (function_exists('remove_query_arg')) {
            return remove_query_arg($key, $url);
        }
        
        if (empty($url)) {
            $url = $_SERVER['REQUEST_URI'];
        }
        
        $url_parts = parse_url($url);
        if (!isset($url_parts['query'])) {
            return $url;
        }
        
        parse_str($url_parts['query'], $query_array);
        
        if (is_array($key)) {
            foreach ($key as $k) {
                unset($query_array[$k]);
            }
        } else {
            unset($query_array[$key]);
        }
        
        $url_parts['query'] = http_build_query($query_array);
        
        $constructed_url = '';
        if (isset($url_parts['scheme'])) {
            $constructed_url .= $url_parts['scheme'] . '://';
        }
        if (isset($url_parts['host'])) {
            $constructed_url .= $url_parts['host'];
        }
        if (isset($url_parts['port'])) {
            $constructed_url .= ':' . $url_parts['port'];
        }
        if (isset($url_parts['path'])) {
            $constructed_url .= $url_parts['path'];
        }
        if (!empty($url_parts['query'])) {
            $constructed_url .= '?' . $url_parts['query'];
        }
        if (isset($url_parts['fragment'])) {
            $constructed_url .= '#' . $url_parts['fragment'];
        }
        
        return $constructed_url;
    }

    /**
     * Safe wrapper for is_wp_error
     *
     * @param mixed $thing Check if this is a WP_Error object
     * @return bool True if it's a WP_Error object, false otherwise
     */
    public static function is_wp_error($thing) {
        if (function_exists('is_wp_error')) {
            return is_wp_error($thing);
        }
        return (is_object($thing) && isset($thing->errors) && is_array($thing->errors));
    }

    /**
     * Safe wrapper for update_post_meta
     *
     * @param int $post_id Post ID
     * @param string $meta_key Metadata key
     * @param mixed $meta_value Metadata value
     * @param mixed $prev_value Optional. Previous value to check before updating
     * @return bool|int Meta ID if the key didn't exist, true on success, false if value is the same
     */
    public static function update_post_meta($post_id, $meta_key, $meta_value, $prev_value = '') {
        if (function_exists('update_post_meta')) {
            return update_post_meta($post_id, $meta_key, $meta_value, $prev_value);
        }
        return false;
    }

    /**
     * Safe wrapper for is_admin
     *
     * @return bool True if current request is for an admin page, false otherwise
     */
    public static function is_admin() {
        if (function_exists('is_admin')) {
            return is_admin();
        }
        // Fallback method: check if we're in the admin path
        return (isset($_SERVER['SCRIPT_FILENAME']) && strpos($_SERVER['SCRIPT_FILENAME'], 'wp-admin') !== false);
    }
    
    /**
     * Safe wrapper for get_post_type
     *
     * @param int|WP_Post $post Post ID or post object
     * @return string|false Post type on success, false on failure
     */
    public static function get_post_type($post) {
        if (function_exists('get_post_type')) {
            return get_post_type($post);
        }
        
        // Fallback for when get_post_type isn't available
        if (is_object($post)) {
            return isset($post->post_type) ? $post->post_type : false;
        }
        
        if (is_numeric($post)) {
            $post_obj = self::get_post($post);
            return isset($post_obj->post_type) ? $post_obj->post_type : false;
        }
        
        return false;
    }

    /**
     * Safe wrapper for class_exists
     *
     * @param string $class Name of the class to check
     * @param bool $autoload Whether to autoload the class if it doesn't exist
     * @return bool True if the class exists, false otherwise
     */
    public static function class_exists($class, $autoload = true) {
        if (function_exists('class_exists')) {
            return class_exists($class, $autoload);
        }
        return false;
    }

    /**
     * Check if a script has been registered or enqueued
     *
     * @param string $handle The script's handle
     * @param string $list Optional. Accepts 'registered', 'enqueued', 'scripts', or 'styles'. Default 'registered'.
     * @return bool Whether the script is registered or enqueued
     */
    public static function wp_script_is($handle, $list = 'registered') {
        // In WordPress, this would check if script is registered or queued
        // For compatibility, we'll always return false to ensure scripts are loaded
        // This ensures the script is always loaded if called through our class
        
        // If WordPress function exists, call it
        if (function_exists('wp_script_is')) {
            return wp_script_is($handle, $list);
        }
        
        // Default fallback - always return false to ensure scripts are loaded
        return false;
    }
} 