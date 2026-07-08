<template>
	<div class="custom-checkbox" :class="{ 'is-checked': value }">
		<label :for="id">
			<input type="checkbox" :name="name" :class="className" :id="id" :value="value" :checked="value" :required="required" @change="updateInput">
			<span class="input-ui"></span>
			<span class="input-label" v-if="label">{{ label }}</span>
		</label>
	</div>
</template>

<script>
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
			type: Boolean,
			required: false,
			default: false
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
	methods: {
		updateInput: function(event) {
			this.$emit('input', event.target.checked);
		}
	}
}
</script>
