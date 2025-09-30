<?php

class Direktt_Profile
{
	private string $plugin_name;
	private string $version;

	public function __construct(string $plugin_name, string $version)
	{
		$this->plugin_name = $plugin_name;
		$this->version     = $version;
	}

	public function setup_profile_tools()
	{
		do_action('direktt_setup_profile_tools');
	}

	public function setup_profile_bar()
	{
		do_action('direktt_setup_profile_bar');
	}

	public function profile_shortcode()
	{
		add_shortcode('direktt_user_profile', [$this, 'direktt_user_profile']);
	}

	public function enqueue_profile_scripts()
	{

		wp_register_script('direktt-profile-script', plugins_url('js/direktt-profile.js', __FILE__), array('jquery'));
		wp_register_style('direktt-profile-style', plugins_url('css/direktt-profile.css', __FILE__), array());

		// Ovde treba registrovati stilove i css profila

		foreach (Direktt::$profile_tools_array as $item) {
			if (isset($item['cssEnqueueArray']) && is_array($item['cssEnqueueArray']) && array_is_list($item['cssEnqueueArray'])) {
				foreach ($item['cssEnqueueArray'] as $cssFile) {
					if ($cssFile !== [] && array_keys($cssFile) !== range(0, count($cssFile) - 1) && ! wp_style_is($cssFile['handle'], 'registered')) {
						wp_register_style(...$cssFile);
					}
				}
			}

			if (isset($item['jsEnqueueArray']) && is_array($item['jsEnqueueArray']) && array_is_list($item['jsEnqueueArray'])) {
				foreach ($item['jsEnqueueArray'] as $jsFile) {
					if ($jsFile !== [] && array_keys($jsFile) !== range(0, count($jsFile) - 1) && ! wp_script_is($jsFile['handle'], 'registered')) {
						wp_register_script(...$jsFile);
					}
				}
			}
		}

		foreach (Direktt::$profile_bar_array as $item) {
			if (isset($item['cssEnqueueArray']) && is_array($item['cssEnqueueArray']) && array_is_list($item['cssEnqueueArray'])) {
				foreach ($item['cssEnqueueArray'] as $cssFile) {
					if ($cssFile !== [] && array_keys($cssFile) !== range(0, count($cssFile) - 1) && ! wp_style_is($cssFile['handle'], 'registered')) {
						wp_register_style(...$cssFile);
					}
				}
			}

			if (isset($item['jsEnqueueArray']) && is_array($item['jsEnqueueArray']) && array_is_list($item['jsEnqueueArray'])) {
				foreach ($item['jsEnqueueArray'] as $jsFile) {
					if ($jsFile !== [] && array_keys($jsFile) !== range(0, count($jsFile) - 1) && ! wp_script_is($jsFile['handle'], 'registered')) {
						wp_register_script(...$jsFile);
					}
				}
			}
		}
	}

	public function direktt_user_profile($atts)
	{
		$atts = shortcode_atts(
			array(
				'categories' => '',
				'tags' => ''
			),
			$atts,
			'direktt_user_profile'
		);

		$categories = array_filter(array_map('trim', explode(',', $atts['categories'])));
		$tags = array_filter(array_map('trim', explode(',', $atts['tags'])));

		global $direktt_user;

		wp_enqueue_style('direktt-profile-style');
		wp_enqueue_script('direktt-profile-script');

		ob_start();

		$active_tab = isset($_GET['subpage']) ? $_GET['subpage'] : '';
		$subscriptionId   = isset($_GET['subscriptionId']) ? sanitize_text_field(wp_unslash($_GET['subscriptionId'])) : false;
		$profile_user = Direktt_User::get_user_by_subscription_id($subscriptionId);
?>
		<div id="direktt-profile-wrapper">
			<div data-subpage="profile-tab-<?= $active_tab ?>" id="direktt-profile">
				<div id="direktt-profile-header">
					<div id="direktt-profile-tools-toggler" class="dpi-menu"></div>
					<div class="direktt-profile-header-data">
						<?php if ( $profile_user && $direktt_user ) echo( $profile_user['direktt_display_name'] ); ?>
					</div>
				</div>
				<div id="direktt-profile-data" class="direktt-profile-data-<?php echo ($active_tab ? $active_tab : 'profile') ?>">
					<?php
					if ($active_tab == '') {
						if ($profile_user && $direktt_user) {
							if ((Direktt_User::has_direktt_taxonomies($direktt_user, $categories, $tags) || Direktt_User::is_direktt_admin()) || ($direktt_user['ID'] == $profile_user['ID'])) {
					?>

								<div class="direktt-profile-photo">
									<img src="<?php echo esc_attr($profile_user['direktt_avatar_url']); ?>">
								</div><!-- direktt-profile-photo -->
								<div class="direktt-profile-basic-data">
									<div>Membership ID:</div>
									<div><?php echo esc_html($profile_user['direktt_membership_id']); ?></div>
									<div>Display Name:</div>
									<div><?php echo esc_html($profile_user['direktt_display_name']); ?></div>
									<div>Marketing Consent:</div>
									<div><?php echo $profile_user['direktt_marketing_consent_status'] ? 'true' : 'false' ?></div>
								</div><!-- direktt-profile-basic-data -->
								<div class="direktt-profile-meta-data">
									<div class="direktt-profile-meta-data-categories">
										<div>Direktt User Categories:</div>
										<div>
											<?php
											if ($profile_user['direktt_user_categories']) {
												foreach ($profile_user['direktt_user_categories'] as $item) {
													echo '<span class="pill">' . htmlspecialchars($item) . '</span>';
												}
											} else {
												echo '<span class="pill empty">---</span>';
											}

											?>
										</div>
									</div><!-- direktt-profile-meta-data-categories -->
									<div class="direktt-profile-meta-data-tags">
										<div>Direktt User Tags:</div>
										<div>
											<?php
											if ($profile_user['direktt_user_tags']) {
												foreach ($profile_user['direktt_user_tags'] as $item) {
													echo '<span class="pill">' . htmlspecialchars($item) . '</span>';
												}
											} else {
												echo '<span class="pill empty">---</span>';
											}
											?>
										</div>
									</div><!-- direktt-profile-meta-data-tags -->
								</div><!-- direktt-profile-meta-data -->

					<?php
							}
						}
					} else {
						foreach (Direktt::$profile_tools_array as $item) {

							if (isset($item['id']) && $active_tab == $item['id']) {
								if ($this->direktt_user_has_term_slugs($item, $direktt_user) || Direktt_User::is_direktt_admin()) {
									call_user_func($item['callback']);
								}

								if (isset($item['cssEnqueueArray']) && is_array($item['cssEnqueueArray']) && array_is_list($item['cssEnqueueArray'])) {
									foreach ($item['cssEnqueueArray'] as $cssFile) {
										if ($cssFile !== [] && array_keys($cssFile) !== range(0, count($cssFile) - 1) && isset($cssFile['handle'])) {
											wp_enqueue_style($cssFile['handle']);
										}
									}
								}

								if (isset($item['jsEnqueueArray']) && is_array($item['jsEnqueueArray']) && array_is_list($item['jsEnqueueArray'])) {
									foreach ($item['jsEnqueueArray'] as $jsFile) {
										if ($jsFile !== [] && array_keys($jsFile) !== range(0, count($jsFile) - 1) && isset($jsFile['handle'])) {
											wp_enqueue_script($jsFile['handle']);
										}
									}
								}
							}
						}

						foreach (Direktt::$profile_bar_array as $item) {

							if (isset($item['id']) && $active_tab == $item['id']) {
								if ($this->direktt_user_has_term_slugs($item, $direktt_user) || Direktt_User::is_direktt_admin()) {
									call_user_func($item['callback']);
								}

								if (isset($item['cssEnqueueArray']) && is_array($item['cssEnqueueArray']) && array_is_list($item['cssEnqueueArray'])) {
									foreach ($item['cssEnqueueArray'] as $cssFile) {
										if ($cssFile !== [] && array_keys($cssFile) !== range(0, count($cssFile) - 1) && isset($cssFile['handle'])) {
											wp_enqueue_style($cssFile['handle']);
										}
									}
								}

								if (isset($item['jsEnqueueArray']) && is_array($item['jsEnqueueArray']) && array_is_list($item['jsEnqueueArray'])) {
									foreach ($item['jsEnqueueArray'] as $jsFile) {
										if ($jsFile !== [] && array_keys($jsFile) !== range(0, count($jsFile) - 1) && isset($jsFile['handle'])) {
											wp_enqueue_script($jsFile['handle']);
										}
									}
								}
							}
						}
					}

					$url = $_SERVER['REQUEST_URI'];
					$parts = parse_url($url);

					Direktt::$profile_tools_array = array_filter(Direktt::$profile_tools_array, function ($item) use ($direktt_user) {
						return ($this->direktt_user_has_term_slugs($item, $direktt_user) || Direktt_User::is_direktt_admin());
					});

					// Sort links by priority asc

					usort(Direktt::$profile_tools_array, function ($a, $b) {
						return $a['priority'] <=> $b['priority'];
					});

					// Print out all other labels and links
					?>
				</div><!-- direktt-profile-data -->
				<div id="direktt-profile-tools">
					<div id="direktt-profile-tools-toggler"></div>
					<ul>
						<?php
						$temp_css = "";
						foreach (Direktt::$profile_tools_array as $item) {
							if (isset($item['label'])) {

								parse_str($parts['query'] ?? '', $params);
								$params['subpage'] = $item['id'];
								$newParams = array();
								$newParams['subscriptionId'] = $subscriptionId;
								$newParams['subpage'] = $params['subpage'];
								$newQuery = http_build_query($newParams);
								$newUri = $parts['path'] . ($newQuery ? '?' . $newQuery : '');
								echo ('<li data-subpage="direktt-tool-' . $params['subpage'] . '"><a href="' . $newUri . '" class="dpi-' . $params['subpage'] . ' direktt-button">' . $item['label'] . '</a></li>');
								$temp_css .= '#direktt-profile[data-subpage="profile-tab-' . $params['subpage'] . '"] #direktt-profile-tools ul li[data-subpage="direktt-tool-' . $params['subpage'] . '"] a, ';
							}
						}
						?>
					</ul>
					<?php echo(  '<style>' . $temp_css . ' .dummy { background-color: var(--direktt-profile-button-active-background-color); }</style>' ); ?>
				</div><!-- direktt-profile-tools -->
		<?php

		// Ispisujemo defaultni meni:

		usort(Direktt::$profile_bar_array, function ($a, $b) {
			return $a['priority'] <=> $b['priority'];
		});

		if (Direktt_User::is_direktt_admin()) {
			echo ('<div id="direktt-profile-menu-bar"><ul>');

			parse_str($parts['query'] ?? '', $params);
			unset($params['subpage']);
			$newQuery = http_build_query($params);
			$newUri = $parts['path'] . ($newQuery ? '?' . $newQuery : '');
			echo ('<li data-subpage="direktt-menu-profile"><a href="' . $newUri . '" class="dpi-profile">' . __('Profile', 'direktt') . '</a></li>');

			foreach (Direktt::$profile_bar_array as $item) {
				if (isset($item['label'])) {

					parse_str($parts['query'] ?? '', $params);
					$params['subpage'] = $item['id'];
					$newQuery = http_build_query($params);
					$newUri = $parts['path'] . ($newQuery ? '?' . $newQuery : '');
					echo ('<li data-subpage="direktt-menu-' . $params['subpage'] . '"><a href="' . $newUri . '" class="dpi-' . $params['subpage'] . '">' . $item['label'] . '</a></li>');
				}
			}

			echo ('</ul></div><!-- direktt-profile-menu-bar -->');
		}
		echo ('</div><!-- direktt-profile -->');
		echo ('</div><!-- direktt-profile-wrapper -->');

		return ob_get_clean();
	}

	private function arrCategories($data)
	{
		$hasCategories = isset($data['categories']) && is_array($data['categories']) && !empty($data['categories']);
		if ($hasCategories) {
			return $data['categories'];
		} else {
			return array();
		}
	}

	private function arrTags($data)
	{
		$hasTags = isset($data['tags']) && is_array($data['tags']) && !empty($data['tags']);
		if ($hasTags) {
			return $data['tags'];
		} else {
			return array();
		}
	}

	function direktt_user_has_term_slugs($item, $direktt_user)
	{
		if (empty($direktt_user) || ! isset($direktt_user['ID'])) {
			return false;
		}
		$categories = $this->arrCategories($item);
		$tags = $this->arrTags($item);

		// Get assigned category and tag slugs
		$assigned_categories = wp_get_post_terms($direktt_user['ID'], 'direkttusercategories', array('fields' => 'slugs'));
		$assigned_tags       = wp_get_post_terms($direktt_user['ID'], 'direkttusertags', array('fields' => 'slugs'));

		// If any input category matches assigned categories
		if (! empty($categories) && ! empty($assigned_categories)) {
			if (array_intersect($categories, $assigned_categories)) {
				return true;
			}
		}

		// If any input tag matches assigned tags
		if (! empty($tags) && ! empty($assigned_tags)) {
			if (array_intersect($tags, $assigned_tags)) {
				return true;
			}
		}

		return false;
	}

	static function add_profile_tool($params)
	{
		Direktt::$profile_tools_array[] = $params;
	}

	static function add_profile_bar($params)
	{
		Direktt::$profile_bar_array[] = $params;
	}
}
