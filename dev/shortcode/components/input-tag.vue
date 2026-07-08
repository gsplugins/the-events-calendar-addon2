<template>
	<div class="input-tag" @click="focusInput">
		<div class="single-tag" v-for="tag in computedTags">{{tag}}<span @click="deleteTag(tag)"><i class="zmdi zmdi-close-circle"></i></span></div>
		<div class="tags-placeholder"><input :id="id" :name="name" type="text" class="tag-input" :size="placeholderSize" :placeholder="placeholderText" v-model="inputText" @keydown="addTag($event)" @blur="removeFocus" @focus="addFocus"></div>
	</div>
</template>

<script>
	export default {

		props: ['tags', 'placeholder', 'placeholderSize', 'placeholderAlways', 'id', 'name'],

		data() {
			return {
				entryKeyCode: [32, 188, 13, 9],
				inputText: '',
				inputPlaceholderAlways: 'true',
				placeholderText: '',
			}
		},

		computed: {
			computedTags() {
				return this.tags;
			}
		},

		mounted() {

			this.computedTags = this.computedTags.filter( (item, pos) => {
				return this.computedTags.indexOf(item) === pos;
			});

			if ( this.placeholderAlways ) {
				this.inputPlaceholderAlways = ( this.placeholderAlways ) ? this.placeholderAlways : this.inputPlaceholderAlways;
			}

			this.placeholderText = this.placeholder;

			if ( this.inputPlaceholderAlways === 'false' ) {
				this.placeholderText = ( this.computedTags.length > 0 ) ? '' : this.placeholder;
			}
		},

		methods: {
			deleteTag(deleteTag) {
				const tagIndex = this.computedTags.findIndex( tag => {
					return tag === deleteTag;
				});

				if ( tagIndex > -1 ) {
					this.computedTags.splice(tagIndex, 1);
				}

				if ( this.inputPlaceholderAlways === 'false' ) {
					this.placeholderText = ( this.computedTags.length > 0 ) ? '' : this.placeholder;
				}

			},

			isExist(val) {
				var index = this.computedTags.indexOf(val);
				
				if ( index > -1 ) {
					var matchedItem = $(this.$el).find('.single-tag').eq(index).addClass('tag-matched');
					var handler = setTimeout(function() {
						matchedItem.removeClass('tag-matched');
					}, 500);
				}
				
				return index > -1;
			},

			focusInput() {
				$(this.$el).find('input').focus();
			},

			removeFocus() {
				$(this.$el).removeClass('input-focused');
			},

			addFocus() {
				$(this.$el).addClass('input-focused');
			},

			addTag(e) {

				if ( !this.inputText && e.keyCode === 8 ) {
					this.computedTags.pop();
				}

				if ( this.entryKeyCode.indexOf(e.keyCode) > -1 ) {
					e.preventDefault();

					if ( !this.inputText ) return;
					if ( this.isExist(this.inputText) ) return;

					this.computedTags.push( this.inputText );
					this.inputText = '';

				}
			}
		}
	}
</script>