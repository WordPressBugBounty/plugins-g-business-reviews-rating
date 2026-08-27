<?php

if (!defined('ABSPATH'))
{
	die();
}

require_once(plugin_dir_path(__FILE__) . 'sync.php');

class google_business_reviews_rating_dashboard extends google_business_reviews_rating_sync
{
	private static ?self $plugin_instance = NULL;

	public function __construct()
	{
		parent::__construct();
		self::$plugin_instance = $this;
		$this->setup();
	}

	/* Returns the single dashboard instance so widget and other classes can access shared data without reinstantiation */

	public static function get_instance(): ?self
	{
		return self::$plugin_instance;
	}

	/* Per user; unrecognised values follow the operating system */

	private function color_scheme(): string
	{
		$stored = get_user_meta(get_current_user_id(), self::OPTION_PREFIX . 'color_scheme', TRUE);

		return (is_string($stored) && preg_match('/^(?:light|dark)$/', $stored)) ? $stored : 'system';
	}

	/* Recent retrievals with how many reviews each one added */

	public function get_retrieval_history(int $limit = 10): array
	{
		$retrieval = $this->get_array_option('retrieval');

		if (!isset($retrieval['requests']) || !is_array($retrieval['requests']))
		{
			return [];
		}

		$seen = [];
		$history = [];

		foreach ($retrieval['requests'] as $a)
		{
			if (!is_array($a) || !isset($a['time']) || !is_numeric($a['time']))
			{
				continue;
			}

			$ids = (isset($a['review_ids']) && is_array($a['review_ids'])) ? $a['review_ids'] : [];
			$added = 0;

			foreach ($ids as $id)
			{
				if (!is_string($id) && !is_numeric($id) || isset($seen[strval($id)]))
				{
					continue;
				}

				$seen[strval($id)] = TRUE;
				$added++;
			}

			$history[] = [
				'time' => intval($a['time']),
				'added' => $added,
				'returned' => count($ids),
				'place_id' => (isset($a['place_id']) && is_string($a['place_id'])) ? $a['place_id'] : NULL,
				'rating_count' => (isset($a['rating_count']) && is_numeric($a['rating_count'])) ? intval($a['rating_count']) : NULL,
				'status' => (isset($a['status'])) ? $a['status'] : NULL,
				'cron' => (isset($a['sync']) && $a['sync'])
			];
		}

		return array_slice(array_reverse($history), 0, $limit);
	}

	/* Returns data arrays needed by the widget form */

	public function widget_data(): array
	{
		if (empty($this->languages))
		{
			$this->set_languages();
		}

		if (empty($this->reviews_themes))
		{
			$this->set_reviews_themes();
		}

		return [
			'review_sort_options' => $this->review_sort_options,
			'languages' => $this->languages,
			'reviews_themes' => $this->reviews_themes,
		];
	}

	/* Initiate the plugin in the dashboard */

	public function setup(): bool
	{
		if (!$this->dashboard)
		{
			return TRUE;
		}

		$this->demo = $this->get_option('demo');
		$this->api_version = $this->get_option('api_version', NULL);
		$this->settings_updated = ($this->dashboard && isset($_REQUEST['settings-updated']) && (is_bool($_REQUEST['settings-updated']) && $_REQUEST['settings-updated'] || is_string($_REQUEST['settings-updated']) && preg_match('/^(?:true|1)$/i', $_REQUEST['settings-updated'])));

		if ($this->settings_updated)
		{
			self::set_api_history();
		}

		register_setting(self::OPTION_PREFIX . 'settings', self::OPTION_PREFIX . 'additional_array_sanitization', ['type' => 'boolean']);
		register_setting(self::OPTION_PREFIX . 'settings', self::OPTION_PREFIX . 'api_key', ['type' => 'string', 'sanitize_callback' => [$this, 'sanitize_api_key']]);
		register_setting(self::OPTION_PREFIX . 'settings', self::OPTION_PREFIX . 'place_id', ['type' => 'string', 'sanitize_callback' => [$this, 'sanitize_place_id']]);
		register_setting(self::OPTION_PREFIX . 'settings', self::OPTION_PREFIX . 'place_delete', ['type' => 'array', 'sanitize_callback' => [$this, 'sanitize_place_delete']]);
		register_setting(self::OPTION_PREFIX . 'settings', self::OPTION_PREFIX . 'language', ['type' => 'string', 'sanitize_callback' => [$this, 'sanitize_input']]);
		register_setting(self::OPTION_PREFIX . 'settings', self::OPTION_PREFIX . 'retrieval_sort', ['type' => 'string', 'sanitize_callback' => [$this, 'sanitize_retrieval_sort']]);
		register_setting(self::OPTION_PREFIX . 'settings', self::OPTION_PREFIX . 'retrieval_translate', ['type' => 'boolean']);
		register_setting(self::OPTION_PREFIX . 'settings', self::OPTION_PREFIX . 'demo', ['type' => 'boolean', 'sanitize_callback' => [$this, 'sanitize_demo']]);
		register_setting(self::OPTION_PREFIX . 'settings', self::OPTION_PREFIX . 'update', ['type' => 'string', 'sanitize_callback' => [$this, 'sanitize_input']]);
		register_setting(self::OPTION_PREFIX . 'settings', self::OPTION_PREFIX . 'review_limit', ['type' => 'string', 'sanitize_callback' => [$this, 'sanitize_input']]);
		register_setting(self::OPTION_PREFIX . 'settings', self::OPTION_PREFIX . 'review_sort', ['type' => 'string', 'sanitize_callback' => [$this, 'sanitize_input']]);
		register_setting(self::OPTION_PREFIX . 'settings', self::OPTION_PREFIX . 'rating_min', ['type' => 'string', 'sanitize_callback' => [$this, 'sanitize_input']]);
		register_setting(self::OPTION_PREFIX . 'settings', self::OPTION_PREFIX . 'rating_max', ['type' => 'string', 'sanitize_callback' => [$this, 'sanitize_input']]);
		register_setting(self::OPTION_PREFIX . 'settings', self::OPTION_PREFIX . 'review_text_min', ['type' => 'string', 'sanitize_callback' => [$this, 'sanitize_input']]);
		register_setting(self::OPTION_PREFIX . 'settings', self::OPTION_PREFIX . 'review_text_max', ['type' => 'string', 'sanitize_callback' => [$this, 'sanitize_input']]);
		register_setting(self::OPTION_PREFIX . 'settings', self::OPTION_PREFIX . 'review_text_excerpt_length', ['type' => 'string', 'sanitize_callback' => [$this, 'sanitize_input']]);
		register_setting(self::OPTION_PREFIX . 'settings', self::OPTION_PREFIX . 'theme', ['type' => 'string', 'sanitize_callback' => [$this, 'sanitize_input']]);
		register_setting(self::OPTION_PREFIX . 'settings', self::OPTION_PREFIX . 'view', ['type' => 'number']);
		register_setting(self::OPTION_PREFIX . 'settings', self::OPTION_PREFIX . 'color_scheme', ['type' => 'string', 'sanitize_callback' => [$this, 'sanitize_input']]);
		register_setting(self::OPTION_PREFIX . 'settings', self::OPTION_PREFIX . 'icon', ['type' => 'string', 'sanitize_callback' => [$this, 'sanitize_input']]);
		register_setting(self::OPTION_PREFIX . 'settings', self::OPTION_PREFIX . 'logo', ['type' => 'string', 'sanitize_callback' => [$this, 'sanitize_input']]);
		register_setting(self::OPTION_PREFIX . 'settings', self::OPTION_PREFIX . 'telephone', ['type' => 'string', 'sanitize_callback' => [$this, 'sanitize_input']]);
		register_setting(self::OPTION_PREFIX . 'settings', self::OPTION_PREFIX . 'business_type', ['type' => 'string', 'sanitize_callback' => [$this, 'sanitize_input']]);
		register_setting(self::OPTION_PREFIX . 'settings', self::OPTION_PREFIX . 'price_range', ['type' => 'string', 'sanitize_callback' => [$this, 'sanitize_input']]);
		register_setting(self::OPTION_PREFIX . 'settings', self::OPTION_PREFIX . 'structured_data', ['type' => 'number']);
		register_setting(self::OPTION_PREFIX . 'settings', self::OPTION_PREFIX . 'local_images', ['type' => 'boolean']);

		add_action('admin_menu', [$this, 'menu']);
		add_action('admin_enqueue_scripts', [$this, 'css_load']);
		add_action('admin_enqueue_scripts', [$this, 'js_load']);
		add_action('wp_ajax_' . self::PLUGIN_ALIAS . '_admin_ajax', [$this, 'ajax']);
		add_action('admin_notices', [$this, 'notices']);
		add_action('in_admin_header', [$this, 'notices_clear'], 1000);
		add_action('wp_dashboard_setup', [$this, 'widget']);

		add_filter('plugin_action_links', [__CLASS__, 'add_action_links'], 10, 2);
		add_filter('plugin_row_meta', [__CLASS__, 'add_plugin_meta'], 10, 2);

		if (!$this->set_data())
		{
			return TRUE;
		}

		$this->set_reviews();
		$this->set_icon();
		$this->set_logo();

		return TRUE;
	}

	/* Set the menu item */

	public function menu(): bool
	{
		$allow_editor = $this->get_option('editor');

		if ($allow_editor != $this->get_option('editor', 'x'))
		{
			$allow_editor = TRUE;
			$this->update_option('editor', $allow_editor, 'no');
		}

		$this->administrator = (current_user_can('manage_options', self::PLUGIN_ALIAS));
		$this->editor = (!$this->administrator && $allow_editor && current_user_can('edit_published_posts', self::PLUGIN_ALIAS));

		if (!$this->editor && !$this->administrator)
		{
			return TRUE;
		}

		if ($this->administrator)
		{
			$pages = [['add_options_page', __('Reviews and Rating - Google Reviews', 'g-business-reviews-rating'), __('Reviews and Rating - Google Reviews', 'g-business-reviews-rating'), 'manage_options', self::PLUGIN_ALIAS . '_settings', [$this, 'settings']]];
		}
		else
		{
			$icon = 'data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0idXRmLTgiPz4KPHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCAyNTYgMjU2IiB4bWw6c3BhY2U9InByZXNlcnZlIj4KPHBhdGggZmlsbD0iI0EwQTVBQSIgZD0iTTIxNi4wNyAxNDIuMDQ2IDI1Ni40NDEgMTEzaC00OS44OTRsLTE1LjM1My00Ny4zMDRMMTc1Ljg0MSAxMTNoLTQ5Ljg5NGw0MC4zNzEgMjkuMDQ2LTE1LjM1OSA0Ny4xNzUgNDAuMjM1LTI5LjMxNyA0MC4yMzYgMjkuMzU0em0tOTUuNzk5LTE4LjEzNGMwLTMuNjI0LS41NTgtNy45MTItMS4zOTQtMTAuOTEySDYydjIzaDMyLjYyMWMtMS4zMDYgNi4zNDktNC4zODkgMTEuNzE5LTguODA1IDE1LjY4OGwtNC4wNDMgMy4xNDljLTUuNDc3IDMuMzU3LTEyLjMyNyA1LjE1My0yMC4wNTEgNS4xNTMtMTYuMTA3IDAtMjkuNjkyLTEwLjQxNS0zNC40MzYtMjQuODk5LTEuMTY4LTMuNTY4LTEuODA5LTcuMzc4LTEuODA5LTExLjM0NSAwLTMuOTg2LjY0Ny03LjgxNCAxLjgyNi0xMS4zOTYgNC43NTktMTQuNDU4IDE4LjMzMS0yNC44NDkgMzQuNDE5LTI0Ljg0OSA4LjY0MyAwIDE2LjQ1IDMuMDY3IDIyLjU4MyA4LjA4NWwxNy44NDQtMTcuODQ0Yy0xMC44NzMtOS40NzktMjQuODE0LTE1LjMzNC00MC40MjctMTUuMzM0LTI0LjI0IDAtNDUuMDcxIDEzLjg4Mi01NS4wNDggMzQuMTY2LTQuMDIzIDguMTgtNi4yODkgMTcuMzk2LTYuMjg5IDI3LjE3MSAwIDkuNzY4IDIuMjYzIDE4Ljk3OSA2LjI4MSAyNy4xNTQgOS45NzQgMjAuMjk0IDMwLjgxIDM0LjE4MyA1NS4wNTYgMzQuMTgzIDEzLjkxNyAwIDI3LjI0Ni00LjYxIDM3LjY2OS0xMy4yNzYuMDEtLjAwNy4wMjItLjAxMi4wMzEtLjAxOSA0LjQyMy0zLjMyNSA4LjUyOS04Ljk1OSA4LjUyOS04Ljk1OSA3LjYyOS05LjkyNSAxMi4zMi0yMi45MzIgMTIuMzItMzguOTE2eiIvPgo8L3N2Zz4=';
			$pages = [['add_menu_page', __('Google Reviews', 'g-business-reviews-rating'), __('Google Reviews', 'g-business-reviews-rating'), 'edit_published_posts', self::PLUGIN_ALIAS, [$this, 'settings'], $icon, 51]];
		}

		foreach ($pages as $p)
		{
			$function = $p[0];
			array_shift($p);
			call_user_func_array($function, $p);
			continue;
		}

		return TRUE;
	}

	/* Check if the plugin is showing in the Dashboard */

	private function current(): bool
	{
		if (!current_user_can('edit_published_posts', self::PLUGIN_ALIAS))
		{
			return FALSE;
		}

		if (isset($_GET['page']) && is_string($_GET['page']) && preg_match('/^(?:google[\s_-]?(?:my[\s_-]?)?business|gmb)[\s_-]?reviews?[\s_-]?rating(?:[\s_-]?settings?)?$/i', $_GET['page']))
		{
			return TRUE;
		}

		$page = get_current_screen();

		return (isset($page->id) && $page->id == 'dashboard');
	}

	/* Introduces a first install once, whoever opens the settings page first */

	public function welcome(): bool
	{
		if ($this->get_option('welcome', NULL) != NULL || is_network_admin())
		{
			return FALSE;
		}

		$initial_version = $this->get_option('initial_version', NULL);
		$this->update_option('welcome', time(), 'no');

		return (is_string($initial_version) && version_compare($initial_version, '6.0', '>='));
	}


	/* Populated on demand for the widget form */

	private function set_languages(): bool
	{
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

		return TRUE;
	}

	/* Populated on demand for the widget form */

	private function set_reviews_themes(): bool
	{
		$this->reviews_themes = [
			'light' => __('Light Background', 'g-business-reviews-rating'),
			'light fonts' => __('Light Background with Fonts', 'g-business-reviews-rating'),
			'light tile' => __('Tiled, Light Background', 'g-business-reviews-rating'),
			'light fonts tile' => __('Tiled, Light Background with Fonts', 'g-business-reviews-rating'),
			'light center' => __('Centered, Light Background', 'g-business-reviews-rating'),
			'light center fonts' => __('Centered, Light Background with Fonts', 'g-business-reviews-rating'),
			'light center tile' => __('Centered, Tiled, Light Background', 'g-business-reviews-rating'),
			'light center fonts tile' => __('Centered, Tiled, Light Background with Fonts', 'g-business-reviews-rating'),
			'light narrow' => __('Narrow, Light Background', 'g-business-reviews-rating'),
			'light narrow fonts' => __('Narrow, Light Background with Fonts', 'g-business-reviews-rating'),
			'light narrow tile' => __('Narrow, Tiled, Light Background', 'g-business-reviews-rating'),
			'light narrow fonts tile' => __('Narrow, Tiled, Light Background with Fonts', 'g-business-reviews-rating'),
			'light center narrow' => __('Narrow, Centered, Light Background', 'g-business-reviews-rating'),
			'light center narrow fonts' => __('Narrow, Centered, Light Background with Fonts', 'g-business-reviews-rating'),
			'light center narrow tile' => __('Narrow, Centered, Tiled, Light Background', 'g-business-reviews-rating'),
			'light center narrow fonts tile' => __('Narrow, Centered, Tiled, Light Background with Fonts', 'g-business-reviews-rating'),
			'dark' => __('Dark Background', 'g-business-reviews-rating'),
			'dark fonts' => __('Dark Background with Fonts', 'g-business-reviews-rating'),
			'dark tile' => __('Tiled, Dark Background', 'g-business-reviews-rating'),
			'dark fonts tile' => __('Tiled, Dark Background with Fonts', 'g-business-reviews-rating'),
			'dark center' => __('Centered, Dark Background', 'g-business-reviews-rating'),
			'dark center fonts' => __('Centered, Dark Background with Fonts', 'g-business-reviews-rating'),
			'dark center tile' => __('Centered, Tiled, Dark Background', 'g-business-reviews-rating'),
			'dark center fonts tile' => __('Centered, Tiled, Dark Background with Fonts', 'g-business-reviews-rating'),
			'dark narrow' => __('Narrow, Dark Background', 'g-business-reviews-rating'),
			'dark narrow fonts' => __('Narrow, Dark Background with Fonts', 'g-business-reviews-rating'),
			'dark narrow tile' => __('Narrow, Tiled, Dark Background', 'g-business-reviews-rating'),
			'dark narrow fonts tile' => __('Narrow, Tiled, Dark Background with Fonts', 'g-business-reviews-rating'),
			'dark center narrow' => __('Narrow, Centered, Dark Background', 'g-business-reviews-rating'),
			'dark center narrow fonts' => __('Narrow, Centered, Dark Background with Fonts', 'g-business-reviews-rating'),
			'dark center narrow tile' => __('Narrow, Centered, Tiled, Dark Background', 'g-business-reviews-rating'),
			'dark center narrow fonts tile' => __('Narrow, Centered, Tiled, Dark Background with Fonts', 'g-business-reviews-rating'),
			'light bubble' => __('Bubble Outline, Light Background', 'g-business-reviews-rating'),
			'light bubble fonts' => __('Bubble Outline, Light Background with Fonts', 'g-business-reviews-rating'),
			'light bubble tile' => __('Bubble Outline, Tiled, Light Background', 'g-business-reviews-rating'),
			'light bubble fonts tile' => __('Bubble Outline, Tiled, Light Background with Fonts', 'g-business-reviews-rating'),
			'light bubble fill' => __('Bubble Filled, Light Background', 'g-business-reviews-rating'),
			'light bubble fill fonts' => __('Bubble Filled, Light Background with Fonts', 'g-business-reviews-rating'),
			'light bubble fill tile' => __('Bubble Filled, Tiled, Light Background', 'g-business-reviews-rating'),
			'light bubble fill fonts tile' => __('Bubble Filled, Tiled, Light Background with Fonts', 'g-business-reviews-rating'),
			'light bubble center' => __('Centered, Bubble Outline, Light Background', 'g-business-reviews-rating'),
			'light bubble center fonts' => __('Centered, Bubble Outline, Light Background with Fonts', 'g-business-reviews-rating'),
			'light bubble center tile' => __('Centered, Bubble Outline, Tiled, Light Background', 'g-business-reviews-rating'),
			'light bubble center fonts tile' => __('Centered, Bubble Outline, Tiled, Light Background with Fonts', 'g-business-reviews-rating'),
			'light bubble fill center' => __('Centered, Bubble Filled, Light Background', 'g-business-reviews-rating'),
			'light bubble fill center fonts' => __('Centered, Bubble Filled, Light Background with Fonts', 'g-business-reviews-rating'),
			'light bubble fill center tile' => __('Centered, Bubble Filled, Tiled, Light Background', 'g-business-reviews-rating'),
			'light bubble fill center fonts tile' => __('Centered, Bubble Filled, Tiled, Light Background with Fonts', 'g-business-reviews-rating'),
			'light bubble narrow' => __('Narrow, Bubble Outline, Light Background', 'g-business-reviews-rating'),
			'light bubble narrow fonts' => __('Narrow, Bubble Outline, Light Background with Fonts', 'g-business-reviews-rating'),
			'light bubble narrow tile' => __('Narrow, Bubble Outline, Tiled, Light Background', 'g-business-reviews-rating'),
			'light bubble narrow fonts tile' => __('Narrow, Bubble Outline, Tiled, Light Background with Fonts', 'g-business-reviews-rating'),
			'light bubble fill narrow' => __('Narrow, Bubble Filled, Light Background', 'g-business-reviews-rating'),
			'light bubble fill narrow fonts' => __('Narrow, Bubble Filled, Light Background with Fonts', 'g-business-reviews-rating'),
			'light bubble fill narrow tile' => __('Narrow, Bubble Filled, Tiled, Light Background', 'g-business-reviews-rating'),
			'light bubble fill narrow fonts tile' => __('Narrow, Bubble Filled, Tiled, Light Background with Fonts', 'g-business-reviews-rating'),
			'light bubble center narrow' => __('Narrow, Centered, Bubble Outline, Light Background', 'g-business-reviews-rating'),
			'light bubble center narrow fonts' => __('Narrow, Centered, Bubble Outline, Light Background with Fonts', 'g-business-reviews-rating'),
			'light bubble center narrow tile' => __('Narrow, Centered, Bubble Outline, Tiled, Light Background', 'g-business-reviews-rating'),
			'light bubble center narrow fonts tile' => __('Narrow, Centered, Bubble Outline, Tiled, Light Background with Fonts', 'g-business-reviews-rating'),
			'light bubble fill center narrow' => __('Narrow, Centered, Bubble Filled, Light Background', 'g-business-reviews-rating'),
			'light bubble fill center narrow fonts' => __('Narrow, Centered, Bubble Filled, Light Background with Fonts', 'g-business-reviews-rating'),
			'light bubble fill center narrow tile' => __('Narrow, Centered, Bubble Filled, Tiled, Light Background', 'g-business-reviews-rating'),
			'light bubble fill center narrow fonts tile' => __('Narrow, Centered, Bubble Filled, Tiled, Light Background with Fonts', 'g-business-reviews-rating'),
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
			'badge tiny dark fonts' => __('Tiny Badge, Dark Background with Fonts', 'g-business-reviews-rating'),
			'columns two' => __('Two Columns, Light Background', 'g-business-reviews-rating'),
			'columns two tile' => __('Two Columns, Tiled, Light Background', 'g-business-reviews-rating'),
			'columns two bubble' => __('Two Columns, Bubble Outline, Light Background', 'g-business-reviews-rating'),
			'columns two bubble tile' => __('Two Columns, Bubble Outline, Tiled, Light Background', 'g-business-reviews-rating'),
			'columns two bubble fill' => __('Two Columns, Bubble Filled, Light Background', 'g-business-reviews-rating'),
			'columns two bubble fill tile' => __('Two Columns, Bubble Filled, Tiled, Light Background', 'g-business-reviews-rating'),
			'columns two center' => __('Two Columns, Centered, Light Background', 'g-business-reviews-rating'),
			'columns two center tile' => __('Two Columns, Centered, Light Background, Tiled', 'g-business-reviews-rating'),
			'columns two bubble center' => __('Two Columns, Bubble Outline, Centered, Light Background', 'g-business-reviews-rating'),
			'columns two bubble tile center' => __('Two Columns, Bubble Outline, Tiled, Centered, Light Background', 'g-business-reviews-rating'),
			'columns two bubble fill center' => __('Two Columns, Bubble Filled, Centered, Light Background', 'g-business-reviews-rating'),
			'columns two bubble fill tile center' => __('Two Columns, Bubble Filled, Tiled, Centered, Light Background', 'g-business-reviews-rating'),
			'columns two fonts' => __('Two Columns, Light Background with Fonts', 'g-business-reviews-rating'),
			'columns two fonts tile' => __('Two Columns, Tiled, Light Background with Fonts', 'g-business-reviews-rating'),
			'columns two fonts bubble' => __('Two Columns, Bubble Outline, Light Background with Fonts', 'g-business-reviews-rating'),
			'columns two fonts bubble tile' => __('Two Columns, Bubble Outline, Tiled, Light Background with Fonts', 'g-business-reviews-rating'),
			'columns two fonts bubble fill' => __('Two Columns, Bubble Filled, Light Background with Fonts', 'g-business-reviews-rating'),
			'columns two fonts bubble fill tile' => __('Two Columns, Bubble Filled, Tiled, Light Background with Fonts', 'g-business-reviews-rating'),
			'columns two fonts center' => __('Two Columns, Centered, Light Background with Fonts', 'g-business-reviews-rating'),
			'columns two fonts center tile' => __('Two Columns, Centered, Light Background, Tiled with Fonts', 'g-business-reviews-rating'),
			'columns two fonts bubble center' => __('Two Columns, Bubble Outline, Centered, Light Background with Fonts', 'g-business-reviews-rating'),
			'columns two fonts bubble tile center' => __('Two Columns, Bubble Outline, Tiled, Centered, Light Background with Fonts', 'g-business-reviews-rating'),
			'columns two fonts bubble fill center' => __('Two Columns, Bubble Filled, Centered, Light Background with Fonts', 'g-business-reviews-rating'),
			'columns two fonts bubble fill tile center' => __('Two Columns, Bubble Filled, Tiled, Centered, Light Background with Fonts', 'g-business-reviews-rating'),
			'columns two dark' => __('Two Columns, Dark Background', 'g-business-reviews-rating'),
			'columns two dark tile' => __('Two Columns, Tiled, Dark Background', 'g-business-reviews-rating'),
			'columns two dark bubble' => __('Two Columns, Bubble Outline, Dark Background', 'g-business-reviews-rating'),
			'columns two dark bubble tile' => __('Two Columns, Bubble Outline, Tiled, Dark Background', 'g-business-reviews-rating'),
			'columns two dark bubble fill' => __('Two Columns, Bubble Filled, Dark Background', 'g-business-reviews-rating'),
			'columns two dark bubble fill tile' => __('Two Columns, Bubble Filled, Tiled, Dark Background', 'g-business-reviews-rating'),
			'columns two dark center' => __('Two Columns, Centered, Dark Background', 'g-business-reviews-rating'),
			'columns two dark center tile' => __('Two Columns, Centered, Dark Background, Tiled', 'g-business-reviews-rating'),
			'columns two dark bubble center' => __('Two Columns, Bubble Outline, Centered, Dark Background', 'g-business-reviews-rating'),
			'columns two dark bubble tile center' => __('Two Columns, Bubble Outline, Tiled, Centered, Dark Background', 'g-business-reviews-rating'),
			'columns two dark bubble fill center' => __('Two Columns, Bubble Filled, Centered, Dark Background', 'g-business-reviews-rating'),
			'columns two dark bubble fill tile center' => __('Two Columns, Bubble Filled, Tiled, Centered, Dark Background', 'g-business-reviews-rating'),
			'columns two dark fonts' => __('Two Columns, Dark Background with Fonts', 'g-business-reviews-rating'),
			'columns two dark fonts tile' => __('Two Columns, Tiled, Dark Background with Fonts', 'g-business-reviews-rating'),
			'columns two dark fonts bubble' => __('Two Columns, Bubble Outline, Dark Background with Fonts', 'g-business-reviews-rating'),
			'columns two dark fonts bubble tile' => __('Two Columns, Bubble Outline, Tiled, Dark Background with Fonts', 'g-business-reviews-rating'),
			'columns two dark fonts bubble fill' => __('Two Columns, Bubble Filled, Dark Background with Fonts', 'g-business-reviews-rating'),
			'columns two dark fonts bubble fill tile' => __('Two Columns, Bubble Filled, Tiled, Dark Background with Fonts', 'g-business-reviews-rating'),
			'columns two dark fonts center' => __('Two Columns, Centered, Dark Background with Fonts', 'g-business-reviews-rating'),
			'columns two dark fonts center tile' => __('Two Columns, Centered, Dark Background, Tiled with Fonts', 'g-business-reviews-rating'),
			'columns two dark fonts bubble center' => __('Two Columns, Bubble Outline, Centered, Dark Background with Fonts', 'g-business-reviews-rating'),
			'columns two dark fonts bubble tile center' => __('Two Columns, Bubble Outline, Tiled, Centered, Dark Background with Fonts', 'g-business-reviews-rating'),
			'columns two dark fonts bubble fill center' => __('Two Columns, Bubble Filled, Centered, Dark Background with Fonts', 'g-business-reviews-rating'),
			'columns two dark fonts bubble fill tile center' => __('Two Columns, Bubble Filled, Tiled, Centered, Dark Background with Fonts', 'g-business-reviews-rating'),
			'columns three' => __('Three Columns, Light Background', 'g-business-reviews-rating'),
			'columns three tile' => __('Three Columns, Tiled, Light Background', 'g-business-reviews-rating'),
			'columns three bubble' => __('Three Columns, Bubble Outline, Light Background', 'g-business-reviews-rating'),
			'columns three bubble tile' => __('Three Columns, Bubble Outline, Tiled, Light Background', 'g-business-reviews-rating'),
			'columns three bubble fill' => __('Three Columns, Bubble Filled, Light Background', 'g-business-reviews-rating'),
			'columns three bubble fill tile' => __('Three Columns, Bubble Filled, Tiled, Light Background', 'g-business-reviews-rating'),
			'columns three center' => __('Three Columns, Centered, Light Background', 'g-business-reviews-rating'),
			'columns three center tile' => __('Three Columns, Centered, Light Background, Tiled', 'g-business-reviews-rating'),
			'columns three bubble center' => __('Three Columns, Bubble Outline, Centered, Light Background', 'g-business-reviews-rating'),
			'columns three bubble tile center' => __('Three Columns, Bubble Outline, Tiled, Centered, Light Background', 'g-business-reviews-rating'),
			'columns three bubble fill center' => __('Three Columns, Bubble Filled, Centered, Light Background', 'g-business-reviews-rating'),
			'columns three bubble fill tile center' => __('Three Columns, Bubble Filled, Tiled, Centered, Light Background', 'g-business-reviews-rating'),
			'columns three fonts' => __('Three Columns, Light Background with Fonts', 'g-business-reviews-rating'),
			'columns three fonts tile' => __('Three Columns, Tiled, Light Background with Fonts', 'g-business-reviews-rating'),
			'columns three fonts bubble' => __('Three Columns, Bubble Outline, Light Background with Fonts', 'g-business-reviews-rating'),
			'columns three fonts bubble tile' => __('Three Columns, Bubble Outline, Tiled, Light Background with Fonts', 'g-business-reviews-rating'),
			'columns three fonts bubble fill' => __('Three Columns, Bubble Filled, Light Background with Fonts', 'g-business-reviews-rating'),
			'columns three fonts bubble fill tile' => __('Three Columns, Bubble Filled, Tiled, Light Background with Fonts', 'g-business-reviews-rating'),
			'columns three fonts center' => __('Three Columns, Centered, Light Background with Fonts', 'g-business-reviews-rating'),
			'columns three fonts center tile' => __('Three Columns, Centered, Light Background, Tiled with Fonts', 'g-business-reviews-rating'),
			'columns three fonts bubble center' => __('Three Columns, Bubble Outline, Centered, Light Background with Fonts', 'g-business-reviews-rating'),
			'columns three fonts bubble tile center' => __('Three Columns, Bubble Outline, Tiled, Centered, Light Background with Fonts', 'g-business-reviews-rating'),
			'columns three fonts bubble fill center' => __('Three Columns, Bubble Filled, Centered, Light Background with Fonts', 'g-business-reviews-rating'),
			'columns three fonts bubble fill tile center' => __('Three Columns, Bubble Filled, Tiled, Centered, Light Background with Fonts', 'g-business-reviews-rating'),
			'columns three dark' => __('Three Columns, Dark Background', 'g-business-reviews-rating'),
			'columns three dark tile' => __('Three Columns, Tiled, Dark Background', 'g-business-reviews-rating'),
			'columns three dark bubble' => __('Three Columns, Bubble Outline, Dark Background', 'g-business-reviews-rating'),
			'columns three dark bubble tile' => __('Three Columns, Bubble Outline, Tiled, Dark Background', 'g-business-reviews-rating'),
			'columns three dark bubble fill' => __('Three Columns, Bubble Filled, Dark Background', 'g-business-reviews-rating'),
			'columns three dark bubble fill tile' => __('Three Columns, Bubble Filled, Tiled, Dark Background', 'g-business-reviews-rating'),
			'columns three dark center' => __('Three Columns, Centered, Dark Background', 'g-business-reviews-rating'),
			'columns three dark center tile' => __('Three Columns, Centered, Dark Background, Tiled', 'g-business-reviews-rating'),
			'columns three dark bubble center' => __('Three Columns, Bubble Outline, Centered, Dark Background', 'g-business-reviews-rating'),
			'columns three dark bubble tile center' => __('Three Columns, Bubble Outline, Tiled, Centered, Dark Background', 'g-business-reviews-rating'),
			'columns three dark bubble fill center' => __('Three Columns, Bubble Filled, Centered, Dark Background', 'g-business-reviews-rating'),
			'columns three dark bubble fill tile center' => __('Three Columns, Bubble Filled, Tiled, Centered, Dark Background', 'g-business-reviews-rating'),
			'columns three dark fonts' => __('Three Columns, Dark Background with Fonts', 'g-business-reviews-rating'),
			'columns three dark fonts tile' => __('Three Columns, Tiled, Dark Background with Fonts', 'g-business-reviews-rating'),
			'columns three dark fonts bubble' => __('Three Columns, Bubble Outline, Dark Background with Fonts', 'g-business-reviews-rating'),
			'columns three dark fonts bubble tile' => __('Three Columns, Bubble Outline, Tiled, Dark Background with Fonts', 'g-business-reviews-rating'),
			'columns three dark fonts bubble fill' => __('Three Columns, Bubble Filled, Dark Background with Fonts', 'g-business-reviews-rating'),
			'columns three dark fonts bubble fill tile' => __('Three Columns, Bubble Filled, Tiled, Dark Background with Fonts', 'g-business-reviews-rating'),
			'columns three dark fonts center' => __('Three Columns, Centered, Dark Background with Fonts', 'g-business-reviews-rating'),
			'columns three dark fonts center tile' => __('Three Columns, Centered, Dark Background, Tiled with Fonts', 'g-business-reviews-rating'),
			'columns three dark fonts bubble center' => __('Three Columns, Bubble Outline, Centered, Dark Background with Fonts', 'g-business-reviews-rating'),
			'columns three dark fonts bubble tile center' => __('Three Columns, Bubble Outline, Tiled, Centered, Dark Background with Fonts', 'g-business-reviews-rating'),
			'columns three dark fonts bubble fill center' => __('Three Columns, Bubble Filled, Centered, Dark Background with Fonts', 'g-business-reviews-rating'),
			'columns three dark fonts bubble fill tile center' => __('Three Columns, Bubble Filled, Tiled, Centered, Dark Background with Fonts', 'g-business-reviews-rating'),
			'columns four' => __('Four Columns, Light Background', 'g-business-reviews-rating'),
			'columns four tile' => __('Four Columns, Tiled, Light Background', 'g-business-reviews-rating'),
			'columns four bubble' => __('Four Columns, Bubble Outline, Light Background', 'g-business-reviews-rating'),
			'columns four bubble tile' => __('Four Columns, Bubble Outline, Tiled, Light Background', 'g-business-reviews-rating'),
			'columns four bubble fill' => __('Four Columns, Bubble Filled, Light Background', 'g-business-reviews-rating'),
			'columns four bubble fill tile' => __('Four Columns, Bubble Filled, Tiled, Light Background', 'g-business-reviews-rating'),
			'columns four center' => __('Four Columns, Centered, Light Background', 'g-business-reviews-rating'),
			'columns four center tile' => __('Four Columns, Centered, Light Background, Tiled', 'g-business-reviews-rating'),
			'columns four bubble center' => __('Four Columns, Bubble Outline, Centered, Light Background', 'g-business-reviews-rating'),
			'columns four bubble tile center' => __('Four Columns, Bubble Outline, Tiled, Centered, Light Background', 'g-business-reviews-rating'),
			'columns four bubble fill center' => __('Four Columns, Bubble Filled, Centered, Light Background', 'g-business-reviews-rating'),
			'columns four bubble fill tile center' => __('Four Columns, Bubble Filled, Tiled, Centered, Light Background', 'g-business-reviews-rating'),
			'columns four fonts' => __('Four Columns, Light Background with Fonts', 'g-business-reviews-rating'),
			'columns four fonts tile' => __('Four Columns, Tiled, Light Background with Fonts', 'g-business-reviews-rating'),
			'columns four fonts bubble' => __('Four Columns, Bubble Outline, Light Background with Fonts', 'g-business-reviews-rating'),
			'columns four fonts bubble tile' => __('Four Columns, Bubble Outline, Tiled, Light Background with Fonts', 'g-business-reviews-rating'),
			'columns four fonts bubble fill' => __('Four Columns, Bubble Filled, Light Background with Fonts', 'g-business-reviews-rating'),
			'columns four fonts bubble fill tile' => __('Four Columns, Bubble Filled, Tiled, Light Background with Fonts', 'g-business-reviews-rating'),
			'columns four fonts center' => __('Four Columns, Centered, Light Background with Fonts', 'g-business-reviews-rating'),
			'columns four fonts center tile' => __('Four Columns, Centered, Light Background, Tiled with Fonts', 'g-business-reviews-rating'),
			'columns four fonts bubble center' => __('Four Columns, Bubble Outline, Centered, Light Background with Fonts', 'g-business-reviews-rating'),
			'columns four fonts bubble tile center' => __('Four Columns, Bubble Outline, Tiled, Centered, Light Background with Fonts', 'g-business-reviews-rating'),
			'columns four fonts bubble fill center' => __('Four Columns, Bubble Filled, Centered, Light Background with Fonts', 'g-business-reviews-rating'),
			'columns four fonts bubble fill tile center' => __('Four Columns, Bubble Filled, Tiled, Centered, Light Background with Fonts', 'g-business-reviews-rating'),
			'columns four dark' => __('Four Columns, Dark Background', 'g-business-reviews-rating'),
			'columns four dark tile' => __('Four Columns, Tiled, Dark Background', 'g-business-reviews-rating'),
			'columns four dark bubble' => __('Four Columns, Bubble Outline, Dark Background', 'g-business-reviews-rating'),
			'columns four dark bubble tile' => __('Four Columns, Bubble Outline, Tiled, Dark Background', 'g-business-reviews-rating'),
			'columns four dark bubble fill' => __('Four Columns, Bubble Filled, Dark Background', 'g-business-reviews-rating'),
			'columns four dark bubble fill tile' => __('Four Columns, Bubble Filled, Tiled, Dark Background', 'g-business-reviews-rating'),
			'columns four dark center' => __('Four Columns, Centered, Dark Background', 'g-business-reviews-rating'),
			'columns four dark center tile' => __('Four Columns, Centered, Dark Background, Tiled', 'g-business-reviews-rating'),
			'columns four dark bubble center' => __('Four Columns, Bubble Outline, Centered, Dark Background', 'g-business-reviews-rating'),
			'columns four dark bubble tile center' => __('Four Columns, Bubble Outline, Tiled, Centered, Dark Background', 'g-business-reviews-rating'),
			'columns four dark bubble fill center' => __('Four Columns, Bubble Filled, Centered, Dark Background', 'g-business-reviews-rating'),
			'columns four dark bubble fill tile center' => __('Four Columns, Bubble Filled, Tiled, Centered, Dark Background', 'g-business-reviews-rating'),
			'columns four dark fonts' => __('Four Columns, Dark Background with Fonts', 'g-business-reviews-rating'),
			'columns four dark fonts tile' => __('Four Columns, Tiled, Dark Background with Fonts', 'g-business-reviews-rating'),
			'columns four dark fonts bubble' => __('Four Columns, Bubble Outline, Dark Background with Fonts', 'g-business-reviews-rating'),
			'columns four dark fonts bubble tile' => __('Four Columns, Bubble Outline, Tiled, Dark Background with Fonts', 'g-business-reviews-rating'),
			'columns four dark fonts bubble fill' => __('Four Columns, Bubble Filled, Dark Background with Fonts', 'g-business-reviews-rating'),
			'columns four dark fonts bubble fill tile' => __('Four Columns, Bubble Filled, Tiled, Dark Background with Fonts', 'g-business-reviews-rating'),
			'columns four dark fonts center' => __('Four Columns, Centered, Dark Background with Fonts', 'g-business-reviews-rating'),
			'columns four dark fonts center tile' => __('Four Columns, Centered, Dark Background, Tiled with Fonts', 'g-business-reviews-rating'),
			'columns four dark fonts bubble center' => __('Four Columns, Bubble Outline, Centered, Dark Background with Fonts', 'g-business-reviews-rating'),
			'columns four dark fonts bubble tile center' => __('Four Columns, Bubble Outline, Tiled, Centered, Dark Background with Fonts', 'g-business-reviews-rating'),
			'columns four dark fonts bubble fill center' => __('Four Columns, Bubble Filled, Centered, Dark Background with Fonts', 'g-business-reviews-rating'),
			'columns four dark fonts bubble fill tile center' => __('Four Columns, Bubble Filled, Tiled, Centered, Dark Background with Fonts', 'g-business-reviews-rating')
		];

		return TRUE;
	}
	/* Set and process settings in the Dashboard */

	public function settings(bool $set_languages = FALSE): bool
	{
		if (!$set_languages && !$this->editor && !$this->administrator)
		{
			wp_die(__('You do not have sufficient permission to access this page.', 'g-business-reviews-rating'));
			return FALSE;
		}

		$this->set_languages();

		if ($set_languages)
		{
			return TRUE;
		}
		
		$this->set_reviews_themes();
		$this->business_types = [
			'AnimalShelter' => __('Animal Shelter', 'g-business-reviews-rating'),
			'ArchiveOrganization' => __('Archive Organization', 'g-business-reviews-rating'),
			'AutomotiveBusiness' => __('Automotive Business', 'g-business-reviews-rating'),
			'ChildCare' => __('Child Care', 'g-business-reviews-rating'),
			'Dentist' => __('Dentist', 'g-business-reviews-rating'),
			'DryCleaningOrLaundry' => __('Dry Cleaning or Laundry', 'g-business-reviews-rating'),
			'EmergencyService' => __('Emergency Service', 'g-business-reviews-rating'),
			'EmploymentAgency' => __('Employment Agency', 'g-business-reviews-rating'),
			'EntertainmentBusiness' => __('Entertainment Business', 'g-business-reviews-rating'),
			'FinancialService' => __('Financial Service', 'g-business-reviews-rating'),
			'FoodEstablishment' => __('Food Establishment', 'g-business-reviews-rating'),
			'GovernmentOffice' => __('Government Office', 'g-business-reviews-rating'),
			'HealthAndBeautyBusiness' => __('Health and Beauty Business', 'g-business-reviews-rating'),
			'HomeAndConstructionBusiness' => __('Home and Construction Business', 'g-business-reviews-rating'),
			'InternetCafe' => __('Internet Café', 'g-business-reviews-rating'),
			'LegalService' => __('Legal Service', 'g-business-reviews-rating'),
			'Library' => __('Library', 'g-business-reviews-rating'),
			'LodgingBusiness' => __('Lodging Business', 'g-business-reviews-rating'),
			'MedicalBusiness' => __('Medical Business', 'g-business-reviews-rating'),
			'ProfessionalService' => __('Professional Service', 'g-business-reviews-rating'),
			'RadioStation' => __('Radio Station', 'g-business-reviews-rating'),
			'RealEstateAgent' => __('Real Estate Agent', 'g-business-reviews-rating'),
			'RecyclingCenter' => __('Recycling Center', 'g-business-reviews-rating'),
			'SelfStorage' => __('Self Storage', 'g-business-reviews-rating'),
			'ShoppingCenter' => __('Shopping Center', 'g-business-reviews-rating'),
			'SportsActivityLocation' => __('Sports Activity Location', 'g-business-reviews-rating'),
			'Store' => __('Store', 'g-business-reviews-rating'),
			'TelevisionStation' => __('Television Station', 'g-business-reviews-rating'),
			'TouristInformationCenter' => __('Tourist Information Center', 'g-business-reviews-rating'),
			'TravelAgency' => __('Travel Agency', 'g-business-reviews-rating')
		];
		$this->price_ranges = [
			1 => [
					'name' => __('Inexpensive $', 'g-business-reviews-rating'),
					'symbol' => '$'
				],
			2 => [
					'name' => __('Moderate $$', 'g-business-reviews-rating'),
					'symbol' => str_repeat('$', 2)
				],
			3 => [
					'name' => __('Expensive $$$', 'g-business-reviews-rating'),
					'symbol' => str_repeat('$', 3)
				],
			4 => [
					'name' => __('Very Expensive $$$$', 'g-business-reviews-rating'),
					'symbol' => str_repeat('$', 4)
				]
		];

		$legacy_section = get_option(self::OPTION_PREFIX . 'section', '');

		if (is_string($legacy_section) && mb_strlen($legacy_section) >= 1)
		{
			$existing_user_section = get_user_meta(get_current_user_id(), self::OPTION_PREFIX . 'section', TRUE);

			if (!is_string($existing_user_section) || mb_strlen($existing_user_section) < 1)
			{
				update_user_meta(get_current_user_id(), self::OPTION_PREFIX . 'section', $legacy_section);
			}

			delete_option(self::OPTION_PREFIX . 'section');
		}

		$this->section = get_user_meta(get_current_user_id(), self::OPTION_PREFIX . 'section', TRUE);

		if ($this->section == NULL && $this->welcome())
		{
			$this->section = 'welcome';
			update_user_meta(get_current_user_id(), self::OPTION_PREFIX . 'section', $this->section);
		}

		$this->places = $this->get_array_option('places');
		$this->show_reviews = (!is_numeric($this->get_option('limit')) || is_numeric($this->get_option('limit')) && $this->get_option('limit') > 0);
		$this->count_reviews_all = $this->reviews_count();
		$this->count_reviews_active = $this->reviews_count(NULL, TRUE);
		$color_scheme = $this->color_scheme();

		include(plugin_dir_path(__FILE__) . 'templates/settings.php');

		return TRUE;
	}
	

	/* Only this plugin’s notices belong on its own screen */

	public function notices_clear(): void
	{
		if (!isset($_GET['page']) || !is_string($_GET['page'])
			|| !preg_match('/^(?:google[\s_-]?(?:my[\s_-]?)?business|gmb)[\s_-]?reviews?[\s_-]?rating(?:[\s_-]?settings?)?$/i', $_GET['page']))
		{
			return;
		}

		remove_all_actions('admin_notices');
		remove_all_actions('all_admin_notices');
		add_action('admin_notices', [$this, 'notices']);
	}

	/* Handle Dashboard notices */

	public function notices(): void
	{
		if (!current_user_can('manage_options', self::PLUGIN_ALIAS) || !$this->current())
		{
			return;
		}
		
		$html = '';
		
		if ($this->get_option('api_key') != NULL && $this->get_option('place_id') != NULL)
		{
			$this->set_data();

			if (!isset($this->data['status']) || (is_numeric($this->data['status']) && $this->data['status'] >= 200 && $this->data['status'] < 300 || is_string($this->data['status']) && preg_match('/^OK$/i', $this->data['status'])))
			{
				$html = '';
			}
			elseif (is_string($this->data['status']) && preg_match('/^REQUEST[\s_-]?DENIED$/i', $this->data['status']))
			{
				$html = '<div id="google-business-reviews-rating-settings-message" class="notice notice-error invisible is-dismissible">
	<p><strong>' . esc_html__('Error:', 'g-business-reviews-rating') . '</strong> '
				/* translators: %1$s: opening anchor tag, %2$s: closing anchor tag */
				. sprintf(esc_html__('Your Google API Key is not valid for this request and permission is denied. Please check your Google %1$sAPI Key%2$s.', 'g-business-reviews-rating'), '<a href="https://developers.google.com/maps/documentation/javascript/get-api-key" target="_blank">', '</a>') . '</p>
</div>
';
			}
			elseif (is_string($this->data['status']) && preg_match('/^INVALID[\s_-]?REQUEST$/i', $this->data['status']))
			{
				$html = '<div id="google-business-reviews-rating-settings-message" class="notice notice-error invisible is-dismissible">
	<p>';
				if (isset($this->data['error_message']) && is_string($this->data['error_message']) && preg_match('/[Uu]nsupported\s+field\s+name|formattedAddress/', $this->data['error_message']))
				{
					$html .= '<strong>' . esc_html__('Error:', 'g-business-reviews-rating') . '</strong> ';
					/* translators: %1$s: opening anchor for Places API link, %2$s: closing anchor, %3$s: opening anchor for API credentials link, %4$s: closing anchor */
					$html .= sprintf(esc_html__('Please ensure %1$sPlaces API (New)%2$s is enabled and set as an API Restriction. Please check your %3$sAPI Key Credentials%4$s.', 'g-business-reviews-rating'), '<a href="https://console.cloud.google.com/apis/library/places.googleapis.com?invt=Ab25rQ" target="_blank">', '</a>', '<a href="https://console.cloud.google.com/apis/credentials/" target="_blank">', '</a>') . '</p>';
				}
				else
				{
					$html .= '<strong>' . esc_html__('Error:', 'g-business-reviews-rating') . '</strong> ';
					/* translators: %1$s: opening anchor tag, %2$s: closing anchor tag */
					$html .= sprintf(esc_html__('Google has returned an invalid request error. Please check your %1$sPlace ID%2$s.', 'g-business-reviews-rating'), '<a href="https://developers.google.com/places/place-id" target="_blank">', '</a>') . '</p>';
				}

				$html .= '</div>
';
			}
			elseif (is_string($this->data['status']) && preg_match('/^NOT[\s_-]?FOUND$/i', $this->data['status']))
			{
				$html = '<div id="google-business-reviews-rating-settings-message" class="notice notice-error invisible is-dismissible">
	<p><strong>' . esc_html__('Error:', 'g-business-reviews-rating') . '</strong> ';
				/* translators: %1$s: opening anchor tag, %2$s: closing anchor tag */
				$html .= sprintf(esc_html__('Google has not found data for the current Place ID. Please ensure you search for a specific business location; not a region or coordinates using the %1$sPlace ID Finder%2$s.', 'g-business-reviews-rating'), '<a href="https://developers.google.com/places/place-id" target="_blank">', '</a>') . '</p>
</div>
';
			}
			else
			{
				$html = '<div id="google-business-reviews-rating-settings-message" class="notice notice-error invisible is-dismissible">
	<p>' . ((isset($this->data['error_message'])) ? preg_replace('/\s+rel="nofollow"/i', ' target="_blank"', '<strong>' . __('Error:', 'g-business-reviews-rating') . '</strong> ' . $this->data['error_message']) : '<strong>' . esc_html__('Error:', 'g-business-reviews-rating') . '</strong> ' . esc_html__('Unknown — Please check Retrieved data to find out more information.', 'g-business-reviews-rating')) . '</p>
</div>
';
			}
		}
		
		if ($html == '')
		{
			return;
		}
		
		echo wp_kses($html, ['div' => ['id' => [], 'class' => []], 'span' => ['id' => [], 'class' => []], 'p' => ['id' => [], 'class' => []], 'a' => ['href' => [], 'target' => [], 'class' => []], 'code' => [], 'strong' => [], 'em' => []]);
	}
	

	/* Match a review row id to its array key */

	protected function get_review_key(?int $id): ?string
	{
		if ($id == NULL || empty($this->reviews))
		{
			return NULL;
		}

		foreach ($this->reviews as $key => $a)
		{
			if (isset($a['id']) && is_numeric($a['id']) && intval($a['id']) == $id)
			{
				return strval($key);
			}
		}

		return NULL;
	}

	/* Handle AJAX requests from Dashboard */

	public function ajax(): void
	{
		$ret = [];
		$allow_editor = $this->get_option('editor', TRUE);
		$this->administrator = (current_user_can('manage_options', self::PLUGIN_ALIAS));
		$this->editor = (!$this->administrator && $allow_editor && current_user_can('edit_published_posts', self::PLUGIN_ALIAS));

		if (!$this->dashboard || (!$this->editor && !$this->administrator))
		{
			echo json_encode($ret);
			wp_die();
		}
		
		$type = (isset($_POST['type']) && is_string($_POST['type'])) ? preg_replace('/[^\w_]/', '', mb_strtolower($this->sanitize_input($_POST['type']))) : NULL;
		
		if ($this->editor && !preg_match('/^(?:delete|language|remove|section|sort|status|submitted)$/', $type))
		{
			echo json_encode($ret);
			wp_die();
		}
		
		$section = (isset($_POST['section']) && is_string($_POST['section']) && !preg_match('/^(?:general|setup)$/i', $_POST['section'])) ? preg_replace('/[^\w_-]/', '', mb_strtolower($this->sanitize_input($_POST['section']))) : NULL;
		$notification_action = (isset($_POST['notification_action']) && is_string($_POST['notification_action']) && mb_strlen($_POST['notification_action']) >= 2 && mb_strlen($_POST['notification_action']) <= 255) ? mb_strtolower($this->sanitize_input($_POST['notification_action'])) : NULL;
		$review = (isset($_POST['review']) && is_numeric($_POST['review']) && intval($_POST['review']) > 0) ? intval($_POST['review']) : NULL;
		$reviews = (isset($_POST['reviews']) && is_array($_POST['reviews'])) ? array_unique(stripslashes_deep($_POST['reviews']), SORT_REGULAR) : [];
		$order = (isset($_POST['order']) && is_array($_POST['order'])) ? array_unique(stripslashes_deep($_POST['order'])) : [];
		$sort = (isset($_POST['sort']) && is_string($_POST['sort']) && is_string($section) && preg_match('/^reviews$/i', $section) && !preg_match('/^relevance(?:[_-]desc)?$/', $_POST['sort'])) ? preg_replace('/[^\w_-]/', '', mb_strtolower($this->sanitize_input($_POST['sort']))) : NULL;
		$submitted = (isset($_POST['submitted']) && is_string($_POST['submitted']) && is_string($_POST['submitted'])) ? $this->sanitize_input($_POST['submitted']) : NULL;
		$status = (isset($_POST['status']) && (is_bool($_POST['status']) && $_POST['status'] || is_string($_POST['status']) && preg_match('/^true$/i', $_POST['status'])));
		$api_key = (isset($_POST['api_key']) && is_string($_POST['api_key']) && mb_strlen($_POST['api_key']) >= 10 && mb_strlen($_POST['api_key']) <= 255) ? $this->sanitize_input($_POST['api_key']) : NULL;
		$place_id = (isset($_POST['place_id']) && is_string($_POST['place_id']) && mb_strlen($_POST['place_id']) >= 10 && mb_strlen($_POST['place_id']) <= 255) ? $this->sanitize_input($_POST['place_id']) : NULL;
		$language = (isset($_POST['language']) && is_string($_POST['language']) && mb_strlen($_POST['language']) >= 2) ? $this->sanitize_input($_POST['language']) : NULL;
		$retrieval_translate = (isset($_POST['retrieval_translate']) && (is_bool($_POST['retrieval_translate']) && $_POST['retrieval_translate'] || is_string($_POST['retrieval_translate']) && preg_match('/^(?:true|[1-9])$/i', $_POST['retrieval_translate'])));
		$notifications = (isset($_POST['notifications']) && (is_bool($_POST['notifications']) && $_POST['notifications'] || is_string($_POST['notifications']) && preg_match('/^(?:true|[1-9])$/i', $_POST['notifications'])));
		$remove_other_places = (isset($_POST['remove_other_places']) && (is_bool($_POST['remove_other_places']) && $_POST['remove_other_places'] || is_string($_POST['remove_other_places']) && preg_match('/^(?:true|[1-9])$/i', $_POST['remove_other_places'])));
		$update = (isset($_POST['update']) && is_numeric($_POST['update'])) ? intval($_POST['update']) : NULL;
		$ids = (isset($_POST['id']) && is_string($_POST['id'])) ? preg_split('/,\s*/', $this->sanitize_input($_POST['id'])) : [];
		$id = (isset($ids[0])) ? $ids[0] : NULL;
		$stylesheet = (isset($_POST['stylesheet']) && is_numeric($_POST['stylesheet']) && $_POST['stylesheet'] >= 0 && $_POST['stylesheet'] <= 2) ? intval($_POST['stylesheet']) : 1;
		$javascript = (isset($_POST['javascript']) && is_numeric($_POST['javascript']) && $_POST['javascript'] >= 0 && $_POST['javascript'] <= 2) ? intval($_POST['javascript']) : 1;
		$custom_styles = (isset($_POST['custom_styles']) && is_string($_POST['custom_styles']) && mb_strlen($_POST['custom_styles']) > 2 && !preg_match('/<\?(?:php|=)?/i', $_POST['custom_styles']) && !preg_match('/<[a-z]/i', $_POST['custom_styles']) && !preg_match('/\burl\s*\(\s*["\']?\s*https?:/i', $_POST['custom_styles'])) ? $this->sanitize_input($_POST['custom_styles']) : NULL;
		$roles_editor = (isset($_POST['roles_editor']) && (is_bool($_POST['roles_editor']) && $_POST['roles_editor'] || is_string($_POST['roles_editor']) && preg_match('/^(?:true|[1-9])$/i', $_POST['roles_editor'])));
		$import_type = (isset($_POST['import_type']) && is_string($_POST['import_type']) && preg_match('/^(?:original|translation)$/i', $_POST['import_type'])) ? $this->sanitize_input($_POST['import_type']) : NULL;
		$link = (isset($_POST['link']) && is_string($_POST['link']) && mb_strlen($_POST['link']) < 255) ? $this->sanitize_input($_POST['link']) : NULL;
		$nonce = (isset($_POST['nonce']) && is_string($_POST['nonce']) && preg_match('/^[0-9a-f]{8,128}$/i', $_POST['nonce'])) ? $this->sanitize_input($_POST['nonce']) : NULL;

		switch($type)
		{
		case 'section':
			if (!wp_verify_nonce($nonce, 'gmbrr_nonce'))
			{
				$ret = [
					'message' => esc_html__('Your session has expired, please refresh this page.', 'g-business-reviews-rating'),
					'success' => FALSE
				];
				break;
			}
			if ($this->editor)
			{
				$section = ($section == 'shortcodes' || $section == 'about' || $section == 'reviews') ? $section : 'reviews';
			}

			$this->section = $section;
			update_user_meta(get_current_user_id(), self::OPTION_PREFIX . 'section', $this->section);
			$ret = [
				'success' => TRUE
			];
			break;
		case 'color_scheme':
			if (!$this->administrator)
			{
				$ret = [
					'success' => FALSE
				];
				break;
			}

			if (!wp_verify_nonce($nonce, 'gmbrr_nonce'))
			{
				$ret = [
					'message' => esc_html__('Your session has expired, please refresh this page.', 'g-business-reviews-rating'),
					'success' => FALSE
				];
				break;
			}

			$scheme = (isset($_POST['scheme']) && is_string($_POST['scheme']) && preg_match('/^(?:system|light|dark)$/', $_POST['scheme'])) ? $_POST['scheme'] : 'system';
			update_user_meta(get_current_user_id(), self::OPTION_PREFIX . 'color_scheme', $scheme);
			$ret = [
				'success' => TRUE
			];
			break;
		case 'notification_action':
			if (!wp_verify_nonce($nonce, 'gmbrr_nonce'))
			{
				$ret = [
					'message' => esc_html__('Your session has expired, please refresh this page.', 'g-business-reviews-rating'),
					'success' => FALSE
				];
				break;
			}
			$logged = FALSE;

			if (preg_match('/^notification rate (later|dismiss|done|now|about)$/', $notification_action, $action_matches))
			{
				$notifications = $this->get_array_option('notifications');

				if (!isset($notifications['review']) || !is_array($notifications['review']))
				{
					$notifications['review'] = ['shown' => [], 'action' => NULL, 'about' => []];
				}

				if ($action_matches[1] == 'about')
				{
					if (!isset($notifications['review']['about']) || !is_array($notifications['review']['about']))
					{
						$notifications['review']['about'] = [];
					}

					$notifications['review']['about'][] = time();

					if (count($notifications['review']['about']) > 50)
					{
						$notifications['review']['about'] = array_slice($notifications['review']['about'], -50);
					}
				}
				elseif ($action_matches[1] != 'now')
				{
					$notifications['review']['action'] = $action_matches[1];
				}

				$this->update_option('notifications', $notifications, 'no');
				$logged = TRUE;
			}

			$ret = [
				'notification_action' => mb_strtolower($notification_action),
				'link' => $link,
				'success' => $logged
			];
			break;
		case 'sort':
			if (!wp_verify_nonce($nonce, 'gmbrr_nonce'))
			{
				$ret = [
					'message' => esc_html__('Your session has expired, please refresh this page.', 'g-business-reviews-rating'),
					'success' => FALSE
				];
				break;
			}
			$clear = FALSE;
			$existing_sort = $this->get_option('review_sort_admin', NULL);
			
			if (is_string($existing_sort))
			{
				if (preg_match('/^(.+)[_-](asc|desc)$/', $sort, $m))
				{
					$existing_sort = $m[1];
					$existing_sort_asc = (!isset($m[2]) || isset($m[2]) && ($m[2] == NULL || $m[2] != 'desc'));
				}
				else
				{
					$existing_sort_asc = (!preg_match('/^(?:date|relevance|retrieved|submitted|time)$/', $existing_sort));
				}
			}
			else
			{
				$existing_sort = $existing_sort_asc = NULL;
			}
			
			if (is_string($sort) && $sort != NULL)
			{
				if (preg_match('/^(.+)[_-](asc|desc)$/', $sort, $m))
				{
					$this->review_sort = $m[1];
					$this->review_sort_asc = ($m[1] == $existing_sort) ? ($m[2] == 'asc') : (!preg_match('/^(?:date|relevance|retrieved|submitted|time)$/', $this->review_sort));
					$clear = (($this->review_sort == 'id' || $this->review_sort == 'ids') && ($existing_sort == 'id' || $existing_sort == 'ids') && is_bool($existing_sort_asc) && $existing_sort_asc);
				}
				else
				{
					$this->review_sort = $sort;
					$this->review_sort_asc = ($sort == $existing_sort) ? !$existing_sort_asc : (!preg_match('/^(?:date|relevance|retrieved|submitted|time)$/', $this->review_sort));
				}
				
				$sort = $this->review_sort . '_' . (($this->review_sort_asc) ? 'asc' : 'desc');
			}
			
			if ($clear || !$clear && (!is_string($sort) || $sort == NULL))
			{
				$sort = NULL;
				$this->review_sort = NULL;
				$this->review_sort_asc = FALSE;
			}
	
			$this->update_option('review_sort_admin', $sort, 'no');
			$ret = [
				'ids' => $this->get_reviews('ids'),
				'clear' => $clear,
				'review_sort' => $this->review_sort,
				'review_sort_asc' => $this->review_sort_asc,
				'success' => TRUE
			];
			$this->review_sort = $sort;
			break;
		case 'welcome':
			$this->section = get_user_meta(get_current_user_id(), self::OPTION_PREFIX . 'section', TRUE);

			if ($this->section != 'welcome')
			{
				$ret = [
					'success' => FALSE
				];
				break;
			}

			if (!wp_verify_nonce($nonce, 'gmbrr_nonce_' . $this->section))
			{
				$ret = [
					'message' => esc_html__('Your session has expired, please refresh this page.', 'g-business-reviews-rating'),
					'success' => FALSE
				];
				break;
			}
			
			$this->sanitize_api_key($api_key);
			$this->sanitize_place_id($place_id);
			$this->section = NULL;
			$this->update_option('api_key', $this->api_key, 'no');
			$this->update_option('place_id', $this->place_id, 'no');
			$this->update_option('language', $language, 'no');
			$this->update_option('retrieval_translate', $retrieval_translate, 'no');
			$this->update_option('update', $update, 'no');
			update_user_meta(get_current_user_id(), self::OPTION_PREFIX . 'section', $this->section);

			$ret = [
				'message' => __('Successfully set Google Places credentials.', 'g-business-reviews-rating'),
				'success' => TRUE
			];
			break;
		case 'demo':
			$this->section = get_user_meta(get_current_user_id(), self::OPTION_PREFIX . 'section', TRUE);

			if ($this->section != 'welcome')
			{
				$ret = [
					'success' => FALSE
				];
				break;
			}

			if (!wp_verify_nonce($nonce, 'gmbrr_nonce_' . $this->section))
			{
				$ret = [
					'message' => esc_html__('Your session has expired, please refresh this page.', 'g-business-reviews-rating'),
					'success' => FALSE
				];
				break;
			}

			$this->section = NULL;
			$this->sanitize_demo(TRUE);
			$this->update_option('demo', $this->demo, 'yes');
			update_user_meta(get_current_user_id(), self::OPTION_PREFIX . 'section', $this->section);
			
			$ret = [
				'success' => TRUE
			];
			break;
		case 'import':
			if (!wp_verify_nonce($nonce, 'gmbrr_nonce'))
			{
				$ret = [
					'count' => 0,
					'errors' => [],
					'message' => esc_html__('Your session has expired, please refresh this page.', 'g-business-reviews-rating'),
					'success' => FALSE
				];
				break;
			}

			if ($this->demo || empty($reviews))
			{
				$ret = [
					'count' => 0,
					'errors' => [],
					'message' => __('No reviews imported.', 'g-business-reviews-rating'),
					'success' => FALSE
				];
				break;
			}
			
			$this->set_data();
			$review_backup = (is_array($this->reviews)) ? $this->reviews : [];
			$add = [];
			$errors = [];
			
			foreach ($reviews as $i => $review)
			{
				if (!is_numeric($review['rating']))
				{
					if (!array_key_exists('rating', $errors))
					{
						$errors['rating'] = [];
					}
					
					$errors['rating'][] = $i;
					continue;
				}
				
				if (!preg_match('/^.+[^\d](\d{20,120})(?:[^\d].*)?$/', $review['author_url'], $m))
				{
					if (!array_key_exists('author', $errors))
					{
						$errors['author'] = [];
					}
					
					$errors['author'][] = $i;
					continue;
				}
				
				$author_url_id = $m[1];

				foreach ($this->reviews as $key => $a)
				{
					if (isset($a['author_id']) && $a['author_id'] == $author_url_id)
					{
						continue(2);
					}

					if (!isset($a['author_url']) || !is_string($a['author_url']) || !preg_match('/^.+[^\d](\d{20,120})[^\d].*$/', $a['author_url'], $m))
					{
						if (isset($a['author_name']) && $review['author_name'] == $a['author_name'])
						{
							continue(2);
						}

						continue;
					}

					if ($author_url_id == $m[1])
					{
						continue(2);
					}
				}
			
				$add[] = $i;
			}
			
			$use_relative_time_description = (!$this->translation_exists(TRUE));
			$max_id = 0;

			foreach ($this->reviews as $a)
			{
				if (is_array($a) && isset($a['id']) && is_numeric($a['id']) && intval($a['id']) > $max_id)
				{
					$max_id = intval($a['id']);
				}
			}
			$count = 1;
			
			foreach ($add as $i)
			{
				$review = $this->sanitize_array($reviews[$i]);

				if (!preg_match('/^(\d+)[^\d]+(\d+)[^\d]+(\d+)(?:[^\d].*)?$/', $review['time'], $t))
				{
					if (!array_key_exists('time', $errors))
					{
						$errors['time'] = [];
					}
					
					$errors['time'][] = $i;
					continue;
				}
				
				$time = mktime(0, 0, 0, $t[2], $t[3], $t[1]);
				$language = (array_key_exists('language', $review) && $review['text'] != NULL) ? $review['language'] : (((array_key_exists('translated', $review) && (!$review['translated'] || $review['translated'] && $import_type == 'translation')) && $review['text'] != NULL) ? preg_replace('/^(?:[^?]+)\?(?:hl=([0-9a-z]+)[0-9a-z-]*).+$/i', '$1', $review['author_url']) : NULL);
				$author_url = preg_replace('/^([^?]+)(?:\?.+)?$/', '$1', $review['author_url']);
				$author_id = (preg_match('/(\d{20,120})/', $author_url, $m)) ? $m[1] : NULL;
				$key = ($author_id != NULL && $this->place_id != NULL) ? $this->place_id . '_' . $author_id : $time . '_' . $review['rating'] . '_' . md5($review['author_name'] . '_' . mb_substr(strval($review['text']), 0, 100));

				if (array_key_exists($key, $this->reviews))
				{
					continue;
				}

				$a = [
					'id' => $max_id + $count,
					'place_id' => $this->place_id,
					'order' => NULL,
					'author_name' => $review['author_name'],
					'author_url' => $author_url,
					'author_id' => $author_id,
					'reference' => (isset($review['reference']) && is_string($review['reference']) && preg_match('/^[\w-]{10,200}$/', $review['reference'])) ? (($this->place_id != NULL) ? 'places/' . $this->place_id . '/reviews/' . $review['reference'] : $review['reference']) : NULL,
					'language' => $language,
					'rating' => round($review['rating']),
					'relative_time_description' => $this->get_relative_time_description($time, $review['relative_time_description'], $use_relative_time_description),
					'text' => ($review['text'] != NULL) ? $review['text'] : NULL,
					'time' => $time,
					'checked' => NULL,
					'retrieved' => NULL,
					'imported' => time(),
					'time_estimate' => TRUE,
					'status' => TRUE
				];

				if (!$this->get_option('local_images', FALSE))
				{
					list($a['profile_photo_url']) = $this->set_avatar($review, $key);
				}
				else
				{
					list($a['profile_photo_url'], $a['avatar']) = $this->set_avatar($review, $key);
				}

				$this->reviews[$key] = $a;
				$count++;
			}

			$count--;
			
			if ($count < 1)
			{
				$message = [__('No reviews imported', 'g-business-reviews-rating')];
				
				if (!empty($errors))
				{
					if (array_key_exists('author', $errors) && !empty($errors['author']))
					{
						/* translators: %u: number of reviews and should remain untouched; mid-sentence phrase */
						$message[] = sprintf(_n('%u review did not have a valid author URL', '%u reviews did not have valid author URLs', count($errors['author']), 'g-business-reviews-rating'), count($errors['author']));
					}
					
					if (array_key_exists('time', $errors) && !empty($errors['time']))
					{
						/* translators: %u: number of reviews and should remain untouched; mid-sentence phrase */
						$message[] = sprintf(_n('%u review was missing a date', '%u reviews were missing dates', count($errors['time']), 'g-business-reviews-rating'), count($errors['time']));
					}
				}

				$ret = [
					'count' => $count,
					'errors' => $errors,
					/* translators: separator character and spacing between multiple message elements; spacing is important */
					'message' => implode(__('; ', 'g-business-reviews-rating'), $message),
					'success' => FALSE
				];
				break;
			}

			delete_transient(self::OPTION_PREFIX . 'reviews_shuffled');
			wp_cache_delete('reviews_shuffled', self::OPTION_PREFIX);
			wp_cache_delete('reviews', self::OPTION_PREFIX);
			$this->update_option('reviews', $this->reviews, 'no');
			
			$this->set_reviews(TRUE);
			$this->count_reviews_all = $this->reviews_count();
			$this->count_reviews_active = $this->reviews_count(NULL, TRUE);
			
			if (count($review_backup) >= $this->count_reviews_all)
			{
				$this->reviews = $review_backup;
				$this->update_option('reviews', $this->reviews, 'no');
				$this->update_option('additional_array_sanitization', TRUE, 'yes');
				$this->set_reviews(TRUE);
				$this->reviews_filtered = $this->reviews;
				
				if ($count > 50)
				{
					$ret = [
						'count' => $count,
						'errors' => $errors,
						/* translators: %u: number of reviews and should remain untouched */
						'message' => sprintf(__('Review import failed. Please select a smaller number of reviews, less than %u.', 'g-business-reviews-rating'), $count),
						'success' => FALSE
					];
					break;
				}

				$ret = [
					'count' => $count,
					'errors' => $errors,
					'message' => __('Review import failed.', 'g-business-reviews-rating'),
					'success' => FALSE
				];
				break;
			}
			
			$review_verify = $this->get_option('reviews');

			if (is_array($review_verify) && count($review_verify) == count($this->reviews))
			{
				/* Works around maybe_serialize() resetting all reviews */

				global $wpdb;

				$review_verify = $wpdb->get_var($wpdb->prepare("SELECT option_value FROM $wpdb->options WHERE option_name = %s LIMIT 1", self::OPTION_PREFIX . 'reviews'));
				$review_verify = (is_string($review_verify)) ? maybe_unserialize($review_verify) : [];
			}

			if (!is_array($review_verify) || is_array($review_verify) && count($review_verify) != count($this->reviews))
			{
				$this->reviews = $review_backup;
				$this->update_option('reviews', $this->reviews, 'no');
				$this->set_reviews(TRUE);
				$this->set_data(TRUE);
				self::log('import failed', ['count' => $count, 'errors' => $errors]);
				
				if ($count > 50)
				{
					$ret = [
						'count' => $count,
						'errors' => $errors,
						/* translators: %u: number of reviews and should remain untouched */
						'message' => sprintf(__('Review import failed due the handling of serialized data by WordPress. Please select a smaller number of reviews, less than %u.', 'g-business-reviews-rating'), $count),
						'success' => FALSE
					];
					break;
				}
				

				$ret = [
					'count' => $count,
					'errors' => $errors,
					'message' => __('Review import failed due the handling of serialized data by WordPress.', 'g-business-reviews-rating'),
					'success' => FALSE
				];
				break;
			}

			$this->reviews_filtered = $this->reviews;
			$this->section = 'reviews';
			update_user_meta(get_current_user_id(), self::OPTION_PREFIX . 'section', $this->section);
			
			/* translators: %u: number of reviews and should remain untouched */
			$message = [sprintf(_n('Successfully imported %u review.', 'Successfully imported %u reviews.', $count, 'g-business-reviews-rating'), $count)];
			
			if (!empty($errors))
			{
				if (array_key_exists('author', $errors) && !empty($errors['author']))
				{
					/* translators: %u: number of reviews and should remain untouched; mid-sentence phrase */
					$message[] = sprintf(_n('%u review did not have a valid author URL', '%u reviews did not have valid author URLs', count($errors['author']), 'g-business-reviews-rating'), count($errors['author']));
				}
				
				if (array_key_exists('time', $errors) && !empty($errors['time']))
				{
					/* translators: %u: number of reviews and should remain untouched; mid-sentence phrase */
					$message[] = sprintf(_n('%u review was missing a date', '%u reviews were missing dates', count($errors['time']), 'g-business-reviews-rating'), count($errors['time']));
				}
			}

			if (!empty($errors))
			{
				self::log('import', ['count' => $count, 'errors' => $errors, 'message' => implode('; ', $message)]);
			}
			else
			{
				self::log('import', ['count' => $count, 'message' => implode('; ', $message)]);
			}

			$ret = [
				'count' => $count,
				'errors' => $errors,
				/* translators: separator character and spacing between multiple message elements; spacing is important */
				'message' => implode(__('; ', 'g-business-reviews-rating'), $message),
				'success' => TRUE
			];
			break;
		case 'submitted':
			if (!wp_verify_nonce($nonce, 'gmbrr_nonce'))
			{
				$ret = [
					'review' => NULL,
					'submitted' => FALSE,
					'message' => esc_html__('Your session has expired, please refresh this page.', 'g-business-reviews-rating'),
					'success' => FALSE
				];
				break;
			}

			$this->set_data();
			$review = $this->get_review_key($review);

			if ($this->demo || !array_key_exists($review, $this->reviews) || !isset($this->reviews[$review]['time_estimate']) || isset($this->reviews[$review]['time_estimate']) && !$this->reviews[$review]['time_estimate'] || !preg_match('/^(\d+)[^\d]+(\d+)[^\d]+(\d+)(?:[^\d].*)?$/', $submitted, $t))
			{
				$ret = [
					'review' => $review,
					'submitted' => $submitted,
					'success' => FALSE
				];
				break;	
			}

			$time = mktime(0, 0, 0, $t[2], $t[3], $t[1]);
			$this->reviews[$review]['time'] = $time;
			$this->update_option('reviews', $this->reviews, 'no');
			
			$this->set_reviews(TRUE);
			$this->reviews_filtered = $this->reviews;
			$ret = [
				'review' => $review,
				'submitted' => $submitted,
				'time' => $time,
				'success' => TRUE
			];

			break;
		case 'language':
			if (!wp_verify_nonce($nonce, 'gmbrr_nonce'))
			{
				$ret = [
					'review' => NULL,
					'submitted' => FALSE,
					'message' => esc_html__('Your session has expired, please refresh this page.', 'g-business-reviews-rating'),
					'success' => FALSE
				];
				break;
			}

			$this->set_data();
			$review = $this->get_review_key($review);

			$language_match = [];
			if ($this->demo || !array_key_exists($review, $this->reviews) || $language != NULL && !preg_match('/^(\w{2}l?)(?:[_-](\w+))?$/', $language, $language_match))
			{
				$ret = [
					'review' => $review,
					'language' => $language,
					'success' => FALSE
				];
				break;	
			}

			$language = ($language != NULL) ? ((isset($language_match[2]) && $language_match[2] != NULL) ? $language_match[1] . '-' . $language_match[2] : $language_match[1]) : NULL;
			$this->reviews[$review]['language'] = $language;
			$this->update_option('reviews', $this->reviews, 'no');
			
			$this->set_reviews(TRUE);
			$this->reviews_filtered = $this->reviews;
			$ret = [
				'review' => $review,
				'language' => $language,
				'success' => TRUE
			];

			break;
		case 'icon-delete':
		case 'icon_delete':
		case 'icon-remove':
		case 'icon_remove':
			if (!wp_verify_nonce($nonce, 'gmbrr_nonce'))
			{
				$ret = [
					'id' => NULL,
					'image' => NULL,
					'message' => esc_html__('Your session has expired, please refresh this page.', 'g-business-reviews-rating'),
					'success' => FALSE
				];
				break;
			}

			$this->delete_icon();
			
			$ret = [
				'id' => NULL,
				'image' => NULL,
				'success' => TRUE
			];
			break;	
		case 'icon':
			if (!wp_verify_nonce($nonce, 'gmbrr_nonce'))
			{
				$ret = [
					'id' => NULL,
					'image' => NULL,
					'message' => esc_html__('Your session has expired, please refresh this page.', 'g-business-reviews-rating'),
					'success' => FALSE
				];
				break;
			}
			
			if (!is_numeric($id))
			{
				$this->delete_icon();
				
				$ret = [
					'id' => NULL,
					'image' => NULL,
					'success' => FALSE
				];
				break;	
			}
			
			$this->set_icon($id);
			
			if (!is_string($this->icon_image_url) || is_string($this->icon_image_url) && mb_strlen($this->icon_image_url) < 5)
			{
				$this->delete_icon();
				
				$ret = [
					'id' => NULL,
					'image' => NULL,
					'success' => FALSE
				];
				
				break;	
			}
			
			$ret = [
				'id' => $this->icon_image_id,
				'image' => preg_replace('/\s+(?:width|height)="\d*"/i', '', wp_get_attachment_image($this->icon_image_id, 'large', FALSE, ['id' => 'icon-image-preview-image'])),
				'success' => TRUE
			];
			break;
		case 'logo-delete':
		case 'logo_delete':
		case 'logo-remove':
		case 'logo_remove':
			if (!wp_verify_nonce($nonce, 'gmbrr_nonce'))
			{
				$ret = [
					'id' => NULL,
					'image' => NULL,
					'message' => esc_html__('Your session has expired, please refresh this page.', 'g-business-reviews-rating'),
					'success' => FALSE
				];
				break;
			}

			$this->delete_logo();
			
			$ret = [
				'id' => NULL,
				'image' => NULL,
				'success' => TRUE
			];
			break;	
		case 'logo':
			if (!wp_verify_nonce($nonce, 'gmbrr_nonce'))
			{
				$ret = [
					'id' => NULL,
					'image' => NULL,
					'message' => esc_html__('Your session has expired, please refresh this page.', 'g-business-reviews-rating'),
					'success' => FALSE
				];
				break;
			}

			if (!is_numeric($id))
			{
				$this->delete_logo();
				
				$ret = [
					'id' => NULL,
					'image' => NULL,
					'success' => FALSE
				];
				break;	
			}
			
			$this->set_logo($id);
			
			if (!is_string($this->logo_image_url) || is_string($this->logo_image_url) && mb_strlen($this->logo_image_url) < 5)
			{
				$this->delete_logo();
				
				$ret = [
					'id' => NULL,
					'image' => NULL,
					'success' => FALSE
				];
				
				break;	
			}
			
			$ret = [
				'id' => $this->logo_image_id,
				'image' => preg_replace('/\s+(?:width|height)="\d*"/i', '', wp_get_attachment_image($this->logo_image_id, 'large', FALSE, ['id' => 'logo-image-preview-image'])),
				'success' => TRUE
			];
			break;
		case 'preview':
			if (!wp_verify_nonce($nonce, 'gmbrr_nonce'))
			{
				$ret = [
					'html' => NULL,
					'message' => esc_html__('Your session has expired, please refresh this page.', 'g-business-reviews-rating'),
					'success' => FALSE
				];
				break;
			}

			$ret = [
				'html' => $this->preview(),
				'status' => $status,
				'success' => TRUE
			];
			break;
		case 'structured-data':
		case 'structured_data':
			if (!wp_verify_nonce($nonce, 'gmbrr_nonce'))
			{
				$ret = [
					'data' => NULL,
					'message' => esc_html__('Your session has expired, please refresh this page.', 'g-business-reviews-rating'),
					'success' => FALSE
				];
				break;
			}

			$data = [];
			
			if (preg_match('/.+\.(?:jpe?g|png|svg|gif|webp)$/i', $this->logo_image_url))
			{
				$data['logo'] = $this->logo_image_url;
			}
			
			if (isset($_POST['telephone']) && is_string($_POST['telephone']) && preg_match('/^[\d _()\[\].+-]+$/', $_POST['telephone']))
			{
				$data['telephone'] = $this->sanitize_input($_POST['telephone']);
			}
			
			if (isset($_POST['business_type']) && is_string($_POST['business_type']) && preg_match('/^[\w\s_-]{1,64}$/i', $_POST['business_type']))
			{
				$data['business_type'] = $this->sanitize_input($_POST['business_type']);
			}
			
			if (isset($_POST['price_range']))
			{
				$data['price_range'] = (is_numeric($_POST['price_range'])) ? str_repeat('$', intval($_POST['price_range'])) : FALSE;
			}
			
			$ret = [
				'data' => $this->get_structured_data_json($data),
				'success' => TRUE
			];
			break;
		case 'status':
			if (!wp_verify_nonce($nonce, 'gmbrr_nonce'))
			{
				$ret = [
					'review' => NULL,
					'status' => FALSE,
					'message' => esc_html__('Your session has expired, please refresh this page.', 'g-business-reviews-rating'),
					'success' => FALSE
				];
				break;
			}

			$this->set_data();
			$review = $this->get_review_key($review);

			if (!array_key_exists($review, $this->reviews) || isset($this->reviews[$review]['status']) && $this->reviews[$review]['status'] == $status)
			{
				$ret = [
					'review' => $review,
					'status' => $status,
					'success' => FALSE
				];
				break;	
			}

			$this->reviews[$review]['status'] = $status;
			$this->reviews_filtered = $this->reviews;
			wp_cache_set((($this->demo) ? 'reviews_demo' : 'reviews'), $this->reviews, self::OPTION_PREFIX, HOUR_IN_SECONDS);
	
			if (!$this->demo)
			{
				$this->update_option('reviews', $this->reviews, 'no');
			}
	
			$ret = [
				'review' => $review,
				'status' => $status,
				'success' => TRUE
			];
			break;
		case 'styles-scripts':
		case 'styles_scripts':
			if (!wp_verify_nonce($nonce, 'gmbrr_nonce'))
			{
				$ret = [
					'message' => esc_html__('Your session has expired, please refresh this page.', 'g-business-reviews-rating'),
					'success' => FALSE
				];
				break;
			}

			if ($stylesheet == $this->get_option('stylesheet') && $javascript == $this->get_option('javascript') && $custom_styles == $this->get_option('custom_styles'))
			{
				$ret = [
					'success' => TRUE
				];
			}
			
			$this->update_option('stylesheet', $stylesheet, 'yes');
			$this->update_option('javascript', $javascript, 'yes');

			if ($custom_styles == $this->get_option('custom_styles'))
			{
				$ret = [
					'message' => __('Successfully saved style and script preference.', 'g-business-reviews-rating'),
					'success' => TRUE
				];
			}
						
			$this->update_option('custom_styles', $custom_styles, 'yes');
			$fp = FALSE;
			$file = self::custom_styles_file();

			if (!is_file($file))
			{
				if (!is_writable(dirname($file)))
				{
					$ret = [
						/* translators: %s: file directory and should remain untouched */
						'message' => sprintf(__('Cannot create a new file in plugin directory: %s', 'g-business-reviews-rating'), './wp/css/'),
						'success' => FALSE
					];
					break;
				}
				
				$fp = fopen($file, 'w');
				
				if (!$fp || !is_file($file))
				{
					if ($fp)
					{
						fclose($fp);
					}
					
					$ret = [
						/* translators: %s: file name and should remain untouched */
						'message' => sprintf(__('Cannot create a new file: %s', 'g-business-reviews-rating'), './wp/css/custom.css'),
						'success' => FALSE
					];
					break;
				}
			}
			
			if (!is_writable($file))
			{
				$ret = [
					/* translators: %s: file name and should remain untouched */
					'message' => sprintf(__('File at: %s is not writable.', 'g-business-reviews-rating'), './wp/css/custom.css'),
					'success' => FALSE
				];
				break;
			}
			
			if (!$fp)
			{
				$fp = fopen($file, 'w');
			}
				
			if (!$fp)
			{
				$ret = [
					/* translators: %s: file name and should remain untouched */
					'message' => sprintf(__('Cannot write new data to file at: %s', 'g-business-reviews-rating'), './wp/css/custom.css'),
					'success' => FALSE
				];
				break;
			}
			
			if ($custom_styles != NULL && !fwrite($fp, $custom_styles))
			{
				fclose($fp);
				$ret = [
					'success' => FALSE
				];
				break;
			}
			
			fclose($fp);
			
			$ret = [
				'message' => __('Successfully updated styles and scripts.', 'g-business-reviews-rating'),
				'success' => TRUE
			];
			break;
		case 'clear':
		case 'cache':
		case 'clear-cache':
		case 'clear_cache':
		case 'api-switch':
		case 'api_switch':
			if (!wp_verify_nonce($nonce, 'gmbrr_nonce'))
			{
				$ret = [
					'message' => esc_html__('Your session has expired, please refresh this page.', 'g-business-reviews-rating'),
					'success' => FALSE
				];
				break;
			}

			$this->api_version = $this->get_option('api_version', NULL);
			$api_switch = (($this->api_version == NULL || intval($this->api_version) < 1) && preg_match('/^api[_-]switch$/i', $type));

			if ($api_switch)
			{
				$this->api_version = 1;
				$this->update_option('api_version', $this->api_version, 'no');
				self::set_api_history();
			}

			delete_transient(self::OPTION_PREFIX . 'reviews_shuffled');
			wp_cache_delete('structured_data', self::OPTION_PREFIX);
			wp_cache_delete('result', self::OPTION_PREFIX);
			wp_cache_delete('result_valid', self::OPTION_PREFIX);
			wp_cache_delete('reviews_shuffled', self::OPTION_PREFIX);
			wp_cache_delete('reviews', self::OPTION_PREFIX);
			$this->data = [];
			$this->result = [];

			if (!$this->set_data(TRUE))
			{
				if (!is_array($this->result) || !is_array($this->data) || empty($this->result) || empty($this->data))
				{
					$ret = [
						'message' => __('Unable to reset data.', 'g-business-reviews-rating'),
						'success' => FALSE
					];
					break;
				}

				if (!is_array(get_option(self::OPTION_PREFIX . 'result', FALSE)))
				{
					if (!$this->get_option('additional_array_sanitization', FALSE))
					{
						$ret = [
							'message' => __('Unable to save data, consider enabling additional sanitization of retrieved data.', 'g-business-reviews-rating'),
							'success' => FALSE
						];
						break;
					}
					
					$ret = [
						'message' => __('Unable to save data.', 'g-business-reviews-rating'),
						'success' => FALSE
					];
					break;
				}

				if ($api_switch)
				{
					$ret = [
						'message' => __('Unable to set new API version.', 'g-business-reviews-rating'),
						'success' => FALSE
					];
					break;
				}

				$ret = [
					'message' => __('Unable to clear cache.', 'g-business-reviews-rating'),
					'success' => FALSE
				];
				break;
			}
			
			$this->section = NULL;
			update_user_meta(get_current_user_id(), self::OPTION_PREFIX . 'section', $this->section);

			if ($api_switch)
			{
				self::log('api switched');
				$ret = [
					'message' => __('API version updated.', 'g-business-reviews-rating'),
					'success' => TRUE
				];
				break;
			}

			self::log('cache cleared');
			$ret = [
				'message' => __('Cache cleared.', 'g-business-reviews-rating'),
				'success' => TRUE
			];

			break;
		case 'delete':
		case 'remove':
			if (!current_user_can('delete_published_posts', self::PLUGIN_ALIAS))
			{
				$ret = [
					'message' => __('You do not have sufficient permission to perform this action.', 'g-business-reviews-rating'),
					'success' => FALSE
				];
				break;
			}

			if (!wp_verify_nonce($nonce, 'gmbrr_nonce'))
			{
				$ret = [
					'message' => esc_html__('Your session has expired, please refresh this page.', 'g-business-reviews-rating'),
					'success' => FALSE
				];
				break;
			}

			$this->set_data();
			$review = $this->get_review_key($review);

			if ($this->demo || !array_key_exists($review, $this->reviews) || isset($this->reviews[$review]['time_estimate']) && !$this->reviews[$review]['time_estimate'] && isset($this->reviews[$review]['removable']) && !$this->reviews[$review]['removable'])
			{
				$ret = [
					'review' => $review,
					'success' => FALSE
				];
				break;	
			}

			if (isset($this->reviews[$review]['avatar']) && $this->reviews[$review]['avatar'] != NULL)
			{
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

				if ($upload_directory_plugin !== NULL && is_dir($upload_directory_plugin) && is_file($upload_directory_plugin . '/' . $this->reviews[$review]['avatar']))
				{
					unlink($upload_directory_plugin . '/' . $this->reviews[$review]['avatar']);
				}
			}

			unset($this->reviews[$review]);
			$this->update_option('reviews', $this->reviews, 'no');
			
			$this->set_reviews(TRUE);
			$this->reviews_filtered = $this->reviews;
			$ret = [
				'review' => $review,
				'success' => TRUE
			];
			break;
		case 'roles':
			if (!wp_verify_nonce($nonce, 'gmbrr_nonce'))
			{
				$ret = [
					'message' => esc_html__('Your session has expired, please refresh this page.', 'g-business-reviews-rating'),
					'success' => FALSE
				];
				break;
			}

			if (!is_bool($roles_editor))
			{
				$ret = [
					'success' => FALSE
				];
				break;
			}

			$this->update_option('editor', $roles_editor, 'no');

			$ret = [
				'roles' => $roles_editor,
				'message' => ($roles_editor) ? __('Permission for editors is enabled.', 'g-business-reviews-rating') : __('Permission for editors is disabled.', 'g-business-reviews-rating'),
				'success' => TRUE
			];
			break;
		case 'reset_notifications':
			if (!wp_verify_nonce($nonce, 'gmbrr_nonce'))
			{
				$ret = [
					'message' => esc_html__('Your session has expired, please refresh this page.', 'g-business-reviews-rating'),
					'success' => FALSE
				];
				break;
			}

			$this->notification_reset();

			$ret = [
				'message' => __('Notifications successfully reset.', 'g-business-reviews-rating'),
				'success' => TRUE
			];
			break;
		case 'remove_other_places':
			if (!$remove_other_places || $this->place_id == NULL)
			{
				$ret = [
					'success' => FALSE
				];
				break;
			}
			
			if (!wp_verify_nonce($nonce, 'gmbrr_nonce'))
			{
				$ret = [
					'message' => esc_html__('Your session has expired, please refresh this page.', 'g-business-reviews-rating'),
					'success' => FALSE
				];
				break;
			}

			if (empty($this->places))
			{
				$this->places = $this->get_array_option('places');
			}
			
			if (count($this->places) < 2)
			{
				$ret = [
					'success' => FALSE
				];
				break;
			}

			foreach ($this->places as $a)
			{
				if (!isset($a['place_id']) || $a['place_id'] == $this->place_id)
				{
					continue;
				}

				$this->delete_place($a['place_id']);
			}

			$this->set_reviews(TRUE);
			$this->count_reviews_all = $this->reviews_count();
			$this->count_reviews_active = $this->reviews_count(NULL, TRUE);

			if ($notifications)
			{
				$this->notification_reset();
			}

			update_user_meta(get_current_user_id(), self::OPTION_PREFIX . 'section', NULL);
			$ret = [
				'message' => __('Places successfully cleared.', 'g-business-reviews-rating'),
				'success' => TRUE
			];
			break;
		case 'relevance':
			if (!$this->administrator)
			{
				$ret = [
					'success' => FALSE
				];
				break;
			}

			if (!wp_verify_nonce($nonce, 'gmbrr_nonce'))
			{
				$ret = [
					'message' => esc_html__('Your session has expired, please refresh this page.', 'g-business-reviews-rating'),
					'success' => FALSE
				];
				break;
			}

			$this->reviews = $this->get_array_option('reviews');

			if (!$this->set_relevance_order())
			{
				$ret = [
					'message' => esc_html__('There are no reviews to arrange.', 'g-business-reviews-rating'),
					'success' => FALSE
				];
				break;
			}

			$this->update_option('reviews', $this->reviews, 'no');
			$this->update_option('review_sort_admin', NULL, 'no');
			wp_cache_delete('reviews', self::OPTION_PREFIX);
			self::log('relevance');
			$ret = [
				/* translators: %u: number of reviews rearranged */
				'message' => sprintf(esc_html__('Rearranged %u reviews by relevance.', 'g-business-reviews-rating'), count($this->reviews)),
				'success' => TRUE
			];
			break;
		case 'relevance_import':
			if (!$this->administrator)
			{
				$ret = [
					'success' => FALSE
				];
				break;
			}

			if (!wp_verify_nonce($nonce, 'gmbrr_nonce'))
			{
				$ret = [
					'message' => esc_html__('Your session has expired, please refresh this page.', 'g-business-reviews-rating'),
					'success' => FALSE
				];
				break;
			}

			if ($this->demo || empty($order))
			{
				$ret = [
					'changes' => [],
					'message' => esc_html__('No review order was supplied.', 'g-business-reviews-rating'),
					'success' => FALSE
				];
				break;
			}

			$this->reviews = $this->get_array_option('reviews');
			$positions = [];

			foreach (array_values($order) as $order_index => $author_url)
			{
				if (!is_string($author_url) || !preg_match('/(\d{20,120})/', $author_url, $m))
				{
					continue;
				}

				$positions[$m[1]] = $order_index + 1;
			}

			$place_id_current = ($this->place_id != NULL) ? $this->place_id : $this->get_option('place_id', NULL);
			$previous = [];
			$matched = [];

			foreach ($this->reviews as $key => $a)
			{
				$previous[$key] = (isset($a['order'])) ? $a['order'] : NULL;

				if (!isset($a['place_id']) || $a['place_id'] != $place_id_current || !isset($a['author_url']) || !is_string($a['author_url']) || !preg_match('/(\d{20,120})/', $a['author_url'], $m) || !array_key_exists($m[1], $positions))
				{
					continue;
				}

				$matched[$key] = $positions[$m[1]];
			}

			asort($matched, SORT_NUMERIC);
			$slots = [];

			foreach (array_keys($matched) as $key)
			{
				if (is_numeric($previous[$key]))
				{
					$slots[] = intval($previous[$key]);
				}
			}

			sort($slots, SORT_NUMERIC);
			$slot_index = 0;

			foreach (array_keys($matched) as $key)
			{
				$this->reviews[$key]['order'] = (isset($slots[$slot_index])) ? $slots[$slot_index] : NULL;
				$slot_index++;
			}

			$this->set_relevance_insert();
			$changes = [];

			foreach ($this->reviews as $key => $a)
			{
				if (!array_key_exists($key, $previous) || $previous[$key] == $a['order'])
				{
					continue;
				}

				$changes[] = [
					'author' => $a['author_name'],
					'before' => $previous[$key],
					'after' => $a['order']
				];
			}

			if (empty($changes))
			{
				$ret = [
					'changes' => [],
					'message' => esc_html__('The stored order already matches Google’s.', 'g-business-reviews-rating'),
					'success' => TRUE
				];
				break;
			}

			$this->update_option('reviews', $this->reviews, 'no');
			wp_cache_delete('reviews', self::OPTION_PREFIX);
			delete_transient(self::OPTION_PREFIX . 'reviews_shuffled');
			wp_cache_delete('reviews_shuffled', self::OPTION_PREFIX);
			self::log('relevance import', ['count' => count($changes)]);
			$ret = [
				'changes' => $changes,
				/* translators: %u: number of reviews and should remain untouched */
				'message' => sprintf(_n('Repositioned %u review to match Google’s order.', 'Repositioned %u reviews to match Google’s order.', count($changes), 'g-business-reviews-rating'), count($changes)),
				'success' => TRUE
			];
			break;
		case 'reset_reviews':
			if (!wp_verify_nonce($nonce, 'gmbrr_nonce'))
			{
				$ret = [
					'message' => esc_html__('Your session has expired, please refresh this page.', 'g-business-reviews-rating'),
					'success' => FALSE
				];
				break;
			}

			if ($remove_other_places && $this->place_id != NULL)
			{
				if (empty($this->places))
				{
					$this->places = $this->get_array_option('places');
				}
				
				if (count($this->places) >= 2)
				{
					foreach ($this->places as $a)
					{
						if (!isset($a['place_id']) || $a['place_id'] == $this->place_id)
						{
							continue;
						}
	
						$this->delete_place($a['place_id']);
					}
				}
			}

			if ($notifications)
			{
				$this->notification_reset();
			}

			delete_transient(self::OPTION_PREFIX . 'reviews_shuffled');
			wp_cache_delete('reviews_shuffled', self::OPTION_PREFIX);
			wp_cache_delete('reviews', self::OPTION_PREFIX);
			$this->update_option('reviews', NULL, 'no');
			update_user_meta(get_current_user_id(), self::OPTION_PREFIX . 'section', NULL);
			$this->set_data(TRUE);

			if ($remove_other_places && $this->place_id != NULL)
			{
				$ret = [
					'message' => __('Places cleared and review archive successfully reset.', 'g-business-reviews-rating'),
					'success' => TRUE
				];
				break;
			}

			$ret = [
				'message' => __('Review archive successfully reset.', 'g-business-reviews-rating'),
				'success' => TRUE
			];
			break;
		case 'reset':
			if (!current_user_can('activate_plugins', self::PLUGIN_ALIAS))
			{
				$ret = [
					'message' => __('You do not have sufficient permission to perform this action.', 'g-business-reviews-rating'),
					'success' => FALSE
				];
				break;
			}

			if (!wp_verify_nonce($nonce, 'gmbrr_nonce'))
			{
				$ret = [
					'message' => esc_html__('Your session has expired, please refresh this page.', 'g-business-reviews-rating'),
					'success' => FALSE
				];
				break;
			}
			
			if ($notifications)
			{
				$this->notification_reset();
			}
			
			$ret = [
				'message' => __('Plugin successfully reset.', 'g-business-reviews-rating'),
				'success' => $this->reset()
			];
			break;
		default:
			break;
		}

		echo json_encode($ret);
		wp_die();
	}
	

	/* Add action link in Dashboard Plugin list */

	public static function add_action_links(array $links, string $file): array
	{
		if (!preg_match('#^([^/]+).*$#', $file, $m1) || !preg_match('#^([^/]+).*$#', plugin_basename(__FILE__), $m2) || $m1[1] != $m2[1])
		{
			return $links;
		}
		
		$new_links = ['settings' => '<a href="' . admin_url('options-general.php?page=google_business_reviews_rating_settings') . '">' . esc_html__('Settings', 'g-business-reviews-rating') . '</a>'];
		$links = array_merge($new_links, $links);

		return $links;
	}
	
	/* Add support link in Dashboard Plugin list */

	public static function add_plugin_meta(array $links, string $file): array
	{
		if (!preg_match('#^([^/]+).*$#', $file, $m1) || !preg_match('#^([^/]+).*$#', plugin_basename(__FILE__), $m2) || $m1[1] != $m2[1])
		{
			return $links;
		}
		
		$new_links = [
			'reviews' => '<a href="https://wordpress.org/support/plugin/g-business-reviews-rating/reviews/#new-post" title="' . esc_attr__('Like our plugin? Please leave a review!', 'g-business-reviews-rating') . '" style="color: #ffb900; line-height: 90%; font-size: 1.3em; letter-spacing: -0.12em; position: relative; top: 0.08em;">★★★★★</a>',
			'support' => '<a href="https://designextreme.com/wordpress/gmbrr/" target="_blank" title="' . esc_attr__('Support', 'g-business-reviews-rating') . '">' . esc_html__('Support', 'g-business-reviews-rating') . '</a>'
		];
		$links = array_merge($links, $new_links);
				
		return $links;
	}

	/* Load style sheet in the Dashboard */

	public function css_load(): void
	{
		if (!$this->dashboard)
		{
			parent::css_load();
			return;
		}

		$current_screen = get_current_screen();

		if (!$this->current() && $current_screen->base != 'dashboard')
		{
			return;
		}
		
		wp_register_style(self::PLUGIN_ALIAS . '_admin_css', self::plugin_url('dashboard/css/css.css'), [], self::VERSION);
		wp_register_style(self::PLUGIN_ALIAS . '_wp_css', self::plugin_url('wp/css/css.css'), [], self::VERSION);
		wp_enqueue_style(self::PLUGIN_ALIAS . '_admin_css');
		wp_enqueue_style(self::PLUGIN_ALIAS . '_wp_css');

		if ($current_screen->base != 'dashboard')
		{
			wp_enqueue_media();
		}
	}
	
	/* Load Javascript in the Dashboard */

	public function js_load(): void
	{
		if (!$this->dashboard)
		{
			parent::js_load();
			return;
		}

		$current_screen = get_current_screen();

		if (!$this->current() && $current_screen->base != 'dashboard')
		{
			return;
		}

		wp_register_script(self::PLUGIN_ALIAS . '_admin_js', self::plugin_url('dashboard/js/js.js'), ['jquery'], self::VERSION);
		wp_localize_script(self::PLUGIN_ALIAS . '_admin_js', self::PLUGIN_ALIAS . '_admin_ajax', ['url' => admin_url('admin-ajax.php'), 'action' => 'google_business_reviews_rating_admin_ajax']);
		wp_register_script(self::PLUGIN_ALIAS . '_wp_js', self::plugin_url('wp/js/js.js'), [], self::VERSION);
		wp_enqueue_script(self::PLUGIN_ALIAS . '_admin_js');
		wp_enqueue_script(self::PLUGIN_ALIAS . '_wp_js');
	}
	

	/* Handling front-end previews from the Dashboard */

	private function preview(array $post = []): string
	{
		if (empty($this->data))
		{
			$this->set_data();
		}

		if (!is_array($post) || is_array($post) && empty($post))
		{
			$post = $this->sanitize_input($_POST);
		}
		
		$theme = (isset($post['theme'])) ? $post['theme'] : NULL;
		$post['type'] = 'reviews';
		$post['errors'] = TRUE;
		$post['animate'] = FALSE;
		$post['cursor'] = FALSE;
		$post['draggable'] = FALSE;
		$post['local_images'] = (isset($post['local_images']) && (is_bool($post['local_images']) && $post['local_images'] || is_string($post['local_images']) && preg_match('/^(?:t(?:rue)?|y?(?:es)?|1|on|show|local)$/i', $post['local_images'])));
		$post['stylesheet'] = (!isset($post['stylesheet']) || isset($post['stylesheet']) && (is_bool($post['stylesheet']) && $post['stylesheet'] || is_numeric($post['stylesheet']) && $post['stylesheet'] > 0 || is_string($post['stylesheet']) && !preg_match('/^(?:f(?:alse)?|no?(?:ne)?|0|off|hide)$/i', $post['stylesheet'])));
		
		if (preg_match('/\bthree\b/i', $theme) && (!is_numeric($post['limit']) || is_numeric($post['limit']) && $post['limit'] > 9))
		{
			$post['limit'] = 9;
		}
		elseif (preg_match('/\b(?:four|six)\b/i', $theme) && (!is_numeric($post['limit']) || is_numeric($post['limit']) && $post['limit'] > 12))
		{
			$post['limit'] = 12;
		}
		elseif (!preg_match('/\b(?:three|four|five)\b/i', $theme) && (!is_numeric($post['limit']) || is_numeric($post['limit']) && $post['limit'] > 10))
		{
			$post['limit'] = 10;
		}
		
		$post['admin_preview'] = TRUE;

		return $this->display($post);
	}
	

	/* Delete the icon image */

	private function delete_icon(): bool
	{
		$this->icon_image_id = NULL;
		$this->icon_image_url = NULL;
		$this->update_option('icon', $this->icon_image_id);

		return TRUE;
	}
	
	/* Set the icon image */

	private function set_icon($id = NULL): bool
	{
		if (is_numeric($id))
		{
			$this->update_option('icon', $id);
			$this->icon_image_id = intval($id);
		}
		else
		{
			$icon = $this->get_option('icon');
			$this->icon_image_id = (is_numeric($icon)) ? intval($icon) : NULL;
		}
		
		if (is_numeric($this->icon_image_id))
		{
			$a = wp_get_attachment_image_src($this->icon_image_id, 'full');
			$this->icon_image_url = (isset($a[0]) && is_string($a[0])) ? $a[0] : NULL;
		}
		
		return TRUE;
	}
	
	/* Delete the logo image for Structured Data */

	private function delete_logo(): bool
	{
		$this->logo_image_id = NULL;
		$this->logo_image_url = NULL;
		$this->update_option('logo', $this->logo_image_id);

		return TRUE;
	}
	
	/* Set the logo image for Structured Data */

	private function set_logo($id = NULL): bool
	{
		if (is_numeric($id))
		{
			$this->update_option('logo', $id);
			$this->logo_image_id = intval($id);
		}
		else
		{
			$logo = $this->get_option('logo');
			$this->logo_image_id = (is_numeric($logo)) ? intval($logo) : NULL;
		}
		
		if (is_numeric($this->logo_image_id))
		{
			$a = wp_get_attachment_image_src($this->logo_image_id, 'full');
			$this->logo_image_url = (isset($a[0]) && is_string($a[0])) ? $a[0] : NULL;
		}
		
		return TRUE;
	}

	/* Initiate Dashboard Widget */

	public function widget(): bool
	{
		if ($this->demo || intval($this->get_option('meta_box_limit', 5)) < 1)
		{
			return TRUE;
		}

		add_filter('postbox_classes_dashboard_' . self::OPTION_PREFIX, [$this, 'widget_classes']);
		wp_add_dashboard_widget(self::OPTION_PREFIX, __('Reviews and Rating - Google Reviews', 'g-business-reviews-rating'), [$this, 'widget_display'], NULL, NULL, 'side', 'default');
		return TRUE;
	}

	/* Brand the dashboard postbox so CSS can target it via .gmbrr.postbox rather than the widget id */

	public function widget_classes(array $classes): array
	{
		$classes[] = 'gmbrr';
		return $classes;
	}
	
	/* Display Dashboard Widget */

	public function widget_display(): bool
	{
		if ($this->demo)
		{
			return TRUE;
		}
	
		echo $this->get_reviews('latest');
		return TRUE;
	}

	/* Display a relevant notification */

	private function notification(?string $message = NULL, ?string $heading = NULL, ?string $type = NULL): string
	{
		if ($message != NULL)
		{
			$notifications = $this->get_array_option('notifications');

			if (!isset($notifications['review']) || !is_array($notifications['review']))
			{
				$notifications['review'] = ['shown' => [], 'action' => NULL, 'about' => []];
			}

			if (!isset($notifications['review']['shown']) || !is_array($notifications['review']['shown']))
			{
				$notifications['review']['shown'] = [];
			}

			$notifications['review']['shown'][] = time();

			if (count($notifications['review']['shown']) > 50)
			{
				$notifications['review']['shown'] = array_slice($notifications['review']['shown'], -50);
			}

			$this->update_option('notifications', $notifications, 'no');

			$html = '<p class="plugin-notification notice is-dismissable' . (($type != NULL) ? esc_attr(' ' . $type) : '') . ' visible" data-nonce="' . esc_attr(wp_create_nonce('gmbrr_nonce')) . '">' . PHP_EOL
			. '<span class="close"><a href="#google-business-reviews-rating-settings" class="button dismiss later" data-notification-action="notification rate later" title="' . esc_attr__('Remind me later', 'g-business-reviews-rating') . '"><span class="dashicons dashicons-dismiss"></span></a></span>' . PHP_EOL
			. (($heading != NULL) ? '<span class="heading">' . $heading . '</span>' . PHP_EOL : '')
			. '<span class="message">'
			. $message
			. '</span>' . PHP_EOL
			. '<span class="buttons">'
			. '<a href="https://wordpress.org/support/plugin/g-business-reviews-rating/reviews/#new-post" target="_blank" class="button button-primary ui-button review" data-notification-action="notification rate now"><span class="dashicons dashicons-star-filled"></span> ' . esc_html__('Leave a review', 'g-business-reviews-rating') . '</a> '
			. '<a href="#google-business-reviews-rating-settings" class="button ui-button later" data-notification-action="notification rate later">' . esc_html__('Remind me later', 'g-business-reviews-rating') . '</a> '
			. '<a href="#google-business-reviews-rating-settings" class="button ui-button dismiss" data-notification-action="notification rate dismiss">' . esc_html__('Dismiss for a year', 'g-business-reviews-rating') . '</a> '
			. '<a href="#google-business-reviews-rating-settings" class="button ui-button done" data-notification-action="notification rate done">' . esc_html__('I’ve already left a review', 'g-business-reviews-rating') . '</a>'
			. '</span>' . PHP_EOL
			. '</p>';

			return $html;
		}

		if (!$this->administrator || !$this->valid() || $this->count_reviews_all <= 5 || !function_exists('array_column'))
		{
			return '';
		}

		$notifications = $this->get_array_option('notifications');
		$review = (isset($notifications['review']) && is_array($notifications['review'])) ? $notifications['review'] : [];
		$review_action = (isset($review['action']) && is_string($review['action'])) ? $review['action'] : NULL;
		$review_shown = (isset($review['shown']) && is_array($review['shown'])) ? array_filter($review['shown'], 'is_numeric') : [];

		if ($review_action == NULL && empty($review_shown))
		{
			foreach ($this->get_array_option('log') as $entry)
			{
				if (!is_array($entry) || !isset($entry['type']) || !is_string($entry['type']))
				{
					continue;
				}

				if ($entry['type'] == 'notification rate' && isset($entry['time']) && is_numeric($entry['time']))
				{
					$review_shown[] = intval($entry['time']);
					continue;
				}

				if (preg_match('/^notification rate (later|dismiss|done)$/', $entry['type'], $m))
				{
					$review_action = $m[1];
				}
			}
		}

		$review_last_shown = (!empty($review_shown)) ? max($review_shown) : NULL;

		if ($review_action == 'done')
		{
			return '';
		}

		$log = $this->get_array_option('log');

		if (empty($review_shown) && (!is_array($log) || count($log) <= 2))
		{
			if (wp_rand(0, 3) >= 2)
			{
				$initial_version = $this->get_option('initial_version', NULL);

				if ($initial_version != NULL && floatval($initial_version) <= 4.27)
				{
					/* translators: 1: The initial version of this plugin, 2: refers to review URL at wordpress.org, 3: string to handle notification data */
					return $this->notification(sprintf(__('You have used this plugin for quite a while, since version %1$s. We’d love to hear what you think about its design, features, support… So, please consider <a href="%2$s" target="_blank" %3$s>leaving a review</a>!', 'g-business-reviews-rating'), $initial_version, 'https://wordpress.org/support/plugin/g-business-reviews-rating/reviews/#new-post', 'data-notification-action="notification rate now"'), esc_html__('You’ve experienced using this plugin', 'g-business-reviews-rating'), 'version-change');
				}
			}

			return '';
		}

		$installation_timestamp = NULL;
		$reset_timestamp = NULL;
		$installation_timestamp_notify = FALSE;
		$reset_timestamp_notify = FALSE;

		if (is_array($log))
		{
			$install_index = array_search('install', array_column($log, 'type'));

			if (is_numeric($install_index) && isset($log[$install_index]['time']) && is_numeric($log[$install_index]['time']))
			{
				$installation_timestamp = intval($log[$install_index]['time']);

				if ($installation_timestamp < time() - YEAR_IN_SECONDS)
				{
					$installation_timestamp_notify = TRUE;
				}
			}
			else
			{
				$reset_index = array_search('reset', array_column($log, 'type'));

				if (is_numeric($reset_index) && isset($log[$reset_index]['time']) && is_numeric($log[$reset_index]['time']))
				{
					$reset_timestamp = intval($log[$reset_index]['time']);

					if ($reset_timestamp < time() - YEAR_IN_SECONDS)
					{
						$reset_timestamp_notify = TRUE;
					}
				}
			}
		}

		$now_threshold = time() - HOUR_IN_SECONDS;
		$later_threshold = time() - 2 * WEEK_IN_SECONDS;
		$dismiss_threshold = time() - YEAR_IN_SECONDS;

		if ($review_last_shown != NULL && $review_last_shown >= $now_threshold)
		{
			return '';
		}

		if ($review_action == 'later' && $review_last_shown != NULL && $review_last_shown >= $later_threshold)
		{
			return '';
		}

		if ($review_action == 'dismiss' && $review_last_shown != NULL && $review_last_shown >= $dismiss_threshold)
		{
			return '';
		}

		if ($installation_timestamp != NULL && ($installation_timestamp >= $later_threshold || $this->count_reviews_all <= 10))
		{
			return '';
		}

		if ($reset_timestamp != NULL && ($reset_timestamp >= $later_threshold || $this->count_reviews_all <= 10))
		{
			return '';
		}

		if (!$this->get_data('boolean'))
		{
			return '';
		}

		if ($installation_timestamp_notify || $reset_timestamp_notify)
		{
			$when = ($installation_timestamp_notify) ? $installation_timestamp : $reset_timestamp;
			/* translators: 1: The plugin installation date, 2: refers to review URL at wordpress.org, 3: string to handle notification data */
			return $this->notification(sprintf(__('You have used this plugin for quite a while, since %1$s. We’d love to hear what you think about its design, features, support… So, please consider <a href="%2$s" target="_blank" %3$s>leaving a review</a>!', 'g-business-reviews-rating'), wp_date("F Y", $when), 'https://wordpress.org/support/plugin/g-business-reviews-rating/reviews/#new-post', 'data-notification-action="notification rate now"'), esc_html__('You’ve experienced this plugin', 'g-business-reviews-rating'), 'version-change');
		}

		/* translators: 1: refers to review URL at wordpress.org, 2: string to handle notification data */
		return $this->notification(sprintf(__('We’d love to hear what you think about its design, features, support… So, please consider <a href="%1$s" target="_blank" %2$s>leaving a review</a>!', 'g-business-reviews-rating'), 'https://wordpress.org/support/plugin/g-business-reviews-rating/reviews/#new-post', 'data-notification-action="notification rate now"'), esc_html__('Please review our Google Reviews plugin', 'g-business-reviews-rating'), 'review-reminder');
	}

	public function sanitize_place_delete($place_ids, bool $force = FALSE): bool
	{
		if (!is_array($place_ids))
		{
			return FALSE;
		}

		if (empty($place_ids) || !$force && !in_array('confirm', $place_ids))
		{
			return TRUE;
		}

		if (empty($this->places))
		{
			$this->places = $this->get_array_option('places');
		}
		
		if (empty($this->places))
		{
			return FALSE;
		}
		
		foreach ($place_ids as $place_id)
		{
			if (!is_string($place_id) || $place_id == 'confirm' || $place_id == $this->place_id || $place_id == $this->get_option('place_id'))
			{
				continue;
			}

			$this->delete_place($place_id);
		}

		$this->set_reviews(TRUE);
		$this->count_reviews_all = $this->reviews_count();
		$this->count_reviews_active = $this->reviews_count(NULL, TRUE);
		
		return TRUE;
	}
	
	/* Clear all notifications from log file */

	private function notification_reset(): bool
	{
		$log = $this->get_option('log', []);
		
		if (!is_array($log) || is_array($log) && empty($log))
		{
			return FALSE;
		}

		$cleaned_log = [];

		foreach ($log as $a)
		{
			if (!isset($a['type']) || isset($a['type']) && preg_match('/^notification rate [a-z]{2,25}$/', $a['type']))
			{
				continue;
			}

			$cleaned_log[] = $a;
		}

		if (count($log) == count($cleaned_log))
		{
			return FALSE;
		}

		$this->update_option('log', $cleaned_log, 'no');

		return TRUE;
	}

}

new google_business_reviews_rating_dashboard();
