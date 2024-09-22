<?php

if (!defined('ABSPATH'))
{
	die();
}

class google_business_reviews_rating
{
	private
		$dashboard = NULL,
		$data = array(),
		$result = array(),
		$result_valid = array(),
		$places = array(),
		$reviews = array(),
		$reviews_filtered = array(),
		$relative_times = array(),
		$languages = array(),
		$reviews_themes = array(),
		$color_schemes = array(),
		$review_sort = NULL,
		$review_sort_asc = NULL,
		$review_sort_option = NULL,
		$review_sort_options = array(),
		$default_html_tags = array(),
		$updates = array(),
		$business_types = array(),
		$price_ranges = array(),
		$instance_count = NULL,
		$request_count = NULL,
		$settings_updated = FALSE,
		$retrieved_data_valid = NULL,
		$retrieved_data_exists = NULL,
		$place_id = NULL,
		$api_key = NULL,
		$section = NULL,
		$show_reviews = FALSE,
		$count_reviews_all = NULL,
		$count_reviews_active = NULL,
		$icon_image_id = NULL,
		$icon_image_url = NULL,
		$logo_image_id = NULL,
		$logo_image_url = NULL,
		$administrator = FALSE,
		$local_images = FALSE,
		$editor = FALSE,
		$demo = FALSE;
	
	public function __construct()
	{
		// Class contructor that starts everything

		$this->dashboard = (is_admin() || defined('DOING_CRON'));
		$this->instance_count = NULL;
		$this->request_count = 0;
		$this->settings_updated = FALSE;
		$this->retrieved_data_valid = FALSE;
		$this->administrator = FALSE;
		$this->editor = FALSE;
		$this->review_sort = NULL;
		$this->review_sort_asc = NULL;
		$this->default_html_tags = array('h2', 'p', 'p', 'ul', 'li', 'ul', 'li', 'p', 'p', 'p');
		$this->updates = array(
			NULL => __('Never Synchronize', 'g-business-reviews-rating'),
			168 => __('Synchronize Weekly', 'g-business-reviews-rating'),
			24 => __('Synchronize Daily', 'g-business-reviews-rating'),
			6 => __('Synchronize Every 6 Hours', 'g-business-reviews-rating'),
			1 => __('Synchronize Hourly', 'g-business-reviews-rating')
		);
		$this->review_sort_options = array(
			'relevance_desc' => array(
				'name' => __('Relevance Descending', 'g-business-reviews-rating'),
				'min_max_values' => array(__('High', 'g-business-reviews-rating'), __('Low', 'g-business-reviews-rating')),
				'field' => NULL,
				'asc' => FALSE,
				'static' => FALSE
			),
			'relevance_asc' => array(
				'name' => __('Relevance Ascending', 'g-business-reviews-rating'),
				'min_max_values' => array(__('Low', 'g-business-reviews-rating'), __('High', 'g-business-reviews-rating')),
				'field' => NULL,
				'asc' => TRUE,
				'static' => FALSE
			),
			'date_desc' => array(
				'name' => __('Date Descending', 'g-business-reviews-rating'),
				'min_max_values' => array(__('New', 'g-business-reviews-rating'), __('Old', 'g-business-reviews-rating')),
				'field' => 'time',
				'asc' => FALSE,
				'static' => FALSE
			),
			'date_asc' => array(
				'name' => __('Date Ascending', 'g-business-reviews-rating'),
				'min_max_values' => array(__('Old', 'g-business-reviews-rating'), __('New', 'g-business-reviews-rating')),
				'field' => 'time',
				'asc' => TRUE,
				'static' => FALSE
			),
			'rating_desc' => array(
				'name' => __('Rating Descending', 'g-business-reviews-rating'),
				'min_max_values' => array(__('High', 'g-business-reviews-rating'), __('Low', 'g-business-reviews-rating')),
				'field' => 'rating',
				'asc' => FALSE,
				'static' => FALSE
			),
			'rating_asc' => array(
				'name' => __('Rating Ascending', 'g-business-reviews-rating'),
				'min_max_values' => array(__('Low', 'g-business-reviews-rating'), __('High', 'g-business-reviews-rating')),
				'field' => 'rating',
				'asc' => TRUE,
				'static' => FALSE
			),
			'author_name_asc' => array(
				'name' => __('Author’s Name Ascending', 'g-business-reviews-rating'),
				'min_max_values' => array('A', 'Z'),
				'field' => 'author_name',
				'asc' => TRUE,
				'static' => FALSE
			),
			'author_name_desc' => array(
				'name' => __('Author’s Name Descending', 'g-business-reviews-rating'),
				'min_max_values' => array('Z', 'A'),
				'field' => 'author_name',
				'asc' => FALSE,
				'static' => FALSE
			),
			'review_words_asc' => array(
				'name' => __('Review Word Count Ascending', 'g-business-reviews-rating'),
				'min_max_values' => array(__('Low', 'g-business-reviews-rating'), __('High', 'g-business-reviews-rating')),
				'field' => 'text',
				'asc' => TRUE,
				'static' => FALSE
			),
			'review_words_desc' => array(
				'name' => __('Review Word Count Descending', 'g-business-reviews-rating'),
				'min_max_values' => array(__('High', 'g-business-reviews-rating'), __('Low', 'g-business-reviews-rating')),
				'field' => 'text',
				'asc' => FALSE,
				'static' => FALSE
			),
			'review_characters_asc' => array(
				'name' => __('Review Character Count Ascending', 'g-business-reviews-rating'),
				'min_max_values' => array(__('Low', 'g-business-reviews-rating'), __('High', 'g-business-reviews-rating')),
				'field' => 'text',
				'asc' => TRUE,
				'static' => FALSE
			),
			'review_characters_desc' => array(
				'name' => __('Review Character Count Descending', 'g-business-reviews-rating'),
				'min_max_values' => array(__('High', 'g-business-reviews-rating'), __('Low', 'g-business-reviews-rating')),
				'field' => 'text',
				'asc' => FALSE,
				'static' => FALSE
			),
			'id_asc' => array(
				'name' => __('ID Ascending', 'g-business-reviews-rating'),
				'min_max_values' => array(__('Low', 'g-business-reviews-rating'), __('High', 'g-business-reviews-rating')),
				'field' => 'id',
				'asc' => TRUE,
				'static' => FALSE
			),
			'id_desc' => array(
				'name' => __('ID Descending', 'g-business-reviews-rating'),
				'min_max_values' => array(__('High', 'g-business-reviews-rating'), __('Low', 'g-business-reviews-rating')),
				'field' => 'id',
				'asc' => FALSE,
				'static' => FALSE
			),
			'shuffle' => array(
				'name' => __('Random Shuffle Static', 'g-business-reviews-rating'),
				'static' => TRUE
			),
			'shuffle_variable' => array(
				'name' => __('Random Shuffle Variable', 'g-business-reviews-rating'),
				'static' => FALSE
			)
		);
		$this->relative_times = array(
			'hour' => array(
				'text' => __('just now', 'g-business-reviews-rating'),
				'min_time' => NULL,
				'max_time' => 2 * HOUR_IN_SECONDS,
				'divider' => HOUR_IN_SECONDS,
				'singular' => TRUE
			),
			'hours' => array(
				/* translators: %u: number of hours, days, weeks, months or years and should remain untouched */
				'text' => __('%u hours ago', 'g-business-reviews-rating'),
				'min_time' => 2 * HOUR_IN_SECONDS,
				'max_time' => (11 * HOUR_IN_SECONDS),
				'divider' => HOUR_IN_SECONDS,
				'singular' => FALSE
			),
			'day' => array(
				'text' => __('a day ago', 'g-business-reviews-rating'),
				'min_time' => (11 * HOUR_IN_SECONDS),
				'max_time' => (1.5 * DAY_IN_SECONDS),
				'divider' => NULL,
				'singular' => TRUE
			),
			'days' => array(
				/* translators: %u: number of hours, days, weeks, months or years and should remain untouched */
				'text' => __('%u days ago', 'g-business-reviews-rating'),
				'min_time' => (1.5 * DAY_IN_SECONDS),
				'max_time' => (3.5 * DAY_IN_SECONDS),
				'divider' => DAY_IN_SECONDS,
				'singular' => FALSE
			),
			'within_week' => array(
				'text' => __('in the last week', 'g-business-reviews-rating'),
				'min_time' => (3.5 * DAY_IN_SECONDS),
				'max_time' => (6.5 * DAY_IN_SECONDS),
				'divider' => NULL,
				'singular' => TRUE
			),
			'week' => array(
				'text' => __('a week ago', 'g-business-reviews-rating'),
				'min_time' => (6.5 * DAY_IN_SECONDS),
				'max_time' => (13.5 * DAY_IN_SECONDS),
				'divider' => NULL,
				'singular' => TRUE
			),
			'weeks' => array(
				/* translators: %u: number of hours, days, weeks, months or years and should remain untouched */
				'text' => __('%u weeks ago', 'g-business-reviews-rating'),
				'min_time' => (13.5 * DAY_IN_SECONDS),
				'max_time' => (29.5 * DAY_IN_SECONDS),
				'divider' => (WEEK_IN_SECONDS),
				'singular' => FALSE
			),
			'month' => array(
				'text' => __('a month ago', 'g-business-reviews-rating'),
				'min_time' => (29.5 * DAY_IN_SECONDS),
				'max_time' => (58 * DAY_IN_SECONDS),
				'divider' => NULL,
				'singular' => TRUE
			),
			'months' => array(
				/* translators: %u: number of hours, days, weeks, months or years and should remain untouched */
				'text' => __('%u months ago', 'g-business-reviews-rating'),
				'min_time' => (58 * DAY_IN_SECONDS),
				'max_time' => (350 * DAY_IN_SECONDS),
				'divider' => (30.5 * DAY_IN_SECONDS),
				'singular' => FALSE
			),
			'year' => array(
				'text' => __('a year ago', 'g-business-reviews-rating'),
				'min_time' => (350 * DAY_IN_SECONDS),
				'max_time' => (700 * DAY_IN_SECONDS),
				'divider' => NULL,
				'singular' => TRUE
			),
			'years' => array(
				/* translators: %u: number of hours, days, weeks, months or years and should remain untouched */
				'text' => __('%u years ago', 'g-business-reviews-rating'),
				'min_time' => (700 * DAY_IN_SECONDS),
				'max_time' => NULL,
				'divider' => (365.25 * DAY_IN_SECONDS),
				'singular' => FALSE
			)
		);
		
		if (!$this->translation_exists())
		{
			$language_code = preg_replace('/^[^a-z]*([a-z]{2}l?).*$/', '$1', mb_strtolower(get_option('WPLANG')));
			
			switch ($language_code)
			{
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
			}
		}

		$this->color_schemes = array(
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
		);
		
		$this->admin_init();
		$this->wp_init();
		return TRUE;
	}
	
	public static function activate($reset = FALSE)
	{
		// Activate plugin
				
		if (!current_user_can('activate_plugins'))
		{
			return;
		}

		$maybe_unserialize_unicode_issue = array('shamrock' => 'Here is a lucky shamrock 🍀');
		set_transient(__CLASS__ . '_maybe_unserialize_test', $maybe_unserialize_unicode_issue, 4);
		$maybe_unserialize_unicode_issue_check = get_transient(__CLASS__ . '_maybe_unserialize_test');
		delete_transient(__CLASS__ . '_maybe_unserialize_test');
		$additional_array_sanitization = ($maybe_unserialize_unicode_issue !== $maybe_unserialize_unicode_issue_check);
		
		if (is_bool(get_option(__CLASS__ . '_place_id')))
		{
			$plugin_data = (function_exists('get_file_data')) ? get_file_data(plugin_dir_path(__FILE__) . 'g-business-reviews-rating.php', array('Version' => 'Version'), FALSE) : array();
			$version = (array_key_exists('Version', $plugin_data)) ? $plugin_data['Version'] : NULL;
			
			if (!is_bool($reset) || is_bool($reset) && !$reset)
			{
				set_transient(__CLASS__ . '_welcome', time(), 30);
				self::log('install', $version);
			}
			else
			{
				self::log('reset', $version);
			}
			
			update_option(__CLASS__ . '_initial_version', $version, 'no');
			update_option(__CLASS__ . '_place_id', NULL, 'no');
			update_option(__CLASS__ . '_api_key', NULL, 'no');
			update_option(__CLASS__ . '_language', NULL, 'no');
			update_option(__CLASS__ . '_demo', FALSE, 'yes');
			update_option(__CLASS__ . '_update', NULL, 'no');
			update_option(__CLASS__ . '_review_limit', NULL, 'yes');
			update_option(__CLASS__ . '_review_sort', NULL, 'yes');
			update_option(__CLASS__ . '_review_sort_admin', NULL, 'no');
			update_option(__CLASS__ . '_rating_min', NULL, 'yes');
			update_option(__CLASS__ . '_rating_max', NULL, 'yes');
			update_option(__CLASS__ . '_review_text_min', NULL, 'yes');
			update_option(__CLASS__ . '_review_text_max', NULL, 'yes');
			update_option(__CLASS__ . '_review_text_excerpt_length', 235, 'yes');
			update_option(__CLASS__ . '_reviews_theme', NULL, 'yes');
			update_option(__CLASS__ . '_view', NULL, 'yes');
			update_option(__CLASS__ . '_color_scheme', NULL, 'yes');
			update_option(__CLASS__ . '_javascript', TRUE, 'yes');
			update_option(__CLASS__ . '_stylesheet', TRUE, 'yes');
			update_option(__CLASS__ . '_icon', NULL, 'no');
			update_option(__CLASS__ . '_logo', NULL, 'no');
			update_option(__CLASS__ . '_telephone', NULL, 'no');
			update_option(__CLASS__ . '_business_type', NULL, 'no');
			update_option(__CLASS__ . '_price_range', NULL, 'no');
			update_option(__CLASS__ . '_places', NULL, 'yes');
			update_option(__CLASS__ . '_structured_data', FALSE, 'yes');
			update_option(__CLASS__ . '_retrieval', NULL, 'no');
			update_option(__CLASS__ . '_retrieval_fields', array('formatted_address', 'icon', 'id', 'name', 'rating', 'reviews', 'url', 'user_ratings_total', 'vicinity'), 'no');
			update_option(__CLASS__ . '_retrieval_sort', 'most_relevant', 'no');
			update_option(__CLASS__ . '_retrieval_translate', FALSE, 'no');
			update_option(__CLASS__ . '_result', NULL, 'no');
			update_option(__CLASS__ . '_result_valid', NULL, 'no');
			update_option(__CLASS__ . '_reviews', NULL, 'no');
			update_option(__CLASS__ . '_section', NULL, 'no');
			update_option(__CLASS__ . '_editor', TRUE, 'no');
			update_option(__CLASS__ . '_local_images', FALSE, 'yes');
			update_option(__CLASS__ . '_custom_styles', NULL, 'yes');
			update_option(__CLASS__ . '_meta_box_limit', 5, 'no');
			update_option(__CLASS__ . '_log', NULL, 'no');
			update_option(__CLASS__ . '_notifications', NULL, 'no');
			update_option(__CLASS__ . '_related_plugins', NULL, 'no');
		}
		else
		{
			self::log('activate');
		}
		
		update_option(__CLASS__ . '_additional_array_sanitization', $additional_array_sanitization, 'yes');
		
		if (!wp_next_scheduled(__CLASS__ . '_sync'))
		{
			require_once(plugin_dir_path(__FILE__) . 'cron.php');
		}
		
		return TRUE;
	}
	
	public static function deactivate()
	{
		// Deactivate the plugin
		
		if (!current_user_can('activate_plugins'))
		{
			return;
		}
		
		delete_transient(__CLASS__ . '_reviews_shuffled');
		delete_transient(__CLASS__ . '_avatars_downloaded');
		wp_cache_delete('structured_data', __CLASS__);
		wp_cache_delete('result', __CLASS__);
		wp_cache_delete('result_valid', __CLASS__);
		wp_cache_delete('result_demo', __CLASS__);
		wp_cache_delete('reviews_shuffled', __CLASS__);
		wp_cache_delete('reviews', __CLASS__);
		wp_cache_delete('reviews_demo', __CLASS__);
		
		update_option(__CLASS__ . '_result', NULL, 'no');
		update_option(__CLASS__ . '_result_valid', NULL, 'no');

		self::log('deactivate');

		require_once(plugin_dir_path(__FILE__) . 'cron.php');
		
		$cron = new google_business_reviews_rating_cron();
		$cron->deactivate();
		
		return TRUE;
	}
	
	public static function uninstall()
	{
		// Uninstall plugin

		if (!current_user_can('activate_plugins', __CLASS__))
		{
			return;
		}

		require_once(plugin_dir_path(__FILE__) . 'cron.php');
		
		$cron = new google_business_reviews_rating_cron();
		$cron->deactivate();

		$reviews = get_option(__CLASS__ . '_reviews');

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
		
		delete_transient(__CLASS__ . '_reviews_shuffled');
		delete_transient(__CLASS__ . '_avatars_downloaded');
		delete_transient(__CLASS__ . '_welcome');
		delete_transient(__CLASS__ . '_force');
		delete_option('widget_' . __CLASS__);
		delete_option(__CLASS__ . '_initial_version');
		delete_option(__CLASS__ . '_place_id');
		delete_option(__CLASS__ . '_api_key');
		delete_option(__CLASS__ . '_language');
		delete_option(__CLASS__ . '_demo');
		delete_option(__CLASS__ . '_editor');
		delete_option(__CLASS__ . '_force');
		delete_option(__CLASS__ . '_update');
		delete_option(__CLASS__ . '_review_limit');
		delete_option(__CLASS__ . '_review_sort');
		delete_option(__CLASS__ . '_review_sort_admin');
		delete_option(__CLASS__ . '_rating_min');
		delete_option(__CLASS__ . '_rating_max');
		delete_option(__CLASS__ . '_review_text_min');
		delete_option(__CLASS__ . '_review_text_max');
		delete_option(__CLASS__ . '_review_text_excerpt_length');
		delete_option(__CLASS__ . '_reviews_theme');
		delete_option(__CLASS__ . '_view');
		delete_option(__CLASS__ . '_color_scheme');
		delete_option(__CLASS__ . '_javascript');
		delete_option(__CLASS__ . '_stylesheet');
		delete_option(__CLASS__ . '_icon');
		delete_option(__CLASS__ . '_logo');
		delete_option(__CLASS__ . '_telephone');
		delete_option(__CLASS__ . '_business_type');
		delete_option(__CLASS__ . '_places');
		delete_option(__CLASS__ . '_price_range');
		delete_option(__CLASS__ . '_structured_data');
		delete_option(__CLASS__ . '_settings');
		delete_option(__CLASS__ . '_retrieval');
		delete_option(__CLASS__ . '_retrieval_fields');
		delete_option(__CLASS__ . '_retrieval_sort');
		delete_option(__CLASS__ . '_retrieval_translate');
		delete_option(__CLASS__ . '_result');
		delete_option(__CLASS__ . '_result_valid');
		delete_option(__CLASS__ . '_reviews');
		delete_option(__CLASS__ . '_local_images');
		delete_option(__CLASS__ . '_custom_styles');
		delete_option(__CLASS__ . '_additional_array_sanitization');
		delete_option(__CLASS__ . '_meta_box_limit');
		delete_option(__CLASS__ . '_log');
		delete_option(__CLASS__ . '_notifications');
		delete_option(__CLASS__ . '_related_plugins');
		delete_option(__CLASS__ . '_section');

		return TRUE;
	}
	
	public static function upgrade($object, $options)
	{
		// Upgrade plugin
		
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
			
			delete_transient(__CLASS__ . '_reviews_shuffled');
			wp_cache_delete('structured_data', __CLASS__);
			wp_cache_delete('result', __CLASS__);
			wp_cache_delete('result_valid', __CLASS__);
			wp_cache_delete('result_demo', __CLASS__);
			wp_cache_delete('reviews_shuffled', __CLASS__);
			wp_cache_delete('reviews', __CLASS__);
			wp_cache_delete('reviews_demo', __CLASS__);
			
			delete_option(__CLASS__ . '_force');
			
			if (is_numeric(get_option(__CLASS__ . '_local_images', 1)))
			{
				update_option(__CLASS__ . '_local_images', FALSE, 'no');
			}
			
			if (is_numeric(get_option(__CLASS__ . '_retrieval_sort', 1)))
			{
				update_option(__CLASS__ . '_retrieval_sort', 'most_relevant', 'no');
			}
			
			if (!is_array(get_option(__CLASS__ . '_retrieval_fields', NULL)))
			{
				update_option(__CLASS__ . '_retrieval_fields', array('formatted_address', 'icon', 'id', 'name', 'rating', 'reviews', 'url', 'user_ratings_total', 'vicinity'), 'no');
			}
			
			if (is_numeric(get_option(__CLASS__ . '_retrieval_translate', 1)))
			{
				update_option(__CLASS__ . '_retrieval_translate', FALSE, 'no');
			}
			
			$allow_editor = get_option(__CLASS__ . '_editor');

			if ($allow_editor != get_option(__CLASS__ . '_editor', 'x'))
			{
				update_option(__CLASS__ . '_editor', TRUE, 'no');
			}

			$custom_styles = get_option(__CLASS__ . '_custom_styles');
			
			if ($custom_styles == NULL)
			{
				return TRUE;
			}
			
			$fp = FALSE;
			$custom_styles_file = plugin_dir_path(__FILE__) . 'wp/css/custom.css';

			if (!is_file($custom_styles_file))
			{
				if (!is_writable(plugin_dir_path(__FILE__) . 'wp/css/'))
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
	
	private function reset()
	{
		// Reset the plugin to a fresh installation
		
		if (!current_user_can('activate_plugins'))
		{
			return FALSE;
		}

		$this->data = array();
		$this->result = array();
		$this->reviews = array();
		$this->reviews_filtered = array();
		
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
		
		delete_transient(__CLASS__ . '_welcome');
		
		return TRUE;
	}

	public function admin_init()
	{
		// Initiate the plugin in the dashboard
		
		if (!$this->dashboard)
		{
			return TRUE;
		}
		
		$this->demo = get_option(__CLASS__ . '_demo');
		$this->settings_updated = ($this->dashboard && isset($_REQUEST['settings-updated']) && (is_bool($_REQUEST['settings-updated']) && $_REQUEST['settings-updated'] || is_string($_REQUEST['settings-updated']) && preg_match('/^(?:true|1)$/i', $_REQUEST['settings-updated'])));
		
		register_setting(__CLASS__ . '_settings', __CLASS__ . '_additional_array_sanitization', array('type' => 'boolean'));
		register_setting(__CLASS__ . '_settings', __CLASS__ . '_api_key', array('type' => 'string', 'sanitize_callback' => array($this, 'sanitize_api_key')));
		register_setting(__CLASS__ . '_settings', __CLASS__ . '_place_id', array('type' => 'string', 'sanitize_callback' => array($this, 'sanitize_place_id')));
		register_setting(__CLASS__ . '_settings', __CLASS__ . '_place_delete', array('type' => 'array', 'sanitize_callback' => array($this, 'delete_places')));
		register_setting(__CLASS__ . '_settings', __CLASS__ . '_language', array('type' => 'string', 'sanitize_callback' => array($this, 'sanitize_input')));
		register_setting(__CLASS__ . '_settings', __CLASS__ . '_retrieval_sort', array('type' => 'string', 'sanitize_callback' => array($this, 'sanitize_retrieval_sort')));
		register_setting(__CLASS__ . '_settings', __CLASS__ . '_retrieval_translate', array('type' => 'boolean'));
		register_setting(__CLASS__ . '_settings', __CLASS__ . '_demo', array('type' => 'boolean', 'sanitize_callback' => array($this, 'sanitize_demo')));
		register_setting(__CLASS__ . '_settings', __CLASS__ . '_update', array('type' => 'string', 'sanitize_callback' => array($this, 'sanitize_input')));
		register_setting(__CLASS__ . '_settings', __CLASS__ . '_review_limit', array('type' => 'string', 'sanitize_callback' => array($this, 'sanitize_input')));
		register_setting(__CLASS__ . '_settings', __CLASS__ . '_review_sort', array('type' => 'string', 'sanitize_callback' => array($this, 'sanitize_input')));
		register_setting(__CLASS__ . '_settings', __CLASS__ . '_rating_min', array('type' => 'string', 'sanitize_callback' => array($this, 'sanitize_input')));
		register_setting(__CLASS__ . '_settings', __CLASS__ . '_rating_max', array('type' => 'string', 'sanitize_callback' => array($this, 'sanitize_input')));
		register_setting(__CLASS__ . '_settings', __CLASS__ . '_review_text_min', array('type' => 'string', 'sanitize_callback' => array($this, 'sanitize_input')));
		register_setting(__CLASS__ . '_settings', __CLASS__ . '_review_text_max', array('type' => 'string', 'sanitize_callback' => array($this, 'sanitize_input')));
		register_setting(__CLASS__ . '_settings', __CLASS__ . '_review_text_excerpt_length', array('type' => 'string', 'sanitize_callback' => array($this, 'sanitize_input')));
		register_setting(__CLASS__ . '_settings', __CLASS__ . '_reviews_theme', array('type' => 'string', 'sanitize_callback' => array($this, 'sanitize_input')));
		register_setting(__CLASS__ . '_settings', __CLASS__ . '_view', array('type' => 'number'));
		register_setting(__CLASS__ . '_settings', __CLASS__ . '_color_scheme', array('type' => 'string', 'sanitize_callback' => array($this, 'sanitize_input')));
		register_setting(__CLASS__ . '_settings', __CLASS__ . '_icon', array('type' => 'string', 'sanitize_callback' => array($this, 'sanitize_input')));
		register_setting(__CLASS__ . '_settings', __CLASS__ . '_logo', array('type' => 'string', 'sanitize_callback' => array($this, 'sanitize_input')));
		register_setting(__CLASS__ . '_settings', __CLASS__ . '_telephone', array('type' => 'string', 'sanitize_callback' => array($this, 'sanitize_input')));
		register_setting(__CLASS__ . '_settings', __CLASS__ . '_business_type', array('type' => 'string', 'sanitize_callback' => array($this, 'sanitize_input')));
		register_setting(__CLASS__ . '_settings', __CLASS__ . '_price_range', array('type' => 'string', 'sanitize_callback' => array($this, 'sanitize_input')));
		register_setting(__CLASS__ . '_settings', __CLASS__ . '_structured_data', array('type' => 'number'));
		register_setting(__CLASS__ . '_settings', __CLASS__ . '_local_images', array('type' => 'boolean'));
		
		add_action('admin_init', array($this, 'admin_welcome'));
		add_action('admin_menu', array($this, 'admin_menu'));
		add_action('admin_enqueue_scripts', array($this, 'admin_css_load'));
		add_action('admin_enqueue_scripts', array($this, 'admin_js_load'));
		add_action('wp_ajax_'.__CLASS__.'_admin_ajax', array($this, 'admin_ajax'));
		add_action('admin_notices', array($this, 'admin_notices'));
		add_action('wp_dashboard_setup', array($this, 'dashboard_widget'));
		add_action('widgets_init', function() { register_widget('google_business_reviews_rating_widget'); });
		add_action('plugins_loaded', array($this, 'loaded'));		

		add_filter('plugin_action_links', array(__CLASS__, 'admin_add_action_links'), 10, 5);
		add_filter('plugin_row_meta', array(__CLASS__, 'admin_add_plugin_meta'), 10, 2);
		
		if (!$this->set_data())
		{
			return TRUE;
		}
		
		$this->set_reviews();
		$this->set_icon();
		$this->set_logo();

		return TRUE;
	}
	
	public function wp_init()
	{
		// Initiate the plugin in the front-end
		
		$this->demo = get_option(__CLASS__ . '_demo');
		$stylesheet = get_option(__CLASS__ . '_stylesheet', TRUE);
		$javascript = get_option(__CLASS__ . '_javascript', TRUE);
		$structured_data = get_option(__CLASS__ . '_structured_data', 0);

		add_shortcode(__CLASS__, array($this, 'wp_display'));
		add_shortcode('reviews_rating', array($this, 'wp_display'));
		add_shortcode('reviews_rating_single', array($this, 'wp_display'));
		add_shortcode('reviews_rating_links', array($this, 'wp_display'));
		add_shortcode('reviews_rating_link', array($this, 'wp_display'));
		add_shortcode('links_google_business', array($this, 'wp_display'));
		add_shortcode('link_google_business', array($this, 'wp_display'));
		add_action('widgets_init', function() { register_widget('google_business_reviews_rating_widget'); });
		
		if (is_bool($stylesheet) && $stylesheet || is_numeric($stylesheet) && $stylesheet > 0 || is_string($stylesheet) && $stylesheet != NULL)
		{
			add_action('wp_enqueue_scripts', array($this, 'wp_css_load'));
		}
		
		if (is_bool($javascript) && $javascript || is_numeric($javascript) && $javascript > 0 || is_string($javascript) && $javascript != NULL)
		{
			add_action('wp_enqueue_scripts', array($this, 'wp_js_load'));
		}
		
		if (is_bool($structured_data) && $structured_data || is_numeric($structured_data) && ($structured_data >= 1 || $structured_data <= -1))
		{
			add_action('wp_head', array($this, 'structured_data'));
		}

		add_action('plugins_loaded', array($this, 'loaded'));		

		return TRUE;
	}
	
	public function admin_menu()
	{
		// Set the menu item

		$allow_editor = get_option(__CLASS__ . '_editor');

		if ($allow_editor != get_option(__CLASS__ . '_editor', 'x'))
		{
			$allow_editor = TRUE;
			update_option(__CLASS__ . '_editor', $allow_editor, 'no');
		}

		$this->administrator = (current_user_can('manage_options', __CLASS__));
		$this->editor = (!$this->administrator && $allow_editor && current_user_can('edit_published_posts', __CLASS__));
		
		if (!$this->editor && !$this->administrator)
		{
			return TRUE;
		}

		if ($this->administrator)
		{
			$pages = array(array('add_options_page', __('Reviews and Rating - Google Reviews', 'g-business-reviews-rating'), __('Reviews and Rating - Google Reviews', 'g-business-reviews-rating'), 'manage_options', __CLASS__ . '_settings', array($this, 'admin_settings')));
		}
		else
		{
			$icon = 'data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0idXRmLTgiPz4KPHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCAyNTYgMjU2IiB4bWw6c3BhY2U9InByZXNlcnZlIj4KPHBhdGggZmlsbD0iI0EwQTVBQSIgZD0iTTIxNi4wNyAxNDIuMDQ2IDI1Ni40NDEgMTEzaC00OS44OTRsLTE1LjM1My00Ny4zMDRMMTc1Ljg0MSAxMTNoLTQ5Ljg5NGw0MC4zNzEgMjkuMDQ2LTE1LjM1OSA0Ny4xNzUgNDAuMjM1LTI5LjMxNyA0MC4yMzYgMjkuMzU0em0tOTUuNzk5LTE4LjEzNGMwLTMuNjI0LS41NTgtNy45MTItMS4zOTQtMTAuOTEySDYydjIzaDMyLjYyMWMtMS4zMDYgNi4zNDktNC4zODkgMTEuNzE5LTguODA1IDE1LjY4OGwtNC4wNDMgMy4xNDljLTUuNDc3IDMuMzU3LTEyLjMyNyA1LjE1My0yMC4wNTEgNS4xNTMtMTYuMTA3IDAtMjkuNjkyLTEwLjQxNS0zNC40MzYtMjQuODk5LTEuMTY4LTMuNTY4LTEuODA5LTcuMzc4LTEuODA5LTExLjM0NSAwLTMuOTg2LjY0Ny03LjgxNCAxLjgyNi0xMS4zOTYgNC43NTktMTQuNDU4IDE4LjMzMS0yNC44NDkgMzQuNDE5LTI0Ljg0OSA4LjY0MyAwIDE2LjQ1IDMuMDY3IDIyLjU4MyA4LjA4NWwxNy44NDQtMTcuODQ0Yy0xMC44NzMtOS40NzktMjQuODE0LTE1LjMzNC00MC40MjctMTUuMzM0LTI0LjI0IDAtNDUuMDcxIDEzLjg4Mi01NS4wNDggMzQuMTY2LTQuMDIzIDguMTgtNi4yODkgMTcuMzk2LTYuMjg5IDI3LjE3MSAwIDkuNzY4IDIuMjYzIDE4Ljk3OSA2LjI4MSAyNy4xNTQgOS45NzQgMjAuMjk0IDMwLjgxIDM0LjE4MyA1NS4wNTYgMzQuMTgzIDEzLjkxNyAwIDI3LjI0Ni00LjYxIDM3LjY2OS0xMy4yNzYuMDEtLjAwNy4wMjItLjAxMi4wMzEtLjAxOSA0LjQyMy0zLjMyNSA4LjUyOS04Ljk1OSA4LjUyOS04Ljk1OSA3LjYyOS05LjkyNSAxMi4zMi0yMi45MzIgMTIuMzItMzguOTE2eiIvPgo8L3N2Zz4=';
			$pages = array(array('add_menu_page', __('Google Reviews', 'g-business-reviews-rating'), __('Google Reviews', 'g-business-reviews-rating'), 'edit_published_posts', __CLASS__, array($this, 'admin_settings'), $icon, 51));
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
	
	public function sync()
	{
		// Handle synchronization from CRON job
		
		if (!defined('DOING_CRON') || defined('DOING_CRON') && !DOING_CRON)
		{
			return FALSE;
		}

		$this->place_id = get_option(__CLASS__ . '_place_id');

		if ($this->place_id == NULL || get_option(__CLASS__ . '_api_key') == NULL)
		{
			return FALSE;
		}

		$this->local_images = get_option(__CLASS__ . '_local_images', FALSE);

		if ($this->local_images && !is_numeric(get_transient(__CLASS__ . '_avatars_downloaded')))
		{
			$this->reviews = get_option(__CLASS__ . '_reviews', array());

			if (is_array($this->reviews))
			{
				$avatars_added = array();

				foreach ($this->reviews as $key => $a)
				{
					if (count($avatars_added) >= 10)
					{
						break;
					}

					if (isset($a['avatar']) && is_string($a['avatar']))
					{
						continue;
					}

					list($profile_photo_url, $avatar) = $this->set_avatar($a, $key);
					$this->reviews[$key]['profile_photo_url'] = $profile_photo_url;
					$this->reviews[$key]['avatar'] = $avatar;
					$avatars_added[] = $avatar;
				}

				if (!empty($avatars_added))
				{
					delete_transient(__CLASS__ . '_reviews_shuffled');
					wp_cache_delete('reviews_shuffled', __CLASS__);
					wp_cache_delete('reviews', __CLASS__);
					update_option(__CLASS__ . '_reviews', $this->reviews, 'no');
				}
				else
				{
					set_transient(__CLASS__ . '_avatars_downloaded', time(), MONTH_IN_SECONDS);
				}
			}
		}
		
		$update = get_option(__CLASS__ . '_update', NULL);
		$modifier = (get_option(__CLASS__ . '_retrieval_sort', 'most_relevant') == NULL) ? 0.5 : 1;
		$retrieval = get_option(__CLASS__ . '_retrieval', array());
		$last_retrieval = (isset($retrieval['requests']) && is_array($retrieval['requests'])) ? end($retrieval['requests']) : array();

		if (!is_numeric($update))
		{
			return FALSE;
		}

		switch ($update)
		{
		case 168:
			if (!empty($last_retrieval) && isset($last_retrieval['place_id']) && $last_retrieval['place_id'] == $this->place_id && isset($last_retrieval['time']) && ((defined('DISABLE_WP_CRON') && DISABLE_WP_CRON && (time() - $last_retrieval['time']) < 14514900 * $modifier) || ((!defined('DISABLE_WP_CRON') || defined('DISABLE_WP_CRON') && !DISABLE_WP_CRON) && (time() - $last_retrieval['time']) < 14514300 * $modifier)))
			{
				return FALSE;
			}
			
			$this->set_data(TRUE);
			break;
		case 24:
			if (!empty($last_retrieval) && isset($last_retrieval['place_id']) && $last_retrieval['place_id'] == $this->place_id && isset($last_retrieval['time']) && ((defined('DISABLE_WP_CRON') && DISABLE_WP_CRON && (time() - $last_retrieval['time']) < 86100 * $modifier) || ((!defined('DISABLE_WP_CRON') || defined('DISABLE_WP_CRON') && !DISABLE_WP_CRON) && (time() - $last_retrieval['time']) < 72000 * $modifier)))
			{
				return FALSE;
			}
			
			$this->set_data(TRUE);
			break;
		case 6:
			if (!empty($last_retrieval) && isset($last_retrieval['place_id']) && $last_retrieval['place_id'] == $this->place_id && isset($last_retrieval['time']) && ((defined('DISABLE_WP_CRON') && DISABLE_WP_CRON && (time() - $last_retrieval['time']) < 21300 * $modifier) || ((!defined('DISABLE_WP_CRON') || defined('DISABLE_WP_CRON') && !DISABLE_WP_CRON) && (time() - $last_retrieval['time']) < 19800 * $modifier)))
			{
				return FALSE;
			}
			
			$this->set_data(TRUE);
			break;
		case 1:
			if (!empty($last_retrieval) && isset($last_retrieval['place_id']) && $last_retrieval['place_id'] == $this->place_id && isset($last_retrieval['time']) && ((defined('DISABLE_WP_CRON') && DISABLE_WP_CRON && (time() - $last_retrieval['time']) < 3300) || ((!defined('DISABLE_WP_CRON') || defined('DISABLE_WP_CRON') && !DISABLE_WP_CRON) && (time() - $last_retrieval['time']) < 2700)))
			{
				return FALSE;
			}
			
			$this->set_data(TRUE);
			break;
		default:
			return FALSE;
		}
		
		return TRUE;
	}
	
	private function admin_current()
	{
		// Check if the plugin is showing in the Dashboard

		if (!current_user_can('edit_published_posts', __CLASS__))
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
	
	private function valid($check = 'status')
	{
		// Check setup uses valid data and returning a result
		
		if ($this->demo)
		{
			return TRUE;
		}
		
		$api_key = get_option(__CLASS__ . '_api_key');
		$place_id = get_option(__CLASS__ . '_place_id');
		
		if ((!is_string($api_key) || is_string($api_key) && mb_strlen($api_key) < 10) || (!is_string($place_id) || is_string($place_id) && mb_strlen($place_id) < 10))
		{
			return FALSE;
		}

		switch ($check)
		{
		case 'api':
		case 'api_key':
		case 'restriction':
		case 'restrictions':
			return (!empty($this->data) && isset($this->data['status']) && preg_match('/^REQUEST_DENIED$/i', $this->data['status']) && preg_match('/referr?er\s+restrictions?/i', $this->data['error_message']));
		case 'billing':
			return (!empty($this->data) && isset($this->data['status']) && preg_match('/^REQUEST_DENIED$/i', $this->data['status']) && preg_match('/billing/i', $this->data['error_message']));
		default:
			break;
		}
		
		return (!empty($this->data) && isset($this->data['status']) && preg_match('/^OK$/i', $this->data['status']));
	}
	
	public function retrieved_data_check($current = FALSE)
	{
		// Check to display retrieved data
		
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
		
		$this->retrieved_data_exists = (get_option('google_business_reviews_rating_place_id') != NULL && $this->get_data('boolean'));
		
		return $this->retrieved_data_exists;
	}
	
	public function admin_welcome()
	{
		// Set and process welcome page in the Dashboard

		if (!get_transient(__CLASS__ . '_welcome'))
		{
			return;
		}
		
		delete_transient(__CLASS__ . '_welcome');
		
		if (is_network_admin() || isset($_GET['activate-multi']) || get_option(__CLASS__ . '_place_id') != NULL || get_option(__CLASS__ . '_api_key') != NULL)
		{
			return;
		}
		
		$this->section = 'welcome';
		update_option(__CLASS__ . '_section', $this->section, 'no');
		wp_safe_redirect(add_query_arg(array('page' => 'google_business_reviews_rating_settings'), admin_url('options-general.php')));

		return;
	}
	
	public function admin_settings()
	{
		// Set and process settings in the Dashboard

		if (!$this->editor && !$this->administrator)
		{
			wp_die(__('You do not have sufficient permission to access this page.', 'g-business-reviews-rating'));
		}
		
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
		);
		$this->business_types = array(
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
		);
		$this->price_ranges = array(
			1 => array(
					'name' => __('Inexpensive $', 'g-business-reviews-rating'),
					'symbol' => '$'
				),
			2 => array(
					'name' => __('Moderate $$', 'g-business-reviews-rating'),
					'symbol' => str_repeat('$', 2)
				),
			3 => array(
					'name' => __('Expensive $$$', 'g-business-reviews-rating'),
					'symbol' => str_repeat('$', 3)
				),
			4 => array(
					'name' => __('Very Expensive $$$$', 'g-business-reviews-rating'),
					'symbol' => str_repeat('$', 4)
				)
		);

		$this->section = get_option(__CLASS__ . '_section');
		$this->places = get_option(__CLASS__ . '_places');
		$this->show_reviews = (!is_numeric(get_option(__CLASS__ . '_limit')) || is_numeric(get_option(__CLASS__ . '_limit')) && get_option(__CLASS__ . '_limit') > 0);
		$this->count_reviews_all = $this->reviews_count();
		$this->count_reviews_active = $this->reviews_count(NULL, TRUE);
		
		include(plugin_dir_path(__FILE__) . 'templates/settings.php');
	}
	
	public function admin_notices()
	{
		// Handle Dashboard notices
		
		if (!current_user_can('manage_options', __CLASS__) || !$this->admin_current())
		{
			return;
		}
		
		$html = '';
		
		if (is_string(get_option(__CLASS__ . '_api_key')) && is_string(get_option(__CLASS__ . '_place_id')))
		{
			$this->set_data();
			
			if (!isset($this->data['status']) || preg_match('/^OK$/i', $this->data['status']))
			{
				$html = '';
			}
			elseif (preg_match('/^REQUEST[\s_-]?DENIED$/i', $this->data['status']))
			{
				$html = '<div id="google-business-reviews-rating-settings-message" class="notice notice-error invisible is-dismissible">
	<p>'
				/* translators: %s refers to useful URLs to resolve errors and should remain untouched */
				. sprintf(__('<strong>Error:</strong> Your Google API Key is not valid for this request and permission is denied. Please check your Google <a href="%s" target="_blank">API Key</a>.', 'g-business-reviews-rating'), 'https://developers.google.com/maps/documentation/javascript/get-api-key') . '</p>
</div>
';
			}
			elseif (preg_match('/^INVALID[\s_-]?REQUEST$/i', $this->data['status']))
			{
				$html = '<div id="google-business-reviews-rating-settings-message" class="notice notice-error invisible is-dismissible">
	<p>'
				/* translators: %s refers to useful URLs to resolve errors and should remain untouched */
				. sprintf(__('<strong>Error:</strong> Google has returned an invalid request error. Please check your <a href="%s" target="_blank">Place ID</a>.', 'g-business-reviews-rating'), 'https://developers.google.com/places/place-id') . '</p>
</div>
';
			}
			elseif (preg_match('/^NOT[\s_-]?FOUND$/i', $this->data['status']))
			{
				$html = '<div id="google-business-reviews-rating-settings-message" class="notice notice-error invisible is-dismissible">
	<p>'
				/* translators: %s refers to useful URLs to resolve errors and should remain untouched */
				. sprintf(__('<strong>Error:</strong> Google has not found data for the current Place ID. Please ensure you search for a specific business location; not a region or coordinates using the <a href="%s" target="_blank">Place ID Finder</a>.', 'g-business-reviews-rating'), 'https://developers.google.com/places/place-id') . '</p>
</div>
';
			}
			else
			{
				$html = '<div id="google-business-reviews-rating-settings-message" class="notice notice-error invisible is-dismissible">
	<p>' . ((isset($this->data['error_message'])) ? preg_replace('/\s+rel="nofollow"/i', ' target="_blank"', '<strong>' . __('Error:', 'g-business-reviews-rating') . '</strong> ' . $this->data['error_message']) : __('<strong>Error:</strong> Unknown — Please check Retrieved data to find out more information.', 'g-business-reviews-rating')) . '</p>
</div>
';
			}
		}
		
		if ($html == '')
		{
			return;
		}
		
		echo wp_kses($html, array('div' => array('id' => array(), 'class' => array()), 'span' => array('id' => array(), 'class' => array()), 'p' => array('id' => array(), 'class' => array()), 'a' => array('href' => array(), 'target' => array(), 'class' => array()), 'code' => array(), 'strong' => array(), 'em' => array()));
	}
	
	public function admin_ajax()
	{
		// Handle AJAX requests from Dashboard
		
		$ret = array();
		$allow_editor = get_option(__CLASS__ . '_editor', TRUE);
		$this->administrator = (current_user_can('manage_options', __CLASS__));
		$this->editor = (!$this->administrator && $allow_editor && current_user_can('edit_published_posts', __CLASS__));

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
		$review = (isset($_POST['review']) && is_string($_POST['review'])) ? preg_replace('/[^\w_]/', '', $this->sanitize_input($_POST['review'])) : NULL;
		$reviews = (isset($_POST['reviews']) && is_array($_POST['reviews'])) ? array_unique(stripslashes_deep($_POST['reviews']), SORT_REGULAR) : array();
		$order = (isset($_POST['order']) && is_array($_POST['order'])) ? array_unique(stripslashes_deep($_POST['order'])) : array();
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
		$ids = (isset($_POST['id']) && is_string($_POST['id'])) ? preg_split('/,\s*/', $this->sanitize_input($_POST['id'])) : array();
		$id = (isset($ids[0])) ? $ids[0] : NULL;
		$stylesheet = (isset($_POST['stylesheet']) && is_numeric($_POST['stylesheet']) && $_POST['stylesheet'] >= 0 && $_POST['stylesheet'] <= 2) ? intval($_POST['stylesheet']) : 1;
		$javascript = (isset($_POST['javascript']) && is_numeric($_POST['javascript']) && $_POST['javascript'] >= 0 && $_POST['javascript'] <= 2) ? intval($_POST['javascript']) : 1;
		$custom_styles = (isset($_POST['custom_styles']) && is_string($_POST['custom_styles']) && mb_strlen($_POST['custom_styles']) > 2 && !preg_match('/<\?(?:php|=)?/i', $_POST['custom_styles'])) ? $this->sanitize_input($_POST['custom_styles']) : NULL;
		$roles_editor = (isset($_POST['roles_editor']) && (is_bool($_POST['roles_editor']) && $_POST['roles_editor'] || is_string($_POST['roles_editor']) && preg_match('/^(?:true|[1-9])$/i', $_POST['roles_editor'])));
		$import_type = (isset($_POST['import_type']) && is_string($_POST['import_type']) && preg_match('/^(?:original|translation)$/i', $_POST['import_type'])) ? $this->sanitize_input($_POST['import_type']) : NULL;
		$link = (isset($_POST['link']) && is_string($_POST['link']) && mb_strlen($_POST['link']) < 255) ? $this->sanitize_input($_POST['link']) : NULL;
		$nonce = (isset($_POST['nonce']) && is_string($_POST['nonce']) && preg_match('/^[0-9a-f]{8,128}$/i', $_POST['nonce'])) ? $this->sanitize_input($_POST['nonce']) : NULL;

		switch($type)
		{
		case 'section':
			if ($this->editor)
			{
				$section = ($section == 'shortcodes' || $section == 'about' || $section == 'reviews') ? $section : 'reviews';
			}

			$this->section = $section;
			update_option(__CLASS__ . '_section', $this->section, 'no');
			$ret = array(
				'success' => TRUE
			);
			break;
		case 'notification_action':
			if (preg_match('/^notification rate [a-z]{2,25}$/', $notification_action))
			{
				self::log($notification_action);
				$logged = TRUE;
			}

			$ret = array(
				'notification_action' => mb_strtolower($notification_action),
				'link' => $link,
				'success' => $logged
			);
			break;
		case 'sort':
			$clear = FALSE;
			$existing_sort = get_option(__CLASS__ . '_review_sort_admin', NULL);
			
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
	
			update_option(__CLASS__ . '_review_sort_admin', $sort, 'no');
			$ret = array(
				'ids' => $this->get_reviews('ids'),
				'clear' => $clear,
				'review_sort' => $this->review_sort,
				'review_sort_asc' => $this->review_sort_asc,
				'success' => TRUE
			);
			$this->review_sort = $sort;
			break;
		case 'welcome':
			$this->section = get_option(__CLASS__ . '_section');

			if ($this->section != 'welcome')
			{
				$ret = array(
					'success' => FALSE
				);
				break;
			}

			if (!wp_verify_nonce($nonce, 'gmbrr_nonce_' . $this->section))
			{
				$ret = array(
					'message' => __('Your session has expired, please refresh this page.', 'g-business-reviews-rating'),
					'success' => FALSE
				);
				break;
			}
			
			$this->sanitize_api_key($api_key);
			$this->sanitize_place_id($place_id);
			$this->section = NULL;
			update_option(__CLASS__ . '_api_key', $this->api_key, 'no');
			update_option(__CLASS__ . '_place_id', $this->place_id, 'no');
			update_option(__CLASS__ . '_language', $language, 'no');
			update_option(__CLASS__ . '_retrieval_translate', $retrieval_translate, 'no');
			update_option(__CLASS__ . '_update', $update, 'no');
			update_option(__CLASS__ . '_section', $this->section, 'no');

			$ret = array(
				'message' => __('Successfully set Google Places credentials.', 'g-business-reviews-rating'),
				'success' => TRUE
			);
			break;
		case 'demo':
			$this->section = get_option(__CLASS__ . '_section');

			if ($this->section != 'welcome')
			{
				$ret = array(
					'success' => FALSE
				);
				break;
			}

			if (!wp_verify_nonce($nonce, 'gmbrr_nonce_' . $this->section))
			{
				$ret = array(
					'message' => __('Your session has expired, please refresh this page.', 'g-business-reviews-rating'),
					'success' => FALSE
				);
				break;
			}

			$this->section = NULL;
			$this->sanitize_demo(TRUE);
			update_option(__CLASS__ . '_demo', $this->demo, 'yes');
			update_option(__CLASS__ . '_section', $this->section, 'no');
			
			$ret = array(
				'success' => TRUE
			);
			break;
		case 'import':
			if (!wp_verify_nonce($nonce, 'gmbrr_nonce'))
			{
				$ret = array(
					'count' => 0,
					'errors' => array(),
					'message' => __('Your session has expired, please refresh this page.', 'g-business-reviews-rating'),
					'success' => FALSE
				);
				break;
			}

			if ($this->demo || empty($reviews))
			{
				$ret = array(
					'count' => 0,
					'errors' => array(),
					'message' => __('No reviews imported.', 'g-business-reviews-rating'),
					'success' => FALSE
				);
				break;
			}

			$this->set_data();
			$review_backup = (is_array($this->reviews)) ? $this->reviews : array();
			$add = array();
			$errors = array();
			
			foreach ($reviews as $i => $review)
			{
				if (!is_numeric($review['rating']))
				{
					if (!array_key_exists('rating', $errors))
					{
						$errors['rating'] = array();
					}
					
					$errors['rating'][] = $i;
					continue;
				}
				
				if (!preg_match('/^.+[^\d](\d{20,120})(?:[^\d].*)?$/', $review['author_url'], $m))
				{
					if (!array_key_exists('author', $errors))
					{
						$errors['author'] = array();
					}
					
					$errors['author'][] = $i;
					continue;
				}
				
				$author_url_id = $m[1];

				foreach ($this->reviews as $key => $a)
				{
					if (!preg_match('/^.+[^\d](\d{20,120})[^\d].*$/', $a['author_url'], $m))
					{
						continue;
					}
					
					if ($review['author_name'] == $a['author_name'] || $author_url_id == $m[1])
					{
						continue(2);
					}
				}
			
				$add[] = $i;
			}
			
			$use_relative_time_description = (!$this->translation_exists(TRUE));
			$max_id = (is_array($this->reviews) && !empty($this->reviews)) ? ((function_exists('array_column')) ? max(array_column($this->reviews, 'id')) : count($this->reviews)) : 0;
			$count = 0;
			
			foreach ($add as $i)
			{
				$review = $this->sanitize_array($reviews[$i]);
				$relevance = FALSE;
				
				if (!preg_match('/^(\d+)[^\d]+(\d+)[^\d]+(\d+)(?:[^\d].*)?$/', $review['time'], $t))
				{
					if (!array_key_exists('time', $errors))
					{
						$errors['time'] = array();
					}
					
					$errors['time'][] = $i;
					continue;
				}
				
				$time = mktime(0, 0, 0, $t[2], $t[3], $t[1]);
				$key = $time . '_' . $review['rating'] . '_' . md5($review['author_name'] . '_' . mb_substr($review['text'], 0, 100));
				
				if (array_key_exists($key, $this->reviews))
				{
					continue;
				}
				
				$language = (array_key_exists('language', $review) && $review['text'] != NULL) ? $review['language'] : (((array_key_exists('translated', $review) && (!$review['translated'] || $review['translated'] && $import_type == 'translated')) && $review['text'] != NULL) ? preg_replace('/^(?:[^?]+)\?(?:hl=([0-9a-z]+)[0-9a-z-]*).+$/i', '$1', $review['author_url']) : NULL);
				$author_url = preg_replace('/^([^?]+)(?:\?.+)?$/', '$1', $review['author_url']);
				
				if (is_array($order) && !empty($order))
				{
					foreach (array_values($order) as $i => $author_url_check)
					{
						if ($author_url == $author_url_check)
						{
							$relevance = $i + 1;
							break;
						}
					}
				}
				
				$a = array(
					'id' => $max_id + $count + 1,
					'place_id' => $this->place_id,
					'order' => (is_numeric($relevance)) ? $relevance : $max_id + $count + 1,
					'author_name' => $review['author_name'],
					'author_url' => $author_url,
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
				);

				if (!get_option(__CLASS__ . '_local_images', FALSE))
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
			
			if ($count < 1)
			{
				$message = array(__('No reviews imported', 'g-business-reviews-rating'));
				
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

				$ret = array(
					'count' => $count,
					'errors' => $errors,
					/* translators: separator character and spacing between multiple message elements; spacing is important */
					'message' => implode(__('; ', 'g-business-reviews-rating'), $message),
					'success' => FALSE
				);
				break;
			}

			global $wpdb;

			delete_transient(__CLASS__ . '_reviews_shuffled');
			wp_cache_delete('reviews_shuffled', __CLASS__);
			wp_cache_delete('reviews', __CLASS__);
			update_option(__CLASS__ . '_reviews', $this->reviews, 'no');
			
			$this->set_reviews(TRUE);
			$this->count_reviews_all = $this->reviews_count();
			$this->count_reviews_active = $this->reviews_count(NULL, TRUE);
			
			if (count($review_backup) >= $this->count_reviews_all)
			{
				$this->reviews = $review_backup;
				update_option(__CLASS__ . '_reviews', $this->reviews, 'no');
				update_option(__CLASS__ . '_additional_array_sanitization', TRUE, 'yes');
				$this->set_reviews(TRUE);
				$this->reviews_filtered = $this->reviews;
				
				if ($count > 10)
				{
					$ret = array(
						'count' => $count,
						'errors' => $errors,
						/* translators: %u: number of reviews and should remain untouched */
						'message' => sprintf(__('Review import failed. Please select a smaller number of reviews, less than %u.', 'g-business-reviews-rating'), $count),
						'success' => FALSE
					);
					break;
				}

				$ret = array(
					'count' => $count,
					'errors' => $errors,
					'message' => __('Review import failed.', 'g-business-reviews-rating'),
					'success' => FALSE
				);
				break;
			}
			
			$review_verify = get_option(__CLASS__ . '_reviews');

			if (is_array($review_verify) && count($review_verify) == count($this->reviews))
			{
				/*
					Plugin Author Note: There is a fault with the WordPress function maybe_serialize() that causes all reviews to be reset. This check prevents that from happening.
				*/
				
				$review_verify = $wpdb->get_var($wpdb->prepare("SELECT option_value FROM $wpdb->options WHERE option_name = %s LIMIT 1", __CLASS__ . '_reviews'));
				$review_verify = (is_string($review_verify)) ? maybe_unserialize($review_verify) : array();
			}

			if (!is_array($review_verify) || is_array($review_verify) && count($review_verify) != count($this->reviews))
			{
				$this->reviews = $review_backup;
				update_option(__CLASS__ . '_reviews', $this->reviews, 'no');
				$this->set_reviews(TRUE);
				$this->set_data(TRUE);
				self::log('import failed', array('count' => $count, 'errors' => $errors));
				
				if ($count > 10)
				{
					$ret = array(
						'count' => $count,
						'errors' => $errors,
						/* translators: %u: number of reviews and should remain untouched */
						'message' => sprintf(__('Review import failed due the handling of serialized data by WordPress. Please select a smaller number of reviews, less than %u.', 'g-business-reviews-rating'), $count),
						'success' => FALSE
					);
					break;
				}
				

				$ret = array(
					'count' => $count,
					'errors' => $errors,
					'message' => __('Review import failed due the handling of serialized data by WordPress.', 'g-business-reviews-rating'),
					'success' => FALSE
				);
				break;
			}

			$this->reviews_filtered = $this->reviews;
			$this->section = 'reviews';
			update_option(__CLASS__ . '_section', $this->section, 'no');
			
			/* translators: %u: number of reviews and should remain untouched */
			$message = array(sprintf(_n('Successfully imported %u review.', 'Successfully imported %u reviews.', $count, 'g-business-reviews-rating'), $count));
			
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
				self::log('import', array('count' => $count, 'errors' => $errors, 'message' => implode('; ', $message)));
			}
			else
			{
				self::log('import', array('count' => $count, 'message' => implode('; ', $message)));
			}

			$ret = array(
				'count' => $count,
				'errors' => $errors,
				/* translators: separator character and spacing between multiple message elements; spacing is important */
				'message' => implode(__('; ', 'g-business-reviews-rating'), $message),
				'success' => TRUE
			);
			break;
		case 'submitted':
			if (!wp_verify_nonce($nonce, 'gmbrr_nonce'))
			{
				$ret = array(
					'review' => NULL,
					'submitted' => FALSE,
					'message' => __('Your session has expired, please refresh this page.', 'g-business-reviews-rating'),
					'success' => FALSE
				);
				break;
			}

			$this->set_data();

			if ($this->demo || !array_key_exists($review, $this->reviews) || !isset($this->reviews[$review]['time_estimate']) || isset($this->reviews[$review]['time_estimate']) && !$this->reviews[$review]['time_estimate'] || !preg_match('/^(\d+)[^\d]+(\d+)[^\d]+(\d+)(?:[^\d].*)?$/', $submitted, $t))
			{
				$ret = array(
					'review' => $review,
					'submitted' => $submitted,
					'success' => FALSE
				);
				break;	
			}

			global $wpdb;

			$time = mktime(0, 0, 0, $t[2], $t[3], $t[1]);
			$this->reviews[$review]['time'] = $time;
			update_option(__CLASS__ . '_reviews', $this->reviews, 'no');
			
			$this->set_reviews(TRUE);
			$this->reviews_filtered = $this->reviews;
			$ret = array(
				'review' => $review,
				'submitted' => $submitted,
				'time' => $time,
				'success' => TRUE
			);

			break;
		case 'language':
			if (!wp_verify_nonce($nonce, 'gmbrr_nonce'))
			{
				$ret = array(
					'review' => NULL,
					'submitted' => FALSE,
					'message' => __('Your session has expired, please refresh this page.', 'g-business-reviews-rating'),
					'success' => FALSE
				);
				break;
			}

			$this->set_data();

			if ($this->demo || !array_key_exists($review, $this->reviews) || $language != NULL && !preg_match('/^(\w{2}l?)(?:[_-](\w+))?$/', $language, $m))
			{
				$ret = array(
					'review' => $review,
					'language' => $language,
					'success' => FALSE
				);
				break;	
			}

			global $wpdb;

			$language = ($language != NULL) ? ((isset($m[2]) && $m[2] != NULL) ? $m[1] . '-' . $m[2] : $m[1]) : NULL;
			$this->reviews[$review]['language'] = $language;
			update_option(__CLASS__ . '_reviews', $this->reviews, 'no');
			
			$this->set_reviews(TRUE);
			$this->reviews_filtered = $this->reviews;
			$ret = array(
				'review' => $review,
				'language' => $language,
				'success' => TRUE
			);

			break;
		case 'icon-delete':
		case 'icon_delete':
		case 'icon-remove':
		case 'icon_remove':
			if (!wp_verify_nonce($nonce, 'gmbrr_nonce'))
			{
				$ret = array(
					'id' => NULL,
					'image' => NULL,
					'message' => __('Your session has expired, please refresh this page.', 'g-business-reviews-rating'),
					'success' => FALSE
				);
				break;
			}

			$this->delete_icon();
			
			$ret = array(
				'id' => NULL,
				'image' => NULL,
				'success' => TRUE
			);
			break;	
		case 'icon':
			if (!wp_verify_nonce($nonce, 'gmbrr_nonce'))
			{
				$ret = array(
					'id' => NULL,
					'image' => NULL,
					'message' => __('Your session has expired, please refresh this page.', 'g-business-reviews-rating'),
					'success' => FALSE
				);
				break;
			}
			
			if (!is_numeric($id))
			{
				$this->delete_icon();
				
				$ret = array(
					'id' => NULL,
					'image' => NULL,
					'success' => FALSE
				);
				break;	
			}
			
			$this->set_icon($id);
			
			if (!is_string($this->icon_image_url) || is_string($this->icon_image_url) && mb_strlen($this->icon_image_url) < 5)
			{
				$this->delete_icon();
				
				$ret = array(
					'id' => NULL,
					'image' => NULL,
					'success' => FALSE
				);
				
				break;	
			}
			
			$ret = array(
				'id' => $this->icon_image_id,
				'image' => preg_replace('/\s+(?:width|height)="\d*"/i', '', wp_get_attachment_image($this->icon_image_id, 'large', FALSE, array('id' => 'icon-image-preview-image'))),
				'success' => TRUE
			);
			break;
		case 'logo-delete':
		case 'logo_delete':
		case 'logo-remove':
		case 'logo_remove':
			if (!wp_verify_nonce($nonce, 'gmbrr_nonce'))
			{
				$ret = array(
					'id' => NULL,
					'image' => NULL,
					'message' => __('Your session has expired, please refresh this page.', 'g-business-reviews-rating'),
					'success' => FALSE
				);
				break;
			}

			$this->delete_logo();
			
			$ret = array(
				'id' => NULL,
				'image' => NULL,
				'success' => TRUE
			);
			break;	
		case 'logo':
			if (!wp_verify_nonce($nonce, 'gmbrr_nonce'))
			{
				$ret = array(
					'id' => NULL,
					'image' => NULL,
					'message' => __('Your session has expired, please refresh this page.', 'g-business-reviews-rating'),
					'success' => FALSE
				);
				break;
			}

			if (!is_numeric($id))
			{
				$this->delete_logo();
				
				$ret = array(
					'id' => NULL,
					'image' => NULL,
					'success' => FALSE
				);
				break;	
			}
			
			$this->set_logo($id);
			
			if (!is_string($this->logo_image_url) || is_string($this->logo_image_url) && mb_strlen($this->logo_image_url) < 5)
			{
				$this->delete_logo();
				
				$ret = array(
					'id' => NULL,
					'image' => NULL,
					'success' => FALSE
				);
				
				break;	
			}
			
			$ret = array(
				'id' => $this->logo_image_id,
				'image' => preg_replace('/\s+(?:width|height)="\d*"/i', '', wp_get_attachment_image($this->logo_image_id, 'large', FALSE, array('id' => 'logo-image-preview-image'))),
				'success' => TRUE
			);
			break;
		case 'preview':
			if (!wp_verify_nonce($nonce, 'gmbrr_nonce'))
			{
				$ret = array(
					'html' => NULL,
					'message' => __('Your session has expired, please refresh this page.', 'g-business-reviews-rating'),
					'success' => FALSE
				);
				break;
			}

			$ret = array(
				'html' => $this->admin_preview(),
				'status' => $status,
				'success' => TRUE
			);
			break;
		case 'structured-data':
		case 'structured_data':
			if (!wp_verify_nonce($nonce, 'gmbrr_nonce'))
			{
				$ret = array(
					'data' => NULL,
					'message' => __('Your session has expired, please refresh this page.', 'g-business-reviews-rating'),
					'success' => FALSE
				);
				break;
			}

			$data = array();
			
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
				$data['price_range'] = (is_numeric($_POST['price_range'])) ? intval($_POST['price_range']) : NULL;
			}
			
			$ret = array(
				'data' => $this->structured_data('json', $data),
				'success' => TRUE
			);
			break;
		case 'status':
			if (!wp_verify_nonce($nonce, 'gmbrr_nonce'))
			{
				$ret = array(
					'review' => NULL,
					'status' => FALSE,
					'message' => __('Your session has expired, please refresh this page.', 'g-business-reviews-rating'),
					'success' => FALSE
				);
				break;
			}

			$this->set_data();

			if (!array_key_exists($review, $this->reviews) || isset($this->reviews[$review]['status']) && $this->reviews[$review]['status'] == $status)
			{
				$ret = array(
					'review' => $review,
					'status' => $status,
					'success' => FALSE
				);
				break;	
			}

			global $wpdb;

			$this->reviews[$review]['status'] = $status;
			$this->reviews_filtered = $this->reviews;
			wp_cache_set((($this->demo) ? 'reviews_demo' : 'reviews'), $this->reviews, __CLASS__, HOUR_IN_SECONDS);
	
			if (!$this->demo)
			{
				update_option(__CLASS__ . '_reviews', $this->reviews, 'no');
			}
	
			$ret = array(
				'review' => $review,
				'status' => $status,
				'success' => TRUE
			);
			break;
		case 'styles-scripts':
		case 'styles_scripts':
			if (!wp_verify_nonce($nonce, 'gmbrr_nonce'))
			{
				$ret = array(
					'message' => __('Your session has expired, please refresh this page.', 'g-business-reviews-rating'),
					'success' => FALSE
				);
				break;
			}

			if ($stylesheet == get_option(__CLASS__ . '_stylesheet') && $javascript == get_option(__CLASS__ . '_javascript') && $custom_styles == get_option(__CLASS__ . '_custom_styles'))
			{
				$ret = array(
					'success' => TRUE
				);
			}
			
			update_option(__CLASS__ . '_stylesheet', $stylesheet, 'yes');
			update_option(__CLASS__ . '_javascript', $javascript, 'yes');

			if ($custom_styles == get_option(__CLASS__ . '_custom_styles'))
			{
				$ret = array(
					'message' => __('Successfully saved style and script preference.', 'g-business-reviews-rating'),
					'success' => TRUE
				);
			}
						
			update_option(__CLASS__ . '_custom_styles', $custom_styles, 'yes');
			$fp = FALSE;
			$file = plugin_dir_path(__FILE__) . 'wp/css/custom.css';

			if (!is_file($file))
			{
				if (!is_writable(plugin_dir_path(__FILE__) . 'wp/css/'))
				{
					$ret = array(
						/* translators: %s: file directory and should remain untouched */
						'message' => sprintf(__('Cannot create a new file in plugin directory: %s', 'g-business-reviews-rating'), './wp/css/'),
						'success' => FALSE
					);
					break;
				}
				
				$fp = fopen($file, 'w');
				
				if (!$fp || !is_file($file))
				{
					if ($fp)
					{
						fclose($fp);
					}
					
					$ret = array(
						/* translators: %s: file name and should remain untouched */
						'message' => sprintf(__('Cannot create a new file: %s', 'g-business-reviews-rating'), './wp/css/custom.css'),
						'success' => FALSE
					);
					break;
				}
			}
			
			if (!is_writable($file))
			{
				$ret = array(
					/* translators: %s: file name and should remain untouched */
					'message' => sprintf(__('File at: %s is not writable.', 'g-business-reviews-rating'), './wp/css/custom.css'),
					'success' => FALSE
				);
				break;
			}
			
			if (!$fp)
			{
				$fp = fopen($file, 'w');
			}
				
			if (!$fp)
			{
				$ret = array(
					/* translators: %s: file name and should remain untouched */
					'message' => sprintf(__('Cannot write new data to file at: %s', 'g-business-reviews-rating'), './wp/css/custom.css'),
					'success' => FALSE
				);
				break;
			}
			
			if ($custom_styles != NULL && !fwrite($fp, $custom_styles))
			{
				fclose($fp);
				$ret = array(
					'success' => FALSE
				);
				break;
			}
			
			fclose($fp);
			
			$ret = array(
				'message' => __('Successfully updated styles and scripts.', 'g-business-reviews-rating'),
				'success' => TRUE
			);
			break;
		case 'clear':
		case 'cache':
		case 'clear-cache':
		case 'clear_cache':
			if (!wp_verify_nonce($nonce, 'gmbrr_nonce'))
			{
				$ret = array(
					'message' => __('Your session has expired, please refresh this page.', 'g-business-reviews-rating'),
					'success' => FALSE
				);
				break;
			}

			delete_transient(__CLASS__ . '_reviews_shuffled');
			wp_cache_delete('structured_data', __CLASS__);
			wp_cache_delete('result', __CLASS__);
			wp_cache_delete('result_valid', __CLASS__);
			wp_cache_delete('reviews_shuffled', __CLASS__);
			wp_cache_delete('reviews', __CLASS__);
			$this->data = array();
			$this->result = array();

			if (!$this->set_data(TRUE))
			{
				if (!is_array($this->result) || !is_array($this->data) || empty($this->result) || empty($this->data))
				{
					$ret = array(
						'message' => __('Unable to reset data.', 'g-business-reviews-rating'),
						'success' => FALSE
					);
					break;
				}

				if (!is_array(get_option(__CLASS__ . 'result', FALSE)))
				{
					if (!get_option(__CLASS__ . '_additional_array_sanitization', FALSE))
					{
						$ret = array(
							'message' => __('Unable to save data, consider enabling additional sanitization of retrieved data.', 'g-business-reviews-rating'),
							'success' => FALSE
						);
						break;
					}
					
					$ret = array(
						'message' => __('Unable to save data.', 'g-business-reviews-rating'),
						'success' => FALSE
					);
					break;
				}

				$ret = array(
					'message' => __('Unable to clear cache.', 'g-business-reviews-rating'),
					'success' => FALSE
				);
				break;
			}
			
			$this->section = NULL;
			update_option(__CLASS__ . '_section', $this->section, 'no');
			self::log('cache cleared');

			$ret = array(
				'message' => __('Cache cleared.', 'g-business-reviews-rating'),
				'success' => TRUE
			);
			break;
		case 'delete':
		case 'remove':
			if (!current_user_can('delete_published_posts', __CLASS__))
			{
				$ret = array(
					'message' => __('You do not have sufficient permission to perform this action.', 'g-business-reviews-rating'),
					'success' => FALSE
				);
				break;
			}

			if (!wp_verify_nonce($nonce, 'gmbrr_nonce'))
			{
				$ret = array(
					'message' => __('Your session has expired, please refresh this page.', 'g-business-reviews-rating'),
					'success' => FALSE
				);
				break;
			}

			$this->set_data();
			
			if ($this->demo || !array_key_exists($review, $this->reviews) || isset($this->reviews[$review]['time_estimate']) && !$this->reviews[$review]['time_estimate'] && isset($this->reviews[$review]['removable']) && !$this->reviews[$review]['removable'])
			{
				$ret = array(
					'review' => $review,
					'success' => FALSE
				);
				break;	
			}

			global $wpdb;

			if (isset($this->reviews[$review]['avatar']) && $this->reviews[$review]['avatar'] != NULL)
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

				if (is_dir($upload_directory_plugin) && is_file($upload_directory_plugin . '/' . $this->reviews[$review]['avatar']))
				{
					@unlink($upload_directory_plugin . '/' . $this->reviews[$review]['avatar']);
				}
			}

			unset($this->reviews[$review]);
			update_option(__CLASS__ . '_reviews', $this->reviews, 'no');
			
			$this->set_reviews(TRUE);
			$this->reviews_filtered = $this->reviews;
			$ret = array(
				'review' => $review,
				'success' => TRUE
			);
			break;
		case 'roles':
			if (!wp_verify_nonce($nonce, 'gmbrr_nonce'))
			{
				$ret = array(
					'message' => __('Your session has expired, please refresh this page.', 'g-business-reviews-rating'),
					'success' => FALSE
				);
				break;
			}

			if (!is_bool($roles_editor))
			{
				$ret = array(
					'success' => FALSE
				);
				break;
			}

			update_option(__CLASS__ . '_editor', $roles_editor, 'no');

			$ret = array(
				'roles' => $roles_editor,
				'message' => ($roles_editor) ? __('Permission for editors is enabled.', 'g-business-reviews-rating') : __('Permission for editors is disabled.', 'g-business-reviews-rating'),
				'success' => TRUE
			);
			break;
		case 'reset_notifications':
			if (!$notifications)
			{
				$ret = array(
					'success' => FALSE
				);
				break;
			}
			
			if (!wp_verify_nonce($nonce, 'gmbrr_nonce'))
			{
				$ret = array(
					'message' => __('Your session has expired, please refresh this page.', 'g-business-reviews-rating'),
					'success' => FALSE
				);
				break;
			}
			
			if (!$this->notification_reset())
			{
				$ret = array(
					'success' => TRUE
				);
				break;
			}

			$ret = array(
				'message' => __('Notifications successfully reset.', 'g-business-reviews-rating'),
				'success' => TRUE
			);
			break;
		case 'remove_other_places':
			if (!$remove_other_places || $this->place_id == NULL)
			{
				$ret = array(
					'success' => FALSE
				);
				break;
			}
			
			if (!wp_verify_nonce($nonce, 'gmbrr_nonce'))
			{
				$ret = array(
					'message' => __('Your session has expired, please refresh this page.', 'g-business-reviews-rating'),
					'success' => FALSE
				);
				break;
			}

			if (empty($this->places))
			{
				$this->places = get_option(__CLASS__ . '_places', array());
			}
			
			if (count($this->places) < 2)
			{
				$ret = array(
					'success' => FALSE
				);
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

			update_option(__CLASS__ . '_section', NULL, 'no');
			$ret = array(
				'message' => __('Places successfully cleared.', 'g-business-reviews-rating'),
				'success' => TRUE
			);
			break;
		case 'reset_reviews':
			if (!wp_verify_nonce($nonce, 'gmbrr_nonce'))
			{
				$ret = array(
					'message' => __('Your session has expired, please refresh this page.', 'g-business-reviews-rating'),
					'success' => FALSE
				);
				break;
			}

			if ($remove_other_places && $this->place_id != NULL)
			{
				if (empty($this->places))
				{
					$this->places = get_option(__CLASS__ . '_places', array());
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

			delete_transient(__CLASS__ . '_reviews_shuffled');
			wp_cache_delete('reviews_shuffled', __CLASS__);
			wp_cache_delete('reviews', __CLASS__);
			update_option(__CLASS__ . '_reviews', NULL, 'no');
			update_option(__CLASS__ . '_section', NULL, 'no');
			$this->set_data(TRUE);

			if ($remove_other_places && $this->place_id != NULL)
			{
				$ret = array(
					'message' => __('Places cleared and review archive successfully reset.', 'g-business-reviews-rating'),
					'success' => TRUE
				);
				break;
			}

			$ret = array(
				'message' => __('Review archive successfully reset.', 'g-business-reviews-rating'),
				'success' => TRUE
			);
			break;
		case 'reset':
			if (!current_user_can('activate_plugins', __CLASS__))
			{
				$ret = array(
					'message' => __('You do not have sufficient permission to perform this action.', 'g-business-reviews-rating'),
					'success' => FALSE
				);
				break;
			}

			if (!wp_verify_nonce($nonce, 'gmbrr_nonce'))
			{
				$ret = array(
					'message' => __('Your session has expired, please refresh this page.', 'g-business-reviews-rating'),
					'success' => FALSE
				);
				break;
			}
			
			if ($notifications)
			{
				$this->notification_reset();
			}
			
			$ret = array(
				'message' => __('Plugin successfully reset.', 'g-business-reviews-rating'),
				'success' => $this->reset()
			);
			break;
		default:
			break;
		}

		echo json_encode($ret);
		wp_die();
	}
	
	public static function admin_add_action_links($links, $file)
	{
		// Add action link in Dashboard Plugin list
		
		if (!preg_match('#^([^/]+).*$#', $file, $m1) || !preg_match('#^([^/]+).*$#', plugin_basename(__FILE__), $m2) || $m1[1] != $m2[1])
		{
			return $links;
		}
		
		$new_links = array('settings' => '<a href="' . admin_url('options-general.php?page=google_business_reviews_rating_settings') . '">' . esc_html__('Settings', 'g-business-reviews-rating') . '</a>');
		$links = array_merge($new_links, $links);

		return $links;
	}
	
	public static function admin_add_plugin_meta($links, $file)
	{
		// Add support link in Dashboard Plugin list
		
		if (!preg_match('#^([^/]+).*$#', $file, $m1) || !preg_match('#^([^/]+).*$#', plugin_basename(__FILE__), $m2) || $m1[1] != $m2[1])
		{
			return $links;
		}
		
		$new_links = array(
			'reviews' => '<a href="https://wordpress.org/support/plugin/g-business-reviews-rating/reviews/#new-post" title="' . esc_attr__('Like our plugin? Please leave a review!', 'g-business-reviews-rating') . '" style="color: #ffb900; line-height: 90%; font-size: 1.3em; letter-spacing: -0.12em; position: relative; top: 0.08em;">★★★★★</a>',
			'support' => '<a href="https://designextreme.com/wordpress/gmbrr/" target="_blank" title="' . esc_attr__('Support', 'g-business-reviews-rating') . '">' . esc_html__('Support', 'g-business-reviews-rating') . '</a>'
		);
		$links = array_merge($links, $new_links);
				
		return $links;
	}

	public function admin_css_load()
	{
		// Load style sheet in the Dashboard

		$current_screen = get_current_screen();
		
		if (!$this->admin_current() && $current_screen->base != 'dashboard')
		{
			return;
		}
		
		wp_register_style(__CLASS__ . '_admin_css', plugins_url('g-business-reviews-rating/admin/css/css.css'));
		wp_register_style(__CLASS__ . '_wp_css', plugins_url('g-business-reviews-rating/wp/css/css.css'));
		wp_enqueue_style(__CLASS__ . '_admin_css');
		wp_enqueue_style(__CLASS__ . '_wp_css');
		wp_enqueue_media();
	}
	
	public function admin_js_load()
	{
		// Load Javascript in the Dashboard

		$current_screen = get_current_screen();
		
		if (!$this->admin_current() && $current_screen->base != 'dashboard')
		{
			return;
		}

		wp_register_script(__CLASS__ . '_admin_js', plugins_url('g-business-reviews-rating/admin/js/js.js'));
		wp_localize_script(__CLASS__ . '_admin_js', __CLASS__ . '_admin_ajax', array('url' => admin_url('admin-ajax.php'), 'action' => 'google_business_reviews_rating_admin_ajax'));
		wp_register_script(__CLASS__ . '_wp_js', plugins_url('g-business-reviews-rating/wp/js/js.js'), array('jquery'));
		wp_enqueue_script(__CLASS__ . '_admin_js');
		wp_enqueue_script(__CLASS__ . '_wp_js');
	}
	
	public function wp_css_load()
	{
		// Load style sheet in the front-end
		
		$mode = get_option(__CLASS__ . '_stylesheet', TRUE);
		$compressed = (is_numeric($mode) && $mode == 2 || is_string($mode) && ($mode == 'compress' || $mode == 'compressed' || $mode == 'min'));
		
		wp_register_style(__CLASS__ . '_wp_css', ($compressed) ? plugins_url('g-business-reviews-rating/wp/css/css.min.css') : plugins_url('g-business-reviews-rating/wp/css/css.css'));
		wp_enqueue_style(__CLASS__ . '_wp_css');
		
		if (is_file(plugin_dir_path(__FILE__) . 'wp/css/custom.css') && filesize(plugin_dir_path(__FILE__) . 'wp/css/custom.css') > 20)
		{
			wp_register_style(__CLASS__ . '_wp_custom_css', plugins_url('g-business-reviews-rating/wp/css/custom.css'));
			wp_enqueue_style(__CLASS__ . '_wp_custom_css');
		}
	}
	
	public function wp_js_load()
	{
		// Load Javascript in the front-end
		
		$mode = get_option(__CLASS__ . '_javascript', TRUE);
		$compressed = (is_numeric($mode) && $mode == 2 || is_string($mode) && ($mode == 'compress' || $mode == 'compressed' || $mode == 'min'));
		
		wp_register_script(__CLASS__ . '_wp_js', ($compressed) ? plugins_url('g-business-reviews-rating/wp/js/js.min.js') : plugins_url('g-business-reviews-rating/wp/js/js.js'), array('jquery'));
		wp_enqueue_script(__CLASS__ . '_wp_js');
	}
	
	public function get_data($type = 'array', $place_id = NULL, $valid = FALSE)
	{
		// Return data from Google Places API, place data or an option value
		
		$data = array();
		$ret = NULL;		
		$retrieved_data_formats = array('boolean', 'html', 'json', 'array', NULL);

		if (is_null($place_id) || !is_string($place_id) || is_string($place_id) && mb_strlen($place_id) < 16)
		{
			$place_id = $this->place_id;
		}
		
		if (!in_array($type, $retrieved_data_formats))
		{
			if (empty($this->places))
			{
				$this->places = ($place_id != $this->place_id) ? get_option(__CLASS__ . '_places', array()) : array();
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
				$this->api_key = ($this->api_key != NULL) ? $this->api_key : get_option(__CLASS__ . '_api_key', NULL);
				$this->place_id = ($this->place_id != NULL) ? $this->place_id : get_option(__CLASS__ . '_place_id', NULL);
				$data = $this->retrieve_data($type);
			}
			
			if (($this->dashboard && $valid) || (!$this->dashboard && !$valid && (!isset($data['status'])) || isset($data['status']) && !preg_match('/^OK$/i', $data['status'])))
			{
				$data = get_option(__CLASS__ . '_result_valid', array());
			}
			elseif (!is_array($data))
			{
				$data = ($valid) ? get_option(__CLASS__ . '_result_valid', NULL) : get_option(__CLASS__ . '_result', NULL);
				
				if (!is_array($data))
				{
					$data = array();
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
				$data_check = get_option(__CLASS__ . '_result', array());
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
			
			if (isset($this->data['result']['formatted_address']) && $this->data['result']['formatted_address'] != NULL)
			{
				$ret = $this->data['result']['formatted_address'];
			}
			
			break;
		case 'name':
		case 'vicinity':
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
			
			if (isset($this->data['result'][$type]) && $this->data['result'][$type] != NULL)
			{
				$ret = $this->data['result'][$type];
				break;
			}
			
			if ($type != 'rating')
			{
				break;
			}
			
			if (isset($this->data['result']['reviews']) && is_array($this->data['result']['reviews']) && !empty($this->data['result']['reviews']))
			{
				$ratings = array();
				
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
			
			if (isset($this->data['result']['rating']) && $this->data['result']['rating'] != NULL)
			{
				$ret = (function_exists('number_format_i18n')) ? number_format_i18n($this->data['result']['rating'], 1) : number_format($this->data['result']['rating'], 1);
				break;
			}
						
			if (isset($this->data['result']['reviews']) && is_array($this->data['result']['reviews']) && !empty($this->data['result']['reviews']))
			{
				$ratings = array();
				
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
			
			if (isset($this->data['result']['user_ratings_total']) && $this->data['result']['user_ratings_total'] != NULL)
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
			
			if (isset($this->data['result']['reviews']) && is_array($this->data['result']['reviews']) && !empty($this->data['result']['reviews']))
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
			
			if (isset($this->data['result']['user_ratings_total']) && $this->data['result']['user_ratings_total'] != NULL)
			{
				$ret = (function_exists('number_format_i18n')) ? number_format_i18n($this->data['result']['user_ratings_total'], 0) : number_format($this->data['result']['user_ratings_total'], 0);
				break;
			}
			
			if (isset($this->reviews) && is_array($this->reviews) && !empty($this->reviews))
			{
				$ret = (function_exists('number_format_i18n')) ? number_format_i18n($this->reviews_count($place_id, NULL, FALSE), 0) : number_format($this->reviews_count($place_id, NULL, FALSE), 0);
				break;
			}
			
			if (isset($this->data['result']['reviews']) && is_array($this->data['result']['reviews']) && !empty($this->data['result']['reviews']))
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
	
			$this->logo_image_id = get_option(__CLASS__ . '_logo');
	
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
			
			$seo_titles = get_option('wpseo_titles');
			
			if (is_array($seo_titles) && isset($seo_titles['company_logo']) && is_string($seo_titles['company_logo']))
			{
				$ret = $seo_titles['company_logo'];
				break;
			}
			
			// Intentional continue

		case 'icon':
			if ($this->icon_image_url != NULL)
			{
				$ret = $this->icon_image_url;
				break;
			}
	
			$this->icon_image_id = get_option(__CLASS__ . '_icon');
	
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
			
			if (isset($this->data['result']['icon']) && $this->data['result']['icon'] != NULL)
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
			
			$retrieval = get_option(__CLASS__ . '_retrieval');
			
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
	
	public function set_data($force = NULL, $api_key = NULL, $place_id = NULL)
	{
		// Set data from Google Places with cache check
		
		if (defined('XMLRPC_REQUEST') && XMLRPC_REQUEST || (!is_bool($force) || !$force) && ((defined('DOING_CRON') && DOING_CRON) || $this->dashboard && (isset($_POST['action']) && is_string($_POST['action']) && preg_match('/(?:[\b_-]|^)heartbeat(?:[\b_-]|$)/i', $_POST['action']) || isset($_POST['type']) && is_string($_POST['type']) && preg_match('/(?:[\b_-]|^)cache(?:[\b_-]|$)/i', $_POST['type']) || isset($_POST['log']) && $_POST['log'] != NULL)))
		{
			return FALSE;
		}
		
		if (!is_bool($force) || !$force)
		{
			$force_check = get_transient(__CLASS__ . '_force');
			
			if (is_string($force_check) && preg_match('#^(\d+(?:\.\d+)?)/0$#', $force_check, $m))
			{
				$force = ((time() - intval($m[1])) < 10);
				delete_transient(__CLASS__ . '_force');
			}
		}

		$this->api_key = ($api_key != NULL) ? $api_key : get_option(__CLASS__ . '_api_key');
		$this->place_id = ($place_id != NULL) ? $place_id : get_option(__CLASS__ . '_place_id');		
		
		if (!$force)
		{
			if ($this->dashboard && $this->request_count == 0) 
			{
				$this->data = $this->retrieve_data();
			}
			
			if (is_array($this->data) && !empty($this->data))
			{
				$this->set_reviews();
				return TRUE;
			}
			
			if (!$this->dashboard)
			{
				if ($this->demo)
				{
					$this->data = wp_cache_get('result_demo', __CLASS__);
				}
				elseif (wp_cache_get('result_valid', __CLASS__) != FALSE)
				{
					$this->data = wp_cache_get('result_valid', __CLASS__);
				}
				elseif (wp_cache_get('result', __CLASS__) != FALSE)
				{
					$this->data = wp_cache_get('result', __CLASS__);
				}
			}
			
			if (is_array($this->data) && !empty($this->data))
			{
				$this->set_reviews();
				return TRUE;
			}
			
			if ($this->demo)
			{
				$this->data = $this->retrieve_data();
				$this->set_reviews();
				return (is_array($this->data) && !empty($this->data));
			}
			
			$this->data = ($this->retrieved_data_valid) ? get_option(__CLASS__ . '_result', NULL) : get_option(__CLASS__ . '_result_valid', NULL);
			
			if ((!is_array($this->data) || is_array($this->data) && empty($this->data)) && $this->request_count == 0)
			{
				$this->request_count++;
				$this->data = $this->retrieve_data();
				
				if (!is_array($this->data) || is_array($this->data) && empty($this->data))
				{
					return FALSE;
				}
				
				update_option(__CLASS__ . '_result', $this->data, 'no');
				wp_cache_add('result', $this->data, __CLASS__, HOUR_IN_SECONDS);
				$this->set_reviews();
				return TRUE;
			}
			
			$this->set_reviews();
			return TRUE;
		}
		
		wp_cache_delete('structured_data', __CLASS__);
		wp_cache_delete((($this->demo) ? 'result_demo' : 'result'), __CLASS__);

		if ($this->request_count > 2)
		{
			return FALSE;
		}
		
		$this->data = $this->retrieve_data('array', TRUE);
		
		if ($this->demo)
		{
			wp_cache_add('result_demo', $this->data, __CLASS__, HOUR_IN_SECONDS);

			$this->set_reviews();
			
			return TRUE;
		}
		
		if (!is_array($this->data) || is_array($this->data) && empty($this->data))
		{
			$this->data = $this->result;
			
			if (!is_array($this->data) || is_array($this->data) && empty($this->data))
			{
				return FALSE;
			}
		}
		
		$this->set_reviews(TRUE);

		return TRUE;
	}
	
	public function retrieve_data($format = 'array', $force = FALSE)
	{
		// Collect data from Google Places as JSON string
		
		$ret = '';

		if ($this->demo)
		{
			$this->result = json_decode(GOOGLE_BUSINESS_REVIEWS_RATING_DEMO_RESULT, TRUE);

			switch ($format)
			{
			case 'boolean':
				return TRUE;
			case 'html':
				return '	<pre id="google-business-reviews-rating-data">' . esc_html(json_encode($this->result, JSON_PRETTY_PRINT)) . '</pre>
';
			case 'json':
				return GOOGLE_BUSINESS_REVIEWS_RATING_DEMO_RESULT;
			case 'array':
			default:
				return $this->result;
			}
		}

		if ($this->place_id == NULL || $this->api_key == NULL)
		{
			switch ($format)
			{
			case 'boolean':
				return FALSE;
			case 'html':
				if ($this->place_id == NULL && $this->api_key == NULL)
				{
					$ret = '<p class="error">' . __('Error: Place ID and Google API Key are required.', 'g-business-reviews-rating') . '</p>';
				}
				elseif ($this->place_id == NULL)
				{
					$ret = '<p class="error">' . __('Error: Place ID is required.', 'g-business-reviews-rating') . '</p>';
				}
				elseif ($this->api_key == NULL)
				{
					$ret = '<p class="error">' . __('Error: Google API Key is required.', 'g-business-reviews-rating') . '</p>';
				}
				
				if ($ret != '')
				{
					break;
				}
				
				return '';
			case 'json':
				if ($this->place_id == NULL && $this->api_key == NULL)
				{
					$ret = json_encode(array(
						'success' => FALSE,
						'error' => __('Place ID and Google API Key are required.', 'g-business-reviews-rating')
					));
				}
				elseif ($this->place_id == NULL)
				{
					$ret = json_encode(array(
						'success' => FALSE,
						'error' => __('Error: Place ID is required.', 'g-business-reviews-rating')
					));
				}
				elseif ($this->api_key == NULL)
				{
					$ret = json_encode(array(
						'success' => FALSE,
						'error' => __('Error: Google API Key is required.', 'g-business-reviews-rating')
					));
				}
				
				if ($ret != '')
				{
					return $ret;
				}
				
				return '';
			case 'array':
			default:
				return array();
			}
		}
		
		$data_array = array();
		$data_string = '';
		$recheck = FALSE;
		$retrieval = NULL;
		$last_retrieval = NULL;

		if ($this->request_count > 2)
		{
			$data_array = ($this->dashboard) ? get_option(__CLASS__ . '_result', NULL) : get_option(__CLASS__ . '_result_valid', NULL);
			
			if (!is_array($data_array))
			{
				$data_array = array();
			}
			
			$this->result = $data_array;
				
			switch ($format)
			{
			case 'boolean':
				return (is_array($this->result) && !empty($this->result));
			case 'html':
				if ($this->place_id == NULL && $this->api_key == NULL)
				{
					$ret = '<p class="error">' . __('Error: Place ID and Google API Key are required.', 'g-business-reviews-rating') . '</p>';
				}
				elseif ($this->place_id == NULL)
				{
					$ret = '<p class="error">' . __('Error: Place ID is required.', 'g-business-reviews-rating') . '</p>';
				}
				elseif ($this->api_key == NULL)
				{
					$ret = '<p class="error">' . __('Error: Google API Key is required.', 'g-business-reviews-rating') . '</p>';
				}
				
				if ($ret != '')
				{
					break;
				}

				$data_string = json_encode($data_array, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
				$ret = '	<pre id="google-business-reviews-rating-data">' . esc_html($data_string) . '</pre>
';
				return $ret;
			case 'json':
				if (!is_array($data_array) || is_array($data_array) && empty($data_array))
				{
					$ret = json_encode(array(
						'success' => FALSE,
						'error' => __('Request count exceeded', 'g-business-reviews-rating')
					));
					return $ret;
				}
				
				$data_string = json_encode($data_array, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
				
				return $data_string;
			case 'array':
			default:
				return $this->result;
			}
		}
		
		$fields = get_option(__CLASS__ . '_retrieval_fields', NULL);
		$language = get_option(__CLASS__ . '_language', NULL);
		$translate = (is_bool(get_option(__CLASS__ . '_retrieval_translate', NULL)) && get_option(__CLASS__ . '_retrieval_translate') || is_string(get_option(__CLASS__ . '_retrieval_translate')) && preg_match('/^(?:1|true)$/i', get_option(__CLASS__ . '_retrieval_translate')) || is_numeric(get_option(__CLASS__ . '_retrieval_translate')) && intval(get_option(__CLASS__ . '_retrieval_translate')) >= 1);
		
		if (!is_array($fields))
		{
			$fields = array('formatted_address', 'icon', 'id', 'name', 'rating', 'reviews', 'url', 'user_ratings_total', 'vicinity');
			update_option(__CLASS__ . '_retrieval_fields', $fields, 'no');
		}

		if ($force)
		{
			$retrieval = get_option(__CLASS__ . '_retrieval');
			
			if (is_array($retrieval) && isset($retrieval['requests']) && is_array($retrieval['requests']) && count($retrieval) > 1)
			{
				$last_retrieval = end($retrieval['requests']);
				$force = (!isset($last_retrieval['place_id']) || isset($last_retrieval['place_id']) && $last_retrieval['place_id'] != $this->place_id || (!isset($last_retrieval['time']) || isset($last_retrieval['time']) && (time() - $last_retrieval['time']) > 10));
			}
		}
		
		if (!$force && (!is_array($this->result) || is_array($this->result) && empty($this->result)))
		{
			$this->result = ($this->dashboard) ? get_option(__CLASS__ . '_result', NULL) : get_option(__CLASS__ . '_result_valid', NULL);
		}
		
		if (!$force && is_array($this->result) && !empty($this->result))
		{
			$data_string = json_encode($this->result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
			$data_array = $this->result;
		}
		
		if ($this->dashboard && !$force && !is_array($retrieval) && (!is_array($this->result) || is_array($this->result) && (empty($this->result) || !empty($this->result) && (!isset($this->result['status']) || $this->settings_updated && isset($this->result['status']) && !preg_match('/^OK$/i', $this->result['status'])))))
		{
			$retrieval = get_option(__CLASS__ . '_retrieval');
			
			if ($this->settings_updated && (!is_array($retrieval) || !isset($retrieval['requests']) || isset($retrieval['requests']) && count($retrieval['requests']) < 5))
			{
				$recheck = TRUE;
			}
			elseif (is_array($retrieval) && isset($retrieval['requests']) && is_array($retrieval['requests']))
			{
				$last_retrieval = end($retrieval['requests']);
				$recheck = ((!isset($last_retrieval['place_id']) || isset($last_retrieval['place_id']) && $last_retrieval['place_id'] == $this->place_id) && (!isset($last_retrieval['time']) || isset($last_retrieval['time']) && (time() - $last_retrieval['time']) > 10));
			}
		}
		
		if ($recheck)
		{
			$this->request_count++;
			
			if (!$force && $format == 'array')
			{
				$this->set_data(TRUE);
				return (is_array($this->result)) ? $this->result : array();
			}			
		}
		
		if ($force || $recheck)
		{
			if (!function_exists('wp_remote_get') || !function_exists('wp_remote_retrieve_body'))
			{
				switch ($format)
				{
				case 'boolean':
					return FALSE;
				case 'html':
					$ret = '<p class="error">'
						/* translators: %s: WordPress function name should remain untouched */
						. sprintf(__('Error: Required remote collection function not available: <em>%s</em>', 'g-business-reviews-rating'), 'wp_remote_get()')
						. '</p>';
					break;
				case 'json':
					$ret = json_encode(array(
						/* translators: %s: WordPress function name should remain untouched */
						'success' => FALSE,
						'error' => sprintf(__('Error: Required remote collection function not available: <em>%s</em>', 'g-business-reviews-rating'), 'wp_remote_get()')
					));
					break;
				case 'array':
				default:
					$ret = array();
					break;
				}
				
				return $ret;
			}
			
			$sort = $this->get_retrieval_sort(TRUE);
			$url = 'https://maps.googleapis.com/maps/api/place/details/json'
				. '?placeid=' . rawurlencode($this->place_id) 
				. '&key=' . rawurlencode($this->api_key)
				. '&fields=' . rawurlencode(implode(',', $fields))
				. '&reviews_sort=' . rawurlencode($sort)
				. '&reviews_no_translations=' . rawurlencode((!$translate) ? 'true' : 'false')
				. (($language != NULL) ? '&language=' . rawurlencode($language) : '');
			
			if (version_compare(PHP_VERSION, '8.1') >= 0)
			{
				$data_string = wp_remote_retrieve_body(@wp_remote_get($url));
			}
			else
			{
				$data_string = wp_remote_retrieve_body(wp_remote_get($url));
			}
			
			$data_array = ($data_string != NULL) ? $this->sanitize_array(json_decode($data_string, TRUE)) : NULL;

			if (!is_array($data_array))
			{
				switch ($format)
				{
				case 'boolean':
					return FALSE;
				case 'html':
					$ret = '<p class="error">'
						/* translators: %s: WordPress function name should remain untouched */
						. sprintf(__('Error: Required remote collection function not available: <em>%s</em>', 'g-business-reviews-rating'), $url)
						. '</p>';
					break;
				case 'json':
					$ret = json_encode(array(
						'success' => FALSE,
						/* translators: %s: WordPress function name should remain untouched */
						'error' => sprintf(__('Error: Unable to collect remote data from URL: <em>%s</em>', 'g-business-reviews-rating'), $url)
					));
					break;
				case 'array':
				default:
					$ret = array();
					break;
				}
				
				return $ret;
			}
			
			$this->result = $data_array;
			$this->places = get_option(__CLASS__ . '_places');

			if (is_null($retrieval))
			{
				$retrieval = get_option(__CLASS__ . '_retrieval');
			}
			
			if (!is_array($retrieval))
			{
				$retrieval = array(
					'count' => 0,
					'initial' => time(),
					'requests' => array()
				);
			}
			elseif (!is_array($retrieval['requests']))
			{
				$retrieval['requests'] = array();
			}
			elseif (count($retrieval['requests']) > 200)
			{
				$retrieval['requests'] = array_slice($retrieval['requests'], -200);
			}
			
			$retrieval['requests'][] = array(
				'time' => time(),
				'place_id' => $this->place_id,
				'status' => (isset($this->result['status'])) ? $this->result['status'] : NULL,
				'name' => (isset($this->result['result']['name'])) ? $this->result['result']['name'] : NULL,
				'icon' => (isset($this->result['result']['icon'])) ? $this->result['result']['icon'] : NULL,
				'vicinity' => (isset($this->result['result']['vicinity'])) ? $this->result['result']['vicinity'] : NULL,
				'rating' => (isset($this->result['result']['rating'])) ? $this->result['result']['rating'] : NULL,
				'review_ids' => (isset($this->result['result']['reviews']) && is_array($this->result['result']['reviews'])) ? $this->get_review_ids($this->result['result']['reviews']) : NULL,
				'rating_count' => (isset($this->result['result']['user_ratings_total'])) ? $this->result['result']['user_ratings_total'] : NULL,
				'review_count' => (isset($this->result['result']['reviews']) && is_array($this->result['result']['reviews'])) ? count($this->result['result']['reviews']) : NULL,
				'review_sort' => $sort,
				'dashboard' => ($this->dashboard && (!defined('DOING_CRON') || defined('DOING_CRON') && !DOING_CRON)),
				'sync' => (defined('DOING_CRON') && DOING_CRON),
				'count' => $this->request_count
			);
			$retrieval['count'] = intval($retrieval['count']) + 1;
			$retrieval = $this->sanitize_array($retrieval);
			$this->request_count++;

			update_option(__CLASS__ . '_retrieval', $retrieval, 'no');
			update_option(__CLASS__ . '_result', $this->result, 'no');
			wp_cache_add('result', $this->result, __CLASS__, HOUR_IN_SECONDS);
			
			if (isset($this->result['result']['reviews']) && is_array($this->result['result']['reviews']) && !empty($this->result['result']['reviews']))
			{
				$this->result_valid = $this->result;
			}
			
			$this->retrieved_data_valid = (is_array($this->result_valid) && !empty($this->result_valid));
				
			if ($this->retrieved_data_valid)
			{
				update_option(__CLASS__ . '_result_valid', $this->result_valid, 'no');
				wp_cache_add('result_valid', $this->result_valid, __CLASS__, HOUR_IN_SECONDS);
			}
			
			$place_set_key = FALSE;
			
			if (!is_array($this->places))
			{
				$this->places = array();
			}
			else
			{
				sort($this->places);
			}
			
			foreach (array_keys($this->places) as $i)
			{
				if ($this->places[$i]['place_id'] != $this->place_id)
				{
					if ($this->places[$i]['default'])
					{
						$this->places[$i]['default'] = FALSE;
					}
					
					if (!array_key_exists('status', $this->places[$i]))
					{
						$this->places[$i]['status'] = ($this->places[$i]['name'] != NULL);
					}
					
					continue;
				}
				
				$place_set_key = $i;
				break;
			}
			
			if (!is_numeric($place_set_key))
			{
				$place_set_key = count($this->places);
			}
			
			if (in_array('name', $fields) && array_key_exists($place_set_key, $this->places) && (!isset($this->result['result']['name']) || isset($this->result['result']['name']) && $this->result['result']['name'] == NULL))
			{
				$this->places[$place_set_key]['time'] = time();
				$this->places[$place_set_key]['default'] = TRUE;
				$this->places[$place_set_key]['status'] = FALSE;
			}
			else
			{
				if (count($fields) == 9)
				{
					$this->places[$place_set_key] = array(
						'id' => (isset($this->result['result']['id'])) ? $this->result['result']['id'] : NULL,
						'place_id' => $this->place_id,
						'time' => time(),
						'name' => (isset($this->result['result']['name'])) ? $this->result['result']['name'] : NULL,
						'icon' => (isset($this->result['result']['icon'])) ? $this->result['result']['icon'] : NULL,
						'vicinity' => (isset($this->result['result']['vicinity'])) ? $this->result['result']['vicinity'] : NULL,
						'formatted_address' => (isset($this->result['result']['formatted_address'])) ? $this->result['result']['formatted_address'] : NULL,
						'rating' => (isset($this->result['result']['rating'])) ? $this->result['result']['rating'] : NULL,
						'rating_count' => (isset($this->result['result']['user_ratings_total'])) ? $this->result['result']['user_ratings_total'] : NULL,
						'default' => TRUE,
						'status' => (isset($this->result['result']['name']) && $this->result['result']['name'] != NULL)
					);
				}
				else
				{
					$this->places[$place_set_key]['place_id'] = $this->place_id;
					$this->places[$place_set_key]['time'] = time();
					$this->places[$place_set_key]['default'] = TRUE;
					
					foreach ($fields as $k)
					{
						switch($k)
						{
						case 'formatted_address':
						case 'icon':
						case 'id':
						case 'rating':
						case 'vicinity':
							if (!array_key_exists($k, $this->result['result']))
							{
								break;
							}
							$this->places[$place_set_key][$k] = $this->result['result'][$k];
							break;
						case 'user_ratings_total':
							if (!array_key_exists($k, $this->result['result']))
							{
								break;
							}
							$this->places[$place_set_key]['rating_count'] = $this->result['result'][$k];
							break;
						case 'name':
							if (!array_key_exists($k, $this->result['result']))
							{
								break;
							}
							$this->places[$place_set_key][$k] = $this->result['result'][$k];
							$this->places[$place_set_key]['status'] = ($this->result['result'][$k] != NULL);
							break;
						}
					}
				}
			}
			
			sort($this->places);
			$this->places = $this->sanitize_array($this->places);
			update_option(__CLASS__ . '_places', $this->places, 'yes');
		}
		
		switch ($format)
		{
		case 'boolean':
			return (is_array($data_array) && !empty($data_array));
		case 'html':
			if (!is_string($data_string) || is_string($data_string) && $data_string == NULL)
			{
				$ret = '	<p class="error">' . __('Error: Empty result.', 'g-business-reviews-rating') . '</p>';
				return $ret;
			}
			
			$ret = '	<pre id="google-business-reviews-rating-data">' . esc_html($data_string) . '</pre>
';
			break;
		case 'json':
			if (!is_array($data_array) || is_array($data_array) && empty($data_array))
			{
				$ret = json_encode(array(
					'success' => FALSE,
					'error' => __('Empty result', 'g-business-reviews-rating')
				));
				return $ret;
			}
			
			return $data_string;
		case 'array':
		default:
			return $data_array;
		}
		
		return $ret;
	}
	
	public function structured_data($return = FALSE, $data = array())
	{
		// Collect Structured Data to display on the home page
		
		$test = (is_bool($return) && $return);
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
			return;
		}
		
		$show_in_page = get_option(__CLASS__ . '_structured_data', 0);
		$show_in_page = (!$this->dashboard && (is_numeric($show_in_page) && ($show_in_page <= -1 || $show_in_page > 1 && function_exists('get_the_ID') && get_the_ID() == intval($show_in_page)) || (is_bool($show_in_page) && $show_in_page || is_numeric($show_in_page) && intval($show_in_page) == 1) && is_front_page()));
		
		if (!$return && !$string && empty($data) && !$show_in_page)
		{
			return;
		}
		
		if (!is_array($this->data) || is_array($this->data) && empty($this->data))
		{
			$this->set_data();

			if (!isset($this->data['result']) || isset($this->data['result']) && !is_array($this->data['result']))
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
				return;
			}
		}
	
		if (!$this->valid() || $this->reviews_count(NULL, TRUE) < 1 || !isset($this->data['result']['name']) || isset($this->data['result']['name']) && $this->data['result']['name'] == NULL)
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
			return;
		}
		
		if ($test)
		{
			return TRUE;
		}
		
		if (!$string)
		{
			$structured_data = wp_cache_get('structured_data', __CLASS__);
			if (is_string($structured_data) && mb_strlen($structured_data) > 20)
			{
				echo wp_kses($structured_data, array('script' => array('type' => 'application/ld+json')));
				return;
			}
		}
		
		$name = $this->get_data('name');
		$logo = $this->get_data('logo');
		$address = $this->get_data('address');
		$rating = $this->get_data('rating');
		$rating_count = $this->get_data('rating_count');
		$telephone = get_option(__CLASS__ . '_telephone', FALSE);
		$business_type = (is_string(get_option(__CLASS__ . '_business_type'))) ? get_option(__CLASS__ . '_business_type') : FALSE;
		$price_range = (is_numeric(get_option(__CLASS__ . '_price_range', NULL))) ? str_repeat('$', get_option(__CLASS__ . '_price_range')) : FALSE;
		
		extract($data, EXTR_OVERWRITE);

		$data = array(
			'@context' => 'http://schema.org',
			'@type' => 'LocalBusiness',
			'name' => ($name != NULL) ? $name : FALSE,
			'address' => ($address != NULL) ? $address : FALSE,
			'image' => ($logo != NULL) ? $logo : FALSE,
			'url' => get_site_url(),
			'telephone' => ($telephone != NULL) ? $telephone : FALSE,
			'additionalType' => ($business_type != NULL) ? $business_type : FALSE,
			'priceRange' => ($price_range != NULL) ? $price_range : FALSE,
			'AggregateRating' => array(
				'@type' => 'AggregateRating',
				'itemReviewed' => ($name != NULL) ? $name : FALSE,
				'ratingCount' => 5,
				'ratingValue' => (is_numeric($rating)) ? floatval($rating) : FALSE,
				'ratingCount' => (is_numeric($rating_count)) ? $rating_count : 0
			),
			'review' => array()
		);
		
		foreach ($this->reviews as $a)
		{
			if (!$a['status'])
			{
				continue;
			}
			
			if (count($data['review']) >= 5)
			{
				break;
			}
			
			$data['review'][] = array(
				'@type' => 'Review',
				'author' => array(
					'@type' => 'Person',
					'name' => ($a['author_name'] != NULL) ? $a['author_name'] : FALSE
				),
				'datePublished' => (function_exists('wp_date')) ? wp_date("Y-m-d", $a['time']) : date("Y-m-d", $a['time']),
				'description' => (mb_strlen($a['text']) > 1) ? strip_tags($a['text']) : FALSE,
				'name' => ($name != NULL) ? $name : FALSE,
				'reviewRating' => array(
					'@type' => 'Rating',
					'bestRating' => 5,
					'ratingValue' => $a['rating'],
					'worstRating' => 1
				)
			);
		}
		
		$data = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
		$structured_data = '<script type="application/ld+json">' . PHP_EOL . '[ ' . $data . ' ]' . PHP_EOL . '</script>';
		wp_cache_add('structured_data', $structured_data, __CLASS__, HOUR_IN_SECONDS);
		
		if ($string)
		{
			return $data;
		}
		
		echo wp_kses($structured_data, array('script' => array('type' => 'application/ld+json')));
		return;
	}
	
	private function admin_preview($post = array())
	{
		// Handling front-end previews from the Dashboard
		
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

		return $this->wp_display($post);
	}
	
	public function set_reviews($force = FALSE)
	{
		// Update stored record of all reviews collected
		
		if (!$this->valid() || empty($this->data) || !empty($this->data) && isset($this->data['result']) && !isset($this->data['result']['reviews']) || isset($this->data['result']) && !is_array($this->data['result']['reviews']))
		{
			return FALSE;
		}
		
		if (!$force)
		{
			if (!$this->dashboard && wp_cache_get((($this->demo) ? 'reviews_demo' : 'reviews'), __CLASS__) != FALSE)
			{
				$this->reviews = wp_cache_get((($this->demo) ? 'reviews_demo' : 'reviews'), __CLASS__);
			}
			
			if (is_array($this->reviews) && !empty($this->reviews))
			{
				$this->reviews_filtered = $this->reviews;
				return TRUE;
			}
		}
		
		wp_cache_delete('structured_data', __CLASS__);
		wp_cache_delete((($this->demo) ? 'reviews_demo' : 'reviews'), __CLASS__);

		$this->reviews = (!$this->demo && is_array(get_option(__CLASS__ . '_reviews'))) ? get_option(__CLASS__ . '_reviews') : array();
		$this->local_images = get_option(__CLASS__ . '_local_images', FALSE);
		$retrieval_sort = get_option(__CLASS__ . '_retrieval_sort', 'most_relevant');
		$retrieval_sort_current = $this->get_retrieval_sort();
		$use_relative_time_description = (!$this->translation_exists(TRUE));
		$relative_time_description_update = FALSE;
		$max_id = (!empty($this->reviews) && function_exists('array_column')) ? max(array_column($this->reviews, 'id')) : count($this->reviews);
		$relevance = ($retrieval_sort_current != $retrieval_sort && $retrieval_sort_current == 'newest' && count($this->reviews) >= 5) ? 5 : 1;
		$count = 1;
		$checked_ids = array();
		
		foreach ($this->data['result']['reviews'] as $review)
		{
			$a = array();
			$key = $review['time'] . '_' . $review['rating'] . '_' . md5($review['author_name'] . '_' . mb_substr($review['text'], 0, 100));
			
			if (!$this->demo)
			{
				if (($force || $this->dashboard) && array_key_exists($key, $this->reviews))
				{
					$this->reviews[$key]['relative_time_description'] = $this->get_relative_time_description($review['time'], $review['relative_time_description'], $use_relative_time_description);
					$this->reviews[$key]['checked'] = time();
					$this->reviews[$key]['order'] = $relevance;
					$this->reviews[$key]['removable'] = FALSE;
					$checked_ids[] = $this->reviews[$key]['id'];
					$relevance++;
					continue;
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
					$review['order'] = $relevance;
					$review['removable'] = FALSE;
					$this->reviews[$key] = $review;
					$checked_ids[] = $review['id'];
					$relevance++;
					continue(2);
				}
			}
			
			$a['id'] = $max_id + $count;
			$a['place_id'] = ($this->demo) ? NULL : $this->place_id;
			$a['order'] = $relevance;
			$a['checked'] = NULL;
			$a['retrieved'] = time();
			$a['imported'] = FALSE;
			$a['time_estimate'] = FALSE;
			$a['removable'] = FALSE;
			$a['status'] = TRUE;

			if (!get_option(__CLASS__ . '_local_images', FALSE))
			{
				list($a['profile_photo_url']) = $this->set_avatar($review, $key);
			}
			else
			{
				list($a['profile_photo_url'], $a['avatar']) = $this->set_avatar($review, $key);
			}

			$this->reviews[$key] = $this->sanitize_array($a + $review);
			$checked_ids[] = $a['id'];
			$relevance++;
			$count++;
		}
		
		if ($force || $this->dashboard && !$use_relative_time_description)
		{
			foreach (array_keys($this->reviews) as $key)
			{
				$a = $this->reviews[$key];
				
				if (!in_array($a['id'], $checked_ids, TRUE))
				{
					$this->reviews[$key]['removable'] = TRUE;
					$this->reviews[$key]['order'] = $relevance;
					$relevance++;
				}
				
				if ($use_relative_time_description || !$force && isset($a['checked']) && is_numeric($a['checked']) && time() - $a['checked'] < HOUR_IN_SECONDS)
				{
					continue;
				}
				
				$this->reviews[$key]['relative_time_description'] = $this->get_relative_time_description($a['time']);
				
				if (!$relative_time_description_update && $this->reviews[$key]['relative_time_description'] != $a['relative_time_description'])
				{
					$relative_time_description_update = TRUE;
				}
			}
		}
 		
		uksort($this->reviews, function ($a, $b)
			{
				// Work-around for instable array bug in PHP 8.3.2
				if (!array_key_exists($a, $this->reviews) || !array_key_exists($b, $this->reviews))
				{
					return 0;
				}

				return $this->reviews[$a]['order'] - $this->reviews[$b]['order'];
			}
		);
		
		wp_cache_add((($this->demo) ? 'reviews_demo' : 'reviews'), $this->reviews, __CLASS__, HOUR_IN_SECONDS);
		
		$this->reviews_filtered = $this->reviews;

		if ($this->demo || (!$relative_time_description_update && $relevance == 1))
		{
			return TRUE;
		}

		if ($force || $this->dashboard)
		{
			update_option(__CLASS__ . '_reviews', $this->reviews, 'no');
		}
		
		return TRUE;
	}
	
	private function delete_icon()
	{
		// Delete the icon image
		
		$this->icon_image_id = NULL;
		$this->icon_image_url = NULL;
		update_option(__CLASS__ . '_icon', $this->icon_image_id);

		return TRUE;
	}
	
	private function set_icon($id = NULL)
	{
		// Set the icon image
		
		if (is_numeric($id))
		{
			update_option(__CLASS__ . '_icon', $id);
			$this->icon_image_id = $id;
		}
		else
		{
			$this->icon_image_id = get_option(__CLASS__ . '_icon');
		}
		
		if (is_numeric($this->icon_image_id))
		{
			$a = wp_get_attachment_image_src($this->icon_image_id, 'full');
			$this->icon_image_url = (isset($a[0]) && is_string($a[0])) ? $a[0] : NULL;
		}
		
		return TRUE;
	}
	
	private function delete_logo()
	{
		// Delete the logo image for Structured Data
		
		$this->logo_image_id = NULL;
		$this->logo_image_url = NULL;
		update_option(__CLASS__ . '_logo', $this->logo_image_id);

		return TRUE;
	}
	
	private function set_logo($id = NULL)
	{
		// Set the logo image for Structured Data
		
		if (is_numeric($id))
		{
			update_option(__CLASS__ . '_logo', $id);
			$this->logo_image_id = $id;
		}
		else
		{
			$this->logo_image_id = get_option(__CLASS__ . '_logo');
		}
		
		if (is_numeric($this->logo_image_id))
		{
			$a = wp_get_attachment_image_src($this->logo_image_id, 'full');
			$this->logo_image_url = (isset($a[0]) && is_string($a[0])) ? $a[0] : NULL;
		}
		
		return TRUE;
	}

	public function set_avatar($data, $key = NULL)
	{
		// Sets an avatar (profile_photo_url) for an individual reviewer based on their review data

		$ret = array(0 => NULL, 1 => NULL);

		if (!is_array($data) || !isset($data['profile_photo_url']) || !is_string($data['profile_photo_url']) || !preg_match('#^https?://(?:\w+\.)?(?:gstatic|google(?:usercontent)?)?\.\w+/.+$#i', $data['profile_photo_url']))
		{
			return $ret;
		}

		$ret[0] = $data['profile_photo_url'];

		if (!get_option(__CLASS__ . '_local_images', FALSE))
		{
			return $ret;
		}

		if ($key == NULL && isset($data['time']) && isset($data['rating']) && isset($data['author_name']) && isset($data['text']) && $data['rating'] != NULL)
		{
			if (!preg_match('/^(\d+)[^\d]+(\d+)[^\d]+(\d+)(?:[^\d].*)?$/', $data['time'], $t))
			{
				return $ret;
			}
			
			$time = mktime(0, 0, 0, $t[2], $t[3], $t[1]);
			$key = $time . '_' . $data['rating'] . '_' . md5(strval($data['author_name']) . '_' . mb_substr(strval($data['text']), 0, 100));
		}

		if ($key == NULL || !function_exists('wp_remote_get') || !function_exists('wp_remote_retrieve_body') || !function_exists('wp_get_upload_dir') || !function_exists('wp_upload_bits'))
		{
			return $ret;
		}

		$upload_directory = wp_get_upload_dir();

		if (isset($upload_directory['basedir']) && is_string($upload_directory['basedir']))
		{
			$upload_directory_plugin = $upload_directory['basedir'] . '/gmbrr';
		}
		elseif (isset($upload_directory['path']) && is_string($upload_directory['path']))
		{
			$upload_directory_plugin = preg_replace('#^(.+?)(?:/\d+/\d+)/?$#', '$1', $upload_directory['path']) . '/gmbrr';
		}

		if (!is_dir($upload_directory_plugin))
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
			$fp = @wp_remote_get($data['profile_photo_url']);
		}
		else
		{
			$fp = wp_remote_get($data['profile_photo_url']);
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

	public function server_ip()
	{
		// Retrieve an accurate IP Address for the web server

		if (is_string(wp_cache_get('server_ip', __CLASS__)))
		{
			return trim(wp_cache_get('server_ip', __CLASS__));
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
				$a = (is_string($string)) ? preg_split('/,/i', $string, 2) : array('', '');
				
				if (preg_match($ip_regex, $a[1]))
				{
					$string = trim(mb_strtolower($a[1]));
					wp_cache_set('server_ip', $string, __CLASS__, HOUR_IN_SECONDS);
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
					wp_cache_set('server_ip', $string, __CLASS__, HOUR_IN_SECONDS);
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
				wp_cache_set('server_ip', $string, __CLASS__, HOUR_IN_SECONDS);
				return $string;
			}
		}
		
		if (isset($_SERVER['SERVER_ADDR']) && is_string($_SERVER['SERVER_ADDR']) && preg_match($ip_regex, $_SERVER['SERVER_ADDR']))
		{
			wp_cache_set('server_ip', trim($_SERVER['SERVER_ADDR']), __CLASS__, HOUR_IN_SECONDS);
			return trim($_SERVER['SERVER_ADDR']);
		}
		
		return NULL;
	}
	
	public function data_hunter($format = 'array', $force = FALSE)
	{
		// Find all references to existing Google Reviews, API Key and Place ID
		
		if (!$this->dashboard || !current_user_can('manage_options', __CLASS__))
		{
			return TRUE;
		}
		
		$return = (!$force && get_option(__CLASS__ . '_place_id') == NULL);
		
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
		
		$ret = array();
		$language = preg_replace('/_/', '-', get_option('WPLANG'));
		
		if (get_option('we_are_open_api_key') != NULL && get_option('we_are_open_place_id') != NULL)
		{
			$ret['api_key'] = get_option('we_are_open_api_key');
			$ret['place_id'] = get_option('we_are_open_place_id');
		}

		if (empty($ret) && is_string(get_option('grw_google_api_key')) && $wpdb->get_var("SHOW TABLES LIKE '" . $wpdb->prefix . "grp_google_place'") == $wpdb->prefix . 'grp_google_place')
		{
			$id = $wpdb->get_var("SELECT `id` FROM `" . $wpdb->prefix . "grp_google_place` ORDER BY `id` DESC LIMIT 1");
			$place_id = $wpdb->get_var("SELECT `place_id` FROM `" . $wpdb->prefix . "grp_google_place` WHERE `id` = '" . esc_sql($id) . "' LIMIT 1");
			$reviews = $wpdb->get_results("SELECT * FROM `" . $wpdb->prefix . "grp_google_review` WHERE `google_place_id` = '" . intval($id) . "'");
			$ret['api_key'] = get_option('grw_google_api_key');
			$ret['place_id'] = $place_id;
			$ret['reviews'] = $reviews;
		}
		
		if (empty($ret) && is_array(get_option('wpfbr_google_options')))
		{
			$d = get_option('wpfbr_google_options');
			if ($d['select_google_api'] != 'default' && is_string($d['google_api_key']))
			{
				$reviews = array();
				
				if ($wpdb->get_var("SHOW TABLES LIKE '" . $wpdb->prefix . "wpfb_reviews'") == $wpdb->prefix . 'wpfb_reviews')
				{
					$reviews = $wpdb->get_results("SELECT * FROM `" . $wpdb->prefix . "wpfb_reviews`");
				}
				
				$ret['api_key'] = $d['google_api_key'];
				$ret['place_id'] = (isset($d['google_location_set']['place_id'])) ? $d['google_location_set']['place_id'] : NULL;
				$ret['language'] = (isset($d['google_language_option'])) ? $d['google_language_option'] : NULL;
				$ret['reviews'] = $reviews;
			}
		}
		
		if (empty($ret) && is_array(get_option('googleplacesreviews_options')))
		{
			$d = get_option('googleplacesreviews_options');
			$w = array('place_id' => NULL);
			
			if (array_key_exists('google_places_api_key', $d))
			{
				$w = get_option('googleplacesreviews_options');
				
				if (is_array($w) && array_key_exists('place_id', $w) && is_string($w['place_id']) && mb_strlen($w['place_id'] >= 17))
				{
					$place_id = $w['place_id'];
				}
				
				$ret['api_key'] = $d['google_places_api_key'];
				$ret['place_id'] = $place_id;
			}
		}
		
		if (empty($ret) && is_string(get_option('google_places_api_key')))
		{
			$ret['api_key'] = get_option('google_places_api_key');
		}
		
		if (empty($ret) && is_array(get_option('trustindex-google-page-details')))
		{
			$d = get_option('trustindex-google-page-details');
			
			if (array_key_exists('id', $d) && is_string($d['id']) && mb_strlen($d['id'] >= 17))
			{
				$ret['place_id'] = $d['id'];
			}
			
			if (is_string(get_option('trustindex-google-lang')))
			{
				$ret['language'] = get_option('trustindex-google-lang');
			}
		}
		
		if ((empty($ret) || (!isset($ret['language']) || isset($ret['language']) && $ret['language'] == NULL)) && is_string($language) && mb_strlen($language) >= 2)
		{
			if (empty($this->languages) || !empty($this->languages) && array_key_exists($language, $this->languages))
			{
				$ret['language'] = $language;
			}
			elseif (!empty($this->languages) && array_key_exists(substr($language, 0, 2), $this->languages))
			{
				$ret['language'] = substr($language, 0, 2);
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
	
	public function reviews_count($place_id = NULL, $status = NULL, $set = TRUE)
	{
		// Count the number of reviews stored
		
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
					if ($a['place_id'] == $place_id)
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
	
	private function reviews_filter($filters = NULL, $atts = NULL)
	{
		// Filter review data
		
		if (!$this->set_reviews() || empty($this->reviews))
		{
			return FALSE;
		}
		
		if (!is_array($filters))
		{
			$filters = array();
		}
		
		if (!is_array($atts))
		{
			$atts = array();
		}
		
		$count = 0;
		$ids = (array_key_exists('id', $filters) && is_numeric($filters['id']) && $filters['id'] > 0) ? array(intval($filters['id'])) : ((array_key_exists('id', $filters) && is_string($filters['id']) && preg_match('/^(?:\d+)(?:,\s*(?:\d+))+$/', $filters['id'])) ? array_unique(preg_split('/[^\d]+/', $filters['id'])) : array());
		$id = (!empty($ids)) ? $ids[0] : NULL;
		$place_id = (!$this->demo && array_key_exists('place_id', $filters) && is_string($filters['place_id']) && mb_strlen($filters['place_id']) >= 20) ? $filters['place_id'] : NULL;
		$language = (array_key_exists('language', $filters) && is_string($filters['language']) && mb_strlen($filters['language']) >= 2 && mb_strlen($filters['language']) <= 16) ? preg_replace('/^([a-z]{2,3}).*$/i', '$1', mb_strtolower($filters['language'])) : NULL;
		$min = ($id == NULL && array_key_exists('min', $filters) && is_numeric($filters['min']) && $filters['min'] >= 1 && $filters['min'] <= 5) ? intval($filters['min']) : NULL;
		$max = ($id == NULL && array_key_exists('max', $filters) && is_numeric($filters['max']) && $filters['max'] >= 1 && $filters['max'] <= 5) ? intval($filters['max']) : NULL;
		$offset = ($id == NULL && array_key_exists('offset', $filters) && is_numeric($filters['offset']) && $filters['offset'] >= 0) ? intval($filters['offset']) : 0;
		$limit = ($id == NULL && array_key_exists('limit', $filters) && is_numeric($filters['limit']) && $filters['limit'] >= 0) ? intval($filters['limit']) : NULL;
		$excerpt = (array_key_exists('excerpt', $filters) && is_numeric($filters['excerpt']) && $filters['excerpt'] >= 20) ? intval($filters['excerpt']) : NULL;
		$review_text_min = (array_key_exists('review_text_min', $filters) && is_numeric($filters['review_text_min']) && $filters['review_text_min'] >= 0) ? intval($filters['review_text_min']) : NULL;
		$review_text_max = (array_key_exists('review_text_max', $filters) && is_numeric($filters['review_text_max']) && $filters['review_text_max'] >= 0 && (!is_numeric($filters['review_text_min']) || is_numeric($filters['review_text_min']) && $filters['review_text_min'] <= $filters['review_text_max'])) ? intval($filters['review_text_max']) : NULL;
		$review_text_inc = (array_key_exists('review_text_inc', $filters) && is_string($filters['review_text_inc']) && mb_strlen($filters['review_text_inc']) > 1) ? array_unique(preg_split('/,\s*/', $filters['review_text_inc'], 10)) : array();
		$review_text_exc = (array_key_exists('review_text_exc', $filters) && is_string($filters['review_text_exc']) && mb_strlen($filters['review_text_exc']) > 1) ? array_unique(preg_split('/,\s*/', $filters['review_text_exc'], 10)) : array();

		$limit = (is_numeric($limit)) ? intval($limit) : ((!array_key_exists('limit', $atts)) ? get_option(__CLASS__ . '_review_limit', NULL) : NULL);
		$sort = ($id == NULL && array_key_exists('sort', $filters) && ($filters['sort'] != NULL && is_string($filters['sort']))) ? preg_replace('/[^\w_-]/', '', $filters['sort']) : get_option(__CLASS__ . '_review_sort', NULL);
		$sort_static = (array_key_exists($sort, $this->review_sort_options) && $this->review_sort_options[$sort]['static']);
		$min = (is_numeric($min)) ? intval($min) : get_option(__CLASS__ . '_rating_min', NULL);
		$max = (is_numeric($max)) ? intval($max) : get_option(__CLASS__ . '_rating_max', NULL);
		$review_text_min = (is_numeric($review_text_min) && $review_text_min >= 0) ? intval($review_text_min) : get_option(__CLASS__ . '_review_text_min', NULL);
		$review_text_max = (is_numeric($review_text_max) && $review_text_max >= 0) ? intval($review_text_max) : get_option(__CLASS__ . '_review_text_max', NULL);
		
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
			$this->reviews_filtered = array();

			if (is_string($this->review_sort_option) && $sort == 'shuffle')
			{
				$keys = NULL;
				
				if ($sort_static)
				{
					$ids_check = $ids;
					$keys = ($sort_static) ? get_transient(__CLASS__ . '_reviews_shuffled') : wp_cache_get('reviews_shuffled', __CLASS__);
					
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
						set_transient(__CLASS__ . '_reviews_shuffled', $keys, HOUR_IN_SECONDS);
					}
					else
					{
						wp_cache_set('reviews_shuffled', $keys, __CLASS__, HOUR_IN_SECONDS);
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
			
			if ($place_id != NULL && $a['place_id'] != $place_id)
			{
				unset($this->reviews_filtered[$key]);
				continue;
			}
			
			if ($language != NULL && isset($a['language']) && ($a['language'] == NULL || preg_replace('/^([a-z]{2,3}).*$/i', '$1', mb_strtolower($a['language'])) != $language))
			{
				unset($this->reviews_filtered[$key]);
				continue;
			}
									
			if (is_numeric($review_text_min) && (!is_string($a['text']) || is_string($a['text']) && $review_text_min > mb_strlen(strip_tags($a['text'])) || is_string($a['text']) && is_numeric($review_text_max) && $review_text_max < mb_strlen(strip_tags($a['text']))))
			{
				unset($this->reviews_filtered[$key]);
				continue;
			}
									
			if (!empty($review_text_inc) || !empty($review_text_exc))
			{
				$t = strip_tags($a['text']);
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
				$keys = ($sort_static) ? get_transient(__CLASS__ . '_reviews_shuffled') : wp_cache_get('reviews_shuffled', __CLASS__);
				
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
					$this->reviews_filtered = array();
					shuffle($keys);
					
					foreach ($keys as $k)
					{ 
						$this->reviews_filtered[$k] = $list[$k];
					}
					
					unset($list);
					
					if ($sort_static)
					{
						set_transient(__CLASS__ . '_reviews_shuffled', $keys, HOUR_IN_SECONDS);
					}
					else
					{
						wp_cache_set('reviews_shuffled', $keys, __CLASS__, HOUR_IN_SECONDS);
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
						
						$c = $d = array(mb_strtolower($v), mb_strtolower($w));
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
	
	private function sanitize_array($array)
	{
		// Sanitize array data to remove characters that can cause update_option() to fail
		
		if (!get_option(__CLASS__ . '_additional_array_sanitization', FALSE))
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
	
	public function sanitize_api_key($api_key)
	{
		// Sanitize data from API Key setting input
		
		if (mb_strlen($api_key) < 10)
		{
			$api_key = NULL;
		}
		
		if (get_option(__CLASS__ . '_api_key') != $api_key)
		{
			delete_transient(__CLASS__ . '_reviews_shuffled');
			wp_cache_delete('structured_data', __CLASS__);
			wp_cache_delete('result', __CLASS__);
			wp_cache_delete('reviews_shuffled', __CLASS__);
			wp_cache_delete('reviews', __CLASS__);
			$this->api_key = sanitize_text_field($api_key);
			
			if ($api_key != NULL)
			{
				set_transient(__CLASS__ . '_force', time() . '/0', 30);
			}

			self::log('api_key', $this->api_key);
		}
		
		return $api_key;
	}
	
	public function sanitize_place_id($place_id)
	{
		// Sanitize data from Place ID setting input
		
		if (mb_strlen($place_id) < 10)
		{
			$place_id = NULL;
		}

		if (empty($this->places))
		{
			$this->places = get_option(__CLASS__ . '_places', array());
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
		
		if (get_option(__CLASS__ . '_place_id') != $place_id)
		{
			$api_key = get_option(__CLASS__ . '_api_key');
			delete_transient(__CLASS__ . '_reviews_shuffled');
			wp_cache_delete('structured_data', __CLASS__);
			wp_cache_delete('result', __CLASS__);
			wp_cache_delete('result_valid', __CLASS__);
			wp_cache_delete('reviews_shuffled', __CLASS__);
			wp_cache_delete('reviews', __CLASS__);
			update_option(__CLASS__ . '_result', NULL, 'no');
			update_option(__CLASS__ . '_structured_data', FALSE, 'yes');
			$this->place_id = sanitize_text_field($place_id);
			$this->data = array();
			$this->result = array();
			
			if ($place_id == NULL && $api_key == NULL)
			{
				$this->reviews = array();
				$this->reviews_filtered = array();
			}
			elseif ($place_id != NULL && $api_key != NULL)
			{
				set_transient(__CLASS__ . '_force', time() . '/0', 30);
			}

			self::log('place_id', $this->place_id);
		}
		
		return $place_id;
	}

	public function sanitize_retrieval_sort($retrieval_sort)
	{
		// Sanitize data from retrieval sort

		if (!is_string($retrieval_sort) || is_string($retrieval_sort) && !preg_match('/^(?:most_relevant|newest|review_sort)$/', $retrieval_sort))
		{
			return NULL;
		}

		return $retrieval_sort;
	}
	
	public function sanitize_demo($demo)
	{
		// Handle switch between active and demo versions
		
		$demo = (bool)$demo;
		$this->demo = $demo;

		if (get_option(__CLASS__ . '_demo') != $demo)
		{
			wp_cache_delete('structured_data', __CLASS__);
			wp_cache_delete('result', __CLASS__);
			wp_cache_delete('result_demo', __CLASS__);
			update_option(__CLASS__ . '_result', NULL, 'no');
			$this->data = array();
			$this->result = array();
			$this->reviews = array();
			$this->reviews_filtered = array();
		}
		
		return $demo;
	}

	public function sanitize_input($data)
	{
		// Sanitizes and normalizes input data
		
		$stripslashes = (function_exists('wp_magic_quotes')); // Unfortunately, no flag exists
		
		if (!is_array($data))
		{
			if (is_null($data))
			{
				return NULL;
			}
			
			if (is_bool($data))
			{
				return (boolean)$data;
			}

			if (is_string($data) || is_numeric($data))
			{
				return ($stripslashes && is_string($data)) ? stripslashes(wp_kses_stripslashes(sanitize_text_field($data), array())) : wp_kses(sanitize_text_field($data), array());
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
				$data[$k] = (boolean)$data[$k];
				continue;
			}

			if (!is_string($data[$k]) && !is_numeric($data[$k]))
			{
				$data[$k] = FALSE;
				continue;
			}

			$data[$k] = ($stripslashes && is_string($data[$k])) ? stripslashes(wp_kses_stripslashes(sanitize_text_field($data[$k]), array())) : wp_kses(sanitize_text_field($data[$k]), array());
		}
	
		return $data;
	}
	
	public function get_reviews($format = 'array')
	{
		// Get all reviews in various formats
		
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
			$this->review_sort = get_option(__CLASS__ . '_review_sort_admin');
			
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
			uksort($this->reviews, function ($b, $a) { return ($this->reviews[$b]['time'] - $this->reviews[$a]['time']); } );
			break;
		case 'retrieved':
			uksort($this->reviews, function ($b, $a) { return ($this->reviews[$b]['retrieved'] - $this->reviews[$a]['retrieved']); } );
			break;
		case 'name':
		case 'author':
		case 'author_name':
			uksort($this->reviews, function ($b, $a)
				{
					$c = $d = array(mb_strtolower($this->reviews[$b]['author_name']), mb_strtolower($this->reviews[$a]['author_name']));
					arsort($c, SORT_REGULAR);
					return (array_keys($c) === array_keys($d)) ? 1 : -1;
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
					
					$c = $d = array(mb_strtolower($this->reviews[$b]['language']), mb_strtolower($this->reviews[$a]['language']));
					arsort($c, SORT_REGULAR);
					return (array_keys($c) === array_keys($d)) ? 1 : -1;
				}
			);
			break;
		case 'place_id':
			uksort($this->reviews, function ($b, $a)
				{
					$c = $d = array(mb_strtolower($this->reviews[$b]['place_id']), mb_strtolower($this->reviews[$a]['place_id']));
					arsort($c, SORT_REGULAR);
					return (array_keys($c) === array_keys($d)) ? 1 : -1;
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
					
					$c = $d = array(mb_strtolower($this->reviews[$b]['text']), mb_strtolower($this->reviews[$a]['text']));
					arsort($c, SORT_REGULAR);
					return (array_keys($c) === array_keys($d)) ? 1 : -1;
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
			$ret = array();
			foreach (array_keys($this->reviews) as $key)
			{
				$ret[] = $key;
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
				/* translators: %s refers to the Settings URL and should remain untouched */
				. sprintf(__('No reviews found, please check your <a href="%s">settings</a>.', 'g-business-reviews-rating'), admin_url('options-general.php?page=google_business_reviews_rating_settings')) . '</p>
</div>';
				return $html;
			}
			
			$this->reviews_filter(array('sort' => 'date_desc', 'limit' => intval(get_option(__CLASS__ . '_meta_box_limit', 5))));
			$i = 0;
			$html = '<div id="latest-google-my-business-reviews" class="activity-block table-view-list">
	<h3>' . __('Recent Reviews', 'g-business-reviews-rating') . '</h3>
	<ul class="list">
';

			foreach ($this->reviews_filtered as $id => $a)
			{
				$html .= '		<li class="review review-item ' . esc_attr((($i % 2) ? 'odd' : 'even') . ' rating-' . $a['rating']) . (($a['text'] == NULL) ? ' no-text' : ''). '" data-id="' . esc_attr($id). '">
			<span class="avatar' . ((isset($a['author_url']) && isset($a['profile_photo_url']) && $a['author_url'] != NULL && $a['profile_photo_url'] != NULL) ? ' original' . ((isset($a['avatar']) && $a['avatar'] != NULL && $avatar_directory != NULL) ? ' local' : '') : ' empty') . '">' . ((isset($a['author_url']) && isset($a['profile_photo_url']) && $a['author_url'] != NULL && $a['profile_photo_url'] != NULL) ? '<img src="' . esc_attr((isset($a['avatar']) && $a['avatar'] != NULL && $avatar_directory != NULL) ? $avatar_directory . '/' . $a['avatar'] : $a['profile_photo_url']) . '" alt="Avatar">' : '') . '</span>
			<span class="review-meta">
				<span class="name">' . esc_html($a['author_name']) . '</span>
				<span class="rating">' . str_repeat('★', $a['rating']) . (($a['rating'] < 5) ? '<span class="not">' . str_repeat('☆', (5 - $a['rating'])) . '</span>' : '') . '</span>
				<span class="submitted date">' . esc_html($this->get_relative_time_description($a['time'])) . '</span>
			</span>
';
				if ($a['text'] != NULL)
				{
					$html .= '            <span class="review-text">' . preg_replace('/(\r\n|\r|\n)+/', ' ' . PHP_EOL . '            	', preg_replace('/^(.{128}[^\s]{0,20})(.*)$/uis', '$1…', esc_html(strip_tags($a['text'])))) . '</span>
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
			<li class="rate"><a href="https://wordpress.org/support/plugin/g-business-reviews-rating/reviews/#new-post">' . __('Rate Plugin', 'g-business-reviews-rating') . ' <span class="screen-reader-text">' . __('(opens in a new tab)') . '</span> <span aria-hidden="true" class="dashicons dashicons-external"></span></a></li>
		</ul>
	</div>
';
			return $html;
		case 'html':
			$show_place_id = ($this->reviews_count(TRUE, NULL, FALSE) != $this->reviews_count(NULL, NULL, FALSE));
			$places = array();
			
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
            <th class="id number'  . (($this->review_sort != NULL && preg_match('/^ids?(?:_(?:asc|desc))?$/i', $this->review_sort)) ? ' sorted' . ((!is_bool($this->review_sort_asc) || is_bool($this->review_sort_asc) && $this->review_sort_asc) ? ' asc' : ' desc') : '') . '" title="' . esc_attr__('ID', 'g-business-reviews-rating') . '"><a href="#reviews-table" class="sort" data-field="id"><span>' . esc_html__('ID', 'g-business-reviews-rating') . '</span> <span class="sorting-indicator"></span></a></th>
            <th class="submitted date'  . (($this->review_sort != NULL && preg_match('/^(?:date|submitted|time)(?:_(?:asc|desc))?$/i', $this->review_sort)) ? ' sorted' . ((!is_bool($this->review_sort_asc) || is_bool($this->review_sort_asc) && $this->review_sort_asc) ? ' asc' : ' desc') : '') . '" title="' . esc_attr__('Submitted', 'g-business-reviews-rating') . '"><a href="#reviews-table" class="sort" data-field="time"><span>' . esc_html__('Submitted', 'g-business-reviews-rating') . '</span> <span class="sorting-indicator"></span></a></th>
            <th class="author'  . (($this->review_sort != NULL && preg_match('/^(?:author(?:[_-]name)?|name)(?:_(?:asc|desc))?$/i', $this->review_sort)) ? ' sorted' . ((!is_bool($this->review_sort_asc) || is_bool($this->review_sort_asc) && $this->review_sort_asc) ? ' asc' : ' desc') : '') . '" title="' . esc_attr__('Author', 'g-business-reviews-rating') . '"><a href="#reviews-table" class="sort" data-field="author_name"><span>' . esc_html__('Author', 'g-business-reviews-rating') . '</span> <span class="sorting-indicator"></span></a></th>
            <th class="rating'  . (($this->review_sort != NULL && preg_match('/^ratings?(?:_(?:asc|desc))?$/i', $this->review_sort)) ? ' sorted' . ((!is_bool($this->review_sort_asc) || is_bool($this->review_sort_asc) && $this->review_sort_asc) ? ' asc' : ' desc') : '') . '" title="' . esc_attr__('Rating', 'g-business-reviews-rating') . '"><a href="#reviews-table" class="sort" data-field="rating"><span>' . esc_html__('Rating', 'g-business-reviews-rating') . '</span> <span class="sorting-indicator"></span></a></th>
            <th class="text'  . (($this->review_sort != NULL && preg_match('/^(?:review(?:[_-]text)?|text)(?:_(?:asc|desc))?$/', $this->review_sort)) ? ' sorted' . ((!is_bool($this->review_sort_asc) || is_bool($this->review_sort_asc) && $this->review_sort_asc) ? ' asc' : ' desc') : '') . '" title="' . esc_attr__('Text', 'g-business-reviews-rating') . '"><a href="#reviews-table" class="sort" data-field="text"><span>' . esc_html__('Text', 'g-business-reviews-rating') . '</span> <span class="sorting-indicator"></span></a></th>
            <th class="language'  . (($this->review_sort != NULL && preg_match('/^languages?(?:_(?:asc|desc))?$/i', $this->review_sort)) ? ' sorted' . ((!is_bool($this->review_sort_asc) || is_bool($this->review_sort_asc) && $this->review_sort_asc) ? ' asc' : ' desc') : '') . '" title="' . esc_attr__('Language', 'g-business-reviews-rating') . '"><a href="#reviews-table" class="sort" data-field="language"><span>' . esc_html__('Language', 'g-business-reviews-rating') . '</span> <span class="sorting-indicator"></span></a></th>
            <th class="retrieved date'  . (($this->review_sort != NULL && preg_match('/^retrieved(?:_(?:asc|desc))?$/i', $this->review_sort)) ? ' sorted' . ((!is_bool($this->review_sort_asc) || is_bool($this->review_sort_asc) && $this->review_sort_asc) ? ' asc' : ' desc') : '') . '" title="' . esc_attr__('Retrieved', 'g-business-reviews-rating') . '"><a href="#reviews-table" class="sort" data-field="retrieved"><span>' . esc_html__('Retrieved', 'g-business-reviews-rating') . '</span> <span class="sorting-indicator"></span></a></th>
';
			if ($show_place_id)
			{
				$html .= '            <th class="place-id'  . (($this->review_sort != NULL && preg_match('/^place_ids?(?:_(?:asc|desc))?$/i', $this->review_sort)) ? ' sorted' . ((!is_bool($this->review_sort_asc) || is_bool($this->review_sort_asc) && $this->review_sort_asc) ? ' asc' : ' desc') : '') . '" title="' . esc_attr__('Place ID', 'g-business-reviews-rating') . '"><a href="#reviews-table" class="sort" data-field="place_id">' . esc_html__('Place ID', 'g-business-reviews-rating') . ' <span class="sorting-indicator"></span></a></th>
';
			}

			$html .= '        </tr>
    </thead>
    <tbody>
';		
			foreach ($this->reviews as $key => $a)
			{
				$html .= '        <tr id="' . esc_attr(preg_replace('/[^0-9a-z-]/', '-', $key)) . '" class="review ' . esc_attr('rating-' . $a['rating']) . esc_attr(((!$a['status']) ? ' inactive' : '')) . ((array_key_exists('time_estimate', $a) && $a['time_estimate']) ? ' estimate' : '') . ((array_key_exists('removable', $a) && $a['removable']) ? ' removable' : '') . '" data-id="' . esc_attr($a['id']) . '" data-order="' . esc_attr($a['order']) . '">
            <td class="id number">' . esc_html($a['id']) . ' <a href="' . esc_attr('#' . preg_replace('/[^0-9a-z-]/', '-', $key)) . '" class="show-hide" title="' . (($a['status']) ? esc_attr__('Hide', 'g-business-reviews-rating') : esc_attr__('Show', 'g-business-reviews-rating')) . '">' . (($a['status']) ? '<span class="dashicons dashicons-visibility"></span>' : '<span class="dashicons dashicons-hidden"></span>') . '</a>' . ((array_key_exists('removable', $a) && $a['removable'] || array_key_exists('time_estimate', $a) && $a['time_estimate']) ? '<a href="' . esc_attr('#' . preg_replace('/[^0-9a-z-]/', '-', $key)) . '" class="remove" title="' . esc_attr__('Remove', 'g-business-reviews-rating') . '"><span class="dashicons dashicons-no"></span></a>' : '') . '</td>
            <td class="submitted date"><span class="date' . ((array_key_exists('time_estimate', $a) && $a['time_estimate']) ? ' date-edit' : '') . '"><span class="value">' . ((array_key_exists('time_estimate', $a) && $a['time_estimate']) ? esc_html(date("Y/m/d", $a['time'])) . '</span> <span class="dashicons dashicons-arrow-down"></span>' : esc_html(date("Y/m/d H:i", $a['time']))) . '</span></span>' . ((array_key_exists('time_estimate', $a) && $a['time_estimate']) ? '<input type="date" id="' . esc_attr('submitted-' . preg_replace('/[^0-9a-z-]/', '-', $key)) . '" class="time-estimate" name="submitted[]" value="' . esc_attr(date("Y-m-d", $a['time'])) . '" max="' . esc_attr(date("Y-m-d")) . '">' : '') . '</td>
            <td class="author">
				<span class="name">' . ((isset($a['author_url']) && $a['author_url'] != NULL) ? '<a href="' . esc_attr($a['author_url']) . '" target="_blank">' : '') . esc_html($a['author_name']) . ((isset($a['author_url']) && $a['author_url'] != NULL) ? '</a>' : '') . '</span>
				' . ((isset($a['author_url']) && isset($a['profile_photo_url']) && $a['author_url'] != NULL && $a['profile_photo_url'] != NULL) ? '<span class="avatar"><a href="' . esc_attr($a['author_url']) . '" target="_blank"><img src="' . esc_attr((isset($a['avatar']) && $a['avatar'] != NULL && $avatar_directory != NULL) ? $avatar_directory . '/' . $a['avatar'] : $a['profile_photo_url']) . '" alt="Avatar"></a></span>' : '') . '
			</td>
            <td class="rating">' . str_repeat('★', $a['rating']) . (($a['rating'] < 5) ? '<span class="not">' . str_repeat('☆', (5 - $a['rating'])) . '</span>' : '') . ' <span class="rating-number">(' . esc_html($a['rating']) . ')</span></td>
            <td class="text"><div class="text-wrap">' . (($a['text'] != NULL) ? preg_replace('/(\r\n|\r|\n)+/', '<br>' . PHP_EOL . '            	', esc_html(strip_tags($a['text']))) : '<span class="none" title="' . esc_attr(__('None', 'g-business-reviews-rating')) . '">—</span>') . '</div></td>
            <td class="language">' . (($a['text'] != NULL) ? '<a href="#reviews-table" class="language-edit"><span class="value">' . ((isset($a['language']) && $a['language'] != NULL) ? esc_html($a['language']) : '—') . '</span> <span class="dashicons dashicons-arrow-down"></span></a> <select id="' . esc_attr('language-' . preg_replace('/[^0-9a-z-]/', '-', $key)) . '" class="language" name="language[]" data-none="' . esc_attr__('None', 'g-business-reviews-rating') . '"></select>' : '<span class="none" title="' . esc_attr__('None', 'g-business-reviews-rating') . '">—</span>') . '</td>
            <td class="retrieved date">' . ((is_numeric($a['retrieved'])) ? esc_html(date("Y/m/d H:i", $a['retrieved'])) : ((is_numeric($a['imported'])) ? '<span class="none" title="' . esc_attr(__('Imported', 'g-business-reviews-rating') . ': ' . date("Y/m/d H:i", $a['imported'])) . '">—</a>' : '<span class="none" title="' . esc_attr__('None', 'g-business-reviews-rating') . '">—</span>')) . '</td>
';
			if ($show_place_id)
			{
				$html .= '            <td class="place-id"><span class="abbr" title="' . (($this->demo) ? 'Abcde-0123456789-Fghij-01234-z' : esc_attr($a['place_id'])) . '"' . ((!empty($places) && array_key_exists($a['place_id'], $places)) ? ' data-place-name="' . esc_attr($places[$a['place_id']]) . '"' : '') . '>' . (($this->demo) ? 'Abcde…z' : esc_html(substr($a['place_id'], 0, 5)) . '…' . esc_html(substr($a['place_id'], -1, 1))) . '</span></td>
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
		return;
	}
	
	public function get_relative_time_description($time, $fallback = NULL, $use_fallback = FALSE)
	{
		// Return current relative time descriptive text
		
		$seconds = round(time() - $time);
		
		if ($use_fallback && $fallback == NULL)
		{
			return $fallback;
		}
		
		foreach ($this->relative_times as $k => $a)
		{
			if ($a['min_time'] == NULL && $seconds >= $a['max_time'] || $a['max_time'] == NULL && $seconds < $a['min_time'] || $a['min_time'] != NULL && $a['max_time'] != NULL && ($seconds >= $a['max_time'] || $seconds < $a['min_time']))
			{
				continue;
			}
			
			if (!$a['singular'] && preg_match('/^pl.*$/i', get_option('WPLANG')) && preg_match('/[\[\]?]/', $a['text']))
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
	
	public function wp_display($atts = NULL, $content = NULL, $shortcode = NULL)
	{
		// Display HTML from shortcodes 
		
		$this->instance_count = (!is_numeric($this->instance_count)) ? 1 : $this->instance_count + 1;
		
		if ($this->instance_count == 1 && !$this->dashboard)
		{
			$this->set_data();
		}
		
		$type_check = NULL;
		$shortcode_defaults = array(
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
			'type' => NULL,
			'vicinity' => NULL,
			'view' => NULL,
			'write_review_link' => NULL,
			'write_review_link_class' => NULL,
			'write_review_url' => NULL
		);
		$types = array(
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
		);
		
		foreach ($types as $t)
		{
			$shortcode_defaults[$t] = FALSE;
		}
		
		$args = shortcode_atts($shortcode_defaults, $atts);
		
		if (!is_array($atts))
		{
			$atts = array();
		}
	
		if (array_key_exists(0, $atts) && in_array($atts[0], $types))
		{
			$type_check = $atts[0];
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
		$place_id = (is_string($place_id) && mb_strlen($place_id) >= 20) ? $place_id : NULL;
		$type = (is_string($type)) ? preg_replace('/[^\w_]/', '_', trim(mb_strtolower($type))) : $type_check;
		$target = (is_string($target)) ? preg_replace('/[^\w_-]/', '-', trim(mb_strtolower($target))) : NULL;
		$rel = (is_string($rel) && preg_match('/^\s*(?:author|bookmark|external|no(?:follow|referrer|opener))\s*$/i', $rel)) ? mb_strtolower($rel) : ((is_string($rel) && preg_match('/^(?:f(?:alse)?|no?(?:ne)?|0|off|hide)$/i', $rel) || !is_string($rel) && array_key_exists('rel', $atts)) ? NULL : 'nofollow');
		$theme = (is_string($theme)) ? preg_replace('/[^\w _-]/', '-', trim(mb_strtolower($theme))) : NULL;
		$class = (is_string($class)) ? preg_replace('/[^\w _-]/', '-', trim(mb_strtolower($class))) : NULL;
		$color_scheme = (is_string($color_scheme) && array_key_exists(preg_replace('/[^\w_]/', '', trim(mb_strtolower($color_scheme))), $this->color_schemes)) ? preg_replace('/[^\w_]/', '', trim(mb_strtolower($color_scheme))) : ((array_key_exists('color_scheme', $atts)) ? NULL : get_option(__CLASS__ . '_color_scheme', NULL));
		$stylesheet = (is_bool($stylesheet) || is_string($stylesheet) && !preg_match('/^(?:f(?:alse)?|no?(?:ne)?|0|off|hide)$/i', $stylesheet)) ? (is_bool($stylesheet) && $stylesheet || is_numeric($stylesheet) && $stylesheet > 0 || is_string($stylesheet) && $stylesheet != NULL) : ((!array_key_exists('stylesheet', $atts)) ? get_option(__CLASS__ . '_stylesheet', NULL) : TRUE);
		$summary = (is_null($summary) || is_bool($summary) && $summary || is_string($summary) && preg_match('/^(?:t(?:rue)?|y(?:es)?|1|on|show)$/i', $summary)) ? TRUE : ((is_string($summary) && !preg_match('/^(?:f(?:alse)?|no?(?:ne)?|0|off|hide)$/i', $summary)) ? preg_split('/,\s*/', preg_replace('/[^\w ,_-]/', '-', trim(mb_strtolower($summary))), 8, PREG_SPLIT_NO_EMPTY) : FALSE);
		$icon = (is_null($icon) || is_bool($icon) && $icon || is_string($icon) && preg_match('/^(?:t(?:rue)?|y(?:es)?|1|on|show)$/', $icon)) ? (is_bool($summary) || is_array($summary) && in_array('icon', $summary)) : ((is_string($icon) && preg_match('/.+\.(?:jpe?g|png|svg|gif)/i', $icon)) ? $icon : FALSE);
		$name = (is_null($name) || is_bool($name) && $name || is_string($name) && preg_match('/^(?:t(?:rue)?|y(?:es)?|1|on|show)$/i', $name)) ? (is_bool($summary) || is_array($summary) && in_array('name', $summary)) : ((is_string($name) && !preg_match('/^(?:f(?:alse)?|no?(?:ne)?|0|off|hide)$/i', $name)) ? $name : FALSE);
		$vicinity = (is_null($vicinity) || is_bool($vicinity) && $vicinity || is_string($vicinity) && preg_match('/^(?:t(?:rue)?|y(?:es)?|1|on|show)$/i', $vicinity)) ? (is_bool($summary) || is_array($summary) && in_array('vicinity', $summary)) : ((is_string($vicinity) && !preg_match('/^(?:f(?:alse)?|no?(?:ne)?|0|off|hide)$/i', $vicinity)) ? $vicinity : FALSE);
		$rating_display = ((!is_array($summary) && (!array_key_exists('rating', $atts) || is_null($rating) || is_bool($rating) && $rating || is_string($rating) && !preg_match('/^(?:f(?:alse)?|no?(?:ne)?|0|off|hide)$/i', $rating))) || is_array($summary) && in_array('rating', $summary));
		$stars = (is_null($stars) || is_bool($stars) && $stars || is_string($stars) && preg_match('/^(?:t(?:rue)?|y(?:es)?|1|on|show|svg|vector)$/i', $stars)) ? ((is_bool($summary) || is_array($summary) && (in_array('stars', $summary))) ? ((!array_key_exists('stars', $atts) && $color_scheme != NULL) ? 'css' : (is_bool($summary) && $summary || is_array($summary) && in_array('stars', $summary))) : FALSE) : ((!array_key_exists('stars', $atts) && $color_scheme != NULL || is_string($stars) && preg_match('/(#(?:[0-9a-f]{2}){2,4}|#[0-9a-f]{3}|(?:rgba?|hsla?)\((?:\d+%?(?:deg|rad|grad|turn)?(?:,|\s)+){2,3}[\s\/]*[\d\.]+%?\))/i', $stars)) ? ((!array_key_exists('stars', $atts) && $color_scheme != NULL) ? 'css' : $stars) : ((is_string($stars) && preg_match('/^(?:html|css|(?:inline[\s_-]+(?:svg|vector)|(?:svg|vector)[\s_-]+inline))$/i', $stars)) ? preg_replace('/[\s_-]+/', ' ', mb_strtolower($stars)) : FALSE));
		$stars_grey = ((is_string($stars_grey) && preg_match('/(#(?:[0-9a-f]{2}){2,4}|#[0-9a-f]{3}|(?:rgba?|hsla?)\((?:\d+%?(?:deg|rad|grad|turn)?(?:,|\s)+){2,3}[\s\/]*[\d\.]+%?\))/i', $stars_grey)) ? $stars_grey : ((is_string($stars_grey) && preg_match('/^(?:html|css)$/i', $stars_grey)) ? mb_strtolower($stars_grey) : NULL));
		$stars_gray = ((is_string($stars_gray) && preg_match('/(#(?:[0-9a-f]{2}){2,4}|#[0-9a-f]{3}|(?:rgba?|hsla?)\((?:\d+%?(?:deg|rad|grad|turn)?(?:,|\s)+){2,3}[\s\/]*[\d\.]+%?\))/i', $stars_gray)) ? $stars_gray : ((is_string($stars_gray) && preg_match('/^(?:html|css)$/i', $stars_gray)) ? mb_strtolower($stars_gray) : $stars_grey));
		$count = (!is_array($summary) && (is_null($count) || is_bool($count) && $count || is_string($count) && preg_match('/^(?:t(?:rue)?|y(?:es)?|1|on|show)$/i', $count)) || is_array($summary) && in_array('count', $summary));
		$limit = (array_key_exists('limit', $atts)) ? ((is_numeric($limit) && $limit >= 0) ? intval($limit) : NULL) : get_option(__CLASS__ . '_review_limit', NULL);
		$view = (is_numeric($view) && $view >= 1 && $view <= 50 && (is_numeric($limit) && $limit > 0 || !is_numeric($limit))) ? ((is_numeric($limit) && $limit > 0 && $view > $limit) ? intval($limit) : intval($view)) : get_option(__CLASS__ . '_view', NULL);
		$loop = (is_numeric($view) && is_numeric($loop) && $loop >= 1 && $loop <= 999) ? intval($loop) : (is_numeric($loop) && $loop < 0 || is_bool($loop) && $loop || is_string($loop) && preg_match('/^(?:t(?:rue)?|y(?:es)?|1|on|show|loop|infin[ia]te?|forever|always|-\d+)$/', $loop));
		$iterations = (is_numeric($view) && $view >= 1 && is_numeric($iterations) && $iterations >= 1 && $iterations <= 999) ? intval($iterations) : NULL;
		$interval = (is_numeric($view) && is_numeric($interval) && $interval >= 0.3 && $interval <= 120) ? floatval($interval) : NULL;
		$transition = (is_string($transition) && preg_match('/^[a-z][0-9a-z .\/()_-]+$/i', $transition)) ? $transition : NULL;
		$transition_duration = (is_numeric($view) && is_string($transition) && is_numeric($transition_duration) && $transition_duration > 0.05 && $transition_duration <= 10) ? floatval($transition_duration) : NULL;
		$bullet = (is_string($bullet) && (mb_strlen($bullet) < 20 && !preg_match('/^(?:false|no(?:ne)?|0|off|hide|t(?:rue)?|y(?:es)?|1|on|show)$/i', $bullet))) ? $bullet : (!array_key_exists('bullet', $atts) || is_bool($bullet) && $bullet || is_string($bullet) && !preg_match('/^(?:false|no(?:ne)?|0|off|hide)$/i', $bullet));
		$cursor = (!array_key_exists('cursor', $atts) || is_bool($cursor) && $cursor || is_string($cursor) && preg_match('/^(?:true|yes|1|on|show|left|right|both)$/', $cursor));
		$draggable = (!array_key_exists('draggable', $atts) || is_bool($draggable) && $draggable || is_string($draggable) && preg_match('/^(?:true|yes|1|on|show|left|right|both)$/', $draggable));
		$avatar = (is_null($avatar) || is_bool($avatar) && $avatar || is_string($avatar) && preg_match('/^(?:t(?:rue)?|y(?:es)?|1|on|show)$/', $avatar)) ? TRUE : ((is_string($avatar) && preg_match('/^.+\.(?:jpe?g|png|svg|gif|webp).*$/i', $avatar)) ? $avatar : FALSE);
		$name_format = (is_bool($name_format) && !$name_format || is_string($name_format) && preg_match('/^(?:f(?:alse)?|no?(?:ne)?|0|off|hide)$/i', $name_format)) ? FALSE : ((is_string($name_format) && preg_match('/first|last|initials?|capitali[sz]e|uc(?:first|words)|(?:(?:lower|upper|title)(?:case)?)/i', $name_format)) ? $name_format : NULL);
		$date = (is_null($date) || is_bool($date) && $date || is_string($date) && preg_match('/^(?:true|yes|1|on|show|relative)$/', $date)) ? TRUE : ((is_string($date) && preg_match('/^[aABcdDeFgGhHiIjLlmMNnoOPrSstTuUvwWYyzZ ,.;:()\[\]\/_-]{1,20}$/', $date) && !preg_match('/^(?:false|no(?:ne)?|0|off|hide)$/i', $date)) ? $date : FALSE);
		$link = (is_bool($link) && $link || is_string($link) && preg_match('/^(?:t(?:rue)?|y(?:es)?|1|on|show)$/i', $link)) ? TRUE : ((is_string($link) && !preg_match('/^(?:f(?:alse)?|no?(?:ne)?|0|off|hide)$/i', $link)) ? $link : FALSE);
		$link_class = (is_string($link_class)) ? preg_replace('/[^\w _-]/', '-', trim(mb_strtolower($link_class))) : NULL;
		$link_disable = (is_bool($link_disable) && $link_disable || is_string($link_disable) && preg_match('/^(?:t(?:rue)?|y(?:es)?|1|on|show)$/i', $link_disable)) ? TRUE : ((is_string($link_disable) && !preg_match('/^(?:f(?:alse)?|no?(?:ne)?|0|off|hide)$/i', $link_disable)) ? preg_split('/,\s*/', preg_replace('/[^\w ,_-]/', '-', trim(mb_strtolower($link_disable))), 3, PREG_SPLIT_NO_EMPTY) : FALSE);
		$reviews_link = (is_bool($reviews_link) && $reviews_link || is_string($reviews_link) && preg_match('/^(?:t(?:rue)?|y(?:es)?|1|on|show)$/i', $reviews_link)) ? TRUE : ((is_string($reviews_link) && !preg_match('/^(?:f(?:alse)?|no?(?:ne)?|0|off|hide)$/i', $reviews_link)) ? $reviews_link : FALSE);
		$write_review_link = (is_bool($write_review_link) && $write_review_link || is_string($write_review_link) && preg_match('/^(?:t(?:rue)?|y(?:es)?|1|on|show)$/i', $write_review_link)) ? TRUE : ((is_string($write_review_link) && !preg_match('/^(?:f(?:alse)?|no?|0|off|hide)$/i', $write_review_link)) ? $write_review_link : FALSE);
		$reviews_url = (is_string($reviews_url) && preg_match('#^((https?:)?//[^/]{4,150}/?.*|/.*)$#i', $reviews_url)) ? $reviews_url : (($this->demo) ? 'https://search.google.com/local/reviews?placeid=ChIJq6pqZz2uEmsRaQAMbAl0RW0' : 'https://search.google.com/local/reviews?placeid=' . esc_attr((is_string($place_id)) ? $place_id : get_option(__CLASS__ . '_place_id')));			
		$write_review_url = (is_string($write_review_url) && preg_match('#^((https?:)?//[^/]{4,150}/?.*|/.*)$#i', $write_review_url)) ? $write_review_url : (($this->demo) ? 'https://search.google.com/local/writereview?placeid=ChIJq6pqZz2uEmsRaQAMbAl0RW0' : 'https://search.google.com/local/writereview?placeid=' . esc_attr((is_string($place_id)) ? $place_id : get_option(__CLASS__ . '_place_id')));			
		$reviews_link_class = (is_string($reviews_link_class)) ? preg_replace('/[^\w _-]/', '-', trim(mb_strtolower($reviews_link_class))) : $link_class;
		$write_review_link_class = (is_string($write_review_link_class)) ? preg_replace('/[^\w _-]/', '-', trim(mb_strtolower($write_review_link_class))) : $link_class;
		$animate = (is_null($animate) || is_bool($animate) && $animate || is_string($summary) && preg_match('/^(?:t(?:rue)?|y(?:es)?|1|on|show|animate|animation)$/i', $animate));
		$review_text = (is_null($review_text) || is_bool($review_text) && $review_text || is_string($review_text) && preg_match('/^(?:t(?:rue)?|y(?:es)?|1|on|show)$/i', $review_text));
		$attribution = (is_null($attribution) || is_bool($attribution) && $attribution || is_string($attribution) && preg_match('/^(?:t(?:rue)?|y(?:es)?|1|on|show|light|dark)$/i', $attribution)) ? ((is_string($attribution) && preg_match('/^(?:light|dark)$/i', $attribution)) ? mb_strtolower($attribution) : TRUE) : ((is_string($attribution) && !preg_match('/^(?:f(?:alse)?|no?(?:ne)?|0|off|hide)$/i', $attribution)) ? $attribution : FALSE);
		$review_text_excerpt_length = (is_numeric($excerpt) && $excerpt >= 20) ? intval($excerpt) : ((!array_key_exists('excerpt', $atts)) ? get_option(__CLASS__ . '_review_text_excerpt_length', NULL) : NULL);
		$review_text_height = (is_string($review_text_height) && preg_match('/^(?:\d+(?:\.\d+)?|\.\d+)(?:px|r?em|%|ch|ex)|(?:calc|clamp)\((?:(?:\d+(?:\.\d+)?|\.\d+)(?:px|r?em|%|ch|ex)[,\s\/*+-]*){1,3}\)$/i', $review_text_height)) ? mb_strtolower($review_text_height) : NULL;
		$review_text_format = (is_string($review_text_format) && $review_text_format != NULL) ? mb_strtolower($review_text_format) : NULL;
		$review_word = (is_string($review_word) && mb_strlen($review_word) >= 2) ? preg_split('#[/,]\s*#', $review_word, 2) : array(__('review', 'g-business-reviews-rating'), __('reviews', 'g-business-reviews-rating'));
		$more = (is_string($more)) ? $more : __('More', 'g-business-reviews-rating');
		$language = (is_string($language) && mb_strlen($language) >= 2 && mb_strlen($language) <= 16) ? substr($language, 0, 2) : NULL;
		$local_images = (array_key_exists('local_images', $atts)) ? (is_bool($local_images) && $local_images || is_string($local_images) && preg_match('/^(?:t(?:rue)?|y(?:es)?|1|on|show|local)$/i', $local_images)) : NULL;
		$loading = (is_string($loading) && preg_match('/^(eager|lazy)(?:\s?loading)?$/i', $loading, $m)) ? mb_strtolower($m[1]) : NULL;
		$html_tags = (is_string($html_tags) && mb_strlen($html_tags) >= 1) ? preg_split('/,+/', preg_replace('/^,+|,+$|[^0-9a-z,]/', '', $html_tags), 8, PREG_SPLIT_NO_EMPTY) : ((is_string($html_tag) && mb_strlen($html_tag) >= 1) ? preg_split('/,+/', preg_replace('/^,+|,+$|[^0-9a-z,]/', '', $html_tag), 8, PREG_SPLIT_NO_EMPTY) : array());
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
				if (!isset($this->data['result']) || isset($this->data['result']) && !is_array($this->data['result']))
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
				if (!isset($this->data['result']) || isset($this->data['result']) && !is_array($this->data['result']))
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

				if (!isset($this->data['result']['reviews']) || isset($this->data['result']) && !is_array($this->data['result']['reviews']))
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
			$this->local_images = get_option(__CLASS__ . '_local_images', FALSE);
			
			if (is_string($theme))
			{
				if ($key = array_search($theme, $this->reviews_themes) && is_string($key))
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
				$theme = (!$admin_preview) ? get_option(__CLASS__ . '_reviews_theme', NULL) : NULL;
				
				if (is_string($theme) && preg_match('/^light(?:\s+([^\s].+))?$/i', $theme, $m))
				{
					$theme = (isset($m[1])) ? $m[1] : NULL;
				}
			}
			
			$html_tags = (!empty($html_tags)) ? array_replace($this->default_html_tags, $html_tags) : $this->default_html_tags;
			$classes = array('google-business-reviews-rating', 'gmbrr');
			$review_item_inline = (is_string($review_item_order) && preg_match('/([\b\s,_-]|^)inline([\b\s,_-]|$)/i', $review_item_order));
			$review_item_text_first = (is_string($review_item_order) && preg_match('/([\b\s,_-]|^)(?:review(?:[\b\s,_-])?text|review|text)[\b\s,_-]?(?:first|top|before|true|on|high|above|1)([\b\s,_-]|$)/i', $review_item_order));
			$review_item_author_switch = (is_string($review_item_order) && preg_match('/([\b\s,_-]|^)(?:author(?:[\b\s,_-])?)[\b\s,_-]?(?:last|bottom|after|low|below|switch|flip)([\b\s,_-]|$)/i', $review_item_order));
			$rating = $this->get_data('rating', $place_id);
			$rating_rounded = $this->get_data('rating_rounded', $place_id);
			$name = (is_bool($name) && $name) ? $this->get_data('name', $place_id) : ((is_string($name)) ? $name : FALSE);
			$icon = (is_string($icon)) ? $icon : (is_bool($icon) && $icon);
			$vicinity = (is_bool($vicinity) && $vicinity) ? $this->get_data('vicinity', $place_id) : ((is_string($vicinity)) ? $vicinity : FALSE);
			$avatar = (is_bool($avatar) || is_string($avatar)) ? $avatar : FALSE;
			$date = (is_bool($date)) ? $date : ((is_string($date)) ? $date : FALSE);
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
				$classes[] = $color_scheme;
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
				$link = $reviews_url;
			}
			elseif ((is_bool($link) && $link || is_string($link)) && (!is_numeric($limit) || is_numeric($limit) && $limit > 0))
			{
				$link = (is_string($theme) && preg_match('/\b(?:tiny|badge)\b/', $theme)) ? $reviews_url : FALSE;
			}
			elseif (is_bool($link) && $link || is_string($link) && preg_match('/^(?:view[\s_-]*)?reviews?$/i', $link))
			{
				$link = $reviews_url;
			}
			elseif (is_string($link) && preg_match('/^write[\s_-]*(?:a[\s_-]*)?reviews?$/i', $link))
			{
				$link = $write_review_url;
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
					$html .= '<span class="attribution google-icon' . ((is_string($attribution)) ? ' ' . esc_attr($attribution) : '') . '" title="' . esc_attr__('Powered by Google') . '"></span> ';
				}

				if ($rating_display)
				{
					$html .= '<span class="number">' . esc_html($rating_rounded) . '</span>' . (((is_bool($stars) && $stars || is_string($stars) || $count)) ? ' ' : '');
				}

				if (is_bool($stars) && $stars || is_string($stars))
				{
					if (preg_match('/^inline|inline$/i', $stars))
					{
						$partial = (round($rating * 10, 0, PHP_ROUND_HALF_UP) - floor($rating) * 10) * 10;
						$html .= '<span class="all-stars inline-svg' . (($animate) ? ' animate' : '') . '">' . PHP_EOL;

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
						$html .= '<span class="all-stars' . (($animate) ? ' animate' : '') . '">'
						. str_repeat('<span class="star"></span>', ($partial > 0) ? floor($rating) : ceil($rating))
						. (($partial > 0) ? '<span class="star split-' . $partial . '-' . (100 - $partial) . '"></span>' : '')
						. str_repeat('<span class="star gray"></span>', ($partial > 0) ? (5 - ceil($rating)) : (5 - floor($rating)))
						. '</span> ';
					}	
					elseif ($stylesheet)
					{
						$html .= '<span class="all-stars">'
						. str_repeat('★', 5)
						. '<span class="rating-stars' . (($animate) ? ' animate' : '') . '"' . (($animate) ? ' style="width: 0;"' : '') . ' data-multiplier="' . (is_numeric($multiplier) ? esc_attr($multiplier) : '') . '">'
						. str_repeat('★', ceil($rating))
						. '</span></span> ';
					}
					else
					{
						$html .= '<span class="rating-stars' . (is_bool($animate) ? ' animate' : '') . '" data-rating="' . esc_attr($rating) . '" data-multiplier="' . (is_numeric($multiplier) ? esc_attr($multiplier) : '') . '">'
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
						$html .= esc_html($rating_count_rounded) . ' ' . $review_word;
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
					$html .= '	<' . $html_tags[9] . ' class="listing no-reviews">' . esc_html__('No reviews found, offset too high or another restriction.', 'g-business-reviews-rating') . '</' . $html_tags[9] . '>
';
				}
				elseif (!is_numeric($limit) || is_numeric($limit) && $limit > 0)
				{
					$options = array(
						'avatar' => $avatar,
						'avatar_directory' => NULL,
						'bullet' => $bullet,
						'date' => $date,
						'html_tags' => $html_tags,
						'id_name' => $id_name,
						'index' => 0,	
						'link_disable' => $link_disable,
						'loading' => $loading,
						'more' => $more,
						'name_format' => $name_format,
						'name_format_match' => array(),
						'rel' => $rel,
						'review_text' => $review_text,
						'review_text_excerpt_length' => $review_text_excerpt_length,
						'review_text_format' => $review_text_format,
						'review_text_height' => $review_text_height,
						'theme' => $theme,
						'view' => $view
					);

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
							if ($check_key = array_search('inline', $review_item_order) != FALSE)
							{
								$options['review_item_inline'] = TRUE;
								unset($review_item_order[$check_key]);
							}
							
							if ($check_key = array_search('text', $review_item_order) != FALSE)
							{
								$review_item_order[$check_key] = 'review';
							}
							
							if ($check_key = array_search('author', $review_item_order) != FALSE || $check_key = array_search('authorname', $review_item_order) != FALSE || $check_key = array_search('author_name', $review_item_order) != FALSE || $check_key = array_search('author-name', $review_item_order) != FALSE)
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
								$review_item_order = array('review', 'avatar', 'rating', 'date', 'name');
							}
							else
							{
								$review_item_order = array('review', 'avatar', 'name', 'rating', 'date');
							}
						}
						elseif (is_string($theme) && preg_match('/\bbubble\b/', $theme) && preg_match('/\bcenter\b/', $theme))
						{
							if ($options['review_item_author_switch'])
							{
								$options['review_item_text_first'] = TRUE;
								$review_item_order = array('rating', 'date', 'review', 'avatar', 'name');
							}
							else
							{
								$review_item_order = array('avatar', 'name', 'review', 'rating', 'date');
							}
						}
						else
						{
							if ($options['review_item_author_switch'])
							{
								$review_item_order = array('avatar', 'rating', 'date', 'name', 'review');
							}
							else
							{
								$review_item_order = array('avatar', 'name', 'rating', 'date', 'review');
							}
						}
					}
					
					$options['avatar'] = ((is_bool($avatar) && $avatar || is_string($avatar) && $avatar != NULL) && (is_string($review_item_order) || is_array($review_item_order) && in_array('avatar', $review_item_order))) ? $avatar : FALSE;
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
					$reviews_link_class = array_merge(array('view-reviews'), $reviews_link_class);
					$reviews_link_class = implode(' ', array_unique($reviews_link_class));
				}				
				else
				{
					$reviews_link_class = 'button view-reviews';
				}

				if ($write_review_link_class != NULL)
				{
					$write_review_link_class = preg_split('/\s+|,\s*/', $write_review_link_class, 15);
					$write_review_link_class = array_merge(array('write-review'), $write_review_link_class);
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
				$html .= '	<' . $html_tags[8] . ' class="attribution"><span class="powered-by-google' . ((is_string($attribution)) ? ' ' . esc_attr($attribution) : '') . '" title="' . esc_attr__('Powered by Google') . '"></span></' . $html_tags[8] . '>
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
			
			$html = ($content != NULL) ? '<a href="' . $reviews_url . '"' . (($class != NULL) ? ' class="' . esc_attr($class) . '"' : '') . (($target != NULL) ? ' target="' . esc_attr($target) . '"' : '') . (($rel != NULL) ? ' rel="' . esc_attr($rel) . '"' : '') . '>' . $content . '</a>' : $reviews_url;
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

			$html = ($content != NULL) ? '<a href="' . $write_review_url . '"' . (($class != NULL) ? ' class="' . esc_attr($class) . '"' : '') . (($target != NULL) ? ' target="' . esc_attr($target) . '"' : '') . (($rel != NULL) ? ' rel="' . esc_attr($rel) . '"' : '') . '>' . $content . '</a>' : $write_review_url;
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
				if (!isset($this->data['result']) || isset($this->data['result']) && !is_array($this->data['url']))
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

			$url = (isset($this->data['result']['url']) && is_string($this->data['result']['url'])) ? $this->data['result']['url'] : '';
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

	private function get_review_ids($reviews = NULL)
	{
		$ids = array();

		if (!is_array($reviews))
		{
			$reviews = $this->result['result']['reviews'];
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

	private function get_review_id($review = NULL)
	{
		return (is_array($review) && isset($review['author_url']) && is_string($review['author_url']) && preg_match('/^.+[^\d](\d{20,120})[^\d].*$/', $review['author_url'], $m)) ? $m[1] : NULL;
	}
	
	private function review_item($data = NULL, $options = NULL, $type = 'all')
	{
		// Display individual review items from well-formatted data and options
		
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
			
			$review_text = strip_tags($review_text);
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
			
			$html .= '			<span class="author-avatar' . (((is_bool($avatar) || is_string($avatar) && $avatar != 'local') && isset($data['avatar']) && $data['avatar'] != NULL && $avatar_directory != NULL) ? ' local' : '') . '">' . ((isset($data['author_url']) && $data['author_url'] != NULL && (is_bool($link_disable) && !$link_disable || is_array($link_disable) && !in_array('author', $link_disable))) ? '<a href="' . esc_attr($data['author_url']) . '" target="_blank"' . (($rel != NULL) ? ' rel="' . esc_attr($rel) . '"' : '') . '>' : '') . (($data['profile_photo_url'] != NULL) ? '<img src="' . esc_attr((is_string($avatar) && $avatar != 'local') ? $avatar : ((isset($data['avatar']) && $data['avatar'] != NULL && $avatar_directory != NULL) ? $avatar_directory . '/' . $data['avatar'] : $data['profile_photo_url'])) . '" alt="Avatar"' . ((isset($loading) && $loading != NULL) ? ' loading="' . esc_attr($loading) . '"' : '') . '>' : '—') . ((isset($data['author_url']) && $data['author_url'] != NULL && (is_bool($link_disable) && !$link_disable || is_array($link_disable) && !in_array('author', $link_disable))) ? '</a>' : '') . '</span>
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
				$author_name = ucwords(trim($author_name), " -\t\r\n\f\v'’");
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
			
			$html .= '				<span class="rating">' . str_repeat('★', $data['rating']) . (($data['rating'] < 5) ? '<span class="not">' . str_repeat('☆', (5 - $data['rating'])) . '</span>' : '') . '</span>
';
			return $html;
		case 'date':
			if (!isset($data['time']) && !isset($data['relative_time_description']))
			{
				return $html;
			}
			
			if (is_string($date) && is_numeric($data['time']))
			{
				$html .= '				<span class="date">' . esc_html((function_exists('wp_date')) ? wp_date($date, $data['time']) : date($date, $data['time'])) . '</span>
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
			
			$html .= '	<' . $html_tags[5] . ' class="navigation">'; 
				
			for ($j = 0; $j < $index / $view; $j++)
			{
				$html .= '		<' . $html_tags[6] . ' class="bullet' . (($j == 0) ? ' current' : '') . '"><a href="#' . esc_attr((($id_name != NULL) ? $id_name : 'google-business-reviews-rating' . (($this->instance_count > 1) ? '-' . $this->instance_count : ''))) . '" data-slide="' . esc_attr($j + 1) . '">' . ((is_string($bullet) && $bullet != NULL) ? $bullet : '●') . '</a></' . $html_tags[6] . '>'; 
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
			$review_item_order = array('avatar', 'name', 'rating', 'date', 'review');
		}
		
		if (in_array('author', $review_item_order) || in_array('authorname', $review_item_order) || in_array('author_name', $review_item_order) || in_array('author-name', $review_item_order))
		{
			$review_item_order = str_replace(array('authorname', 'author_name', 'author-name', 'author'), array('name', 'name', 'name', 'name'), $review_item_order);
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
		$html .= '		<' . $html_tags[4] . ' class="' . esc_attr('rating-' . $data['rating']) . ((is_numeric($view)) ? ' ' . (($index < $view) ? 'visible' : 'hidden') : '') . ((is_bool($avatar) && !$avatar) ? ' no-avatar' : '') . ((!is_bool($date) || is_bool($date) && !$date) && (!is_string($date) || is_string($date) && !is_numeric($data['time'])) ? ' no-date' : '') . (($review_item_text_first) ? ' text-first' : '') . (($review_item_inline) ? ' inline' : '') . (($review_item_author_switch) ? ' author-switch' : '') . '" data-index="' . esc_attr($index) . '">
';

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

	private function get_retrieval_sort($next_sort = FALSE)
	{
		// Get the current or next retrieval/review sort
		
		$retrieval_sort = 'most_relevant';
		
		if ($this->place_id == NULL || $this->demo)
		{
			return $retrieval_sort;
		}

		$option = get_option(__CLASS__ . '_retrieval_sort', 'most_relevant');

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
			$retrieval = get_option(__CLASS__ . '_retrieval');
				
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
	
	public function translation_exists($loose = FALSE)
	{
		// Check if current translation exists
		
		if ($loose)
		{
			return (preg_match('/^(?:(?:de|en|es|f|it|nl|pl).*)?$/i', get_option('WPLANG')));
		}
		
		$test_word = 'Welcome';
		
		return (preg_match('/^(?:en.*)?$/i', get_option('WPLANG')) || __($test_word, 'g-business-reviews-rating') != $test_word);
	}
	
	public function dashboard_widget()
	{
		// Initiate Dashboard Widget
	
		if ($this->demo || intval(get_option(__CLASS__ . '_meta_box_limit', 5)) < 1)
		{
			return TRUE;
		}
		
		wp_add_dashboard_widget(__CLASS__, __('Reviews and Rating - Google Reviews', 'g-business-reviews-rating'), array($this, 'dashboard_widget_display'), NULL, NULL, 'side', 'default');
		return TRUE;
	}
	
	public function dashboard_widget_display()
	{
		// Display Dashboard Widget
	
		if ($this->demo)
		{
			return TRUE;
		}
	
		echo $this->get_reviews('latest');
		return TRUE;
	}
	
	public function loaded()
	{
		// Load languages
		
		load_plugin_textdomain('g-business-reviews-rating', FALSE, basename(dirname(__FILE__)) . '/languages');

		return TRUE;
	}

	private function notification($message = NULL, $heading = NULL, $type = NULL)
	{
		// Display a relevant notification

		if ($message != NULL)
		{
			$html = '<p class="plugin-notification notice is-dismissable' . (($type != NULL) ? esc_attr(' ' . $type) : '') . ' visible">' . PHP_EOL
			. '<span class="close"><a href="#google-business-reviews-rating-settings" class="button dismiss later" data-notification-action="notification rate later" title="' . esc_attr__('Remind me later', 'g-business-reviews-rating') . '"><span class="dashicons dashicons-dismiss"></span></a></span>' . PHP_EOL
			. (($heading != NULL) ? '<span class="heading">' . $heading . '</span>' . PHP_EOL : '')
			. '<span class="message">'
			. $message
			. '</span>' . PHP_EOL
			. '<span class="buttons">'
			. '<a href="#google-business-reviews-rating-settings" class="button ui-button later" data-notification-action="notification rate later">' . esc_html__('Remind me later', 'g-business-reviews-rating') . '</a> '
			. '<a href="#google-business-reviews-rating-settings" class="button ui-button dismiss" data-notification-action="notification rate dismiss">' . esc_html__('Dismiss for a year', 'g-business-reviews-rating') . '</a> '
			. '<a href="#google-business-reviews-rating-settings" class="button ui-button done" data-notification-action="notification rate done">' . esc_html__('I’ve already left a review', 'g-business-reviews-rating') . '</a>'
			. '</span>' . PHP_EOL
			. '</p>';
			self::log('notification rate', (($heading != NULL) ? $heading . PHP_EOL : '') . strip_tags($message));

			return $html;
		}

		if (!$this->administrator || !$this->valid() || $this->count_reviews_all <= 5 || !function_exists('array_column'))
		{
			return '';
		}

		$log = get_option(__CLASS__ . '_log', array());
		
		if (!is_array($log) || count($log) <= 2)
		{
			if (mt_rand(0, 3) >= 2)
			{
				$initial_version = get_option(__CLASS__ . '_initial_version', NULL);
				
				if ($initial_version != NULL && floatval($initial_version) <= 4.27 && (empty($log) || is_array($log) && !in_array('notification rate', array_column($log, 'type'))))
				{
					/* translators: 1: The initial version of this plugin, 2: refers to review URL at wordpress.org, 3: string to handle notification data */
					return $this->notification(sprintf(__('You have used this plugin for quite a while, since version %1$s. We’d love to hear what you think about its design, features, support… So, please consider <a href="%2$s" target="_blank" %3$s>leaving a review</a>!', 'g-business-reviews-rating'), $initial_version, 'https://wordpress.org/support/plugin/g-business-reviews-rating/reviews/#new-post', 'data-notification-action="notification rate now"'), esc_html__('You’ve experienced using this plugin', 'g-business-reviews-rating'), 'version-change');
				}
			}
			
			return '';
		}

		if (!is_array($log) || in_array('notification rate done', array_column($log, 'type')))
		{
			return '';
		}
		
		$installation_timestamp = array_search('install', array_column($log, 'type'));
		$reset_timestamp = NULL;
		$reset_timestamp_notify = $installation_timestamp_notify = FALSE;

		if (is_numeric($installation_timestamp) && isset($log[$installation_timestamp]) && isset($log[$installation_timestamp]['time']))
		{
			$installation_timestamp = $log[$installation_timestamp]['time'];

			if (is_numeric($installation_timestamp) && $installation_timestamp < time() - YEAR_IN_SECONDS)
			{
				$installation_timestamp_notify = TRUE;
			}
		}
		else
		{
			$reset_timestamp = array_search('reset', array_column($log, 'type'));
			$reset_timestamp_notify = FALSE;

			if (is_numeric($reset_timestamp) && isset($log[$reset_timestamp]) && isset($log[$reset_timestamp]['time']))
			{
				$reset_timestamp = $log[$reset_timestamp]['time'];

				if (is_numeric($reset_timestamp) && $reset_timestamp < time() - YEAR_IN_SECONDS)
				{
					$reset_timestamp_notify = TRUE;
				}
			}
			else
			{
				$reset_timestamp = NULL;
			}

			$installation_timestamp = NULL;
		}

		$notification_rating = TRUE;
		$notification_rating_now_timestamp = time() - HOUR_IN_SECONDS;
		$notification_rating_later_timestamp = time() - 2 * WEEK_IN_SECONDS;
		$notification_rating_dismiss_timestamp = time() - YEAR_IN_SECONDS;
		$log_keys = array_reverse(array_keys($log));

		foreach ($log_keys as $k)
		{
			if (!isset($log[$k]['type']) || !isset($log[$k]['time']))
			{
				continue;
			}

			if ($log[$k]['type'] == 'notification rate now' && $log[$k]['time'] >= $notification_rating_now_timestamp)
			{
				$notification_rating = FALSE;
				break;
			}

			if ($log[$k]['type'] == 'notification rate later' && $log[$k]['time'] >= $notification_rating_later_timestamp)
			{
				$notification_rating = FALSE;
				break;
			}

			if ($log[$k]['type'] == 'notification rate dismiss' && $log[$k]['time'] >= $notification_rating_dismiss_timestamp)
			{
				$notification_rating = FALSE;
				break;
			}

			if (($log[$k]['type'] == 'install' || $log[$k]['type'] == 'reset') && ($log[$k]['time'] >= $notification_rating_later_timestamp || $this->count_reviews_all <= 10))
			{
				$notification_rating = FALSE;
				break;
			}

			if ($log[$k]['time'] < $notification_rating_dismiss_timestamp)
			{
				break;
			}
		}
		
		if ($notification_rating && $this->get_data('boolean'))
		{
			if (is_numeric($installation_timestamp) && $installation_timestamp_notify || is_numeric($reset_timestamp) && $reset_timestamp_notify)
			{
				/* translators: 1: The plugin installation date, 2: refers to review URL at wordpress.org, 3: string to handle notification data */
				return $this->notification(sprintf(__('You have used this plugin for quite a while, since %1$s. We’d love to hear what you think about its design, features, support… So, please consider <a href="%2$s" target="_blank" %3$s>leaving a review</a>!', 'g-business-reviews-rating'), wp_date("F Y", $installation_timestamp), 'https://wordpress.org/support/plugin/g-business-reviews-rating/reviews/#new-post', 'data-notification-action="notification rate now"'), esc_html__('You’ve experienced this plugin', 'g-business-reviews-rating'), 'version-change');
			}

			/* translators: 1: refers to review URL at wordpress.org, 2: string to handle notification data */
			return $this->notification(sprintf(__('We’d love to hear what you think about its design, features, support… So, please consider <a href="%1$s" target="_blank" %2$s>leaving a review</a>!', 'g-business-reviews-rating'), 'https://wordpress.org/support/plugin/g-business-reviews-rating/reviews/#new-post', 'data-notification-action="notification rate now"'), esc_html__('Please review our Google Reviews plugin', 'g-business-reviews-rating'), 'review-reminder');
		}

		return '';
	}

	public function delete_places($place_ids, $force = FALSE)
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
			$this->places = get_option(__CLASS__ . '_places', array());
		}
		
		if (empty($this->places))
		{
			return FALSE;
		}
		
		foreach ($place_ids as $place_id)
		{
			if (!is_string($place_id) || $place_id == 'confirm' || $place_id == $this->place_id || $place_id == get_option(__CLASS__ . '_place_id'))
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
	
	private function delete_place($place_id)
	{
		if (!is_string($place_id))
		{
			return FALSE;
		}

		if (empty($this->places))
		{
			$this->places = get_option(__CLASS__ . '_places', array());
		}
		
		if (empty($this->places))
		{
			return FALSE;
		}

		$reviews = array();

		foreach ($this->places as $i => $a)
		{
			if (!isset($a['place_id']) || $a['place_id'] != $place_id)
			{
				continue;
			}

			if (empty($this->reviews))
			{
				$this->reviews = get_option(__CLASS__ . '_reviews', array());
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

				delete_transient(__CLASS__ . '_reviews_shuffled');
				wp_cache_delete('reviews_shuffled', __CLASS__);
				wp_cache_delete('reviews', __CLASS__);
				update_option(__CLASS__ . '_reviews', $this->reviews, 'no');
			}

			unset($this->places[$i]);
			sort($this->places);
			update_option(__CLASS__ . '_places', $this->places, 'yes');

			return TRUE;
		}
		
		return TRUE;
	}

	private function notification_reset()
	{
		// Clear all notifications from log file

		$log = get_option(__CLASS__ . '_log', array());
		
		if (!is_array($log) || is_array($log) && empty($log))
		{
			return FALSE;
		}

		$cleaned_log = array();

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

		update_option(__CLASS__ . '_log', $cleaned_log, 'no');

		return TRUE;
	}

	public static function log($type, $data = NULL)
	{
		// Log actions

		$log = get_option(__CLASS__ . '_log', array());

		if (!is_array($log))
		{
			$log = array();
		}

		$log = array_splice($log, -1000);

		$log[] = array(
			'type' => $type,
			'data' => $data,
			'user' => (function_exists('get_current_user_id')) ? get_current_user_id() : NULL,
			'cron' => (defined('DOING_CRON') && DOING_CRON),
			'time' => time()
		);

		update_option(__CLASS__ . '_log', $log, 'no');

		return TRUE;
	}
}

defined('GOOGLE_BUSINESS_REVIEWS_RATING_DEMO_RESULT') or define('GOOGLE_BUSINESS_REVIEWS_RATING_DEMO_RESULT', '{"html_attributions":"","result":{"icon":"https://maps.gstatic.com/mapfiles/place_api/icons/restaurant-71.png","name":"Everyday Demo Restaurant","rating":3.9,"reviews":[{"author_name":"Lisa Dooley","author_url":"#","language":"en","profile_photo_url":"data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0idXRmLTgiPz4KPCFET0NUWVBFIHN2ZyBQVUJMSUMgIi0vL1czQy8vRFREIFNWRyAxLjEvL0VOIiAiaHR0cDovL3d3dy53My5vcmcvR3JhcGhpY3MvU1ZHLzEuMS9EVEQvc3ZnMTEuZHRkIj4KPHN2ZyB2ZXJzaW9uPSIxLjEiIGlkPSJMYXllcl8xIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHhtbG5zOnhsaW5rPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5L3hsaW5rIiB4PSIwcHgiIHk9IjBweCIKd2lkdGg9IjEyOHB4IiBoZWlnaHQ9IjEyOHB4IiB2aWV3Qm94PSIwIDAgMTI4IDEyOCIgZW5hYmxlLWJhY2tncm91bmQ9Im5ldyAwIDAgMTI4IDEyOCIgeG1sOnNwYWNlPSJwcmVzZXJ2ZSI+CjxjaXJjbGUgZmlsbD0iIzAwN0Y3MCIgY3g9IjY0IiBjeT0iNjQiIHI9IjY0Ii8+CjxnPgo8cGF0aCBmaWxsPSIjRkZGRkZGIiBkPSJNNDYuOTM5LDI4LjA1aDYuMjU2djYyLjU1N0g4OC4xN3Y1LjQ5N2gtNDEuMjNWMjguMDV6Ii8+CjwvZz4KPC9zdmc+Cg==","rating":5,"relative_time_description":"a month ago","text":"Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.","time":1561637346},{"author_name":"Catherine P","author_url":"#","language":"en","profile_photo_url":"data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0idXRmLTgiPz4KPCFET0NUWVBFIHN2ZyBQVUJMSUMgIi0vL1czQy8vRFREIFNWRyAxLjEvL0VOIiAiaHR0cDovL3d3dy53My5vcmcvR3JhcGhpY3MvU1ZHLzEuMS9EVEQvc3ZnMTEuZHRkIj4KPHN2ZyB2ZXJzaW9uPSIxLjEiIGlkPSJMYXllcl8xIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHhtbG5zOnhsaW5rPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5L3hsaW5rIiB4PSIwcHgiIHk9IjBweCIKd2lkdGg9IjEyOHB4IiBoZWlnaHQ9IjEyOHB4IiB2aWV3Qm94PSIwIDAgMTI4IDEyOCIgZW5hYmxlLWJhY2tncm91bmQ9Im5ldyAwIDAgMTI4IDEyOCIgeG1sOnNwYWNlPSJwcmVzZXJ2ZSI+CjxjaXJjbGUgZmlsbD0iI0JGMDkwMCIgY3g9IjY0IiBjeT0iNjQiIHI9IjY0Ii8+CjxnPgo8cGF0aCBmaWxsPSIjRkZGRkZGIiBkPSJNOTIuNjI1LDczLjkyNWMtMC41MDcsNy41ODMtMy4xNSwxMy40OTItNy45MjksMTcuNzI1Qzc5LjkxOCw5NS44ODQsNzMuNDc3LDk4LDY1LjM3Niw5OApjLTQuNTU3LDAtOC42NTUtMC44MjItMTIuMjk1LTIuNDY0Yy0zLjY0MS0xLjY0My02Ljc1Ni0zLjk5Ni05LjM1MS03LjA2MmMtMi41OTUtMy4wNjQtNC41OS02Ljg0LTUuOTgyLTExLjMyNwpjLTEuMzkyLTQuNDg1LTIuMDg4LTkuNTcyLTIuMDg4LTE1LjI2YzAtNS42MjMsMC43MTEtMTAuNjQ3LDIuMTM1LTE1LjA3YzEuNDI1LTQuNDIyLDMuNDM1LTguMTY3LDYuMDI5LTExLjIzMgpjMi41OTUtMy4wNjQsNS43MjktNS40MDMsOS40LTcuMDE0YzMuNjctMS42MTEsNy43ODQtMi40MTcsMTIuMzQyLTIuNDE3YzMuODYxLDAsNy4zOTEsMC41MTMsMTAuNTg3LDEuNTM4CmMzLjE5NSwxLjAyNSw1LjkzMywyLjQ3OSw4LjIxMiw0LjM2YzIuMjc3LDEuODgyLDQuMDY2LDQuMTUsNS4zNjQsNi44MDRjMS4yOTcsMi42NTQsMi4wNDIsNS42MjUsMi4yMzEsOC45MWgtNi4yNTYKYy0wLjUwNi00Ljk5MS0yLjU1OS04LjkyNC02LjE2MS0xMS44MDFjLTMuNjAyLTIuODc1LTguMjc4LTQuMzEzLTE0LjAyNy00LjMxM2MtNy4yNjcsMC0xMi45ODUsMi41NjgtMTcuMTU2LDcuNzAxCmMtNC4xNyw1LjEzNS02LjI1NSwxMi42MTQtNi4yNTUsMjIuNDM4YzAsNC45NDUsMC41NTIsOS4zMTksMS42NTksMTMuMTIyYzEuMTA0LDMuODAzLDIuNjg1LDcuMDA1LDQuNzM5LDkuNjAzCmMyLjA1MywyLjYsNC41MzQsNC41ODEsNy40NCw1Ljk0M2MyLjkwNiwxLjM2Miw2LjE2MSwyLjA0NCw5Ljc2MywyLjA0NGM2LjEyOCwwLDEwLjk5NS0xLjYyNiwxNC41OTctNC44ODIKYzMuNjAyLTMuMjU0LDUuNjIzLTcuODE5LDYuMDY2LTEzLjY5Nkg5Mi42MjV6Ii8+CjwvZz4KPC9zdmc+Cg==","rating":1,"relative_time_description":"2 months ago","text":"Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. \\nExcepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum. Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. \\nExcepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.","time":1557738977},{"author_name":"Fay A","author_url":"#","language":"en","profile_photo_url":"data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0idXRmLTgiPz4KPCFET0NUWVBFIHN2ZyBQVUJMSUMgIi0vL1czQy8vRFREIFNWRyAxLjEvL0VOIiAiaHR0cDovL3d3dy53My5vcmcvR3JhcGhpY3MvU1ZHLzEuMS9EVEQvc3ZnMTEuZHRkIj4KPHN2ZyB2ZXJzaW9uPSIxLjEiIGlkPSJMYXllcl8xIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHhtbG5zOnhsaW5rPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5L3hsaW5rIiB4PSIwcHgiIHk9IjBweCIKd2lkdGg9IjEyOHB4IiBoZWlnaHQ9IjEyOHB4IiB2aWV3Qm94PSIwIDAgMTI4IDEyOCIgZW5hYmxlLWJhY2tncm91bmQ9Im5ldyAwIDAgMTI4IDEyOCIgeG1sOnNwYWNlPSJwcmVzZXJ2ZSI+CjxjaXJjbGUgZmlsbD0iI0EzMDBDNCIgY3g9IjY0IiBjeT0iNjQiIHI9IjY0Ii8+CjxnPgo8cGF0aCBmaWxsPSIjRkZGRkZGIiBkPSJNNDQuNjY1LDI3Ljk1Nmg0My4xMjZ2NS40OTdINTAuOTJ2MjQuODMzaDMzLjQ1OHY1LjQ5N0g1MC45MnYzMi4zMjFoLTYuMjU2VjI3Ljk1NnoiLz4KPC9nPgo8L3N2Zz4K","rating":5,"relative_time_description":"2 weeks ago","text":"Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam.","time":1563122393},{"author_name":"Dexter Ortega","author_url":"#","language":"es","profile_photo_url":"data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0idXRmLTgiPz4KPCFET0NUWVBFIHN2ZyBQVUJMSUMgIi0vL1czQy8vRFREIFNWRyAxLjEvL0VOIiAiaHR0cDovL3d3dy53My5vcmcvR3JhcGhpY3MvU1ZHLzEuMS9EVEQvc3ZnMTEuZHRkIj4KPHN2ZyB2ZXJzaW9uPSIxLjEiIGlkPSJMYXllcl8xIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHhtbG5zOnhsaW5rPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5L3hsaW5rIiB4PSIwcHgiIHk9IjBweCIKd2lkdGg9IjEyOHB4IiBoZWlnaHQ9IjEyOHB4IiB2aWV3Qm94PSIwIDAgMTI4IDEyOCIgZW5hYmxlLWJhY2tncm91bmQ9Im5ldyAwIDAgMTI4IDEyOCIgeG1sOnNwYWNlPSJwcmVzZXJ2ZSI+CjxjaXJjbGUgZmlsbD0iIzI2NUVGRiIgY3g9IjY0IiBjeT0iNjQiIHI9IjY0Ii8+CjxnPgo8cGF0aCBmaWxsPSIjRkZGRkZGIiBkPSJNNTkuMjE0LDI3Ljk1NmM0LjA0MywwLDcuNzA4LDAuMTg5LDEwLjk5NCwwLjU2OGMzLjI4NSwwLjM3OSw2LjI1NiwxLjQyMiw4LjkxLDMuMTI4CmM0LjE3LDIuNjU0LDcuMzYsNi41MjUsOS41NzMsMTEuNjExYzIuMjExLDUuMDg3LDMuMzE3LDExLjI5NiwzLjMxNywxOC42MjVjMCw3Ljg5OS0xLjIxOCwxNC40NTQtMy42NDksMTkuNjY4CmMtMi40MzQsNS4yMTMtNS45ODcsOS4wODQtMTAuNjYzLDExLjYxYy0yLjQ2NSwxLjMyNy01LjM3MiwyLjE0OS04LjcyMSwyLjQ2NWMtMy4zNSwwLjMxNi03LjIzNSwwLjQ3NC0xMS42NTgsMC40NzRIMzguNTUxVjI3Ljk1NgpoMTYuMDE5SDU5LjIxNHogTTU3Ljk4MSw5MC42MDdjMy43OTIsMCw3LjEwOC0wLjEyNiw5Ljk1Mi0wLjM4MWMyLjg0NC0wLjI1Myw1LjI3NS0wLjk4Miw3LjI5OC0yLjE4OApjNi44ODctNC4xMiwxMC4zMzItMTIuNzc1LDEwLjMzMi0yNS45NjJjMC0xMi43NDItMy4yODctMjEuMjctOS44NTctMjUuNTgxYy0yLjIxMy0xLjQ1Ny00LjgzNC0yLjMzLTcuODY3LTIuNjE1CmMtMy4wMzMtMC4yODQtNi41NC0wLjQyOC0xMC41MjEtMC40MjhINDQuODA3djU3LjE1NUg1Ny45ODF6Ii8+CjwvZz4KPC9zdmc+Cg==","rating":5,"relative_time_description":"3 months ago","text":"Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.","time":1554727451},{"author_name":"Mary N","author_url":"#","language":"en","profile_photo_url":"data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0idXRmLTgiPz4KPCFET0NUWVBFIHN2ZyBQVUJMSUMgIi0vL1czQy8vRFREIFNWRyAxLjEvL0VOIiAiaHR0cDovL3d3dy53My5vcmcvR3JhcGhpY3MvU1ZHLzEuMS9EVEQvc3ZnMTEuZHRkIj4KPHN2ZyB2ZXJzaW9uPSIxLjEiIGlkPSJMYXllcl8xIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHhtbG5zOnhsaW5rPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5L3hsaW5rIiB4PSIwcHgiIHk9IjBweCIKd2lkdGg9IjEyOHB4IiBoZWlnaHQ9IjEyOHB4IiB2aWV3Qm94PSIwIDAgMTI4IDEyOCIgZW5hYmxlLWJhY2tncm91bmQ9Im5ldyAwIDAgMTI4IDEyOCIgeG1sOnNwYWNlPSJwcmVzZXJ2ZSI+CjxjaXJjbGUgZmlsbD0iI0I2M0RGRiIgY3g9IjY0IiBjeT0iNjQiIHI9IjY0Ii8+CjxnPgo8cGF0aCBmaWxsPSIjRkZGRkZGIiBkPSJNMzIuODY0LDI3Ljk1Nmg4LjgxNWwyMi40NjMsNTkuOTk4bDIyLjA4NC01OS45OThoOC44MTV2NjguMTQ5aC02LjI1NlYzNi42NzVMNjcuMDgxLDk2LjEwNGgtNS43ODIKTDM5LjEyLDM2LjY3NXY1OS40MjloLTYuMjU2VjI3Ljk1NnoiLz4KPC9nPgo8L3N2Zz4K","rating":4,"relative_time_description":"4 months ago","text":"Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.","time":1552675416},{"author_name":"Jerry Jet","author_url":"#","language":"en","profile_photo_url":"data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0idXRmLTgiPz4KPCFET0NUWVBFIHN2ZyBQVUJMSUMgIi0vL1czQy8vRFREIFNWRyAxLjEvL0VOIiAiaHR0cDovL3d3dy53My5vcmcvR3JhcGhpY3MvU1ZHLzEuMS9EVEQvc3ZnMTEuZHRkIj4KPHN2ZyB2ZXJzaW9uPSIxLjEiIGlkPSJMYXllcl8xIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHhtbG5zOnhsaW5rPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5L3hsaW5rIiB4PSIwcHgiIHk9IjBweCIKd2lkdGg9IjEyOHB4IiBoZWlnaHQ9IjEyOHB4IiB2aWV3Qm94PSIwIDAgMTI4IDEyOCIgZW5hYmxlLWJhY2tncm91bmQ9Im5ldyAwIDAgMTI4IDEyOCIgeG1sOnNwYWNlPSJwcmVzZXJ2ZSI+CjxjaXJjbGUgZmlsbD0iI0ZGQjQwNSIgY3g9IjY0IiBjeT0iNjQiIHI9IjY0Ii8+CjxnPgo8cGF0aCBmaWxsPSIjRkZGRkZGIiBkPSJNNDkuNTQ2LDc1LjI1MnY0LjM2YzAsOC41OTQsMy44MjMsMTIuODkxLDExLjQ2OSwxMi44OTFjNC41NSwwLDcuNzM5LTEuMTIxLDkuNTczLTMuMzY1CmMxLjgzMi0yLjI0MiwyLjc0OC01Ljg5MiwyLjc0OC0xMC45NDdWMjguMDVoNi4yNTZ2NTEuNTYyYzAsNi4wNjYtMS42MTEsMTAuNjQ4LTQuODM0LDEzLjc0M0M3MS41MzUsOTYuNDUxLDY2Ljg5MSw5OCw2MC44MjUsOTgKYy0xMS42OTEsMC0xNy41MzUtNS45MzgtMTcuNTM1LTE3LjgxOXYtNC45MjlINDkuNTQ2eiIvPgo8L2c+Cjwvc3ZnPgo=","rating":2,"relative_time_description":"4 months ago","text":"Ut enim ad minima veniam, quis nostrum exercitationem ullam corporis suscipit laboriosam, nisi ut aliquid ex ea commodi consequatur? Quis autem vel eum iure reprehenderit qui in ea voluptate velit esse quam nihil molestiae consequatur, vel illum qui dolorem eum fugiat quo voluptas nulla pariatur?","time":1552675416},{"author_name":"Ian A","author_url":"#","language":"it","profile_photo_url":"data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0idXRmLTgiPz4KPCFET0NUWVBFIHN2ZyBQVUJMSUMgIi0vL1czQy8vRFREIFNWRyAxLjEvL0VOIiAiaHR0cDovL3d3dy53My5vcmcvR3JhcGhpY3MvU1ZHLzEuMS9EVEQvc3ZnMTEuZHRkIj4KPHN2ZyB2ZXJzaW9uPSIxLjEiIGlkPSJMYXllcl8xIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHhtbG5zOnhsaW5rPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5L3hsaW5rIiB4PSIwcHgiIHk9IjBweCIKd2lkdGg9IjEyOHB4IiBoZWlnaHQ9IjEyOHB4IiB2aWV3Qm94PSIwIDAgMTI4IDEyOCIgZW5hYmxlLWJhY2tncm91bmQ9Im5ldyAwIDAgMTI4IDEyOCIgeG1sOnNwYWNlPSJwcmVzZXJ2ZSI+CjxjaXJjbGUgZmlsbD0iIzAwQjk4NyIgY3g9IjY0IiBjeT0iNjQiIHI9IjY0Ii8+CjxnPgo8cGF0aCBmaWxsPSIjRkZGRkZGIiBkPSJNNjAuODI1LDI3Ljk1Nmg2LjI1NXY2OC4xNDloLTYuMjU1VjI3Ljk1NnoiLz4KPC9nPgo8L3N2Zz4K","rating":5,"relative_time_description":"2 months ago","text":"Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem aperiam, eaque ipsa quae ab illo inventore veritatis et quasi architecto beatae vitae dicta sunt explicabo. Nemo enim ipsam voluptatem quia voluptas sit aspernatur aut odit aut fugit, sed quia consequuntur magni dolores eos qui ratione voluptatem sequi nesciunt.","time":1557738977}],"url":"https://goo.gl/maps/CciLp41Y9fMZgubPA","user_ratings_total":31,"vicinity":"123 Battersea Place, London"},"status":"OK"}');

new google_business_reviews_rating; 
