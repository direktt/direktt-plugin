<?php

class Direktt_Taxonomies_Service {
    public function direktt_taxonomies_service_add_shortcode() {
        add_shortcode( 'direktt_edit_taxonomies_service', [ $this, 'direktt_taxonomies_service_shortcode' ] );
    }
    static function direktt_register_taxonomies_service_scripts() {
        wp_register_style( 'jquery-ui-css', 'https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css' );
    }
    
    static function direktt_taxonomies_service_shortcode() {
        if ( ! Direktt_User::is_direktt_admin() ) {
            return;
        }
    
        $subpage = isset( $_GET['subpage'] ) ? sanitize_text_field( wp_unslash( $_GET['subpage'] ) ) : '';
    
        ob_start();
    
        if ( $subpage ) {
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
    
            if ( isset( $_POST['save_user_categories'] ) ) {
                if ( ! isset( $_POST['save_user_categories_nonce'] )
                || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['save_user_categories_nonce'] ) ), 'save_user_categories_nonce' )
                ) {
                    wp_send_json( ['status' => 'nonce_failed'] );
                }
    
                $id_to_add = isset( $_POST['id_to_add_category'] ) ? intval( wp_unslash( $_POST['id_to_add_category'] ) ) : 0;
                $category = isset( $_POST['category'] ) ? sanitize_text_field( wp_unslash( $_POST['category'] ) ) : '';
    
                if ( ! $id_to_add ) {
                    wp_send_json( ['status' => 'no_user'] );
                } else {
                    wp_add_object_terms( $id_to_add, $category, 'direkttusercategories' );
                }
    
                $redirect_url = add_query_arg( 'status_flag', '1', $_SERVER['REQUEST_URI'] );
                wp_safe_redirect( esc_url_raw( $redirect_url ) );
                exit;
            }
    
            if ( isset( $_POST['save_user_tags'] ) ) {
                if ( ! isset( $_POST['save_user_tags_nonce'] )
                || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['save_user_tags_nonce'] ) ), 'save_user_tags_nonce' )
                ) {
                    wp_send_json( ['status' => 'nonce_failed'] );
                }
    
                $id_to_add = isset( $_POST['id_to_add_tag'] ) ? intval( wp_unslash( $_POST['id_to_add_tag'] ) ) : 0;
                $tag = isset( $_POST['tag'] ) ? sanitize_text_field( wp_unslash( $_POST['tag'] ) ) : '';
    
                if ( ! $id_to_add ) {
                    wp_send_json( ['status' => 'no_user'] );
                } else {
                    wp_add_object_terms( $id_to_add, $tag, 'direkttusertags' );
                }
    
                $redirect_url = add_query_arg( 'status_flag', '1', $_SERVER['REQUEST_URI'] );
                wp_safe_redirect( esc_url_raw( $redirect_url ) );
                exit;
            }
    
            if ( isset( $_POST['remove_user_categories'] ) ) {
                if ( ! isset( $_POST['save_user_categories_nonce'] ) 
                || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['save_user_categories_nonce'] ) ), 'save_user_categories_nonce' )
                ) {
                    wp_send_json( ['status' => 'nonce_failed'] );
                }
                $id_to_remove = isset( $_POST['id_to_remove_category'] ) ? intval( wp_unslash( $_POST['id_to_remove_category'] ) ) : 0;
                $category = isset( $_POST['category'] ) ? sanitize_text_field( wp_unslash( $_POST['category'] ) ) : '';
    
                if ( ! $id_to_remove ) {
                    wp_send_json( ['status' => 'no_user'] );
                } else {
                    wp_remove_object_terms( $id_to_remove, $category, 'direkttusercategories' );
                }
    
                $redirect_url = add_query_arg( 'status_flag', '1', $_SERVER['REQUEST_URI'] );
                wp_safe_redirect( esc_url_raw( $redirect_url ) );
                exit;
            }
    
            if ( isset( $_POST['remove_user_tags'] ) ) {
                if ( ! isset( $_POST['save_user_tags_nonce'] ) 
                || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['save_user_tags_nonce'] ) ), 'save_user_tags_nonce' )
                ) {
                    wp_send_json( ['status' => 'nonce_failed'] );
                }
                $id_to_remove = isset( $_POST['id_to_remove_tag'] ) ? intval( wp_unslash( $_POST['id_to_remove_tag'] ) ) : 0;
                $tag = isset( $_POST['tag'] ) ? sanitize_text_field( wp_unslash( $_POST['tag'] ) ) : '';
    
                if ( ! $id_to_remove ) {
                    wp_send_json( ['status' => 'no_user'] );
                } else {
                    wp_remove_object_terms( $id_to_remove, $tag, 'direkttusertags' );
                }
    
                $redirect_url = add_query_arg( 'status_flag', '1', $_SERVER['REQUEST_URI'] );
                wp_safe_redirect( esc_url_raw( $redirect_url ) );
                exit;
            }
    
            $status_flag    = isset( $_GET['status_flag'] ) ? intval( $_GET['status_flag'] ) : 0;
            $status_message = '';
            if ( $status_flag === 1 ) {
                $status_message = esc_html__( 'Saved successfully.', 'direktt' );
            }
    
            $uriParts = parse_url( $_SERVER['REQUEST_URI'] );
            $backUri = isset( $uriParts['path'] ) ? $uriParts['path'] : '';

            ?>
				<div id="direktt-profile-wrapper">
					<div class="direktt-edit-taxonomies-service-wrapper direktt-edit-taxonomies-service-editor" id="direktt-profile">
					<?php
					if ( 'edit-category' === $subpage || 'edit-tag' === $subpage ) {
						$taxonomy = 'edit-category' === $subpage ? 'direkttusercategories' : 'direkttusertags';
						$term = get_term_by( 'name', sanitize_text_field( $_GET['tax_name'] ), $taxonomy );
			
						?>
						<p class="direktt-edit-taxonomies-service-status"><?php echo $status_message; ?></p>
						<h2><?php echo 'edit-category' === $subpage ? esc_html__( 'Category Name:', 'direktt' ) : esc_html__( 'Tag Name:', 'direktt' ); ?> <?php echo esc_html( $_GET['tax_name'] ); ?></h2>
						<h3><?php echo esc_html__( 'Users:', 'direktt' ); ?></h3>
						<div class="direktt-edit-taxonomies-service-users">
							<form method="post" action="">
								<?php
								if ( $term ) {
									$user_ids = get_objects_in_term( $term->term_id, $taxonomy );
									$user_ids = array_values( array_filter( array_map( 'absint', $user_ids ) ) );
			
									$tax_name = sanitize_text_field( $_GET['tax_name'] );
									wp_enqueue_script( 'jquery-ui-autocomplete' );
									wp_enqueue_style( 'jquery-ui-css' );
									?>
									<style>
										/* Popup */
										.direktt-edit-taxonomies-popup {
											position: fixed;
											top: 0;
											left: 0;
											width: 100%;
											height: 100%;
											background: rgba(0, 0, 0, 0.7);
											display: none;
											z-index: 9998;
										}
										.direktt-edit-taxonomies-popup .direktt-edit-taxonomies-popup-content {
											position: absolute;
											top: 50%;
											left: 50%;
											transform: translate(-50%, -50%);
											background: white;
											padding: 20px;
											border-radius: 10px;
											z-index: 10000;
											display: flex;
											flex-direction: column;
										}
			
										/* Loader */
										#direktt-loader-overlay {
											position: fixed;
											top: 0;
											left: 0;
											width: 100%;
											height: 100%;
											background: rgba(0, 0, 0, 0.7);
											display: none;
											z-index: 9999;
										}
										#direktt-loader-overlay #direktt-loader-container {
											position: absolute;
											top: 50%;
											left: 50%;
											transform: translate(-50%, -50%);
											text-align: center;
											z-index: 10000;
										}
										#direktt-loader-overlay #direktt-loader-container #direktt-loader {
											border: 8px solid #f3f3f3;
											border-top: 8px solid #3498db;
											border-radius: 50%;
											width: 50px;
											height: 50px;
											animation: spin 2s linear infinite;
											display: inline-block;
										}
										#direktt-loader-overlay #direktt-loader-container #direktt-loader-text {
											margin-top: 20px;
											color: white;
											font-size: 16px;
										}
			
										@keyframes spin {
											0% {
												transform: rotate(0deg);
											}
											100% {
												transform: rotate(360deg);
											}
										}
									</style>
									<div class="direktt-edit-taxonomies-service-users-list">
										<script>
											jQuery(function($) {
												var availableUsers = <?php echo json_encode( array_keys( $user_map ) ); ?>;
												var usersInList = <?php echo json_encode( array_keys( array_intersect( $user_map, $user_ids ) ) ); ?>;
												var autoCompleteList = availableUsers.filter(function(user) {
													return !usersInList.includes(user);
												});
			
												$("#direktt-user-search").autocomplete({
													source: autoCompleteList
												});
											});
										</script>
										<?php
										if ( ! empty ( $user_ids ) ) {
											foreach ( $user_ids as $user_id ) {
												?>
												<div class="direktt-edit-taxonomies-service-user-item">
													<p><?php echo esc_html( get_the_title( $user_id ) ); ?></p>
													<input type="hidden" name="<?php echo esc_attr( 'edit-category' === $subpage ? 'user_id_category' : 'user_id_tag' ); ?>" value="<?php echo esc_attr( $user_id ); ?>">
													<button type="button" class="direktt-button button-invert remove-user-btn" data-id="<?php echo esc_attr( $user_id ); ?>">
														<?php echo esc_html__( 'Remove', 'direktt' ); ?>
													</button>
												</div>
												<?php
											}
										} else {
											?>
											<p><?php echo 'edit-category' === $subpage ? esc_html__( 'No users found for this category.', 'direktt' ) : esc_html__( 'No users found for this tag.', 'direktt' ); ?></p>
											<?php
										}
										?>
									</div>
									<div class="direktt-edit-taxonomies-service-users-search">
										<input type="text" id="direktt-user-search" placeholder="<?php echo esc_attr__( 'Search users (enter display name)', 'direktt' ); ?>" />
										<!-- <input type="submit" id="add-user" name="<?php /* echo esc_attr( 'edit-category' === $subpage ? 'save_user_categories' : 'save_user_tags' ); */ ?>" value="<?php /* echo esc_attr__( 'Add User', 'direktt' ); */ ?>" /> -->
										<button id="add-user" class="direktt-button"><?php echo esc_html__( 'Add User', 'direktt' ); ?></button>
										<input type="hidden" name="<?php echo esc_attr( 'edit-category' === $subpage ? 'category' : 'tag' ); ?>" value="<?php echo esc_attr( $tax_name ); ?>">
										<input type="hidden" name="<?php echo esc_attr( 'edit-category' === $subpage ? 'save_user_categories_nonce' : 'save_user_tags_nonce' ); ?>" value="<?php echo 'edit-category' === $subpage ? esc_attr( wp_create_nonce( 'save_user_categories_nonce' ) ) : esc_attr( wp_create_nonce( 'save_user_tags_nonce' ) ); ?>">
									</div>
									<div class="direktt-edit-taxonomies-alert direktt-edit-taxonomies-popup">
										<div class="direktt-edit-taxonomies-alert-content direktt-edit-taxonomies-popup-content">
											<div class="direktt-edit-taxonomies-alert-header">
												<h3><?php echo esc_html__( 'Alert', 'direktt' ); ?></h3>    
											</div>
											<div class="direktt-edit-taxonomies-alert-text">
												<p></p>
											</div>
											<div class="direktt-edit-taxonomies-alert-actions">
												<button class="direktt-edit-taxonomies-ok"><?php echo esc_html__( 'OK', 'direktt' ); ?></button>
											</div>
										</div>
									</div>
									<div class="direktt-edit-taxonomies-confirm direktt-edit-taxonomies-popup">
										<div class="direktt-edit-taxonomies-confirm-content direktt-edit-taxonomies-popup-content">
											<div class="direktt-edit-taxonomies-confirm-header">
												<h3><?php echo esc_html__( 'Confirm', 'direktt' ); ?></h3>    
											</div>
											<div class="direktt-edit-taxonomies-confirm-text">
												<p><?php echo esc_html__( 'Are you sure you want to remove this user from this category?', 'direktt' ); ?></p>
											</div>
											<div class="direktt-edit-taxonomies-confirm-actions">
												<button id="direktt-edit-taxonomies-confirm-yes"><?php echo esc_html__( 'Yes', 'direktt' ); ?></button>
												<button class="direktt-edit-taxonomies-confirm-no"><?php echo esc_html__( 'No', 'direktt' ); ?></button>
											</div>
										</div>
									</div>
									<div id="direktt-loader-overlay">
										<div id="direktt-loader-container">
											<p id="direktt-loader-text"><?php echo esc_html__( 'Don\'t refresh the page', 'direktt' ); ?></p>
											<div id="direktt-loader"></div>
										</div>
									</div>
									<?php
									$messages = array(
										'user_already_exists' => 'edit-category' === $subpage ? esc_html__( 'User already exists in this category.', 'direktt' ) : esc_html__( 'User already exists in this tag.', 'direktt' ),
										'user_not_found'      => esc_html__( 'User not found.', 'direktt' ),
									);
									?>
									<script>
										document.getElementById('add-user').addEventListener('click', function() {
											event.preventDefault();

											var messages = <?php echo json_encode( $messages ); ?>;
			
											var availableUsers = <?php echo json_encode( array_keys( $user_map ) ); ?>;
			
											var input = document.getElementById('direktt-user-search');
											var newUserName = input.value.trim();
			
											const postTitleToIdMap = <?php echo json_encode( $user_map ); ?>;
											var newUserId = postTitleToIdMap[newUserName];
			
											function showAlert(message) {
												var alertBox = document.querySelector('.direktt-edit-taxonomies-alert');
												var alertText = alertBox.querySelector('.direktt-edit-taxonomies-alert-text p');
												alertText.textContent = message;
												jQuery(alertBox).fadeIn();
											}
			
											document.querySelector('.direktt-edit-taxonomies-ok').addEventListener('click', function() {
												event.preventDefault();
												input.value = '';
												var alertBox = document.querySelector('.direktt-edit-taxonomies-alert');
												jQuery(alertBox).fadeOut();
											});
			
											if (!newUserName) {
												event.preventDefault();
												showAlert(messages.user_not_found);
												return;
											}
											if (!newUserId) {
												event.preventDefault();
												showAlert(messages.user_not_found);
												return;
											}
			
											var existingItems = document.querySelectorAll('.direktt-edit-taxonomies-service-user-item p');
											for (var i = 0; i < existingItems.length; i++) {
												if (existingItems[i].textContent.trim() === newUserName) {
													event.preventDefault();
													showAlert(messages.user_already_exists);
													return;
												}
											}

											const form = this.closest('form');
											
											const actionInput = document.createElement('input');
											actionInput.type = 'hidden';
											actionInput.name = '<?php echo esc_attr( 'edit-category' === $subpage ? 'save_user_categories' : 'save_user_tags' ); ?>';
											actionInput.value = '1';

											form.appendChild(actionInput);

											var idToAdd = document.createElement('input');
											idToAdd.type = 'hidden';
											idToAdd.name = '<?php echo esc_attr( 'edit-category' === $subpage ? 'id_to_add_category' : 'id_to_add_tag' ); ?>';
											idToAdd.value = newUserId;
											document.querySelector('.direktt-edit-taxonomies-service-users-list').appendChild(idToAdd);
											var loader = document.querySelector('#direktt-loader-overlay');
											jQuery(loader).fadeIn();
											form.submit();
										});
										document.querySelectorAll('.remove-user-btn').forEach(function(button) {
											button.addEventListener('click', function(event) {
												event.preventDefault();
			
												var confirmBox = document.querySelector('.direktt-edit-taxonomies-confirm');
												var confirmYes = document.getElementById('direktt-edit-taxonomies-confirm-yes');
												var confirmNo = confirmBox.querySelector('.direktt-edit-taxonomies-confirm-no');
			
												jQuery(confirmBox).fadeIn();
			
												var idToRemove = document.createElement('input');
												idToRemove.type = 'hidden';
												idToRemove.name = '<?php echo esc_attr( 'edit-category' === $subpage ? 'id_to_remove_category' : 'id_to_remove_tag' ); ?>';
												idToRemove.value = this.getAttribute('data-id');
												confirmBox.appendChild(idToRemove);

												const form = this.closest('form');
												const actionInput = document.createElement('input');
												actionInput.type = 'hidden';
												actionInput.name = '<?php echo esc_attr( 'edit-category' === $subpage ? 'remove_user_categories' : 'remove_user_tags' ); ?>';
												actionInput.value = '1';
												form.appendChild(actionInput);
			
												confirmYes.onclick = function(e) {
													e.preventDefault();
													var loader = document.querySelector('#direktt-loader-overlay');
													jQuery(loader).fadeIn();
													jQuery(confirmBox).fadeOut();
													form.submit();
												};
			
												confirmNo.onclick = function(e) {
													e.preventDefault();
													jQuery(confirmBox).fadeOut();
													confirmBox.removeChild(idToRemove);
													form.removeChild(actionInput);
												};
											});
										});
									</script>
									<?php
								} else {
									?>
									<p><?php echo 'edit-category' === $subpage ? esc_html__( 'No users found for this category.', 'direktt' ) : esc_html__( 'No users found for this tag.', 'direktt' ); ?></p>
									<?php
								}
								?>
							</form>
						</div>
						<p><a href="<?php echo esc_url( $backUri ); ?>" class="direktt-button button-invert button-dark-gray"><?php echo esc_html__( 'Show All Taxonomies', 'direktt' ); ?></a></p>
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
					<div class="direktt-edit-taxonomies-service-categories">
						<h2><?php echo esc_html__( 'Categories', 'direktt' ); ?></h2>
						<?php
						foreach ( $all_categories as $category ) {
							$url = $_SERVER['REQUEST_URI'];
							$parts = parse_url( $url );
							parse_str( $parts['query'] ?? '', $params );
							$params['subpage'] = 'edit-category';
							$params['tax_name'] = $category['name'];
							$newQuery = http_build_query( $params );
							$newUri = $parts['path'] . ( $newQuery ? '?' . $newQuery : '' );
		
							$term_obj = get_term_by( 'name', $category['name'], 'direkttusercategories' );
							$count = 0;
							if ( $term_obj ) {
								$user_ids = get_objects_in_term( $term_obj->term_id, 'direkttusercategories' );
								$user_ids = array_values( array_filter( array_map( 'absint', $user_ids ) ) );
								$count = count( $user_ids );
							}
							?>
							<p><a href="<?php echo esc_url( $newUri ); ?>" class="direktt-button button-large"><?php echo esc_html( $category['name'] ); ?><?php echo ' <i>(' . esc_html( $count ) . ')</i>'; ?></a></p>
							<?php
						}
						?>
					</div>
					<div class="direktt-edit-taxonomies-service-tags">
						<h2><?php echo esc_html__( 'Tags', 'direktt' ); ?></h2>
						<?php
						foreach ( $all_tags as $tag ) {
							$url = $_SERVER['REQUEST_URI'];
							$parts = parse_url( $url );
							parse_str( $parts['query'] ?? '', $params );
							$params['subpage'] = 'edit-tag';
							$params['tax_name'] = $tag['name'];
							$newQuery = http_build_query( $params );
							$newUri = $parts['path'] . ( $newQuery ? '?' . $newQuery : '' );
		
							$term_obj = get_term_by( 'name', $tag['name'], 'direkttusertags' );
							$count = 0;
							if ( $term_obj ) {
								$user_ids = get_objects_in_term( $term_obj->term_id, 'direkttusertags' );
								$user_ids = array_values( array_filter( array_map( 'absint', $user_ids ) ) );
								$count = count( $user_ids );
							}
							?>
							<p><a href="<?php echo esc_url( $newUri ); ?>" class="direktt-button button-large"><?php echo esc_html( $tag['name'] ); ?><?php echo ' <i>(' . esc_html( $count ) . ')</i>'; ?></a></p>
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