<?php

class Direktt_Admin
{
	private string $plugin_name;
	private string $version;

	public function __construct(string $plugin_name, string $version)
	{
		$this->plugin_name = $plugin_name;
		$this->version     = $version;
	}

	public function register_menu_page()
	{
		add_menu_page(
			__('Direktt', 'direktt'),
			__('Direktt', 'direktt'),
			"manage_options",
			"direktt-dashboard",
			[$this, 'render_admin_page'],
			"",
			30
		);

		add_submenu_page(
			'direktt-dashboard',
			__('Dashboard', 'direktt'),
			__('Dashboard', 'direktt'),
			'manage_options',
			'direktt-dashboard'
		);
	}

	public function register_menu_page_end()
	{

		add_submenu_page(
			'direktt-dashboard', 
			__('User Categories', 'direktt'), 
			__('User Categories', 'direktt'),
			'manage_options', 
			'edit-tags.php?taxonomy=direkttusercategories', 
			false
		);

		add_submenu_page(
			'direktt-dashboard', 
			__('User Tags', 'direktt'), 
			__('User Tags', 'direktt'),
			'manage_options', 
			'edit-tags.php?taxonomy=direkttusertags', 
			false
		);

		add_submenu_page(
			'direktt-dashboard',
			__('Settings', 'direktt'),
			__('Settings', 'direktt'),
			'manage_options',
			'direktt-settings',
			[$this, 'render_admin_page']
		);
	}

	public function highlight_direktt_submenu($parent_file){
		global $submenu_file, $current_screen, $pagenow;
		
		if ( $pagenow == 'edit-tags.php' && $current_screen->taxonomy == 'direkttusercategories' ) {
			$submenu_file = 'edit-tags.php?taxonomy=direkttusercategories';
			$parent_file = 'direktt-dashboard';
		}

		if ( $pagenow == 'edit-tags.php' && $current_screen->taxonomy == 'direkttusertags' ) {
			$submenu_file = 'edit-tags.php?taxonomy=direkttusertags';
			$parent_file = 'direktt-dashboard';
		}
		
		return $parent_file;
	}

	public function register_custom_post_types()
	{

		// User Categories

		$labels = array(
			'name'              => _x('User Categories', 'taxonomy general name', 'direktt'),
			'singular_name'     => _x('User Category', 'taxonomy singular name', 'direktt'),
			'search_items'      => __('Search Categories', 'direktt'),
			'all_items'         => __('All Categories', 'direktt'),
			'parent_item'       => __('Parent Category', 'direktt'),
			'parent_item_colon' => __('Parent Category:', 'direktt'),
			'edit_item'         => __('Edit Category', 'direktt'),
			'update_item'       => __('Update Category', 'direktt'),
			'add_new_item'      => __('Add New Category', 'direktt'),
			'new_item_name'     => __('New Category Name', 'direktt'),
			'menu_name'         => __('Category', 'direktt'),
		);
		$args   = array(
			'hierarchical'      => true, 
			'public'			=> false,
			'labels'            => $labels,
			'show_ui'           => true,
			'show_admin_column' => true,
			'show_in_menu'      => 'direktt-dashboard',
			'show_in_nav_menus' => true,
			'query_var'         => true,
			'show_in_rest'	=> false,
			'publicly_queryable'  => false
		);

		register_taxonomy('direkttusercategories', ['direkttusers'], $args);

		// User Tags

		$labels = array(
			'name'              => _x('User Tags', 'taxonomy general name', 'direktt'),
			'singular_name'     => _x('User Tag', 'taxonomy singular name', 'direktt'),
			'search_items'      => __('Search Tags', 'direktt'),
			'all_items'         => __('All Tags', 'direktt'),
			'parent_item'       => __('Parent Tag', 'direktt'),
			'parent_item_colon' => __('Parent Tag:', 'direktt'),
			'edit_item'         => __('Edit Tag', 'direktt'),
			'update_item'       => __('Update Tag', 'direktt'),
			'add_new_item'      => __('Add New Tag', 'direktt'),
			'new_item_name'     => __('New Tag Name', 'direktt'),
			'menu_name'         => __('Tag', 'direktt'),
		);
		$args   = array(
			'hierarchical'      => false, // make it hierarchical (like categories)
			'public'			=> false,
			'labels'            => $labels,
			'show_ui'           => true,
			'show_admin_column' => true,
			'show_in_menu'      => 'direktt-dashboard',
			'show_in_nav_menus' => true,
			'query_var'         => true,
			'show_in_rest'	=> false,
			'publicly_queryable'  => false
		);

		register_taxonomy('direkttusertags', ['direkttusers'], $args);

		// Users

		$labels = array(
			'name'                => __('Direktt Users', 'direktt'),
			'singular_name'       => __('Direktt User',  'direktt'),
			'menu_name'           => __('Direktt', 'direktt'),
			'all_items'           => __('Direktt Users', 'direktt'),
			'view_item'           => __('View Direktt User', 'direktt'),
			'add_new_item'        => __('Add New Direktt User', 'direktt'),
			'add_new'             => __('Add New', 'direktt'),
			'edit_item'           => __('Edit Direktt User', 'direktt'),
			'update_item'         => __('Update Direktt User', 'direktt'),
			'search_items'        => __('Search Direktt Users', 'direktt'),
			'not_found'           => __('Not Found', 'direktt'),
			'not_found_in_trash'  => __('Not found in Trash', 'direktt'),
		);

		$args = array(
			'label'               => __('users', 'direktt'),
			'description'         => __('Direktt Users', 'direktt'),
			'labels'              => $labels,
			'supports'            => array('title', 'editor'),
			'hierarchical'        => false,
			'public'              => false,
			'show_ui'             => true,
			'show_in_menu'        => 'direktt-dashboard',
			'show_in_nav_menus'   => true,
			'show_in_admin_bar'   => true,
			'menu_position'       => 5,
			'can_export'          => true,
			'has_archive'         => false,
			'exclude_from_search' => true,
			'publicly_queryable'  => false,
			'capability_type'     => 'post',
			'capabilities'          => array(
				// todo Srediti prava za new i edit ako ikako moze, ako ne, ostaviti new
				//'create_posts' => 'do_not_allow', 
				//'edit_posts' => 'allow' 
			),
			'show_in_rest'	=> false,
		);

		register_post_type('direkttusers', $args);
	}

	// todo skloniti jer nam realno ne treba

	public function display_admin_notice()
	{
		if (!get_option('jwt_auth_admin_notice')) {
?>
			<div class="notice notice-info is-dismissible">
				<p>
					<?php
					printf(
						/* translators: %s: Link to the JWT Authentication settings page */
						__(
							'Please visit the <a href="%s">JWT Authentication settings page</a> for an important message from the author.',
							'direktt'
						),
						admin_url('options-general.php?page=jwt_authentication')
					);
					?>
				</p>
			</div>
		<?php
			update_option('jwt_auth_admin_notice', true);
		}
	}

	public function enqueue_plugin_assets(string $suffix)
	{
		// Settings page

		if ($suffix == 'direktt_page_direktt-settings') {
			wp_enqueue_script(
				$this->plugin_name . '-settings',
				plugin_dir_url(__DIR__) . 'js/settings/direktt-settings.js',
				array('jquery'),
				'',
				[
					'in_footer' => true,
				]
			);

			wp_enqueue_style(
				$this->plugin_name . '-settings',
				plugin_dir_url(__DIR__) . 'js/settings/direktt-settings.css',
				[],
				''
			);

			$nonce = wp_create_nonce($this->plugin_name . '-settings');

			wp_localize_script(
				$this->plugin_name . '-settings',
				$this->plugin_name . '_settings_object',
				array(
					'ajaxurl' => admin_url('admin-ajax.php'),
					'nonce' => $nonce
				)
			);
		}

		// Dashboard

		if ($suffix == 'toplevel_page_direktt-dashboard') {
			wp_enqueue_script(
				$this->plugin_name . '-dashboard',
				plugin_dir_url(__DIR__) . 'js/dashboard/direktt-dashboard.js',
				[],
				'',
				[
					'in_footer' => true,
				]
			);

			// Enqueue the style file
			wp_enqueue_style(
				$this->plugin_name . '-dashboard',
				plugin_dir_url(__DIR__) . 'css/style.js',
				[],
				''
			);
		}

		// CPT direktusers

		if ($suffix == 'post.php') {
			global $post;

			if ( 'direkttusers' === $post->post_type ) {

				wp_enqueue_script(
					$this->plugin_name . '-users',
					plugin_dir_url(__DIR__) . 'js/users/direktt-users.js',
					[],
					'',
					[
						'in_footer' => true,
					]
				);
	
				// Enqueue the style file
				wp_enqueue_style(
					$this->plugin_name . '-users',
					plugin_dir_url(__DIR__) . 'js/users/direktt-users.css',
					[],
					''
				);

				wp_localize_script(
					$this->plugin_name . '-users',
					$this->plugin_name . '_users_object',
					array(
						'ajaxurl' => admin_url('admin-ajax.php'),
						'postId' => $post->ID
					)
				);

			}
		}
	}
		
	public function render_admin_page()
	{
		?>
			<div id="app"></div>
		<?php
	}

	public function render_meta_panel( $post )
	{
		if ($post->post_type != 'direkttusers') return;
		?>
			<div id="app"></div>
		<?php
	}

	public function page_direktt_custom_box() {
		$screens = [ 'page' ];
		foreach ( $screens as $screen ) {
			add_meta_box(
				'direktt_features',                 // Unique ID
				'Direktt features',      // Box title
				array($this, 'render_direktt_custom_box'),  // Content callback, must be of type callable
				$screen,                            // Post type
				'side',
				'low'
			);
		}
	}

	public function render_direktt_custom_box( $post )
	{
		wp_nonce_field( 'direktt_custom_box_nonce', 'direktt_custom_box_nonce' );
		$box_value = intval(get_post_meta($post->ID, 'direktt_custom_box', true)) === 1;
		$box_checked = $box_value? 'checked': 0;
		?>
			<input id="direktt_custom_box" name="direktt_custom_box" type="checkbox" <?php echo $box_checked ?>>
			<label><?php echo __( 'Perform Direktt checks', 'direktt') ?></label>
		<?php
	}

	function save_direktt_custom_box( $post_id, $post ) {

		// nonce check
		if ( ! isset( $_POST[ 'direktt_custom_box_nonce' ] ) || ! wp_verify_nonce( $_POST[ 'direktt_custom_box_nonce' ], 'direktt_custom_box_nonce' ) ) {
			return $post_id;
		}
	
		// check current user permissions
		$post_type = get_post_type_object( $post->post_type );
	
		if ( ! current_user_can( $post_type->cap->edit_post, $post_id ) ) {
			return $post_id;
		}
	
		// Do not save the data if autosave
		if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) {
			return $post_id;
		}
	
		if( 'page' !== $post->post_type ) {
			return $post_id;
		}
	
		if( isset( $_POST[ 'direktt_custom_box' ] ) && sanitize_text_field( $_POST[ 'direktt_custom_box' ] ) == 'on' ) {
			update_post_meta( $post_id, 'direktt_custom_box', true );
		} else {
			delete_post_meta( $post_id, 'direktt_custom_box' );
		}
		
		return $post_id;
	}

}
