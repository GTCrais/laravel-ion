<template>

	<div class="form-group row" :class="{ 'has-error': errorMessage }">
		<label class="col-sm-2 col-form-label"></label>
		<div class="col-sm-10">
			<div class="icheck-material-white">
				<input type="checkbox"
					   :name="name"
					   :id="id"
					   value="1"
					   v-model="checked"
					   v-bind="this.options"
				>

				<label :for="id">{{ formField.label }}</label>
			</div>
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
				ignoreMixinMounted: true,
				checked: false
			}
		},

		mounted: function() {
			this.setChecked();
		},

		methods: {
			setChecked: function() {
				this.checked = parseInt(this.defaultValues[this.formType]) ? true : false;
			},

			getValue: function() {
				return this.checked ? 1 : 0;
			}
		},

		computed: {

		}
	}
</script>