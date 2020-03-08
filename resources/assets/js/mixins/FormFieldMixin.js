
import LabelAndInputWidthMixin from 'ionMixins/LabelAndInputWidthMixin.js'

var FormFieldMixin = {
	mixins: [LabelAndInputWidthMixin],

	props: [
		'parentFormIonId',
		'formField',
		'formType',
		'formErrors',
		'locale',
		'idAppendix',
		'overrides'
	],

	data: function() {
		return {
			isField: true,
			name: this.localizedProperty,
			value: null,
			errorMessage: null
		}
	},

	mounted: function() {
		if (!this.ignoreMixinMounted) {
			this.setInitialValue();
		}
	},

	watch: {
		formErrors: function() {
			this.setErrorMessage();
		},

		value: function() {
			eventHub.$emit('field-value-changed', this.formField, this.locale, this.getValue(), this.parentFormIonId);
		}
	},

	methods: {
		setInitialValue: function() {
			const initValue = this.defaultValues[this.formType];
			this.value =  sc.get('ValueChecker').notEmpty(initValue) ? initValue : null;
		},

		setErrorMessage: function() {
			this.errorMessage = this.formErrors?.[this.localizedProperty]?.[0] || null;
		},

		clearErrorMessage: function() {
			this.$emit('clear-form-error', this.localizedProperty)
			this.errorMessage = null;
		},

		getValue: function() {
			return this.value;
		},

		getPropertyAndValue: function() {
			let propertyAndValue = {};
			propertyAndValue[this.localizedProperty] = this.getValue();

			return propertyAndValue;
		}
	},

	computed: {
		entityConfig: function() {
			return this.overrides?.entityConfig || this.$store.getters.entityConfig;
		},

		entityObjectData: function() {
			// Entity Object Data will be stored in 'navigationEntityData' for 'keyValue' type of models
			return this.overrides?.entityObjectData || this.$store.getters.navigationEntityData?.entityObjectData || this.$store.getters.entityObjectData;
		},

		entityObject: function() {
			return this.entityObjectData.object;
		},

		id: function() {
			return this.overrides?.id || 'field-' + this.localizedProperty + (this.idAppendix || '');
		},

		options: function() {
			return this.formField.options || {};
		},

		localizedProperty: function() {
			return this.locale ? this.formField.property + '_' + this.locale : this.formField.property;
		},

		defaultEditValues: function() {
			return this.entityObjectData?.defaultEditValues || null;
		},

		defaultEditValue: function() {
			return this.defaultEditValues?.[this.localizedProperty] || null;
		},

		defaultValues: function() {
			return {
				add: this.formField.default,
				edit: this.defaultEditValue
			}
		}
	}

}

export default FormFieldMixin;

