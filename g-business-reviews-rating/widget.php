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
		$result = array(),
		$data = array(),
		$reviews = array(),
		$reviews_filtered = array(),
		$review_sort_option = NULL,
		$review_sort_options = array(),
		$languages = array(),
		$reviews_themes = array(),
		$administrator = FALSE,
		$editor = FALSE,
		$plugin_url = NULL;
	
    public function __construct()
    {
		$this->alias = preg_replace('/^(.+)[_-][^_-]+$/', '$1', __CLASS__);
		$this->reference = preg_replace('/[^0-9a-z-]/', '-', $this->alias);
		$this->first = NULL;
		
        parent::__construct($this->alias, __('Reviews and Rating - Google Reviews', 'g-business-reviews-rating'), array(
            'description' => __('Have your rating and a review showing in your sidebar', 'g-business-reviews-rating'),
			'classname' => $this->reference . '-widget'
        ));
		
		$this->set();
		
		add_action('admin_enqueue_scripts', array($this, 'admin_css_load'));
		add_action('admin_enqueue_scripts', array($this, 'admin_js_load'));
        return TRUE;
    }
	
	private function set()
	{
		// Set rating and review data
		
		$this->review_sort_options = array(
			'relevance_desc' => array(
				'name' => 'Relevance Descending',
				'min_max_values' => array('High', 'Low'),
				'field' => NULL,
				'asc' => FALSE
			),
			'relevance_asc' => array(
				'name' => 'Relevance Ascending',
				'min_max_values' => array('Low', 'High'),
				'field' => NULL,
				'asc' => TRUE
			),
			'date_desc' => array(
				'name' => 'Date Descending',
				'min_max_values' => array('New', 'Old'),
				'field' => 'time',
				'asc' => FALSE
			),
			'date_asc' => array(
				'name' => 'Date Ascending',
				'min_max_values' => array('Old', 'New'),
				'field' => 'time',
				'asc' => TRUE
			),
			'rating_desc' => array(
				'name' => 'Rating Descending',
				'min_max_values' => array('High', 'Low'),
				'field' => 'rating',
				'asc' => FALSE
			),
			'rating_asc' => array(
				'name' => 'Rating Ascending',
				'min_max_values' => array('Low', 'High'),
				'field' => 'rating',
				'asc' => TRUE
			),
			'author_name_asc' => array(
				'name' => 'Author’s Name Ascending',
				'min_max_values' => array('A', 'Z'),
				'field' => 'author_name',
				'asc' => TRUE
			),
			'author_name_desc' => array(
				'name' => 'Author’s Name Descending',
				'min_max_values' => array('Z', 'A'),
				'field' => 'author_name',
				'asc' => FALSE
			),
			'id_asc' => array(
				'name' => 'ID Ascending',
				'min_max_values' => array('Low', 'High'),
				'field' => 'id',
				'asc' => TRUE
			),
			'id_desc' => array(
				'name' => 'ID Descending',
				'min_max_values' => array('High', 'Low'),
				'field' => 'id',
				'asc' => FALSE
			),
			'shuffle' => array(
				'name' => 'Random Shuffle'
			)
		);
		$this->languages = array(
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
		);
		$this->reviews_themes = array(
			'light' => __('Light Background', 'g-business-reviews-rating-extended'),
			'light fonts' => __('Light Background with Fonts', 'g-business-reviews-rating-extended'),
			'light tile' => __('Light Background, Tiled', 'g-business-reviews-rating-extended'),
			'light fonts tile' => __('Light Background, Tiled with Fonts', 'g-business-reviews-rating-extended'),
			'light center' => __('Centered, Light Background', 'g-business-reviews-rating-extended'),
			'light center fonts' => __('Centered, Light Background with Fonts', 'g-business-reviews-rating-extended'),
			'light center tile' => __('Centered, Light Background, Tiled', 'g-business-reviews-rating-extended'),
			'light center fonts tile' => __('Centered, Light Background, Tiled with Fonts', 'g-business-reviews-rating-extended'),
			'light narrow' => __('Narrow, Light Background', 'g-business-reviews-rating-extended'),
			'light narrow fonts' => __('Narrow, Light Background with Fonts', 'g-business-reviews-rating-extended'),
			'light narrow tile' => __('Narrow, Light Background, Tiled', 'g-business-reviews-rating-extended'),
			'light narrow fonts tile' => __('Narrow, Light Background, Tiled with Fonts', 'g-business-reviews-rating-extended'),
			'light center narrow' => __('Narrow, Centered, Light Background', 'g-business-reviews-rating-extended'),
			'light center narrow fonts' => __('Narrow, Centered, Light Background with Fonts', 'g-business-reviews-rating-extended'),
			'light center narrow tile' => __('Narrow, Centered, Light Background, Tiled', 'g-business-reviews-rating-extended'),
			'light center narrow fonts tile' => __('Narrow, Centered, Light Background, Tiled with Fonts', 'g-business-reviews-rating-extended'),
			'dark' => __('Dark Background', 'g-business-reviews-rating-extended'),
			'dark fonts' => __('Dark Background with Fonts', 'g-business-reviews-rating-extended'),
			'dark tile' => __('Dark Background, Tiled', 'g-business-reviews-rating-extended'),
			'dark fonts tile' => __('Dark Background, Tiled with Fonts', 'g-business-reviews-rating-extended'),
			'dark center' => __('Centered, Dark Background', 'g-business-reviews-rating-extended'),
			'dark center fonts' => __('Centered, Dark Background with Fonts', 'g-business-reviews-rating-extended'),
			'dark center tile' => __('Centered, Dark Background, Tiled', 'g-business-reviews-rating-extended'),
			'dark center fonts tile' => __('Centered, Dark Background, Tiled with Fonts', 'g-business-reviews-rating-extended'),
			'dark narrow' => __('Narrow, Dark Background', 'g-business-reviews-rating-extended'),
			'dark narrow fonts' => __('Narrow, Dark Background with Fonts', 'g-business-reviews-rating-extended'),
			'dark narrow tile' => __('Narrow, Dark Background, Tiled', 'g-business-reviews-rating-extended'),
			'dark narrow fonts tile' => __('Narrow, Dark Background, Tiled with Fonts', 'g-business-reviews-rating-extended'),
			'dark center narrow' => __('Narrow, Centered, Dark Background', 'g-business-reviews-rating-extended'),
			'dark center narrow fonts' => __('Narrow, Centered, Dark Background with Fonts', 'g-business-reviews-rating-extended'),
			'dark center narrow tile' => __('Narrow, Centered, Dark Background, Tiled', 'g-business-reviews-rating-extended'),
			'dark center narrow fonts tile' => __('Narrow, Centered, Dark Background, Tiled with Fonts', 'g-business-reviews-rating-extended'),
			'light bubble' => __('Light Background, Bubble Outline', 'g-business-reviews-rating-extended'),
			'light bubble fonts' => __('Light, Bubble Outline with Fonts', 'g-business-reviews-rating-extended'),
			'light bubble tile' => __('Light Background, Bubble Outline, Tiled', 'g-business-reviews-rating-extended'),
			'light bubble fonts tile' => __('Light, Bubble Outline, Tiled with Fonts', 'g-business-reviews-rating-extended'),
			'light bubble fill' => __('Light Background, Bubble Filled', 'g-business-reviews-rating-extended'),
			'light bubble fill fonts' => __('Light, Bubble Filled with Fonts', 'g-business-reviews-rating-extended'),
			'light bubble fill tile' => __('Light Background, Bubble Filled, Tiled', 'g-business-reviews-rating-extended'),
			'light bubble fill fonts tile' => __('Light, Bubble Filled, Tiled with Fonts', 'g-business-reviews-rating-extended'),
			'light bubble center' => __('Centered, Light, Bubble Outline', 'g-business-reviews-rating-extended'),
			'light bubble center fonts' => __('Centered, Light, Bubble Outline with Fonts', 'g-business-reviews-rating-extended'),
			'light bubble center tile' => __('Centered, Light, Bubble Outline, Tiled', 'g-business-reviews-rating-extended'),
			'light bubble center fonts tile' => __('Centered, Light, Bubble Outline, Tiled with Fonts', 'g-business-reviews-rating-extended'),
			'light bubble fill center' => __('Centered, Light, Bubble Filled', 'g-business-reviews-rating-extended'),
			'light bubble fill center fonts' => __('Centered, Light, Bubble Filled with Fonts', 'g-business-reviews-rating-extended'),
			'light bubble fill center tile' => __('Centered, Light, Bubble Filled, Tiled', 'g-business-reviews-rating-extended'),
			'light bubble fill center fonts tile' => __('Centered, Light, Bubble Filled, Tiled with Fonts', 'g-business-reviews-rating-extended'),
			'light bubble narrow' => __('Narrow, Light, Bubble Outline', 'g-business-reviews-rating-extended'),
			'light bubble narrow fonts' => __('Narrow, Light, Bubble Outline with Fonts', 'g-business-reviews-rating-extended'),
			'light bubble narrow tile' => __('Narrow, Light, Bubble Outline, Tiled', 'g-business-reviews-rating-extended'),
			'light bubble narrow fonts tile' => __('Narrow, Light, Bubble Outline, Tiled with Fonts', 'g-business-reviews-rating-extended'),
			'light bubble fill narrow' => __('Narrow, Light, Bubble Filled', 'g-business-reviews-rating-extended'),
			'light bubble fill narrow fonts' => __('Narrow, Light, Bubble Filled with Fonts', 'g-business-reviews-rating-extended'),
			'light bubble fill narrow tile' => __('Narrow, Light, Bubble Filled, Tiled', 'g-business-reviews-rating-extended'),
			'light bubble fill narrow fonts tile' => __('Narrow, Light, Bubble Filled, Tiled with Fonts', 'g-business-reviews-rating-extended'),
			'light bubble center narrow' => __('Narrow, Centered, Light, Bubble Outline', 'g-business-reviews-rating-extended'),
			'light bubble center narrow fonts' => __('Narrow, Centered, Light, Bubble Outline with Fonts', 'g-business-reviews-rating-extended'),
			'light bubble center narrow tile' => __('Narrow, Centered, Light, Bubble Outline, Tiled', 'g-business-reviews-rating-extended'),
			'light bubble center narrow fonts tile' => __('Narrow, Centered, Light, Bubble Outline, Tiled with Fonts', 'g-business-reviews-rating-extended'),
			'light bubble fill center narrow' => __('Narrow, Centered, Light, Bubble Filled', 'g-business-reviews-rating-extended'),
			'light bubble fill center narrow fonts' => __('Narrow, Centered, Light, Bubble Filled with Fonts', 'g-business-reviews-rating-extended'),
			'light bubble fill center narrow tile' => __('Narrow, Centered, Light, Bubble Filled, Tiled', 'g-business-reviews-rating-extended'),
			'light bubble fill center narrow fonts tile' => __('Narrow, Centered, Light, Bubble Filled, Tiled with Fonts', 'g-business-reviews-rating-extended'),
			'dark bubble' => __('Dark, Bubble Outline', 'g-business-reviews-rating-extended'),
			'dark bubble fonts' => __('Dark, Bubble Outline with Fonts', 'g-business-reviews-rating-extended'),
			'dark bubble tile' => __('Dark, Bubble Outline, Tiled', 'g-business-reviews-rating-extended'),
			'dark bubble fonts tile' => __('Dark, Bubble Outline, Tiled with Fonts', 'g-business-reviews-rating-extended'),
			'dark bubble fill' => __('Dark, Bubble Filled', 'g-business-reviews-rating-extended'),
			'dark bubble fill fonts' => __('Dark, Bubble Filled with Fonts', 'g-business-reviews-rating-extended'),
			'dark bubble fill tile' => __('Dark, Bubble Filled, Tiled', 'g-business-reviews-rating-extended'),
			'dark bubble fill fonts tile' => __('Dark, Bubble Filled, Tiled with Fonts', 'g-business-reviews-rating-extended'),
			'dark bubble center' => __('Centered, Dark, Bubble Outline', 'g-business-reviews-rating-extended'),
			'dark bubble center fonts' => __('Centered, Dark, Bubble Outline with Fonts', 'g-business-reviews-rating-extended'),
			'dark bubble center tile' => __('Centered, Dark, Bubble Outline, Tiled', 'g-business-reviews-rating-extended'),
			'dark bubble center fonts tile' => __('Centered, Dark, Bubble Outline, Tiled with Fonts', 'g-business-reviews-rating-extended'),
			'dark bubble fill center' => __('Centered, Dark, Bubble Filled', 'g-business-reviews-rating-extended'),
			'dark bubble fill center fonts' => __('Centered, Dark, Bubble Filled with Fonts', 'g-business-reviews-rating-extended'),
			'dark bubble fill center tile' => __('Centered, Dark, Bubble Filled, Tiled', 'g-business-reviews-rating-extended'),
			'dark bubble fill center fonts tile' => __('Centered, Dark, Bubble Filled, Tiled with Fonts', 'g-business-reviews-rating-extended'),
			'dark bubble narrow' => __('Narrow, Dark, Bubble Outline', 'g-business-reviews-rating-extended'),
			'dark bubble narrow fonts' => __('Narrow, Dark, Bubble Outline with Fonts', 'g-business-reviews-rating-extended'),
			'dark bubble narrow tile' => __('Narrow, Dark, Bubble Outline, Tiled', 'g-business-reviews-rating-extended'),
			'dark bubble narrow fonts tile' => __('Narrow, Dark, Bubble Outline, Tiled with Fonts', 'g-business-reviews-rating-extended'),
			'dark bubble fill narrow' => __('Narrow, Dark, Bubble Filled', 'g-business-reviews-rating-extended'),
			'dark bubble fill narrow fonts' => __('Narrow, Dark, Bubble Filled with Fonts', 'g-business-reviews-rating-extended'),
			'dark bubble fill narrow tile' => __('Narrow, Dark, Bubble Filled, Tiled', 'g-business-reviews-rating-extended'),
			'dark bubble fill narrow fonts tile' => __('Narrow, Dark, Bubble Filled, Tiled with Fonts', 'g-business-reviews-rating-extended'),
			'dark bubble center narrow' => __('Narrow, Centered, Dark, Bubble Outline', 'g-business-reviews-rating-extended'),
			'dark bubble center narrow fonts' => __('Narrow, Centered, Dark, Bubble Outline with Fonts', 'g-business-reviews-rating-extended'),
			'dark bubble center narrow tile' => __('Narrow, Centered, Dark, Bubble Outline, Tiled', 'g-business-reviews-rating-extended'),
			'dark bubble center narrow fonts tile' => __('Narrow, Centered, Dark, Bubble Outline, Tiled with Fonts', 'g-business-reviews-rating-extended'),
			'dark bubble fill center narrow' => __('Narrow, Centered, Dark, Bubble Filled', 'g-business-reviews-rating-extended'),
			'dark bubble fill center narrow fonts' => __('Narrow, Centered, Dark, Bubble Filled with Fonts', 'g-business-reviews-rating-extended'),
			'dark bubble fill center narrow tile' => __('Narrow, Centered, Dark, Bubble Filled, Tiled', 'g-business-reviews-rating-extended'),
			'dark bubble fill center narrow fonts tile' => __('Narrow, Centered, Dark, Bubble Filled, Tiled with Fonts', 'g-business-reviews-rating-extended'),
			'badge light' => __('Badge, Light Background', 'g-business-reviews-rating-extended'),
			'badge light fonts' => __('Badge, Light Background with Fonts', 'g-business-reviews-rating-extended'),
			'badge light narrow' => __('Narrow Badge, Light Background', 'g-business-reviews-rating-extended'),
			'badge light narrow fonts' => __('Narrow Badge, Light Background with Fonts', 'g-business-reviews-rating-extended'),
			'badge dark' => __('Badge, Dark Background', 'g-business-reviews-rating-extended'),
			'badge dark fonts' => __('Badge, Dark Background with Fonts', 'g-business-reviews-rating-extended'),
			'badge dark narrow' => __('Narrow Badge, Dark Background', 'g-business-reviews-rating-extended'),
			'badge dark narrow fonts' => __('Narrow Badge, Dark Background with Fonts', 'g-business-reviews-rating-extended'),
			'badge tiny light' => __('Tiny Badge, Light Background', 'g-business-reviews-rating-extended'),
			'badge tiny light fonts' => __('Tiny Badge, Light Background with Fonts', 'g-business-reviews-rating-extended'),
			'badge tiny dark' => __('Tiny Badge, Dark Background', 'g-business-reviews-rating-extended'),
			'badge tiny dark fonts' => __('Tiny Badge, Dark Background with Fonts', 'g-business-reviews-rating-extended')
		);

		$this->administrator = (function_exists('current_user_can') && current_user_can('manage_options', $this->alias));
		$this->editor = (!$this->administrator && function_exists('current_user_can') && current_user_can('edit_published_posts', $this->alias) && get_option($this->alias . '_editor', TRUE));
		$this->plugin_url = (!$this->editor) ? './admin.php?page=' . $this->alias : './options-general.php?page=' . $this->alias . '_settings';
		
		$this->demo = get_option($this->alias . '_demo');
		$this->api_key = get_option($this->alias . '_api_key');
		$this->place_id = get_option($this->alias . '_place_id');		
		$this->theme = get_option($this->alias . '_theme');
		$this->more = get_option($this->alias . '_more');
		
		if (!$this->demo)
		{
			$icon_image_id = get_option($this->alias . '_icon');
			$icon_image_url = NULL;
		
			if (is_numeric($icon_image_id))
			{
				global $wpdb;
				$icon_image_url = $wpdb->get_var("SELECT `guid` FROM `" . $wpdb->posts . "` WHERE ID='" . intval($icon_image_id) . "' LIMIT 1");
			}

			$this->result = get_option($this->alias . '_result');
			$this->data = (isset($this->result['result'])) ? $this->result['result'] : array();
			$this->rating = (is_array($this->data) && !empty($this->data) && isset($this->data['rating'])) ? floatval($this->data['rating']) : NULL;
			$this->business_name = (is_array($this->data) && !empty($this->data) && isset($this->data['name'])) ? $this->data['name'] : NULL;
			$this->business_icon = ($icon_image_url != NULL) ? $icon_image_url : ((is_array($this->data) && !empty($this->data) && isset($this->data['icon'])) ? $this->data['icon'] : NULL);
			$this->user_ratings_total = (is_array($this->data) && !empty($this->data) && isset($this->data['user_ratings_total'])) ? intval($this->data['user_ratings_total']) : NULL;
			$this->reviews = get_option($this->alias . '_reviews');
			$this->reviews_filtered = $this->reviews;
			
			if ((!is_numeric($this->rating) || is_numeric($this->rating) && $this->rating == 0 || $this->user_ratings_total == NULL) && is_array($this->reviews) && !empty($this->reviews))
			{
				$this->user_ratings_total = count($this->reviews);
				$ratings = array();
				
				foreach ($this->reviews as $a)
				{
					$ratings[] = $a['rating'];
				}
				
				$this->rating = (!empty($ratings)) ? array_sum($ratings)/count($ratings) : 0;
			}
			
			return TRUE;
		}
		
		$this->result = json_decode(GOOGLE_BUSINESS_REVIEWS_RATING_DEMO_RESULT, TRUE);
		$this->data = $this->result['result'];
		$this->rating = (is_array($this->data) && !empty($this->data) && isset($this->data['rating'])) ? floatval($this->data['rating']) : NULL;
		$this->business_name = (is_array($this->data) && !empty($this->data) && isset($this->data['name'])) ? $this->data['name'] : NULL;
		$this->business_icon = (is_array($this->data) && !empty($this->data) && isset($this->data['icon'])) ? $this->data['icon'] : NULL;
		$this->user_ratings_total = (is_array($this->data) && !empty($this->data) && isset($this->data['user_ratings_total'])) ? intval($this->data['user_ratings_total']) : NULL;

		$this->reviews = array();
		$reviews_length = 0;
		$count = 1;
		
		foreach($this->data['reviews'] as $review)
		{
			$key = $review['time'].'_'.$review['rating'].'_'.md5($review['author_name'].'_'.substr($review['text'], 0, 100));
			
			$a['id'] = $reviews_length + $count;
			$a['place_id'] = ($this->demo) ? NULL : get_option($this->alias . '_place_id');
			$a['order'] = $count;
			$a['checked'] = NULL;
			$a['retrieved'] = time();
			$a['status'] = TRUE;
			
			$this->reviews[$key] = $a + $review;
			$count++;
		}
		
		uksort($this->reviews, function ($a, $b) {
			if (!isset($this->reviews[$a]) || !isset($this->reviews[$b]))
			{
				return 0;
			}

			return $this->reviews[$b]['retrieved'] - ($this->reviews[$b]['order'] * 0.1) - $this->reviews[$a]['retrieved'] - ($this->reviews[$a]['order'] * 0.1);
		});
		$this->reviews_filtered = $this->reviews;

        return TRUE;
	}
	
	private function default_values()
	{
		// Set the default values
		
		if (empty($this->reviews))
		{
			return array();
		}
		
		$count = $this->reviews_count();
		
		return array(
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
		);
	}

	private function reviews_filter($filters = array(), $override = TRUE)
	{
		// Filter review data
				
		if (empty($filters) || empty($this->reviews))
		{
			return FALSE;
		}
		
		$count = 0;
		$id = NULL;
		$ids = array();
		
		if (isset($filters['id']))
		{
			$ids = (is_numeric($filters['id']) && $filters['id'] > 0) ? array(intval($filters['id'])) : ((preg_match('/^(?:\d+)(?:,\s*(?:\d+))+$/', $filters['id'])) ? array_unique(preg_split('/[^\d]+/', $filters['id'])) : array());
			$id = (!empty($ids)) ? $ids[0] : NULL;
		}
		
		$place_id = (!$this->demo && isset($filters['place_id']) && is_string($filters['place_id']) && strlen($filters['place_id']) >= 20) ? $filters['place_id'] : NULL;
		$rating_min = ($id == NULL && is_numeric($filters['rating_min']) && $filters['rating_min'] > 1 && $filters['rating_min'] <= 5) ? intval($filters['rating_min']) : NULL;
		$rating_max = ($id == NULL && is_numeric($filters['rating_max']) && $filters['rating_max'] >= 1 && $filters['rating_max'] < 5) ? intval($filters['rating_max']) : NULL;
		$view = ($id == NULL && is_numeric($filters['view']) && $filters['view'] >= 0) ? intval($filters['view']) : NULL;
		$offset = ($id == NULL && is_numeric($filters['offset']) && $filters['offset'] >= 0) ? intval($filters['offset']) : 0;
		$limit = ($id == NULL && is_numeric($filters['limit']) && $filters['limit'] >= 0) ? intval($filters['limit']) : NULL;
		$sort = ($id == NULL && array_key_exists('sort', $filters) && is_string($filters['sort'])) ? preg_replace('/[^\w_-]/', '', $filters['sort']) : NULL;
		$excerpt_length = (is_numeric($filters['excerpt_length']) && $filters['excerpt_length'] >= 20) ? intval($filters['excerpt_length']) : NULL;
		$review_text_min = (is_numeric($filters['review_text_min']) && $filters['review_text_min'] >= 0) ? intval($filters['review_text_min']) : NULL;
		$review_text_max = (is_numeric($filters['review_text_max']) && $filters['review_text_max'] >= 0 && (!is_numeric($filters['review_text_min']) || is_numeric($filters['review_text_min']) && $filters['review_text_min'] <= $filters['review_text_max'])) ? intval($filters['review_text_max']) : NULL;
		$language = (isset($filters['language']) && is_string($filters['language']) && strlen($filters['language']) >= 2 && strlen($filters['language']) <= 16) ? preg_replace('/^([a-z]{2,3}).*$/i', '$1', strtolower($filters['language'])) : NULL;

		if (!$override)
		{
			$limit = (is_numeric($limit)) ? intval($limit) : get_option($this->alias . '_review_limit', NULL);
			$view = (is_numeric($view)) ? intval($view) : get_option($this->alias . '_review_view', NULL);
			$sort = (is_string($sort)) ? preg_replace('/[^\w_-]/', '', $sort) : get_option($this->alias . '_review_sort', NULL);
			$rating_min = (is_numeric($rating_min)) ? intval($rating_min) : get_option($this->alias . '_rating_min', NULL);
			$rating_max = (is_numeric($rating_max)) ? intval($rating_max) : get_option($this->alias . '_rating_max', NULL);
			$review_text_min = (is_numeric($review_text_min) && $review_text_min >= 0) ? intval($review_text_min) : get_option($this->alias . '_review_text_min', NULL);
			$review_text_max = (is_numeric($review_text_max) && $review_text_max >= 0) ? intval($review_text_max) : get_option($this->alias . '_review_text_max', NULL);
		}
		
		$this->review_sort_option = ($sort != NULL && $sort != 'relevance_desc' && array_key_exists($sort, $this->review_sort_options)) ? $sort : NULL;
				
		if (!$filters['display_reviews'])
		{
			$limit = 0;
			$view = NULL;
		}
		elseif (is_numeric($limit) && $limit == 0)
		{
			$display_reviews = FALSE;
			$view = NULL;
		}
		
		if (!empty($ids))
		{
			$this->reviews_filtered = array();
			foreach ($ids as $id)
			{
				foreach ($this->reviews as $key => $a)
				{
					if ($a['id'] != $id)
					{
						continue;
					}
					$this->reviews_filtered[$key] = $a;
					break;
				}
			}
			
			return TRUE;
		}
		
		foreach ($this->reviews as $key => $a)
		{
			if (!array_key_exists($key, $this->reviews_filtered))
			{
				continue;
			}
			
			if (!$a['status'])
			{
				unset($this->reviews_filtered[$key]);
				continue;
			}
			
			if (is_numeric($rating_min) && $rating_min > 1 && $a['rating'] < $rating_min || is_numeric($rating_max) && $rating_max < 5 && $a['rating'] > $rating_max)
			{
				unset($this->reviews_filtered[$key]);
				continue;
			}
			
			if ($place_id != NULL && $a['place_id'] != $place_id)
			{
				unset($this->reviews_filtered[$key]);
				continue;
			}
																		
			if ($language != NULL && isset($a['language']) && ($a['language'] == NULL || strtolower($a['language']) != $language))
			{
				unset($this->reviews_filtered[$key]);
				continue;
			}

			if (is_numeric($review_text_min) && $review_text_min > strlen(strip_tags($a['text'])) || is_numeric($review_text_max) && $review_text_max < strlen(strip_tags($a['text'])))
			{
				unset($this->reviews_filtered[$key]);
				continue;
			}
			
			$count++;
		}
	
		if ($this->review_sort_option != NULL)
		{
			if ($this->review_sort_option == 'shuffle')
			{
				$offset = 0;
				$list = $this->reviews_filtered;
				$keys = array_keys($this->reviews_filtered); 
				$this->reviews_filtered = array(); 
				shuffle($keys); 
				foreach ($keys as $k)
				{ 
					$this->reviews_filtered[$k] = $list[$k]; 
				}
			}
			elseif ($this->review_sort_option == 'relevance_asc')
			{
				$this->reviews_filtered = array_reverse($this->reviews_filtered, TRUE);
			}
			else
			{
				uksort($this->reviews_filtered, function ($b, $a)
					{
						$v = $this->reviews_filtered[$a][$this->review_sort_options[$this->review_sort_option]['field']];
						$w = $this->reviews_filtered[$b][$this->review_sort_options[$this->review_sort_option]['field']];
						
						if ($this->review_sort_options[$this->review_sort_option]['field'] != 'id' && is_numeric($v) && $v < 10 && is_numeric($w) && $w < 10 && is_numeric($this->reviews_filtered[$a]['time']) && $this->reviews_filtered[$a]['time'] > 100000000 && is_numeric($this->reviews_filtered[$b]['time']) && $this->reviews_filtered[$b]['time'] > 100000000)
						{
							$v -= (1000000000/$this->reviews_filtered[$a]['time']);
							$w -= (1000000000/$this->reviews_filtered[$b]['time']);
							
							$v *= 100;
							$w *= 100;
						}
						
						if (is_numeric($v) && is_numeric($w))
						{
							return round($v) - round($w);
						}
						
						if (strtolower($v) == strtolower($w))
						{
							return 0;
						}
						
						$c = $d = array(strtolower($v), strtolower($w));
						arsort($c, SORT_REGULAR);
						return (array_keys($c) === array_keys($d)) ? 1 : -1;
					}
				);
				
				if ($this->review_sort_options[$this->review_sort_option]['asc'])
				{
					$this->reviews_filtered = array_reverse($this->reviews_filtered, TRUE);
				}
			}
		}
		
		if (is_numeric($offset) && is_numeric($limit) && $limit < $count)
		{
			$this->reviews_filtered = array_splice($this->reviews_filtered, $offset, $limit);
		}
		
		return TRUE;
	}
	
	private function reviews_count()
	{
		// Count the number of reviews stored and visible
		
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
	
    public function update($new_instance, $old_instance = array())
    {
		// Process Dashboard form updates
		
		$default_values = $this->default_values();
		$set_default = (!array_key_exists('title', $new_instance));
        $a = array();
		
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
				$a[$key] = (!$set_default) ? (bool)$new_instance[$key] : $default_value;
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
			
			$a[$key] = ($new_instance[$key] != NULL) ? strip_tags($new_instance[$key]) : (($set_default) ? $default_value : NULL);
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
			$a['iterations'] = ($a['loop'] && is_numeric($a['iterations']) && $a['iterations'] >= 0.3 && $a['iterations'] <= 60) ? $a['iterations'] : NULL;
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

	public function admin_css_load()
	{
		// Load style sheet in the Dashboard
		
		global $pagenow;
		
		if (!preg_match('/^(?:widgets|customize)\.php$/', $pagenow))
		{
			return;
		}
		
 		wp_register_style($this->alias . '_admin_css', plugins_url('g-business-reviews-rating/admin/css/css.css'));
		wp_enqueue_style($this->alias . '_admin_css');
	}
	
	public function admin_js_load()
	{
		// Load Javascript in the Dashboard
		
		global $pagenow;
		
		if (!preg_match('/^(?:widgets|customize)\.php$/', $pagenow))
		{
			return;
		}
		
		wp_register_script(__CLASS__ . '_admin_js', plugins_url('g-business-reviews-rating/admin/js/js.js'));
		wp_localize_script(__CLASS__ . '_admin_js', $this->alias . '_admin_ajax', array('url' => admin_url('admin-ajax.php'), 'action' => 'google_business_reviews_rating_admin_ajax'));
		wp_enqueue_script(__CLASS__ . '_admin_js');
	}
	
    public function widget($args, $data)
    {
		// Display the widget
		
		$shortcode_parameters = '';
		$shortcode_arguments = array(
			'class' => array('widget'),
			'summary' => array('icon', 'name', 'vicinity', 'rating', 'stars', 'count'),
			'limit' => (array_key_exists('limit', $data)) ? ((is_numeric($data['limit']) && $data['limit'] >= 0) ? intval($data['limit']) : NULL) : 0,
			'min' => (array_key_exists('rating_min', $data)) ? ((is_numeric($data['rating_min']) && $data['rating_min'] >= 1 && $data['rating_min'] <= 5) ? intval($data['rating_min']) : 1) : NULL,
			'max' => (array_key_exists('rating_max', $data)) ? ((is_numeric($data['rating_max']) && $data['rating_max'] >= 1 && $data['rating_max'] <= 5) ? intval($data['rating_max']) : 5) : NULL,
			'view' => (array_key_exists('view', $data) && is_numeric($data['view']) && $data['view'] >= 1) ? intval($data['view']) : NULL
		);
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
					$shortcode_arguments['review_item_order'] = array('author', 'rating', 'date');
				}
				else
				{
					$shortcode_arguments['review_item_order'] = array('avatar', 'author', 'rating', 'date');
				}
			}
			elseif (array_key_exists('display_avatar', $data) && !$data['display_avatar'])
			{
				if (array_key_exists('display_author', $data) && !$data['display_author'])
				{
					$shortcode_arguments['review_item_order'] = array('rating', 'date', 'review');
				}
				else
				{
					$shortcode_arguments['review_item_order'] = array('author', 'rating', 'date', 'review');
				}
			}
			elseif (array_key_exists('display_author', $data) && !$data['display_author'])
			{
				$shortcode_arguments['review_item_order'] = array('avatar', 'rating', 'date', 'review');
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
    
    public function form($instance)
    {
		// Display the widget form in Dashboard
				
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
			echo wp_kses($html, array('p' => array('id' => array(), 'class' => array()), 'a' => array('href' => array(), 'target' => array(), 'class' => array()), 'strong' => array(), 'em' => array()));
			return;
		}

		$count = $this->reviews_count();
		
		if ((!is_numeric($this->rating) || is_numeric($this->rating) && $this->rating == 0) && $count == 0)
		{
			$html = '        <p class="error">' . esc_html__('Not reviews or ratings exist.', 'g-business-reviews-rating') . '</p>
';
		}
		
		if ($html != '')
		{
			echo wp_kses($html, array('p' => array('id' => array(), 'class' => array())));
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
