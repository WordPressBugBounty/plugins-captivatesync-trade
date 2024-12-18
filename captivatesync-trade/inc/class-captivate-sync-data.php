<?php
/**
 * Used to register post type and taxonomies
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'CFMH_Hosting_Data' ) ) :

	/**
	 * Hosting Data class
	 *
	 * @since 1.0
	 */
	class CFMH_Hosting_Data {

		/**
		 * Register captivate_podcast, captivate_category, and captivate_tag
		 *
		 * @since 1.0
		 */
		public static function register() {

			$cfm_general_settings = get_option( 'cfm_general_settings' );

			// Archive title.
			$archive_title = ( isset( $cfm_general_settings['archive_title'] ) && '' != $cfm_general_settings['archive_title'] ) ? $cfm_general_settings['archive_title'] : 'Captivate Podcasts';

			// Archive enable/disable and slug.
			if ( ( isset( $cfm_general_settings['archive_enable'] ) && '0' == $cfm_general_settings['archive_enable'] ) ) {
				$has_archive = false;
			}
			else {
				$has_archive = ( isset( $cfm_general_settings['archive_slug'] ) && '' != $cfm_general_settings['archive_slug']) ? $cfm_general_settings['archive_slug'] : true;
			}

			// Single posts slug.
			$single_slug = ( isset( $cfm_general_settings['single_slug'] ) && '' != $cfm_general_settings['single_slug'] ) ? $cfm_general_settings['single_slug'] : 'captivate-podcast';

			// Captivate Podcast Post Type.
			$labels = array(
				'name'               => __( $archive_title, 'captivate-fm-sync' ),
				'singular_name'      => __( 'Captivate Podcast', 'captivate-fm-sync' ),
				'add_new'            => _x( 'Create Podcast', 'captivate-fm-sync', 'captivate-fm-sync' ),
				'add_new_item'       => __( 'Create Podcast', 'captivate-fm-sync' ),
				'edit_item'          => __( 'Edit', 'captivate-fm-sync' ),
				'new_item'           => __( 'Create Captivate Podcast', 'captivate-fm-sync' ),
				'view_item'          => __( 'View Captivate Podcast', 'captivate-fm-sync' ),
				'search_items'       => __( 'Search Captivate Podcasts', 'captivate-fm-sync' ),
				'not_found'          => __( 'No Captivate Podcasts found.', 'captivate-fm-sync' ),
				'not_found_in_trash' => __( 'No Captivate Podcasts found in Trash.', 'captivate-fm-sync' ),
				'parent_item_colon'  => __( 'Parent Captivate Podcast:', 'captivate-fm-sync' ),
				'menu_name'          => __( 'Captivate Podcasts', 'captivate-fm-sync' ),
				'all_items'          => __( 'Captivate Podcasts', 'captivate-fm-sync' ),
			);

			$args = array(
				'labels'              => $labels,
				'public'              => true,
				'show_ui'             => true,
				'show_in_menu'        => false,
				'show_in_admin_bar'   => false,
				'show_in_rest'   	  => true,
				'menu_icon'           => 'dashicons-controls-volumeon',
				'menu_position'       => 8,
				'show_in_nav_menus'   => true,
				'publicly_queryable'  => true,
				'exclude_from_search' => false,
				'has_archive'         => $has_archive,
				'rewrite'             => array( 'slug' => $single_slug, 'with_front' => false ),
				'supports'            => array( 'title', 'editor', 'author', 'thumbnail', 'excerpt', 'trackbacks', 'custom-fields', 'comments', 'revisions', 'sticky' ),
			);

			if ( ( true === cfm_user_authentication() ) ) :

				register_post_type( 'captivate_podcast', $args );

			endif;

			// Category Archive Slug
			$category_archive_slug = ( isset( $cfm_general_settings['category_archive_slug'] ) && '' != $cfm_general_settings['category_archive_slug'] ) ? $cfm_general_settings['category_archive_slug'] : 'captivate-category';

			// Podcast Category Custom Taxonomy.
			$labels = array(
				'name'                  => _x( 'Podcast Categories', 'Taxonomy Podcast Categories', 'captivate-fm-sync' ),
				'singular_name'         => _x( 'Podcast Category', 'Taxonomy Podcast Category', 'captivate-fm-sync' ),
				'search_items'          => __( 'Search Podcast Categories', 'captivate-fm-sync' ),
				'popular_items'         => __( 'Popular Podcast Categories', 'captivate-fm-sync' ),
				'all_items'             => __( 'All Podcast Categories', 'captivate-fm-sync' ),
				'parent_item'           => __( 'Parent Podcast Category', 'captivate-fm-sync' ),
				'parent_item_colon'     => __( 'Parent Podcast Category', 'captivate-fm-sync' ),
				'edit_item'             => __( 'Edit Podcast Category', 'captivate-fm-sync' ),
				'update_item'           => __( 'Update Podcast Category', 'captivate-fm-sync' ),
				'add_new_item'          => __( 'Add New Podcast Category', 'captivate-fm-sync' ),
				'new_item_name'         => __( 'New Podcast Category Name', 'captivate-fm-sync' ),
				'add_or_remove_items'   => __( 'Add or remove Podcast Categories', 'captivate-fm-sync' ),
				'choose_from_most_used' => __( 'Choose from most used Podcast categories', 'captivate-fm-sync' ),
				'menu_name'             => __( 'Podcast Categories', 'captivate-fm-sync' ),
			);

			$args = array(
				'labels'            => $labels,
				'public'            => true,
				'show_in_nav_menus' => true,
				'show_admin_column' => true,
				'show_in_rest'   	=> true,
				'hierarchical'      => true,
				'show_tagcloud'     => true,
				'show_ui'           => true,
				'rewrite'           => array( 'slug' => $category_archive_slug ),
			);

			if ( ( true === cfm_user_authentication() ) ) :

				register_taxonomy( 'captivate_category', array( 'captivate_podcast' ), $args );

			endif;

			// Tag Archive Slug
			$tag_archive_slug = ( isset( $cfm_general_settings['tag_archive_slug'] ) && '' != $cfm_general_settings['tag_archive_slug'] ) ? $cfm_general_settings['tag_archive_slug'] : 'captivate-tag';

			// Podcast Tags Custom Taxonomy.
			$labels = array(
				'name'                  => _x( 'Podcast Tags', 'Taxonomy Podcast Tags', 'captivate-fm-sync' ),
				'singular_name'         => _x( 'Podcast Tag', 'Taxonomy Podcast Tag', 'captivate-fm-sync' ),
				'search_items'          => __( 'Search Podcast Tags', 'captivate-fm-sync' ),
				'popular_items'         => __( 'Popular Podcast Tags', 'captivate-fm-sync' ),
				'all_items'             => __( 'All Podcast Tags', 'captivate-fm-sync' ),
				'parent_item'           => __( 'Parent Podcast Tag', 'captivate-fm-sync' ),
				'parent_item_colon'     => __( 'Parent Podcast Tag', 'captivate-fm-sync' ),
				'edit_item'             => __( 'Edit Podcast Tag', 'captivate-fm-sync' ),
				'update_item'           => __( 'Update Podcast Tag', 'captivate-fm-sync' ),
				'add_new_item'          => __( 'Add New Podcast Tag', 'captivate-fm-sync' ),
				'new_item_name'         => __( 'New Podcast Tag Name', 'captivate-fm-sync' ),
				'add_or_remove_items'   => __( 'Add or remove Podcast Tags', 'captivate-fm-sync' ),
				'choose_from_most_used' => __( 'Choose from most used podcast tags', 'captivate-fm-sync' ),
				'menu_name'             => __( 'Podcast Tags', 'captivate-fm-sync' ),
			);

			$args = array(
				'labels'            => $labels,
				'public'            => true,
				'show_in_nav_menus' => true,
				'show_admin_column' => true,
				'show_in_rest'   	=> true,
				'hierarchical'      => false,
				'show_tagcloud'     => true,
				'show_ui'           => true,
				'rewrite'           => array( 'slug' => $tag_archive_slug ),
			);

			if ( ( true === cfm_user_authentication() ) ) :

				register_taxonomy( 'captivate_tag', array( 'captivate_podcast' ), $args );

			endif;

		}

		/**
		 * Unregister blogging
		 *
		 * @since 1.0
		 */
		public static function unregister() {

			if ( class_exists( 'PW_Admin_UI' ) && get_option( 'cfm_builder_post_type_transferred' ) == '1' ) {
				unregister_post_type( 'blogging' );
			}

		}

	}

endif;
