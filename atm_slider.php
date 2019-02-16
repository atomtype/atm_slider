<?php
/**
Plugin Name: Atoms Slider Plugin
URI: www.skrestaurants.com/staging/
Description: Atoms slider plugin dynamic wordpress slider.
Version: 1.0
Author: Atom Type
**/

// Plugin activation and deactivation hooks
function atm_slider_activation() {
}
register_activation_hook( __FILE__, 'atm_slider_activation' );

function atm_slider_deactivation() {
}
register_deactivation_hook( __FILE__, 'atm_slider_deactivation' );

// Attach custom scripts to the plugin
add_action( 'wp_enqueue_scripts', 'atm_scripts' );
function atm_scripts() {

	wp_enqueue_script( 'jquery' );

	wp_register_script( 'bootstrap_min_js', plugins_url( 'js/bootstrap.min.js', __FILE__ ) );
	wp_enqueue_script( 'bootstrap_min_js' );

	wp_register_script( 'jquery_min_js', plugins_url( 'js/jquery-3.3.1.min.js', __FILE__ ), array("jquery") );
	wp_enqueue_script( 'jquery_min_js' );

	wp_register_script( 'popper_min_js', plugins_url( 'js/popper.min.js', __FILE__ ) );
	wp_enqueue_script( 'popper_min_js' );

}

// Attach custom styles to the plugin
add_action( 'wp_enqueue_scripts', 'atm_styles' );
function atm_styles() {

	wp_register_style( 'bootstrap_min_css', plugins_url( 'css/bootstrap.min.css', __FILE__ ) );
	wp_enqueue_style( 'bootstrap_min_css' );

}

// Register shortcode to use anywhere in the wordpress
add_shortcode( "atm_slider", "atm_display_slider" );
function atm_display_slider( $attr, $content ) {

    extract(shortcode_atts(array(
                'id' => ''
                    ), $attr));

    $gallery_images = get_post_meta($id, "_atm_gallery_images", true);
    $gallery_images = ($gallery_images != '') ? json_decode($gallery_images) : array();



    $plugins_url = plugins_url();


    $html = '<div id="myCarousel" class="carousel slide carousel-fade revealable" data-ride="carousel">
		<ol class="carousel-indicators carousel-main">
		  <li data-target="#myCarousel" data-slide-to="0" class="active"></li>
		  <li data-target="#myCarousel" data-slide-to="1"></li>
		  <li data-target="#myCarousel" data-slide-to="2"></li>
		</ol>
		<div class="carousel-inner">';

    // foreach ($gallery_images as $gal_img) {
        if ($gallery_images != "") {
            $html .= "<div class='atm-carousel-item carousel-item active'>
		    			<img src='".$gallery_images[0]."' alt='Image' style='width:100%;'>
	  				</div>
	  				<div class='atm-carousel-item carousel-item'>
		    			<img src='".$gallery_images[1]."' alt='Image' style='width:100%;'>
	  				</div>
	  				<div class='atm-carousel-item carousel-item'>
		    			<img src='".$gallery_images[2]."' alt='Image' style='width:100%;'>
	  				</div>";
        }
    // }

    $html .= '</div>
		<a class="carousel-control-prev" href="#myCarousel" role="button" data-slide="prev">
		  <span class="carousel-control-prev-icon" aria-hidden="true"></span>
		  <span class="sr-only">Previous</span>
		</a>
		<a class="carousel-control-next" href="#myCarousel" role="button" data-slide="next">
		  <span class="carousel-control-next-icon" aria-hidden="true"></span>
		  <span class="sr-only">Next</span>
		</a>
	</div>';

    return $html;
}

// Initialize the slider function to add at the backend of the wordpress
add_action( 'init', 'atm_register_slider' );

function atm_register_slider() {
    $labels = array(
        'menu_name' => _x('Sliders', 'atms_slider'),
    );

    $args = array(
        'labels' => $labels,
        'hierarchical' => true,
        'description' => 'Slideshows',
        'supports' => array('title', 'editor'),
        'public' => true,
        'show_ui' => true,
        'show_in_menu' => true,
        'show_in_nav_menus' => true,
        'publicly_queryable' => true,
        'exclude_from_search' => false,
        'has_archive' => true,
        'query_var' => true,
        'can_export' => true,
        'rewrite' => true,
        'capability_type' => 'post'
    );

    register_post_type('atms_slider', $args);
}

/* Define shortcode column in Rhino Slider List View */
add_filter('manage_edit-atms_slider_columns', 'atm_set_custom_edit_atms_slider_columns');
add_action('manage_atms_slider_posts_custom_column', 'atm_custom_atms_slider_column', 10, 2);

function atm_set_custom_edit_atms_slider_columns($columns) {
    return $columns
    + array('slider_shortcode' => __('Shortcode'));
}

function atm_custom_atms_slider_column($column, $post_id) {

    $slider_meta = get_post_meta($post_id, "_atm_slider_meta", true);
    $slider_meta = ($slider_meta != '') ? json_decode($slider_meta) : array();

    switch ($column) {
        case 'slider_shortcode':
            echo "[atm_slider id='$post_id' /]";
            break;
    }
}

// Insert Meta Boxes to add manual images to the slides
add_action( 'add_meta_boxes', 'atm_slider_meta_box' );

function atm_slider_meta_box() {

    add_meta_box("atm-slider-images", "Slider Images", 'atm_view_slider_images_box', "atms_slider", "normal");
}

function atm_view_slider_images_box() {
    global $post;

    $gallery_images = get_post_meta($post->ID, "_atm_gallery_images", true);
    // print_r($gallery_images);exit;
    $gallery_images = ($gallery_images != '') ? json_decode($gallery_images) : array();

    // Use nonce for verification
    $html = '<input type="hidden" name="atm_slider_box_nonce" value="' . wp_create_nonce(basename(__FILE__)) . '" />';

    $html .= '<table class="form-table">';

    $html .= "
          <tr>
            <th style=''><label for='Upload Images'>Image 1</label></th>
            <td><input name='gallery_img[]' id='atm_slider_upload' type='text' value='" . $gallery_images[0] . "'  /></td>
          </tr>
          <tr>
            <th style=''><label for='Upload Images'>Image 2</label></th>
            <td><input name='gallery_img[]' id='atm_slider_upload' type='text' value='" . $gallery_images[1] . "' /></td>
          </tr>
          <tr>
            <th style=''><label for='Upload Images'>Image 3</label></th>
            <td><input name='gallery_img[]' id='atm_slider_upload' type='text'  value='" . $gallery_images[2] . "' /></td>
          </tr>
        </table>";

    echo $html;
}

/* Save Slider Options to database */
add_action('save_post', 'atm_save_slider_info');

function atm_save_slider_info($post_id) {


    // verify nonce
    if (!wp_verify_nonce($_POST['atm_slider_box_nonce'], basename(__FILE__))) {
        return $post_id;
    }

    // check autosave
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return $post_id;
    }

    // check permissions
    if ('atms_slider' == $_POST['post_type'] && current_user_can('edit_post', $post_id)) {

        /* Save Slider Images */
        //echo "<pre>";print_r($_POST['gallery_img']);exit;
        $gallery_images = (isset($_POST['gallery_img']) ? $_POST['gallery_img'] : '');
        $gallery_images = strip_tags(json_encode($gallery_images));
        update_post_meta($post_id, "_atm_gallery_images", $gallery_images);

       
    } else {
        return $post_id;
    }
}