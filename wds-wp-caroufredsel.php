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

		add_action( 'init', 'scripts_styles' );

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
		wp_register_script( 'caroufredsel', WDSCOLORBOX_URL .'js/caroufredsel/jquery.carouFredSel-6.2.0-packed.js', array('jquery'), '6.2.0' );
		wp_register_script( 'caroufredsel-init', WDSCOLORBOX_URL .'js/caroufredsel-init.js', array( 'caroufredsel', 'jquery' ), '1.0' );
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
	wp_localize_script( 'caroufredsel-init', 'wdscaroufredsel', array( 'element' => $element, 'params' => $caroufredsel_params ) );
}
