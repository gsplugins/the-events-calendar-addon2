<template>
	<div class="gs-containeer-f edit-shortcode-container">

		<div class="shortcode-settings-area-box" v-if="Object.keys(shortcode_settings).length">

			<!-- Settings Panel -->
			<div class="gs-teca-box gs-teca-settings-tab">

				<div class="gs-teca-tab-links--area">
					<ul class="gs-teca-tab-links">
						<li :class="currentTab == 'general_settings' ? 'is-active' : ''"><button @click="setSettingsTab('general_settings')">{{translation('general-settings')}}</button></li>
						<li :class="currentTab == 'style_settings' ? 'is-active' : ''"><button @click="setSettingsTab('style_settings')">{{translation('style-settings')}}</button></li>
						<li :class="currentTab == 'query_settings' ? 'is-active' : ''"><button @click="setSettingsTab('query_settings')">{{translation('query-settings')}}</button></li>
						<li style="margin-right: 0px;" :class="currentTab == 'visibility_settings' ? 'is-active' : ''"><button @click="setSettingsTab('visibility_settings')">{{translation('visibility-settings')}}</button></li>
					</ul>
				</div>

				<div class="gs-teca-settings-tab-contents">
					<div class="gs-teca--general-settings" v-if="currentTab == 'general_settings'">

						<div class="shortcode-setting--row" v-if="shortcode_text">

							<div>
								<label class="font-16 m-b-0" for="shortcode-input"
									style="margin-right: 6px;">Shortcode:</label>
								<input id="shortcode-input" type="text" class="shortcode-input" :value="shortcode_text"
									readonly>
								<span :class="['copy-holder', copied ? 'copied' : '']" @click.prevent="shortcodeUpdateCopy"
									v-text="copied ? 'Copied' : 'Copy'"></span>
							</div>

						</div>

						<div class="shortcode-setting--row">

							<div class="gs-roow row-20">

								<div class="gs-col-xs-5">
									<label class="m-t-10" for="shortcode_name">{{ translation('shortcode-name')
										}}:</label><br>
								</div>

								<div class="gs-col-xs-7">
									<input type="text" id="shortcode_name" class="bi-input-control" v-model="shortcode_name"
										:placeholder="translation('shortcode-name')">
								</div>
							</div>
						</div>

						<div class="shortcode-setting--row" v-if="shouldShowThemeStyle()">

							<div class="gs-roow row-20">

								<div class="gs-col-xs-5">
									<label class="m-t-10" for="gs_teca_template">{{ translation('gs_teca_template') }}:</label>
									<button class="gs-teca-show--info"><i class="zmdi zmdi-help-outline"></i></button>
								</div>

								<div class="gs-col-xs-7">
									<input-select key="gs_teca_template" id="gs_teca_template"
										v-model="shortcode_settings.gs_teca_template" :options="shortcode_options.gs_teca_template"
										:placeholder="translation('gs-teca-template--placeholder')"></input-select>
								</div>

								<div class="gs-col-xs-12 bi-text-help--area">
									<p class="bi-text-help">{{ translation('gs-teca-template--help') }}</p>
								</div>

							</div>

						</div>

						<div class="shortcode-setting--row">

							<div class="gs-roow row-20">

								<div class="gs-col-xs-5">
									<label class="m-t-10" for="view_type">{{ translation('view_type')
										}}:</label>
								</div>

								<div class="gs-col-xs-7">
									<input-select key="view_type" id="view_type" v-model="shortcode_settings.view_type"
										:options="shortcode_options.view_type"
										:placeholder="translation('view_type--help')"></input-select>
								</div>

							</div>

						</div>

						<div class="shortcode-setting--row" v-if="isCalendarLayoutView()">
							<div class="gs-roow row-20">
								<div class="gs-col-xs-5">
									<label class="m-t-10" for="calendar_layout">{{ translation('calendar_layout') }}:</label>
								</div>
								<div class="gs-col-xs-7">
									<input-select key="calendar_layout" id="calendar_layout" v-model="shortcode_settings.calendar_layout"
										:options="shortcode_options.calendar_layout"></input-select>
								</div>
							</div>
						</div>

						<div class="shortcode-setting--row" v-if="isCalendarLayoutView()">
							<div class="gs-roow row-20">
								<div class="gs-col-xs-5">
									<label class="m-t-10" for="calendar_select_filter">{{ translation('calendar_select_filter') }}:</label>
								</div>
								<div class="gs-col-xs-7">
									<input-select key="calendar_select_filter" id="calendar_select_filter" v-model="shortcode_settings.calendar_select_filter"
										:options="shortcode_options.calendar_select_filter"></input-select>
								</div>
							</div>
						</div>

						<template v-if="shortcode_settings.view_type === 'events-section'">
							<div class="shortcode-setting--row">
								<div class="gs-roow row-20">
									<div class="gs-col-xs-12">
										<h4 class="m-t-10 m-b-10">{{ translation('events_section') }}</h4>
									</div>
								</div>
							</div>

							<div class="shortcode-setting--row">
								<div class="gs-roow row-20">
									<div class="gs-col-xs-5">
										<label class="m-t-10" for="event_layout">{{ translation('event_layout') }}:</label>
									</div>
									<div class="gs-col-xs-7">
										<input-select key="event_layout" id="event_layout" v-model="shortcode_settings.event_layout"
											:options="shortcode_options.event_layout"></input-select>
									</div>
								</div>
							</div>
						</template>

						<template v-if="shortcode_settings.view_type === 'venue_template'">
							<div class="shortcode-setting--row">
								<div class="gs-roow row-20">
									<div class="gs-col-xs-12">
										<h4 class="m-t-10 m-b-10">{{ translation('venue_template') }}</h4>
									</div>
								</div>
							</div>

							<div class="shortcode-setting--row">
								<div class="gs-roow row-20">
									<div class="gs-col-xs-5">
										<label class="m-t-10" for="venue_template_layout">{{ translation('venue_template_layout') }}:</label>
									</div>
									<div class="gs-col-xs-7">
										<input-select key="venue_template_layout" id="venue_template_layout" v-model="shortcode_settings.venue_template_layout"
											:options="shortcode_options.venue_template_layout"></input-select>
									</div>
								</div>
							</div>
						</template>

						<template v-if="shortcode_settings.view_type === 'organizer_template'">
							<div class="shortcode-setting--row">
								<div class="gs-roow row-20">
									<div class="gs-col-xs-12">
										<h4 class="m-t-10 m-b-10">{{ translation('organizer_template') }}</h4>
									</div>
								</div>
							</div>

							<div class="shortcode-setting--row">
								<div class="gs-roow row-20">
									<div class="gs-col-xs-5">
										<label class="m-t-10" for="organizer_template_layout">{{ translation('organizer_template_layout') }}:</label>
									</div>
									<div class="gs-col-xs-7">
										<input-select key="organizer_template_layout" id="organizer_template_layout" v-model="shortcode_settings.organizer_template_layout"
											:options="shortcode_options.organizer_template_layout"></input-select>
									</div>
								</div>
							</div>
						</template>

						<template v-if="isFilterView()">

							<div class="shortcode-setting--row">

								<div class="gs-roow row-20">

									<div class="gs-col-xs-5">
										<label class="m-t-10" for="gs_teca_filter_type">{{translation('filter_type')}}:</label>
										<button class="gs-teca-show--info"><i class="zmdi zmdi-help-outline"></i></button>
									</div>

									<div class="gs-col-xs-7">
										<input-select key="gs_teca_filter_type" id="gs_teca_filter_type" v-model="shortcode_settings.gs_teca_filter_type" :options="shortcode_options.gs_teca_filter_type" :placeholder="translation('filter_type')"></input-select>
									</div>

									<div class="gs-col-xs-12 bi-text-help--area">
										<p class="bi-text-help">{{translation('filter_type__details')}}</p>
									</div>

								</div>

							</div>

							<div class="shortcode-setting--row">

								<div class="gs-roow row-20">

									<div class="gs-col-xs-5">
										<label class="m-t-10" for="gs_filter_cat">{{ translation('gs-filter-cat') }}:</label>
										<button class="gs-teca-show--info"><i class="zmdi zmdi-help-outline"></i></button>
									</div>

									<div class="gs-col-xs-7">
										<input-select key="gs_filter_cat" id="gs_filter_cat"
											v-model="shortcode_settings.gs_filter_cat"
											:options="shortcode_options.gs_filter_cat"
											:placeholder="translation('gs-columns--placeholder')"></input-select>
									</div>

									<div class="gs-col-xs-12 bi-text-help--area">
										<p class="bi-text-help">{{ translation('gs-columns--help') }}</p>
									</div>

								</div>

							</div>

							<div class="shortcode-setting--row">

								<div class="gs-roow row-20">

									<div class="gs-col-xs-5">
										<label class="m-t-10" for="gs_filters_by">{{ translation('gs-filter-by') }}:</label>
										<button class="gs-teca-show--info"><i class="zmdi zmdi-help-outline"></i></button>
									</div>

									<div class="gs-col-xs-7">
										<input-select key="gs_filters_by" id="gs_filters_by"
											v-model="shortcode_settings.gs_filters_by"
											:options="shortcode_options.gs_filters_by"></input-select>
									</div>

									<div class="gs-col-xs-12 bi-text-help--area">
										<p class="bi-text-help">{{ translation('gs-filter-by--help') }}</p>
									</div>

								</div>

							</div>

						</template>

						<template>

							<div class="shortcode-setting--row teca-general-switch-row teca-enable-pagination-control" v-if="is_display_pagination_settings()">

								<div class="teca-style-control-row">
									<div class="teca-style-control-label">
										<label for="gs_teca_pagination">{{translation('gs_teca_pagination')}}:</label>
										<button class="gs-teca-show--info"><i class="zmdi zmdi-help-outline"></i></button>
									</div>
									<div class="teca-style-control-actions">
										<input-toggle name="gs_teca_pagination" v-model="shortcode_settings.gs_teca_pagination" offLabel="Off" onLabel="On"></input-toggle>
									</div>
								</div>

								<div class="bi-text-help--area">
									<p class="bi-text-help">{{translation('gs_teca_pagination__details')}}</p>
								</div>

							</div>

							<div class="shortcode-setting--row" v-if="shortcode_settings.gs_teca_pagination && is_display_pagination_settings()">

								<div class="gs-roow row-20">

									<div class="gs-col-xs-5">
										<label class="m-t-10" for="pagination_type">{{translation('pagination_type')}}:</label>
										<button class="gs-teca-show--info"><i class="zmdi zmdi-help-outline"></i></button>
									</div>

									<div class="gs-col-xs-7">
										<input-select key="pagination_type" id="pagination_type" v-model="shortcode_settings.pagination_type" :options="shortcode_options.pagination_type" :placeholder="translation('pagination_type')"></input-select>
									</div>

									<div class="gs-col-xs-12 bi-text-help--area">
										<p class="bi-text-help">{{translation('pagination_type__details')}}</p>
									</div>

								</div>

							</div>

							<div class="shortcode-setting--row" v-if="shortcode_settings.gs_teca_pagination && is_display_pagination_settings() && displayCondition( shortcode_settings.pagination_type, [ 'load-more-button', 'load-more-scroll' ] )">

								<div class="gs-roow row-20">

									<div class="gs-col-xs-5">
										<label class="m-t-10" for="initial_items">{{translation('initial_items')}}:</label>
										<button class="gs-teca-show--info"><i class="zmdi zmdi-help-outline"></i></button>
									</div>

									<div class="gs-col-xs-7">
										<input type="number" class="bi-input-control" id="initial_items" v-model="shortcode_settings.initial_items" placeholder="6">
									</div>

									<div class="gs-col-xs-12 bi-text-help--area">
										<p class="bi-text-help">{{translation('initial_items__details')}}</p>
									</div>

								</div>

							</div>

							<div class="shortcode-setting--row" v-if="shortcode_settings.gs_teca_pagination && is_display_pagination_settings() && displayCondition( shortcode_settings.pagination_type, [ 'normal-pagination', 'ajax-pagination' ] )">

								<div class="gs-roow row-20">

									<div class="gs-col-xs-5">
										<label class="m-t-10" for="item_per_page">{{translation('item_per_page')}}:</label>
										<button class="gs-teca-show--info"><i class="zmdi zmdi-help-outline"></i></button>
									</div>

									<div class="gs-col-xs-7">
										<input type="number" class="bi-input-control" id="item_per_page" v-model="shortcode_settings.item_per_page" placeholder="6">
									</div>

									<div class="gs-col-xs-12 bi-text-help--area">
										<p class="bi-text-help">{{translation('item_per_page__details')}}</p>
									</div>

								</div>

							</div>

							<div class="shortcode-setting--row" v-if="shortcode_settings.gs_teca_pagination && is_display_pagination_settings() && shortcode_settings.pagination_type == 'load-more-button'">

								<div class="gs-roow row-20">

									<div class="gs-col-xs-5">
										<label class="m-t-10" for="load_per_click">{{translation('load_per_click')}}:</label>
										<button class="gs-teca-show--info"><i class="zmdi zmdi-help-outline"></i></button>
									</div>

									<div class="gs-col-xs-7">
										<input type="number" class="bi-input-control" id="load_per_click" v-model="shortcode_settings.load_per_click" placeholder="6">
									</div>

									<div class="gs-col-xs-12 bi-text-help--area">
										<p class="bi-text-help">{{translation('load_per_click__details')}}</p>
									</div>

								</div>

							</div>

							<div class="shortcode-setting--row" v-if="shortcode_settings.gs_teca_pagination && is_display_pagination_settings() && shortcode_settings.pagination_type == 'load-more-scroll'">

								<div class="gs-roow row-20">

									<div class="gs-col-xs-5">
										<label class="m-t-10" for="per_load">{{translation('per_load')}}:</label>
										<button class="gs-teca-show--info"><i class="zmdi zmdi-help-outline"></i></button>
									</div>

									<div class="gs-col-xs-7">
										<input type="number" class="bi-input-control" id="per_load" v-model="shortcode_settings.per_load" placeholder="6">
									</div>

									<div class="gs-col-xs-12 bi-text-help--area">
										<p class="bi-text-help">{{translation('per_load__details')}}</p>
									</div>

								</div>

							</div>

							<div class="shortcode-setting--row" v-if="shortcode_settings.gs_teca_pagination && is_display_pagination_settings() && shortcode_settings.pagination_type == 'load-more-button'">

								<div class="gs-roow row-20">

									<div class="gs-col-xs-5">
										<label class="m-t-10" for="load_button_text">{{translation('load_button_text')}}:</label>
										<button class="gs-teca-show--info"><i class="zmdi zmdi-help-outline"></i></button>
									</div>

									<div class="gs-col-xs-7">
										<input type="text" class="bi-input-control" id="load_button_text" v-model="shortcode_settings.load_button_text" placeholder="Load More">
									</div>

									<div class="gs-col-xs-12 bi-text-help--area">
										<p class="bi-text-help">{{translation('load_button_text__details')}}</p>
									</div>

								</div>

							</div>

						</template>

						<template>

							<div class="device-switcher--wrapper">
								
								<label class="m-t-10" for="device-switcher">{{ translation( currentDevice ) }}</label>

								<div class="device-switcher">

									<button :class="currentDevice === 'desktop' && 'is-active'" @click.prevent="changeDevice('desktop')">
										<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
											<rect x="3" y="4" width="18" height="12" stroke="currentColor" stroke-width="2"/>
											<rect x="9" y="18" width="6" height="2" stroke="currentColor" stroke-width="2"/>
											<line x1="9" y1="16" x2="15" y2="16" stroke="currentColor" stroke-width="2"/>
										</svg>
									</button>

									<button :class="currentDevice === 'tablet' && 'is-active'" @click.prevent="changeDevice('tablet')">
										<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
											<rect x="6" y="3" width="12" height="18" stroke="currentColor" stroke-width="2"/>
											<circle cx="12" cy="19" r="1" fill="currentColor"/>
										</svg>
									</button>

									<button :class="currentDevice === 'mobile_landscape' && 'is-active'" @click.prevent="changeDevice('mobile_landscape')">
										<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
											<rect x="3" y="8" width="18" height="8" stroke="currentColor" stroke-width="2"/>
											<circle cx="5" cy="12" r="1" fill="currentColor"/>
										</svg>
									</button>

									<button :class="currentDevice === 'mobile' && 'is-active'" @click.prevent="changeDevice('mobile')">
										<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
											<rect x="8" y="2" width="8" height="20" stroke="currentColor" stroke-width="2"/>
											<circle cx="12" cy="18" r="1" fill="currentColor"/>
										</svg>
									</button>

								</div>
							</div>

							<div class="shortcode-setting--row" v-if="currentDevice === 'desktop'">

								<div class="gs-roow row-20">

									<div class="gs-col-xs-5">
										<label class="m-t-10" for="columns">{{ translation('columns') }}:</label>
									</div>

									<div class="gs-col-xs-7">
										<input-select key="columns" id="columns" v-model="shortcode_settings.columns" :options="shortcode_options.columns"></input-select>
									</div>

								</div>

							</div>

							<div class="shortcode-setting--row" v-if="currentDevice === 'tablet'">

								<div class="gs-roow row-20">

									<div class="gs-col-xs-5">
										<label class="m-t-10" for="columns_tablet">{{ translation('columns_tablet') }}:</label>
									</div>

									<div class="gs-col-xs-7">
										<input-select key="columns_tablet" id="columns_tablet" v-model="shortcode_settings.columns_tablet" :options="shortcode_options.columns_tablet"></input-select>
									</div>

								</div>

							</div>

							<div class="shortcode-setting--row" v-if="currentDevice === 'mobile_landscape'">

								<div class="gs-roow row-20">

									<div class="gs-col-xs-5">
										<label class="m-t-10" for="columns_mobile_portrait">{{ translation('columns_mobile_portrait') }}:</label>
									</div>

									<div class="gs-col-xs-7">
										<input-select key="columns_mobile_portrait" id="columns_mobile_portrait" v-model="shortcode_settings.columns_mobile_portrait" :options="shortcode_options.columns_mobile_portrait"></input-select>
									</div>

								</div>

							</div>

							<div class="shortcode-setting--row" v-if="currentDevice === 'mobile'">

								<div class="gs-roow row-20">

									<div class="gs-col-xs-5">
										<label class="m-t-10" for="columns_mobile">{{ translation('columns_mobile') }}:</label>
									</div>

									<div class="gs-col-xs-7">
										<input-select key="columns_mobile" id="columns_mobile" v-model="shortcode_settings.columns_mobile" :options="shortcode_options.columns_mobile"></input-select>
									</div>

								</div>

							</div>

						</template>

						<template>
							<div class="shortcode-setting--row" v-if = "isSliderView() || isTickerView()">

								<div class="gs-roow row-20 range-slider-area">

									<div class="gs-col-xs-5">
										<label class="m-t-10" for="gs_teca_slide_speed">{{translation('gs_teca_slide_speed')}}:</label>
										<button class="gs-teca-show--info"><i class="zmdi zmdi-help-outline"></i></button>
									</div>

									<div class="gs-col-xs-7">
										<div class="range-slider-container no-right-info">
											<input type="text" class="slider-left-info" id="gs_teca_slide_speed" v-model="shortcode_settings.gs_teca_slide_speed">
											<input-range :dot-size="20" :step="50" :tooltip="false" :min="0" :max="10000" v-model="shortcode_settings.gs_teca_slide_speed"></input-range>
										</div>
									</div>

									<div class="gs-col-xs-12 bi-text-help--area">
										<p class="bi-text-help">{{translation('$gs_teca_slide_speed--help')}}</p>
									</div>

								</div>

							</div>

							<div class="shortcode-setting--row" v-if="isSliderView()">

								<div class="gs-roow row-20">

									<div class="gs-col-xs-7">
										<label class="m-t-10" for="gs_teca_is_autop">{{translation('gs_teca_is_autop')}}:</label>
										<button class="gs-teca-show--info"><i class="zmdi zmdi-help-outline"></i></button>
									</div>

									<div class="gs-col-xs-5">
										<input-toggle class="m-t-6" name="gs_teca_is_autop" v-model="shortcode_settings.gs_teca_is_autop" offLabel="Off" onLabel="On"></input-toggle>
									</div>

									<div class="gs-col-xs-12 bi-text-help--area">
										<p class="bi-text-help">{{translation('gs-teca-play-pause--help')}}</p>
									</div>

								</div>

							</div>

							<div class="shortcode-setting--row" v-if="isSliderView()">

								<div class="gs-roow row-20 range-slider-area">

									<div class="gs-col-xs-5">
										<label class="m-t-10" for="gs_teca_autop_pause">{{translation('gs_teca_autop_pause')}}:</label>
										<button class="gs-teca-show--info"><i class="zmdi zmdi-help-outline"></i></button>
									</div>

									<div class="gs-col-xs-7">
										<div class="range-slider-container no-right-info">
											<input type="text" class="slider-left-info" id="gs_teca_autop_pause" v-model="shortcode_settings.gs_teca_autop_pause">
											<input-range :dot-size="20" :step="50" :tooltip="false" :min="0" :max="10000" v-model="shortcode_settings.gs_teca_autop_pause"></input-range>
										</div>
									</div>

									<div class="gs-col-xs-12 bi-text-help--area">
										<p class="bi-text-help">{{translation('gs-teca-autop-pause--help')}}</p>
									</div>

								</div>

							</div>
							
							<div class="shortcode-setting--row" v-if="isSliderView() || isTickerView()">

								<div class="gs-roow row-20">

									<div class="gs-col-xs-7">
										<label class="m-t-10" for="gs_teca_pause_on_hover">{{translation('gs_teca_pause_on_hover')}}:</label>
										<button class="gs-teca-show--info"><i class="zmdi zmdi-help-outline"></i></button>
									</div>

									<div class="gs-col-xs-5">
										<input-toggle class="m-t-6" name="gs_pause_on_hover" v-model="shortcode_settings.gs_teca_pause_on_hover" offLabel="Off" onLabel="On"></input-toggle>
									</div>

									<div class="gs-col-xs-12 bi-text-help--area">
										<p class="bi-text-help">{{translation('gs-teca-slider-stop--help')}}</p>
									</div>

								</div> 

							</div>

							<div class="shortcode-setting--row" v-if="isSliderView()">

								<div class="gs-roow row-20">

									<div class="gs-col-xs-7">
										<label class="m-t-10" for="gs_teca_inf_loop">{{translation('gs_teca_inf_loop')}}:</label>
										<button class="gs-teca-show--info"><i class="zmdi zmdi-help-outline"></i></button>
									</div>

									<div class="gs-col-xs-5">
										<input-toggle class="m-t-6" name="gs_teca_inf_loop" v-model="shortcode_settings.gs_teca_inf_loop" offLabel="Off" onLabel="On"></input-toggle>
									</div>

									<div class="gs-col-xs-12 bi-text-help--area">
										<p class="bi-text-help">{{translation('gs-teca-inf-loop--help')}}</p>
									</div>

								</div>

							</div>

							<div class="shortcode-setting--row" v-if="isSliderView() || isTickerView()">

								<div class="gs-roow row-20">

									<div class="gs-col-xs-7">
										<label class="m-t-10" for="gs_teca_reverse_direction">{{translation('gs-teca-reverse-direction')}}:</label>
										<button class="gs-teca-show--info"><i class="zmdi zmdi-help-outline"></i></button>
									</div>

									<div class="gs-col-xs-5">
										<input-toggle class="m-t-6" name="gs_teca_reverse_direction" v-model="shortcode_settings.gs_teca_reverse_direction" offLabel="Off" onLabel="On"></input-toggle>
									</div>

									<div class="gs-col-xs-12 bi-text-help--area">
										<p class="bi-text-help">{{translation('gs-teca-reverse-direction--help')}}</p>
									</div>

								</div>

							</div>

						</template>

						<div class="shortcode-setting--row teca-general-switch-row teca-link-events-control">

							<div class="teca-style-control-row">
								<div class="teca-style-control-label">
									<label for="gs_teca_name_is_linked">{{translation('gs_teca_name_is_linked')}}:</label>
									<button class="gs-teca-show--info"><i class="zmdi zmdi-help-outline"></i></button>
								</div>
								<div class="teca-style-control-actions">
									<input-toggle name="gs_teca_name_is_linked" v-model="shortcode_settings.gs_teca_name_is_linked" offLabel="Off" onLabel="On"></input-toggle>
								</div>
							</div>

							<div class="bi-text-help--area">
								<p class="bi-text-help">{{translation('gs_teca_name_is_linked__details')}}</p>
							</div>

						</div>

						<template v-if="shortcode_settings.gs_teca_name_is_linked">

							<div class="shortcode-setting--row">

								<div class="gs-roow row-20">

									<div class="gs-col-xs-5">
										<label class="m-t-10" for="gs_teca_link_type">{{translation('gs_teca_link_type')}}:</label>
										<button class="gs-teca-show--info"><i class="zmdi zmdi-help-outline"></i></button>
									</div>

									<div class="gs-col-xs-7">
										<input-select key="gs_teca_link_type" id="gs_teca_link_type" v-model="shortcode_settings.gs_teca_link_type" :options="shortcode_options.gs_teca_link_type" :placeholder="translation('column')"></input-select>
									</div>

									<div class="gs-col-xs-12 bi-text-help--area">
										<p class="bi-text-help">{{translation('gs_teca_link_type__details')}}</p>
									</div>

								</div>

							</div>

							<div class="shortcode-setting--row" v-if="shortcode_settings.gs_teca_link_type == 'popup'">

								<div class="gs-roow row-20">

									<div class="gs-col-xs-5">
										<label class="m-t-10" for="popup_style">{{translation('popup_style')}}:</label>
										<button class="gs-teca-show--info"><i class="zmdi zmdi-help-outline"></i></button>
									</div>

									<div class="gs-col-xs-7">
										<input-select key="popup_style" id="popup_style" v-model="shortcode_settings.popup_style" :options="shortcode_options.popup_style" :placeholder="translation('popup_style')"></input-select>
									</div>

									<div class="gs-col-xs-12 bi-text-help--area">
										<p class="bi-text-help">{{translation('popup_style__details')}}</p>
									</div>

								</div>

							</div>

							<div class="teca-popup-related-events-settings" v-if="shortcode_settings.gs_teca_link_type == 'popup'">

								<div class="shortcode-setting--row teca-related-events-settings">

									<div class="gs-roow row-20">

										<div class="gs-col-xs-5">
											<label class="m-t-10" for="popup_show_related_events">{{translation('popup_show_related_events')}}:</label>
											<button class="gs-teca-show--info"><i class="zmdi zmdi-help-outline"></i></button>
										</div>

										<div class="gs-col-xs-7">
											<input-toggle name="popup_show_related_events" v-model="shortcode_settings.popup_show_related_events" offLabel="Off" onLabel="On"></input-toggle>
										</div>

										<div class="gs-col-xs-12 bi-text-help--area">
											<p class="bi-text-help">{{translation('popup_show_related_events__details')}}</p>
										</div>

									</div>

								</div>

								<div class="shortcode-setting--row">

									<div class="gs-roow row-20">

										<div class="gs-col-xs-5">
											<label class="m-t-10" for="popup_related_events_title">{{translation('popup_related_events_title')}}:</label>
											<button class="gs-teca-show--info"><i class="zmdi zmdi-help-outline"></i></button>
										</div>

										<div class="gs-col-xs-7">
											<input id="popup_related_events_title" type="text" class="form-control" v-model="shortcode_settings.popup_related_events_title" />
										</div>

										<div class="gs-col-xs-12 bi-text-help--area">
											<p class="bi-text-help">{{translation('popup_related_events_title__details')}}</p>
										</div>

									</div>

								</div>

								<div class="shortcode-setting--row">

									<div class="gs-roow row-20">

										<div class="gs-col-xs-5">
											<label class="m-t-10" for="popup_related_events_limit">{{translation('popup_related_events_limit')}}:</label>
											<button class="gs-teca-show--info"><i class="zmdi zmdi-help-outline"></i></button>
										</div>

										<div class="gs-col-xs-7">
											<input-increment name="popup_related_events_limit" v-model="shortcode_settings.popup_related_events_limit" :min="1" :max="12"></input-increment>
										</div>

										<div class="gs-col-xs-12 bi-text-help--area">
											<p class="bi-text-help">{{translation('popup_related_events_limit__details')}}</p>
										</div>

									</div>

								</div>

								<div class="shortcode-setting--row">

									<div class="gs-roow row-20">

										<div class="gs-col-xs-5">
											<label class="m-t-10" for="popup_related_events_sources">{{translation('popup_related_events_sources')}}:</label>
											<button class="gs-teca-show--info"><i class="zmdi zmdi-help-outline"></i></button>
										</div>

										<div class="gs-col-xs-7">
											<input-select
												key="popup_related_events_sources"
												id="popup_related_events_sources"
												v-model="shortcode_settings.popup_related_events_sources"
												:options="shortcode_options.popup_related_events_sources"
												:placeholder="translation('popup_related_events_sources')"
												multiple
											></input-select>
										</div>

										<div class="gs-col-xs-12 bi-text-help--area">
											<p class="bi-text-help">{{translation('popup_related_events_sources__details')}}</p>
										</div>

									</div>

								</div>

							</div>

						</template>

						<div class="shortcode-setting--row">

							<div class="gs-roow row-20">

								<div class="gs-col-xs-5">
									<label class="m-t-10" for="details_length_type">{{ translation('details-length-type') }}:</label>
									<button class="gs-teca-show--info"><i class="zmdi zmdi-help-outline"></i></button>
								</div>

								<div class="gs-col-xs-7">
									<input-select key="details_length_type" id="details_length_type"
										v-model="shortcode_settings.details_length_type"
										:options="shortcode_options.details_length_type">
									</input-select>
								</div>

								<div class="gs-col-xs-12 bi-text-help--area">
									<p class="bi-text-help">{{ translation('details-length-type--help') }}</p>
								</div>

							</div>

						</div>

						<div class="shortcode-setting--row">

							<div class="gs-roow row-20">

								<div class="gs-col-xs-5">
									<label class="m-t-10" for="details_length">{{ translation('details-length') }}:</label>
									<button class="gs-teca-show--info"><i class="zmdi zmdi-help-outline"></i></button>
								</div>

								<div class="gs-col-xs-7">
									<input-increment id="details_length" v-model.number="shortcode_settings.details_length" :min="1"></input-increment>
								</div>

								<div class="gs-col-xs-12 bi-text-help--area">
									<p class="bi-text-help">{{ translation('details-length--help') }}</p>
								</div>

							</div>

						</div>

					</div>

					<div class="gs-teca--style-settings" v-if="currentTab == 'style_settings'">

						<div class="shortcode-setting--row"
							v-if="shortcode_settings.view_type == 'carousel' || shortcode_settings.gs_teca_template.substr(0, 6) == 'carousel'">

							<div class="gs-roow row-20">

								<div class="gs-col-xs-7">
									<label class="m-t-10" for="gs_teca_slider_navs">{{ translation('gs-teca-slider-navs')
										}}:</label>
									<button class="gs-teca-show--info"><i class="zmdi zmdi-help-outline"></i></button>
								</div>

								<div class="gs-col-xs-5">
									<input-toggle class="m-t-6" name="gs_teca_slider_navs"
										v-model="shortcode_settings.gs_teca_slider_navs" offLabel="Off"
										onLabel="On"></input-toggle>
								</div>

								<div class="gs-col-xs-12 bi-text-help--area">
									<p class="bi-text-help">{{ translation('gs-teca-slider-navs--help') }}</p>
								</div>

							</div>

						</div>

						<div class="shortcode-setting--row"
							v-if="shortcode_settings.view_type == 'carousel' || shortcode_settings.gs_teca_template.substr(0, 6) == 'carousel'">

							<div class="gs-roow row-20">

								<div class="gs-col-xs-7">
									<label class="m-t-10" for="gs_teca_slider_dots">{{ translation('gs-teca-slider-dots')
										}}:</label>
									<button class="gs-teca-show--info"><i class="zmdi zmdi-help-outline"></i></button>
								</div>

								<div class="gs-col-xs-5">
									<input-toggle class="m-t-6" name="gs_teca_slider_dots"
										v-model="shortcode_settings.gs_teca_slider_dots" offLabel="Off"
										onLabel="On"></input-toggle>
								</div>

								<div class="gs-col-xs-12 bi-text-help--area">
									<p class="bi-text-help">{{ translation('gs-teca-slider-dots--help') }}</p>
								</div>

							</div>

						</div>

						<div class="shortcode-setting--row"
							v-if="shortcode_settings.view_type == 'carousel' || shortcode_settings.gs_teca_template.substr(0, 6) == 'carousel'">

							<div class="gs-roow row-20">

								<div class="gs-col-xs-5">
									<label class="m-t-10" for="gs_teca_navs_style">{{ translation('gs_teca_navs_style')
										}}:</label>
									<button class="gs-teca-show--info"><i class="zmdi zmdi-help-outline"></i></button>
								</div>

								<div class="gs-col-xs-7">
									<input-select key="gs_teca_navs_style" id="gs_teca_navs_style"
										v-model="shortcode_settings.gs_teca_navs_style"
										:options="shortcode_options.gs_teca_navs_style"
										:placeholder="translation('gs_teca_navs_style')"></input-select>
								</div>

								<div class="gs-col-xs-12 bi-text-help--area">
									<p class="bi-text-help">{{ translation('gs_teca_navs_style__details') }}</p>
								</div>

							</div>

						</div>

						<div class="shortcode-setting--row"
							v-if="shortcode_settings.view_type == 'filter'">

							<div class="gs-roow row-20">

								<div class="gs-col-xs-5">
									<label class="m-t-10" for="gs_teca_filter_style">{{ translation('gs_teca_filter_style')
										}}:</label>
									<button class="gs-teca-show--info"><i class="zmdi zmdi-help-outline"></i></button>
								</div>

								<div class="gs-col-xs-7">
									<input-select key="gs_teca_filter_style" id="gs_teca_filter_style"
										v-model="shortcode_settings.gs_teca_filter_style"
										:options="shortcode_options.gs_teca_filter_style"
										:placeholder="translation('gs_teca_filter_style')"></input-select>
								</div>

								<div class="gs-col-xs-12 bi-text-help--area">
									<p class="bi-text-help">{{ translation('gs_teca_navs_style__details') }}</p>
								</div>

							</div>

						</div>

						<div class="shortcode-setting--row"
							v-if="(shortcode_settings.gs_teca_slider_dots && shortcode_settings.view_type == 'carousel') || (shortcode_settings.gs_teca_slider_dots && shortcode_settings.gs_teca_template.substr(0, 6) == 'slider')">

							<div class="gs-roow row-20">

								<div class="gs-col-xs-5">
									<label class="m-t-10" for="gs_teca_dots_style">{{ translation('gs_teca_dots_style')
										}}:</label>
									<button class="gs-teca-show--info"><i class="zmdi zmdi-help-outline"></i></button>
								</div>

								<div class="gs-col-xs-7">
									<input-select key="gs_teca_dots_style" id="gs_teca_dots_style"
										v-model="shortcode_settings.gs_teca_dots_style"
										:options="shortcode_options.gs_teca_dots_style"
										:placeholder="translation('gs_teca_dots_style')"></input-select>
								</div>

								<div class="gs-col-xs-12 bi-text-help--area">
									<p class="bi-text-help">{{ translation('gs_teca_dots_style__details') }}</p>
								</div>

							</div>

						</div>

						<div class="shortcode-setting--row"
							v-if="(shortcode_settings.gs_teca_slider_navs && shortcode_settings.view_type == 'carousel') || (shortcode_settings.gs_teca_slider_navs && shortcode_settings.gs_teca_template.substr(0, 6) == 'carousel')">

							<div class="gs-roow row-20">

								<div class="gs-col-xs-5">
									<label class="m-t-10" for="gs_p_ctrl_pos">{{ translation('gs-teca-ctrl-pos') }}:</label>
									<button class="gs-teca-show--info"><i class="zmdi zmdi-help-outline"></i></button>
								</div>

								<div class="gs-col-xs-7">
									<input-select key="gs_teca_ctrl_pos" id="gs_teca_ctrl_pos"
										v-model="shortcode_settings.gs_teca_ctrl_pos"
										:options="shortcode_options.gs_teca_ctrl_pos"
										:placeholder="translation('gs-teca-ctrl-pos--placeholder')"></input-select>
								</div>

								<div class="gs-col-xs-12 bi-text-help--area">
									<p class="bi-text-help">{{ translation('gs-teca-ctrl-pos--help') }}</p>
								</div>

							</div>

						</div>

						<div class="shortcode-setting--row">
							<div class="gs-roow row-20">
								<div class="gs-col-xs-5">
									<label class="m-t-10" for="image_filter_style">{{ translation('image_filter') }}:</label>
								</div>
								<div class="gs-col-xs-7">
									<input-select
										key="image_filter_style"
										id="image_filter_style"
										v-model="shortcode_settings.image_filter_style"
										:options="shortcode_options.image_filter_style"
										:placeholder="translation('image_filter_style')"
									></input-select>
								</div>
							</div>
						</div>

						<div class="shortcode-setting--row">
							<div class="gs-roow row-20">
								<div class="gs-col-xs-5">
									<label class="m-t-10" for="image_filter_hover_style">{{ translation('image_filter_hover') }}:</label>
								</div>
								<div class="gs-col-xs-7">
									<input-select
										key="image_filter_hover_style"
										id="image_filter_hover_style"
										v-model="shortcode_settings.image_filter_hover_style"
										:options="shortcode_options.image_filter_hover_style"
										:placeholder="translation('image_filter_hover_style')"
									></input-select>
								</div>
							</div>
						</div>

						<div class="shortcode-setting--row" v-if="!isVenueTemplateView() && !isOrganizerTemplateView()">
							<div class="gs-roow row-20">
								<div class="gs-col-xs-5">
									<label class="m-t-10" for="date_format">{{ translation('date_format') }}:</label>
								</div>
								<div class="gs-col-xs-7">
									<input-select
										:key="'date-format-' + getDateFormatLayoutKey()"
										id="date_format"
										v-model="getDateFormatConfig().format"
										:options="shortcode_options.date_format_presets"
										:placeholder="translation('date_format')"
									></input-select>
								</div>
								<div class="gs-col-xs-12" v-if="getDateFormatConfig().format === 'custom'">
									<label class="m-t-10" for="custom_date_format">{{ translation('custom_date_format') }}:</label>
									<input
										id="custom_date_format"
										type="text"
										class="form-control m-t-5"
										v-model="getDateFormatConfig().custom"
										:placeholder="translation('custom_date_format')"
									/>
									<p class="bi-text-help">{{ translation('custom_date_format__help') }}</p>
								</div>
							</div>
						</div>

						<div class="shortcode-setting--row" v-if="shortcode_settings.gs_teca_link_type === 'popup'">
							<div class="gs-roow row-20">
								<div class="gs-col-xs-5">
									<label class="m-t-10" for="popup_date_format">{{ translation('date_format') }} ({{ translation('popup_style') }}):</label>
								</div>
								<div class="gs-col-xs-7">
									<input-select
										:key="'popup-date-format-' + getPopupDateFormatLayoutKey()"
										id="popup_date_format"
										v-model="getPopupDateFormatConfig().format"
										:options="shortcode_options.date_format_presets"
										:placeholder="translation('date_format')"
									></input-select>
								</div>
								<div class="gs-col-xs-12" v-if="getPopupDateFormatConfig().format === 'custom'">
									<label class="m-t-10" for="popup_custom_date_format">{{ translation('custom_date_format') }}:</label>
									<input
										id="popup_custom_date_format"
										type="text"
										class="form-control m-t-5"
										v-model="getPopupDateFormatConfig().custom"
										:placeholder="translation('custom_date_format')"
									/>
								</div>
							</div>
						</div>

						<div class="shortcode-setting--row box-accordion" :class="{'accordion-open': styleAccordionOpen.typography, 'accordion-open-icon': styleAccordionOpen.typography}">
							<div class="top-area teca-style-accordion-header teca-typography-accordion-header">
								<h3 class="teca-accordion-label">{{ translation('style_accordion_typography') }}</h3>
								<div class="accordion-control teca-accordion-icon">
									<a href="#" @click.prevent="toggleStyleAccordion('typography')"></a>
								</div>
							</div>
							<div class="bottom-area teca-style-accordion-panel teca-typography-accordion-panel">
								<div class="teca-style-control-row">
									<div class="teca-style-control-label">
										<label for="title_typography">{{ translation('title_typography') }}:</label>
									</div>
									<div class="teca-style-control-actions" id="title_typography">
										<typography v-model="shortcode_settings.title_typography" :key="'title-' + getTypographyScopeClass()" :field-custom="getTypographyFieldCustom('title')" @update:fieldCustom="setTypographyFieldCustom('title', $event)" :layout-defaults="getTypographyLayoutDefaults('title')" :device="currentDevice" typography-key="title" typography-group="title"></typography>
									</div>
								</div>
							</div>
						</div>

						<div class="shortcode-setting--row box-accordion" :class="{'accordion-open': styleAccordionOpen.colorTypography, 'accordion-open-icon': styleAccordionOpen.colorTypography}">
							<div class="top-area teca-style-accordion-header teca-color-typography-accordion-header">
								<h3 class="teca-accordion-label">{{ translation('style_accordion_color_typography') }}</h3>
								<div class="accordion-control teca-accordion-icon">
									<a href="#" @click.prevent="toggleStyleAccordion('colorTypography')"></a>
								</div>
							</div>
							<div class="bottom-area teca-style-accordion-panel teca-color-typography-accordion-panel">
								<color-field
									v-for="field in getColorTypographyFields()"
									:key="field.value + '-' + getTypographyScopeClass()"
									:field-key="field.value"
									:label="field.label"
									:default-value="getColorLayoutDefault(field.value)"
									v-model="shortcode_settings[field.value]"
									:is-custom="isColorFieldCustom(field.value)"
									@update:isCustom="setColorFieldCustom(field.value, $event)"
								></color-field>
							</div>
						</div>

						<div class="shortcode-setting--row box-accordion" v-if="shouldShowPopupDetailTypography()" :class="{'accordion-open': styleAccordionOpen.detailTypography, 'accordion-open-icon': styleAccordionOpen.detailTypography}">
							<div class="top-area teca-style-accordion-header teca-typography-accordion-header">
								<h3 class="teca-accordion-label">{{ translation('style_accordion_detail_typography') }}</h3>
								<div class="accordion-control teca-accordion-icon">
									<a href="#" @click.prevent="toggleStyleAccordion('detailTypography')"></a>
								</div>
							</div>
							<div class="bottom-area teca-style-accordion-panel teca-typography-accordion-panel">
								<div class="teca-style-control-row" v-for="group in getPopupDetailTypographyGroups()" :key="group.value + '-detail-typo-' + getPopupDetailTypographyScopeClass()">
									<div class="teca-style-control-label">
										<label :for="group.setting_key">{{ group.label }}:</label>
									</div>
									<div class="teca-style-control-actions" :id="group.setting_key">
										<typography
											v-model="shortcode_settings[group.setting_key]"
											:key="group.value + '-detail-' + getPopupDetailTypographyScopeClass()"
											:field-custom="getPopupDetailTypographyFieldCustom(group.value)"
											@update:fieldCustom="setPopupDetailTypographyFieldCustom(group.value, $event)"
											:layout-defaults="getPopupDetailTypographyLayoutDefaults(group.value)"
											:device="currentDevice"
											:typography-key="'popup-detail-' + group.value"
											:typography-group="'popup-detail-' + group.value"
										></typography>
									</div>
								</div>
							</div>
						</div>

						<div class="shortcode-setting--row box-accordion" v-if="shouldShowPopupDetailTypography()" :class="{'accordion-open': styleAccordionOpen.detailColorTypography, 'accordion-open-icon': styleAccordionOpen.detailColorTypography}">
							<div class="top-area teca-style-accordion-header teca-color-typography-accordion-header">
								<h3 class="teca-accordion-label">{{ translation('style_accordion_detail_color_typography') }}</h3>
								<div class="accordion-control teca-accordion-icon">
									<a href="#" @click.prevent="toggleStyleAccordion('detailColorTypography')"></a>
								</div>
							</div>
							<div class="bottom-area teca-style-accordion-panel teca-color-typography-accordion-panel">
								<color-field
									v-for="field in getPopupDetailColorFields()"
									:key="field.value + '-detail-color-' + getPopupDetailTypographyScopeClass()"
									:field-key="field.value"
									:label="field.label"
									:default-value="getPopupDetailColorLayoutDefault(field.value)"
									v-model="shortcode_settings[field.value]"
									:is-custom="isPopupDetailColorFieldCustom(field.value)"
									@update:isCustom="setPopupDetailColorFieldCustom(field.value, $event)"
								></color-field>
							</div>
						</div>
						
					</div>

					<div class="gspg--query-settings" v-if="currentTab == 'query_settings'">

						<div class="shortcode-setting--row">

							<div class="gs-roow row-20">

								<div class="gs-col-xs-5">
									<label class="m-t-10" for="posts">{{translation('posts')}}:</label>
									<button class="gs-teca-show--info"><i class="zmdi zmdi-help-outline"></i></button>
								</div>

								<div class="gs-col-xs-7">
									<input type="text" class="bi-input-control" id="posts" v-model="shortcode_settings.posts" :placeholder="translation('posts--placeholder')">
								</div>
								
								<div class="gs-col-xs-12 bi-text-help--area">
									<p class="bi-text-help">{{translation('posts--help')}}</p>
								</div>

							</div>

						</div>

						<div class="shortcode-setting--row">

							<div class="gs-roow row-20">

								<div class="gs-col-xs-5">
									<label class="m-t-10" for="order">{{translation('order')}}:</label>
								</div>

								<div class="gs-col-xs-7">
									<input-select key="order" id="order" v-model="shortcode_settings.order" :options="shortcode_options.order" :placeholder="translation('order--placeholder')"></input-select>
								</div>

							</div>

						</div>

						<div class="shortcode-setting--row">

							<div class="gs-roow row-20">

								<div class="gs-col-xs-5">
									<label class="m-t-10" for="orderby">{{translation('order-by')}}:</label>
								</div>

								<div class="gs-col-xs-7">
									<input-select key="orderby" id="orderby" v-model="shortcode_settings.orderby" :options="shortcode_options.orderby" :placeholder="translation('order-by--placeholder')"></input-select>
								</div>

							</div>

						</div>

						<div class="shortcode-setting--row">

							<div class="gs-roow row-20">

								<div class="gs-col-xs-5">
									<label class="m-t-10" for="cat_order">{{ translation('cat_order') }}:</label>
									<button class="gs-teca-show--info"><i class="zmdi zmdi-help-outline"></i></button>
								</div>

								<div class="gs-col-xs-7">
									<input-select key="cat_order" id="cat_order" v-model="shortcode_settings.cat_order"
										:options="shortcode_options.cat_order"
										:placeholder="translation('cat_order')"></input-select>
								</div>

							</div>

						</div>

						<div class="shortcode-setting--row">

							<div class="gs-roow row-20">

								<div class="gs-col-xs-5">
									<label class="m-t-10" for="cat_order_by">{{ translation('cat-order-by') }}:</label>
									<button class="gs-teca-show--info"><i class="zmdi zmdi-help-outline"></i></button>
								</div>

								<div class="gs-col-xs-7">
									<input-select key="cat_order_by" id="cat_order_by"
										v-model="shortcode_settings.cat_order_by" :options="shortcode_options.cat_order_by"
										:placeholder="translation('cat-order-by')"></input-select>
								</div>

							</div>

						</div>

					</div>

					<div class="gs-teca--visibility-settings" v-if="currentTab == 'visibility_settings'">

						<!-- Card Fields -->
						<div class="event-center">
							<h3>Event Fields</h3>
							<table class="table table-striped">
								<thead>
									<tr>
										<th>Field</th>
										<th>
											<div class="visibility-device"><span>{{ translation('desktop') }}</span><svg
													width="24" height="24" viewBox="0 0 24 24" fill="none"
													xmlns="http://www.w3.org/2000/svg">
													<rect x="3" y="4" width="18" height="12" stroke="currentColor"
														stroke-width="2" />
													<rect x="9" y="18" width="6" height="2" stroke="currentColor"
														stroke-width="2" />
													<line x1="9" y1="16" x2="15" y2="16" stroke="currentColor"
														stroke-width="2" />
												</svg></div>
										</th>
										<th>
											<div class="visibility-device"><span>{{ translation('tablet') }}</span><svg
													width="24" height="24" viewBox="0 0 24 24" fill="none"
													xmlns="http://www.w3.org/2000/svg">
													<rect x="6" y="3" width="12" height="18" stroke="currentColor"
														stroke-width="2" />
													<circle cx="12" cy="19" r="1" fill="currentColor" />
												</svg></div>
										</th>
										<th>
											<div class="visibility-device"><span>{{ translation('mobile_landscape')}}</span><svg 
													width="24" height="24" viewBox="0 0 24 24" fill="none"
													xmlns="http://www.w3.org/2000/svg">
													<rect x="3" y="8" width="18" height="8" stroke="currentColor"
														stroke-width="2" />
													<circle cx="5" cy="12" r="1" fill="currentColor" />
												</svg></div>
										</th>
										<th>
											<div class="visibility-device"><span>{{ translation('mobile') }}</span><svg
													width="24" height="24" viewBox="0 0 24 24" fill="none"
													xmlns="http://www.w3.org/2000/svg">
													<rect x="8" y="2" width="8" height="20" stroke="currentColor"
														stroke-width="2" />
													<circle cx="12" cy="18" r="1" fill="currentColor" />
												</svg></div>
										</th>
									</tr>
								</thead>
								<tbody>
									<tr v-for="(item, index) in shortcode_settings.visibility_settings" :key="index">
										<td @click="toggleCheckbox(item)">{{ translation(item.translation_key) }}</td>
										<td><label class="gs-checkbox-ui" :for="index + '_desktop'"><input
													:id="index + '_desktop'" type="checkbox" v-model="item.desktop"
													:name="index + '_desktop'" /></label></td>
										<td><label class="gs-checkbox-ui" :for="index + '_tablet'"><input
													:id="index + '_tablet'" type="checkbox" v-model="item.tablet"
													:name="index + '_tablet'" /></label></td>
										<td><label class="gs-checkbox-ui" :for="index + '_mobile_landscape'"><input
													:id="index + '_mobile_landscape'" type="checkbox"
													v-model="item.mobile_landscape"
													:name="index + '_mobile_landscape'" /></label></td>
										<td><label class="gs-checkbox-ui" :for="index + '_mobile'"><input
													:id="index + '_mobile'" type="checkbox" v-model="item.mobile"
													:name="index + '_mobile'" /></label></td>
									</tr>
								</tbody>
							</table>
						</div>

						<!-- Popup Fields -->
						<div class="popup-center" v-if="shortcode_settings.gs_teca_link_type === 'popup'">

							<h3>Popup Fields</h3>

							<table class="table table-striped title-left">
								<thead>
									<tr>
										<th></th>
										<th>Field</th>
										<th><div class="visibility-device"><span>{{ translation('desktop') }}</span><svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><rect x="3" y="4" width="18" height="12" stroke="currentColor" stroke-width="2"/><rect x="9" y="18" width="6" height="2" stroke="currentColor" stroke-width="2"/><line x1="9" y1="16" x2="15" y2="16" stroke="currentColor" stroke-width="2"/></svg></div></th>
										<th><div class="visibility-device"><span>{{ translation('tablet') }}</span><svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><rect x="6" y="3" width="12" height="18" stroke="currentColor" stroke-width="2"/><circle cx="12" cy="19" r="1" fill="currentColor"/></svg></div></th>
										<th><div class="visibility-device"><span>{{ translation('mobile_landscape') }}</span><svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><rect x="3" y="8" width="18" height="8" stroke="currentColor" stroke-width="2"/><circle cx="5" cy="12" r="1" fill="currentColor"/></svg></div></th>
										<th><div class="visibility-device"><span>{{ translation('mobile') }}</span><svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><rect x="8" y="2" width="8" height="20" stroke="currentColor" stroke-width="2"/><circle cx="12" cy="18" r="1" fill="currentColor"/></svg></div></th>
									</tr>
								</thead>

								<draggable 
									v-model="draggablePopupList" 
									tag="tbody" 
									handle=".sort-handle" 
									item-key="id"
									class="popup-sortable"
								>
									<tr 
										v-for="item in draggablePopupList" 
										:key="item.id" 
										class="sortable-row"
									>   
										<td class="sort-handle">
											<i class="fas fa-arrows-alt" style="cursor: move;"></i>
										</td>
										<td>{{ translation( item.translation_key ) }}</td>
										
										<td>
											<label class="gs-checkbox-ui">
												<input type="checkbox" 
													:checked="shortcode_settings.popup_visibility_settings[item.id].desktop" 
													@change="shortcode_settings.popup_visibility_settings[item.id].desktop = $event.target.checked" />
											</label>
										</td>

										<td>
											<label class="gs-checkbox-ui">
												<input type="checkbox" 
													:checked="shortcode_settings.popup_visibility_settings[item.id].tablet" 
													@change="shortcode_settings.popup_visibility_settings[item.id].tablet = $event.target.checked" />
											</label>
										</td>

										<td>
											<label class="gs-checkbox-ui">
												<input type="checkbox" 
													:checked="shortcode_settings.popup_visibility_settings[item.id].mobile_landscape" 
													@change="shortcode_settings.popup_visibility_settings[item.id].mobile_landscape = $event.target.checked" />
											</label>
										</td>

										<td>
											<label class="gs-checkbox-ui">
												<input type="checkbox" 
													:checked="shortcode_settings.popup_visibility_settings[item.id].mobile" 
													@change="shortcode_settings.popup_visibility_settings[item.id].mobile = $event.target.checked" />
											</label>
										</td>
									</tr>
								</draggable>
							</table>

						</div>

					</div>

					<!-- Save butons -->
					<div class="m-t-20">
						<button class="btn btn-sm btn-brand m-r-10 m-b-10" @click.prevent.stop="saveOrUpdateShortcode"><i class="zmdi zmdi-floppy"></i><span>{{translation('save-shortcode')}}</span></button>
						<button class="btn btn-sm btn-default" @click.prevent.stop="generatePreview"><i class="zmdi zmdi-eye"></i><span>{{translation('preview-shortcode')}}</span></button>
					</div>
				</div>

			</div>
			
			<!-- Right Panel -->
			<div class="gs-teca-right-panel">

				<!-- Preview Panel -->
				<div class="preview-shortcode-iframe-wrapper">
					<iframe v-if="previewReady" id="gs-teca-shortcode-preview-iframe" :src='getSiteURL()+"/?gs_teca_shortcode_preview=" + getPreviewTempID( )' frameborder="0" @load="onPreviewFrameLoad"></iframe>
				</div>

			</div>

		</div>

	</div>
</template>

<script>

	import notify from '../includes/notify';
	

	
	export default {

		data() {

			const now = new Date();

			function pad(n) {
				return n < 10 ? '0' + n : n;
			}

			function formatDate(date, format) {
				const map = {
					'F': date.toLocaleString('default', { month: 'long' }),
					'j': date.getDate(),
					'Y': date.getFullYear(),
					'm': pad(date.getMonth() + 1),
					'd': pad(date.getDate())
				};

				return format
					.replace('F', map.F)
					.replace('j', map.j)
					.replace('Y', map.Y)
					.replace('m', map.m)
					.replace('d', map.d);
			}

			const options = [
				{ value: 'F j, Y', label: `${formatDate(now, 'F j, Y')} (F j, Y)` },
				{ value: 'Y-m-d', label: `${formatDate(now, 'Y-m-d')} (Y-m-d)` },
				{ value: 'm/d/Y', label: `${formatDate(now, 'm/d/Y')} (m/d/Y)` },
				{ value: 'd/m/Y', label: `${formatDate(now, 'd/m/Y')} (d/m/Y)` },
				{ value: 'custom', label: 'Custom' }
			];

			return {

				currentTab: 'general_settings',

				currentDevice: 'desktop',

				pageTitle: this.translation('create-shortcode'),

				pageDescription: this.translation('create-a-new-shortcode-and'),

				previewReady: false,

				id: null,

				copied: false,

				shortcode_name: null,

				shortcode_settings: {},

				shortcode_options: {},

				custom_image_size_width: '',
				custom_image_size_height: '',
				custom_image_size_crop: 'hard-crop',
				dateOptions: options,

				styleAccordionOpen: {
					typography: true,
					colorTypography: false,
					detailTypography: false,
					detailColorTypography: false
				}

			}

		},

		mounted() {
			this.stopWatcher = false;
			
			this.previewTempID = this.getPreviewTempID();

			this.id = ( this.$route.params.id ) ? this.$route.params.id: null;

			if ( this.id ) {
				this.pageTitle = this.translation('edit-shortcode'),
				this.fetchShortcode( this.id );
			} else {
				this.setInitialSettings();
				this.generatePreview();
			}

			this.initHelpText();

			this.copyShortcodeToClipboard();

			if (this.currentTab === 'visibility_settings' &&
				this.shortcode_settings.gs_teca_link_type === 'popup') {

				this.$nextTick(() => {
					jQuery('.popup-sortable').sortable({
						update: () => {
							this.updatePopupOrder();
						}
					});
				});
			}

		},

		computed: {

			shortcode_text() {
				if ( ! this.id ) return '';
				return '[gs-teca id='+this.id+']';
			},

			draggablePopupList: {
				get() {
					const settings = this.shortcode_settings.popup_visibility_settings;
					if (!settings) return [];

					return this.getPopupVisibilityOrder().map(key => {
						if (settings[key]) {
							return {
								id: key,
								...settings[key]
							};
						}
						return null;
					}).filter(Boolean);
				},

				set(newList) {
					const existingSettings = this.shortcode_settings.popup_visibility_settings || {};
					const updatedSettings = { ...existingSettings };
					const newOrder = [];

					newList.forEach(item => {
						const { id, ...rest } = item;
						updatedSettings[id] = { ...existingSettings[id], ...rest };
						newOrder.push(id);
					});

					Object.keys(updatedSettings).forEach(key => {
						if (!newOrder.includes(key)) {
							newOrder.push(key);
						}
					});

					// local state update
					this.shortcode_settings.popup_visibility_settings = updatedSettings;
					this.shortcode_settings.popup_visibility_order = newOrder;

					// ✅ PASS newOrder properly
					this.onDragUpdate(newOrder);
				}
			},


			view_type_options() {

				let view_type_option = this.shortcode_options.gs_view_type;
				let view_type;

				if ( this.getThemeType() === 'list' ) {
					view_type_option =  view_type_option.filter(item => {
						return item.value === 'gs-posts-grid' || item.value === 'gs-posts-filter';
					});
				}

				if ( this.getThemeType() === 'horizontal' ) {
					view_type_option =  view_type_option.filter(item => {
						return item.value !== 'gs-posts-masonry';
					});
				}

				if ( this.chched_view_type ) {

					view_type = view_type_option.find(item => {
						return item.value === this.chched_view_type
					});

					if ( view_type ) {
						this.shortcode_settings.gs_view_type = this.chched_view_type;
						this.chched_view_type = null;
					} else {
						this.shortcode_settings.gs_view_type = view_type_option[0].value;
					}

				} else {

					view_type = view_type_option.find(item => {
						return item.value === this.shortcode_settings.gs_view_type
					});

					if ( ! view_type ) {
						this.chched_view_type = this.nonReactive(this.shortcode_settings.gs_view_type);
						this.shortcode_settings.gs_view_type = view_type_option[0].value;
					}

				}
				
				return view_type_option;

			},

			is_popup_enabled() {
				if ( ! this.shortcode_settings.gs_teca_name_is_linked ) return false;
				if ( this.shortcode_settings.gs_teca_link_type == 'popup' ) return true;
				if ( this.shortcode_settings.gs_teca_link_type == 'single_page' ) return false;
			},

			getCategoryWithSubcategories() {
				if (!this.shortcode_settings.gs_post_cats || !this.shortcode_settings.gs_post_cats.length) {
					return [];
				}
				
				return this.shortcode_settings.gs_post_cats.map(catId => {
					const category = this.allCategories.find(c => c.term_id == catId);
					if (!category) return null;
					
					const subcategories = this.allCategories.filter(c => c.parent == catId);
					return {
						...category,
						subcategories
					};
				}).filter(Boolean);
			}

		},

		methods: {

			isToggleOn( value ) {
				return value === true || value === 'on' || value === 1 || value === '1';
			},

			handleCarouselToggle(value) {
				// When carousel is toggled, update the view type accordingly
				if (value) {
					this.shortcode_settings.gs_view_type = 'gs-posts-slider';
				} else {
					this.shortcode_settings.gs_view_type = 'gs-posts-grid';
				}
			},

			isCalendarView() {
				return this.isCalendarLayoutView() || this.shortcode_settings.view_type === 'events-section' || this.isVenueTemplateView() || this.isOrganizerTemplateView();
			},

			isVenueTemplateView() {
				return this.shortcode_settings.view_type === 'venue_template';
			},

			isOrganizerTemplateView() {
				return this.shortcode_settings.view_type === 'organizer_template';
			},

			shouldShowThemeStyle() {
				return ! this.isCalendarView();
			},

			shouldShowPopupDetailTypography() {
				return this.shortcode_settings.gs_teca_link_type === 'popup';
			},

			isCalendarLayoutView() {
				return [
					'calendar',
					'daily-calendar',
					'weekly-calendar',
					'monthly-calendar',
					'quarterly-calendar',
					'yearly-calendar',
				].includes( this.shortcode_settings.view_type );
			},

			is_display_pagination_settings() {
				if ( this.isCalendarView() ) return false;
				if ( this.shortcode_settings.view_type === 'carousel' ) return false;
				if ( this.shortcode_settings.view_type === 'filter' && this.shortcode_settings.gs_teca_filter_type === 'normal-filter' ) {
					return false;
				}
				return true;
			},

			showField($field) {

				if( $field === 'post_thumbnail' ) return true;

				let post_title = ['style_1', 'style_2', 'style_3', 'style_4', 'style_5', 'style_6', 'style_7', 'style_8', 'style_9', 'style_10', 'style_11', 'style_12', 'style_13', 'style_14', 'style_15', 'style_16', 'style_17', 'style_18', 'style_19', 'style_20', 'style_21', 'style_22', 'style_23', 'style_24', 'horizontal_1', 'horizontal_2', 'horizontal_4', 'horizontal_5', 'horizontal_6', 'widget_1', 'widget_2', 'widget_3', 'widget_4', 'widget_5','list_1', 'list_2', 'list_3', 'list_4', 'list_5', 'list_6', 'slider_1', 'slider_2', 'slider_3', 'slider_4', 'slider_5', 'timeline_1', 'timeline_2', 'timeline_3', 'timeline_4', 'timeline_5'];
				let post_cat = ['style_1', 'style_2', 'style_3', 'style_4', 'style_5', 'style_6', 'style_7', 'style_8', 'style_9', 'style_10', 'style_11', 'style_12', 'style_13', 'style_14', 'style_15', 'style_16', 'style_17', 'style_18', 'style_19', 'style_21', 'style_22', 'style_23', 'style_24', 'horizontal_1', 'horizontal_2', 'horizontal_4', 'horizontal_5', 'horizontal_6', 'widget_1', 'widget_2', 'widget_3', 'widget_4', 'widget_5', 'list_3', 'list_4', 'list_6', 'slider_1', 'slider_2', 'slider_3', 'slider_4', 'slider_5', 'timeline_1', 'timeline_2', 'timeline_3', 'timeline_4', 'timeline_5'];
				let post_tags = ['style_1', 'style_2', 'style_3', 'style_4', 'style_5', 'style_6', 'style_7', 'style_8', 'style_9', 'style_10', 'style_11', 'style_12', 'style_13', 'style_15', 'style_16', 'style_17', 'style_19', 'style_21', 'style_23', 'style_24', 'horizontal_1', 'horizontal_2', 'horizontal_4', 'horizontal_5', 'horizontal_6', 'widget_1', 'widget_2', 'widget_3', 'widget_4', 'widget_5', 'list_6', 'slider_1', 'slider_2', 'slider_3', 'slider_4', 'slider_5', 'timeline_1', 'timeline_2', 'timeline_3', 'timeline_4', 'timeline_5'];
				let post_authors = ['style_1', 'style_2', 'style_3', 'style_4', 'style_5', 'style_6', 'style_7', 'style_8', 'style_9', 'style_10', 'style_11', 'style_12', 'style_13', 'style_14', 'style_15', 'style_16', 'style_17', 'style_18', 'style_19', 'style_20', 'style_21', 'style_22', 'style_23', 'style_24', 'horizontal_1', 'horizontal_2', 'horizontal_4', 'horizontal_5', 'horizontal_6', 'widget_1', 'widget_2', 'widget_3', 'widget_4', 'widget_5','list_1', 'list_3', 'list_5', 'list_6', 'slider_1', 'slider_2', 'slider_3', 'slider_4', 'slider_5', 'timeline_1', 'timeline_2', 'timeline_3', 'timeline_4', 'timeline_5'];
				let post_details = ['style_1', 'style_2', 'style_3', 'style_4', 'style_5', 'style_6', 'style_7', 'style_8', 'style_9', 'style_10', 'style_11', 'style_12', 'style_13', 'style_14', 'style_15', 'style_16', 'style_17', 'style_18', 'style_19', 'style_20', 'style_21', 'style_22', 'style_23', 'style_24', 'horizontal_1', 'horizontal_2', 'horizontal_4', 'horizontal_5', 'horizontal_6', 'widget_1', 'widget_2', 'widget_3', 'widget_4', 'widget_5', 'widget_6', 'list_2', 'list_3', 'list_5', 'list_6', 'slider_1', 'slider_2', 'slider_3', 'slider_4', 'slider_5', 'timeline_1', 'timeline_2', 'timeline_3', 'timeline_4', 'timeline_5'];
				let post_excerpt = ['style_1', 'style_2', 'style_3', 'style_4', 'style_6', 'style_8', 'style_10', 'style_11', 'style_12', 'style_13', 'style_14', 'style_15', 'style_16', 'style_18', 'style_19', 'style_20', 'style_21', 'style_22', 'style_23', 'style_24', 'horizontal_1', 'horizontal_2', 'horizontal_5', 'horizontal_6', 'widget_1', 'widget_2', 'widget_3', 'widget_4', 'widget_5', 'widget_6', 'list_2', 'list_3', 'list_5', 'list_6', 'slider_1', 'slider_2', 'slider_3', 'slider_4', 'slider_5', 'timeline_1', 'timeline_2', 'timeline_3', 'timeline_4', 'timeline_5'];

				const fieldMap = {
					'post_title': post_title,
					'post_cat': post_cat,
					'post_tags': post_tags,
					'post_authors': post_authors,
					'post_details': post_details,
					'post_excerpt': post_excerpt
				};

				if (fieldMap.hasOwnProperty($field)) {
					return this.displayCondition(this.shortcode_settings.theme_effect, fieldMap[$field]);
				}

			},

			displayCondition( field, values ) {
				if ( values.includes(field) ) return true;
				return false;
			},

			onDragUpdate(newOrder) {
				this.savePopupOrder(newOrder);
			},

			getPopupVisibilityOrder() {
				const settings = this.shortcode_settings.popup_visibility_settings || {};
				let order = this.shortcode_settings.popup_visibility_order;

				if (!Array.isArray(order)) {
					order = typeof order === 'object' && order !== null
						? Object.values(order)
						: Object.keys(settings);
				}

				const defaultOrder = Object.keys(settings);
				const ordered = order.filter(key => settings[key]);
				const remaining = defaultOrder.filter(key => !ordered.includes(key));

				return [...ordered, ...remaining];
			},

			normalizePopupVisibilitySettings() {
				if (!this.shortcode_settings.popup_visibility_settings) {
					return;
				}

				this.$set(
					this.shortcode_settings,
					'popup_visibility_order',
					this.getPopupVisibilityOrder()
				);
			},

			savePopupOrder(order) {

				const data = window.GS_TECA_POPUP_ORDER_DATA;


				if (!data || !data.ajaxurl) {
					console.error('GS_TECA_POPUP_ORDER_DATA missing');
					return;
				}

				// 🔥 এখানেই আসল fix
				if (!this.id) {
					console.error('shortcode id missing from route');
					return;
				}

				jQuery.post(data.ajaxurl, {
					action: data.action,          // hardcode না
					_ajax_nonce: data.nonce,
					shortcode_id: this.id,        // ✅ ROUTE ID ব্যবহার করো
					order: order
				})
				.done((res) => {
					console.log('Popup order saved', res);
				})
				.fail((err) => {
					console.error('Popup order save failed', err);
				});
			},


			toggleCheckbox(item) {
				let state = !item.desktop;
				item.desktop = state;
				item.tablet = state;
				item.mobile_landscape = state;
				item.mobile = state;
			},

			changeDevice( device ) {
				this.currentDevice = device;
			},

			getThemeType() {
				let gs_teca_template = this.shortcode_settings.gs_teca_template;
				if ( gs_teca_template.substr(0, 6) === 'gs-teca-style-' ) return 'grid';
				// if ( gs_teca_template.indexOf( 'horizontal' ) > -1 ) return 'horizontal';
				// if ( gs_teca_template.indexOf( 'list-style' ) > -1 ) return 'list';
				// if ( gs_teca_template.indexOf( 'metro-style' ) > -1 ) return 'metro';
				// if ( gs_teca_template.indexOf( 'justified-style' ) > -1 ) return 'justified';
				// if ( gs_teca_template.indexOf( 'timeline' ) > -1 ) return 'timeline';
				// if ( gs_teca_template.indexOf( 'slider' ) > -1 ) return 'slider';
				return 'grid';
			},

			isGridView() {
				return this.getThemeType() === 'grid';
			},

			isSliderView() {
				if ( this.getThemeType() === 'slider' ) return true;
				return this.shortcode_settings.view_type === 'carousel' && ['grid', 'horizontal'].includes( this.getThemeType() );
			},

			isFilterView() {
				return this.shortcode_settings.view_type === 'filter' && ['grid', 'horizontal', 'list'].includes( this.getThemeType() )
			},

			isMasonry() {
				return this.shortcode_settings.view_type === 'masonry';
			},

			isTickerView() {
				return this.shortcode_settings.view_type === 'ticker';
			},

			isJustified() {
				if ( this.getThemeType() === 'justified' ) return true;
			},

			shortcodeUpdateCopy() {

				this.copied = true;

				var handler = setTimeout(() => {
					clearTimeout(handler);
					this.copied = false;
				}, 4000);
			},

			_is( field, values ) {

				if ( Array.isArray(values) ) return values.includes(field);

				if ( ['string', 'boolean'].includes(typeof values) ) return field == values;

				return false;

			},

			setInitialSettings() {

				let shortcode_options = this._getShortcodeOptions();
				const preserveObjectOptions = [ 'style_design_registry', 'date_format_presets' ];

				for ( let option_group in shortcode_options ) {
					if ( preserveObjectOptions.includes( option_group ) ) {
						continue;
					}

					if( ! Array.isArray(shortcode_options[option_group]) ) {
						shortcode_options[option_group] = [];
					}
				}

				this.$set( this, 'shortcode_options', shortcode_options );

				this.setShortcodeSettings( this._getShortcodeSettings() );

				this.normalizePopupVisibilitySettings();

				if ( ! this.shortcode_settings.date_formats || typeof this.shortcode_settings.date_formats !== 'object' ) {
					this.$set( this.shortcode_settings, 'date_formats', {} );
				}

				if ( ! Array.isArray( this.shortcode_settings.popup_related_events_sources ) ) {
					this.$set( this.shortcode_settings, 'popup_related_events_sources', [ 'category', 'tag', 'venue', 'organizer', 'upcoming' ] );
				}

				if ( typeof this.shortcode_settings.popup_related_events_limit === 'undefined' ) {
					this.$set( this.shortcode_settings, 'popup_related_events_limit', 3 );
				}

				if ( typeof this.shortcode_settings.popup_related_events_title === 'undefined' ) {
					this.$set( this.shortcode_settings, 'popup_related_events_title', 'Related Events' );
				}

				if ( typeof this.shortcode_settings.popup_show_related_events === 'undefined' ) {
					this.$set( this.shortcode_settings, 'popup_show_related_events', 'on' );
				}

				if ( this.shortcode_settings.popup_show_related_events === 'on' || this.shortcode_settings.popup_show_related_events === true ) {
					this.$set( this.shortcode_settings, 'popup_show_related_events', true );
				} else if ( this.shortcode_settings.popup_show_related_events === 'off' || this.shortcode_settings.popup_show_related_events === false ) {
					this.$set( this.shortcode_settings, 'popup_show_related_events', false );
				}

			},

			getPreviewTempID() {
				if ( this.previewTempID ) return this.previewTempID;
				return 'gs_teca_' + Math.random().toString(36).substr(2, 9);
				
			},
				
			setSettingsTab( val ) {
				this.currentTab = val;
				this.$nextTick(() => {
					if ( val === 'style_settings' ) {
						this.applyStylePreview();
					}
				});
			},

			toggleStyleAccordion( key ) {
				if ( ! this.styleAccordionOpen[ key ] ) {
					this.$set( this.styleAccordionOpen, key, true );
					return;
				}

				this.$set( this.styleAccordionOpen, key, false );
			},

			getColorTypographyFields() {
				return this.shortcode_options.color_typography_fields || [];
			},

			getPopupDetailTypographyGroups() {
				return this.shortcode_options.popup_detail_typography_groups || [];
			},

			getPopupDetailColorFields() {
				return this.shortcode_options.popup_detail_color_fields || [];
			},

			getPopupDetailTypographyScopeClass() {
				const style = this.shortcode_settings.popup_style || 'default';
				return `popup:${style}`;
			},

			getPopupDetailDesignRegistryEntry() {
				const scope = this.getPopupDetailTypographyScopeClass();
				const registry = this.shortcode_options.popup_detail_design_registry || {};

				return registry[ scope ] || registry['popup:default'] || { typography: {}, colors: {} };
			},

			getPopupDetailTypographyLayoutDefaults( group ) {
				const design = this.getPopupDetailDesignRegistryEntry();

				return ( design.typography && design.typography[ group ] ) ? design.typography[ group ] : {};
			},

			getPopupDetailColorLayoutDefault( fieldKey ) {
				const design = this.getPopupDetailDesignRegistryEntry();

				return ( design.colors && design.colors[ fieldKey ] ) ? design.colors[ fieldKey ] : '';
			},

			getPopupDetailTypographyCustomKey( group ) {
				return `popup_detail_${group}_typography_custom`;
			},

			getPopupDetailTypographyFieldCustom( group ) {
				const nested = this.shortcode_settings.popup_detail_typography_custom;

				if ( nested && nested[ group ] && typeof nested[ group ] === 'object' && ! Array.isArray( nested[ group ] ) ) {
					return Object.assign( {}, this.getEmptyTypographyFieldCustom(), nested[ group ] );
				}

				const key = this.getPopupDetailTypographyCustomKey( group );
				const flags = this.shortcode_settings[ key ];

				if ( ! flags || typeof flags !== 'object' || Array.isArray( flags ) ) {
					return this.getEmptyTypographyFieldCustom();
				}

				return Object.assign( {}, this.getEmptyTypographyFieldCustom(), flags );
			},

			setPopupDetailTypographyFieldCustom( group, flags ) {
				this.$set( this.shortcode_settings, this.getPopupDetailTypographyCustomKey( group ), flags );

				if ( ! this.shortcode_settings.popup_detail_typography_custom || typeof this.shortcode_settings.popup_detail_typography_custom !== 'object' ) {
					this.$set( this.shortcode_settings, 'popup_detail_typography_custom', {} );
				}

				this.$set( this.shortcode_settings.popup_detail_typography_custom, group, flags );
			},

			isPopupDetailColorFieldCustom( fieldKey ) {
				const nested = this.shortcode_settings.popup_detail_color_custom;

				if ( nested && Object.prototype.hasOwnProperty.call( nested, fieldKey ) ) {
					return !!nested[ fieldKey ];
				}

				return !!this.shortcode_settings[ this.getColorCustomFlagKey( fieldKey ) ];
			},

			setPopupDetailColorFieldCustom( fieldKey, isCustom ) {
				this.$set( this.shortcode_settings, this.getColorCustomFlagKey( fieldKey ), !!isCustom );

				if ( ! this.shortcode_settings.popup_detail_color_custom || typeof this.shortcode_settings.popup_detail_color_custom !== 'object' ) {
					this.$set( this.shortcode_settings, 'popup_detail_color_custom', {} );
				}

				this.$set( this.shortcode_settings.popup_detail_color_custom, fieldKey, !!isCustom );

				if ( ! isCustom ) {
					this.$set( this.shortcode_settings, fieldKey, '' );
				}
			},

			getPopupDetailTypographyWatchers() {
				return ( this.getPopupDetailTypographyGroups() || [] ).map( group => ( {
					key: `shortcode_settings.${group.setting_key}`,
					customKey: this.getPopupDetailTypographyCustomKey( group.value ),
					group: group.value
				} ) );
			},

			getPopupDetailColorWatchers() {
				return ( this.getPopupDetailColorFields() || [] ).map( field => ( {
					key: `shortcode_settings.${field.value}`,
					customKey: this.getColorCustomFlagKey( field.value ),
					fieldKey: field.value
				} ) );
			},

			getColorCustomFlagKey( fieldKey ) {
				return `${fieldKey}_custom`;
			},

			getTypographyScopeClass() {
				return this.getDateFormatLayoutKey();
			},

			getDateFormatLayoutKey() {
				const settings = this.shortcode_settings || {};
				const viewType = settings.view_type || '';
				const template = settings.gs_teca_template || '';

				const accordionMap = {
					'gs-teca-accordion-1': 'teca-accordion-1',
					'gs-teca-accordion-2': 'teca-accordion-2',
					'gs-teca-accordion-3': 'teca-accordion-3',
				};

				const timelineMap = {
					'gs-teca-timeline-1': 'teca-timeline-1',
					'gs-teca-timeline-2': 'teca-timeline-2',
					'gs-teca-timeline-3': 'teca-timeline-3',
				};

				if ( viewType === 'accordion' || accordionMap[ template ] ) {
					return accordionMap[ template ] || 'teca-accordion-1';
				}

				if ( viewType === 'timeline' || timelineMap[ template ] ) {
					return timelineMap[ template ] || 'teca-timeline-1';
				}

				if ( viewType === 'events-section' ) {
					const layout = settings.event_layout || 'event-layout-1';
					const match = String( layout ).match( /event-layout-(\d+)/ );
					return match ? `teca-events-layout-${match[1]}` : 'teca-events-layout-1';
				}

				if ( viewType === 'venue_template' ) {
					const layout = settings.venue_template_layout || 'layout-1';
					const match = String( layout ).match( /layout-(\d+)/ );
					return match ? `teca-venue-template-layout-${match[1]}` : 'teca-venue-template-layout-1';
				}

				if ( viewType === 'organizer_template' ) {
					const layout = settings.organizer_template_layout || 'layout-1';
					const match = String( layout ).match( /layout-(\d+)/ );
					return match ? `teca-organizer-template-layout-${match[1]}` : 'teca-organizer-template-layout-1';
				}

				if ( viewType === 'calendar' ) {
					const filter = settings.calendar_select_filter || 'daily';
					const layoutKey = `${filter}_calendar_layout`;
					const layout = settings[ layoutKey ] || `${filter}-layout-1`;
					const match = String( layout ).match( /(\w+)-layout-(\d+)/ );

					if ( match ) {
						return `teca-${match[1]}-layout-${match[2]}`;
					}

					const calendarLayout = settings.calendar_layout || 'calendar-layout-1';
					const calendarMatch = String( calendarLayout ).match( /calendar-layout-(\d+)/ );
					return calendarMatch ? `teca-daily-layout-${calendarMatch[1]}` : 'teca-daily-layout-1';
				}

				const styleMatch = template.match( /gs-teca-style-(\d+)/ );
				if ( styleMatch ) return `teca-style-${styleMatch[1]}`;

				const listStyleMatch = template.match( /gs-teca-list-style-(\d+)/ );
				if ( listStyleMatch ) return `teca-list-${listStyleMatch[1]}`;

				const listMatch = template.match( /gs-teca-list-(\d+)/ );
				if ( listMatch ) return `teca-list-${listMatch[1]}`;

				const tableMatch = template.match( /gs-teca-table-(\d+)/ );
				if ( tableMatch ) return `teca-table-${tableMatch[1]}`;

				const filterMatch = template.match( /gs-teca-filter-(\d+)/ );
				if ( filterMatch ) return `teca-filter-${filterMatch[1]}`;

				if ( template ) {
					return template.replace( 'gs-teca-', 'teca-' );
				}

				return 'teca-style-1';
			},

			getPopupDateFormatLayoutKey() {
				const style = this.shortcode_settings.popup_style || 'default';
				return `popup:${style}`;
			},

			ensureDateFormatsObject() {
				if ( ! this.shortcode_settings.date_formats || typeof this.shortcode_settings.date_formats !== 'object' || Array.isArray( this.shortcode_settings.date_formats ) ) {
					this.$set( this.shortcode_settings, 'date_formats', {} );
				}
			},

			getDateFormatConfig( layoutKey = '' ) {
				this.ensureDateFormatsObject();
				const key = layoutKey || this.getDateFormatLayoutKey();

				if ( ! this.shortcode_settings.date_formats[ key ] ) {
					this.$set( this.shortcode_settings.date_formats, key, { format: 'default', custom: '' } );
				}

				return this.shortcode_settings.date_formats[ key ];
			},

			getPopupDateFormatConfig() {
				return this.getDateFormatConfig( this.getPopupDateFormatLayoutKey() );
			},

			getColorWatchers() {
				return ( this.getColorTypographyFields() || [] ).map( field => ( {
					key: `shortcode_settings.${field.value}`,
					customKey: this.getColorCustomFlagKey( field.value ),
					fieldKey: field.value
				} ) );
			},

			getTypographyCustomKey( group ) {
				const typographyKeyMap = {
					title: 'title_typography',
					cat: 'cat_typography',
					tag: 'tag_typography',
					org: 'org_typography',
					date: 'date_typography',
					details: 'details_typography',
					venue: 'venue_typography',
					view_details: 'view_details_button_typography',
					google_calendar: 'google_calendar_button_typography',
				};
				const settingKey = typographyKeyMap[ group ] || `${group}_typography`;

				return `${settingKey}_custom`;
			},

			getEmptyTypographyFieldCustom() {
				return {
					font_family: false,
					font_size: false,
					font_weight: false,
					line_height: false,
					letter_spacing: false,
					text_transform: false,
					font_style: false,
					text_decoration: false
				};
			},

			getTypographyFieldCustom( group ) {
				const nested = this.shortcode_settings.typography_custom;

				if ( nested && nested[ group ] && typeof nested[ group ] === 'object' && ! Array.isArray( nested[ group ] ) ) {
					return Object.assign( {}, this.getEmptyTypographyFieldCustom(), nested[ group ] );
				}

				const key = this.getTypographyCustomKey( group );
				const flags = this.shortcode_settings[ key ];

				if ( ! flags || typeof flags !== 'object' || Array.isArray( flags ) ) {
					return this.getEmptyTypographyFieldCustom();
				}

				return Object.assign( {}, this.getEmptyTypographyFieldCustom(), flags );
			},

			setTypographyFieldCustom( group, flags ) {
				this.$set( this.shortcode_settings, this.getTypographyCustomKey( group ), flags );

				if ( ! this.shortcode_settings.typography_custom || typeof this.shortcode_settings.typography_custom !== 'object' ) {
					this.$set( this.shortcode_settings, 'typography_custom', {} );
				}

				this.$set( this.shortcode_settings.typography_custom, group, flags );
			},

			getStyleDesignRegistryEntry() {
				const scope = this.getTypographyScopeClass();
				const registry = this.shortcode_options.style_design_registry || {};

				return registry[ scope ] || registry['teca-style-1'] || { typography: {}, colors: {} };
			},

			getTypographyLayoutDefaults( group ) {
				const design = this.getStyleDesignRegistryEntry();

				return ( design.typography && design.typography[ group ] ) ? design.typography[ group ] : {};
			},

			getColorLayoutDefault( fieldKey ) {
				const design = this.getStyleDesignRegistryEntry();

				return ( design.colors && design.colors[ fieldKey ] ) ? design.colors[ fieldKey ] : '';
			},

			isColorFieldCustom( fieldKey ) {
				const nested = this.shortcode_settings.color_custom;

				if ( nested && Object.prototype.hasOwnProperty.call( nested, fieldKey ) ) {
					return !!nested[ fieldKey ];
				}

				return !!this.shortcode_settings[ this.getColorCustomFlagKey( fieldKey ) ];
			},

			setColorFieldCustom( fieldKey, isCustom ) {
				this.$set( this.shortcode_settings, this.getColorCustomFlagKey( fieldKey ), !!isCustom );

				if ( ! this.shortcode_settings.color_custom || typeof this.shortcode_settings.color_custom !== 'object' ) {
					this.$set( this.shortcode_settings, 'color_custom', {} );
				}

				this.$set( this.shortcode_settings.color_custom, fieldKey, !!isCustom );

				if ( ! isCustom ) {
					this.$set( this.shortcode_settings, fieldKey, '' );
				}
			},

			getTypographyWatchers() {
				return [
					{key: 'shortcode_settings.title_typography', messageKey: 'title_typography', customKey: 'title_typography_custom', group: 'title'},
					{key: 'shortcode_settings.cat_typography', messageKey: 'cat_typography', customKey: 'cat_typography_custom', group: 'cat'},
					{key: 'shortcode_settings.tag_typography', messageKey: 'tag_typography', customKey: 'tag_typography_custom', group: 'tag'},
					{key: 'shortcode_settings.org_typography', messageKey: 'org_typography', customKey: 'org_typography_custom', group: 'org'},
					{key: 'shortcode_settings.date_typography', messageKey: 'date_typography', customKey: 'date_typography_custom', group: 'date'},
					{key: 'shortcode_settings.details_typography', messageKey: 'details_typography', customKey: 'details_typography_custom', group: 'details'},
					{key: 'shortcode_settings.venue_typography', messageKey: 'venue_typography', customKey: 'venue_typography_custom', group: 'venue'},
					{key: 'shortcode_settings.view_details_button_typography', messageKey: 'view_details_button_typography', customKey: 'view_details_button_typography_custom', group: 'view_details'},
					{key: 'shortcode_settings.google_calendar_button_typography', messageKey: 'google_calendar_button_typography', customKey: 'google_calendar_button_typography_custom', group: 'google_calendar'}
				];
			},

			normalizeTypographyValue( value ) {
				if ( ! value ) return null;

				if ( typeof value === 'string' ) {
					try {
						value = JSON.parse( value );
					} catch ( error ) {
						return null;
					}
				}

				if ( typeof value !== 'object' || Array.isArray( value ) ) {
					return null;
				}

				return value;
			},

			typographyHasValues( value ) {
				const normalized = this.normalizeTypographyValue( value );

				if ( ! normalized ) {
					return false;
				}

				const colorKeys = ['color', 'backgroundColor', 'hoverColor', 'hoverBgColor'];

				return Object.keys( normalized ).some( key => {
					if ( colorKeys.includes( key ) ) {
						return false;
					}

					const item = normalized[key];
					return item !== '' && item !== null && item !== undefined;
				});
			},

			buildStylePreviewPayload() {
				const design = this.getStyleDesignRegistryEntry();
				const payload = {
					previewId: this.previewTempID,
					scopeClass: this.getTypographyScopeClass(),
					design_preset: {
						typography: this.nonReactive( design.typography || {} ),
						colors: this.nonReactive( design.colors || {} )
					},
					typography_field_custom: {},
					typography_overrides: {}
				};

				this.getTypographyWatchers().forEach(item => {
					const settingKey = item.key.replace('shortcode_settings.', '');
					const value = this.normalizeTypographyValue( this.shortcode_settings[settingKey] );
					const fieldCustom = this.getTypographyFieldCustom( item.group );

					payload.typography_field_custom[ item.messageKey ] = this.nonReactive( fieldCustom );
					payload.typography_overrides[ item.messageKey ] = value ? this.nonReactive( value ) : {};
				});

				payload.color_fields = {};

				this.getColorWatchers().forEach(item => {
					const fieldKey = item.fieldKey;
					const isCustom = this.isColorFieldCustom( fieldKey );
					const value = this.shortcode_settings[fieldKey] || '';

					if ( isCustom && value ) {
						payload.color_fields[fieldKey] = value;
					} else {
						payload.color_fields[fieldKey] = null;
					}
				});

				if ( this.shouldShowPopupDetailTypography() ) {
					const popupDesign = this.getPopupDetailDesignRegistryEntry();

					payload.popup_detail_scope_class = `gs_teca_popup_shortcode_${this.previewTempID}`;
					payload.popup_detail_design_preset = {
						typography: this.nonReactive( popupDesign.typography || {} ),
						colors: this.nonReactive( popupDesign.colors || {} )
					};
					payload.popup_detail_typography_field_custom = {};
					payload.popup_detail_typography_overrides = {};
					payload.popup_detail_color_fields = {};

					this.getPopupDetailTypographyGroups().forEach( group => {
						const value = this.normalizeTypographyValue( this.shortcode_settings[ group.setting_key ] );

						payload.popup_detail_typography_field_custom[ group.value ] = this.nonReactive( this.getPopupDetailTypographyFieldCustom( group.value ) );
						payload.popup_detail_typography_overrides[ group.value ] = value ? this.nonReactive( value ) : {};
					} );

					this.getPopupDetailColorFields().forEach( field => {
						const isCustom = this.isPopupDetailColorFieldCustom( field.value );
						const value = this.shortcode_settings[ field.value ] || '';

						payload.popup_detail_color_fields[ field.value ] = ( isCustom && value ) ? value : null;
					} );
				}

				return payload;
			},

			applyStylePreview() {
				const frame = this.getPreviewFrame();

				if ( ! frame || ! frame.contentWindow ) return;

				frame.contentWindow.postMessage( this.buildStylePreviewPayload(), '*' );
			},

			applyTypographyStyles() {
				this.applyStylePreview();
			},

			onPreviewFrameLoad() {
				this.applyStylePreview();
			},

			getShortcodeName() {
				return this.shortcode_name;
			},

			getShortcodeSettings( json = false ) {
				
				let shortcode_settings = this.nonReactive( this.shortcode_settings );

				let logo_cat = shortcode_settings.logo_cat;

				if ( logo_cat && typeof logo_cat == 'object' && logo_cat.length ) {
					shortcode_settings.logo_cat = logo_cat.join(',');
				}

				for ( let field in shortcode_settings ) {

					if ( typeof shortcode_settings[field] === "boolean" ) {
						shortcode_settings[field] = this.convertBooleanToString( shortcode_settings[field] );
					}

				}

				if ( json ) return JSON.stringify(shortcode_settings);

				return shortcode_settings;

			},

			setShortcodeSettings( settings ) {

				for ( let field in settings ) {

					if ( typeof settings[field] === "string" && (settings[field] === 'on' || settings[field] === 'off' ) ) {
						settings[field] = this.convertStringToBoolean( settings[field] );
					}

				}

				this.shortcode_settings = Object.assign( {}, this.shortcode_settings, settings );

				this.normalizePopupVisibilitySettings();

				this.setWatchers();

				this.$nextTick(() => {
					this.applyTypographyStyles();
				});

				if ( settings.logo_cat && typeof settings.logo_cat == 'string' ) {
					this.shortcode_settings.logo_cat = settings.logo_cat.split(',');
				}
				
				this.custom_image_size_width = this.shortcode_settings.custom_image_size_width;
				this.custom_image_size_height = this.shortcode_settings.custom_image_size_height;
				this.custom_image_size_crop = this.shortcode_settings.custom_image_size_crop;

				this.$forceUpdate();

			},

			fetchShortcode( shortcode_id ) {

				jQuery.ajax({
					url: this.getAjaxURL(),
					type: 'GET',
					cache: false,
					data: {
						action: 'gsteca_get_shortcode',
						id: shortcode_id,
						_wpnonce: this.getWPNonce('get_shortcode'),
					}
				})
				.done( response => {

					let shortcode = response.data;

					this.shortcode_name = shortcode.shortcode_name;
					this.setInitialSettings();
					this.setShortcodeSettings( shortcode.shortcode_settings );
					this.generatePreview();

				})
				.error( response => {

					this.notifyError( response );

				});

			},

			saveOrUpdateShortcode() {

				this.id ? this.updateShortcode() : this.saveShortcode();

			},

			saveShortcode() {

				let shortcode_name = this.getShortcodeName();
				let shortcode_settings = this.getShortcodeSettings();

				jQuery.ajax({
					url: this.getAjaxURL(),
					type: 'POST',
					cache: false,
					data: {
						action: 'gsteca_create_shortcode',
						_wpnonce: this.getWPNonce('create_shortcode'),
						shortcode_name: shortcode_name,
						shortcode_settings: shortcode_settings
					}
				})
				.done( response => {

					if ( response.success ) {
						console.log(response);
						notify({
							message: response.data.message,
							type: 'success'
						});

						let id = response.data.shortcode_id;

						return this.$router.push(`/shortcode/${id}`);
						
					}

					notify({
						message: response.data,
						type: 'info'
					});

				})
				.error( response => {

					this.notifyError( response );

				});

			},

			generatePreview() {
				this.saveTempSettings();
			},

			updatePreview() {
				let frame = jQuery(this.$el).find('#gs-teca-shortcode-preview-iframe');
				if ( frame.length ) frame[0].contentWindow.location.reload();
			},

			saveTempSettings() {

				let shortcode_name = this.getShortcodeName();
				let shortcode_settings = this.getShortcodeSettings();


				jQuery.ajax({
					url: this.getAjaxURL(),
					type: 'POST',
					cache: false,
					data: {
						action: 'gsteca_temp_save_shortcode_settings',
						_wpnonce: this.getWPNonce('temp_save_shortcode_settings'),
						temp_key: this.getPreviewTempID(),
						shortcode_settings: shortcode_settings
					}
				})
				.done( response => {

					if ( ! response.success ) return;

					if ( this.previewReady ) {
						this.updatePreview();
					} else {
						this.previewReady = true;
					}

				})
				.error( response => {

					this.notifyError( response );

				});

			},

			

			updateShortcode() {

				let shortcode_name = this.getShortcodeName();
				let shortcode_settings = this.getShortcodeSettings();

				jQuery.ajax({
					url: this.getAjaxURL(),
					type: 'POST',
					cache: false,
					data: {
						action: 'gsteca_update_shortcode',
						_wpnonce: this.getWPNonce('update_shortcode'),
						id: this.id,
						shortcode_name: shortcode_name,
						shortcode_settings: shortcode_settings
					}
				})
				.done( response => {

					if ( response.success ) {

						return notify({
							message: response.data.message,
							type: 'success'
						});

					}

					notify({
						message: response.data,
						type: 'info'
					});
				})
				.error( response => {

					this.notifyError( response );

				});

			},

			updateCustomSize() {
				this.$set( this.shortcode_settings, 'custom_image_size_width', this.custom_image_size_width );
				this.$set( this.shortcode_settings, 'custom_image_size_height', this.custom_image_size_height );
				this.$set( this.shortcode_settings, 'custom_image_size_crop', this.custom_image_size_crop );
			},

			setWatchers() {

				if ( this.is_watchers_set ) return;
				
				const Live_Watchers = [
					...this.getTypographyWatchers(),
					...this.getColorWatchers(),
					...this.getPopupDetailTypographyWatchers(),
					...this.getPopupDetailColorWatchers()
				];

				Object.keys(this.shortcode_settings).forEach( key => {

					const liveWatcherItem = Live_Watchers.find(item => item.key === `shortcode_settings.${key}`);
					
					if ( liveWatcherItem ) {
						this.liveWatcher( liveWatcherItem );
					} else {
						this.templateWatcher( key );
					}

				});

				this.templateWatcher( 'date_formats' );

				this.is_watchers_set = true;

			},

			getPreviewFrame() {
				if ( ! this.previewFrame ) this.previewFrame = document.getElementById('gs-teca-shortcode-preview-iframe');
				return this.previewFrame;
			},

			liveWatcher( item ) {
				const postStyleUpdate = () => {
					if ( ! this.getPreviewFrame() ) return;
					this.applyStylePreview();
				};

				this.$watch( item.key, postStyleUpdate, { deep: true } );

				if ( item.customKey ) {
					this.$watch( `shortcode_settings.${item.customKey}`, postStyleUpdate, { deep: true } );
				}
			},

			templateWatcher( key ) {
				
				this.$watch( `shortcode_settings.${key}`, () => {

					if (this.previewHandler) {
						clearTimeout(this.previewHandler);
					}
					this.previewHandler = setTimeout(() => {
						this.generatePreview();
					}, 200);

				}, { deep: true });

			}


		},

	}

</script>

<style scoped>
.post-center {
	table td:not(:first-child) {
		text-align: center;
	}
}

.popup-center {
	table td:nth-child(n+3) {
		text-align: center;
	}
}
</style>