<template>
    <div class="gs-containeer layout-container">
        <div class="gs-teca-box">
            <div class="top-section head-section">
                <h2>{{ sectionHeading }}</h2>
            </div>

            <div class="bottom-section">
                <!-- Sidebar -->
                <aside class="gs-teca-layout__sidebar">
                    <ul class="gs-nav">
                        <!-- simple items -->
                        <li
                            class="gs-nav__item"
                            :class="{ 'is-active': isActive('singular') }"
                            @click="activate('singular')"
                        >
                            <span class="gs-nav__icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" width="16" height="16" stroke="currentColor" fill="none" stroke-width="2"><path d="M7 3h6l5 5v13a1 1 0 0 1-1 1H7a1 1 0 0 1-1-1V4z"/><path d="M13 3v6h6"/></svg>
                            </span>
                            <span class="gs-nav__label">Single Event</span>
                        </li>

                        <!-- archive group -->
                        <li class="gs-nav__group">
                            <button
                                class="gs-nav__group-btn"
                                :class="{ 'is-open': openGroups.archive }"
                                @click="toggleGroup('archive')"
                                type="button"
                            >
                                <span class="gs-nav__icon" v-html="icons.archive"></span>
                                <span class="gs-nav__label">Archive</span>
                                <span class="gs-nav__caret" aria-hidden="true"></span>
                            </button>

                            <ul class="gs-sub" v-show="openGroups.archive">
                                <li 
                                    class="gs-sub__item"
                                    :class="{ 'is-active': activeMain==='archive' && activeSub === 'category' }"
                                    @click="activate({ type:'sub', main:'archive', key: 'category' })">
                                    <span class="gs-sub__dot" aria-hidden="true"></span>
                                    <span class="gs-sub__label">Category</span>
                                </li>

                                <li 
                                    class="gs-sub__item"
                                    :class="{ 'is-active': activeMain==='archive' && activeSub === 'tags' }"
                                    @click="activate({ type:'sub', main:'archive', key: 'tags' })">
                                    <span class="gs-sub__dot" aria-hidden="true"></span>
                                    <span class="gs-sub__label">Tags</span>
                                </li>
                            </ul>
                        </li>
                    </ul>
                </aside>

                <!-- Content -->
                <section class="gs-teca-layout__content">
                    <div class="gs-grid">

                        <div v-if="isActive('singular')">
                            <div class="shortcode-setting--row">
                                <div class="gs-roow row-20">
                                    <div class="gs-col-xs-12">
                                        <label class="m-t-10" for="single_page_style"
                                            >{{ translation("single_teca_page") }}:</label
                                        >
                                    </div>

                                    <div class="gs-col-xs-12">
                                        <input-select class="" key="single_page_style" id="single_page_style" v-model="layout.single_page_style" :options="layout_options.single_page_style" :placeholder="translation('single_page_style')" :pro-locked-message="translation('single_page_style_pro_message')"></input-select>
                                    </div>
                                </div>
                            </div>

                            <div class="shortcode-setting--row m-t-20">
                                <div class="gs-roow row-20">
                                    <div class="gs-col-xs-12">
                                        <label class="m-t-10" for="single_date_format">{{ translation('date_format') }}:</label>
                                    </div>
                                    <div class="gs-col-xs-12">
                                        <input-select
                                            :key="'single-date-format-' + getSingleDateFormatLayoutKey()"
                                            id="single_date_format"
                                            v-model="getSingleDateFormatConfig().format"
                                            :options="layout_options.date_format_presets || []"
                                            :placeholder="translation('date_format')"
                                        ></input-select>
                                        <div v-if="getSingleDateFormatConfig().format === 'custom'" class="m-t-10">
                                            <label for="single_custom_date_format">{{ translation('custom_date_format') }}:</label>
                                            <input
                                                id="single_custom_date_format"
                                                type="text"
                                                class="form-control m-t-5"
                                                v-model="getSingleDateFormatConfig().custom"
                                                :placeholder="translation('custom_date_format')"
                                            />
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="shortcode-setting--row m-t-20">
                                <div class="gs-roow row-20">
                                    <div class="gs-col-xs-12">
                                        <label class="m-t-10" for="show_related_events">{{ translation('show_related_events') }}:</label>
                                    </div>
                                    <div class="gs-col-xs-12">
                                        <input-toggle name="layout_show_related_events" v-model="layout.show_related_events" offLabel="Off" onLabel="On"></input-toggle>
                                    </div>
                                </div>
                            </div>

                            <template v-if="isToggleOn(layout.show_related_events)">
                                <div class="shortcode-setting--row m-t-20">
                                    <div class="gs-roow row-20">
                                        <div class="gs-col-xs-12">
                                            <label class="m-t-10" for="layout_related_events_title">{{ translation('related_events_title') }}:</label>
                                        </div>
                                        <div class="gs-col-xs-12">
                                            <input id="layout_related_events_title" type="text" class="form-control" v-model="layout.related_events_title" />
                                        </div>
                                    </div>
                                </div>

                                <div class="shortcode-setting--row m-t-20">
                                    <div class="gs-roow row-20">
                                        <div class="gs-col-xs-12">
                                            <label class="m-t-10" for="layout_related_events_limit">{{ translation('related_events_limit') }}:</label>
                                        </div>
                                        <div class="gs-col-xs-12">
                                            <input-increment name="layout_related_events_limit" v-model="layout.related_events_limit" :min="1" :max="12"></input-increment>
                                        </div>
                                    </div>
                                </div>

                                <div class="shortcode-setting--row m-t-20">
                                    <div class="gs-roow row-20">
                                        <div class="gs-col-xs-12">
                                            <label class="m-t-10" for="layout_related_events_sources">{{ translation('related_events_sources') }}:</label>
                                        </div>
                                        <div class="gs-col-xs-12">
                                            <input-select
                                                key="layout_related_events_sources"
                                                id="layout_related_events_sources"
                                                v-model="layout.related_events_sources"
                                                :options="layout_options.related_events_sources || []"
                                                :placeholder="translation('related_events_sources')"
                                                multiple
                                            ></input-select>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>

                        <div v-if="activeSub === 'category'">

                            <div class="shortcode-setting--row shortcode-setting--row-v2">
                                <label class="m-t-10" for="event_cat">{{translation('event_cat')}}:</label>
                                <input-toggle class="m-t-6" name="event_cat" v-model="layout.event_cat" offLabel="Off" onLabel="On"></input-toggle>
                            </div>
                            
                            <template v-if="layout.event_cat">

                                <div class="shortcode-setting--row">

                                    <div class="gs-roow row-20">

                                        <div class="gs-col-xs-12">
                                            <label class="m-t-10" for="event_cat_shortcode">{{translation('event_select_shortcode')}}:</label>
                                        </div>

                                        <div class="gs-col-xs-12">
                                            <input-select key="event_cat_shortcode" id="event_cat_shortcode" v-model="layout.event_cat_shortcode" :options="layout_options.shortcodes" :placeholder="translation('posts_select_shortcode')"></input-select>
                                        </div>

                                    </div>
                                
                                </div>

                                <div class="shortcode-setting--row">

                                    <div class="gs-roow row-20">

                                        <div class="gs-col-xs-12">
                                            <label class="m-t-10" for="event_cat_replace_type">{{translation('event_replace_type')}}:</label>
                                        </div>

                                        <div class="gs-col-xs-12">
                                            <input-select key="event_cat_replace_type" id="event_cat_replace_type" v-model="layout.event_cat_replace_type" :options="layout_options.replace_types" :placeholder="translation('posts_replace_type')"></input-select>
                                        </div>

                                    </div>
                                
                                </div>

                            </template>

                        </div>

                        <div v-if="activeSub === 'tags'">

                            <div class="shortcode-setting--row shortcode-setting--row-v2">
                                <label class="m-t-10" for="event_tag">{{translation('event_tag')}}:</label>
                                <input-toggle class="m-t-6" name="event_tag" v-model="layout.event_tag" offLabel="Off" onLabel="On"></input-toggle>
                            </div>
                            
                            <template v-if="layout.event_tag">

                                <div class="shortcode-setting--row">

                                    <div class="gs-roow row-20">

                                        <div class="gs-col-xs-12">
                                            <label class="m-t-10" for="event_tag_shortcode">{{translation('event_select_shortcode')}}:</label>
                                        </div>

                                        <div class="gs-col-xs-12">
                                            <input-select key="event_tag_shortcode" id="event_tag_shortcode" v-model="layout.event_tag_shortcode" :options="layout_options.shortcodes" :placeholder="translation('posts_select_shortcode')"></input-select>
                                        </div>

                                    </div>
                                
                                </div>

                                <div class="shortcode-setting--row">

                                    <div class="gs-roow row-20">

                                        <div class="gs-col-xs-12">
                                            <label class="m-t-10" for="event_tag_replace_type">{{translation('event_replace_type')}}:</label>
                                        </div>

                                        <div class="gs-col-xs-12">
                                            <input-select key="event_tag_replace_type" id="event_tag_replace_type" v-model="layout.event_tag_replace_type" :options="layout_options.replace_types" :placeholder="translation('posts_replace_type')"></input-select>
                                        </div>

                                    </div>
                                
                                </div>

                            </template>

                        </div>

                    </div>
                </section>
            </div>
            <button class="btn btn-brand btn-sm m-t-30" @click.prevent.stop="saveOrUpdateLayout"><i class="zmdi zmdi-floppy"></i><span>Save Layout</span></button>
        </div>
    </div>
</template>

<script>
import notify from '../includes/notify';
export default {

    name: 'LayoutPage',
    
    data() {
        return {
            layout: {
                event_cat: false,
                event_cat_shortcode: '',
                event_cat_replace_type: 'no_change',
                event_tag: false,
                event_tag_shortcode: '',
                event_tag_replace_type: 'no_change',
                single_page_style: 'default',
                date_formats: {}
            },
            layout_options: {},  
            // active location
            activeMain: 'archive',
            activeSub: 'category',
            // groups open/close
            openGroups: { archive: true },

            // icons (inline svgs, raw)
            icons: {
                single:'<svg viewBox="0 0 24 24" width="16" height="16" stroke="currentColor" fill="none" stroke-width="2"><path d="M7 3h6l5 5v13a1 1 0 0 1-1 1H7a1 1 0 0 1-1-1V4z"/><path d="M13 3v6h6"/></svg>',
                search:'<svg viewBox="0 0 24 24" width="16" height="16" stroke="currentColor" fill="none" stroke-width="2"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4.3-4.3"/></svg>',
                archive:'<svg viewBox="0 0 24 24" width="16" height="16" stroke="currentColor" fill="none" stroke-width="2"><rect x="3" y="4" width="18" height="4"/><path d="M5 8v11a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8"/></svg>',
            },

            // simple (non-group) items
            flatItems: [
                { key: 'single', label: 'Singular', icon: null, type: 'item' }
            ],

            // archive children
            archiveChildren: [
                { key: 'category', label: 'Category' },
                { key: 'tags', label: 'Tags' },
                { key: 'date', label: 'Date' }
            ],
        };
    },

    mounted() {

        this.stopWatcher = false;
        this.setInitialSettings();
        this.get_layout();
        this.getLayoutOptions();
        this.initHelpText();
        Events.$emit('editor-sm:update-value');

    },

    created() {
        // attach SVGs to flat items
        this.flatItems = this.flatItems.map(it => ({ ...it, icon: this.icons[it.key] || this.icons.all }));
    },

    computed: {

        currentSubLabel() {
            const found = this.archiveChildren.find(c => c.key === this.activeSub);
            return found ? found.label : '';
        },

        sectionHeading() {
            if (this.activeMain === "archive") {
                return `${this.currentSubLabel} Archive Template`;
            }
            const cur = this.flatItems.find((i) => i.key === this.activeMain);
            return cur ? cur.label : "Single Page Template";
        },

        shownTemplates() {
            // keep demo simple; in real app, filter by activeMain/activeSub
            return this.templates;
        }
    },

    methods: {

        isToggleOn( value ) {
            return value === true || value === 'on' || value === 1 || value === '1';
        },

        toggleGroup(key) {
            this.openGroups[key] = !this.openGroups[key];
        },

        isActive(item) {
            return this.activeMain === item.key;
        },

        activate(item) {
            if (item.type === 'sub') {
                this.activeMain = item.main;
                this.activeSub  = item.key;
                return;
            }
            this.activeMain = item.key;
            if (item.key !== 'archive') this.activeSub = '';
        },

        getLayout( json = false ) {
            let layout = this.nonReactive( this.layout );
            layout = this.normalizeLayoutForSave( layout );
            if ( json ) return JSON.stringify(layout);
            return layout;
        },

        normalizeLayoutForSave( layout ) {
            const normalized = { ...layout };

            if ( normalized.single_page_style && typeof normalized.single_page_style === 'object' ) {
                normalized.single_page_style = normalized.single_page_style.value || 'default';
            }

            if ( ! normalized.date_formats || typeof normalized.date_formats !== 'object' || Array.isArray( normalized.date_formats ) ) {
                normalized.date_formats = {};
            }

            if ( typeof normalized.show_related_events === 'boolean' ) {
                normalized.show_related_events = normalized.show_related_events ? 'on' : 'off';
            }

            return normalized;
        },

        normalizeSinglePageStyleKey( raw ) {
            if ( raw && typeof raw === 'object' ) {
                raw = raw.value || 'default';
            }

            const map = {
                '': 'default',
                default: 'default',
                'style-one': 'style-1',
                'style-two': 'style-2',
                'style-three': 'style-3',
                'style-four': 'style-4',
                'style-five': 'style-5',
                'style-1': 'style-1',
                'style-2': 'style-2',
                'style-3': 'style-3',
                'style-4': 'style-4',
                'style-5': 'style-5',
            };

            const key = String( raw || 'default' ).toLowerCase().trim();
            return map[ key ] || 'default';
        },

        getSingleDateFormatLayoutKey() {
            return `single:${this.normalizeSinglePageStyleKey( this.layout.single_page_style )}`;
        },

        ensureLayoutDateFormatsObject() {
            if ( ! this.layout.date_formats || typeof this.layout.date_formats !== 'object' || Array.isArray( this.layout.date_formats ) ) {
                this.$set( this.layout, 'date_formats', {} );
            }
        },

        getSingleDateFormatConfig() {
            this.ensureLayoutDateFormatsObject();
            const key = this.getSingleDateFormatLayoutKey();

            if ( ! this.layout.date_formats[ key ] ) {
                this.$set( this.layout.date_formats, key, { format: 'default', custom: '' } );
            }

            return this.layout.date_formats[ key ];
        },

        normalizeLayoutFromServer( layout ) {
            const normalized = { ...layout };

            if ( normalized.single_page_style && typeof normalized.single_page_style === 'object' ) {
                normalized.single_page_style = normalized.single_page_style.value || 'default';
            }

            if ( ! Array.isArray( normalized.related_events_sources ) ) {
                normalized.related_events_sources = [ 'category', 'tag', 'venue', 'organizer', 'upcoming' ];
            }

            if ( typeof normalized.related_events_limit === 'undefined' ) {
                normalized.related_events_limit = 3;
            }

            if ( typeof normalized.related_events_title === 'undefined' ) {
                normalized.related_events_title = 'Related Events';
            }

            if ( typeof normalized.show_related_events === 'undefined' ) {
                normalized.show_related_events = 'on';
            }

            if ( normalized.show_related_events === 'on' || normalized.show_related_events === true || normalized.show_related_events === 1 || normalized.show_related_events === '1' ) {
                normalized.show_related_events = true;
            } else if ( normalized.show_related_events === 'off' || normalized.show_related_events === false || normalized.show_related_events === 0 || normalized.show_related_events === '0' ) {
                normalized.show_related_events = false;
            }

            return normalized;
        },

        setInitialSettings() {
            const layoutOptions = this.applyProLocksToLayoutOptions( this._getLayoutOptions() );
            this.$set( this, 'layout_options', layoutOptions );
            this.setLayout( this._getLayout() );
        },

        getLayoutOptions() {
            let self = this;
            jQuery.ajax({
                url: this.getAjaxURL(),
                type: 'GET',
                data: {
                    action: 'gsteca_get_layout_options',
                    _wpnonce: this.getWPNonce('get_shortcode_layout_options')
                }
            })
            .done(function(response) {
                if (response && response.data && response.success) {
                    self.layout_options = self.applyProLocksToLayoutOptions( response.data );
                }
            })
            .fail(function(response) {
                console.error(response);
            });
        },

        setLayout( settings ) {
            this.layout = Object.assign( {}, this.layout, settings );
        },  
        
        get_layout() {
            let self = this;

            jQuery.ajax({
                url: this.getAjaxURL(),
                type: 'GET',
                data: {
                    action: 'gsteca_get_shortcode_layout',
                    _wpnonce: this.getWPNonce('get_shortcode_layout')
                }
            })
            .done(function(response) {
                if (response && response.data && response.success) {
                    const layout = self.normalizeLayoutFromServer( response.data );

                    // Set entire preference object once
                    self.layout = {
                        ...self.layout,
                        ...layout,
                        // gs_logo_slider_custom_css: layout.gs_logo_slider_custom_css || '' 
                    };
                    
                }
            })
            .fail(function(response) {
                console.error(response);
            });
        },

        saveOrUpdateLayout() {

            let self = this;

            jQuery.ajax({
                url: this.getAjaxURL(),
                type: 'POST',
                cache: false,
                data: {
                    action: 'gsteca_save_shortcode_layout',
                    _wpnonce: this.getWPNonce('save_shortcode_layout'),
                    layout: this.getLayout()
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

        }
    }
};
</script>

<style scoped>

    .bottom-section {
        display: flex;
        width: 980px;
        max-width: 100%;
        margin-top: 30px;
        gap: 30px;
    }

    .head-section h2 {
        font-size: 20px;
        font-weight: 600;
    }

    .gs-teca-layout__sidebar {
        width: 35%;
        border: 1px solid #e5eaf2;
        border-radius: 10px;
        padding: 8px;
    }

    .gs-teca-layout__content {
        width: 65%;
    }

    .shortcode-setting--row-v2 {
        display: flex;
        justify-content: space-between;
    }

    .gs-nav {
        list-style:none;
        margin:0;
        padding:0
    }

    /* Simple item */
    .gs-nav__item{display:flex;align-items:center;gap:10px;padding:10px 12px;border-radius:10px;cursor:pointer;color:#334155}
    .gs-nav__item:hover{background:#f1f5f9}
    .gs-nav__item.is-active{background:#5b6be8;color:#fff;box-shadow:0 8px 20px rgba(15,23,42,.08)}
    .gs-nav__icon{width:24px;height:24px;display:grid;place-items:center;background:#e2e8f0;border-radius:6px}
    .gs-nav__item.is-active .gs-nav__icon{background:rgba(255,255,255,.16)}
    .gs-nav__label{font-size:14px}

    /* Group */
    .gs-nav__group{margin-top:2px}
    .gs-nav__group-btn{width:100%;display:flex;align-items:center;gap:10px;padding:10px 12px;border-radius:10px;border:none;background:transparent;color:#334155;cursor:pointer}
    .gs-nav__group-btn:hover{background:#f1f5f9}
    .gs-nav__group-btn .gs-nav__icon{width:24px;height:24px;display:grid;place-items:center;background:#e2e8f0;border-radius:6px}
    .gs-nav__caret{margin-left:auto;width:0;height:0;border-left:5px solid transparent;border-right:5px solid transparent;border-top:6px solid #94a3b8;transition:transform .15s ease}
    .gs-nav__group-btn.is-open .gs-nav__caret{transform:rotate(180deg)}
    .gs-sub{list-style:none;margin:4px 0 6px 12px;padding:6px 0 6px 10px;border-left:2px solid #e2e8f0}
    .gs-sub__item{display:flex;align-items:center;gap:10px;padding:8px 10px;border-radius:8px;cursor:pointer;color:#334155}
    .gs-sub__item:hover{background:#f1f5f9}
    .gs-sub__item.is-active{background:#eef2ff;color:#1e2fd2}
    .gs-sub__dot{width:6px;height:6px;border-radius:50%;background:#cbd5e1}
    .gs-sub__label{font-size:13px}


    /* Buttons */
    .gs-btn{display:inline-flex;align-items:center;gap:6px;height:30px;padding:0 10px;font-size:12px;font-weight:700;border-radius:10px;border:none;cursor:pointer}
    .gs-btn--success{background:#10b981;color:#fff}
    .gs-btn--success:hover{background:#059669}
    .gs-btn--pro{background:#f97316;color:#fff}
    .gs-btn--pro:hover{background:#ea580c}

</style>