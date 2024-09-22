<?php

if (!defined('ABSPATH'))
{
	die();
}

?>
	<div class="<?php echo esc_attr($this->reference); ?>" data-limit="<?php echo esc_attr($limit); ?>">
        <p>
            <label for="<?php echo esc_attr($this->get_field_name('title')); ?>"><?php esc_attr_e('Title:', 'g-business-reviews-rating'); ?></label>
            <input type="text" id="<?php echo esc_attr($this->get_field_id('title')); ?>" class="widefat" name="<?php echo esc_attr($this->get_field_name('title')); ?>" value="<?php echo esc_attr($title); ?>">
        </p>
        <p class="business-name"<?php echo (!$display_name && !$display_icon) ? ' style="display: none;"' : ''; ?>><?php echo (($this->business_icon != NULL) ? '<span class="icon"' . ((!$display_icon) ? ' style="display: none;"' : '') . '><img src="' . esc_attr($this->business_icon) . '" alt="' . esc_attr($this->business_name . ' ' . __('Icon', 'g-business-reviews-rating-extended')) . '"></span>' : '') . '<span class="name"' . ((!$display_name) ? ' style="display: none;"' : '') . '>' . esc_html($this->business_name) . '</span>'; ?></p>
	    <p class="plugin-attribution"<?php echo (!$display_attribution) ? ' style="display: none;"' : ''; ?>><span class="powered-by-google"></span></p>
        <p class="theme">
            <label for="<?php echo esc_attr($this->get_field_name('theme')); ?>"><?php esc_attr_e('Theme:', 'g-business-reviews-rating'); ?></label>
            <select id="<?php echo esc_attr($this->get_field_id('theme')); ?>" name="<?php echo esc_attr($this->get_field_name('theme')); ?>">
                <option value=""<?php echo ($theme == NULL) ? ' selected' : ''; ?>><?php esc_html_e('Default', 'g-business-reviews-rating'); ?></option>
<?php
	foreach ($this->reviews_themes as $k => $theme_name) :
?>
                <option value="<?php echo esc_attr($k); ?>"<?php echo (($theme == $k) ? ' selected' : ''); ?>><?php echo esc_html($theme_name); ?></option>
<?php
	endforeach;
?>
            </select>
		</p>
        <p class="limit">
			<label for="<?php echo esc_attr($this->get_field_name('limit')); ?>"><?php esc_attr_e('Review Limit:', 'g-business-reviews-rating'); ?></label>
			<input type="number" id="<?php echo esc_attr($this->get_field_id('limit')); ?>" class="small-text" name="<?php echo esc_attr($this->get_field_name('limit')); ?>" value="<?php echo esc_attr($limit); ?>" placeholder="&mdash;" step="1" min="0" max="<?php echo esc_attr($count); ?>">
			<span class="description"><a href="<?php echo esc_attr($this->plugin_url . '#reviews'); ?>"><?php ($count == 1) ? printf(__( '%1$s review available'), $count) : printf(__( '%1$s reviews available'), $count); ?></a></span>
        </p>
        <p class="view">
			<label for="<?php echo esc_attr($this->get_field_name('view')); ?>"><?php esc_attr_e('Carousel View:', 'g-business-reviews-rating'); ?></label>
			<input type="number" id="<?php echo esc_attr($this->get_field_id('view')); ?>" class="small-text view" name="<?php echo esc_attr($this->get_field_name('view')); ?>" value="<?php echo esc_attr((isset($view)) ? $view : ''); ?>" placeholder="&mdash;" step="1" min="1" max="<?php echo esc_attr(($count > 2) ? $count - 1 : 1); ?>">
			<input class="checkbox loop" type="checkbox" id="<?php echo esc_attr($this->get_field_id('loop')); ?>" name="<?php echo esc_attr($this->get_field_name('loop')); ?>" value="1"<?php echo ((isset($view) && isset($loop) && is_numeric($view) && $loop) ? ' checked="checked"' : ''); ?>>
            <label for="<?php echo esc_attr($this->get_field_id('loop')); ?>"><?php
				/* translators: meaning for carousel loop, infinitely, without stopping */
				esc_attr_e('Loop ∞', 'g-business-reviews-rating'); ?></label>
            <input type="number" id="<?php echo esc_attr($this->get_field_id('interval')); ?>" class="small-text interval" name="<?php echo esc_attr($this->get_field_name('interval')); ?>" value="<?php echo esc_attr((isset($view) && isset($loop) && isset($interval) && is_numeric($view) && $loop && is_numeric($interval)) ? $interval : 7); ?>" placeholder="&mdash;" step="0.1" min="0.3" max="60">
            <label for="<?php echo esc_attr($this->get_field_id('interval')); ?>" class="<?php echo esc_attr((!isset($view) || !isset($loop) || !isset($interval) || !is_numeric($view) || !$loop) ? 'inactive' : ''); ?>">
<?php _e('Seconds per slide', 'g-business-reviews-rating'); ?></label>
        </p>
        <p class="offset">
			<label for="<?php echo esc_attr($this->get_field_name('offset')); ?>"><?php esc_attr_e('Review Offset:', 'g-business-reviews-rating'); ?></label>
			<input type="number" id="<?php echo esc_attr($this->get_field_id('offset')); ?>" class="small-text" name="<?php echo esc_attr($this->get_field_name('offset')); ?>" value="<?php echo esc_attr($offset); ?>" placeholder="&mdash;" step="1" min="0" max="<?php echo esc_attr(($count - 1)); ?>">
        </p>
        <p class="sort">
			<label for="<?php echo esc_attr($this->get_field_name('sort')); ?>"><?php esc_attr_e('Review Sort:', 'g-business-reviews-rating'); ?></label>
			<select id="<?php echo esc_attr($this->get_field_id('sort')); ?>" name="<?php echo esc_attr($this->get_field_name('sort')); ?>">
<?php
	foreach ($this->review_sort_options as $k => $a) :
?>
				<option value="<?php echo (($k == 'relevance_desc') ? '' : esc_attr($k)); ?>"<?php echo ($sort == $k || $k == 'relevance_desc' && $sort == NULL) ? ' selected' : ''; ?>><?php echo esc_attr($a['name'] . ((isset($a['min_max_values']) && is_array($a['min_max_values'])) ? ' (' . implode(' → ', $a['min_max_values']) . ')' : '')); ?></option>
<?php
	endforeach;
?>
			</select>
        </p>
        <p class="rating">
			<label for="<?php echo esc_attr($this->get_field_name('rating_min')); ?>"><?php esc_attr_e('Rating Range:', 'g-business-reviews-rating'); ?></label>
			<input type="number" id="<?php echo esc_attr($this->get_field_id('rating_min')); ?>" class="small-text" name="<?php echo esc_attr($this->get_field_name('rating_min')); ?>" value="<?php echo esc_attr((is_numeric($rating_min) && $rating_min > 1) ? $rating_min : 1); ?>" step="1" min="1" max="5"> &mdash;
			<input type="number" id="<?php echo esc_attr($this->get_field_id('rating_max')); ?>" class="small-text" name="<?php echo esc_attr($this->get_field_name('rating_max')); ?>" value="<?php echo esc_attr((is_numeric($rating_max) && $rating_max < 5) ? $rating_max : 5); ?>" step="1" min="1" max="5">
        </p>

        <p class="language">
            <label for="<?php echo esc_attr($this->get_field_name('language')); ?>"><?php esc_attr_e('Review Text Language:', 'g-business-reviews-rating'); ?></label>
            <select id="<?php echo esc_attr($this->get_field_id('language')); ?>" name="<?php echo esc_attr($this->get_field_name('language')); ?>">
                <option value=""<?php echo ($language == NULL) ? ' selected' : ''; ?>><?php esc_html_e('Any Language', 'g-business-reviews-rating'); ?></option>
<?php
	foreach ($this->languages as $k => $language_name) :
?>
                <option value="<?php echo esc_attr($k); ?>"<?php echo (($language == $k) ? ' selected' : ''); ?>><?php echo esc_html($language_name); ?></option>
<?php
	endforeach;
?>
            </select>
        </p>
        <p class="review-text-length">
			<label for="<?php echo esc_attr($this->get_field_name('review_text_min')); ?>"><?php esc_attr_e('Review Text Length Range:', 'g-business-reviews-rating'); ?></label>
			<input type="number" id="<?php echo esc_attr($this->get_field_id('review_text_min')); ?>" class="small-text" name="<?php echo esc_attr($this->get_field_name('review_text_min')); ?>" value="<?php echo esc_attr($review_text_min); ?>" step="1" min="0" max="4000"> &mdash;
			<input type="number" id="<?php echo esc_attr($this->get_field_id('review_text_max')); ?>" class="small-text" name="<?php echo esc_attr($this->get_field_name('review_text_max')); ?>" value="<?php echo esc_attr($review_text_max); ?>" step="1" min="0" max="4000">
        </p>
        <p class="excerpt-length">
			<label for="<?php echo esc_attr($this->get_field_name('excerpt_length')); ?>"><?php esc_attr_e('Review Excerpt Length:', 'g-business-reviews-rating'); ?></label>
			<input type="number" id="<?php echo esc_attr($this->get_field_id('excerpt_length')); ?>" class="small-text" name="<?php echo esc_attr($this->get_field_name('excerpt_length')); ?>" value="<?php echo esc_attr($excerpt_length); ?>" placeholder="&mdash;" step="1" min="20" max="4000">
        </p>
        <p class="more-text">
            <label for="<?php echo esc_attr($this->get_field_name('more')); ?>"><?php esc_attr_e('More Text:', 'g-business-reviews-rating'); ?></label>
            <input type="text" id="<?php echo esc_attr($this->get_field_id('more')); ?>" class="medium-text" name="<?php echo esc_attr($this->get_field_name('more')); ?>" value="<?php echo esc_attr($more); ?>">
        </p>
        <p class="display-options">
			<input class="checkbox display-name" type="checkbox" id="<?php echo esc_attr($this->get_field_id('display_name')); ?>" name="<?php echo esc_attr($this->get_field_name('display_name')); ?>" value="1"<?php echo (($display_name) ? ' checked="checked"' : ''); ?>> <label for="<?php echo esc_attr($this->get_field_id('display_name')); ?>"><?php esc_attr_e('Display business name', 'g-business-reviews-rating'); ?></label><br>
			<input class="checkbox display-icon" type="checkbox" id="<?php echo esc_attr($this->get_field_id('display_icon')); ?>" name="<?php echo esc_attr($this->get_field_name('display_icon')); ?>" value="1"<?php echo (($display_icon) ? ' checked="checked"' : ''); ?>> <label for="<?php echo esc_attr($this->get_field_id('display_icon')); ?>"><?php esc_attr_e('Display icon', 'g-business-reviews-rating'); ?></label><br>
			<input class="checkbox display-vicinity" type="checkbox" id="<?php echo esc_attr($this->get_field_id('display_vicinity')); ?>" name="<?php echo esc_attr($this->get_field_name('display_vicinity')); ?>" value="1"<?php echo ((!isset($display_vicinity) || $display_vicinity) ? ' checked="checked"' : ''); ?>> <label for="<?php echo esc_attr($this->get_field_id('display_vicinity')); ?>"><?php esc_attr_e('Display vicinity', 'g-business-reviews-rating'); ?></label><br>
			<input class="checkbox display-rating" type="checkbox" id="<?php echo esc_attr($this->get_field_id('display_rating')); ?>" name="<?php echo esc_attr($this->get_field_name('display_rating')); ?>" value="1"<?php echo (($display_rating) ? ' checked="checked"' : ''); ?>> <label for="<?php echo esc_attr($this->get_field_id('display_rating')); ?>"><?php esc_attr_e('Display rating', 'g-business-reviews-rating'); ?></label><br>
			<input class="checkbox display-rating-stars" type="checkbox" id="<?php echo esc_attr($this->get_field_id('display_rating_stars')); ?>" name="<?php echo esc_attr($this->get_field_name('display_rating_stars')); ?>" value="1"<?php echo (($display_rating_stars) ? ' checked="checked"' : ''); ?>> <label for="<?php echo esc_attr($this->get_field_id('display_rating_stars')); ?>"><?php esc_attr_e('Display rating stars', 'g-business-reviews-rating'); ?></label><br>
			<input class="checkbox display-review-count" type="checkbox" id="<?php echo esc_attr($this->get_field_id('display_review_count')); ?>" name="<?php echo esc_attr($this->get_field_name('display_review_count')); ?>" value="1"<?php echo (($display_review_count) ? ' checked="checked"' : ''); ?>> <label for="<?php echo esc_attr($this->get_field_id('display_review_count')); ?>"><?php esc_attr_e('Display review count', 'g-business-reviews-rating'); ?></label><br>
			<input class="checkbox display-reviews" type="checkbox" id="<?php echo esc_attr($this->get_field_id('display_reviews')); ?>" name="<?php echo esc_attr($this->get_field_name('display_reviews')); ?>" value="1"<?php echo (($display_reviews) ? ' checked="checked"' : ''); ?>> <label for="<?php echo esc_attr($this->get_field_id('display_reviews')); ?>"><?php esc_attr_e('Display reviews', 'g-business-reviews-rating'); ?></label><br>
			<input class="checkbox display-review-text" type="checkbox" id="<?php echo esc_attr($this->get_field_id('display_review_text')); ?>" name="<?php echo esc_attr($this->get_field_name('display_review_text')); ?>" value="1"<?php echo (($display_review_text) ? ' checked="checked"' : ''); ?>> <label for="<?php echo esc_attr($this->get_field_id('display_review_text')); ?>"><?php esc_attr_e('Display review text', 'g-business-reviews-rating'); ?></label><br>
			<input class="checkbox display-avatar" type="checkbox" id="<?php echo esc_attr($this->get_field_id('display_avatar')); ?>" name="<?php echo esc_attr($this->get_field_name('display_avatar')); ?>" value="1"<?php echo ((!isset($display_avatar) || $display_avatar) ? ' checked="checked"' : ''); ?>> <label for="<?php echo esc_attr($this->get_field_id('display_avatar')); ?>"><?php esc_attr_e('Display avatar', 'g-business-reviews-rating'); ?></label><br>
			<input class="checkbox display-view-reviews-button" type="checkbox" id="<?php echo esc_attr($this->get_field_id('display_view_reviews_button')); ?>" name="<?php echo esc_attr($this->get_field_name('display_view_reviews_button')); ?>" value="1"<?php echo (($display_view_reviews_button) ? ' checked="checked"' : ''); ?>> <label for="<?php echo esc_attr($this->get_field_id('display_view_reviews_button')); ?>"><?php esc_attr_e('Display view reviews button', 'g-business-reviews-rating'); ?></label><br>
			<input class="checkbox display-write-review-button" type="checkbox" id="<?php echo esc_attr($this->get_field_id('display_write_review_button')); ?>" name="<?php echo esc_attr($this->get_field_name('display_write_review_button')); ?>" value="1"<?php echo (($display_write_review_button) ? ' checked="checked"' : ''); ?>> <label for="<?php echo esc_attr($this->get_field_id('display_write_review_button')); ?>"><?php esc_attr_e('Display write review button', 'g-business-reviews-rating'); ?></label><br>
			<input class="checkbox display-attribution" type="checkbox" id="<?php echo esc_attr($this->get_field_id('display_attribution')); ?>" name="<?php echo esc_attr($this->get_field_name('display_attribution')); ?>" value="1"<?php echo (($display_attribution) ? ' checked="checked"' : ''); ?>> <label for="<?php echo esc_attr($this->get_field_id('display_attribution')); ?>"><?php esc_attr_e('Display attribution', 'g-business-reviews-rating'); ?></label><br>
			<input class="checkbox class-fill" type="checkbox" id="<?php echo esc_attr($this->get_field_id('class_fill')); ?>" name="<?php echo esc_attr($this->get_field_name('class_fill')); ?>" value="1"<?php echo (($class_fill) ? ' checked="checked"' : ''); ?>> <label for="<?php echo esc_attr($this->get_field_id('class_fill')); ?>"><?php esc_attr_e('Fill background', 'g-business-reviews-rating'); ?></label><br>
			<input class="checkbox animate" type="checkbox" id="<?php echo esc_attr($this->get_field_id('animate')); ?>" name="<?php echo esc_attr($this->get_field_name('animate')); ?>" value="1"<?php echo (($animate) ? ' checked="checked"' : ''); ?>> <label for="<?php echo esc_attr($this->get_field_id('animate')); ?>"><?php esc_attr_e('Animate rating stars', 'g-business-reviews-rating'); ?></label><br>
			<input class="checkbox stylesheet" type="checkbox" id="<?php echo esc_attr($this->get_field_id('stylesheet')); ?>" name="<?php echo esc_attr($this->get_field_name('stylesheet')); ?>" value="1"<?php echo (($stylesheet) ? ' checked="checked"' : ''); ?>> <label for="<?php echo esc_attr($this->get_field_id('stylesheet')); ?>"><?php esc_attr_e('Style Sheet active', 'g-business-reviews-rating'); ?></label>
		</p>
        <p class="buttons"><a href="<?php echo esc_attr($this->plugin_url); ?>" class="button button-secondary"><?php ($this->editor) ? esc_html_e('Reviews', 'g-business-reviews-rating') : esc_html_e('Settings', 'g-business-reviews-rating'); ?></a><?php echo ($this->demo) ? ' <a href="' . esc_attr($this->plugin_url) . '" class="demo"><span class="dashicons dashicons-warning"></span> ' . __('Demo Mode', 'g-business-reviews-rating') . '</a>' : ''; ?></p>
    </div>
