
class StoreManager
{
	setupStore()
	{
		const storeManger = this;

		return new Vuex.Store({
			state: Object.assign(
				storeManger.storeState()
			),

			getters: Object.assign(
				storeManger.storeGetters()
			),

			mutations: Object.assign(
				storeManger.storeMutations()
			),

			actions: Object.assign(
				storeManger.storeActions()
			)
		});
	}

	storeState()
	{
		return sc.get('DataProvider').getEmptyData();
	}

	storeGetters()
	{
		return {
			initDataSet: function(state) {
				return state.initDataSet;
			},

			csrf: function(state) {
				return state.csrf;
			},

			user: function(state) {
				return state.user;
			},

			auth: function(state) {
				return {
					check: !!state.user.id,
					guest: !state.user.id,
					id: state.user.id
				}
			},

			entityConfigs: function(state) {
				return state.entityConfigs
			},

			entityConfig: function(state) {
				return state.entityConfig
			},

			entityObjectData: function(state) {
				return state.entityObjectData;
			},

			entityLocales: function(state) {
				return state.entityLocales;
			},

			navigation: function(state) {
				return state.navigation
			},

			navigationEntityData: function(state) {
				return state.navigationEntityData
			},

			defaultEntityUid: function(state) {
				return state.defaultEntityUid;
			},

			ionWebPath: function(state) {
				return state.ionWebPath;
			},

			prefixedIonWebPath: function(state) {
				return state.prefixedIonWebPath;
			},

			ionApiPath: function(state) {
				return state.ionApiPath;
			}
		}
	}

	storeMutations()
	{
		return {
			markInitDataSet: function(state, value) {
				state.initDataSet = value;
			},

			setCsrf: function(state, csrf) {
				state.csrf = csrf;
			},

			setUser: function(state, user) {
				state.user = user;
			},

			setEntityConfigs: function(state, entityConfigs) {
				state.entityConfigs = entityConfigs;
			},

			setEntityConfig: function(state, entityConfig) {
				state.entityConfig = entityConfig;
			},

			setNavigation: function(state, navigation) {
				state.navigation = navigation;
			},

			setNavigationEntityData: function(state, navigationEntityData) {
				state.navigationEntityData = navigationEntityData;
			},

			setDefaultEntityUid: function(state, defaultEntityUid) {
				state.defaultEntityUid = defaultEntityUid;
			},

			setIonApiPath: function(state, ionApiPath) {
				state.ionApiPath = ionApiPath;
			},

			setIonWebPath: function(state, ionWebPath) {
				state.ionWebPath = ionWebPath;

				let prefixedIonWebPath = ionWebPath;

				if (prefixedIonWebPath && !prefixedIonWebPath.startsWith('/')) {
					prefixedIonWebPath = '/' + prefixedIonWebPath;
				}

				state.prefixedIonWebPath = prefixedIonWebPath;
			},

			setEntityObjectData: function(state, entityObjectData) {
				state.entityObjectData = entityObjectData;
			},

			setEntityLocales: function(state, entityLocales) {
				state.entityLocales = entityLocales;
			}
		}
	}

	storeActions()
	{
		return {

		}
	}
}

export default StoreManager