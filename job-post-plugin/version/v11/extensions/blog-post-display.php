<?php

if (!defined('ABSPATH')) {
    exit;
}

// Register the shortcode
add_shortcode( 'wzjob_blog', 'wzjob_blog_grid_display' );

function wzjob_blog_grid_display( $atts ) {
    // Parse shortcode attributes
    $atts = shortcode_atts( array(
        'categories' => '',
    ), $atts, 'wzjob_blog' );

    $categories = array_filter( array_map( 'trim', explode( ',', $atts['categories'] ) ) );

    if ( empty( $categories ) ) {
        return '<p>No categories specified. Use: [wzjob_blog categories="category-slug1,category-slug2"]</p>';
    }

    $output = '';

    // Add inline CSS
    $output .= '<style>
        .wzjob_blog_container {
            width: 100%;
            padding: 20px 0;
        }

        .wzjob_blog_category_section {
            margin-bottom: 50px;
        }

        .wzjob_blog_category_title {
            font-size: 28px;
            font-weight: bold;
            color: #333;
            margin-bottom: 25px;
            padding-bottom: 10px;
            border-bottom: 3px solid #2874BF;
            text-transform: capitalize;
            text-align: center;
        }

        .wzjob_blog_grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 25px;
            margin-bottom: 40px;
        }

        .wzjob_blog_card {
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .wzjob_blog_card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.15);
        }

        .wzjob_blog_image {
            width: 100%;
            height: auto;
            object-fit: cover;
            cursor: pointer;
            display: block;
        }

        .wzjob_blog_content {
            padding: 15px;
        }

        .wzjob_blog_title {
            font-size: 18px;
            font-weight: bold;
            color: #222;
            line-height: 1.4;
            margin: 0;
            cursor: pointer;
            transition: color 0.2s ease;
        }

        .wzjob_blog_title:hover {
            color: #2874BF;
        }

        .wzjob_blog_no_posts {
            grid-column: 1 / -1;
            padding: 30px;
            background: #f5f5f5;
            border-radius: 8px;
            text-align: center;
            color: #666;
        }

        /* Pagination Styling */
        .pagination {
            list-style: none;
            padding: 0;
            margin: 0;
            display: flex;
            justify-content: center;
            gap: 8px;
            flex-wrap: wrap;
        }

        .pagination li {
            display: inline-block;
        }

        .pagination a,
        .pagination .page-numbers {
            padding: 10px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            text-decoration: none;
            color: #2874BF;
            transition: all 0.3s ease;
        }

        .pagination a:hover {
            background-color: #2874BF;
            color: white;
            border-color: #2874BF;
        }

        .pagination .current {
            background-color: #2874BF;
            color: white;
            border-color: #2874BF;
            padding: 10px 12px;
            border-radius: 4px;
        }

        span.page-numbers.current , a.page-numbers {
            border: 2px solid green;
            padding: 5px 14px;
            border-radius: 5px;
            font-weight: bold;
        }

        span.page-numbers.current {
            color: white;
        }

        a.page-numbers {
            color: #2874BF
        }

        a.page-numbers:hover {
            background-color: green;
            color: white;
        }

        /* Tablet: 2 columns */
        @media (max-width: 768px) {
            .wzjob_blog_grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 20px;
            }

            .wzjob_blog_category_title {
                font-size: 24px;
            }

            .wzjob_blog_title {
                font-size: 17px;
            }

            .pagination {
                gap: 5px;
            }

            .pagination a,
            .pagination .page-numbers {
                padding: 8px 10px;
                font-size: 14px;
            }
        }

        /* Mobile: 1 column */
        @media (max-width: 480px) {
            .wzjob_blog_grid {
                grid-template-columns: 1fr;
                gap: 15px;
            }

            .wzjob_blog_category_title {
                font-size: 20px;
                margin-bottom: 15px;
            }

            .wzjob_blog_card {
                margin-bottom: 10px;
            }

            .wzjob_blog_content {
                padding: 12px;
            }

            .wzjob_blog_title {
                font-size: 17px;
            }

            .pagination {
                gap: 4px;
            }

            .pagination a,
            .pagination .page-numbers {
                padding: 8px 10px;
                font-size: 12px;
            }
        }
    </style>';

    $output .= '<div class="wzjob_blog_container">';

    // Loop through each category
    foreach ( $categories as $category_slug ) {
        $category = get_category_by_slug( $category_slug );

        if ( ! $category ) {
            continue;
        }

        $output .= '<div class="wzjob_blog_category_section">';
        $output .= '<h1 class="wzjob_blog_category_title">' . esc_html( get_the_title() ) . '</h1>';

        // Get current page from URL - check 'paged' and 'pageblog' parameters
        if ( get_query_var( 'paged' ) ) {
            $paged = get_query_var( 'paged' );
        } elseif ( get_query_var( 'pageblog' ) ) {
            $paged = get_query_var( 'pageblog' );
        } else {
            $paged = 1;
        }

        // Query posts for this category
        $args = array(
            'post_type'      => 'post',
            'posts_per_page' => 6,
            'paged'          => $paged,
            'post_status'    => 'publish',
            'cat'            => $category->term_id,
            'orderby'        => 'modified',
            'order'          => 'DESC',
        );

        $query = new WP_Query( $args );

        $output .= '<div class="wzjob_blog_grid">';

        if ( $query->have_posts() ) {
            while ( $query->have_posts() ) {
                $query->the_post();
                $post_id = get_the_ID();
                $post_permalink = get_permalink( $post_id );

                // Get featured image or first image from post
                $image_url = '';
                $image_alt = '';

                if ( has_post_thumbnail( $post_id ) ) {
                    $image_url = get_the_post_thumbnail_url( $post_id, 'large' );
                    $image_alt = esc_attr( get_the_title( $post_id ) );
                } else {
                    // Get first image from post content
                    $post_content = get_the_content( $post_id );
                    preg_match( '/<img[^>]+src=[\'"]([^\'"]+)[\'"][^>]*alt=[\'"]([^\'"]*)[\'"][^>]*>/i', $post_content, $matches );

                    if ( ! empty( $matches[1] ) ) {
                        $image_url = $matches[1];
                        $image_alt = ! empty( $matches[2] ) ? $matches[2] : esc_attr( get_the_title( $post_id ) );
                    } else {
                        // Fallback: Try finding image without alt attribute
                        preg_match( '/<img[^>]+src=[\'"]([^\'"]+)[\'"][^>]*>/i', $post_content, $matches );
                        if ( ! empty( $matches[1] ) ) {
                            $image_url = $matches[1];
                            $image_alt = esc_attr( get_the_title( $post_id ) );
                        }
                    }
                }

                // Only display if image exists
                if ( $image_url ) {
                    $output .= '<div class="wzjob_blog_card">';
                    $output .= '<a href="' . esc_url( $post_permalink ) . '" style="text-decoration: none;">';
                    $output .= '<img src="' . esc_url( $image_url ) . '" alt="' . $image_alt . '" class="wzjob_blog_image">';
                    $output .= '</a>';
                    $output .= '<div class="wzjob_blog_content">';
                    $output .= '<a href="' . esc_url( $post_permalink ) . '" style="text-decoration: none;">';
                    $output .= '<h3 class="wzjob_blog_title">' . esc_html( get_the_title( $post_id ) ) . '</h3>';
                    $output .= '</a>';
                    $output .= '</div>';
                    $output .= '</div>';
                }
            }
        } else {
            $output .= '<div class="wzjob_blog_no_posts">No posts found in this category.</div>';
        }

        $output .= '</div>';

        // Manual pagination - show only 4 at a time
        if ( $query->max_num_pages > 1 ) {

            $current_page = max( 1, get_query_var( 'pageblog' ) );
            $total_pages  = $query->max_num_pages;
            $range        = 4;

            $start = max( 1, $current_page - floor( $range / 2 ) );
            $end   = $start + $range - 1;

            if ( $end > $total_pages ) {
                $end   = $total_pages;
                $start = max( 1, $end - $range + 1 );
            }

            $output .= '<div style="text-align:center;margin-top:30px;margin-bottom:40px;">';

            if ( $start > 1 ) {
                $url = esc_url( remove_query_arg( 'pageblog' ) );
                $output .= '<a class="page-numbers" href="' . $url . '">1</a> ';
                if ( $start > 2 ) {
                    $output .= '<span class="page-numbers">...</span> ';
                }
            }

            for ( $i = $start; $i <= $end; $i++ ) {

                if ( $i == $current_page ) {
                    $output .= '<span class="page-numbers current">' . $i . '</span> ';
                } else {
                    if ( $i == 1 ) {
                        $url = esc_url( remove_query_arg( 'pageblog' ) );
                    } else {
                        $url = esc_url( add_query_arg( 'pageblog', $i ) );
                    }
                    $output .= '<a class="page-numbers" href="' . $url . '">' . $i . '</a> ';
                }
            }

            if ( $end < $total_pages ) {
                if ( $end < $total_pages - 1 ) {
                    $output .= '<span class="page-numbers">...</span> ';
                }
                $url = esc_url( add_query_arg( 'pageblog', $total_pages ) );
                $output .= '<a class="page-numbers" href="' . $url . '">' . $total_pages . '</a>';
            }

            $output .= '</div>';
        }

        $output .= '</div>';

        wp_reset_postdata();
    }

    $output .= '</div>';

    return $output;
}

// Add 'pageblog' as a recognized query variable
add_filter( 'query_vars', 'gmpipo_add_query_vars' );
function gmpipo_add_query_vars( $vars ) {
    $vars[] = 'pageblog';
    return $vars;
}

// Remove 'pageblog=1' from first page URL to keep it clean
add_filter( 'paginate_links', 'gmpipo_clean_pagination_links' );
function gmpipo_clean_pagination_links( $link ) {
    // Remove ?pageblog=1 from the first page
    $link = str_replace( '?pageblog=1', '', $link );
    return $link;
}

// Add to Menu
add_action('admin_menu', 'blog_post_display_page', 15);

function blog_post_display_page() {
    add_submenu_page(
        'webzeeto-job',
        'Blog Post Display',
        'Blog Post Display',
        'manage_options',
        'wzjob-blog-post-display',
        'wzjob_blog_post_display_page_html'
    );
}

// Information page HTML
function wzjob_blog_post_display_page_html() {
    if (!current_user_can('manage_options')) {
        return;
    }
    ?>
    
    <div class="wrap">
        <h1><strong>Blog Post Display</strong></h1>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-top: 10px;">
            <div style="background: white; padding: 25px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">

                <h2 style="margin-top: 0;">🌐 Blog Post Display</h2>
                <hr style="margin: 10px 0 20px 0;">
                <p>There is No Settings for Blog Post Display</p>
            </div>

            <div style="background: white; padding: 25px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                <h2 style="margin-top: 0;">⁉️ How to use this?</h2>
                <hr style="margin: 10px 0 20px 0;">
                <p>Here is the shortcode to use this <code>[wzjob_blog categories="tech,business"]</code></p>
            </div>
        </div>
    </div>
    
    <?php
}
?>