<template>
    <div class="custom-code-editor-cm" :class="className">
        <textarea name="code" :id="id" :mode="mode" v-model="computedValue"></textarea>
    </div>
</template>

<script>

    const CodeMirror = require('../codemirror/lib/codemirror.js');

    require('../codemirror/mode/javascript/javascript.js');
    require('../codemirror/mode/css/css.js');
    require('../codemirror/addon/hint/show-hint.js');

    export default {
        props: {
            value: {
                default: ''
            },
            className: {
                type: String,
                required: false
            },
            mode: {
                type: String,
                default: 'css'
            },
            id: {
                type: String,
                default: null,
                required: true
            }
        },
        computed: {
            computedValue() {
                return this.value;
            }
        },
        mounted() {

            this.editor = CodeMirror.fromTextArea(jQuery(this.$el).find('#'+this.id)[0], {
                lineNumbers: true,
                mode: this.mode,
                theme: 'monokai'
            });

            this.editor.on('change', function(instance){
                this.$emit('input', instance.getValue());
            }.bind(this));

            if ( this.value ) {
                this.editor.setValue( this.value );
            }

            Events.$on('editor-sm:update-value', this.updateValue);
        },
        methods: {
            updateValue() {
                let handler = setTimeout(function() {
                    clearTimeout( handler );
                    this.editor.setValue( this.value );
                }.bind(this), 100);
            }
        }
    }

</script>