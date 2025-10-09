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
                        "deps" => array("direktt-quill")
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

            // Optionally, add admin_notice or a query arg to confirm success
            wp_redirect(add_query_arg(['notes_updated' => 1]));
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

?>

        <div id="direktt-notes-view" style="margin-bottom: 20px;">
            <div id="editor">
                <?php echo wpautop($notes); // Shows HTML content 
                ?>
            </div>
        </div>
        
        <form id="direktt-notes-edit-form" method="post"">
            <?php wp_nonce_field('direktt_save_user_notes_' . $post_id, 'direktt_user_notes_nonce'); ?>
            <input type="hidden" name="direktt_notes_post_id" value="<?php echo esc_attr($post_id); ?>">
            <textarea id="direkttNotes" name="direktt_notes" rows="10" cols="40" style="display: none;"></textarea>
            <div class="direktt-tinymce-footer" style="margin-top:10px;">
                <button id="notes_save" class="button button-primary">Save</button>
            </div>
        </form>
<?php
    }
}
