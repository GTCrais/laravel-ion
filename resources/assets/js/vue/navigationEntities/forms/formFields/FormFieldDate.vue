<template>

	<div class="form-group row" :class="{ 'has-error': errorMessage }">
		<label :for="id" class="col-form-label" :class="'col-sm-' + labelWidth">{{ formField.label }}</label>
		<div :class="'col-sm-' + inputWidth">
			<div class="input-group">
				<input
					type="text"
					:name="name"
					class="form-control standard-visuals is-date"
					:id="id"
					v-bind="this.options"
					readonly="readonly"
					ref="datePicker"
				>

				<div class="input-group-append">
					<button type="button" class="btn btn-light" @click="clearDate">
						<i class="fa fa-times"></i>
					</button>
				</div>
			</div>

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
				ignoreMixinMounted: true,
				inputField: null
			}
		},

		mounted: function() {
			this.setupDatePicker();
		},

		methods: {
			getInitialValue: function() {
				let value = this.defaultValues[this.formType];

				if (value) {
					value = new Date(value);
				} else {
					value = null;
				}

				return value;
			},

			setupDatePicker: function() {
				let comp = this;
				this.inputField = $(this.$refs.datePicker);
				this.inputField.datepicker(this.datePickerOptions())
					.on('changeDate', function(e) {
						comp.clearErrorMessage();
						comp.setValue();
					});

				let setDateMethod = 'setDate';

				this.inputField.datepicker(setDateMethod, this.getInitialValue());
				this.inputField.datepicker('update');
			},

			datePickerOptions: function() {
				let options = {
					autoclose: true,
					todayHighlight: true,
					maxViewMode: 'decades',
					format: 'dd.mm.yyyy.',
					weekStart: 1,
					language: 'hr'
				}

				return Object.assign(options, this.formField.datePickerOptions);
			},

			clearDate: function() {
				this.inputField.datepicker('update', '');
				this.value = null;
			},

			setValue: function() {
				let date = this.inputField.datepicker('getDate');

				this.value = date ? moment(date).format('YYYY-MM-DD') : null;
			}
		}
	}
</script>