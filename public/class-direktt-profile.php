<?php

defined( 'ABSPATH' ) || exit;

class Direktt_Profile {

	private string $plugin_name;
	private string $version;

	public function __construct( string $plugin_name, string $version ) {
		$this->plugin_name = $plugin_name;
		$this->version     = $version;
	}

	public function setup_profile_tools() {
		do_action( 'direktt_setup_profile_tools' );
	}

	public function setup_profile_bar() {
		do_action( 'direktt_setup_profile_bar' );
	}

	public function profile_shortcode() {
		add_shortcode( 'direktt_user_profile', array( $this, 'direktt_user_profile' ) );
	}

	public function enqueue_profile_scripts() {

		wp_register_script( 'direktt-profile-script', plugins_url( 'js/direktt-profile.js', __FILE__ ), array( 'jquery' ), $this->version, true );
		wp_register_style( 'direktt-profile-style', plugins_url( 'css/direktt-profile.css', __FILE__ ), array(), $this->version );

		foreach ( Direktt::$profile_tools_array as $item ) {
			if ( isset( $item['cssEnqueueArray'] ) && is_array( $item['cssEnqueueArray'] ) && array_is_list( $item['cssEnqueueArray'] ) ) {
				foreach ( $item['cssEnqueueArray'] as $css_file ) {
					if ( array() !== $css_file && array_keys( $css_file ) !== range( 0, count( $css_file ) - 1 ) && ! wp_style_is( $css_file['handle'], 'registered' ) ) {
						wp_register_style( ...$css_file );
					}
				}
			}

			if ( isset( $item['jsEnqueueArray'] ) && is_array( $item['jsEnqueueArray'] ) && array_is_list( $item['jsEnqueueArray'] ) ) {
				foreach ( $item['jsEnqueueArray'] as $js_file ) {
					if ( array() !== $js_file && array_keys( $js_file ) !== range( 0, count( $js_file ) - 1 ) && ! wp_script_is( $js_file['handle'], 'registered' ) ) {
						wp_register_script( ...$js_file );
					}
				}
			}
		}

		foreach ( Direktt::$profile_bar_array as $item ) {
			if ( isset( $item['cssEnqueueArray'] ) && is_array( $item['cssEnqueueArray'] ) && array_is_list( $item['cssEnqueueArray'] ) ) {
				foreach ( $item['cssEnqueueArray'] as $css_file ) {
					if ( array() !== $css_file && array_keys( $css_file ) !== range( 0, count( $css_file ) - 1 ) && ! wp_style_is( $css_file['handle'], 'registered' ) ) {
						wp_register_style( ...$css_file );
					}
				}
			}

			if ( isset( $item['jsEnqueueArray'] ) && is_array( $item['jsEnqueueArray'] ) && array_is_list( $item['jsEnqueueArray'] ) ) {
				foreach ( $item['jsEnqueueArray'] as $js_file ) {
					if ( array() !== $js_file && array_keys( $js_file ) !== range( 0, count( $js_file ) - 1 ) && ! wp_script_is( $js_file['handle'], 'registered' ) ) {
						wp_register_script( ...$js_file );
					}
				}
			}
		}
	}

	public function direktt_user_profile( $atts ) {
		$atts = shortcode_atts(
			array(
				'categories' => '',
				'tags'       => '',
			),
			$atts,
			'direktt_user_profile'
		);

		$categories = array_filter( array_map( 'trim', explode( ',', $atts['categories'] ) ) );
		$tags       = array_filter( array_map( 'trim', explode( ',', $atts['tags'] ) ) );

		global $direktt_user;

		wp_enqueue_style( 'direktt-profile-style' );
		wp_enqueue_script( 'direktt-profile-script' );

		add_action(
			'wp_head',
			function () {
				?>
			<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
			<style>
				:root {
					touch-action: pan-x pan-y;
					height: 100%
				}

				html {
					touch-action: pan-x pan-y;
					height: 100%
				}
			</style>
				<?php
			},
			-1
		);

		ob_start();

		$active_tab     = isset( $_GET['subpage'] ) ? sanitize_text_field( wp_unslash( $_GET['subpage'] ) ) : ''; //phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Justification: not a form processing, subpage based router for content rendering
		$subscription_id = isset( $_GET['subscriptionId'] ) ? sanitize_text_field( wp_unslash( $_GET['subscriptionId'] ) ) : false; //phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Justification: not a form processing, subscriptionId based router for content rendering
		$profile_user   = Direktt_User::get_user_by_subscription_id( $subscription_id );
		?>
		<div id="direktt-profile-wrapper">
			<div data-subpage="profile-tab-<?php echo esc_attr( $active_tab ); ?>" id="direktt-profile">
				<div id="direktt-profile-header">
					<div id="direktt-profile-tools-toggler" class="dpi-menu"></div>
					<div class="direktt-profile-header-data">
						<?php
						if ( $profile_user && $direktt_user ) {
							echo esc_html( $profile_user['direktt_display_name'] );}
						?>
					</div>
				</div>
				<div id="direktt-profile-data" class="direktt-profile-data-<?php echo esc_html( $active_tab ? $active_tab : 'profile' ); ?>">
					<?php
					if ( '' === $active_tab ) {
						if ( $profile_user && $direktt_user ) {
							if ( ( Direktt_User::has_direktt_taxonomies( $direktt_user, $categories, $tags ) || Direktt_User::is_direktt_admin() ) || ( $direktt_user['ID'] === $profile_user['ID'] ) ) {
								?>

								<div class="direktt-profile-photo">
									<img src="<?php echo esc_attr( $profile_user['direktt_avatar_url'] ); ?>">
								</div><!-- direktt-profile-photo -->
								<div class="direktt-profile-basic-data">
									<div><?php echo esc_html__( 'Membership ID:', 'direktt' ); ?></div>
									<div><?php echo esc_html( $profile_user['direktt_membership_id'] ); ?></div>
									<div><?php echo esc_html__( 'Display Name:', 'direktt' ); ?></div>
									<div><?php echo esc_html( $profile_user['direktt_display_name'] ); ?></div>
									<div><?php echo esc_html__( 'Marketing Consent:', 'direktt' ); ?></div>
									<div><?php echo $profile_user['direktt_marketing_consent_status'] ? 'true' : 'false'; ?></div>
								</div><!-- direktt-profile-basic-data -->
								<div class="direktt-profile-meta-data">
									<div class="direktt-profile-meta-data-categories">
										<div><?php echo esc_html__( 'Direktt User Categories:', 'direktt' ); ?></div>
										<div>
											<?php
											if ( $profile_user['direktt_user_categories'] ) {
												foreach ( $profile_user['direktt_user_categories'] as $item ) {
													echo '<span class="pill">' . esc_html( htmlspecialchars( $item ) ) . '</span>';
												}
											} else {
												echo '<span class="pill empty">---</span>';
											}

											?>
										</div>
									</div><!-- direktt-profile-meta-data-categories -->
									<div class="direktt-profile-meta-data-tags">
										<div><?php echo esc_html__( 'Direktt User Tags:', 'direktt' ); ?></div>
										<div>
											<?php
											if ( $profile_user['direktt_user_tags'] ) {
												foreach ( $profile_user['direktt_user_tags'] as $item ) {
													echo '<span class="pill">' . esc_html( htmlspecialchars( $item ) ) . '</span>';
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
						foreach ( Direktt::$profile_tools_array as $item ) {

							if ( isset( $item['id'] ) && $active_tab === $item['id'] ) {
								if ( $this->direktt_user_has_term_slugs( $item, $direktt_user ) || Direktt_User::is_direktt_admin() ) {
									call_user_func( $item['callback'] );
								}

								if ( isset( $item['cssEnqueueArray'] ) && is_array( $item['cssEnqueueArray'] ) && array_is_list( $item['cssEnqueueArray'] ) ) {
									foreach ( $item['cssEnqueueArray'] as $css_file ) {
										if ( array() !== $css_file && array_keys( $css_file ) !== range( 0, count( $css_file ) - 1 ) && isset( $css_file['handle'] ) ) {
											wp_enqueue_style( $css_file['handle'] );
										}
									}
								}

								if ( isset( $item['jsEnqueueArray'] ) && is_array( $item['jsEnqueueArray'] ) && array_is_list( $item['jsEnqueueArray'] ) ) {
									foreach ( $item['jsEnqueueArray'] as $js_file ) {
										if ( array() !== $js_file && array_keys( $js_file ) !== range( 0, count( $js_file ) - 1 ) && isset( $js_file['handle'] ) ) {
											wp_enqueue_script( $js_file['handle'] );
										}
									}
								}
							}
						}

						foreach ( Direktt::$profile_bar_array as $item ) {

							if ( isset( $item['id'] ) && $active_tab === $item['id'] ) {
								if ( $this->direktt_user_has_term_slugs( $item, $direktt_user ) || Direktt_User::is_direktt_admin() ) {
									call_user_func( $item['callback'] );
								}

								if ( isset( $item['cssEnqueueArray'] ) && is_array( $item['cssEnqueueArray'] ) && array_is_list( $item['cssEnqueueArray'] ) ) {
									foreach ( $item['cssEnqueueArray'] as $css_file ) {
										if ( array() !== $css_file && array_keys( $css_file ) !== range( 0, count( $css_file ) - 1 ) && isset( $css_file['handle'] ) ) {
											wp_enqueue_style( $css_file['handle'] );
										}
									}
								}

								if ( isset( $item['jsEnqueueArray'] ) && is_array( $item['jsEnqueueArray'] ) && array_is_list( $item['jsEnqueueArray'] ) ) {
									foreach ( $item['jsEnqueueArray'] as $js_file ) {
										if ( array() !== $js_file && array_keys( $js_file ) !== range( 0, count( $js_file ) - 1 ) && isset( $js_file['handle'] ) ) {
											wp_enqueue_script( $js_file['handle'] );
										}
									}
								}
							}
						}
					}

					if ( ! isset( $_SERVER['REQUEST_URI'] ) ) {
						return;
					}

					$url   = sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) );
					$parts = wp_parse_url( $url );

					Direktt::$profile_tools_array = array_filter(
						Direktt::$profile_tools_array,
						function ( $item ) use ( $direktt_user ) {
							return ( $this->direktt_user_has_term_slugs( $item, $direktt_user ) || Direktt_User::is_direktt_admin() );
						}
					);

					// Sort links by priority asc.

					usort(
						Direktt::$profile_tools_array,
						function ( $a, $b ) {
							return $a['priority'] <=> $b['priority'];
						}
					);

					// Print out all other labels and links.
		?>
				</div><!-- direktt-profile-data -->
				<div id="direktt-profile-tools">
					<div id="direktt-profile-tools-toggler"></div>
					<ul>
						<?php
						$temp_css = '';
						foreach ( Direktt::$profile_tools_array as $item ) {
							if ( isset( $item['label'] ) ) {

								parse_str( $parts['query'] ?? '', $params );
								$params['subpage']           = $item['id'];
								$new_params                   = array();
								$new_params['subscriptionId'] = $subscription_id;
								$new_params['subpage']        = $params['subpage'];
								$new_query                    = http_build_query( $new_params );
								$new_uri                      = $parts['path'] . ( $new_query ? '?' . $new_query : '' );
								echo ( '<li data-subpage="direktt-tool-' . esc_attr( $params['subpage'] ) . '"><a href="' . esc_attr( $new_uri ) . '" class="dpi-' . esc_attr( $params['subpage'] ) . ' direktt-button">' . esc_html( $item['label'] ) . '</a></li>' );
								$temp_css .= '#direktt-profile[data-subpage="profile-tab-' . esc_attr( $params['subpage'] ) . '"] #direktt-profile-tools ul li[data-subpage="direktt-tool-' . esc_attr( $params['subpage'] ) . '"] a, ';
							}
						}
						?>
					</ul>
					<?php echo ( '<style>' . esc_html( $temp_css ) . ' .dummy { background-color: var(--direktt-profile-button-active-background-color); }</style>' ); ?>
				</div><!-- direktt-profile-tools -->
		<?php

		usort(
			Direktt::$profile_bar_array,
			function ( $a, $b ) {
				return $a['priority'] <=> $b['priority'];
			}
		);

		if ( Direktt_User::is_direktt_admin() ) {
			echo ( '<div id="direktt-profile-menu-bar"><ul>' );

			parse_str( $parts['query'] ?? '', $params );
			unset( $params['subpage'] );
			$new_params                   = array();
			$new_params['subscriptionId'] = $subscription_id;
			$new_query                    = http_build_query( $new_params );
			$new_uri                      = $parts['path'] . ( $new_query ? '?' . $new_query : '' );
			echo ( '<li data-subpage="direktt-menu-profile"><a href="' . esc_attr( $new_uri ) . '" class="dpi-profile">' . esc_html__( 'Profile', 'direktt' ) . '</a></li>' );

			foreach ( Direktt::$profile_bar_array as $item ) {
				if ( isset( $item['label'] ) ) {

					parse_str( $parts['query'] ?? '', $params );
					$new_params                   = array();
					$new_params['subscriptionId'] = $subscription_id;
					$new_params['subpage']        = $item['id'];
					$new_query                    = http_build_query( $new_params );
					$new_uri                      = $parts['path'] . ( $new_query ? '?' . $new_query : '' );
					echo ( '<li data-subpage="direktt-menu-' . esc_attr( $new_params['subpage'] ) . '"><a href="' . esc_attr( $new_uri ) . '" class="dpi-' . esc_attr( $new_params['subpage'] ) . '">' . esc_html( $item['label'] ) . '</a></li>' );
				}
			}

			echo ( '</ul></div><!-- direktt-profile-menu-bar -->' );
		}
		echo ( '</div><!-- direktt-profile -->' );
		echo ( '</div><!-- direktt-profile-wrapper -->' );

		return ob_get_clean();
	}

	private function arr_categories( $data ) {
		$has_categories = isset( $data['categories'] ) && is_array( $data['categories'] ) && ! empty( $data['categories'] );
		if ( $has_categories ) {
			return $data['categories'];
		} else {
			return array();
		}
	}

	private function arr_tags( $data ) {
		$has_tags = isset( $data['tags'] ) && is_array( $data['tags'] ) && ! empty( $data['tags'] );
		if ( $has_tags ) {
			return $data['tags'];
		} else {
			return array();
		}
	}

	public function direktt_user_has_term_slugs( $item, $direktt_user ) {
		if ( empty( $direktt_user ) || ! isset( $direktt_user['ID'] ) ) {
			return false;
		}
		$categories = $this->arr_categories( $item );
		$tags       = $this->arr_tags( $item );

		// Get assigned category and tag slugs.
		$assigned_categories = wp_get_post_terms( $direktt_user['ID'], 'direkttusercategories', array( 'fields' => 'slugs' ) );
		$assigned_tags       = wp_get_post_terms( $direktt_user['ID'], 'direkttusertags', array( 'fields' => 'slugs' ) );

		// If any input category matches assigned categories.
		if ( ! empty( $categories ) && ! empty( $assigned_categories ) ) {
			if ( array_intersect( $categories, $assigned_categories ) ) {
				return true;
			}
		}

		// If any input tag matches assigned tags.
		if ( ! empty( $tags ) && ! empty( $assigned_tags ) ) {
			if ( array_intersect( $tags, $assigned_tags ) ) {
				return true;
			}
		}

		return false;
	}

	public static function add_profile_tool( $params ) {
		Direktt::$profile_tools_array[] = $params;
	}

	public static function add_profile_bar( $params ) {
		Direktt::$profile_bar_array[] = $params;
	}
}
