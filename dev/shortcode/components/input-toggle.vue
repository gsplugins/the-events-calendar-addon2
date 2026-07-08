<template>

	<div class="custom-toggle-button--wrapper" :class="{ 'is-checked': isChecked }">
		<div class="custom-toggle-button" :class="{ 'is-checked': isChecked }">
			<label :for="id">
				<input type="checkbox" :name="name" :id="id" :value="isChecked" :checked="isChecked" :required="required" @change="updateInput">
			</label>
		</div>
		<span class="custom-toggle--status" v-if="offLabel && ! isChecked">{{offLabel}}</span>
		<span class="custom-toggle--status" v-if="onLabel && isChecked">{{onLabel}}</span>
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
		offLabel: {
			type: String,
			required: false,
		},
		onLabel: {
			type: String,
			required: false,
		},
		id: {
			type: String,
			required: false
		},
		value: {
			required: false,
			default: false
		},
		required: {
			type: Boolean,
			required: false,
			default: false
		},
	},
	computed: {
		isChecked() {
			return this.value === true || this.value === 'on' || this.value === 1 || this.value === '1';
		},
	},
	methods: {
		updateInput: function(event) {
			this.$emit('input', event.target.checked);
		}
	}
}
</script>
