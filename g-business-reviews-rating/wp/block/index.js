const { registerBlockType } = window.wp.blocks;
const { createElement: create_element, Fragment } = window.wp.element;
const { InspectorControls, useBlockProps } = window.wp.blockEditor;
const { PanelBody, TextControl, SelectControl, RangeControl, ToggleControl } = window.wp.components;
const { __ } = window.wp.i18n;
const server_side_render = window.wp.serverSideRender;

const google_business_reviews_rating_block_columns = { two: 2, three: 3, four: 4, five: 5, six: 6 };

const google_business_reviews_rating_block_icon = create_element(
    'svg',
    { xmlns: 'http://www.w3.org/2000/svg', viewBox: '272.67 13.72 96 96' },
    create_element('path', { fill: '#F7B603', d: 'M338.809 72.417 368.252 51h-36.389l-11.197-34.5L309.468 51h-36.389l29.444 21.417-11.202 34.523 29.345-21.324 29.345 21.322z' })
);

const google_business_reviews_rating_block_options = (list, empty_label) => {
    let options = [{ label: empty_label, value: '' }],
        key;

    if (typeof list != 'object' || list == null) {
        return options;
    }

    for (key in list) {
        options.push({ label: String(list[key]), value: key });
    }

    return options;
};

/* Editor interface for the reviews block */

const google_business_reviews_rating_block_edit = ( { attributes, setAttributes } ) => {
    const lists = (typeof google_business_reviews_rating_block_lists != 'undefined') ? google_business_reviews_rating_block_lists : {};

    const toggle = (label, attribute) => create_element( ToggleControl, {
        label: label,
        checked: attributes[attribute],
        onChange: value => setAttributes( { [attribute]: value } )
    } );

    const controls = create_element( InspectorControls, {},
        create_element( PanelBody, { title: __( 'Source', 'g-business-reviews-rating' ) },
            create_element( TextControl, {
                label: __( 'Place ID', 'g-business-reviews-rating' ),
                help: __( 'Leave empty to use the Place ID from the plugin settings.', 'g-business-reviews-rating' ),
                value: attributes.placeId,
                onChange: value => setAttributes( { placeId: value } )
            } ),
            create_element( SelectControl, {
                label: __( 'Language', 'g-business-reviews-rating' ),
                value: attributes.language,
                options: google_business_reviews_rating_block_options( lists.languages, __( 'Any language', 'g-business-reviews-rating' ) ),
                onChange: value => setAttributes( { language: value } )
            } )
        ),
        create_element( PanelBody, { title: __( 'Appearance', 'g-business-reviews-rating' ) },
            create_element( SelectControl, {
                label: __( 'Theme', 'g-business-reviews-rating' ),
                value: attributes.theme,
                options: google_business_reviews_rating_block_options( lists.themes, __( 'Default', 'g-business-reviews-rating' ) ),
                onChange: value => {
                    let match = value.match( /\bcolumns (two|three|four|five|six)\b/i ),
                        columns = 0,
                        limit = 0;

                    if (/badge/i.test( value )) {
                        setAttributes( {
                            theme: value,
                            limit: 0,
                            summary: true,
                            displayName: false,
                            displayIcon: false,
                            displayVicinity: false,
                            displayRating: true,
                            displayStars: true,
                            displayCount: true,
                            reviewsLink: false,
                            writeReviewLink: false
                        } );
                        return;
                    }

                    if (match == null || attributes.limit <= 0) {
                        setAttributes( { theme: value } );
                        return;
                    }

                    columns = google_business_reviews_rating_block_columns[ match[1].toLowerCase() ];
                    limit = Math.max( columns, Math.round( attributes.limit / columns ) * columns );

                    setAttributes( { theme: value, limit: (limit > 50) ? Math.floor( 50 / columns ) * columns : limit } );
                }
            } )
        ),
        create_element( PanelBody, { title: __( 'Summary', 'g-business-reviews-rating' ), initialOpen: false },
            toggle( __( 'Show the summary', 'g-business-reviews-rating' ), 'summary' ),
            toggle( __( 'Business name', 'g-business-reviews-rating' ), 'displayName' ),
            toggle( __( 'Icon', 'g-business-reviews-rating' ), 'displayIcon' ),
            toggle( __( 'Address', 'g-business-reviews-rating' ), 'displayVicinity' ),
            toggle( __( 'Rating', 'g-business-reviews-rating' ), 'displayRating' ),
            toggle( __( 'Stars', 'g-business-reviews-rating' ), 'displayStars' ),
            toggle( __( 'Review count', 'g-business-reviews-rating' ), 'displayCount' )
        ),
        create_element( PanelBody, { title: __( 'Reviews', 'g-business-reviews-rating' ), initialOpen: false },
            create_element( SelectControl, {
                label: __( 'Sort', 'g-business-reviews-rating' ),
                value: attributes.sort,
                options: google_business_reviews_rating_block_options( lists.sorts, __( 'Relevance', 'g-business-reviews-rating' ) ),
                onChange: value => setAttributes( { sort: value } )
            } ),
            create_element( RangeControl, {
                label: __( 'Number of reviews', 'g-business-reviews-rating' ),
                value: attributes.limit,
                min: 0,
                max: 50,
                onChange: value => setAttributes( { limit: value } )
            } ),
            create_element( RangeControl, {
                label: __( 'Skip the first', 'g-business-reviews-rating' ),
                value: attributes.offset,
                min: 0,
                max: 50,
                onChange: value => setAttributes( { offset: value } )
            } ),
            create_element( RangeControl, {
                label: __( 'Lowest rating shown', 'g-business-reviews-rating' ),
                value: attributes.ratingMin,
                min: 1,
                max: 5,
                onChange: value => setAttributes( { ratingMin: (value > attributes.ratingMax) ? attributes.ratingMax : value } )
            } ),
            create_element( RangeControl, {
                label: __( 'Highest rating shown', 'g-business-reviews-rating' ),
                value: attributes.ratingMax,
                min: 1,
                max: 5,
                onChange: value => setAttributes( { ratingMax: (value < attributes.ratingMin) ? attributes.ratingMin : value } )
            } ),
            create_element( RangeControl, {
                label: __( 'Shorten review text to', 'g-business-reviews-rating' ),
                help: __( 'Characters. Zero uses the plugin setting.', 'g-business-reviews-rating' ),
                value: attributes.excerpt,
                min: 0,
                max: 500,
                step: 20,
                onChange: value => setAttributes( { excerpt: value } )
            } ),
            create_element( TextControl, {
                label: __( 'Read more text', 'g-business-reviews-rating' ),
                value: attributes.more,
                onChange: value => setAttributes( { more: value } )
            } ),
            toggle( __( 'Review date', 'g-business-reviews-rating' ), 'displayDate' ),
            toggle( __( 'Reviewer photos', 'g-business-reviews-rating' ), 'displayAvatar' )
        ),
        create_element( PanelBody, { title: __( 'Links', 'g-business-reviews-rating' ), initialOpen: false },
            toggle( __( 'View reviews on Google', 'g-business-reviews-rating' ), 'reviewsLink' ),
            toggle( __( 'Write a review', 'g-business-reviews-rating' ), 'writeReviewLink' )
        ),
        create_element( PanelBody, { title: __( 'About', 'g-business-reviews-rating' ), initialOpen: false },
            toggle( __( 'Attribution', 'g-business-reviews-rating' ), 'attribution' ),
            create_element( 'p', { className: 'components-base-control__help' }, __( 'Credits Google as the source. Some themes already make this clear, so it can be turned off.', 'g-business-reviews-rating' ) ),
            create_element( 'p', { className: 'components-base-control__help' }, __( 'These are the common options. The shortcode covers everything else.', 'g-business-reviews-rating' ) )
        )
    );

    const preview = create_element( server_side_render, {
        block: 'g-business-reviews-rating/reviews',
        attributes: attributes
    } );

    return create_element( Fragment, {}, controls, create_element( 'div', useBlockProps(), preview ) );
};

registerBlockType( 'g-business-reviews-rating/reviews', {
    icon: google_business_reviews_rating_block_icon,
    edit: google_business_reviews_rating_block_edit,
    save: () => null
} );
