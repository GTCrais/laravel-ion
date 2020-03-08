
const LocalizedFieldPropertyMixin = {

	methods: {

		localizedTranslatableFieldPropertyForLocale: function(field, locale) {
			if (field.translatable) {
				return field.property + '_' + locale;
			}

			return null;
		}

	}

}

export default LocalizedFieldPropertyMixin;

