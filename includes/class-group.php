<?php

class AAG_Group {

	const post_type = 'aag_group';

	public static $found_items = 0;

	var $initial = true;

	public $id;
	public $title;
	public $description;
	public $status;

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
			$this->description = get_post_meta( $post->ID, '_description', true );
			$this->status = get_post_meta( $post->ID, '_status', true );
		}
	}

	public function save() {
		$post_title = $this->title;
		$post_content = $this->description;

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
			update_post_meta( $post_id, '_description', $this->description );
			update_post_meta( $post_id, '_status', $this->status );
		}

		return $post_id;
	}

	public function trash() {
		if ( empty( $this->id ) )
			return;

		if ( ! EMPTY_TRASH_DAYS )
			return $this->delete();

		$post = wp_trash_post( $this->id );

		return (bool) $post;
	}

	public function untrash() {
		if ( empty( $this->id ) )
			return;

		$post = wp_untrash_post( $this->id );

		return (bool) $post;
	}

	public function delete() {
		if ( empty( $this->id ) )
			return;

		if ( $post = wp_delete_post( $this->id, true ) )
			$this->id = 0;

		return (bool) $post;
	}

}

?>