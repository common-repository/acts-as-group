<?php

class AAG_Message {

	const post_type = 'aag_message';

	public static $found_items = 0;

	var $initial = true;

	public $id;
	public $title;
	public $content;
	public $from;
	public $to;
	public $group;
	public $date;

	public static function register_post_type() {
		register_post_type( self::post_type );
	}

	public static function find( $args = '' ) {
		$defaults = array(
			'posts_per_page' => 10,
			'offset' => 0,
			'orderby' => 'ID',
			'order' => 'ASC',
			'meta_key' => '',
			'meta_value' => '',
			'post_status' => 'any' );

		$args = wp_parse_args( $args, $defaults );

		$args['post_type'] = self::post_type;

		$q = new WP_Query();
		$posts = $q->query( $args );

		self::$found_items = $q->found_posts;

		$objs = array();

		foreach ( (array) $posts as $post )
			$objs[] = new self( $post );

		return $objs;
	}

	public function __construct( $post = null ) {
		if ( ! empty( $post ) )
			$post = get_post( $post );

		if ( ! empty( $post ) && self::post_type == get_post_type( $post ) ) {
			$this->initial = false;
			$this->id = $post->ID;
			$this->title = $post->post_title;
			$this->content = $post->post_content;
			$this->from = get_post_meta( $post->ID, '_from', true );
			$this->to = get_post_meta( $post->ID, '_to', true );
			$this->group = get_post_meta( $post->ID, '_group', true );
			$this->date = $post->post_date;
		}
	}

	public function save() {
		$post_title = $this->title;
		$post_content = $this->content;

		$postarr = array(
			'ID' => absint( $this->id ),
			'post_type' => self::post_type,
			'post_status' => 'publish',
			'post_title' => $post_title,
			'post_content' => $post_content );

		$post_id = wp_insert_post( $postarr );

		if ( $post_id ) {
			$this->initial = false;
			$this->id = $post_id;
			update_post_meta( $post_id, '_from', $this->from );
			update_post_meta( $post_id, '_to', $this->to );
			update_post_meta( $post_id, '_group', $this->group );
		}

		return $post_id;
	}

}

?>