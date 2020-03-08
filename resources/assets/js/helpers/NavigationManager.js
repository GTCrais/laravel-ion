
class NavigationManager
{
	getNavigationEntity()
	{
		if (sc.get('VueRouter').currentRoute.meta.isNavigationEntity) {
			return _.find(sc.get('Store').getters.navigation, ['urlPath', sc.get('VueRouter').currentRoute.params.navigationEntity]);
		}

		return null;
	}
}

export default NavigationManager;