<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'Custom_Popup_Builder_Utils' ) ) {

	class Custom_Popup_Builder_Utils {

		public static function get_post_types() {

			$post_types = get_post_types( array( 'public' => true ), 'objects' );

			$deprecated = apply_filters(
				'custom-popup-builder/post-types-list/deprecated',
				array(
					'attachment',
					'elementor_library',
					custom_popup_builder()->post_type->slug(),
				)
			);

			$result = array();

			if ( empty( $post_types ) ) {
				return $result;
			}

			foreach ( $post_types as $slug => $post_type ) {

				if ( in_array( $slug, $deprecated ) ) {
					continue;
				}

				$result[ $slug ] = $post_type->label;

			}

			return $result;

		}

		public static function get_avaliable_popups() {

			$avaliable_popups = [];

			$avaliable_popups = [
				'' => esc_html__( 'Not Selected', 'custom-popup-builder' ),
			];

			$query_args = apply_filters( 'custom_popup_builder_default_query_args',
				[
					'post_type'      => custom_popup_builder()->post_type->slug(),
					'order'          => 'DESC',
					'orderby'        => 'date',
					'posts_per_page' => -1,
					'post_status'    => 'publish',
				]
			);

			$popups_query = new WP_Query( $query_args );

			if ( is_wp_error( $popups_query ) ) {
				return false;
			}

			if ( $popups_query->have_posts() ) {

				foreach ( $popups_query->posts as $popup ) {

					$post_id = $popup->ID;
					$post_title = $popup->post_title;
					$avaliable_popups[ $post_id ] = $post_title;
				}
			} else {
				return false;
			}

			return $avaliable_popups;
		}

		public static function get_roles_list() {

			if ( ! function_exists( 'get_editable_roles' ) ) {
				require_once ABSPATH . 'wp-admin/includes/user.php';
			}

			$roles['guest'] = esc_html__( 'Guest', 'custom-popup-builder' );

			foreach ( get_editable_roles() as $role_slug => $role_data ) {
				$roles[ $role_slug ] = $role_data['name'];
			}

			return $roles;
		}

		public static function get_taxonomies() {

			$taxonomies = get_taxonomies( array(
				'public'   => true,
				'_builtin' => false
			), 'objects' );

			$deprecated = apply_filters(
				'custom-popup-builder/taxonomies-list/deprecated',
				array()
			);

			$result = array();

			if ( empty( $taxonomies ) ) {
				return $result;
			}

			foreach ( $taxonomies as $slug => $tax ) {

				if ( in_array( $slug, $deprecated ) ) {
					continue;
				}

				$result[ $slug ] = $tax->label;

			}

			return $result;

		}

		public static function search_posts_by_type( $type, $query ) {

			add_filter( 'posts_where', array( __CLASS__, 'force_search_by_title' ), 10, 2 );

			$posts = get_posts( array(
				'post_type'           => $type,
				'ignore_sticky_posts' => true,
				'posts_per_page'      => -1,
				'suppress_filters'    => false,
				's_title'             => $query,
				'post_status'         => [ 'publish', 'private' ],
			) );

			remove_filter( 'posts_where', array( __CLASS__, 'force_search_by_title' ), 10, 2 );

			$result = array();

			if ( ! empty( $posts ) ) {
				foreach ( $posts as $post ) {
					$result[] = array(
						'id'   => $post->ID,
						'text' => $post->post_title,
					);
				}
			}

			return $result;
		}

		public static function force_search_by_title( $where, $query ) {

			$args = $query->query;

			if ( ! isset( $args['s_title'] ) ) {
				return $where;
			} else {
				global $wpdb;

				$searh = esc_sql( $wpdb->esc_like( $args['s_title'] ) );
				$where .= " AND {$wpdb->posts}.post_title LIKE '%$searh%'";

			}

			return $where;
		}

		public static function search_terms_by_tax( $tax, $query ) {

			$terms = get_terms( array(
				'taxonomy'   => $tax,
				'hide_empty' => false,
				'name__like' => $query,
			) );

			$result = array();

			if ( ! empty( $terms ) ) {
				foreach ( $terms as $term ) {
					$result[] = array(
						'id'   => $term->term_id,
						'text' => $term->name,
					);
				}
			}

			return $result;

		}

		public static function get_avaliable_mailchimp_list() {
			$mailchimp_data = get_option( 'custom-popup-builder-settings_mailchimp', [] );

			$apikey = custom_popup_builder()->settings->get( 'apikey', '' );

			if ( empty( $mailchimp_data ) ) {
				return false;
			}

			if ( empty( $apikey ) ) {
				return false;
			}

			if ( ! array_key_exists( $apikey , $mailchimp_data ) ) {
				return false;
			}

			$mailchimp_account = $mailchimp_data[ $apikey ];

			if ( ! array_key_exists( 'lists' , $mailchimp_account ) ) {
				return false;
			}

			$lists = $mailchimp_account['lists'];

			$avaliable_lists = [];

			foreach ( $lists as $key => $data ) {
				$info = $data['info'];
				$avaliable_lists[ $info['id'] ] = $info['name'];
			}

			if ( ! empty( $avaliable_lists ) ) {
				return $avaliable_lists;
			}

			return false;

		}

		public static function get_avaliable_mailchimp_merge_fields( $list_id ) {
			$mailchimp_data = get_option( 'custom-popup-builder-settings_mailchimp', [] );

			$apikey = custom_popup_builder()->settings->get( 'apikey', '' );

			if ( empty( $mailchimp_data ) ) {
				return false;
			}

			if ( empty( $apikey ) ) {
				return false;
			}

			if ( ! array_key_exists( $apikey , $mailchimp_data ) ) {
				return false;
			}

			$mailchimp_account = $mailchimp_data[ $apikey ];

			if ( ! array_key_exists( 'lists', $mailchimp_account ) ) {
				return false;
			}

			$lists = $mailchimp_account['lists'];

			if ( ! array_key_exists( $list_id, $lists ) ){
				return false;
			}

			$list = $lists[ $list_id ];

			if ( ! array_key_exists( 'merge_fields', $list ) ) {
				return false;
			}

			return $list['merge_fields'];
		}

		public static function get_milliseconds_by_tag( $tag = 'none' ) {

			if ( 'none' === $tag ) {
				return 'none';
			}

			switch ( $tag ) {

				case 'minute':
					$delay = MINUTE_IN_SECONDS * 1000;
					break;

				case '10minutes':
					$delay = 10 * MINUTE_IN_SECONDS * 1000;
					break;

				case '30minutes':
					$delay = 30 * MINUTE_IN_SECONDS * 1000;
					break;

				case 'hour':
					$delay = HOUR_IN_SECONDS * 1000;
					break;

				case '3hours':
					$delay = 3 * HOUR_IN_SECONDS * 1000;
					break;

				case '6hours':
					$delay = 6 * HOUR_IN_SECONDS * 1000;
					break;

				case '12hours':
					$delay = 12 * HOUR_IN_SECONDS * 1000;
					break;

				case 'day':
					$delay = DAY_IN_SECONDS * 1000;
					break;

				case '3days':
					$delay = 3 * DAY_IN_SECONDS * 1000;
					break;

				case 'week':
					$delay = WEEK_IN_SECONDS * 1000;
					break;

				case 'month':
					$delay = MONTH_IN_SECONDS * 1000;
					break;

				default:
					$delay = 'none';
					break;
			}

			return $delay;
		}

	}
}
