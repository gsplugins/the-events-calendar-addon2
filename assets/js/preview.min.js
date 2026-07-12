(function($) {

    const varMap = {
        title_typography: 'title',
        cat_typography: 'cat',
        tag_typography: 'tag',
        org_typography: 'org',
        date_typography: 'date',
        details_typography: 'details',
        venue_typography: 'venue',
        view_details_button_typography: 'view_details',
        google_calendar_button_typography: 'google_calendar',
    };

    const customClassMap = {
        title_typography: 'title',
        cat_typography: 'cat',
        tag_typography: 'tag',
        org_typography: 'org',
        date_typography: 'date',
        details_typography: 'details',
        venue_typography: 'venue',
        view_details_button_typography: 'view_details',
        google_calendar_button_typography: 'google_calendar',
    };

    const controlToFieldMap = {
        getFonts: 'font_family',
        size: 'font_size',
        weight: 'font_weight',
        lineHeight: 'line_height',
        letterSpacing: 'letter_spacing',
        transform: 'text_transform',
        style: 'font_style',
        decoration: 'text_decoration'
    };

    const elementTargets = {
        title: ['.teca-event-title', '.teca-event-title a', '.gs-teca-title', '.gs-teca-title a', '.teca-accordion-title', '.teca-accordion-title a', '.teca-timeline-title', '.teca-timeline-title a'],
        cat: ['.teca-event-category', '.teca-event-category a', '.teca-event-categories .teca-event-category', '.teca-event-categories .teca-event-category a', '.teca-event-categories a', '.teca-event-categories span', '.gs-teca-categories .gs-teca-category', '.gs-teca-categories .gs-teca-category a', '.gs-teca-categories a', '.gs-teca-categories span', '.gs-teca-cat', '.gs-teca-cat a'],
        tag: ['.teca-event-tag', '.teca-event-tag a', '.teca-event-tags .teca-event-tag', '.teca-event-tags .teca-event-tag a', '.teca-event-tags a', '.gs-teca-tags .gs-teca-tag', '.gs-teca-tags .gs-teca-tag a', '.gs-teca-tags span.gs-teca-tag', '.gs-teca-tag', '.gs-teca-tag a'],
        org: ['.teca-event-organizer', '.teca-event-organizer a', '.gs-teca-organizers', '.gs-teca-organizers a', '.gs-teca-org', '.gs-teca-org a'],
        date: ['.teca-event-date', '.teca-event-date span', '.teca-event-time', '.teca-event-time-value', '.gs-teca-date', '.gs-teca-date a', '.gs-teca-glass-date', '.gs-teca-glass-date a'],
        details: ['.teca-event-details', '.teca-event-details a', '.teca-event-excerpt', '.teca-event-excerpt a', '.teca-event-meta', '.teca-event-meta a', '.gs-teca-details', '.gs-teca-details a', '.gs-teca-desc', '.gs-teca-desc a', '.gs-teca-excerpt', '.gs-teca-excerpt a'],
        venue: ['.teca-event-venue', '.teca-event-venue a', '.gs-teca-venue', '.gs-teca-venue a'],
        view_details: [
            '.teca-view-details',
            '.teca-view-details a',
            '.teca-view-details button',
            '.gs-teca-view-details',
            '.gs-teca-view-details a',
            '.gs-teca-view-details button',
            '.gs-teca-view-details .teca-event-button',
            '.gs-teca-view-details .gs-teca-btn-popup',
            '.gs-teca-view-details .gs-teca-btn-link',
            '.gs-teca-btn-popup',
            '.gs-teca-btn-link',
            '.gs-teca-action-btn',
            '.teca-single-button',
            '.teca-single-button.teca-event-button',
            '.teca-accordion-button',
            '.teca-accordion-button a',
            '.teca-timeline-button',
            '.teca-timeline-button a',
            '[class*="teca-grid-style-"][class*="-button"]',
            '[class*="teca-style-"][class*="-link"]',
        ],
        google_calendar: [
            '.teca-google-calendar-btn',
            '.teca-google-calendar-btn span',
            '.teca-google-calendar-actions .teca-google-calendar-btn',
            '.teca-google-calendar-actions .teca-google-calendar-btn span',
            '.teca-popup-actions .teca-google-calendar-btn',
            '.teca-google-calendar-btn--card',
            '.teca-google-calendar-btn--table',
            '.teca-list-google-calendar-wrap .teca-google-calendar-btn',
        ],
    };

    const colorElementTargets = {
        cat: {
            color: ['.teca-event-category', '.teca-event-category a', '.teca-event-categories .teca-event-category', '.teca-event-categories a'],
            background: ['.teca-event-category'],
        },
        tag: {
            color: ['.teca-event-tag', '.teca-event-tag a', '.teca-event-tags .teca-event-tag', '.teca-event-tags a'],
            background: ['.teca-event-tag'],
        },
        view_details: {
            color: [
                '.gs-teca-view-details .gs-teca-btn-popup',
                '.gs-teca-view-details .gs-teca-btn-link',
                '.gs-teca-view-details .teca-event-button',
                '.gs-teca-view-details .gs-teca-btn-popup span',
                '.gs-teca-view-details .gs-teca-btn-link span',
                '.gs-teca-btn-popup',
                '.gs-teca-btn-link',
                '.gs-teca-action-btn',
                '.teca-accordion-button',
                '.teca-accordion-button a',
                '.teca-timeline-button',
                '.teca-timeline-button a',
                '.teca-single-button',
                '[class*="teca-grid-style-"][class*="-button"]',
                '[class*="teca-style-"][class*="-link"]',
            ],
            background: [
                '.gs-teca-view-details .gs-teca-btn-popup',
                '.gs-teca-view-details .gs-teca-btn-link',
                '.gs-teca-view-details .teca-event-button',
                '.gs-teca-btn-popup',
                '.gs-teca-btn-link',
                '.gs-teca-action-btn',
                '.teca-accordion-button',
                '.teca-timeline-button',
                '.teca-single-button',
                '[class*="teca-grid-style-"][class*="-button"]',
                '[class*="teca-style-"][class*="-link"]',
            ],
        },
        google_calendar: {
            color: [
                '.teca-google-calendar-btn',
                '.teca-google-calendar-btn span',
                '.teca-google-calendar-actions .teca-google-calendar-btn',
                '.teca-google-calendar-actions .teca-google-calendar-btn span',
                '.teca-google-calendar-btn--card',
                '.teca-google-calendar-btn--table',
                '.teca-list-google-calendar-wrap .teca-google-calendar-btn',
            ],
            background: [
                '.teca-google-calendar-btn',
                '.teca-google-calendar-actions .teca-google-calendar-btn',
                '.teca-google-calendar-btn--card',
                '.teca-google-calendar-btn--table',
                '.teca-list-google-calendar-wrap .teca-google-calendar-btn',
            ],
        },
    };

    const colorFieldMap = {
        title_color: { group: 'title', property: 'color', pseudo: '' },
        title_background_color: { group: 'title', property: 'background-color', pseudo: '' },
        title_hover_color: { group: 'title', property: 'color', pseudo: ':hover' },
        title_hover_background_color: { group: 'title', property: 'background-color', pseudo: ':hover' },
        cat_color: { group: 'cat', property: 'color', pseudo: '' },
        cat_background_color: { group: 'cat', property: 'background-color', pseudo: '' },
        cat_hover_color: { group: 'cat', property: 'color', pseudo: ':hover' },
        cat_hover_background_color: { group: 'cat', property: 'background-color', pseudo: ':hover' },
        tag_color: { group: 'tag', property: 'color', pseudo: '' },
        tag_background_color: { group: 'tag', property: 'background-color', pseudo: '' },
        tag_hover_color: { group: 'tag', property: 'color', pseudo: ':hover' },
        tag_hover_background_color: { group: 'tag', property: 'background-color', pseudo: ':hover' },
        org_color: { group: 'org', property: 'color', pseudo: '' },
        org_background_color: { group: 'org', property: 'background-color', pseudo: '' },
        org_hover_color: { group: 'org', property: 'color', pseudo: ':hover' },
        org_hover_background_color: { group: 'org', property: 'background-color', pseudo: ':hover' },
        date_color: { group: 'date', property: 'color', pseudo: '' },
        date_background_color: { group: 'date', property: 'background-color', pseudo: '' },
        date_hover_color: { group: 'date', property: 'color', pseudo: ':hover' },
        date_hover_background_color: { group: 'date', property: 'background-color', pseudo: ':hover' },
        details_color: { group: 'details', property: 'color', pseudo: '' },
        details_background_color: { group: 'details', property: 'background-color', pseudo: '' },
        details_hover_color: { group: 'details', property: 'color', pseudo: ':hover' },
        details_hover_background_color: { group: 'details', property: 'background-color', pseudo: ':hover' },
        venue_color: { group: 'venue', property: 'color', pseudo: '' },
        venue_background_color: { group: 'venue', property: 'background-color', pseudo: '' },
        venue_hover_color: { group: 'venue', property: 'color', pseudo: ':hover' },
        venue_hover_background_color: { group: 'venue', property: 'background-color', pseudo: ':hover' },
        view_details_color: { group: 'view_details', property: 'color', pseudo: '' },
        view_details_background_color: { group: 'view_details', property: 'background-color', pseudo: '' },
        view_details_hover_color: { group: 'view_details', property: 'color', pseudo: ':hover' },
        view_details_hover_background_color: { group: 'view_details', property: 'background-color', pseudo: ':hover' },
        google_calendar_color: { group: 'google_calendar', property: 'color', pseudo: '' },
        google_calendar_background_color: { group: 'google_calendar', property: 'background-color', pseudo: '' },
        google_calendar_hover_color: { group: 'google_calendar', property: 'color', pseudo: ':hover' },
        google_calendar_hover_background_color: { group: 'google_calendar', property: 'background-color', pseudo: ':hover' },
    };

    const popupDetailElementTargets = {
        title: ['.teca-popup-detail-title'],
        category: [
            '.teca-popup-detail-category',
            '.teca-popup-detail-category a',
            '.teca-popup-detail-categories',
            '.teca-popup-detail-categories .teca-popup-detail-category',
            '.teca-popup-detail-categories a',
        ],
        tag: [
            '.teca-popup-detail-tag',
            '.teca-popup-detail-tag a',
            '.teca-popup-detail-tags',
            '.teca-popup-detail-tags .teca-popup-detail-tag',
            '.teca-popup-detail-tags a',
        ],
        venue_title: ['.teca-popup-detail-venue-title'],
        venue_value: ['.teca-popup-detail-venue-value'],
        organizer_title: ['.teca-popup-detail-organizer-title'],
        organizer_value: ['.teca-popup-detail-organizer-value'],
        organizer_phone: ['.teca-popup-detail-organizer-phone', '.teca-popup-detail-organizer-phone a'],
        organizer_website: ['.teca-popup-detail-organizer-website', '.teca-popup-detail-organizer-website a'],
        organizer_email: ['.teca-popup-detail-organizer-email', '.teca-popup-detail-organizer-email a'],
        excerpt: ['.teca-popup-detail-excerpt'],
        date: ['.teca-popup-detail-date'],
        time: ['.teca-popup-detail-time'],
        cost: ['.teca-popup-detail-cost'],
        location: ['.teca-popup-detail-location'],
        address: ['.teca-popup-detail-address'],
        details: ['.teca-popup-detail-description', '.teca-popup-detail-details'],
        view_details_button: [
            '.teca-popup-button.teca-view-details',
            '.teca-popup-actions .teca-view-details',
            '.teca-popup-actions .gs-teca-btn-link',
            '.teca-single-button',
            '.teca-single-button.teca-event-button',
        ],
        google_calendar_button: [
            '.teca-popup-actions .teca-google-calendar-btn',
            '.teca-google-calendar-btn--popup',
            '.teca-google-calendar-btn--single',
            '.teca-google-calendar-actions--single .teca-google-calendar-btn',
        ],
        event_website_button: [
            '.teca-event-website-btn',
            '.teca-popup-website-link',
            '.teca-popup-actions .teca-event-website-btn',
            '.teca-popup-actions .teca-popup-website-link',
            '.teca-popup-button.teca-event-website-btn',
            '.teca-single-website-link',
            '.teca-single-element-website a',
        ],
    };

    const popupDetailColorFieldMap = {
        popup_detail_title_color: { property: 'color', pseudo: '', selectors: popupDetailElementTargets.title },
        popup_detail_category_color: { property: 'color', pseudo: '', selectors: popupDetailElementTargets.category },
        popup_detail_category_background_color: { property: 'background-color', pseudo: '', selectors: ['.teca-popup-detail-category', '.teca-popup-detail-categories .teca-popup-detail-category'] },
        popup_detail_category_hover_color: { property: 'color', pseudo: ':hover', selectors: ['.teca-popup-detail-category', '.teca-popup-detail-categories .teca-popup-detail-category'] },
        popup_detail_tag_color: { property: 'color', pseudo: '', selectors: popupDetailElementTargets.tag },
        popup_detail_tag_background_color: { property: 'background-color', pseudo: '', selectors: ['.teca-popup-detail-tag', '.teca-popup-detail-tags .teca-popup-detail-tag'] },
        popup_detail_tag_hover_color: { property: 'color', pseudo: ':hover', selectors: ['.teca-popup-detail-tag', '.teca-popup-detail-tags .teca-popup-detail-tag'] },
        popup_detail_venue_title_color: { property: 'color', pseudo: '', selectors: popupDetailElementTargets.venue_title },
        popup_detail_venue_value_color: { property: 'color', pseudo: '', selectors: popupDetailElementTargets.venue_value },
        popup_detail_organizer_title_color: { property: 'color', pseudo: '', selectors: popupDetailElementTargets.organizer_title },
        popup_detail_organizer_value_color: { property: 'color', pseudo: '', selectors: popupDetailElementTargets.organizer_value },
        popup_detail_organizer_phone_color: { property: 'color', pseudo: '', selectors: popupDetailElementTargets.organizer_phone },
        popup_detail_organizer_phone_hover_color: { property: 'color', pseudo: ':hover', selectors: ['.teca-popup-detail-organizer-phone a'] },
        popup_detail_organizer_website_color: { property: 'color', pseudo: '', selectors: popupDetailElementTargets.organizer_website },
        popup_detail_organizer_website_hover_color: { property: 'color', pseudo: ':hover', selectors: ['.teca-popup-detail-organizer-website a'] },
        popup_detail_organizer_email_color: { property: 'color', pseudo: '', selectors: popupDetailElementTargets.organizer_email },
        popup_detail_organizer_email_hover_color: { property: 'color', pseudo: ':hover', selectors: ['.teca-popup-detail-organizer-email a'] },
        popup_detail_excerpt_color: { property: 'color', pseudo: '', selectors: popupDetailElementTargets.excerpt },
        popup_detail_date_color: { property: 'color', pseudo: '', selectors: popupDetailElementTargets.date },
        popup_detail_time_color: { property: 'color', pseudo: '', selectors: popupDetailElementTargets.time },
        popup_detail_cost_color: { property: 'color', pseudo: '', selectors: popupDetailElementTargets.cost },
        popup_detail_location_color: { property: 'color', pseudo: '', selectors: popupDetailElementTargets.location },
        popup_detail_address_color: { property: 'color', pseudo: '', selectors: popupDetailElementTargets.address },
        popup_detail_details_color: { property: 'color', pseudo: '', selectors: popupDetailElementTargets.details },
        popup_detail_view_details_button_color: { property: 'color', pseudo: '', selectors: popupDetailElementTargets.view_details_button },
        popup_detail_view_details_button_background_color: { property: 'background-color', pseudo: '', selectors: popupDetailElementTargets.view_details_button },
        popup_detail_view_details_button_hover_color: { property: 'color', pseudo: ':hover', selectors: popupDetailElementTargets.view_details_button },
        popup_detail_view_details_button_hover_background_color: { property: 'background-color', pseudo: ':hover', selectors: popupDetailElementTargets.view_details_button },
        popup_detail_google_calendar_button_color: { property: 'color', pseudo: '', selectors: popupDetailElementTargets.google_calendar_button },
        popup_detail_google_calendar_button_background_color: { property: 'background-color', pseudo: '', selectors: popupDetailElementTargets.google_calendar_button },
        popup_detail_google_calendar_button_hover_color: { property: 'color', pseudo: ':hover', selectors: popupDetailElementTargets.google_calendar_button },
        popup_detail_google_calendar_button_hover_background_color: { property: 'background-color', pseudo: ':hover', selectors: popupDetailElementTargets.google_calendar_button },
        popup_detail_event_website_button_color: { property: 'color', pseudo: '', selectors: popupDetailElementTargets.event_website_button },
        popup_detail_event_website_button_background_color: { property: 'background-color', pseudo: '', selectors: popupDetailElementTargets.event_website_button },
        popup_detail_event_website_button_hover_color: { property: 'color', pseudo: ':hover', selectors: popupDetailElementTargets.event_website_button },
        popup_detail_event_website_button_hover_background_color: { property: 'background-color', pseudo: ':hover', selectors: popupDetailElementTargets.event_website_button },
    };

    const build_popup_detail_scoped_selectors = function(scopeClass, selectors) {
        const output = [];
        const shortcodeId = scopeClass.replace('gs_teca_popup_shortcode_', '');

        if (!scopeClass || !selectors || !selectors.length) {
            return output;
        }

        const roots = [
            `.${scopeClass}`,
            `.mfp-wrap .${scopeClass}`,
            `.mfp-container .${scopeClass}`,
            `.mfp-content .${scopeClass}`,
            `#gs_teca_area_${shortcodeId} .${scopeClass}`,
        ];

        roots.forEach(root => {
            selectors.forEach(selector => {
                output.push(`${root} ${selector}`);
            });
        });

        return output;
    };

    const apply_popup_detail_typography = function(group, presetControls, overrides, fieldCustom, context) {
        const styleId = `gsteca--popup_detail_typography_${group}_style`;
        const scopeClass = (context && context.popup_detail_scope_class) || '';

        $(`#${styleId}`).remove();

        if (!scopeClass || !typography_group_has_custom(fieldCustom)) {
            return;
        }

        const targets = popupDetailElementTargets[group] || [];
        const merged = merge_typography_controls(presetControls, overrides, fieldCustom);
        const rules = build_typography_rules(merged, fieldCustom);

        if (!rules || !targets.length) {
            return;
        }

        const selectors = build_popup_detail_scoped_selectors(scopeClass, targets);

        if (!selectors.length) {
            return;
        }

        $('head').append(`<style id="${styleId}">${selectors.join(',')}{${rules}}</style>`);
    };

    const apply_popup_detail_color_field = function(fieldKey, value, context) {
        const styleId = `gsteca--popup_detail_color_${fieldKey}_style`;
        const scopeClass = (context && context.popup_detail_scope_class) || '';
        const meta = popupDetailColorFieldMap[fieldKey];

        $(`#${styleId}`).remove();

        if (!scopeClass || !value || !meta || !meta.selectors || !meta.selectors.length) {
            return;
        }

        const selectors = build_popup_detail_scoped_selectors(scopeClass, meta.selectors).map(base => {
            return meta.pseudo ? `${base}${meta.pseudo}` : base;
        });

        if (!selectors.length) {
            return;
        }

        $('head').append(`<style id="${styleId}">${selectors.join(',')}{${meta.property}:${value} !important}</style>`);
    };

    const maybe_load_font = function(font) {
        if (!font) return;

        const fontName = font.split(',')[0].replace(/['"]/g, '').trim();
        const href = `https://fonts.googleapis.com/css2?family=${fontName.replace(/ /g, '+')}&display=swap`;

        if (!$(`link[href="${href}"]`).length) {
            $('head').append(`<link href="${href}" rel="stylesheet" />`);
        }
    };

    const format_font_family = function(font) {
        if (!font) return '';
        if (font.indexOf(',') > -1) return font;
        return `"${font}", sans-serif`;
    };

    const format_size = function(size) {
        if (size === '' || size === null || typeof size === 'undefined') return '';
        if (typeof size === 'object' && size !== null) {
            const value = size.size;
            const unit = size.unit || 'px';
            if (value === '' || value === null || typeof value === 'undefined') return '';
            return `${value}${unit}`;
        }
        if (!isNaN(size)) return `${size}px`;
        return `${size}`;
    };

    const format_letter_spacing = function(spacing) {
        if (spacing === '' || spacing === null || typeof spacing === 'undefined') return '';
        if (typeof spacing === 'object' && spacing !== null) {
            const value = spacing.size;
            const unit = spacing.unit || 'px';
            if (value === '' || value === null || typeof value === 'undefined') return '';
            return `${value}${unit}`;
        }
        if (!isNaN(spacing)) return `${spacing}px`;
        return `${spacing}`;
    };

    const pick_typography_value = function(source, keys) {
        if (!source || typeof source !== 'object') return '';

        for (let i = 0; i < keys.length; i++) {
            const key = keys[i];
            if (!Object.prototype.hasOwnProperty.call(source, key)) continue;

            const value = source[key];
            if (value === null || typeof value === 'undefined') continue;
            if (value === '') continue;

            return value;
        }

        return '';
    };

    const normalize_typography_value = function(settings) {
        if (!settings || typeof settings !== 'object') {
            return {
                font_family: '',
                font_size: '',
                font_weight: '',
                line_height: '',
                letter_spacing: '',
                text_transform: '',
                text_decoration: '',
                font_style: '',
            };
        }

        return {
            font_family: pick_typography_value(settings, ['getFonts', 'font_family', 'font-family', 'fontFamily']),
            font_size: pick_typography_value(settings, ['size', 'font_size', 'font-size', 'fontSize', 'typography_size']),
            font_weight: pick_typography_value(settings, ['weight', 'font_weight', 'font-weight', 'fontWeight']),
            line_height: pick_typography_value(settings, ['lineHeight', 'line_height', 'line-height']),
            letter_spacing: pick_typography_value(settings, ['letterSpacing', 'letter_spacing', 'letter-spacing']),
            text_transform: pick_typography_value(settings, ['transform', 'text_transform', 'text-transform', 'textTransform']),
            text_decoration: pick_typography_value(settings, ['decoration', 'text_decoration', 'text-decoration', 'textDecoration']),
            font_style: pick_typography_value(settings, ['style', 'font_style', 'font-style', 'fontStyle']),
        };
    };

    const typography_group_has_custom = function(fieldCustom) {
        if (!fieldCustom || typeof fieldCustom !== 'object') {
            return false;
        }

        return Object.values(fieldCustom).some(flag => !!flag);
    };

    const merge_typography_controls = function(presetControls, overrides, fieldCustom) {
        const merged = Object.assign({}, presetControls || {});

        Object.keys(controlToFieldMap).forEach(controlKey => {
            const field = controlToFieldMap[controlKey];

            if (!fieldCustom || !fieldCustom[field]) {
                return;
            }

            if (!overrides || overrides[controlKey] === undefined || overrides[controlKey] === '' || overrides[controlKey] === null) {
                return;
            }

            merged[controlKey] = overrides[controlKey];
        });

        return merged;
    };

    const build_typography_rules = function(settings, fieldCustom) {
        const typo = normalize_typography_value(settings);
        const rules = [];

        const maybe = function(field, rule) {
            if (fieldCustom && !fieldCustom[field]) {
                return;
            }
            rules.push(rule);
        };

        if (typo.font_family) {
            maybe('font_family', `font-family:${format_font_family(typo.font_family)}`);
            maybe_load_font(typo.font_family);
        }
        if (typo.font_size) maybe('font_size', `font-size:${format_size(typo.font_size)} !important`);
        if (typo.font_weight) maybe('font_weight', `font-weight:${typo.font_weight} !important`);
        if (typo.line_height) maybe('line_height', `line-height:${typo.line_height}`);
        if (typo.letter_spacing) maybe('letter_spacing', `letter-spacing:${format_letter_spacing(typo.letter_spacing)}`);
        if (typo.text_transform) maybe('text_transform', `text-transform:${typo.text_transform}`);
        if (typo.text_decoration !== '' && typo.text_decoration !== null && typeof typo.text_decoration !== 'undefined') {
            maybe('text_decoration', `text-decoration:${typo.text_decoration} !important`);
        }
        if (typo.font_style) maybe('font_style', `font-style:${typo.font_style}`);

        return rules.join(';');
    };

    const get_preview_area = function(context) {
        if (context && context.previewId) {
            const $scoped = $(`#gs_teca_area_${context.previewId}`);
            if ($scoped.length) {
                return $scoped;
            }
        }

        return $('body .gs_teca_area').first();
    };

    const build_layout_scoped_selectors = function(instanceId, scopeClass, selectors) {
        const output = [];
        const root = `${instanceId ? `#${instanceId}` : '.gs_teca_area'}.${scopeClass}`;

        selectors.forEach(selector => {
            output.push(`${root} ${selector}`);
        });

        return output;
    };

    const apply_title_font_preset = function(presetControls, overrides, fieldCustom, context) {
        const styleId = 'gsteca--typography_title_preset_style';
        const $area = get_preview_area(context);

        $(`#${styleId}`).remove();

        if (!$area.length) {
            return;
        }

        const preset = presetControls || {};
        let font = preset.getFonts || 'Arimo';

        if (fieldCustom && fieldCustom.font_family && overrides && overrides.getFonts) {
            font = overrides.getFonts;
        }

        if (!font) {
            return;
        }

        maybe_load_font(font);

        const scopeClass = (context && context.scopeClass) || '';
        const instanceId = $area.attr('id') || '';
        const targets = elementTargets.title || [];
        const rules = `font-family:${format_font_family(font)}`;

        if (!rules || !scopeClass || !targets.length) {
            return;
        }

        $area.addClass(scopeClass);

        const selectors = build_layout_scoped_selectors(instanceId, scopeClass, targets);

        if (!selectors.length) {
            return;
        }

        $('head').append(`<style id="${styleId}">${selectors.join(',')}{${rules}}</style>`);
    };

    const apply_typography = function(messageKey, presetControls, overrides, fieldCustom, context) {
        const group = customClassMap[messageKey];
        const styleId = `gsteca--typography_${messageKey}_style`;
        const $area = get_preview_area(context);

        if (!group || !$area.length) return;

        $(`#${styleId}`).remove();

        if (!typography_group_has_custom(fieldCustom)) {
            return;
        }

        const scopeClass = (context && context.scopeClass) || '';
        const instanceId = $area.attr('id') || '';
        const targets = elementTargets[group] || [];
        const merged = merge_typography_controls(presetControls, overrides, fieldCustom);
        const rules = build_typography_rules(merged, fieldCustom);

        if (!rules || !scopeClass || !targets.length) return;

        $area.addClass(scopeClass);

        const selectors = build_layout_scoped_selectors(instanceId, scopeClass, targets);

        if (!selectors.length) return;

        $('head').append(`<style id="${styleId}">${selectors.join(',')}{${rules}}</style>`);
    };

    const get_color_element_targets = function(meta, fieldKey) {
        const scoped = colorElementTargets[meta.group];

        if (scoped) {
            if (meta.property === 'background-color') {
                return scoped.background;
            }

            return scoped.color;
        }

        return elementTargets[meta.group] || [];
    };

    const apply_color_field = function(fieldKey, value, context) {
        const meta = colorFieldMap[fieldKey];
        const styleId = `gsteca--color_${fieldKey}_style`;
        const $area = get_preview_area(context);

        if (!meta || !$area.length) return;

        $(`#${styleId}`).remove();

        if (!value) {
            return;
        }

        const scopeClass = (context && context.scopeClass) || '';
        const instanceId = $area.attr('id') || '';
        const targets = get_color_element_targets(meta, fieldKey);
        const selectors = build_layout_scoped_selectors(instanceId, scopeClass, targets).map(base => {
            if (meta.pseudo) {
                return `${base}${meta.pseudo}`;
            }
            return base;
        });

        if (!selectors.length || !scopeClass) return;

        $area.addClass(scopeClass);

        $('head').append(`<style id="${styleId}">${selectors.join(',')}{${meta.property}:${value} !important}</style>`);
    };

    const clear_style_state = function(context) {
        $('[id^="gsteca--typography_"]').remove();
        $('[id^="gsteca--color_"]').remove();
        $('[id^="gsteca--popup_detail_"]').remove();
    };

    const apply_style_preview = function(data) {
        if (!data || typeof data !== 'object') {
            return;
        }

        clear_style_state(data);

        const preset = data.design_preset || {};
        const presetTypography = preset.typography || {};
        const fieldCustomMap = data.typography_field_custom || {};
        const overridesMap = data.typography_overrides || {};

        apply_title_font_preset(
            presetTypography.title || {},
            overridesMap.title_typography || {},
            fieldCustomMap.title || {},
            data
        );

        Object.keys(varMap).forEach(key => {
            const group = customClassMap[key];
            apply_typography(
                key,
                presetTypography[group] || {},
                overridesMap[key] || {},
                fieldCustomMap[key] || {},
                data
            );
        });

        const colorFields = data.color_fields || {};

        Object.keys(colorFieldMap).forEach(fieldKey => {
            if (Object.prototype.hasOwnProperty.call(colorFields, fieldKey)) {
                apply_color_field(fieldKey, colorFields[fieldKey], data);
            }
        });

        if (data.popup_detail_scope_class) {
            const popupPreset = data.popup_detail_design_preset || {};
            const popupTypographyPreset = popupPreset.typography || {};
            const popupFieldCustomMap = data.popup_detail_typography_field_custom || {};
            const popupOverridesMap = data.popup_detail_typography_overrides || {};

            Object.keys(popupDetailElementTargets).forEach(group => {
                apply_popup_detail_typography(
                    group,
                    popupTypographyPreset[group] || {},
                    popupOverridesMap[group] || {},
                    popupFieldCustomMap[group] || {},
                    data
                );
            });

            const popupColorFields = data.popup_detail_color_fields || {};

            Object.keys(popupDetailColorFieldMap).forEach(fieldKey => {
                if (Object.prototype.hasOwnProperty.call(popupColorFields, fieldKey)) {
                    apply_popup_detail_color_field(fieldKey, popupColorFields[fieldKey], data);
                }
            });
        }
    };

    window.gstecaApplyTypographyStyles = apply_style_preview;

    window.addEventListener('message', event => {
        apply_style_preview(event.data);
    });

})(jQuery);
