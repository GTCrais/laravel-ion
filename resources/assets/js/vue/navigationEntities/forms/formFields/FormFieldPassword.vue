<template>

	<div class="form-group row" :class="{ 'has-error': errorMessage }">
		<label :for="id" class="col-form-label" :class="'col-sm-' + labelWidth">{{ formField.label }}</label>
		<div class="col-sm-10">
			<input type="password"
				   :name="name"
				   class="form-control temp-disabled"
				   :id="id"
				   :disabled="disabled"
				   v-model="value"
				   v-bind="this.options"
				   @input="clearErrorMessage"
			>

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
				disabled: true
			}
		},

		mounted: function() {
			setTimeout(() => {
				this.enable();
			}, 650);
		},

		methods: {
			enable: function() {
				this.disabled = false;
			}
		},

		computed: {

		}
	}
</script>