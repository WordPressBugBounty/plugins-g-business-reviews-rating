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
		
		add_action('wp', array($this, 'cron_scheduler'));
		add_action('google_business_reviews_rating_run', array($this, 'cron_cast'));
		
		return TRUE;
	}
	
	public function cron_scheduler()
	{
		if (!wp_next_scheduled('google_business_reviews_rating_run'))
		{
			wp_schedule_event(time(), 'hourly', 'google_business_reviews_rating_run');
		}
		
		return TRUE;
	}
	
	public function deactivate()
	{
		wp_clear_scheduled_hook('google_business_reviews_rating_run');
		
		return TRUE;
	}
	
	public function cron_cast()
	{
		require_once(plugin_dir_path(__FILE__) . 'index.php');
		
		defined('DOING_CRON') or define('DOING_CRON', (preg_match('/^cli/i', $this->sapi)));
		$google_business_reviews_rating = new google_business_reviews_rating;
		$google_business_reviews_rating->sync();
		
		return TRUE;
	}
}

new google_business_reviews_rating_cron();
