
var SelectFieldMixin = {

	data: function() {
		return {
			multiple: false
		}
	},

	methods: {
		reduce: function(option) {
			const keyProp = this.formField.settings?.keyProp || 'key';

			return option[keyProp];
		}
	},

	computed: {
		options: function() {
			const options = Object.assign(this.defaultOptions, this.formField.options);

			if (this.multiple) {
				options.multiple = true;
			}

			delete options.class;

			return options;
		},

		defaultOptions: function() {
			const options = {
				selectOnTab: true
			}

			if (!this.multiple) {
				options.clearable = true;
			}

			return options;
		},

		label: function() {
			return this.formField.settings?.valueProp || 'value';
		},

		cssClasses: function() {
			const classes = [];

			if (this.multiple) {
				classes.push('vs--multiple');
			}

			if (!this.value?.length) {
				classes.push('vs--empty');
			}

			if (this.options.clearable) {
				classes.push('vs--clearable');
			}

			if (this.formField.options?.class) {
				classes.push(this.formField.options?.class);
			}

			return classes;
		},

		selectList: function() {
			return this.entityObjectData.selectValues[this.formField.property] || {};
		}
	}

}

export default SelectFieldMixin;

