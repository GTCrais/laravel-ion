
const _ = require('lodash');

var QuickSearchMixin = {

	data: function() {
		return {
			quickSearchTerm: null
		}
	},

	mounted: function() {
		this.setInitialQuickSearchTerm();
	},

	methods: {
		setInitialQuickSearchTerm: function() {
			this.quickSearchTerm = this.$route.query?.term;
		},

		quickSearch: _.debounce(function() {
			let query = {};

			if ($.trim(this.quickSearchTerm)) {
				query.term = $.trim(this.quickSearchTerm);
			}

			this.$router.push({ name: this.$route.name, params: this.$route.params, query: query });
		}, 120, { 'leading': false, 'trailing': true }),

		clearQuickSearch: function() {
			this.quickSearchTerm = null;
			this.$router.push({ name: this.$route.name, params: this.$route.params });
		}
	}

}

export default QuickSearchMixin;

