WP-carouFredSel
===============

Simple plugin that will A) enqueue carouFredSel when requested, and B) (if requested) create a custom "featured" post type for the carousel

To enqueue carouFredSel:
In your theme functions file or plugin, place the function
```php
wds_caroufredsel();
```

To configure a carouFredSel instance:
```php
wds_caroufredsel( '#element' );
```

To pass configuration parameters to the carouFredSel instance:
```php
$args = array(
	width => 870,
	items => 8,
	scroll => 4
);
wds_caroufredsel( '#element', $args );
```

To enable a featured custom post type:
```php
wds_enable_cfs_cpt();
```

