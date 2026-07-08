<template>
	<div class="custom-radio" :class="{ 'is-checked': checked }">
		<label :for="id">
			<input type="radio" :name="name" :class="className" :id="id" :value="value" :checked="checked" :required="required" @change="updateInput">
			<span class="input-ui"></span>
			<span class="input-label">{{ label }}</span>
		</label>
	</div>
</template>

<script>
export default {
	props: {
		model: {
			type: String,
			required: false
		},
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
			required: false
		},
		required: {
			type: Boolean,
			required: false,
			default: false
		},
		checked: {
			type: Boolean,
			required: false,
			default: false
		},
		label: {
			type: String,
			required: true
		},
	},
	methods: {
		updateInput: function(event) {
			this.$emit('update:model', event.target.value);
		}
	}
}
</script>
