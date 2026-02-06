<?php
/**
 * Used for admin data
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'CFMH_Hosting_Admin' ) ) :

	if ( function_exists( 'set_time_limit' ) ) {
		set_time_limit( 0 );
	}

	/**
	 * Hosting Dashboard Admin class
	 *
	 * @since 1.0
	 */
	class CFMH_Hosting_Admin {

		/**
		 * Enqueueu assets
		 *
		 * @since 1.0
		 */
		public static function assets() {

			$current_screen = get_current_screen();

			$all_screens = array(
				'toplevel_page_cfm-hosting-podcasts',
				'admin_page_cfm-hosting-podcasts',
				'captivate-sync_page_cfm-hosting-podcasts',

				'toplevel_page_cfm-hosting-publish-episode',
				'admin_page_cfm-hosting-publish-episode',
				'captivate-sync_page_cfm-hosting-publish-episode',

				'toplevel_page_cfm-hosting-edit-episode',
				'admin_page_cfm-hosting-edit-episode',
				'captivate-sync_page_cfm-hosting-edit-episode',

				'toplevel_page_cfm-hosting-shortcode',
				'admin_page_page_cfm-hosting-shortcode',
				'captivate-sync_page_cfm-hosting-shortcode',

				'toplevel_page_cfm-hosting-podcast-episodes',
				'admin_page_cfm-hosting-podcast-episodes',
				'captivate-sync_page_cfm-hosting-podcast-episodes',

				'toplevel_page_cfm-hosting-settings',
				'admin_page_cfm-hosting-settings',
				'captivate-sync_page_cfm-hosting-settings',

				'toplevel_page_cfm-hosting-credentials',
				'admin_page_cfm-hosting-credentials',
				'captivate-sync_page_cfm-hosting-credentials',

				'toplevel_page_cfm-hosting-migration',
				'admin_page_cfm-hosting-migration',
				'captivate-sync_page_cfm-hosting-migration',
			);

			if ( in_array( $current_screen->id, $all_screens ) || ( 0 === strpos( $current_screen->id, 'captivate-sync_page_cfm-hosting-podcast-episodes_' ) ) ) :

				// fonts.
				wp_register_style( 'cfmsync-google-fonts', '//fonts.googleapis.com/css?family=Poppins:300,400,500,700' );
				wp_register_style( 'cfmsync-font-awesome', CFMH_URL . 'vendor/fontawesome-pro/css/all.min.css', array(), '6.4.0', 'all' );

				// cfm.
				wp_enqueue_script( 'cfmsync-functions', CFMH_URL . 'captivate-sync-assets/js/dist/functions-min.js', array(), CFMH_VERSION, true );
				wp_register_script( 'cfmsync', CFMH_URL . 'captivate-sync-assets/js/dist/admin-min.js', array(), CFMH_VERSION, true );

				wp_localize_script(
					'cfmsync',
					'cfmsync',
					array(
						'CFMH'          				=> CFMH,
						'CFMH_URL'      				=> CFMH_URL,
						'CFMH_ADMINURL' 				=> admin_url(),
						'CFMH_SHOWID'   				=> cfm_get_show_id(),
						'CFMH_CURRENT_SCREEN'   		=> $current_screen->id,
						'ajaxurl'       				=> admin_url( 'admin-ajax.php' ),
						'ajaxnonce'     				=> wp_create_nonce( '_cfm_nonce' ),
					)
				);

				wp_enqueue_style( 'cfmsync-google-fonts' );
				wp_enqueue_style( 'cfmsync-font-awesome' );
				wp_enqueue_script( 'clipboard' );
				wp_enqueue_script( 'bootstrap-js', CFMH_URL . 'vendor/bootstrap/js/bootstrap.bundle.min.js', array(), '5.3.3', true );

				wp_register_style( 'cfmsync', CFMH_URL . 'captivate-sync-assets/css/dist/admin-min.css', array(), CFMH_VERSION, 'all' );

				// cfm.
				wp_enqueue_script( 'cfmsync' );
				wp_enqueue_style( 'cfmsync' );

			endif;

			$data_tables = array(
				'toplevel_page_cfm-hosting-podcast-episodes',
				'admin_page_cfm-hosting-podcast-episodes',
				'captivate-sync_page_cfm-hosting-podcast-episodes',
			);

			if ( in_array( $current_screen->id, $data_tables ) || ( strpos( $current_screen->id, 'captivate-sync_page_cfm-hosting-podcast-episodes_' ) === 0 ) ) :

				wp_enqueue_style( 'cfm-data-tables', CFMH_URL . 'vendor/datatables/jquery.dataTables.min.css', array(), '1.10.19' );
				wp_enqueue_style( 'cfm-data-tables-style', CFMH_URL . 'captivate-sync-assets/css/dist/data-tables-min.css', array(), '1.10.19' );
				wp_enqueue_script( 'cfm-data-tables', CFMH_URL . 'vendor/datatables/jquery.dataTables.min.js', array(), '1.10.19', true );
				wp_enqueue_script( 'cfm-data-tables-js', CFMH_URL . 'captivate-sync-assets/js/dist/data-tables-min.js', array(), '1.10.19', true );

			endif;
		}

		/**
		 * Restrict admin pages
		 *
		 * @since 1.0
		 */
		public static function restrict_other_admin_pages() {

			$current_screen = get_current_screen();

			if ( 'edit-captivate_podcast' == $current_screen->id || 'captivate_podcast' == $current_screen->id  ) {
				if ( ! class_exists( 'PW_Admin_UI' ) || class_exists( 'PW_Admin_UI' ) && 'customersupport' != pwaui_current_user_login() ) {
					wp_die( __( 'You do not have sufficient permissions to access this page.' ) ); exit;
				}
			}

		}

		/**
		 * Admin menus
		 *
		 * @since 1.0
		 */
		public static function menus() {

			$shows = cfm_get_shows();
			$user_shows = get_user_meta( get_current_user_id(), 'cfm_user_shows', true );

			$main_menu_slug = ! empty( $shows ) ? 'cfm-hosting-publish-episode' : 'cfm-hosting-podcasts';
			$main_menu_sub  = ! empty( $shows ) ? 'publish_episode' : 'my_podcasts';

			if ( current_user_can( 'manage_options' ) || ( ! current_user_can( 'manage_options' ) && cfm_is_user_has_show() ) ) {
				add_menu_page( 'Captivate Sync&trade;', 'Captivate Sync&trade;', 'edit_posts', $main_menu_slug, array( 'CFMH_Hosting_Admin', $main_menu_sub ), CFMH_URL . 'captivate-sync-assets/img/menu-icon.png' );
			}

			if ( ! empty( $shows ) ) {
				if ( current_user_can( 'manage_options' ) || ( ! current_user_can( 'manage_options' ) && cfm_is_user_has_show() ) ) {
					add_submenu_page( $main_menu_slug, cfm_get_show_info( cfm_get_show_id(), 'title' ), 'Publish Episode', 'edit_posts', 'cfm-hosting-publish-episode', array( 'CFMH_Hosting_Admin', 'publish_episode' ), null );
					add_submenu_page( 'options.php', cfm_get_show_info( cfm_get_show_id(), 'title' ), 'Edit Episode', 'edit_posts', 'cfm-hosting-edit-episode', array( 'CFMH_Hosting_Admin', 'publish_episode' ), null );
				}
			}

			if ( current_user_can( 'manage_options' ) || ( ! current_user_can( 'manage_options' ) && cfm_is_user_has_show() ) ) {
				add_submenu_page( $main_menu_slug, 'My Podcasts', 'My Podcasts', 'edit_posts', 'cfm-hosting-podcasts', array( 'CFMH_Hosting_Admin', 'my_podcasts' ), null );
			}

			if ( ! empty( $shows ) ) {
				foreach ( $shows as $show ) {
					if ( current_user_can( 'manage_options' ) || ( ! current_user_can( 'manage_options' ) && ! empty( $user_shows ) && in_array( $show['id'], $user_shows ) ) ) {
						add_submenu_page( $main_menu_slug, $show['title'], $show['title'], 'edit_posts', 'cfm-hosting-podcast-episodes_' . $show['id'], array( 'CFMH_Hosting_Admin', 'my_podcast_episodes' ), null );
					}
				}

				if ( current_user_can( 'manage_options' ) || ( ! current_user_can( 'manage_options' ) && cfm_is_user_has_show() ) ) {
					add_submenu_page( $main_menu_slug, 'Shortcode Builder', 'Shortcode Builder', 'edit_posts', 'cfm-hosting-shortcode', array( 'CFMH_Hosting_Admin', 'shortcode' ), null );
				}
			}

			add_submenu_page( $main_menu_slug, 'Categories', 'Categories', 'manage_categories', admin_url( 'edit-tags.php?taxonomy=captivate_category' ), null );
			add_submenu_page( $main_menu_slug, 'Tags', 'Tags', 'manage_categories', admin_url( 'edit-tags.php?taxonomy=captivate_tag' ), null );
			cfm_custom_taxonomy_submenus( $main_menu_slug, 'captivate_podcast' );

			if ( ! empty( $shows ) ) {
				if ( current_user_can( 'manage_options' ) ) :
					add_submenu_page( $main_menu_slug, 'Settings', 'Settings', 'manage_options', 'cfm-hosting-settings', array( 'CFMH_Hosting_Admin', 'settings' ), null );
				endif;
			}

			if ( ! class_exists( 'PW_Admin_UI' ) || class_exists( 'PW_Admin_UI' ) && 'customersupport' == pwaui_current_user_login() ) :
				add_submenu_page( $main_menu_slug, 'Authentication', 'Authentication', 'manage_options', 'cfm-hosting-credentials', array( 'CFMH_Hosting_Admin', 'user_credentials' ), null );
			endif;

		}

		/**
		 * Podcasts template
		 *
		 * @since 1.0
		 */
		public static function my_podcasts() {
			include CFMH . 'inc/templates/podcasts.php';
		}

		/**
		 * Episodes template
		 *
		 * @since 1.0
		 */
		public static function my_podcast_episodes() {
			include CFMH . 'inc/templates/episodes.php';
		}

		/**
		 * Shortcode template
		 *
		 * @since 1.2.0
		 */
		public static function shortcode() {
			include CFMH . 'inc/templates/shortcode.php';
		}

		/**
		 * Publish episode template
		 *
		 * @since 1.0
		 */
		public static function publish_episode() {

			$shows = cfm_get_shows();

			$shows_count = count( $shows );

			if ( ! empty( $shows ) && $shows_count > 1 && ! isset( $_GET['show_id'] ) ) {
				// js redirect to prevent "headers already sent" error.
				echo '<script>document.location.href = "' . admin_url( 'admin.php?page=cfm-hosting-podcasts&ref=publish' ) . '";</script>';
			} else {
				include CFMH . 'inc/templates/publish-episode.php';
			}

		}

		/**
		 * User authentication template
		 *
		 * @since 1.0
		 */
		public static function user_credentials() {

			include CFMH . 'inc/templates/authentication.php';
		}

		/**
		 * Settings template
		 *
		 * @since 3.0
		 */
		public static function settings() {

			include CFMH . 'inc/templates/settings.php';
		}

		/**
		 * Add podcast management to edit user profile
		 *
		 * @since 1.1.4
		 * @return html
		 */
		public static function add_user_podcast_management( $user ) {

			if ( user_can( $user->ID, 'manage_options' ) )
				return false;

			$shows = cfm_get_shows();
			$user_shows = get_user_meta( $user->ID, 'cfm_user_shows', true );

			if ( ! empty( $shows ) ) {

				echo '<h3>Podcast Management</h3>';

				echo '<table class="form-table"><tr>';

					echo '<th scope="row">User Shows</th>';
					echo '<td>';
						foreach ( $shows as $show ) {

							$checked = '';
							if ( ! empty( $user_shows ) && in_array( $show['id'], $user_shows ) ) {
								$checked = ' checked="checked"';
							}

							echo '<p><label><input type="checkbox" name="user_show[]" value="' . esc_attr( $show['id'] ) . '"' . $checked . '> ' . esc_html( $show['title'] ) . '</label></p>';

						}
					echo '</td>';

				echo '</tr></table>';
			}

		}

		/**
		 * Update podcast management in edit user profile
		 *
		 * @since 1.1.4
		 * @return html
		 */
		public static function update_user_podcast_management( $user_id ) {

			if ( ! current_user_can( 'edit_user' ) )
				return false;

			update_user_meta( $user_id, 'cfm_user_shows', $_POST['user_show'] );

		}

		/**
		 * Admin footer
		 *
		 * @since 1.2.3
		 * @return html
		 */
		public static function admin_footer() {

			$current_screen = get_current_screen();

			// cfm toaster.
			echo '<div id="cfm-toast-container" class="cfm-toast-container"><div class="cfm-toaster"><span class="cfm-toaster-text"></span><span class="cfm-toast-dismiss"><i class="far fa-times"></i></span></div></div>';

			// force admin menu selected for podcast categories and tags.
			if ( 'edit-captivate_category' == $current_screen->id || 'edit-captivate_tag' == $current_screen->id ) {
				$current = ( 'edit-captivate_category' == $current_screen->id ) ? 'Categories' : 'Tags';
				?>
				<script>
				jQuery(document).ready(function($) {
					$('#toplevel_page_cfm-hosting-publish-episode, #toplevel_page_cfm-hosting-publish-episode > a').removeClass('wp-not-current-submenu');
					$('#toplevel_page_cfm-hosting-publish-episode, #toplevel_page_cfm-hosting-publish-episode > a').addClass('wp-has-current-submenu wp-menu-open');
					$('#toplevel_page_cfm-hosting-publish-episode > ul > li a:contains("<?php echo $current; ?>")').parent().addClass('current');
					$('#menu-posts').removeClass('wp-has-current-submenu wp-menu-open');
				});
				</script>
				<?php
			}

			// force admin menu selected for edit episode.
			if ( 'admin_page_cfm-hosting-edit-episode' == $current_screen->id ) {
				?>
				<script>
				jQuery(document).ready(function($) {
					$('#toplevel_page_cfm-hosting-publish-episode, #toplevel_page_cfm-hosting-publish-episode > a').addClass('wp-has-current-submenu wp-menu-open');
					$('#toplevel_page_cfm-hosting-publish-episode > ul > li a[href*="<?php echo cfm_get_show_id(); ?>"]').parent().addClass('current');
				});
				</script>
				<?php
			}
		}

		/**
		 * Body Class
		 *
		 * @since 3.0.0
		 * @return $classes
		 */
		public static function body_class( $classes ) {
			if ( class_exists( 'PW_Admin_UI' ) ) {
				$classes .= ' cfm-pw-admin-ui ';
			}

    		return $classes;
		}
	}

endif;
