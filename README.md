WP-carouFredSel
===============

Simple plugin that will A) enqueue carouFredSel when requested, and B) (if requested) create a custom "featured" post type for the carousel

### To enqueue carouFredSel:
In your theme functions file or plugin, place the function
```php
wds_caroufredsel();
```

### To configure a carouFredSel instance:
```php
wds_caroufredsel( '#element' );
```

### To pass configuration parameters to the carouFredSel instance:
```php
$args = array(
	'width' => 870,
	'items' => 8,
	'scroll' => 4
);
wds_caroufredsel( '#element', $args );
```

### To enable a featured custom post type:
```php
wds_enable_cfs_cpt();
```

### To grab data (wrapped in a transient) from the featured custom post type:
```php
wds_fcs_get_featured();
```

### wds_fcs_get_featured() takes 3 arguments:

**$WP_Query_args:** pass any WP_Query arguments you want that aren't in the default
Default: array(
	'post_type' => 'featured-entries',
	'posts_per_page' => 5,
	'post_status'    => 'publish',
	'no_found_rows'  => true,
)

**$return_full_query:** whether to return the query from WP_Query or a subset of the data (Title, link meta, content, and featured image)
Default: false (return the subset of data instead of the full query)

**$use_transient:** Whether to wrap data in a transient to save on load time and queries to the database. The transient resets when a featured entry is saved in the admin.
Default: true (use transient)

### To grab data (wrapped in a transient) from featured custom post type and then display it in a carouFredSel instance:
```php
// Assumes you have 'wds_enable_cfs_cpt();' in your themes functions file.

/**
 * Enqueu carouFredSel and loop through our featured posts
 */
function dma_loop_featured() {
	// make sure our plugin is activated
	if ( !function_exists('wds_fcs_get_featured') )
		return;
	$featured = wds_fcs_get_featured();
	// if our query is empty, bail here
	if ( !$featured )
		return;
	?>
	<div class="wds-featured-wrap">
		<ul class="wds-featured">
		<?php
		foreach ( $featured as $id => $feature ) {

			// feature's image
			$html = wds_maybe_link( $feature, 'image' );
			// feature's title
			$html .= wds_maybe_link( $feature, 'title', 'h2' );
			// feature's content
			$html .= wds_maybe_link( $feature, 'content' );

			echo '<li>'. $html .'</li>';
		}
		?>
		</ul><!-- .wds-featured -->
	</div><!-- .wds-featured-wrap -->

	<?php
	// enqueue carouFredSel and configure its options
	wds_caroufredsel( '.wds-featured', array(
		'width' => 572,
		'items' => 1,
		'scroll' => 1,
		'scroll' => array(
			'fx' => 'crossfade'
		),
		'auto' => array(
			'easing' => 'linear',
			'duration' => 1000,
			'timeoutDuration' => 2000,
			'pauseOnHover' => true
		),
	) );

}

/**
 * Wrap feature in url if it has one
 */
function wds_maybe_link( $feature, $index, $wrap = false ) {

	if ( !$index )
		return '';

	if ( !$feature['url'] )
		return $feature[$index];

	$html = '<a class="'. $index .'" href="'. $feature['url'] .'">'. $feature[$index] .'</a>';
	if ( $wrap )
		$html = '<'. $wrap .'>'. $html .'</'. $wrap .'>';

	return $html;
}
```