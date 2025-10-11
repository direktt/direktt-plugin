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
			[$this, 'render_admin_dashboard'],
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

	public function register_cpt_menus()
	{
		add_submenu_page(
			'direktt-dashboard',
			__('Direktt Users', 'direktt'),
			__('Direktt Users', 'direktt'),
			'edit_posts',
			'edit.php?post_type=direkttusers',
			null,
			2
		);

		add_submenu_page(
			'direktt-dashboard',
			__('User Categories', 'direktt'),
			__('User Categories', 'direktt'),
			'edit_posts',
			'edit-tags.php?taxonomy=direkttusercategories',
			null,
			3
		);

		add_submenu_page(
			'direktt-dashboard',
			__('User Tags', 'direktt'),
			__('User Tags', 'direktt'),
			'edit_posts',
			'edit-tags.php?taxonomy=direkttusertags',
			null,
			4
		);

		add_submenu_page(
			'direktt-dashboard',
			__('Message Templates', 'direktt'),
			__('Message Templates', 'direktt'),
			'edit_posts',
			'edit.php?post_type=direkttmtemplates',
			null,
			5
		);
	}

	public function register_menu_page_end()
	{
		add_submenu_page(
			'direktt-dashboard',
			__('Settings', 'direktt'),
			__('Settings', 'direktt'),
			'manage_options',
			'direktt-settings',
			[$this, 'render_admin_settings'],
			100
		);

		do_action('direktt_setup_settings_pages');
	}

	public function setup_admin_menu()
	{
		do_action('direktt_setup_admin_menu');
	}

	public function highlight_direktt_submenu($parent_file)
	{
		global $submenu_file, $current_screen, $pagenow;

		if ($pagenow === 'edit-tags.php' || $pagenow === 'term.php') {
			if ($current_screen->taxonomy === 'direkttusercategories') {
				$submenu_file = 'edit-tags.php?taxonomy=direkttusercategories';
				$parent_file = 'direktt-dashboard';
			} else if ($current_screen->taxonomy === 'direkttusertags') {
				$submenu_file = 'edit-tags.php?taxonomy=direkttusertags';
				$parent_file = 'direktt-dashboard';
			}
		} else if ($pagenow === 'post.php' || $pagenow === 'post-new.php') {
			if ($current_screen->post_type === 'direkttusers') {
				$submenu_file = 'edit.php?post_type=direkttusers';
				$parent_file = 'direktt-dashboard';
			} else if ($current_screen->post_type === 'direkttmtemplates') {
				$submenu_file = 'edit.php?post_type=direkttmtemplates';
				$parent_file = 'direktt-dashboard';
			}
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
			'show_in_menu'        => false,
			'show_in_nav_menus'   => true,
			'show_in_admin_bar'   => true,
			'menu_position'       => 2,
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
			'show_in_menu'        => false,
			'show_in_nav_menus'   => true,
			'show_in_admin_bar'   => true,
			'menu_position'       => 5,
			'can_export'          => true,
			'has_archive'         => false,
			'exclude_from_search' => false,
			'publicly_queryable'  => false,
			'capability_type'     => 'post',
			'capabilities'          => array(),
			'show_in_rest'	=> false,
		);
		register_post_type('direkttmtemplates', $args);
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

		if ($suffix == 'post.php' || $suffix == 'post-new.php') {
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

			if ('direkttmtemplates' === $post->post_type) {

				wp_enqueue_style(
					$this->plugin_name . '-admin-reset',
					plugin_dir_url(__DIR__) . 'admin/css/direktt-admin-reset.css',
					[],
					''
				);

				wp_enqueue_script(
					$this->plugin_name . '-mtemplates',
					plugin_dir_url(__DIR__) . 'js/mtemplates/direktt-mtemplates.js',
					[],
					'',
					[
						'in_footer' => true,
					]
				);

				// Enqueue the style file
				wp_enqueue_style(
					$this->plugin_name . '-mtemplates',
					plugin_dir_url(__DIR__) . 'js/mtemplates/direktt-mtemplates.css',
					[],
					''
				);

				wp_localize_script(
					$this->plugin_name . '-mtemplates',
					$this->plugin_name . '_mtemplates_object',
					array(
						'ajaxurl' => admin_url('admin-ajax.php'),
						'postId' => $post->ID
					)
				);

				wp_enqueue_media();
			}
		}

		wp_enqueue_style(
			$this->plugin_name . '-admin',
			plugin_dir_url(__DIR__) . 'admin/css/direktt-admin.css',
			[],
			''
		);
	}

	public function render_admin_dashboard()
	{

?>
		<div id="app"></div>
		<?php

	}

	public function render_admin_settings()
	{
		$active_tab = isset($_GET['subpage']) ? $_GET['subpage'] : '';

		$url = $_SERVER['REQUEST_URI'];
		$parts = parse_url($url);

		// Print out the Direktt Settings label and link

		echo ('<h1>' . esc_html__('Direktt Settings', 'direktt') . '</h1>');

		echo ('<nav class="nav-tab-wrapper">');

		if (!empty(Direktt::$settings_array)) {
			parse_str($parts['query'] ?? '', $params);
			unset($params['subpage']);
			$newQuery = http_build_query($params);
			$newUri = $parts['path'] . ($newQuery ? '?' . $newQuery : '');
			// Only apply 'nav-tab-active' when active_tab is empty

			$active_class = ($active_tab == '') ? ' nav-tab-active' : '';
			echo ('<a href="' . esc_url($newUri) . '" class="nav-tab' . $active_class . '">' . esc_html__('General Settings', 'direktt') . '</a>');
		}

		// Sort links by priority asc

		usort(Direktt::$settings_array, function ($a, $b) {
			return $a['priority'] <=> $b['priority'];
		});

		// Print out all other labels and links

		foreach (Direktt::$settings_array as $item) {
			if (isset($item['label'])) {
				parse_str($parts['query'] ?? '', $params);
				$params['subpage'] = $item['id'];
				$newQuery = http_build_query($params);
				$newUri = $parts['path'] . ($newQuery ? '?' . $newQuery : '');

				// Use nav-tab-active if active_tab matches this item's id
				
				$active_class = ($active_tab === $item['id']) ? ' nav-tab-active' : '';
				echo ('<a href="' . esc_url($newUri) . '" class="nav-tab' . $active_class . '">' . esc_html($item['label']) . '</a>');
			}
		}
		echo ('</nav>');

		if ($active_tab == '') {
		?>
			<div id="app"></div>
		<?php
		} else {
			foreach (Direktt::$settings_array as $item) {
				if (isset($item['id']) && $active_tab == $item['id']) {
					call_user_func($item['callback']);
				}
			}
		}
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

		if ($user instanceof WP_User) {

			$direktt_user_id = get_user_meta($user->ID, 'direktt_user_id', true);
			$direktt_wp_user_id = get_user_meta($user->ID, 'direktt_wp_user_id', true);
			$direktt_user_pair_code = $this->get_or_generate_user_pair_code($user->ID);

		?>
			<h2> <?php echo esc_html__('Direktt User Properties', 'direktt') ?></h2>
			<table class="form-table" role="presentation">
				<tbody v-if="data">
					<?php
					if (!Direktt_User::is_wp_user_direktt_role($user)) {
					?>
						<tr>
							<th scope="row"><label for="direktt_test_user_id">Post Id of Test Direktt User <p class="description">Post id of Direktt User which will be used on Direktt pages</p></label></th>
							<td>
								<input type="text" name="direktt_test_user_id" id="direktt_test_user_id" size="50" placeholder="Enter Direktt User Id here" value="<?php echo esc_attr(get_user_meta($user->ID, 'direktt_test_user_id', true)); ?>">
							</td>
						</tr>

						<?php

						$related_users = get_users(array(
							'role__in' => array('direktt'),
							'meta_key' => 'direktt_wp_user_id',
							'meta_value' => $user->ID,
							'fields' => 'ID'
						));

						if (!empty($related_users)) {

						?>

							<tr>
								<th scope="row"><label for="direktt_test_user_id">Post Id of related Direktt User:</label></th>
								<td>
									<b><?php echo esc_attr(get_user_meta($related_users[0], 'direktt_user_id', true)); ?> - <?php echo '<a href="' . esc_url(get_edit_post_link(get_user_meta($related_users[0], 'direktt_user_id', true))) . '">View Post</a>' ?></b>
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
						} else {

							// Ako ne postoji ispisati upar kod
							if (! empty($direktt_user_pair_code)) {
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
						}
					} else {
						// onda ide else (role == direkt)
						?>
						<tr>
							<th scope="row"><label for="direktt_test_user_id">Post Id of related Direktt User:</label></th>
							<td>
								<b><?php echo esc_attr($direktt_user_id); ?> - <?php echo '<a href="' . esc_url(get_edit_post_link($direktt_user_id)) . '">View Post</a>' ?></b>
							</td>
						</tr>
						<tr>
							<th scope="row"><label for="direktt_wp_user_id">User Id of related WordPress User:</label></th>
							<td>
								<?php
								if ($direktt_wp_user_id) {
								?>
									<b><?php echo esc_attr($direktt_wp_user_id); ?> - <?php echo '<a href="' . esc_url(get_edit_user_link($direktt_wp_user_id)) . '">View User</a>' ?></b>
								<?php
								} else {
								?>
									<b>There is no associated WP User</b>
								<?php
								}
								?>
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

	private function get_or_generate_user_pair_code($user_id)
	{
		// Define the meta key
		$meta_key = 'direktt_user_pair_code';

		// Check if the user already has the meta field set
		$pair_code = get_user_meta($user_id, $meta_key, true);

		// If the meta field is not set or is empty, generate a new 6-digit code
		if (empty($pair_code)) {

			$par_code_prefix = get_option('direktt_pairing_prefix', false);

			if (!$par_code_prefix) {
				$par_code_prefix = 'pair';
			}

			$pair_code = $par_code_prefix . str_pad(mt_rand(0, 999999), 6, '0', STR_PAD_LEFT);

			// Save the new code to the user's meta field
			update_user_meta($user_id, $meta_key, $pair_code);
		}

		// Return the existing or newly generated code
		return $pair_code;
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

			$related_users = get_users(array(
				'role__in' => array('direktt'),
				'meta_key' => 'direktt_wp_user_id',
				'meta_value' => $userId,
				'fields' => 'ID' // Return only user IDs
			));

			if (!empty($related_users)) {
				delete_user_meta($related_users[0], 'direktt_wp_user_id');
			}
		}
	}

	public function page_direktt_custom_box()
	{
		$screens = ['page'];
		foreach ($screens as $screen) {
			add_meta_box(
				'direktt_features',                 // Unique ID
				'Direktt',      // Box title
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

		$direktt_user_categories = get_post_meta($post->ID, 'direktt_user_categories', true); // array
		if (!is_array($direktt_user_categories)) $direktt_user_categories = array();

		$category_terms = get_terms(array(
			'taxonomy' => 'direkttusercategories',
			'hide_empty' => false,
		));

		$direktt_user_tags = get_post_meta($post->ID, 'direktt_user_tags', true); // array
		if (!is_array($direktt_user_tags)) $direktt_user_tags = array();

		$tag_terms = get_terms(array(
			'taxonomy' => 'direkttusertags',
			'hide_empty' => false,
		));

		$all_tags = [];
		foreach ($tag_terms as $term) {
			$all_tags[] = $term->name;
		}

		?>
		<p>
			<input id="direktt_custom_box" name="direktt_custom_box" type="checkbox" <?php echo $box_checked ?>>
			<label><?php echo __('Allow access to Direktt users', 'direktt') ?></label>
		</p>
		<p>
			<input id="direktt_custom_admin_box" name="direktt_custom_admin_box" type="checkbox" <?php echo $box_admin_checked ?>>
			<label><?php echo __('Allow access to Direktt admin', 'direktt') ?></label>
		</p>
		<p>
			<strong><?php echo __('Allow access to Direktt User Categories:', 'direktt') ?></strong>
		</p>
		<p>
			<?php
			if (empty($category_terms) || is_wp_error($category_terms)) {
				echo '<em>' . __('No Direktt User Categories Found', 'direktt') . '</em>';
				return;
			}

			foreach ($category_terms as $term) {
				printf(
					'<label><input type="checkbox" name="direktt_user_categories[]" value="%d" %s /> %s</label><br>',
					esc_attr($term->term_id),
					in_array($term->term_id, $direktt_user_categories) ? 'checked="checked"' : '',
					esc_html($term->name)
				);
			}
			?>
		</p>
		<?php
		$selected_term_names = [];
		foreach ($direktt_user_tags as $term_id) {
			$term_obj = get_term($term_id, 'direkttusertags');
			if ($term_obj && !is_wp_error($term_obj)) {
				$selected_term_names[] = $term_obj->name;
			}
		}

		?>
		<p>
			<strong><?php echo __('Allow access to Direktt User Tags:', 'direktt') ?></strong>
		</p>
		<p>
			<input
				type="text"
				id="direktt_user_tags"
				name="direktt_user_tags"
				value="<?php echo esc_attr(implode(', ', $selected_term_names)); ?>"
				style="width:100%;"
				data-available-tags='<?php echo json_encode($all_tags); ?>' />
		</p>
		<p class="description"><?php echo __('Enter tags separated by commas. Existing tags: ', 'direktt') ?><?php echo implode(', ', $all_tags); ?></p>
		<script>
			jQuery(function($) {
				var tags = <?php echo json_encode($all_tags); ?>;
				$('#direktt_user_tags').autocomplete({
					source: tags,
					minLength: 0
				}).on('focus', function() {
					$(this).autocomplete("search");
				});
			});
		</script>

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

		$term_ids = isset($_POST['direktt_user_categories']) && is_array($_POST['direktt_user_categories'])
			? array_map('intval', $_POST['direktt_user_categories']) : array();
		update_post_meta($post_id, 'direktt_user_categories', $term_ids);

		$tags_input = isset($_POST['direktt_user_tags']) ? sanitize_text_field($_POST['direktt_user_tags']) : '';
		$tag_names = array_filter(array_map('trim', explode(',', $tags_input)));

		$tag_ids = [];
		foreach ($tag_names as $tag_name) {
			$term = get_term_by('name', $tag_name, 'direkttusertags');
			if ($term && !is_wp_error($term)) {
				$tag_ids[] = $term->term_id;
			}
		}
		update_post_meta($post_id, 'direktt_user_tags', $tag_ids);

		return $post_id;
	}

	function direkttmtemplates_add_custom_box()
	{
		add_meta_box(
			'direkttMTJson_textarea',           // ID
			__('Message Template Builder', 'direktt'),                       // Title
			[$this, 'direkttmtemplates_render_textarea'],    // Callback function
			'direkttmtemplates',                    // CPT slug
			'normal',                        // Context
			'high'                           // Priority
		);
	}

	function direkttmtemplates_render_textarea($post)
	{
		$value = get_post_meta($post->ID, 'direkttMTJson', true);

		if (!$value || $value == "") {
			$value = "[]";
		}

		// Get stored value or default
		$dropdown_value = get_post_meta($post->ID, 'direkttMTType', true);

		if (!$dropdown_value) $dropdown_value = 'all';

		echo '<p><label for="direktt_mt_type"><strong>' . __('Where to display template', 'direktt') . '</strong></label> ';
		echo '<select name="direktt_mt_type" id="direktt_mt_type">';
		echo '<option value="all"' . selected($dropdown_value, 'all', false) . '>' . __('Always display this template', 'direktt') . '</option>';
		echo '<option value="bulk"' . selected($dropdown_value, 'bulk', false) . '>' . __('Display only when sending Bulk Messages', 'direktt') . '</option>';
		echo '<option value="individual"' . selected($dropdown_value, 'individual', false) . '>' . __('Display only when sending Individual Messages', 'direktt') . '</option>';
		echo '<option value="none"' . selected($dropdown_value, 'none', false) . '>' . __('Never, I will use it only via API', 'direktt') . '</option>';
		echo '</select></p>';

		// Security nonce
		wp_nonce_field('direktt_mt_json_nonce', 'direktt_mt_json_nonce');

		echo ('<div id="appBuilder"></div>');

		echo '<p><label for="direktt_mt_type"><strong>' . __('Template JSON Content', 'direktt') . '</strong></label></p> ';
		echo '<textarea style="width:100%" rows="15" name="direktt_mt_json" id="direktt_mt_json" readonly>' . esc_textarea($value) . '</textarea>';
		echo '<input type="hidden" name="direktt_mt_json_hidden" id="direktt_mt_json_hidden" value="' . esc_attr($value) . '">';

		echo '<p><label for="direktt_mt_type"><strong>' . __('Send Message Template', 'direktt') . '</strong></label></p> ';

		echo ('<div id="app"></div>');
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

	public function pair_wp_user_by_code($event)
	{

		function strip_special_chars($input)
		{
			return preg_replace('/[^a-zA-Z0-9]/', '', $input);
		}

		function find_pair_code($string, $pair_patt = 'pair')
		{
			$pattern = '/' . $pair_patt . '\d{6}/';

			if (preg_match($pattern, $string, $matches)) {
				return $matches[0];
			} else {
				return false;
			}
		}

		$par_code_prefix = get_option('direktt_pairing_prefix', false);

		if (!$par_code_prefix) {
			$par_code_prefix = 'pair';
		}

		$event_data = strtolower(strip_special_chars($event["event_data"]));
		$pair_code = find_pair_code($event_data, $par_code_prefix);

		if ($pair_code) {

			$users = get_users(array(
				'meta_key' => 'direktt_user_pair_code',
				'meta_value' => $pair_code,
				'fields' => 'ID'
			));

			if (!empty($users)) {

				$meta_user_post = Direktt_User::get_user_by_subscription_id($event['direktt_user_id']);

				$users_to_update = get_users(array(
					'meta_key' => 'direktt_user_id',
					'meta_value' => $meta_user_post['ID'],
					'fields' => 'ID'
				));

				$pairing_message_template = get_option('direktt_pairing_succ_template', false);

				foreach ($users as $user_id) {

					foreach ($users_to_update as $user_id_to_update) {
						update_user_meta($user_id_to_update, 'direktt_wp_user_id', $user_id);
						delete_user_meta($user_id_to_update, 'direktt_user_pair_code');
					}

					delete_user_meta($user_id, 'direktt_user_pair_code');

					if ($pairing_message_template) {

						Direktt_Message::send_message_template(
							array($event['direktt_user_id']),
							$pairing_message_template,
							[
								"wp_user" =>  get_user_by('id', $user_id)->user_login
							]
						);
					} else {

						$pushNotificationMessage = array(
							"type" =>  "text",
							"content" => 'You have been paired'
						);

						Direktt_Message::send_message(array($event['direktt_user_id'] => $pushNotificationMessage));
					}
				}
			}
		}
	}
}
