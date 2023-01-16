<?php
/*
 * Plugin Name: Beautiful Subtitles
 * Description: A plugin that allows you to insert custom subtitles in your posts and pages.
 * Version: 1.0
 * Author: Federico Noya
 * Author URI: https://www.federiconoya.com
 * License: GPL2
 */

class Beautiful_Subtitles
{
    public function __construct()
    {
        // Register actions and filters
        add_action('add_meta_boxes', array($this, 'add_subtitle_meta_box'));
        add_action('save_post', array($this, 'save_subtitle'));
        add_filter('the_content', array($this, 'display_subtitle'));
    }


    public function add_subtitle_meta_box()


    {

        if (current_user_can('edit_posts')) {
            add_meta_box(
                'beautiful_subtitles_meta_box',
                __('Subtitle', 'beautiful-subtitles'),
                array($this, 'render_subtitle_meta_box'),
                'post',
                'normal',
                'default'
            );
            add_meta_box(
                'beautiful_subtitles_meta_box',
                __('Subtitle', 'beautiful-subtitles'),
                array($this, 'render_subtitle_meta_box'),
                'page',
                'normal',
                'default'
            );
        }
    }

    public function render_subtitle_meta_box($post)
    {
        // Retrieve the subtitle from the database
        $subtitle = esc_html(get_post_meta($post->ID, 'beautiful_subtitles_subtitle', true));
        $html_tag = esc_attr(get_post_meta($post->ID, 'beautiful_subtitles_html_tag', true));

        // Add a nonce field
        wp_nonce_field('beautiful_subtitles_meta_box', 'beautiful_subtitles_meta_box_nonce');
        // Create the form fields for the subtitle and html tag
?>
        <p>
            <label for="beautiful_subtitles_subtitle"><?php _e('Subtitle:', 'beautiful-subtitles'); ?></label>
            <input type="text" id="beautiful_subtitles_subtitle" name="beautiful_subtitles_subtitle" value="<?php echo $subtitle; ?>" style="width:100%; border-radius:0px; margin-top:12px; padding:10px;" />
        </p>
        <p>
            <label for="beautiful_subtitles_html_tag"><?php _e('HTML Tag to display:', 'beautiful-subtitles'); ?></label>
            <select id="beautiful_subtitles_html_tag" name="beautiful_subtitles_html_tag">
                <option value="h2" <?php selected($html_tag, 'h2'); ?>>h2</option>
                <option value="h3" <?php selected($html_tag, 'h3'); ?>>h3</option>
                <option value="h4" <?php selected($html_tag, 'h4'); ?>>h4</option>
                <option value="p" <?php selected($html_tag, 'p'); ?>>p</option>
                <option value="div" <?php selected($html_tag, 'div'); ?>>div</option>
            </select>
        </p>
<?php
    }


    public function save_subtitle($post_id)
    {
        if (current_user_can('edit_posts')) {
            // Check if the subtitle field is set
            if (isset($_POST['beautiful_subtitles_subtitle'])) {
                // Verify the nonce
                if (wp_verify_nonce($_POST['beautiful_subtitles_meta_box_nonce'], 'beautiful_subtitles_meta_box')) {
                    // Save the subtitle to the database
                    update_post_meta($post_id, 'beautiful_subtitles_subtitle', sanitize_text_field($_POST['beautiful_subtitles_subtitle']));
                }
            }
            if (isset($_POST['beautiful_subtitles_html_tag'])) {
                // Sanitize the HTML tag
                $html_tag = sanitize_text_field($_POST['beautiful_subtitles_html_tag']);
                // Check if the selected HTML tag is valid
                $valid_tags = array('h1', 'h2', 'h3', 'h4', 'p', 'div');
                if (in_array($html_tag, $valid_tags)) {
                    // Verify the nonce
                    if (wp_verify_nonce($_POST['beautiful_subtitles_meta_box_nonce'], 'beautiful_subtitles_meta_box')) {
                        update_post_meta($post_id, 'beautiful_subtitles_html_tag', $html_tag);
                    }
                }
            }
        }
    }



    public function display_subtitle($content)
    {
        // Check if we're on a single post or page
        if (is_singular()) {
            // Retrieve the subtitle and html tag from the database
            $subtitle = get_post_meta(get_the_ID(), 'beautiful_subtitles_subtitle', true);
            $html_tag = get_post_meta(get_the_ID(), 'beautiful_subtitles_html_tag', true);

            // Check if the subtitle is set
            if (!empty($subtitle)) {
                // check if the html tag is valid
                $valid_tags = array('h2', 'h3', 'h4', 'p', 'div');
                if (!in_array($html_tag, $valid_tags)) {
                    $html_tag = 'div';
                }
                // Add the subtitle to the content
                $content = '<' . esc_attr($html_tag) . ' class="beautiful-subtitle">' . wp_kses_post($subtitle) . '</' . esc_attr($html_tag) . '>' . $content;
            }
        }
        return $content;
    }
}

new Beautiful_Subtitles();
