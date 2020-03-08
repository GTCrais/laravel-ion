<template>

	<div class="form-group row" :class="{ 'has-error': errorMessage }">
		<label :for="id" class="col-form-label" :class="'col-sm-' + labelWidth">{{ formField.label }}</label>
		<div :class="'col-sm-' + inputWidth" @keydown.enter.prevent.self>
			<v-select
				:id="id"
				class="form-control"
				:class="cssClasses"
				:options="selectList"
				:reduce="reduce"
				:label="label"
				v-bind="options"
				v-model="value"
				@input="clearErrorMessage"
			>
			</v-select>

			<label class="error" v-if="errorMessage">
				<i class="fa fa-exclamation"></i>
				{{ errorMessage }}
			</label>
		</div>
	</div>

</template>

<script type="text/babel">

	import FormFieldMixin from 'ionMixins/FormFieldMixin.js'
	import SelectFieldMixin from 'ionMixins/SelectFieldMixin.js'

	export default {
		mixins: [FormFieldMixin, SelectFieldMixin],
	}
</script>