<template>
	<div>
		<component
			v-for="(field, fieldIndex) in regularFields"
			v-show="field.__ion_visible"
			:is="formFieldComponentName(field)"
			:key="formType + '-regular-field-' + field.property + '-' + fieldIndex + '-' + __ion_id"
			:parent-form-ion-id="parentFormIonId"
			:form-field="field"
			:form-type="formType"
			:form-errors="formErrors"
			:id-appendix="idAppendix"
			:overrides="overridesForField(field)"
			@clear-form-error="clearFormError"
		>
		</component>

		<div v-show="visibleTranslatableFieldsExist" class="row">
			<div class="w-100">
				<ul class="nav nav-tabs nav-tabs-primary">
					<li v-for="(locale, localeIndex) in entityLocales"
						:key="'locale-tab-nav-' + locale + '-' + localeIndex + '-' + __ion_id"
						class="nav-item"
						:class="{ 'error': localeHasErrors(locale) }"
					>
						<a :href="'#locale-' + locale + '-' + tabKey + '-' + __ion_id"
						   class="nav-link"
						   :class="{ 'active': (localeIndex === 0) }"
						   data-toggle="tab"
						>
							{{ locale }}
						</a>
					</li>
				</ul>

				<div class="tab-content">
					<div v-for="(locale, localeIndex) in entityLocales"
						 :key="'locale-tab-content-' + locale + '-' + localeIndex + '-' + __ion_id"
						 :id="'locale-' + locale + '-' + tabKey + '-' + __ion_id"
						 class="container tab-pane"
						 :class="{ 'active': (localeIndex === 0) }"
					>
						<component
							v-for="(field, fieldIndex) in translatableFields"
							v-show="field.__ion_visible[locale]"
							:is="formFieldComponentName(field)"
							:key="formType + '-translatable-field-' + field.property + '-' + fieldIndex + '-' + locale + '-' + __ion_id"
							:parent-form-ion-id="parentFormIonId"
							:form-field="field"
							:form-type="formType"
							:form-errors="formErrors"
							:locale="locale"
							:id-appendix="idAppendix"
							:overrides="overridesForField(field, locale)"
							@clear-form-error="clearFormError"
						>
						</component>
					</div>
				</div>
			</div>
		</div>
	</div>
</template>

<script>

	import LocalizedFieldPropertyMixin from 'ionMixins/LocalizedFieldPropertyMixin.js';
	import FormErrorKeysMixin from 'ionMixins/FormErrorKeysMixin.js';

	export default {
		mixins: [LocalizedFieldPropertyMixin, FormErrorKeysMixin],

		props: [
			'parentFormIonId',
			'formType',
			'formErrors',

			// If we have tabs, these will be Regular Fields for Tab,
			// otherwise, these will be All Regular Fields
			'regularFields',

			// If we have tabs, these will be Translatable Fields for Tab,
			// otherwise, these will be All Translatable Fields
			'translatableFields',

			// tabKey will be null in case we have no tabs
			'tabKey',

			// idAppendix will be appended to the ID of every Form Field,
			// unless the field ID is overridden through overrides
			'idAppendix',

			// Overrides that will be passed to each Form Field
			'overrides'
		],

		data: function() {
			return {
				isFormFieldsContainer: true
			}
		},

		mounted: function() {

		},

		methods: {
			composeSubmitData: function() {
				let data = {};

				for (let formField of this.formFieldComponents) {
					data = Object.assign(data, formField.getPropertyAndValue());
				}

				return data;
			},

			localeHasErrors: function(locale) {
				const localeFieldKeys = _.map(this.translatableFields, (translatableField) => {
					return this.localizedTranslatableFieldPropertyForLocale(translatableField, locale);
				});

				return localeFieldKeys.some((tabFieldKey) => this.formErrorKeys.includes(tabFieldKey));
			},

			clearFormError: function(property) {
				this.$emit('clear-form-error', property)
			},

			overridesForField: function(formField, locale = null) {
				const fieldOverrides = this.overrides ? Object.assign({}, this.overrides) : null;

				if (this.overrides?.fieldIds) {
					const localizedProperty = locale ? formField.property + '_' + locale : formField.property;
					const fieldIds = Object.assign({}, fieldOverrides.fieldIds);

					delete fieldOverrides.fieldIds;

					if (fieldIds[localizedProperty]) {
						fieldOverrides.id = fieldIds[localizedProperty];
					}
				}

				return fieldOverrides;
			},

			formFieldComponent: function(property, locale = null) {
				if (locale) {
					return _.find(this.formFieldComponents, (component) => {
						return component.formField.property === property && component.locale === locale;
					});
				} else {
					return _.find(this.formFieldComponents, (component) => {
						return component.formField.property === property;
					});
				}
			},

			formFieldComponentName(formField)
			{
				return "FormField" + (formField.type.replace(formField.type[0], formField.type[0].toUpperCase()));
			}
		},

		computed: {
			formFieldComponents: function() {
				return _.filter(this.$children, ['isField', true]);
			},

			formFields: function() {
				const regularFields = this.regularFields || [];
				const translatableFields = this.translatableFields || [];

				return regularFields.concat(translatableFields);
			},

			visibleTranslatableFieldsExist: function() {
				for (let field of this.translatableFields) {
					for (let locale of this.$store.getters.entityLocales) {
						if (field.__ion_visible[locale]) {
							return true;
						}
					}
				}

				return false;
			},

			entityLocales: function() {
				return this.$store.getters.entityLocales;
			}
		}
	}
</script>