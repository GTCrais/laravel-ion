<template>

	<form @submit.prevent="submitForm">

		<div v-if="entityConfig.fieldTabs">
			<ul class="nav nav-tabs nav-tabs-primary">
				<li v-for="(fieldTab, key, index) in entityConfig.fieldTabs"
					:key="'field-tab-nav-' + key + '-' + index"
					class="nav-item"
					:class="{ 'error': tabHasErrors(key) }"
				>
					<a :href="'#' + key"
					   class="nav-link"
					   :class="{ 'active': (index === 0) }"
					   data-toggle="tab"
					>
						{{ fieldTab }}
					</a>
				</li>
			</ul>

			<div class="tab-content">
				<div v-for="(fieldTab, tabKey, tabIndex) in entityConfig.fieldTabs"
					 :key="'field-tab-content-' + tabKey + '-' + tabIndex"
					 :id="tabKey"
					 class="container tab-pane"
					 :class="{ 'active': (tabIndex === 0) }"
				>

					<entity-form-fields-container
						:parent-form-ion-id="__ion_id"
						:form-type="formType"
						:form-errors="formErrors"
						:regular-fields="regularEntityConfigFieldsForTab(tabKey)"
						:translatable-fields="translatableEntityConfigFieldsForTab(tabKey)"
						:tab-key="tabKey"
						@clear-form-error="clearFormError"
					>
					</entity-form-fields-container>

				</div>
			</div>
		</div>

		<entity-form-fields-container
			v-else
			:parent-form-ion-id="__ion_id"
			:form-type="formType"
			:form-errors="formErrors"
			:regular-fields="regularEntityConfigFields"
			:translatable-fields="translatableEntityConfigFields"
			:tab-key="null"
			@clear-form-error="clearFormError"
		>
		</entity-form-fields-container>

		<div :class="{ 'container': !!entityConfig.fieldTabs }">
			<div class="form-group row">
				<label class="col-form-label" :class="'col-sm-' + labelWidth"></label>
				<div :class="'col-sm-' + inputWidth">
					<button type="submit" class="btn btn-light px-3" :class="{'disabled': formIsSubmitting}"
							@click.prevent="submitForm">
						<i class="fa mr-1" :class="buttonIcon"></i> Save
					</button>
				</div>
			</div>
		</div>

	</form>

</template>

<script type="text/babel">

	import LabelAndInputWidthMixin from 'ionMixins/LabelAndInputWidthMixin.js';
	import LocalizedFieldPropertyMixin from 'ionMixins/LocalizedFieldPropertyMixin.js';
	import FormErrorKeysMixin from 'ionMixins/FormErrorKeysMixin.js';

	export default {
		mixins: [
			LabelAndInputWidthMixin,
			LocalizedFieldPropertyMixin,
			FormErrorKeysMixin
		],

		props: [
			'formType'
		],

		data: function() {
			return {
				formIsSubmitting: false,
				formErrors: {},
				buttonIcon: 'fa-save',
				listenForFieldValueChanges: false,
				fieldVisibilityDependencyMap: {}
			}
		},

		mounted: function() {
			this.buildFieldVisibilityDependencyMap();

			this.$nextTick(() => {
				this.setFieldsVisibility();
				this.listenForFieldValueChanges = true;
			});
		},

		methods: {
			submitForm: function() {
				if (this.formIsSubmitting) {
					return;
				}

				this.formIsSubmitting = true;
				this.clearFormErrors();

				let comp = this;

				sc.get('Request').make({
					type: 'POST',
					url: comp.submitUrl,
					data: comp.composeSubmitData(),
					success: function(data) {
						comp.buttonIcon = 'fa-check';

						setTimeout(function() {
							comp.$emit('submission-successful', data.object);
							comp.formIsSubmitting = false;
							comp.buttonIcon = 'fa-save';
						}, 200);
					},
					error: function(jqXHR, error, errorThrown) {
						if (jqXHR.status === 422) {
							comp.formErrors = jqXHR.responseJSON.errors;
						} else {
							sc.get('ErrorHandler').handle(jqXHR.status, { jqXHR: jqXHR });
						}

						comp.formIsSubmitting = false;
						comp.buttonIcon = 'fa-save';
					},
					complete: function() {

					}
				});

			},

			composeSubmitData: function() {
				let data = {};

				for (let formFieldsContainer of this.formFieldsContainers) {
					data = Object.assign(data, formFieldsContainer.composeSubmitData());
				}

				if (this.formType === 'edit') {
					data['_method'] = 'PUT';
				}

				const parentData = sc.get('ParentParamsHelper').getHighestIndexParent();

				if (parentData?.parentProperty) {
					data[parentData.parentProperty] = parentData.value;
				}

				return data;
			},

			clearFormErrors: function() {
				this.formErrors = {};
			},

			clearFormError: function(property) {
				this.$delete(this.formErrors, property);
			},

			fieldValueChanged: function(field, locale, newValue, parentFormIonId) {
				if (this.listenForFieldValueChanges) {
					this.setFieldsVisibility(field, locale, newValue, parentFormIonId);
				}
			},

			setFieldsVisibility: function(changedField, changedFieldLocale, newValue, parentFormIonId) {
				if (this.overrides?.setFieldsVisibility) {

					return this.overrides.setFieldsVisibility(this);

				} else {

					// ParentFormIonId will be falsy when initially setting
					// fields visibility in mounted method
					if (!parentFormIonId || (this.__ion_id === parentFormIonId)) {

						// ChangedField will be falsy when initially setting fields visibility in mounted
						// method. In this case we want to set the initial visibility to TRUE for all
						// the fields which DON'T have visibility dependencies and set it to FALSE
						// for all fields which DO have visibility dependencies.

						const dependentFields = changedField ? this.fieldVisibilityDependencyMap[changedField.property] : _.values(this.entityConfigFields);

						if (dependentFields) {
							for (let dependentField of dependentFields) {

								if (!changedField) {
									this.setInitialFieldVisibility(dependentField);
								}

								this.setFieldVisibilityAfterDependencyValueChange(dependentField);
							}
						}

					}
				}
			},

			setFieldVisibilityAfterDependencyValueChange: function(dependentField) {

				if (dependentField.showWhen && this.dependencyCanAffectFieldVisibility(dependentField, this.formType)) {

					for (let [dependencyFieldProperty, allowedValues] of Object.entries(dependentField.showWhen)) {

						let dependencyField = this.entityConfigFields[dependencyFieldProperty];

						// We have 4 possible cases:
						// 1:
						// DependentField IS Translatable && DependencyField IS Translatable
						// - for every locale check DependencyField value, and update
						//   DependentField visibility for that locale
						// 2:
						// DependentField IS Translatable && DependencyField IS NOT Translatable
						// - check DependencyField value, and update DependentField visibility
						//   for every locale based on that single value
						// 3:
						// DependentField IS NOT Translatable && DependencyField IS NOT Translatable
						// - check DependencyField value, and update DependentField visibility
						//   based on that value
						// 4:
						// DependentField IS NOT Translatable && DependencyField IS Translatable
						// - Translatable fields CANNOT be visibility dependencies for
						//   Non-Translatable fields. Ignore this case

						if (dependentField.translatable) {

							// Case 1:
							if (dependencyField.translatable) {
								for (let locale of this.$store.getters.entityLocales) {
									dependentField.__ion_visible[locale] = _.includes(allowedValues, this.formFieldComponent(dependencyFieldProperty, locale).getValue());
								}

								// Case 2:
							} else {
								let visible = _.includes(allowedValues, this.formFieldComponent(dependencyFieldProperty).getValue());

								for (let locale of this.$store.getters.entityLocales) {
									dependentField.__ion_visible[locale] = visible;
								}
							}
						} else {

							// Case 3:
							if (!dependencyField.translatable) {
								dependentField.__ion_visible = _.includes(allowedValues, this.formFieldComponent(dependencyFieldProperty).getValue());
							}

							// Case 4:
							// Ignoring this case. See comments above.
						}
					}
				}
			},

			setInitialFieldVisibility: function(field) {
				let visible;
				let visibleValue = !field.showWhen;

				if (!this.dependencyCanAffectFieldVisibility(field, this.formType)) {
					visibleValue = false;
				}

				if (field.translatable) {
					visible = {};

					for (let locale of this.$store.getters.entityLocales) {
						visible[locale] = visibleValue;
					}
				} else {
					visible = visibleValue;
				}

				this.$set(field, '__ion_visible', visible);
			},

			dependencyCanAffectFieldVisibility: function(formField, formType) {

				// If Field is hidden for current user, or is hidden in current form,
				// dependency value change should not affect Field's visibility

				return this.fieldIsVisibleForFormType(formField, formType) &&
					this.fieldIsVisibleForUserRole(formField)
			},

			fieldIsVisibleForFormType: function(formField, formType)
			{
				if (formField.hidden && _.includes(formField.hidden, formType)) {
					return false;
				}

				return true;
			},

			fieldIsVisibleForUserRole(formField)
			{
				if (formField.viewableForRoles && !_.includes(formField.viewableForRoles, sc.get('Store').getters.user?.ion_role)) {
					return false;
				}

				return true;
			},

			buildFieldVisibilityDependencyMap: function() {
				for (let [property, field] of Object.entries(this.entityConfigFields)) {

					// If Field's visibility is dependent on some other Field's value...
					if (field.showWhen) {
						for (let [dependency, value] of Object.entries(field.showWhen)) {

							// ...push that other Field as a Dependency into the map, and
							// push current Field as Dependent field for this Dependency
							if (this.fieldVisibilityDependencyMap[dependency]) {
								this.fieldVisibilityDependencyMap[dependency].push(field);
							} else {
								this.fieldVisibilityDependencyMap[dependency] = [field];
							}
						}
					}
				}
			},

			formFieldComponent: function(property, locale = null) {
				let component = null;

				for (let formFieldsContainer of this.formFieldsContainers) {
					component = formFieldsContainer.formFieldComponent(property, locale);

					if (component) {
						break;
					}
				}

				return component;
			},

			regularEntityConfigFieldsForTab: function(tabKey) {
				return _.filter(this.regularEntityConfigFields, ['fieldTab', tabKey]);
			},

			translatableEntityConfigFieldsForTab: function(tabKey) {
				return _.filter(this.translatableEntityConfigFields, ['fieldTab', tabKey]);
			},

			entityConfigFieldsForTab: function(tabKey) {
				return _.filter(this.entityConfigFields, ['fieldTab', tabKey]);
			},

			tabHasErrors: function(tabKey) {
				const tabFieldKeys = _.flatten(
					_.map(this.entityConfigFieldsForTab(tabKey), (field) => {
						return this.optionallyLocalizedFieldProperties(field);
					})
				);

				return tabFieldKeys.some((tabFieldKey) => this.formErrorKeys.includes(tabFieldKey));
			},

			optionallyLocalizedFieldProperties: function(field) {
				const properties = [];

				if (field.translatable) {
					for (let locale of this.entityLocales) {
						properties.push(this.localizedTranslatableFieldPropertyForLocale(field, locale));
					}
				} else {
					properties.push(field.property);
				}

				return properties;
			}
		},

		computed: {
			entityConfig: function() {
				return this.$store.getters.entityConfig;
			},

			entityObject: function() {
				return this.$store.getters.entityObjectData.object;
			},

			entityConfigFields: function() {
				return this.entityConfig.fields.all;
			},

			regularEntityConfigFields: function() {
				// Instead of using 'this.entityConfig.fields.regular', we're filtering Regular
				// fields from All fields so we can reference the same objects regardless of
				// iterating over 'regularEntityConfigFields' or 'entityConfigFields'

				return _.filter(this.entityConfigFields, (field) => {
					return !field.translatable;
				})
			},

			translatableEntityConfigFields: function() {
				// Instead of using 'this.entityConfig.fields.translatable', we're filtering Translatable
				// fields from All fields so we can reference the same objects regardless of
				// iterating over 'translatableEntityConfigFields' or 'entityConfigFields'

				return _.filter(this.entityConfigFields, (field) => {
					return field.translatable;
				})
			},

			formFieldsContainers: function() {
				return _.filter(this.$children, ['isFormFieldsContainer', true]);
			},

			entityLocales: function() {
				return this.$store.getters.entityLocales;
			},

			submitUrl: function() {
				if (this.formType === 'add') {
					return sc.get('Request').url(this.entityConfig.uid + '/' + 'add')
				}

				if (this.formType === 'edit') {
					let submitUrl = sc.get('Request').url(this.entityConfig.uid + '/edit');

					if (this.entityConfig.structure?.type !== 'keyValue') {
						submitUrl += '/' + this.entityObject.id;
					}

					return submitUrl;
				}

				return sc.get('Request').url('/');
			}
		},

		created: function() {
			eventHub.$on('field-value-changed', this.fieldValueChanged);
		},

		beforeDestroy: function() {
			eventHub.$off('field-value-changed', this.fieldValueChanged);
		}
	}
</script>