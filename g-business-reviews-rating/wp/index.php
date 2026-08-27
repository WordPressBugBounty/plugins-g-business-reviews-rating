<?php

if (!defined('ABSPATH'))
{
	die();
}

class google_business_reviews_rating_frontend extends google_business_reviews_rating
{
	public function __construct()
	{
		parent::__construct();
		$this->init();
	}

	/* Initiate the plugin in the front-end */

	public function init(): bool
	{
		$this->demo = $this->get_option('demo');
		$stylesheet = $this->get_option('stylesheet', TRUE);
		$javascript = $this->get_option('javascript', TRUE);
		$structured_data = $this->get_option('structured_data', 0);

		add_shortcode(self::PLUGIN_ALIAS, [$this, 'display']);
		add_shortcode('reviews_rating', [$this, 'display']);
		add_shortcode('reviews_rating_single', [$this, 'display']);
		add_shortcode('reviews_rating_links', [$this, 'display']);
		add_shortcode('reviews_rating_link', [$this, 'display']);
		add_shortcode('links_google_business', [$this, 'display']);
		add_shortcode('link_google_business', [$this, 'display']);
		add_action('widgets_init', function() { register_widget('google_business_reviews_rating_widget'); });
		
		if (is_bool($stylesheet) && $stylesheet || is_numeric($stylesheet) && $stylesheet > 0 || is_string($stylesheet) && $stylesheet != NULL)
		{
			add_action('wp_enqueue_scripts', [$this, 'css_load']);
		}
		
		if (is_bool($javascript) && $javascript || is_numeric($javascript) && $javascript > 0 || is_string($javascript) && $javascript != NULL)
		{
			add_action('wp_enqueue_scripts', [$this, 'js_load']);
		}
		
		if (is_bool($structured_data) && $structured_data || is_numeric($structured_data) && ($structured_data >= 1 || $structured_data <= -1))
		{
			add_action('wp_head', [$this, 'structured_data']);
		}

		return TRUE;
	}

	/* Load style sheet in the front-end */

	public function css_load(): void
	{
		$mode = $this->get_option('stylesheet', TRUE);
		$compressed = (is_numeric($mode) && $mode == 2 || is_string($mode) && ($mode == 'compress' || $mode == 'compressed' || $mode == 'min'));
		
		wp_register_style(self::PLUGIN_ALIAS . '_wp_css', ($compressed) ? self::plugin_url('wp/css/css.min.css') : self::plugin_url('wp/css/css.css'), [], self::VERSION);
		wp_enqueue_style(self::PLUGIN_ALIAS . '_wp_css');

		if (is_file(self::custom_styles_file()) && filesize(self::custom_styles_file()) > 20)
		{
			wp_register_style(self::PLUGIN_ALIAS . '_wp_custom_css', self::plugin_url('wp/css/custom.css'), [], self::VERSION);
			wp_enqueue_style(self::PLUGIN_ALIAS . '_wp_custom_css');
		}
	}

	/* Load Javascript in the front-end */

	public function js_load(): void
	{
		$mode = $this->get_option('javascript', TRUE);
		$compressed = (is_numeric($mode) && $mode == 2 || is_string($mode) && ($mode == 'compress' || $mode == 'compressed' || $mode == 'min'));
		
		wp_register_script(self::PLUGIN_ALIAS . '_wp_js', ($compressed) ? self::plugin_url('wp/js/js.min.js') : self::plugin_url('wp/js/js.js'), [], self::VERSION, TRUE);
		wp_enqueue_script(self::PLUGIN_ALIAS . '_wp_js');
	}

	/* Output or test structured data for the front-end */

	public function structured_data($return = FALSE, array $data = [])
	{
		$test   = (is_bool($return) && $return);
		$string = (is_string($return) && $return == 'json');

		if ($this->demo)
		{
			if ($test)
			{
				return FALSE;
			}

			if ($string)
			{
				return NULL;
			}

			echo '';
			return NULL;
		}

		$show_in_page = get_option(self::OPTION_PREFIX . 'structured_data', 0);
		$show_in_page = (!$this->dashboard && (is_numeric($show_in_page) && ($show_in_page <= -1 || $show_in_page > 1 && function_exists('get_the_ID') && get_the_ID() == intval($show_in_page)) || (is_bool($show_in_page) && $show_in_page || is_numeric($show_in_page) && intval($show_in_page) == 1) && is_front_page()));

		if (!$return && !$string && empty($data) && !$show_in_page)
		{
			return NULL;
		}

		if (!$string)
		{
			$cached = wp_cache_get('structured_data', self::OPTION_PREFIX);
			if (is_string($cached) && mb_strlen($cached) > 20)
			{
				if ($test)
				{
					return TRUE;
				}

				echo wp_kses($cached, ['script' => ['type' => 'application/ld+json']]);
				return NULL;
			}
		}

		$json = $this->get_structured_data_json($data);

		if ($json === NULL)
		{
			if ($test)
			{
				return FALSE;
			}

			if ($string)
			{
				return NULL;
			}

			echo '';
			return NULL;
		}

		if ($test)
		{
			return TRUE;
		}

		if ($string)
		{
			return $json;
		}

		$output = '<script type="application/ld+json">' . PHP_EOL . '[ ' . $json . ' ]' . PHP_EOL . '</script>';
		wp_cache_add('structured_data', $output, self::OPTION_PREFIX, HOUR_IN_SECONDS);

		echo wp_kses($output, ['script' => ['type' => 'application/ld+json']]);
		return NULL;
	}

	/* Display HTML from shortcodes */

	public function display($atts = NULL, $content = NULL, $shortcode = NULL): string
	{
		$this->instance_count = (!is_numeric($this->instance_count)) ? 1 : $this->instance_count + 1;
		
		if ($this->instance_count == 1 && !$this->dashboard)
		{
			$this->set_data();
		}
		
		$type_check = NULL;
		$shortcode_defaults = [
			'animate' => NULL,
			'attribution' => NULL,
			'avatar' => NULL,
			'bullet' => NULL,
			'class' => NULL,
			'color_scheme' => NULL,
			'count' => NULL,
			'cursor' => NULL,
			'date' => NULL,
			'draggable' => NULL,
			'errors' => NULL,
			'excerpt' => NULL,
			'html_tag' => NULL,
			'html_tags' => NULL,
			'icon' => NULL,
			'id' => NULL,
			'interval' => NULL,
			'iterations' => NULL,
			'language' => NULL,
			'language_class' => NULL,
			'limit' => NULL,
			'link' => NULL,
			'link_class' => NULL,
			'link_disable' => NULL,
			'local_images' => NULL,
			'loading' => NULL,
			'loop' => NULL,
			'max' => NULL,
			'min' => NULL,
			'more' => NULL,
			'multiplier' => NULL,
			'name' => NULL,
			'name_format' => NULL,
			'offset' => NULL,
			'outer_tag' => NULL,
			'place_id' => NULL,
			'rating' => NULL,
			'rel' => NULL,
			'review_item_order' => NULL,
			'review_text' => NULL,
			'review_text_exc' => NULL,
			'review_text_format' => NULL,
			'review_text_height' => NULL,
			'review_text_inc' => NULL,
			'review_text_max' => NULL,
			'review_text_min' => NULL,
			'review_word' => NULL,
			'reviews_link' => NULL,
			'reviews_link_class' => NULL,
			'reviews_url' => NULL,
			'sort' => NULL,
			'stars' => NULL,
			'stars_gray' => NULL,
			'stars_grey' => NULL,
			'stylesheet' => NULL,
			'summary' => NULL,
			'target' => NULL,
			'theme' => NULL,
			'transition' => NULL,
			'transition_duration' => NULL,
			'translated' => NULL,
			'type' => NULL,
			'vicinity' => NULL,
			'view' => NULL,
			'write_review_link' => NULL,
			'write_review_link_class' => NULL,
			'write_review_url' => NULL
		];
		$types = [
			'maps_link',
			'maps_url',
			'rating',
			'rating_count',
			'review_count',
			'reviews',
			'reviews_link',
			'reviews_url',
			'structured_data',
			'write_review_link',
			'write_review_url'
		];
		
		foreach ($types as $t)
		{
			$shortcode_defaults[$t] = FALSE;
		}
		
		$places = NULL;
		$args = shortcode_atts($shortcode_defaults, $atts);
		
		if (!is_array($atts))
		{
			$atts = [];
		}

		foreach (array_keys($atts) as $i)
		{
			if (!is_numeric($i))
			{
				continue;
			}
			
			if (!is_string($type_check) && in_array($atts[$i], $types))
			{
				$type_check = $atts[$i];
				continue;
			}
			
			if (preg_match('/^p(?:lace(?:[_-]?id)?)?[_-]?(\d+)$/i', $atts[$i], $m))
			{
				if (!is_array($places))
				{
					$places = $this->get_option('places', []);
				}
				
				if (!is_array($places))
				{
					continue;
				}

				$place_key = (is_numeric($m[1])) ? intval($m[1]) - 1 : 0;

				if (!array_key_exists($place_key, $places) || !isset($places[$place_key]['place_id']) || !is_string($places[$place_key]['place_id']))
				{
					continue;
				}

				$place_id = $places[$place_key]['place_id'];
			}
		}
		
		if ($type_check == NULL && is_string($shortcode) && preg_match('/^.+_links?$/i', $shortcode))
		{
			$type_check = 'reviews_link';
		}
		
		foreach ($args as $k => $v)
		{
			if (is_string($v) && (mb_strlen($v) == 0 || $v == 'NULL' || $v == 'null'))
			{
				$args[$k] = NULL;
			}
		}
				
		extract($args, EXTR_SKIP);
		
		$admin_preview = ($this->dashboard && is_array($atts) && array_key_exists('admin_preview', $atts) && is_bool($atts['admin_preview']) && $atts['admin_preview']);
		$id_name = (is_string($id) && preg_match('/^[a-z][0-9a-z_-]*[0-9a-z]$/i', $id)) ? mb_strtolower($id) : NULL;
		$place_ids = (!$this->demo) ? $this->get_place_ids($place_id) : NULL;

		if (is_array($place_ids) && empty($place_ids))
		{
			return '';
		}

		$default_place_id = $this->get_option('place_id', NULL);
		$place_id = (is_array($place_ids)) ? ((in_array($default_place_id, $place_ids, TRUE)) ? $default_place_id : $place_ids[0]) : NULL;
		$args['place_id'] = $place_ids;
		$type = (is_string($type)) ? preg_replace('/[^\w_]/', '_', trim(mb_strtolower($type))) : $type_check;
		$target = (is_string($target)) ? preg_replace('/[^\w_-]/', '-', trim(mb_strtolower($target))) : NULL;
		$rel = (is_string($rel) && preg_match('/^\s*(?:author|bookmark|external|no(?:follow|referrer|opener))\s*$/i', $rel)) ? mb_strtolower($rel) : ((is_string($rel) && preg_match('/^(?:f(?:alse)?|no?(?:ne)?|0|off|hide)$/i', $rel) || !is_string($rel) && array_key_exists('rel', $atts)) ? NULL : 'nofollow');
		$theme = (is_string($theme)) ? preg_replace('/[^\w _-]/', '-', trim(mb_strtolower($theme))) : NULL;
		$class = (is_string($class)) ? preg_replace('/[^\w _-]/', '-', trim(mb_strtolower($class))) : NULL;
		$color_scheme = (is_string($color_scheme) && array_key_exists(preg_replace('/[^\w_]/', '', trim(mb_strtolower($color_scheme))), $this->color_schemes)) ? preg_replace('/[^\w_]/', '', trim(mb_strtolower($color_scheme))) : ((array_key_exists('color_scheme', $atts)) ? NULL : $this->get_option('color_scheme', NULL));
		$stylesheet = (is_bool($stylesheet) || is_string($stylesheet) && !preg_match('/^(?:f(?:alse)?|no?(?:ne)?|0|off|hide)$/i', $stylesheet)) ? (is_bool($stylesheet) && $stylesheet || is_numeric($stylesheet) && $stylesheet > 0 || is_string($stylesheet) && $stylesheet != NULL) : ((!array_key_exists('stylesheet', $atts)) ? $this->get_option('stylesheet', NULL) : TRUE);
		$summary = (is_null($summary) || is_bool($summary) && $summary || is_string($summary) && preg_match('/^(?:t(?:rue)?|y(?:es)?|1|on|show)$/i', $summary)) ? TRUE : ((is_string($summary) && !preg_match('/^(?:f(?:alse)?|no?(?:ne)?|0|off|hide)$/i', $summary)) ? preg_split('/,\s*/', preg_replace('/[^\w ,_-]/', '-', trim(mb_strtolower($summary))), 8, PREG_SPLIT_NO_EMPTY) : FALSE);
		$icon = (is_null($icon) || is_bool($icon) && $icon || is_string($icon) && preg_match('/^(?:t(?:rue)?|y(?:es)?|1|on|show)$/', $icon)) ? (is_bool($summary) || is_array($summary) && in_array('icon', $summary)) : ((is_string($icon) && preg_match('/.+\.(?:jpe?g|png|svg|gif)/i', $icon)) ? wp_strip_all_tags($icon, TRUE) : FALSE);
		$name = (is_null($name) || is_bool($name) && $name || is_string($name) && preg_match('/^(?:t(?:rue)?|y(?:es)?|1|on|show)$/i', $name)) ? (is_bool($summary) || is_array($summary) && in_array('name', $summary)) : ((is_string($name) && !preg_match('/^(?:f(?:alse)?|no?(?:ne)?|0|off|hide)$/i', $name)) ? wp_strip_all_tags($name, TRUE) : FALSE);
		$vicinity = (is_null($vicinity) || is_bool($vicinity) && $vicinity || is_string($vicinity) && preg_match('/^(?:t(?:rue)?|y(?:es)?|1|on|show)$/i', $vicinity)) ? (is_bool($summary) || is_array($summary) && in_array('vicinity', $summary)) : ((is_string($vicinity) && !preg_match('/^(?:f(?:alse)?|no?(?:ne)?|0|off|hide)$/i', $vicinity)) ? wp_strip_all_tags($vicinity, TRUE) : FALSE);
		$rating_display = ((!is_array($summary) && (!array_key_exists('rating', $atts) || is_null($rating) || is_bool($rating) && $rating || is_string($rating) && !preg_match('/^(?:f(?:alse)?|no?(?:ne)?|0|off|hide)$/i', $rating))) || is_array($summary) && in_array('rating', $summary));
		$stars = (is_null($stars) || is_bool($stars) && $stars || is_string($stars) && preg_match('/^(?:t(?:rue)?|y(?:es)?|1|on|show|svg|vector)$/i', $stars)) ? ((is_bool($summary) || is_array($summary) && (in_array('stars', $summary))) ? ((!array_key_exists('stars', $atts) && $color_scheme != NULL) ? 'css' : (is_bool($summary) && $summary || is_array($summary) && in_array('stars', $summary))) : FALSE) : ((!array_key_exists('stars', $atts) && $color_scheme != NULL || is_string($stars) && preg_match('/(#(?:[0-9a-f]{2}){2,4}|#[0-9a-f]{3}|(?:rgba?|hsla?)\((?:\d+%?(?:deg|rad|grad|turn)?(?:,|\s)+){2,3}[\s\/]*[\d\.]+%?\))/i', $stars)) ? ((!array_key_exists('stars', $atts) && $color_scheme != NULL) ? 'css' : $stars) : ((is_string($stars) && preg_match('/^(?:html|css|(?:inline[\s_-]+(?:svg|vector)|(?:svg|vector)[\s_-]+inline))$/i', $stars)) ? preg_replace('/[\s_-]+/', ' ', mb_strtolower($stars)) : FALSE));
		$stars_grey = ((is_string($stars_grey) && preg_match('/(#(?:[0-9a-f]{2}){2,4}|#[0-9a-f]{3}|(?:rgba?|hsla?)\((?:\d+%?(?:deg|rad|grad|turn)?(?:,|\s)+){2,3}[\s\/]*[\d\.]+%?\))/i', $stars_grey)) ? $stars_grey : ((is_string($stars_grey) && preg_match('/^(?:html|css)$/i', $stars_grey)) ? mb_strtolower($stars_grey) : NULL));
		$stars_gray = ((is_string($stars_gray) && preg_match('/(#(?:[0-9a-f]{2}){2,4}|#[0-9a-f]{3}|(?:rgba?|hsla?)\((?:\d+%?(?:deg|rad|grad|turn)?(?:,|\s)+){2,3}[\s\/]*[\d\.]+%?\))/i', $stars_gray)) ? $stars_gray : ((is_string($stars_gray) && preg_match('/^(?:html|css)$/i', $stars_gray)) ? mb_strtolower($stars_gray) : $stars_grey));
		$count = (!is_array($summary) && (is_null($count) || is_bool($count) && $count || is_string($count) && preg_match('/^(?:t(?:rue)?|y(?:es)?|1|on|show)$/i', $count)) || is_array($summary) && in_array('count', $summary));
		$limit = (array_key_exists('limit', $atts)) ? ((is_numeric($limit) && $limit >= 0) ? intval($limit) : NULL) : $this->get_option('review_limit', NULL);
		$view = (is_numeric($view) && $view >= 1 && $view <= 50 && (is_numeric($limit) && $limit > 0 || !is_numeric($limit))) ? ((is_numeric($limit) && $limit > 0 && $view > $limit) ? intval($limit) : intval($view)) : $this->get_option('view', NULL);
		$loop = (is_numeric($view) && is_numeric($loop) && $loop >= 1 && $loop <= 999) ? intval($loop) : (is_numeric($loop) && $loop < 0 || is_bool($loop) && $loop || is_string($loop) && preg_match('/^(?:t(?:rue)?|y(?:es)?|1|on|show|loop|infin[ia]te?|forever|always|-\d+)$/', $loop));
		$iterations = (is_numeric($view) && $view >= 1 && is_numeric($iterations) && $iterations >= 1 && $iterations <= 999) ? intval($iterations) : NULL;
		$interval = (is_numeric($view) && is_numeric($interval) && $interval >= 0.3 && $interval <= 120) ? floatval($interval) : NULL;
		$transition = (is_string($transition) && preg_match('/^[a-z][0-9a-z .\/()_-]+$/i', $transition)) ? $transition : NULL;
		$transition_duration = (is_numeric($view) && is_string($transition) && is_numeric($transition_duration) && $transition_duration > 0.05 && $transition_duration <= 10) ? floatval($transition_duration) : NULL;
		$bullet = (is_string($bullet) && (mb_strlen($bullet) < 20 && !preg_match('/^(?:false|no(?:ne)?|0|off|hide|t(?:rue)?|y(?:es)?|1|on|show)$/i', $bullet))) ? wp_strip_all_tags($bullet, TRUE) : (!array_key_exists('bullet', $atts) || is_bool($bullet) && $bullet || is_string($bullet) && !preg_match('/^(?:false|no(?:ne)?|0|off|hide)$/i', $bullet));
		$cursor = (!array_key_exists('cursor', $atts) || is_bool($cursor) && $cursor || is_string($cursor) && preg_match('/^(?:true|yes|1|on|show|left|right|both)$/', $cursor));
		$draggable = (!array_key_exists('draggable', $atts) || is_bool($draggable) && $draggable || is_string($draggable) && preg_match('/^(?:true|yes|1|on|show|left|right|both)$/', $draggable));
		$avatar = (is_null($avatar) || is_bool($avatar) && $avatar || is_string($avatar) && preg_match('/^(?:t(?:rue)?|y(?:es)?|1|on|show)$/', $avatar)) ? TRUE : ((is_string($avatar) && preg_match('/^.+\.(?:jpe?g|png|svg|gif|webp).*$/i', $avatar)) ? wp_strip_all_tags($avatar, TRUE) : FALSE);
		$name_format = (is_bool($name_format) && !$name_format || is_string($name_format) && preg_match('/^(?:f(?:alse)?|no?(?:ne)?|0|off|hide)$/i', $name_format)) ? FALSE : ((is_string($name_format) && preg_match('/first|last|initials?|capitali[sz]e|uc(?:first|words)|(?:(?:lower|upper|title)(?:case)?)/i', $name_format)) ? $name_format : NULL);
		$date = (is_null($date) || is_bool($date) && $date || is_string($date) && preg_match('/^(?:true|yes|1|on|show)$/i', $date)) ? TRUE : ((is_string($date) && preg_match('/^[aABcdDeFgGhHiIjLlmMNnoOPrSstTuUvwWYyzZ ,.;:()\[\]\/_-]{1,20}$/', $date) && !preg_match('/^(?:false|no(?:ne)?|0|off|hide)$/i', $date)) ? wp_strip_all_tags($date, TRUE) : FALSE);
		$relative_date = (is_string($date) && preg_match('/^(?:relative)$/i', $date));
		$link = (is_bool($link) && $link || is_string($link) && preg_match('/^(?:t(?:rue)?|y(?:es)?|1|on|show)$/i', $link)) ? TRUE : ((is_string($link) && !preg_match('/^(?:f(?:alse)?|no?(?:ne)?|0|off|hide)$/i', $link)) ? wp_strip_all_tags($link, TRUE) : FALSE);
		$link_class = (is_string($link_class)) ? preg_replace('/[^\w _-]/', '-', trim(mb_strtolower($link_class))) : NULL;
		$link_disable = (is_bool($link_disable) && $link_disable || is_string($link_disable) && preg_match('/^(?:t(?:rue)?|y(?:es)?|1|on|show)$/i', $link_disable)) ? TRUE : ((is_string($link_disable) && !preg_match('/^(?:f(?:alse)?|no?(?:ne)?|0|off|hide)$/i', $link_disable)) ? preg_split('/,\s*/', preg_replace('/[^\w ,_-]/', '-', trim(mb_strtolower($link_disable))), 3, PREG_SPLIT_NO_EMPTY) : FALSE);
		$reviews_link = (is_bool($reviews_link) && $reviews_link || is_string($reviews_link) && preg_match('/^(?:t(?:rue)?|y(?:es)?|1|on|show)$/i', $reviews_link)) ? TRUE : ((is_string($reviews_link) && !preg_match('/^(?:f(?:alse)?|no?(?:ne)?|0|off|hide)$/i', $reviews_link)) ? wp_strip_all_tags($reviews_link, TRUE) : FALSE);
		$write_review_link = (is_bool($write_review_link) && $write_review_link || is_string($write_review_link) && preg_match('/^(?:t(?:rue)?|y(?:es)?|1|on|show)$/i', $write_review_link)) ? TRUE : ((is_string($write_review_link) && !preg_match('/^(?:f(?:alse)?|no?|0|off|hide)$/i', $write_review_link)) ? wp_strip_all_tags($write_review_link, TRUE) : FALSE);
		$reviews_url = (is_string($reviews_url) && preg_match('#^((https?:)?//[^/]{4,150}/?.*|/.*)$#i', $reviews_url)) ? wp_strip_all_tags($reviews_url, TRUE) : (($this->demo) ? 'https://search.google.com/local/reviews?placeid=ChIJq6pqZz2uEmsRaQAMbAl0RW0' : 'https://search.google.com/local/reviews?placeid=' . esc_attr((is_string($place_id)) ? wp_strip_all_tags($place_id, TRUE) : $this->get_option('place_id')));			
		$write_review_url = (is_string($write_review_url) && preg_match('#^((https?:)?//[^/]{4,150}/?.*|/.*)$#i', $write_review_url)) ? wp_strip_all_tags($write_review_url, TRUE) : (($this->demo) ? 'https://search.google.com/local/writereview?placeid=ChIJq6pqZz2uEmsRaQAMbAl0RW0' : 'https://search.google.com/local/writereview?placeid=' . esc_attr((is_string($place_id)) ? wp_strip_all_tags($place_id, TRUE) : $this->get_option('place_id')));			
		$reviews_link_class = (is_string($reviews_link_class)) ? preg_replace('/[^\w _-]/', '-', trim(mb_strtolower($reviews_link_class))) : wp_strip_all_tags($link_class, TRUE);
		$write_review_link_class = (is_string($write_review_link_class)) ? preg_replace('/[^\w _-]/', '-', trim(mb_strtolower($write_review_link_class))) : wp_strip_all_tags($link_class, TRUE);
		$animate = (array_key_exists('animate', $atts) && is_string($animate) && preg_match('/^(?:immediate(?:ly)?|(?:on)?(?:load|ready))$/i', $animate)) ? 'immediate' : (is_null($animate) || is_bool($animate) && $animate || is_string($summary) && preg_match('/^(?:t(?:rue)?|y(?:es)?|1|on|show|animate|animation)$/i', $animate));
		$review_text = (is_null($review_text) || is_bool($review_text) && $review_text || is_string($review_text) && preg_match('/^(?:t(?:rue)?|y(?:es)?|1|on|show)$/i', $review_text));
		$attribution = (is_null($attribution) || is_bool($attribution) && $attribution || is_string($attribution) && preg_match('/^(?:t(?:rue)?|y(?:es)?|1|on|show|light|dark)$/i', $attribution)) ? ((is_string($attribution) && preg_match('/^(?:light|dark)$/i', $attribution)) ? mb_strtolower($attribution) : TRUE) : ((is_string($attribution) && !preg_match('/^(?:f(?:alse)?|no?(?:ne)?|0|off|hide)$/i', $attribution)) ? wp_strip_all_tags($attribution, TRUE) : FALSE);
		$review_text_excerpt_length = (is_numeric($excerpt) && $excerpt >= 20) ? intval($excerpt) : ((!array_key_exists('excerpt', $atts)) ? $this->get_option('review_text_excerpt_length', NULL) : NULL);
		$review_text_height = (is_string($review_text_height) && preg_match('/^(?:\d+(?:\.\d+)?|\.\d+)(?:px|r?em|%|ch|ex|lh)|(?:calc|clamp)\((?:(?:\d+(?:\.\d+)?|\.\d+)(?:px|r?em|%|ch|ex|lh)[,\s\/*+-]*){1,3}\)$/i', $review_text_height)) ? mb_strtolower($review_text_height) : NULL;
		$review_text_format = (is_string($review_text_format) && $review_text_format != NULL) ? mb_strtolower($review_text_format) : NULL;
		$review_word = (is_string($review_word) && mb_strlen($review_word) >= 2) ? preg_split('#[/,]\s*#', $review_word, 2) : [__('review', 'g-business-reviews-rating'), __('reviews', 'g-business-reviews-rating')];
		$more = (is_string($more)) ? wp_strip_all_tags($more, TRUE) : __('More', 'g-business-reviews-rating');
		$language = (is_string($language) && mb_strlen($language) >= 2 && mb_strlen($language) <= 16) ? mb_substr($language, 0, 2) : NULL;
		$language_class = ($language != NULL || array_key_exists('language_class', $atts) && (is_bool($language_class) && $language_class || is_string($language_class) && preg_match('/^(?:true|yes|1|on|show|left|right|both)$/', $language_class)));
		$local_images = (array_key_exists('local_images', $atts)) ? (is_bool($local_images) && $local_images || is_string($local_images) && preg_match('/^(?:t(?:rue)?|y(?:es)?|1|on|show|local)$/i', $local_images)) : NULL;
		$loading = (is_string($loading) && preg_match('/^(eager|lazy)(?:\s?loading)?$/i', $loading, $m)) ? mb_strtolower($m[1]) : NULL;
		$html_tags = array_values(array_filter((is_string($html_tags) && mb_strlen($html_tags) >= 1) ? preg_split('/[^a-z0-9]+/', $html_tags, 8, PREG_SPLIT_NO_EMPTY) : ((is_string($html_tag) && mb_strlen($html_tag) >= 1) ? preg_split('/[^a-z0-9]+/', $html_tag, 8, PREG_SPLIT_NO_EMPTY) : []), function($tag) { return in_array($tag, $this->accepted_html_tags, TRUE); }));
		$outer_tag = (!array_key_exists('outer_tag', $atts) || (is_null($outer_tag) || is_bool($outer_tag) && $outer_tag || is_string($outer_tag) && !preg_match('/^(?:f(?:alse)?|no?(?:ne)?|0|off|hide)$/i', $outer_tag)));
		$multiplier = (is_numeric($multiplier) && $multiplier > 0 && $multiplier < 10) ? floatval($multiplier) : 0.196;
		$errors = (is_bool($errors) && !$errors || is_string($errors) && preg_match('/^(?:f(?:alse)?|no?(?:ne)?|0|off|hide)$/i', $errors)) ? FALSE : ((defined('WP_DEBUG')) ? WP_DEBUG : FALSE);

		switch ($type)
		{
		case 'rating':
		case 'rating_overall':
		case 'rating_mean':
		case 'rating_average':
		case 'mean_rating':
		case 'overall_rating':
		case 'overall_google_rating':
		case 'google_rating':
		case 'google_rating_overall':
		case 'google_rating_mean':
		case 'google_rating_average':
			if (!is_array($this->data) || is_array($this->data) && empty($this->data))
			{
				$this->set_data();

				if ($this->api_version != NULL && intval($this->api_version) >= 1 && (!is_array($this->data) || is_array($this->data) && !isset($this->data['id']) || isset($this->data['id']) && $this->data['id'] == NULL) || $this->api_version == NULL && (!isset($this->data['result']) || isset($this->data['result']) && !is_array($this->data['result'])))
				{
					if (!$errors)
					{
						return '';
					}
					
					$text = esc_html__('Error', 'g-business-reviews-rating') . ': No rating data found';
					return $text;
				}
			}

			$html = $this->get_data('rating_rounded', $place_id);
			break;
		case 'rating_count':
		case 'google_rating_count':
		case 'review_count':
		case 'google_review_count':
			if (!is_array($this->data) || is_array($this->data) && empty($this->data))
			{
				$this->set_data();
				
				if ($this->api_version != NULL && intval($this->api_version) >= 1 && (!is_array($this->data) || is_array($this->data) && !isset($this->data['userRatingCount']) || isset($this->data['userRatingCount']) && $this->data['userRatingCount'] == NULL) || $this->api_version == NULL && (!isset($this->data['result']) || isset($this->data['result']) && !is_array($this->data['result'])))
				{
					if (!$errors)
					{
						return '';
					}
					
					$text = esc_html__('Error', 'g-business-reviews-rating') . ': No rating count found';
					return $text;
				}
			}

			$html = $this->get_data('rating_count', $place_id);
			break;
		case NULL;
		case 'reviews':
		case 'google_reviews':
			if (!is_array($this->data) || is_array($this->data) && empty($this->data))
			{
				$this->set_data();

				if ($this->api_version != NULL && intval($this->api_version) >= 1 && (!is_array($this->data) || is_array($this->data) && !isset($this->data['reviews']) || isset($this->data['reviews']) && !is_array($this->data['reviews'] == NULL)) || $this->api_version == NULL && (!isset($this->data['result']) || !isset($this->data['result']['reviews']) || isset($this->data['result']['reviews']) && !is_array($this->data['result']['reviews'])))
				{
					if (!$errors)
					{
						return '';
					}
					
					$html = '<p class="error">' . esc_html__('Error', 'g-business-reviews-rating') . ': No review data found</p>';

					return $html;
				}
			}

			$this->reviews_filter($args, $atts);
			$this->local_images = $this->get_option('local_images', FALSE);
			
			if (is_string($theme))
			{
				if (($key = array_search($theme, $this->reviews_themes)) !== FALSE && is_string($key))
				{
					$theme = $key;
				}
				else
				{
					$theme = preg_replace('/[^0-9a-z -]/', '-', mb_strtolower($theme));
				}
				
				if (preg_match('/^light(?:\s+([^\s].+))?$/i', $theme, $m))
				{
					$theme = (isset($m[1])) ? $m[1] : NULL;
				}
			}
			else
			{
				$theme = (!$admin_preview) ? $this->get_theme() : NULL;
				
				if (is_string($theme) && preg_match('/^light(?:\s+([^\s].+))?$/i', $theme, $m))
				{
					$theme = (isset($m[1])) ? $m[1] : NULL;
				}
			}
			
			$html_tags = (!empty($html_tags)) ? array_replace($this->default_html_tags, $html_tags) : $this->default_html_tags;
			$classes = ['google-business-reviews-rating', 'gmbrr'];
			$review_item_inline = (is_string($review_item_order) && preg_match('/([\b\s,_-]|^)inline([\b\s,_-]|$)/i', $review_item_order));
			$review_item_text_first = (is_string($review_item_order) && preg_match('/([\b\s,_-]|^)(?:review(?:[\b\s,_-])?text|review|text)[\b\s,_-]?(?:first|top|before|true|on|high|above|1)([\b\s,_-]|$)/i', $review_item_order));
			$review_item_author_switch = (is_string($review_item_order) && preg_match('/([\b\s,_-]|^)(?:author(?:[\b\s,_-])?)[\b\s,_-]?(?:last|bottom|after|low|below|switch|flip)([\b\s,_-]|$)/i', $review_item_order));
			$rating = $this->get_data('rating', $place_id);
			$rating_rounded = $this->get_data('rating_rounded', $place_id);
			$name = (is_bool($name) && $name) ? $this->get_data('name', $place_id) : ((is_string($name)) ? wp_strip_all_tags($name, TRUE) : FALSE);
			$icon = (is_string($icon)) ? wp_strip_all_tags($icon, TRUE) : (is_bool($icon) && $icon);
			$vicinity = (is_bool($vicinity) && $vicinity) ? $this->get_data('vicinity', $place_id) : ((is_string($vicinity)) ? wp_strip_all_tags($vicinity, TRUE) : FALSE);
			$avatar = is_bool($avatar) ? $avatar : (is_string($avatar) ? wp_strip_all_tags($avatar, TRUE) : FALSE);
			$date = ($relative_date || is_bool($date) && $date) ? 'relative' : (is_bool($date) ? FALSE : (is_string($date) ? wp_strip_all_tags($date, TRUE) : FALSE));
			$rating_count = $this->get_data('rating_count', $place_id);
			$rating_count_rounded = $this->get_data('rating_count_rounded', $place_id);
			
			if (is_string($theme) && mb_strlen($theme) > 2)
			{
				$classes = array_merge($classes, preg_split('/\s+/', $theme, 8));
			}
			
			if (is_string($class) && mb_strlen($class) > 2)
			{
				$classes = array_merge($classes, preg_split('/\s+/', $class, 12));
			}
			
			if (is_string($color_scheme) && mb_strlen($color_scheme) > 2)
			{
				$classes[] = wp_strip_all_tags($color_scheme, TRUE);
			}
			
			if (is_bool($stylesheet) && !$stylesheet)
			{
				$classes[] = 'no-styles';
			}
			elseif (is_string($stars) && preg_match('/^inline|inline$/i', $stars))
			{
				$classes[] = 'inline-svg';
			}
			else
			{
				if (is_string($stars))
				{
					$classes[] = ($stars == 'html' || $stars == 'css') ? 'stars-' . $stars : 'stars-color';
				}
				
				if (is_string($stars_gray))
				{
					$classes[] = ($stars_gray == 'html' || $stars_gray == 'css') ? 'stars-' . $stars_gray : 'stars-gray-color';
				}
			}
			
			if (is_numeric($view))
			{
				$classes[] = 'carousel';
			}
			
			if (is_string($bullet) && $bullet != NULL)
			{
				$classes[] = 'bullet-symbol';
			}			
			
			if (is_string($link))
			{
				$classes[] = 'link';
			}
			
			if ($this->demo)
			{
				$classes[] = 'demo';
			}

			$class = implode(' ', array_unique($classes));
			
			if (is_bool($icon) && $icon)
			{
				$icon = $this->get_data('icon', $place_id);
			}
			
			if (is_bool($link) && !$link && is_numeric($limit) && $limit == 0 && is_string($theme) && preg_match('/\b(?:tiny|badge)\b/', $theme))
			{
				$link = wp_strip_all_tags($reviews_url, TRUE);
			}
			elseif ((is_bool($link) && $link || is_string($link)) && (!is_numeric($limit) || is_numeric($limit) && $limit > 0))
			{
				$link = (is_string($theme) && preg_match('/\b(?:tiny|badge)\b/', $theme)) ? wp_strip_all_tags($reviews_url, TRUE) : FALSE;
			}
			elseif (is_bool($link) && $link || is_string($link) && preg_match('/^(?:view[\s_-]*)?reviews?$/i', $link))
			{
				$link = wp_strip_all_tags($reviews_url, TRUE);
			}
			elseif (is_string($link) && preg_match('/^write[\s_-]*(?:a[\s_-]*)?reviews?$/i', $link))
			{
				$link = wp_strip_all_tags($write_review_url, TRUE);
			}
			
			if (!array_key_exists('summary', $atts) && !array_key_exists('icon', $atts) && !array_key_exists('name', $atts) && !array_key_exists('vicinity', $atts) && is_string($theme) && preg_match('/\b(?:tiny\b.*badge|badge\b.*tiny)\b/', $theme))
			{
				$icon = FALSE;
				$name = FALSE;
				$vicinity = FALSE;
			}
			
			$html = '<div id="' . esc_attr(($id_name != NULL) ? $id_name : 'google-business-reviews-rating' . (($this->instance_count > 1) ? '-' . $this->instance_count : '')) . '" ' 
			. 'class="' . esc_attr($class) . '"'
			. ((is_string($link) && (is_bool($link_disable) && !$link_disable || !is_bool($link_disable))) ? ' data-href="' . esc_attr($link) . '"' : '')
			. (($stylesheet && is_string($stars) && $stars != 'html' && $stars != 'css') ? ' data-stars="' . esc_attr($stars) . '"' : '')
			. (($stylesheet && is_string($stars_gray) && $stars_gray != 'html' && $stars_gray != 'css') ? ' data-stars-gray="' . esc_attr($stars_gray) . '"' : '')
			. ((is_string($animate) && $animate == 'immediate') ? ' data-animate="' . esc_attr($animate) . '"' : '')
			. ((is_numeric($view)) ? ' data-view="' . esc_attr($view) . '"' . ((is_numeric($loop) || is_bool($loop) && $loop) ? ' data-loop="' . esc_attr((!is_numeric($loop)) ? '-1' : $loop) . '"' : '') . ((is_numeric($iterations)) ? ' data-iterations="' . esc_attr($iterations) . '"' : '') . ((is_numeric($interval)) ? ' data-interval="' . esc_attr($interval) . '"' : '') . ((is_string($transition)) ? ' data-transition="' . esc_attr($transition) . '"' . ((is_numeric($transition_duration)) ? ' data-transition-duration="' . esc_attr($transition_duration) . '"' : '') : '') . ((is_bool($cursor) && !$cursor) ? ' data-cursor="0"' : '') . ((is_bool($draggable) && !$draggable) ? ' data-draggable="0"' : '') : '')
			. '>
';
			
			if ($summary)
			{
				if ((!is_bool($icon) || is_bool($icon) && $icon || is_string($icon)) || (!is_bool($name) || is_bool($name) && $name) || (!is_bool($vicinity) || is_bool($vicinity) && $vicinity))
				{
					if (is_string($icon) || is_string($name))
					{
						$html .= '	<' . $html_tags[0] . ' class="heading' . (($icon == NULL) ? ' no-icon' : '') . ((!is_string($name)) ? ' no-name' : '') . '">'
						. (($icon != NULL) ? '<span class="icon' . (((is_bool($local_images) && $local_images || is_null($local_images) && $this->local_images) && (is_bool($icon) && $icon || is_string($icon) && preg_match('#^https?://(?:\w+\.)?(?:gstatic|google(?:usercontent)?)?\.\w+/.+$#i', $icon))) ? ' generic' : '') . '">'
						. (((is_bool($local_images) && !$local_images || is_null($local_images) && !$this->local_images) || (is_bool($local_images) && $local_images || is_null($local_images) && $this->local_images) && is_string($icon) && !preg_match('#^https?://(?:\w+\.)?(?:gstatic|google(?:usercontent)?)?\.\w+/.+$#i', $icon)) ? '<img src="' . esc_attr($icon) . '" alt="' . esc_attr(trim($name . ' ' . __('Icon', 'g-business-reviews-rating'))) . '"' . (($loading != NULL) ? ' loading="' . esc_attr($loading) . '"' : '') . '>' : '')
						. '</span>' : '')
						. ((is_string($name)) ? esc_html($name) : '')
						. '</' . $html_tags[0] . '>
';
					}
					
					if (is_string($vicinity) && mb_strlen($vicinity) >= 1)
					{
						$html .= '	<' . $html_tags[1] . ' class="vicinity">' . esc_html($vicinity) . '</' . $html_tags[1] . '>
';
					}
				}
				
				$html .= '	<' . $html_tags[2] . ' class="rating' . (($rating <= 0) ? ' rating-none' : '') . '">';
				
				if ((is_bool($attribution) && $attribution || is_string($attribution) && mb_strlen($attribution) >= 1) && is_string($theme) && preg_match('/\btiny\b/', $theme))
				{
					$html .= '<span class="attribution google-icon' . ((is_string($attribution)) ? ' ' . esc_attr($attribution) : '') . '" title="' . esc_attr__('Powered by Google', 'g-business-reviews-rating') . '"></span> ';
				}

				if ($rating_display)
				{
					$html .= '<span class="number">' . esc_html($rating_rounded) . '</span>' . (((is_bool($stars) && $stars || is_string($stars) || $count)) ? ' ' : '');
				}

				/* translators: %s: rating value out of five, for example 4.5 */
				$stars_aria = ($rating_display) ? ' aria-hidden="true"' : ' role="img" aria-label="' . esc_attr(sprintf(__('Rated %s out of 5', 'g-business-reviews-rating'), $rating_rounded)) . '"';

				if (is_bool($stars) && $stars || is_string($stars))
				{
					if (preg_match('/^inline|inline$/i', $stars))
					{
						$partial = (round($rating * 10, 0, PHP_ROUND_HALF_UP) - floor($rating) * 10) * 10;
						$html .= '<span class="all-stars inline-svg' . ((is_bool($animate) && $animate) ? ' animate' : '') . '"' . $stars_aria . '>' . PHP_EOL;

						for ($star = 1; $star <= 5; $star++)
						{
							$html .= '	<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" xml:space="preserve" class="star' . (($star > ceil($rating)) ? ' gray' : (($star > floor($rating) && $partial > 0) ? ' mask-' . $partial . '-' . (100 - $partial) : '')) . '" width="100" height="100" viewBox="0 0 100 100">' . PHP_EOL
		. (($star <= ceil($rating)) ? '	    <defs>
	        <filter id="' . esc_attr('gmbrr-star-blur-' . $this->instance_count . '-' . $star) . '" color-interpolation-filters="linear" x="-50%" y="-50%" width="200%" height="200%">
				<feGaussianBlur in="SourceGraphic" stdDeviation="14"></feGaussianBlur>
	        </filter>
	    </defs>	    <clipPath id="' . esc_attr('gmbrr-star-mask-shape-' . $this->instance_count . '-' . $star) . '">
	        <rect class="mask-shape" />
	    </clipPath>' . PHP_EOL : '')
		. '	    <clipPath id="' . esc_attr('gmbrr-star-mask-shape-' . $this->instance_count . '-' . ($star + 5)) . '">
	        <rect class="mask-shape static" />
	    </clipPath>
	    <path class="gray" d="m50 2.447 11.743 36.411L100 38.774l-31 22.42 11.902 36.359L50 74.998 19.098 97.553 31 61.194 0 38.774l38.257.084z" />' . PHP_EOL
		. (($star <= ceil($rating)) ? '	    <path class="yellow" clip-path="url(' . esc_attr('#gmbrr-star-mask-shape-' . $this->instance_count . '-' . $star) . ')" d="m50 2.447 11.743 36.411L100 38.774l-31 22.42 11.902 36.359L50 74.998 19.098 97.553 31 61.194 0 38.774l38.257.084z" />
	    <circle class="glow" clip-path="url(' . esc_attr('#gmbrr-star-mask-shape-' . $this->instance_count . '-' . $star) . ')" filter="url(' . esc_attr('#gmbrr-star-blur-' . $this->instance_count . '-' . $star) . ')" cx="-100%" cy="50%" r="60"></circle>' . PHP_EOL : '')
		. '	    <path class="outline" fill="none" stroke="#F7B704" stroke-miterlimit="10" stroke-width="1.937" d="M96.975 39.754 67.85 60.817l11.182 34.159L50 73.786l-29.032 21.19L32.15 60.817 3.025 39.753l35.943.079L50 5.624l11.032 34.208 35.943-.079v.001l-.61.441" />
	</svg>' . PHP_EOL;
						}

						$html .= '</span> ';
					}
					elseif ($stylesheet && ((!is_string($stars) || is_string($stars) && $stars != 'html') && !preg_match('/\bversion[_-]?1\b/i', $class)))
					{
						$partial = (round($rating * 10, 0, PHP_ROUND_HALF_UP) - floor($rating) * 10) * 10;
						$html .= '<span class="all-stars' . (($animate) ? ' animate' : '') . '"' . $stars_aria . '>'
						. str_repeat('<span class="star"></span>', ($partial > 0) ? floor($rating) : ceil($rating))
						. (($partial > 0) ? '<span class="star split-' . $partial . '-' . (100 - $partial) . '"></span>' : '')
						. str_repeat('<span class="star gray"></span>', ($partial > 0) ? (5 - ceil($rating)) : (5 - floor($rating)))
						. '</span> ';
					}	
					elseif ($stylesheet)
					{
						$html .= '<span class="all-stars"' . $stars_aria . '>'
						. str_repeat('★', 5)
						. '<span class="rating-stars' . (($animate) ? ' animate' : '') . '"' . (($animate) ? ' style="width: 0;"' : '') . ' data-multiplier="' . (is_numeric($multiplier) ? esc_attr($multiplier) : '') . '">'
						. str_repeat('★', ceil($rating))
						. '</span></span> ';
					}
					else
					{
						$html .= '<span class="rating-stars' . (is_bool($animate) ? ' animate' : '') . '"' . $stars_aria . ' data-rating="' . esc_attr($rating) . '" data-multiplier="' . (is_numeric($multiplier) ? esc_attr($multiplier) : '') . '">'
						. str_repeat('★', round($rating)) . ((round($rating) < 5) ? '<span class="not">' . str_repeat('☆', (5 - round($rating, 0, PHP_ROUND_HALF_DOWN))) . '</span>' : '')
						. '</span> ';
					}
				}
				
				if ($count)
				{
					$review_word = (count($review_word) == 2 && $rating_count != 1) ? $review_word[1] : $review_word[0];
					$html .= (($link != $reviews_url && (is_bool($link_disable) && !$link_disable || is_array($link_disable) && !in_array('reviews', $link_disable))) ? '<a href="' . esc_attr($reviews_url). '" target="_blank"' . (($rel != NULL) ? ' rel="' . esc_attr($rel) . '"' : '') . ' class="count">' : '<span class="count">');

					if (preg_match('/^(?:([^%]+)%[us]|([^%]+)%[us]([^%]+)|%[us]([^%]+))$/i', $review_word, $m))
					{
						$html .= ((isset($m[1])) ? $m[1] : '') . ((isset($m[2])) ? $m[2] : '') . esc_html($rating_count_rounded) . ((isset($m[3])) ? $m[3] : '') . ((isset($m[4])) ? $m[4] : '');
					}
					else
					{
						$html .= esc_html($rating_count_rounded) . ' ' . wp_strip_all_tags($review_word, TRUE);
					}
					
					$html .= (($link != $reviews_url && (is_bool($link_disable) && !$link_disable || is_array($link_disable) && !in_array('reviews', $link_disable))) ? '</a>' : '</span>');
				}
				
				$html .= '</' . $html_tags[2] . '>
';
			}
						
			if ((!is_numeric($limit) || is_numeric($limit) && $limit > 0) && ($errors || !$errors && !empty($this->reviews) && !empty($this->reviews_filtered)))
			{
				if (empty($this->reviews))
				{
					$html .= '	<' . $html_tags[9] . ' class="listing no-reviews">' . esc_html__('No reviews found.', 'g-business-reviews-rating') . '</' . $html_tags[9] . '>
';
				}
				elseif (empty($this->reviews_filtered))
				{
					/* translators: %s: the language the reviews are filtered by */
					$no_reviews = (($language != NULL) ? sprintf(esc_html__('No reviews found in %s.', 'g-business-reviews-rating'), esc_html($language)) : esc_html__('No reviews found, offset too high or another restriction.', 'g-business-reviews-rating'));

					$html .= '	<' . $html_tags[9] . ' class="listing no-reviews">' . $no_reviews . '</' . $html_tags[9] . '>
';
				}
				elseif (!is_numeric($limit) || is_numeric($limit) && $limit > 0)
				{
					$options = [
						'avatar' => $avatar,
						'avatar_directory' => NULL,
						'bullet' => $bullet,
						'date' => $date,
						'html_tags' => $html_tags,
						'id_name' => $id_name,
						'index' => 0,	
						'link_disable' => $link_disable,
						'language_class' => $language_class,
						'loading' => $loading,
						'more' => $more,
						'name_format' => $name_format,
						'name_format_match' => [],
						'rel' => $rel,
						'review_text' => $review_text,
						'review_text_excerpt_length' => $review_text_excerpt_length,
						'review_text_format' => $review_text_format,
						'review_text_height' => $review_text_height,
						'theme' => $theme,
						'view' => $view
					];

					$options['author_name_capitalize'] = (is_string($name_format) && preg_match('/(?:^|\b)(?:capitali[sz]e|uc(?:first|words)|title(?:case))(?:\b|$)/i', $name_format));
					$options['author_name_lowercase'] = (!$options['author_name_capitalize'] && is_string($name_format) && preg_match('/(?:^|\b)lower(?:case)?(?:\b|$)/i', $name_format));
					$options['author_name_uppercase'] = (!$options['author_name_capitalize'] && !$options['author_name_lowercase'] && is_string($name_format) && preg_match('/(?:^|\b)upper(?:case)?(?:\b|$)/i', $name_format));

					if (is_string($name_format) && preg_match('/^(?:capitali[sz]e|uc(?:first|words)|(?:(?:lower|upper|title)(?:case)?))?\s*(?:(?:(first|last)\s+)?initials?(?:\s+(only)?)?(?:\s+(?:with\s+)?(dot|(?:full)?stop|point|space)s?(?:\s+(?:and\s+)?(dot|(?:full)?stop|point|space)s?)?)?|(first|last)(?:\s+name)?(?:\s+only)?)\s*(?:capitali[sz]e|uc(?:first|words)|(?:(?:lower|upper|title)(?:case)?))?$/i', $name_format, $name_format_match))
					{
						$options['name_format_match'] = $name_format_match;
						$options['author_name_first'] = (isset($name_format_match[5]) && is_string($name_format_match[5]) && mb_strtolower($name_format_match[5]) == 'first');
						$options['author_name_last'] = (!$options['author_name_first'] && isset($name_format_match[5]) && is_string($name_format_match[5]) && mb_strtolower($name_format_match[5]) == 'last');
						$options['author_name_first_initials'] = (!$options['author_name_first'] && !$options['author_name_last'] && isset($name_format_match[1]) && is_string($name_format_match[1]) && mb_strtolower($name_format_match[1]) == 'first');
						$options['author_name_last_initials'] = (!$options['author_name_first'] && !$options['author_name_last'] && !$options['author_name_first_initials'] && isset($name_format_match[1]) && is_string($name_format_match[1]) && mb_strtolower($name_format_match[1]) == 'last');
						$options['author_name_only'] = (isset($name_format_match[2]) && is_string($name_format_match[2]) && $name_format_match[2] != NULL);
						$options['author_name_dot'] = ((isset($name_format_match[3]) && is_string($name_format_match[3]) && $name_format_match[3] != NULL && mb_strtolower($name_format_match[3]) != 'space') || (isset($name_format_match[4]) && is_string($name_format_match[4]) && $name_format_match[4] != NULL && mb_strtolower($name_format_match[4]) != 'space'));
						$options['author_name_space'] = ((isset($name_format_match[3]) && is_string($name_format_match[3]) && mb_strtolower($name_format_match[3]) == 'space') || (isset($name_format_match[4]) && is_string($name_format_match[4]) && mb_strtolower($name_format_match[4]) == 'space'));
					}
					
					$check_key = NULL;
					$options['review_item_inline'] = FALSE;
					$options['review_item_text_first'] = FALSE;
					$options['review_item_author_switch'] = FALSE;
					
					if (is_string($review_item_order))
					{
						$options['review_item_inline'] = (preg_match('/([\b\s,_-]|^)inline([\b\s,_-]|$)/i', $review_item_order));
						$options['review_item_text_first'] = (preg_match('/([\b\s,_-]|^)(?:review(?:[\b\s,_-])?text|review|text)[\b\s,_-]?(?:first|top|before|true|on|high|above|1)([\b\s,_-]|$)/i', $review_item_order));
						$options['review_item_author_switch'] = (preg_match('/([\b\s,_-]|^)(?:(?:author(?:[_-]?name)?|name)(?:[\b\s,_-])?)[\b\s,_-]?(?:last|bottom|after|low|below|switch|flip)([\b\s,_-]|$)/i', $review_item_order));
						
						if (!$options['review_item_text_first'] && !$options['review_item_author_switch'] && preg_match('/^(?:(?:author(?:[_-]?name)?|avatar|date|inline|name|rating|review|text)[,\s]*){2,6}$/i', $review_item_order))
						{
							$review_item_order = preg_split('/,\s*/', mb_strtolower($review_item_order), 6, PREG_SPLIT_NO_EMPTY);
						}
						
						if (is_array($review_item_order))
						{
							if (($check_key = array_search('inline', $review_item_order)) !== FALSE)
							{
								$options['review_item_inline'] = TRUE;
								unset($review_item_order[$check_key]);
							}
							
							if (($check_key = array_search('text', $review_item_order)) !== FALSE)
							{
								$review_item_order[$check_key] = 'review';
							}
							
							if (($check_key = array_search('author', $review_item_order)) !== FALSE
								|| ($check_key = array_search('authorname', $review_item_order)) !== FALSE
								|| ($check_key = array_search('author_name', $review_item_order)) !== FALSE
								|| ($check_key = array_search('author-name', $review_item_order)) !== FALSE)
							{
								$review_item_order[$check_key] = 'name';
							}
							
							$review_item_order = array_unique($review_item_order);
						}
					}
					
					if (is_array($review_item_order) && count($review_item_order) >= 2 && ($review_item_order[0] == 'text' || $review_item_order[0] == 'view'))
					{
						$options['review_item_text_first'] = TRUE;
					}
					elseif (!is_array($review_item_order))
					{
						if ($options['review_item_text_first'])
						{
							if ($options['review_item_author_switch'])
							{
								$review_item_order = ['review', 'avatar', 'rating', 'date', 'name'];
							}
							else
							{
								$review_item_order = ['review', 'avatar', 'name', 'rating', 'date'];
							}
						}
						elseif (is_string($theme) && preg_match('/\bbubble\b/', $theme) && preg_match('/\bcenter\b/', $theme))
						{
							if ($options['review_item_author_switch'])
							{
								$options['review_item_text_first'] = TRUE;
								$review_item_order = ['rating', 'date', 'review', 'avatar', 'name'];
							}
							else
							{
								$review_item_order = ['avatar', 'name', 'review', 'rating', 'date'];
							}
						}
						else
						{
							if ($options['review_item_author_switch'])
							{
								$review_item_order = ['avatar', 'rating', 'date', 'name', 'review'];
							}
							else
							{
								$review_item_order = ['avatar', 'name', 'rating', 'date', 'review'];
							}
						}
					}
					
					$options['avatar'] = ((is_bool($avatar) && $avatar || is_string($avatar) && $avatar != NULL) && (is_string($review_item_order) || is_array($review_item_order) && in_array('avatar', $review_item_order))) ? (is_bool($avatar) ? $avatar : wp_strip_all_tags($avatar, TRUE)) : FALSE;
					$options['review_item_order'] = $review_item_order;

					if ((is_bool($options['avatar']) && $options['avatar'] || is_string($options['avatar']) && $options['avatar'] == 'local') && (is_bool($local_images) && $local_images || is_null($local_images) && $this->local_images) && function_exists('wp_upload_dir'))
					{
						$upload_dir = wp_upload_dir();

						if (isset($upload_dir['baseurl']) && is_string($upload_dir['baseurl']))
						{
							$options['avatar_directory'] = $upload_dir['baseurl'] . '/gmbrr';
						}
					}
			
					$html .= '<' . $html_tags[3] . ' class="listing">
';

					foreach ($this->reviews_filtered as $a)
					{
						$html .= $this->review_item($a, $options);
						$options['index']++;
					}
					
					$html .= '	</' . $html_tags[3] . '>
';
					
					$html .= $this->review_item($a, $options, 'navigation');
				}
			}
			
			if ((is_bool($link_disable) && !$link_disable || !is_bool($link_disable)) && ((is_bool($reviews_link) && $reviews_link || is_string($reviews_link)) || (is_bool($write_review_link) && $write_review_link || is_string($write_review_link))))
			{
				if ($reviews_link_class != NULL)
				{
					$reviews_link_class = preg_split('/\s+|,\s*/', $reviews_link_class, 15);
					$reviews_link_class = array_merge(['view-reviews'], $reviews_link_class);
					$reviews_link_class = implode(' ', array_unique($reviews_link_class));
				}				
				else
				{
					$reviews_link_class = 'button view-reviews';
				}

				if ($write_review_link_class != NULL)
				{
					$write_review_link_class = preg_split('/\s+|,\s*/', $write_review_link_class, 15);
					$write_review_link_class = array_merge(['write-review'], $write_review_link_class);
					$write_review_link_class = implode(' ', array_unique($write_review_link_class));
				}
				else
				{
					$write_review_link_class = 'button write-review';
				}

				$html .= '	<' . $html_tags[7] . ' class="buttons">';
				
				if (is_bool($reviews_link) && $reviews_link || is_string($reviews_link))
				{
					$html .= '<a href="' . esc_attr($reviews_url). '"' . (($reviews_link_class != NULL) ? ' class="' . esc_attr($reviews_link_class) . '"' : '') . ' target="_blank"' . (($rel != NULL) ? ' rel="' . esc_attr($rel) . '"' : '') . '>' . ((is_string($reviews_link)) ? esc_html($reviews_link) : esc_html__('View Reviews', 'g-business-reviews-rating')) . '</a>';
				}
				
				if ((is_bool($reviews_link) && $reviews_link || is_string($reviews_link)) && (is_bool($write_review_link) && $write_review_link || is_string($write_review_link)))
				{
					$html .= ' ';
				}
				
				if (is_bool($write_review_link) && $write_review_link || is_string($write_review_link))
				{
					$html .= '<a href="' . esc_attr($write_review_url). '"' . (($write_review_link_class != NULL) ? ' class="' . esc_attr($write_review_link_class) . '"' : '') . ' target="_blank"' . (($rel != NULL) ? ' rel="' . esc_attr($rel) . '"' : '') . '>' . ((is_string($write_review_link)) ? esc_html($write_review_link) : esc_html__('Write Review', 'g-business-reviews-rating')). '</a>';
				}
				
				$html .= '</' . $html_tags[7] . '>
';
			}
			
			if ((is_bool($attribution) && $attribution || is_string($attribution) && mb_strlen($attribution) >= 1) && (!is_string($theme) || is_string($theme) && !preg_match('/\btiny\b/', $theme)))
			{
				$html .= '	<' . $html_tags[8] . ' class="attribution"><span class="powered-by-google' . ((is_string($attribution)) ? ' ' . esc_attr($attribution) : '') . '" title="' . esc_attr__('Powered by Google', 'g-business-reviews-rating') . '"></span></' . $html_tags[8] . '>
';
			}

			$html .= '</div>
';
			break;
		case 'review':
		case 'review_list':
		case 'reviews_list':
		case 'review_url':
		case 'reviews_url':
		case 'review_link':
		case 'reviews_link':
		case 'review_href':
		case 'reviews_href':
		case 'review_list_link':
		case 'reviews_list_link':
		case 'review_list_href':
		case 'reviews_list_href':
		case 'google_review':
		case 'google_review_list':
		case 'google_reviews_list':
		case 'google_review_url':
		case 'google_reviews_url':
		case 'google_review_link':
		case 'google_reviews_link':
		case 'google_review_href':
		case 'google_reviews_href':
		case 'google_review_list_link':
		case 'google_review_list_href':
		case 'google_reviews_list_link':
		case 'google_reviews_list_href':
			if ($class == NULL && is_string($link_class))
			{
				$class = $link_class;
			}
			
			$html = ($content != NULL) ? '<a href="' . $reviews_url . '"' . (($class != NULL) ? ' class="' . esc_attr($class) . '"' : '') . (($target != NULL) ? ' target="' . esc_attr($target) . '"' : '') . (($rel != NULL) ? ' rel="' . esc_attr($rel) . '"' : '') . '>' . $content . '</a>' : wp_strip_all_tags($reviews_url, TRUE);
			break;
		case 'write_review':
		case 'write_review_url':
		case 'write_review_link':
		case 'write_review_href':
		case 'google_write_review':
		case 'google_write_review_url':
		case 'google_write_review_link':
		case 'google_write_review_href':
			if ($class == NULL && is_string($link_class))
			{
				$class = $link_class;
			}

			$html = ($content != NULL) ? '<a href="' . $write_review_url . '"' . (($class != NULL) ? ' class="' . esc_attr($class) . '"' : '') . (($target != NULL) ? ' target="' . esc_attr($target) . '"' : '') . (($rel != NULL) ? ' rel="' . esc_attr($rel) . '"' : '') . '>' . $content . '</a>' : wp_strip_all_tags($write_review_url, TRUE);
			break;
		case 'url':
		case 'map':
		case 'maps':
		case 'maps_url':
		case 'maps_link':
		case 'maps_href':
		case 'google_map':
		case 'google_maps':
		case 'google_map_url':
		case 'google_map_link':
		case 'google_map_href':
		case 'google_maps_url':
		case 'google_maps_link':
		case 'google_maps_href':
			if (!is_array($this->data) || is_array($this->data) && empty($this->data))
			{
				$this->set_data();
				
				if ($this->api_version != NULL && intval($this->api_version) >= 1 && (!is_array($this->data) || is_array($this->data) && !isset($this->data['googleMapsUri']) || isset($this->data['googleMapsUri']) && $this->data['googleMapsUri'] == NULL) || $this->api_version == NULL && (!isset($this->data['result']) || isset($this->data['result']) && !is_array($this->data['result']) || is_array($this->data['result']) && !isset($this->data['result']['url'])))
				{
					if (!$errors)
					{
						return '';
					}
					
					$text = esc_html__('Error', 'g-business-reviews-rating') . ': No URL found';
					return $text;
				}
			}
			
			if ($class == NULL && is_string($link_class))
			{
				$class = $link_class;
			}

			$url = ($this->api_version != NULL && intval($this->api_version) >= 1 && isset($this->data['googleMapsUri']) && is_string($this->data['googleMapsUri'])) ? $this->data['googleMapsUri'] : ((isset($this->data['result']['url']) && is_string($this->data['result']['url'])) ? $this->data['result']['url'] : '');
			$html = ($content != NULL) ? '<a href="' . $url . '"' . (($class != NULL) ? ' class="' . esc_attr($class) . '"' : '') . (($target != NULL) ? ' target="' . esc_attr($target) . '"' : '') . (($rel != NULL) ? ' rel="' . esc_attr($rel) . '"' : '') . '>' . $content . '</a>' : $url;
			break;
		case 'structured_data':
			$tag = (is_array($html_tags) && !empty($html_tags)) ? $html_tags[0] : 'pre';
			$html = (($outer_tag) ? '<' . $tag . (($tag == 'script') ? ' type="application/ld+json"' : ' class="structured-data"') . '>' : '')
				. $this->structured_data('json')
				. (($outer_tag) ? '</' . $tag . '>' : '');
			break;
		default:
			$html = '<pre class="error">[' . esc_html($shortcode) . ' type not found: ' . esc_html($type) . ']</pre>';
			break;
		}
		
		return $html;
	}

	/* Collect unique review identifiers from stored review data */

	protected function get_review_ids(?array $reviews = NULL): ?array
	{
		$ids = [];

		if (!is_array($reviews))
		{
			$reviews = ($this->api_version != NULL && intval($this->api_version) >= 1) ? (isset($this->result['reviews']) ? $this->result['reviews'] : []) : (isset($this->result['result']['reviews']) ? $this->result['result']['reviews'] : []);
		}

		if (!is_array($reviews))
		{
			return NULL;
		}

		foreach ($reviews as $a)
		{
			$ids[] = $this->get_review_id($a);
		}

		return $ids;
	}

	/* Extract unique reviewer identifier from a single review record */

	protected function get_review_id(?array $review = NULL): ?string
	{
		if (!is_array($review))
		{
			return NULL;
		}

		$url = isset($review['author_url']) && is_string($review['author_url']) ? $review['author_url']
			: (isset($review['authorAttribution']['uri']) && is_string($review['authorAttribution']['uri']) ? $review['authorAttribution']['uri'] : NULL);

		return ($url != NULL && preg_match('/^.+[^\d](\d{20,120})[^\d].*$/', $url, $m)) ? $m[1] : NULL;
	}

	/* Render a single review item element from structured data and display options */

	protected function review_item(?array $data = NULL, ?array $options = NULL, string $type = 'all'): string
	{
		$html = '';
		extract($options, EXTR_SKIP);		
		$author_name = (isset($data['author_name']) && $data['author_name'] != NULL && ($name_format == NULL || (!is_bool($name_format) || is_bool($name_format) && $name_format))) ? $data['author_name'] : NULL;
		
		switch ($type)
		{
		case 'all':
			break;
		case 'review':
			if (isset($review_text) && !$review_text || !is_string($data['text']) || is_string($data['text']) && mb_strlen($data['text']) == 0)
			{
				return $html;
			}
			
			$review_text = $data['text'];
						
			if ($review_text != NULL && $review_text_format != NULL && preg_match('/(?:strip|remove|clear)[ _-]?line(?:[ _-]?break)?s?/i', $review_text_format) && preg_match('/(?:(?:add|insert)[ _-]?)?punctuations?/i', $review_text_format) && preg_match('/[a-z][ \t]*(?:<br\s?\/?>|\r|\n)/i', $review_text))
			{
				$review_text = preg_replace('/([a-z])[ \t]*($|<br\s?\/?>|\r|\n)/i', '$1.$2', $review_text);
			}
			
			$review_text = wp_strip_all_tags($review_text);
			$set_excerpt = (is_numeric($review_text_excerpt_length) && mb_strlen($review_text) > 20 && $review_text_excerpt_length < round(mb_strlen($review_text) * 1.1));
			$html .= '			<div class="text' . (($set_excerpt) ? ' text-excerpt' : '') . '' . (($review_text_height != NULL) ? ' fixed-height' : '') . '"' . (($review_text_height != NULL) ? ' style="height: ' . esc_attr($review_text_height) . ';"' : '') . '>';
			
			if ($review_text_format != NULL && preg_match('/(?:(?:add|insert)[ _-]?)?paragraphs?/i', $review_text_format))
			{
				$html .= PHP_EOL . '				<p>';
			}
			
			if ($review_text_format != NULL && preg_match('/(?:strip|remove|clear)[ _-]line(?:[ _-]?break)?s?/i', $review_text_format))
			{
				if ($set_excerpt)
				{
					$html .= preg_replace('/(\r\n|\r|\n)+/', ' ', preg_replace('/^(.{' . $review_text_excerpt_length . '}[^\s]{0,20})(.*)$/uis', '<span class="review-snippet">$1</span> <span class="review-more-placeholder">… ' . esc_html($more) . '</span><span class="review-full-text">$2</span>', esc_html($review_text)));
				}
				else
				{
					$html .= preg_replace('/(\r\n|\r|\n)+/', ' ', esc_html($review_text));
				}
			}
			elseif (!$set_excerpt && $review_text_format != NULL && preg_match('/(?:(?:add|insert)[ _-]?)?paragraphs?/i', $review_text_format))
			{
				$html .= preg_replace('/(\r\n|\r|\n)+/', '</p>' . PHP_EOL . '				<p>', esc_html($review_text));
			}
			else
			{
				if ($set_excerpt)
				{
					$html .= preg_replace('/(\r\n|\r|\n)+/', '<br>' . PHP_EOL . '				', preg_replace('/^(.{' . $review_text_excerpt_length . '}[^\s]{0,20})(.*)$/uis', '<span class="review-snippet">$1</span> <span class="review-more-placeholder">… ' . esc_html($more) . '</span><span class="review-full-text">$2</span>', esc_html($review_text)));
				}
				else
				{
					$html .= preg_replace('/(\r\n|\r|\n)+/', '<br>' . PHP_EOL . '				', esc_html($review_text));
				}
			}
			
			if ($review_text_format != NULL && preg_match('/(?:(?:add|insert)[ _-]?)?paragraphs?/i', $review_text_format))
			{
				$html .= '</p>' . PHP_EOL;
			}
			
			$html .= '</div>
';

			return $html;
		case 'avatar':
			if (!isset($data['author_url']) || $data['author_url'] == NULL)
			{
				return $html;
			}
			
			$html .= '			<span class="author-avatar' . (((is_bool($avatar) || is_string($avatar) && $avatar != 'local') && isset($data['avatar']) && $data['avatar'] != NULL && $avatar_directory != NULL) ? ' local' : '') . '">' . ((isset($data['author_url']) && $data['author_url'] != NULL && (is_bool($link_disable) && !$link_disable || is_array($link_disable) && !in_array('author', $link_disable))) ? '<a href="' . esc_attr($data['author_url']) . '" target="_blank"' . (($rel != NULL) ? ' rel="' . esc_attr($rel) . '"' : '') . '>' : '') . (($data['profile_photo_url'] != NULL) ? '<img src="' . esc_attr((is_string($avatar) && $avatar != 'local') ? $avatar : ((isset($data['avatar']) && $data['avatar'] != NULL && $avatar_directory != NULL) ? $avatar_directory . '/' . $data['avatar'] : $data['profile_photo_url'])) . '" alt="'
				. ((is_string($author_name) && $author_name != NULL)
				/* translators: %s refers to the name of the author with a possessive */
				? sprintf(esc_attr__('%s’s Avatar', 'g-business-reviews-rating'), $author_name)
				: esc_attr__('Avatar', 'g-business-reviews-rating'))
				. '"' . ((isset($loading) && $loading != NULL) ? ' loading="' . esc_attr($loading) . '"' : '') . '>' : '—') . ((isset($data['author_url']) && $data['author_url'] != NULL && (is_bool($link_disable) && !$link_disable || is_array($link_disable) && !in_array('author', $link_disable))) ? '</a>' : '') . '</span>
';
			return $html;
		case 'name':
			if ($author_name == NULL)
			{
				return $html;
			}
			
			if ($name_format != NULL && !empty($name_format_match))
			{
				$author_name_array = preg_split('/[.\s]+/', $author_name, -1, PREG_SPLIT_NO_EMPTY);
				$author_name = '';
				
				if (count($author_name_array) == 1 || $author_name_first || $author_name_last || $author_name_first_initials || $author_name_last_initials)
				{
					if (count($author_name_array) == 1 || $author_name_first || $author_name_first_initials)
					{
						$author_name = ($author_name_first) ? $author_name_array[0] : mb_strtoupper(mb_substr($author_name_array[0], 0, 1) . (($author_name_dot) ? '.' : ''));
			
						if (!$author_name_first && !$author_name_only && count($author_name_array) > 1)
						{
							$author_name .= ' ' . implode(' ', array_slice($author_name_array, 1));
						}
					}
					else
					{
						if (!$author_name_first && !$author_name_last && !$author_name_only)
						{
							$author_name = implode(' ', array_slice($author_name_array, 0, -1));
						}
						
						$author_name .= ($author_name_last) ? end($author_name_array) : ' ' . mb_strtoupper(mb_substr(end($author_name_array), 0, 1) . (($author_name_dot) ? '.' : ''));
					}
				}
				else
				{
					$author_name = ($author_name_last) ? end($author_name_array) : mb_strtoupper(mb_substr($author_name_array[0], 0, 1) . (($author_name_dot) ? '.' : '') . (($author_name_space) ? ' ' : '') . mb_substr(end($author_name_array), 0, 1) . (($author_name_dot) ? '.' : ''));
				}
				
				$author_name = trim($author_name);
			}
			
			if ($author_name_capitalize)
			{
				$author_name = ucwords(trim($author_name), " -\t\r\n\f\v''");
			}
			
			if ($author_name_lowercase)
			{
				$author_name = mb_strtolower(trim($author_name));
			}
			
			if ($author_name_uppercase)
			{
				$author_name = mb_strtoupper(trim($author_name));
			}
			
			$html .= '				<span class="author-name">' . ((isset($data['author_url']) && $data['author_url'] != NULL && (is_bool($link_disable) && !$link_disable || is_array($link_disable) && !in_array('author', $link_disable))) ? '<a href="' . esc_attr($data['author_url']) . '" target="_blank"' . (($rel != NULL) ? ' rel="' . esc_attr($rel) . '"' : '') . '>' : '') . esc_html($author_name) . ((isset($data['author_url']) && $data['author_url'] != NULL && (is_bool($link_disable) && !$link_disable || is_array($link_disable) && !in_array('author', $link_disable))) ? '</a>' : '') . '</span>
';
			return $html;
		case 'rating':
			if (!isset($data['rating']) || !is_numeric($data['rating']))
			{
				return $html;
			}
			
			/* translators: %s: rating value out of five, for example 4.5 */
			$rating_aria = esc_attr(sprintf(__('Rated %s out of 5', 'g-business-reviews-rating'), $data['rating']));
			$html .= '				<span class="rating" role="img" aria-label="' . $rating_aria . '">' . str_repeat('★', $data['rating']) . (($data['rating'] < 5) ? '<span class="not">' . str_repeat('☆', (5 - $data['rating'])) . '</span>' : '') . '</span>
';
			return $html;
		case 'date':
			if (!isset($data['time']) && !isset($data['relative_time_description']))
			{
				return $html;
			}

			if (is_string($date) && $date != 'relative' && is_numeric($data['time']))
			{
				$html .= '				<span class="date">' . esc_html((function_exists('wp_date')) ? wp_date($date, $data['time']) : date($date, $data['time'])) . '</span>
';
				return $html;
			}

			if (is_numeric($data['time']))
			{
				$html .= '				<span class="relative-time-description">' . esc_html($this->get_relative_time_description($data['time'])) . '</span>
';
				return $html;
			}

			$html .= '				<span class="relative-time-description">' . esc_html($data['relative_time_description']) . '</span>
';
			return $html;
		case 'navigation':
			if (!is_numeric($view) || $index <= 0 || $view <= 0 || $index < $view || is_bool($bullet) && !$bullet)
			{
				return $html;
			}
			
			$html .= '	<' . $html_tags[5] . ' class="navigation" aria-label="' . esc_attr__('Reviews carousel', 'g-business-reviews-rating') . '">'; 
				
			for ($j = 0; $j < $index / $view; $j++)
			{
				/* translators: %u: slide number, starting at 1 */
				$slide_label = esc_attr(sprintf(__('Go to slide %u', 'g-business-reviews-rating'), $j + 1));
				$html .= '		<' . $html_tags[6] . ' class="bullet' . (($j == 0) ? ' current' : '') . '"><a href="#' . esc_attr((($id_name != NULL) ? $id_name : 'google-business-reviews-rating' . (($this->instance_count > 1) ? '-' . $this->instance_count : ''))) . '" data-slide="' . esc_attr($j + 1) . '"' . (($j == 0) ? ' aria-current="true"' : '') . ' aria-label="' . $slide_label . '">' . ((is_string($bullet) && $bullet != NULL) ? $bullet : '●') . '</a></' . $html_tags[6] . '>';
			}
			
			$html .= '	</' . $html_tags[5] . '>';
			return $html;
		default:
			return $html;
		}
		
		$type = NULL;
		$check_key = NULL;
		
		if (!is_array($review_item_order))
		{
			$review_item_order = ['avatar', 'name', 'rating', 'date', 'review'];
		}
		
		if (in_array('author', $review_item_order) || in_array('authorname', $review_item_order) || in_array('author_name', $review_item_order) || in_array('author-name', $review_item_order))
		{
			$review_item_order = str_replace(['authorname', 'author_name', 'author-name', 'author'], ['name', 'name', 'name', 'name'], $review_item_order);
		}
	
		if (in_array('text', $review_item_order))
		{
			$check_key = array_search('text', $review_item_order);
			
			if (!in_array('review', $review_item_order))
			{
				$review_item_order[$check_key] = 'review';
			}
			else
			{
				unset($review_item_order[$check_key]);
			}
		}
		
		
		if (in_array('time', $review_item_order))
		{
			$check_key = array_search('time', $review_item_order);
			
			if (!in_array('date', $review_item_order))
			{
				$review_item_order[$check_key] = 'date';
			}
			else
			{
				unset($review_item_order[$check_key]);
			}
		}

		if ($author_name == NULL && in_array('name', $review_item_order))
		{
			$check_key = array_search('name', $review_item_order);

			if (is_numeric($check_key))
			{
				unset($review_item_order[$check_key]);
			}
		}
		
		if (isset($avatar) && is_bool($avatar) && !$avatar || is_string($theme) && preg_match('/\bbadge\b/', $theme) && in_array('avatar', $review_item_order))
		{
			$check_key = array_search('avatar', $review_item_order);

			if (is_numeric($check_key))
			{
				unset($review_item_order[$check_key]);
			}
		}

		if (isset($date) && is_bool($date) && !$date || is_string($date) && !is_numeric($data['time']) && in_array('date', $review_item_order))
		{
			$check_key = array_search('date', $review_item_order);

			if (is_numeric($check_key))
			{
				unset($review_item_order[$check_key]);
			}
		}

		if (isset($review_text) && is_bool($review_text) && !$review_text && in_array('review', $review_item_order))
		{
			$check_key = array_search('review', $review_item_order);

			if (is_numeric($check_key))
			{
				unset($review_item_order[$check_key]);
			}
		}

		$review_item_order = array_values($review_item_order);
		$html .= '		<' . $html_tags[4] . ' class="'
			. esc_attr('rating-' . $data['rating'])
			. (($language_class && isset($data['language']) && is_string($data['language']) && $data['language'] != NULL) ? ' ' . esc_attr('language-' . preg_replace('/[^0-9a-z-]+/', '-', mb_strtolower($data['language']))) : '')
			. ((is_numeric($view)) ? ' ' . (($index < $view) ? 'visible' : 'hidden') : '')
			. ((is_bool($avatar) && !$avatar) ? ' no-avatar' : '')
			. ((!is_bool($date) || is_bool($date) && !$date) && (!is_string($date) || is_string($date) && !is_numeric($data['time'])) ? ' no-date' : '')
			. (($review_item_text_first) ? ' text-first' : '')
			. (($review_item_inline) ? ' inline' : '')
			. (($review_item_author_switch) ? ' author-switch' : '')
			. '"'
			. (($language_class && isset($data['language']) && is_string($data['language']) && $data['language'] != NULL) ? ' data-language="' . esc_attr($data['language']) . '"' : '')
			. ' data-index="' . esc_attr($index) . '"'
			. '>';

		foreach ($review_item_order as $i => $type)
		{
			$previous_type = (array_key_exists($i - 1, $review_item_order)) ? $review_item_order[$i - 1] : NULL; 
			$next_type = (array_key_exists($i + 1, $review_item_order)) ? $review_item_order[$i + 1] : NULL;
			
			if (($previous_type == NULL || $previous_type == 'avatar' || $previous_type == 'review') && ($type == 'name' || $type == 'rating' || $type == 'date'))
			{
				$html .= '			<span class="review-meta">
';
			}

			$html .= $this->review_item($data, $options, $type);

			if (($type == 'name' || $type == 'rating' || $type == 'date') && ($next_type == NULL || $next_type == 'avatar' || $next_type == 'review'))
			{
				$html .= '			</span>
';
			}
		}

		$html .= '		</' . $html_tags[4] . '>
';
		return $html;
	}

}
