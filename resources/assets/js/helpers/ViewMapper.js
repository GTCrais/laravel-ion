
class ViewMapper
{
	constructor()
	{
		this.viewMap = this.defaultViewMap();
	}

	getViewComponentName(viewKey)
	{
		let navigationEntity = sc.get('NavigationManager').getNavigationEntity();

		if (navigationEntity) {
			// Return an entity specific view, if it exists
			if (this.viewMap[navigationEntity.uid] && this.viewMap[navigationEntity.uid][viewKey]) {
				return this.viewMap[navigationEntity.uid][viewKey]
			}

			let entityConfig = sc.get('Store').getters.entityConfig;

			// Return one of the default views, based on View Key

			if (viewKey === 'index') {

				return this.viewMap['model']['index'][entityConfig.structure.type];

				// todo: implement dashboard
			}

			return this.viewMap[navigationEntity.type][viewKey];
		}

		return sc.get('VueRouter').currentRoute.meta?.error || 'e404';
	}

	mapView(keyOrObject, componentName = null)
	{
		if (_.isObject(keyOrObject)) {
			_.merge(this.viewMap, keyOrObject);
		} else {
			if (!componentName) {
				throw "You must specify the component name.";
			}

			this.viewMap[keyOrObject] = componentName;
		}
	}

	defaultViewMap()
	{
		return {
			model: {
				main: 'model-entity',
				index: {
					list: 'list-model-entity-index',
					tree: 'tree-model-entity-index',
					keyValue: 'key-value-model-entity-index'
				},
				add: 'model-entity-add',
				edit: 'model-entity-edit'
			}
		}
	}
}

export default ViewMapper;