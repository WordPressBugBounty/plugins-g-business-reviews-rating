<?php

if (!defined('ABSPATH'))
{
	die();
}

class google_business_reviews_rating_sync extends google_business_reviews_rating_frontend
{
	/* Handle synchronization from CRON job */

	protected function get_last_retrieval(array $retrieval): array
	{
		if (!isset($retrieval['requests']) || !is_array($retrieval['requests']))
		{
			return [];
		}

		foreach (array_reverse($retrieval['requests']) as $a)
		{
			if (is_array($a) && isset($a['place_id']) && $a['place_id'] == $this->place_id)
			{
				return $a;
			}
		}

		return [];
	}

	public function sync(): bool
	{
		if (!defined('DOING_CRON') || defined('DOING_CRON') && !DOING_CRON)
		{
			return FALSE;
		}

		$this->place_id = $this->get_option('place_id', NULL);

		if ($this->place_id == NULL || $this->get_option('api_key') == NULL)
		{
			return FALSE;
		}

		$this->local_images = $this->get_option('local_images', FALSE);

		if ($this->local_images && !is_numeric(get_transient(self::OPTION_PREFIX . 'avatars_downloaded')))
		{
			$this->reviews = $this->get_array_option('reviews');

			if (is_array($this->reviews))
			{
				$avatars_added = [];

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
					delete_transient(self::OPTION_PREFIX . 'reviews_shuffled');
					wp_cache_delete('reviews_shuffled', self::OPTION_PREFIX);
					wp_cache_delete('reviews', self::OPTION_PREFIX);
					$this->update_option('reviews', $this->reviews, 'no');
				}
				else
				{
					set_transient(self::OPTION_PREFIX . 'avatars_downloaded', time(), MONTH_IN_SECONDS);
				}
			}
		}

		$update = $this->get_option('update', NULL);
		$modifier = ($this->get_option('api_version', NULL) == NULL && $this->get_option('retrieval_sort', 'most_relevant') == NULL) ? 0.5 : 1;
		$retrieval = $this->get_option('retrieval', []);
		$last_retrieval = (is_array($retrieval)) ? $this->get_last_retrieval($retrieval) : [];

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

	/* Set data from Google Places with cache check */

	public function set_data($force = NULL, ?string $api_key = NULL, ?string $place_id = NULL): bool
	{
		$posted_action = (isset($_POST['action']) && is_string($_POST['action'])) ? sanitize_text_field(wp_unslash($_POST['action'])) : NULL;
		$posted_type = (isset($_POST['type']) && is_string($_POST['type'])) ? sanitize_text_field(wp_unslash($_POST['type'])) : NULL;

		if (defined('XMLRPC_REQUEST') && XMLRPC_REQUEST || (!is_bool($force) || !$force) && ((defined('DOING_CRON') && DOING_CRON) || $this->dashboard && ($posted_action != NULL && preg_match('/(?:[\b_-]|^)heartbeat(?:[\b_-]|$)/i', $posted_action) || $posted_type != NULL && preg_match('/(?:[\b_-]|^)cache(?:[\b_-]|$)/i', $posted_type) || isset($_POST['log']) && $_POST['log'] != NULL)))
		{
			return FALSE;
		}

		if (!is_bool($force) || !$force)
		{
			$force_check = get_transient(self::OPTION_PREFIX . 'force');

			if (is_string($force_check) && preg_match('#^(\d+(?:\.\d+)?)/0$#', $force_check, $m))
			{
				$force = ((time() - intval($m[1])) < 10);
				delete_transient(self::OPTION_PREFIX . 'force');
			}
		}

		$this->api_key = ($api_key != NULL) ? $api_key : $this->get_option('api_key', NULL);
		$this->place_id = ($place_id != NULL) ? $place_id : $this->get_option('place_id', NULL);

		if (!$force)
		{
			$last_retrieval = $this->get_last_retrieval($this->get_array_option('retrieval'));
			$collected = (isset($last_retrieval['status']) && (is_numeric($last_retrieval['status']) && $last_retrieval['status'] >= 200 && $last_retrieval['status'] < 300 || is_string($last_retrieval['status']) && preg_match('/^OK$/i', $last_retrieval['status'])));

			if ($this->dashboard && $this->request_count == 0 && !$collected)
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
				$cached = wp_cache_get(($this->demo) ? 'result_demo' : 'result_valid', self::OPTION_PREFIX);

				if (!$this->demo && !is_array($cached))
				{
					$cached = wp_cache_get('result', self::OPTION_PREFIX);
				}

				if (is_array($cached))
				{
					$this->data = $cached;
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

			$this->data = ($this->retrieved_data_valid) ? $this->get_array_option('result') : $this->get_array_option('result_valid');

			if ((!is_array($this->data) || is_array($this->data) && empty($this->data)) && $this->request_count == 0 && !$collected)
			{
				$this->request_count++;
				$this->data = $this->retrieve_data();

				if (!is_array($this->data) || is_array($this->data) && empty($this->data))
				{
					return FALSE;
				}

				$this->update_option('result', $this->data, 'no');
				wp_cache_add('result', $this->data, self::OPTION_PREFIX, HOUR_IN_SECONDS);
				$this->set_reviews();
				return TRUE;
			}

			$this->set_reviews();
			return TRUE;
		}

		wp_cache_delete('structured_data', self::OPTION_PREFIX);
		wp_cache_delete((($this->demo) ? 'result_demo' : 'result'), self::OPTION_PREFIX);

		if ($this->request_count > 2)
		{
			return FALSE;
		}

		$this->data = $this->retrieve_data('array', TRUE);

		if ($this->demo)
		{
			wp_cache_add('result_demo', $this->data, self::OPTION_PREFIX, HOUR_IN_SECONDS);

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

	/* Collect data from Google Places as JSON string */

	public function retrieve_data(string $format = 'array', bool $force = FALSE)
	{
		$ret = '';

		if ($this->demo)
		{
			$this->api_version = 1;
			$decoded = json_decode(GOOGLE_BUSINESS_REVIEWS_RATING_DEMO_RESULT, TRUE);
			$this->result = (is_array($decoded)) ? $decoded : [];

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
					$ret = json_encode([
						'success' => FALSE,
						'error' => __('Place ID and Google API Key are required.', 'g-business-reviews-rating')
					]);
				}
				elseif ($this->place_id == NULL)
				{
					$ret = json_encode([
						'success' => FALSE,
						'error' => __('Error: Place ID is required.', 'g-business-reviews-rating')
					]);
				}
				elseif ($this->api_key == NULL)
				{
					$ret = json_encode([
						'success' => FALSE,
						'error' => __('Error: Google API Key is required.', 'g-business-reviews-rating')
					]);
				}

				if ($ret != '')
				{
					return $ret;
				}

				return '';
			case 'array':
			default:
				return [];
			}
		}

		$data_array = [];
		$data_string = '';
		$recheck = FALSE;
		$retrieval = NULL;
		$last_retrieval = NULL;

		if ($this->request_count > 2)
		{
			$data_array = ($this->dashboard) ? $this->get_option('result', NULL) : $this->get_option('result_valid', NULL);

			if (!is_array($data_array))
			{
				$data_array = [];
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
					$ret = json_encode([
						'success' => FALSE,
						'error' => __('Request count exceeded', 'g-business-reviews-rating')
					]);
					return $ret;
				}

				$data_string = json_encode($data_array, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

				return $data_string;
			case 'array':
			default:
				return $this->result;
			}
		}

		$this->api_version = $this->get_option('api_version', NULL);
		$fields = $this->get_option('retrieval_fields', NULL);
		$language = $this->get_option('language', NULL);
		$translate = (is_bool($this->get_option('retrieval_translate', NULL)) && $this->get_option('retrieval_translate') || is_string($this->get_option('retrieval_translate')) && preg_match('/^(?:1|true)$/i', $this->get_option('retrieval_translate')) || is_numeric($this->get_option('retrieval_translate')) && intval($this->get_option('retrieval_translate')) >= 1);

		if (!is_array($fields) || is_array($fields) && $this->api_version != NULL && in_array('name', $fields))
		{
			switch ($this->api_version)
			{
			case 1:
				$fields = ['displayName', 'formattedAddress', 'googleMapsUri', 'iconMaskBaseUri', 'id', 'shortFormattedAddress', 'rating', 'reviews', 'userRatingCount'];
				break;
			default:
				$fields = ['formatted_address', 'icon', 'id', 'name', 'rating', 'reviews', 'url', 'user_ratings_total', 'vicinity'];
				break;
			}

			$this->update_option('retrieval_fields', $fields, 'no');
		}

		if ($force)
		{
			$retrieval = $this->get_option('retrieval');

			if (is_array($retrieval) && isset($retrieval['requests']) && is_array($retrieval['requests']) && count($retrieval) > 1)
			{
				$last_retrieval = $this->get_last_retrieval($retrieval);
				$force = (empty($last_retrieval) || !isset($last_retrieval['time']) || (time() - $last_retrieval['time']) > 10);
			}
		}

		if (!$force && (!is_array($this->result) || is_array($this->result) && empty($this->result)))
		{
			$this->result = ($this->dashboard) ? $this->get_array_option('result') : $this->get_array_option('result_valid');
		}

		if (!$force && is_array($this->result) && !empty($this->result))
		{
			$data_string = json_encode($this->result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
			$data_array = $this->result;
		}

		if ($this->dashboard && !$force && !is_array($retrieval) && (!is_array($this->result) || is_array($this->result) && (empty($this->result) || !empty($this->result) && (!isset($this->result['status']) || $this->settings_updated && isset($this->result['status']) && !preg_match('/^OK$/i', $this->result['status'])))))
		{
			$retrieval = $this->get_option('retrieval');

			if ($this->settings_updated && (!is_array($retrieval) || !isset($retrieval['requests']) || isset($retrieval['requests']) && count($retrieval['requests']) < 5))
			{
				$recheck = TRUE;
			}
			elseif (is_array($retrieval) && isset($retrieval['requests']) && is_array($retrieval['requests']))
			{
				$last_retrieval = $this->get_last_retrieval($retrieval);
				$recheck = (empty($last_retrieval) || !isset($last_retrieval['time']) || !isset($last_retrieval['status']) || !(is_numeric($last_retrieval['status']) && $last_retrieval['status'] >= 200 && $last_retrieval['status'] < 300 || is_string($last_retrieval['status']) && preg_match('/^OK$/i', $last_retrieval['status'])));
			}
		}

		if ($recheck)
		{
			$this->request_count++;

			if (!$force && $format == 'array')
			{
				$this->set_data(TRUE);
				return (is_array($this->result)) ? $this->result : [];
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
						/* translators: %s: WordPress function name such as wp_remote_get() */
						. sprintf(esc_html__('Error: Required remote collection function not available: %s', 'g-business-reviews-rating'), '<em>wp_remote_get()</em>')
						. '</p>';
					break;
				case 'json':
					$ret = json_encode([
						'success' => FALSE,
						/* translators: %s: WordPress function name such as wp_remote_get() */
						'error' => sprintf(esc_html__('Error: Required remote collection function not available: %s', 'g-business-reviews-rating'), 'wp_remote_get()')
					]);
					break;
				case 'array':
				default:
					$ret = [];
					break;
				}

				return $ret;
			}

			$sort = ($this->api_version != NULL && intval($this->api_version) >= 1) ? NULL : $this->get_retrieval_sort(TRUE);

			switch ($this->api_version)
			{
			case 1:

				/* Places (New): languageCode is a parameter, no review sort */

				$url = 'https://places.googleapis.com/v' . strval($this->api_version) . '/places/' . rawurlencode($this->place_id)
					. (($language != NULL) ? '?languageCode=' . rawurlencode($language) : '');
				$arguments = [
					'headers' => [
						'X-Goog-Api-Key' => $this->api_key,
						'X-Goog-FieldMask' => implode(',', $fields)
					]
				];

				$response = (version_compare(PHP_VERSION, '8.1') >= 0) ? @wp_remote_get($url, $arguments) : wp_remote_get($url, $arguments);
				break;
			default:
				$url = 'https://maps.googleapis.com/maps/api/place/details/json'
					. '?placeid=' . rawurlencode($this->place_id)
					. '&key=' . rawurlencode($this->api_key)
					. '&fields=' . rawurlencode(implode(',', $fields))
					. '&reviews_sort=' . rawurlencode($sort)
					. '&reviews_no_translations=' . rawurlencode((!$translate) ? 'true' : 'false')
					. (($language != NULL) ? '&language=' . rawurlencode($language) : '');
				$response = (version_compare(PHP_VERSION, '8.1') >= 0) ? @wp_remote_get($url) : wp_remote_get($url);
				break;
			}

			$response_code = wp_remote_retrieve_response_code($response);
			$data_string = wp_remote_retrieve_body($response);
			$decoded = ($data_string != NULL) ? json_decode($data_string, TRUE) : NULL;
			$data_array = (is_array($decoded)) ? $this->sanitize_array($decoded) : NULL;

			if (!is_array($data_array))
			{
				switch ($format)
				{
				case 'boolean':
					return FALSE;
				case 'html':
					$ret = '<p class="error">'
						/* translators: %s: the API URL that was called */
						. sprintf(esc_html__('Error: Unable to collect remote data from URL: %s', 'g-business-reviews-rating'), '<em>' . esc_html($url) . '</em>')
						. '</p>';
					break;
				case 'json':
					$ret = json_encode([
						'success' => FALSE,
						/* translators: %s: the API URL that was called */
						'error' => sprintf(esc_html__('Error: Unable to collect remote data from URL: %s', 'g-business-reviews-rating'), $url)
					]);
					break;
				case 'array':
				default:
					$ret = [];
					break;
				}

				return $ret;
			}

			$this->result = $data_array;
			$this->places = $this->get_array_option('places');

			if (is_null($retrieval))
			{
				$retrieval = $this->get_option('retrieval');
			}

			if (!is_array($retrieval))
			{
				$retrieval = [
					'count' => 0,
					'initial' => time(),
					'requests' => []
				];
			}
			elseif (!is_array($retrieval['requests']))
			{
				$retrieval['requests'] = [];
			}
			elseif (count($retrieval['requests']) > 200)
			{
				$retrieval['requests'] = array_slice($retrieval['requests'], -200);
			}

			switch ($this->api_version)
			{
			case 1:
				$retrieval['requests'][] = [
					'time' => time(),
					'place_id' => $this->place_id,
					'status' => (is_numeric($response_code)) ? intval($response_code) : ((isset($this->result['status'])) ? $this->result['status'] : NULL),
					'name' => (isset($this->result['displayName']) && isset($this->result['displayName']['text'])) ? $this->result['displayName']['text'] : NULL,
					'icon' => (isset($this->result['iconMaskBaseUri']) && $this->result['iconMaskBaseUri'] != NULL) ? $this->result['iconMaskBaseUri'] . '.svg' : NULL,
					'vicinity' => (isset($this->result['shortFormattedAddress'])) ? $this->result['shortFormattedAddress'] : NULL,
					'rating' => (isset($this->result['rating'])) ? $this->result['rating'] : NULL,
					'review_ids' => (isset($this->result['reviews']) && is_array($this->result['reviews'])) ? $this->get_review_ids($this->result['reviews']) : NULL,
					'rating_count' => (isset($this->result['userRatingCount'])) ? $this->result['userRatingCount'] : NULL,
					'review_count' => (isset($this->result['reviews']) && is_array($this->result['reviews'])) ? count($this->result['reviews']) : NULL,
					'review_sort' => $sort,
					'dashboard' => ($this->dashboard && (!defined('DOING_CRON') || defined('DOING_CRON') && !DOING_CRON)),
					'sync' => (defined('DOING_CRON') && DOING_CRON),
					'count' => $this->request_count
				];
				break;
			default:
				$retrieval['requests'][] = [
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
				];
				break;
			}
			$retrieval['count'] = intval($retrieval['count']) + 1;
			$retrieval = $this->sanitize_array($retrieval);
			$this->request_count++;

			$this->update_option('retrieval', $retrieval, 'no');
			$this->update_option('result', $this->result, 'no');
			wp_cache_add('result', $this->result, self::OPTION_PREFIX, HOUR_IN_SECONDS);

			if ($this->api_version != NULL && intval($this->api_version) >= 1 && isset($this->result['reviews']) && is_array($this->result['reviews']) && !empty($this->result['reviews']) || $this->api_version == NULL && isset($this->result['result']['reviews']) && is_array($this->result['result']['reviews']) && !empty($this->result['result']['reviews']))
			{
				$this->result_valid = $this->result;
			}

			$this->retrieved_data_valid = (is_array($this->result_valid) && !empty($this->result_valid));

			if ($this->retrieved_data_valid)
			{
				$this->update_option('result_valid', $this->result_valid, 'no');
				wp_cache_add('result_valid', $this->result_valid, self::OPTION_PREFIX, HOUR_IN_SECONDS);
				self::set_api_history();
			}

			$place_set_key = FALSE;

			if (!is_array($this->places))
			{
				$this->places = [];
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

			if (in_array('name', $fields) && array_key_exists($place_set_key, $this->places) && ($this->api_version != NULL && intval($this->api_version) >= 1 && (!isset($this->result['displayName']) || isset($this->result['displayName']) && ($this->result['displayName'] == NULL || !isset($this->result['displayName']['text']) || isset($this->result['displayName']['text']) && $this->result['displayName']['text'] == NULL)) || $this->api_version == NULL && (!isset($this->result['result']['name']) || isset($this->result['result']['name']) && $this->result['result']['name'] == NULL)))
			{
				$this->places[$place_set_key]['time'] = time();
				$this->places[$place_set_key]['default'] = TRUE;
				$this->places[$place_set_key]['status'] = FALSE;
			}
			else
			{
				if (count($fields) == 9)
				{
					switch ($this->api_version)
					{
					case 1:
						$this->places[$place_set_key] = [
							'id' => (isset($this->result['id'])) ? $this->result['id'] : NULL,
							'place_id' => $this->place_id,
							'time' => time(),
							'name' => (isset($this->result['displayName']) && isset($this->result['displayName']['text'])) ? $this->result['displayName']['text'] : NULL,
							'icon' => (isset($this->result['iconMaskBaseUri']) && $this->result['iconMaskBaseUri'] != NULL) ? $this->result['iconMaskBaseUri'] . '.svg' : NULL,
							'vicinity' => (isset($this->result['shortFormattedAddress'])) ? $this->result['shortFormattedAddress'] : NULL,
							'formatted_address' => (isset($this->result['formattedAddress'])) ? $this->result['formattedAddress'] : NULL,
							'rating' => (isset($this->result['rating'])) ? $this->result['rating'] : NULL,
							'rating_count' => (isset($this->result['userRatingCount'])) ? $this->result['userRatingCount'] : NULL,
							'default' => TRUE,
							'status' => (isset($this->result['displayName']) && isset($this->result['displayName']['text']) && $this->result['displayName']['text'] != NULL)
						];
						break;
					default:
						$this->places[$place_set_key] = [
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
						];
						break;
					}
				}
				else
				{
					$this->places[$place_set_key]['place_id'] = $this->place_id;
					$this->places[$place_set_key]['time'] = time();
					$this->places[$place_set_key]['default'] = TRUE;

					foreach ($fields as $k)
					{
						switch ($this->api_version)
						{
						case 1:
							switch($k)
							{
							case 'id':
							case 'rating':
								if (!array_key_exists($k, $this->result))
								{
									break;
								}

								$this->places[$place_set_key][$k] = $this->result[$k];
								break;
							case 'formatted_address':
							case 'formattedAddress':
								if (!array_key_exists('formattedAddress', $this->result))
								{
									break;
								}

								$this->places[$place_set_key]['formatted_address'] = $this->result['formattedAddress'];
								break;
							case 'icon':
							case 'iconMaskBaseUri':
								if (!array_key_exists('iconMaskBaseUri', $this->result))
								{
									break;
								}

								$this->places[$place_set_key]['icon'] = ($this->result['iconMaskBaseUri'] != NULL) ? $this->result['iconMaskBaseUri'] . '.svg' : NULL;
								break;
							case 'vicinity':
							case 'shortFormattedAddress':
								if (!array_key_exists('shortFormattedAddress', $this->result))
								{
									break;
								}

								$this->places[$place_set_key]['vicinity'] = $this->result['shortFormattedAddress'];
								break;
							case 'user_ratings_total':
							case 'userRatingCount':
								if (!array_key_exists('userRatingCount', $this->result))
								{
									break;
								}

								$this->places[$place_set_key]['rating_count'] = $this->result['userRatingCount'];
								break;
							case 'name':
							case 'displayName':
								if (!array_key_exists('displayName', $this->result) || !isset($this->result['displayName']['text']))
								{
									break;
								}

								$this->places[$place_set_key]['name'] = $this->result['displayName']['text'];
								$this->places[$place_set_key]['status'] = ($this->result['displayName']['text'] != NULL);
								break;
							}
							break;
						default:
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
							break;
						}
					}
				}
			}

			sort($this->places);
			$this->places = $this->sanitize_array($this->places);
			$this->update_option('places', $this->places, 'yes');
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
				$ret = json_encode([
					'success' => FALSE,
					'error' => __('Empty result', 'g-business-reviews-rating')
				]);
				return $ret;
			}

			return $data_string;
		case 'array':
		default:
			return $data_array;
		}

		return $ret;
	}

}
