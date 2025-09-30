<?php

class Direktt_Taxonomies_Tool {
    public function setup_profile_tools_taxonomies() {
        Direktt_Profile::add_profile_bar(
            array(
                "id" => "edit-user-taxonomies",
                "label" => esc_html__( 'Edit Taxonomies', 'direktt' ),
                "callback" => [$this, 'render_user_taxonomies'],
                "categories" => [],
                "tags" => [],
                "priority" => 2
            )
        );
    }

    public function render_user_taxonomies() {
        $subscriptionId = isset( $_GET['subscriptionId'] ) ? sanitize_text_field( wp_unslash( $_GET['subscriptionId'] ) ) : false;
        $profile_user   = Direktt_User::get_user_by_subscription_id( $subscriptionId );
    
        if ( isset( $_POST['save_user_taxonomies'] ) ) {
            if ( ! isset( $_POST['save_user_taxonomies_nonce'] )
                || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['save_user_taxonomies_nonce'] ) ), 'save_user_taxonomies_nonce' )
            ) {
                return;
            }
    
            $user_id = $profile_user['ID'];
            $categories = isset( $_POST['user_categories'] )
                ? array_map( 'sanitize_text_field', wp_unslash( $_POST['user_categories'] ) )
                : [];
            wp_set_object_terms( $user_id, $categories, 'direkttusercategories' );
    
            $tags = isset( $_POST['user_tags'] )
                ? array_map( 'sanitize_text_field', wp_unslash( $_POST['user_tags'] ) )
                : [];
            wp_set_object_terms( $user_id, $tags, 'direkttusertags' );
    
            $redirect_url = add_query_arg( 'status_flag', '1', $_SERVER['REQUEST_URI'] );
            wp_safe_redirect( esc_url_raw( $redirect_url ) );
            exit;
        }
    
        $all_categories = Direktt_User::get_all_user_categories();
        $all_tags       = Direktt_User::get_all_user_tags();
    
        if ( $subscriptionId === false || $profile_user === false ) {
            return;
        }
    
        $assigned_categories = wp_get_post_terms( $profile_user['ID'], 'direkttusercategories', array( 'fields' => 'names' ) );
        $assigned_tags       = wp_get_post_terms( $profile_user['ID'], 'direkttusertags', array( 'fields' => 'names' ) );
    
        $status_flag    = isset( $_GET['status_flag'] ) ? intval( $_GET['status_flag'] ) : 0;
        $status_message = '';
        if ( $status_flag === 1 ) {
            $status_message = esc_html__( 'Saved successfully.', 'direktt' );
        }
    
        ?>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
            var statusEl = document.querySelector('.direktt-taxonomies-tool-status');
            var submitBtn = document.querySelector('.direktt-taxonomies-tool-submit input[type="submit"]');
            if ( submitBtn && statusEl ) {
                submitBtn.addEventListener('click', function() {
                    statusEl.textContent = '<?php echo esc_js( __( 'Saving...', 'direktt' ) ); ?>';
                });
            }
        });
        </script>
        <form method="post" action="">
            <div class="direktt-taxonomies-tool-wrapper">
                <?php if ( $status_message ) : ?>
                    <div class="direktt-taxonomies-tool-info">
                        <!-- TODO Mozda neki text editing taxonomies for user... -->
                        <!-- <p class="direktt-taxonomies-tool-user-info -->
                        <p class="direktt-taxonomies-tool-status"><?php echo $status_message; ?></p>
                    </div>
                <?php endif; ?>
                <div class="direktt-taxonomies-tool-categories">
                    <h3><?php echo esc_html__( 'Categories', 'direktt' ); ?></h3>
                    <p>
						<?php
						foreach ( $all_categories as $category ) {
							$is_checked = in_array( $category['name'], $assigned_categories ) ? 'checked' : '';
							?>
							<label>
							<input type="checkbox" name="user_categories[]" value="<?php echo esc_attr( $category['name'] ); ?>" <?php echo esc_attr( $is_checked ); ?>>
							<?php echo ' ' . esc_html( $category['name'] ); ?>
							</label>
							<?php
						}
						?>
					</p>
                </div>
                <div class="direktt-taxonomies-tool-tags">
                    <h3><?php echo esc_html__( 'Tags', 'direktt' ); ?></h3>
                    <p>
						<?php
						foreach ( $all_tags as $tag ) {
							$is_checked = in_array( $tag['name'], $assigned_tags ) ? 'checked' : '';
							?>
							<label>
							<input type="checkbox" name="user_tags[]" value="<?php echo esc_attr( $tag['name'] ); ?>" <?php echo esc_attr( $is_checked ); ?>>
							<?php echo ' ' . esc_html( $tag['name'] ); ?>
							</label>
							<?php
						}
						?>
					</p>
                </div>
                <div class="direktt-taxonomies-tool-submit">
                    <input type="submit" name="save_user_taxonomies" value="<?php echo esc_html__( 'Save', 'direktt' ); ?>" class="button button-primary button-large">
                    <input type="hidden" name="save_user_taxonomies_nonce" value="<?php echo esc_attr( wp_create_nonce( 'save_user_taxonomies_nonce' ) ); ?>">
                </div>
            </div>
        </form>
        <?php
    }
}