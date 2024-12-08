// JavaScript Document

function google_business_reviews_rating(e, i) {
	if (typeof e == 'undefined') {
		var e = null;
	}
	else if (typeof e == 'string' && e.match(/^[\d]+$/)) {
		e = parseInt(e);
	}
		
	if (typeof i == 'undefined') {
		var i = null;
	}
	else if (typeof i == 'string') {
		i = parseInt(i.replace(/[^\d]/, ''));
	}
	
	if ((typeof e == 'number' || typeof e == 'object' || typeof e == 'string') && typeof i == 'number') {
		if (typeof e == 'object') {
			e = jQuery('.google-business-reviews-rating').index(e);
		}
		else if (typeof e == 'string') {
			e = jQuery('.google-business-reviews-rating').index(jQuery('#' + e));
		}
		
		jQuery('.review-full-text:eq(0)', '.google-business-reviews-rating:eq(' + e + ') li:eq(' + i + ')').show();
		jQuery('.review-more-link:eq(0)', '.google-business-reviews-rating:eq(' + e + ') li:eq(' + i + ')').remove();
		return;
	}

	const observer = new IntersectionObserver(stars => {
			stars.forEach(item => {
				if (item.isIntersecting) {
					item.target.classList.add('animation-start');
					return;
				}

				item.target.classList.remove('animation-pause');
				return;
			});
		}, { threshold: 0.5 }),
		safari = (navigator.userAgent.match(/^((?!chrome|android).)*safari/i) != null);
	
	var stars_width_multiplier = null,
		rating = null,
		rating_width = null,
		clear_styles = (jQuery('#stylesheet-none').length && jQuery('#stylesheet-none').is(':checked')),
		star_html = false,
		star_css = false,
		star_inline = false,
		star_image = null,
		overall_link = null,
		reviews_window = null,
		view = null;

	jQuery('.google-business-reviews-rating').each(function(index) {
		var e = jQuery(this),
			view = (jQuery(this).hasClass('carousel') && typeof jQuery(this).data('view') == 'number' && jQuery(this).data('view') >= 1 && jQuery(this ).data('view') <= 50) ? jQuery(this).data('view') : null,
			star_html = (typeof jQuery(this).attr('class') == 'string' && (jQuery(this).hasClass('stars-html') || jQuery(this).attr('class').match(/\bversion[_-]?1\b/i))),
			star_css = (!star_html && typeof jQuery(this).attr('class') == 'string' && (jQuery(this).hasClass('stars-css') || jQuery(this).hasClass('stars-gray-css'))),
			star_inline = (!star_html && !star_css && typeof jQuery(e).data('stars') == 'string' && jQuery(e).data('stars').match(/^inline|inline$/i) != null),
			star_color = (!star_html && !star_css && !star_inline && (typeof jQuery(e).data('stars') == 'string' || typeof jQuery(e).data('stars-gray') == 'string')),
			stars_width_multiplier = 0.196,
			rating = (jQuery('.number', this).length) ? parseFloat(jQuery('.number:eq(0)', this).text().replace(/,/g, '.').replace(/(\d+(?:\.\d+)?)/, '$1')) : null,
			overall_link = (typeof jQuery(this).data('href') == 'string' && jQuery(this).data('href').length && !jQuery('.buttons', this).length && (!jQuery('.listing', this).length || jQuery('.listing', this).length && !jQuery('.listing > *', this).length)) ? jQuery(this).data('href') : null;
		
		if (clear_styles) {
			jQuery(this).removeAttr('class');
		}
		else if ((typeof jQuery(this).attr('id') != 'string' || !jQuery(this).attr('id').length)) {
			jQuery(this).attr('id', 'google-business-reviews-rating' + ((index > 1) ? '-' + index : ''));
		}
		
		if (!clear_styles && jQuery(this).hasClass('no-styles')) {
			jQuery(this).removeAttr('class');
		}
		
		if (jQuery(this).hasClass('link')) {
			if (overall_link != null) {
				jQuery(this).on('click', { overall_link: overall_link }, function(event) {
					if (!jQuery(event.target).is('a')) {
						event.preventDefault();
						event.stopPropagation();
						if (event.data.overall_link.match(/^\/.*$/)) {
							document.location.href = event.data.overall_link;
						}
						else {
							reviews_window = window.open(event.data.overall_link, '_blank');
							reviews_window.focus();
						}
						return false;
					}
				});
			}
			else {
				jQuery(this).removeClass('link');
			}
			
			jQuery(this).removeData('href').removeAttr('data-href');
		}

		if (this.getAttribute('data-animate') == 'immediate') {
			this.querySelector(':scope .all-stars.animate').classList.add('animation-start');
		}
		else if (this.querySelector(':scope .all-stars.animate') != null) {
			observer.observe(this.querySelector(':scope .all-stars.animate'));
		}

		if (!star_html && jQuery('.star', jQuery('.all-stars', e)).length) {
			if (star_css) {
				if (!jQuery('.rating-stars', e).length) {
					jQuery('.all-stars', e).append('<span class="rating-stars star temporary" style="display: none;">.</span>');
				}
				
				if (!jQuery('.star.gray', e).css('color')) {
					jQuery('.all-stars', e).append('<span class="star gray temporary" style="display: none;">.</span>');
				}
				
				if (typeof jQuery('.star.gray', e).css('color') == 'string' && !jQuery('.rating-stars', e).css('color').match(/^(?:#(?:F7B\d0\d|E7711B)|rgba?\s*\(23[12],\s*11[34],\s*2[78](?:,\s*1(?:\.0+)?)?\))$/i)) {
					jQuery(e).data('stars', jQuery('.rating-stars', e).css('color'));
					star_color = true;
				}
				
				if (typeof jQuery('.star.gray', e).css('color') == 'string' && (!jQuery(e).hasClass('dark') && !jQuery('.star.gray', e).css('color').match(/^(?:#(?:A4A4A4|C1C1C1|C9C9C9)|rgba?\s*\(193,\s*193,\s*193(?:,\s*1(?:\.0+)?)?\))$/i) || jQuery(e).hasClass('dark') && !jQuery('.star.gray', e).css('color').match(/^(?:#B4B4B4|rgba?\s*\(180,\s*180,\s*180(?:,\s*0?\.8)?\))$/i))) {
					jQuery(e).data('stars-gray', jQuery('.star.gray', e).css('color'));
					star_color = true;
				}
				
				if (jQuery('.temporary', jQuery('.all-stars', e)).length) {
					jQuery('.temporary', jQuery('.all-stars', e)).remove();
				}
			}
			
			if (star_color && (typeof jQuery(e).data('stars') == 'string' && jQuery(e).data('stars').length && !jQuery(e).data('stars').match(/^#(?:F7B\d0\d|E7711B)$/i) || typeof jQuery(e).data('stars-gray') == 'string' && jQuery(e).data('stars-gray').length && !jQuery(e).data('stars-gray').match(/^#(?:A4A4A4|C1C1C1|C9C9C9)$/i))) {
				if (star_css && (typeof jQuery(e).data('stars-gray') != 'string' || typeof jQuery(e).data('stars-gray') == 'string' && jQuery(e).data('stars-gray') == 'css') && !jQuery('.star.gray', jQuery('.all-stars', e)).length) {
					jQuery('.all-stars', e).append('<span class="temporary" style="display: none;">.</span>');
				}
				
				jQuery('.star', jQuery('.all-stars', e)).each(function() {
					try {
						star_image = atob(jQuery(this).css('background-image').replace(/^url\(["']data:image\/svg\+xml;charset=UTF-8;base64,(.+)["']\)$/, '$1'));
						
						if (typeof jQuery(e).data('stars') == 'string' && jQuery(e).data('stars').length && !jQuery(e).data('stars').match(/^#(?:F7B\d0\d|E7711B)$/i)) {
							star_image = star_image.replace(/#(?:F7B\d0\d|E7711B)/g, jQuery(e).data('stars'));
						}
	
						if (typeof jQuery(e).data('stars-gray') == 'string' && jQuery(e).data('stars-gray').length && !jQuery(e).data('stars-gray').match(/^#(?:A4A4A4|C1C1C1|C9C9C9)$/i)) {
							star_image = star_image.replace(/#(?:A4A4A4|C1C1C1|C9C9C9)/g, jQuery(e).data('stars-gray'));
						}
	
						jQuery(this).css('background-image', 'url(\'data:image\/svg+xml;charset=UTF-8;base64,' + btoa(star_image) + '\')');
					}
					catch (err) {
						return;
					}
				});
			}
		}
		
		if (jQuery('.review-more-placeholder', e).length) {
			jQuery('.review-more-placeholder', e).each(function(more) {
				if (jQuery(this).siblings('.review-full-text').length && !jQuery(this).siblings('.review-full-text').html().length) {
					jQuery(this).parent().removeClass('text-excerpt');
					jQuery(this).siblings('.review-full-text').remove();
					jQuery(this).remove();
				}
				else if (jQuery(e).hasClass('js-links')) {
					jQuery(this).after('<a href="javascript:google_business_reviews_rating(' + index + ', ' + jQuery('li', jQuery(e)).index(jQuery(this).closest('li')) + ');" class="review-more-link">' + jQuery(this).html() + '</a>');
					jQuery(this).remove();
				}
				else {
					jQuery(this).after('<a href="#' + jQuery(e).attr('id') + '" class="review-more-link">' + jQuery(this).html() + '</a>');
					jQuery('.review-more-link', jQuery(this).parent()).on('click', function(event) {
						event.preventDefault();
						event.stopPropagation();
						jQuery(this).next('.review-full-text').show();
						
						if (view == null) {
							jQuery(this).remove();
							return false;
						}
	
						jQuery(this).hide();
						google_business_reviews_rating_carousel(this, null);
						return false;						
					});
					
					jQuery(this).remove();
				}
			});
		}
		
		if (jQuery('.fixed-height', e).length && jQuery(e).hasClass('bubble')) {
			jQuery('.text', e).each(function() {
				if (jQuery(this).prev().length && (jQuery(this).prev().hasClass('author-avatar') || jQuery(this).prev().hasClass('review-meta') && (jQuery('.author-name', jQuery(this).prev()).length))) {
					jQuery('.text', e).before('<span class="arrow arrow-up"></span>');
					return;
				}
				
				if (jQuery(this).next().length && (jQuery(this).next().hasClass('author-avatar') || jQuery(this).next().hasClass('review-meta') && (jQuery('.author-name', jQuery(this).next()).length))) {
					jQuery('.text', e).after('<span class="arrow arrow-down"></span>');
					return;
				}
            });
		}
		
		if (!star_html && !star_inline && jQuery('.all-stars', e).length && jQuery('.all-stars', e).hasClass('animate') && typeof rating == 'number' && rating > 1.5 && jQuery('.number:eq(0)', e).length) {
			jQuery('.all-stars', e)
				.after(jQuery('<span>')
					.addClass('all-stars')
					.addClass('backdrop')
					.css({
						width: Math.ceil(jQuery('.all-stars', e).width() + 0.1) + 'px',
						margin: (jQuery('body').hasClass('rtl')) ? '0 ' + (-1 * jQuery('.all-stars', e).width() - 0.1) + 'px 0 0' :'0 0 0 ' + (-1 * jQuery('.all-stars', e).width() - 0.1) + 'px' 
						})
					.html('<span class="star gray"></span><span class="star gray"></span><span class="star gray"></span><span class="star gray"></span><span class="star gray"></span>'));
					
			if (jQuery('.all-stars:eq(0)', e).position().top - jQuery('.all-stars.backdrop', e).position().top != 0) {
				jQuery('.all-stars.backdrop', e).css('margin-top', (jQuery('.all-stars:eq(0)', e).position().top - jQuery('.all-stars.backdrop', e).position().top) + 'px');
			}

			if (typeof jQuery(e).data('stars-gray') == 'string' && jQuery(e).data('stars-gray').length && !jQuery(e).data('stars-gray').match(/^#(?:A4A4A4|C1C1C1|C9C9C9)$/i)) {
				jQuery('.star', jQuery('.all-stars.backdrop', e)).each(function() {
					try {
						star_image = atob(jQuery(this).css('background-image').replace(/^url\(["']data:image\/svg\+xml;charset=UTF-8;base64,(.+)["']\)$/, '$1'));
						star_image = star_image.replace(/#(?:A4A4A4|C1C1C1|C9C9C9)/g, jQuery(e).data('stars-gray'));
						jQuery(this).css('background-image', 'url(\'data:image\/svg+xml;charset=UTF-8;base64,' + btoa(star_image) + '\')');
					}
					catch (err) {
						return;
					}
				});
			}

			jQuery('.star:last', jQuery('.all-stars:eq(0)', e)).on('webkitAnimationEnd oanimationend msAnimationEnd animationend', function(event) {
				if (jQuery('.all-stars.backdrop', e).length) {
					jQuery('.all-stars.backdrop', jQuery(this).closest('.rating')).fadeOut(300, function() { jQuery(this).remove(); });
				}
			});
			
			setTimeout(
				function(e) {
					if (jQuery('.all-stars.backdrop', e).length) {
						jQuery('.all-stars.backdrop', e).fadeOut(300, function() { jQuery(this).remove(); });
					}
				}, 4800, jQuery('.all-stars.backdrop', e).length);
		}
		else if (star_html && typeof rating == 'number') {
			if (safari) {
				jQuery('.all-stars', e).addClass('safari');
			}
				
			if (rating == 5) {
				setTimeout(
					function() {
							jQuery('.all-stars', e).css('color', 'rgba(0, 0, 0, 0)');
					}, 2400);
			}
			else if (rating == 0) {
				jQuery('.rating-stars', e).remove();
			}
			
			if (jQuery('.rating-stars', e) && jQuery('.all-stars', e).length) {
				if (typeof jQuery('.rating-stars', e).data('multiplier') == 'number') {
					stars_width_multiplier = jQuery('.rating-stars', e).data('multiplier');
				}
				
				rating_width = Math.round(jQuery('.all-stars', e).width() * rating * stars_width_multiplier + stars_width_multiplier * 0.05 * Math.sin(rating * 2 * Math.PI) + 0.5 * stars_width_multiplier * (Math.round(rating + 0.49) - rating));
				jQuery('.rating-stars', e).width(rating_width).css({ margin: (jQuery('body').hasClass('rtl')) ? '0 0 0 ' + (-1 * rating_width) + 'px' : '0 ' + (-1 * rating_width) + 'px 0 0' });
			}
		}
		
		if (view == null || view > jQuery('.listing', e).children().length) {
			return;
		}
		
		google_business_reviews_rating_carousel(e);
	});
	return;
}

function google_business_reviews_rating_carousel(e, i, auto) {
	if (typeof e != 'object') {
		return;
	}
	
	if (typeof i != 'number') {
		var i = (!jQuery(e).hasClass('google-business-reviews-rating') && !jQuery(this).hasClass('review-more-link') && jQuery(e).closest('.bullet').length) ? jQuery(e).closest('.bullet').index() : null;
	}
	
	if (typeof auto != 'boolean') {
		var auto = false;
	}
	
	e = (jQuery(e).hasClass('google-business-reviews-rating')) ? e : jQuery(e).closest('.google-business-reviews-rating');
	
	var view = (jQuery(e).hasClass('carousel') && typeof jQuery(e).data('view') == 'number' && jQuery(e).data('view') >= 1 && jQuery(e).data('view') <= 50) ? jQuery(e).data('view') : null,
		slide = (view != null && typeof jQuery(e).data('slide') == 'number' && jQuery(e).data('slide') >= 2) ? jQuery(e).data('slide') : 1,
		iterations = (view != null && typeof jQuery(e).data('loop') != 'number' && typeof jQuery(e).data('iterations') == 'number') ? jQuery(e).data('iterations') : null,
		loop = (view != null && typeof jQuery(e).data('loop') == 'number' && typeof jQuery(e).data('loop') || typeof jQuery(e).data('loop') == 'boolean') ? ((typeof jQuery(e).data('loop') == 'number' && jQuery(e).data('loop') < 1) ? true : jQuery(e).data('loop')) : ((iterations != null) ? Math.round(iterations * view) : false),
		loop_counter = (view != null && loop && typeof jQuery(e).data('counter') == 'number') ? jQuery(e).data('counter') : null,
		interval = (view != null && loop && typeof jQuery(e).data('interval') == 'number') ? jQuery(e).data('interval') : null,
		interval_id = (view != null && loop && typeof jQuery(e).data('interval-id') == 'number') ? jQuery(e).data('interval-id') : null,
		new_slide = (i != null) ? i + 1 : ((auto) ? slide + 1 : null),
		transition = (view != null && typeof jQuery(e).data('transition') == 'string') ? jQuery(e).data('transition') : null,
		transition_duration = (view != null && transition == 'string' && typeof jQuery(e).data('transition-duration') == 'number') ? jQuery(e).data('transition-duration') : null,
		bounds = null,
		list_area = [null, null, null, null],
		list_visible = view,
		list_width = 0,
		list_height = 0;
		
	if (view == null || new_slide != null && view > jQuery('.listing:eq(0)', e).children().length || auto && jQuery(e).is(':hover')) {
		return;
	}
	
	if (new_slide != null && (view < 1 || slide == new_slide || (!auto || auto && (typeof loop == 'boolean' && !loop || typeof loop == 'number' && (loop_counter != null && loop_counter > loop))) && (new_slide < 1 || new_slide > Math.ceil(jQuery('.listing:eq(0) > *', e).length / view)))) {
		if (auto && interval_id != null) {
			clearInterval(interval_id);
		};
		
		return;
	}
	
	if (auto && (new_slide < 1 || new_slide > Math.ceil(jQuery('.listing:eq(0) > *', e).length / view))) {
		new_slide = (new_slide < 1) ? Math.ceil(jQuery('.listing:eq(0) > *', e).length / view) : 1;
		
		if (!jQuery('.navigation', e).length) {
			jQuery(e).data('slide', new_slide);
		}
	}
	
	if (new_slide != null) {
		switch (transition)
		{
		default:
			jQuery('.listing:eq(0) > *', e).each(function(j) {
				if (Math.ceil((jQuery(this).data('index') + 1) / view) == slide) {
					if (jQuery('.review-more-link', this).length && jQuery('.review-full-text', this).length) {
						jQuery('.review-full-text', this).hide();
						jQuery('.review-more-link', this).show();
					}
	
					jQuery(this).removeClass('visible').addClass('hidden');
					return;
				}
				
				if (Math.ceil((jQuery(this).data('index') + 1) / view) == new_slide) {
					jQuery(this).removeClass('hidden').addClass('visible');
					return;
				}
			});
			
			break;
		}
		
		if (jQuery('.navigation', e).length) {
			jQuery('a:eq(' + (new_slide - 1) + ')', jQuery('.navigation', e)).parent().addClass('current').siblings().removeClass('current');
		}
		
		slide = new_slide;
		jQuery(e).data('slide', slide);
	}
	
	jQuery('.listing:eq(0) > .visible', e).each(function(j) {
		bounds = this.getBoundingClientRect();
		
		if (list_area[0] == null || list_area[0] > bounds.top) {
			list_area[0] = bounds.top;
		}
		
		if (list_area[1] == null || list_area[1] < bounds.right) {
			list_area[1] = bounds.right;
		}
		
		if (list_area[2] == null || list_area[2] < bounds.top) {
			list_area[2] = bounds.bottom;
		}
		
		if (list_area[3] == null || list_area[3] > bounds.right) {
			list_area[3] = bounds.left;
		}
	});
	
	if (list_area[0] != null && list_area[1] != null && list_area[2] != null && list_area[3] != null) {
		list_width = parseInt(list_area[1] - list_area[3]) + parseInt(jQuery('.listing:eq(0) > .visible:eq(0)', e).css('margin-left')) + parseInt(jQuery('.listing:eq(0) > .visible:last', e).css('margin-right'));
		list_height = parseInt(list_area[2] - list_area[0]) + parseInt(jQuery('.listing:eq(0) > .visible:eq(0)', e).css('margin-top')) + parseInt(jQuery('.listing:eq(0) > .visible:last', e).css('margin-bottom'));
		if (list_width == 0 || list_height == 0) {
			if (jQuery('.navigation', e)) {
				jQuery('a', jQuery('.navigation', e)).each(function() {
					jQuery(this).on('click', function(event) {
						event.preventDefault();
						event.stopPropagation();
					});
				});
			}
			
			if (typeof jQuery(e).data('reattempt') != 'number' || jQuery(e).data('reattempt') < 1) {
				interval_id = setTimeout(google_business_reviews_rating_carousel, 10, e);
				jQuery(e).data('reattempt', interval_id);
				return;
			}
			
			return;
		}
	}
	
	if (auto && typeof loop == 'number' && loop >= 1) {
		if (typeof loop_counter != 'number' || loop_counter < 1) {
			loop_counter = 1;
		}
		
		loop_counter++;
		jQuery(e).data('counter', loop_counter);
		
		if (auto && interval_id != null && typeof loop == 'number' && loop_counter > loop) {
			clearInterval(interval_id);
			return;
		};
	}
	
	if (typeof jQuery('.listing:eq(0)', e).data('initial-height') == 'number' || list_area[0] == null || list_height == 0) {
		return;
	}
	
	jQuery('.listing:eq(0)', e).data('initial-height', parseInt(list_height));
	
	if (jQuery('.navigation', e).length) {
		jQuery('a', jQuery('.navigation', e)).each(function(index) {
			jQuery(this).on('click', function(event) {
				event.preventDefault();
				event.stopPropagation();
				
				if (jQuery(this).hasClass('current')) {
					return;
				}
				
				google_business_reviews_rating_carousel(this)
			});
		});
	}
	
	if (!auto && interval_id == null && (typeof loop == 'boolean' && loop || typeof loop == 'number' && loop >= 1) && typeof interval == 'number' && interval >= 0.3 && interval <= 999) {
		interval_id = setInterval(google_business_reviews_rating_carousel, interval * 1000, e, null, true);
		jQuery(e).data('interval-id', interval_id);
	}
	
	if (typeof jQuery(e).data('draggable') == 'boolean' && !jQuery(e).data('draggable') || typeof jQuery(e).data('draggable') == 'number' && jQuery(e).data('draggable') <= 0) {
		return;
	}

	jQuery('.listing:eq(0)', e).on('touchstart', function(event) {
		var e = jQuery(this).closest('.google-business-reviews-rating'),
			click_start = event.originalEvent.touches[0].pageX,
			view = (jQuery(e).hasClass('carousel') && typeof jQuery(e).data('view') == 'number' && jQuery(e).data('view') >= 1 && jQuery(e).data('view') <= 50) ? jQuery(e).data('view') : null,
			slide = (view != null && typeof jQuery(e).data('slide') == 'number' && jQuery(e).data('slide') >= 2) ? jQuery(e).data('slide') : 1;
		
		jQuery(this).one('touchmove', function(event) {
			var move_x = event.originalEvent.touches[0].pageX,
				pixel_sensitivity = 7;
	
			if (!jQuery('body').hasClass('rtl') && Math.ceil(move_x - click_start) > pixel_sensitivity || jQuery('body').hasClass('rtl') && Math.ceil(click_start - move_x) > pixel_sensitivity) {
				if (!jQuery('.navigation', e).length) {
					if (slide <= 1) {
						return
					}
					
					google_business_reviews_rating_carousel(e, slide - 2);
					return;
				}
				if (jQuery('.current', jQuery('.navigation', e)).index() <= 0) {
					return;
				}				
				
				google_business_reviews_rating_carousel(jQuery('.current > a', jQuery('.navigation', e)), jQuery('.current', jQuery('.navigation', e)).index() - 1);
				return;
			}
			
			if (!jQuery('body').hasClass('rtl') && Math.ceil(click_start - move_x) > pixel_sensitivity || jQuery('body').hasClass('rtl') && Math.ceil(move_x - click_start) > pixel_sensitivity) {
				if (!jQuery('.navigation', e).length) {
					if (slide >= Math.ceil(jQuery('.listing:eq(0)', e).children().length/view)) {
						return
					}
					
					google_business_reviews_rating_carousel(e, slide);
					return;
				}
				
				if (jQuery('.current', jQuery('.navigation', e)).index() >= jQuery('.bullet', jQuery('.navigation', e)).length - 1) {
					return;
				}
				
				google_business_reviews_rating_carousel(jQuery('.current > a', jQuery('.navigation', e)), jQuery('.current', jQuery('.navigation', e)).index() + 1);
				return;
			}
			
		});
		
		jQuery(this).on('touchend', function() {
			jQuery(this).off('touchmove');
		});
	});	
	
	return;
}

function google_business_reviews_rating_actions(event) {
	if (!jQuery('.google-business-reviews-rating.carousel').length || event.type != 'keydown' || event.keyCode != 37 && event.keyCode != 39) {
		return;
	}
		
	var i = 0,
		bounds = null,
		active = false,
		view = null,
		slide = null;
	
	for (i = 0; i < 2; i++) {
		jQuery('.google-business-reviews-rating.carousel').each(function() {
			if (active || typeof jQuery(this).data('cursor') == 'boolean' && !jQuery(this).data('cursor') || typeof jQuery(this).data('cursor') == 'number' && jQuery(this).data('cursor') <= 0) {
				return false;
			}
			
			if (i == 0 && !jQuery(this).is(':hover') || !jQuery('.listing', this).length) {
				return;
			}
			
			bounds = this.querySelector('.listing').getBoundingClientRect();
			
			if (typeof bounds != 'object' || bounds.bottom < 0 || bounds.top > (window.innerHeight || document.documentElement.clientHeight)) {
				return;
			}
			
			active = true;
			view = (jQuery(this).hasClass('carousel') && typeof jQuery(this).data('view') == 'number' && jQuery(this).data('view') >= 1 && jQuery(this).data('view') <= 50) ? jQuery(this).data('view') : null;
			slide = (view != null && typeof jQuery(this).data('slide') == 'number' && jQuery(this).data('slide') >= 2) ? jQuery(this).data('slide') : 1;
					
			if (!jQuery('body').hasClass('rtl') && event.keyCode == 37 || jQuery('body').hasClass('rtl') && event.keyCode == 39) {
				if (!jQuery('.navigation', this).length) {
					if (slide <= 1) {
						return false;
					}
					
					google_business_reviews_rating_carousel(this, slide - 2);
					return;
				}
				if (jQuery('.current', jQuery('.navigation', this)).index() <= 0) {
					return false;
				}
				
				google_business_reviews_rating_carousel(jQuery('.current > a', jQuery('.navigation', this)), jQuery('.current', jQuery('.navigation', this)).index() - 1);
				return false;
			}
			
			if (!jQuery('body').hasClass('rtl') && event.keyCode == 39 || jQuery('body').hasClass('rtl') && event.keyCode == 37) {
				if (!jQuery('.navigation', this).length) {
					if (slide >= Math.ceil(jQuery('.listing:eq(0)', this).children().length/view)) {
						return false;
					}
					
					google_business_reviews_rating_carousel(this, slide);
					return;
				}
				
				if (jQuery('.current', jQuery('.navigation', this)).index() >= jQuery('.bullet', jQuery('.navigation', this)).length - 1) {
					return false;
				}
				
				google_business_reviews_rating_carousel(jQuery('.current > a', jQuery('.navigation', this)), jQuery('.current', jQuery('.navigation', this)).index() + 1);
				return false;
			}
			
			return false;
		});
	}
}

jQuery(document).ready(function($){
	google_business_reviews_rating();
	return;
});

jQuery(window).on('keydown', function(event) {
	google_business_reviews_rating_actions(event);
	return;
});
