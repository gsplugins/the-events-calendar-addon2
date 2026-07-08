// require('../libs/color-picker/color-picker.js');
require('./includes/polyfills.js');

import Vue from 'vue';
import VueRouter from 'vue-router';
import helpers from './includes/helpers';
import Clipboard from 'clipboard';
import VueConfirmDialog from 'vue-confirm-dialog';
import draggable from 'vuedraggable';

window.Clipboard = Clipboard;

// Pages
import Shortcodes from './pages/shortcodes.vue';
import Shortcode from './pages/shortcode.vue';
import Preferences from './pages/preferences.vue';
import Layout from './pages/layout.vue';
import DemoData from './pages/demo-data.vue';

// global use
Vue.use(VueRouter);
Vue.use(VueConfirmDialog);

if ( window.vuedraggable ) {
    Vue.component('draggable', window.vuedraggable);
} else {
    console.warn("VueDraggable library not found! Check if CDN is loaded.");
}

window.Events = new Vue({});

Vue.mixin({
	methods: helpers
});

Vue.component( 'input-tag', 		require('./components/input-tag.vue').default );
Vue.component( 'input-increment', 	require('./components/input-increment.vue') .default );
Vue.component( 'input-range', 		require('./components/input-range.vue').default );
Vue.component( 'input-checkbox', 	require('./components/input-checkbox.vue').default );
Vue.component( 'input-radio', 		require('./components/input-radio.vue').default );
Vue.component( 'input-select', 		require('./components/input-select/component/select.vue').default );
Vue.component( 'input-color', 		require('./components/input-color/input-color.vue').default );
Vue.component( 'input-toggle', 		require('./components/input-toggle.vue').default );
Vue.component( 'editor-cm', 		require('./components/editor-codemirror/component/editor-cm.vue').default );
Vue.component( 'typography',        require('./components/typography.vue').default );
Vue.component( 'color-field',       require('./components/color-field.vue').default );
Vue.component( 'vue-confirm-dialog', VueConfirmDialog.default );
Vue.component('draggable', draggable);


jQuery(function($){

	const routes = [
		{ path: '/', 				component: Shortcodes },
		{ path: '/shortcode', 		component: Shortcode },
		{ path: '/shortcode/:id', 	component: Shortcode },
		{ path: '/preferences', 	component: Preferences },
		{ path: '/layout',      	component: Layout },
		{ path: '/demo-data',      	component: DemoData }
	];

	const router = new VueRouter({ mode: 'hash', routes });

	if ( $('#gs-teca-shortcode-app').length > 0 ) {
		window.app = new Vue({
			router
		}).$mount('#gs-teca-shortcode-app');
	}

});