<?php

defined('ABSPATH') || exit;

class Direktt_Notes_Tool
{

    private string $plugin_name;
    private string $version;

    public function __construct(string $plugin_name, string $version)
    {
        $this->plugin_name = $plugin_name;
        $this->version     = $version;
    }

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
                        "deps" => array(),
                        "ver" => "2.0.3"
                    ),
                    array(
                        "handle" => "direktt-quill-image-drag-drop",
                        "src" => plugins_url('../js/quill-image-drop-and-paste.min.js', __FILE__),
                        "deps" => array(),
                        "ver" => "2.0.1"
                    ),
                    array(
                        "handle" => "direktt-profile-notes-script",
                        "src" => plugins_url('../js/direktt-profile-notes.js', __FILE__),
                        "deps" => array("direktt-quill", "jquery"),
                        "ver" => $this->version
                    )
                ],
                "cssEnqueueArray" => [
                    array(
                        "handle" => "direktt-quill-style",
                        "src" => plugins_url('../css/quill.snow.css', __FILE__),
                        "ver" => "2.0.3"
                    ),
                ],
            )
        );
    }

    public function render_user_notes()
    {
        $allowed_html = wp_kses_allowed_html('post');

        if (
            !empty($_POST['direktt_notes_post_id']) &&
            !empty($_POST['direktt_user_notes_nonce'])
        ) {
            $post_id = intval($_POST['direktt_notes_post_id']);

            // Check nonce
            if (!wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['direktt_user_notes_nonce'])), 'direktt_save_user_notes_' . $post_id)) {
                wp_die('Invalid nonce. Please refresh and try again.');
            }

            $notes = isset($_POST['direktt_notes']) ? wp_kses( wp_unslash($_POST['direktt_notes']), $allowed_html ) : '';

            // Save as post_content
            wp_update_post([
                'ID' => $post_id,
                'post_content' => $notes
            ]);

            
            $redirect_url = add_query_arg('status_flag', '1');
            wp_safe_redirect(esc_url_raw($redirect_url));
            
            exit;
        }

        $subscriptionId = isset($_GET['subscriptionId']) ? sanitize_text_field(wp_unslash($_GET['subscriptionId'])) : false;
        $profile_user   = Direktt_User::get_user_by_subscription_id($subscriptionId);

        if (!$profile_user) {
            echo "<p>" . esc_html__("No notes found.", "direktt") . "</p>";
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

        echo wp_kses(Direktt_Public::direktt_render_loader(__('Saving note', 'direktt')), $allowed_html );

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
                    <p class="send-message-tool-status"><?php echo esc_html($status_message); ?></p>
                </div>
            <?php endif; ?>

            <div id="direktt-notes-view">
                <div id="editor">
                    <?php echo wp_kses(wpautop($notes),  $allowed_html);
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
    }

    public function direktt_quill_upload_image_handler()
    {
        global $direktt_user;

        if (! isset($_POST['direktt_notes_post_id'])) {
            wp_send_json_error(array('message' => 'Invalid nonce, Missing id.'), 403);
        } else {
            $post_id = sanitize_text_field(wp_unslash($_POST['direktt_notes_post_id']));
        }

        if (! isset($_POST['nonce']) || ! wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'direktt_save_user_notes_' . $post_id)) {
            wp_send_json_error(array('message' => 'Invalid nonce.'), 403);
        }

        if (! Direktt_User::is_direktt_admin()) {
            wp_send_json_error(array('message' => 'Not authorized'), 403);
        }

        if (
            empty($_FILES['file']) ||
            ! isset($_FILES['file']['tmp_name'])
        ) {
            wp_send_json_error(array('message' => 'No file uploaded.'), 400);
        } 

        $file = $_FILES['file'];        //phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Jutification: no sanitization for $_FILES['file']. All related properties have been checked - $file['name'] and mime type
        $file['name'] = sanitize_file_name($file['name']);

        // Basic PHP upload error check
        if ($file['error'] !== UPLOAD_ERR_OK) {
            wp_send_json_error(array('message' => 'Upload error: ' . $file['error']), 400);
        }

        // Restrict to allowed MIME types
        $allowed_mimes = array(
            'jpg|jpeg|jpe' => 'image/jpeg',
            'png'          => 'image/png'
        );

        // Validate extension and real mime against the file contents
        $check = wp_check_filetype_and_ext($file['tmp_name'], $file['name'], $allowed_mimes);
        if (! $check['ext'] || ! $check['type']) {
            wp_send_json_error(array('message' => 'Invalid file type.'), 400);
        }

        // 1. Set up a filter to alter the upload directory
        $custom_subdir = '/direktt-notes';
        $upload_dir_filter = function ($dirs) use ($custom_subdir) {
            $dirs['subdir'] = $custom_subdir;
            $dirs['path'] = $dirs['basedir'] . $custom_subdir;
            $dirs['url'] = $dirs['baseurl'] . $custom_subdir;
            return $dirs;
        };
        add_filter('upload_dir', $upload_dir_filter);

        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        // 2. Set up a filter to use a custom filename
        $custom_filename = wp_generate_password(8, false, false) . '.' . $ext;
        $prefilter = function ($file_array) use ($custom_filename) {
            $file_array['name'] = $custom_filename;
            return $file_array;
        };
        add_filter('wp_handle_upload_prefilter', $prefilter);

        $upload_overrides = array('test_form' => false);

        // 3. Handle the upload
        $movefile = wp_handle_upload($file, $upload_overrides);

        // 4. Remove filters
        remove_filter('upload_dir', $upload_dir_filter);
        remove_filter('wp_handle_upload_prefilter', $prefilter);

        // 5. Handle result
        if (isset($movefile['error'])) {
            wp_send_json_error(array('message' => 'Upload failed: ' . $movefile['error']), 500);
        }

        wp_send_json_success(array('image_url' => $movefile['url']));
    }
}
