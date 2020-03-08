
var LabelAndInputWidthMixin = {

	computed: {
		labelWidth: function() {
			return this.entityConfig.form?.labelWidth || 2;
		},

		inputWidth: function() {
			return this.entityConfig.form?.inputWidth || 10;
		}
	}

}

export default LabelAndInputWidthMixin;

