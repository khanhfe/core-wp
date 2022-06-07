<?php

/**
 * Author: NguyenBa <banv.drei@gmail.com>
 * Last Edited: 01 September 2020
 * Edited By: NguyenBa
 */

/**
 * edit logo login
 */
function custom_type_admin()
{ ?>
    <style type="text/css">
        #login h1 a {
            background-image: url('<?php echo LOGO_LOGIN_ADMIN; ?>');
            width: 310px;
            height: 80px;
            background-size: auto;
            background-position: center bottom;
        }

        .login.wp-core-ui {
            background-image: url('<?php echo LOGO_LOGIN_ADMIN_W ?>');
            background-color: #F5F5FA;
            background-size: 180px auto;
            background-attachment: fixed;
        }
    </style>
<?php }
add_action('login_enqueue_scripts', 'custom_type_admin');

/**
 * edit href logo login wp.org -> home_url('/');
 */
add_filter('login_headerurl', 'login_logo_url');
function login_logo_url()
{
    return HOME_URL;
}

/**
 *add logo admin container
 */
// function banv_admin_logo(){
//     echo '<img src="'.THEME_ASSETS.'/images/logo-admin.png'.'" width="300" style="display: block; margin-top: 20px;">';
// }
// add_action('admin_notices', 'banv_admin_logo');

/**
 * footer
 */

function banv_admin_footer($text)
{
    $text = '<p id="footer-left" class="alignleft">
                <span>
                    Email: <a href="mailto:info@twinger.vn">info@twinger.vn</a> | phone: <a href="mailto:+84 933 886 556">+84 933 886 556</a>
                </span>
                <br>
                <span>
                    Location: 5th floor, Song Da Building (HH4 Tower), 18 Pham Hung, My Dinh, Hanoi
                </span>
                <br>
                <span>
                    Copyright © 2021 by Twinger. All rights reserved
                </span> 
            </p>';
    return $text;
}
add_filter('admin_footer_text', 'banv_admin_footer');

/**
 * edit favicon admin
 */
function favicon_admin()
{
    echo '<link rel="Shortcut Icon" type="image/x-icon" href="' . FAVICON_ADMIN . '" />';
}
add_action('admin_head', 'favicon_admin');

/**
 * remove wp bar login
 */
function example_admin_bar_remove_logo()
{
    global $wp_admin_bar;
    $wp_admin_bar->remove_menu('wp-logo');
}
add_action('wp_before_admin_bar_render', 'example_admin_bar_remove_logo', 0);

/**
 * Remove Parent Category from Child Category URL
 * @method no_category_parents()
 * @param string $url
 * @param string $term
 * @param string $taxonomy
 * @return string $url
 */
add_filter('term_link', 'no_category_parents', 1000, 3);
function no_category_parents($url, $term, $taxonomy)
{
    if ($taxonomy == 'category') {
        $term_nicename = $term->slug;
        $url = trailingslashit(get_option('home')) . user_trailingslashit($term_nicename, 'category');
    }
    return $url;
}

/**
 * Rewrite url mới
 * @method no_category_parents_rewrite_rules()
 * @param bool #flash
 * @return
 */

function no_category_parents_rewrite_rules($flash = false)
{
    $terms = get_terms(array(
        'taxonomy' => 'category',
        'post_type' => 'post',
        'hide_empty' => false,
    ));
    if ($terms && !is_wp_error($terms)) {
        foreach ($terms as $term) {
            $term_slug = $term->slug;
            add_rewrite_rule($term_slug . '/?$', 'index.php?category_name=' . $term_slug, 'top');
            add_rewrite_rule($term_slug . '/page/([0-9]{1,})/?$', 'index.php?category_name=' . $term_slug . '&paged=$matches[1]', 'top');
            add_rewrite_rule($term_slug . '/(?:feed/)?(feed|rdf|rss|rss2|atom)/?$', 'index.php?category_name=' . $term_slug . '&feed=$matches[1]', 'top');
        }
    }
    if ($flash == true)
        flush_rewrite_rules(false);
}
add_action('init', 'no_category_parents_rewrite_rules');

/*Sửa lỗi khi tạo mới category bị 404*/
function new_category_edit_success()
{
    no_category_parents_rewrite_rules(true);
}
add_action('created_category', 'new_category_edit_success');
add_action('edited_category', 'new_category_edit_success');
add_action('delete_category', 'new_category_edit_success');


/* Hide WP version strings from scripts and styles
* @return {string} $src
* @filter script_loader_src
* @filter style_loader_src
*/
function remove_wp_version_strings($src)
{
    global $wp_version;
    parse_str(parse_url($src, PHP_URL_QUERY), $query);
    if (!empty($query['ver']) && $query['ver'] === $wp_version) {
        $src = remove_query_arg('ver', $src);
    }
    return $src;
}
add_filter('script_loader_src', 'remove_wp_version_strings');
add_filter('style_loader_src', 'remove_wp_version_strings');

/* Hide WP version strings from generator meta tag */
function wp_remove_version()
{
    return '';
}
add_filter('the_generator', 'wp_remove_version');

?>