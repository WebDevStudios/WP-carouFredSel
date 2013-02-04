<?php
/*
Plugin Name: WDS CarouFredSel Slider
Plugin URI: http://webdevstudios.com/wordpress/support-packages/
Description: Adds a CarouFredSel Slider with options and button navigation.
Author: WebdevStudios LLC
Author URI: http://webdevstudios.com
Version: 1.0
*/

class WDSCarouFredSel {

	private $plugin_name = 'WDS CarouFredSel Slider';
	private $cpt;

	function __construct() {

		define( 'WDSCFS_PATH', apply_filters( 'wdscaroufredsel_path', plugin_dir_path( __FILE__ ) ) );
		define( 'WDSCFS_URL', apply_filters( 'wdscaroufredsel_url', plugins_url('/', __FILE__ ) ) );

		add_action( 'init', array( $this, 'scripts_styles' ) );

		// include a featured content cpt (off by default)
		$include_cpt = get_option( 'wdscaroufredsel_cpt' ) ? true : false;

		if ( !$include_cpt )
			return;
		// Snippet Post-Type Setup
		if ( !class_exists( 'CPT_Setup' ) )
			require_once( WDSCFS_PATH .'lib/CPT_Setup.php' );
		require_once( WDSCFS_PATH .'lib/Featured_CPT_Setup.php' );
		$this->cpt = new Featured_CPT_Setup();
	}

	public function scripts_styles() {
		wp_register_script( 'caroufredsel', WDSCFS_URL .'lib/js/caroufredsel/jquery.carouFredSel-6.2.0-packed.js', array('jquery'), '6.2.0' );
		wp_register_script( 'caroufredsel-init', WDSCFS_URL .'lib/js/caroufredsel-init.js', array( 'caroufredsel', 'jquery' ), '1.0' );
	}

}
new WDSCarouFredSel;

/**
 * enable/disable a featured content cpt
 */
function wds_enable_cfs_cpt( $enable = true ) {
	if ( !is_admin() )
		return;

	if ( $enable && !get_option( 'wdscaroufredsel_cpt' ) )
		update_option( 'wdscaroufredsel_cpt', true );
	elseif ( !$enable )
		delete_option( 'wdscaroufredsel_cpt', true );
}

function wds_caroufredsel( $element = false, $caroufredsel_params = array() ) {

	// enqueue caroufredsel.
	wp_enqueue_script( 'caroufredsel' );

	// if no element is passed, we'll just leave it enqueued and configuration will be added elsewhere
	if ( !$element )
		return;

	$defaults = array(
		// 'hierarchical' => true,
		// 'labels' => $labels,
		// 'show_ui' => true,
		// 'query_var' => true,
		// 'rewrite' => array( 'slug' => $this->slug ),
	);
	$caroufredsel_params = wp_parse_args( $caroufredsel_params, $defaults );

	wp_enqueue_script( 'caroufredsel-init' );
	// send data to caroufredsel

	$data = array( 'element' => $element );

	if ( !empty( $caroufredsel_params ) )
		$data['params'] = $caroufredsel_params;

	wp_localize_script( 'caroufredsel-init', 'wdscaroufredsel', $data );
}

function wds_fcs_get_featured( $WP_Query_args = array(), $return_full_query = false, $use_transient = true ) {
	// if the cpt isn't enabled
	if ( !get_option( 'wdscaroufredsel_cpt' ) )
		return false;

	$trans = false;
	// if we're using a transient
	if ( $use_transient )
		$trans = get_transient( 'wds_cfs_cpt_data' );
	// and we found a transient
	if ( $trans )
		// return it
		return $trans;

	// get our query with our merged arguments
	$query = new WP_Query( wp_parse_args( $WP_Query_args, array(
		'post_type' => 'featured-entries',
		'posts_per_page' => 5,
		'post_status'    => 'publish',
		'no_found_rows'  => true,
	) ) );
	// if no posts, bail
	if ( !$query->have_posts() )
		return false;
	// if the full query is requested, return it
	if ( $return_full_query ) {
		if ( $use_transient )
			set_transient( 'wds_cfs_cpt_data', $query, 60*60*24 );
		return $query;
	}

	$data = array();
	while ( $query->have_posts() ) : $query->the_post();

		$id = get_the_ID();
		$url = get_post_meta( $id, '_wdsfeatured_link', true );

		$data[$id] = apply_filters( 'wdscaroufredsel_cpt_data', array(
			'title' => get_the_title( $id ),
			'url' => !empty( $url ) ? esc_url( $url ) : false,
			'content' => get_the_content(),
			'image' => get_the_post_thumbnail( $id, apply_filters( 'wdscaroufredsel_cpt_image_name', 'post-thumbnail' ) ),
		) );

	endwhile;
	wp_reset_postdata();

	// save our transient
	if ( $use_transient )
		set_transient( 'wds_cfs_cpt_data', $data, 60*60*24 );

	// return our arrayed data
	return $data;
}
