<?php
error_reporting(0);
/**
 * Plugin Name:  Post Grid with Ajax Filter
 * Plugin URI:   http://addonmaster.com
 * Author:       AddonMaster
 * Author URI:   http://addonmaster.com/plugins/post-grid-with-ajax-filter
 * Version:      3.2.2
 * Description:  Post Grid with Ajax Filter helps you filter your posts by category terms with Ajax. Infinite scroll function included.
 * License:      GPL2
 * License URI:  https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:  ajax-filter-posts
 * Domain Path:  /lang
 */

/**
 * Including Plugin file for security
 * Include_once
 *
 * @since 1.0.0
 */
include_once ABSPATH . "wp-admin/includes/plugin.php";

// Defines
define("AM_POST_GRID_VERSION", "3.2.2");

/**
 * Loading Text Domain
 */
add_action("plugins_loaded", "am_post_grid_plugin_loaded_action", 10, 2);
function am_post_grid_plugin_loaded_action()
{
    load_plugin_textdomain(
        "ajax-filter-posts",
        false,
        dirname(plugin_basename(__FILE__)) . "/lang/"
    );
}

/**
 *  Admin Page
 */
//require_once( dirname( __FILE__ ) . '/inc/admin/admin-page.php' );

// Enqueue scripts
function am_ajax_filter_posts_scripts()
{
    // CSS File
    wp_enqueue_style(
        "asrafp-styles",
        plugin_dir_url(__FILE__) . "assets/css/post-grid-styles.css",
        null,
        AM_POST_GRID_VERSION
    );

    // JS File
    wp_register_script(
        "asr_ajax_filter_post",
        plugin_dir_url(__FILE__) . "assets/js/post-grid-scripts.js",
        ["jquery"],
        AM_POST_GRID_VERSION
    );
    wp_enqueue_script("asr_ajax_filter_post");

    // Localization
    wp_localize_script("asr_ajax_filter_post", "asr_ajax_params", [
        "asr_ajax_nonce" => wp_create_nonce("asr_ajax_nonce"),
        "asr_ajax_url" => admin_url("admin-ajax.php"),
    ]);
}

add_action("wp_enqueue_scripts", "am_ajax_filter_posts_scripts");

//shortcode function
function am_post_grid_shortcode_mapper($atts, $content = null){
    // Posts per pages.
    $posts_per_page = get_option("posts_per_page", true)
        ? get_option("posts_per_page", true)
        : 9;

    // Default attributes
    $shortcode_atts = shortcode_atts(
        [
            "show_filter" => "yes",
            "btn_all" => "yes",
            "initial" => "-1",
            "layout" => "1",
            "post_type" => ["news"],
            "posts_per_page" => 9,
            "cat" => "",
            "terms" => "",
            "paginate" => "yes",
            "hide_empty" => "true",
            "orderby" => "menu_order date", //Display posts sorted by ‘menu_order’ with a fallback to post ‘date’
            "order" => "DESC",
            "pagination_type" => "",
            "infinite_scroll" => "",
            "animation" => "",
            "grid_id" => "", // master ID
        ],
        $atts
    );

    // Params extraction
    extract($shortcode_atts);
    ob_start();

    // Texonomy arguments

    $taxonomy = "category";
    $args = [
        "hide_empty" => $hide_empty,
        "taxonomy" => $taxonomy,
        "include" => $terms ? $terms : $cat,
    ];
  
    // Get category terms
    $terms = get_terms($args); ?>
    <?php $tags = get_tags();  ?>
         

<div class="am_ajax_post_grid_wrap" data-pagination_type="<?php echo esc_attr(
    $pagination_type ); ?>" data-am_ajax_post_grid='<?php echo json_encode($shortcode_atts); ?>'>
        <div class="content_page_top_text">
            <div class="container">
                <div clas="row">
                    <p class="breadcrumb"><?php get_breadcrumb(); ?></p>
                </div>
                <div class="row policy_banner_text">
                        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                            <div class="tags">
                                <?php foreach ($tags as $tag) { ?>
                                    <span class="asr_texonomy tags_category" data-type="tags" data-text="<?php echo $tag->name; ?>" data_id="<?php echo $tag->term_id; ?>"><p><?php echo $tag->name; ?><span>#</span> </p>
                                    </span>
                                <?php } ?>
                                <div class="mul_tags" style="display:none;" data_ids="123"></div>
                            </div>
                        </div>
                    <?php if ($show_filter == "yes" && $terms && !is_wp_error($terms)) { ?>
                    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                        <div class="asr-filter-div" data-layout="<?php echo $layout; ?>">
                            <span><h3><?php the_field('sort_by'); ?>: </h3></span>
                            <ul>
                                <?php if ($btn_all != "no"): ?>
                                    <li class="asr_texonomy <?php echo isset($_GET['d']) ? '' : 'active' ?> all" data_id="0">
                                        <div>
                                            <img src="<?php echo get_stylesheet_directory_uri(); ?>/assets/images/all-contest.svg">
                                        </div> 
                                        <p>
                                            <?php 
                                                the_field('all_of_them'); 
                                                echo esc_html('','ajax-filter-posts');
                                            ?>
                                        </p>
                                    </li>
                                <?php endif; ?>

                                <?php foreach ($terms as $term) { 
                                    if(isset($_GET['d']) && in_array($term->term_id, $_GET['d'])){
                                        $class = 'active';
                                    } ?>
                                    <li class="asr_texonomy content_top_cat <?=$class?>" data-type="terms" data_id="<?php echo $term->term_id; ?>">
                                        <div>
                                            <img src="<?= get_field("cat_icon",$term->taxonomy . "_" . $term->term_id) ?>"class="cat_icon_white" 
                                            data-type="terms" data_id="<?php echo $term->term_id; ?>" />
                                        </div>
                                        <p><?php echo $term->name; ?></p>
                                    </li>           
                                <?php } ?>
                            </ul>  
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php } ?>
    <div class="container">
        <div class="row">
            <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                <div class="overflow-x-hidden">
                    <h3 class="text-center sub_heading dot_large_design d-inline-block position-relative">
                        <!--<span class="asr_texonomy" data-type="tags" data_id="<?php echo $tag->term_id; ?>">-->
                        <p class="tagname_byfilter text-capitalize d-inline-block mb-0"> <span class="tagname_byfilter_dec"></span><?php //echo $tag->name; ?></p> 
                          <!--</span>-->
                    </h3>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                <div class="asr-ajax-container">
                    <div class="asr-loader">
                        <div class="lds-dual-ring"></div>
                    </div>
                    <div class="asrafp-filter-result">
                        <?php echo am_post_grid_output(am_post_grid_get_args_from_shortcode_atts($shortcode_atts)); ?>
                    </div>
                </div>
            </div>
        </div>
        <hr class="dashed_border">
    </div>
</div>
    <?php return ob_get_clean();
}
add_shortcode("asr_ajax", "am_post_grid_shortcode_mapper");
add_shortcode("am_post_grid", "am_post_grid_shortcode_mapper");

// Get Args from Json Data
function am_post_grid_get_args_from_shortcode_atts($jsonData)
{
    if (isset($jsonData["posts_per_page"])) {
        $data["posts_per_page"] = intval($jsonData["posts_per_page"]);
    }

    if (isset($jsonData["orderby"])) {
        $data["orderby"] = sanitize_text_field($jsonData["orderby"]);
    }

    if (isset($jsonData["order"])) {
        $data["order"] = sanitize_text_field($jsonData["order"]);
    }

    if (isset($jsonData["pagination_type"])) {
        $data["pagination_type"] = sanitize_text_field(
            $jsonData["pagination_type"]
        );
    }

    if (isset($jsonData["animation"]) && $jsonData["animation"] == "true") {
        $data["animation"] = "am_has_animation";
    }

    if (
        isset($jsonData["infinite_scroll"]) &&
        $jsonData["infinite_scroll"] == "true"
    ) {
        $data["infinite_scroll"] = "infinite_scroll";
    }

    // Bind to Category Terms
    $terms = "";
    if (isset($jsonData["cat"]) && !empty($jsonData["cat"])) {
        $terms = explode(",", $jsonData["cat"]);
    } elseif (isset($jsonData["terms"]) && !empty($jsonData["terms"])) {
        $terms = explode(",", $jsonData["terms"]);
    }

    // Tax Query
    if (!empty($terms)) {
        $data["tax_query"] = [
            "category" => $terms,
        ];
    }

    return $data;
}

// Load Posts Ajax actions
add_action(
    "wp_ajax_asr_filter_posts",
    "am_post_grid_load_posts_ajax_functions"
);
add_action(
    "wp_ajax_nopriv_asr_filter_posts",
    "am_post_grid_load_posts_ajax_functions"
);

// Load Posts Ajax function
function am_post_grid_load_posts_ajax_functions()
{
    $json_data = json_decode(stripslashes($_POST["jsonData"]));
    $data = [];

    // $term_ID = isset( $_POST['term_ID'] ) ? sanitize_text_field( intval($_POST['term_ID']) ) : '';
    $term_ID = $_POST["term_ID"];

    // Pagination
    if ($_POST["paged"]) {
        $dataPaged = intval($_POST["paged"]);
    } else {
        $dataPaged = get_query_var("paged") ? get_query_var("paged") : 1;
    }

    $jsonData = json_decode(str_replace("\\", "", $_POST["jsonData"]), true);

    // Merge Json Data
    $data = array_merge(
        am_post_grid_get_args_from_shortcode_atts($jsonData),
        $data
    );

    // Current Page
    if (isset($_POST["paged"])) {
        $data["paged"] = sanitize_text_field($_POST["paged"]);
    }

    // Selected Category
    if (!empty($term_ID) && $term_ID != -1) {
        if ($_POST["type"] == "tags") {
            $data["tax_query"] = [
                "category" => array_unique($term_ID),
            ];
        } else {
            $data["tax_query"] = [
                "category" => array_unique($term_ID),
            ];
        }
    }

    // Output
    echo am_post_grid_output($data, $_POST["type"]);

    die();
}

// Post Grid
function am_post_grid_output($args = [], $type = null){

    // Parse Args
    $args = wp_parse_args($args, [
        "post_type" => ["news"],
        "post_status" => "publish",
        "paged" => $_POST['paged'] ? $_POST['paged'] : 1,
        "posts_per_page" => "9",
        'ignore_sticky_posts' => 1,
        "orderby" => "",
        "order" => "",
        "layout" => "1",
        "pagination_type" => "",
        "animation" => "",
        "infinite_scroll" => "",
        "tax_query" => [
            "category" => [],
        ],
    ]);

    // Post Query Args
    $query_args = [
        "post_type" => ["news"],
        "post_status" => "publish",
        "paged" => $args["paged"],
    ];

    // If json data found
    if (!empty($args["posts_per_page"])) {
        $query_args["posts_per_page"] = intval($args["posts_per_page"]);
    }

    if (!empty($args["orderby"])) {
        $query_args["orderby"] = sanitize_text_field($args["orderby"]);
    }

    if (!empty($args["order"])) {
        $query_args["order"] = sanitize_text_field($args["order"]);
    }
    // Pagination Type
    $pagination_type = sanitize_text_field($args["pagination_type"]);
    $dataPaged = sanitize_text_field($args["paged"]);

    // Tax Query Var
    $tax_query = [];
    $tags = [];
    $cats = [];
    // Check if has terms
    if(isset($_GET['d'])){
        $tax_query[] = [
            "taxonomy" => "category",
            "field" => "term_id",
            "terms" => $_GET['d'],
        ];
        $query_args['tax_query'][] = array_merge($query_relation, $tax_query);
    }
    if (!empty($args["tax_query"]['category']) && is_array($args["tax_query"])) {
        $cat_ids = $args["tax_query"]['category'];
        
        foreach ($cat_ids as $key => $term_ids) {
            $term = get_term($term_ids);
            switch ($term->taxonomy) {
                case 'post_tag':
                    $tags[] = $term->term_id;
                break;
                case 'category':
                    $cats[] = $term->term_id;
                break;
            }
        }
        if($tags && $cats){
            $tax_query['relation'] = 'AND';
        }
        
        if (!empty($tags)) {
            $query_relation = ['relation' => 'OR'];
            $tax_query[] = [
                "taxonomy" => "post_tag",
                "field" => "term_id",
                "terms" => $tags,
            ];
            $query_args['tax_query'][] = array_merge($query_relation, $tax_query);     
        }

        if (!empty($cats)) {
            $query_relation = ['relation' => 'OR'];
            $tax_query[] = [
                "taxonomy" => "category",
                "field" => "term_id",
                "terms" => $cats,
            ];
            $query_args['tax_query'][] = array_merge($query_relation, $tax_query);     
        }
    }

    // Tax Query
    if (!empty($tax_query)) {
        $query_args["tax_query"] = $tax_query;
    }
    //post query
    $query = new WP_Query( $query_args );
    $tag_ids = [];
    $arr_tags = [];
        ob_start();
        echo $pagination_type == "load_more" ? '<div class="am-postgrid-wrapper">': ""; ?>
        <div class="<?php echo esc_attr("am_post_grid am__col-3 am_layout_{$args["layout"]} {$args["animation"]} "); ?>">
            <?php
                if ( $query->have_posts() ) {
                    while ( $query->have_posts() ) {
                        $query->the_post(); 
                            global $post;
                            $term_list    = get_the_terms($post->ID, "category");
                            $tag_list     = get_the_terms($post->ID, "post_tag");
                            
                            if (!empty($args["tax_query"]['category']) && is_array($args["tax_query"])) {
                                $is_tags     = get_the_terms($post->ID, "post_tag");
                                
                                foreach ($is_tags as $key => $tg) {
                                    $arr_tags[] = $tg->term_id;
                                }
                                
                            }

                            foreach ($tag_list as $key => $tag) {
                                $tag_ids[] = $tag->term_id;
                            }
                           
                            $cat_icon     = get_field("cat_icon", "term_" . $term_list[0]->term_id); 
                            $link         = get_post_meta($post->ID);
                            $ext_link     = $link['external_link_for_post'][0];
                            $extract_arr  = unserialize($ext_link);
                            $usefull_link = !empty($extract_arr) ? $extract_arr['url'] : get_permalink();
                           ?>
                            <div class="am_grid_col">
                                <div class="content_box_top_cat">
                                   <div class="d-flex align-items-center">
                                        <p><?= $term_list[0]->name ?></p>
                                        <img src="<?= $cat_icon ?>"class="cat_icon_blue" />
                                    </div>  
                                </div>
                                <div class="am_single_grid leadership_educational_item content_grid_thumb">
                                    <div class="am_thumb">
                                        <?php the_post_thumbnail("Full"); ?>
                                           <a href="<?php echo $usefull_link; ?>" target="_blank">
                                            <i class="fa fa-plus" aria-hidden="true"></i>
                                            <?php echo the_field('Click_for_details','options'); ?>
                                        </a>
                                    </div>
                                    <div class="am_cont leadership_content">
                                        <h2 class="am__title rtldirect "><?php echo wp_trim_words( get_the_title(), 15, '...' ); ?></h2>
                                        <div class="d-flex justify-content-end align-items-center publish_thumbnail_main home_content_recomend_author">
                                            <?php 
                                                $author_name = get_field('writing');

                                                foreach ($author_name as $key => $post_author) {                      
                                                    echo '<p class="authors_name">' . $post_author['author_name'] . '</p>';
                                                    if ($key != (count($author_name) - 1)) {
                                                        echo '<span style="color: #00987e;">,</span>';
                                                    }
                                                }
                                            ?>
                                            <p class="sec_date_content">|&nbsp;<?php echo get_the_date('d-m-Y'); ?></p> 
                                        </div>     
                                   </div>
                                </div>
                            </div>
                        <?php
                    }
                    if (!empty($args["tax_query"]['category']) && is_array($args["tax_query"])) {
                        echo "<input class='tIds' type='hidden' value='".implode(',',array_unique($arr_tags))."' />"; ?>
                        <script>
                            var ids = jQuery('.tIds').val(); 
                            var array = $.map(ids.split(','), function(value) {
                              return parseInt(value.trim(), 10);
                            });
                            var cats_arr = [];
                            var cats = jQuery('.tags .asr_texonomy')
                            jQuery.each(cats, function(key, val) {
                                cats_arr.push(jQuery(this).attr('data_id'));
                            });
                                
                            cats_arr.forEach(function(cat) {
                                var catElement = jQuery('.tags .asr_texonomy[data_id="' + cat + '"]');
                                if (array.includes(parseInt(cat))) {
                                    catElement.addClass('match');
                                    catElement.removeClass('not-match');
                                } else {
                                    catElement.addClass('not-match');
                                    catElement.removeClass('match');
                                }
                            });
                        </script>
                        <?php
                    }
                   
                } else {
                    echo 'No posts found.';
                }
            echo $pagination_type == "load_more" ? "</div>" : "";
            // Restore original post data
            wp_reset_postdata();
             // echo "<input type='type' value='".implode(',', array_unique($tag_ids))."'/>";
            ?>
        </div>
        <div class="am_posts_navigation">
            <?php
            $big = 999999999; // need an unlikely integer
            $dataNext = $dataPaged + 1;

            $paged = get_query_var("paged") ? absint(get_query_var("paged")) : 1;

            $paginate_links = paginate_links([
                "base" => str_replace($big, "%#%", esc_url(get_pagenum_link($big))),
                "format" => "?paged=%#%",
                "current" => max(1, $dataPaged),
                "prev_next" => true,
                "mid_size" => 2,
                "total" => $query->max_num_pages,
                "prev_text" => __("&laquo; Previous"),
                "next_text" => __("Next &raquo;"),
            ]);

            // Load more button
            if ($pagination_type == "load_more") {
                if ($paginate_links && $dataPaged < $query->max_num_pages) {
                    echo "<button type='button' data-paged='{$dataPaged}' data-next='{$dataNext}' class='{$args["infinite_scroll"]} am-post-grid-load-more'>" .
                    esc_html__("Load More", "ajax-filter-posts") .
                    "</button>";
                 }
            } else {
                // Paginate links
                //  echo "<div id='am_posts_navigation_init'>{$paginate_links}</div>";

                echo "<div id='am_posts_navigation_init'>";
                
                $pre_disabled = ($dataPaged == 1) ? 'disabled' : '';
                $next_disabled = ($dataPaged == $query->max_num_pages) ? 'disabled' : '';

                echo '<a class="page-numbers prev_btn '.($pre_disabled).'" data-page="'.($dataPaged-1).'" href="'.get_site_url().'/en/our-content/page/'.$paged.'/"><i class="fa fa-angle-double-left" aria-hidden="true"></i>לעמוד קודם</a>';

                echo "<div class='nav-position'>".$dataPaged."/".$query->max_num_pages."</div>";

                echo '<a class="page-numbers next_btn '.($next_disabled).'" data-page="'.$dataNext.'" href="'.get_site_url().'/en/our-content/page/'.($paged+1).'/">לעמוד הבא<i class="fa fa-angle-double-right" aria-hidden="true"></i></a>';

                echo "</div>";

            }
            ?>
        </div>
        <?php
    return ob_get_clean();
}

/**
 * Add plugin action links.
 *
 * @since 1.0.0
 * @version 4.0.0
 */
function am_ajax_post_grid_plugin_action_links($links)
{
    $plugin_links = [
        '<a href="' .
        admin_url("admin.php?page=ajax-post-grid") .
        '">' .
        esc_html__("Options", "ajax-filter-posts") .
        "</a>",
    ];
    return array_merge($plugin_links, $links);
}
//add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'am_ajax_post_grid_plugin_action_links' );

