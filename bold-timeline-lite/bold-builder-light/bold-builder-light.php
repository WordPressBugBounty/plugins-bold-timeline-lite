<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

require_once 'class-btbb-light-list-table.php';
require_once 'class-btbb-light-item.php';

if ( ! class_exists( 'BTBB_Light' ) ) {
	
	class BTBB_Light {

		private $slug;
		private $edit_slug;
		private $single_name;
		private $plural_name;
		private $icon;
		private $home_url;
		private $doc_url;
		private $support_url;
		private $changelog_url;
		private $shortcode;

		private $map;
		private $elements;
		private $bt_bb_array;
		
		private $license;
		
		private $license_server;
		private $license_server_route;
		private $license_server_download_route;
		
		private $license_server_json_update_base;
		
		private $domain;
		
		private $product_id;
		
		private $plugin_file_path;
		
		public $license_slug;
		public $license_server_json_route;

		function __construct( $arr ) {
			$this->slug = $arr['slug'];
			$this->edit_slug = $arr['slug'] . '-' . 'edit';
			$this->license_slug = $arr['slug'] . '-' . 'license';
			$this->single_name = $arr['single_name'];
			$this->plural_name = $arr['plural_name'];
			$this->icon = $arr['icon'];
			$this->home_url = $arr['home_url'];
			$this->doc_url = $arr['doc_url'];
			$this->support_url = $arr['support_url'];
			$this->changelog_url = $arr['changelog_url'];
			$this->shortcode = $arr['shortcode'];
			
			$this->plugin_file_path = $arr['plugin_file_path'];

			$this->map = array();
			$this->elements = array();
			$this->bt_bb_array = array();
			
			$this->license = get_site_option( $this->slug . '-license' );
			
			$this->license_server = 'https://license.bold-themes.com/wp-json/';
			$this->license_server_route = 'bt_license_server/v1';
			$this->license_server_json_route = 'bt_license_server_json/v1';
			$this->license_server_download_route = 'bt_license_server_download/v1';
			
			$urlparts = parse_url( home_url() );
			$this->domain = $urlparts['host'];
			
			$this->product_id = $arr['product_id'];

			if ( $this->license && $this->product_id != '' ) {
				if ( ! class_exists( 'Puc_v4_Factory' ) ) {
					require_once 'plugin-update-checker/plugin-update-checker.php';
				}
				$updateChecker = Puc_v4_Factory::buildUpdateChecker(
					$this->license_server . $this->license_server_json_route . '/license=' . $this->license['purchase_code'] . '/domain=' . $this->domain . '/product_id=' . $this->product_id . '/product_name=' . urlencode( $this->single_name ) . '/changelog_url=' . urlencode( urlencode( $this->changelog_url ) ),
					$this->plugin_file_path // Full path to the main plugin file.
				);
			}
			
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue' ), 20 ); // after BB
			add_action( 'admin_head', array( $this, 'map_js' ) );
			add_action( 'admin_footer', array( $this, 'js_settings' ) );
			add_action( 'admin_footer', array( $this, 'translate' ) );
			add_action( 'admin_menu', array( $this, 'admin_menu' ) );
			add_action( 'admin_notices', array( $this, 'admin_notices' ) );
			add_action( 'init', array( $this, 'create_post_type' ) );
			add_action( 'wp_loaded', array( $this, 'create_post_type' ) ); // fix wp-includes/media.php on line 4988... in WP 6.8.1

			add_shortcode( $this->shortcode, array( $this, 'add_shortcode' ) );
			
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_fe' ) );

		}

		/**
		 * Enqueue
		 */

		function enqueue() {
			$screen = get_current_screen();

			if ( strpos( $screen->base, $this->license_slug ) ) {
				wp_enqueue_style( 'bt-bb-light-license', plugins_url( 'css/license.css', __FILE__ ) );
			}
			
			if ( ! strpos( $screen->base, $this->edit_slug ) ) {
				return;
			}

			wp_enqueue_style( 'bt-bb-light-font-awesome.min', plugins_url( 'css/font-awesome.min.css', __FILE__ ) );
			wp_enqueue_style( 'bt-bb-light', plugins_url( 'css/style.crush.css', __FILE__ ) );

			wp_enqueue_script( 'bt-bb-light-react', plugins_url( 'react.min.js', __FILE__ ) );
			wp_enqueue_script( 'bt-bb-light', plugins_url( 'bundle.js', __FILE__ ), array( 'jquery' ) );
			wp_enqueue_script( 'bt-bb-light-misc', plugins_url( 'misc.js', __FILE__ ), array( 'jquery', 'bt-bb-light' ) );
			wp_enqueue_script( 'bt-bb-light-autosize', plugins_url( 'autosize.min.js', __FILE__ ) );

			wp_enqueue_script( 'wp-color-picker' );

			wp_enqueue_style( 'wp-color-picker' );
			
			wp_enqueue_script( 'wp-color-picker-alpha', plugins_url( 'wp-color-picker-alpha.min.js', __FILE__ ), array( 'wp-color-picker' ) );
		}

		/**
		 * Enqueue FE
		 */

		function enqueue_fe() {
			wp_enqueue_script( 'bt-bb-light', plugins_url( 'bt-bb-light.js', __FILE__ ), array( 'jquery' ) );
		}

		/**
		 * Translate
		 */

		function translate() {
			$screen = get_current_screen();
			if ( ! strpos( $screen->base, $this->edit_slug ) ) {
				return;
			}
			echo '<script>';
				echo 'window.bt_bb_text = [];';
				echo 'window.bt_bb_text.toggle = "' . esc_html__( 'Toggle', 'bold-timeline' ) . '";';
				echo 'window.bt_bb_text.add = "' . esc_html__( 'Add', 'bold-timeline' ) . '";';
				echo 'window.bt_bb_text.edit = "' . esc_html__( 'Edit', 'bold-timeline' ) . '";';
				echo 'window.bt_bb_text.edit_content = "' . esc_html__( 'Edit Content', 'bold-timeline' ) . '";';
				echo 'window.bt_bb_text.clone = "' . esc_html__( 'Clone', 'bold-timeline' ) . '";';
				echo 'window.bt_bb_text.delete = "' . esc_html__( 'Delete', 'bold-timeline' ) . '";';
				echo 'window.bt_bb_text.layout_error = "' . esc_html__( 'Layout error!', 'bold-timeline' ) . '";';
				echo 'window.bt_bb_text.add_element = "' . esc_html__( 'Add Element', 'bold-timeline' ) . '";';
				echo 'window.bt_bb_text.select_layout = "' . esc_html__( 'Select Layout', 'bold-timeline' ) . '";';
				echo 'window.bt_bb_text.edit_layout = "' . esc_html__( 'Edit Layout', 'bold-timeline' ) . '";';
				echo 'window.bt_bb_text.select = "' . esc_html__( 'Select', 'bold-timeline' ) . '";';
				echo 'window.bt_bb_text.submit = "' . esc_html__( 'Submit', 'bold-timeline' ) . '";';
				echo 'window.bt_bb_text.copy = "' . esc_html__( 'Copy', 'bold-timeline' ) . '";';
				echo 'window.bt_bb_text.copy_plus = "' . esc_html__( 'Copy +', 'bold-timeline' ) . '";';
				echo 'window.bt_bb_text.paste = "' . esc_html__( 'Paste', 'bold-timeline' ) . '";';
				echo 'window.bt_bb_text.export = "' . esc_html__( 'Export', 'bold-timeline' ) . '";';
				echo 'window.bt_bb_text.import = "' . esc_html__( 'Import', 'bold-timeline' ) . '";';
				echo 'window.bt_bb_text.not_allowed = "' . esc_html__( 'Not allowed!', 'bold-timeline' ) . '";';
				echo 'window.bt_bb_text.manage_cb = "' . esc_html__( 'Manage Clipboard', 'bold-timeline' ) . '";';
				echo 'window.bt_bb_text.filter = "' . esc_html__( 'Filter...', 'bold-timeline' ) . '";';
				echo 'window.bt_bb_text.sc_mapper = "' . esc_html__( 'Shortcode Mapper', 'bold-timeline' ) . '";';
				echo 'window.bt_bb_text.insert_mapping = "' . esc_html__( 'Insert Mapping', 'bold-timeline' ) . '";';
				echo 'window.bt_bb_text.save = "' . esc_html__( 'Save', 'bold-timeline' ) . '";';
				echo 'window.bt_bb_text.switch_editor = "' . esc_html__( 'Switch Editor', 'bold-timeline' ) . '";';
				echo 'window.bt_bb_text.custom_css = "' . esc_html__( 'Custom CSS', 'bold-timeline' ) . '";';
				echo 'window.bt_bb_text.switch_editor_confirm = "' . esc_html__( 'Are you sure you want to switch editor?', 'bold-timeline' ) . '";';
				echo 'window.bt_bb_text.general = "' . esc_html__( 'General', 'bold-timeline' ) . '";';
			echo '</script>';
		}

		/**
		 * Settings
		 */

		function js_settings() {
			$screen = get_current_screen();
			if ( ! strpos( $screen->base, $this->edit_slug ) ) {
				return;
			}
			
			echo '<script>';
				echo 'window.bt_bb_settings = [];';
				echo 'window.bt_bb_settings.tag_as_name = "0";';

				echo 'window.BTAJAXURL = "' . esc_js( admin_url( 'admin-ajax.php' ) ) . '";';

				echo 'window.bt_bb.is_bb_content = true;';

			echo '</script>';
			
			echo '<script>';
				if ( function_exists( 'boldthemes_get_icon_fonts_bb_array' ) ) {
					$icon_arr = boldthemes_get_icon_fonts_bb_array();
				} else {
					require_once( dirname(__FILE__) . '/content_elements_misc/bt_bb_fa_icons.php' );
					require_once( dirname(__FILE__) . '/content_elements_misc/bt_bb_fa5_regular_icons.php' );
					require_once( dirname(__FILE__) . '/content_elements_misc/bt_bb_fa5_solid_icons.php' );
					require_once( dirname(__FILE__) . '/content_elements_misc/bt_bb_fa5_brands_icons.php' );
					require_once( dirname(__FILE__) . '/content_elements_misc/bt_bb_s7_icons.php' );
					$icon_arr = array( 'Font Awesome' => bt_bb_fa_icons(), 'Font Awesome 5 Regular' => bt_bb_fa5_regular_icons(), 'Font Awesome 5 Solid' => bt_bb_fa5_solid_icons(), 'Font Awesome 5 Brands' => bt_bb_fa5_brands_icons(), 'S7' => bt_bb_s7_icons() );
				}
				echo 'window.bt_bb_icons = JSON.parse(\'' . wp_json_encode( $icon_arr ) . '\')';
			echo '</script>';
			
		}

		/**
		 * Map shortcodes (js)
		 */

		function map_js() {
			
			if ( is_admin() ) { // back end
				$screen = get_current_screen();
				if ( ! strpos( $screen->base, $this->edit_slug ) ) {
					return;
				}
			}
			
			echo '<script>';
				foreach( $this->elements as $base => $params ) {
					$proxy = new BTBB_Light_Map_Proxy( $base, $params, $this->map );
					$proxy->js_map();
				}
			echo '</script>';
		}

		/**
		 * Map shortcodes
		 */
		function map( $base, $params ) {
			$i = 0;
			if ( isset( $params['params'] ) ) {
				foreach( $params['params'] as $param ) {
					if ( ! isset( $param['weight'] ) ) {
						$params['params'][ $i ]['weight'] = $i;
					}
					$i++;
				}
			}
			$this->elements[ $base ] = $params;
		}

		/**
		 * Prints the box content.
		 * 
		 * @param WP_Post $post The object for the current post/page.
		 */
		function show( $post_content ) {

			$this->do_shortcode( $post_content );

			$json_content = json_encode( $this->bt_bb_array );

			echo '<div id="bt_bb_sectionid"><div class="inside">';
			
			echo '<div id="bt_bb"></div><div id="bt_bb_add_root"><i></i></div>';
			
			echo '<div id="bt_bb_dialog" class="bt_bb_dialog">';
				echo '<div class="bt_bb_dialog_header"><div class="bt_bb_dialog_close"></div><span></span></div>';
				echo '<div class="bt_bb_dialog_header_tools"></div>';
				
				do_action( 'bt_bb_light_dialog_header' );
				
				echo '<div class="bt_bb_dialog_content">';
				echo '</div>';
				echo '<div class="bt_bb_dialog_tinymce">';
					echo '<div class="bt_bb_dialog_tinymce_editor_container">';
						wp_editor( '' , 'bt_bb_tinymce', array( 'textarea_rows' => 12 ) );
					echo '</div>';
					echo '<input type="button" class="bt_bb_dialog_button bt_bb_edit button button-small" value="' . esc_html__( 'Submit', 'bold-timeline' ) . '">';
				echo '</div>';
			echo '</div>';

			echo '<div id="bt_bb_main_toolbar">';
			echo '<i class="bt_bb_undo" title="' . esc_html__( 'Undo', 'bold-timeline' ) . '"></i>';
			echo '<i class="bt_bb_redo" title="' . esc_html__( 'Redo', 'bold-timeline' ) . '"></i>';
				echo '<span class="bt_bb_separator">|</span>';
			echo '<i class="bt_bb_paste_root" title="' . esc_html__( 'Paste', 'bold-timeline' ) . '"></i>';
			echo '<span class="bt_bb_cb_items"></span>';
			echo '<i class="bt_bb_manage_clipboard" title="' . esc_html__( 'Clipboard Manager', 'bold-timeline' ) . '"></i>';
				echo '<span class="bt_bb_separator">|</span>';
			echo '<i class="bt_bb_save bt_bb_disabled" title="' . esc_html__( 'Save', 'bold-timeline' ) . '"></i>';
			echo '</div>';

			echo '</div></div>';

			add_action( 'admin_footer', array( new BTBB_Light_Data_Proxy( $json_content ), 'js' ) );

		}

		function do_shortcode( $content ) {
			global $shortcode_tags;
			if ( ! ( ( empty( $shortcode_tags ) || ! is_array( $shortcode_tags ) ) ) ) {
				$pattern = get_shortcode_regex();
				
				$callback = new BTBB_Light_Callback( $this->bt_bb_array, $this->elements );
				
				$preg_cb = preg_replace_callback( "/$pattern/s", array( $callback, 'bt_bb_do_shortcode_tag' ), $content );
			}
		}

		function add_shortcode( $atts ) {
			$a = shortcode_atts( array(
				'id' => ''
			), $atts );

			if ( $atts['id'] != '' ) {
				$args = array(
					'include' => $atts['id'],
					'post_type' => $this->slug,
				);
				$posts_array = get_posts( $args );
			}
			
			if ( isset( $posts_array[0]->post_content ) ) {
				return do_shortcode( $posts_array[0]->post_content );
			} else {
				return null;
			}
			
		}

		// create post type
		function create_post_type() {
			register_post_type( $this->slug,
				array(
					'labels' => array(
						'name' => $this->plural_name,
						'singular_name' => $this->single_name
					),
					'rewrite' => false,
					'query_var' => false,
					'supports' => array( 'title', 'revisions' ),
					'show_in_rest' => true,
				)
			);
		}

		// admin menu
		function admin_menu() {
			global $_wp_last_object_menu;

			$_wp_last_object_menu++;

			add_menu_page( $this->single_name,
				$this->single_name,
				'edit_posts', $this->slug,
				array( $this, 'admin_management_page' ), $this->icon,
				$_wp_last_object_menu );

			$edit = add_submenu_page( $this->slug,
				esc_html__( 'Edit ', 'bold-timeline' ) . $this->single_name,
				$this->plural_name,
				'edit_posts', $this->slug,
				array( $this, 'admin_management_page' ) );

			add_action( 'load-' . $edit, array( $this, 'load_admin' ) );
			
			if ( $this->product_id != '' ) {
				add_submenu_page( $this->slug,
					esc_html__( 'Product License', 'bold-timeline' ),
					esc_html__( 'Product License', 'bold-timeline' ),
					'activate_plugins', $this->license_slug,
					array( $this, 'admin_license_page' ) );
			}

			add_submenu_page( $this->slug,
				esc_html__( 'Add New ', 'bold-timeline' ) . $this->single_name,
				esc_html__( 'Add New', 'bold-timeline' ),
				'edit_posts', $this->edit_slug,
				array( $this, 'admin_edit_page' ) );

		}
		
		// cpt admin
		function load_admin() {
			
			$current_screen = get_current_screen();
			add_filter( 'manage_' . $current_screen->id . '_columns', array( 'BTBB_Light_List_Table', 'define_columns' ) );

			// save
			if ( isset( $_POST['action'] ) && $_POST['action'] == 'save' ) {
				
				if ( ! wp_verify_nonce( sanitize_text_field( $_REQUEST['_wpnonce'] ), 'bt-bb-light-edit' ) ) {
					wp_die( esc_html__( 'Nonce error.', 'bold-timeline' ) );
				}

				if ( ! current_user_can( 'edit_posts' ) ) {
					wp_die( esc_html__( 'You are not allowed to edit posts.', 'bold-timeline' ) );
				}
			
				$post_id = isset( $_GET['post'] ) ? intval( $_GET['post'] ) : -1;
				$post_title = $_POST['post_title'] != '' ? sanitize_text_field( $_POST['post_title'] ) : esc_html__( 'Untitled', 'bold-timeline' );
				$post_content = wp_kses_post( stripslashes( $_POST['post_content'] ) );
				$query = array();
				if ( $post_id == -1 ) { // new post
					$post_id = wp_insert_post( array(
						'post_type' => $this->slug,
						'post_status' => 'publish',
						'post_title' => $post_title,
						'post_content' => trim( $post_content ),
					) );
					if ( $post_id ) {
						$query['message'] = 'created';
					}
				} else { // update post
					$post_id = wp_update_post( array(
						'ID' => (int) $post_id,
						'post_status' => 'publish',
						'post_title' => $post_title,
						'post_content' => trim( $post_content ),
					) );
					if ( $post_id ) {
						$query['message'] = 'saved';
					}
				}

				if ( $post_id ) {
					$query['post'] = $post_id;
					$redirect_to = add_query_arg( $query, menu_page_url( $this->edit_slug, false ) );
				} else {
					$redirect_to = add_query_arg( $query, menu_page_url( $this->slug, false ) );
				}

				wp_safe_redirect( $redirect_to );

				exit();

			}

			// delete
			else if ( isset( $_GET['action'] ) && $_GET['action'] == 'delete' ) {

				$posts = empty( $_POST['post_ID'] ) ? (array) $_GET['post'] : (array) $_POST['post_ID'];

				$is_deleted = false;
				
				$bulk = wp_verify_nonce( sanitize_text_field( $_REQUEST['_wpnonce'] ), 'bulk-posts' );

				foreach ( $posts as $post_id ) {
					
					if ( ! $bulk ) {
						check_admin_referer( $this->slug . '-delete-' . $post_id ); // check will also fail if it is a bulk action but bulk nonce is not valid
					}

					if ( ! current_user_can( 'delete_posts' ) ) {
						wp_die( esc_html__( 'You are not allowed to delete posts.', 'bold-timeline' ) );
					}

					$deleted = wp_delete_post( $post_id, true );
					if ( $deleted ) {
						$is_deleted = true;
					}

				}

				$query = array();

				if ( $is_deleted ) {
					if ( count( $posts ) > 1 ) {
						$query['message'] = 'posts_deleted';
					} else { 
						$query['message'] = 'post_deleted';
					}
				}

				$redirect_to = add_query_arg( $query, menu_page_url( $this->slug, false ) );

				wp_safe_redirect( $redirect_to );

				exit();

			}
		}

		// management page
		function admin_management_page() {
			
			if ( ! current_user_can( 'edit_posts' ) ) {
				wp_die( esc_html__( 'You are not allowed edit posts.', 'bold-timeline' ) );
			}

			// table
			$list_table = new BTBB_Light_List_Table( $this->slug, $this->shortcode );
			$list_table->prepare_items();
			?>
			<div class="wrap">

			<h1 class="wp-heading-inline"><?php
				echo esc_html( $this->plural_name );
			?></h1>

			<?php
			
				echo sprintf( '<a href="%1$s" class="add-new-h2">%2$s</a>',
					esc_url( menu_page_url( $this->edit_slug, false ) ),
					esc_html__( 'Add New', 'bold-timeline' ) );
				

				if ( ! empty( $_REQUEST['s'] ) ) {
					echo sprintf( '<span class="subtitle">'
						. esc_html__( 'Search results for &#8220;%s&#8221;', 'bold-timeline' )
						. '</span>', esc_html( sanitize_text_field( $_REQUEST['s'] ) ) );
				}
				
			?>

			<hr class="wp-header-end">

			<form method="get" action="">
				<input type="hidden" name="page" value="<?php echo esc_attr( sanitize_text_field( $_REQUEST['page'] ) ); ?>" />
				<?php $list_table->search_box( esc_html__( 'Search', 'bold-timeline' ), $this->slug ); ?>
				<?php $list_table->display(); ?>
			</form>

			</div>
			<?php
		}

		// edit page
		function admin_edit_page() {
			
			if ( ! current_user_can( 'edit_posts' ) ) {
				wp_die( esc_html__( 'You are not allowed to edit posts.', 'bold-timeline' ) );
			}

			$post_type = $this->slug;

			$post_title = '';
			$post_content = '';

			$post_id = isset( $_GET['post'] ) ? intval( $_GET['post'] ) : -1;
			
			if ( $post_id > 0 ) {
				$post = get_post( $post_id );
				$post_title = $post->post_title;
				$post_content = $post->post_content;
			}

			?>

			<div class="wrap">

			<h1 class="wp-heading-inline"><?php
				if ( $post_id == -1 ) {
					esc_html_e( 'Add New ', 'bold-timeline' ) . $this->single_name;
				} else {
					esc_html_e( 'Edit ', 'bold-timeline' ) . $this->single_name;
				}
			?></h1>

			<form method="post" action="<?php echo esc_url( add_query_arg( array( 'post' => $post_id ), menu_page_url( $this->slug, false ) ) ); ?>">

				<?php wp_nonce_field( 'bt-bb-light-edit' ); ?>
				
				<input type="hidden" id="post_ID" name="post_ID" value="<?php echo esc_attr( $post_id ); ?>">
				<input type="hidden" id="hiddenaction" name="action" value="save">
				<input type="hidden" id="post_content" name="post_content" value="">

				<div id="poststuff">
				<div id="post-body" class="metabox-holder columns-2">
				<div id="post-body-content">
				<div id="titlediv">
				<div id="titlewrap">

					<label id="title-prompt-text" for="title" class="screen-reader-text"><?php esc_html_e( 'Enter title here', 'bold-timeline' ); ?></label>
					<input type="text" name="post_title" size="30" value="<?php echo esc_attr( $post_title ); ?>" id="title" spellcheck="true" autocomplete="off">

				</div><!-- #titlewrap -->

				<?php if ( $post_id > 0 ) { ?>

				<div class="inside">
					<p class="description">
					<label for="bt_bb_light_shortcode"><?php esc_html_e( 'Copy this shortcode and paste it into your post, page, or text widget content:', 'bold-timeline' ); ?></label>
					<span><input type="text" id="bt_bb_light_shortcode" onfocus="this.select();" readonly="readonly" class="large-text code" value="[<?php echo esc_attr( $this->shortcode ); ?> id=&quot;<?php echo esc_attr( $post_id ); ?>&quot;]"></span>
					</p>
				</div>

				<?php } ?>

				</div><!-- #titlediv -->
				</div><!-- #post-body-content -->

				<div id="postbox-container-1" class="postbox-container">

				<div id="submitdiv" class="postbox">
				<h2><span><?php esc_html_e( 'Status', 'bold-timeline' ); ?></span></h2>
				<div class="inside">
				<div class="submitbox" id="submitpost">

				<div id="major-publishing-actions">

				<div id="publishing-action">
					<span class="spinner"></span>
					<?php submit_button( esc_html__( 'Save', 'bold-timeline' ), 'primary', 'save', false, 'disabled' ); ?>
				</div>
				<div class="clear"></div>
				</div><!-- #major-publishing-actions -->
				</div><!-- #submitpost -->
				</div>
				</div><!-- #submitdiv -->

				<div id="informationdiv" class="postbox">
				<div class="postbox-header">
				<h2 class="hndle"><span><?php esc_html_e( 'Info', 'bold-timeline' ); ?></span></h2>
				</div>
				<div class="inside">
				<ul class="bt-bb-light-info">
				<li class="bt-bb-light-home-page"><a href="<?php echo esc_url( $this->home_url ); ?>" target="_blank"><?php esc_html_e( 'Home page', 'bold-timeline' ); ?></a></li>
				<?php
				if ( current_user_can( 'activate_plugins' ) && $this->product_id != '' ) { ?>
					<li class="bt-bb-light-licence"><a href="<?php echo esc_url( admin_url( 'admin.php?page=' . $this->license_slug ) ); ?>" target="_blank"><?php esc_html_e( 'Product License', 'bold-timeline' ); ?></a></li>
				<?php }
				if ( $this->doc_url != '' ) { ?>
					<li class="bt-bb-light-documentation"><a href="<?php echo esc_url( $this->doc_url ); ?>" target="_blank"><?php esc_html_e( 'Documentation', 'bold-timeline' ); ?></a></li>
				<?php }
				if ( $this->support_url != '' ) { ?>
					<li class="bt-bb-light-support"><a href="<?php echo esc_url( $this->support_url ); ?>" target="_blank"><?php esc_html_e( 'Support', 'bold-timeline' ); ?></a></li>
				<?php } ?>
					<li class="bt-bb-light-revisions"><a href="<?php echo wp_get_post_revisions_url( $post_id ); ?>" target="_blank"><?php esc_html_e( 'Revisions', 'bold-timeline' ); ?></a></li>
				</ul>
				</div>
				</div><!-- #informationdiv -->

				</div><!-- #postbox-container-1 -->

				<div id="postbox-container-2" class="postbox-container">

				<div id="bt-bb-light-editor" class="postbox">
					<?php $this->show( $post_content ); ?>
				</div>

				</div><!-- #postbox-container-2 -->

				</div><!-- #post-body -->
				<br class="clear" />
				</div><!-- #poststuff -->

			</form>

			<script>
				window.bt_bb_light_post_type = '<?php echo esc_html( $post_type ); ?>';

				if ( '' === jQuery( '#title' ).val() ) {
					jQuery( '#title' ).focus();
				}
				var $title = jQuery( '#title' );
				var $titleprompt = jQuery( '#title-prompt-text' );

				if ( '' === $title.val() ) {
					$titleprompt.removeClass( 'screen-reader-text' );
				}

				$titleprompt.click( function() {
					jQuery( this ).addClass( 'screen-reader-text' );
					$title.focus();
				} );

				$title.blur( function() {
					if ( '' === jQuery( this ).val() ) {
						$titleprompt.removeClass( 'screen-reader-text' );
					}
				} ).focus( function() {
					$titleprompt.addClass( 'screen-reader-text' );
					jQuery( '#save' ).prop( 'disabled', false );
					jQuery( 'i.bt_bb_save' ).removeClass( 'bt_bb_disabled' );
				} ).keydown( function( e ) {
					$titleprompt.addClass( 'screen-reader-text' );
					jQuery( this ).unbind( e );
					jQuery( '#save' ).prop( 'disabled', false );
					jQuery( 'i.bt_bb_save' ).removeClass( 'bt_bb_disabled' );
				} );
			</script>

			<?php

		}
		
		// license page
		function admin_license_page() {
			
			if ( ! current_user_can( 'activate_plugins' ) ) {
				wp_die( esc_html__( 'You are not allowed to do this.', 'bold-timeline' ) );
			}
			
			$purchase_code = '';
			$email = '';
			
			$m = '';
			$m_type = '';
			
			$disabled = '';

			if ( isset( $_POST['purchase_code'] ) ) {
			
				if ( ! wp_verify_nonce( sanitize_text_field( $_REQUEST['_wpnonce'] ), 'bt-bb-light-license' ) ) {
					wp_die( esc_html__( 'Nonce error.', 'bold-timeline' ) );
				}
				
				$purchase_code = sanitize_text_field( $_POST['purchase_code'] );
				$email = sanitize_email( $_POST['email'] );
				
				if ( isset( $_POST['deactivate'] ) ) {
					
					if ( $email == '' ) {
						$email = 'noemail';
					}
					
					$url = $this->license_server . $this->license_server_route . '/license=' . $purchase_code . '/domain=' . $this->domain . '/email=' . $email . '/product_id=' . $this->product_id . '/action=deactivate';

					$r = wp_remote_get( $url, array( 'timeout' => 30 ) );

					if ( is_wp_error( $r ) ) {
						$m_type = 'error';
						$m = esc_html__( 'Error 01. Please try again later.', 'bold-timeline' );
					} else {
						$r = json_decode( $r['body'] );
					}
					
					// errors
					if ( ! is_object( $r ) || $r->code != 'success' ) {
						$m_type = 'error';
						$m = esc_html__( 'Error 02. Please try again later.', 'bold-timeline' );
						if ( $email == 'noemail' ) {
							$email = '';
						}
					// success
					} else if ( $r->code == 'success' ) {
						delete_site_option( $this->slug . '-license' );
						$this->license = false;
						$m_type = 'ok';
						$m = esc_html__( 'License has been deactivated.', 'bold-timeline' );
						$purchase_code = '';
						$email = '';
					}
					
				} else {

					if ( $purchase_code == '' ) {
						$purchase_code = 'nopurchasecode';
					}
					if ( $email == '' ) {
						$email = 'noemail';
					}

					$url = $this->license_server . $this->license_server_route . '/license=' . $purchase_code . '/domain=' . $this->domain . '/email=' . $email . '/product_id=' . $this->product_id . '/action=activate';

					$r = wp_remote_get( $url, array( 'timeout' => 30 ) );
				
					if ( is_wp_error( $r ) ) {
						$m_type = 'error';
						$m = esc_html__( 'Error 03. Please try again later.', 'bold-timeline' );
					} else {
						$r = json_decode( $r['body'] );
					}

					// errors
					if ( ! is_object( $r ) || ! property_exists( $r, 'code' ) ) {
						$m_type = 'error';
						$m = esc_html__( 'Error 04. Please try again later.', 'bold-timeline' );
					} else if ( $r->code == 'rest_invalid_param' ) {
						$m_type = 'error';
						$m = esc_html__( 'Error. Please check that you have entered the correct data.', 'bold-timeline' );
					} else if ( $r->code == 'api_error' ) {
						$m_type = 'error';
						if ( $r->message == 'invalid purchase code' ) {
							$m = esc_html__( 'Error. Please check Purchase Code.', 'bold-timeline' );
						} else {
							$m = esc_html__( 'Error 05. Please try again later.', 'bold-timeline' );
						}
					} else if ( $r->code == 'already_activated' ) {
						$m_type = 'error';
						$m = esc_html__( 'Error. Purchase code already activated on domain ', 'bold-timeline' ) . $r->message . esc_html__( '.', 'bold-timeline' );
					// sucess
					} else if ( $r->code == 'success' ) {
						$m_type = 'ok';
						$m = esc_html__( 'License has been activated.', 'bold-timeline' );
						
						if ( $purchase_code == 'nopurchasecode' ) {
							$purchase_code = '';
						}
						if ( $email == 'noemail' ) {
							$email = '';
						}
						
						$this->license = array( 'purchase_code' => $purchase_code, 'email' => $email );
						update_site_option( $this->slug . '-license', $this->license );
						
					}
					
					if ( $purchase_code == 'nopurchasecode' ) {
						$purchase_code = '';
					}
					if ( $email == 'noemail' ) {
						$email = '';
					}
					
				}
				
			}

			if ( $this->license ) {
				$purchase_code = $this->license['purchase_code'];
				$email = $this->license['email'];
				$disabled = ' disabled';
			}
			
			?>

			<div class="wrap">

			<h1 class="wp-heading-inline"><?php
				esc_html_e( 'Product License', 'bold-timeline' );
			?></h1>

			<form method="post" action="">

				<?php 
				
				wp_nonce_field( 'bt-bb-light-license' );
				
				if ( $this->license ) { ?>
					<input type="hidden" name="deactivate">
				<?php } 
				if ( $disabled ) { ?>
					<input type="hidden" name="purchase_code" value="<?php echo esc_attr( $purchase_code ); ?>">
					<input type="hidden" name="email" value="<?php echo esc_attr( $email ); ?>">
				<?php } 
				?>

				<div id="poststuff">
				<div id="post-body" class="metabox-holder columns-2">
				<div id="post-body-content">
				
				<p class="bt-bb-light-description"><?php echo sprintf( esc_html__( 'In order to receive all benefits, you need to activate your copy of the plugin. By activating license you will unlock premium options - %sdirect plugin updates%s and %sassistance of our support team%s.' ), '<strong>', '</strong>', '<strong>', '</strong>' ); ?></p>
				
				<p class="bt-bb-light-description"><?php esc_html_e( 'If you do not have a license or you have activated a license on another site, then you can ', 'bold-timeline' ); ?><a href="<?php echo esc_url( $this->home_url ); ?>" target="_blank"><em><?php esc_html_e( 'purchase a license here', 'bold-timeline' ); ?></em></a><?php esc_html_e( '.', 'bold-timeline' ); ?></p>
				
					<table class="form-table" role="presentation">
						<tbody>
							<tr>
								<th scope="row"><label for="purchase_code"><?php esc_html_e( 'Purchase Code', 'bold-timeline' ); ?></label></th>
								<td>
									<input name="purchase_code" type="text" id="purchase_code" aria-describedby="purchase_code_description" value="<?php echo esc_attr( $purchase_code ); ?>" class="regular-text"<?php echo esc_html( $disabled ); ?>>
									<p class="description" id="purchase_code_description"><?php esc_html_e( 'Enter your purchase code here.', 'bold-timeline' ); ?></p>
								</td>
							</tr>
							<tr>
								<th scope="row"><label for="email"><?php esc_html_e( 'Email Address (optional)', 'bold-timeline' ); ?></label></th>
								<td>
									<input name="email" type="text" id="email" aria-describedby="email_description" value="<?php echo esc_attr( $email ); ?>" class="regular-text"<?php echo esc_html( $disabled ); ?>>
									<p class="description" id="email_description"><?php esc_html_e( 'Enter to get important info and special offers.', 'bold-timeline' ); ?></p>
								</td>
							</tr>
						</tbody>
					</table>
					
				</div><!-- #post-body-content -->

				</div><!-- #post-body -->
				<br class="clear" />
				</div><!-- #poststuff -->
				
				<?php
				
				if ( $m != '' ) { ?>
					<p class="bt-bb-light-message bt-bb-light-message-<?php echo esc_attr( $m_type ); ?>"><?php echo esc_html( $m ); ?></p>
				<?php }
				
				if ( $this->license ) {
					submit_button( esc_html__( 'Deactivate', 'bold-timeline' ) );
				} else {
					submit_button( esc_html__( 'Submit', 'bold-timeline' ) );
				}
				
				?>

			</form>

			<script>
				
			</script>

			<?php

		}

		// admin notices
		function admin_notices() {
			if ( empty( $_REQUEST['message'] ) ) {
				return;
			}
			if ( ! isset( $_REQUEST['page'] ) || strpos( $_REQUEST['page'], $this->slug ) === false ) {
				return;
			}
			if ( 'created' == $_REQUEST['message'] ) {
				$updated_message = esc_html__( 'Post created.', 'bold-timeline' );
			} elseif ( 'saved' == $_REQUEST['message'] ) {
				$updated_message = esc_html__( 'Post saved.', 'bold-timeline' );
			} elseif ( 'post_deleted' == $_REQUEST['message'] ) {
				$updated_message = esc_html__( 'Post deleted.', 'bold-timeline' );
			} elseif ( 'posts_deleted' == $_REQUEST['message'] ) {
				$updated_message = esc_html__( 'Posts deleted.', 'bold-timeline' );
			}

			if ( ! empty( $updated_message ) ) {
				echo sprintf( '<div id="message" class="notice notice-success is-dismissible"><p>%s</p></div>', esc_html( $updated_message ) );
				return;
			}
		}
		
		static function responsive_data_override_class( &$class, &$data_override_class, $arr ) {
			if ( $arr['value'] != '' ) {
				if ( strpos( $arr['value'], '%$%' ) !== false ) {
					$value_arr = explode( '%$%', $arr['value'] );
				} else {
					$value_arr = explode( ',;,', $arr['value'] );
				}
				if ( isset( $arr['suffix'] ) ) {
					$suffix = $arr['suffix'];
				} else {
					$suffix = '_';
				}
				
				$main = $arr['prefix'] . $arr['param'] . $suffix . $value_arr[0];
				
				$class[] = $main;
				
				if ( count( $value_arr ) == 5 ) {
					$data_override_class[ $arr['prefix'] . $arr['param'] . $suffix ][ 'current_class' ] = $main;
					$data_override_class[ $arr['prefix'] . $arr['param'] . $suffix ][ 'xxl' ] = $value_arr[0];
					$data_override_class[ $arr['prefix'] . $arr['param'] . $suffix ][ 'xl' ] = $value_arr[0];

					if ( $value_arr[1] != '' ) {
						$data_override_class[ $arr['prefix'] . $arr['param'] . $suffix ][ 'lg' ] = $value_arr[1];
					}
					if ( $value_arr[2] != '' ) {
						$data_override_class[ $arr['prefix'] . $arr['param'] . $suffix ][ 'md' ] = $value_arr[2];
					}
					if ( $value_arr[3] != '' ) {
						$data_override_class[ $arr['prefix'] . $arr['param'] . $suffix ][ 'sm' ] = $value_arr[3];
					}
					if ( $value_arr[4] != '' ) {
						$data_override_class[ $arr['prefix'] . $arr['param'] . $suffix ][ 'xs' ] = $value_arr[4];
					}
				} else if ( count( $value_arr ) == 6 ) {
					$data_override_class[ $arr['prefix'] . $arr['param'] . $suffix ][ 'current_class' ] = $main;
					$data_override_class[ $arr['prefix'] . $arr['param'] . $suffix ][ 'xxl' ] = $value_arr[0];

					if ( $value_arr[1] != '' ) {
						$data_override_class[ $arr['prefix'] . $arr['param'] . $suffix ][ 'xl' ] = $value_arr[1];
					}
					if ( $value_arr[2] != '' ) {
						$data_override_class[ $arr['prefix'] . $arr['param'] . $suffix ][ 'lg' ] = $value_arr[2];
					}
					if ( $value_arr[3] != '' ) {
						$data_override_class[ $arr['prefix'] . $arr['param'] . $suffix ][ 'md' ] = $value_arr[3];
					}
					if ( $value_arr[4] != '' ) {
						$data_override_class[ $arr['prefix'] . $arr['param'] . $suffix ][ 'sm' ] = $value_arr[4];
					}
					if ( $value_arr[5] != '' ) {
						$data_override_class[ $arr['prefix'] . $arr['param'] . $suffix ][ 'xs' ] = $value_arr[5];
					}
				}
			}
		}
		
		static function responsive_data_override_style_var( &$style, &$data_override_style_var, $arr ) {
			if ( $arr['value'] != '' ) {
				if ( strpos( $arr['value'], '%$%' ) !== false ) {
					$value_arr = explode( '%$%', $arr['value'] );
				} else {
					$value_arr = explode( ',;,', $arr['value'] );
				}
				
				$main = '--' . $arr['prefix'] . $arr['param'] . ': ' . $value_arr[0];
				
				$style[] = $main;
				
				if ( count( $value_arr ) == 5 ) {
					$data_override_style_var[ $arr['prefix'] . $arr['param'] ][ 'xxl' ] = $value_arr[0];
					$data_override_style_var[ $arr['prefix'] . $arr['param'] ][ 'xl' ] = $value_arr[0];

					if ( $value_arr[1] != '' ) {
						$data_override_style_var[ $arr['prefix'] . $arr['param'] ][ 'lg' ] = $value_arr[1];
					}
					if ( $value_arr[2] != '' ) {
						$data_override_style_var[ $arr['prefix'] . $arr['param'] ][ 'md' ] = $value_arr[2];
					}
					if ( $value_arr[3] != '' ) {
						$data_override_style_var[ $arr['prefix'] . $arr['param'] ][ 'sm' ] = $value_arr[3];
					}
					if ( $value_arr[4] != '' ) {
						$data_override_style_var[ $arr['prefix'] . $arr['param'] ][ 'xs' ] = $value_arr[4];
					}
				} else if ( count( $value_arr ) == 6 ) {
					$data_override_style_var[ $arr['prefix'] . $arr['param'] ][ 'xxl' ] = $value_arr[0];
					
					if ( $value_arr[1] != '' ) {
						$data_override_style_var[ $arr['prefix'] . $arr['param'] ][ 'xl' ] = $value_arr[1];
					}
					if ( $value_arr[2] != '' ) {
						$data_override_style_var[ $arr['prefix'] . $arr['param'] ][ 'lg' ] = $value_arr[2];
					}
					if ( $value_arr[3] != '' ) {
						$data_override_style_var[ $arr['prefix'] . $arr['param'] ][ 'md' ] = $value_arr[3];
					}
					if ( $value_arr[4] != '' ) {
						$data_override_style_var[ $arr['prefix'] . $arr['param'] ][ 'sm' ] = $value_arr[4];
					}
					if ( $value_arr[5] != '' ) {
						$data_override_style_var[ $arr['prefix'] . $arr['param'] ][ 'xs' ] = $value_arr[5];
					}
				}
			}
		}		
		
	}
	
}

if ( ! class_exists( 'BTBB_Light_Map_Proxy' ) ) {
	class BTBB_Light_Map_Proxy {
		public $base;
		public $params;
		function __construct( $base, $params, &$map ) {
			$this->base = $base;
			$params['base'] = $base;
			$this->params = $params;
		}

		public function js_map() {
			if ( shortcode_exists( $this->base ) ) {
				if ( isset( $this->params['admin_enqueue_css'] ) ) {
					foreach( $this->params['admin_enqueue_css'] as $item ) {
						wp_enqueue_style( 'bt_bb_admin_' . uniqid(), $item );
					}
				}
				echo 'window.bt_bb_map["' . $this->base . '"] = window.bt_bb_map_primary.' . $this->base . ' = ' . json_encode( $this->params ) . ';';
				$map[ $this->base ] = $this->params;
			}
		}
	}
	
}

/**
 * Initial data.
 */

if ( ! class_exists( 'BTBB_Light_Data_Proxy' ) ) {

	class BTBB_Light_Data_Proxy {
		public $data;
		function __construct( $data ) {
			$this->data = $data;
		}
		public function js() {
			echo '<script>window.bt_bb_data = { title: "_root", base: "_root", key: "' . uniqid( 'bt_bb_' ) . '", children: ' . $this->data . ' };</script>';
		}
	}
	
}

if ( ! class_exists( 'BTBB_Light_Callback' ) ) {

	class BTBB_Light_Callback {

		private $bt_bb_array;
		public $elements;

		function __construct( &$bt_bb_array, $elements ) {
			$this->bt_bb_array = &$bt_bb_array;
			$this->elements = $elements;
		}

		function bt_bb_do_shortcode_tag( $m ) {

			// allow [[foo]] syntax for escaping a tag
			if ( $m[1] == '[' && $m[6] == ']' ) {
				return $m[0];
			}

			$tag = $m[2];
			$attr = shortcode_parse_atts( $m[3] );

			if ( is_array( $attr ) ) {
				$this->bt_bb_array[] = array( 'title' => $tag, 'base' => $tag, 'key' => str_replace( '.', '', uniqid( 'bt_bb_', true ) ), 'attr' => json_encode( $attr ), 'children' => array() );
			} else {
				$this->bt_bb_array[] = array( 'title' => $tag, 'base' => $tag, 'key' => str_replace( '.', '', uniqid( 'bt_bb_', true ) ), 'children' => array() );
			}

			if ( isset( $m[5] ) && $m[5] != '' ) {
				// enclosing tag - extra parameter
				$pattern = get_shortcode_regex();
				
				if ( isset( $this->elements[ $m[2] ]['accept']['_content'] ) && $this->elements[ $m[2] ]['accept']['_content'] ) {
					$r = $m[5];
				} else {
					$callback = new BTBB_Light_Callback( $this->bt_bb_array[ count( $this->bt_bb_array ) - 1 ]['children'], $this->elements );
					$r = preg_replace_callback( "/$pattern/s", array( $callback, 'bt_bb_do_shortcode_tag' ), $m[5] );
					$r = trim( $r );
				}
			
				if ( $r != '' ) {
					$this->bt_bb_array[ count( $this->bt_bb_array ) - 1 ]['children'][0] = array( 'title' => '_content', 'base' => '_content', 'content' => $r, 'children' => array() );
				}
			}
		}	
	}	
	
}
