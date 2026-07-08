const GS_TECA_DATA = window._gsteca_data || {};

const config = Object.assign({}, GS_TECA_DATA);

const translations = Object.assign({}, GS_TECA_DATA.translations || {});

delete GS_TECA_DATA.nonce;
delete GS_TECA_DATA.siteurl;
delete GS_TECA_DATA.ajaxurl;
delete GS_TECA_DATA.adminurl;
delete GS_TECA_DATA.shortcode_settings;
delete GS_TECA_DATA.shortcode_options;
delete GS_TECA_DATA.preference;
delete GS_TECA_DATA.preference_options;
delete GS_TECA_DATA.translations;

import notify from './notify';

const helpers = {

	copyShortcodeToClipboard() {

		new Clipboard('.copy-holder', {
			target(trigger) {
				return jQuery(trigger).parent().find('.shortcode-input')[0];
			}
		}).on('success', function(e) {
			e.clearSelection();
		});

		jQuery(this.$el).delegate('.shortcode-input', 'click', function(event) {
			jQuery(this).select();
		});

	},

	getDemoDataStatus() {
		return GS_TECA_DATA.demo_data;
	},

	_updateDemoDataStatus( data = {} ) {
		GS_TECA_DATA.demo_data = Object.assign({}, GS_TECA_DATA.demo_data, data);
	},

	_getShortcodeSettings() {
		return config.shortcode_settings;
	},

	initHelpText() {
		jQuery(this.$el).on( 'click', '.gs-teca-show--info', function() {
			jQuery(this).closest('.shortcode-setting--row').find('.bi-text-help--area').slideToggle(250).end().siblings().find('.bi-text-help--area').slideUp(250);
		});
	},

	_getShortcodeOptions() {
		return config.shortcode_options;
	},

	_getPreference() {
		return config.preference;
	},

	_getPreferenceOptions() {
		return config.preference_options;
	},

	_getLayout() {
		return config.layout;
	},

	_getLayoutOptions() {
		return config.layout_options;
	},  
			
	convertBooleanToString( val ) {

		return val === true ? 'on' : 'off'

	},
			
	convertStringToBoolean( val ) {

		return val === 'on' ? true : false;

	},
	
	getSiteURL() {
		return config.siteurl;
	},
	
	getWPNonce( action ) {
		return config.nonce[action];
	},
	
	getAjaxURL() {
		return config.ajaxurl;
	},
	
	getAdminURL() {
		return config.adminurl;
	},

	isEmptyObject( data ) {

		for ( var prop in data ) {
			return !data.hasOwnProperty(prop);
		}

		return JSON.stringify(data) === JSON.stringify({});

	},

	isArray( data ) {
		return typeof data && Array.isArray(data);
	},

	isObject( data ) {
		return typeof data && !Array.isArray(data);
	},

	nonReactive(data) {
		return JSON.parse( JSON.stringify( data ) );
	},

	ltrim( str, charlist ) {

		charlist = !charlist ? ' \\s\u00A0' : (charlist + '').replace(/([[\]().?/*{}+$^:])/g, '$1');

		let re = new RegExp('^[' + charlist + ']+', 'g');

		return (str + '').replace(re, '');

	},

	notifyError( response ) {

		if ( response && response.responseJSON && response.responseJSON.data ) {
			return notify({
				message: response.responseJSON.data,
				clearAll: true
			});			
		}

		notify({
			clearAll: true
		});

	},

	translation( key = null ) {

		if ( key && key in translations ) {
			return translations[key];
		}

		return '';

	},

	getFonts() {
		return GS_TECA_DATA.fonts_data || [];
	}
	
}

export default helpers;