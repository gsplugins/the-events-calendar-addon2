<template>
	<div class="gs-containeer shortcodes-container">
		<div class="gs-teca-box">

			<div class="top-section head-section">
				<h2>{{translation('preference')}}</h2>
				<p>{{translation('global-settings-for-teca')}}</p>
			</div>

			<div class="bottom-section" v-if="Object.keys(preference).length">

				<div class="shortcode-setting--row">

					<div class="gs-roow row-20">

						<div class="gs-col-xs-6 gs-col-sm-5 gs-col-md-4">
							<label class="m-t-10" for="gs_teca_enable_multilingual">{{translation('enable-multilingual')}}:</label>
							<button class="gs-teca-show--info"><i class="zmdi zmdi-help-outline"></i></button>
						</div>

						<div class="gs-col-xs-6 gs-col-sm-6 gs-col-md-5">
							<input-toggle class="m-t-6" name="gs_teca_enable_multilingual" v-model="preference.gs_teca_enable_multilingual" offLabel="Off" onLabel="On"></input-toggle>
						</div>

						<div class="gs-col-xs-12 bi-text-help--area">
							<p class="bi-text-help">{{translation('enable-multilingual--details')}}</p>
						</div>

					</div>

				</div>

				<div class="shortcode-setting--row" v-if="! preference.gs_teca_enable_multilingual">

					<div class="gs-roow row-20">

						<div class="gs-col-xs-6 gs-col-sm-5 gs-col-md-4">
							<label class="m-t-10" for="gs_teca_more">{{translation('pref-more')}}:</label>
							<button class="gs-teca-show--info"><i class="zmdi zmdi-help-outline"></i></button>
						</div>

						<div class="gs-col-xs-6 gs-col-sm-6 gs-col-md-5">
							<input type="text" class="bi-input-control" name="gs_teca_more" v-model="preference.gs_teca_more" />
						</div>

						<div class="gs-col-xs-12 bi-text-help--area">
							<p class="bi-text-help">{{translation('pref-more-details')}}</p>
						</div>

					</div>
				
				</div>

				<div class="shortcode-setting--row" v-if="! preference.gs_teca_enable_multilingual">

					<div class="gs-roow row-20">

						<div class="gs-col-xs-6 gs-col-sm-5 gs-col-md-4">
							<label class="m-t-10" for="gs_teca_prev_txt">{{translation('prev')}}:</label>
							<button class="gs-teca-show--info"><i class="zmdi zmdi-help-outline"></i></button>
						</div>

						<div class="gs-col-xs-6 gs-col-sm-6 gs-col-md-5">
							<input type="text" class="bi-input-control" name="gs_teca_prev_txt" v-model="preference.gs_teca_prev_txt" />
						</div>

						<div class="gs-col-xs-12 bi-text-help--area">
							<p class="bi-text-help">{{translation('prev-details')}}</p>
						</div>

					</div>
				
				</div>

				<div class="shortcode-setting--row" v-if="! preference.gs_teca_enable_multilingual">

					<div class="gs-roow row-20">

						<div class="gs-col-xs-6 gs-col-sm-5 gs-col-md-4">
							<label class="m-t-10" for="gs_teca_next_txt">{{translation('next')}}:</label>
							<button class="gs-teca-show--info"><i class="zmdi zmdi-help-outline"></i></button>
						</div>

						<div class="gs-col-xs-6 gs-col-sm-6 gs-col-md-5">
							<input type="text" class="bi-input-control" name="gs_teca_next_txt" v-model="preference.gs_teca_next_txt" />
						</div>

						<div class="gs-col-xs-12 bi-text-help--area">
							<p class="bi-text-help">{{translation('next-details')}}</p>
						</div>

					</div>
				
				</div>

				<div class="shortcode-setting--row" v-if="! preference.gs_teca_enable_multilingual">

					<div class="gs-roow row-20">

						<div class="gs-col-xs-6 gs-col-sm-5 gs-col-md-4">
							<label class="m-t-10" for="gs_teca_view_details_text">{{translation('pref-view-details')}}:</label>
							<button class="gs-teca-show--info"><i class="zmdi zmdi-help-outline"></i></button>
						</div>

						<div class="gs-col-xs-6 gs-col-sm-6 gs-col-md-5">
							<input type="text" class="bi-input-control" name="gs_teca_view_details_text" v-model="preference.gs_teca_view_details_text" />
						</div>

						<div class="gs-col-xs-12 bi-text-help--area">
							<p class="bi-text-help">{{translation('pref-view-details-details')}}</p>
						</div>

					</div>

				</div>

				<div class="shortcode-setting--row" v-if="! preference.gs_teca_enable_multilingual">

					<div class="gs-roow row-20">

						<div class="gs-col-xs-6 gs-col-sm-5 gs-col-md-4">
							<label class="m-t-10" for="gs_teca_related_events_title">{{translation('pref-related-events-title')}}:</label>
							<button class="gs-teca-show--info"><i class="zmdi zmdi-help-outline"></i></button>
						</div>

						<div class="gs-col-xs-6 gs-col-sm-6 gs-col-md-5">
							<input type="text" class="bi-input-control" name="gs_teca_related_events_title" v-model="preference.gs_teca_related_events_title" />
						</div>

						<div class="gs-col-xs-12 bi-text-help--area">
							<p class="bi-text-help">{{translation('pref-related-events-title-details')}}</p>
						</div>

					</div>

				</div>

				<div class="shortcode-setting--row" v-if="! preference.gs_teca_enable_multilingual">

					<div class="gs-roow row-20">

						<div class="gs-col-xs-6 gs-col-sm-5 gs-col-md-4">
							<label class="m-t-10" for="gs_teca_event_website_text">{{translation('pref-event-website')}}:</label>
							<button class="gs-teca-show--info"><i class="zmdi zmdi-help-outline"></i></button>
						</div>

						<div class="gs-col-xs-6 gs-col-sm-6 gs-col-md-5">
							<input type="text" class="bi-input-control" name="gs_teca_event_website_text" v-model="preference.gs_teca_event_website_text" />
						</div>

						<div class="gs-col-xs-12 bi-text-help--area">
							<p class="bi-text-help">{{translation('pref-event-website-details')}}</p>
						</div>

					</div>

				</div>

				<div class="shortcode-setting--row" v-if="! preference.gs_teca_enable_multilingual">

					<div class="gs-roow row-20">

						<div class="gs-col-xs-6 gs-col-sm-5 gs-col-md-4">
							<label class="m-t-10" for="gs_teca_add_to_calendar_text">{{translation('pref-add-to-calendar')}}:</label>
							<button class="gs-teca-show--info"><i class="zmdi zmdi-help-outline"></i></button>
						</div>

						<div class="gs-col-xs-6 gs-col-sm-6 gs-col-md-5">
							<input type="text" class="bi-input-control" name="gs_teca_add_to_calendar_text" v-model="preference.gs_teca_add_to_calendar_text" />
						</div>

						<div class="gs-col-xs-12 bi-text-help--area">
							<p class="bi-text-help">{{translation('pref-add-to-calendar-details')}}</p>
						</div>

					</div>

				</div>

				<div class="shortcode-setting--row">

					<div class="gs-roow row-20">

						<div class="gs-col-xs-4">
							<label class="m-t-10" for="anchor_tag_rel">{{translation('anchor-tag-rel')}}:</label>
							<button class="gs-teca-show--info"><i class="zmdi zmdi-help-outline"></i></button>
						</div>

						<div class="gs-col-xs-5">
							<input-select key="anchor_tag_rel" id="anchor_tag_rel" v-model="preference.anchor_tag_rel" :options="preference_options.anchor_tag_rel"></input-select>
						</div>

						<div class="gs-col-xs-12 bi-text-help--area">
							<p class="bi-text-help">{{translation('anchor_tag_rel--details')}}</p>
						</div>

					</div>
				
				</div>

				<div class="shortcode-setting--row" style="border: none;">

					<div class="gs-roow row-20">

						<div class="gs-col-xs-12">
							<label class="m-t-10" for="gs_teca_custom_css">{{translation('custom-css')}}:</label>
						</div>

						<div class="gs-col-xs-9">
							<editor-cm id="gs_teca_custom_css" mode="css" name="gs_teca_custom_css" v-model="preference.gs_teca_custom_css"></editor-cm>
						</div>

					</div>
				
				</div>

				<button class="btn btn-brand btn-sm m-t-10" @click.prevent.stop="saveOrUpdatePreference"><i class="zmdi zmdi-floppy"></i><span>{{translation('save-preference')}}</span></button>

			</div>
		</div>
	</div>
</template>

<script>

	import notify from '../includes/notify';
	
	export default {

		data() {
			return {
				preference: {
					gs_teca_custom_css: ''
				},
				preference_options: {}
			}
		},

		mounted() {

			this.stopWatcher = false;
			this.setInitialSettings();
			this.get_preference();
			
			this.initHelpText();
			Events.$emit('editor-sm:update-value');

		},

		computed: {

		},

		methods: {

			getPreference( json = false ) {
				
				let preference = this.nonReactive( this.preference );

				for ( let field in preference ) {

					if ( typeof preference[field] === "boolean" ) {
						preference[field] = this.convertBooleanToString( preference[field] );
					}

				}

				if ( json ) return JSON.stringify(preference);

				return preference;

			},

			setInitialSettings() {

				this.$set( this, 'preference_options', this._getPreferenceOptions() );

				this.setPreference( this._getPreference() );

			},

			setPreference( settings ) {

				for ( let field in settings ) {

					if ( typeof settings[field] === "string" && (settings[field] === 'on' || settings[field] === 'off' ) ) {
						settings[field] = this.convertStringToBoolean( settings[field] );
					}

				}

				this.preference = Object.assign( {}, this.preference, settings );

			},  

			// get_preference() {

			// 	let self = this;

			// 	jQuery.ajax({
			// 		url: this.getAjaxURL(),
			// 		type: 'GET',
			// 		data: {
			// 			action: 'gs_posts_grid_get_shortcode_pref'
			// 		}
			// 	})
			// 	.done(function(response) {

			// 		if ( response && response.data && response.success ) {

			// 			const prefs = response.data;

			// 			for ( let prop in prefs ) {

			// 				if ( typeof prefs[prop] === "string" && (prefs[prop] === 'on' || prefs[prop] === 'off' ) ) {
			// 					prefs[prop] = self.convertStringToBoolean( prefs[prop] );
			// 				}


			// 			}

			// 		self.preference = Object.assign( {}, self.preference, prefs );
			// 		}

			// 	})
			// 	.error( response => {

			// 		// We shouldn't show the alert to visitors, so console is fine
			// 		console.error( response );

			// 	});

			// },

			get_preference() {
				let self = this;

				jQuery.ajax({
					url: this.getAjaxURL(),
					type: 'GET',
					data: {
						action: 'gsteca_get_shortcode_pref'
					}
				})
				.done(function(response) {
					if (response && response.data && response.success) {
						const prefs = response.data;

						// Convert 'on'/'off' strings to boolean
						for (let prop in prefs) {
							if (typeof prefs[prop] === 'string' && (prefs[prop] === 'on' || prefs[prop] === 'off')) {
								prefs[prop] = self.convertStringToBoolean(prefs[prop]);
							}
						}

						// Set entire preference object once
						self.preference = {
							...self.preference,
							...prefs,
							gs_teca_custom_css: prefs.gs_teca_custom_css || '' // Ensure it's always a string
						};
					}
				})
				.fail(function(response) {
					console.error(response);
				});
			},


			saveOrUpdatePreference() {

				let self = this;

				jQuery.ajax({
					url: this.getAjaxURL(),
					type: 'POST',
					cache: false,
					data: {
						action: 'gsteca_save_shortcode_pref',
						_wpnonce: this.getWPNonce('save_shortcode_pref'),
						prefs: this.getPreference()
					}
				})
				.done( response => {

					if ( response.success && response.data ) {
						notify({
							type: 'success',
							message: response.data
						});
					}

				})
				.error( response => {

					// We shouldn't show the alert to visitors, so console is fine
					console.error( response );

				});

			},

		}

	}
</script>