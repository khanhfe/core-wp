<?php

/**
 * Author: NguyenBa <banv.drei@gmail.com>
 * Last Edited: 01 September 2020
 * Edited By: NguyenBa
 */
class FormCommon
{
    private static $_instance = null;
    // private $fields;

    public function __construct($fields = array())
    {
        $this->fields = $fields;
        add_action('wp_ajax_formCommonAjax', array($this, 'formCommonAjax'));
        add_action('wp_ajax_nopriv_formCommonAjax', array($this, 'formCommonAjax'));
        add_action('init', array($this, 'initRegisterPostType'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue'));
    }

    public static function instance()
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public static function security()
    {
        wp_nonce_field('form_submit', 'contact_nonce');
    }

    public function enqueue()
    {
        wp_enqueue_script('contact-form-js', THEME_URI . '/inc/contact-form/FormAjax.js', array('jquery'));
        $wp_script_data = array(
            'AJAX_URL' => ADMIN_AJAX_URL,
            'HOME_URL' => HOME_URL
        );
        wp_localize_script('contact-form-js', 'obj', $wp_script_data);
    }

    /**
     * Post Type: Form.
     */

    public function initRegisterPostType()
    {
        $labels = [
            "name" => __("Register Form", "corex"),
            "singular_name" => __("Register Form", "corex"),
        ];

        $args = [
            "label" => __("Register Form", "corex"),
            "labels" => $labels,
            "description" => "",
            "public" => true,
            "publicly_queryable" => true,
            "show_ui" => true,
            "show_in_rest" => false,
            "rest_base" => "",
            "rest_controller_class" => "WP_REST_Posts_Controller",
            "has_archive" => false,
            "show_in_menu" => true,
            "show_in_nav_menus" => true,
            "delete_with_user" => false,
            "exclude_from_search" => false,
            "capability_type" => "post",
            "map_meta_cap" => true,
            "hierarchical" => false,
            "rewrite" => ["slug" => "contact", "with_front" => true],
            "query_var" => true,
            "menu_icon" => "dashicons-buddicons-pm",
            "supports" => ["title", "custom-fields"],
            // Block SEO data form
            'capabilities' => array(
                'create_posts' => false,
            ),
            'map_meta_cap' => true,
            'publicly_queryable'  => false
        ];

        register_post_type("register", $args);
    }

    public function formCommonAjax()
    {
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'form_submit')) {
            wp_send_json_error(__('Không thể xác nhận mã khoá bảo mật', 'corex'));
            exit;
        }

        $post_arr = array(
            'post_title' => $_POST['data'][$_POST['postTitle']],
            'post_content' => '',
            'post_status' => 'pending',
            'post_type' => $_POST['postType'],
            'meta_input' => $_POST['data']
        );

        $resp = wp_insert_post($post_arr);

        if ($resp) {
            wp_send_json_success(__('Gửi thành công', 'corex'));
        } else {
            wp_send_json_error(__('Lỗi, không thể tạo liên hệ mới', 'corex'));
        }
    }
}
