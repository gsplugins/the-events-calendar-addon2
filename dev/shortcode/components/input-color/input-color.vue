<template>
	<div class="custom-color-picker">
		<label :for="id">
			<input type="color" :name="name" :class="className" :id="id" :value="value" :checked="value" :required="required">
			<span class="input-label">{{ label }}</span>
		</label>
	</div>
</template>

<script>

	require('./color-picker/color-picker.js');

	export default {
		props: {
			name: {
				type: String,
				required: false
			},
			className: {
				type: String,
				required: false
			},
			id: {
				type: String,
				required: false
			},
			value: {
				type: String,
				required: false,
				default: '#fff'
			},
			required: {
				type: Boolean,
				required: false,
				default: false
			},
			label: {
				type: String,
				required: false
			},
		},
		mounted() {
			var that = this;
			this.$input = jQuery(that.$el).find('input');
			
			this.$input.spectrum({
				color: that.value,
				showButtons: false,
				showAlpha: true,
				showInput: true,
				preferredFormat: "rgb",
				change: function(color) {
					that.$emit('input', color.toRgbString());
					window.Events.$emit('input-color:manually-changed');
				},
				move: function(color) {
					that.$emit('input', color.toRgbString());
					window.Events.$emit('input-color:manually-changed');
				}
			});
			
			jQuery(that.$el).find('label').on('click', function() {
				that.$input.spectrum("show");
				return false;
			});
		},
		watch: {
			value() {
				this.$input.spectrum("set", this.value);
			}
		}
	}

</script>