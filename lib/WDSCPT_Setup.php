<?php
/**
 * Plugin class for generating Custom Post Types.
 *
 */
class WDSCPT_Setup {

	private $post_type;
	public $single;
	private $plural;
	public $plurals;
	private $registered;
	public $slug;
	private $args;

	/**
	 * Holds copy of instance, so other plugins can remove our hooks.
	 *
	 * @since 1.0
	 * @link http://core.trac.wordpress.org/attachment/ticket/16149/query-standard-format-posts.php
	 * @link http://twitter.com/#!/markjaquith/status/66862769030438912
	 *
	 */
	static $instance;


	function WDSCPT_Setup( $post_type, $plural = '', $registered = '', $args = array() ) {
		WDSCPT_Setup::__construct( $post_type, $plural, $registered, $args );
	}

	function __construct( $post_type, $plural = '', $registered = '', $args = array() ) {

		self::$instance = $this;

		$this->post_type = $post_type;
		$this->single = $this->post_type;
		$this->plural = !empty( $plural ) ? $plural : $post_type .'s';
		$this->plurals = $this->plural;
		$this->registered = !empty( $registered ) ? $registered : sanitize_title( $this->plural );
		$this->slug = $this->registered;
		$this->args = (array) $args;


		add_action( 'init', array( &$this, 'cpt_loop' ) );
		add_filter( 'post_updated_messages', array( &$this, 'messages' ) );
		add_filter( 'manage_edit-'. $this->slug .'_columns', array( &$this, 'columns' ) );
		add_action( 'manage_posts_custom_column', array( &$this, 'columns_display' ) );
		add_action( 'admin_head', array( &$this, 'cpt_icons' ) );


	}

	public function cpt_loop() {

		$file = WDSCFS_PATH .'lib/css/'. $this->registered .'.png';
		$path = WDSCFS_URL .'lib/css/'. $this->registered .'.png';
		$img = file_exists( $file ) ? $path : null;

		//set default custom post type options
		$defaults = array(
			'labels' => array(
				'name' => $this->plural,
				'singular_name' => $this->post_type,
				'add_new' => 'Add New ' .$this->post_type,
				'add_new_item' => 'Add New ' .$this->post_type,
				'edit_item' => 'Edit ' .$this->post_type,
				'new_item' => 'New ' .$this->post_type,
				'all_items' => 'All ' .$this->plural,
				'view_item' => 'View ' .$this->post_type,
				'search_items' => 'Search ' .$this->plural,
				'not_found' =>  'No ' .$this->plural .' found',
				'not_found_in_trash' => 'No ' .$this->plural .' found in Trash',
				'parent_item_colon' => '',
				'menu_name' => $this->plural
			),
			'public' => true,
			'publicly_queryable' => true,
			'show_ui' => true,
			'show_in_menu' => true,
			'query_var' => true,
			'menu_icon' => $img,
			'rewrite' => true,
			'capability_type' => 'post',
			'has_archive' => true,
			'hierarchical' => false,
			'menu_position' => null,
			'supports' => array( 'title', 'editor', 'excerpt' )
		);

		$args = wp_parse_args( $this->args, $defaults );
		register_post_type( $this->registered, $args );
	}

	public function messages( $messages ) {
		global $post, $post_ID;

		$messages[$this->post_type] = array(
			0 => '', // Unused. Messages start at index 1.
			1 => sprintf( __( '%1$s updated. <a href="%2$s">View %1$s</a>' ), $this->post_type, esc_url( get_permalink( $post_ID ) ) ),
			2 => __( 'Custom field updated.' ),
			3 => __( 'Custom field deleted.' ),
			4 => sprintf( __( '%1$s updated.' ), $this->post_type ),
			/* translators: %s: date and time of the revision */
			5 => isset( $_GET['revision'] ) ? sprintf( __( '%1$s restored to revision from %2$s' ), $this->post_type , wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
			6 => sprintf( __( '%1$s published. <a href="%2$s">View %1$s</a>' ), $this->post_type, esc_url( get_permalink( $post_ID ) ) ),
			7 => sprintf( __( '%1$s saved.' ), $this->post_type ),
			8 => sprintf( __( '%1$s submitted. <a target="_blank" href="%2$s">Preview %1$s</a>' ), $this->post_type, esc_url( add_query_arg( 'preview', 'true', get_permalink( $post_ID ) ) ) ),
			9 => sprintf( __( '%1$s scheduled for: <strong>%2$s</strong>. <a target="_blank" href="%3$s">Preview %1$s</a>' ), $this->post_type,
					// translators: Publish box date format, see http://php.net/date
					date_i18n( __( 'M j, Y @ G:i' ), strtotime( $post->post_date ) ), esc_url( get_permalink( $post_ID ) ) ),
			10 => sprintf( __( '%1$s draft updated. <a target="_blank" href="%2$s">Preview %1$s</a>' ), $this->post_type, esc_url( add_query_arg( 'preview', 'true', get_permalink( $post_ID ) ) ) ),
		);
		return $messages;

	}

	public function columns( $columns ) {
		return $columns;
	}

	public function columns_display( $column ) {
		// placeholder
	}

	function cpt_icons() {
		$screen = get_current_screen()->id;
?>
<style type="text/css">
	#adminmenu li.menu-top:hover .wp-menu-image img,
	#adminmenu li.wp-has-current-submenu .wp-menu-image img {
		top: 0;
	}
	<?php

	if ( $screen == 'edit-'. $this->registered || $screen == $this->registered ) {
		$file = WDSCFS_PATH .'lib/css/'. $this->registered .'32.png';
		$path = WDSCFS_URL .'lib/css/'. $this->registered .'32.png';
		if ( file_exists( $file ) ) {
	?>#icon-edit.icon32.icon32-posts-<?php echo $this->registered; ?> {
		background-position: 0 0;
		background-image: url('<?php echo $path; ?>');
	}
	<?php

		}

	}
	?>#menu-posts-<?php $this->registered; ?> .wp-menu-image a {
		overflow: hidden;
	}
	#adminmenu .menu-icon-<?php echo $this->registered; ?> div.wp-menu-image {
		overflow: hidden;
	}
	#menu-posts-<?php $this->registered; ?> .wp-menu-image img {
		opacity: 1;
		filter: alpha(opacity=100);
		position: relative;
		top: -24px;
	}
</style>
<?php
	}

}
