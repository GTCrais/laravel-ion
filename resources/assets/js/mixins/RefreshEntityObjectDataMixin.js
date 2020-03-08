
var RefreshEntityObjectDataMixin = {

	methods: {
		refreshEntityObjectData: function(data) {
			sc.get('Store').commit('setEntityObjectData', data);
		},

		refreshEntityObject: function(entityObject) {
			let entityObjectData = sc.get('Store').getters.entityObjectData;
			entityObjectData.object = entityObject;

			this.refreshEntityObjectData(entityObjectData);
		},

		refreshEntityObjectInPaginator: function(entityObject) {
			let navigationEntityData = sc.get('Store').getters.navigationEntityData;
			navigationEntityData.paginator.data = _.map(navigationEntityData.paginator.data, function(thisEntityObject) {
				return thisEntityObject.id === entityObject.id ? entityObject : thisEntityObject;
			});

			sc.get('Store').commit('setNavigationEntityData', navigationEntityData);
		}
	}

}

export default RefreshEntityObjectDataMixin;