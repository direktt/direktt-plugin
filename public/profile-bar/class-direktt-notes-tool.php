<?php

class Direktt_Notes_Tool
{
    public function setup_profile_tools_notes()
    {
        Direktt_Profile::add_profile_bar(
            array(
                "id" => "notes",
                "label" => esc_html__('Notes', 'direktt'),
                "callback" => [$this, 'render_user_notes'],
                "categories" => [],
                "tags" => [],
                "priority" => 3,
                "jsEnqueueArray" => [
                    array(
                        "handle" => "direktt-quill",
                        "src" => plugins_url('../js/quill.js', __FILE__),
                        "deps" => array()
                    ),
                    array(
                        "handle" => "direktt-quill-image-drag-drop",
                        "src" => plugins_url('../js/quill-image-drop-and-paste.min.js', __FILE__),
                        "deps" => array()
                    ),
                    array(
                        "handle" => "direktt-profile-notes-script",
                        "src" => plugins_url('../js/direktt-profile-notes.js', __FILE__),
                        "deps" => array("direktt-quill", "jquery")
                    )
                ],
                "cssEnqueueArray" => [
                    array(
                        "handle" => "direktt-quill-style",
                        "src" => plugins_url('../css/quill.snow.css', __FILE__)
                    ),
                ],
            )
        );
    }

    public function render_user_notes()
    {
        if (
            !empty($_POST['direktt_notes_post_id']) &&
            !empty($_POST['direktt_user_notes_nonce'])
        ) {
            $post_id = intval($_POST['direktt_notes_post_id']);

            // Check nonce
            if (!wp_verify_nonce($_POST['direktt_user_notes_nonce'], 'direktt_save_user_notes_' . $post_id)) {
                wp_die('Invalid nonce. Please refresh and try again.');
            }


            $notes = isset($_POST['direktt_notes']) ? wp_unslash($_POST['direktt_notes']) : '';

            // Save as post_content
            wp_update_post([
                'ID' => $post_id,
                'post_content' => $notes
            ]);

            $redirect_url = add_query_arg('status_flag', '1', $_SERVER['REQUEST_URI']);

            wp_safe_redirect(esc_url_raw($redirect_url));
            exit;
        }

        $subscriptionId = isset($_GET['subscriptionId']) ? sanitize_text_field(wp_unslash($_GET['subscriptionId'])) : false;
        $profile_user   = Direktt_User::get_user_by_subscription_id($subscriptionId);

        if (!$profile_user) {
            echo "<p>No notes found.</p>";
            return;
        }

        $notes = $profile_user['direktt_notes'] ?? '';
        $post_id = $profile_user['ID'];

        $status_flag    = isset($_GET['status_flag']) ? intval($_GET['status_flag']) : 0;
        $status_message = '';
        if ($status_flag === 1) {
            $status_message = esc_html__('Note saved successfully.', 'direktt');
        }
        if ($status_flag === 2) {
            $status_message = esc_html__('There was an error while saving the note.', 'direktt');
        }

?>
        <style>
            #editor,
            .ql-editor {
                touch-action: pan-x pan-y !important;
                height: 100% !important;
                font-size: 16px !important;
            }
        </style>

        <div class="direktt-notes-tool-wrapper">

            <?php if ($status_message) : ?>
                <div class="send-message-tool-info">
                    <p class="send-message-tool-status"><?php echo $status_message; ?></p>
                </div>
            <?php endif; ?>

            <div id="direktt-notes-view">
                <div id="editor">
                    <?php echo wpautop($notes);
                    ?>
                </div>
            </div>

            <form id="direktt-notes-edit-form" method="post">
                <?php wp_nonce_field('direktt_save_user_notes_' . $post_id, 'direktt_user_notes_nonce'); ?>
                <input type="hidden" id="direktt_notes_post_id" name="direktt_notes_post_id" value="<?php echo esc_attr($post_id); ?>">
                <textarea id="direkttNotes" name="direktt_notes" rows="10" cols="40" style="display: none;">
            </textarea>
            </form>

        </div>

<?php
        echo Direktt_Public::direktt_render_loader(__('Saving note', 'direktt'));
    }

    public function direktt_quill_upload_image_handler()
    {
        global $direktt_user;

        if (! isset($_POST['direktt_notes_post_id'])) {
            wp_send_json_error(array('message' => 'Invalid nonce, Missing id.'), 403);
        } else {
            $post_id = sanitize_text_field($_POST['direktt_notes_post_id']);
        }

        if (! isset($_POST['nonce']) || ! wp_verify_nonce($_POST['nonce'], 'direktt_save_user_notes_' . $post_id)) {
            wp_send_json_error(array('message' => 'Invalid nonce.'), 403);
        }

        if (! Direktt_User::is_direktt_admin()) {
            wp_send_json_error(array('message' => 'Not authorized'), 403);
        }

        if (
            empty($_FILES['file']) ||
            ! isset($_FILES['file']['tmp_name']) ||
            ! is_uploaded_file($_FILES['file']['tmp_name'])
        ) {
            wp_send_json_error(array('message' => 'No file uploaded.'), 400);
        }
        $file = $_FILES['file'];

        $upload_dir = wp_upload_dir();
        $custom_subdir = '/direktt-notes';
        $target_dir = $upload_dir['basedir'] . $custom_subdir;
        if (! file_exists($target_dir)) {
            if (! wp_mkdir_p($target_dir)) {
                wp_send_json_error(array('message' => 'Failed to create upload folder.'), 500);
            }
        }

        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (! in_array($ext, array('png', 'jpg', 'jpeg', 'gif', 'bmp', 'ico', 'webp'))) {
            wp_send_json_error(array('message' => 'Invalid file type.'), 400);
        }
        $rand_filename = wp_generate_password(8, false, false) . '.' . $ext;
        $target_path = trailingslashit($target_dir) . $rand_filename;

        if (! move_uploaded_file($file['tmp_name'], $target_path)) {
            wp_send_json_error(array('message' => 'Upload failed.'), 500);
        }

        $image_url = $upload_dir['baseurl'] . $custom_subdir . '/' . $rand_filename;

        wp_send_json_success(array('image_url' => $image_url));
    }
}
