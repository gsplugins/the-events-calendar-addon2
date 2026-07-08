<template>
	<div class="input-increment">
		<button @click.prevent="decrement">-</button>
		<input :id="id" type="text" :value="value" @input="updateField">
		<button @click.prevent="increment">+</button>
	</div>
</template>

<script>
	export default {

		props: ['min', 'max', 'step', 'value', 'id'],

		computed: {
			data_step() {
				return ( this.step ) ? Number(this.step) : 1;
			},

			data_min() {
				return ( this.min ) ? Number(this.min) : null;
			},

			data_max() {
				return ( this.max ) ? Number(this.max) : null;
			},
		},

		methods: {
			getValidField(val) {
				var fval = Number(val);
				if ( this.data_max !== null ) {
					fval = ( fval > this.data_max ) ? this.data_max : fval;
				}
				if ( this.data_min !== null ) {
					fval = ( fval <= this.data_min ) ? this.data_min : fval;
				}
				return fval;
			},
			increment() {
				var val = this.value;
				val += this.data_step;
				val = this.getValidField( val );
				this.$emit('input', val);
			},
			decrement() {
				var val = this.value;
				val -= this.data_step;
				val = this.getValidField( val );
				this.$emit('input', val);
			},
			updateField: function (event) {
				var val = this.getValidField( event.target.value );
				$(this.$el).find('input').val(val);
				this.$emit('input', val);
			}
		}
	}
</script>