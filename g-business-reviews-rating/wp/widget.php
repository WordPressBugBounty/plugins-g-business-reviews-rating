<?php

if (!defined('ABSPATH'))
{
	die();
}

class google_business_reviews_rating_widget extends WP_Widget
{
	private
		$alias = NULL,
		$reference = NULL,
		$first = NULL,
		$api_key = NULL,
		$place_id = NULL,
		$rating = NULL,
		$business_name = NULL,
		$business_icon = NULL,
		$demo = NULL,
		$user_ratings_total = NULL,
		$more = NULL,
		$theme = NULL,
		$result = [],
		$data = [],
		$reviews = [],
		$review_sort_option = NULL,
		$review_sort_options = [],
		$languages = [],
		$reviews_themes = [],
		$administrator = FALSE,
		$editor = FALSE,
		$plugin_url = NULL;
	
	public function __construct()
	{
		$this->alias = preg_replace('/^(.+)[_-][^_-]+$/', '$1', __CLASS__);
		$this->reference = preg_replace('/[^0-9a-z-]/', '-', $this->alias);
		$this->first = NULL;

		parent::__construct($this->alias, __('Reviews and Rating - Google Reviews', 'g-business-reviews-rating'), [
			'description' => __('Have your rating and a review showing in your sidebar', 'g-business-reviews-rating'),
			'classname' => $this->reference . '-widget'
		]);

		$this->set();

		add_action('admin_enqueue_scripts', [$this, 'css_load']);
		add_action('admin_enqueue_scripts', [$this, 'js_load']);
	}
	
	/* Fetched lazily so translations are loaded */

	private function set_lists(): bool
	{
		$plugin = google_business_reviews_rating_dashboard::get_instance();

		if ($plugin !== NULL)
		{
			$widget_data = $plugin->widget_data();
			$this->review_sort_options = $widget_data['review_sort_options'];
			$this->languages = $widget_data['languages'];
			$this->reviews_themes = $widget_data['reviews_themes'];
		}

		if (empty($this->reviews_themes))
		{
			$this->review_sort_options = [
			'relevance_desc' => [
				'name' => 'Relevance Descending',
				'min_max_values' => ['High', 'Low'],
				'field' => NULL,
				'asc' => FALSE
			],
			'relevance_asc' => [
				'name' => 'Relevance Ascending',
				'min_max_values' => ['Low', 'High'],
				'field' => NULL,
				'asc' => TRUE
			],
			'date_desc' => [
				'name' => 'Date Descending',
				'min_max_values' => ['New', 'Old'],
				'field' => 'time',
				'asc' => FALSE
			],
			'date_asc' => [
				'name' => 'Date Ascending',
				'min_max_values' => ['Old', 'New'],
				'field' => 'time',
				'asc' => TRUE
			],
			'rating_desc' => [
				'name' => 'Rating Descending',
				'min_max_values' => ['High', 'Low'],
				'field' => 'rating',
				'asc' => FALSE
			],
			'rating_asc' => [
				'name' => 'Rating Ascending',
				'min_max_values' => ['Low', 'High'],
				'field' => 'rating',
				'asc' => TRUE
			],
			'author_name_asc' => [
				'name' => 'Author’s Name Ascending',
				'min_max_values' => ['A', 'Z'],
				'field' => 'author_name',
				'asc' => TRUE
			],
			'author_name_desc' => [
				'name' => 'Author’s Name Descending',
				'min_max_values' => ['Z', 'A'],
				'field' => 'author_name',
				'asc' => FALSE
			],
			'id_asc' => [
				'name' => 'ID Ascending',
				'min_max_values' => ['Low', 'High'],
				'field' => 'id',
				'asc' => TRUE
			],
			'id_desc' => [
				'name' => 'ID Descending',
				'min_max_values' => ['High', 'Low'],
				'field' => 'id',
				'asc' => FALSE
			],
			'shuffle' => [
				'name' => 'Random Shuffle'
			]
		];
		$this->languages = [
			'af' => 'Afrikaans',
			'sq' => 'Albanian',
			'am' => 'Amharic',
			'ar' => 'Arabic',
			'hy' => 'Armenian',
			'az' => 'Azerbaijani',
			'eu' => 'Basque',
			'be' => 'Belarusian',
			'bn' => 'Bengali',
			'bs' => 'Bosnian',
			'bg' => 'Bulgarian',
			'my' => 'Burmese',
			'ca' => 'Catalan',
			'zh' => 'Chinese',
			'zh-CN' => 'Chinese (Simplified)',
			'zh-HK' => 'Chinese (Hong Kong)',
			'zh-TW' => 'Chinese (Traditional)',
			'hr' => 'Croatian',
			'cs' => 'Czech',
			'da' => 'Danish',
			'nl' => 'Dutch',
			'en' => 'English',
			'en-AU' => 'English (Australian)',
			'en-GB' => 'English (Great Britain)',
			'et' => 'Estonian',
			'fa' => 'Farsi',
			'fi' => 'Finnish',
			'fil' => 'Filipino',
			'fr' => 'French',
			'fr-CA' => 'French (Canada)',
			'gl' => 'Galician',
			'ka' => 'Georgian',
			'de' => 'German',
			'el' => 'Greek',
			'gu' => 'Gujarati',
			'iw' => 'Hebrew',
			'hi' => 'Hindi',
			'hu' => 'Hungarian',
			'is' => 'Icelandic',
			'id' => 'Indonesian',
			'it' => 'Italian',
			'ja' => 'Japanese',
			'kn' => 'Kannada',
			'kk' => 'Kazakh',
			'km' => 'Khmer',
			'ko' => 'Korean',
			'ky' => 'Kyrgyz',
			'lo' => 'Lao',
			'lv' => 'Latvian',
			'lt' => 'Lithuanian',
			'mk' => 'Macedonian',
			'ms' => 'Malay',
			'ml' => 'Malayalam',
			'mr' => 'Marathi',
			'mn' => 'Mongolian',
			'ne' => 'Nepali',
			'no' => 'Norwegian',
			'pl' => 'Polish',
			'pt' => 'Portuguese',
			'pt-BR' => 'Portuguese (Brazil)',
			'pt-PT' => 'Portuguese (Portugal)',
			'pa' => 'Punjabi',
			'ro' => 'Romanian',
			'ru' => 'Russian',
			'sr' => 'Serbian',
			'si' => 'Sinhalese',
			'sk' => 'Slovak',
			'sl' => 'Slovenian',
			'es' => 'Spanish',
			'es-419' => 'Spanish (Latin America)',
			'sw' => 'Swahili',
			'sv' => 'Swedish',
			'ta' => 'Tamil',
			'te' => 'Telugu',
			'th' => 'Thai',
			'tr' => 'Turkish',
			'uk' => 'Ukrainian',
			'ur' => 'Urdu',
			'uz' => 'Uzbek',
			'vi' => 'Vietnamese',
			'zu' => 'Zulu'
		];
		$this->reviews_themes = [
			'light' => __('Light Background', 'g-business-reviews-rating'),
			'light fonts' => __('Light Background with Fonts', 'g-business-reviews-rating'),
			'light tile' => __('Light Background, Tiled', 'g-business-reviews-rating'),
			'light fonts tile' => __('Light Background, Tiled with Fonts', 'g-business-reviews-rating'),
			'light center' => __('Centered, Light Background', 'g-business-reviews-rating'),
			'light center fonts' => __('Centered, Light Background with Fonts', 'g-business-reviews-rating'),
			'light center tile' => __('Centered, Light Background, Tiled', 'g-business-reviews-rating'),
			'light center fonts tile' => __('Centered, Light Background, Tiled with Fonts', 'g-business-reviews-rating'),
			'light narrow' => __('Narrow, Light Background', 'g-business-reviews-rating'),
			'light narrow fonts' => __('Narrow, Light Background with Fonts', 'g-business-reviews-rating'),
			'light narrow tile' => __('Narrow, Light Background, Tiled', 'g-business-reviews-rating'),
			'light narrow fonts tile' => __('Narrow, Light Background, Tiled with Fonts', 'g-business-reviews-rating'),
			'light center narrow' => __('Narrow, Centered, Light Background', 'g-business-reviews-rating'),
			'light center narrow fonts' => __('Narrow, Centered, Light Background with Fonts', 'g-business-reviews-rating'),
			'light center narrow tile' => __('Narrow, Centered, Light Background, Tiled', 'g-business-reviews-rating'),
			'light center narrow fonts tile' => __('Narrow, Centered, Light Background, Tiled with Fonts', 'g-business-reviews-rating'),
			'dark' => __('Dark Background', 'g-business-reviews-rating'),
			'dark fonts' => __('Dark Background with Fonts', 'g-business-reviews-rating'),
			'dark tile' => __('Dark Background, Tiled', 'g-business-reviews-rating'),
			'dark fonts tile' => __('Dark Background, Tiled with Fonts', 'g-business-reviews-rating'),
			'dark center' => __('Centered, Dark Background', 'g-business-reviews-rating'),
			'dark center fonts' => __('Centered, Dark Background with Fonts', 'g-business-reviews-rating'),
			'dark center tile' => __('Centered, Dark Background, Tiled', 'g-business-reviews-rating'),
			'dark center fonts tile' => __('Centered, Dark Background, Tiled with Fonts', 'g-business-reviews-rating'),
			'dark narrow' => __('Narrow, Dark Background', 'g-business-reviews-rating'),
			'dark narrow fonts' => __('Narrow, Dark Background with Fonts', 'g-business-reviews-rating'),
			'dark narrow tile' => __('Narrow, Dark Background, Tiled', 'g-business-reviews-rating'),
			'dark narrow fonts tile' => __('Narrow, Dark Background, Tiled with Fonts', 'g-business-reviews-rating'),
			'dark center narrow' => __('Narrow, Centered, Dark Background', 'g-business-reviews-rating'),
			'dark center narrow fonts' => __('Narrow, Centered, Dark Background with Fonts', 'g-business-reviews-rating'),
			'dark center narrow tile' => __('Narrow, Centered, Dark Background, Tiled', 'g-business-reviews-rating'),
			'dark center narrow fonts tile' => __('Narrow, Centered, Dark Background, Tiled with Fonts', 'g-business-reviews-rating'),
			'light bubble' => __('Light Background, Bubble Outline', 'g-business-reviews-rating'),
			'light bubble fonts' => __('Light, Bubble Outline with Fonts', 'g-business-reviews-rating'),
			'light bubble tile' => __('Light Background, Bubble Outline, Tiled', 'g-business-reviews-rating'),
			'light bubble fonts tile' => __('Light, Bubble Outline, Tiled with Fonts', 'g-business-reviews-rating'),
			'light bubble fill' => __('Light Background, Bubble Filled', 'g-business-reviews-rating'),
			'light bubble fill fonts' => __('Light, Bubble Filled with Fonts', 'g-business-reviews-rating'),
			'light bubble fill tile' => __('Light Background, Bubble Filled, Tiled', 'g-business-reviews-rating'),
			'light bubble fill fonts tile' => __('Light, Bubble Filled, Tiled with Fonts', 'g-business-reviews-rating'),
			'light bubble center' => __('Centered, Light, Bubble Outline', 'g-business-reviews-rating'),
			'light bubble center fonts' => __('Centered, Light, Bubble Outline with Fonts', 'g-business-reviews-rating'),
			'light bubble center tile' => __('Centered, Light, Bubble Outline, Tiled', 'g-business-reviews-rating'),
			'light bubble center fonts tile' => __('Centered, Light, Bubble Outline, Tiled with Fonts', 'g-business-reviews-rating'),
			'light bubble fill center' => __('Centered, Light, Bubble Filled', 'g-business-reviews-rating'),
			'light bubble fill center fonts' => __('Centered, Light, Bubble Filled with Fonts', 'g-business-reviews-rating'),
			'light bubble fill center tile' => __('Centered, Light, Bubble Filled, Tiled', 'g-business-reviews-rating'),
			'light bubble fill center fonts tile' => __('Centered, Light, Bubble Filled, Tiled with Fonts', 'g-business-reviews-rating'),
			'light bubble narrow' => __('Narrow, Light, Bubble Outline', 'g-business-reviews-rating'),
			'light bubble narrow fonts' => __('Narrow, Light, Bubble Outline with Fonts', 'g-business-reviews-rating'),
			'light bubble narrow tile' => __('Narrow, Light, Bubble Outline, Tiled', 'g-business-reviews-rating'),
			'light bubble narrow fonts tile' => __('Narrow, Light, Bubble Outline, Tiled with Fonts', 'g-business-reviews-rating'),
			'light bubble fill narrow' => __('Narrow, Light, Bubble Filled', 'g-business-reviews-rating'),
			'light bubble fill narrow fonts' => __('Narrow, Light, Bubble Filled with Fonts', 'g-business-reviews-rating'),
			'light bubble fill narrow tile' => __('Narrow, Light, Bubble Filled, Tiled', 'g-business-reviews-rating'),
			'light bubble fill narrow fonts tile' => __('Narrow, Light, Bubble Filled, Tiled with Fonts', 'g-business-reviews-rating'),
			'light bubble center narrow' => __('Narrow, Centered, Light, Bubble Outline', 'g-business-reviews-rating'),
			'light bubble center narrow fonts' => __('Narrow, Centered, Light, Bubble Outline with Fonts', 'g-business-reviews-rating'),
			'light bubble center narrow tile' => __('Narrow, Centered, Light, Bubble Outline, Tiled', 'g-business-reviews-rating'),
			'light bubble center narrow fonts tile' => __('Narrow, Centered, Light, Bubble Outline, Tiled with Fonts', 'g-business-reviews-rating'),
			'light bubble fill center narrow' => __('Narrow, Centered, Light, Bubble Filled', 'g-business-reviews-rating'),
			'light bubble fill center narrow fonts' => __('Narrow, Centered, Light, Bubble Filled with Fonts', 'g-business-reviews-rating'),
			'light bubble fill center narrow tile' => __('Narrow, Centered, Light, Bubble Filled, Tiled', 'g-business-reviews-rating'),
			'light bubble fill center narrow fonts tile' => __('Narrow, Centered, Light, Bubble Filled, Tiled with Fonts', 'g-business-reviews-rating'),
			'dark bubble' => __('Dark, Bubble Outline', 'g-business-reviews-rating'),
			'dark bubble fonts' => __('Dark, Bubble Outline with Fonts', 'g-business-reviews-rating'),
			'dark bubble tile' => __('Dark, Bubble Outline, Tiled', 'g-business-reviews-rating'),
			'dark bubble fonts tile' => __('Dark, Bubble Outline, Tiled with Fonts', 'g-business-reviews-rating'),
			'dark bubble fill' => __('Dark, Bubble Filled', 'g-business-reviews-rating'),
			'dark bubble fill fonts' => __('Dark, Bubble Filled with Fonts', 'g-business-reviews-rating'),
			'dark bubble fill tile' => __('Dark, Bubble Filled, Tiled', 'g-business-reviews-rating'),
			'dark bubble fill fonts tile' => __('Dark, Bubble Filled, Tiled with Fonts', 'g-business-reviews-rating'),
			'dark bubble center' => __('Centered, Dark, Bubble Outline', 'g-business-reviews-rating'),
			'dark bubble center fonts' => __('Centered, Dark, Bubble Outline with Fonts', 'g-business-reviews-rating'),
			'dark bubble center tile' => __('Centered, Dark, Bubble Outline, Tiled', 'g-business-reviews-rating'),
			'dark bubble center fonts tile' => __('Centered, Dark, Bubble Outline, Tiled with Fonts', 'g-business-reviews-rating'),
			'dark bubble fill center' => __('Centered, Dark, Bubble Filled', 'g-business-reviews-rating'),
			'dark bubble fill center fonts' => __('Centered, Dark, Bubble Filled with Fonts', 'g-business-reviews-rating'),
			'dark bubble fill center tile' => __('Centered, Dark, Bubble Filled, Tiled', 'g-business-reviews-rating'),
			'dark bubble fill center fonts tile' => __('Centered, Dark, Bubble Filled, Tiled with Fonts', 'g-business-reviews-rating'),
			'dark bubble narrow' => __('Narrow, Dark, Bubble Outline', 'g-business-reviews-rating'),
			'dark bubble narrow fonts' => __('Narrow, Dark, Bubble Outline with Fonts', 'g-business-reviews-rating'),
			'dark bubble narrow tile' => __('Narrow, Dark, Bubble Outline, Tiled', 'g-business-reviews-rating'),
			'dark bubble narrow fonts tile' => __('Narrow, Dark, Bubble Outline, Tiled with Fonts', 'g-business-reviews-rating'),
			'dark bubble fill narrow' => __('Narrow, Dark, Bubble Filled', 'g-business-reviews-rating'),
			'dark bubble fill narrow fonts' => __('Narrow, Dark, Bubble Filled with Fonts', 'g-business-reviews-rating'),
			'dark bubble fill narrow tile' => __('Narrow, Dark, Bubble Filled, Tiled', 'g-business-reviews-rating'),
			'dark bubble fill narrow fonts tile' => __('Narrow, Dark, Bubble Filled, Tiled with Fonts', 'g-business-reviews-rating'),
			'dark bubble center narrow' => __('Narrow, Centered, Dark, Bubble Outline', 'g-business-reviews-rating'),
			'dark bubble center narrow fonts' => __('Narrow, Centered, Dark, Bubble Outline with Fonts', 'g-business-reviews-rating'),
			'dark bubble center narrow tile' => __('Narrow, Centered, Dark, Bubble Outline, Tiled', 'g-business-reviews-rating'),
			'dark bubble center narrow fonts tile' => __('Narrow, Centered, Dark, Bubble Outline, Tiled with Fonts', 'g-business-reviews-rating'),
			'dark bubble fill center narrow' => __('Narrow, Centered, Dark, Bubble Filled', 'g-business-reviews-rating'),
			'dark bubble fill center narrow fonts' => __('Narrow, Centered, Dark, Bubble Filled with Fonts', 'g-business-reviews-rating'),
			'dark bubble fill center narrow tile' => __('Narrow, Centered, Dark, Bubble Filled, Tiled', 'g-business-reviews-rating'),
			'dark bubble fill center narrow fonts tile' => __('Narrow, Centered, Dark, Bubble Filled, Tiled with Fonts', 'g-business-reviews-rating'),
			'badge light' => __('Badge, Light Background', 'g-business-reviews-rating'),
			'badge light fonts' => __('Badge, Light Background with Fonts', 'g-business-reviews-rating'),
			'badge light narrow' => __('Narrow Badge, Light Background', 'g-business-reviews-rating'),
			'badge light narrow fonts' => __('Narrow Badge, Light Background with Fonts', 'g-business-reviews-rating'),
			'badge dark' => __('Badge, Dark Background', 'g-business-reviews-rating'),
			'badge dark fonts' => __('Badge, Dark Background with Fonts', 'g-business-reviews-rating'),
			'badge dark narrow' => __('Narrow Badge, Dark Background', 'g-business-reviews-rating'),
			'badge dark narrow fonts' => __('Narrow Badge, Dark Background with Fonts', 'g-business-reviews-rating'),
			'badge tiny light' => __('Tiny Badge, Light Background', 'g-business-reviews-rating'),
			'badge tiny light fonts' => __('Tiny Badge, Light Background with Fonts', 'g-business-reviews-rating'),
			'badge tiny dark' => __('Tiny Badge, Dark Background', 'g-business-reviews-rating'),
			'badge tiny dark fonts' => __('Tiny Badge, Dark Background with Fonts', 'g-business-reviews-rating')
			];
		}

		return TRUE;
	}

	/* Set rating and review data */

	private function set(): bool
	{
		$this->administrator = (function_exists('current_user_can') && current_user_can('manage_options', $this->alias));
		$this->editor = (!$this->administrator && function_exists('current_user_can') && current_user_can('edit_published_posts', $this->alias) && get_option($this->alias . '_editor', TRUE));
		$this->plugin_url = (!$this->editor) ? './admin.php?page=' . $this->alias : './options-general.php?page=' . $this->alias . '_settings';
		
		$this->demo = get_option($this->alias . '_demo', '');
		$this->api_key = get_option($this->alias . '_api_key', '');
		$this->place_id = get_option($this->alias . '_place_id', '');		
		$this->theme = get_option($this->alias . '_theme', '');
		$this->more = get_option($this->alias . '_more', '');
		
		if (!$this->demo)
		{
			$icon_image_id = get_option($this->alias . '_icon', '');
			$icon_image_url = NULL;
		
			if (is_numeric($icon_image_id))
			{
				global $wpdb;
				$icon_image_url = $wpdb->get_var($wpdb->prepare("SELECT `guid` FROM `{$wpdb->posts}` WHERE ID = %d LIMIT 1", $icon_image_id));
			}

			$this->result = get_option($this->alias . '_result', '');
			/* Support both Places API (New) flat response and old API 'result' wrapper */
			$this->data = (isset($this->result['result'])) ? $this->result['result'] : (is_array($this->result) ? $this->result : []);
			$this->rating = (is_array($this->data) && !empty($this->data) && isset($this->data['rating'])) ? floatval($this->data['rating']) : NULL;
			$this->business_name = (is_array($this->data) && !empty($this->data)) ? (isset($this->data['displayName']['text']) ? $this->data['displayName']['text'] : (isset($this->data['name']) ? $this->data['name'] : NULL)) : NULL;
			$this->business_icon = ($icon_image_url != NULL) ? $icon_image_url : ((is_array($this->data) && !empty($this->data)) ? (isset($this->data['iconMaskBaseUri']) ? $this->data['iconMaskBaseUri'] : (isset($this->data['icon']) ? $this->data['icon'] : NULL)) : NULL);
			$this->user_ratings_total = (is_array($this->data) && !empty($this->data)) ? (isset($this->data['userRatingCount']) ? intval($this->data['userRatingCount']) : (isset($this->data['user_ratings_total']) ? intval($this->data['user_ratings_total']) : NULL)) : NULL;
			$this->reviews = get_option($this->alias . '_reviews', '');
			
			if ((!is_numeric($this->rating) || is_numeric($this->rating) && $this->rating == 0 || $this->user_ratings_total == NULL) && is_array($this->reviews) && !empty($this->reviews))
			{
				$this->user_ratings_total = count($this->reviews);
				$ratings = [];
				
				foreach ($this->reviews as $a)
				{
					$ratings[] = $a['rating'];
				}
				
				$this->rating = (!empty($ratings)) ? array_sum($ratings)/count($ratings) : 0;
			}
			
			return TRUE;
		}
		
		$this->result = json_decode(GOOGLE_BUSINESS_REVIEWS_RATING_DEMO_RESULT, TRUE);
		$this->data = is_array($this->result) ? $this->result : [];
		$this->rating = (is_array($this->data) && !empty($this->data) && isset($this->data['rating'])) ? floatval($this->data['rating']) : NULL;
		$this->business_name = (is_array($this->data) && !empty($this->data)) ? (isset($this->data['displayName']['text']) ? $this->data['displayName']['text'] : (isset($this->data['name']) ? $this->data['name'] : NULL)) : NULL;
		$this->business_icon = (is_array($this->data) && !empty($this->data)) ? (isset($this->data['iconMaskBaseUri']) ? $this->data['iconMaskBaseUri'] : (isset($this->data['icon']) ? $this->data['icon'] : NULL)) : NULL;
		$this->user_ratings_total = (is_array($this->data) && !empty($this->data)) ? (isset($this->data['userRatingCount']) ? intval($this->data['userRatingCount']) : (isset($this->data['user_ratings_total']) ? intval($this->data['user_ratings_total']) : NULL)) : NULL;

		$this->reviews = [];
		$count = 1;

		foreach (is_array($this->data['reviews'] ?? NULL) ? $this->data['reviews'] : [] as $review)
		{
			$author_name = (isset($review['authorAttribution']['displayName'])) ? $review['authorAttribution']['displayName'] : (isset($review['author_name']) ? $review['author_name'] : NULL);
			$review_text = (isset($review['originalText']['text'])) ? $review['originalText']['text'] : (isset($review['text']['text']) ? $review['text']['text'] : (isset($review['text']) && is_string($review['text']) ? $review['text'] : NULL));
			$review_time = (isset($review['publishTime'])) ? (strtotime($review['publishTime']) ?: 0) : (isset($review['time']) ? intval($review['time']) : 0);
			$review_language = (isset($review['originalText']['languageCode'])) ? $review['originalText']['languageCode'] : (isset($review['language']) ? $review['language'] : NULL);
			$key = $review_time . '_' . (isset($review['rating']) ? $review['rating'] : '') . '_' . md5(strval($author_name) . '_' . mb_substr(strval($review_text), 0, 100));

			$this->reviews[$key] = [
				'id' => $count,
				'place_id' => NULL,
				'order' => $count,
				'checked' => NULL,
				'retrieved' => time(),
				'imported' => FALSE,
				'time_estimate' => FALSE,
				'removable' => FALSE,
				'status' => (isset($review['rating']) && $review['rating'] >= 1 && $review['rating'] <= 5),
				'profile_photo_url' => (isset($review['authorAttribution']['photoUri'])) ? $review['authorAttribution']['photoUri'] : (isset($review['profile_photo_url']) ? $review['profile_photo_url'] : NULL),
				'avatar' => NULL,
				'author_name' => $author_name,
				'author_url' => (isset($review['authorAttribution']['uri'])) ? $review['authorAttribution']['uri'] : (isset($review['author_url']) ? $review['author_url'] : NULL),
				'language' => $review_language,
				'original_language' => $review_language,
				'rating' => (isset($review['rating']) && is_numeric($review['rating'])) ? intval($review['rating']) : NULL,
				'text' => $review_text,
				'relative_time_description' => (isset($review['relativePublishTimeDescription'])) ? $review['relativePublishTimeDescription'] : (isset($review['relative_time_description']) ? $review['relative_time_description'] : NULL),
				'time' => $review_time,
				'translated' => FALSE,
			];
			$count++;
		}

		uksort($this->reviews, function ($key_a, $key_b)
		{
			if (!isset($this->reviews[$key_a]) || !isset($this->reviews[$key_b]))
			{
				return 0;
			}

			return $this->reviews[$key_b]['retrieved'] - ($this->reviews[$key_b]['order'] * 0.1) - $this->reviews[$key_a]['retrieved'] - ($this->reviews[$key_a]['order'] * 0.1);
		});

		return TRUE;
	}
	
	/* Build default widget option values based on review count */

	private function default_values(): array
	{
		if (empty($this->reviews))
		{
			return [];
		}
		
		$count = $this->approved_count();
		
		return [
			'title' => __('Google Rating', 'g-business-reviews-rating'),
			'limit' => ($count < 3) ? $count : 3,
			'view' => NULL,
			'sort' => NULL,
			'offset' => 0,
			'rating_min' => 1,
			'rating_max' => 5,
			'review_text_min' => 0,
			'review_text_max' => NULL,
			'excerpt_length' => 120,
			'more' => __('More', 'g-business-reviews-rating'),
			'language' => NULL,
			'theme' => NULL,
			'display_name' => FALSE,
			'display_icon' => FALSE,
			'display_vicinity' => FALSE,
			'display_rating' => TRUE,
			'display_rating_stars' => TRUE,
			'display_review_count' => TRUE,
			'display_reviews' => TRUE,
			'display_review_text' => TRUE,
			'display_avatar' => TRUE,
			'display_view_reviews_button' => FALSE,
			'display_write_review_button' => FALSE,
			'display_attribution' => TRUE,
			'class_fill' => FALSE,
			'animate' => TRUE,
			'stylesheet' => TRUE
		];
	}
	
	/* Count the number of visible (approved) reviews */

	private function approved_count(): int
	{
		$count = 0;
		
		if (!is_array($this->reviews))
		{
			return $count;
		}

		foreach ($this->reviews as $a)
		{
			$count += ($a['status']) ? 1 : 0;
		}
		
		return $count;
	}
	
	/* Process and sanitize widget form submissions */

	public function update($new_instance, $old_instance = []): array
	{
		$default_values = $this->default_values();
		$set_default = (!array_key_exists('title', $new_instance));
		$a = [];
		
		foreach ($default_values as $key => $default_value)
		{
			if (!array_key_exists($key, $new_instance))
			{
				if ($set_default)
				{
					$a[$key] = $default_value;
					continue;
				}
				
				$a[$key] = (is_bool($default_value)) ? FALSE : NULL;
				continue;
			}
			
			if (is_bool($default_value))
			{
				$a[$key] = (!$set_default) ? boolval($new_instance[$key]) : $default_value;
				continue;
			}
			
			if (is_numeric($default_value) || preg_match('/^.+_(?:min|max)$/i', $key))
			{
				$a[$key] = (is_numeric($new_instance[$key])) ? intval($new_instance[$key]) : (($set_default) ? $default_value : NULL);
				continue;
			}
			
			if (!is_string($new_instance[$key]))
			{
				continue;
			}
			
			$a[$key] = ($new_instance[$key] != NULL) ? wp_strip_all_tags($new_instance[$key]) : (($set_default) ? $default_value : NULL);
		}
		
		if ((!is_numeric($a['limit']) || is_numeric($a['limit']) && $a['limit'] > 5) && preg_match('/badge/i', $a['theme']))
		{
			$a['limit'] = (is_numeric($a['limit']) && $a['limit'] > 5) ? 5 : 0;
		}
		
		if (!$a['display_reviews'] && (!is_numeric($a['limit']) || is_numeric($a['limit']) && $a['limit'] > 0))
		{
			$a['limit'] = 0;
		}
		elseif ($a['display_reviews'] && is_numeric($a['limit']) && $a['limit'] == 0)
		{
			$a['display_reviews'] = FALSE;
			
			if (!array_key_exists('display_reviews', $old_instance) || array_key_exists('display_reviews', $old_instance) && !$old_instance['display_reviews'])
			{
				$a['display_reviews'] = TRUE;
				$a['limit'] = 1;
			}
		}
		
		if (is_numeric($a['view']) && $a['view'] >= 1 && (!is_numeric($a['limit']) && $a['limit'] == NULL || is_numeric($a['limit']) && $a['limit'] > 1))
		{
			$a['view'] = (is_numeric($a['limit']) && $a['view'] < $a['limit']) ? intval($a['view']) : 1;
			$a['loop'] = (isset($a['loop']) && $a['loop']);
			$a['iterations'] = ($a['loop'] && is_numeric($a['iterations']) && $a['iterations'] >= 0.3 && $a['iterations'] <= MINUTE_IN_SECONDS) ? $a['iterations'] : NULL;
		}
		else
		{
			$a['view'] = NULL;
			$a['loop'] = FALSE;
			$a['iterations'] = NULL;
		}
						
		if ($a['sort'] == 'relevance_desc')
		{
			$a['sort'] = NULL;
		}
		
		if (is_numeric($a['rating_max']))
		{
			if ($a['rating_max'] <= 1)
			{
				$a['rating_max'] = 1;
			}
			elseif ($a['rating_max'] >= 5)
			{
				$a['rating_max'] = 5;
			}
		}
		
		if (is_numeric($a['rating_min']))
		{
			if ($a['rating_min'] <= 1)
			{
				$a['rating_min'] = 1;
			}
			elseif ($a['rating_min'] >= 5)
			{
				$a['rating_min'] = 5;
			}
		}
		
		if (is_numeric($a['rating_min']) && is_numeric($a['rating_max']) && $a['rating_min'] > $a['rating_max'])
		{
			$a['rating_min'] = $a['rating_max'];
		}
		
		if (is_numeric($a['review_text_min']) && is_numeric($a['review_text_max']) && $a['review_text_min'] > $a['review_text_max'])
		{
			$a['review_text_min'] = $a['review_text_max'];
		}
		
		foreach ($default_values as $k => $v)
		{
			if (is_bool($v))
			{
				$a[$k] = $a[$k] ? '1' : '';
			}
		}
		
		return $a;
	}

	/* Load plugin stylesheets on the widgets and customizer screens */

	public function css_load(): void
	{
		global $pagenow;
		
		if (!preg_match('/^(?:widgets|customize)\.php$/', $pagenow))
		{
			return;
		}
		
		wp_register_style($this->alias . '_admin_css', google_business_reviews_rating::plugin_url('dashboard/css/css.css'), [], google_business_reviews_rating::VERSION);
		wp_register_style($this->alias . '_wp_css', google_business_reviews_rating::plugin_url('wp/css/css.css'), [], google_business_reviews_rating::VERSION);
		wp_enqueue_style($this->alias . '_admin_css');
		wp_enqueue_style($this->alias . '_wp_css');
	}
	
	/* Load plugin scripts on the widgets and customizer screens */

	public function js_load(): void
	{
		global $pagenow;
		
		if (!preg_match('/^(?:widgets|customize)\.php$/', $pagenow))
		{
			return;
		}
		
		wp_register_script(__CLASS__ . '_admin_js', google_business_reviews_rating::plugin_url('dashboard/js/js.js'), [], google_business_reviews_rating::VERSION);
		wp_localize_script(__CLASS__ . '_admin_js', $this->alias . '_admin_ajax', ['url' => admin_url('admin-ajax.php'), 'action' => 'google_business_reviews_rating_admin_ajax']);
		wp_enqueue_script(__CLASS__ . '_admin_js');
	}
	
	/* Render the widget on the front-end */

	public function widget($args, $data): void
	{
		$shortcode_parameters = '';
		$shortcode_arguments = [
			'class' => ['widget'],
			'summary' => ['icon', 'name', 'vicinity', 'rating', 'stars', 'count'],
			'limit' => (array_key_exists('limit', $data)) ? ((is_numeric($data['limit']) && $data['limit'] >= 0) ? intval($data['limit']) : NULL) : 0,
			'min' => (array_key_exists('rating_min', $data)) ? ((is_numeric($data['rating_min']) && $data['rating_min'] >= 1 && $data['rating_min'] <= 5) ? intval($data['rating_min']) : 1) : NULL,
			'max' => (array_key_exists('rating_max', $data)) ? ((is_numeric($data['rating_max']) && $data['rating_max'] >= 1 && $data['rating_max'] <= 5) ? intval($data['rating_max']) : 5) : NULL,
			'view' => (array_key_exists('view', $data) && is_numeric($data['view']) && $data['view'] >= 1) ? intval($data['view']) : NULL
		];
		$title = (isset($data['title'])) ? apply_filters('widget_title', $data['title']) : NULL;
		
		if (isset($data['theme']) && is_string($data['theme']) && $data['theme'] != NULL)
		{
			$shortcode_arguments['theme'] = $data['theme'];
		}
		
		if (isset($data['language']) && is_string($data['language']) && $data['language'] != NULL)
		{
			$shortcode_arguments['language'] = $data['language'];
		}
		
		if (!isset($data['display_review_count']) || !$data['display_review_count'])
		{
			unset($shortcode_arguments['summary'][5]);
		}
		
		if (!isset($data['display_rating_stars']) || !$data['display_rating_stars'])
		{
			unset($shortcode_arguments['summary'][4]);
		}
		
		if (!isset($data['display_rating']) || !$data['display_rating'])
		{
			unset($shortcode_arguments['summary'][3]);
		}
		
		if (!isset($data['display_vicinity']) || !$data['display_vicinity'])
		{
			unset($shortcode_arguments['summary'][2]);
		}
		
		if (!isset($data['display_name']) || !$data['display_name'])
		{
			unset($shortcode_arguments['summary'][1]);
		}

		if (!isset($data['display_icon']) || !$data['display_icon'])
		{
			unset($shortcode_arguments['summary'][0]);
		}
		
		if (empty($shortcode_arguments['summary']))
		{
			$shortcode_arguments['summary'] = FALSE;
		}
		elseif (count($shortcode_arguments['summary']) == 6)
		{
			unset($shortcode_arguments['summary']);
		}
		
		if (array_key_exists('display_reviews', $data) && !$data['display_reviews'])
		{
			$shortcode_arguments['limit'] = 0;
			
			if (isset($shortcode_arguments['view']))
			{
				unset($shortcode_arguments['view']);
			}
			
			if (isset($shortcode_arguments['min']))
			{
				unset($shortcode_arguments['min']);
			}
			
			if (isset($shortcode_arguments['max']))
			{
				unset($shortcode_arguments['max']);
			}
		}
		
		if (!is_numeric($shortcode_arguments['limit']) && $shortcode_arguments['limit'] == NULL || is_numeric($shortcode_arguments['limit']) && $shortcode_arguments['limit'] > 0)
		{
			if (is_numeric($shortcode_arguments['view']))
			{
				if ($shortcode_arguments['limit'] <= 1)
				{
					unset($shortcode_arguments['view']);
					
					if (isset($shortcode_arguments['loop']))
					{
						unset($shortcode_arguments['loop']);
					}
					
					if (isset($shortcode_arguments['iterations']))
					{
						unset($shortcode_arguments['iterations']);
					}
				}
				else
				{
					if ($shortcode_arguments['view'] < 1)
					{
						$shortcode_arguments['view'] = 1;
					}
					
					if (is_numeric($shortcode_arguments['limit']) && $shortcode_arguments['view'] >= $shortcode_arguments['limit'])
					{
						$shortcode_arguments['view'] = $shortcode_arguments['limit'] - 1;
					}
					
					if (isset($shortcode_arguments['loop']) || isset($shortcode_arguments['iterations']))
					{
						if (isset($shortcode_arguments['loop']) && isset($shortcode_arguments['iterations']) && (!$shortcode_arguments['loop'] || !is_numeric($shortcode_arguments['iterations']) || is_numeric($shortcode_arguments['iterations']) && ($shortcode_arguments['iterations'] < 0.3 || $shortcode_arguments['iterations'] > 60)))
						{
							unset($shortcode_arguments['loop']);
							unset($shortcode_arguments['iterations']);
						}
						elseif (!isset($shortcode_arguments['loop']) || !$shortcode_arguments['loop'] && isset($shortcode_arguments['iterations']))
						{
							unset($shortcode_arguments['iterations']);
						}
					}
				}
			}
			
			if (array_key_exists('offset', $data) && is_numeric($data['offset']) && $data['offset'] > 0)
			{
				$shortcode_arguments['offset'] = intval($data['offset']);
			}
			
			if (!isset($data['display_review_text']) || !$data['display_review_text'])
			{
				if (array_key_exists('display_avatar', $data) && !$data['display_avatar'])
				{
					$shortcode_arguments['review_item_order'] = ['author', 'rating', 'date'];
				}
				else
				{
					$shortcode_arguments['review_item_order'] = ['avatar', 'author', 'rating', 'date'];
				}
			}
			elseif (array_key_exists('display_avatar', $data) && !$data['display_avatar'])
			{
				if (array_key_exists('display_author', $data) && !$data['display_author'])
				{
					$shortcode_arguments['review_item_order'] = ['rating', 'date', 'review'];
				}
				else
				{
					$shortcode_arguments['review_item_order'] = ['author', 'rating', 'date', 'review'];
				}
			}
			elseif (array_key_exists('display_author', $data) && !$data['display_author'])
			{
				$shortcode_arguments['review_item_order'] = ['avatar', 'rating', 'date', 'review'];
			}

			if (isset($data['review_text_min']) && is_numeric($data['review_text_min']) && intval($data['review_text_min']) >= 20)
			{
				$shortcode_arguments['review_text_min'] = intval($data['review_text_min']);
			}
			
			if (isset($data['review_text_max']) && is_numeric($data['review_text_max']) && intval($data['review_text_max']) >= 20)
			{
				$shortcode_arguments['review_text_max'] = intval($data['review_text_max']);
			}
			
			if (isset($data['sort']) && is_string($data['sort']) && $data['sort'] != NULL)
			{
				$shortcode_arguments['sort'] = $data['sort'];
			}
			
			if (isset($data['excerpt_length']) && is_numeric($data['excerpt_length']) && $data['excerpt_length'] >= 20)
			{
				$shortcode_arguments['excerpt'] = $data['excerpt_length'];
				
				if (array_key_exists('more', $data) && (is_string($data['more']) || $data['more'] == NULL))
				{
					$shortcode_arguments['more'] = $data['more'];
				}
			}
		}		

		if (isset($data['display_view_reviews_button']) && $data['display_view_reviews_button'])
		{
			$shortcode_arguments['reviews_link'] = TRUE;
		}
		
		if (isset($data['display_write_review_button']) && $data['display_write_review_button'])
		{
			$shortcode_arguments['write_review_link'] = TRUE;
		}
		
		if (array_key_exists('display_attribution', $data) && !$data['display_attribution'])
		{
			$shortcode_arguments['attribution'] = FALSE;
		}

		if (isset($data['class_fill']) && $data['class_fill'])
		{
			$shortcode_arguments['class'][] = 'fill';
		}

		if (array_key_exists('animate', $data) && !$data['animate'])
		{
			$shortcode_arguments['animate'] = FALSE;
		}
		
		if (array_key_exists('stylesheet', $data) && !$data['stylesheet'])
		{
			$shortcode_arguments['stylesheet'] = FALSE;
		}
		
		foreach ($shortcode_arguments as $k => $v)
		{
			$shortcode_parameters .= ' ' . $k . '=';
			
			if (is_array($v))
			{
				if ($k == 'class')
				{
					$shortcode_parameters .= '"' . implode(' ', $v) . '"';
					continue;
				}
				
				$shortcode_parameters .= '"' . implode(', ', $v) . '"';
				continue;
			}

			if (is_numeric($v))
			{
				$shortcode_parameters .= $v;
				continue;
			}

			if (is_bool($v))
			{
				$shortcode_parameters .= (($v) ? 'true' : 'false');
				continue;
			}
			
			if ($v == NULL)
			{
				$shortcode_parameters .= '""';
				continue;
			}

			$shortcode_parameters .= '"' . preg_replace('/^\s+|\s+$|["\[\]]/', '', $v) . '"';
		}
		
		extract($args, EXTR_SKIP);
		
		echo $before_widget . ((is_string($title) && $title != NULL) ? $before_title . esc_html($title) . $after_title : '') . do_shortcode('[reviews_rating ' . trim($shortcode_parameters) . ']') . $after_widget;
	}
	
	/* Render the widget settings form in the Dashboard */

	public function form($instance): void
	{
		$this->set_lists();
		$html = '';
		
		if (!$this->demo)
		{
			if ($this->editor)
			{
				$html = '        <p class="error">' . esc_html__('This plugin is not fully set up. Please ask your administrator to complete the process.', 'g-business-reviews-rating') . '</p>
';
			}
			elseif ((!$this->api_key || $this->api_key == NULL) && (!$this->place_id || $this->place_id == NULL))
			{
				$html = '        <p class="error"><a href="' . esc_attr($this->plugin_url) . '">' . esc_html__('Please set your Google API Key and Place ID', 'g-business-reviews-rating') . '</a>.</p>
        <p class="buttons"><a href="' . esc_attr($this->plugin_url) . '" class="button button-secondary">' . esc_html(__('Settings', 'g-business-reviews-rating')) . '</a></p>
';
			}
			elseif (!$this->api_key || $this->api_key == NULL)
			{
				$html = '        <p class="error"><a href="' . esc_attr($this->plugin_url) . '">' . esc_html__('Please set your Google API Key', 'g-business-reviews-rating') . '</a>.</p>
        <p class="buttons"><a href="' . esc_attr($this->plugin_url) . '" class="button button-secondary">' . esc_html__('Settings', 'g-business-reviews-rating') . '</a></p>
';
			}
			elseif (!$this->place_id || $this->place_id == NULL)
			{
				$html = '        <p class="error"><a href="' . esc_attr($this->plugin_url) . '">' . esc_html__('Please set your Place ID', 'g-business-reviews-rating') . '</a>.</p>
        <p class="buttons"><a href="' . esc_attr($this->plugin_url) . '" class="button button-secondary">' . esc_html__('Settings', 'g-business-reviews-rating') . '</a></p>
';
			}
			elseif ($this->result == NULL)
			{
				$html = '        <p class="error">'.esc_html__('No rating or review data found.', 'g-business-reviews-rating') . ' <a href="' . esc_attr($this->plugin_url) . '">' . esc_html__('Please check your Reviews and Rating settings.', 'g-business-reviews-rating') . '</a>.</p>
        <p class="buttons"><a href="' . esc_attr($this->plugin_url) . '" class="button button-secondary">' . esc_html__('Settings', 'g-business-reviews-rating') . '</a></p>
';
			}
		}
		
		if ($html != '')
		{
			echo wp_kses($html, ['p' => ['id' => [], 'class' => []], 'a' => ['href' => [], 'target' => [], 'class' => []], 'strong' => [], 'em' => []]);
			return;
		}

		$count = $this->approved_count();
		
		if ((!is_numeric($this->rating) || is_numeric($this->rating) && $this->rating == 0) && $count == 0)
		{
			$html = '        <p class="error">' . esc_html__('Not reviews or ratings exist.', 'g-business-reviews-rating') . '</p>
';
		}
		
		if ($html != '')
		{
			echo wp_kses($html, ['p' => ['id' => [], 'class' => []]]);
			return;
		}

		$default_values = $this->default_values();

		if (!array_key_exists('title', $instance) || !array_key_exists('limit', $instance) || !array_key_exists('rating_min', $instance) || !array_key_exists('rating_max', $instance))
		{
			$instance = array_merge($default_values, $instance);
		}
		
		extract($instance, EXTR_SKIP);
		
		if (count($default_values) != count($instance))
		{
			extract($default_values, EXTR_SKIP);
		}

		include(plugin_dir_path(__FILE__) . 'templates/widget.php');
		return;
	}
}
