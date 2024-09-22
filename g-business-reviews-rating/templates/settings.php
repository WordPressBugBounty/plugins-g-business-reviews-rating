<?php

if (!defined('ABSPATH'))
{
	die();
}

?>
<div id="google-business-reviews-rating-settings" class="google-business-reviews-rating wrap banner">
	<h1><span><?php esc_html_e('Reviews and Rating - Google Reviews', 'g-business-reviews-rating'); ?></span><?php echo ($this->demo) ? ' <span class="demo"><span class="dashicons dashicons-warning"></span> ' . __('Demo Mode', 'g-business-reviews-rating') . '</span>' : ''; ?></h1>
    <p id="plugin-attribution"><span class="powered-by-google"></span></p>
    <p class="keyboard-navigation"><span class="dashicons dashicons-leftright" aria-hidden="true"></span> <?php /* translators: 1: Screen reader text start, 2: Screen reader text end */ 
        echo sprintf(__('%1$s(left and right arrow)%2$s keys navigate sections', 'g-business-reviews-rating'), '<span class="screen-reader-text">', '</span>'); ?></p>
    <nav class="nav-tab-wrapper wp-clearfix" aria-label="Secondary menu">
<?php if ($this->section != 'welcome'): ?>
<?php if ($this->administrator) : ?>
        <a href="#general" class="nav-tab general<?php echo ($this->section == NULL) ? ' nav-tab-active' : ''; ?>"><span class="icon"></span> <?php esc_html_e('General', 'g-business-reviews-rating'); ?></a>
<?php endif; ?>
<?php if ($this->administrator && $this->valid()): ?>
        <a href="#shortcodes" class="nav-tab shortcodes<?php echo ($this->section == 'shortcodes') ? ' nav-tab-active' : ''; ?>"><span class="icon"></span> <?php esc_html_e('Shortcodes', 'g-business-reviews-rating'); ?></a>
<?php endif; ?>
<?php if ($this->count_reviews_all >= 1): ?>
        <a href="#reviews" class="nav-tab reviews<?php echo ($this->administrator && $this->section == 'reviews' || $this->editor && $this->section != 'shortcodes' && $this->section != 'about') ? ' nav-tab-active' : ''; ?>"><span class="icon"></span> <?php esc_html_e('Reviews', 'g-business-reviews-rating'); ?> <span class="count"><?php echo esc_html($this->count_reviews_all); ?></span></a>
<?php endif; ?>
<?php if ($this->editor && $this->valid()): ?>
        <a href="#shortcodes" class="nav-tab shortcodes<?php echo ($this->section == 'shortcodes') ? ' nav-tab-active' : ''; ?>"><span class="icon"></span> <?php esc_html_e('Shortcodes', 'g-business-reviews-rating'); ?></a>
<?php endif; ?>
<?php if ($this->administrator && $this->retrieved_data_check()): ?>
        <a href="#data" class="nav-tab data<?php echo ($this->section == 'data') ? ' nav-tab-active' : ''; ?>"><span class="icon"></span> <?php esc_html_e('Retrieved Data', 'g-business-reviews-rating'); ?></a>
<?php endif; ?>
<?php if ($this->administrator) : ?>
        <a href="#advanced" class="nav-tab advanced<?php echo ($this->section == 'advanced') ? ' nav-tab-active' : ''; ?>"><span class="icon"></span> <?php esc_html_e('Advanced', 'g-business-reviews-rating'); ?></a>
<?php endif; ?>
        <a href="#about" class="nav-tab about<?php echo ($this->section == 'about') ? ' nav-tab-active' : ''; ?>"><span class="icon"></span> <?php esc_html_e('About', 'g-business-reviews-rating'); ?></a>
<?php endif; ?>
    </nav>

<?php if ($this->section == 'welcome') : ?>
	<div id="welcome" class="section" data-errors="<?php echo esc_attr(json_encode(array(__('A Google API Key and a Place ID are required', 'g-business-reviews-rating'), __('A Google API Key is required', 'g-business-reviews-rating'), __('A Place ID is required', 'g-business-reviews-rating'), __('The Google API Key and Place ID have different values', 'g-business-reviews-rating'), __('An unknown error has a occurred, please reload this page.', 'g-business-reviews-rating')))); ?>">
<?php if ($this->editor) : ?>
        <h2><?php esc_html_e('General', 'g-business-reviews-rating'); ?></h2>
        <p><?php /* translators: %s: a URL for a visual guide */ 
                echo sprintf(__('Please ask your administrator to set up this plugin using our <a href="%s" class="components-external-link" target="_blank">visual guide</a>.', 'g-business-reviews-rating'), 'https://designextreme.com/wordpress/gmbrr/#api-key'); ?></p>
<?php else : ?>
		<h2><?php esc_html_e('Google Credentials', 'g-business-reviews-rating'); ?></h2>
        <form method="post" action="options.php" id="google-business-reviews-rating-settings-welcome" data-nonce="<?php echo esc_attr(wp_create_nonce('gmbrr_nonce_' . $this->section)); ?>">
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="welcome-api-key"><?php esc_html_e('Google API Key', 'g-business-reviews-rating'); ?></label></th>
                    <td>
                        <p class="input">
                            <input type="text" id="welcome-api-key" class="regular-text code" name="google_business_reviews_rating_api_key" placeholder="<?php echo esc_attr(str_repeat('x', 40)); ?>" value="<?php echo esc_attr(get_option('google_business_reviews_rating_api_key')); ?>">
                        </p>
                        <p class="description<?php echo ((get_option('google_business_reviews_rating_api_key') == NULL) ? ' unset' : ''); ?>"><?php /* translators: 1: URL of Place ID Finder, 2: IP of the web server, 3: Help icon and reveal toggle link */ 
						echo sprintf(__('To retrieve data from Google My Business, you’ll need your <a href="%1$s" class="components-external-link" target="_blank">API Key</a> restricted to the API: <span class="highlight">Places API</span> and IP address: <span class="highlight">%2$s</span> %3$s', 'g-business-reviews-rating'), 'https://developers.google.com/maps/documentation/javascript/get-api-key', esc_html($this->server_ip()), ' <a id="welcome-google-credentials-help" href="#welcome-google-credentials-steps"><span class="dashicons dashicons-editor-help"></span></a>'); ?></p>
                        <ol id="welcome-google-credentials-steps">
							<li>
                        <?php /* translators: 1: URL of Google Developer Console, 2: URL of Place API, 3: URL of Google Developer Console, 4: IP of web server, 5: URL for Google billing account */
						echo preg_replace('/[\r\n]+/', '</li>' . PHP_EOL . str_repeat("\t", 7) . '<li>', sprintf(__('Create a new project or open an existing project in <a href="%1$s" class="components-external-link" target="_blank">Google Developer’s Console</a>
Search for <a href="%2$s" class="components-external-link" target="_blank">Places API</a> and click the button to enable this API in your account
In <a href="%3$s" class="components-external-link" target="_blank">Credentials</a>, click the button: “+ Create Credentials”
Select “API Key” from the options
Once this key is created, click “Close”
Select your newly created API Key
Under “Application restrictions”, set this to: “IP addresses” and “Add an item” with your web server’s IP: <span class="highlight">%4$s</span>
Under “API restrictions”, select “Restrict Key”, select just “Places API” from the list of options and click “OK”
Click “Save” to set the restrictions
Copy this new API Key to this plugin’s settings
Finally for regular requests, please <a href="%5$s" class="components-external-link" target="_blank">enable billing</a> for your project to receive your <em>substantial and free</em> API request allocation', 'g-business-reviews-rating'), 'https://console.developers.google.com/apis/credentials', 'https://console.cloud.google.com/apis/library/places-backend.googleapis.com?q=place', 'https://console.developers.google.com/apis/credentials', $this->server_ip(), 'https://console.cloud.google.com/projectselector/billing/enable')); ?></li>
                        </ol>
                        <p class="visual-guide"><?php /* translators: %s: a URL for a visual guide */ 
						echo sprintf(__('Would you follow this better with diagrams? Check out our <a href="%s" class="components-external-link" target="_blank">visual guide</a>.', 'g-business-reviews-rating'), 'https://designextreme.com/wordpress/gmbrr/#api-key'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="welcome-place-id"><?php esc_html_e('Google Place ID', 'g-business-reviews-rating'); ?></label></th>
                    <td>
                        <p class="input">
                            <input type="text" id="welcome-place-id" class="regular-text code" name="google_business_reviews_rating_place_id" pattern="[B-Za-z][0-9A-Za-z_\-]{15,125}[0-9A-Za-z]" placeholder="<?php echo esc_attr(str_repeat('x', 26)); ?>" value="<?php echo esc_attr(get_option('google_business_reviews_rating_place_id')); ?>">
<?php if (is_array($this->places) && count($this->places) == 1 && isset($this->data['result']['name']) && $this->data['result']['name'] != NULL): ?>
                            <input type="text" id="welcome-place-name" class="regular-text" name="place_name" value="<?php echo esc_attr($this->data['result']['name']); ?>"<?php echo ' style="width: calc(' . (strlen($this->data['result']['name']) + 1) . 'ch + 16px);"'; ?> disabled>
<?php endif; ?>
                        </p>
<?php if ($this->demo || !$this->valid() || (!isset($this->data['result']['url']) || isset($this->data['result']['url']) && $this->data['result']['url'] == NULL)): ?>
                        <p class="description"><?php /* translators: %s: the Google Place Finder URL */ 
						echo sprintf(__('You can find your unique Place ID by searching by your business’ name in <a href="%s" class="components-external-link" target="_blank">Google’s Place ID Finder</a>. Single business locations are accepted; coverage areas are not.', 'g-business-reviews-rating'), 'https://developers.google.com/places/place-id'); ?></p>
<?php else: ?>
                        <p class="description"><?php /* translators: %s: the Google Place Finder URL */ 
						echo sprintf(__('Find your business’ name in <a href="%s" class="components-external-link" target="_blank">Google’s Place ID Finder</a>; single business locations are accepted; coverage areas are not accepted.', 'g-business-reviews-rating'), 'https://developers.google.com/places/place-id'); ?></p>
<?php endif; ?>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="welcome-language"><?php esc_html_e('Preferred Review Language', 'g-business-reviews-rating'); ?></label></th>
                    <td>
                        <select id="welcome-language" name="google_business_reviews_rating_language">
                        <option value=""<?php echo (get_option('google_business_reviews_rating_language') == NULL) ? ' selected' : ''; ?>><?php esc_html_e('No Language Preference', 'g-business-reviews-rating'); ?></option>
<?php foreach ($this->languages as $k => $name) : ?>
                            <option value="<?php echo esc_attr($k); ?>"<?php echo (get_option('google_business_reviews_rating_language') == $k) ? ' selected' : ''; ?>><?php echo esc_attr($name); ?></option>
<?php endforeach; ?>
                        </select>
                        <label for="welcome-retrieval-translate"<?php echo (get_option('google_business_reviews_rating_language') == NULL) ? ' class="disabled"' : ''; ?>><input type="checkbox" id="welcome-retrieval-translate" name="google_business_reviews_rating_retrieval_translate" value="<?php echo esc_attr((is_numeric(get_option('google_business_reviews_rating_retrieval_translate')) && get_option('google_business_reviews_rating_retrieval_translate') >= 1) ? get_option('google_business_reviews_rating_retrieval_translate') : 1); ?>"<?php echo ((get_option('google_business_reviews_rating_language') != NULL && preg_match('/^(?:true|[1-9])$/i', get_option('google_business_reviews_rating_retrieval_translate', '1'))) ? ' checked="checked"' : '') . (get_option('google_business_reviews_rating_language') == NULL) ? ' disabled="disabled"' : ''; ?>> <?php esc_html_e('Translate reviews into this language.', 'g-business-reviews-rating'); ?></label>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="welcome-update"><?php esc_html_e('Update Frequency', 'g-business-reviews-rating'); ?></label></th>
                    <td>
                        <select id="welcome-update" name="google_business_reviews_rating_update">
<?php foreach ($this->updates as $k => $name) :
		if ($k == 24)
		{
			/* translators: %s: is the translation of “Synchronize Daily” */
			$name = sprintf(__('%s (Recommended)', 'g-business-reviews-rating'), $name);
		}
?>
                            <option value="<?php echo esc_attr($k); ?>"<?php echo (get_option('google_business_reviews_rating_update') == $k) ? ' selected' : ''; ?>><?php echo esc_attr($name); ?></option>
<?php endforeach; ?>
                        </select>
                    </td>
                </tr>
            </table>
            
            <p class="submit">
				<?php submit_button(NULL, 'primary', 'submit', FALSE, array('id' => 'welcome-save')); ?>
                <a href="#general" id="welcome-demo"><?php esc_html_e('Skip and enable Demo mode', 'g-business-reviews-rating'); ?></a>
            </p>
        </form>
	</div>
<?php endif; ?>
<?php endif; ?>

<?php if ($this->administrator) : ?>
    <div id="general" class="section<?php echo (($this->section != NULL) ? ' hide' : '') . ((preg_match('/\bdark\b/i', get_option('google_business_reviews_rating_reviews_theme'))) ? ' dark' : '') . ((preg_match('/\bfonts\b/i', get_option('google_business_reviews_rating_reviews_theme'))) ? ' fonts' : '') ?>"<?php echo ($this->data_hunter('boolean')) ? ' data-hunter="' . esc_attr($this->data_hunter('json')) . '"' : ''; ?>>
        <form method="post" action="options.php" id="google-business-reviews-rating-general" data-nonce="<?php echo esc_attr(wp_create_nonce('gmbrr_nonce')); ?>">
<?php
	settings_fields('google_business_reviews_rating_settings');
	do_settings_sections('google_business_reviews_rating_settings');
	
if ($this->valid()): ?>
            <nav class="section-bookmarks">
                <ul>
                    <li><a href="#general-reviews-rating"><?php esc_html_e('Reviews and Rating', 'g-business-reviews-rating'); ?></a></li>
                    <?php if (!$this->demo && $this->structured_data(TRUE)): ?>
                    <li><a href="#general-structured-data"><?php esc_html_e('Structured Data', 'g-business-reviews-rating'); ?></a></li>
                    <?php endif; ?>
                    <li><a href="#general-credentials"><?php esc_html_e('Google Credentials', 'g-business-reviews-rating'); ?></a></li>
                </ul>
            </nav>
<?php echo $this->notification(); ?>
            <h2 id="general-reviews-rating"><?php esc_html_e('Reviews and Rating', 'g-business-reviews-rating'); ?></h2>
            <p><?php _e('The general settings for your reviews and rating elements. Shortcode parameters will take precedence.', 'g-business-reviews-rating'); ?></p>
            <table id="reviews-rating-settings" class="form-table">
                <tr>
                    <th scope="row"><label for="reviews-theme"><?php esc_html_e('Theme', 'g-business-reviews-rating'); ?></label></th>
                    <td>
                        <select id="reviews-theme" name="google_business_reviews_rating_reviews_theme"<?php echo ((!preg_match('/^(?:[12]|(?:compress|minif[iy])(?:ed)?)$/i', get_option('google_business_reviews_rating_stylesheet', 1))) ? ' disabled="disabled"' : ''); ?>>
							<option value=""<?php echo (get_option('google_business_reviews_rating_reviews_theme') == NULL) ? ' selected' : ''; ?>><?php esc_html_e('Default', 'g-business-reviews-rating'); ?></option>
<?php foreach ($this->reviews_themes as $k => $name) : ?>
                            <option value="<?php echo esc_attr($k); ?>"<?php echo (get_option('google_business_reviews_rating_reviews_theme') == $k) ? ' selected' : ''; ?>><?php echo esc_attr($name); ?></option>
<?php endforeach; ?>
                        </select>
                        <p id="theme-recommendation-narrow" class="description"><?php _e('Recommended for narrow spaces such as a sidebar.', 'g-business-reviews-rating'); ?></p>
                        <p id="theme-recommendation-badge" class="description"><?php _e('We recommend hiding all reviews with this theme.', 'g-business-reviews-rating'); ?></p>
                        <p id="theme-recommendation-columns" class="description"><?php _e('We recommend matching the limit to multiples of columns.', 'g-business-reviews-rating'); ?></p>
                        <p id="theme-recommendation-bubble" class="description"><?php /* translators: %s: refers to a shortcode parameter */
						echo sprintf(__('We recommend using the shortcode parameter: <em>%s</em> for further customizations.', 'g-business-reviews-rating'), 'review_item_order'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="review-limit"><?php esc_html_e('Review Limit', 'g-business-reviews-rating'); ?></label></th>
                    <td>
                        <p class="input">
                            <input type="number" id="review-limit" class="small-text" name="google_business_reviews_rating_review_limit" value="<?php echo esc_attr(get_option('google_business_reviews_rating_review_limit')); ?>" placeholder="—" step="1" min="0" max="<?php echo ($this->count_reviews_all > 5) ? esc_attr(round($this->count_reviews_all * 1.2)) : 5; ?>">
                            <label for="review-limit-hide"><input type="radio" id="review-limit-hide" name="google_business_reviews_rating_review_limit_option" value="0"<?php echo (!$this->show_reviews) ? ' checked="checked"' : ''; ?>> <?php esc_html_e('Hide Reviews', 'g-business-reviews-rating'); ?></label>
                            <label for="review-limit-set"><input type="radio" id="review-limit-set" name="google_business_reviews_rating_review_limit_option" value="1"<?php echo ($this->show_reviews && is_numeric(get_option('google_business_reviews_rating_review_limit'))) ? ' checked="checked"' : ''; ?>> <?php esc_html_e('Show Limited Reviews', 'g-business-reviews-rating'); ?></label>
                            <label for="review-limit-all"><input type="radio" id="review-limit-all" name="google_business_reviews_rating_review_limit_option" value="all"<?php echo ($this->show_reviews && !is_numeric(get_option('google_business_reviews_rating_review_limit'))) ? ' checked="checked"' : ''; ?>> <?php esc_html_e('Show All Reviews', 'g-business-reviews-rating'); ?></label>
                        </p>
                        <p class="description"><?php /* translators: %u: refers number of reviews and must remain intact */
						printf(_n('You currently have %u active review retrieved from Google Places.', 'You currently have %u active reviews retrieved from Google Places (and imported).', $this->count_reviews_active, 'g-business-reviews-rating'), $this->count_reviews_active); ?></p>
                    </td>
                </tr>
                <tr class="show-reviews">
                    <th scope="row"><label for="carousel-view"><?php esc_html_e('Carousel View', 'g-business-reviews-rating'); ?></label></th>
                    <td>
                        <p class="input">
                    		<input type="number" id="carousel-view" class="small-text" name="google_business_reviews_rating_view" value="<?php echo esc_attr(get_option('google_business_reviews_rating_view')); ?>" placeholder="—" step="1" min="1" max="<?php echo ($this->count_reviews_all > 5) ? esc_attr(round($this->count_reviews_all * 1.2)) : 5; ?>"<?php echo (!$this->show_reviews) ? ' disabled="disabled"' : ''; ?>>
                        </p>
                        <p class="description"><?php _e('If set, will enable the carousel with a slide matching the number of reviews set in the view.', 'g-business-reviews-rating'); ?></p>
                    </td>
                </tr>
                <tr class="show-reviews">
                    <th scope="row"><label for="review-sort"><?php esc_html_e('Review Sort', 'g-business-reviews-rating'); ?></label></th>
                    <td>
                        <select id="review-sort" name="google_business_reviews_rating_review_sort"<?php echo (!$this->show_reviews) ? ' disabled="disabled"' : ''; ?>>
<?php foreach ($this->review_sort_options as $k => $a) : ?>
                            <option value="<?php echo (($k == 'relevance_desc') ? '' : esc_attr($k)); ?>"<?php echo (get_option('google_business_reviews_rating_review_sort') == $k || $k == 'relevance_desc' && get_option('google_business_reviews_rating_review_sort') == NULL) ? ' selected' : ''; ?>><?php echo esc_attr($a['name'] . ((isset($a['min_max_values']) && is_array($a['min_max_values'])) ? ' (' . implode(' → ', $a['min_max_values']) . ')' : '')); ?></option>
<?php endforeach ;?>
                        </select>
                    </td>
                </tr>
                <tr class="show-reviews">
                    <th scope="row"><label for="rating-min"><?php esc_html_e('Rating Range', 'g-business-reviews-rating'); ?></label></th>
                    <td>
                        <select id="rating-min" class="min" name="google_business_reviews_rating_rating_min"<?php echo (!$this->show_reviews) ? ' disabled="disabled"' : ''; ?>>
<?php for ($i = 1; $i <= 5; $i++) : ?>
                            <option value="<?php echo esc_attr($i); ?>"<?php echo (get_option('google_business_reviews_rating_rating_min') == $i || get_option('google_business_reviews_rating_rating_min') == NULL && $i == 1) ? ' selected' : ''; ?>><?php echo esc_attr($i); ?></option>
<?php endfor; ?>
                        </select> – 
                        <select id="rating-max" class="max" name="google_business_reviews_rating_rating_max"<?php echo (!$this->show_reviews) ? ' disabled="disabled"' : ''; ?>>
<?php for ($i = 1; $i <= 5; $i++) : ?>
                            <option value="<?php echo esc_attr($i); ?>"<?php echo (get_option('google_business_reviews_rating_rating_max') == $i || get_option('google_business_reviews_rating_rating_min') == NULL && $i == 5) ? ' selected' : ''; ?>><?php echo esc_attr($i); ?></option>
<?php endfor; ?>
                        </select>
                    </td>
                </tr>
                <tr class="show-reviews">
                    <th scope="row"><label for="rating-min"><?php esc_html_e('Review Text Length Range', 'g-business-reviews-rating'); ?></label></th>
                    <td>
                        <input type="number" id="review-text-min" class="min" name="google_business_reviews_rating_review_text_min" value="<?php echo esc_attr(get_option('google_business_reviews_rating_review_text_min')); ?>" placeholder="—" step="1" min="0"<?php echo (!$this->show_reviews) ? ' disabled="disabled"' : ''; ?>> – 
                        <input type="number" id="review-text-max" class="min" name="google_business_reviews_rating_review_text_max" value="<?php echo esc_attr(get_option('google_business_reviews_rating_review_text_max')); ?>" placeholder="—" step="1" min="0"<?php echo (!$this->show_reviews) ? ' disabled="disabled"' : ''; ?>> 
                    </td>
                </tr>
                <tr class="show-reviews">
                    <th scope="row"><?php esc_html_e('Review Excerpt Length', 'g-business-reviews-rating'); ?></th>
                    <td>
                        <p class="input">
                            <input type="number" id="review-text-excerpt-length" class="small-text" name="google_business_reviews_rating_review_text_excerpt_length" value="<?php echo esc_attr(get_option('google_business_reviews_rating_review_text_excerpt_length')); ?>" placeholder="—" step="1" min="20"<?php echo (!$this->show_reviews) ? ' disabled="disabled"' : ''; ?>>
                        </p>
                        <p class="description"><?php /* translators: %s: refers to a HTML ID, leave unchanged */
						echo sprintf(__('The characters displayed before a <a href="%s" class="void">… More</a> toggle is shown to reveal the full review text. Leave empty for no excerpt.', 'g-business-reviews-rating'), '#review-text-excerpt-length'); ?></p>
                    </td>
                </tr>
                <tr id="color-schemes">
                    <th scope="row"><label for="color-scheme"><?php esc_html_e('Color Scheme', 'g-business-reviews-rating'); ?></label></th>
                    <td>
                    	<label class="<?php echo 'default' . ((get_option('google_business_reviews_rating_color_scheme') == NULL) ? ' selected' : ''); ?>" for="color-scheme"><input type="radio" id="color-scheme" name="google_business_reviews_rating_color_scheme" value=""<?php echo (get_option('google_business_reviews_rating_color_scheme') == NULL) ? ' checked="checked"' : ''; ?>> <?php _e('None', 'g-business-reviews-rating'); ?></label>
<?php foreach ($this->color_schemes as $k => $name) : ?>
                    	<label class="<?php echo esc_attr($k) . ((get_option('google_business_reviews_rating_color_scheme') == $k) ? ' selected' : ''); ?>" for="<?php echo esc_attr('color-scheme-' . $k); ?>"><input type="radio" id="<?php echo esc_attr('color-scheme-' . $k); ?>" name="google_business_reviews_rating_color_scheme" value="<?php echo esc_attr($k); ?>"<?php echo (get_option('google_business_reviews_rating_color_scheme') == $k) ? ' checked' : ''; ?>><?php echo esc_html($name); ?></label>
<?php endforeach; ?>
</td>
                </tr>
                <tr<?php echo ((get_option('google_business_reviews_rating_icon') == NULL) ? ' class="empty"' : ''); ?>>
                    <th scope="row"><?php esc_html_e('Icon', 'g-business-reviews-rating'); ?></th>
                    <td>
                        <p class="business-icon-image<?php echo (get_option('google_business_reviews_rating_icon') == NULL) ? ' empty' : ''; ?>">
                            <span id="icon-image-preview" class="image thumbnail"><?php echo (get_option('google_business_reviews_rating_icon') != NULL) ? preg_replace('/\s+(?:width|height)="\d*"/i', '', wp_get_attachment_image($this->icon_image_id, 'large')) : ''; ?></span>
                            <span class="set"><button type="button" id="icon-image" class="button button-secondary ui-button" name="icon-image" value="1" data-set-text="<?php esc_attr_e('Choose Image', 'g-business-reviews-rating'); ?>" data-replace-text="<?php esc_attr_e('Replace', 'g-business-reviews-rating'); ?>"><span class="dashicons dashicons-format-image"></span> <?php echo (get_option('google_business_reviews_rating_icon') == NULL) ? esc_attr(__('Choose Image', 'g-business-reviews-rating')) : esc_attr(__('Replace', 'g-business-reviews-rating')); ?></button></span>
                            <span class="delete"<?php echo (get_option('google_business_reviews_rating_icon') == NULL) ? ' style="display: none;"' : ''; ?>><button type="button" id="icon-image-delete" class="button button-secondary ui-button" name="icon-image-delete" value="1"><span class="dashicons dashicons-no"></span> Remove</button></span>
                            <input type="hidden" id="icon-image-id" name="google_business_reviews_rating_icon" value="<?php echo esc_attr($this->icon_image_id); ?>">
                        </p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label><?php esc_html_e('Shortcode', 'g-business-reviews-rating'); ?></label></th>
                    <td>
                        <span id="shortcode-short" class="shortcode" contenteditable="true">[reviews_rating]</span>
                        <p class="description"><?php /* translators: %s: refers to the shortcode section bookmark */
						echo sprintf(__('View all <a href="%s">shortcodes and parameters</a> for further customization.', 'g-business-reviews-rating'), '#shortcodes'); ?></p>
                    </td>
                </tr>
            </table>
            <h2 id="reviews-rating-preview-heading" class="hide"><a href="reviews-rating-preview"><span class="dashicons dashicons-arrow-right"></span> <?php esc_html_e('Preview', 'g-business-reviews-rating'); ?></a></h2>
            <div id="reviews-rating-preview" class="google-business-reviews-rating-preview hide<?php echo esc_attr(((get_option('google_business_reviews_rating_reviews_theme') != NULL) ? ' ' . get_option('google_business_reviews_rating_reviews_theme') : '') . ((get_option('google_business_reviews_rating_color_scheme') != NULL) ? ' ' . get_option('google_business_reviews_rating_color_scheme') : '')); ?>">
            </div>

<?php else: ?>
            <input type="hidden" id="review-limit" name="google_business_reviews_rating_review_limit" value="<?php echo esc_attr(get_option('google_business_reviews_rating_review_limit')); ?>">
            <input type="hidden" id="rating-min" name="google_business_reviews_rating_rating_min" value="<?php echo esc_attr(get_option('google_business_reviews_rating_rating_min')); ?>">
            <input type="hidden" id="rating-max" name="google_business_reviews_rating_rating_max" value="<?php echo esc_attr(get_option('google_business_reviews_rating_rating_max')); ?>">
            <input type="hidden" id="review-text-min" name="google_business_reviews_rating_review_text_min" value="<?php echo esc_attr(get_option('google_business_reviews_rating_review_text_min')); ?>">
            <input type="hidden" id="review-text-max" name="google_business_reviews_rating_review_text_max" value="<?php echo esc_attr(get_option('google_business_reviews_rating_review_text_max')); ?>">
            <input type="hidden" id="review-text-excerpt-length" name="google_business_reviews_rating_review_text_excerpt_length" value="<?php echo esc_attr(get_option('google_business_reviews_rating_review_text_excerpt_length')); ?>">
            <input type="hidden" id="reviews-theme" name="google_business_reviews_rating_reviews_theme" value="<?php echo esc_attr(get_option('google_business_reviews_rating_reviews_theme')); ?>">
            <input type="hidden" id="retrieval-sort" name="google_business_reviews_rating_retrieval_sort" value="<?php echo esc_attr(get_option('google_business_reviews_rating_retrieval_sort')); ?>">
            <input type="hidden" id="icon" name="google_business_reviews_rating_icon" value="<?php echo esc_attr(get_option('google_business_reviews_rating_icon')); ?>">
            <input type="hidden" id="structured-data" name="google_business_reviews_rating_structured_data" value="<?php echo esc_attr(get_option('google_business_reviews_rating_structured_data')); ?>">
            <input type="hidden" id="telephone" name="google_business_reviews_rating_telephone" value="<?php echo esc_attr(get_option('google_business_reviews_rating_telephone')); ?>">
            <input type="hidden" id="business-type" name="google_business_reviews_rating_business_type" value="<?php echo esc_attr(get_option('google_business_reviews_rating_business_type')); ?>">
            <input type="hidden" id="price-range" name="google_business_reviews_rating_price_range" value="<?php echo esc_attr(get_option('google_business_reviews_rating_price_range')); ?>">
            <input type="hidden" id="logo" name="google_business_reviews_rating_logo" value="<?php echo esc_attr(get_option('google_business_reviews_rating_logo')); ?>">
<?php endif; ?>

<?php if (!$this->demo && $this->valid() && $this->structured_data(TRUE)): ?>
            <h2 id="general-structured-data"><?php esc_html_e('Structured Data', 'g-business-reviews-rating'); ?></h2>
            <p><?php /* translators: %s: refers to Schema URL and name, leave unchanged */ 
			echo sprintf(__('Allow search engines to easily read review data for your website using Structured Data %s which includes general business information and recent, relevant and visible reviews.', 'g-business-reviews-rating'), '(<a href="//schema.org" class="components-external-link" target="_blank">Schema.org</a>)'); ?></p>
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="structured-data"><?php esc_html_e('Structured Data', 'g-business-reviews-rating'); ?></label></th>
                    <td>
                        <p>
                            <label for="structured-data"><input type="checkbox" id="structured-data" name="google_business_reviews_rating_structured_data" value="<?php echo esc_attr((is_numeric(get_option('google_business_reviews_rating_structured_data')) && get_option('google_business_reviews_rating_structured_data') != 0) ? get_option('google_business_reviews_rating_structured_data') : 1); ?>"<?php echo ($this->count_reviews_active == 0) ? ' disabled="disabled"' : ''; ?><?php echo (intval(get_option('google_business_reviews_rating_structured_data')) != 0) ? ' checked="checked"' : ''; ?><?php echo ($this->count_reviews_active == 0) ? ' disabled="disabled"' : ''; ?>>
<?php if (is_numeric(get_option('google_business_reviews_rating_structured_data')) && get_option('google_business_reviews_rating_structured_data') > 1) : ?>
                            <?php /* translators: %s: refers to the title of a specific page */ 
			                    printf(esc_html__('Enable and insert Structured Data on a specific page: %s.', 'g-business-reviews-rating'), get_the_title(get_option('google_business_reviews_rating_structured_data'))); ?>
<?php elseif (is_numeric(get_option('google_business_reviews_rating_structured_data')) && get_option('google_business_reviews_rating_structured_data') == -1) : ?>
                            <?php esc_html_e('Enable and insert Structured Data on all pages.', 'g-business-reviews-rating'); ?>
<?php else: ?>
                            <?php esc_html_e('Enable and insert Structured Data on the front page.', 'g-business-reviews-rating'); ?>
<?php endif; ?>
                            </label>
                            <button type="button" name="structured-data-preview" id="structured-data-preview" class="button button-secondary structured-data"<?php echo (get_option('google_business_reviews_rating_structured_data') ? '' : ' style="display: none"'); ?>><span class="dashicons dashicons-text-page"></span> <?php esc_html_e('Preview', 'g-business-reviews-rating'); ?></button>
						</p>
                    </td>
                </tr>
                <tr class="structured-data"<?php echo (get_option('google_business_reviews_rating_structured_data') ? '' : ' style="display: none"'); ?>>
                    <th scope="row"><label for="telephone"><?php esc_html_e('Telephone', 'g-business-reviews-rating'); ?></label></th>
                    <td>
                        <input type="tel" id="telephone" name="google_business_reviews_rating_telephone" placeholder="—" value="<?php echo esc_attr(get_option('google_business_reviews_rating_telephone')); ?>">
                    </td>
                </tr>
                <tr class="structured-data"<?php echo (get_option('google_business_reviews_rating_structured_data') ? '' : ' style="display: none"'); ?>>
                    <th scope="row"><label for="business-type"><?php esc_html_e('Business Type', 'g-business-reviews-rating'); ?></label></th>
                    <td>
                        <select id="business-type" name="google_business_reviews_rating_business_type">
                            <optgroup label="<?php esc_attr_e('Local Business', 'g-business-reviews-rating'); ?>" data-type="LocalBusiness">
                                <option value=""<?php echo (get_option('google_business_reviews_rating_business_type') == NULL) ? ' selected' : ''; ?>><?php esc_html_e('Not Applicable/Other', 'g-business-reviews-rating'); ?></option>
<?php foreach ($this->business_types as $k => $name) : ?>
                                <option value="<?php echo esc_attr($k); ?>"<?php echo (get_option('google_business_reviews_rating_business_type') == $k) ? ' selected' : ''; ?>><?php echo esc_attr($name); ?></option>
<?php endforeach; ?>
							</optgroup>
                            <optgroup label="<?php esc_attr_e('Airline', 'g-business-reviews-rating'); ?>" data-type="Airline">
                                <option value="" disabled><?php esc_html_e('Structured Data Not Available', 'g-business-reviews-rating'); ?></option>
                            </optgroup>
                            <optgroup label="<?php esc_attr_e('Consortium', 'g-business-reviews-rating'); ?>" data-type="Consortium">
                                <option value="" disabled><?php esc_html_e('Structured Data Not Available', 'g-business-reviews-rating'); ?></option>
                            </optgroup>
                            <optgroup label="<?php esc_attr_e('Corporation', 'g-business-reviews-rating'); ?>" data-type="Corporation">
                                <option value="" disabled><?php esc_html_e('Structured Data Not Available', 'g-business-reviews-rating'); ?></option>
                            </optgroup>
                            <optgroup label="<?php esc_attr_e('Educational Organization', 'g-business-reviews-rating'); ?>" data-type="EducationalOrganization">
                                <option value="" disabled><?php esc_html_e('Structured Data Not Available', 'g-business-reviews-rating'); ?></option>
                            </optgroup>
                            <optgroup label="<?php esc_attr_e('Funding Scheme', 'g-business-reviews-rating'); ?>" data-type="FundingScheme">
                                <option value="" disabled><?php esc_html_e('Structured Data Not Available', 'g-business-reviews-rating'); ?></option>
                            </optgroup>
                            <optgroup label="<?php esc_attr_e('Government Organization', 'g-business-reviews-rating'); ?>" data-type="GovernmentOrganization">
                                <option value="" disabled><?php esc_html_e('Structured Data Not Available', 'g-business-reviews-rating'); ?></option>
                            </optgroup>
                            <optgroup label="<?php esc_attr_e('Library System', 'g-business-reviews-rating'); ?>" data-type="LibrarySystem">
                                <option value="" disabled><?php esc_html_e('Structured Data Not Available', 'g-business-reviews-rating'); ?></option>
                            </optgroup>
                            <optgroup label="<?php esc_attr_e('Medical Organization', 'g-business-reviews-rating'); ?>" data-type="MedicalOrganization">
                                <option value="" disabled><?php esc_html_e('Structured Data Not Available', 'g-business-reviews-rating'); ?></option>
                            </optgroup>
                            <optgroup label="<?php esc_attr_e('NGO', 'g-business-reviews-rating'); ?>" data-type="NGO">
                                <option value="" disabled><?php esc_html_e('Structured Data Not Available', 'g-business-reviews-rating'); ?></option>
                            </optgroup>
                            <optgroup label="<?php esc_attr_e('News Media Organization', 'g-business-reviews-rating'); ?>" data-type="NewsMediaOrganization">
                                <option value="" disabled><?php esc_html_e('Structured Data Not Available', 'g-business-reviews-rating'); ?></option>
                            </optgroup>
                            <optgroup label="<?php esc_attr_e('Performing Group', 'g-business-reviews-rating'); ?>" data-type="PerformingGroup">
                                <option value="" disabled><?php esc_html_e('Structured Data Not Available', 'g-business-reviews-rating'); ?></option>
                            </optgroup>
                            <optgroup label="<?php esc_attr_e('Project', 'g-business-reviews-rating'); ?>" data-type="Project">
                                <option value="" disabled><?php esc_html_e('Structured Data Not Available', 'g-business-reviews-rating'); ?></option>
                            </optgroup>
                            <optgroup label="<?php esc_attr_e('Sports Organization', 'g-business-reviews-rating'); ?>" data-type="SportsOrganization">
                                <option value="" disabled><?php esc_html_e('Structured Data Not Available', 'g-business-reviews-rating'); ?></option>
                            </optgroup>
                            <optgroup label="<?php esc_attr_e('Workers Union', 'g-business-reviews-rating'); ?>" data-type="WorkersUnion">
                                <option value="" disabled><?php esc_html_e('Structured Data Not Available', 'g-business-reviews-rating'); ?></option>
                            </optgroup>
                        </select>
                    </td>
                </tr>
                <tr class="structured-data"<?php echo (get_option('google_business_reviews_rating_structured_data') ? '' : ' style="display: none"'); ?>>
                    <th scope="row"><label for="price-range"><?php esc_html_e('Price Range', 'g-business-reviews-rating'); ?></label></th>
                    <td>
                        <select id="price-range" name="google_business_reviews_rating_price_range">
                            <option value=""<?php echo (get_option('google_business_reviews_rating_price_range') == NULL) ? ' selected' : ''; ?>><?php esc_html_e('Not Applicable', 'g-business-reviews-rating'); ?></option>
<?php foreach ($this->price_ranges as $k => $a) : ?>
                            <option value="<?php echo esc_attr($k); ?>"<?php echo (get_option('google_business_reviews_rating_price_range') == $k) ? ' selected' : ''; ?>><?php echo esc_html($a['name']); ?></option>
<?php endforeach; ?>
                        </select>
                    </td>
                </tr>
                <tr id="logo-image-row" class="structured-data<?php echo ((get_option('google_business_reviews_rating_logo') == NULL) ? ' empty' : ''); ?>"<?php echo ((get_option('google_business_reviews_rating_structured_data') ? '' : ' style="display: none"')); ?>>
                    <th scope="row"><?php esc_html_e('Logo', 'g-business-reviews-rating'); ?></th>
                    <td>
                        <p class="logo-image<?php echo (get_option('google_business_reviews_rating_logo') == NULL) ? ' empty' : ''; ?>">
                            <span id="logo-image-preview" class="image thumbnail"><?php echo (get_option('google_business_reviews_rating_logo') != NULL) ? preg_replace('/\s+(?:width|height)="\d*"/i', '', wp_get_attachment_image($this->logo_image_id, 'large')) : ''; ?></span>
                            <span class="set"><button type="button" id="logo-image" class="button button-secondary ui-button" name="logo-image" value="1" data-set-text="<?php esc_attr_e('Choose Image', 'g-business-reviews-rating'); ?>" data-replace-text="<?php esc_attr_e('Replace', 'g-business-reviews-rating'); ?>"><span class="dashicons dashicons-format-image"></span> <?php echo (get_option('google_business_reviews_rating_logo') == NULL) ? esc_attr(__('Choose Image', 'g-business-reviews-rating')) : esc_attr(__('Replace', 'g-business-reviews-rating')); ?></button></span>
                            <span class="delete"<?php echo (get_option('google_business_reviews_rating_logo') == NULL) ? ' style="display: none;"' : ''; ?>><button type="button" id="logo-image-delete" class="button button-secondary ui-button" name="logo-image-delete" value="1"><span class="dashicons dashicons-no"></span> Remove</button></span>
                            <input type="hidden" id="logo-image-id" name="google_business_reviews_rating_logo" value="<?php echo esc_attr($this->logo_image_id); ?>">
                        </p>
                    </td>
                </tr>
            </table>

<?php endif; ?>
            <h2 id="general-credentials"><?php esc_html_e('Google Credentials', 'g-business-reviews-rating'); ?></h2>
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="api-key"><?php esc_html_e('Google API Key', 'g-business-reviews-rating'); ?></label></th>
                    <td>
                        <p class="input">
                            <input type="text" id="api-key" class="regular-text code" name="google_business_reviews_rating_api_key" placeholder="<?php echo esc_attr(str_repeat('x', 40)); ?>" value="<?php echo esc_attr(get_option('google_business_reviews_rating_api_key')); ?>">
                        </p>
                        <p class="description<?php echo ((get_option('google_business_reviews_rating_api_key') == NULL) ? ' unset' : ''); ?>"><?php /* translators: 1: URL of Place ID Finder, 2: IP of the web server, 3: Help icon and reveal toggle link */ 
						echo sprintf(__('To retrieve data from Google My Business, you’ll need your <a href="%1$s" class="components-external-link" target="_blank">API Key</a> restricted to the API: <span class="highlight">Places API</span> and IP address: <span class="highlight">%2$s</span> %3$s', 'g-business-reviews-rating'), 'https://developers.google.com/maps/documentation/javascript/get-api-key', esc_html($this->server_ip()), ' <a id="google-credentials-help" href="#google-credentials-steps"><span class="dashicons dashicons-editor-help"></span></a>'); ?></p>
                        <ol id="google-credentials-steps">
							<li><?php /* translators: 1: URL of Google Developer Console, 2: URL of Place API, 3: URL of Google Developer Console, 4: IP of web server, 5: URL for Google billing account */
						echo preg_replace('/[\r\n]+/', '</li>' . PHP_EOL . str_repeat("\t", 7) . '<li>', sprintf(__('Create a new project or open an existing project in <a href="%1$s" class="components-external-link" target="_blank">Google Developer’s Console</a>
Search for <a href="%2$s" class="components-external-link" target="_blank">Places API</a> and click the button to enable this API in your account
In <a href="%3$s" class="components-external-link" target="_blank">Credentials</a>, click the button: “+ Create Credentials”
Select “API Key” from the options
Once this key is created, click “Close”
Select your newly created API Key
Under “Application restrictions”, set this to: “IP addresses” and “Add an item” with your web server’s IP: <span class="highlight">%4$s</span>
Under “API restrictions”, select “Restrict Key”, select just “Places API” from the list of options and click “OK”
Click “Save” to set the restrictions
Copy this new API Key to this plugin’s settings
Finally for regular requests, please <a href="%5$s" class="components-external-link" target="_blank">enable billing</a> for your project to receive your <em>substantial and free</em> API request allocation', 'g-business-reviews-rating'), 'https://console.developers.google.com/apis/credentials', 'https://console.cloud.google.com/apis/library/places-backend.googleapis.com?q=place', 'https://console.developers.google.com/apis/credentials', $this->server_ip(), 'https://console.cloud.google.com/projectselector/billing/enable')); ?></li>
                        </ol>
                        <p class="visual-guide"><?php /* translators: %s: a URL for a visual guide */ 
						echo sprintf(__('Would you follow this better with diagrams? Check out our <a href="%s" class="components-external-link" target="_blank">visual guide</a>.', 'g-business-reviews-rating'), 'https://designextreme.com/wordpress/gmbrr/#api-key'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="place-id"><?php echo esc_html(_n('Google Place ID', 'Google Places', ((is_array($this->places)) ? count($this->places) : 1)), 'g-business-reviews-rating'); ?></label></th>
                    <td>
                        <div class="input">
<?php if (!is_array($this->places) || is_array($this->places) && count($this->places) <= 1): ?>
                            <input type="text" id="place-id" class="regular-text code" name="google_business_reviews_rating_place_id" pattern="[B-Za-z][0-9A-Za-z_\-]{15,125}[0-9A-Za-z]" placeholder="<?php echo esc_attr(str_repeat('x', 26)); ?>" value="<?php echo esc_attr(get_option('google_business_reviews_rating_place_id')); ?>">
<?php endif; ?>
<?php if (is_array($this->places) && count($this->places) == 1 && isset($this->data['result']['name']) && $this->data['result']['name'] != NULL): ?>
                            <input type="text" id="place-name" class="regular-text" name="place_name" value="<?php echo esc_attr($this->data['result']['name']); ?>"<?php echo ' style="width: calc(' . (strlen($this->data['result']['name']) + 1) . 'ch + 16px);"'; ?> disabled>
<?php elseif (is_array($this->places) && count($this->places) > 1): ?>
                            <ul id="google-places" class="google-places">
                                <li class="header">
                                    <span class="current"><?php esc_html_e('Current', 'g-business-reviews-rating'); ?></span>
                                    <span class="name"><?php esc_html_e('Name', 'g-business-reviews-rating'); ?></span>
                                    <span class="delete empty"></span>
                                    <span class="place-id"><?php esc_html_e('Place ID', 'g-business-reviews-rating'); ?></span>
                                    <span class="rating"><?php esc_html_e('Rating', 'g-business-reviews-rating'); ?></span>
                                    <span class="reviews"><?php esc_html_e('Reviews', 'g-business-reviews-rating'); ?></span>
                                    <span class="added"><?php esc_html_e('Added', 'g-business-reviews-rating'); ?></span>
                                </li>
<?php foreach ($this->places as $i => $a) : ?>
<?php if ((!isset($a['name']) || $a['name'] == NULL) && get_option('google_business_reviews_rating_place_id') != $a['place_id']) : ?>
<?php continue; ?>
<?php endif; ?>
                                <li class="place-id<?php echo (get_option('google_business_reviews_rating_place_id') == $a['place_id']) ? ' current' : ''; ?>" data-place-id="<?php echo esc_attr($a['place_id']); ?>">
                                    <label for="<?php echo esc_attr('place-id-' . ($i + 1)); ?>" class="current">
                                        <input type="radio" id="<?php echo esc_attr('place-id-' . ($i + 1)); ?>" name="google_business_reviews_rating_place_id" value="<?php echo esc_attr($a['place_id']); ?>"<?php echo ((get_option('google_business_reviews_rating_place_id') == $a['place_id']) ? ' checked="checked"' : ''); ?>>
                                    </label>
                                    <label for="<?php echo esc_attr('place-id-' . ($i + 1)); ?>" class="name" title="<?php echo esc_attr((isset($a['name']) && $a['name'] != NULL) ? $a['name'] . ((isset($a['vicinity']) && $a['vicinity'] != NULL) ? ' — ' . $a['vicinity'] : '') : __('Unknown Place', 'g-business-reviews-rating')); ?>">
                                        <?php echo esc_html((isset($a['name']) && $a['name'] != NULL) ? $a['name'] : esc_html__('Unknown Place', 'g-business-reviews-rating')); ?>
                                        <?php echo (get_option('google_business_reviews_rating_place_id') == $a['place_id']) ? '<span class="dashicons dashicons-yes-alt" placeholder="' . esc_attr__('Current Place ID', 'g-business-reviews-rating') . '>"></span>' : ''; ?>
                                    </label>
<?php if (get_option('google_business_reviews_rating_place_id') != $a['place_id']) : ?>
                                    <label for="<?php echo esc_attr('place-delete-' . ($i + 1)); ?>" class="delete" title="<?php echo esc_attr('Delete Place and Reviews', 'g-business-reviews-rating'); ?>">
                                        <input type="checkbox" id="<?php echo esc_attr('place-delete-' . ($i + 1)); ?>" name="google_business_reviews_rating_place_delete[]" value="<?php echo esc_attr($a['place_id']); ?>">
                                        <span class="dashicons dashicons-trash"></span>
                                    </label>
<?php else: ?>
                                    <span class="delete empty"></span>
<?php endif; ?>
                                    <span class="place-id">
                                        <code><?php echo esc_html($a['place_id']); ?></code>
                                    </span>
                                    <span class="rating"><?php echo (isset($a['rating']) && is_numeric($a['rating'])) ? esc_html(number_format_i18n($a['rating'], 1)) : '—'; ?></span>
                                    <span class="reviews"><?php echo (isset($a['rating_count']) && is_numeric($a['rating_count'])) ? number_format_i18n($this->reviews_count($a['place_id']), 0) . '/' . esc_html(number_format_i18n($a['rating_count'], 0)) : '—'; ?></span>
                                    <span class="added"><?php echo (isset($a['time']) && is_numeric($a['time'])) ? esc_html(wp_date(get_option('date_format'), $a['time'])) : '—'; ?></span>
                                </li>
<?php endforeach; ?>
                                <li class="place-id new<?php echo (get_option('google_business_reviews_rating_place_id') == NULL) ? ' current' : ''; ?>">
                                    <label for="place-id-new" class="current">
                                        <input type="radio" id="place-id-new" name="google_business_reviews_rating_place_id" value=""<?php echo ((get_option('google_business_reviews_rating_place_id') == NULL) ? ' checked="checked"' : ''); ?>>
                                    </label>
                                    <label for="<?php echo esc_attr('place-id-' . ($i + 1)); ?>" class="place-id">
                                        <input type="text" id="place-id" class="regular-text code" name="google_business_reviews_rating_place_id_new" pattern="[B-Za-z][0-9A-Za-z_\-]{15,125}[0-9A-Za-z]" placeholder="<?php esc_attr_e('Place ID', 'g-business-reviews-rating'); ?>" value="">
                                    </label>
                                </li>
                            </ul>
<?php endif; ?>
                            <p id="google-places-delete" class="google-places-delete hide">
                                <label>
                                    <input type="checkbox" id="<?php echo esc_attr('place-delete-' . ($i + 1)); ?>" name="google_business_reviews_rating_place_delete[]" value="confirm">
                                    <?php esc_html_e('Delete selected places and reviews', 'g-business-reviews-rating'); ?>
                                </label>
                            </p>
                        </div>
<?php if ($this->demo || !$this->valid() || (!isset($this->data['result']['url']) || isset($this->data['result']['url']) && $this->data['result']['url'] == NULL)): ?>
                        <p class="description"><?php /* translators: %s: the Google Place Finder URL */ 
						echo sprintf(__('You can find your unique Place ID by searching by your business’ name in <a href="%s" class="components-external-link" target="_blank">Google’s Place ID Finder</a>. Single business locations are accepted; coverage areas are not accepted.', 'g-business-reviews-rating'), 'https://developers.google.com/places/place-id'); ?></p>
<?php else: ?>
                        <p class="description"><?php /* translators: 1: the Google Place Finder URL, 2: the URL of the business in Google Maps */ 
						echo sprintf(__('Find your business’ name in <a href="%1$s" class="components-external-link" target="_blank">Google’s Place ID Finder</a>; single business locations are accepted; coverage areas are not. You may edit the business’ name in <a href="%2$s" class="components-external-link" target="_blank">Google Maps</a>.', 'g-business-reviews-rating'), 'https://developers.google.com/places/place-id', esc_attr($this->data['result']['url'])); ?></p>
<?php endif; ?>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="update"><?php esc_html_e('Update Frequency', 'g-business-reviews-rating'); ?></label></th>
                    <td>
                        <select id="update" name="google_business_reviews_rating_update">
<?php foreach ($this->updates as $k => $name) : ?>
                            <option value="<?php echo esc_attr($k); ?>"<?php echo (get_option('google_business_reviews_rating_update') == $k) ? ' selected' : ''; ?>><?php echo esc_attr($name); ?></option>
<?php endforeach; ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="language"><?php esc_html_e('Preferred Review Language', 'g-business-reviews-rating'); ?></label></th>
                    <td>
                        <select id="language" name="google_business_reviews_rating_language">
                        <option value=""<?php echo (get_option('google_business_reviews_rating_language') == NULL) ? ' selected' : ''; ?>><?php esc_html_e('None', 'g-business-reviews-rating'); ?></option>
<?php foreach ($this->languages as $k => $name) : ?>
                            <option value="<?php echo esc_attr($k); ?>"<?php echo (get_option('google_business_reviews_rating_language') == $k) ? ' selected' : ''; ?>><?php echo esc_attr($name); ?></option>
<?php endforeach; ?>
                        </select>
                        <label for="retrieval-translate"<?php echo (get_option('google_business_reviews_rating_language') == NULL) ? ' class="disabled"' : ''; ?>><input type="checkbox" id="retrieval-translate" name="google_business_reviews_rating_retrieval_translate" value="<?php echo esc_attr((is_numeric(get_option('google_business_reviews_rating_retrieval_translate')) && get_option('google_business_reviews_rating_retrieval_translate') >= 1) ? get_option('google_business_reviews_rating_retrieval_translate') : 1); ?>"<?php echo ((get_option('google_business_reviews_rating_language') != NULL && preg_match('/^(?:true|[1-9])$/i', get_option('google_business_reviews_rating_retrieval_translate', '1'))) ? ' checked="checked"' : '') . ((get_option('google_business_reviews_rating_language') == NULL) ? ' disabled="disabled"' : ''); ?>> <?php esc_html_e('Translate reviews into this language.', 'g-business-reviews-rating'); ?></label>
                    </td>
                </tr>
                <tr id="review-retrieval-row">
                    <th scope="row"><label for="retrieval-sort"><?php esc_html_e('Review Retrieval', 'g-business-reviews-rating'); ?></label></th>
                    <td>
                        <select id="retrieval-sort" name="google_business_reviews_rating_retrieval_sort">
                            <option value="most_relevant"<?php echo (get_option('google_business_reviews_rating_retrieval_sort', 'most_relevant') == 'most_relevant') ? ' selected' : ''; ?>><?php esc_html_e('Retrieve relevant reviews only', 'g-business-reviews-rating'); ?></option>
                            <option value="newest"<?php echo (get_option('google_business_reviews_rating_retrieval_sort') == 'newest') ? ' selected' : ''; ?>><?php esc_html_e('Retrieve new reviews only', 'g-business-reviews-rating'); ?></option>
                            <option value="review_sort"<?php echo (get_option('google_business_reviews_rating_retrieval_sort') == 'review_sort') ? ' selected' : ''; ?>><?php esc_html_e('Retrieve reviews based on current review sort', 'g-business-reviews-rating'); ?></option>
                            <option value=""<?php echo (get_option('google_business_reviews_rating_retrieval_sort', 'most_relevant') == NULL) ? ' selected' : ''; ?>><?php esc_html_e('Retrieve both new and relevant reviews', 'g-business-reviews-rating'); ?></option>
                        </select>
                        <p id="retrieval-sort-recommendation-newest" class="description"><?php _e('This option will disrupt review sorting by relevance.', 'g-business-reviews-rating'); ?></p>
                        <p id="retrieval-sort-recommendation-both" class="description"><?php _e('This option will disrupt review sorting by relevance.', 'g-business-reviews-rating'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="local-images"><?php esc_html_e('Local Images', 'g-business-reviews-rating'); ?></label></th>
                    <td>
                        <label for="local-images"><input type="checkbox" id="local-images" name="google_business_reviews_rating_local_images" value="1"<?php echo (get_option('google_business_reviews_rating_local_images') ? ' checked="checked"' : ''); ?>> <?php esc_html_e('Store images locally rather than using Google’s CDN', 'g-business-reviews-rating'); ?></label>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="additional-array-sanitization"><?php esc_html_e('Clean Retrieved Data', 'g-business-reviews-rating'); ?></label></th>
                    <td>
                        <label for="additional-array-sanitization"><input type="checkbox" id="additional-array-sanitization" name="google_business_reviews_rating_additional_array_sanitization" value="1"<?php echo (get_option('google_business_reviews_rating_additional_array_sanitization') ? ' checked="checked"' : ''); ?>> <?php esc_html_e('Additional sanitization of retrieved data — emoticons are removed from text', 'g-business-reviews-rating'); ?></label>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="demo"><?php esc_html_e('Demo Mode', 'g-business-reviews-rating'); ?></label></th>
                    <td>
                        <label for="demo"><input type="checkbox" id="demo" name="google_business_reviews_rating_demo" value="1"<?php echo (get_option('google_business_reviews_rating_demo') ? ' checked="checked"' : ''); ?>> <?php esc_html_e('Enable Demo Mode with dummy data', 'g-business-reviews-rating'); ?></label>
                    </td>
                </tr>
            </table>
			<?php submit_button(); ?>
		</form>
    </div>
<?php endif; ?>

    <div id="shortcodes" class="section<?php echo ($this->section != 'shortcodes') ? ' hide' : ''; ?>">
        <nav class="section-bookmarks">
            <ul>
                <li><a href="#examples"><?php esc_html_e('Examples', 'g-business-reviews-rating'); ?></a></li>
                <li><a href="#parameters"><?php esc_html_e('Parameters', 'g-business-reviews-rating'); ?></a></li>
                <li><a href="#classes"><?php esc_html_e('HTML Classes', 'g-business-reviews-rating'); ?></a></li>
            </ul>
        </nav>
        <form method="post" action="options.php" id="google-business-reviews-rating-shortcodes">
            <h2 id="examples"><?php esc_html_e('Shortcodes', 'g-business-reviews-rating'); ?></h2>
            <p><?php /* translators: %s: URL for Shortcode Demonstration Website */
				echo sprintf(__('Here is a selection of Shortcodes. You can find more Shortcode examples with their output in the <a href="%s" class="components-external-link" target="_blank">demonstration website</a>.', 'g-business-reviews-rating'), 'https://demo.designextreme.com/reviews-rating-google-business/'); ?></p>
			<div class="columns">
                <div class="left">
                    <h3 id="shortcodes-reviews"><?php esc_html_e('Reviews', 'g-business-reviews-rating'); ?></h3>
                    <table class="form-table">
                        <tr>
                            <th><?php esc_html_e('Google reviews', 'g-business-reviews-rating'); ?></th>
                            <td><span id="<?php $id = 0; echo esc_attr('shortcode-' . $id); $id++; ?>" class="shortcode" contenteditable="true">[reviews_rating]</span></td>
                        </tr>
                        <tr>
                            <th><?php esc_html_e('Google reviews (IDs)', 'g-business-reviews-rating'); ?></th>
                            <td><span id="<?php echo esc_attr('shortcode-' . $id); $id++; ?>" class="shortcode" contenteditable="true">[reviews_rating id=&quot;1,5,3&quot;]</span></td>
                        </tr>
                        <tr>
                            <th rowspan="10"><?php esc_html_e('Google reviews (options)', 'g-business-reviews-rating'); ?></th>
                            <td>
                            	<span id="<?php echo esc_attr('shortcode-' . $id); $id++; ?>" class="shortcode abbreviated" contenteditable="true">[reviews_rating theme=&quot;dark&quot; min=3 max=5 offset=0 limit=3 summary=&quot;yes&quot; icon=&quot;no&quot; excerpt=160 more=&quot;read more&quot;]</span>
                            	<span id="<?php echo esc_attr('shortcode-' . $id); $id++; ?>" class="shortcode additional" contenteditable="true">[reviews_rating language=&quot;nl&quot; review_word=&quot;recensie/recensies&quot;]</span>
                            </td>
                        </tr>
                        <tr>
                            <td>
                            	<span id="<?php echo esc_attr('shortcode-' . $id); $id++; ?>" class="shortcode abbreviated" contenteditable="true">[reviews_rating theme=&quot;columns three fonts&quot; vicinity=&quot;E4, London&quot; reviews_link=&quot;View Google Reviews&quot; write_review_link=&quot;Leave A Review&quot;]</span>
                            	<span id="<?php echo esc_attr('shortcode-' . $id); $id++; ?>" class="shortcode additional" contenteditable="true">[reviews_rating theme=&quot;dark&quot; min=3 max=5 offset=0 limit=3]</span>
                            </td>
                        </tr>
                        <tr>
                            <td>
                            	<span id="<?php echo esc_attr('shortcode-' . $id); $id++; ?>" class="shortcode abbreviated" contenteditable="true">[reviews_rating icon=&quot;/wp-content/uploads/logo.png&quot; avatar=&quot;false&quot; review_item_order=&quot;text first&quot; review_text_min=200]</span>
                            	<span id="<?php echo esc_attr('shortcode-' . $id); $id++; ?>" class="shortcode additional" contenteditable="true">[reviews_rating summary=&quot;yes&quot; icon=&quot;no&quot; excerpt=160 more=&quot;read more&quot;]</span>
                            </td>
                        </tr>
                        <tr>
                            <td>
                            	<span id="<?php echo esc_attr('shortcode-' . $id); $id++; ?>" class="shortcode abbreviated" contenteditable="true">[reviews_rating place_id=&quot;ChIJtTeDfh9w5kcRJEWRKN1Yy6I&quot; animate=&quot;no&quot; review_text=&quot;no&quot; attribution=&quot;yes&quot;]</span>
                            	<span id="<?php echo esc_attr('shortcode-' . $id); $id++; ?>" class="shortcode additional" contenteditable="true">[reviews_rating theme=&quot;columns three fonts&quot; reviews_link=&quot;View Google Reviews&quot; write_review_link=&quot;Leave A Review&quot;]</span>
                            </td>
                        </tr>
                        <tr class="additional">
                            <td><span id="<?php echo esc_attr('shortcode-' . $id); $id++; ?>" class="shortcode" contenteditable="true">[reviews_rating avatar=&quot;false&quot; review_item_order=&quot;text first&quot; review_text_min=200]</span></td>
                        </tr>
                        <tr class="additional">
                            <td><span id="<?php echo esc_attr('shortcode-' . $id); $id++; ?>" class="shortcode" contenteditable="true">[reviews_rating icon=&quot;/wp-content/uploads/logo.png&quot; name=&quot;Star Consultancy&quot; vicinity=&quot;E4, London&quot;]</span></td>
                        </tr>
                        <tr class="additional">
                            <td><span id="<?php echo esc_attr('shortcode-' . $id); $id++; ?>" class="shortcode" contenteditable="true">[reviews_rating place_id=&quot;ChIJaxhvijEFdkgRPtwPZgzI4w8&quot; animate=&quot;no&quot; review_text=&quot;no&quot;]</span></td>
                        </tr>
                        <tr class="additional">
                            <td><span id="<?php echo esc_attr('shortcode-' . $id); $id++; ?>" class="shortcode" contenteditable="true">[reviews_rating summary=false date=false limit=3 min=5 review_text_min=140 review_text_max=290 name_format=&quot;last initial&quot;]</span></td>
                        </tr>
                        <tr class="additional">
                            <td><span id="<?php echo esc_attr('shortcode-' . $id); $id++; ?>" class="shortcode" contenteditable="true">[reviews_rating theme=&quot;bubble center fill columns three&quot; summary=false limit=3 min=5 excerpt=150]</span></td>
                        </tr>
                        <tr class="additional">
                            <td><span id="<?php echo esc_attr('shortcode-' . $id); $id++; ?>" class="shortcode" contenteditable="true">[reviews_rating theme=&quot;narrow center&quot; summary=&quot;icon, name, rating, stars, count&quot; limit=0 stars=&quot;css&quot;]</span></td>
                        </tr>
                    </table>
                </div>
                <div class="right">
                    <h3 id="shortcodes-links"><?php esc_html_e('Links', 'g-business-reviews-rating'); ?></h3>
                    <table class="form-table">
                        <tr>
                            <th><?php esc_html_e('Google reviews link', 'g-business-reviews-rating'); ?></th>
                            <td><span id="<?php echo esc_attr('shortcode-' . $id); $id++; ?>" class="shortcode" contenteditable="true"><?php echo esc_html('[reviews_rating_link reviews_link]' . __('Our Reviews on Google', 'g-business-reviews-rating') . '[/reviews_rating_link]'); ?></span></td>
                        </tr>
                        <tr>
                            <th rowspan="3"><?php esc_html_e('Google reviews link (options)', 'g-business-reviews-rating'); ?></th>
                            <td><span id="<?php echo esc_attr('shortcode-' . $id); $id++; ?>" class="shortcode" contenteditable="true"><?php echo esc_html('[reviews_rating_link reviews_link class="button" target="_blank"]' . __('Our Reviews on Google', 'g-business-reviews-rating') . '[/reviews_rating_link]'); ?></span></td>
                        </tr>
                        <tr>
                            <td><span id="<?php echo esc_attr('shortcode-' . $id); $id++; ?>" class="shortcode" contenteditable="true"><?php echo esc_html('[reviews_rating_link reviews_link]&lt;span class=&quot;google-icon&quot;&gt;&lt;/span&gt; ' . __('Our Reviews on Google', 'g-business-reviews-rating') . '[/reviews_rating_link]'); ?></span></td>
                        </tr>
                        <tr>
                            <td><span id="<?php echo esc_attr('shortcode-' . $id); $id++; ?>" class="shortcode" contenteditable="true"><?php echo esc_html('[reviews_rating_link reviews_link]' . __('Our Reviews on Google', 'g-business-reviews-rating') . ' &lt;span class=&quot;google-icon black end&quot;&gt;&lt;/span&gt;[/reviews_rating_link]'); ?></span></td>
                        </tr>
                        <tr>
                            <th><?php esc_html_e('Write a Google review link', 'g-business-reviews-rating'); ?></th>
                            <td><span id="<?php echo esc_attr('shortcode-' . $id); $id++; ?>" class="shortcode" contenteditable="true"><?php echo esc_html('[reviews_rating_link write_review_link]' . __('Leave Your Review on Google', 'g-business-reviews-rating') . '[/reviews_rating_link]'); ?></span></td>
                        </tr>
                        <tr>
                            <th><?php esc_html_e('Google Maps link', 'g-business-reviews-rating'); ?></th>
                            <td><span id="<?php echo esc_attr('shortcode-' . $id); $id++; ?>" class="shortcode" contenteditable="true"><?php echo esc_html('[reviews_rating_link maps_link]' . __('View Location on Google Maps', 'g-business-reviews-rating') . '[/reviews_rating_link]'); ?></span></td>
                        </tr>
                    </table>
                    <h3 id="shortcodes-text"><?php esc_html_e('Text', 'g-business-reviews-rating'); ?></h3>
                    <table class="form-table">
                        <tr>
                            <th><?php esc_html_e('Google rating', 'g-business-reviews-rating'); ?></th>
                            <td><span id="<?php echo esc_attr('shortcode-' . $id); $id++; ?>" class="shortcode" contenteditable="true">[reviews_rating rating]</span></td>
                        </tr>
                        <tr>
                            <th><?php esc_html_e('Google review count', 'g-business-reviews-rating'); ?></th>
                            <td><span id="<?php echo esc_attr('shortcode-' . $id); $id++; ?>" class="shortcode" contenteditable="true">[reviews_rating review_count]</span></td>
                        </tr>
                        <tr>
                            <th><?php esc_html_e('Google reviews URL', 'g-business-reviews-rating'); ?></th>
                            <td><span id="<?php echo esc_attr('shortcode-' . $id); $id++; ?>" class="shortcode" contenteditable="true">[reviews_rating reviews_url]</span></td>
                        </tr>
                        <tr>
                            <th><?php esc_html_e('Write a Google review URL', 'g-business-reviews-rating'); ?></th>
                            <td><span id="<?php echo esc_attr('shortcode-' . $id); $id++; ?>" class="shortcode" contenteditable="true">[reviews_rating write_review_url]</span></td>
                        </tr>
                        <tr>
                            <th><?php esc_html_e('Google Maps URL', 'g-business-reviews-rating'); ?></th>
                            <td><span id="<?php echo esc_attr('shortcode-' . $id); $id++; ?>" class="shortcode" contenteditable="true">[reviews_rating maps_url]</span></td>
                        </tr>
                    </table>
                </div>
            </div>
            
            <h2 id="parameters"><?php esc_html_e('Parameters', 'g-business-reviews-rating'); ?></h2>
            <p><?php _e('There are quite a wide range of parameters that are accepted, so a guide will help cover all the possibilities to customize the output of your reviews, links and text. Shortcode parameters will override the values set in the General section. All parameters are optional.', 'g-business-reviews-rating'); ?></p>
            <table class="wp-list-table widefat fixed striped parameters">
                <tr>
                    <th class="parameter"><?php esc_html_e('Parameter', 'g-business-reviews-rating'); ?></th>
                    <th class="description"><?php esc_html_e('Description', 'g-business-reviews-rating'); ?></th>
                    <th class="accepted"><?php esc_html_e('Accepted Values', 'g-business-reviews-rating'); ?></th>
                    <th class="default"><?php esc_html_e('Default', 'g-business-reviews-rating'); ?></th>
                    <th class="boolean"><?php esc_html_e('Reviews', 'g-business-reviews-rating'); ?></th>
                    <th class="boolean"><?php esc_html_e('Links', 'g-business-reviews-rating'); ?></th>
                </tr>
                <tr id="parameter-limit">
                    <td class="parameter">limit</td>
                    <td class="description"><?php /* translators: 1: NULL as a parameter value, 2: Numerical zero as a parameter value */
						echo sprintf(__('Simply sets the number of reviews you would like listed. Do not set or leave this empty (<span class="code">%1$s</span>) = all reviews and <span class="code">%2$s</span> = hide reviews.', 'g-business-reviews-rating'), 'NULL', '0'); ?></td>
                    <td class="accepted"><span class="code">NULL</span>, <span class="code">0</span>, <span class="code">1</span>, <span class="code">2</span>, …</td>
                    <td class="default"><span class="code">NULL</span></td>
                    <td class="boolean"><span class="dashicons dashicons-yes"></span></td>
                    <td class="boolean"><span class="dashicons dashicons-no"></span></td>
                </tr>
                <tr id="parameter-sort">
                    <td class="parameter">sort</td>
                    <td class="description"><?php /* translators: %s: parameter value for NULL */
						echo sprintf(__('Your preference for sorting when 2 or more reviews are displayed. Leave empty (<span class="code">%s</span>) to sort by Google’s relevance.', 'g-business-reviews-rating'), 'NULL'); ?></td>
                    <td class="accepted"><span class="code">NULL</span>, <?php $review_sort_options = array_keys($this->review_sort_options); array_shift($review_sort_options); echo esc_html(implode(', ', $review_sort_options)); ?></td>
                    <td class="default"><span class="code">NULL</span></td>
                    <td class="boolean"><span class="dashicons dashicons-yes"></span></td>
                    <td class="boolean"><span class="dashicons dashicons-no"></span></td>
                </tr>
                <tr id="parameter-offset">
                    <td class="parameter">offset</td>
                    <td class="description"><?php _e('When the limit is 1 or more, you can offset the review results to &ldquo;jump&rdquo; forward.', 'g-business-reviews-rating'); ?></td>
                    <td class="accepted"><span class="code">0</span>, <span class="code">1</span>, <span class="code">2</span>, …</td>
                    <td class="default"><span class="code">0</span></td>
                    <td class="boolean"><span class="dashicons dashicons-yes"></span></td>
                    <td class="boolean"><span class="dashicons dashicons-no"></span></td>
                </tr>
                <tr id="parameter-view">
                    <td class="parameter">view</td>
                    <td class="description"><?php /* translators: %s: bookmark for limit parameter */
						echo sprintf(__('Enable a review carousel. There must be two or more reviews with the view number always less than the <a href="%s">limit</a>.', 'g-business-reviews-rating'), '#parameter-limit'); ?></td>
                    <td class="accepted"><span class="code">1</span>, <span class="code">2</span>, …</td>
                    <td class="default"><span class="code">NULL</span></td>
                    <td class="boolean"><span class="dashicons dashicons-yes"></span></td>
                    <td class="boolean"><span class="dashicons dashicons-no"></span></td>
                </tr>
                <tr id="parameter-id">
                    <td class="parameter">id</td>
                    <td class="description"><?php /* translators: %s: bookmark for reviews */
						echo sprintf(__('Show an individual review or set of reviews by their ID (see <a href="%s">Reviews</a>); order is preserved; use a comma separated list for multiple IDs.', 'g-business-reviews-rating'), '#reviews'); ?></td>
                    <td class="accepted"><span class="code">1</span>, <span class="code">2</span>, <span class="code">3</span>, … or &quot;1, 5, 6, 2&quot;</td>
                    <td class="default"><span class="code">NULL</span></td>
                    <td class="boolean"><span class="dashicons dashicons-yes"></span></td>
                    <td class="boolean"><span class="dashicons dashicons-no"></span></td>
                </tr>
                <tr id="parameter-place-id">
                    <td class="parameter">place_id</td>
                    <td class="description"><?php _e('If you have more than one Place in the retrieved reviews, you can filter by the Place ID. Only the active Place will receive new reviews and data.', 'g-business-reviews-rating'); ?></td>
                    <td class="accepted"><em><?php esc_html_e('String'); ?></em></td>
                    <td class="default"><span class="code">NULL</span></td>
                    <td class="boolean"><span class="dashicons dashicons-yes"></span></td>
                    <td class="boolean"><span class="dashicons dashicons-no"></span></td>
                </tr>
                <tr id="parameter-language">
                    <td class="parameter">language</td>
                    <td class="description"><?php _e('Filter results based on the language using the two letter language code. Empty reviews have no language will always appear.', 'g-business-reviews-rating'); ?></td>
                    <td class="accepted"><?php /* translators: example two character language codes, lowercase, start with local language */ _e('en, fr, de, …'); ?></td>
                    <td class="default"><span class="code">NULL</span></td>
                    <td class="boolean"><span class="dashicons dashicons-yes"></span></td>
                    <td class="boolean"><span class="dashicons dashicons-no"></span></td>
                </tr>
                <tr id="parameter-theme" data-show="<?php esc_attr_e('Show', 'g-business-reviews-rating'); ?>">
                    <td class="parameter">theme</td>
                    <td class="description"><?php /* translators: %s: bookmark for HTML classes */
						echo sprintf(__('Set a theme based on your overall visual requirements. You may use their combination of <a href="%s">class names</a> (recommended) or the full text name (not recommended). These values match with the class attribute set to the main HTML element.', 'g-business-reviews-rating'), '#classes'); ?></td>
                    <td class="accepted"><span class="code">NULL</span>, <?php echo esc_html('"' . implode('", "', array_keys($this->reviews_themes)) . '"'); ?></td>
                    <td class="default">light</td>
                    <td class="boolean"><span class="dashicons dashicons-yes"></span></td>
                    <td class="boolean"><span class="dashicons dashicons-no"></span></td>
                </tr>
                <tr id="parameter-color-scheme">
                    <td class="parameter">color_scheme</td>
                    <td class="description"><?php _e('Set a color scheme based on your overall visual requirements. These values match with the class attribute set to the main HTML element.', 'g-business-reviews-rating'); ?></td>
                    <td class="accepted"><span class="code">NULL</span>, <?php echo esc_html('"' . implode('", "', array_keys($this->color_schemes)) . '"'); ?></td>
                    <td class="default"><span class="code">NULL</span></td>
                    <td class="boolean"><span class="dashicons dashicons-yes"></span></td>
                    <td class="boolean"><span class="dashicons dashicons-no"></span></td>
                </tr>
                <tr id="parameter-min">
                    <td class="parameter">min</td>
                    <td class="description"><?php _e('Set to filter out any ratings that fall below this minimum value.', 'g-business-reviews-rating'); ?></td>
                    <td class="accepted"><span class="code">1</span>, <span class="code">2</span>, <span class="code">3</span>, <span class="code">4</span>, <span class="code">5</span></td>
                    <td class="default"><span class="code">NULL</span></td>
                    <td class="boolean"><span class="dashicons dashicons-yes"></span></td>
                    <td class="boolean"><span class="dashicons dashicons-no"></span></td>
                </tr>
                <tr id="parameter-max">
                    <td class="parameter">max</td>
                    <td class="description"><?php _e('Set to filter out any ratings that lie above this maximum value.', 'g-business-reviews-rating'); ?></td>
                    <td class="accepted"><span class="code">1</span>, <span class="code">2</span>, <span class="code">3</span>, <span class="code">4</span>, <span class="code">5</span></td>
                    <td class="default"><span class="code">NULL</span></td>
                    <td class="boolean"><span class="dashicons dashicons-yes"></span></td>
                    <td class="boolean"><span class="dashicons dashicons-no"></span></td>
                </tr>
                <tr id="parameter-summary">
                    <td class="parameter">summary</td>
                    <td class="description"><?php /* translators: %s: is a list of parameters and should remain unchanged */
						echo sprintf(__('Show or hide the summary section — containing the icon, business name, vicinity and rating. You may also list individual elements that match the parameters: %s; as a list separated by commas.', 'g-business-reviews-rating'), '<a href="#parameter-icon">icon</a>, <a href="#parameter-name">name</a>, <a href="#parameter-vicinity">vicinity</a>, <a href="#parameter-rating">rating</a>, <a href="#parameter-stars">stars</a>, <a href="#parameter-count">count</a>'); ?></td>
                    <td class="accepted">icon, name, vicinity, rating, &quot;rating number&quot;, &quot;rating stars&quot;, &quot;review count&quot;, yes, no, true, false, <span class="code">1</span>, <span class="code">0</span>, show, hide, on, off</td>
                    <td class="default"><span class="code">TRUE</span></td>
                    <td class="boolean"><span class="dashicons dashicons-yes"></span></td>
                    <td class="boolean"><span class="dashicons dashicons-no"></span></td>
                </tr>
                <tr id="parameter-icon">
                    <td class="parameter">icon</td>
                    <td class="description"><?php _e('Show or hide business icon — or specify your own image replacement (jpg, jpeg, png, gif, svg extensions are supported).', 'g-business-reviews-rating'); ?></td>
                    <td class="accepted">yes, no, true, false, <span class="code">1</span>, <span class="code">0</span>, show, hide, on, off, <em>/url/to/image.jpg</em></td>
                    <td class="default"><span class="code">TRUE</span></td>
                    <td class="boolean"><span class="dashicons dashicons-yes"></span></td>
                    <td class="boolean"><span class="dashicons dashicons-no"></span></td>
                </tr>
                <tr id="parameter-name">
                    <td class="parameter">name</td>
                    <td class="description"><?php _e('Show or hide business name — or specify your own choice of business name.', 'g-business-reviews-rating'); ?></td>
                    <td class="accepted">yes, no, true, false, <span class="code">1</span>, <span class="code">0</span>, show, hide, on, off, <em><?php esc_html_e('Any string', 'g-business-reviews-rating'); ?></em></td>
                    <td class="default"><span class="code">TRUE</span></td>
                    <td class="boolean"><span class="dashicons dashicons-yes"></span></td>
                    <td class="boolean"><span class="dashicons dashicons-no"></span></td>
                </tr>
                <tr id="parameter-vicinity">
                    <td class="parameter">vicinity</td>
                    <td class="description"><?php _e('Show or hide business vicinity according to Google — or specify your own text replacement.', 'g-business-reviews-rating'); ?></td>
                    <td class="accepted">yes, no, true, false, <span class="code">1</span>, <span class="code">0</span>, show, hide, on, off, <em><?php esc_html_e('Any string', 'g-business-reviews-rating'); ?></em></td>
                    <td class="default"><span class="code">TRUE</span></td>
                    <td class="boolean"><span class="dashicons dashicons-yes"></span></td>
                    <td class="boolean"><span class="dashicons dashicons-no"></span></td>
                </tr>
                <tr id="parameter-rating">
                    <td class="parameter">rating</td>
                    <td class="description"><?php _e('Show or hide number of the overall rating.', 'g-business-reviews-rating'); ?></td>
                    <td class="accepted">yes, no, true, false, <span class="code">1</span>, <span class="code">0</span>, show, hide, on, off</td>
                    <td class="default"><span class="code">TRUE</span></td>
                    <td class="boolean"><span class="dashicons dashicons-yes"></span></td>
                    <td class="boolean"><span class="dashicons dashicons-no"></span></td>
                </tr>
                <tr id="parameter-stars">
                    <td class="parameter">stars</td>
                    <td class="description"><?php /* translators: 1: parameter value for CSS, 2: parameter value for specific colors */
						echo sprintf(__('Show or hide stars for the overall rating, with an option to override default orange color for stars in the overall rating. Set as <em>%1$s</em> to mirror the style sheet rule <em>%2$s</em>.', 'g-business-reviews-rating'), 'css', 'color'); ?></td>
                    <td class="accepted">svg, html, css, yes, no, true, false, show, hide, on, off, <em><?php esc_html_e('Any valid color string', 'g-business-reviews-rating'); ?></em></td>
                    <td class="default">svg</td>
                    <td class="boolean"><span class="dashicons dashicons-yes"></span></td>
                    <td class="boolean"><span class="dashicons dashicons-no"></span></td>
                </tr>
                <tr id="parameter-stars-gray">
                    <td class="parameter">stars_gray</td>
                    <td class="description"><?php /* translators: 1: parameter value for CSS, 2: parameter value for specific colors */
						echo sprintf(__('Optionally override the default gray color for stars in the overall rating. Set as <em>%1$s</em> to mirror the style sheet rule <em>%2$s</em>.', 'g-business-reviews-rating'), 'css', 'color'); ?></td>
                    <td class="accepted"><span class="code">NULL</span>, css, <em><?php esc_html_e('Any valid color string', 'g-business-reviews-rating'); ?></em></td>
                    <td class="default"><span class="code">NULL</span></td>
                    <td class="boolean"><span class="dashicons dashicons-yes"></span></td>
                    <td class="boolean"><span class="dashicons dashicons-no"></span></td>
                </tr>
                <tr id="parameter-count">
                    <td class="parameter">count</td>
                    <td class="description"><?php _e('Show or hide total review/rating count.', 'g-business-reviews-rating'); ?></td>
                    <td class="accepted">yes, no, true, false, show, hide, on, off</td>
                    <td class="default"><span class="code">TRUE</span></td>
                    <td class="boolean"><span class="dashicons dashicons-yes"></span></td>
                    <td class="boolean"><span class="dashicons dashicons-no"></span></td>
                </tr>
                <tr id="parameter-review-word">
                    <td class="parameter">review_word</td>
                    <td class="description"><?php /* translators: 1: A reference to the string %u, keep example in an alternative language, using something other than Spanish is acceptable */
						echo sprintf(__('Word displayed after the total review/rating count number in the summary. Accepts %1$s to set placement of review/rating count (e.g. <em>Tenemos %1$s comentarios</em>). For completeness, you may separate singular and plural with , or / characters.', 'g-business-reviews-rating'), '%u'); ?></td>
                    <td class="accepted"><em><?php esc_html_e('Any valid string', 'g-business-reviews-rating'); ?></em></td> 
                    <td class="default"><?php esc_html_e('review', 'g-business-reviews-rating'); ?>/<?php esc_html_e('reviews', 'g-business-reviews-rating'); ?></td>
                    <td class="boolean"><span class="dashicons dashicons-yes"></span></td>
                    <td class="boolean"><span class="dashicons dashicons-no"></span></td>
                </tr>
                <tr id="parameter-animate">
                    <td class="parameter">animate</td>
                    <td class="description"><?php /* translators: %s: parameter value for FALSE */
						echo sprintf(__('Animate the rating stars on load or set as static (<span class="code">%s</span>).', 'g-business-reviews-rating'), 'FALSE'); ?></td>
                    <td class="accepted">yes, no, true, false, <span class="code">1</span>, <span class="code">0</span>, show, hide, on, off, animate, static</td>
                    <td class="default"><span class="code">TRUE</span></td>
                    <td class="boolean"><span class="dashicons dashicons-yes"></span></td>
                    <td class="boolean"><span class="dashicons dashicons-no"></span></td>
                </tr>
                <tr id="parameter-review-text">
                    <td class="parameter">review_text</td>
                    <td class="description"><?php _e('Show or hide all review text leaving just the names, ratings and relative times.', 'g-business-reviews-rating'); ?></td>
                    <td class="accepted">yes, no, true, false, <span class="code">1</span>, <span class="code">0</span>, show, hide, on, off</td>
                    <td class="default"><span class="code">TRUE</span></td>
                    <td class="boolean"><span class="dashicons dashicons-yes"></span></td>
                    <td class="boolean"><span class="dashicons dashicons-no"></span></td>
                </tr>
                <tr id="parameter-review-text-min">
                    <td class="parameter">review_text_min</td>
                    <td class="description"><?php /* translators: 1: NULL as a parameter value, 2: Numerical zero as a parameter value */
						echo sprintf(__('Filter by a minimum review text character count. Empty (<span class="code">%1$s</span>) or <span class="code">%2$s</span> = no minimum. ', 'g-business-reviews-rating'), 'NULL', '0'); ?></td>
                    <td class="accepted"><span class="code">NULL</span>, <span class="code">0</span>, <span class="code">1</span>, <span class="code">2</span>, …</td>
                    <td class="default"><span class="code">NULL</span></td>
                    <td class="boolean"><span class="dashicons dashicons-yes"></span></td>
                    <td class="boolean"><span class="dashicons dashicons-no"></span></td>
                </tr>
                <tr id="parameter-review-text-max">
                    <td class="parameter">review_text_max</td>
                    <td class="description"><?php /* translators: 1: NULL as a parameter value, 2: Numerical zero as a parameter value */
						echo sprintf(__('Filter by a maximum review text character count. Empty (<span class="code">%1$s</span>) = no maximum. <span class="code">%2$s</span> = no review text. ', 'g-business-reviews-rating'), 'NULL', '0'); ?></td>
                    <td class="accepted"><span class="code">NULL</span>, <span class="code">0</span>, <span class="code">1</span>, <span class="code">2</span>, …</td>
                    <td class="default"><span class="code">NULL</span></td>
                    <td class="boolean"><span class="dashicons dashicons-yes"></span></td>
                    <td class="boolean"><span class="dashicons dashicons-no"></span></td>
                </tr>
                <tr id="parameter-review-text-inc">
                    <td class="parameter">review_text_inc</td>
                    <td class="description"><?php _e('Require a specific word or words in review text. Case insensitive; full word match. Multiple required words as a comma separated list.', 'g-business-reviews-rating'); ?></td>
                    <td class="accepted">excellent or &quot;good, superb, great, …&quot;, <em><?php esc_html_e('Any string', 'g-business-reviews-rating'); ?></em></td>
                    <td class="default"><span class="code">NULL</span></td>
                    <td class="boolean"><span class="dashicons dashicons-yes"></span></td>
                    <td class="boolean"><span class="dashicons dashicons-no"></span></td>
                </tr>
                <tr id="parameter-review-text-exc">
                    <td class="parameter">review_text_exc</td>
                    <td class="description"><?php _e('Filter out reviews containing a specific word or words. Case insensitive; full word match. Multiple required words as a comma separated list.', 'g-business-reviews-rating'); ?></td>
                    <td class="accepted">poor or &quot;average, bad, avoid, …&quot;, <em><?php esc_html_e('Any string', 'g-business-reviews-rating'); ?></em></td>
                    <td class="default"><span class="code">NULL</span></td>
                    <td class="boolean"><span class="dashicons dashicons-yes"></span></td>
                    <td class="boolean"><span class="dashicons dashicons-no"></span></td>
                </tr>
                <tr id="parameter-review-text-format">
                    <td class="parameter">review_text_format</td>
                    <td class="description"><?php _e('Apply basic formatting to the review text such as replacing newlines with punctuation and space.', 'g-business-reviews-rating'); ?></td>
                    <td class="accepted">strip lines, add paragraphs, add punctuation</td>
                    <td class="default"><span class="code">FALSE</span></td>
                    <td class="boolean"><span class="dashicons dashicons-yes"></span></td>
                    <td class="boolean"><span class="dashicons dashicons-no"></span></td>
                </tr>
                <tr id="parameter-review-text-height">
                    <td class="parameter">review_text_height</td>
                    <td class="description"><?php _e('Set a fixed height for all reviews text within the list of reviews. Longer reviews will have a vertical scroll. Units required.', 'g-business-reviews-rating'); ?></td>
                    <td class="accepted"><em><?php esc_html_e('Any valid CSS value', 'g-business-reviews-rating'); ?></em></td>
                    <td class="default"><span class="code">NULL</span></td>
                    <td class="boolean"><span class="dashicons dashicons-yes"></span></td>
                    <td class="boolean"><span class="dashicons dashicons-no"></span></td>
                </tr>
                <tr id="parameter-excerpt">
                    <td class="parameter">excerpt</td>
                    <td class="description"><?php /* translators: %s: parameter value for NULL */
						echo sprintf(__('Characters in review text to show before a &ldquo;more&rdquo; toggle link is shown to expand the remainder of the review. Empty (<span class="code">%s</span>) = no excerpt; show all review text.', 'g-business-reviews-rating'), 'NULL'); ?></td>
                    <td class="accepted"><span class="code">NULL</span>, <span class="code">20</span>, <span class="code">21</span>, <span class="code">22</span>, …</td>
                    <td class="default"><span class="code">235</span></td>
                    <td class="boolean"><span class="dashicons dashicons-yes"></span></td>
                    <td class="boolean"><span class="dashicons dashicons-no"></span></td>
                </tr>
                <tr id="parameter-more">
                    <td class="parameter">more</td>
                    <td class="description"><?php _e('Text to use in the &ldquo;more&rdquo; toggle link.', 'g-business-reviews-rating'); ?></td>
                    <td class="accepted"><em><?php esc_html_e('Any string', 'g-business-reviews-rating'); ?></em></td> 
                    <td class="default"><?php esc_html_e('More', 'g-business-reviews-rating'); ?></td>
                    <td class="boolean"><span class="dashicons dashicons-yes"></span></td>
                    <td class="boolean"><span class="dashicons dashicons-no"></span></td>
                </tr>
                <tr id="parameter-date">
                    <td class="parameter">date</td>
                    <td class="description"><?php /* translators: use local language version at php.net if available */
						_e('Format review submission dates using either the <a href="https://www.php.net/manual/en/datetime.format.php" class="components-external-link" target="_blank">PHP date</a>, relative text or hide this entirely. Imported review dates will be an approximation.', 'g-business-reviews-rating'); ?></td>
                    <td class="accepted">relative, no, false, <span class="code">0</span>, hide, off, <em><?php esc_html_e('Any valid string', 'g-business-reviews-rating'); ?></em></td>
                    <td class="default">relative</td>
                    <td class="boolean"><span class="dashicons dashicons-yes"></span></td>
                    <td class="boolean"><span class="dashicons dashicons-no"></span></td>
                </tr>
                <tr id="parameter-link">
                    <td class="parameter">link</td>
                    <td class="description"><?php _e('Set the entire element as a link to all reviews listed externally at Google (only if no reviews listed). Automatically added to Badge theme when no reviews are showing.', 'g-business-reviews-rating'); ?></td>
                    <td class="accepted">reviews, &quot;write review&quot;, yes, no, true, false, <span class="code">1</span>, <span class="code">0</span>, <em><?php esc_html_e('URL string', 'g-business-reviews-rating'); ?></em></td>
                    <td class="default"><span class="code">FALSE</span></td>
                    <td class="boolean"><span class="dashicons dashicons-yes"></span></td>
                    <td class="boolean"><span class="dashicons dashicons-no"></span></td>
                </tr>
                <tr id="parameter-link-disable">
                    <td class="parameter">link_disable</td>
                    <td class="description"><?php /* translators: 1: parameter value for switching author name in review listings, 2: parameter value for setting author details to appear inline in the review listings */
						echo sprintf(__('Disable some or all external links: <em>%1$s</em>, <em>%2$s</em> or <em>%3$s</em>; comma separated list accepted.', 'g-business-reviews-rating'), 'reviews', 'write review', 'author'); ?></td>
                    <td class="accepted">reviews, &quot;write review&quot;, author, yes, no, true, false, <span class="code">1</span>, <span class="code">0</span>, show, hide, on, off</td>
                    <td class="default"><span class="code">FALSE</span></td>
                    <td class="boolean"><span class="dashicons dashicons-yes"></span></td>
                    <td class="boolean"><span class="dashicons dashicons-no"></span></td>
                </tr>
                <tr id="parameter-reviews-link">
                    <td class="parameter">reviews_link</td>
                    <td class="description"><?php _e('Show a link/button for all reviews listed externally at Google. Specify custom text string to appear as the link text. This is hidden by default.', 'g-business-reviews-rating'); ?></td>
                    <td class="accepted">yes, no, true, false, <span class="code">1</span>, <span class="code">0</span>, show, hide, on, off, <em><?php esc_html_e('Any string', 'g-business-reviews-rating'); ?></em></td>
                    <td class="default"><span class="code">FALSE</span></td>
                    <td class="boolean"><span class="dashicons dashicons-yes"></span></td>
                    <td class="boolean"><span class="dashicons dashicons-no"></span></td>
                </tr>
                <tr id="parameter-write-review-link">
                    <td class="parameter">write_review_link</td>
                    <td class="description"><?php _e('Show a link/button to allow a visitor to leave a review at Google. Specify custom text string to appear as the link text. This is hidden by default.', 'g-business-reviews-rating'); ?></td>
                    <td class="accepted">yes, no, true, false, <span class="code">1</span>, <span class="code">0</span>, show, hide, on, off, <em><?php esc_html_e('Any string', 'g-business-reviews-rating'); ?></em></td>
                    <td class="default"><span class="code">FALSE</span></td>
                    <td class="boolean"><span class="dashicons dashicons-yes"></span></td>
                    <td class="boolean"><span class="dashicons dashicons-no"></span></td>
                </tr>
                <tr id="parameter-reviews-url">
                    <td class="parameter">reviews_url</td>
                    <td class="description"><?php _e('Override the default “read reviews” URL to one of your own choosing.', 'g-business-reviews-rating'); ?></td>
                    <td class="accepted"><em><?php esc_html_e('Any valid URL', 'g-business-reviews-rating'); ?></em></td>
                    <td class="default"><span class="code">NULL</span></td>
                    <td class="boolean"><span class="dashicons dashicons-yes"></span></td>
                    <td class="boolean"><span class="dashicons dashicons-yes"></span></td>
                </tr>
                <tr id="parameter-write-review-url">
                    <td class="parameter">write_review_url</td>
                    <td class="description"><?php _e('Override the default “write a review” URL to one of your own choosing.', 'g-business-reviews-rating'); ?></td>
                    <td class="accepted"><em><?php esc_html_e('Any valid URL', 'g-business-reviews-rating'); ?></em></td>
                    <td class="default"><span class="code">NULL</span></td>
                    <td class="boolean"><span class="dashicons dashicons-yes"></span></td>
                    <td class="boolean"><span class="dashicons dashicons-yes"></span></td>
                </tr>
                <tr id="parameter-avatar">
                    <td class="parameter">avatar</td>
                    <td class="description"><?php _e('Show or hide users’ avatars — or specify your own [single] image replacement (jpg, jpeg, png, gif, svg extensions are supported).', 'g-business-reviews-rating'); ?></td>
                    <td class="accepted">yes, no, true, false, <span class="code">1</span>, <span class="code">0</span>, show, hide, on, off, <em><?php /* translators: example path to image, include forward slashes */ esc_html_e('/url/to/image.jpg', 'g-business-reviews-rating'); ?></em></td>
                    <td class="default"><span class="code">TRUE</span></td>
                    <td class="boolean"><span class="dashicons dashicons-yes"></span></td>
                    <td class="boolean"><span class="dashicons dashicons-no"></span></td>
                </tr>
                <tr id="parameter-loading">
                    <td class="parameter">loading</td>
                    <td class="description"><?php _e('Enable either eager or lazy loading for all linked images.', 'g-business-reviews-rating'); ?></td>
                    <td class="accepted"><span class="code">NULL</span>, eager, lazy</td>
                    <td class="default"><span class="code">NULL</span></td>
                    <td class="boolean"><span class="dashicons dashicons-yes"></span></td>
                    <td class="boolean"><span class="dashicons dashicons-no"></span></td>
                </tr>
                <tr id="parameter-name-format">
                    <td class="parameter">name_format</td>
                    <td class="description"><?php /* translators: do not alter some words in the brackets — initials, last name initials — as these must remain in English. lowercase */ _e('Control the format of reviewers’ names such as (e.g. initials or last name initials). Options for case selection and formatting can be combined.', 'g-business-reviews-rating'); ?></td>
                    <td class="accepted"><span class="code">NULL</span>, &quot;capitalize&quot;, &quot;lowercase&quot;, &quot;uppercase&quot;, &quot;full name&quot;, &quot;intials&quot;, &quot;first initial&quot;, &quot;last initial&quot;, &quot;intials with dots&quot;, &quot;first initial with dot&quot;, &quot;last initial with dot&quot;, &quot;initials with dots and space&quot;,  &quot;first initial with dot and space&quot;, &quot;last initial with dot and space&quot;</td>
                    <td class="default">&quot;full name&quot;</td>
                    <td class="boolean"><span class="dashicons dashicons-yes"></span></td>
                    <td class="boolean"><span class="dashicons dashicons-no"></span></td>
                </tr>
                <tr id="parameter-review-item-order">
                    <td class="parameter">review_item_order</td>
                    <td class="description"><?php /* translators: 1: parameter value for switching author name in review listings, 2: parameter value for setting author details to appear inline in the review listings, 3: list of available elements */
						echo sprintf(__('Change the ordering within review item: review text may be set to appear at the top of each entry. Add <em>%1$s</em> to switch the author’s avatar and name with stars and date. Add <em>%2$s</em> to set the author name, stars and date inline. Individual elements can be set and ordered using a comma separated list (e.g. <em>%3$s</em>).', 'g-business-reviews-rating'), 'author switch', 'inline', 'avatar, name, rating, date, review'); ?></td>
                    <td class="accepted"><span class="code">NULL</span>, &quot;text first&quot;, &quot;text last&quot;, &quot;author switch&quot;, inline, &quot;text first inline&quot;, &quot;author switch inline&quot;, &quot;text first author switch inline&quot;, &quot;avatar, name, rating, date, review&quot;, </td>
                    <td class="default">&quot;text last&quot;</td>
                    <td class="boolean"><span class="dashicons dashicons-yes"></span></td>
                    <td class="boolean"><span class="dashicons dashicons-no"></span></td>
                </tr>
                <tr id="parameter-iterations">
                    <td class="parameter">iterations</td>
                    <td class="description"><?php /* translators: 1: bookmark for view parameter, 2: parameter for view, 3: bookmark for interval parameter, 4: parameter for interval */
						echo sprintf(__('In the carousel, set a specific number of slides to play. The carousel must active with the following parameters: <a href="%1$s">%2$s</a> and <a href="%3$s">%4$s</a>.', 'g-business-reviews-rating'), '#parameter-view', 'view', '#parameter-interval', 'interval'); ?></td>
                    <td class="accepted"><span class="code">0</span>, <span class="code">1</span>, <span class="code">2</span>, …</td>
                    <td class="default"><span class="code">0</span></td>
                    <td class="boolean"><span class="dashicons dashicons-yes"></span></td>
                    <td class="boolean"><span class="dashicons dashicons-no"></span></td>
                </tr>
                <tr id="parameter-loop">
                    <td class="parameter">loop</td>
                    <td class="description"><?php /* translators: 1: number/text used for infinite loop, 2: boolean value used for infinite loop, 3: bookmark for view parameter, 4: parameter for view, 5: bookmark for interval parameter, 6: parameter for interval */
						echo sprintf(__('Specify the number of complete loops to automatically play through a set of slides. Set to <span class="code">%1$s</span> or <span class="code">%2$s</span> for an infinite loop. Carousel must be active with both parameters set: <a href="%3$s">%4$s</a> and <a href="%5$s">%6$s</a>.', 'g-business-reviews-rating'), '-1', 'TRUE', '#parameter-view', 'view', '#parameter-interval', 'interval'); ?></td>
                    <td class="accepted">yes, no, true, false, infinite, <span class="code">-1</span>, <span class="code">0</span>, <span class="code">1</span>, <span class="code">2</span>, …</td>
                    <td class="default"><span class="code">0</span></td>
                    <td class="boolean"><span class="dashicons dashicons-yes"></span></td>
                    <td class="boolean"><span class="dashicons dashicons-no"></span></td>
                </tr>
                <tr id="parameter-interval">
                    <td class="parameter">interval</td>
                    <td class="description"><?php /* translators: 1: bookmark for view parameter, 2: parameter for carousel, 3: number/text used for infinite loop */
						echo sprintf(__('Interval time in seconds for each slide. Ensure carousel is active with <a href="%1$s">%2$s</a> and either <a href="%3$s">%4$s</a> or <a href="%5$s">%6$s</a> set.', 'g-business-reviews-rating'), '#parameter-view', 'view', '#parameter-iterations', 'iterations', '#parameter-loop', 'loop'); ?></td>
                    <td class="accepted"><span class="code">0.3</span> &ndash; <span class="code">50</span></td>
                    <td class="default"><span class="code">NULL</span></td>
                    <td class="boolean"><span class="dashicons dashicons-yes"></span></td>
                    <td class="boolean"><span class="dashicons dashicons-no"></span></td>
                </tr>
                <tr id="parameter-bullet">
                    <td class="parameter">bullet</td>
                    <td class="description"><?php /* translators: 1: bookmark for view parameter, 2: parameter for carousel, 3: number/text used for infinite loop */
						echo sprintf(__('Specify a bullet design or character for a carousel’s navigation. Disable navigation with <span class="code">%1$s</span>. Ensure carousel is active with <a href="%2$s">%3$s</a>.', 'g-business-reviews-rating'), 'FALSE', '#parameter-view', 'view'); ?></td>
                    <td class="accepted">yes, no, true, false, <span class="code">1</span>, <span class="code">0</span>, show, hide, on, off, <em><?php esc_html_e('Any string', 'g-business-reviews-rating'); ?></em></td>
                    <td class="default"><span class="bullet">●</span></td>
                    <td class="boolean"><span class="dashicons dashicons-yes"></span></td>
                    <td class="boolean"><span class="dashicons dashicons-no"></span></td>
                </tr>
                <tr id="parameter-attribution">
                    <td class="parameter">attribution</td>
                    <td class="description"><?php /* translators: for the moment, leave powered by Google unchanged — title attribute exists for other languages */ _e('Show or hide the &ldquo;powered by Google&rdquo; attribution.', 'g-business-reviews-rating'); ?></td>
                    <td class="accepted">yes, no, true, false, <span class="code">1</span>, <span class="code">0</span>, show, hide, on, off, light, dark</td>
                    <td class="default"><span class="code">TRUE</span></td>
                    <td class="boolean"><span class="dashicons dashicons-yes"></span></td>
                    <td class="boolean"><span class="dashicons dashicons-no"></span></td>
                </tr>
                <tr id="parameter-html-tags">
                    <td class="parameter">html_tags</td>
                    <td class="description"><?php /* translators: 1: heading HTML tag, 2: another heading HTML tag */
						echo sprintf(__('Set your own HTML tags for elements such as replacing <span class="code">%1$s</span> with <span class="code">%2$s</span>. Any sequence length accepted; separated by commas for: heading, vicinity, rating, list, list item, buttons, attribution and errors.', 'g-business-reviews-rating'), '&lt;h2&gt;', '&lt;h3&gt;'); ?></td>
                    <td class="accepted">h3 or &quot;h4, div, div, <em>…&quot;</em></td>
                    <td class="default"><?php echo esc_html('"' . implode(', ', $this->default_html_tags) . '"'); ?></td>
                    <td class="boolean"><span class="dashicons dashicons-yes"></span></td>
                    <td class="boolean"><span class="dashicons dashicons-no"></span></td>
                </tr>
                <tr id="parameter-class">
                    <td class="parameter">class</td>
                    <td class="description"><?php /* translators: %s: refers the ID of the next section and should remain unchanged */
						echo sprintf(__('Set the class attribute for main HTML element or the single anchor link. Refer to the <a href="%s">HTML Classes</a> for a comprehensive list of pre-defined styles and triggers. Separated by spaces; not commas.', 'g-business-reviews-rating'), '#classes'); ?></td>
                    <td class="accepted"><em><?php esc_html_e('Any valid string', 'g-business-reviews-rating'); ?></em></td>
                    <td class="default"><span class="code">NULL</span></td>
                    <td class="boolean"><span class="dashicons dashicons-yes"></span></td>
                    <td class="boolean"><span class="dashicons dashicons-yes"></span></td>
                </tr>
                <tr id="parameter-link-class">
                    <td class="parameter">link_class</td>
                    <td class="description"><?php _e('Specifically set the class attribute for a link or links. Separated by spaces; not commas.', 'g-business-reviews-rating'); ?></td>
                    <td class="accepted"><em><?php esc_html_e('Any valid string', 'g-business-reviews-rating'); ?></em></td>
                    <td class="default"><span class="code">NULL</span></td>
                    <td class="boolean"><span class="dashicons dashicons-yes"></span></td>
                    <td class="boolean"><span class="dashicons dashicons-yes"></span></td>
                </tr>
                <tr id="parameter-reviews-link-class">
                    <td class="parameter">reviews_link_class</td>
                    <td class="description"><?php _e('Set the class attribute for the Google reviews link only. Separated by spaces; not commas.', 'g-business-reviews-rating'); ?></td>
                    <td class="accepted"><em><?php esc_html_e('Any valid string', 'g-business-reviews-rating'); ?></em></td>
                    <td class="default"><span class="code">NULL</span></td>
                    <td class="boolean"><span class="dashicons dashicons-yes"></span></td>
                    <td class="boolean"><span class="dashicons dashicons-no"></span></td>
                </tr>
                <tr id="parameter-write-review-link-class">
                    <td class="parameter">write_review_link_class</td>
                    <td class="description"><?php _e('Set the class attribute for the Write a Google review link only. Separated by spaces; not commas.', 'g-business-reviews-rating'); ?></td>
                    <td class="accepted"><em><?php esc_html_e('Any valid string', 'g-business-reviews-rating'); ?></em></td>
                    <td class="default"><span class="code">NULL</span></td>
                    <td class="boolean"><span class="dashicons dashicons-yes"></span></td>
                    <td class="boolean"><span class="dashicons dashicons-no"></span></td>
                </tr>
                <tr id="parameter-target">
                    <td class="parameter">target</td>
                    <td class="description"><?php /* translators: 1: the target attribute 2: parameter value for NULL */
						echo sprintf(__('Set the anchor link’s <a href="https://www.w3schools.com/tags/att_a_target.asp" class="components-external-link" target="_blank">%1$s</a>. Empty (<span class="code">%2$s</span>) to remove attribute.', 'g-business-reviews-rating'), 'target', 'NULL'); ?></td>
                    <td class="accepted"><span class="code">NULL</span>, <em><?php esc_html_e('Any valid string', 'g-business-reviews-rating'); ?></em></td>
                    <td class="default">_blank</td>
                    <td class="boolean"><span class="dashicons dashicons-yes"></span></td>
                    <td class="boolean"><span class="dashicons dashicons-yes"></span></td>
                </tr>
                <tr id="parameter-rel">
                    <td class="parameter">rel</td>
                    <td class="description"><?php /* translators: 1: the rel attribute 2: parameter value for NULL */
						echo sprintf(__('Set the anchor link’s <a href="https://www.w3schools.com/tags/att_a_rel.asp" class="components-external-link" target="_blank"><abbr title="relationship">%1$s</abbr> attribute</a>. Empty (<span class="code">%2$s</span>) to remove attribute.', 'g-business-reviews-rating'), 'rel', 'NULL'); ?></td>
                    <td class="accepted"><span class="code">NULL</span>, author, bookmark, external, nofollow, noopener, noreferrer</td>
                    <td class="default">nofollow</td>
                    <td class="boolean"><span class="dashicons dashicons-yes"></span></td>
                    <td class="boolean"><span class="dashicons dashicons-yes"></span></td>
                </tr>
                <tr id="parameter-stylesheet">
                    <td class="parameter">stylesheet</td>
                    <td class="description"><?php _e('Choose to not load the style sheet that makes your rating and reviews look good. <em>Not recommended as a parameter.</em>', 'g-business-reviews-rating'); ?></td>
                    <td class="accepted">yes, no, true, false, <span class="code">1</span>, <span class="code">0</span>, show, hide, on, off</td>
                    <td class="default"><span class="code">TRUE</span></td>
                    <td class="boolean"><span class="dashicons dashicons-yes"></span></td>
                    <td class="boolean"><span class="dashicons dashicons-no"></span></td>
                </tr>
                <tr id="parameter-multiplier">
                    <td class="parameter">multiplier</td>
                    <td class="description"><?php /* translators: 1: parameter value for HTML entities used in overall rating star, 2: bookmark link, 3: parameter value to trigger script for previous version of plugin */
						echo sprintf(__('If the stars aren’t aligning in the overall rating, you can modify this value to adjust the width. Only applicable when used with stars parameter: <em>%1$s</em> or <a href="%2$s">class parameter</a>: <em>%3$s</em>.', 'g-business-reviews-rating'), 'html', '#parameter-class', 'version-1'); ?></td>
                    <td class="accepted"><em><?php esc_html_e('Positive float number:', 'g-business-reviews-rating'); ?></em> <span class="code">0.001</span> &ndash; <span class="code">10</span></td>
                    <td class="default"><span class="code">0.196</span></td>
                    <td class="boolean"><span class="dashicons dashicons-yes"></span></td>
                    <td class="boolean"><span class="dashicons dashicons-no"></span></td>
                </tr>
                <tr id="parameter-errors">
                    <td class="parameter">errors</td>
                    <td class="description"><?php /* translators: 1: WP_DEBUG definition name, 2: WordPress configuration file name */
						echo sprintf(__('You can choose to hide error notices caused by lack of reviews, filtering that leads to no reviews or lack of source data. Defaults to <span class="code">%1$s</span> if defined in <em>%2$s</em>.', 'g-business-reviews-rating'), 'WP_DEBUG', 'wp-config.php'); ?></td>
                    <td class="accepted">yes, no, true, false, <span class="code">1</span>, <span class="code">0</span>, show, hide, on, off</td>
                    <td class="default"><span class="code">FALSE</span></td>
                    <td class="boolean"><span class="dashicons dashicons-yes"></span></td>
                    <td class="boolean"><span class="dashicons dashicons-yes"></span></td>
                </tr>
            </table>
            
            <h2 id="classes"><?php esc_html_e('HTML Classes', 'g-business-reviews-rating'); ?></h2>
            <p><?php /* translators: 1: bookmark for parameter theme, 2: name of theme parameter, 3: bookmark for parameter class, 4: name of class parameter */
				echo sprintf(__('Stylistically, you may wish to make changes that are beyond the list of themes. Here is a list of HTML classes that can be used by <a href="%1$s">%2$s</a> or <a href="%3$s">%4$s</a> parameter to set your design and functionality preferences.', 'g-business-reviews-rating'), '#parameter-theme', 'theme', '#parameter-class', 'class'); ?></p>
            <table class="wp-list-table widefat fixed striped classes">
              <tr>
                  <th class="class"><?php esc_html_e('Class', 'g-business-reviews-rating'); ?></th>
                  <th class="description"><?php esc_html_e('Description', 'g-business-reviews-rating'); ?></th>
                  <th class="boolean"><?php esc_html_e('Reviews', 'g-business-reviews-rating'); ?></th>
                  <th class="boolean"><?php esc_html_e('Links', 'g-business-reviews-rating'); ?></th>
              </tr>
              <tr id="class-light">
                  <td class="class">light</td>
                  <td class="description"><?php _e('This is the default theme and implied, so it doesn’t need to be specified.', 'g-business-reviews-rating'); ?></td>
                  <td class="boolean"><span class="dashicons dashicons-yes"></span></td>
                  <td class="boolean"><span class="dashicons dashicons-no"></span></td>
              </tr>
                <tr>
                  <td class="class">dark</td>
                  <td class="description"><?php _e('For pages or sections with dark backgrounds — some text and icons have their colors inverted compared to the default, light background.', 'g-business-reviews-rating'); ?></td>
                  <td class="boolean"><span class="dashicons dashicons-yes"></span></td>
                  <td class="boolean"><span class="dashicons dashicons-no"></span></td>
                </tr>
                <tr>
                  <td class="class">fonts</td>
                  <td class="description"><?php _e('Uses fonts, styling and sizing found in the Google review listings.', 'g-business-reviews-rating'); ?></td>
                  <td class="boolean"><span class="dashicons dashicons-yes"></span></td>
                  <td class="boolean"><span class="dashicons dashicons-no"></span></td>
                </tr>
                <tr>
                  <td class="class">badge</td>
                  <td class="description"><?php _e('A more compact version to summarize your business’ listing on Google, better without reviews.', 'g-business-reviews-rating'); ?></td>
                  <td class="boolean"><span class="dashicons dashicons-yes"></span></td>
                  <td class="boolean"><span class="dashicons dashicons-no"></span></td>
                </tr>
                <tr>
                  <td class="class">tiny</td>
                  <td class="description"><?php _e('Even smaller styling of fonts with some elements hidden by default. Works well with badge to summarize your business’ listing on Google. Recommend setting your own max-width value in your style sheet.', 'g-business-reviews-rating'); ?></td>
                  <td class="boolean"><span class="dashicons dashicons-yes"></span></td>
                  <td class="boolean"><span class="dashicons dashicons-no"></span></td>
                </tr>
                <tr>
                  <td class="class">narrow</td>
                  <td class="description"><?php _e('If the shortcode is placed in a narrow container and text or elements are jumping to a new lines, try applying this class to handle the narrow space in a neater fashion.', 'g-business-reviews-rating'); ?></td>
                  <td class="boolean"><span class="dashicons dashicons-yes"></span></td>
                  <td class="boolean"><span class="dashicons dashicons-no"></span></td>
                </tr>
                <tr>
                  <td class="class">center</td>
                  <td class="description"><?php _e('Centrally align all text and elements. Can be applied to any theme but some may already share its styling.', 'g-business-reviews-rating'); ?></td>
                  <td class="boolean"><span class="dashicons dashicons-yes"></span></td>
                  <td class="boolean"><span class="dashicons dashicons-no"></span></td>
                </tr>
                <tr>
                  <td class="class">columns</td>
                  <td class="description"><?php /* translators: 1: the English class name number two, 2: the English class name number three, 3: the English class name number four, 4: the English class name number five, 5: the English class name number six */
					echo sprintf(__('Place reviews into multiple columns; requires an accompanying class: %1$s, %2$s, %3$s, %4$s or %5$s.', 'g-business-reviews-rating'), 'two', 'three', 'four', 'five', 'six'); ?></td>
                  <td class="boolean"><span class="dashicons dashicons-yes"></span></td>
                  <td class="boolean"><span class="dashicons dashicons-no"></span></td>
                </tr>
                <tr>
                  <td class="class">two</td>
                  <td class="description"><?php _e('Set to two columns, must be used with columns class.', 'g-business-reviews-rating'); ?></td>
                  <td class="boolean"><span class="dashicons dashicons-yes"></span></td>
                  <td class="boolean"><span class="dashicons dashicons-no"></span></td>
                </tr>
                <tr>
                  <td class="class">three</td>
                  <td class="description"><?php _e('Set to three columns, must be used with columns class.', 'g-business-reviews-rating'); ?></td>
                  <td class="boolean"><span class="dashicons dashicons-yes"></span></td>
                  <td class="boolean"><span class="dashicons dashicons-no"></span></td>
                </tr>
                <tr>
                  <td class="class">four</td>
                  <td class="description"><?php _e('Set to four columns, must be used with columns class.', 'g-business-reviews-rating'); ?></td>
                  <td class="boolean"><span class="dashicons dashicons-yes"></span></td>
                  <td class="boolean"><span class="dashicons dashicons-no"></span></td>
                </tr>
                <tr>
                  <td class="class">five</td>
                  <td class="description"><?php _e('Set to five columns, must be used with columns class.', 'g-business-reviews-rating'); ?></td>
                  <td class="boolean"><span class="dashicons dashicons-yes"></span></td>
                  <td class="boolean"><span class="dashicons dashicons-no"></span></td>
                </tr>
                <tr>
                  <td class="class">six</td>
                  <td class="description"><?php _e('Set to six columns, must be used with columns class.', 'g-business-reviews-rating'); ?></td>
                  <td class="boolean"><span class="dashicons dashicons-yes"></span></td>
                  <td class="boolean"><span class="dashicons dashicons-no"></span></td>
                </tr>
                <tr>
                  <td class="class">justify</td>
                  <td class="description"><?php _e('Review text can be justified — works better with narrow spaces or columns.', 'g-business-reviews-rating'); ?></td>
                  <td class="boolean"><span class="dashicons dashicons-yes"></span></td>
                  <td class="boolean"><span class="dashicons dashicons-no"></span></td>
                </tr>
                <tr>
                  <td class="class">fill</td>
                  <td class="description"><?php _e('Useful for badge and tiny themes — fill the background with a solid color that works with light or dark. Recommend using your own styling to set a specific color.', 'g-business-reviews-rating'); ?></td>
                  <td class="boolean"><span class="dashicons dashicons-yes"></span></td>
                  <td class="boolean"><span class="dashicons dashicons-no"></span></td>
                </tr>
                <tr>
                  <td class="class">padding</td>
                  <td class="description"><?php _e('Works well with fill — add some padding between the main element and its children.', 'g-business-reviews-rating'); ?></td>
                  <td class="boolean"><span class="dashicons dashicons-yes"></span></td>
                  <td class="boolean"><span class="dashicons dashicons-no"></span></td>
                </tr>
                <tr>
                  <td class="class">bubble</td>
                  <td class="description"><?php _e('Review text has a bubble appearance.', 'g-business-reviews-rating'); ?></td>
                  <td class="boolean"><span class="dashicons dashicons-yes"></span></td>
                  <td class="boolean"><span class="dashicons dashicons-no"></span></td>
                </tr>
                <tr>
                  <td class="class">tile</td>
                  <td class="description"><?php _e('Add the tile effect to all reviews in the list.', 'g-business-reviews-rating'); ?></td>
                  <td class="boolean"><span class="dashicons dashicons-yes"></span></td>
                  <td class="boolean"><span class="dashicons dashicons-no"></span></td>
                </tr>
                <tr>
                  <td class="class">outline</td>
                  <td class="description"><?php _e('Add the outline effect, the same as the badge theme, to other themes.', 'g-business-reviews-rating'); ?></td>
                  <td class="boolean"><span class="dashicons dashicons-yes"></span></td>
                  <td class="boolean"><span class="dashicons dashicons-no"></span></td>
                </tr>
                <tr>
                  <td class="class">no-outline</td>
                  <td class="description"><?php _e('Useful for badge — remove the outline effect (box-shadow). Helpful if you want to use a parent element’s styling.', 'g-business-reviews-rating'); ?></td>
                  <td class="boolean"><span class="dashicons dashicons-yes"></span></td>
                  <td class="boolean"><span class="dashicons dashicons-no"></span></td>
                </tr>
                <tr>
                  <td class="class">stripe</td>
                  <td class="description"><?php _e('Alternatively stripe the main sections with alternating backgrounds.', 'g-business-reviews-rating'); ?></td>
                  <td class="boolean"><span class="dashicons dashicons-yes"></span></td>
                  <td class="boolean"><span class="dashicons dashicons-no"></span></td>
                </tr>
                <tr>
                  <td class="class">contrast</td>
                  <td class="description"><?php _e('Used with stripe to offer a more contrasting version of the alternating backgrounds with dark/light text.', 'g-business-reviews-rating'); ?></td>
                  <td class="boolean"><span class="dashicons dashicons-yes"></span></td>
                  <td class="boolean"><span class="dashicons dashicons-no"></span></td>
                </tr>
                <tr>
                  <td class="class">spaced</td>
                  <td class="description"><?php _e('Space out the rating and submission dates in each review item.', 'g-business-reviews-rating'); ?></td>
                  <td class="boolean"><span class="dashicons dashicons-yes"></span></td>
                  <td class="boolean"><span class="dashicons dashicons-no"></span></td>
                </tr>
                <tr>
                  <td class="class">google-icon-white</td>
                  <td class="description"><?php _e('Override the default Google icon with a monochrome white version. Used for tiny badges and links.', 'g-business-reviews-rating'); ?></td>
                  <td class="boolean"><span class="dashicons dashicons-yes"></span></td>
                  <td class="boolean"><span class="dashicons dashicons-yes"></span></td>
                </tr>
                <tr>
                  <td class="class">google-icon-black</td>
                  <td class="description"><?php _e('Override the default Google icon with a monochrome black version. Used for tiny badges and links.', 'g-business-reviews-rating'); ?></td>
                  <td class="boolean"><span class="dashicons dashicons-yes"></span></td>
                  <td class="boolean"><span class="dashicons dashicons-yes"></span></td>
                </tr>
                <tr>
                  <td class="class">stars-html</td>
                  <td class="description"><?php /* translators: 1: bookmark for parameter stars, 2: parameter name for stars, 3: bookmark for parameter stars_gray, 4: parameter name for stars_gray */
						echo sprintf(__('Use HTML entities rather than SVG vector images in the overall rating stars. Alternatively, use the parameter: <a href="%1$s">%2$s</a> or <a href="%3$s">%4$s</a>.', 'g-business-reviews-rating'), '#parameter-stars', 'stars', '#parameter-stars-gray', 'stars_gray'); ?></td>
                  <td class="boolean"><span class="dashicons dashicons-yes"></span></td>
                  <td class="boolean"><span class="dashicons dashicons-no"></span></td>
                </tr>
                <tr>
                  <td class="class">stars-css</td>
                  <td class="description"><?php /* translators: 1: bookmark for parameter stars, 2: parameter name for stars, 3: bookmark for parameter stars_gray, 4: parameter name for stars_gray */
						echo sprintf(__('Apply style sheet colors to SVG vector images in the overall rating stars. Alternatively, use the parameter: <a href="%1$s">%2$s</a> or <a href="%3$s">%4$s</a>.', 'g-business-reviews-rating'), '#parameter-stars', 'stars', '#parameter-stars-gray', 'stars_gray'); ?></td>
                  <td class="boolean"><span class="dashicons dashicons-yes"></span></td>
                  <td class="boolean"><span class="dashicons dashicons-no"></span></td>
                </tr>
                <tr>
                  <td class="class">stars-orange</td>
                  <td class="description"><?php _e('Set the star color to orange; used with plugin for versions: 1.x – 3.x.', 'g-business-reviews-rating'); ?></td>
                  <td class="boolean"><span class="dashicons dashicons-yes"></span></td>
                  <td class="boolean"><span class="dashicons dashicons-no"></span></td>
                </tr>
                <tr>
                  <td class="class">bullet-square</td>
                  <td class="description"><?php _e('With a Carousel: set bullet to a square shape.', 'g-business-reviews-rating'); ?></td>
                  <td class="boolean"><span class="dashicons dashicons-yes"></span></td>
                  <td class="boolean"><span class="dashicons dashicons-no"></span></td>
                </tr>                
                <tr>
                  <td class="class">bullet-square-rounded</td>
                  <td class="description"><?php _e('With a Carousel: set bullet to a square shape with rounded edges.', 'g-business-reviews-rating'); ?></td>
                  <td class="boolean"><span class="dashicons dashicons-yes"></span></td>
                  <td class="boolean"><span class="dashicons dashicons-no"></span></td>
                </tr>                
                <tr>
                  <td class="class">bullet-diamond</td>
                  <td class="description"><?php _e('With a Carousel: set bullet to a diamond shape.', 'g-business-reviews-rating'); ?></td>
                  <td class="boolean"><span class="dashicons dashicons-yes"></span></td>
                  <td class="boolean"><span class="dashicons dashicons-no"></span></td>
                </tr>                
                <tr>
                  <td class="class">link</td>
                  <td class="description"><?php _e('This will trigger an overall link to the reviews on Google for the entire shortcode provided that no reviews are listed. This will already be applied in some instances.', 'g-business-reviews-rating'); ?></td>
                  <td class="boolean"><span class="dashicons dashicons-yes"></span></td>
                  <td class="boolean"><span class="dashicons dashicons-no"></span></td>
                </tr>
                <tr>
                  <td class="class">js-links</td>
                  <td class="description"><?php _e('If the more text does not expand, there may be another script interfering with click events. Try using this for a more basic approach.', 'g-business-reviews-rating'); ?></td>
                  <td class="boolean"><span class="dashicons dashicons-yes"></span></td>
                  <td class="boolean"><span class="dashicons dashicons-no"></span></td>
                </tr>
                <tr>
                  <td class="class">no-styles</td>
                  <td class="description"><?php _e('Alternative to clearing all applied styles using JavaScript.', 'g-business-reviews-rating'); ?></td>
                  <td class="boolean"><span class="dashicons dashicons-yes"></span></td>
                  <td class="boolean"><span class="dashicons dashicons-no"></span></td>
                </tr>
            </table>
        </form>
    </div>

<?php if ($this->count_reviews_all >= 1): ?>
	<div id="reviews" class="section<?php echo ($this->administrator && $this->section != 'reviews' || $this->editor && ($this->section == 'shortcodes' || $this->section == 'about')) ? ' hide' : ''; ?>">
		<h2><?php esc_html_e('Reviews', 'g-business-reviews-rating'); ?></h2>
		<p class="rating"><span class="rating-field"><?php esc_html_e('Rating:', 'g-business-reviews-rating'); ?></span> <span class="number"><?php echo esc_html($this->get_data('rating_rounded')); ?></span> <span class="all-stars"><?php echo esc_html(str_repeat('★', 5)); ?><span class="rating-stars" style="<?php echo esc_attr('width: ' . round(0.835 * $this->get_data('rating'), 2) . 'em;'); ?>"><?php echo esc_html(str_repeat('★', ceil($this->get_data('rating')))); ?></span></span> <span class="count"><?php echo esc_html(number_format_i18n($this->get_data('rating_count')) . ' ' . (($this->get_data('rating_count') == 1) ? __('review', 'g-business-reviews-rating') : __('reviews', 'g-business-reviews-rating'))) . (($this->get_data('rating_count') > $this->count_reviews_all || $this->reviews_count(TRUE, NULL, FALSE) != $this->reviews_count(NULL, NULL, FALSE)) ? '*' : ''); ?></span></p>
<?php if ($this->demo || $this->count_reviews_all > 5) : ?>
        <div class="review-filter">
            <p class="review-filter-query">
                <input type="search" id="review-filter" name="review-filter" placeholder="<?php esc_attr_e('Filter reviews', 'g-business-reviews-rating'); ?>">
            </p>
            <ul class="review-filter-ratings">
                <li id="review-filter-rating-5" class="review-rating rating-5 inactive">
                    <a href="#review-filter-rating-5" class="rating" data-rating="5" title="<?php /* translators: %u: refers rating number from 1 to 5 */
						printf(esc_attr(_n('%u star', '%u stars', 5, 'g-business-reviews-rating')), 5); ?>">
                        <?php echo str_repeat('★', 5); ?>
                    </a>
                    <a href="#review-filter-rating-5" class="line-holder" data-rating="5">
                        <span class="line"></span>
                    </a>
                    <a href="#review-filter-rating-5" class="rating-count" data-rating="5">0</a>
                </li>
                <li id="review-filter-rating-4" class="review-rating rating-4 inactive">
                    <a href="#review-filter-rating-4" class="rating" data-rating="4" title="<?php /* translators: %u: refers rating number from 1 to 5 */
						printf(esc_attr(_n('%u star', '%u stars', 4, 'g-business-reviews-rating')), 4); ?>">
                        <?php echo str_repeat('★', 4) . '<span class="not">' . str_repeat('★', 1) . '</span>'; ?>
                    </a>
                    <a href="#review-filter-rating-4" class="line-holder" data-rating="4">
                        <span class="line"></span>
                    </a>
                    <a href="#review-filter-rating-4" class="rating-count" data-rating="4">0</a>
                </li>
                <li id="review-filter-rating-3" class="review-rating rating-3 inactive">
                    <a href="#review-filter-rating-3" class="rating" data-rating="3" title="<?php /* translators: %u: refers rating number from 1 to 5 */
						printf(esc_attr(_n('%u star', '%u stars', 3, 'g-business-reviews-rating')), 3); ?>">
                        <?php echo str_repeat('★', 3) . '<span class="not">' . str_repeat('★', 2) . '</span>'; ?>
                    </a>
                    <a href="#review-filter-rating-3" class="line-holder" data-rating="3">
                        <span class="line"></span>
                    </a>
                    <a href="#review-filter-rating-3" class="rating-count" data-rating="3">0</a>
                </li>
                <li id="review-filter-rating-2" class="review-rating rating-2 inactive">
                    <a href="#review-filter-rating-2" class="rating" data-rating="2" title="<?php /* translators: %u: refers rating number from 1 to 5 */
						printf(esc_attr(_n('%u star', '%u stars', 2, 'g-business-reviews-rating')), 2); ?>">
                        <?php echo str_repeat('★', 2) . '<span class="not">' . str_repeat('★', 3) . '</span>'; ?>
                    </a>
                    <a href="#review-filter-rating-2" class="line-holder" data-rating="2">
                        <span class="line"></span>
                    </a>
                    <a href="#review-filter-rating-2" class="rating-count" data-rating="2">0</a>
                </li>
                <li id="review-filter-rating-1" class="review-rating rating-1 inactive">
                    <a href="#review-filter-rating-1" class="rating" data-rating="1" title="<?php /* translators: %u: refers rating number from 1 to 5 */
						printf(esc_attr(_n('%u star', '%u stars', 1, 'g-business-reviews-rating')), 1); ?>">
                        <?php echo str_repeat('★', 1) . '<span class="not">' . str_repeat('★', 4) . '</span>'; ?>
                    </a>
                    <a href="#review-filter-rating-1" class="line-holder" data-rating="1">
                        <span class="line"></span>
                    </a>
                    <a href="#review-filter-rating-1" class="rating-count" data-rating="1">0</a>
                </li>
            </ul>
        </div>
<?php endif; ?>
        <div class="reviews-table">
<?php echo $this->get_reviews('html'); ?>
        </div>
<?php if (!$this->demo && $this->reviews_count(TRUE, NULL, FALSE) != $this->reviews_count(NULL, NULL, FALSE)) : ?>
       <p class="note help">* <?php _e('Please note: this is the total for your current location and the total number of reviews listed at Google will not always match the number of reviews that are retrievable through its API.', 'g-business-reviews-rating'); ?></p>
<?php elseif ($this->demo || $this->get_data('rating_count') > $this->count_reviews_all): ?>
		<p class="note help">* <?php _e('Please note: the total number of reviews listed at Google will not always match the number of reviews that are retrievable through its API.', 'g-business-reviews-rating'); ?></p>
<?php endif; ?>
	</div>
<?php endif; ?>

<?php if ($this->administrator && $this->retrieved_data_check()): ?>
	<div id="data" class="section<?php echo ($this->section != 'data') ? ' hide' : ''; ?>">
		<h2><?php esc_html_e('Retrieved Data', 'g-business-reviews-rating'); ?></h2>
		<?php echo $this->get_data('html'); ?>
<?php if (!$this->retrieved_data_check(TRUE)) : ?> 
<?php if (!$this->retrieved_data_check(FALSE)) : ?>
		<h2><?php esc_html_e('Most Recent Valid Retrieved Data', 'g-business-reviews-rating'); ?></h2>
<?php endif; ?>
        <p><?php /* translators: 1: URL of reviews on Google, 2: URL of Place Finder */ 
		echo sprintf(__('This is the last successfully retrieved data from Google and will be used in the website. While your current reviews may still be visible on <a href="%1$s" class="components-external-link" target="_blank">Google</a>, they are no longer being retrieved.
		Please check and update your <a href="%2$s" class="components-external-link" target="_blank">Place ID</a> if you wish to regain full functionality.', 'g-business-reviews-rating'), 'https://search.google.com/local/reviews?placeid=' . esc_attr($this->place_id), 'https://developers.google.com/places/place-id'); ?></p>
<?php echo $this->get_data('html', NULL, TRUE); ?>
<?php endif; ?>
	</div>
<?php endif; ?>

<?php if ($this->administrator) : ?>
	<div id="advanced" class="section<?php echo ($this->section != 'advanced') ? ' hide' : ''; ?>">
        <nav class="section-bookmarks html-import-hide">
            <ul>
                <li><a href="#advanced-import"><?php esc_html_e('Import', 'g-business-reviews-rating'); ?></a></li>
                <li><a href="#advanced-styles-scripts"><?php esc_html_e('Styles and Scripts', 'g-business-reviews-rating'); ?></a></li>
                <li><a href="#advanced-roles"><?php esc_html_e('Roles', 'g-business-reviews-rating'); ?></a></li>
                <li><a href="#advanced-cache"><?php esc_html_e('Cache', 'g-business-reviews-rating'); ?></a></li>
                <li><a href="#advanced-reset"><?php esc_html_e('Reset', 'g-business-reviews-rating'); ?></a></li>
            </ul>
        </nav>
		<h2><?php esc_html_e('Advanced', 'g-business-reviews-rating'); ?></h2>
		<h3 id="advanced-import"><?php esc_html_e('Import', 'g-business-reviews-rating'); ?></h3>
<?php if (!$this->demo && $this->place_id != NULL && $this->count_reviews_all >= 5): ?>
        <form method="post" action="options.php" id="google-business-reviews-rating-settings-html-import" data-nonce="<?php echo esc_attr(wp_create_nonce('gmbrr_nonce')); ?>">
            <div class="introduction html-import-hide">
<?php /* translators: 1: URL of reviews on Google, 2: URL of diagram image, 3: URL of diagram image, 4: URL of diagram image */ 
	echo sprintf(__('
                <div class="entry-content advanced">
                    <p>Okay, this bit is little advanced, if you can use the browser’s inspector, you can load <em>all</em> the Google Reviews into your website with approximate dates.</p>
                    <ol id="html-import-instructions">
                        <li>Go to your <a href="%1$s" class="components-external-link" target="_blank">Google Reviews</a>;</li>
                        <li>Wait until it loads; expand all reviews by scrolling down;</li>
                        <li>Inspect the overall popup — on the outer white margin: <span class="right-click">right click</span> | <em>Inspect</em> (Fig. 1, 2);</li>
                        <li>In the HTML Inspector panel, <span class="right-click">right click</span> on the &lt;div&gt; that highlights all the reviews and <em>Copy | Outer HTML</em> (Fig. 3);</li>
                        <li>Paste this HTML into the <label for="html-import">textarea below</label>:</li>
                    </ol>
                </div>
                <div class="entry-meta advanced">
                    <ul id="html-import-figures">
                        <li id="html-import-figure-1"><img src="%2$s" alt="Fig. 1: Import Step 3, Part 1"><span class="caption"><strong>Fig 1:</strong> In the margin, <span class="right-click">right click</span>.</span></li>
                        <li id="html-import-figure-2"><img src="%3$s" alt="Fig. 2: Import Step 3, Part 2"><span class="caption"><strong>Fig 2:</strong> Select <em>Inspect</em>.</span></li>
                        <li id="html-import-figure-3"><img src="%4$s" alt="Fig. 3: Import Step 4"><span class="caption"><strong>Fig 3:</strong> <span class="right-click">Right click</span> on the highlighted &lt;div&gt; tag and click <em>Copy | Outer HTML</em>.</span></li>
                    </ul>
                </div>', 'g-business-reviews-rating'),
                esc_attr('https://search.google.com/local/reviews?placeid=' . $this->place_id),
                esc_attr(plugin_dir_url(__FILE__) . 'images/advanced-html-import-step-3a.jpg'),
                esc_attr(plugin_dir_url(__FILE__) . 'images/advanced-html-import-step-3b.jpg'),
                esc_attr(plugin_dir_url(__FILE__) . 'images/advanced-html-import-step-4.jpg')); ?>
            </div>
            <p class="html-import">
                <textarea id="html-import" class="html-import-hide" name="html-import" data-relative-times="<?php echo esc_attr(json_encode($this->relative_times)); ?>" data-languages="<?php echo esc_attr(json_encode($this->languages)); ?>" placeholder="<?php echo '&lt;div class=&quot;c9QyIf&quot;&gt;&lt;div class=&quot;review-dialog-top&quot;&gt;' . esc_attr(__('HTML from your Reviews on Google', 'g-business-reviews-rating')) . '&lt;/div&gt;&lt;/div&gt;'; ?>"></textarea>
                <select id="html-import-review-text" name="html-import-review-text" class="html-import-hide">
                    <option value="original" selected><?php esc_html_e('Only import original', 'g-business-reviews-rating'); ?></option>
                    <option value="translation"><?php esc_html_e('Only import translation', 'g-business-reviews-rating'); ?></option>
                    <option value=""><?php esc_html_e('Import full review text', 'g-business-reviews-rating'); ?></option>
                </select>
                <label id="html-import-empty-label" class="html-import-hide" for="html-import-empty"><input type="checkbox" id="html-import-empty" name="html-import-empty" value="1" checked="checked"> <?php esc_html_e('Import empty reviews', 'g-business-reviews-rating') ?></label>
                <label id="html-import-existing-label" class="html-import-hide" for="html-import-existing"><input type="checkbox" id="html-import-existing" name="html-import-existing" value="1"> <?php esc_html_e('Show existing entries', 'g-business-reviews-rating') ?></label>
            </p>
            <p class="submit">
                <button type="button" name="import-process" id="import-process-button" class="button button-primary html-import-hide"><?php esc_html_e('Process', 'g-business-reviews-rating'); ?></button>
                <button type="button" name="import" id="import-button" class="button button-primary"><?php esc_html_e('Import', 'g-business-reviews-rating'); ?></button>
                <button type="button" name="import-clear" id="import-clear-button" class="button button-secondary"><?php esc_html_e('Clear', 'g-business-reviews-rating'); ?></button>
            </p>
        </form>
<?php else: ?>
        <p><?php _e('This section is only available when the following criteria are met:', 'g-business-reviews-rating'); ?></p>
        <ul class="checklist">
        	<li><?php echo (!$this->demo) ? '<span class="dashicons dashicons-yes"></span>' : '<span class="dashicons dashicons-no"></span>'; ?> <?php _e('Demo mode is inactive;', 'g-business-reviews-rating'); ?></li>
        	<li><?php echo ($this->valid()) ? '<span class="dashicons dashicons-yes"></span>' : '<span class="dashicons dashicons-no"></span>'; ?> <?php _e('API Key and Place ID are both set and valid;', 'g-business-reviews-rating'); ?></li>
        	<li><?php echo ($this->demo) ? '<span class="dashicons dashicons-minus"></span>' : (($this->count_reviews_all >= 5) ? '<span class="dashicons dashicons-yes"></span>' : '<span class="dashicons dashicons-no"></span>'); ?> <?php _e('Five or more reviews retrieved from Google.', 'g-business-reviews-rating'); ?></li>
        </ul>
<?php endif; ?>
        <form method="post" action="options.php" id="google-business-reviews-rating-settings-styles-scripts" class="html-import-hide" data-nonce="<?php echo esc_attr(wp_create_nonce('gmbrr_nonce')); ?>">
            <h3 id="advanced-styles-scripts"><?php esc_html_e('Styles and Scripts', 'g-business-reviews-rating'); ?></h3>
            <h4><?php _e('Custom Style Sheet', 'g-business-reviews-rating'); ?></h4>
            <p><?php _e('If you prefer to manage your style sheet outside of your theme, you may add your own customized styles.', 'g-business-reviews-rating'); ?></p>
            <p>
                <textarea id="custom-styles" name="google_business_reviews_rating_custom_styles" placeholder="&#x2F;&#x2A;&#x20;CSS&#x20;Document&#x20;&#x2A;&#x2F;&#xA;&#xA;.google-business-reviews-rating.badge&#x20;{&#xA;&#x9;box-shadow:&#x20;0&#x20;14px&#x20;3px&#x20;-8px&#x20;rgba(0,&#x20;0,&#x20;0,&#x20;0.25),&#x20;0&#x20;0&#x20;0&#x20;3px&#x20;#F00&#x20;inset;&#xA;}"><?php echo esc_html(get_option('google_business_reviews_rating_custom_styles')); ?></textarea>
			</p>
            <h4><?php _e('Style Sheet', 'g-business-reviews-rating'); ?></h4>
            <p class="input">
                <label for="stylesheet-standard"><input type="radio" id="stylesheet-standard" name="google_business_reviews_rating_stylesheet" value="1"<?php echo ((get_option('google_business_reviews_rating_stylesheet', 1) == 1) ? ' checked="checked"' : ''); ?>> <?php esc_html_e('Legible', 'g-business-reviews-rating'); ?></label>
                <label for="stylesheet-compressed"><input type="radio" id="stylesheet-compressed" name="google_business_reviews_rating_stylesheet" value="2"<?php echo ((preg_match('/^(?:2|(?:compress|minif[iy])(?:ed)?)$/i', get_option('google_business_reviews_rating_stylesheet', 1))) ? ' checked="checked"' : ''); ?>> <?php esc_html_e('Compressed', 'g-business-reviews-rating'); ?></label>
                <label for="stylesheet-none"><input type="radio" id="stylesheet-none" name="google_business_reviews_rating_stylesheet" value="0"<?php echo ((!preg_match('/^(?:[12]|(?:compress|minif[iy])(?:ed)?)$/i', get_option('google_business_reviews_rating_stylesheet', 1))) ? ' checked="checked"' : ''); ?>> <?php esc_html_e('None', 'g-business-reviews-rating'); ?></label>
            </p>
            <h4><?php _e('JavaScript', 'g-business-reviews-rating'); ?></h4>
            <p class="input">
                <label for="javascript-standard"><input type="radio" id="javascript-standard" name="google_business_reviews_rating_javascript" value="1"<?php echo ((get_option('google_business_reviews_rating_javascript', 1) == 1) ? ' checked="checked"' : ''); ?>> <?php esc_html_e('Legible', 'g-business-reviews-rating'); ?></label>
                <label for="javascript-compressed"><input type="radio" id="javascript-compressed" name="google_business_reviews_rating_javascript" value="2"<?php echo ((preg_match('/^(?:2|(?:compress|minif[iy])(?:ed)?)$/i', get_option('google_business_reviews_rating_javascript', 1))) ? ' checked="checked"' : ''); ?>> <?php esc_html_e('Compressed', 'g-business-reviews-rating'); ?></label>
                <label for="javascript-none"><input type="radio" id="javascript-none" name="google_business_reviews_rating_javascript" value="0"<?php echo ((!preg_match('/^(?:[12]|(?:compress|minif[iy])(?:ed)?)$/i', get_option('google_business_reviews_rating_javascript', 1))) ? ' checked="checked"' : ''); ?>> <?php esc_html_e('None', 'g-business-reviews-rating'); ?></label>
            </p>
            <p class="submit">
                <button type="button" name="save" id="styles-scripts-button" class="button button-primary"><?php esc_html_e('Save', 'g-business-reviews-rating'); ?></button>
            </p>
        </form>
        <form method="post" action="options.php" id="google-business-reviews-rating-settings-roles" class="html-import-hide" data-nonce="<?php echo esc_attr(wp_create_nonce('gmbrr_nonce')); ?>">
            <h3 id="advanced-roles"><?php esc_html_e('Roles', 'g-business-reviews-rating'); ?></h3>
            <p class="input">
                <label for="roles-editor"><input type="checkbox" id="roles-editor" name="google_business_reviews_rating_editor" value="1"<?php echo ((get_option('google_business_reviews_rating_editor', 1)) ? ' checked="checked"' : ''); ?>> <?php /* translators: Include the WordPress role of: Editor */
                esc_html_e('Allow Editors to manage reviews.', 'g-business-reviews-rating'); ?></label>
            </p>
            <p class="submit">
                <button type="button" name="save" id="roles-button" class="button button-primary"><?php esc_html_e('Save', 'g-business-reviews-rating'); ?></button>
            </p>
        </form>
<?php if (!$this->demo): ?>
        <form method="post" action="options.php" id="google-business-reviews-rating-settings-cache" class="html-import-hide" data-nonce="<?php echo esc_attr(wp_create_nonce('gmbrr_nonce')); ?>">
            <h3 id="advanced-cache"><?php esc_html_e('Cache', 'g-business-reviews-rating'); ?></h3>
            <p><?php _e('You may wish to clear the cache and retrieve fresh data from Google.', 'g-business-reviews-rating'); ?></p>
            <p class="submit">
                <button type="button" name="clear-cache" id="clear-cache-button" class="button button-primary"><?php esc_html_e('Clear Cache', 'g-business-reviews-rating'); ?></button>
            </p>
        </form>
<?php endif; ?>
        <form method="post" action="options.php" id="google-business-reviews-rating-settings-reset" class="html-import-hide" data-nonce="<?php echo esc_attr(wp_create_nonce('gmbrr_nonce')); ?>">
            <h3 id="advanced-reset"><?php esc_html_e('Reset', 'g-business-reviews-rating'); ?></h3>
            <p><?php _e('At times you may wish to clear all reviews, places, notifications or just start over.', 'g-business-reviews-rating'); ?></p>
            <p id="reset-confirm-text">
<?php if ($this->valid()): ?>
                <label for="reset-notifications"><input type="checkbox" id="reset-notifications" name="google_business_reviews_rating_reset_notifications" value="1"> <?php esc_html_e('Reset administrator notifications.', 'g-business-reviews-rating'); ?></label>
<?php endif; ?>
<?php if (is_array($this->places) && count($this->places) > 1): ?>
                <label for="remove-other-places"><input type="checkbox" id="remove-other-places" name="google_business_reviews_rating_remove_other_places" value="1"> <?php esc_html_e('Remove places and reviews other than the current place.', 'g-business-reviews-rating'); ?></label>
<?php endif; ?>
<?php if ($this->count_reviews_all > 5): ?>
                <label for="reset-reviews"><input type="checkbox" id="reset-reviews" name="google_business_reviews_rating_reset_reviews" value="1"> <?php esc_html_e('Clear the review archive.', 'g-business-reviews-rating'); ?></label>
<?php endif; ?>
                <label for="reset-all" class="reset-all"><input type="checkbox" id="reset-all" name="google_business_reviews_rating_reset_all" value="1"> <?php esc_html_e('Yes, I confirm I wish to reset everything.', 'g-business-reviews-rating'); ?></label>
			</p>
            <p class="submit">
                <button type="button" name="reset" id="reset-button" class="button button-primary"><?php esc_html_e('Reset', 'g-business-reviews-rating'); ?></button>
            </p>
        </form>
	</div>
<?php endif; ?>

	<div id="about" class="section<?php echo ($this->section != 'about') ? ' hide' : ''; ?>">
        <nav class="section-bookmarks">
            <ul>
                <li><a href="#about-story"><?php esc_html_e('About', 'g-business-reviews-rating'); ?></a></li>
                <li><a href="#about-developer-plugins"><?php esc_html_e('Plugins by the Developer', 'g-business-reviews-rating'); ?></a></li>
                <li><a href="#about-support"><?php esc_html_e('Support', 'g-business-reviews-rating'); ?></a></li>
                <li><a href="#about-follow"><?php esc_html_e('Follow Us', 'g-business-reviews-rating'); ?></a></li>
                <li><a href="#about-rate"><?php esc_html_e('Rate Us', 'g-business-reviews-rating'); ?></a></li>
                <li><a href="#about-donate"><?php esc_html_e('Donate', 'g-business-reviews-rating'); ?></a></li>
            </ul>
        </nav>
    	<div class="entry-content">
            <h2 id="about-story"><?php esc_html_e('About', 'g-business-reviews-rating'); ?></h2>
            <p class="about-story"><?php _e('This review plugin came about as a consequence of collecting a business’s opening times for a London-based web client. There was plenty of relevant data for the business offered by the Places API from Google — with data from Google Maps and Google My Business. The recent review data is available and, with some tweaks, it could be displayed anywhere in a similar style to Google’s reviews pop-up in the search results.', 'g-business-reviews-rating'); ?></p>
            <p class="about-story"><?php _e('To keep the reviews current, the Places API is called at regular intervals with new relevant reviews added to the catalog over time. The extensive Shortcode can be used in any post, or page or try the Widget to place directly into the sidebar or footer. Any Shortcode parameters will overwrite the default settings in the first tab. I have kept the style sheet minimal to allow for your customizations — as a web developer/designer, this is what I’d like to see for all plugins.', 'g-business-reviews-rating'); ?></p>
            <p class="about-story"><?php /* translators: 1: plugin author’s website, 2: WordPress.org URL for reviews  */ 
            echo sprintf(__('This is my first published plugin for WordPress so I’d appreciate any positive or negative <a href="%1$s" class="components-external-link" target="_blank">feedback</a> or leave your thoughts in a <a href="%2$s" class="components-external-link" target="_blank">review</a>. So if you have any comments or feature requests, please feel free to <a href="%1$s" class="components-external-link" target="_blank">get in touch</a> with me.', 'g-business-reviews-rating'), 'https://designextreme.com/wordpress/gmbrr/', 'https://wordpress.org/support/plugin/g-business-reviews-rating/reviews/#new-post'); ?></p>
            <p class="about-story sign-off">
                <span class="signature">
                    <svg xmlns="http://www.w3.org/2000/svg" xml:space="preserve" width="100" height="46" viewBox="-255.52 439.73 100 46" title="Noah H">
                        <path d="M-248.8 471.04s-3.66 7.23-4.67 8.75c-1.02 1.53-.6.58-.6.42-.02-.35 18.92-36.19 18.93-36.03.03.74-2.37 15.25-3.1 21.17-1.05 8.54-1.46 16.19-1.37 16.19.29 0 7.09-13.48 9.94-18.73 2.85-5.24 10.33-18.72 10.33-18.72"/>
                        <path d="M-224.06 468.56c-1.65-.13-7.5 2.82-9.3 6.5-1.4 2.9-.98 5.15 1.27 5.83 2.01.62 4.49-.17 6.47-2.1 6.04-5.88-.44-12.2-1.23-10.11-1.58 4.2 16.57 3.21 17.9 1.02.55-.91-3.7-.35-7.07 1.78-2.32 1.46-5.64 4.86-5.28 6.88.49 2.72 3.9 3.42 8.1-.12 3.27-2.76 7-9.45 6.88-9.2-2.49 4.92-3.65 12.3-2.06 11.84 4.43-1.3 16.25-27.22 17.3-28.86.32-.47 2.8-5.6.06-.79a798.44 798.44 0 0 0-11.35 21.43c-1.04 2.1-3.23 7.27-2.94 8.22.29.95 6.26-9.53 10.93-11.72 7.67-3.6.96 9.9 2.6 13.4"/>
                        <path d="M-168.67 447.73c-.42 1.03-4.49 9.56-5.81 12.4s-5.51 11.67-6.4 13.76c-2.57 6.05-4.35 8.15-4.88 7.1-.38-.74.36-4.1 1.72-8.5 1.38-4.45 3.17-7.3 3.26-7.3.1 0 2.88 3.45 6.27 4.59s8.52-4.28 11.07-7.57c2.55-3.3 6.92-13.74 6.92-13.74.28.12-3.98 8.72-7.5 16.21-3.52 7.5-8.81 19.73-8.81 19.73"/> 
                    </svg>
                </span>
            <?php /* translators: 1: author’s website, 2: author’s business name */ 
            echo sprintf(__('Developer, <a href="%1$s" class="components-external-link" target="_blank">%2$s</a>', 'g-business-reviews-rating'), 'https://designextreme.com', 'Design Extreme'); ?></p>
			<h2 id="about-developer-plugins"><?php if ($this->administrator) : ?><a href="<?php echo esc_attr(admin_url('plugin-install.php?s=designextreme&tab=search&type=author')); ?>"><?php endif; ?><?php esc_html_e('Plugins by the Developer', 'g-business-reviews-rating'); ?><?php if ($this->administrator) : ?></a><?php endif; ?></h2>
			<ul id="wordpress-plugin-list">
            	<li id="wordpress-plugin-g-business-reviews-rating">
                	<h3><a href="https://wordpress.org/plugins/g-business-reviews-rating/" class="components-external-link" target="_blank"><span class="icon"></span> Reviews and Rating – Google Reviews</a></h3>
                    <p>Shortcode and widget for Google reviews and rating. Give customers a chance to leave their own rating/review; includes Structured Data for SEO.</p>
                    <p class="more-details"><a href="https://wordpress.org/plugins/g-business-reviews-rating/" class="components-external-link" target="_blank"><?php esc_html_e('More Details', 'g-business-reviews-rating'); ?></a></p>
                    <p class="installed"><?php esc_html_e('Installed', 'g-business-reviews-rating'); ?></p>
                </li>
            	<li id="wordpress-plugin-open">
                	<h3><a href="https://wordpress.org/plugins/opening-hours/" class="components-external-link" target="_blank"><span class="icon"></span> We’re Open!</a></h3>
                    <p>Simple and easy to manage regular and special opening hours for your business, includes support for Structured Data and populating from Google My Business.</p>
                    <p class="more-details"><a href="https://wordpress.org/plugins/opening-hours/" class="components-external-link" target="_blank"><?php esc_html_e('More Details', 'g-business-reviews-rating'); ?></a></p>
<?php if (is_plugin_active('opening-hours/opening-hours.php')) : ?>
                    <p class="installed"><?php esc_html_e('Installed', 'g-business-reviews-rating'); ?></p>
<?php endif; ?>
                </li>
            </ul>
		</div>
    	<div class="entry-meta">
            <div class="widget plugin-support">
                <h3 id="about-support" class="widget-title"><?php esc_html_e('Support', 'g-business-reviews-rating'); ?></h3>
                <p class="aside"><?php esc_html_e('Do you have any general support queries? Please search our forums at WordPress or make your own contribution. You can see that we are always very quick to reply!', 'g-business-reviews-rating'); ?></p>
                <p><a class="button" href="https://wordpress.org/support/plugin/g-business-reviews-rating/"><span class="dashicons dashicons-editor-help"></span> <?php esc_html_e('View Support Forum', 'g-business-reviews-rating'); ?></a></p>			
            </div>
            <div class="widget plugin-social">
                <h3 id="about-follow" class="widget-title"><?php esc_html_e('Follow Us', 'g-business-reviews-rating'); ?></h3>
                <p class="aside"><?php esc_html_e('Want some easy-to-follow pro tips with examples? We will help you to make your reviews really stand out. Feature requests are welcome too.', 'g-business-reviews-rating'); ?></p>
                <p><a class="button" href="https://twitter.com/designextreme_"><span class="icon icon-x"><svg xmlns="http://www.w3.org/2000/svg" xml:space="preserve" viewBox="0 0 1668.56 1221.19"><path fill="currentColor" d="M336.33 142.25 722.72 658.9l-388.83 420.05h87.51l340.42-367.76 275.05 367.76h297.8l-408.13-545.7 361.92-390.99h-87.51l-313.51 338.7-253.31-338.7h-297.8zm128.69 64.46h136.81l604.13 807.76h-136.81L465.02 206.71z"/></svg></span> <?php esc_html_e('Follow Us', 'g-business-reviews-rating'); ?></a></p>			
            </div>
            <div class="widget plugin-ratings">
                <h3 id="about-rate" class="widget-title"><?php esc_html_e('Rate Us', 'g-business-reviews-rating'); ?></h3>
                <p class="aside"><?php esc_html_e('Love this plugin with as much heart as we’ve put into its code? Why not share your feedback to help others with their plugin decision.', 'g-business-reviews-rating'); ?></p>
                <p><a class="button" href="https://wordpress.org/support/plugin/g-business-reviews-rating/reviews/#new-post"><span class="dashicons dashicons-star-filled"></span> <?php esc_html_e('Add My Review', 'g-business-reviews-rating'); ?></a></p>			
            </div>
            <div class="widget plugin-donate">
                <h3 id="about-donate" class="widget-title"><?php esc_html_e('Donate', 'g-business-reviews-rating'); ?></h3>
                <p class="aside"><?php esc_html_e('This plugin is actually powered by oat flat whites… We welcome any show of support the advancement of this plugin, no matter how small.', 'g-business-reviews-rating'); ?></p>
                <p><a class="button button-secondary" href="https://paypal.me/designextreme"><span class="dashicons dashicons-heart"></span> <?php esc_html_e('Donate To This Plugin', 'g-business-reviews-rating'); ?></a></p>
            </div>
		</div>
	</div>
</div>
