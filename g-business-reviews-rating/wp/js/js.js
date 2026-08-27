'use strict';

function google_business_reviews_rating(e, i) {
	if (e == undefined) {
		e = null;
	}
	else if (typeof e == 'string' && /^\d+$/.test(e)) {
		e = parseInt(e, 10);
	}

	if (i == undefined) {
		i = null;
	}
	else if (typeof i == 'string') {
		i = parseInt(i.replace(/[^\d]/, ''), 10);
	}
	
	if ((typeof e == 'number' || typeof e == 'object' || typeof e == 'string') && typeof i == 'number') {
		const containers = [...document.querySelectorAll('.google-business-reviews-rating')];

		if (typeof e == 'object' && e != null) {
			e = containers.indexOf(e);
		}
		else if (typeof e == 'string') {
			e = containers.indexOf(document.getElementById(e));
		}

		const li = containers[e]?.querySelectorAll('li')?.[i],
		      full_text = li?.querySelector('.review-full-text');

		if (!li) {
			return;
		}

		if (full_text) {
			full_text.style.display = '';
		}

		li.querySelector('.review-more-link')?.remove();
		return;
	}

	const safari = /^((?!chrome|android).)*safari/i.test(navigator.userAgent),
	      clear_styles = document.getElementById('stylesheet-none')?.checked ?? false,
	      is_rtl = document.body.classList.contains('rtl'),
	      observer = new IntersectionObserver(entries => {
		entries.forEach(entry => {
			if (entry.isIntersecting) {
				entry.target.classList.add('animation-start');
				return;
			}

			entry.target.classList.remove('animation-pause');
		});
	}, { threshold: 0.5 });

	document.querySelectorAll('.google-business-reviews-rating').forEach((review, index) => {
		const view = (review.classList.contains('carousel') && parseInt(review.dataset.view) >= 1 && parseInt(review.dataset.view) <= 50) ? parseInt(review.dataset.view) : null,
		      star_html = review.classList.contains('stars-html') || /\bversion[_-]?1\b/i.test(review.className),
		      star_css = !star_html && (review.classList.contains('stars-css') || review.classList.contains('stars-gray-css')),
		      star_inline = !star_html && !star_css && review.dataset.stars != null && /^inline|inline$/i.test(review.dataset.stars),
		      number_element = review.querySelector('.number'),
		      rating = (number_element) ? parseFloat(number_element.textContent.replace(/,/g, '.').replace(/(\d+(?:\.\d+)?)/, '$1')) : null,
		      href_data = review.dataset.href,
		      overall_link = (href_data != null && href_data.length && !review.querySelector('.buttons') && !review.querySelector('.listing > *')) ? href_data : null,
		      all_stars_element = review.querySelector('.all-stars'),
		      placeholders = review.querySelectorAll('.review-more-placeholder'),
		      listing_element = review.querySelector('.listing');

		let stars_width_multiplier = 0.196,
		    reviews_window = null,
		    star_color = !star_html && !star_css && !star_inline && (review.dataset.stars != null || review.dataset.starsGray != null);

		if (clear_styles) {
			review.removeAttribute('class');
		}
		else if (!review.id) {
			review.id = `google-business-reviews-rating${(index > 1) ? `-${index}` : ''}`;
		}

		if (!clear_styles && review.classList.contains?.('no-styles')) {
			review.removeAttribute('class');
		}

		if (review.classList?.contains('link')) {
			if (overall_link != null) {
				review.addEventListener('click', event => {
					if (!event.target.matches('a')) {
						event.preventDefault();
						event.stopPropagation();

						if (/^\//.test(overall_link)) {
							document.location.href = overall_link;
							return;
						}

						reviews_window = window.open(overall_link, '_blank');
						reviews_window.focus();
					}
				});
			}
			else {
				review.classList.remove('link');
			}

			delete review.dataset.href;
		}

		if (review.dataset.animate == 'immediate') {
			review.querySelector(':scope .all-stars.animate')?.classList.add('animation-start');
		}
		else {
			const animate_stars_element = review.querySelector(':scope .all-stars.animate');

			if (animate_stars_element) {
				observer.observe(animate_stars_element);
			}
		}

		if (!star_html && all_stars_element?.querySelector('.star')) {
			if (star_css) {
				if (!review.querySelector('.rating-stars')) {
					all_stars_element.insertAdjacentHTML('beforeend', '<span class="rating-stars star temporary" style="display: none;">.</span>');
				}

				if (!review.querySelector('.star.gray')) {
					all_stars_element.insertAdjacentHTML('beforeend', '<span class="star gray temporary" style="display: none;">.</span>');
				}

				const gray_star_color = getComputedStyle(review.querySelector('.star.gray'))?.color ?? null,
				      rating_stars_color = getComputedStyle(review.querySelector('.rating-stars'))?.color ?? null;

				if (gray_star_color && !/#(?:F7B\d0\d|E7711B)|rgba?\s*\(23[12],\s*11[34],\s*2[78]/i.test(rating_stars_color)) {
					review.dataset.stars = rating_stars_color;
					star_color = true;
				}

				const default_gray_pattern = (review.classList.contains('dark')) ? /^(?:#B4B4B4|rgba?\s*\(180,\s*180,\s*180(?:,\s*0?\.8)?\))$/i : /^(?:#(?:A4A4A4|C1C1C1|C9C9C9)|rgba?\s*\(193,\s*193,\s*193(?:,\s*1(?:\.0+)?)?\))$/i;

				if (gray_star_color && !default_gray_pattern.test(gray_star_color)) {
					review.dataset.starsGray = gray_star_color;
					star_color = true;
				}

				all_stars_element.querySelectorAll('.temporary').forEach(temp_element => temp_element.remove());
			}

			const stars_data = review.dataset.stars,
			      stars_gray_data = review.dataset.starsGray,
			      has_custom_stars = stars_data != null && stars_data.length && !/^#(?:F7B\d0\d|E7711B)$/i.test(stars_data),
			      has_custom_gray = stars_gray_data != null && stars_gray_data.length && !/^#(?:A4A4A4|C1C1C1|C9C9C9)$/i.test(stars_gray_data);

			if (star_color && (has_custom_stars || has_custom_gray)) {
				if (star_css && (stars_gray_data == null || stars_gray_data == 'css') && !all_stars_element.querySelector('.star.gray')) {
					all_stars_element.insertAdjacentHTML('beforeend', '<span class="temporary" style="display: none;">.</span>');
				}

				all_stars_element.querySelectorAll('.star').forEach(star => {
					try {
						let star_svg = atob(getComputedStyle(star).backgroundImage.replace(/^url\(["']data:image\/svg\+xml;charset=UTF-8;base64,(.+)["']\)$/, '$1'));

						if (has_custom_stars) {
							star_svg = star_svg.replace(/#(?:F7B\d0\d|E7711B)/g, stars_data);
						}

						if (has_custom_gray) {
							star_svg = star_svg.replace(/#(?:A4A4A4|C1C1C1|C9C9C9)/g, stars_gray_data);
						}

						star.style.backgroundImage = `url('data:image/svg+xml;charset=UTF-8;base64,${btoa(star_svg)}')`;
					}
					catch (_) {}
				});
			}
		}

		if (placeholders.length) {
			const list_items = [...(review.querySelector('.listing')?.querySelectorAll('li') ?? [])];

			placeholders.forEach(placeholder => {
				const full_text = placeholder.parentElement?.querySelector('.review-full-text');

				if (full_text && !full_text.innerHTML.length) {
					placeholder.closest('li')?.classList.remove('text-excerpt');
					full_text.remove();
					placeholder.remove();
					return;
				}

				const more_link = document.createElement('a');
				more_link.className = 'review-more-link';
				more_link.innerHTML = placeholder.innerHTML;

				const author_name = placeholder.closest('li')?.querySelector('.author-name')?.textContent?.trim();

				if (author_name) {
					more_link.setAttribute('aria-label', `${more_link.textContent.trim()} – ${author_name}`);
				}

				if (review.classList.contains('js-links')) {
					const li_index = list_items.indexOf(placeholder.closest('li'));
					more_link.href = '#';
					more_link.addEventListener('click', event => {
						event.preventDefault();
						event.stopPropagation();
						google_business_reviews_rating(index, li_index);
					});
					placeholder.insertAdjacentElement('afterend', more_link);
					placeholder.remove();
					return;
				}

				more_link.href = `#${review.id || ''}`;
				more_link.addEventListener('click', event => {
					event.preventDefault();
					event.stopPropagation();

					if (more_link.nextElementSibling?.classList.contains('review-full-text')) {
						more_link.nextElementSibling.style.display = '';
					}

					if (view == null) {
						more_link.remove();
						return;
					}

					more_link.style.display = 'none';
					google_business_reviews_rating_carousel(more_link, null);
				});

				placeholder.insertAdjacentElement('afterend', more_link);
				placeholder.remove();
			});
		}

		if (review.querySelector('.fixed-height') && review.classList.contains('bubble')) {
			review.querySelectorAll('.text').forEach(text_element => {
				const prev = text_element.previousElementSibling,
				      next = text_element.nextElementSibling;

				if (prev && (prev.classList.contains('author-avatar') || (prev.classList.contains('review-meta') && prev.querySelector('.author-name')))) {
					text_element.insertAdjacentHTML('beforebegin', '<span class="arrow arrow-up"></span>');
					return;
				}

				if (next && (next.classList.contains('author-avatar') || (next.classList.contains('review-meta') && next.querySelector('.author-name')))) {
					text_element.insertAdjacentHTML('afterend', '<span class="arrow arrow-down"></span>');
				}
			});
		}

		if (!star_html && !star_inline && all_stars_element?.classList.contains('animate') && typeof rating == 'number' && rating > 1.5 && number_element) {
			const all_stars_width = all_stars_element.getBoundingClientRect().width,
			      backdrop = document.createElement('span');
			backdrop.classList.add('all-stars', 'backdrop');
			backdrop.style.inlineSize = `${Math.ceil(all_stars_width + 0.1)}px`;
			backdrop.style.marginInlineStart = `${-all_stars_width - 0.1}px`;
			backdrop.innerHTML = '<span class="star gray"></span>'.repeat(5);
			all_stars_element.insertAdjacentElement('afterend', backdrop);

			const backdrop_element = review.querySelector('.all-stars.backdrop');

			if (backdrop_element) {
				let backdrop_offset = all_stars_element.getBoundingClientRect().top - backdrop_element.getBoundingClientRect().top + parseFloat(getComputedStyle(backdrop_element).marginBlockStart);

				if (backdrop_offset != 0) {
					backdrop_element.style.marginBlockStart = `${backdrop_offset}px`;
				}
			}

			const stars_gray_data = review.dataset.starsGray;

			if (stars_gray_data != null && stars_gray_data.length && !/^#(?:A4A4A4|C1C1C1|C9C9C9)$/i.test(stars_gray_data)) {
				review.querySelectorAll('.all-stars.backdrop .star').forEach(star => {
					try {
						let star_svg = atob(getComputedStyle(star).backgroundImage.replace(/^url\(["']data:image\/svg\+xml;charset=UTF-8;base64,(.+)["']\)$/, '$1'));
						star_svg = star_svg.replace(/#(?:A4A4A4|C1C1C1|C9C9C9)/g, stars_gray_data);
						star.style.backgroundImage = `url('data:image/svg+xml;charset=UTF-8;base64,${btoa(star_svg)}')`;
					}
					catch (_) {}
				});
			}

			const all_star_spans = [...all_stars_element.querySelectorAll('.star')],
			      last_star = all_star_spans.at(-1);

			if (last_star) {
				last_star.addEventListener('animationend', () => {
					const animating_backdrop = last_star.closest('.rating')?.querySelector('.all-stars.backdrop');

					if (animating_backdrop) {
						animating_backdrop.style.transition = 'opacity 300ms';
						animating_backdrop.style.opacity = '0';
						setTimeout(() => animating_backdrop.remove(), 300);
					}
				});
			}

			setTimeout(() => {
				const timer_backdrop = review.querySelector('.all-stars.backdrop');

				if (timer_backdrop) {
					timer_backdrop.style.transition = 'opacity 300ms';
					timer_backdrop.style.opacity = '0';
					setTimeout(() => timer_backdrop.remove(), 300);
				}
			}, 4800);
		}

		if (star_html && typeof rating == 'number') {
			if (safari) {
				all_stars_element?.classList.add('safari');
			}

			if (rating == 5) {
				setTimeout(() => {
					if (all_stars_element) {
						all_stars_element.style.color = '#0000';
					}
				}, 2400);
			}

			if (rating == 0) {
				review.querySelector('.rating-stars')?.remove();
			}

			const rating_stars_element = review.querySelector('.rating-stars');

			if (rating_stars_element && all_stars_element) {
				const multiplier = rating_stars_element.dataset.multiplier;

				if (multiplier != null) {
					stars_width_multiplier = parseFloat(multiplier);
				}

				const all_stars_width = all_stars_element.getBoundingClientRect().width,
				      rating_width = Math.round(
				          all_stars_width * rating * stars_width_multiplier
				          + stars_width_multiplier * 0.05 * Math.sin(rating * 2 * Math.PI)
				          + 0.5 * stars_width_multiplier * (Math.round(rating + 0.49) - rating)
				      );
				rating_stars_element.style.width = `${rating_width}px`;
				rating_stars_element.style.margin = (is_rtl) ? `0 0 0 ${-rating_width}px` : `0 ${-rating_width}px 0 0`;
			}
		}

		if (view == null || !listing_element || view > listing_element.children.length) {
			return;
		}

		google_business_reviews_rating_carousel(review);
	});
}

function google_business_reviews_rating_carousel(e, i, auto) {
	if (typeof e != 'object' || e == null) {
		return;
	}

	if (typeof i != 'number') {
		const is_container = e.classList?.contains('google-business-reviews-rating'),
		      is_more_link = e.classList?.contains('review-more-link'),
		      bullet_e = (!is_container && !is_more_link) ? e.closest?.('.bullet') : null;

		i = (bullet_e) ? [...bullet_e.parentElement.children].indexOf(bullet_e) : null;
	}

	if (typeof auto != 'boolean') {
		auto = false;
	}

	e = (e.classList?.contains('google-business-reviews-rating')) ? e : e.closest?.('.google-business-reviews-rating');

	if (!e) {
		return;
	}

	const is_rtl = document.body.classList.contains('rtl'),
	      view = (e.classList.contains('carousel') && parseInt(e.dataset.view) >= 1 && parseInt(e.dataset.view) <= 50) ? parseInt(e.dataset.view) : null,
	      iterations = (view != null && isNaN(parseInt(e.dataset.loop)) && parseInt(e.dataset.iterations) >= 1) ? parseInt(e.dataset.iterations) : null,
	      loop_num = parseInt(e.dataset.loop),
	      loop = (view != null && e.dataset.loop != null) ? ((e.dataset.loop == 'false') ? false : ((!isNaN(loop_num) && loop_num < 1) ? true : ((!isNaN(loop_num)) ? loop_num : true))) : ((iterations != null) ? Math.round(iterations * view) : false),
	      interval = (view != null && loop && e.dataset.interval != null) ? parseFloat(e.dataset.interval) : null,
	      first_listing = e.querySelector('.listing'),
	      total_children = () => first_listing?.children.length ?? 0,
	      total_pages = () => Math.ceil(total_children() / view);

	let slide = (view != null && parseInt(e.dataset.slide) >= 2) ? parseInt(e.dataset.slide) : 1,
	    loop_counter = (view != null && loop && e.dataset.counter != null) ? parseInt(e.dataset.counter) : null,
	    interval_id = (view != null && loop && e.dataset.intervalId != null) ? parseInt(e.dataset.intervalId) : null,
	    new_slide = (i != null) ? i + 1 : ((auto) ? slide + 1 : null);

	if (view == null || (new_slide != null && view > total_children()) || (auto && e.matches(':hover'))) {
		return;
	}

	if (new_slide != null && (view < 1 || slide == new_slide || (!auto || (auto && (!loop || loop_counter != null && loop_counter > loop))) && (new_slide < 1 || new_slide > total_pages()))) {
		if (auto && interval_id != null) {
			clearInterval(interval_id);
		}
		return;
	}

	if (auto && (new_slide < 1 || new_slide > total_pages())) {
		new_slide = (new_slide < 1) ? total_pages() : 1;

		if (!e.querySelector('.navigation')) {
			e.dataset.slide = new_slide;
		}
	}

	if (new_slide != null) {
		[...(first_listing?.children ?? [])].forEach(item => {
			const item_index = parseInt(item.dataset.index);

			if (Math.ceil((item_index + 1) / view) == slide) {
				const full_text = item.querySelector('.review-full-text'),
				      more_link = item.querySelector('.review-more-link');

				if (more_link && full_text) {
					full_text.style.display = 'none';
					more_link.style.display = '';
				}
				item.classList.replace('visible', 'hidden');
				return;
			}

			if (Math.ceil((item_index + 1) / view) == new_slide) {
				item.classList.replace('hidden', 'visible');
			}
		});

		const nav = e.querySelector('.navigation'),
		      target_link = nav?.querySelectorAll('a')?.[new_slide - 1];

		if (target_link) {
			target_link.parentElement.classList.add('current');
			target_link.setAttribute('aria-current', 'true');
			[...target_link.parentElement.parentElement.children].forEach(sibling => {
				if (sibling == target_link.parentElement) {
					return;
				}

				sibling.classList.remove('current');
				sibling.querySelector('a')?.removeAttribute('aria-current');
			});
		}

		slide = new_slide;
		e.dataset.slide = slide;
	}

	let list_area = [null, null, null, null],
	    list_width = 0,
	    list_height = 0;

	const visibles = [...(first_listing?.querySelectorAll('.visible') ?? [])];

	visibles.forEach(item => {
		const bounds = item.getBoundingClientRect();

		if (list_area[0] == null || list_area[0] > bounds.top) {
			list_area[0] = bounds.top;
		}

		if (list_area[1] == null || list_area[1] < bounds.right) {
			list_area[1] = bounds.right;
		}

		if (list_area[2] == null || list_area[2] < bounds.bottom) {
			list_area[2] = bounds.bottom;
		}

		if (list_area[3] == null || list_area[3] > bounds.left) {
			list_area[3] = bounds.left;
		}
	});

	if (list_area[0] != null) {
		const first_visible = visibles[0],
		      last_visible = visibles.at(-1);

		if (first_visible && last_visible) {
			const first_visible_style = getComputedStyle(first_visible),
			      last_visible_style = getComputedStyle(last_visible);

			list_width = parseInt(list_area[1] - list_area[3]) + parseInt(first_visible_style.marginLeft)  + parseInt(last_visible_style.marginRight);
			list_height = parseInt(list_area[2] - list_area[0]) + parseInt(first_visible_style.marginTop)   + parseInt(last_visible_style.marginBottom);
		}

		if (list_width == 0 || list_height == 0) {
			e.querySelector('.navigation')?.querySelectorAll('a').forEach(link_element => {
				link_element.addEventListener('click', event => {
					event.preventDefault();
					event.stopPropagation();
				});
			});

			if (e.dataset.reattempt == null || parseInt(e.dataset.reattempt) < 1) {
				const timeout_id = setTimeout(google_business_reviews_rating_carousel, 10, e);
				e.dataset.reattempt = timeout_id;
			}
			return;
		}
	}

	if (auto && typeof loop != 'boolean' && loop >= 1) {
		loop_counter = (loop_counter == null || loop_counter < 1) ? 1 : loop_counter;
		loop_counter++;
		e.dataset.counter = loop_counter;

		if (interval_id != null && loop_counter > loop) {
			clearInterval(interval_id);
			return;
		}
	}

	if (!first_listing) {
		return;
	}

	if (first_listing.dataset.initialHeight != null || list_area[0] == null || list_height == 0) {
		return;
	}

	first_listing.dataset.initialHeight = list_height;

	const nav = e.querySelector('.navigation');

	nav?.querySelectorAll('a').forEach(link_element => {
		link_element.addEventListener('click', event => {
			event.preventDefault();
			event.stopPropagation();

			if (link_element.parentElement.classList.contains('current')) {
				return;
			}
			google_business_reviews_rating_carousel(link_element);
		});
	});

	if (!auto && interval_id == null && loop && interval != null && interval >= 0.3 && interval <= 999) {
		interval_id = setInterval(google_business_reviews_rating_carousel, interval * 1000, e, null, true);
		e.dataset.intervalId = interval_id;
	}

	const draggable = e.dataset.draggable;

	if (draggable == 'false' || draggable != null && parseInt(draggable) <= 0) {
		return;
	}

	if (!first_listing.dataset.touchInit) {
		first_listing.dataset.touchInit = true;

		first_listing.addEventListener('touchstart', event => {
			const container = first_listing.closest('.google-business-reviews-rating'),
			      click_start = event.touches[0].pageX,
			      nav_element = container.querySelector('.navigation'),
				  current_view = (container.classList.contains('carousel') && parseInt(container.dataset.view) >= 1 && parseInt(container.dataset.view) <= 50) ? parseInt(container.dataset.view) : null,
			      current_slide = (current_view != null && parseInt(container.dataset.slide) >= 2) ? parseInt(container.dataset.slide) : 1,
				  get_bullet_index = bullet_e => [...(nav_element?.querySelectorAll('.bullet') ?? [])].indexOf(bullet_e),
			      current_bullet = () => nav_element?.querySelector('.current');

			const on_touch_move = move_event => {
				const move_x = move_event.touches[0].pageX,
				      pixel_sensitivity = 7;

				if (!is_rtl && Math.ceil(move_x - click_start) > pixel_sensitivity || is_rtl && Math.ceil(click_start - move_x) > pixel_sensitivity) {
					if (!nav_element) {
						if (current_slide > 1) {
							google_business_reviews_rating_carousel(container, current_slide - 2);
						}
						return;
					}

					if (get_bullet_index(current_bullet()) > 0) {
						google_business_reviews_rating_carousel(current_bullet().querySelector('a'), get_bullet_index(current_bullet()) - 1);
					}
					return;
				}

				if (!is_rtl && Math.ceil(click_start - move_x) > pixel_sensitivity || is_rtl && Math.ceil(move_x - click_start) > pixel_sensitivity) {
					if (!nav_element) {
						if (current_slide < Math.ceil((first_listing?.children.length ?? 0) / current_view)) {
							google_business_reviews_rating_carousel(container, current_slide);
						}
						return;
					}

					const bullets = nav_element.querySelectorAll('.bullet');

					if (get_bullet_index(current_bullet()) < bullets.length - 1) {
						google_business_reviews_rating_carousel(current_bullet().querySelector('a'), get_bullet_index(current_bullet()) + 1);
					}
				}
			};

			first_listing.addEventListener('touchmove', on_touch_move, { once: true });
			first_listing.addEventListener('touchend', () => first_listing.removeEventListener('touchmove', on_touch_move), { once: true });
		});
	}
}

function google_business_reviews_rating_actions(event) {
	const carousels = document.querySelectorAll('.google-business-reviews-rating.carousel'),
	      is_rtl = document.body.classList.contains('rtl');

	let active = false;

	if (!carousels.length || event.key != 'ArrowLeft' && event.key != 'ArrowRight') {
		return;
	}

	for (let pass = 0; pass < 2; pass++) {
		for (const carousel of carousels) {
			if (active) {
				break;
			}

			const cursor_data = carousel.dataset.cursor;

			if (cursor_data == 'false' || cursor_data != null && parseInt(cursor_data) <= 0) {
				continue;
			}

			const listing_element = carousel.querySelector('.listing');

			if ((pass == 0 && !carousel.matches(':hover')) || !listing_element) {
				continue;
			}

			const bounds = listing_element.getBoundingClientRect();

			if (bounds.bottom < 0 || bounds.top > (window.innerHeight || document.documentElement.clientHeight)) {
				continue;
			}

			active = true;

			const view = (carousel.classList.contains('carousel') && parseInt(carousel.dataset.view) >= 1 && parseInt(carousel.dataset.view) <= 50) ? parseInt(carousel.dataset.view) : null,
			      slide = (view != null && parseInt(carousel.dataset.slide) >= 2) ? parseInt(carousel.dataset.slide) : 1,
			      nav = carousel.querySelector('.navigation'),
			      get_bullet_index = bullet_e => [...(nav?.querySelectorAll('.bullet') ?? [])].indexOf(bullet_e),
			      current_bullet = () => nav?.querySelector('.current');

			if (!is_rtl && event.key == 'ArrowLeft' || is_rtl && event.key == 'ArrowRight') {
				if (!nav) {
					if (slide > 1) {
						google_business_reviews_rating_carousel(carousel, slide - 2);
					}
					break;
				}

				if (get_bullet_index(current_bullet()) > 0) {
					google_business_reviews_rating_carousel(current_bullet().querySelector('a'), get_bullet_index(current_bullet()) - 1);
				}
				break;
			}

			if (!is_rtl && event.key == 'ArrowRight' || is_rtl && event.key == 'ArrowLeft') {
				if (!nav) {
					if (slide < Math.ceil((listing_element?.children.length ?? 0) / view)) {
						google_business_reviews_rating_carousel(carousel, slide);
					}
					break;
				}

				const bullets = nav.querySelectorAll('.bullet');

				if (get_bullet_index(current_bullet()) < bullets.length - 1) {
					google_business_reviews_rating_carousel(current_bullet().querySelector('a'), get_bullet_index(current_bullet()) + 1);
				}
				break;
			}
		}

		if (active) {
			break;
		}
	}
}

document.addEventListener('DOMContentLoaded', () => {
	google_business_reviews_rating();
	window.addEventListener('keydown', google_business_reviews_rating_actions);
});
