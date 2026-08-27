<?php

if (!defined('ABSPATH'))
{
	die();
}

add_action('init', 'google_business_reviews_rating_block_init');
add_action('enqueue_block_editor_assets', 'google_business_reviews_rating_block_editor_lists');

/* Register the reviews block and its editor script */

function google_business_reviews_rating_block_init(): void
{
	if (!function_exists('register_block_type') || version_compare(get_bloginfo('version'), '6.3', '<'))
	{
		return;
	}

	wp_register_script(
		'google-business-reviews-rating-block',
		plugins_url('block/index.js', __FILE__),
		['wp-blocks', 'wp-element', 'wp-block-editor', 'wp-components', 'wp-i18n', 'wp-server-side-render'],
		google_business_reviews_rating::VERSION,
		TRUE
	);

	if (function_exists('wp_set_script_translations'))
	{
		wp_set_script_translations('google-business-reviews-rating-block', 'g-business-reviews-rating');
	}

	wp_register_style('google-business-reviews-rating-block-editor', plugins_url('css/css.css', __FILE__), [], google_business_reviews_rating::VERSION);
	register_block_type(__DIR__ . '/block', ['render_callback' => 'google_business_reviews_rating_block_render']);
}

/* Fetched lazily so the lists are only built for the editor */

function google_business_reviews_rating_block_editor_lists(): void
{
	if (!wp_script_is('google-business-reviews-rating-block', 'registered'))
	{
		return;
	}

	$lists = ['themes' => [], 'languages' => [], 'sorts' => []];
	$plugin = (class_exists('google_business_reviews_rating_dashboard')) ? google_business_reviews_rating_dashboard::get_instance() : NULL;

	if ($plugin !== NULL)
	{
		$widget_data = $plugin->widget_data();
		$lists['themes'] = (isset($widget_data['reviews_themes']) && is_array($widget_data['reviews_themes'])) ? $widget_data['reviews_themes'] : [];
		$lists['languages'] = (isset($widget_data['languages']) && is_array($widget_data['languages'])) ? $widget_data['languages'] : [];

		if (isset($widget_data['review_sort_options']) && is_array($widget_data['review_sort_options']))
		{
			foreach ($widget_data['review_sort_options'] as $key => $a)
			{
				$lists['sorts'][$key] = (isset($a['name']) && is_string($a['name'])) ? $a['name'] : $key;
			}
		}
	}

	wp_localize_script('google-business-reviews-rating-block', 'google_business_reviews_rating_block_lists', $lists);
}

/* Render the reviews block by delegating to the shortcode */

function google_business_reviews_rating_block_render(array $attributes): string
{
	$shortcode = '[reviews_rating';
	$text_parameters = [
		'placeId' => 'place_id',
		'theme' => 'theme',
		'language' => 'language',
		'sort' => 'sort'
	];

	foreach ($text_parameters as $attribute => $parameter)
	{
		if (empty($attributes[$attribute]) || !is_string($attributes[$attribute]))
		{
			continue;
		}

		$shortcode .= ' ' . $parameter . '="' . esc_attr($attributes[$attribute]) . '"';
	}

	if (isset($attributes['limit']) && is_numeric($attributes['limit']))
	{
		$shortcode .= ' limit=' . intval($attributes['limit']);
	}

	if (isset($attributes['offset']) && is_numeric($attributes['offset']) && $attributes['offset'] > 0)
	{
		$shortcode .= ' offset=' . intval($attributes['offset']);
	}

	if (isset($attributes['ratingMin']) && is_numeric($attributes['ratingMin']) && $attributes['ratingMin'] > 1)
	{
		$shortcode .= ' min=' . intval($attributes['ratingMin']);
	}

	if (isset($attributes['ratingMax']) && is_numeric($attributes['ratingMax']) && $attributes['ratingMax'] < 5)
	{
		$shortcode .= ' max=' . intval($attributes['ratingMax']);
	}

	if (isset($attributes['excerpt']) && is_numeric($attributes['excerpt']) && $attributes['excerpt'] >= 20)
	{
		$shortcode .= ' excerpt=' . intval($attributes['excerpt']);
	}

	if (isset($attributes['more']) && is_string($attributes['more']) && $attributes['more'] != NULL)
	{
		$shortcode .= ' more="' . esc_attr($attributes['more']) . '"';
	}

	if (isset($attributes['displayDate']) && !$attributes['displayDate'])
	{
		$shortcode .= ' date=false';
	}

	$summary_parameters = [
		'displayIcon' => 'icon',
		'displayName' => 'name',
		'displayVicinity' => 'vicinity',
		'displayRating' => 'rating',
		'displayStars' => 'stars',
		'displayCount' => 'count'
	];
	$summary = [];

	foreach ($summary_parameters as $attribute => $parameter)
	{
		if (isset($attributes[$attribute]) && !$attributes[$attribute])
		{
			continue;
		}

		$summary[] = $parameter;
	}

	if (isset($attributes['summary']) && !$attributes['summary'] || empty($summary))
	{
		$shortcode .= ' summary=false';
	}
	elseif (count($summary) < count($summary_parameters))
	{
		$shortcode .= ' summary="' . esc_attr(implode(',', $summary)) . '"';
	}

	if (isset($attributes['displayAvatar']) && !$attributes['displayAvatar'])
	{
		$shortcode .= ' avatar=false';
	}

	if (isset($attributes['reviewsLink']) && $attributes['reviewsLink'])
	{
		$shortcode .= ' reviews_link=true reviews_link_class="wp-block-button__link wp-element-button"';
	}

	if (isset($attributes['writeReviewLink']) && $attributes['writeReviewLink'])
	{
		$shortcode .= ' write_review_link=true write_review_link_class="wp-block-button__link wp-element-button"';
	}

	if (isset($attributes['attribution']) && !$attributes['attribution'])
	{
		$shortcode .= ' attribution=false';
	}

	$shortcode .= ']';
	$html = do_shortcode($shortcode);

	if (!function_exists('get_block_wrapper_attributes'))
	{
		return $html;
	}

	return '<div ' . get_block_wrapper_attributes() . '>' . $html . '</div>';
}
