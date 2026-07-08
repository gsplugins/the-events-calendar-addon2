<template>
    <div :class="['teca-style-control-row teca-color-field', isHasValue ? 'teca-color-field--active' : '']">
        <div class="teca-style-control-label">
            <label>{{ label }}</label>
        </div>
        <div class="teca-style-control-actions teca-color-field__controls">
            <input-color v-model="localValue"></input-color>
            <button
                type="button"
                class="teca-reset-color"
                :class="{ 'is-resetting': isResetting }"
                :data-color-target="fieldKey"
                :title="resetLabel"
                :aria-label="resetLabel"
                @click.stop="resetColor"
            >
                <i class="zmdi zmdi-refresh"></i>
            </button>
        </div>
    </div>
</template>

<script>
export default {
    props: {
        fieldKey: {
            type: String,
            required: true
        },
        label: {
            type: String,
            required: true
        },
        value: {
            type: String,
            default: ''
        },
        defaultValue: {
            type: String,
            default: ''
        },
        isCustom: {
            type: Boolean,
            default: false
        }
    },

    data() {
        return {
            localValue: '',
            isResetting: false,
            isSyncingFromParent: false
        };
    },

    computed: {
        isHasValue() {
            return this.isCustom;
        },
        resetLabel() {
            return `Reset ${this.label}`;
        },
        displayDefault() {
            return this.defaultValue || '';
        }
    },

    mounted() {
        this.syncFromProp();
    },

    methods: {
        resetColor(event) {
            if (event) {
                event.preventDefault();
                event.stopPropagation();
            }

            if (this.isResetting) {
                return;
            }

            this.isResetting = true;
            this.isSyncingFromParent = true;
            this.localValue = this.displayDefault;
            this.$emit('input', '');
            this.$emit('update:isCustom', false);
            this.$emit('reset', this.fieldKey);

            this.$nextTick(() => {
                this.isSyncingFromParent = false;
                this.isResetting = false;
            });
        },

        syncFromProp() {
            this.isSyncingFromParent = true;

            if (this.isCustom && this.value) {
                this.localValue = this.value;
            } else {
                this.localValue = this.displayDefault;
            }

            this.$nextTick(() => {
                this.isSyncingFromParent = false;
            });
        },

        valuesEqual(a, b) {
            return String(a || '').toLowerCase() === String(b || '').toLowerCase();
        },

        updateValue() {
            if (this.isSyncingFromParent || this.isResetting) {
                return;
            }

            const next = this.localValue || '';
            const isCustom = !!next && !this.valuesEqual(next, this.displayDefault);

            if (isCustom) {
                if (this.valuesEqual(next, this.value) && this.isCustom) {
                    return;
                }

                this.$emit('update:isCustom', true);
                this.$emit('input', next);
                return;
            }

            if (!this.isCustom && this.valuesEqual(next, this.displayDefault)) {
                return;
            }

            this.$emit('update:isCustom', false);
            this.$emit('input', '');
        }
    },

    watch: {
        value() {
            if (this.isResetting) {
                return;
            }

            this.syncFromProp();
        },
        defaultValue() {
            if (this.isResetting) {
                return;
            }

            this.syncFromProp();
        },
        isCustom() {
            if (this.isResetting) {
                return;
            }

            this.syncFromProp();
        },
        localValue() {
            this.updateValue();
        }
    }
};
</script>
