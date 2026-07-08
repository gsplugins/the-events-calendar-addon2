<template>
	<div class="gs-containeer shortcodes-container">

		<div class="gs-teca-box" v-if="!processing">
			
			<div class="top-section head-section">
				<h2>{{translation('install-demo-data')}}</h2>
				<p>{{translation('install-demo-data-description')}}</p>
			</div>

			<hr>

			<div class="bottom-section">

				<div v-if="mode == 'all'">

					<div class="demo-data--import-section m-b-30">

						<h3>Import All Data</h3>
						<p>Following data will get imported:</p>

						<ul class="ul-list">
							<li>6 Events</li>
							<li>6 Attachments for Events</li>
							<li>Taxonomies: Category, Tag</li>
							<li>6 Premade Shortcodes will be created</li>
						</ul>

						<button class="btn btn-brand btn-sm m-t-6 m-r-5" @click.prevent.stop="importAllData" v-if="!eventImported || !shortcodeImported">
							<i class="zmdi zmdi-cloud-download"></i>
							<span>Import Now</span>
						</button>

						<div class="btn btn-success btn-sm m-t-6 m-r-5" v-if="eventImported && shortcodeImported">
							<i class="zmdi zmdi-cloud-done"></i>
							<span>Already Imported</span>
						</div>

						<button class="btn btn-red btn-sm m-t-6" @click.prevent.stop="removeAllData" :disabled="!eventImported && !shortcodeImported">
							<i class="zmdi zmdi-delete"></i>
							<span>Remove Data</span>
						</button>
					
					</div>

					<div class="import-manually">
						<a href="#" @click.prevent="mode='manual'">Import Manually</a>
					</div>

				</div>

				<div v-else>

					<div class="demo-data--import-section m-b-30">

						<h3>Import Events</h3>
						<p>Following data will get imported:</p>

						<ul class="ul-list">
							<li>12 Events</li>
							<li>12 Attachments for Events</li>
							<li>Taxonomies: Category, Tag</li>
						</ul>

						<button class="btn btn-brand btn-sm m-t-6 m-r-5" @click.prevent.stop="importEventData" v-if="!eventImported">
							<i class="zmdi zmdi-cloud-download"></i>
							<span>Import Now</span>
						</button>

						<div class="btn btn-success btn-sm m-t-6 m-r-5" v-if="eventImported">
							<i class="zmdi zmdi-cloud-done"></i>
							<span>Already Imported</span>
						</div>

						<button class="btn btn-red btn-sm m-t-6" @click.prevent.stop="removeEventData" :disabled="!eventImported">
							<i class="zmdi zmdi-delete"></i>
							<span>Remove Data</span>
						</button>
					
					</div>

					<hr>

					<div class="demo-data--import-section m-t-20 m-b-30">

						<h3>Import Prebuilt Shortcodes</h3>
						<p>Following data will get imported:</p>

						<ul class="ul-list">
							<li>20 Premade Shortcodes will be created</li>
						</ul>

						<button class="btn btn-brand btn-sm m-t-6 m-r-5" @click.prevent.stop="importEventShortcodes" v-if="!shortcodeImported">
							<i class="zmdi zmdi-cloud-download"></i>
							<span>Import Now</span>
						</button>

						<div class="btn btn-success btn-sm m-t-6 m-r-5" v-if="shortcodeImported">
							<i class="zmdi zmdi-cloud-done"></i>
							<span>Already Imported</span>
						</div>

						<button class="btn btn-red btn-sm m-t-6" @click.prevent.stop="removeEventShortcodes" :disabled="!shortcodeImported">
							<i class="zmdi zmdi-delete"></i>
							<span>Remove Data</span>
						</button>
					
					</div>

					<div class="import-manually">
						<a href="#" @click.prevent="mode='all'">Import All</a>
					</div>

				</div>

				<vue-confirm-dialog></vue-confirm-dialog>

			</div>
		</div>

		<div class="demo-data--processing" v-if="processing">
			<div class="gs-teca-box">
				<div class="demo-data--pro-wrapper">
					<h3>Processing..</h3>
					<p>Please wait until the process completed.</p>
					<div class="gs-teca-loader"></div>
				</div>
			</div>
		</div>

	</div>
</template>

<script>

	import notify from '../includes/notify';
	
	export default {

		data() {
			return {
				mode: 'all',
				processing: false,
				eventImported: false,
				shortcodeImported: false,
			}
		},

		mounted() {

			const demoDataStatus = this.getDemoDataStatus();

			this.eventImported = demoDataStatus.eevnt_data;
			this.shortcodeImported = demoDataStatus.shortcode_data;

		},

		computed: {

		},

		methods: {

			showLoader() {

				this.processing = true;

			},

			hideLoader() {

				this.processing = false;

			},

			updateDemoDataStatus() {

				this._updateDemoDataStatus({
					event_data: this.eventImported,
					shortcode_data: this.shortcodeImported
				});

			},

			importAllData() {

				this.ajax( 'gsteca_import_all_data' ).then( () => {
					this.eventImported = true;
					this.shortcodeImported = true;
					this.updateDemoDataStatus();
				});

			},

			removeAllData() {

				this.$confirm({
					title: 'Are you sure?',
					message: 'This action will delete all unmodified shortcodes & Portfolios including attachments & taxonomies, that inserted by gsportfolio dummy data importer',
					button: {
						yes: 'Yes',
						no: 'Cancel'
					},
					callback: confirm => {
						
						if ( ! confirm ) return;

						this.ajax( 'gsteca_remove_all_data' ).then( () => {
							this.eventImported = false;
							this.shortcodeImported = false;
							this.updateDemoDataStatus();
						});

					}
				});
				
			},

			importEventData() {

				this.ajax( 'gsteca_import_teca_data' ).then( () => {
					this.eventImported = true;
					this.updateDemoDataStatus();
				});

			},

			removeEventData() {

				this.$confirm({
					title: 'Are you sure?',
					message: 'This action will delete all unmodified portfolios including attachments & taxonomies, that inserted by gsportfolio dummy data importer',
					button: {
						yes: 'Yes',
						no: 'Cancel'
					},
					callback: confirm => {
						
						if ( ! confirm ) return;

						this.ajax( 'gsteca_remove_teca_data' ).then( () => {
							this.eventImported = false;
							this.updateDemoDataStatus();
						});

					}
				});

			},

			importEventShortcodes() {

				this.ajax( 'gsteca_import_shortcode_data' ).then( () => {
					this.shortcodeImported = true;
					this.updateDemoDataStatus();
				});

			},

			removeEventShortcodes() {

				this.$confirm({
					title: 'Are you sure?',
					message: 'This action will delete all unmodified shortcodes, that inserted by gsportfolio dummy data importer',
					button: {
						yes: 'Yes',
						no: 'Cancel'
					},
					callback: confirm => {
						
						if ( ! confirm ) return;

						this.ajax( 'gsteca_remove_shortcode_data' ).then( () => {
							this.shortcodeImported = false;
							this.updateDemoDataStatus();
						});

					}
				});

			},

			ajax( action ) {
				
				return new Promise( (resolve, reject) => {
					
					this.showLoader();

					jQuery.ajax({
						url: this.getAjaxURL(),
						type: 'POST',
						cache: false,
						data: {
							action: action,
							_wpnonce: this.getWPNonce('import_gsteca_demo')
						}
					})
					.done( (response, status, settings) => {

						resolve();

						if ( this.mode == 'all' ) {
							for ( let data in response.data ) {
								notify({
									type: response.data[data].status > 200 ? 'info' : 'success',
									message: response.data[data].message
								});
							}
							return;
						}

						if ( response.success && settings.status > 200 && response.data ) {
							notify({
								type: 'info',
								message: response.data
							});
						}

						if ( response.success && settings.status == 200 && response.data ) {
							notify({
								type: 'success',
								message: response.data
							});
						}

					})
					.error( response => {

						reject();
						
						notify({
							message: 'Something is wrong! Please try again later'
						});

					})
					.always( response => {

						this.hideLoader();

					});
					
				});

			},

		},

		watch: {

		}

	}
</script>