<template>

	<div class="form-group row" :class="{ 'has-error': errorMessage }">
		<label :for="id" class="col-form-label" :class="'col-sm-' + labelWidth">{{ formField.label }}</label>
		<div :class="'col-sm-' + inputWidth">
			<textarea
				:name="name"
				class="form-control"
				:id="id"
				v-model="value"
				v-bind="this.options"
				@input="clearErrorMessage"
				ref="input"
			></textarea>

			<label class="error" v-if="errorMessage">
				<i class="fa fa-exclamation"></i>
				{{ errorMessage }}
			</label>
		</div>
	</div>

</template>

<script type="text/babel">

	import FormFieldMixin from 'ionMixins/FormFieldMixin.js'

	export default {
		mixins: [FormFieldMixin],

		props: [

		],

		data: function() {
			return {

			}
		},

		mounted: function() {
			if (this.formField.numeric) {
				$(this.$refs.input).numeric(this.formField.numeric);
			}
		},

		methods: {

		},

		computed: {

		}
	}
</script>