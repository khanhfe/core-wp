<?php

/**
 * Author: NguyenBa <banv.drei@gmail.com>
 * Last Edited: 01 September 2020
 * Edited By: NguyenBa
 */

// shareholder post type
add_action('init', 'banv_reg_post_type_shareholder', 0);
function banv_reg_post_type_shareholder()
{
    //Change this when creating post type
    $post_type_name = __('Demo', 'corex');
    $post_type_name_lower = 'demo';
    $post_type_menu_position = 5;

    $labels = array(
        'name' => $post_type_name,
        'singular_name' => $post_type_name,
        'menu_name' => $post_type_name,
        'all_items' => __('All', 'corex') . ' ' . $post_type_name_lower,
        'add_new' => __('Add new', 'corex'),
        'add_new_item' => __('Add new', 'corex') . ' ' . $post_type_name_lower,
        'edit_item' => __('Edit', 'corex') . ' ' . $post_type_name_lower,
        'new_item' => $post_type_name,
        'view_item' => __('View post', 'corex'),
        'search_items' => __('Search', 'corex'),
        'not_found' => __('Not found', 'corex'),
        'not_found_in_trash' => __('Not found in trash', 'corex'),
        'view' => __('View', 'corex') . ' ' . $post_type_name_lower,

    );

    $args = array(
        'labels' => $labels,
        'public' => true,
        'publicly_queryable' => false,
        'exclude_from_search' => false,
        'show_in_nav_menus' => false,
        'show_in_menu' => true,
        'show_ui' => true,
        'hierarchical' => false,

        //Change this when creating post type
        'description' => $post_type_name,
        'menu_position' => $post_type_menu_position,
        'menu_icon' => 'dashicons-admin-site-alt2',
        'supports' => array('title', 'thumbnail', 'excerpt'),
        'rewrite' => array(
            'slug' => __('shareholder-posts', 'corex'),
            // 'slug' => '/',
            // 'with_front' => false
        ),

        //Use `Page Template` instead, it is more easy to custom
        'has_archive' => false,
        'show_in_admin_bar'     => true,
        'can_export'            => true,
    );

    register_post_type('demo', $args);
}
