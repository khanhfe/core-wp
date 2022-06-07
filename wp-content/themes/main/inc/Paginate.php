<?php
/**
 * Author: NguyenBa <banv.drei@gmail.com>
 * Last Edited: 01 September 2020
 * Edited By: NguyenBa
 */

/**
 * navigation core
 * @method paginate_custom_ulli()
 * @param object $custom_query
 * @return array|string|void
 */
function wp_paginate_custom_ulli($custom_query = null) {
    if(is_singular()){
        return;
    }
    global $wp_query;

    if($custom_query) $main_query = $custom_query;
    else $main_query = $wp_query;

    if($main_query->max_num_pages <= 1){
        return;
    }
    $paged = get_query_var('paged') ? absint(get_query_var('paged')) : 1;
    $max = intval($main_query->max_num_pages);

    if($paged >= 1){
        $links[] = $paged;
    }

    if($paged >= 3){
        $links[] = $paged - 1;
        $links[] = $paged - 2;
    }

    if(($paged + 2) <= $max){
        $links[] = $paged + 2;
        $links[] = $paged + 1;
    }

    echo '<div class="pagination-common"> <ul class="paginate_links">'."\n";
    if(get_previous_post_link()){
        printf('<li class="prev">%s</li>' . "\n" , get_previous_posts_link('<'));
    }

    if(!in_array(1, $links)){
        $class = 1 == $paged ? ' class="is--active"' : '';
        printf('<li%s><a href="%s">%s</a></li>'."\n", $class, esc_url(get_pagenum_link(1)), '1');
        if(!in_array(2, $links)){
            echo '<li>...</li>';
        }
    }

    sort($links);
    foreach ((array) $links as $link) {
        $class = $paged == $link ? ' class="is--active"' : '';
        printf('<li%s><a href="%s">%s</a></li>'."\n", $class, esc_url(get_pagenum_link($link)), $link);
    }

    if(!in_array($max, $links)){
        if(!in_array($max-1,$links)){
            echo '<li>...</li>'."\n";
        }

        $class = $paged == $max ? ' class="is--active"' : '';
        printf('<li%s><a href="%s">%s</a></li>'."\n", $class, esc_url(get_pagenum_link($max)), $max);
    }
    if(get_next_posts_link()){
        printf('<li class="next">%s</li>' . "\n" , get_next_posts_link('>'));
    }
    echo '</ul> </div>'."\n";
}

/**
 * code phan trang ajax
 * @method wp_paginate_paged_ajax()
 * @param object $custom_query
 * @param int $page
 * @return array|string|void
 */
function wp_paginate_paged_ajax($custom_query = null, $paged = 1) {
    global $wp_query, $wp_rewrite;
    if($custom_query) $main_query = $custom_query;
    else $main_query = $wp_query;
    $big = 999999999;
    $total = isset($main_query->max_num_pages)?$main_query->max_num_pages:'';
    if($total > 1) echo '<div class="paginate_links">';
    echo paginate_links( array(
        'base'        => trailingslashit( home_url() ) . "{$wp_rewrite->pagination_base}/%#%/",
        'format'   => '?paged=%#%',
        'current'  => max( 1, $paged ),
        'total'    => $total,
        'mid_size' => '3',
        'prev_text'    => __('<','devvn'),
        'next_text'    => __('>','next'),
    ) );
    if($total > 1) echo '</div>';
};

//Code phan trang
/**
 * code phan trang ajax
 * @method wp_paginate_paged()
 * @param object $custom_query
 * @param int $page
 * @return array|string|void
 */
function wp_paginate_paged($custom_query = null, $paged = null) {
    global $wp_query;
    if($custom_query) $main_query = $custom_query;
    else $main_query = $wp_query;
    $paged = ($paged) ? $paged : get_query_var('paged');
    $big = 999999999;
    $total = isset($main_query->max_num_pages)?$main_query->max_num_pages:'';
    if($total > 1) echo '<div class="paginate_links">';
    echo paginate_links( array(
        'base' => str_replace( $big, '%#%', esc_url( get_pagenum_link( $big ) ) ),
        'format' => '?paged=%#%',
        'current' => max( 1, $paged ),
        'total' => $total,
        'mid_size' => '10',
        'prev_text'    => __('<','devvn'),
        'next_text'    => __('>','next'),
    ) );
    if($total > 1) echo '</div>';
}
