<template>
	<div class="gs-containeer shortcodes-container">
		<div class="gs-teca-box">
			<div class="top-section head-section">
				<h2>{{translation('shortcodes')}}</h2>
				<p>{{translation('all-shortcodes-for-gs-logo-slider')}}</p>
				<router-link to="/shortcode/" class="btn btn-brand btn-sm"><i class="zmdi zmdi-plus"></i><span>{{translation('create-new-shortcode')}}</span></router-link>
			</div>
			<div class="bottom-section">
				<table class="gs-teca-table">
					<tbody>
						<tr>
							<th style="width: 2%;"><input-checkbox name="selectShortcode" v-model="selectAll" @input="toggleAllSelection"></input-checkbox></th>
							<th style="width: 14%;">{{translation('name')}}</th>
							<th style="width: 15%;">{{translation('shortcode')}}</th>
							<th style="width: 25%;">{{translation('action')}}</th>
						</tr>

						<tr v-for="shortcode in shortcodes" :key="shortcode.id" :class="[shortcode.cloned ? 'is-last-cloned' : '']">
							<td class="row-checkbox"><input-checkbox name="selectShortcode" v-model="shortcode.selected" @input="detectSelection(shortcode)"></input-checkbox></td>
							<td>{{shortcode.shortcode_name}}</td>
							<td>
								<input type="text" class="shortcode-input" :value="shortcode.shortcode_text" readonly>
								<span :class="['copy-holder', shortcode.copied ? 'copied' : '']" @click.prevent="shortcodeUpdateCopy(shortcode)" v-text="shortcode.copied ? 'Copied' : 'Copy'"></span>
							</td>
							<td class="shortcode-actions">
								<router-link :to='"/shortcode/"+shortcode.id'><i class="zmdi zmdi-edit"></i><span class="hidden-xs">{{translation('edit')}}</span></router-link>
								<a href="#" class="shortcode-clone" @click.prevent.stop="shortcodeClone(shortcode)"><i class="zmdi zmdi-copy"></i><span class="hidden-xs">{{translation('clone')}}</span></a>
								<a href="#" class="shortcode-delete" @click.prevent.stop="shortcodeDelete(shortcode)"><i class="zmdi zmdi-delete"></i><span class="hidden-xs">{{translation('delete')}}</span></a>
							</td>
						</tr>
					</tbody>
					<tfoot v-if="anyShortcodeSelected">
						<tr class="border-bottom-none font-110">
							<td class="shortcode-actions p-t-30" colspan="4">
								<span class="m-r-20">{{translation('actions')}}: </span>
								<a href="#" class="shortcode-delete" @click.prevent.stop="shortcodesDelete"><i class="zmdi zmdi-delete"></i><span>{{translation('delete-all')}}</span></a>
							</td>
						</tr>
					</tfoot>
				</table>
	

			</div>
		</div>
	</div>
</template>

<script>

	import notify from '../includes/notify';

	export default {

		data() {
			return {
				selectAll: false,
				shortcodes: []
			}
		},

		mounted() {

			this.getShortcodes();

			this.copyShortcodeToClipboard();

			const page = new URLSearchParams(window.location.search).get('page');

			if (page === 'gs-the-events-calendar-addon_preferences') {
				this.$router.replace('/preferences');
			}

			if (page === 'gs-the-events-calendar-addon_layout') {
				this.$router.replace('/layout');
			}

		},

		computed: {

			anyShortcodeSelected() {
				return this.shortcodes.some( shortcode => shortcode.selected );
			}

		},

		methods: {

			getShortcodes() {

				jQuery.ajax({
					url: this.getAjaxURL(),
					type: 'GET',
					cache: false,
					data: {
						action: 'gsteca_get_shortcodes'
					}
				})
				.done( response => {

					if ( response.success ) {

						if ( ! response.data ) return;

						return this.shortcodes = response.data.map( shortcode => {
							shortcode.copied = false;
							shortcode.selected = false;
							shortcode.shortcode_text = '[gs-teca id='+shortcode.id+']';
							return shortcode;
						});

					}

					notify({
						message: response.data,
						type: 'info',
						clearAll: true
					});

				})
				.error( response => {

					this.notifyError( response );

				});

			},

			shortcodeClone( currentShortcode ) {

				jQuery.ajax({
					url: this.getAjaxURL(),
					type: 'POST',
					cache: false,
					data: {
						action: 'gsteca_clone_shortcode',
						_wpnonce: this.getWPNonce('clone_shortcode'),
						clone_id: currentShortcode.id
					}
				})
				.done( response => {

					if ( response.success ) {

						let shortcode = response.data.shortcode;
						
						shortcode.copied = false;
						shortcode.selected = false;
						shortcode.cloned = true;
						shortcode.shortcode_text = '[gs-teca id='+shortcode.id+']';

						// this.$set( this.shortcodes, 0, shortcode );
						this.shortcodes.unshift(shortcode);

					}

					notify({
						message: response.data.message,
						type: 'success',
						clearAll: true
					});

				})
				.error( response => {

					this.notifyError( response );

				});

			},

			shortcodeDelete( currentShortcode ) {

				this.shortcodesDelete( [currentShortcode.id] );

				return;

			},

			shortcodesDelete( ids ) {

				let _ids = ( ids && Array.isArray(ids) ) ? ids : [];

				if ( ! _ids.length ) {
					_ids = ( this.selectAll ? this.shortcodes : this.shortcodes.filter(shortcode => shortcode.selected) ).map( shortcode => shortcode.id );
				}

				if ( ! _ids.length ) {
					return notify({
						message: 'please-select-any-shortcode',
						type: 'warning'
					});
				}

				jQuery.ajax({
					url: this.getAjaxURL(),
					type: 'POST',
					cache: false,
					data: {
						action: 'gsteca_delete_shortcodes',
						_wpnonce: this.getWPNonce('delete_shortcodes'),
						ids: _ids
					}
				})
				.done( response => {

					this.shortcodes.filter( shortcode => _ids.includes(shortcode.id) ).forEach( shortcode => {

						let dShortcodeIndex = this.shortcodes.findIndex( _shortcode => {
							return _shortcode.id == shortcode.id;
						});

						this.shortcodes.splice( dShortcodeIndex, 1 );

					});

					notify({
						message: response.data.message,
						type: 'success'
					});

					if ( ! this.shortcodes.length ) {
						this.$router.push("/shortcode/");
					}

				})
				.error( response => {

					this.notifyError( response );

				});

			},

			toggleAllSelection() {
				this.shortcodes.forEach( shortcode => shortcode.selected = this.selectAll );
			},

			detectSelection( shortcode ) {
				this.selectAll = this.shortcodes.every( shortcode => shortcode.selected );
			},

			shortcodeUpdateCopy( currentShortcode ) {

				this.shortcodes.map(shortcode => {
					shortcode.copied = ( shortcode.id == currentShortcode.id ) ? true : false;
				});

				var handler = setTimeout(() => {
					clearTimeout(handler);
					currentShortcode.copied = false;
				}, 4000);
			},

		},
	}
	
</script>