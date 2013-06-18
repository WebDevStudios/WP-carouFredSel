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
		$include_cpt = wds_cfs_cpt_option();

		if ( !$include_cpt )
			return;
		// Featured Post-Type Setup
		if ( !class_exists( 'WDSCPT_Setup' ) )
			require_once( WDSCFS_PATH .'lib/WDSCPT_Setup.php' );
		if ( !class_exists( 'Featured_CPT_Setup' ) )
			require_once( WDSCFS_PATH .'lib/Featured_CPT_Setup.php' );
		$GLOBALS['wds_cfs_featured_cpt'] = new Featured_CPT_Setup( $include_cpt );
	}

	public function scripts_styles() {
		wp_register_script( 'caroufredsel', WDSCFS_URL .'lib/js/carouFredSel/jquery.carouFredSel-6.2.1-packed.js', array('jquery'), '6.2.1' );
		wp_register_script( 'caroufredsel-init', WDSCFS_URL .'lib/js/caroufredsel-init.js', array( 'caroufredsel', 'jquery' ), '1.0' );
	}

}
new WDSCarouFredSel;

/**
 * Gets cpt registration option only once per page
 */
function wds_cfs_cpt_option( $set = false ) {
	$key = 'wds_cfs_cpt_option';
	$option = &$GLOBALS[$key];
	if ( !$set ) {
		// check global first
		if ( isset( $option ) )
			return $option;
		// check transient second
		$option = get_transient( $key );
		if ( $option )
			return $option;
	}
	// get option third
	$option = get_option( 'wdscaroufredsel_cpt' );
	// and save our transient (week)
	set_transient( $key, $option, 60*60*24*7 );
	return $option;
}

/**
 * enable/disable a featured content cpt
 */
function wds_enable_cfs_cpt( $enable = true ) {
	if ( !is_admin() )
		return;
	// delete option to include cpt
	if ( !$enable ) {
		delete_option( 'wdscaroufredsel_cpt', true );
		wds_cfs_cpt_option( true );
		return;
	}

	if ( !get_option( 'wdscaroufredsel_cpt' ) ) {
		$enable = is_array( $enable ) ? $enable : true;
		update_option( 'wdscaroufredsel_cpt', $enable );
		wds_cfs_cpt_option( true );
	}
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

	// if the cpt isn't enabled then bail
	if ( !$include_cpt = wds_cfs_cpt_option() )
		return false;

	// Check query var to bypass/reset transients
	$bypass = ( isset( $_GET['delete-trans'] ) && $_GET['delete-trans'] == true );
	$use_transient = $bypass ? false : $use_transient;

	$trans_id = 'wds_cfs_cpt_data';
	// look for and append a suffix if independent transients are required (passed as an arg)
	if ( isset( $WP_Query_args['trans_id'] ) ) {
		$trans_id .= '_'. $WP_Query_args['trans_id'];
		unset( $WP_Query_args['trans_id'] );
	}

	$trans = false;
	// if we're using a transient
	if ( $use_transient )
		$trans = get_transient( $trans_id );
	// and we found a transient
	if ( $trans )
		// return it
		return $trans;

	// get registered cpt slug or use default
	$post_type = isset( $GLOBALS['wds_cfs_featured_cpt']->slug ) ? $GLOBALS['wds_cfs_featured_cpt']->slug : 'featured-entries';
	// get our query with our merged arguments
	$query = new WP_Query( wp_parse_args( $WP_Query_args, array(
		'post_type' => $post_type,
		'posts_per_page' => 5,
		'post_status'    => 'publish',
		'no_found_rows'  => true,
	) ) );
	// if no posts, bail
	if ( !$query->have_posts() )
		return false;
	// if the full query is requested, return it
	if ( $return_full_query ) {
		if ( $use_transient || $bypass )
			set_transient( $trans_id, $query, 60*60*24 );
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

	// save our transient (1 day)
	if ( $use_transient || $bypass )
		set_transient( $trans_id, $data, 60*60*24 );

	// return our arrayed data
	return $data;
}
