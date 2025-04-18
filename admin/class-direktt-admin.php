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

		/*add_submenu_page(
			'direktt-dashboard',
			__('Hello', 'direktt'),
			__('Hello', 'direktt'),
			'manage_options',
			'direktt-hello',
			[$this, 'render_hello']
		);*/

	}

	public function setup_settings_pages(){
		do_action('direktt_setup_settings_pages');
	}

	public function on_setup_settings_pages(){
		Direktt::add_settings_page(
			array(
				"id" => "bulk-message",
				"label" => __('Bulk Messaging Settings', 'direktt'),
				"callback" => [$this, 'render_bulk_message_settings']
			)
		);
	}

	public function highlight_direktt_submenu($parent_file)
	{
		global $submenu_file, $current_screen, $pagenow;

		if ($pagenow == 'edit-tags.php' && $current_screen->taxonomy == 'direkttusercategories') {
			$submenu_file = 'edit-tags.php?taxonomy=direkttusercategories';
			$parent_file = 'direktt-dashboard';
		}

		if ($pagenow == 'edit-tags.php' && $current_screen->taxonomy == 'direkttusertags') {
			$submenu_file = 'edit-tags.php?taxonomy=direkttusertags';
			$parent_file = 'direktt-dashboard';
		}

		return $parent_file;
	}

	public function register_custom_post_types()
	{

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
			'exclude_from_search' => false,
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
			'query_var'         => false,
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
			'hierarchical'      => true, // make it hierarchical (like categories)
			'public'			=> false,
			'labels'            => $labels,
			'show_ui'           => true,
			'show_admin_column' => true,
			'show_in_menu'      => 'direktt-dashboard',
			'show_in_nav_menus' => true,
			'query_var'         => false,
			'show_in_rest'	=> false,
			'publicly_queryable'  => false
		);

		register_taxonomy('direkttusertags', ['direkttusers'], $args);

		// User role direktt

		add_role('direktt', 'Direktt User', array());

		// Message templates

		$labels = array(
			'name'                => __('Message Templates', 'direktt'),
			'singular_name'       => __('Message Template',  'direktt'),
			'menu_name'           => __('Direktt', 'direktt'),
			'all_items'           => __('Message Templates', 'direktt'),
			'view_item'           => __('View Message Template', 'direktt'),
			'add_new_item'        => __('Add New Message Template', 'direktt'),
			'add_new'             => __('Add New', 'direktt'),
			'edit_item'           => __('Edit Message Template', 'direktt'),
			'update_item'         => __('Update Message Template', 'direktt'),
			'search_items'        => __('Search Message Templates', 'direktt'),
			'not_found'           => __('Not Found', 'direktt'),
			'not_found_in_trash'  => __('Not found in Trash', 'direktt'),
		);

		$args = array(
			'label'               => __('message templates', 'direktt'),
			'description'         => __('Message Templates', 'direktt'),
			'labels'              => $labels,
			'supports'            => array('title'),
			'hierarchical'        => false,
			'public'              => false,
			'show_ui'             => true,
			'show_in_menu'        => 'direktt-dashboard',
			'show_in_nav_menus'   => true,
			'show_in_admin_bar'   => true,
			'menu_position'       => 5,
			'can_export'          => true,
			'has_archive'         => false,
			'exclude_from_search' => false,
			'publicly_queryable'  => false,
			'capability_type'     => 'post',
			'capabilities'          => array(
				// todo Srediti prava za new i edit ako ikako moze, ako ne, ostaviti new
				//'create_posts' => 'do_not_allow', 
				//'edit_posts' => 'allow' 
			),
			'show_in_rest'	=> false,
		);
		register_post_type('direkttmtemplates', $args);
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
				plugin_dir_url(__DIR__) . 'js/dashboard/direktt-dashboard.css',
				[],
				''
			);

			$nonce = wp_create_nonce($this->plugin_name . '-dashboard');

			wp_localize_script(
				$this->plugin_name . '-dashboard',
				$this->plugin_name . '_dashboard_object',
				array(
					'ajaxurl' => admin_url('admin-ajax.php'),
					'nonce' => $nonce
				)
			);
		}

		// CPT direktusers

		if ($suffix == 'post.php') {
			global $post;

			if ('direkttusers' === $post->post_type) {

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
		$active_tab = isset($_GET['subpage']) ? $_GET['subpage'] : '';

		if ($active_tab == '') {
		?>
			<div id="app"></div>
		<?php
		} else {
			foreach (Direktt::$settings_array as $item) {
				if ( isset( $item['id'] ) && $active_tab == $item['id'] ) {
					echo('<h1>' . $item['label'] . '</h1>');
					call_user_func($item['callback']);
				} 
			}
		}

		$url = $_SERVER['REQUEST_URI'];
		$parts = parse_url($url);

		if( !empty(Direktt::$settings_array) ){
			parse_str($parts['query'] ?? '', $params);
			unset($params['subpage']);
			$newQuery = http_build_query($params);
			$newUri = $parts['path'] . ($newQuery ? '?' . $newQuery : '');
			echo('<p><a href="' . $newUri . '">' . __('Direktt Settings', 'direktt') . '</a></p>');
		}

		foreach (Direktt::$settings_array as $item) {
			if ( isset( $item['label'] ) ) {
				parse_str($parts['query'] ?? '', $params);
				$params['subpage'] = $item['id'];
				$newQuery = http_build_query($params);
				$newUri = $parts['path'] . ($newQuery ? '?' . $newQuery : '');
				echo('<p><a href="' . $newUri . '">' . $item['label'] . '</a></p>');
			} 
		}

		$url = $_SERVER['REQUEST_URI'];
		$parts = parse_url($url);

		parse_str($parts['query'] ?? '', $params);

		$params['foo'] = 'new_value'; 
		unset($params['bar']); 

	}

	public function render_bulk_message_settings()
	{
		?>
		Hello
	<?php
	}

	public function render_meta_panel($post)
	{
		if ($post->post_type != 'direkttusers') return;
	?>
		<div id="app"></div>
		<?php
	}

	public function render_user_meta_panel($user)
	{
		if ($user instanceof WP_User && !in_array('direktt', $user->roles)) {

			$direktt_user_id = get_user_meta($user->ID, 'direktt_user_id', true);
			$direktt_user_pair_code = Direktt_User::get_or_generate_user_pair_code($user->ID);

		?>
			<h2>Direktt Test User</h2>
			<table class="form-table" role="presentation">
				<tbody v-if="data">
					<tr>
						<th scope="row"><label for="direktt_test_user_id">Post Id of Test Direktt User <p class="description">Post id of Direktt User which will be used on Direktt pages</p></label></th>
						<td>
							<input type="text" name="direktt_test_user_id" id="direktt_test_user_id" size="50" placeholder="Enter Direktt User Id here" value="<?php echo esc_attr(get_user_meta($user->ID, 'direktt_test_user_id', true)); ?>">
						</td>
					</tr>

					<?php

					if (! empty($direktt_user_id)) {
					?>

						<tr>
							<th scope="row"><label for="direktt_test_user_id">Post Id of related Direktt User:</label></th>
							<td>
								<b><?php echo esc_attr($direktt_user_id); ?> - <?php echo '<a href="' . esc_url(get_edit_post_link($direktt_user_id)) . '">View Post</a>' ?></b>
							</td>
						</tr>

						<tr>
							<th scope="row"><label for="direktt_test_user_id">Delete Relation with Direktt User</label></th>
							<td>
								<label for="direktt_delete_relation">
									<input name="direktt_delete_relation" type="checkbox" id="direktt_delete_relation" value="1">
									Check to delete relation with Direktt User and click Update Profile </label>
							</td>
						</tr>

					<?php

					} else if (! empty($direktt_user_pair_code)) {
					?>

						<tr>
							<th scope="row"><label for="direktt_test_user_id">Code for pairing with related Direktt User:</label></th>
							<td>
								<b><?php echo esc_html($direktt_user_pair_code);  ?></b>
							</td>
						</tr>

						<tr>
							<th scope="row"><label for="direktt_test_user_id">Reset Pairing Code</label></th>
							<td>
								<label for="direktt_update_code">
									<input name="direktt_update_code" type="checkbox" id="direktt_update_code" value="1">
									Check to update pairing code and click Update Profile </label>
							</td>
						</tr>

					<?php

					}

					?>
				</tbody>
			</table>
		<?php
		}
	}

	function save_user_meta_panel($userId)
	{
		if (!current_user_can('edit_user', $userId)) {
			return;
		}

		if (isset($_POST['direktt_test_user_id'])) {
			update_user_meta($userId, 'direktt_test_user_id', sanitize_text_field($_POST['direktt_test_user_id']));
		} else {
			delete_user_meta($userId, 'direktt_test_user_id');
		}

		if (isset($_POST['direktt_update_code'])) {
			delete_user_meta($userId, 'direktt_user_pair_code');
		}

		if (isset($_POST['direktt_delete_relation'])) {
			delete_user_meta($userId, 'direktt_user_id');
		}
	}

	public function page_direktt_custom_box()
	{
		$screens = ['page'];
		foreach ($screens as $screen) {
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

	public function render_direktt_custom_box($post)
	{
		wp_nonce_field('direktt_custom_box_nonce', 'direktt_custom_box_nonce');
		$box_value = intval(get_post_meta($post->ID, 'direktt_custom_box', true)) === 1;
		$box_checked = $box_value ? 'checked' : 0;
		$box_admin_value = intval(get_post_meta($post->ID, 'direktt_custom_admin_box', true)) === 1;
		$box_admin_checked = $box_admin_value ? 'checked' : 0;
		?>
		<p>
			<input id="direktt_custom_box" name="direktt_custom_box" type="checkbox" <?php echo $box_checked ?>>
			<label><?php echo __('Restrict access to Direktt users', 'direktt') ?></label>
		</p>
		<p>
			<input id="direktt_custom_admin_box" name="direktt_custom_admin_box" type="checkbox" <?php echo $box_admin_checked ?>>
			<label><?php echo __('Restrict access to Direktt admins', 'direktt') ?></label>
		</p>
<?php
	}

	function save_direktt_custom_box($post_id, $post)
	{

		// nonce check
		if (!isset($_POST['direktt_custom_box_nonce']) || !wp_verify_nonce($_POST['direktt_custom_box_nonce'], 'direktt_custom_box_nonce')) {
			return $post_id;
		}

		// check current user permissions
		$post_type = get_post_type_object($post->post_type);

		if (!current_user_can($post_type->cap->edit_post, $post_id)) {
			return $post_id;
		}

		// Do not save the data if autosave
		if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
			return $post_id;
		}

		if ('page' !== $post->post_type) {
			return $post_id;
		}

		if (isset($_POST['direktt_custom_box']) && sanitize_text_field($_POST['direktt_custom_box']) == 'on') {
			update_post_meta($post_id, 'direktt_custom_box', true);
		} else {
			delete_post_meta($post_id, 'direktt_custom_box');
		}

		if (isset($_POST['direktt_custom_admin_box']) && sanitize_text_field($_POST['direktt_custom_admin_box']) == 'on') {
			update_post_meta($post_id, 'direktt_custom_admin_box', true);
		} else {
			delete_post_meta($post_id, 'direktt_custom_admin_box');
		}

		return $post_id;
	}

	function direkttmtemplates_add_custom_box()
	{
		add_meta_box(
			'direkttMTJson_textarea',           // ID
			__('Message JSON Content', 'direktt'),                       // Title
			[$this, 'direkttmtemplates_render_textarea'],    // Callback function
			'direkttmtemplates',                    // CPT slug
			'normal',                        // Context
			'high'                           // Priority
		);
	}

	function direkttmtemplates_render_textarea($post)
	{
		$value = get_post_meta($post->ID, 'direkttMTJson', true);

		echo '<textarea style="width:100%" rows="10" name="direktt_mt_json">' . esc_textarea($value) . '</textarea>';

		// Get stored value or default
		$dropdown_value = get_post_meta($post->ID, 'direkttMTType', true);

		if (!$dropdown_value) $dropdown_value = 'all';

		echo '<p><label for="direktt_mt_type"><strong>' . __('Message Type', 'direktt') . '</strong></label> ';
		echo '<select name="direktt_mt_type" id="direktt_mt_type">';
		echo '<option value="all"' . selected($dropdown_value, 'all', false) . '>' . __('All Messages', 'direktt') . '</option>';
		echo '<option value="bulk"' . selected($dropdown_value, 'bulk', false) . '>' . __('Only Bulk Messages', 'direktt') . '</option>';
		echo '<option value="individual"' . selected($dropdown_value, 'individual', false) . '>' . __('Only Individual Messages', 'direktt') . '</option>';
		echo '<option value="none"' . selected($dropdown_value, 'none', false) . '>' . __('Use it only via API', 'direktt') . '</option>';
		echo '</select></p>';

		// Security nonce
		wp_nonce_field('direktt_mt_json_nonce', 'direktt_mt_json_nonce');
	}

	function direkttmtemplates_save_meta_box_data($post_id)
	{
		if (
			!isset($_POST['direktt_mt_json_nonce']) ||
			!wp_verify_nonce($_POST['direktt_mt_json_nonce'], 'direktt_mt_json_nonce')
		) {
			return;
		}
		if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
		if (isset($_POST['post_type']) && 'direkttmtemplates' === $_POST['post_type']) {
			$content = sanitize_textarea_field($_POST['direktt_mt_json']);
			update_post_meta($post_id, 'direkttMTJson', $content);
		}

		if (isset($_POST['direktt_mt_type'])) {
			$dropdown_value = sanitize_text_field($_POST['direktt_mt_type']);
			update_post_meta($post_id, 'direkttMTType', $dropdown_value);
		}
	}
}
