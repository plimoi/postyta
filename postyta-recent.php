<?php
/**
 * Plugin Name: Postyta Recent
 * Description: Display recent posts in a widget with sorting options.
 * Version: 1.3.3
 * Author: Your Name
 * Author URI: Your Website
 */

class Postyta_Recent_Widget extends WP_Widget {
    public function __construct() {
        parent::__construct(
            'postyta_recent_widget',
            'Postyta Recent',
            array('description' => 'Display recent posts in a widget with sorting options.')
        );
    }

    public function widget($args, $instance) {
        $title = apply_filters('widget_title', $instance['title']);
        $number_of_posts = !empty($instance['number_of_posts']) ? $instance['number_of_posts'] : 5;
        $sort_by = !empty($instance['sort_by']) ? $instance['sort_by'] : 'date';
        $order = !empty($instance['order']) ? $instance['order'] : 'desc';
        $display_thumbnail = !empty($instance['display_thumbnail']) ? $instance['display_thumbnail'] : false;
        $desktop_thumbnail_size = !empty($instance['desktop_thumbnail_size']) ? $instance['desktop_thumbnail_size'] : 'medium';
        $mobile_thumbnail_size = !empty($instance['mobile_thumbnail_size']) ? $instance['mobile_thumbnail_size'] : 'thumbnail';
        $display_date = !empty($instance['display_date']) ? $instance['display_date'] : false;
        $display_time = !empty($instance['display_time']) ? $instance['display_time'] : false;
        $display_category = !empty($instance['display_category']) ? $instance['display_category'] : false;
        $display_tags = !empty($instance['display_tags']) ? $instance['display_tags'] : false;
        $display_excerpts = !empty($instance['display_excerpts']) ? $instance['display_excerpts'] : false;
        $post_title_tag = !empty($instance['post_title_tag']) ? $instance['post_title_tag'] : 'h2';
        $add_title_property = !empty($instance['add_title_property']) ? $instance['add_title_property'] : false;
        $category_ids = !empty($instance['category_ids']) ? explode(',', $instance['category_ids']) : array();
        $tag_ids = !empty($instance['tag_ids']) ? explode(',', $instance['tag_ids']) : array();
        $desktop_columns = !empty($instance['desktop_columns']) ? intval($instance['desktop_columns']) : 1;
        $mobile_columns = !empty($instance['mobile_columns']) ? intval($instance['mobile_columns']) : 1;

        echo $args['before_widget'];

        if ($title) {
            echo $args['before_title'] . $title . $args['after_title'];
        }

        $args = array(
            'numberposts' => $number_of_posts,
            'post_status' => 'publish',
            'orderby' => $sort_by,
            'order' => $order,
            'category__in' => $category_ids,
            'tag__in' => $tag_ids,
        );

        $recent_posts = wp_get_recent_posts($args);

        if ($recent_posts) {
            // Adjust column width for mobile devices when mobile_columns is set to 1
            if ($mobile_columns === 1) {
                $mobile_column_width = 100;
            } else {
                $mobile_column_width = 100 / $mobile_columns;
            }

            // Add column classes to the widget container
            $widget_classes = 'widget_postyta_recent';
            $widget_classes .= ' columns-desktop-' . $desktop_columns;
            $widget_classes .= ' columns-mobile-' . $mobile_columns;

            // Get the additional CSS class for the main div
            $main_div_class = !empty($instance['main_div_class']) ? sanitize_html_class($instance['main_div_class']) : '';

            // Output the widget container with column classes and main div class
            echo '<div class="' . $widget_classes . ' ' . $main_div_class . '">';
            echo '<ul>';

            foreach ($recent_posts as $recent) {
                echo '<li>';

                if ($display_thumbnail && has_post_thumbnail($recent['ID'])) {
                    $thumbnail_size = wp_is_mobile() ? $mobile_thumbnail_size : $desktop_thumbnail_size;
                    $thumbnail = get_the_post_thumbnail($recent['ID'], $thumbnail_size);
                    $image = wp_get_attachment_image_src(get_post_thumbnail_id($recent['ID']), $thumbnail_size);
                    $width = $image[1];
                    $height = $image[2];

                    echo '<div class="post-thumbnail">';
                    echo '<a href="' . get_permalink($recent['ID']) . '">';
                    echo '<img src="' . $image[0] . '" width="' . $width . '" height="' . $height . '" alt="' . $recent['post_title'] . '">';
                    echo '</a>';
                    echo '</div>';
                }

                echo '<' . $post_title_tag . ' class="post-title">';
                echo '<a href="' . get_permalink($recent['ID']);

                if ($add_title_property) {
                    echo '" title="' . esc_attr($recent['post_title']);
                }

                echo '">' . $recent['post_title'] . '</a>';
                echo '</' . $post_title_tag . '>';

                echo '<div class="post-meta">';

                if ($display_date || $display_time) {
                    echo '<div class="post-date-time">';

                    if ($display_date) {
                        echo '<span class="post-date">' . get_the_date('', $recent['ID']) . '</span>';
                    }

                    if ($display_date && $display_time) {
                        echo ' / ';
                    }

                    if ($display_time) {
                        echo '<span class="post-time">' . get_the_time('', $recent['ID']) . '</span>';
                    }

                    echo '</div>';
                }

                if ($display_category) {
                    $categories = get_the_category($recent['ID']);

                    if ($categories) {
                        echo '<div class="post-categories">';
                        
                        if ($instance['display_all_categories']) {
                            $category_count = count($categories);
                            foreach ($categories as $index => $category) {
                                echo '<a href="' . get_category_link($category->term_id) . '">' . $category->name . '</a>';
                                if ($index < $category_count - 1) {
                                    echo ', ';
                                }
                            }
                        } else {
                            $main_category = $categories[0];
                            echo '<a href="' . get_category_link($main_category->term_id) . '">' . $main_category->name . '</a>';
                        }
                        
                        echo '</div>';
                    }
                }

                if ($display_tags) {
                    $tags = get_the_tags($recent['ID']);

                    if ($tags) {
                        echo '<div class="post-tags">';
                        $tag_count = count($tags);
                        foreach ($tags as $index => $tag) {
                            echo '<a href="' . get_tag_link($tag->term_id) . '">' . $tag->name . '</a>';
                            if ($index < $tag_count - 1) {
                                echo ', ';
                            }
                        }
                        echo '</div>';
                    }
                }

                if ($display_excerpts) {
                    echo '<div class="post-excerpt">' . get_the_excerpt($recent['ID']) . '</div>';
                }

                echo '</div>'; // Close post-meta

                echo '</li>';
            }

            echo '</ul>';
            echo '</div>'; // Close the widget container
        } else {
            echo 'No recent posts found.';
        }

        echo $args['after_widget'];
    }

    public function form($instance) {
        $title = !empty($instance['title']) ? $instance['title'] : '';
        $number_of_posts = !empty($instance['number_of_posts']) ? $instance['number_of_posts'] : 5;
        $sort_by = !empty($instance['sort_by']) ? $instance['sort_by'] : 'date';
        $order = !empty($instance['order']) ? $instance['order'] : 'desc';
        $display_thumbnail = !empty($instance['display_thumbnail']) ? $instance['display_thumbnail'] : false;
        $desktop_thumbnail_size = !empty($instance['desktop_thumbnail_size']) ? $instance['desktop_thumbnail_size'] : 'medium';
        $mobile_thumbnail_size = !empty($instance['mobile_thumbnail_size']) ? $instance['mobile_thumbnail_size'] : 'thumbnail';
        $display_date = !empty($instance['display_date']) ? $instance['display_date'] : false;
        $display_time = !empty($instance['display_time']) ? $instance['display_time'] : false;
        $display_category = !empty($instance['display_category']) ? $instance['display_category'] : false;
        $display_tags = !empty($instance['display_tags']) ? $instance['display_tags'] : false;
        $display_excerpts = !empty($instance['display_excerpts']) ? $instance['display_excerpts'] : false;
        $post_title_tag = !empty($instance['post_title_tag']) ? $instance['post_title_tag'] : 'h2';
        $add_title_property = !empty($instance['add_title_property']) ? $instance['add_title_property'] : false;
        $display_all_categories = !empty($instance['display_all_categories']) ? $instance['display_all_categories'] : false;
        $category_ids = !empty($instance['category_ids']) ? $instance['category_ids'] : '';
        $tag_ids = !empty($instance['tag_ids']) ? $instance['tag_ids'] : '';
        $desktop_columns = !empty($instance['desktop_columns']) ? intval($instance['desktop_columns']) : 1;
        $mobile_columns = !empty($instance['mobile_columns']) ? intval($instance['mobile_columns']) : 1;
        $main_div_class = !empty($instance['main_div_class']) ? $instance['main_div_class'] : '';

        ?>
        <p>
            <label for="<?php echo $this->get_field_id('title'); ?>">Title:</label>
            <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>">
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('number_of_posts'); ?>">Number of Posts to Show:</label>
            <input class="widefat" id="<?php echo $this->get_field_id('number_of_posts'); ?>" name="<?php echo $this->get_field_name('number_of_posts'); ?>" type="number" min="1" max="10" value="<?php echo esc_attr($number_of_posts); ?>">
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('sort_by'); ?>">Sort By:</label>
            <select class="widefat" id="<?php echo $this->get_field_id('sort_by'); ?>" name="<?php echo $this->get_field_name('sort_by'); ?>">
                <option value="date" <?php selected($sort_by, 'date'); ?>>Date</option>
                <option value="rand" <?php selected($sort_by, 'rand'); ?>>Random</option>
            </select>
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('order'); ?>">Order:</label>
            <select class="widefat" id="<?php echo $this->get_field_id('order'); ?>" name="<?php echo $this->get_field_name('order'); ?>">
                <option value="desc" <?php selected($order, 'desc'); ?>>Descending</option>
                <option value="asc" <?php selected($order, 'asc'); ?>>Ascending</option>
            </select>
        </p>
        <p>
            <input class="checkbox" type="checkbox" <?php checked($display_thumbnail, true); ?> id="<?php echo $this->get_field_id('display_thumbnail'); ?>" name="<?php echo $this->get_field_name('display_thumbnail'); ?>" />
            <label for="<?php echo $this->get_field_id('display_thumbnail'); ?>">Display Thumbnails</label>
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('desktop_thumbnail_size'); ?>">Desktop Thumbnail Size:</label>
            <input class="widefat" id="<?php echo $this->get_field_id('desktop_thumbnail_size'); ?>" name="<?php echo $this->get_field_name('desktop_thumbnail_size'); ?>" type="text" value="<?php echo esc_attr($desktop_thumbnail_size); ?>">
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('mobile_thumbnail_size'); ?>">Mobile Thumbnail Size:</label>
            <input class="widefat" id="<?php echo $this->get_field_id('mobile_thumbnail_size'); ?>" name="<?php echo $this->get_field_name('mobile_thumbnail_size'); ?>" type="text" value="<?php echo esc_attr($mobile_thumbnail_size); ?>">
        </p>
        <p>
            <input class="checkbox" type="checkbox" <?php checked($display_date, true); ?> id="<?php echo $this->get_field_id('display_date'); ?>" name="<?php echo $this->get_field_name('display_date'); ?>" />
            <label for="<?php echo $this->get_field_id('display_date'); ?>">Display Date</label>
        </p>
        <p>
            <input class="checkbox" type="checkbox" <?php checked($display_time, true); ?> id="<?php echo $this->get_field_id('display_time'); ?>" name="<?php echo $this->get_field_name('display_time'); ?>" />
            <label for="<?php echo $this->get_field_id('display_time'); ?>">Display Time</label>
        </p>
		<p>
			<input class="checkbox" type="checkbox" <?php checked($display_category, true); ?> id="<?php echo $this->get_field_id('display_category'); ?>" name="<?php echo $this->get_field_name('display_category'); ?>" />
			<label for="<?php echo $this->get_field_id('display_category'); ?>">Display Category</label>
		</p>
		<?php if ($display_category) : ?>
			<p>
				<input class="checkbox" type="checkbox" <?php checked($display_all_categories, true); ?> id="<?php echo $this->get_field_id('display_all_categories'); ?>" name="<?php echo $this->get_field_name('display_all_categories'); ?>" />
				<label for="<?php echo $this->get_field_id('display_all_categories'); ?>">Display All Categories</label>
			</p>
		<?php endif; ?>
        <p>
            <input class="checkbox" type="checkbox" <?php checked($display_tags, true); ?> id="<?php echo $this->get_field_id('display_tags'); ?>" name="<?php echo $this->get_field_name('display_tags'); ?>" />
            <label for="<?php echo $this->get_field_id('display_tags'); ?>">Display Tags</label>
        </p>
        <p>
            <input class="checkbox" type="checkbox" <?php checked($display_excerpts, true); ?> id="<?php echo $this->get_field_id('display_excerpts'); ?>" name="<?php echo $this->get_field_name('display_excerpts'); ?>" />
            <label for="<?php echo $this->get_field_id('display_excerpts'); ?>">Display Excerpts</label>
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('post_title_tag'); ?>">Post Title HTML Tag:</label>
            <input class="widefat" id="<?php echo $this->get_field_id('post_title_tag'); ?>" name="<?php echo $this->get_field_name('post_title_tag'); ?>" type="text" value="<?php echo esc_attr($post_title_tag); ?>">
        </p>
        <p>
            <input class="checkbox" type="checkbox" <?php checked($add_title_property, true); ?> id="<?php echo $this->get_field_id('add_title_property'); ?>" name="<?php echo $this->get_field_name('add_title_property'); ?>" />
            <label for="<?php echo $this->get_field_id('add_title_property'); ?>">Add Title Property</label>
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('category_ids'); ?>">Category IDs (comma-separated):</label>
            <input class="widefat" id="<?php echo $this->get_field_id('category_ids'); ?>" name="<?php echo $this->get_field_name('category_ids'); ?>" type="text" value="<?php echo esc_attr($category_ids); ?>">
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('tag_ids'); ?>">Tag IDs (comma-separated):</label>
            <input class="widefat" id="<?php echo $this->get_field_id('tag_ids'); ?>" name="<?php echo $this->get_field_name('tag_ids'); ?>" type="text" value="<?php echo esc_attr($tag_ids); ?>">
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('desktop_columns'); ?>">Number of Columns (Desktop):</label>
            <input class="widefat" id="<?php echo $this->get_field_id('desktop_columns'); ?>" name="<?php echo $this->get_field_name('desktop_columns'); ?>" type="number" min="1" max="4" value="<?php echo esc_attr($desktop_columns); ?>">
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('mobile_columns'); ?>">Number of Columns (Mobile):</label>
            <input class="widefat" id="<?php echo $this->get_field_id('mobile_columns'); ?>" name="<?php echo $this->get_field_name('mobile_columns'); ?>" type="number" min="1" max="4" value="<?php echo esc_attr($mobile_columns); ?>">
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('main_div_class'); ?>">Main Div Class:</label>
            <input class="widefat" id="<?php echo $this->get_field_id('main_div_class'); ?>" name="<?php echo $this->get_field_name('main_div_class'); ?>" type="text" value="<?php echo esc_attr($main_div_class); ?>">
        </p>
        <?php
    }

    public function update($new_instance, $old_instance) {
        $instance = array();
        $instance['title'] = !empty($new_instance['title']) ? sanitize_text_field($new_instance['title']) : '';
        $instance['number_of_posts'] = !empty($new_instance['number_of_posts']) ? intval($new_instance['number_of_posts']) : 5;
        $instance['sort_by'] = !empty($new_instance['sort_by']) ? sanitize_text_field($new_instance['sort_by']) : 'date';
        $instance['order'] = !empty($new_instance['order']) ? sanitize_text_field($new_instance['order']) : 'desc';
        $instance['display_thumbnail'] = !empty($new_instance['display_thumbnail']) ? 1 : 0;
        $instance['desktop_thumbnail_size'] = !empty($new_instance['desktop_thumbnail_size']) ? sanitize_text_field($new_instance['desktop_thumbnail_size']) : 'medium';
        $instance['mobile_thumbnail_size'] = !empty($new_instance['mobile_thumbnail_size']) ? sanitize_text_field($new_instance['mobile_thumbnail_size']) : 'thumbnail';
        $instance['display_date'] = !empty($new_instance['display_date']) ? 1 : 0;
        $instance['display_time'] = !empty($new_instance['display_time']) ? 1 : 0;
        $instance['display_category'] = !empty($new_instance['display_category']) ? 1 : 0;
        $instance['display_tags'] = !empty($new_instance['display_tags']) ? 1 : 0;
        $instance['display_excerpts'] = !empty($new_instance['display_excerpts']) ? 1 : 0;
        $instance['post_title_tag'] = !empty($new_instance['post_title_tag']) ? sanitize_text_field($new_instance['post_title_tag']) : 'h2';
        $instance['add_title_property'] = !empty($new_instance['add_title_property']) ? 1 : 0;
        $instance['display_all_categories'] = !empty($new_instance['display_all_categories']) ? 1 : 0;
        $instance['category_ids'] = !empty($new_instance['category_ids']) ? sanitize_text_field($new_instance['category_ids']) : '';
        $instance['tag_ids'] = !empty($new_instance['tag_ids']) ? sanitize_text_field($new_instance['tag_ids']) : '';
        $instance['desktop_columns'] = !empty($new_instance['desktop_columns']) ? intval($new_instance['desktop_columns']) : 1;
        $instance['mobile_columns'] = !empty($new_instance['mobile_columns']) ? intval($new_instance['mobile_columns']) : 1;
        $instance['main_div_class'] = !empty($new_instance['main_div_class']) ? sanitize_text_field($new_instance['main_div_class']) : '';
        return $instance;
    }
}

function register_postyta_recent_widget() {
    register_widget('Postyta_Recent_Widget');
}
add_action('widgets_init', 'register_postyta_recent_widget');

function postyta_recent_widget_enqueue_styles() {
    wp_enqueue_style('postyta-style', plugin_dir_url(__FILE__) . 'postyta-style.css');
}
add_action('wp_enqueue_scripts', 'postyta_recent_widget_enqueue_styles');
