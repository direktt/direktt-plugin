<?php

class Direktt_Taxonomies_Service
{
	private string $plugin_name;
	private string $version;

	public function __construct(string $plugin_name, string $version)
	{
		$this->plugin_name = $plugin_name;
		$this->version     = $version;
	}

	public function direktt_taxonomies_service_add_shortcode()
	{
		add_shortcode('direktt_edit_taxonomies_service', [$this, 'direktt_taxonomies_service_shortcode']);
	}
	public function direktt_register_taxonomies_service_scripts()
	{
		wp_register_script("direktt-taxonomies-service-autocomplete-script", plugins_url('../js/autoComplete.min.js', __FILE__), array(), "10.2.9", true);
		wp_register_script("direktt-taxonomies-service-script", plugins_url('../js/direktt-service-taxonomies.js', __FILE__), array("direktt-taxonomies-service-autocomplete-script", "jquery"), $this->version, true);
		wp_register_style("direktt-taxonomies-service-autocomplete-style", plugins_url('../css/autoComplete.01.css', __FILE__), array(), $this->version);
	}

	public function direktt_taxonomies_service_shortcode()
	{
		if (! Direktt_User::is_direktt_admin()) {
			return;
		}

		$subpage = isset($_GET['subpage']) ? sanitize_text_field(wp_unslash($_GET['subpage'])) : '';

		if ($subpage) {
			$args = array(
				'post_type' => 'direkttusers',
				'posts_per_page' => -1,
			);

			$query = new WP_Query($args);

			$user_map = array();

			if ($query->have_posts()) {
				while ($query->have_posts()) {
					$query->the_post();
					$user_map[get_the_title()] = get_the_ID();
				}
				wp_reset_postdata();
			}

			if (isset($_POST['save_user_categories'])) {
				if (
					! isset($_POST['save_user_categories_nonce'])
					|| ! wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['save_user_categories_nonce'])), 'save_user_categories_nonce')
				) {
					wp_send_json(['status' => 'nonce_failed']);
				}

				$id_to_add = isset($_POST['id_to_add_category']) ? intval(wp_unslash($_POST['id_to_add_category'])) : 0;
				$category = isset($_POST['category']) ? sanitize_text_field(wp_unslash($_POST['category'])) : '';

				if (! $id_to_add) {
					wp_send_json(['status' => 'no_user']);
				} else {
					wp_add_object_terms($id_to_add, $category, 'direkttusercategories');
				}

				$redirect_url = add_query_arg('status_flag', '1');
				wp_safe_redirect(esc_url_raw($redirect_url));
				exit;
			}

			if (isset($_POST['save_user_tags'])) {
				if (
					! isset($_POST['save_user_tags_nonce'])
					|| ! wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['save_user_tags_nonce'])), 'save_user_tags_nonce')
				) {
					wp_send_json(['status' => 'nonce_failed']);
				}

				$id_to_add = isset($_POST['id_to_add_tag']) ? intval(wp_unslash($_POST['id_to_add_tag'])) : 0;
				$tag = isset($_POST['tag']) ? sanitize_text_field(wp_unslash($_POST['tag'])) : '';

				if (! $id_to_add) {
					wp_send_json(['status' => 'no_user']);
				} else {
					wp_add_object_terms($id_to_add, $tag, 'direkttusertags');
				}

				$redirect_url = add_query_arg('status_flag', '1');
				wp_safe_redirect(esc_url_raw($redirect_url));
				exit;
			}

			if (isset($_POST['remove_user_categories'])) {
				if (
					! isset($_POST['save_user_categories_nonce'])
					|| ! wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['save_user_categories_nonce'])), 'save_user_categories_nonce')
				) {
					wp_send_json(['status' => 'nonce_failed']);
				}
				$id_to_remove = isset($_POST['id_to_remove_category']) ? intval(wp_unslash($_POST['id_to_remove_category'])) : 0;
				$category = isset($_POST['category']) ? sanitize_text_field(wp_unslash($_POST['category'])) : '';

				if (! $id_to_remove) {
					wp_send_json(['status' => 'no_user']);
				} else {
					wp_remove_object_terms($id_to_remove, $category, 'direkttusercategories');
				}

				$redirect_url = add_query_arg('status_flag', '1');
				wp_safe_redirect(esc_url_raw($redirect_url));
				exit;
			}

			if (isset($_POST['remove_user_tags'])) {
				if (
					! isset($_POST['save_user_tags_nonce'])
					|| ! wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['save_user_tags_nonce'])), 'save_user_tags_nonce')
				) {
					wp_send_json(['status' => 'nonce_failed']);
				}
				$id_to_remove = isset($_POST['id_to_remove_tag']) ? intval(wp_unslash($_POST['id_to_remove_tag'])) : 0;
				$tag = isset($_POST['tag']) ? sanitize_text_field(wp_unslash($_POST['tag'])) : '';

				if (! $id_to_remove) {
					wp_send_json(['status' => 'no_user']);
				} else {
					wp_remove_object_terms($id_to_remove, $tag, 'direkttusertags');
				}

				$redirect_url = add_query_arg('status_flag', '1');
				wp_safe_redirect(esc_url_raw($redirect_url));
				exit;
			}

			$status_flag    = isset($_GET['status_flag']) ? intval($_GET['status_flag']) : 0;
			$status_message = '';
			if ($status_flag === 1) {
				$status_message = esc_html__('Saved successfully.', 'direktt');
			}

			$backUri = '';

			if (isset($_SERVER['REQUEST_URI'])) {

				$request_uri = sanitize_text_field(wp_unslash($_SERVER['REQUEST_URI']));
				$path = wp_parse_url($request_uri, PHP_URL_PATH);
				$backUri = is_string( $path ) ? $path : '';
			}

			wp_enqueue_script('direktt-taxonomies-service-autocomplete-script');
			wp_enqueue_script('direktt-taxonomies-service-script');
			wp_enqueue_style('direktt-taxonomies-service-autocomplete-style');

			ob_start();

?>
			<div id="direktt-profile-wrapper">
				<div class="direktt-edit-taxonomies-service-wrapper direktt-edit-taxonomies-service-editor" id="direktt-profile">
					<?php

					$allowed_html = wp_kses_allowed_html('post');
					echo wp_kses(Direktt_Public::direktt_render_confirm_popup('edit-taxonomies-service-confirm', esc_html__("Are you sure that you want to remove the user?", 'direktt')), $allowed_html );
					echo wp_kses(Direktt_Public::direktt_render_loader(__('Saving data', 'direktt')), $allowed_html );

					if (('edit-category' === $subpage || 'edit-tag' === $subpage) && isset($_GET['tax_name'])) {
						$tax_name = sanitize_text_field(wp_unslash($_GET['tax_name']));
						$taxonomy = 'edit-category' === $subpage ? 'direkttusercategories' : 'direkttusertags';
						$term = get_term_by('name', $tax_name, $taxonomy);
					?>
						<?php if( !empty( $status_message ) ): ?>
							<p class="direktt-edit-taxonomies-service-status"><?php echo esc_html($status_message); ?></p>
						<?php endif; ?>
						<h2><?php echo 'edit-category' === $subpage ? esc_html__('Category Name:', 'direktt') : esc_html__('Tag Name:', 'direktt'); ?> <?php echo esc_html($tax_name); ?></h2>
						<div class="direktt-edit-taxonomies-service-users">
							<form method="post" action="">
								<div class="direktt-edit-taxonomies-service-users-search">
									<input id="autoComplete" aria-autocomplete="none" autocomplete="off">
									<input type="hidden" id="userID" name="userID">
									<!-- <input type="submit" id="add-user" name="<?php /* echo esc_attr( 'edit-category' === $subpage ? 'save_user_categories' : 'save_user_tags' ); */ ?>" value="<?php /* echo esc_attr__( 'Add User', 'direktt' ); */ ?>" /> -->
									<input type="hidden" name="<?php echo esc_attr('edit-category' === $subpage ? 'category' : 'tag'); ?>" value="<?php echo esc_attr($tax_name); ?>">
									<input type="hidden" name="<?php echo esc_attr('edit-category' === $subpage ? 'save_user_categories_nonce' : 'save_user_tags_nonce'); ?>" value="<?php echo 'edit-category' === $subpage ? esc_attr(wp_create_nonce('save_user_categories_nonce')) : esc_attr(wp_create_nonce('save_user_tags_nonce')); ?>">
									<input type="hidden" id="usersNonce" name="usersNonce" value="<?php echo esc_attr(wp_create_nonce('user_list_nonce')); ?>">
									<div class="direktt-edit-taxonomies-service-submit">
										<p>
											<input type="submit" id="addUserBtn" value="<?php echo esc_html__('Add User', 'direktt'); ?>" class="button button-primary">
										</p>
									</div>
								</div>
								<h3><?php echo esc_html__('Users:', 'direktt'); ?></h3>
								<?php

								if ($term) {
									$user_ids = get_objects_in_term($term->term_id, $taxonomy);
									$user_ids = array_values(array_filter(array_map('absint', $user_ids)));
								?>
									<div class="direktt-edit-taxonomies-service-users-list">

										<?php
										if (! empty($user_ids)) {
											foreach ($user_ids as $user_id) {
										?>
												<div class="direktt-edit-taxonomies-service-user-item">
													<p><?php echo esc_html(get_the_title($user_id)); ?></p>
													<input type="hidden" name="<?php echo esc_attr('edit-category' === $subpage ? 'user_id_category' : 'user_id_tag'); ?>" value="<?php echo esc_attr($user_id); ?>">
													<button type="button" class="direktt-button button-invert remove-user-btn" data-id="<?php echo esc_attr($user_id); ?>">
														<?php echo esc_html__('Remove', 'direktt'); ?>
													</button>
												</div>
											<?php
											}
										} else {
											?>
											<p><?php echo 'edit-category' === $subpage ? esc_html__('No users found for this category.', 'direktt') : esc_html__('No users found for this tag.', 'direktt'); ?></p>
										<?php
										}
										?>
									</div>

									<script>
										usersInList = <?php echo json_encode($user_ids); ?>;
										actionInputName = '<?php echo esc_attr('edit-category' === $subpage ? 'save_user_categories' : 'save_user_tags'); ?>';
										actionInputDeleteName = '<?php echo esc_attr('edit-category' === $subpage ? 'remove_user_categories' : 'remove_user_tags'); ?>';
										idToAddName = '<?php echo esc_attr('edit-category' === $subpage ? 'id_to_add_category' : 'id_to_add_tag'); ?>';
										idToRemoveName = '<?php echo esc_attr('edit-category' === $subpage ? 'id_to_remove_category' : 'id_to_remove_tag'); ?>';
										idToRemoveId = ''
										form = null
									</script>
								<?php
								} else {
								?>
									<p><?php echo 'edit-category' === $subpage ? esc_html__('No users found for this category.', 'direktt') : esc_html__('No users found for this tag.', 'direktt'); ?></p>
								<?php
								}
								?>
							</form>
						</div>
						<p><a href="<?php echo esc_url($backUri); ?>" class="direktt-button button-invert button-dark-gray"><?php echo esc_html__('Show All Taxonomies', 'direktt'); ?></a></p>
				</div>
			</div>
		<?php
					}
				} else {
					$all_categories = Direktt_User::get_all_user_categories();
					$all_tags       = Direktt_User::get_all_user_tags();
		?>
		<div id="direktt-profile-wrapper">
			<div class="direktt-edit-taxonomies-service-wrapper" id="direktt-profile">
				<h2><?php echo esc_html__('Categories', 'direktt'); ?></h2>
				<div class="direktt-edit-taxonomies-service-categories">
					<?php
					foreach ($all_categories as $category) {
						$url = sanitize_text_field( wp_unslash($_SERVER['REQUEST_URI']));
						$parts = wp_parse_url($url);
						parse_str($parts['query'] ?? '', $params);
						$params['subpage'] = 'edit-category';
						$params['tax_name'] = $category['name'];
						$newQuery = http_build_query($params);
						$newUri = $parts['path'] . ($newQuery ? '?' . $newQuery : '');

						$term_obj = get_term_by('name', $category['name'], 'direkttusercategories');
						$count = 0;
						if ($term_obj) {
							$user_ids = get_objects_in_term($term_obj->term_id, 'direkttusercategories');
							$user_ids = array_values(array_filter(array_map('absint', $user_ids)));
							$count = count($user_ids);
						}
					?>
						<p><a href="<?php echo esc_url($newUri); ?>" class="direktt-button"><?php echo esc_html($category['name']); ?><?php echo ' <i>(' . esc_html($count) . ')</i>'; ?></a></p>
					<?php
					}
					?>
				</div>
				<h2><?php echo esc_html__('Tags', 'direktt'); ?></h2>
				<div class="direktt-edit-taxonomies-service-tags">
					<?php
					foreach ($all_tags as $tag) {
						$url = sanitize_text_field( wp_unslash($_SERVER['REQUEST_URI']));
						$parts = wp_parse_url($url);
						parse_str($parts['query'] ?? '', $params);
						$params['subpage'] = 'edit-tag';
						$params['tax_name'] = $tag['name'];
						$newQuery = http_build_query($params);
						$newUri = $parts['path'] . ($newQuery ? '?' . $newQuery : '');

						$term_obj = get_term_by('name', $tag['name'], 'direkttusertags');
						$count = 0;
						if ($term_obj) {
							$user_ids = get_objects_in_term($term_obj->term_id, 'direkttusertags');
							$user_ids = array_values(array_filter(array_map('absint', $user_ids)));
							$count = count($user_ids);
						}
					?>
						<p><a href="<?php echo esc_url($newUri); ?>" class="direktt-button"><?php echo esc_html($tag['name']); ?><?php echo ' <i>(' . esc_html($count) . ')</i>'; ?></a></p>
					<?php
					}
					?>
				</div>
			</div>
		</div>
<?php
				}

				return ob_get_clean();
			}
		}
