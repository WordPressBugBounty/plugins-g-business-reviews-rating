<?php

if (!defined('ABSPATH'))
{
	die();
}

class google_business_reviews_rating_cron
{
	private $sapi = NULL;

	public function __construct()
	{
		$this->sapi = php_sapi_name();

		add_action('wp', [__CLASS__, 'cron_scheduler']);
		add_action('google_business_reviews_rating_run', [$this, 'cron_cast']);
		add_action('google_business_reviews_rating_shortcode_scan', [$this, 'shortcode_scan_cast']);
		add_filter('cron_schedules', [$this, 'cron_schedules']);

		return TRUE;
	}

	/* Register a weekly recurrence so the shortcode scan can be a light, infrequent pass */

	public function cron_schedules(array $schedules): array
	{
		if (!isset($schedules['weekly']))
		{
			$schedules['weekly'] = [
				'interval' => WEEK_IN_SECONDS,
				'display' => __('Weekly', 'g-business-reviews-rating')
			];
		}

		return $schedules;
	}

	/* Re-schedules a lost event rather than failing silently */

	public static function cron_scheduler(): bool
	{
		if (!wp_next_scheduled('google_business_reviews_rating_run'))
		{
			wp_schedule_event(time(), 'hourly', 'google_business_reviews_rating_run');
		}

		if (!wp_next_scheduled('google_business_reviews_rating_shortcode_scan'))
		{
			wp_schedule_event(time() + HOUR_IN_SECONDS, 'weekly', 'google_business_reviews_rating_shortcode_scan');
		}

		return TRUE;
	}

	public static function deactivate(): bool
	{
		wp_clear_scheduled_hook('google_business_reviews_rating_run');
		wp_clear_scheduled_hook('google_business_reviews_rating_shortcode_scan');

		return TRUE;
	}

	public function cron_cast(): bool
	{
		require_once(plugin_dir_path(__FILE__) . 'index.php');

		defined('DOING_CRON') or define('DOING_CRON', (preg_match('/^cli/i', $this->sapi)));
		$sync = new google_business_reviews_rating_sync;
		$sync->sync();

		return TRUE;
	}

	/* Count published posts containing a plugin shortcode; updates notifications.shortcode for the review notification's confidence signal */

	public function shortcode_scan_cast(): bool
	{
		global $wpdb;

		defined('DOING_CRON') or define('DOING_CRON', (preg_match('/^cli/i', $this->sapi)));

		$count = $wpdb->get_var($wpdb->prepare(
			"SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_status = 'publish' AND post_type NOT IN ('revision', 'auto-draft', 'attachment', 'nav_menu_item') AND (post_content LIKE %s OR post_content LIKE %s)",
			'%' . $wpdb->esc_like('[reviews_rating') . '%',
			'%' . $wpdb->esc_like('[reviews_rating_link') . '%'
		));

		$notifications = get_option('google_business_reviews_rating_notifications', []);

		if (!is_array($notifications))
		{
			$notifications = [];
		}

		if (!isset($notifications['shortcode']) || !is_array($notifications['shortcode']))
		{
			$notifications['shortcode'] = ['count' => 0, 'scanned' => NULL];
		}

		$notifications['shortcode']['count'] = (is_numeric($count)) ? intval($count) : 0;
		$notifications['shortcode']['scanned'] = time();

		update_option('google_business_reviews_rating_notifications', $notifications, 'no');

		return TRUE;
	}
}

new google_business_reviews_rating_cron();
