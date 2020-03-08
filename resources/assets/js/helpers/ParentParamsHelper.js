
class ParentParamsHelper
{
	constructor()
	{
		this.parentParamRegex = /^parent_([1-9])\d*_(.+)$/; // Example: parent_1_page
	}

	constructParentParamObject(uid, value, index = null)
	{
		return {
			[this.constructParentParam(uid, index)]: value
		};
	}

	constructParentParam(uid, index = null)
	{
		if (index === null) {
			index = this.getHighestIndex() + 1;
		}

		return 'parent_' + index + '_' + uid;
	}

	getHighestIndex()
	{
		const parent = this.getHighestIndexParent();

		return parent?.index || 0;
	}

	getHighestIndexParent()
	{
		let parent = null;
		let highestIndex = 1;

		for (let _parent of this.parseParentParams()) {
			if (_parent.index >= highestIndex) {
				highestIndex = _parent.index;
				parent = _parent;
			}
		}

		return parent;
	}

	parseParentParams()
	{
		const parents = [];

		for (let [key, value] of Object.entries(this.getCurrentRoute().query)) {
			let result = key.match(this.parentParamRegex);

			if (result) {
				parents.push({
					param: result[0],
					index: parseInt(result[1]),
					uid: result[2],
					parentProperty: this.getParentPropertyFromUid(result[2]),
					value: value
				});
			}
		}

		return parents;
	}

	getParentPropertyFromUid(uid)
	{
		const objectEntityConfig = sc.get('Store').getters.entityConfig;
		const parentEntityConfig = sc.get('Store').getters.entityConfigs[uid];

		if (objectEntityConfig && parentEntityConfig) {
			return objectEntityConfig.parentEntitiesData?.[uid].parentProperty;
		}

		return null;
	}

	getCurrentRoute()
	{
		return sc.get('VueRouter').currentRoute;
	}
}

export default ParentParamsHelper;