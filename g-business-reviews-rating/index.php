<?php

if (!defined('ABSPATH'))
{
	die();
}

class google_business_reviews_rating
{
	const OPTION_PREFIX = 'google_business_reviews_rating_',
		PLUGIN_ALIAS = 'google_business_reviews_rating',
		VERSION = '6.1';
	protected
		bool $settings_updated = FALSE,
		$show_reviews = FALSE,
		$administrator = FALSE,
		$local_images = FALSE,
		$editor = FALSE,
		$demo = FALSE;
	protected
		?bool $dashboard = NULL,
		$review_sort_asc = NULL,
		$retrieved_data_valid = NULL,
		$retrieved_data_exists = NULL;
	protected
		array $data = [],
		$result = [],
		$result_valid = [],
		$places = [],
		$reviews = [],
		$reviews_filtered = [],
		$relative_times = [],
		$languages = [],
		$reviews_themes = [],
		$color_schemes = [],
		$review_sort_options = [],
		$default_html_tags = [],
		$accepted_html_tags = [],
		$updates = [],
		$business_types = [],
		$price_ranges = [];
	protected
		?int $instance_count = NULL,
		$request_count = NULL,
		$count_reviews_all = NULL,
		$count_reviews_active = NULL,
		$icon_image_id = NULL,
		$logo_image_id = NULL;
	protected
		?string $review_sort = NULL,
		$place_id = NULL,
		$api_key = NULL,
		$section = NULL,
		$icon_image_url = NULL,
		$logo_image_url = NULL;
	protected
		$api_version = NULL,
		$review_sort_option = NULL;
	
	/* Class contructor that starts everything */

	public function __construct()
	{
		$this->dashboard = (is_admin() || defined('DOING_CRON'));
		$this->instance_count = NULL;
		$this->request_count = 0;
		$this->settings_updated = FALSE;
		$this->retrieved_data_valid = FALSE;
		$this->administrator = FALSE;
		$this->editor = FALSE;
		$this->review_sort = NULL;
		$this->review_sort_asc = NULL;
		$this->default_html_tags = ['h2', 'p', 'p', 'ul', 'li', 'ul', 'li', 'p', 'p', 'p'];
		$this->accepted_html_tags = ['a', 'abbr', 'address', 'article', 'aside', 'b', 'blockquote', 'br', 'caption', 'cite', 'code', 'col', 'colgroup', 'dd', 'del', 'details', 'dfn', 'div', 'dl', 'dt', 'em', 'figcaption', 'figure', 'footer', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'header', 'hr', 'i', 'img', 'ins', 'kbd', 'li', 'main', 'mark', 'nav', 'ol', 'p', 'picture', 'pre', 'q', 's', 'samp', 'section', 'small', 'span', 'strong', 'sub', 'summary', 'sup', 'table', 'tbody', 'td', 'tfoot', 'th', 'thead', 'time', 'tr', 'u', 'ul', 'var'];
		$this->updates = [
			'' => 'Never Synchronize',
			168 => 'Synchronize Weekly',
			24 => 'Synchronize Daily',
			6 => 'Synchronize Every 6 Hours',
			1 => 'Synchronize Hourly'
		];
		$this->review_sort_options = [
			'relevance_desc' => [
				'name' => NULL,
				'min_max_values' => [NULL, NULL],
				'field' => NULL,
				'asc' => FALSE,
				'static' => FALSE
			],
			'relevance_asc' => [
				'name' => NULL,
				'min_max_values' => [NULL, NULL],
				'field' => NULL,
				'asc' => TRUE,
				'static' => FALSE
			],
			'date_desc' => [
				'name' => NULL,
				'min_max_values' => [NULL, NULL],
				'field' => 'time',
				'asc' => FALSE,
				'static' => FALSE
			],
			'date_asc' => [
				'name' => NULL,
				'min_max_values' => [NULL, NULL],
				'field' => 'time',
				'asc' => TRUE,
				'static' => FALSE
			],
			'rating_desc' => [
				'name' => NULL,
				'min_max_values' => [NULL, NULL],
				'field' => 'rating',
				'asc' => FALSE,
				'static' => FALSE
			],
			'rating_asc' => [
				'name' => NULL,
				'min_max_values' => [NULL, NULL],
				'field' => 'rating',
				'asc' => TRUE,
				'static' => FALSE
			],
			'author_name_asc' => [
				'name' => NULL,
				'min_max_values' => ['A', 'Z'],
				'field' => 'author_name',
				'asc' => TRUE,
				'static' => FALSE
			],
			'author_name_desc' => [
				'name' => NULL,
				'min_max_values' => ['Z', 'A'],
				'field' => 'author_name',
				'asc' => FALSE,
				'static' => FALSE
			],
			'review_words_asc' => [
				'name' => NULL,
				'min_max_values' => [NULL, NULL],
				'field' => 'text',
				'asc' => TRUE,
				'static' => FALSE
			],
			'review_words_desc' => [
				'name' => NULL,
				'min_max_values' => [NULL, NULL],
				'field' => 'text',
				'asc' => FALSE,
				'static' => FALSE
			],
			'review_characters_asc' => [
				'name' => NULL,
				'min_max_values' => [NULL, NULL],
				'field' => 'text',
				'asc' => TRUE,
				'static' => FALSE
			],
			'review_characters_desc' => [
				'name' => NULL,
				'min_max_values' => [NULL, NULL],
				'field' => 'text',
				'asc' => FALSE,
				'static' => FALSE
			],
			'id_asc' => [
				'name' => NULL,
				'min_max_values' => [NULL, NULL],
				'field' => 'id',
				'asc' => TRUE,
				'static' => FALSE
			],
			'id_desc' => [
				'name' => NULL,
				'min_max_values' => [NULL, NULL],
				'field' => 'id',
				'asc' => FALSE,
				'static' => FALSE
			],
			'shuffle' => [
				'name' => NULL,
				'static' => TRUE
			],
			'shuffle_variable' => [
				'name' => NULL,
				'static' => FALSE
			]
		];
		$this->relative_times = [
			'hour' => [
				'text' => NULL,
				'min_time' => NULL,
				'max_time' => 2 * HOUR_IN_SECONDS,
				'divider' => HOUR_IN_SECONDS,
				'singular' => TRUE
			],
			'hours' => [
				'text' => NULL,
				'min_time' => 2 * HOUR_IN_SECONDS,
				'max_time' => DAY_IN_SECONDS,
				'divider' => HOUR_IN_SECONDS,
				'singular' => FALSE
			],
			'day' => [
				'text' => NULL,
				'min_time' => DAY_IN_SECONDS,
				'max_time' => 2 * DAY_IN_SECONDS,
				'divider' => NULL,
				'singular' => TRUE
			],
			'days' => [
				'text' => NULL,
				'min_time' => 2 * DAY_IN_SECONDS,
				'max_time' => 4 * DAY_IN_SECONDS,
				'divider' => DAY_IN_SECONDS,
				'singular' => FALSE
			],
			'within_week' => [
				'text' => NULL,
				'min_time' => 4 * DAY_IN_SECONDS,
				'max_time' => WEEK_IN_SECONDS,
				'divider' => NULL,
				'singular' => TRUE
			],
			'week' => [
				'text' => NULL,
				'min_time' => WEEK_IN_SECONDS,
				'max_time' => 2 * WEEK_IN_SECONDS,
				'divider' => NULL,
				'singular' => TRUE
			],
			'weeks' => [
				'text' => NULL,
				'min_time' => 2 * WEEK_IN_SECONDS,
				'max_time' => MONTH_IN_SECONDS,
				'divider' => WEEK_IN_SECONDS,
				'singular' => FALSE
			],
			'month' => [
				'text' => NULL,
				'min_time' => MONTH_IN_SECONDS,
				'max_time' => 2 * MONTH_IN_SECONDS,
				'divider' => NULL,
				'singular' => TRUE
			],
			'months' => [
				'text' => NULL,
				'min_time' => 2 * MONTH_IN_SECONDS,
				'max_time' => YEAR_IN_SECONDS,
				'divider' => MONTH_IN_SECONDS,
				'singular' => FALSE
			],
			'year' => [
				'text' => NULL,
				'min_time' => YEAR_IN_SECONDS,
				'max_time' => 2 * YEAR_IN_SECONDS,
				'divider' => NULL,
				'singular' => TRUE
			],
			'years' => [
				'text' => NULL,
				'min_time' => 2 * YEAR_IN_SECONDS,
				'max_time' => NULL,
				'divider' => YEAR_IN_SECONDS,
				'singular' => FALSE
			]
		];

		$this->color_schemes = [
			'cranberry' => NULL,
			'coral' => NULL,
			'pumpkin' => NULL,
			'mustard' => NULL,
			'forest' => NULL,
			'turquoise' => NULL,
			'ocean' => NULL,
			'amethyst' => NULL,
			'magenta' => NULL,
			'slate' => NULL,
			'carbon' => NULL,
			'copper' => NULL,
			'coffee' => NULL,
			'contrast' => NULL
		];
		
		add_action('init', [$this, 'loaded']);

		return TRUE;
	}

	/* Stub — overridden by google_business_reviews_rating_frontend */

	public function structured_data($return = FALSE, array $data = [])
	{
		return NULL;
	}

	/* Stubs — overridden by google_business_reviews_rating_sync */

	protected function set_data($force = NULL, ?string $api_key = NULL, ?string $place_id = NULL): bool
	{
		return FALSE;
	}

	protected function retrieve_data(string $format = 'array', bool $force = FALSE)
	{
		return [];
	}

	/* Stub — overridden by google_business_reviews_rating_dashboard */

	protected function settings(bool $set_languages = FALSE): bool
	{
		return FALSE;
	}

	protected function get_option(string $key, $default = '')
	{
		return \get_option(self::OPTION_PREFIX . $key, $default);
	}

	protected function get_array_option(string $key): array
	{
		$value = $this->get_option($key, []);

		return (is_array($value)) ? $value : [];
	}

	protected function update_option(string $key, $value, ?string $autoload = NULL): bool
	{
		return ($autoload === NULL)
			? \update_option(self::OPTION_PREFIX . $key, $value)
			: \update_option(self::OPTION_PREFIX . $key, $value, $autoload);
	}

	protected function delete_option(string $key): bool
	{
		return \delete_option(self::OPTION_PREFIX . $key);
	}

	/* Keeps one step of API history so a changeover can be dated and presented */

	protected static function set_api_history(): bool
	{
		$version = \get_option(self::OPTION_PREFIX . 'api_version', NULL);
		$history = \get_option(self::OPTION_PREFIX . 'api_history', NULL);
		$history = (is_array($history)) ? $history : [];

		if (array_key_exists('version', $history) && $history['version'] == $version)
		{
			return FALSE;
		}

		$previous = NULL;

		if (array_key_exists('version', $history))
		{
			unset($history['previous']);
			$previous = $history;
		}

		\update_option(self::OPTION_PREFIX . 'api_history', [
			'version' => (is_numeric($version)) ? intval($version) : NULL,
			'endpoint' => (is_numeric($version)) ? 'https://places.googleapis.com/v' . intval($version) . '/places/' : 'https://maps.googleapis.com/maps/api/place/details/json',
			'added' => time(),
			'previous' => $previous,
		], 'no');

		return TRUE;
	}

	/* Resolves against this file so subdirectory callers agree */

	protected static function custom_styles_file(): string
	{
		return plugin_dir_path(__FILE__) . 'wp/css/custom.css';
	}

	/* Resolves against this file so the folder can be named anything */

	public static function plugin_url(string $path): string
	{
		return plugins_url($path, __FILE__);
	}

	/* Falls back to the pre-6.0 reviews_theme key */

	protected function get_theme(): ?string
	{
		$theme = $this->get_option('theme', NULL);

		if (is_string($theme) && $theme != NULL)
		{
			return $theme;
		}

		$theme = $this->get_option('reviews_theme', NULL);

		return (is_string($theme) && $theme != NULL) ? $theme : NULL;
	}

	/* NULL when unset, an empty array when set but nothing matched */

	protected function get_place_ids($place_identifier): ?array
	{
		if (!is_string($place_identifier) || trim($place_identifier) == NULL)
		{
			return NULL;
		}

		$places = (!empty($this->places)) ? $this->places : $this->get_array_option('places');
		$known = [];
		$default_place_id = $this->get_option('place_id', NULL);

		if (is_string($default_place_id) && $default_place_id != NULL)
		{
			$known[] = $default_place_id;
		}

		foreach ($places as $a)
		{
			if (is_array($a) && isset($a['place_id']) && is_string($a['place_id']) && !in_array($a['place_id'], $known, TRUE))
			{
				$known[] = $a['place_id'];
			}
		}

		if (preg_match('/^(?:all|any|\*)$/i', trim($place_identifier)))
		{
			return $known;
		}

		$ret = [];

		foreach (preg_split('/,\s*/', trim($place_identifier), 30, PREG_SPLIT_NO_EMPTY) as $reference)
		{
			$reference = wp_strip_all_tags($reference, TRUE);

			if (preg_match('/^(?:p(?:lace)?(?:[_-]?id)?)?[_-]?(\d+)$/i', $reference, $m))
			{
				$index = intval($m[1]) - 1;

				if (isset($known[$index]) && !in_array($known[$index], $ret, TRUE))
				{
					$ret[] = $known[$index];
				}

				continue;
			}

			if (in_array($reference, $known, TRUE) && !in_array($reference, $ret, TRUE))
			{
				$ret[] = $reference;
			}
		}

		return $ret;
	}

	/* Scored locally; Places (New) has no review sort */

	protected function get_relevance_score(array $review): float
	{
		$words = 0;
		$text = (isset($review['text']) && is_string($review['text'])) ? $review['text'] : '';

		if ($text != NULL && preg_match_all('/[\pL\pN\pPd]+/u', $text, $m))
		{
			$words = count($m[0]);
		}

		/* Scripts without word spacing would otherwise count as one or two words */

		if ($text != NULL && preg_match('/[\x{3040}-\x{30FF}\x{3400}-\x{4DBF}\x{4E00}-\x{9FFF}\x{F900}-\x{FAFF}\x{AC00}-\x{D7AF}\x{0E00}-\x{0E7F}]/u', $text))
		{
			$words = max($words, mb_strlen(preg_replace('/\s+/u', '', $text)) / 2);
		}

		$age = (isset($review['time']) && is_numeric($review['time'])) ? max(0, time() - intval($review['time'])) : 2 * YEAR_IN_SECONDS;

		/* Square root so a moderate review scores close to a long one; beyond 150 words is noise */

		$substance = sqrt(min($words, 150) / 150);
		$recency = max(0, 1 - $age / (2 * YEAR_IN_SECONDS));
		$locale = preg_replace('/^[^a-z]*([a-z]{2}).*$/', '$1', mb_strtolower(strval(get_option('WPLANG', ''))));
		$review_language = (isset($review['original_language']) && is_string($review['original_language']) && $review['original_language'] != NULL) ? $review['original_language'] : ((isset($review['language']) && is_string($review['language'])) ? $review['language'] : '');
		$language = (is_string($locale) && $locale != NULL && $review_language != NULL && preg_match('/^' . preg_quote($locale, '/') . '/i', $review_language)) ? 1 : 0;
		$author = (isset($review['profile_photo_url']) && $review['profile_photo_url'] != NULL || isset($review['author_url']) && $review['author_url'] != NULL) ? 1 : 0;
		$rating = (isset($review['rating']) && is_numeric($review['rating'])) ? intval($review['rating']) / 5 : 0;

		return $substance * 0.5 + $recency * 0.34 + $language * 0.1 + $author * 0.05 + $rating * 0.01;
	}

	/* Slots reviews with no order into the existing sequence by score, leaving manual positions alone */

	protected function set_relevance_insert(): bool
	{
		$scores = [];
		$sequence = [];
		$pending = [];

		foreach ($this->reviews as $key => $a)
		{
			if (!is_array($a))
			{
				continue;
			}

			$scores[$key] = $this->get_relevance_score($a);

			if (isset($a['order']) && is_numeric($a['order']))
			{
				$sequence[$key] = intval($a['order']);
				continue;
			}

			$pending[$key] = $scores[$key];
		}

		if (empty($pending))
		{
			return FALSE;
		}

		asort($sequence, SORT_NUMERIC);
		arsort($pending, SORT_NUMERIC);
		$ordered = array_keys($sequence);

		foreach ($pending as $key => $score)
		{
			$position = 0;

			foreach ($ordered as $index => $ordered_key)
			{
				if ($scores[$ordered_key] >= $score)
				{
					$position = $index + 1;
				}
			}

			array_splice($ordered, $position, 0, [$key]);
		}

		$position = 1;

		foreach ($ordered as $key)
		{
			$this->reviews[$key]['order'] = $position;
			$position++;
		}

		return TRUE;
	}

	/* Seeds order for manual arranging */

	protected function set_relevance_order(): bool
	{
		if (empty($this->reviews))
		{
			return FALSE;
		}

		$scores = [];

		foreach ($this->reviews as $key => $a)
		{
			$scores[$key] = (is_array($a)) ? $this->get_relevance_score($a) : 0;
		}

		arsort($scores, SORT_NUMERIC);
		$position = 1;

		foreach (array_keys($scores) as $key)
		{
			$this->reviews[$key]['order'] = $position;
			$position++;
		}

		return TRUE;
	}

	/* Adds author_id and re-keys by place where the data allows */

	protected function set_review_identity(): bool
	{
		if (empty($this->reviews))
		{
			return FALSE;
		}

		$max_id = 0;

		foreach ($this->reviews as $a)
		{
			if (is_array($a) && isset($a['id']) && is_numeric($a['id']) && intval($a['id']) > $max_id)
			{
				$max_id = intval($a['id']);
			}
		}

		$reviews = [];
		$changed = FALSE;

		foreach ($this->reviews as $key => $a)
		{
			if (!is_array($a))
			{
				$reviews[$key] = $a;
				continue;
			}

			if (!isset($a['author_id']) && isset($a['author_url']) && is_string($a['author_url']) && preg_match('/(\d{20,120})/', $a['author_url'], $m))
			{
				$a['author_id'] = $m[1];
				$changed = TRUE;
			}

			if (!isset($a['id']) || !is_numeric($a['id']))
			{
				$max_id++;
				$a['id'] = $max_id;
				$changed = TRUE;
			}

			$new_key = (isset($a['author_id']) && isset($a['place_id']) && is_string($a['place_id']) && $a['place_id'] != NULL) ? $a['place_id'] . '_' . $a['author_id'] : $key;

			if ($new_key != $key && !array_key_exists($new_key, $reviews))
			{
				$reviews[$new_key] = $a;
				$changed = TRUE;
				continue;
			}

			$reviews[$key] = $a;
		}

		$this->reviews = $reviews;

		return $changed;
	}

	/* Activate plugin */

	public static function activate(bool $reset = FALSE): ?bool
	{
		if (!current_user_can('activate_plugins'))
		{
			return NULL;
		}

		/* Some databases mangle 4-byte UTF-8 on a serialize round-trip */

		$maybe_unserialize_unicode_issue = ['shamrock' => 'Here is a lucky shamrock 🍀'];
		set_transient(self::OPTION_PREFIX . 'maybe_unserialize_test', $maybe_unserialize_unicode_issue, 4);
		$maybe_unserialize_unicode_issue_check = get_transient(self::OPTION_PREFIX . 'maybe_unserialize_test');
		delete_transient(self::OPTION_PREFIX . 'maybe_unserialize_test');
		$additional_array_sanitization = ($maybe_unserialize_unicode_issue !== $maybe_unserialize_unicode_issue_check);
		
		if (get_option(self::OPTION_PREFIX . 'place_id', '') == NULL)
		{
			$plugin_data = (function_exists('get_file_data')) ? get_file_data(plugin_dir_path(__FILE__) . 'g-business-reviews-rating.php', ['Version' => 'Version'], FALSE) : [];
			$version = (array_key_exists('Version', $plugin_data)) ? $plugin_data['Version'] : NULL;
			
			if (!is_bool($reset) || is_bool($reset) && !$reset)
			{
				self::log('install', $version);
			}
			else
			{
				self::log('reset', $version);
			}
			
			update_option(self::OPTION_PREFIX . 'initial_version', $version, 'no');
			update_option(self::OPTION_PREFIX . 'place_id', NULL, 'no');
			update_option(self::OPTION_PREFIX . 'api_key', NULL, 'no');
			update_option(self::OPTION_PREFIX . 'api_version', 1, 'no');
			self::set_api_history();
			update_option(self::OPTION_PREFIX . 'language', NULL, 'no');
			update_option(self::OPTION_PREFIX . 'demo', FALSE, 'yes');
			update_option(self::OPTION_PREFIX . 'update', NULL, 'no');
			update_option(self::OPTION_PREFIX . 'review_limit', NULL, 'yes');
			update_option(self::OPTION_PREFIX . 'review_sort', NULL, 'yes');
			update_option(self::OPTION_PREFIX . 'review_sort_admin', NULL, 'no');
			update_option(self::OPTION_PREFIX . 'rating_min', NULL, 'yes');
			update_option(self::OPTION_PREFIX . 'rating_max', NULL, 'yes');
			update_option(self::OPTION_PREFIX . 'review_text_min', NULL, 'yes');
			update_option(self::OPTION_PREFIX . 'review_text_max', NULL, 'yes');
			update_option(self::OPTION_PREFIX . 'review_text_excerpt_length', 235, 'yes');
			update_option(self::OPTION_PREFIX . 'reviews_theme', NULL, 'yes');
			update_option(self::OPTION_PREFIX . 'theme', NULL, 'yes');
			update_option(self::OPTION_PREFIX . 'view', NULL, 'yes');
			update_option(self::OPTION_PREFIX . 'color_scheme', NULL, 'yes');
			update_option(self::OPTION_PREFIX . 'javascript', TRUE, 'yes');
			update_option(self::OPTION_PREFIX . 'stylesheet', TRUE, 'yes');
			update_option(self::OPTION_PREFIX . 'icon', NULL, 'no');
			update_option(self::OPTION_PREFIX . 'logo', NULL, 'no');
			update_option(self::OPTION_PREFIX . 'telephone', NULL, 'no');
			update_option(self::OPTION_PREFIX . 'business_type', NULL, 'no');
			update_option(self::OPTION_PREFIX . 'price_range', NULL, 'no');
			update_option(self::OPTION_PREFIX . 'places', NULL, 'yes');
			update_option(self::OPTION_PREFIX . 'structured_data', FALSE, 'yes');
			update_option(self::OPTION_PREFIX . 'retrieval', NULL, 'no');
			update_option(self::OPTION_PREFIX . 'retrieval_fields', ['displayName', 'formattedAddress', 'googleMapsUri', 'iconMaskBaseUri', 'id', 'shortFormattedAddress', 'rating', 'reviews', 'userRatingCount'], 'no');
			update_option(self::OPTION_PREFIX . 'retrieval_sort', 'most_relevant', 'no');
			update_option(self::OPTION_PREFIX . 'retrieval_translate', FALSE, 'no');
			update_option(self::OPTION_PREFIX . 'result', NULL, 'no');
			update_option(self::OPTION_PREFIX . 'result_valid', NULL, 'no');
			update_option(self::OPTION_PREFIX . 'reviews', NULL, 'no');
			update_option(self::OPTION_PREFIX . 'section', NULL, 'no');
			update_option(self::OPTION_PREFIX . 'editor', TRUE, 'no');
			update_option(self::OPTION_PREFIX . 'local_images', FALSE, 'yes');
			update_option(self::OPTION_PREFIX . 'custom_styles', NULL, 'yes');
			update_option(self::OPTION_PREFIX . 'meta_box_limit', 5, 'no');
			update_option(self::OPTION_PREFIX . 'log', NULL, 'no');
			update_option(self::OPTION_PREFIX . 'notifications', [
				'review' => [
					'shown' => [],
					'action' => NULL,
					'about' => []
				],
				'shortcode' => [
					'count' => 0,
					'scanned' => NULL
				]
			], 'no');
			update_option(self::OPTION_PREFIX . 'related_plugins', NULL, 'no');
		}
		else
		{
			self::log('activate');
		}
		
		update_option(self::OPTION_PREFIX . 'additional_array_sanitization', $additional_array_sanitization, 'yes');
		
		require_once(plugin_dir_path(__FILE__) . 'cron.php');
		google_business_reviews_rating_cron::cron_scheduler();

		(new static())->refresh_relative_time_descriptions();

		return TRUE;
	}

	/* Deactivate the plugin */

	public static function deactivate(): ?bool
	{
		if (!current_user_can('activate_plugins'))
		{
			return NULL;
		}
		
		delete_transient(self::OPTION_PREFIX . 'reviews_shuffled');
		delete_transient(self::OPTION_PREFIX . 'avatars_downloaded');
		wp_cache_delete('structured_data', self::OPTION_PREFIX);
		wp_cache_delete('result', self::OPTION_PREFIX);
		wp_cache_delete('result_valid', self::OPTION_PREFIX);
		wp_cache_delete('result_demo', self::OPTION_PREFIX);
		wp_cache_delete('reviews_shuffled', self::OPTION_PREFIX);
		wp_cache_delete('reviews', self::OPTION_PREFIX);
		wp_cache_delete('reviews_demo', self::OPTION_PREFIX);
		
		update_option(self::OPTION_PREFIX . 'result', NULL, 'no');
		update_option(self::OPTION_PREFIX . 'result_valid', NULL, 'no');

		self::log('deactivate');

		require_once(plugin_dir_path(__FILE__) . 'cron.php');
		google_business_reviews_rating_cron::deactivate();

		return TRUE;
	}
	
	/* Uninstall plugin */

	public static function uninstall(): ?bool
	{
		if (!current_user_can('activate_plugins', self::PLUGIN_ALIAS))
		{
			return NULL;
		}

		require_once(plugin_dir_path(__FILE__) . 'cron.php');
		google_business_reviews_rating_cron::deactivate();

		$reviews = get_option(self::OPTION_PREFIX . 'reviews', '');

		if (is_array($reviews) && function_exists('wp_get_upload_dir'))
		{
			$upload_directory = wp_get_upload_dir();
			
			if (isset($upload_directory['basedir']) && is_string($upload_directory['basedir']))
			{
				$upload_directory_plugin = $upload_directory['basedir'] . '/gmbrr';
			}
			elseif (isset($upload_directory['path']) && is_string($upload_directory['path']))
			{
				$upload_directory_plugin = preg_replace('#^(.+?)(?:/\d+/\d+)/?$#', '$1', $upload_directory['path']) . '/gmbrr';
			}

			if (is_dir($upload_directory_plugin))
			{
				foreach ($reviews as $a)
				{
					if (!isset($a['avatar']) || $a['avatar'] == NULL || !is_file($upload_directory_plugin . '/' . $a['avatar']))
					{
						continue;
					}
					
					@unlink($upload_directory_plugin . '/' . $a['avatar']);
				}

				@unlink($upload_directory_plugin);
			}
		}
		
		delete_transient(self::OPTION_PREFIX . 'reviews_shuffled');
		delete_transient(self::OPTION_PREFIX . 'avatars_downloaded');
		delete_transient(self::OPTION_PREFIX . 'force');
		delete_option('widget_' . self::PLUGIN_ALIAS);
		delete_option(self::OPTION_PREFIX . 'initial_version');
		delete_option(self::OPTION_PREFIX . 'welcome');
		delete_option(self::OPTION_PREFIX . 'place_id');
		delete_option(self::OPTION_PREFIX . 'api_key');
		delete_option(self::OPTION_PREFIX . 'api_version');
		delete_option(self::OPTION_PREFIX . 'api_history');
		delete_option(self::OPTION_PREFIX . 'language');
		delete_option(self::OPTION_PREFIX . 'demo');
		delete_option(self::OPTION_PREFIX . 'editor');
		delete_option(self::OPTION_PREFIX . 'force');
		delete_option(self::OPTION_PREFIX . 'update');
		delete_option(self::OPTION_PREFIX . 'review_limit');
		delete_option(self::OPTION_PREFIX . 'review_sort');
		delete_option(self::OPTION_PREFIX . 'review_sort_admin');
		delete_option(self::OPTION_PREFIX . 'rating_min');
		delete_option(self::OPTION_PREFIX . 'rating_max');
		delete_option(self::OPTION_PREFIX . 'review_text_min');
		delete_option(self::OPTION_PREFIX . 'review_text_max');
		delete_option(self::OPTION_PREFIX . 'review_text_excerpt_length');
		delete_option(self::OPTION_PREFIX . 'reviews_theme');
		delete_option(self::OPTION_PREFIX . 'theme');
		delete_option(self::OPTION_PREFIX . 'view');
		delete_option(self::OPTION_PREFIX . 'color_scheme');
		delete_option(self::OPTION_PREFIX . 'javascript');
		delete_option(self::OPTION_PREFIX . 'stylesheet');
		delete_option(self::OPTION_PREFIX . 'icon');
		delete_option(self::OPTION_PREFIX . 'logo');
		delete_option(self::OPTION_PREFIX . 'telephone');
		delete_option(self::OPTION_PREFIX . 'business_type');
		delete_option(self::OPTION_PREFIX . 'places');
		delete_option(self::OPTION_PREFIX . 'price_range');
		delete_option(self::OPTION_PREFIX . 'structured_data');
		delete_option(self::OPTION_PREFIX . 'settings');
		delete_option(self::OPTION_PREFIX . 'retrieval');
		delete_option(self::OPTION_PREFIX . 'retrieval_fields');
		delete_option(self::OPTION_PREFIX . 'retrieval_sort');
		delete_option(self::OPTION_PREFIX . 'retrieval_translate');
		delete_option(self::OPTION_PREFIX . 'result');
		delete_option(self::OPTION_PREFIX . 'result_valid');
		delete_option(self::OPTION_PREFIX . 'reviews');
		delete_option(self::OPTION_PREFIX . 'local_images');
		delete_option(self::OPTION_PREFIX . 'custom_styles');
		delete_option(self::OPTION_PREFIX . 'additional_array_sanitization');
		delete_option(self::OPTION_PREFIX . 'meta_box_limit');
		delete_option(self::OPTION_PREFIX . 'log');
		delete_option(self::OPTION_PREFIX . 'notifications');
		delete_option(self::OPTION_PREFIX . 'related_plugins');
		delete_option(self::OPTION_PREFIX . 'section');

		delete_metadata('user', 0, self::OPTION_PREFIX . 'section', '', TRUE);
		delete_metadata('user', 0, self::OPTION_PREFIX . 'color_scheme', '', TRUE);

		return TRUE;
	}
	
	/* Upgrade plugin */

	public static function upgrade($object, array $options): bool
	{
		if (!isset($options['action']) || isset($options['action']) && $options['action'] != 'update' || !isset($options['type']) || isset($options['type']) && $options['type'] != 'plugin' || !isset($options['plugins']) || isset($options['plugins']) && !is_array($options['plugins']))
		{
			return TRUE;
		}
		
		$plugin_directory_name = preg_replace('#^/?([^/]+)/.*$#', '$1', plugin_basename(__FILE__));
		
		foreach ($options['plugins'] as $path)
		{
			if (!preg_match('#^/?' . preg_quote($plugin_directory_name, '#'). '/.*$#', $path))
			{
				continue;
			}
			
			delete_transient(self::OPTION_PREFIX . 'reviews_shuffled');
			wp_cache_delete('structured_data', self::OPTION_PREFIX);
			wp_cache_delete('result', self::OPTION_PREFIX);
			wp_cache_delete('result_valid', self::OPTION_PREFIX);
			wp_cache_delete('result_demo', self::OPTION_PREFIX);
			wp_cache_delete('reviews_shuffled', self::OPTION_PREFIX);
			wp_cache_delete('reviews', self::OPTION_PREFIX);
			wp_cache_delete('reviews_demo', self::OPTION_PREFIX);
			
			delete_option(self::OPTION_PREFIX . 'force');

			if (get_option(self::OPTION_PREFIX . 'theme', NULL) == NULL && get_option(self::OPTION_PREFIX . 'reviews_theme', NULL) != NULL)
			{
				update_option(self::OPTION_PREFIX . 'theme', get_option(self::OPTION_PREFIX . 'reviews_theme', NULL), 'yes');
			}

			if (!is_numeric(get_option(self::OPTION_PREFIX . 'api_version', NULL)))
			{
				update_option(self::OPTION_PREFIX . 'api_version', NULL, 'no');
			}

			self::set_api_history();

			if (is_numeric(get_option(self::OPTION_PREFIX . 'local_images', 1)))
			{
				update_option(self::OPTION_PREFIX . 'local_images', FALSE, 'no');
			}
			
			if (is_numeric(get_option(self::OPTION_PREFIX . 'retrieval_sort', 1)))
			{
				update_option(self::OPTION_PREFIX . 'retrieval_sort', 'most_relevant', 'no');
			}
			
			if (!is_array(get_option(self::OPTION_PREFIX . 'retrieval_fields', NULL)))
			{
				if (is_numeric(get_option(self::OPTION_PREFIX . 'api_version', NULL)))
				{
					update_option(self::OPTION_PREFIX . 'retrieval_fields', ['displayName', 'formattedAddress', 'googleMapsUri', 'iconMaskBaseUri', 'id', 'shortFormattedAddress', 'rating', 'reviews', 'userRatingCount'], 'no');
				}
				else
				{
					update_option(self::OPTION_PREFIX . 'retrieval_fields', ['formatted_address', 'icon', 'id', 'name', 'rating', 'reviews', 'url', 'user_ratings_total', 'vicinity'], 'no');
				}
			}
			
			if (is_numeric(get_option(self::OPTION_PREFIX . 'retrieval_translate', 1)))
			{
				update_option(self::OPTION_PREFIX . 'retrieval_translate', FALSE, 'no');
			}
			
			$allow_editor = get_option(self::OPTION_PREFIX . 'editor', '');

			if ($allow_editor != get_option(self::OPTION_PREFIX . 'editor', 'x'))
			{
				update_option(self::OPTION_PREFIX . 'editor', TRUE, 'no');
			}

			$notifications = get_option(self::OPTION_PREFIX . 'notifications', NULL);

			if (!is_array($notifications) || !isset($notifications['review']) || !is_array($notifications['review']))
			{
				$notifications = [
					'review' => [
						'shown' => [],
						'action' => NULL,
						'about' => []
					],
					'shortcode' => [
						'count' => 0,
						'scanned' => NULL
					]
				];

				$log = get_option(self::OPTION_PREFIX . 'log', []);

				if (is_array($log))
				{
					$log_remaining = [];

					foreach ($log as $entry)
					{
						if (!is_array($entry) || !isset($entry['type']) || !is_string($entry['type']))
						{
							$log_remaining[] = $entry;
							continue;
						}

						$time = (isset($entry['time']) && is_numeric($entry['time'])) ? intval($entry['time']) : NULL;

						if ($entry['type'] == 'notification rate' && $time != NULL)
						{
							$notifications['review']['shown'][] = $time;
							continue;
						}

						if (preg_match('/^notification rate (later|dismiss|done)$/', $entry['type'], $action_matches))
						{
							$notifications['review']['action'] = $action_matches[1];
							continue;
						}

						if ($entry['type'] == 'notification rate now')
						{
							continue;
						}

						$log_remaining[] = $entry;
					}

					update_option(self::OPTION_PREFIX . 'log', $log_remaining, 'no');
				}

				update_option(self::OPTION_PREFIX . 'notifications', $notifications, 'no');
			}

			(new static())->refresh_relative_time_descriptions();

			$custom_styles = get_option(self::OPTION_PREFIX . 'custom_styles', '');
			
			if ($custom_styles == NULL)
			{
				return TRUE;
			}
			
			$fp = FALSE;
			$custom_styles_file = self::custom_styles_file();

			if (!is_file($custom_styles_file))
			{
				if (!is_writable(dirname($custom_styles_file)))
				{
					return TRUE;
				}
				
				$fp = fopen($custom_styles_file, 'w');
				
				if (!$fp || !is_file($custom_styles_file))
				{
					if ($fp)
					{
						fclose($fp);
					}
					
					return TRUE;
				}
			}
			
			if (!is_writable($custom_styles_file))
			{
				return TRUE;
			}
			
			if (!$fp)
			{
				$fp = fopen($custom_styles_file, 'w');
			}
				
			if (!$fp || !fwrite($fp, ($custom_styles != NULL) ? $custom_styles : ''))
			{
				return TRUE;
			}
			
			fclose($fp);

			return TRUE;
		}
		
		return TRUE;
	}
	
	/* Reset the plugin to a fresh installation */

	protected function reset(): bool
	{
		if (!current_user_can('activate_plugins'))
		{
			return FALSE;
		}

		$this->data = [];
		$this->result = [];
		$this->reviews = [];
		$this->reviews_filtered = [];
		
		if (!self::deactivate())
		{
			return FALSE;
		}
		
		if (!self::uninstall())
		{
			return FALSE;
		}
		
		if (!self::activate(TRUE))
		{
			return FALSE;
		}
		
		return TRUE;
	}

	/* Initiate the text translations */

	public function loaded(): bool
	{
		if ($this->dashboard)
		{
			$this->updates = [
				'' => __('Never Synchronize', 'g-business-reviews-rating'),
				168 => __('Synchronize Weekly', 'g-business-reviews-rating'),
				24 => __('Synchronize Daily', 'g-business-reviews-rating'),
				6 => __('Synchronize Every 6 Hours', 'g-business-reviews-rating'),
				1 => __('Synchronize Hourly', 'g-business-reviews-rating')
			];
			$this->review_sort_options['relevance_desc']['name'] = __('Relevance Descending', 'g-business-reviews-rating');
			$this->review_sort_options['relevance_desc']['min_max_values'] = [__('High', 'g-business-reviews-rating'), __('Low', 'g-business-reviews-rating')];
			$this->review_sort_options['relevance_asc']['name'] = __('Relevance Ascending', 'g-business-reviews-rating');
			$this->review_sort_options['relevance_asc']['min_max_values'] = [__('Low', 'g-business-reviews-rating'), __('High', 'g-business-reviews-rating')];
			$this->review_sort_options['date_desc']['name'] = __('Date Descending', 'g-business-reviews-rating');
			$this->review_sort_options['date_desc']['min_max_values'] = [__('New', 'g-business-reviews-rating'), __('Old', 'g-business-reviews-rating')];
			$this->review_sort_options['date_asc']['name'] = __('Date Ascending', 'g-business-reviews-rating');
			$this->review_sort_options['date_asc']['min_max_values'] = [__('Old', 'g-business-reviews-rating'), __('New', 'g-business-reviews-rating')];
			$this->review_sort_options['rating_desc']['name'] = __('Rating Descending', 'g-business-reviews-rating');
			$this->review_sort_options['rating_desc']['min_max_values'] = [__('High', 'g-business-reviews-rating'), __('Low', 'g-business-reviews-rating')];
			$this->review_sort_options['rating_asc']['name'] = __('Rating Ascending', 'g-business-reviews-rating');
			$this->review_sort_options['rating_asc']['min_max_values'] = [__('Low', 'g-business-reviews-rating'), __('High', 'g-business-reviews-rating')];
			$this->review_sort_options['author_name_asc']['name'] = __('Author’s Name Ascending', 'g-business-reviews-rating');
			$this->review_sort_options['author_name_asc']['min_max_values'] = ['A', 'Z'];
			$this->review_sort_options['author_name_desc']['name'] = __('Author’s Name Descending', 'g-business-reviews-rating');
			$this->review_sort_options['author_name_desc']['min_max_values'] = ['Z', 'A'];
			$this->review_sort_options['review_words_asc']['name'] = __('Review Word Count Ascending', 'g-business-reviews-rating');
			$this->review_sort_options['review_words_asc']['min_max_values'] = [__('Low', 'g-business-reviews-rating'), __('High', 'g-business-reviews-rating')];
			$this->review_sort_options['review_words_desc']['name'] = __('Review Word Count Descending', 'g-business-reviews-rating');
			$this->review_sort_options['review_words_desc']['min_max_values'] = [__('High', 'g-business-reviews-rating'), __('Low', 'g-business-reviews-rating')];
			$this->review_sort_options['review_characters_asc']['name'] = __('Review Character Count Ascending', 'g-business-reviews-rating');
			$this->review_sort_options['review_characters_asc']['min_max_values'] = [__('Low', 'g-business-reviews-rating'), __('High', 'g-business-reviews-rating')];
			$this->review_sort_options['review_characters_desc']['name'] = __('Review Character Count Descending', 'g-business-reviews-rating');
			$this->review_sort_options['review_characters_desc']['min_max_values'] = [__('High', 'g-business-reviews-rating'), __('Low', 'g-business-reviews-rating')];
			$this->review_sort_options['id_asc']['name'] = __('ID Ascending', 'g-business-reviews-rating');
			$this->review_sort_options['id_asc']['min_max_values'] = [__('Low', 'g-business-reviews-rating'), __('High', 'g-business-reviews-rating')];
			$this->review_sort_options['id_desc']['name'] = __('ID Descending', 'g-business-reviews-rating');
			$this->review_sort_options['id_desc']['min_max_values'] = [__('High', 'g-business-reviews-rating'), __('Low', 'g-business-reviews-rating')];
			$this->review_sort_options['shuffle']['name'] = __('Random Shuffle Static', 'g-business-reviews-rating');
			$this->review_sort_options['shuffle_variable']['name'] = __('Random Shuffle Variable', 'g-business-reviews-rating');
		}

		$this->relative_times['hour']['text'] = __('just now', 'g-business-reviews-rating');
		/* translators: %u: number of hours, days, weeks, months or years and should remain untouched */
		$this->relative_times['hours']['text'] = __('%u hours ago', 'g-business-reviews-rating');
		$this->relative_times['day']['text'] = __('a day ago', 'g-business-reviews-rating');
		/* translators: %u: number of hours, days, weeks, months or years and should remain untouched */
		$this->relative_times['days']['text'] = __('%u days ago', 'g-business-reviews-rating');
		$this->relative_times['within_week']['text'] = __('in the last week', 'g-business-reviews-rating');
		$this->relative_times['week']['text'] = __('a week ago', 'g-business-reviews-rating');
		/* translators: %u: number of hours, days, weeks, months or years and should remain untouched */
		$this->relative_times['weeks']['text'] = __('%u weeks ago', 'g-business-reviews-rating');
		$this->relative_times['month']['text'] = __('a month ago', 'g-business-reviews-rating');
		/* translators: %u: number of hours, days, weeks, months or years and should remain untouched */
		$this->relative_times['months']['text'] = __('%u months ago', 'g-business-reviews-rating');
		$this->relative_times['year']['text'] = __('a year ago', 'g-business-reviews-rating');
		/* translators: %u: number of hours, days, weeks, months or years and should remain untouched */
		$this->relative_times['years']['text'] = __('%u years ago', 'g-business-reviews-rating');
		
		if (!$this->translation_exists())
		{
			$language_code = preg_replace('/^[^a-z]*([a-z]{2}l?).*$/', '$1', mb_strtolower(get_option('WPLANG', '')));
			
			switch ($language_code)
			{
			case 'ar':
				$this->relative_times['hour']['text'] = 'الآن';
				$this->relative_times['hours']['text'] = 'قبل %u (ساعات|ساعة)';
				$this->relative_times['day']['text'] = 'قبل يوم واحد';
				$this->relative_times['days']['text'] = 'قبل %u أيام';
				$this->relative_times['within_week']['text'] = 'خلال الأسبوع الماضي';
				$this->relative_times['week']['text'] = 'قبل أسبوع';
				$this->relative_times['weeks']['text'] = 'قبل %u أسابيع';
				$this->relative_times['month']['text'] = 'قبل شهر';
				$this->relative_times['months']['text'] = 'قبل %u (أشهر|شهراً)';
				$this->relative_times['year']['text'] = 'قبل سنة';
				$this->relative_times['years']['text'] = 'قبل %u (سنوات|سنة)';
				break;
			case 'cs':
			case 'cz':
				$this->relative_times['hour']['text'] = 'právě teď';
				$this->relative_times['hours']['text'] = 'před %u hodinami';
				$this->relative_times['day']['text'] = 'před jedním dnem';
				$this->relative_times['days']['text'] = 'před %u dny';
				$this->relative_times['within_week']['text'] = 'tento týden';
				$this->relative_times['week']['text'] = 'před týdnem';
				$this->relative_times['weeks']['text'] = 'před %u týdny';
				$this->relative_times['month']['text'] = 'před měsícem';
				$this->relative_times['months']['text'] = 'před %u měsíci';
				$this->relative_times['year']['text'] = 'před rokem';
				$this->relative_times['years']['text'] = 'před %u lety';
				break;
			case 'da':
				$this->relative_times['hour']['text'] = 'nu';
				$this->relative_times['hours']['text'] = '%u timer siden';
				$this->relative_times['day']['text'] = 'en dag siden';
				$this->relative_times['days']['text'] = '%u dage siden';
				$this->relative_times['within_week']['text'] = 'for mindre end en uge siden';
				$this->relative_times['week']['text'] = 'en uge siden';
				$this->relative_times['weeks']['text'] = '%u uger siden';
				$this->relative_times['month']['text'] = 'for en måned siden';
				$this->relative_times['months']['text'] = '%u måneder siden';
				$this->relative_times['year']['text'] = 'for et år siden';
				$this->relative_times['years']['text'] = '%u år siden';
				break;
			case 'de':
				$this->relative_times['hour']['text'] = 'gerade jetzt';
				$this->relative_times['hours']['text'] = 'vor %u Stunden';
				$this->relative_times['day']['text'] = 'vor einem Tag';
				$this->relative_times['days']['text'] = 'vor %u Tagen';
				$this->relative_times['within_week']['text'] = 'in der letzten Woche';
				$this->relative_times['week']['text'] = 'vor einer Woche';
				$this->relative_times['weeks']['text'] = 'vor %u Wochen';
				$this->relative_times['month']['text'] = 'vor einem Monat';
				$this->relative_times['months']['text'] = 'vor %u Monaten';
				$this->relative_times['year']['text'] = 'vor einem Jahr';
				$this->relative_times['years']['text'] = 'vor %u Jahren';
				break;
			case 'el':
				$this->relative_times['hour']['text'] = 'πριν από μία ώρα';
				$this->relative_times['hours']['text'] = 'πριν από %u ώρες';
				$this->relative_times['day']['text'] = 'πριν από μία ημέρα';
				$this->relative_times['days']['text'] = 'πριν από %u ημέρες';
				$this->relative_times['within_week']['text'] = 'αυτή την εβδομάδα';
				$this->relative_times['week']['text'] = 'πριν από μία εβδομάδα';
				$this->relative_times['weeks']['text'] = 'πριν από %u εβδομάδες';
				$this->relative_times['month']['text'] = 'πριν από μία μήνα';
				$this->relative_times['months']['text'] = 'πριν από %u μήνες';
				$this->relative_times['year']['text'] = 'πριν από μία έτος';
				$this->relative_times['years']['text'] = 'πριν από %u έτη';
				break;
			case 'es':
				$this->relative_times['hour']['text'] = 'justo ahora';
				$this->relative_times['hours']['text'] = 'hace %u horas';
				$this->relative_times['day']['text'] = 'hace un día';
				$this->relative_times['days']['text'] = 'hace %u días';
				$this->relative_times['within_week']['text'] = 'en la ultima semana';
				$this->relative_times['week']['text'] = 'hace una semana';
				$this->relative_times['weeks']['text'] = 'hace %u semanas';
				$this->relative_times['month']['text'] = 'hace un mes';
				$this->relative_times['months']['text'] = 'hace %u meses';
				$this->relative_times['year']['text'] = 'hace un año';
				$this->relative_times['years']['text'] = 'hace %u años';
				break;
			case 'fr':
				$this->relative_times['hour']['text'] = 'maintenant';
				$this->relative_times['hours']['text'] = 'il y a %u heures';
				$this->relative_times['day']['text'] = 'il y a un jour';
				$this->relative_times['days']['text'] = 'il y a %u jours';
				$this->relative_times['within_week']['text'] = 'il y a moins d’une semaine';
				$this->relative_times['week']['text'] = 'il y a une semaine';
				$this->relative_times['weeks']['text'] = 'il y a %u semaines';
				$this->relative_times['month']['text'] = 'il y a un mois';
				$this->relative_times['months']['text'] = 'il y a %u mois';
				$this->relative_times['year']['text'] = 'il y a un an';
				$this->relative_times['years']['text'] = 'il y a %u années';
				break;
			case 'hr':
				$this->relative_times['hour']['text'] = 'upravo sada';
				$this->relative_times['hours']['text'] = 'prije %u sati';
				$this->relative_times['day']['text'] = 'prije jedan dan';
				$this->relative_times['days']['text'] = 'prije %u dana';
				$this->relative_times['within_week']['text'] = 'u posljednjem tjednu';
				$this->relative_times['week']['text'] = 'prije tjedan dana';
				$this->relative_times['weeks']['text'] = 'prije %u tjedana';
				$this->relative_times['month']['text'] = 'prije mjesec dana';
				$this->relative_times['months']['text'] = 'prije %u mjeseci';
				$this->relative_times['year']['text'] = 'prije godinu dana';
				$this->relative_times['years']['text'] = 'prije %u godina';
				break;
			case 'hu':
				$this->relative_times['hour']['text'] = 'éppen most';
				$this->relative_times['hours']['text'] = '%u órája';
				$this->relative_times['day']['text'] = '%u napja';
				$this->relative_times['days']['text'] = '%u napja';
				$this->relative_times['within_week']['text'] = 'előző héten';
				$this->relative_times['week']['text'] = 'egy hete';
				$this->relative_times['weeks']['text'] = '%u hete';
				$this->relative_times['month']['text'] = 'egy hónapja';
				$this->relative_times['months']['text'] = '%u hónapja';
				$this->relative_times['year']['text'] = 'egy éve';
				$this->relative_times['years']['text'] = '%u éve';
				break;
			case 'it':
				$this->relative_times['hour']['text'] = 'proprio adesso';
				$this->relative_times['hours']['text'] = '%u ore fa';
				$this->relative_times['day']['text'] = 'un giorno fa';
				$this->relative_times['days']['text'] = '%u giorni fa';
				$this->relative_times['within_week']['text'] = 'nell’ultima settimana';
				$this->relative_times['week']['text'] = 'una settimana fa';
				$this->relative_times['weeks']['text'] = '%u settimane fa';
				$this->relative_times['month']['text'] = 'un mese fa';
				$this->relative_times['months']['text'] = '%u mesi fa';
				$this->relative_times['year']['text'] = 'un anno fa';
				$this->relative_times['years']['text'] = '%u anni fa';
				break;
			case 'he':
			case 'iw':
				$this->relative_times['hour']['text'] = 'עַכשָׁיו';
				$this->relative_times['hours']['text'] = 'לפני %u שעות';
				$this->relative_times['day']['text'] = 'לפני יום';
				$this->relative_times['days']['text'] = 'לפני %u ימים';
				$this->relative_times['within_week']['text'] = 'לפני פחות משבוע';
				$this->relative_times['week']['text'] = 'לפני שבוע';
				$this->relative_times['weeks']['text'] = 'לפני %u שבועות';
				$this->relative_times['month']['text'] = 'לפני חודש';
				$this->relative_times['months']['text'] = 'לפני %u חודשים';
				$this->relative_times['year']['text'] = 'לפני שנה';
				$this->relative_times['years']['text'] = 'לפני %u שנים';
				break;
			case 'ja':
				$this->relative_times['hour']['text'] = 'ちょうど今';
				$this->relative_times['hours']['text'] = '%u 時間前';
				$this->relative_times['day']['text'] = '%u 日前';
				$this->relative_times['days']['text'] = '%u 日前';
				$this->relative_times['within_week']['text'] = '過去 %u 週間以内';
				$this->relative_times['week']['text'] = '一週間前';
				$this->relative_times['weeks']['text'] = '%u 週間前';
				$this->relative_times['month']['text'] = '%u か月前';
				$this->relative_times['months']['text'] = '%u か月前';
				$this->relative_times['year']['text'] = '%u 年前';
				$this->relative_times['years']['text'] = '%u 年前';
				break;
			case 'nl':
				$this->relative_times['hour']['text'] = 'net nu';
				$this->relative_times['hours']['text'] = '%u uur geleden';
				$this->relative_times['day']['text'] = 'een dag geleden';
				$this->relative_times['days']['text'] = '%u dagen geleden';
				$this->relative_times['within_week']['text'] = 'in de afgelopen week';
				$this->relative_times['week']['text'] = 'een week geleden';
				$this->relative_times['weeks']['text'] = '%u weken geleden';
				$this->relative_times['month']['text'] = 'een maand geleden';
				$this->relative_times['months']['text'] = '%u maanden geleden';
				$this->relative_times['year']['text'] = 'een jaar geleden';
				$this->relative_times['years']['text'] = '%u jaar geleden';
				break;
			case 'pl':
				$this->relative_times['hour']['text'] = 'teraz';
				$this->relative_times['hours']['text'] = '%u godzin[ay]? temu';
				$this->relative_times['day']['text'] = 'dzień temu';
				$this->relative_times['days']['text'] = '%u dni temu';
				$this->relative_times['within_week']['text'] = 'w ostatnim tygodniu';
				$this->relative_times['week']['text'] = 'tydzień temu';
				$this->relative_times['weeks']['text'] = '%u tygodni temu';
				$this->relative_times['month']['text'] = 'miesiąc temu';
				$this->relative_times['months']['text'] = '%u miesi[ąę]c[ey] temu';
				$this->relative_times['year']['text'] = 'rok temu';
				$this->relative_times['years']['text'] = '%u lat[a]? temu';
				break;
			case 'ko':
				$this->relative_times['hour']['text'] = '지금';
				$this->relative_times['hours']['text'] = '%u시간 전';
				$this->relative_times['day']['text'] = '하루 전';
				$this->relative_times['days']['text'] = '%u일 전';
				$this->relative_times['within_week']['text'] = '1주일 미만 전';
				$this->relative_times['week']['text'] = '일주일 전';
				$this->relative_times['weeks']['text'] = '%u주 전';
				$this->relative_times['month']['text'] = '한 달 전';
				$this->relative_times['months']['text'] = '%u 달전';
				$this->relative_times['year']['text'] = '일년 전';
				$this->relative_times['years']['text'] = '%u 년 전';
				break;
			case 'sr':
				$this->relative_times['hour']['text'] = 'управо сада';
				$this->relative_times['hours']['text'] = 'пре %u сати';
				$this->relative_times['day']['text'] = 'пре један дан';
				$this->relative_times['days']['text'] = 'пре %u дана';
				$this->relative_times['within_week']['text'] = 'у последњој недељи';
				$this->relative_times['week']['text'] = 'пре недељу дана';
				$this->relative_times['weeks']['text'] = 'пре %u недеља';
				$this->relative_times['month']['text'] = 'пре месец дана';
				$this->relative_times['months']['text'] = 'пре %u месеци';
				$this->relative_times['year']['text'] = 'пре годину дана';
				$this->relative_times['years']['text'] = 'пре %u година';
				break;
			case 'zh':
				if (preg_match('/^zh[_-](?:tw|hk)/i', get_option('WPLANG', '')))
				{
					$this->relative_times['hour']['text'] = '剛剛';
					$this->relative_times['hours']['text'] = '%u 小時前';
					$this->relative_times['day']['text'] = '%u 天前';
					$this->relative_times['days']['text'] = '%u 天前';
					$this->relative_times['within_week']['text'] = '過去 %u 週內';
					$this->relative_times['week']['text'] = '%u 週前';
					$this->relative_times['weeks']['text'] = '%u 週前';
					$this->relative_times['month']['text'] = '%u 個月前';
					$this->relative_times['months']['text'] = '%u 個月前';
					$this->relative_times['year']['text'] = '%u 年前';
					$this->relative_times['years']['text'] = '%u 年前';
					break;
				}

				$this->relative_times['hour']['text'] = '刚刚';
				$this->relative_times['hours']['text'] = '%u 小时前';
				$this->relative_times['day']['text'] = '%u 天前';
				$this->relative_times['days']['text'] = '%u 天前';
				$this->relative_times['within_week']['text'] = '过去 %u 周内';
				$this->relative_times['week']['text'] = '%u 周前';
				$this->relative_times['weeks']['text'] = '%u 周前';
				$this->relative_times['month']['text'] = '%u 个月前';
				$this->relative_times['months']['text'] = '%u 个月前';
				$this->relative_times['year']['text'] = '%u 年前';
				$this->relative_times['years']['text'] = '%u 年前';
				break;
			}
		}

		$this->color_schemes = [
			'cranberry' => __('Cranberry', 'g-business-reviews-rating'),
			'coral' => __('Coral', 'g-business-reviews-rating'),
			'pumpkin' => __('Pumpkin', 'g-business-reviews-rating'),
			'mustard' => __('Mustard', 'g-business-reviews-rating'),
			'forest' => __('Forest', 'g-business-reviews-rating'),
			'turquoise' => __('Turquoise', 'g-business-reviews-rating'),
			'ocean' => __('Ocean', 'g-business-reviews-rating'),
			'amethyst' => __('Amethyst', 'g-business-reviews-rating'),
			'magenta' => __('Magenta', 'g-business-reviews-rating'),
			'slate' => __('Slate', 'g-business-reviews-rating'),
			'carbon' => __('Carbon', 'g-business-reviews-rating'),
			'copper' => __('Copper', 'g-business-reviews-rating'),
			'coffee' => __('Coffee', 'g-business-reviews-rating'),
			'contrast' => __('High Contrast', 'g-business-reviews-rating')
		];

		add_action('update_option_WPLANG', [$this, 'refresh_relative_time_descriptions']);

		return TRUE;
	}

/* Check setup uses valid data and returning a result */

	protected function valid(string $check = 'status'): bool
	{
		if ($this->demo)
		{
			return TRUE;
		}
		
		$api_key = $this->get_option('api_key');
		$api_version = $this->get_option('api_version', NULL);
		$place_id = $this->get_option('place_id');
		
		if ((!is_string($api_key) || is_string($api_key) && mb_strlen($api_key) < 10) || (!is_string($place_id) || is_string($place_id) && mb_strlen($place_id) < 10) || $api_version != NULL && !preg_match('/^\d+(?:\.\d+){0,3}$/', strval($api_version)))
		{
			return FALSE;
		}

		switch ($api_version)
		{
		case 1:
			$error_code = (isset($this->data['error']['code']) && is_numeric($this->data['error']['code'])) ? intval($this->data['error']['code']) : NULL;
			$error_status = (isset($this->data['error']['status']) && is_string($this->data['error']['status']) && preg_match('/^[A-Z_]+$/i', $this->data['error']['status'])) ? $this->data['error']['status'] : NULL;
			$error_message = (isset($this->data['error']['message']) && is_string($this->data['error']['message'])) ? $this->data['error']['message'] : NULL;

			switch ($check)
			{
			case 'api':
			case 'api_key':
			case 'restriction':
			case 'restrictions':
				return (!empty($this->data) && isset($this->data['error']) && is_string($error_status) && preg_match('/^REQUEST_DENIED$/i', $error_status) && is_string($error_message) && preg_match('/referr?er\s+restrictions?/i', $error_message));
			case 'billing':
				return (!empty($this->data) && isset($this->data['error']) && is_string($error_status) && preg_match('/^REQUEST_DENIED$/i', $error_status) && is_string($error_message) && preg_match('/billing/i', $error_message));
			default:
				break;
			}

			return (!empty($this->data) && isset($this->data['id']) && $this->data['id'] != NULL && ($error_code == NULL || is_numeric($error_code) && $error_code >= 200 && $error_code < 300));
		default:
			$error_status = (isset($this->data['status']) && is_string($this->data['status']) && preg_match('/^[A-Z_]+$/i', $this->data['status'])) ? $this->data['status'] : NULL;
			$error_message = (isset($this->data['error_message']) && is_string($this->data['error_message']) && preg_match('/^[A-Z_]+$/i', $this->data['error_message'])) ? $this->data['error_message'] : NULL;

			switch ($check)
			{
			case 'api':
			case 'api_key':
			case 'restriction':
			case 'restrictions':
				return (!empty($this->data) && isset($this->data['status']) && is_string($error_status) && preg_match('/^REQUEST_DENIED$/i', $error_status) && is_string($error_message) && preg_match('/referr?er\s+restrictions?/i', $error_message));
			case 'billing':
				return (!empty($this->data) && isset($this->data['status']) && is_string($error_status) && preg_match('/^REQUEST_DENIED$/i', $error_status) && is_string($error_message) && preg_match('/billing/i', $error_message));
			default:
				break;
			}
			
			return (!empty($this->data) && isset($this->data['status']) && is_string($error_status) && preg_match('/^OK$/i', $error_status));
		}

		return FALSE;
	}

	/* Build and return the structured data JSON string, or NULL if unavailable */

	public function get_structured_data_json(array $data = []): ?string
	{
		if ($this->demo)
		{
			return NULL;
		}

		if (!is_array($this->data) || empty($this->data))
		{
			$this->set_data();

			if ($this->api_version != NULL && intval($this->api_version) >= 1 && (!isset($this->data['reviews']) || !is_array($this->data['reviews'])) || $this->api_version == NULL && (!isset($this->data['result']) || !is_array($this->data['result'])))
			{
				return NULL;
			}
		}

		if (!$this->valid() || $this->reviews_count(NULL, TRUE) < 1 || ($this->api_version != NULL && intval($this->api_version) >= 1 && (!array_key_exists('displayName', $this->data) || !isset($this->data['displayName']['text']) || $this->data['displayName']['text'] == NULL)) || $this->api_version == NULL && (!isset($this->data['result']['name']) || $this->data['result']['name'] == NULL))
		{
			return NULL;
		}

		$name = $this->get_data('name');
		$logo = $this->get_data('logo');
		$address = $this->get_data('address');
		$rating = (is_numeric($this->get_data('rating'))) ? round(floatval($this->get_data('rating')), 1) : 0;
		$rating_count = (is_numeric($this->get_data('rating_count'))) ? intval($this->get_data('rating_count')) : 0;
		$telephone = $this->get_option('telephone', FALSE);
		$business_type = ($this->get_option('business_type') != NULL) ? $this->get_option('business_type') : FALSE;
		$price_range = is_numeric($this->get_option('price_range', NULL)) ? str_repeat('$', $this->get_option('price_range')) : FALSE;

		extract($data, EXTR_OVERWRITE);

		$schema = [
			'@context'    => 'http://schema.org',
			'@type'       => 'LocalBusiness',
			'name'        => ($name != NULL) ? $name : FALSE,
			'address'     => ($address != NULL) ? $address : FALSE,
			'image'       => ($logo != NULL) ? $logo : FALSE,
			'url'         => get_site_url(),
			'telephone'   => ($telephone != NULL) ? $telephone : FALSE,
			'additionalType' => ($business_type != NULL) ? $business_type : FALSE,
			'priceRange'  => ($price_range != NULL) ? $price_range : FALSE,
			'AggregateRating' => [
				'@type'        => 'AggregateRating',
				'itemReviewed' => ($name != NULL) ? $name : FALSE,
				'bestRating'   => 5,
				'worstRating'  => 1,
				'ratingValue'  => (is_numeric($rating)) ? $rating : FALSE,
				'ratingCount'  => (is_numeric($rating_count)) ? $rating_count : 0
			],
			'review' => []
		];

		foreach ($this->reviews as $a)
		{
			if (!$a['status'])
			{
				continue;
			}

			if (count($schema['review']) >= 5)
			{
				break;
			}

			$schema['review'][] = [
				'@type'  => 'Review',
				'author' => [
					'@type' => 'Person',
					'name'  => ($a['author_name'] != NULL) ? $a['author_name'] : FALSE
				],
				'datePublished' => (function_exists('wp_date')) ? wp_date("Y-m-d", $a['time']) : gmdate("Y-m-d", $a['time']),
				'description'   => (mb_strlen($a['text']) > 1) ? wp_strip_all_tags($a['text']) : FALSE,
				'name'          => ($name != NULL) ? $name : FALSE,
				'reviewRating'  => [
					'@type'       => 'Rating',
					'bestRating'  => 5,
					'ratingValue' => $a['rating'],
					'worstRating' => 1
				]
			];
		}

		return json_encode($schema, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
	}

	/* Check to display retrieved data */

	public function retrieved_data_check(bool $current = FALSE): bool
	{
		if ($this->demo)
		{
			return TRUE;
		}

		if ($current)
		{
			return ($this->get_data('boolean', NULL, TRUE));
		}
		
		if (is_bool($this->retrieved_data_exists))
		{
			return $this->retrieved_data_exists;
		}
		
		$this->retrieved_data_exists = (get_option('google_business_reviews_rating_place_id', '') != NULL && $this->get_data('boolean'));
		
		return $this->retrieved_data_exists;
	}

/* Return data from Google Places API, place data or an option value */

	public function get_data(string $type = 'array', $place_id = NULL, bool $valid = FALSE)
	{
		$data = [];
		$ret = NULL;		
		$retrieved_data_formats = ['boolean', 'html', 'json', 'array', NULL];

		if (!isset($this->api_version))
		{
			$this->api_version = $this->get_option('api_version', NULL);
		}

		if (is_array($place_id))
		{
			$place_id = (!empty($place_id)) ? reset($place_id) : NULL;
		}

		if (is_null($place_id) || !is_string($place_id) || is_string($place_id) && mb_strlen($place_id) < 16)
		{
			$place_id = $this->place_id;
		}
		
		if (!in_array($type, $retrieved_data_formats))
		{
			if (empty($this->places))
			{
				$this->places = ($place_id != $this->place_id) ? $this->get_array_option('places') : [];
			}
			
			if (empty($this->data))
			{
				$this->set_data();
			}
		}
		else
		{
			if ($this->demo)
			{
				return $this->retrieve_data($type);
			}
	
			if ($this->dashboard && !$valid)
			{
				$this->api_key = ($this->api_key != NULL) ? $this->api_key : $this->get_option('api_key', NULL);
				$this->place_id = ($this->place_id != NULL) ? $this->place_id : $this->get_option('place_id', NULL);
				$data = $this->retrieve_data($type);
			}
			
			if (($this->dashboard && $valid) || (!$this->dashboard && !$valid && (!isset($data['status'])) || isset($data['status']) && !preg_match('/^OK$/i', $data['status'])))
			{
				$data = $this->get_option('result_valid', []);
			}
			elseif (!is_array($data))
			{
				$data = ($valid) ? $this->get_option('result_valid', NULL) : $this->get_option('result', NULL);
				
				if (!is_array($data))
				{
					$data = [];
				}
			}
		}
		
		switch ($type)
		{
		case 'array':
		case NULL:
			$ret = $data;
			break;
		case 'json':
			$ret = json_encode($data);
			break;
		case 'boolean':
			if ($this->dashboard && $valid)
			{
				$data_check = $this->get_option('result', []);
				$this->retrieved_data_valid = (empty($data_check) || empty($data) || serialize($data_check) == serialize($data));
				$ret = $this->retrieved_data_valid;
				break;
			}

			$ret = (is_array($data) && !empty($data));
			break;
		case 'html':
			$ret = '	<pre id="google-business-reviews-rating-' . (($valid) ? 'valid-' : '') . 'data">' . esc_html(json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)) . '</pre>
';
			break;
		case 'address':
		case 'formattedAddress':
		case 'formatted_address':
			if (is_array($this->places))
			{
				foreach ($this->places as $a)
				{
					if ($a['place_id'] != $place_id)
					{
						continue;
					}
					
					if (!isset($a['formatted_address']) || isset($a['formatted_address']) && !is_string($a['formatted_address']))
					{
						break;
					}
					
					$ret = $a['formatted_address'];
					break(2);
				}
			}
			
			if ($this->api_version != NULL && intval($this->api_version) >= 1 && isset($this->data['formattedAddress']) && $this->data['formattedAddress'] != NULL)
			{
				$ret = $this->data['formattedAddress'];
				break;
			}

			if ($this->api_version == NULL && isset($this->data['result']['formatted_address']) && $this->data['result']['formatted_address'] != NULL)
			{
				$ret = $this->data['result']['formatted_address'];
				break;
			}
			
			break;
		case 'name':
			if (is_array($this->places))
			{
				foreach ($this->places as $a)
				{
					if ($a['place_id'] != $place_id)
					{
						continue;
					}
					
					if (!isset($a[$type]) || isset($a[$type]) && $a[$type] == NULL)
					{
						break;
					}
					
					$ret = $a[$type];
					break(2);
				}
			}

			if ($this->api_version != NULL && intval($this->api_version) >= 1)
			{
				$ret = (isset($this->data['displayName']) && isset($this->data['displayName']['text']) && $this->data['displayName']['text'] != NULL) ? $this->data['displayName']['text'] : NULL;
				break;
			}

			$ret = (isset($this->data['result'][$type])) ? $this->data['result'][$type] : NULL;
			break;
		case 'vicinity':
			if (is_array($this->places))
			{
				foreach ($this->places as $a)
				{
					if ($a['place_id'] != $place_id)
					{
						continue;
					}
					
					if (!isset($a[$type]) || isset($a[$type]) && $a[$type] == NULL)
					{
						break;
					}
					
					$ret = $a[$type];
					break(2);
				}
			}
			
			if ($this->api_version != NULL && intval($this->api_version) >= 1)
			{
				$ret = (isset($this->data['shortFormattedAddress']) && $this->data['shortFormattedAddress'] != NULL) ? $this->data['shortFormattedAddress'] : NULL;
				break;
			}
			
			$ret = (isset($this->data['result'][$type])) ? $this->data['result'][$type] : NULL;
			break;
		case 'rating':
			if (is_array($this->places))
			{
				foreach ($this->places as $a)
				{
					if ($a['place_id'] != $place_id)
					{
						continue;
					}
					
					if (!isset($a[$type]) || isset($a[$type]) && $a[$type] == NULL)
					{
						break;
					}
					
					$ret = $a[$type];
					break(2);
				}
			}
			
			if ($this->api_version != NULL && intval($this->api_version) >= 1 && isset($this->data[$type]) && $this->data[$type] != NULL)
			{
				$ret = $this->data[$type];
				break;
			}
			
			if ($this->api_version == NULL && isset($this->data['result'][$type]) && $this->data['result'][$type] != NULL)
			{
				$ret = $this->data['result'][$type];
				break;
			}
			
			if ($this->api_version != NULL && intval($this->api_version) >= 1 && isset($this->data['reviews']) && is_array($this->data['reviews']) && !empty($this->data['reviews']))
			{
				$ratings = [];
				
				foreach ($this->data['reviews'] as $a)
				{
					$ratings[] = $a['rating'];
				}
				
				$ret = (!empty($ratings)) ? array_sum($ratings)/count($ratings) : 0;
				break;
			}

			if ($this->api_version == NULL && isset($this->data['result']['reviews']) && is_array($this->data['result']['reviews']) && !empty($this->data['result']['reviews']))
			{
				$ratings = [];
				
				foreach ($this->data['result']['reviews'] as $a)
				{
					$ratings[] = $a['rating'];
				}
				
				$ret = (!empty($ratings)) ? array_sum($ratings)/count($ratings) : 0;
			}

			break;
		case 'rating_rounded':
			$ret = 0;
			
			if (is_array($this->places))
			{
				foreach ($this->places as $a)
				{
					if ($a['place_id'] != $place_id)
					{
						continue;
					}
					
					if ($a['rating'] == NULL)
					{
						break;
					}
					
					$ret = (function_exists('number_format_i18n')) ? number_format_i18n($a['rating'], 1) : number_format($a['rating'], 1);
					break(2);
				}
			}
			
			if ($this->api_version != NULL && intval($this->api_version) >= 1 && isset($this->data['rating']) && $this->data['rating'] != NULL)
			{
				$ret = (function_exists('number_format_i18n')) ? number_format_i18n($this->data['rating'], 1) : number_format($this->data['rating'], 1);
				break;
			}
			
			if ($this->api_version == NULL && isset($this->data['result']['rating']) && $this->data['result']['rating'] != NULL)
			{
				$ret = (function_exists('number_format_i18n')) ? number_format_i18n($this->data['result']['rating'], 1) : number_format($this->data['result']['rating'], 1);
				break;
			}
						
			if ($this->api_version != NULL && intval($this->api_version) >= 1 && isset($this->data['reviews']) && is_array($this->data['reviews']) && !empty($this->data['reviews']))
			{
				$ratings = [];
				
				foreach ($this->data['reviews'] as $a)
				{
					$ratings[] = $a['rating'];
				}
				
				$ret = (!empty($ratings)) ? ((function_exists('number_format_i18n')) ? number_format_i18n(array_sum($ratings)/count($ratings), 1) : number_format(array_sum($ratings)/count($ratings), 1)) : 0;
				break;
			}
						
			if ($this->api_version == NULL && isset($this->data['result']['reviews']) && is_array($this->data['result']['reviews']) && !empty($this->data['result']['reviews']))
			{
				$ratings = [];
				
				foreach ($this->data['result']['reviews'] as $a)
				{
					$ratings[] = $a['rating'];
				}
				
				$ret = (!empty($ratings)) ? ((function_exists('number_format_i18n')) ? number_format_i18n(array_sum($ratings)/count($ratings), 1) : number_format(array_sum($ratings)/count($ratings), 1)) : 0;
			}
			break;
		case 'rating_count':
			$ret = 0;
			
			if (is_array($this->places))
			{
				foreach ($this->places as $a)
				{
					if ($a['place_id'] != $place_id)
					{
						continue;
					}
					
					if (!is_numeric($a[$type]))
					{
						break;
					}
					
					$ret = $a[$type];
					break(2);
				}
			}
			
			if ($this->api_version != NULL && intval($this->api_version) >= 1 && isset($this->data['userRatingCount']) && $this->data['userRatingCount'] != NULL)
			{
				$ret = intval($this->data['userRatingCount']);
			}
			elseif ($this->api_version == NULL && isset($this->data['result']['user_ratings_total']) && $this->data['result']['user_ratings_total'] != NULL)
			{
				$ret = intval($this->data['result']['user_ratings_total']);
			}
			
			if (is_numeric($ret) && $ret > 0)
			{
				break;
			}
			
			if (isset($this->reviews) && is_array($this->reviews) && !empty($this->reviews))
			{
				$ret = $this->reviews_count($place_id, NULL, FALSE);
				break;
			}
			
			if ($this->api_version != NULL && intval($this->api_version) >= 1 && isset($this->data['reviews']) && is_array($this->data['reviews']))
			{
				$ret = count($this->data['reviews']);
				break;
			}

			if ($this->api_version == NULL && isset($this->data['result']['reviews']) && is_array($this->data['result']['reviews']))
			{
				$ret = count($this->data['result']['reviews']);
			}
			
			break;
		case 'rating_count_rounded':
			$ret = 0;
			
			if (is_array($this->places))
			{
				foreach ($this->places as $a)
				{
					if ($a['place_id'] != $place_id)
					{
						continue;
					}
					
					if ($a['rating'] == NULL)
					{
						break;
					}
					
					$ret = (function_exists('number_format_i18n')) ? number_format_i18n($a['rating_count'], 0) : number_format($a['rating_count'], 0);
					break(2);
				}
			}
			
			if ($this->api_version != NULL && intval($this->api_version) >= 1 && isset($this->data['userRatingCount']) && $this->data['userRatingCount'] != NULL)
			{
				$ret = (function_exists('number_format_i18n')) ? number_format_i18n($this->data['userRatingCount'], 0) : number_format($this->data['userRatingCount'], 0);
				break;
			}

			if ($this->api_version == NULL && isset($this->data['result']['user_ratings_total']) && $this->data['result']['user_ratings_total'] != NULL)
			{
				$ret = (function_exists('number_format_i18n')) ? number_format_i18n($this->data['result']['user_ratings_total'], 0) : number_format($this->data['result']['user_ratings_total'], 0);
				break;
			}
			
			if (isset($this->reviews) && is_array($this->reviews) && !empty($this->reviews))
			{
				$ret = (function_exists('number_format_i18n')) ? number_format_i18n($this->reviews_count($place_id, NULL, FALSE), 0) : number_format($this->reviews_count($place_id, NULL, FALSE), 0);
				break;
			}
			
			if ($this->api_version != NULL && intval($this->api_version) >= 1 && isset($this->data['reviews']) && is_array($this->data['reviews']) && !empty($this->data['reviews']))
			{
				$ret = (function_exists('number_format_i18n')) ? number_format_i18n(count($this->data['reviews']), 0) : number_format(count($this->data['reviews']), 0);
			}

			if ($this->api_version == NULL && isset($this->data['result']['reviews']) && is_array($this->data['result']['reviews']) && !empty($this->data['result']['reviews']))
			{
				$ret = (function_exists('number_format_i18n')) ? number_format_i18n(count($this->data['result']['reviews']), 0) : number_format(count($this->data['result']['reviews']), 0);
			}
			
			break;
		case 'logo':
			if ($this->logo_image_url != NULL)
			{
				$ret = $this->logo_image_url;
				break;
			}
	
			$logo = $this->get_option('logo');
			$this->logo_image_id = (is_numeric($logo)) ? intval($logo) : NULL;

			if (is_numeric($this->logo_image_id))
			{
				$a = wp_get_attachment_image_src($this->logo_image_id, 'full');
				$this->logo_image_url = (isset($a[0]) && is_string($a[0])) ? $a[0] : NULL;
				
				if ($this->logo_image_url != NULL)
				{
					$ret = $this->logo_image_url;
					break;
				}
			}
			
			$seo_titles = get_option('wpseo_titles', '');
			
			if (is_array($seo_titles) && isset($seo_titles['company_logo']) && is_string($seo_titles['company_logo']))
			{
				$ret = $seo_titles['company_logo'];
				break;
			}
			
			/* Intentional */

		case 'icon':
			if ($this->icon_image_url != NULL)
			{
				$ret = $this->icon_image_url;
				break;
			}
	
			$icon = $this->get_option('icon');
			$this->icon_image_id = (is_numeric($icon)) ? intval($icon) : NULL;

			if (is_numeric($this->icon_image_id))
			{
				$a = wp_get_attachment_image_src($this->icon_image_id, 'full');
				$this->icon_image_url = (isset($a[0]) && is_string($a[0])) ? $a[0] : NULL;
				
				if ($this->icon_image_url != NULL)
				{
					$ret = $this->icon_image_url;
					break;
				}
			}

if ($this->api_version != NULL && intval($this->api_version) >= 1 && isset($this->data['iconMaskBaseUri']) && $this->data['iconMaskBaseUri'] != NULL)
			{
				$ret = $this->data['iconMaskBaseUri'] . '.svg';
				break;
			}

			if ($this->api_version == NULL && isset($this->data['result']['icon']) && $this->data['result']['icon'] != NULL)
			{
				$ret = $this->data['result']['icon'];
				break;
			}
			
			if (is_array($this->places))
			{
				foreach ($this->places as $a)
				{
					if ($a['place_id'] != $place_id)
					{
						continue;
					}
					
					if (!isset($a['icon']) || $a['icon'] == NULL)
					{
						break;
					}
					
					$ret = $a['icon'];
					break(2);
				}
			}
			
			$retrieval = $this->get_option('retrieval');
			
			if (is_array($retrieval) && isset($retrieval['requests']) && !empty($retrieval['requests']))
			{
				krsort($retrieval);
				foreach ($retrieval as $a)
				{
					if (!isset($a['icon']) || $a['icon'] == NULL)
					{
						continue;
					}
					
					$ret = $a['icon'];
					break(2);
				}
			}
			
			break;
		}
		
		return $ret;
	}

/* Update stored record of all reviews collected */

	public function set_reviews(bool $force = FALSE): bool
	{
		if ($this->api_version == NULL)
		{
			$this->api_version = $this->get_option('api_version', NULL);
		}
		
		if (!$this->valid() || empty($this->data) || !empty($this->data) && ($this->api_version != NULL && intval($this->api_version) >= 1 && (!isset($this->data['reviews']) || !is_array($this->data['reviews'])) || $this->api_version == NULL && (!isset($this->data['result']['reviews']) || !is_array($this->data['result']['reviews']))))
		{
			return FALSE;
		}
		
		if (!$force)
		{
			if (!$this->dashboard)
			{
				$cached = wp_cache_get((($this->demo) ? 'reviews_demo' : 'reviews'), self::OPTION_PREFIX);

				if (is_array($cached))
				{
					$this->reviews = $cached;
				}
			}
			
			if (is_array($this->reviews) && !empty($this->reviews))
			{
				$this->reviews_filtered = $this->reviews;
				return TRUE;
			}
		}

wp_cache_delete('structured_data', self::OPTION_PREFIX);
		wp_cache_delete((($this->demo) ? 'reviews_demo' : 'reviews'), self::OPTION_PREFIX);
		
		$this->settings(TRUE);
		$this->reviews = (!$this->demo) ? $this->get_array_option('reviews') : [];

		if (!$this->demo && $this->set_review_identity())
		{
			$this->update_option('reviews', $this->reviews, 'no');
		}

		$this->local_images = $this->get_option('local_images', FALSE);
		$language = $this->get_option('language', NULL);
		$translate = (is_bool($this->get_option('retrieval_translate', NULL)) && $this->get_option('retrieval_translate') || is_string($this->get_option('retrieval_translate')) && preg_match('/^(?:1|true)$/i', $this->get_option('retrieval_translate')) || is_numeric($this->get_option('retrieval_translate')) && intval($this->get_option('retrieval_translate')) >= 1);
		$retrieval_sort = $this->get_option('retrieval_sort', 'most_relevant');
		$retrieval_sort_current = $this->get_retrieval_sort();
		$use_relative_time_description = (!$this->translation_exists(TRUE));
		$relative_time_description_update = FALSE;
		$max_id = 0;

		foreach ($this->reviews as $a)
		{
			if (is_array($a) && isset($a['id']) && is_numeric($a['id']) && intval($a['id']) > $max_id)
			{
				$max_id = intval($a['id']);
			}
		}
		$relevance = ($retrieval_sort_current != $retrieval_sort && $retrieval_sort_current == 'newest' && count($this->reviews) >= 5) ? 5 : 1;
		$count = 1;
		$checked_keys = [];
		$reviews = ($this->api_version != NULL && $this->api_version >= 1) ? $this->data['reviews'] : (isset($this->data['result']['reviews']) ? $this->data['result']['reviews'] : []);

		$this->reviews = array_filter($this->reviews,
			function ($v, $k)
			{
				return (array_key_exists('rating', $v) && array_key_exists('time', $v) && array_key_exists('author_name', $v));
			},
			ARRAY_FILTER_USE_BOTH
		);
		
		foreach ($reviews as $review)
		{
			$key = NULL;
			
			if (!$this->demo)
			{
				switch ($this->api_version)
				{
				case 1:
					$author_id = (isset($review['authorAttribution']['uri']) && is_string($review['authorAttribution']['uri']) && preg_match('/(\d{20,120})/', $review['authorAttribution']['uri'], $m)) ? $m[1] : NULL;
					$key = ($author_id != NULL && $this->place_id != NULL) ? $this->place_id . '_' . $author_id : (isset($review['publishTime']) ? (strtotime($review['publishTime']) ?: 0) : 0) . '_' . (isset($review['rating']) ? $review['rating'] : '') . '_' . md5(((isset($review['authorAttribution']['displayName']) && is_string($review['authorAttribution']['displayName'])) ? $review['authorAttribution']['displayName'] : '') . '_' . mb_substr(((isset($review['originalText']['text']) && is_string($review['originalText']['text'])) ? $review['originalText']['text'] : ''), 0, 100));

					if (($force || $this->dashboard) && array_key_exists($key, $this->reviews))
					{
						$this->reviews[$key]['relative_time_description'] = (isset($review['publishTime']) && isset($review['relativePublishTimeDescription'])) ? $this->get_relative_time_description($review['publishTime'], $review['relativePublishTimeDescription'], $use_relative_time_description) : $this->get_relative_time_description((isset($review['publishTime'])) ? $review['publishTime'] : NULL, NULL, TRUE);
						$this->reviews[$key]['checked'] = time();
						$this->reviews[$key]['removable'] = FALSE;
						$checked_keys[] = $this->reviews[$key]['id'];
						$relevance++;
						continue(2);
					}

					foreach (array_keys($this->reviews) as $key_temp)
					{
						if (isset($this->reviews[$key_temp]['reference']) && $this->reviews[$key_temp]['reference'] != NULL)
						{
							if ($this->reviews[$key_temp]['reference'] != $review['name'])
							{
								continue;
							}
						}
						else
						{
							$author_url_id = (array_key_exists('author_url', $this->reviews[$key_temp]) && $this->reviews[$key_temp]['author_url'] != NULL && preg_match('/^.+[^\d](\d{20,120})[^\d].*$/', $this->reviews[$key_temp]['author_url'], $m)) ? $m[1] : NULL;
							$author_check = ($author_url_id != NULL && !empty($review['authorAttribution']['uri']) && preg_match('/^.+[^\d](\d{20,120})[^\d].*$/', $review['authorAttribution']['uri'], $m)) ? ($author_url_id == $m[1]) : ($author_url_id == NULL);
				
							if ($this->reviews[$key_temp]['author_name'] != ($review['authorAttribution']['displayName'] ?? NULL) || !$author_check)
							{
								continue;
							}
						}

						$key = $key_temp;
						$this->reviews[$key] = [
							'id' => (isset($this->reviews[$key_temp]['id']) && is_numeric($this->reviews[$key_temp]['id'])) ? $this->reviews[$key_temp]['id'] : $max_id + $count,
							'author_id' => (isset($review['authorAttribution']['uri']) && is_string($review['authorAttribution']['uri']) && preg_match('/(\d{20,120})/', $review['authorAttribution']['uri'], $m)) ? $m[1] : NULL,
							'reference' => (isset($review['name']) && is_string($review['name'])) ? $review['name'] : NULL,
							'place_id' => $this->place_id,
							'checked' => NULL,
							'imported' => FALSE,
							'time_estimate' => FALSE,
							'status' => (isset($review['rating']) && is_numeric($review['rating']) && $review['rating'] >= 1 && $review['rating'] <= 5),
							'profile_photo_url' => (isset($review['authorAttribution']['photoUri']) && is_string($review['authorAttribution']['photoUri'])) ? $review['authorAttribution']['photoUri'] : NULL,
							'avatar' => NULL,
							'author_name' => (isset($review['authorAttribution']['displayName']) && is_string($review['authorAttribution']['displayName'])) ? $review['authorAttribution']['displayName'] : NULL,
							'author_url' => (isset($review['authorAttribution']['uri']) && is_string($review['authorAttribution']['uri'])) ? $review['authorAttribution']['uri'] : NULL,
							'language' => (isset($review['originalText']['languageCode']) && is_string($review['originalText']['languageCode'])) ? $review['originalText']['languageCode'] : NULL,
							'original_language' => (isset($review['originalText']['languageCode']) && is_string($review['originalText']['languageCode'])) ? $review['originalText']['languageCode'] : NULL,
							'rating' => (isset($review['rating']) && is_numeric($review['rating']) && $review['rating'] >= 1 && $review['rating'] <= 5) ? intval($review['rating']) : NULL,
							'text' => (isset($review['originalText']['text']) && is_string($review['originalText']['text'])) ? $review['originalText']['text'] : NULL,
							'original_text' => NULL,
							'review_url' => (isset($review['googleMapsUri']) && is_string($review['googleMapsUri'])) ? $review['googleMapsUri'] : NULL,
							'time' => (isset($review['publishTime'])) ? (strtotime($review['publishTime']) ?: NULL) : NULL,
							'visit_date' => (isset($review['visitDate']) && is_array($review['visitDate'])) ? ['year' => (isset($review['visitDate']['year']) && is_numeric($review['visitDate']['year'])) ? intval($review['visitDate']['year']) : NULL, 'month' => (isset($review['visitDate']['month']) && is_numeric($review['visitDate']['month'])) ? intval($review['visitDate']['month']) : NULL] : NULL,
							'translated' => (isset($review['text']['languageCode']) && isset($review['originalText']['languageCode']) && $review['text']['languageCode'] != $review['originalText']['languageCode']),
							'relative_time_description' => (isset($review['publishTime']) && isset($review['relativePublishTimeDescription'])) ? $this->get_relative_time_description($review['publishTime'], $review['relativePublishTimeDescription'], $use_relative_time_description) : $this->get_relative_time_description((isset($review['publishTime'])) ? $review['publishTime'] : NULL, NULL, TRUE),
							'retrieved' => time(),
							'order' => NULL,
							'removable' => FALSE,
						];

						if ($language != NULL && $translate && isset($review['text']['languageCode']) && isset($review['originalText']['languageCode']) && $review['text']['languageCode'] != $review['originalText']['languageCode'])
						{
							if ($review['text']['languageCode'] == $language)
							{
								$this->reviews[$key]['text'] = (isset($review['text']['text']) && is_string($review['text']['text'])) ? $review['text']['text'] : NULL;
								$this->reviews[$key]['language'] = $language;
								$this->reviews[$key]['translated'] = TRUE;
							}
							elseif ($review['text']['languageCode'] == preg_replace('/^([a-z]{2}l?).*/i', '$1', $language))
							{
								$this->reviews[$key]['text'] = (isset($review['text']['text']) && is_string($review['text']['text'])) ? $review['text']['text'] : NULL;
								$this->reviews[$key]['language'] = $language;
								$this->reviews[$key]['translated'] = TRUE;
							}
						}

						if ($this->reviews[$key]['text'] != NULL && isset($review['originalText']['text']) && is_string($review['originalText']['text']) && $review['originalText']['text'] != $this->reviews[$key]['text'])
						{
							$this->reviews[$key]['original_text'] = $review['originalText']['text'];
						}

						if ($this->reviews[$key]['text'] == NULL)
						{
							$this->reviews[$key]['language'] = NULL;
						}

						$checked_keys[] = $key_temp;
						$relevance++;
						continue(3);
					}

					$this->reviews[$key] = [
						'id' => $max_id + $count,
						'author_id' => $author_id,
						'reference' => (isset($review['name']) && is_string($review['name'])) ? $review['name'] : NULL,
						'place_id' => $this->place_id,
						'checked' => NULL,
						'imported' => FALSE,
						'time_estimate' => FALSE,
						'status' => (isset($review['rating']) && is_numeric($review['rating']) && $review['rating'] >= 1 && $review['rating'] <= 5),
						'profile_photo_url' => (isset($review['authorAttribution']['photoUri']) && is_string($review['authorAttribution']['photoUri'])) ? $review['authorAttribution']['photoUri'] : NULL,
						'avatar' => NULL,
						'author_name' => (isset($review['authorAttribution']['displayName']) && is_string($review['authorAttribution']['displayName'])) ? $review['authorAttribution']['displayName'] : NULL,
						'author_url' => (isset($review['authorAttribution']['uri']) && is_string($review['authorAttribution']['uri'])) ? $review['authorAttribution']['uri'] : NULL,
						'language' => (isset($review['originalText']['languageCode']) && is_string($review['originalText']['languageCode'])) ? $review['originalText']['languageCode'] : NULL,
						'original_language' => (isset($review['originalText']['languageCode']) && is_string($review['originalText']['languageCode'])) ? $review['originalText']['languageCode'] : NULL,
						'rating' => (isset($review['rating']) && is_numeric($review['rating']) && $review['rating'] >= 1 && $review['rating'] <= 5) ? intval($review['rating']) : NULL,
						'text' => (isset($review['originalText']['text']) && is_string($review['originalText']['text'])) ? $review['originalText']['text'] : NULL,
						'original_text' => NULL,
						'review_url' => (isset($review['googleMapsUri']) && is_string($review['googleMapsUri'])) ? $review['googleMapsUri'] : NULL,
						'time' => (isset($review['publishTime'])) ? (strtotime($review['publishTime']) ?: NULL) : NULL,
						'visit_date' => (isset($review['visitDate']) && is_array($review['visitDate'])) ? ['year' => (isset($review['visitDate']['year']) && is_numeric($review['visitDate']['year'])) ? intval($review['visitDate']['year']) : NULL, 'month' => (isset($review['visitDate']['month']) && is_numeric($review['visitDate']['month'])) ? intval($review['visitDate']['month']) : NULL] : NULL,
						'translated' => (isset($review['text']['languageCode']) && isset($review['originalText']['languageCode']) && $review['text']['languageCode'] != $review['originalText']['languageCode']),
						'relative_time_description' => (isset($review['publishTime']) && isset($review['relativePublishTimeDescription'])) ? $this->get_relative_time_description($review['publishTime'], $review['relativePublishTimeDescription'], $use_relative_time_description) : $this->get_relative_time_description((isset($review['publishTime'])) ? $review['publishTime'] : NULL, NULL, TRUE),
						'retrieved' => time(),
						'order' => NULL,
						'removable' => FALSE,
					];

					if ($language != NULL && $translate && isset($review['text']['languageCode']) && isset($review['originalText']['languageCode']) && $review['text']['languageCode'] != $review['originalText']['languageCode'])
					{
						if ($review['text']['languageCode'] == $language)
						{
							$this->reviews[$key]['text'] = (isset($review['text']['text']) && is_string($review['text']['text'])) ? $review['text']['text'] : NULL;
							$this->reviews[$key]['language'] = $language;
							$this->reviews[$key]['translated'] = TRUE;
						}
						elseif ($review['text']['languageCode'] == preg_replace('/^([a-z]{2}l?).*/i', '$1', $language))
						{
							$this->reviews[$key]['text'] = (isset($review['text']['text']) && is_string($review['text']['text'])) ? $review['text']['text'] : NULL;
							$this->reviews[$key]['language'] = $language;
							$this->reviews[$key]['translated'] = TRUE;
						}
					}

					if ($this->reviews[$key]['text'] != NULL && isset($review['originalText']['text']) && is_string($review['originalText']['text']) && $review['originalText']['text'] != $this->reviews[$key]['text'])
					{
						$this->reviews[$key]['original_text'] = $review['originalText']['text'];
					}

					if (!$this->get_option('local_images', FALSE))
					{
						list($this->reviews[$key]['profile_photo_url']) = $this->set_avatar($review, $key);
					}
					else
					{
						list($this->reviews[$key]['profile_photo_url'], $this->reviews[$key]['avatar']) = $this->set_avatar($review, $key);
					}

					$checked_keys[] = $key;
					$relevance++;
					$count++;
					break;
				default:
					$author_id = (isset($review['author_url']) && is_string($review['author_url']) && preg_match('/(\d{20,120})/', $review['author_url'], $m)) ? $m[1] : NULL;
					$key = ($author_id != NULL && $this->place_id != NULL) ? $this->place_id . '_' . $author_id : $review['time'] . '_' . $review['rating'] . '_' . md5($review['author_name'] . '_' . mb_substr(strval($review['text']), 0, 100));
					$a = [];

					if (($force || $this->dashboard) && array_key_exists($key, $this->reviews))
					{
						$this->reviews[$key]['relative_time_description'] = $this->get_relative_time_description($review['time'], $review['relative_time_description'], $use_relative_time_description);
						$this->reviews[$key]['checked'] = time();
						$this->reviews[$key]['removable'] = FALSE;
						$checked_keys[] = $this->reviews[$key]['id'];
						$relevance++;
						continue(2);
					}
					
					foreach (array_keys($this->reviews) as $key_temp)
					{
						$author_url_id = (array_key_exists('author_url', $this->reviews[$key_temp]) && preg_match('/^.+[^\d](\d{20,120})[^\d].*$/', $this->reviews[$key_temp]['author_url'], $m)) ? $m[1] : NULL;
						$author_check = ($author_url_id != NULL && array_key_exists('author_url', $review) && preg_match('/^.+[^\d](\d{20,120})[^\d].*$/', $review['author_url'], $m)) ? ($author_url_id == $m[1]) : ($author_url_id == NULL);
			
						if ($this->reviews[$key_temp]['author_name'] != $review['author_name'] || !$author_check)
						{
							continue;
						}
		
						$review = array_merge($this->reviews[$key_temp], $review);
						unset($this->reviews[$key_temp]);
		
						$review['retrieved'] = time();
						$review['time_estimate'] = FALSE;
						$review['removable'] = FALSE;
						$this->reviews[$key] = $review;
						$checked_keys[] = $key_temp;
						$relevance++;
						continue(3);
					}

					$a['id'] = $max_id + $count;
					$a['author_id'] = $author_id;
					$a['place_id'] = ($this->demo) ? NULL : $this->place_id;
					$a['order'] = NULL;
					$a['checked'] = NULL;
					$a['retrieved'] = time();
					$a['imported'] = FALSE;
					$a['time_estimate'] = FALSE;
					$a['removable'] = FALSE;
					$a['status'] = TRUE;
		
					if (!$this->get_option('local_images', FALSE))
					{
						list($a['profile_photo_url']) = $this->set_avatar($review, $key);
					}
					else
					{
						list($a['profile_photo_url'], $a['avatar']) = $this->set_avatar($review, $key);
					}
		
					$this->reviews[$key] = $this->sanitize_array($a + $review);
					$checked_keys[] = $a['id'];
					$relevance++;
					$count++;
					break;
				}
			}
		}
		
		if ($force || $this->dashboard && !$use_relative_time_description)
		{
			/* Google returns a varying subset, so keep existing order */

			foreach (array_keys($this->reviews) as $key)
			{
				if (!in_array($key, $checked_keys, TRUE))
				{
					$this->reviews[$key]['removable'] = TRUE;

				}
			}

			if (!$use_relative_time_description && $this->set_relative_time_descriptions())
			{
				$relative_time_description_update = TRUE;
			}
		}
 		
		$this->set_relevance_insert();

		uksort($this->reviews, function ($a, $b)
			{
				/* Work-around for instable array bug in PHP 8.3.2 */
				if (!array_key_exists($a, $this->reviews) || !array_key_exists($b, $this->reviews))
				{
					return 0;
				}

				return $this->reviews[$a]['order'] - $this->reviews[$b]['order'];
			}
		);
		
		wp_cache_add((($this->demo) ? 'reviews_demo' : 'reviews'), $this->reviews, self::OPTION_PREFIX, HOUR_IN_SECONDS);
		
		$this->reviews_filtered = $this->reviews;

		if ($this->demo || (!$relative_time_description_update && $relevance == 1))
		{
			return TRUE;
		}

		if ($force || $this->dashboard)
		{
			$this->update_option('reviews', $this->reviews, 'no');
		}
		
		return TRUE;
	}
	
	/* Sets an avatar (profile_photo_url) for an individual reviewer based on their review data */

	public function set_avatar(array $data, $key = NULL): array
	{
		$google_image_regex = '#^https?://(?:\w+\.)?(?:gstatic|google(?:usercontent)?)?\.\w+/.+$#i';
		$ret = [0 => NULL, 1 => NULL];

		if (!is_array($data) || is_numeric($this->api_version) && (!isset($data['authorAttribution']['photoUri']) || !is_string($data['authorAttribution']['photoUri']) || !preg_match($google_image_regex, $data['authorAttribution']['photoUri'])) || !is_numeric($this->api_version) && (!isset($data['profile_photo_url']) || !is_string($data['profile_photo_url']) || !preg_match($google_image_regex, $data['profile_photo_url'])))
		{
			return $ret;
		}

		$ret[0] = $photo_url = (is_numeric($this->api_version)) ? $data['authorAttribution']['photoUri'] : $data['profile_photo_url'];

		if (!$this->get_option('local_images', FALSE))
		{
			return $ret;
		}

		$time = (is_numeric($this->api_version) && isset($data['publishTime']) && is_string($data['publishTime'])) ? strtotime($data['publishTime']) : (isset($data['time']) ? $data['time'] : NULL);
		$rating = (isset($data['rating']) && is_numeric($data['rating'])) ? intval($data['rating']) : NULL;
		$text = (is_numeric($this->api_version) && isset($data['originalText']['text']) && is_string($data['originalText']['text'])) ? $data['originalText']['text'] : (isset($data['text']) ? $data['text'] : NULL);
		$author_name = (is_numeric($this->api_version) && isset($data['authorAttribution']['displayName']) && is_string($data['authorAttribution']['displayName'])) ? $data['authorAttribution']['displayName'] : (isset($data['author_name']) ? $data['author_name'] : NULL);

		if ($key == NULL && $time != NULL && $rating != NULL && $author_name != NULL)
		{
			if (!is_numeric($time) && is_string($time))
			{
				if (!preg_match('/^(\d+)[^\d]+(\d+)[^\d]+(\d+)(?:[^\d].*)?$/', $time, $t))
				{
					return $ret;
				}
				
				$time = mktime(0, 0, 0, $t[2], $t[3], $t[1]);
			}

			$key = $time . '_' . $rating . '_' . md5(strval($author_name) . '_' . mb_substr(strval($text), 0, 100));
		}

		if ($key == NULL || !function_exists('wp_remote_get') || !function_exists('wp_remote_retrieve_body') || !function_exists('wp_get_upload_dir') || !function_exists('wp_upload_bits'))
		{
			return $ret;
		}

		$upload_directory = wp_get_upload_dir();
		$upload_directory_plugin = NULL;

		if (isset($upload_directory['basedir']) && is_string($upload_directory['basedir']))
		{
			$upload_directory_plugin = $upload_directory['basedir'] . '/gmbrr';
		}
		elseif (isset($upload_directory['path']) && is_string($upload_directory['path']))
		{
			$upload_directory_plugin = preg_replace('#^(.+?)(?:/\d+/\d+)/?$#', '$1', $upload_directory['path']) . '/gmbrr';
		}

		if ($upload_directory_plugin === NULL || !is_dir($upload_directory_plugin))
		{
			if (!wp_mkdir_p($upload_directory_plugin))
			{
				return $ret;
			}

			if (!is_writable($upload_directory_plugin . '/'))
			{
				return $ret;
			}
		}
		
		$image_alias = preg_replace('/[^0-9a-z-]+/i', '-', $key);

		if (is_file($upload_directory_plugin . '/' . $image_alias . '.png') || is_file($upload_directory_plugin . '/' . $image_alias . '.svg'))
		{
			$image_name = (is_file($upload_directory_plugin . '/' . $image_alias . '.png')) ? $image_alias . '.png' : $image_alias . '.svg';
			$ret[1] = $image_name;
			
			return $ret;
		}
		
		if (version_compare(PHP_VERSION, '8.1') >= 0)
		{
			$fp = @wp_remote_get($photo_url);
		}
		else
		{
			$fp = wp_remote_get($photo_url);
		}

		$image_type = wp_remote_retrieve_header($fp, 'content-type');

		if (!is_string($image_type) || $image_type == NULL)
		{
			return $ret;
		}

		if (!function_exists('wp_get_current_user'))
		{
			include(ABSPATH . 'wp-includes/pluggable.php'); 
		}

		if (!function_exists('wp_get_current_user'))
		{
			return $ret;
		}

		$image_name = (preg_match('/xml|svg/i', $image_type)) ? $image_alias . '.svg' : $image_alias . '.png';
		$image_temporary = wp_upload_bits($image_name, NULL, wp_remote_retrieve_body($fp));
		
		if (isset($image_temporary['error']) && is_string($image_temporary['error']) && $image_temporary['error'] != NULL || !isset($image_temporary['file']) || isset($image_temporary['file']) && $image_temporary['file'] == NULL || !is_file($image_temporary['file']))
		{
			return $ret;
		}
		
		if (!rename($image_temporary['file'], $upload_directory_plugin . '/' . $image_name))
		{
			@unlink($image_temporary['file']);
			return $ret;
		}
		
		$ret[1] = $image_name;

		return $ret;
	}

	/* Retrieve an accurate IP Address for the web server */

	public function server_ip(): ?string
	{
		if (is_string(wp_cache_get('server_ip', self::OPTION_PREFIX)))
		{
			return trim(wp_cache_get('server_ip', self::OPTION_PREFIX));
		}

		$ip_regex = '/(?:^(?:25[0-5]|2[0-4]\d|1\d\d|[1-9]\d|\d)(?:\.(?:25[0-5]|2[0-4]\d|1\d\d|[1-9]\d|\d)){3}$)|(?:^(?:(?:[a-fA-F\d]{1,4}:){7}(?:[a-fA-F\d]{1,4}|:)|(?:[a-fA-F\d]{1,4}:){6}(?:(?:25[0-5]|2[0-4]\d|1\d\d|[1-9]\d|\d)(?:\\.(?:25[0-5]|2[0-4]\d|1\d\d|[1-9]\d|\d)){3}|:[a-fA-F\d]{1,4}|:)|(?:[a-fA-F\d]{1,4}:){5}(?::(?:25[0-5]|2[0-4]\d|1\d\d|[1-9]\d|\d)(?:\\.(?:25[0-5]|2[0-4]\d|1\d\d|[1-9]\d|\d)){3}|(?::[a-fA-F\d]{1,4}){1,2}|:)|(?:[a-fA-F\d]{1,4}:){4}(?:(?::[a-fA-F\d]{1,4}){0,1}:(?:25[0-5]|2[0-4]\d|1\d\d|[1-9]\d|\d)(?:\\.(?:25[0-5]|2[0-4]\d|1\d\d|[1-9]\d|\d)){3}|(?::[a-fA-F\d]{1,4}){1,3}|:)|(?:[a-fA-F\d]{1,4}:){3}(?:(?::[a-fA-F\d]{1,4}){0,2}:(?:25[0-5]|2[0-4]\d|1\d\d|[1-9]\d|\d)(?:\\.(?:25[0-5]|2[0-4]\d|1\d\d|[1-9]\d|\d)){3}|(?::[a-fA-F\d]{1,4}){1,4}|:)|(?:[a-fA-F\d]{1,4}:){2}(?:(?::[a-fA-F\d]{1,4}){0,3}:(?:25[0-5]|2[0-4]\d|1\d\d|[1-9]\d|\d)(?:\\.(?:25[0-5]|2[0-4]\d|1\d\d|[1-9]\d|\d)){3}|(?::[a-fA-F\d]{1,4}){1,5}|:)|(?:[a-fA-F\d]{1,4}:){1}(?:(?::[a-fA-F\d]{1,4}){0,4}:(?:25[0-5]|2[0-4]\d|1\d\d|[1-9]\d|\d)(?:\\.(?:25[0-5]|2[0-4]\d|1\d\d|[1-9]\d|\d)){3}|(?::[a-fA-F\d]{1,4}){1,6}|:)|(?::(?:(?::[a-fA-F\d]{1,4}){0,5}:(?:25[0-5]|2[0-4]\d|1\d\d|[1-9]\d|\d)(?:\\.(?:25[0-5]|2[0-4]\d|1\d\d|[1-9]\d|\d)){3}|(?::[a-fA-F\d]{1,4}){1,7}|:)))(?:%[0-9a-zA-Z]{1,})?$)/mi';
		
		if (function_exists('wp_remote_get') && function_exists('wp_remote_retrieve_body'))
		{
			if (version_compare(PHP_VERSION, '8.1') >= 0)
			{
				$response = @wp_remote_get('http://ip6.me/api/');
			}
			else
			{
				$response = wp_remote_get('http://ip6.me/api/');
			}
			
			if (is_array($response) && !is_wp_error($response))
			{
				$string = wp_remote_retrieve_body($response);
				$a = (is_string($string)) ? preg_split('/,/i', $string, 2) : ['', ''];
				
				if (preg_match($ip_regex, $a[1]))
				{
					$string = trim(mb_strtolower($a[1]));
					wp_cache_set('server_ip', $string, self::OPTION_PREFIX, HOUR_IN_SECONDS);
					return $string;
				}
			}

			if (version_compare(PHP_VERSION, '8.1') >= 0)
			{
				$response = @wp_remote_get('http://checkip.dyndns.com/');
			}
			else
			{
				$response = wp_remote_get('http://checkip.dyndns.com/');
			}
			
			if (is_array($response) && !is_wp_error($response))
			{
				$string = wp_remote_retrieve_body($response);
				$string = (is_string($string)) ? preg_replace('/^.+ip\s+address[:\s]+\[?([^<>\s\b\]]+)\]?.*$/i', '$1', $string) : '';
			
				if (preg_match($ip_regex, $string))
				{
					$string = trim(mb_strtolower($string));
					wp_cache_set('server_ip', $string, self::OPTION_PREFIX, HOUR_IN_SECONDS);
					return $string;
				}
			}
		}

		if (function_exists('gethostname') && function_exists('gethostbyname'))
		{
			$string = gethostbyname(gethostname());

			if (is_string($string) && preg_match($ip_regex, $string))
			{
				$string = trim(mb_strtolower($string));
				wp_cache_set('server_ip', $string, self::OPTION_PREFIX, HOUR_IN_SECONDS);
				return $string;
			}
		}
		
		$server_address = (isset($_SERVER['SERVER_ADDR']) && is_string($_SERVER['SERVER_ADDR'])) ? sanitize_text_field(wp_unslash($_SERVER['SERVER_ADDR'])) : NULL;

		if ($server_address != NULL && preg_match($ip_regex, $server_address))
		{
			wp_cache_set('server_ip', $server_address, self::OPTION_PREFIX, HOUR_IN_SECONDS);
			return $server_address;
		}
		
		return NULL;
	}
	
	/* Find all references to existing Google Reviews, API Key and Place ID */

	public function data_hunter(string $format = 'array', bool $force = FALSE)
	{
		if (!$this->dashboard || !current_user_can('manage_options', self::OPTION_PREFIX))
		{
			return TRUE;
		}
		
		$return = (!$force && $this->get_option('place_id') == NULL);
		
		switch ($format)
		{
		case 'boolean':
			return $return;
		case 'json':
			if (!$return)
			{
				return NULL;
			}
		default:
			break;
		}
		
		global $wpdb;
		
		$ret = [];
		$language = preg_replace('/_/', '-', get_option('WPLANG', ''));
		
		if (get_option('we_are_open_api_key', '') != NULL && get_option('we_are_open_place_id', '') != NULL)
		{
			$ret['api_key'] = get_option('we_are_open_api_key', '');
			$ret['place_id'] = get_option('we_are_open_place_id', '');
			$ret['api_version'] = get_option('we_are_open_google_places_api', '');
		}

		if (empty($ret) && get_option('grw_google_api_key', '') != NULL && $wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $wpdb->prefix . 'grp_google_place')) == $wpdb->prefix . 'grp_google_place')
		{
			$id = intval($wpdb->get_var("SELECT `id` FROM `" . $wpdb->prefix . "grp_google_place` ORDER BY `id` DESC LIMIT 1"));
			$place_id = $wpdb->get_var($wpdb->prepare("SELECT `place_id` FROM `" . $wpdb->prefix . "grp_google_place` WHERE `id` = %d LIMIT 1", $id));
			$reviews = $wpdb->get_results($wpdb->prepare("SELECT * FROM `" . $wpdb->prefix . "grp_google_review` WHERE `google_place_id` = %d", $id));
			$ret['api_key'] = get_option('grw_google_api_key', '');
			$ret['place_id'] = $place_id;
			$ret['reviews'] = $reviews;
		}
		
		if (empty($ret) && is_array(get_option('wpfbr_google_options', '')))
		{
			$d = get_option('wpfbr_google_options', '');
			if ($d['select_google_api'] != 'default' && is_string($d['google_api_key']))
			{
				$reviews = [];
				
				if ($wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $wpdb->prefix . 'wpfb_reviews')) == $wpdb->prefix . 'wpfb_reviews')
				{
					$reviews = $wpdb->get_results("SELECT * FROM `" . $wpdb->prefix . "wpfb_reviews`");
				}
				
				$ret['api_key'] = $d['google_api_key'];
				$ret['place_id'] = (isset($d['google_location_set']['place_id'])) ? $d['google_location_set']['place_id'] : NULL;
				$ret['language'] = (isset($d['google_language_option'])) ? $d['google_language_option'] : NULL;
				$ret['reviews'] = $reviews;
			}
		}
		
		if (empty($ret) && is_array(get_option('googleplacesreviews_options', '')))
		{
			$d = get_option('googleplacesreviews_options', '');
			$w = ['place_id' => NULL];
			
			if (array_key_exists('google_places_api_key', $d))
			{
				$w = get_option('googleplacesreviews_options', '');
				
				if (is_array($w) && array_key_exists('place_id', $w) && is_string($w['place_id']) && mb_strlen($w['place_id'] >= 17))
				{
					$place_id = $w['place_id'];
				}
				
				$ret['api_key'] = $d['google_places_api_key'];
				$ret['place_id'] = $place_id;
			}
		}
		
		if (empty($ret) && get_option('google_places_api_key', '') != NULL)
		{
			$ret['api_key'] = get_option('google_places_api_key', '');
		}
		
		if (empty($ret) && is_array(get_option('trustindex-google-page-details', '')))
		{
			$d = get_option('trustindex-google-page-details', '');
			
			if (array_key_exists('id', $d) && is_string($d['id']) && mb_strlen($d['id'] >= 17))
			{
				$ret['place_id'] = $d['id'];
			}
			
			if (get_option('trustindex-google-lang', '') != NULL)
			{
				$ret['language'] = get_option('trustindex-google-lang', '');
			}
		}
		
		if ((empty($ret) || (!isset($ret['language']) || isset($ret['language']) && $ret['language'] == NULL)) && is_string($language) && mb_strlen($language) >= 2)
		{
			if (empty($this->languages) || !empty($this->languages) && array_key_exists($language, $this->languages))
			{
				$ret['language'] = $language;
			}
			elseif (!empty($this->languages) && array_key_exists(mb_substr($language, 0, 2), $this->languages))
			{
				$ret['language'] = mb_substr($language, 0, 2);
			}
		}

		switch ($format)
		{
		case 'boolean':
			$ret = (!empty($ret));
			break;
		case 'json':
			if (isset($ret['reviews']))
			{
				$ret['review_count'] = (is_array($ret['reviews'])) ? count($ret['reviews']) : 0;
			}
			
			$ret = json_encode($ret);
			break;
		default:
			break;
		}
		
		return $ret;
	}
	
	/* Count the number of reviews stored */

	public function reviews_count($place_id = NULL, $status = NULL, bool $set = TRUE): int
	{
		if ($set)
		{
			$this->set_reviews();
		}
		
		if (!is_string($place_id) && is_bool($place_id) && $place_id)
		{
			$place_id = $this->place_id;
		}
		
		$count = 0;
		
		if (!is_array($this->reviews))
		{
			return $count;
		}

		if ($place_id == NULL && !is_bool($status))
		{
			return count($this->reviews);
		}
		
		foreach ($this->reviews as $a)
		{
			if (is_bool($status))
			{
				if (is_string($place_id))
				{
					if ($a['place_id'] == $place_id && $a['status'] == $status)
					{
						$count++;
					}

					continue;
				}
				
				if ($a['status'] == $status)
				{
					$count++;
				}
				
				continue;
			}
			
			if ($a['place_id'] == $place_id)
			{
				$count++;
			}
		}
		
		return $count;
	}
	
	/* Filter review data */

	protected function reviews_filter(?array $filters = NULL, ?array $atts = NULL): bool
	{
		if (!$this->set_reviews() || empty($this->reviews))
		{
			return FALSE;
		}
		
		if (!is_array($filters))
		{
			$filters = [];
		}
		
		if (!is_array($atts))
		{
			$atts = [];
		}
		
		$count = 0;
		$ids = (array_key_exists('id', $filters) && is_numeric($filters['id']) && $filters['id'] > 0) ? [intval($filters['id'])] : ((array_key_exists('id', $filters) && is_string($filters['id']) && preg_match('/^(?:\d+)(?:,\s*(?:\d+))+$/', $filters['id'])) ? array_unique(preg_split('/[^\d]+/', $filters['id'])) : []);
		$id = (!empty($ids)) ? $ids[0] : NULL;
		$place_id = (!$this->demo && array_key_exists('place_id', $filters)) ? $filters['place_id'] : NULL;
		$place_id = (is_array($place_id)) ? $place_id : ((is_string($place_id) && mb_strlen($place_id) >= 20) ? [$place_id] : NULL);
		$language = (array_key_exists('language', $filters) && is_string($filters['language']) && mb_strlen($filters['language']) >= 2 && mb_strlen($filters['language']) <= 16) ? preg_replace('/^([a-z]{2,3}).*$/i', '$1', mb_strtolower($filters['language'])) : NULL;
		$min = ($id == NULL && array_key_exists('min', $filters) && is_numeric($filters['min']) && $filters['min'] >= 1 && $filters['min'] <= 5) ? intval($filters['min']) : NULL;
		$max = ($id == NULL && array_key_exists('max', $filters) && is_numeric($filters['max']) && $filters['max'] >= 1 && $filters['max'] <= 5) ? intval($filters['max']) : NULL;
		$offset = ($id == NULL && array_key_exists('offset', $filters) && is_numeric($filters['offset']) && $filters['offset'] >= 0) ? intval($filters['offset']) : 0;
		$limit = ($id == NULL && array_key_exists('limit', $filters) && is_numeric($filters['limit']) && $filters['limit'] >= 0) ? intval($filters['limit']) : NULL;
		$excerpt = (array_key_exists('excerpt', $filters) && is_numeric($filters['excerpt']) && $filters['excerpt'] >= 20) ? intval($filters['excerpt']) : NULL;
		$review_text_min = (array_key_exists('review_text_min', $filters) && is_numeric($filters['review_text_min']) && $filters['review_text_min'] >= 0) ? intval($filters['review_text_min']) : NULL;
		$review_text_max = (array_key_exists('review_text_max', $filters) && is_numeric($filters['review_text_max']) && $filters['review_text_max'] >= 0 && (!is_numeric($filters['review_text_min']) || is_numeric($filters['review_text_min']) && $filters['review_text_min'] <= $filters['review_text_max'])) ? intval($filters['review_text_max']) : NULL;
		$review_text_inc = (array_key_exists('review_text_inc', $filters) && is_string($filters['review_text_inc']) && mb_strlen($filters['review_text_inc']) > 1) ? array_unique(preg_split('/,\s*/', $filters['review_text_inc'], 10)) : [];
		$review_text_exc = (array_key_exists('review_text_exc', $filters) && is_string($filters['review_text_exc']) && mb_strlen($filters['review_text_exc']) > 1) ? array_unique(preg_split('/,\s*/', $filters['review_text_exc'], 10)) : [];

		$limit = (is_numeric($limit)) ? intval($limit) : ((!array_key_exists('limit', $atts)) ? $this->get_option('review_limit', NULL) : NULL);
		$sort = ($id == NULL && array_key_exists('sort', $filters) && ($filters['sort'] != NULL && is_string($filters['sort']))) ? preg_replace('/[^\w_-]/', '', $filters['sort']) : $this->get_option('review_sort', NULL);
		$sort_static = (array_key_exists($sort, $this->review_sort_options) && $this->review_sort_options[$sort]['static']);
		$min = (is_numeric($min)) ? intval($min) : $this->get_option('rating_min', NULL);
		$max = (is_numeric($max)) ? intval($max) : $this->get_option('rating_max', NULL);
		$review_text_min = (is_numeric($review_text_min) && $review_text_min >= 0) ? intval($review_text_min) : $this->get_option('review_text_min', NULL);
		$review_text_max = (is_numeric($review_text_max) && $review_text_max >= 0) ? intval($review_text_max) : $this->get_option('review_text_max', NULL);
		
		if (is_numeric($limit) && $limit == 0)
		{
			return TRUE;
		}
		
		switch($sort)
		{
		case 'relevance':
		case 'relevance_desc':
			$sort = NULL;
			break;
		case 'date':
		case 'rating':
			$sort .= '_desc';
			break;
		case 'id':
		case 'author_name':
			$sort .= '_asc';
			break;
		case 'time':
		case 'time_desc':
		case 'relative_time_description':
		case 'relative_time_description_desc':
			$sort = 'date_desc';
			break;
		case 'time_asc':
		case 'relative_time_description_asc':
			$sort = 'date_asc';
			break;
		case 'name':
		case 'author':
		case 'name_asc':
		case 'author_asc':
			$sort = 'author_name_asc';
			break;
		case 'name_desc':
		case 'author_desc':
			$sort = 'author_name_desc';
			break;
		case 'review_length':
		case 'review_words':
		case 'review_word_count':
		case 'review_length_asc':
		case 'review_word_count_asc':
			$sort = 'review_words_asc';
			break;
		case 'review_length_desc':
		case 'review_word_count_desc':
			$sort = 'review_words_desc';
			break;
		case 'review_characters':
		case 'review_character_count':
		case 'review_character_count_asc':
			$sort = 'review_characters_asc';
			break;
		case 'review_character_count_desc':
			$sort = 'review_characters_desc';
			break;
		case 'random':
		case 'random_variable':
		case 'shuffle':
		case 'shuffle_variable':
		case 'random-shuffle':
		case 'random_shuffle':
		case 'random-shuffle-variable':
		case 'random_shuffle_variable':
			$sort = 'shuffle';
			break;
		}
		
		if (array_key_exists($sort, $this->review_sort_options))
		{
			$this->review_sort_option = $sort;
		}

		if (!empty($ids))
		{
			$this->reviews_filtered = [];

			if (is_string($this->review_sort_option) && $sort == 'shuffle')
			{
				$keys = NULL;
				
				if ($sort_static)
				{
					$ids_check = $ids;
					$keys = ($sort_static) ? get_transient(self::OPTION_PREFIX . 'reviews_shuffled') : wp_cache_get('reviews_shuffled', self::OPTION_PREFIX);
					
					if (is_array($keys) && !empty($keys))
					{
						foreach ($keys as $k)
						{
							if (!array_key_exists($k, $this->reviews) || array_key_exists($k, $this->reviews) && !in_array($this->reviews[$k]['id'], $ids_check))
							{
								continue;
							}
							
							$ids[] = $this->reviews[$k]['id'];
						}
					}
					unset($ids_check);
				}				
				
				if (!is_array($keys))
				{
					shuffle($ids);
					
					if ($sort_static)
					{
						set_transient(self::OPTION_PREFIX . 'reviews_shuffled', $keys, HOUR_IN_SECONDS);
					}
					else
					{
						wp_cache_set('reviews_shuffled', $keys, self::OPTION_PREFIX, HOUR_IN_SECONDS);
					}
				}
			}

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
			
			if (is_numeric($offset) && is_numeric($limit) && $limit < $count)
			{
				$this->reviews_filtered = array_splice($this->reviews_filtered, $offset, $limit);
			}
			
			return TRUE;
		}

		foreach ($this->reviews as $key => $a)
		{
			if (!array_key_exists($key, $this->reviews_filtered))
			{
				continue;
			}

			if (!$this->dashboard && !$a['status'])
			{
				unset($this->reviews_filtered[$key]);
				continue;
			}
			
			if (is_numeric($min) && $min > 1 && $a['rating'] < $min || is_numeric($max) && $max < 5 && $a['rating'] > $max)
			{
				unset($this->reviews_filtered[$key]);
				continue;
			}
			
			if (is_array($place_id) && !in_array($a['place_id'], $place_id, TRUE))
			{
				unset($this->reviews_filtered[$key]);
				continue;
			}
			
			if ($language != NULL && isset($a['text']) && is_string($a['text']) && mb_strlen($a['text']) > 0)
			{
				$text_language = (isset($a['language']) && is_string($a['language'])) ? preg_replace('/^([a-z]{2,3}).*$/i', '$1', mb_strtolower($a['language'])) : NULL;
				$original_language = (isset($a['original_text']) && is_string($a['original_text']) && mb_strlen($a['original_text']) > 0 && isset($a['original_language']) && is_string($a['original_language'])) ? preg_replace('/^([a-z]{2,3}).*$/i', '$1', mb_strtolower($a['original_language'])) : NULL;

				if ($text_language != $language && $original_language != $language)
				{
					unset($this->reviews_filtered[$key]);
					continue;
				}

				if ($text_language != $language)
				{
					$a['text'] = $a['original_text'];
					$a['language'] = $a['original_language'];
					$a['translated'] = FALSE;
					$this->reviews_filtered[$key]['text'] = $a['text'];
					$this->reviews_filtered[$key]['language'] = $a['language'];
					$this->reviews_filtered[$key]['translated'] = FALSE;
				}
			}
									
			if (is_numeric($review_text_min) && (!is_string($a['text']) || is_string($a['text']) && $review_text_min > mb_strlen(wp_strip_all_tags($a['text'])) || is_string($a['text']) && is_numeric($review_text_max) && $review_text_max < mb_strlen(wp_strip_all_tags($a['text']))))
			{
				unset($this->reviews_filtered[$key]);
				continue;
			}
									
			if (!empty($review_text_inc) || !empty($review_text_exc))
			{
				$t = wp_strip_all_tags($a['text']);
				$inc = $exc = FALSE;
					
				if (!empty($review_text_inc))
				{
					foreach ($review_text_inc as $v)
					{
						if (preg_match('/\b' . preg_quote($v, '/'). '\b/i', $t))
						{
							$inc = TRUE;
							break;
						}
					}
					
					if (!$inc)
					{
						unset($this->reviews_filtered[$key]);
						continue;
					}
				}

				if (!empty($review_text_exc))
				{
					foreach ($review_text_exc as $v)
					{
						if (preg_match('/\b' . preg_quote($v, '/'). '\b/i', $t))
						{
							$exc = TRUE;
							break;
						}
					}
					
					if ($exc)
					{
						unset($this->reviews_filtered[$key]);
						continue;
					}
				}
			}
			
			$count++;
		}
		
		if ($this->review_sort_option != NULL)
		{
			if ($this->review_sort_option == 'shuffle')
			{
				$keys = ($sort_static) ? get_transient(self::OPTION_PREFIX . 'reviews_shuffled') : wp_cache_get('reviews_shuffled', self::OPTION_PREFIX);
				
				if (is_array($keys) && !empty($keys))
				{
					foreach ($keys as $k)
					{ 
						$this->reviews_filtered[$k] = $this->reviews[$k];
					}
				}
				else
				{
					$keys = array_keys($this->reviews_filtered);
					$list = $this->reviews_filtered;
					$this->reviews_filtered = [];
					shuffle($keys);
					
					foreach ($keys as $k)
					{ 
						$this->reviews_filtered[$k] = $list[$k];
					}
					
					unset($list);
					
					if ($sort_static)
					{
						set_transient(self::OPTION_PREFIX . 'reviews_shuffled', $keys, HOUR_IN_SECONDS);
					}
					else
					{
						wp_cache_set('reviews_shuffled', $keys, self::OPTION_PREFIX, HOUR_IN_SECONDS);
					}
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
						if ($this->review_sort_option == 'review_characters_asc' || $this->review_sort_option == 'review_characters_desc')
						{
							return mb_strlen(strval($this->reviews_filtered[$a][$this->review_sort_options[$this->review_sort_option]['field']])) - mb_strlen(strval($this->reviews_filtered[$b][$this->review_sort_options[$this->review_sort_option]['field']]));
						}
						
						if ($this->review_sort_option == 'review_words_asc' || $this->review_sort_option == 'review_words_desc')
						{
							preg_match_all('/[\pL\pN\pPd]+/u', strval($this->reviews_filtered[$a][$this->review_sort_options[$this->review_sort_option]['field']]), $c);
							preg_match_all('/[\pL\pN\pPd]+/u', strval($this->reviews_filtered[$b][$this->review_sort_options[$this->review_sort_option]['field']]), $d);
							return (((isset($c[0]) && is_array($c[0])) ? count($c[0]) : 0) + mb_strlen(strval($this->reviews_filtered[$a][$this->review_sort_options[$this->review_sort_option]['field']])) / 100) - (((isset($d[0]) && is_array($d[0])) ? count($d[0]) : 0) + mb_strlen(strval($this->reviews_filtered[$b][$this->review_sort_options[$this->review_sort_option]['field']])) / 100);
						}
						
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
						
						if (mb_strtolower($v) == mb_strtolower($w))
						{
							return 0;
						}
						
						$c = $d = [mb_strtolower($v), mb_strtolower($w)];
						arsort($c, SORT_REGULAR);
						return (array_keys($c) == array_keys($d)) ? 1 : -1;
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
	
	/* Sanitize array data to remove characters that can cause update_option() to fail */

	protected function sanitize_array($array): array
	{
		if (!is_array($array))
		{
			return [];
		}

		if (!$this->get_option('additional_array_sanitization', FALSE))
		{
			return $array;
		}

		array_walk_recursive(
			$array,
			function (&$v)
			{
				if (is_string($v) && preg_match('/[^ -\x{2122}]\s+|\s*[^ -\x{2122}]/u', $v))
				{
					$v = preg_replace('/[^ -\x{2122}]\s+|\s*[^ -\x{2122}]/u', '', $v);
				}
			}
		);
		
		return $array;
	}
	
	/* Sanitize data from API Key setting input */

	public function sanitize_api_key($api_key): ?string
	{
		if (!is_string($api_key) || mb_strlen($api_key) < 10)
		{
			$api_key = NULL;
		}
		
		if ($this->get_option('api_key') != $api_key)
		{
			delete_transient(self::OPTION_PREFIX . 'reviews_shuffled');
			wp_cache_delete('structured_data', self::OPTION_PREFIX);
			wp_cache_delete('result', self::OPTION_PREFIX);
			wp_cache_delete('reviews_shuffled', self::OPTION_PREFIX);
			wp_cache_delete('reviews', self::OPTION_PREFIX);
			$this->api_key = sanitize_text_field($api_key);
			
			if ($api_key != NULL)
			{
				set_transient(self::OPTION_PREFIX . 'force', time() . '/0', 30);
			}

			self::log('api_key', $this->api_key);
		}
		
		return ($api_key != NULL) ? sanitize_text_field($api_key) : NULL;
	}

	/* Sanitize data from Place ID setting input */

	public function sanitize_place_id($place_id): ?string
	{
		if (mb_strlen($place_id) < 10)
		{
			$place_id = NULL;
		}

		if (empty($this->places))
		{
			$this->places = $this->get_array_option('places');
		}
		
		if (is_array($this->places))
		{
			foreach ($this->places as $a)
			{
				if (!isset($a['place_id']) || isset($a['name']) && $a['name'] != NULL || $a['place_id'] == $this->place_id || $a['place_id'] == $place_id)
				{
					continue;
				}
	
				$this->delete_place($a['place_id']);
			}
		}
		
		if ($this->get_option('place_id') != $place_id)
		{
			$api_key = $this->get_option('api_key');
			delete_transient(self::OPTION_PREFIX . 'reviews_shuffled');
			wp_cache_delete('structured_data', self::OPTION_PREFIX);
			wp_cache_delete('result', self::OPTION_PREFIX);
			wp_cache_delete('result_valid', self::OPTION_PREFIX);
			wp_cache_delete('reviews_shuffled', self::OPTION_PREFIX);
			wp_cache_delete('reviews', self::OPTION_PREFIX);
			$this->update_option('result', NULL, 'no');
			$this->update_option('structured_data', FALSE, 'yes');
			$this->place_id = sanitize_text_field($place_id);
			$this->data = [];
			$this->result = [];
			
			if ($place_id == NULL && $api_key == NULL)
			{
				$this->reviews = [];
				$this->reviews_filtered = [];
			}
			elseif ($place_id != NULL && $api_key != NULL)
			{
				set_transient(self::OPTION_PREFIX . 'force', time() . '/0', 30);
			}

			self::log('place_id', $this->place_id);
		}
		
		return $place_id;
	}

	/* Sanitize data from retrieval sort */

	public function sanitize_retrieval_sort($retrieval_sort): ?string
	{
		if (!is_string($retrieval_sort) || is_string($retrieval_sort) && !preg_match('/^(?:most_relevant|newest|review_sort)$/', $retrieval_sort))
		{
			return NULL;
		}

		return $retrieval_sort;
	}
	
	/* Handle switch between active and demo versions */

	public function sanitize_demo($demo): bool
	{
		$demo = boolval($demo);
		$this->demo = $demo;

		if ($this->get_option('demo') != $demo)
		{
			wp_cache_delete('structured_data', self::OPTION_PREFIX);
			wp_cache_delete('result', self::OPTION_PREFIX);
			wp_cache_delete('result_demo', self::OPTION_PREFIX);
			$this->update_option('result', NULL, 'no');
			$this->data = [];
			$this->result = [];
			$this->reviews = [];
			$this->reviews_filtered = [];
		}
		
		return $demo;
	}

	/* Sanitizes and normalizes input data */

	public function sanitize_input($data)
	{
		$stripslashes = (function_exists('wp_magic_quotes')); /* Unfortunately, no flag exists */
		
		if (!is_array($data))
		{
			if (is_null($data))
			{
				return NULL;
			}
			
			if (is_bool($data))
			{
				return boolval($data);
			}

			if (is_string($data) || is_numeric($data))
			{
				return ($stripslashes && is_string($data)) ? stripslashes(wp_kses_stripslashes(sanitize_text_field($data), [])) : wp_kses(sanitize_text_field($data), []);
			}

			return FALSE;
		}
		
		foreach (array_keys($data) as $k)
		{
			if (sanitize_key($k) != $k)
			{
				unset($data[$k]);
				continue;
			}

			if (is_array($data[$k]))
			{
				$data[$k] = $this->sanitize_input($data[$k]);
				continue;
			}

			if (is_null($data[$k]))
			{
				$data[$k] = NULL;
				continue;
			}
			
			if (is_bool($data[$k]))
			{
				$data[$k] = boolval($data[$k]);
				continue;
			}

			if (!is_string($data[$k]) && !is_numeric($data[$k]))
			{
				$data[$k] = FALSE;
				continue;
			}

			$data[$k] = ($stripslashes && is_string($data[$k])) ? stripslashes(wp_kses_stripslashes(sanitize_text_field($data[$k]), [])) : wp_kses(sanitize_text_field($data[$k]), []);
		}
	
		return $data;
	}
	
	/* Get all reviews in various formats */

	public function get_reviews(string $format = 'array')
	{
		$this->set_reviews();
		$avatar_directory = NULL;
		$html = '';

		if ($this->local_images && function_exists('wp_upload_dir'))
		{
			$upload_dir = wp_upload_dir();
			
			if (isset($upload_dir['baseurl']) && is_string($upload_dir['baseurl']))
			{
				$avatar_directory = $upload_dir['baseurl'] . '/gmbrr';
			}
		}
		
		if ($this->dashboard && !is_string($this->review_sort) && !is_bool($this->review_sort_asc))
		{
			$this->review_sort = $this->get_option('review_sort_admin', NULL);
			
			if (!is_string($this->review_sort) || !is_array($this->reviews) || is_array($this->reviews) && count($this->reviews) <= 1)
			{
				$this->review_sort = NULL;
				$this->review_sort_asc = NULL;
			}
			elseif (is_string($this->review_sort) && preg_match('/^(.+)(?:[_-](asc|desc))?$/', $this->review_sort, $m))
			{
				$this->review_sort = $m[1];
				$this->review_sort_asc = (!isset($m[2]) || isset($m[2]) && ($m[2] == NULL || $m[2] != 'desc'));
			}
		}
		
		if (is_string($this->review_sort) && preg_match('/^(.+)[_-](asc|desc)$/', $this->review_sort, $m))
		{
			$this->review_sort = $m[1];
			$this->review_sort_asc = ($m[2] != 'desc');
		}
		
		switch ($this->review_sort)
		{
		case 'id':
		case 'ids':
			uksort($this->reviews, function ($b, $a) { return ($this->reviews[$b]['id'] - $this->reviews[$a]['id']); } );
			break;
		case 'rating':
			uksort($this->reviews, function ($b, $a) { return ($this->reviews[$b]['rating'] + $this->reviews[$b]['id'] * 0.01 - $this->reviews[$a]['rating'] + $this->reviews[$b]['id'] * 0.01); } );
			break;
		case 'time':
		case 'submitted':
			uksort($this->reviews, function ($b, $a) { return (!isset($this->reviews[$a]['time']) || !isset($this->reviews[$b]['time'])) ? 0 : ($this->reviews[$b]['time'] - $this->reviews[$a]['time']); } );
			break;
		case 'retrieved':
			uksort($this->reviews, function ($b, $a) { return ($this->reviews[$b]['retrieved'] - $this->reviews[$a]['retrieved']); } );
			break;
		case 'name':
		case 'author':
		case 'author_name':
			uksort($this->reviews, function ($b, $a)
				{
					$c = $d = [mb_strtolower($this->reviews[$b]['author_name']), mb_strtolower($this->reviews[$a]['author_name'])];
					arsort($c, SORT_REGULAR);
					return (array_keys($c) == array_keys($d)) ? 1 : -1;
				}
			);
			break;
		case 'language':
			uksort($this->reviews, function ($b, $a)
				{
					if ($this->reviews[$a]['language'] == NULL && $this->reviews[$b]['language'] == NULL)
					{
						return 0;
					}
					
					$c = $d = [mb_strtolower($this->reviews[$b]['language']), mb_strtolower($this->reviews[$a]['language'])];
					arsort($c, SORT_REGULAR);
					return (array_keys($c) == array_keys($d)) ? 1 : -1;
				}
			);
			break;
		case 'place_id':
			uksort($this->reviews, function ($b, $a)
				{
					$c = $d = [mb_strtolower($this->reviews[$b]['place_id']), mb_strtolower($this->reviews[$a]['place_id'])];
					arsort($c, SORT_REGULAR);
					return (array_keys($c) == array_keys($d)) ? 1 : -1;
				}
			);
			break;
		case 'text':
		case 'review':
		case 'review_text':
			uksort($this->reviews, function ($b, $a)
				{
					if ($this->reviews[$a]['text'] == NULL && $this->reviews[$b]['text'] == NULL)
					{
						return 0;
					}
					
					$c = $d = [mb_strtolower($this->reviews[$b]['text']), mb_strtolower($this->reviews[$a]['text'])];
					arsort($c, SORT_REGULAR);
					return (array_keys($c) == array_keys($d)) ? 1 : -1;
				}
			);
			break;
		default:
			break;
		}
		
		if (is_string($this->review_sort) && is_bool($this->review_sort_asc) && !$this->review_sort_asc)
		{
			$this->reviews = array_reverse($this->reviews);
		}
		
		switch ($format)
		{
		case 'ids':
			$ret = [];
			foreach ($this->reviews as $a)
			{
				if (isset($a['id']) && is_numeric($a['id']))
				{
					$ret[] = intval($a['id']);
				}
			}
			return $ret;
		case 'array':
			return $this->reviews;
		case 'latest':
			if (empty($this->reviews))
			{
				if ($this->editor)
				{
					return $html;
				}

				$html = '<div id="latest-google-my-business-reviews" class="activity-block table-view-list">
<p class="none">'
				/* translators: %1$s: opening anchor tag to the settings page, %2$s: closing anchor tag */
				. sprintf(esc_html__('No reviews found, please check your %1$ssettings%2$s.', 'g-business-reviews-rating'), '<a href="' . esc_url(admin_url('options-general.php?page=google_business_reviews_rating_settings')) . '">', '</a>') . '</p>
</div>';
				return $html;
			}
			
			$this->reviews_filter(['sort' => 'date_desc', 'limit' => intval($this->get_option('meta_box_limit', 5))]);
			$i = 0;
			$html = '<div id="latest-google-my-business-reviews" class="activity-block table-view-list">
	<h3>' . __('Recent Reviews', 'g-business-reviews-rating') . '</h3>
	<ul class="list">
';

			foreach ($this->reviews_filtered as $id => $a)
			{
				$html .= '		<li class="review review-item ' . esc_attr((($i % 2) ? 'odd' : 'even') . ' rating-' . $a['rating']) . (($a['text'] == NULL) ? ' no-text' : ''). '" data-id="' . esc_attr($id). '">
			<span class="avatar' . ((isset($a['author_url']) && isset($a['profile_photo_url']) && $a['author_url'] != NULL && $a['profile_photo_url'] != NULL) ? ' original' . ((isset($a['avatar']) && $a['avatar'] != NULL && $avatar_directory != NULL) ? ' local' : '') : ' empty') . '">' . ((isset($a['author_url']) && isset($a['profile_photo_url']) && $a['author_url'] != NULL && $a['profile_photo_url'] != NULL) ? '<img src="' . esc_attr((isset($a['avatar']) && $a['avatar'] != NULL && $avatar_directory != NULL) ? $avatar_directory . '/' . $a['avatar'] : $a['profile_photo_url']) . '" alt="' . esc_attr__('Avatar', 'g-business-reviews-rating') . '" loading="lazy" width="32" height="32">' : '') . '</span>
			<span class="review-meta">
				<span class="name">' . esc_html($a['author_name']) . '</span>
				<span class="rating">' . str_repeat('★', $a['rating']) . (($a['rating'] < 5) ? '<span class="not">' . str_repeat('☆', (5 - $a['rating'])) . '</span>' : '') . '</span>
				<span class="submitted date">' . esc_html($this->get_relative_time_description($a['time'])) . '</span>
			</span>
';
				if ($a['text'] != NULL)
				{
					$html .= '            <span class="review-text">' . preg_replace('/(\r\n|\r|\n)+/', ' ' . PHP_EOL . '            	', preg_replace('/^(.{128}[^\s]{0,20})(.*)$/uis', '$1…', esc_html(wp_strip_all_tags($a['text'])))) . '</span>
';
				}
				$html .= '		</li>
';
				$i++;
			}
			
			$html .= '	</ul>
		<ul class="subsubsub links">
			<li class="reviews"><a href="' . esc_attr(admin_url(($this->editor) ? './admin.php?page=google_business_reviews_rating' : './options-general.php?page=google_business_reviews_rating_settings#reviews')) . '">' . __('Reviews', 'g-business-reviews-rating') . ' <span class="count">(<span class="reviews-count">' . esc_html($this->reviews_count()) . '</span>)</span></a> |</li>
' . (($this->administrator) ? '			<li class="settings"><a href="' . esc_attr(admin_url('./options-general.php?page=google_business_reviews_rating_settings')) . '">' . __('Settings', 'g-business-reviews-rating') . '</a> |</li>' : '') .
'			<li class="about"><a href="' . esc_attr(admin_url(($this->editor) ? './admin.php?page=google_business_reviews_rating#about' : './options-general.php?page=google_business_reviews_rating_settings#about')) . '">' . __('About', 'g-business-reviews-rating') . '</a> |</li>
			<li class="rate"><a href="https://wordpress.org/support/plugin/g-business-reviews-rating/reviews/#new-post">' . __('Rate Plugin', 'g-business-reviews-rating') . ' <span class="screen-reader-text">' . esc_html__('(opens in a new tab)', 'g-business-reviews-rating') . '</span> <span aria-hidden="true" class="dashicons dashicons-external"></span></a></li>
		</ul>
	</div>
';
			return $html;
		case 'html':
			$show_place_id = ($this->reviews_count(TRUE, NULL, FALSE) != $this->reviews_count(NULL, NULL, FALSE));
			$places = [];
			
			if (!$this->demo && !empty($this->places))
			{
				foreach ($this->places as $p)
				{
					$places[$p['place_id']] = (isset($p['name']) && $p['name'] != NULL) ? $p['name'] : NULL;
				}
			}

			$html .= '<table id="reviews-table" class="wp-list-table widefat fixed striped reviews-table' . (($show_place_id) ? ' places' : '') . '" data-languages="' . esc_attr(json_encode($this->languages)) . '" data-nonce="' . esc_attr(wp_create_nonce('gmbrr_nonce')) . '">
    <thead>
        <tr>
            <th class="id number'  . (($this->review_sort != NULL && preg_match('/^ids?(?:_(?:asc|desc))?$/i', $this->review_sort)) ? ' sorted' . ((!is_bool($this->review_sort_asc) || is_bool($this->review_sort_asc) && $this->review_sort_asc) ? ' asc' : ' desc') : (($this->review_sort == NULL) ? ' relevance' : '')) . '" title="' . (($this->review_sort == NULL) ? esc_attr__('Sorted by relevance', 'g-business-reviews-rating') : esc_attr__('ID', 'g-business-reviews-rating')) . '" data-title="' . esc_attr__('ID', 'g-business-reviews-rating') . '" data-title-relevance="' . esc_attr__('Sorted by relevance', 'g-business-reviews-rating') . '"><a href="#reviews-table" class="sort" data-field="id"><span>' . esc_html__('ID', 'g-business-reviews-rating') . '</span> <span class="sort-arrow"></span></a></th>
            <th class="submitted date'  . (($this->review_sort != NULL && preg_match('/^(?:date|submitted|time)(?:_(?:asc|desc))?$/i', $this->review_sort)) ? ' sorted' . ((!is_bool($this->review_sort_asc) || is_bool($this->review_sort_asc) && $this->review_sort_asc) ? ' asc' : ' desc') : '') . '" title="' . esc_attr__('Submitted', 'g-business-reviews-rating') . '"><a href="#reviews-table" class="sort" data-field="time"><span>' . esc_html__('Submitted', 'g-business-reviews-rating') . '</span> <span class="sort-arrow"></span></a></th>
            <th class="author'  . (($this->review_sort != NULL && preg_match('/^(?:author(?:[_-]name)?|name)(?:_(?:asc|desc))?$/i', $this->review_sort)) ? ' sorted' . ((!is_bool($this->review_sort_asc) || is_bool($this->review_sort_asc) && $this->review_sort_asc) ? ' asc' : ' desc') : '') . '" title="' . esc_attr__('Author', 'g-business-reviews-rating') . '"><a href="#reviews-table" class="sort" data-field="author_name"><span>' . esc_html__('Author', 'g-business-reviews-rating') . '</span> <span class="sort-arrow"></span></a></th>
            <th class="rating'  . (($this->review_sort != NULL && preg_match('/^ratings?(?:_(?:asc|desc))?$/i', $this->review_sort)) ? ' sorted' . ((!is_bool($this->review_sort_asc) || is_bool($this->review_sort_asc) && $this->review_sort_asc) ? ' asc' : ' desc') : '') . '" title="' . esc_attr__('Rating', 'g-business-reviews-rating') . '"><a href="#reviews-table" class="sort" data-field="rating"><span>' . esc_html__('Rating', 'g-business-reviews-rating') . '</span> <span class="sort-arrow"></span></a></th>
            <th class="text'  . (($this->review_sort != NULL && preg_match('/^(?:review(?:[_-]text)?|text)(?:_(?:asc|desc))?$/', $this->review_sort)) ? ' sorted' . ((!is_bool($this->review_sort_asc) || is_bool($this->review_sort_asc) && $this->review_sort_asc) ? ' asc' : ' desc') : '') . '" title="' . esc_attr__('Text', 'g-business-reviews-rating') . '"><a href="#reviews-table" class="sort" data-field="text"><span>' . esc_html__('Text', 'g-business-reviews-rating') . '</span> <span class="sort-arrow"></span></a></th>
            <th class="language'  . (($this->review_sort != NULL && preg_match('/^languages?(?:_(?:asc|desc))?$/i', $this->review_sort)) ? ' sorted' . ((!is_bool($this->review_sort_asc) || is_bool($this->review_sort_asc) && $this->review_sort_asc) ? ' asc' : ' desc') : '') . '" title="' . esc_attr__('Language', 'g-business-reviews-rating') . '"><a href="#reviews-table" class="sort" data-field="language"><span>' . esc_html__('Language', 'g-business-reviews-rating') . '</span> <span class="sort-arrow"></span></a></th>
            <th class="retrieved date'  . (($this->review_sort != NULL && preg_match('/^retrieved(?:_(?:asc|desc))?$/i', $this->review_sort)) ? ' sorted' . ((!is_bool($this->review_sort_asc) || is_bool($this->review_sort_asc) && $this->review_sort_asc) ? ' asc' : ' desc') : '') . '" title="' . esc_attr__('Retrieved', 'g-business-reviews-rating') . '"><a href="#reviews-table" class="sort" data-field="retrieved"><span>' . esc_html__('Retrieved', 'g-business-reviews-rating') . '</span> <span class="sort-arrow"></span></a></th>
';
			if ($show_place_id)
			{
				$html .= '            <th class="place-id'  . (($this->review_sort != NULL && preg_match('/^place_ids?(?:_(?:asc|desc))?$/i', $this->review_sort)) ? ' sorted' . ((!is_bool($this->review_sort_asc) || is_bool($this->review_sort_asc) && $this->review_sort_asc) ? ' asc' : ' desc') : '') . '" title="' . esc_attr__('Place ID', 'g-business-reviews-rating') . '"><a href="#reviews-table" class="sort" data-field="place_id">' . esc_html__('Place ID', 'g-business-reviews-rating') . ' <span class="sort-arrow"></span></a></th>
';
			}

			$html .= '        </tr>
    </thead>
    <tbody>
';		
			foreach ($this->reviews as $key => $a)
			{
				$html .= '        <tr id="' . esc_attr('review-' . $a['id']) . '" class="review ' . esc_attr('rating-' . $a['rating']) . esc_attr(((!$a['status']) ? ' inactive' : '')) . ((array_key_exists('time_estimate', $a) && $a['time_estimate']) ? ' estimate' : '') . ((array_key_exists('removable', $a) && $a['removable']) ? ' removable' : '') . '" data-id="' . esc_attr($a['id']) . '" data-order="' . esc_attr($a['order']) . '">
            <td class="id number">' . esc_html($a['id']) . ' <a href="' . esc_attr('#review-' . $a['id']) . '" class="show-hide" title="' . (($a['status']) ? esc_attr__('Hide', 'g-business-reviews-rating') : esc_attr__('Show', 'g-business-reviews-rating')) . '">' . (($a['status']) ? '<span class="dashicons dashicons-visibility"></span>' : '<span class="dashicons dashicons-hidden"></span>') . '</a>' . ((array_key_exists('removable', $a) && $a['removable'] || array_key_exists('time_estimate', $a) && $a['time_estimate']) ? '<a href="' . esc_attr('#review-' . $a['id']) . '" class="remove" title="' . esc_attr__('Remove', 'g-business-reviews-rating') . '"><span class="dashicons dashicons-no"></span></a>' : '') . '</td>
            <td class="submitted date"><span class="date' . ((array_key_exists('time_estimate', $a) && $a['time_estimate']) ? ' date-edit' : '') . '"><span class="value">' . ((array_key_exists('time_estimate', $a) && $a['time_estimate']) ? esc_html(gmdate("Y/m/d", $a['time'])) . '</span> <span class="dashicons dashicons-arrow-down"></span>' : esc_html(gmdate("Y/m/d H:i", $a['time']))) . '</span></span>' . ((array_key_exists('time_estimate', $a) && $a['time_estimate']) ? '<input type="date" id="' . esc_attr('submitted-' . $a['id']) . '" class="time-estimate" name="submitted[]" value="' . esc_attr(gmdate("Y-m-d", $a['time'])) . '" max="' . esc_attr(gmdate("Y-m-d")) . '">' : '') . '</td>
            <td class="author">
				<span class="name">' . ((isset($a['author_url']) && $a['author_url'] != NULL) ? '<a href="' . esc_attr($a['author_url']) . '" target="_blank">' : '') . esc_html($a['author_name']) . ((isset($a['author_url']) && $a['author_url'] != NULL) ? '</a>' : '') . '</span>
				' . ((isset($a['author_url']) && isset($a['profile_photo_url']) && $a['author_url'] != NULL && $a['profile_photo_url'] != NULL) ? '<span class="avatar"><a href="' . esc_attr($a['author_url']) . '" target="_blank"><img src="' . esc_attr((isset($a['avatar']) && $a['avatar'] != NULL && $avatar_directory != NULL) ? $avatar_directory . '/' . $a['avatar'] : $a['profile_photo_url']) . '" alt="' . esc_attr__('Avatar', 'g-business-reviews-rating') . '" loading="lazy" width="32" height="32"></a></span>' : '') . '
			</td>
            <td class="rating">' . str_repeat('★', $a['rating']) . (($a['rating'] < 5) ? '<span class="not">' . str_repeat('☆', (5 - $a['rating'])) . '</span>' : '') . ' <span class="rating-number">(' . esc_html($a['rating']) . ')</span></td>
            <td class="text"><div class="text-wrap">' . (($a['text'] != NULL && is_string($a['text'])) ? preg_replace('/(\r\n|\r|\n)+/', '<br>' . PHP_EOL . '            	', esc_html(wp_strip_all_tags($a['text']))) : '<span class="none" title="' . esc_attr(__('None', 'g-business-reviews-rating')) . '">—</span>') . '</div></td>
            <td class="language">' . (($a['text'] != NULL) ? '<a href="#reviews-table" class="language-edit"><span class="value">' . ((isset($a['language']) && $a['language'] != NULL) ? esc_html($a['language']) : '—') . '</span> <span class="dashicons dashicons-arrow-down"></span></a> <select id="' . esc_attr('language-' . $a['id']) . '" class="language" name="language[]" data-none="' . esc_attr__('None', 'g-business-reviews-rating') . '"></select>' . ((isset($a['original_text']) && is_string($a['original_text']) && mb_strlen($a['original_text']) > 0 && $a['original_text'] != $a['text']) ? ' <button type="button" class="review-language" data-text="' . esc_attr($a['original_text']) . '" data-value="' . esc_attr((isset($a['original_language']) && is_string($a['original_language'])) ? $a['original_language'] : '') . '">' . esc_html((isset($a['original_language']) && is_string($a['original_language'])) ? $a['original_language'] : '?') . '</button>' : '') : '<span class="none" title="' . esc_attr__('None', 'g-business-reviews-rating') . '">—</span>') . '</td>
            <td class="retrieved date">' . ((is_numeric($a['retrieved'])) ? esc_html(gmdate("Y/m/d H:i", $a['retrieved'])) : ((is_numeric($a['imported'])) ? '<span class="none" title="' . esc_attr(__('Imported', 'g-business-reviews-rating') . ': ' . gmdate("Y/m/d H:i", $a['imported'])) . '">—</a>' : '<span class="none" title="' . esc_attr__('None', 'g-business-reviews-rating') . '">—</span>')) . '</td>
';
			if ($show_place_id)
			{
				$html .= '            <td class="place-id"><span class="abbr" title="' . (($this->demo) ? 'Abcde-0123456789-Fghij-01234-z' : esc_attr($a['place_id'])) . '"' . ((!empty($places) && array_key_exists($a['place_id'], $places)) ? ' data-place-name="' . esc_attr($places[$a['place_id']]) . '"' : '') . '>' . (($this->demo) ? 'Abcde…z' : esc_html(mb_substr($a['place_id'], 0, 5)) . '…' . esc_html(mb_substr($a['place_id'], -1, 1))) . '</span></td>
';
			}

			$html .= '        </tr>
';
			}

			$html .= '        <tr id="reviews-no-results" class="no-reviews hide">
			<td colspan="' . (($show_place_id) ? 8 : 7) . '">' . esc_html__('No reviews found.', 'g-business-reviews-rating') . '</td>
        </tr>
    </tbody>
</table>
';
			return $html;
		}

		return NULL;
	}

	/* Return current relative time descriptive text */

	public function get_relative_time_description($time, ?string $fallback = NULL, bool $use_fallback = FALSE): ?string
	{
		if ($time != NULL && is_string($time))
		{
			$time = strtotime($time);
		}

		if (!is_numeric($time) || $time <= 0)
		{
			if ($use_fallback && $fallback == NULL)
			{
				return $fallback;
			}

			return '';
		}
		
		$seconds = round(time() - $time);
		
		if ($use_fallback && $fallback == NULL)
		{
			return $fallback;
		}
		
		foreach ($this->relative_times as $k => $a)
		{
			if ($a['text'] == NULL || $a['min_time'] == NULL && $seconds >= $a['max_time'] || $a['max_time'] == NULL && $seconds < $a['min_time'] || $a['min_time'] != NULL && $a['max_time'] != NULL && ($seconds >= $a['max_time'] || $seconds < $a['min_time']))
			{
				continue;
			}
			
			if (!$a['singular'] && preg_match('/^ar/i', get_option('WPLANG', '')) && preg_match('/\([^()|]+\|[^()|]+\)/', $a['text']))
			{
				$count = round($seconds / $a['divider']);

				return sprintf(preg_replace('/\(([^()|]+)\|([^()|]+)\)/', ($count >= 11) ? '$2' : '$1', $a['text']), $count);
			}

			if (!$a['singular'] && preg_match('/^pl.*$/i', get_option('WPLANG', '')) && preg_match('/[\[\]?]/', $a['text']))
			{
				switch ($k)
				{
				case 'hours':	
					if (round($seconds / $a['divider']) == 1)
					{
						return sprintf(preg_replace('/\[(a)y\]\?/i', '$1', $a['text']), round($seconds / $a['divider']));
					}
					
					if (round($seconds / $a['divider']) < 5)
					{
						return sprintf(preg_replace('/\[a(y)\]\?/i', '$1', $a['text']), round($seconds / $a['divider']));
					}
					
					return sprintf(preg_replace('/\[[^\]]+\]\?/i', '', $a['text']), round($seconds / $a['divider']));
				case 'months':	
					if (round($seconds / $a['divider']) < 5)
					{
						return sprintf(preg_replace('/\[(ą)ę\](c)\[(e)y\]/i', '$1$2$3', $a['text']), round($seconds / $a['divider']));
					}
					
					return sprintf(preg_replace('/\[ą(ę)\](c)\[e(y)\]/i', '$1$2$3', $a['text']), round($seconds / $a['divider']));
				case 'years':	
					if (round($seconds / $a['divider']) < 5)
					{
						return sprintf(preg_replace('/\[(a)\]\?/i', '$1', $a['text']), round($seconds / $a['divider']));
					}
					
					return sprintf(preg_replace('/\[[^\]]+\]\?/i', '', $a['text']), round($seconds / $a['divider']));
				}
			}
			
			return ($a['singular']) ? $a['text'] : sprintf($a['text'], round($seconds / $a['divider']));
		}

		return $fallback;
	}

/* Recompute relative_time_description for every stored review from its time/publishTime — returns TRUE if any value changed */

	protected function set_relative_time_descriptions(): bool
	{
		if (empty($this->reviews))
		{
			return FALSE;
		}

		$changed = FALSE;

		foreach (array_keys($this->reviews) as $key)
		{
			$a = $this->reviews[$key];
			$new = (isset($a['time'])) ? $this->get_relative_time_description($a['time']) : '';

			if (!isset($a['relative_time_description']) || $new != $a['relative_time_description'])
			{
				$this->reviews[$key]['relative_time_description'] = $new;
				$changed = TRUE;
			}
		}

		return $changed;
	}

/* Force-refresh stored relative date strings — triggered on plugin update, activation and locale change */

	public function refresh_relative_time_descriptions(): bool
	{
		if (get_option(self::OPTION_PREFIX . 'update', '') == '')
		{
			return FALSE;
		}

		if (!$this->translation_exists(TRUE))
		{
			return FALSE;
		}

		$place_id = get_option(self::OPTION_PREFIX . 'place_id', '');
		$api_key = get_option(self::OPTION_PREFIX . 'api_key', '');

		if ((!is_string($place_id) || mb_strlen($place_id) < 10) || (!is_string($api_key) || mb_strlen($api_key) < 10))
		{
			return FALSE;
		}

		if (empty($this->reviews))
		{
			$this->reviews = $this->get_array_option('reviews');
		}

		if (!$this->set_relative_time_descriptions())
		{
			return FALSE;
		}

		$this->update_option('reviews', $this->reviews, 'no');

		return TRUE;
	}

/* Check if current translation exists */

	public function translation_exists(bool $loose = FALSE): bool
	{
		$locale = (function_exists('determine_locale')) ? determine_locale() : get_option('WPLANG', '');
		$locale = (is_string($locale)) ? $locale : '';

		if ($loose)
		{
			return (preg_match('/^(?:(?:ar|cs|cz|da|de|el|en|es|fr|he|hr|hu|it|iw|ja|ko|nl|pl|sr|zh).*)?$/i', $locale));
		}

		return (preg_match('/^(?:en.*)?$/i', $locale) || is_textdomain_loaded('g-business-reviews-rating'));
	}
	
	public function setup(): bool
	{
		return TRUE;
	}

	protected function delete_place(string $place_id): bool
	{
		if (!is_string($place_id))
		{
			return FALSE;
		}

		if (empty($this->places))
		{
			$this->places = $this->get_array_option('places');
		}

		if (empty($this->places))
		{
			return FALSE;
		}

		$reviews = [];

		foreach ($this->places as $i => $a)
		{
			if (!isset($a['place_id']) || $a['place_id'] != $place_id)
			{
				continue;
			}

			if (empty($this->reviews))
			{
				$this->reviews = $this->get_array_option('reviews');
			}

			foreach ($this->reviews as $j => $r)
			{
				if (!isset($r['place_id']) || $r['place_id'] != $place_id)
				{
					continue;
				}

				$reviews[] = $j;
			}

			if (!empty($reviews))
			{
				$upload_directory_plugin = NULL;

				foreach ($reviews as $j)
				{
					if (isset($this->reviews[$j]['avatar']) && $this->reviews[$j]['avatar'] != NULL)
					{
						if ($upload_directory_plugin == NULL)
						{
							$upload_directory = wp_get_upload_dir();

							if (isset($upload_directory['basedir']) && is_string($upload_directory['basedir']))
							{
								$upload_directory_plugin = $upload_directory['basedir'] . '/gmbrr';
							}
							elseif (isset($upload_directory['path']) && is_string($upload_directory['path']))
							{
								$upload_directory_plugin = preg_replace('#^(.+?)(?:/\d+/\d+)/?$#', '$1', $upload_directory['path']) . '/gmbrr';
							}
						}

						if (!is_dir($upload_directory_plugin) || !is_file($upload_directory_plugin . '/' . $this->reviews[$j]['avatar']))
						{
							continue;
						}

						@unlink($upload_directory_plugin . '/' . $this->reviews[$j]['avatar']);
					}

					unset($this->reviews[$j]);
				}

				delete_transient(self::OPTION_PREFIX . 'reviews_shuffled');
				wp_cache_delete('reviews_shuffled', self::OPTION_PREFIX);
				wp_cache_delete('reviews', self::OPTION_PREFIX);
				$this->update_option('reviews', $this->reviews, 'no');
			}

			unset($this->places[$i]);
			sort($this->places);
			$this->update_option('places', $this->places, 'yes');

			return TRUE;
		}

		return TRUE;
	}

	/* Get the current or next retrieval/review sort */

	protected function get_retrieval_sort(bool $next_sort = FALSE): string
	{
		$retrieval_sort = 'most_relevant';

		if ($this->place_id == NULL || $this->demo)
		{
			return $retrieval_sort;
		}

		$option = $this->get_option('retrieval_sort', 'most_relevant');

		switch ($option)
		{
		case 'most_relevant':
		case 'newest':
			$retrieval_sort = $option;
			break;
		case 'review_sort':
			$retrieval_sort = (isset($this->review_sort) && is_string($this->review_sort) && !preg_match('/^relevance.*$/i', $this->review_sort)) ? 'newest' : 'most_relevant';
			break;
		default:
			$retrieval = $this->get_option('retrieval');

			if (!is_array($retrieval) || is_array($retrieval) && (empty($retrieval) || !isset($retrieval['requests']) || !is_array($retrieval['requests'])))
			{
				break;
			}

			$requests = array_reverse($retrieval['requests']);

			foreach ($requests as $a)
			{
				if ($a['place_id'] != $this->place_id)
				{
					continue;
				}

				$retrieval_sort = (isset($a['review_sort']) && ($next_sort && $a['review_sort'] == 'most_relevant' || !$next_sort && $a['review_sort'] == 'newest')) ? 'newest' : 'most_relevant';
				break;
			}

			break;
		}

		return $retrieval_sort;
	}

	/* Log actions */

	public static function log(string $type, $data = NULL): bool
	{
		$log = get_option(self::OPTION_PREFIX . 'log', []);

		if (!is_array($log))
		{
			$log = [];
		}

		$log = array_splice($log, -1000);

		$log[] = [
			'type' => $type,
			'data' => $data,
			'user' => (function_exists('get_current_user_id')) ? get_current_user_id() : NULL,
			'cron' => (defined('DOING_CRON') && DOING_CRON),
			'time' => time()
		];

		update_option(self::OPTION_PREFIX . 'log', $log, 'no');

		return TRUE;
	}
}

defined('GOOGLE_BUSINESS_REVIEWS_RATING_DEMO_RESULT') or define('GOOGLE_BUSINESS_REVIEWS_RATING_DEMO_RESULT', '{\"displayName\":{\"text\":\"Everyday Demo Restaurant\",\"languageCode\":\"en\"},\"formattedAddress\":\"123 Battersea Place, London, UK\",\"googleMapsUri\":\"https://www.google.com/maps/place/?q=place_id:ChIJtTeDfh9w5kcRJEWRKN1Yy6I\",\"iconMaskBaseUri\":\"https://maps.gstatic.com/mapfiles/place_api/icons/v2/restaurant_pinlet\",\"id\":\"ChIJtTeDfh9w5kcRJEWRKN1Yy6I\",\"shortFormattedAddress\":\"123 Battersea Place, London\",\"rating\":3.9,\"userRatingCount\":31,\"reviews\":[{\"name\":\"places/ChIJtTeDfh9w5kcRJEWRKN1Yy6I/reviews/AfLeW3v1demo\",\"authorAttribution\":{\"displayName\":\"Lisa Dooley\",\"uri\":\"#\",\"photoUri\":\"data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0idXRmLTgiPz4KPCFET0NUWVBFIHN2ZyBQVUJMSUMgIi0vL1czQy8vRFREIFNWRyAxLjEvL0VOIiAiaHR0cDovL3d3dy53My5vcmcvR3JhcGhpY3MvU1ZHLzEuMS9EVEQvc3ZnMTEuZHRkIj4KPHN2ZyB2ZXJzaW9uPSIxLjEiIGlkPSJMYXllcl8xIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHhtbG5zOnhsaW5rPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5L3hsaW5rIiB4PSIwcHgiIHk9IjBweCIKd2lkdGg9IjEyOHB4IiBoZWlnaHQ9IjEyOHB4IiB2aWV3Qm94PSIwIDAgMTI4IDEyOCIgZW5hYmxlLWJhY2tncm91bmQ9Im5ldyAwIDAgMTI4IDEyOCIgeG1sOnNwYWNlPSJwcmVzZXJ2ZSI+CjxjaXJjbGUgZmlsbD0iIzAwN0Y3MCIgY3g9IjY0IiBjeT0iNjQiIHI9IjY0Ii8+CjxnPgo8cGF0aCBmaWxsPSIjRkZGRkZGIiBkPSJNNDYuOTM5LDI4LjA1aDYuMjU2djYyLjU1N0g4OC4xN3Y1LjQ5N2gtNDEuMjNWMjguMDV6Ii8+CjwvZz4KPC9zdmc+Cg==\"},\"rating\":5,\"relativePublishTimeDescription\":\"3 weeks ago\",\"text\":{\"text\":\"Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.\",\"languageCode\":\"en\"},\"originalText\":{\"text\":\"Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.\",\"languageCode\":\"en\"},\"publishTime\":\"2026-04-16T07:29:13Z\"},{\"name\":\"places/ChIJtTeDfh9w5kcRJEWRKN1Yy6I/reviews/AfLeW3v2demo\",\"authorAttribution\":{\"displayName\":\"Catherine P\",\"uri\":\"#\",\"photoUri\":\"data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0idXRmLTgiPz4KPCFET0NUWVBFIHN2ZyBQVUJMSUMgIi0vL1czQy8vRFREIFNWRyAxLjEvL0VOIiAiaHR0cDovL3d3dy53My5vcmcvR3JhcGhpY3MvU1ZHLzEuMS9EVEQvc3ZnMTEuZHRkIj4KPHN2ZyB2ZXJzaW9uPSIxLjEiIGlkPSJMYXllcl8xIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHhtbG5zOnhsaW5rPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5L3hsaW5rIiB4PSIwcHgiIHk9IjBweCIKd2lkdGg9IjEyOHB4IiBoZWlnaHQ9IjEyOHB4IiB2aWV3Qm94PSIwIDAgMTI4IDEyOCIgZW5hYmxlLWJhY2tncm91bmQ9Im5ldyAwIDAgMTI4IDEyOCIgeG1sOnNwYWNlPSJwcmVzZXJ2ZSI+CjxjaXJjbGUgZmlsbD0iI0JGMDkwMCIgY3g9IjY0IiBjeT0iNjQiIHI9IjY0Ii8+CjxnPgo8cGF0aCBmaWxsPSIjRkZGRkZGIiBkPSJNOTIuNjI1LDczLjkyNWMtMC41MDcsNy41ODMtMy4xNSwxMy40OTItNy45MjksMTcuNzI1Qzc5LjkxOCw5NS44ODQsNzMuNDc3LDk4LDY1LjM3Niw5OApjLTQuNTU3LDAtOC42NTUtMC44MjItMTIuMjk1LTIuNDY0Yy0zLjY0MS0xLjY0My02Ljc1Ni0zLjk5Ni05LjM1MS03LjA2MmMtMi41OTUtMy4wNjQtNC41OS02Ljg0LTUuOTgyLTExLjMyNwpjLTEuMzkyLTQuNDg1LTIuMDg4LTkuNTcyLTIuMDg4LTE1LjI2YzAtNS42MjMsMC43MTEtMTAuNjQ3LDIuMTM1LTE1LjA3YzEuNDI1LTQuNDIyLDMuNDM1LTguMTY3LDYuMDI5LTExLjIzMgpjMi41OTUtMy4wNjQsNS43MjktNS40MDMsOS40LTcuMDE0YzMuNjctMS42MTEsNy43ODQtMi40MTcsMTIuMzQyLTIuNDE3YzMuODYxLDAsNy4zOTEsMC41MTMsMTAuNTg3LDEuNTM4CmMzLjE5NSwxLjAyNSw1LjkzMywyLjQ3OSw4LjIxMiw0LjM2YzIuMjc3LDEuODgyLDQuMDY2LDQuMTUsNS4zNjQsNi44MDRjMS4yOTcsMi42NTQsMi4wNDIsNS42MjUsMi4yMzEsOC45MWgtNi4yNTYKYy0wLjUwNi00Ljk5MS0yLjU1OS04LjkyNC02LjE2MS0xMS44MDFjLTMuNjAyLTIuODc1LTguMjc4LTQuMzEzLTE0LjAyNy00LjMxM2MtNy4yNjcsMC0xMi45ODUsMi41NjgtMTcuMTU2LDcuNzAxCmMtNC4xNyw1LjEzNS02LjI1NSwxMi42MTQtNi4yNTUsMjIuNDM4YzAsNC45NDUsMC41NTIsOS4zMTksMS42NTksMTMuMTIyYzEuMTA0LDMuODAzLDIuNjg1LDcuMDA1LDQuNzM5LDkuNjAzCmMyLjA1MywyLjYsNC41MzQsNC41ODEsNy40NCw1Ljk0M2MyLjkwNiwxLjM2Miw2LjE2MSwyLjA0NCw5Ljc2MywyLjA0NGM2LjEyOCwwLDEwLjk5NS0xLjYyNiwxNC41OTctNC44ODIKYzMuNjAyLTMuMjU0LDUuNjIzLTcuODE5LDYuMDY2LTEzLjY5Nkg5Mi42MjV6Ii8+CjwvZz4KPC9zdmc+Cg==\"},\"rating\":1,\"relativePublishTimeDescription\":\"2 months ago\",\"text\":{\"text\":\"Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. \nExcepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum. Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. \nExcepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.\",\"languageCode\":\"en\"},\"originalText\":{\"text\":\"Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. \nExcepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum. Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. \nExcepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.\",\"languageCode\":\"en\"},\"publishTime\":\"2026-03-02T04:36:24Z\"},{\"name\":\"places/ChIJtTeDfh9w5kcRJEWRKN1Yy6I/reviews/AfLeW3v3demo\",\"authorAttribution\":{\"displayName\":\"Fay A\",\"uri\":\"#\",\"photoUri\":\"data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0idXRmLTgiPz4KPCFET0NUWVBFIHN2ZyBQVUJMSUMgIi0vL1czQy8vRFREIFNWRyAxLjEvL0VOIiAiaHR0cDovL3d3dy53My5vcmcvR3JhcGhpY3MvU1ZHLzEuMS9EVEQvc3ZnMTEuZHRkIj4KPHN2ZyB2ZXJzaW9uPSIxLjEiIGlkPSJMYXllcl8xIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHhtbG5zOnhsaW5rPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5L3hsaW5rIiB4PSIwcHgiIHk9IjBweCIKd2lkdGg9IjEyOHB4IiBoZWlnaHQ9IjEyOHB4IiB2aWV3Qm94PSIwIDAgMTI4IDEyOCIgZW5hYmxlLWJhY2tncm91bmQ9Im5ldyAwIDAgMTI4IDEyOCIgeG1sOnNwYWNlPSJwcmVzZXJ2ZSI+CjxjaXJjbGUgZmlsbD0iI0EzMDBDNCIgY3g9IjY0IiBjeT0iNjQiIHI9IjY0Ii8+CjxnPgo8cGF0aCBmaWxsPSIjRkZGRkZGIiBkPSJNNDQuNjY1LDI3Ljk1Nmg0My4xMjZ2NS40OTdINTAuOTJ2MjQuODMzaDMzLjQ1OHY1LjQ5N0g1MC45MnYzMi4zMjFoLTYuMjU2VjI3Ljk1NnoiLz4KPC9nPgo8L3N2Zz4K\"},\"rating\":5,\"relativePublishTimeDescription\":\"a week ago\",\"text\":{\"text\":\"Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam.\",\"languageCode\":\"en\"},\"originalText\":{\"text\":\"Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam.\",\"languageCode\":\"en\"},\"publishTime\":\"2026-05-03T12:00:00Z\"},{\"name\":\"places/ChIJtTeDfh9w5kcRJEWRKN1Yy6I/reviews/AfLeW3v4demo\",\"authorAttribution\":{\"displayName\":\"Dexter Ortega\",\"uri\":\"#\",\"photoUri\":\"data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0idXRmLTgiPz4KPCFET0NUWVBFIHN2ZyBQVUJMSUMgIi0vL1czQy8vRFREIFNWRyAxLjEvL0VOIiAiaHR0cDovL3d3dy53My5vcmcvR3JhcGhpY3MvU1ZHLzEuMS9EVEQvc3ZnMTEuZHRkIj4KPHN2ZyB2ZXJzaW9uPSIxLjEiIGlkPSJMYXllcl8xIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHhtbG5zOnhsaW5rPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5L3hsaW5rIiB4PSIwcHgiIHk9IjBweCIKd2lkdGg9IjEyOHB4IiBoZWlnaHQ9IjEyOHB4IiB2aWV3Qm94PSIwIDAgMTI4IDEyOCIgZW5hYmxlLWJhY2tncm91bmQ9Im5ldyAwIDAgMTI4IDEyOCIgeG1sOnNwYWNlPSJwcmVzZXJ2ZSI+CjxjaXJjbGUgZmlsbD0iIzI2NUVGRiIgY3g9IjY0IiBjeT0iNjQiIHI9IjY0Ii8+CjxnPgo8cGF0aCBmaWxsPSIjRkZGRkZGIiBkPSJNNTkuMjE0LDI3Ljk1NmM0LjA0MywwLDcuNzA4LDAuMTg5LDEwLjk5NCwwLjU2OGMzLjI4NSwwLjM3OSw2LjI1NiwxLjQyMiw4LjkxLDMuMTI4CmM0LjE3LDIuNjU0LDcuMzYsNi41MjUsOS41NzMsMTEuNjExYzIuMjExLDUuMDg3LDMuMzE3LDExLjI5NiwzLjMxNywxOC42MjVjMCw3Ljg5OS0xLjIxOCwxNC40NTQtMy42NDksMTkuNjY4CmMtMi40MzQsNS4yMTMtNS45ODcsOS4wODQtMTAuNjYzLDExLjYxYy0yLjQ2NSwxLjMyNy01LjM3MiwyLjE0OS04LjcyMSwyLjQ2NWMtMy4zNSwwLjMxNi03LjIzNSwwLjQ3NC0xMS42NTgsMC40NzRIMzguNTUxVjI3Ljk1NgpoMTYuMDE5SDU5LjIxNHogTTU3Ljk4MSw5MC42MDdjMy43OTIsMCw3LjEwOC0wLjEyNiw5Ljk1Mi0wLjM4MWMyLjg0NC0wLjI1Myw1LjI3NS0wLjk4Miw3LjI5OC0yLjE4OAppYzYuODg3LTQuMTIsMTAuMzMyLTEyLjc3NSwxMC4zMzItMjUuOTYyYzAtMTIuNzQyLTMuMjg3LTIxLjI3LTkuODU3LTI1LjU4MWMtMi4yMTMtMS40NTctNC44MzQtMi4zMy03Ljg2Ny0yLjYxNQpjLTMuMDMzLTAuMjg0LTYuNTQtMC40MjgtMTAuNTIxLTAuNDI4SDQ0LjgwN3Y1Ny4xNTVINTcuOTgxeiIvPgo8L2c+PC9zdmc+Cg==\"},\"rating\":5,\"relativePublishTimeDescription\":\"3 months ago\",\"text\":{\"text\":\"Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.\",\"languageCode\":\"es\"},\"originalText\":{\"text\":\"Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.\",\"languageCode\":\"es\"},\"publishTime\":\"2026-01-26T08:04:18Z\"},{\"name\":\"places/ChIJtTeDfh9w5kcRJEWRKN1Yy6I/reviews/AfLeW3v5demo\",\"authorAttribution\":{\"displayName\":\"Mary N\",\"uri\":\"#\",\"photoUri\":\"data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0idXRmLTgiPz4KPCFET0NUWVBFIHN2ZyBQVUJMSUMgIi0vL1czQy8vRFREIFNWRyAxLjEvL0VOIiAiaHR0cDovL3d3dy53My5vcmcvR3JhcGhpY3MvU1ZHLzEuMS9EVEQvc3ZnMTEuZHRkIj4KPHN2ZyB2ZXJzaW9uPSIxLjEiIGlkPSJMYXllcl8xIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHhtbG5zOnhsaW5rPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5L3hsaW5rIiB4PSIwcHgiIHk9IjBweCIKd2lkdGg9IjEyOHB4IiBoZWlnaHQ9IjEyOHB4IiB2aWV3Qm94PSIwIDAgMTI4IDEyOCIgZW5hYmxlLWJhY2tncm91bmQ9Im5ldyAwIDAgMTI4IDEyOCIgeG1sOnNwYWNlPSJwcmVzZXJ2ZSI+CjxjaXJjbGUgZmlsbD0iI0I2M0RGRiIgY3g9IjY0IiBjeT0iNjQiIHI9IjY0Ii8+CjxnPgo8cGF0aCBmaWxsPSIjRkZGRkZGIiBkPSJNMzIuODY0LDI3Ljk1Nmg4LjgxNWwyMi40NjMsNTkuOTk4bDIyLjA4NC01OS45OThoOC44MTV2NjguMTQ5aC02LjI1NlYzNi42NzVMNjcuMDgxLDk2LjEwNGgtNS43ODIKTDM5LjEyLDM2LjY3NXY1OS40MjloLTYuMjU2VjI3Ljk1NnoiLz4KPC9nPgo8L3N2Zz4K\"},\"rating\":4,\"relativePublishTimeDescription\":\"4 months ago\",\"text\":{\"text\":\"Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.\",\"languageCode\":\"en\"},\"originalText\":{\"text\":\"Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.\",\"languageCode\":\"en\"},\"publishTime\":\"2026-01-02T14:03:43Z\"},{\"name\":\"places/ChIJtTeDfh9w5kcRJEWRKN1Yy6I/reviews/AfLeW3v6demo\",\"authorAttribution\":{\"displayName\":\"Jerry Jet\",\"uri\":\"#\",\"photoUri\":\"data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0idXRmLTgiPz4KPCFET0NUWVBFIHN2ZyBQVUJMSUMgIi0vL1czQy8vRFREIFNWRyAxLjEvL0VOIiAiaHR0cDovL3d3dy53My5vcmcvR3JhcGhpY3MvU1ZHLzEuMS9EVEQvc3ZnMTEuZHRkIj4KPHN2ZyB2ZXJzaW9uPSIxLjEiIGlkPSJMYXllcl8xIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHhtbG5zOnhsaW5rPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5L3hsaW5rIiB4PSIwcHgiIHk9IjBweCIKd2lkdGg9IjEyOHB4IiBoZWlnaHQ9IjEyOHB4IiB2aWV3Qm94PSIwIDAgMTI4IDEyOCIgZW5hYmxlLWJhY2tncm91bmQ9Im5ldyAwIDAgMTI4IDEyOCIgeG1sOnNwYWNlPSJwcmVzZXJ2ZSI+CjxjaXJjbGUgZmlsbD0iI0ZGQjQwNSIgY3g9IjY0IiBjeT0iNjQiIHI9IjY0Ii8+CjxnPgo8cGF0aCBmaWxsPSIjRkZGRkZGIiBkPSJNNDkuNTQ2LDc1LjI1MnY0LjM2YzAsOC41OTQsMy44MjMsMTIuODkxLDExLjQ2OSwxMi44OTFjNC41NSwwLDcuNzM5LTEuMTIxLDkuNTczLTMuMzY1CmMxLjgzMi0yLjI0MiwyLjc0OC01Ljg5MiwyLjc0OC0xMC45NDdWMjguMDVoNi4yNTZ2NTEuNTYyYzAsNi4wNjYtMS42MTEsMTAuNjQ4LTQuODM0LDEzLjc0M0M3MS41MzUsOTYuNDUxLDY2Ljg5MSw5OCw2MC44MjUsOTgKYy0xMS42OTEsMC0xNy41MzUtNS45MzgtMTcuNTM1LTE3LjgxOXYtNC45MjlINDkuNTQ2eiIvPgo8L2c+Cjwvc3ZnPgo=\"},\"rating\":2,\"relativePublishTimeDescription\":\"4 months ago\",\"text\":{\"text\":\"Ut enim ad minima veniam, quis nostrum exercitationem ullam corporis suscipit laboriosam, nisi ut aliquid ex ea commodi consequatur? Quis autem vel eum iure reprehenderit qui in ea voluptate velit esse quam nihil molestiae consequatur, vel illum qui dolorem eum fugiat quo voluptas nulla pariatur?\",\"languageCode\":\"en\"},\"originalText\":{\"text\":\"Ut enim ad minima veniam, quis nostrum exercitationem ullam corporis suscipit laboriosam, nisi ut aliquid ex ea commodi consequatur? Quis autem vel eum iure reprehenderit qui in ea voluptate velit esse quam nihil molestiae consequatur, vel illum qui dolorem eum fugiat quo voluptas nulla pariatur?\",\"languageCode\":\"en\"},\"publishTime\":\"2026-01-02T14:03:43Z\"},{\"name\":\"places/ChIJtTeDfh9w5kcRJEWRKN1Yy6I/reviews/AfLeW3v7demo\",\"authorAttribution\":{\"displayName\":\"Ian A\",\"uri\":\"#\",\"photoUri\":\"data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0idXRmLTgiPz4KPCFET0NUWVBFIHN2ZyBQVUJMSUMgIi0vL1czQy8vRFREIFNWRyAxLjEvL0VOIiAiaHR0cDovL3d3dy53My5vcmcvR3JhcGhpY3MvU1ZHLzEuMS9EVEQvc3ZnMTEuZHRkIj4KPHN2ZyB2ZXJzaW9uPSIxLjEiIGlkPSJMYXllcl8xIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHhtbG5zOnhsaW5rPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5L3hsaW5rIiB4PSIwcHgiIHk9IjBweCIKd2lkdGg9IjEyOHB4IiBoZWlnaHQ9IjEyOHB4IiB2aWV3Qm94PSIwIDAgMTI4IDEyOCIgZW5hYmxlLWJhY2tncm91bmQ9Im5ldyAwIDAgMTI4IDEyOCIgeG1sOnNwYWNlPSJwcmVzZXJ2ZSI+CjxjaXJjbGUgZmlsbD0iIzAwQjk4NyIgY3g9IjY0IiBjeT0iNjQiIHI9IjY0Ii8+CjxnPgo8cGF0aCBmaWxsPSIjRkZGRkZGIiBkPSJNNjAuODI1LDI3Ljk1Nmg2LjI1NXY2OC4xNDloLTYuMjU1VjI3Ljk1NnoiLz4KPC9nPgo8L3N2Zz4K\"},\"rating\":5,\"relativePublishTimeDescription\":\"2 months ago\",\"text\":{\"text\":\"Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem aperiam, eaque ipsa quae ab illo inventore veritatis et quasi architecto beatae vitae dicta sunt explicabo. Nemo enim ipsam voluptatem quia voluptas sit aspernatur aut odit aut fugit, sed quia consequuntur magni dolores eos qui ratione voluptatem sequi nesciunt.\",\"languageCode\":\"it\"},\"originalText\":{\"text\":\"Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem aperiam, eaque ipsa quae ab illo inventore veritatis et quasi architecto beatae vitae dicta sunt explicabo. Nemo enim ipsam voluptatem quia voluptas sit aspernatur aut odit aut fugit, sed quia consequuntur magni dolores eos qui ratione voluptatem sequi nesciunt.\",\"languageCode\":\"it\"},\"publishTime\":\"2026-03-02T04:36:24Z\"}]}');

require_once(plugin_dir_path(__FILE__) . 'wp/index.php');
require_once(plugin_dir_path(__FILE__) . 'wp/widget.php');
require_once(plugin_dir_path(__FILE__) . 'wp/block.php');
require_once(plugin_dir_path(__FILE__) . 'dashboard/index.php');
