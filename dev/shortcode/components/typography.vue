<template>
    <div :class="['group-control--wrapper typography-control', isHasValue ? 'group-has--values' : '', proLocked ? 'sh-disabled' : '']">
        <div class="group-control--trigger teca-style-control-actions">
            <a href="#" @click.prevent.stop="toggleControls" :title="editLabel" :aria-label="editLabel">
                <i class="zmdi zmdi-edit"></i>
            </a>
            <button
                type="button"
                class="teca-reset-typography"
                :class="{ 'is-resetting': isResetting }"
                :data-typography-target="typographyKey"
                :title="resetLabel"
                :aria-label="resetLabel"
                @click.stop="resetTypography"
            >
                <i class="zmdi zmdi-refresh"></i>
            </button>
        </div>

        <transition name="fade">
            <div class="group-control" v-if="showControls" @click.prevent.stop>

                <div class="gs-roow row-20">
                    <div class="gs-col-xs-5"><label class="m-t-10">Family:</label></div>
                    <div class="gs-col-xs-7">
                        <input-select v-model="localValue.getFonts" :options="getFonts()"
                            placeholder="Default"></input-select>
                    </div>
                </div>

                <div class="gs-roow row-20">
                    <div class="gs-col-xs-12"><label class="m-t-10">Size:</label></div>
                    <div class="gs-col-xs-12">
                        <div class="range-slider-container no-right-info">
                            <template v-if="device == 'desktop'">
                                <input-range :dot-size="16" :tooltip="false" :min="0" :max="200"
                                    v-model="localValue.size"></input-range>
                                <input class="slider-left-info" style="margin-right: 0px" v-model="localValue.size">
                            </template>
                            <template v-if="device == 'tablet'">
                                <input-range :dot-size="16" :tooltip="false" :min="0" :max="200"
                                    v-model="localValue.size_tablet"></input-range>
                                <input class="slider-left-info" style="margin-right: 0px" v-model="localValue.size_tablet">
                            </template>
                            <template v-if="device == 'mobile'">
                                <input-range :dot-size="16" :tooltip="false" :min="0" :max="200"
                                    v-model="localValue.size_mobile"></input-range>
                                <input class="slider-left-info" style="margin-right: 0px" v-model="localValue.size_mobile">
                            </template>
                        </div>
                    </div>
                </div>

                <div class="gs-roow row-20">
                    <div class="gs-col-xs-5"><label class="m-t-10">Weight:</label></div>
                    <div class="gs-col-xs-7">
                        <input-select v-model="localValue.weight" :options="getFontWeights()"
                            placeholder="Default"></input-select>
                    </div>
                </div>

                <div class="gs-roow row-20">
                    <div class="gs-col-xs-5"><label class="m-t-10">Transform:</label></div>
                    <div class="gs-col-xs-7">
                        <input-select v-model="localValue.transform" :options="getTextTransform()"
                            placeholder="Default"></input-select>
                    </div>
                </div>

                <div class="gs-roow row-20">
                    <div class="gs-col-xs-5"><label class="m-t-10">Style:</label></div>
                    <div class="gs-col-xs-7">
                        <input-select v-model="localValue.style" :options="getFontStyles()"
                            placeholder="Default"></input-select>
                    </div>
                </div>

                <div class="gs-roow row-20">
                    <div class="gs-col-xs-5"><label class="m-t-10">Decoration:</label></div>
                    <div class="gs-col-xs-7">
                        <input-select v-model="localValue.decoration" :options="getTextDecoration()"
                            placeholder="Default"></input-select>
                    </div>
                </div>

                <div class="gs-roow row-20">
                    <div class="gs-col-xs-12"><label class="m-t-10">Line Height:</label></div>
                    <div class="gs-col-xs-12">
                        <div class="range-slider-container no-right-info">
                            <template v-if="device == 'desktop'">
                                <input-range :dot-size="16" :step="0.1" :tooltip="false" :min="0" :max="10"
                                    v-model="localValue.lineHeight"></input-range>
                                <input class="slider-left-info" style="margin-right: 0px" v-model="localValue.lineHeight">
                            </template>
                            <template v-if="device == 'tablet'">
                                <input-range :dot-size="16" :step="0.1" :tooltip="false" :min="0" :max="10"
                                    v-model="localValue.lineHeight_tablet"></input-range>
                                <input class="slider-left-info" style="margin-right: 0px"
                                    v-model="localValue.lineHeight_tablet">
                            </template>
                            <template v-if="device == 'mobile'">
                                <input-range :dot-size="16" :step="0.1" :tooltip="false" :min="0" :max="10"
                                    v-model="localValue.lineHeight_mobile"></input-range>
                                <input class="slider-left-info" style="margin-right: 0px"
                                    v-model="localValue.lineHeight_mobile">
                            </template>
                        </div>
                    </div>
                </div>

                <div class="gs-roow row-20">
                    <div class="gs-col-xs-12"><label class="m-t-10">Letter Spacing:</label></div>
                    <div class="gs-col-xs-12">
                        <div class="range-slider-container no-right-info">
                            <template v-if="device == 'desktop'">
                                <input-range :dot-size="16" :step="0.1" :tooltip="false" :min="-5" :max="10"
                                    v-model="localValue.letterSpacing"></input-range>
                                <input class="slider-left-info" style="margin-right: 0px" v-model="localValue.letterSpacing">
                            </template>
                            <template v-if="device == 'tablet'">
                                <input-range :dot-size="16" :step="0.1" :tooltip="false" :min="-5" :max="10"
                                    v-model="localValue.letterSpacing_tablet"></input-range>
                                <input class="slider-left-info" style="margin-right: 0px"
                                    v-model="localValue.letterSpacing_tablet">
                            </template>
                            <template v-if="device == 'mobile'">
                                <input-range :dot-size="16" :step="0.1" :tooltip="false" :min="-5" :max="10"
                                    v-model="localValue.letterSpacing_mobile"></input-range>
                                <input class="slider-left-info" style="margin-right: 0px"
                                    v-model="localValue.letterSpacing_mobile">
                            </template>
                        </div>
                    </div>
                </div>

            </div>
        </transition>
    </div>
</template>

<script>
export default {
    data() {
        return {
            showControls: false,
            isResetting: false,
            isSyncingFromParent: false,
            instanceId: Math.random().toString(36).substring(2, 9),
            localValue: this.getDefaults()
        };
    },

    props: {
        device: {
            type: String,
            default: 'desktop'
        },
        typographyKey: {
            type: String,
            default: ''
        },
        typographyGroup: {
            type: String,
            default: ''
        },
        layoutDefaults: {
            type: Object,
            default() {
                return {};
            }
        },
        fieldCustom: {
            type: Object,
            default() {
                return {};
            }
        },
        value: {
            type: [Object, Array],
            required: true,
            default() {
                return this.getDefaults();
            }
        },
        proLocked: {
            type: Boolean,
            default: false
        },
        proLockedMessage: {
            type: String,
            default: 'This typography control is available in the Pro version only.'
        }
    },

    computed: {
        isHasValue() {
            return this.hasAnyFieldCustom();
        },
        editLabel() {
            return this.typographyKey
                ? `Edit ${this.formatTypographyLabel(this.typographyKey)} Typography`
                : 'Edit Typography';
        },
        resetLabel() {
            return this.typographyKey
                ? `Reset ${this.formatTypographyLabel(this.typographyKey)} Typography`
                : 'Reset Typography';
        }
    },

    mounted() {
        window.addEventListener('click', this.bodyClickHandler);
        window.addEventListener('typography-popup-open', this.onGlobalPopupOpen);
        this.syncLocalValueFromProp();
    },

    beforeDestroy() {
        window.removeEventListener('click', this.bodyClickHandler);
        window.removeEventListener('typography-popup-open', this.onGlobalPopupOpen);
    },

    methods: {
        showProLockedAlert() {
            alert(this.proLockedMessage);
        },

        guardProLocked() {
            if (this.proLocked) {
                this.showProLockedAlert();
                return true;
            }

            return false;
        },

        getControlToFieldMap() {
            return {
                getFonts: 'font_family',
                size: 'font_size',
                weight: 'font_weight',
                lineHeight: 'line_height',
                letterSpacing: 'letter_spacing',
                transform: 'text_transform',
                style: 'font_style',
                decoration: 'text_decoration'
            };
        },

        getEmptyFieldCustom() {
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

        hasAnyFieldCustom() {
            const flags = this.fieldCustom || {};
            return Object.values(flags).some(flag => !!flag);
        },

        isFieldCustom(fieldKey) {
            return !!(this.fieldCustom && this.fieldCustom[fieldKey]);
        },

        valuesEqual(a, b) {
            return String(a ?? '') === String(b ?? '');
        },

        normalizeTypographyValue(value) {
            if (!value) return {};
            if (typeof value === 'string') {
                try {
                    value = JSON.parse(value);
                } catch (error) {
                    return {};
                }
            }
            if (typeof value !== 'object' || Array.isArray(value)) {
                return {};
            }
            return value;
        },

        buildDisplayValue() {
            const display = this.normalizeControlDefaults(
                Object.assign({}, this.getDefaults(), this.layoutDefaults || {})
            );
            const overrides = this.normalizeTypographyValue(this.value);
            const map = this.getControlToFieldMap();

            Object.keys(map).forEach(controlKey => {
                const fieldKey = map[controlKey];

                if (this.isFieldCustom(fieldKey) && overrides[controlKey] !== undefined && overrides[controlKey] !== '') {
                    display[controlKey] = overrides[controlKey];
                }
            });

            return this.normalizeControlDefaults(display);
        },

        normalizeControlDefaults(values) {
            const normalized = Object.assign({}, values || {});

            if (normalized.size !== '' && normalized.size !== null && normalized.size !== undefined) {
                normalized.size = Number(normalized.size);
            }
            if (normalized.weight !== '' && normalized.weight !== null && normalized.weight !== undefined) {
                normalized.weight = String(normalized.weight);
            }
            if (normalized.lineHeight !== '' && normalized.lineHeight !== null && normalized.lineHeight !== undefined) {
                normalized.lineHeight = Number(normalized.lineHeight);
            }
            if (normalized.letterSpacing !== '' && normalized.letterSpacing !== null && normalized.letterSpacing !== undefined) {
                normalized.letterSpacing = Number(normalized.letterSpacing);
            }

            return normalized;
        },

        buildSparsePayload(localValue) {
            const map = this.getControlToFieldMap();
            const payload = {};
            const nextFlags = this.getEmptyFieldCustom();

            Object.keys(map).forEach(controlKey => {
                const fieldKey = map[controlKey];
                const defaultVal = (this.layoutDefaults && this.layoutDefaults[controlKey] !== undefined)
                    ? this.layoutDefaults[controlKey]
                    : '';
                const nextVal = localValue[controlKey];
                const isCustom = !this.valuesEqual(nextVal, defaultVal)
                    && nextVal !== ''
                    && nextVal !== null
                    && nextVal !== undefined;

                nextFlags[fieldKey] = isCustom;

                if (isCustom) {
                    payload[controlKey] = nextVal;
                }
            });

            return { payload, nextFlags };
        },

        getDefaults() {
            return {
                getFonts: '',
                size: '',
                size_tablet: '',
                size_mobile: '',
                weight: '',
                transform: '',
                style: '',
                decoration: '',
                lineHeight: '',
                lineHeight_tablet: '',
                lineHeight_mobile: '',
                letterSpacing: '',
                letterSpacing_tablet: '',
                letterSpacing_mobile: ''
            };
        },

        typographyHasValues(value) {
            if (!value || typeof value !== 'object') {
                return false;
            }

            const colorKeys = ['color', 'backgroundColor', 'hoverColor', 'hoverBgColor'];

            return Object.keys(value).some(key => {
                if (colorKeys.includes(key)) {
                    return false;
                }

                const item = value[key];
                return item !== '' && item !== null && item !== undefined;
            });
        },

        formatTypographyLabel(key) {
            return key.charAt(0).toUpperCase() + key.slice(1);
        },

        resetTypography(event) {
            if (this.guardProLocked()) {
                return;
            }

            if (event) {
                event.preventDefault();
                event.stopPropagation();
            }

            if (this.isResetting) {
                return;
            }

            this.isResetting = true;
            this.isSyncingFromParent = true;
            this.showControls = false;
            this.$emit('update:fieldCustom', this.getEmptyFieldCustom());
            this.$emit('input', {});
            this.localValue = Object.assign({}, this.getDefaults(), this.normalizeControlDefaults(this.layoutDefaults || {}));
            this.$emit('reset', this.typographyKey || null);

            this.$nextTick(() => {
                this.isSyncingFromParent = false;
                this.isResetting = false;
            });
        },
        toggleControls() {
            if (this.guardProLocked()) {
                return;
            }

            this.showControls = !this.showControls;
            if (this.showControls) {
                window.dispatchEvent(new CustomEvent('typography-popup-open', {
                    detail: this.instanceId
                }));
            }
        },

        bodyClickHandler() {
            this.showControls = false;
        },

        onGlobalPopupOpen(e) {
            if (e.detail !== this.instanceId) {
                this.showControls = false;
            }
        },

        nonReactive(obj) {
            return JSON.parse(JSON.stringify(obj));
        },

        getFontWeights() {
            return [
                { label: '100', value: '100' },
                { label: '200', value: '200' },
                { label: '300', value: '300' },
                { label: '400', value: '400' },
                { label: '500', value: '500' },
                { label: '600', value: '600' },
                { label: '700', value: '700' },
                { label: '800', value: '800' },
                { label: '900', value: '900' },
                { label: 'Default', value: '' },
                { label: 'Normal', value: 'normal' },
                { label: 'Bold', value: 'bold' }
            ];
        },

        getTextTransform() {
            return [
                { label: 'Default', value: '' },
                { label: 'Uppercase', value: 'uppercase' },
                { label: 'Lowercase', value: 'lowercase' },
                { label: 'Capitalize', value: 'capitalize' },
                { label: 'Normal', value: 'normal' }
            ];
        },

        getFontStyles() {
            return [
                { label: 'Default', value: '' },
                { label: 'Normal', value: 'normal' },
                { label: 'Italic', value: 'italic' },
                { label: 'Oblique', value: 'oblique' }
            ];
        },

        getTextDecoration() {
            return [
                { label: 'Default', value: '' },
                { label: 'Underline', value: 'underline' },
                { label: 'Overline', value: 'overline' },
                { label: 'Line Through', value: 'line-through' },
                { label: 'None', value: 'none' }
            ];
        },

        updateValue() {
            if (this.isSyncingFromParent || this.isResetting) {
                return;
            }

            if (this.proLocked) {
                this.showProLockedAlert();
                this.syncLocalValueFromProp();
                return;
            }

            const { payload, nextFlags } = this.buildSparsePayload(this.localValue);
            const currentPayload = this.normalizeTypographyValue(this.value);

            if (JSON.stringify(payload) === JSON.stringify(currentPayload)
                && JSON.stringify(nextFlags) === JSON.stringify(this.fieldCustom || {})) {
                return;
            }

            this.$emit('update:fieldCustom', nextFlags);
            this.$emit('input', payload);
        },

        syncLocalValueFromProp() {
            this.isSyncingFromParent = true;
            this.localValue = this.buildDisplayValue();

            this.$nextTick(() => {
                this.isSyncingFromParent = false;
            });
        }
    },
    watch: {
        value: {
            handler() {
                if (this.isResetting) {
                    return;
                }

                this.syncLocalValueFromProp();
            },
            deep: true
        },
        fieldCustom: {
            handler() {
                if (this.isResetting) {
                    return;
                }

                this.syncLocalValueFromProp();
            },
            deep: true
        },
        layoutDefaults: {
            handler() {
                if (this.isResetting) {
                    return;
                }

                this.syncLocalValueFromProp();
            },
            deep: true
        },
        localValue: {
            handler() {
                this.updateValue();
            },
            deep: true
        }
    }
};
</script>
