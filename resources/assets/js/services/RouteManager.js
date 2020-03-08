
class RouteManager
{
	getRoutes(vueComponents)
	{
		let routes = [];
		let ionWebPath = sc.get('Store').getters.prefixedIonWebPath;

		routes.push(
			{
				path: ionWebPath,
				name: 'home',
				meta: {
					requiresAuth: true,
					redirectsToDefault: true
				}
			}
		);

		routes.push(
			{
				path: ionWebPath + '/login',
				name: 'login',
				components: {
					layout: vueComponents['AppLogin']
				},
				meta: {
					requiresGuest: true
				}
			}
		);

		routes.push(
			{
				path: ionWebPath + '/unauthorized',
				name: 'unauthorized',
				components: {
					layout: vueComponents['AppBase']
				},
				meta: {
					requiresAuth: true,
					error: 'e403'
				}
			}
		);

		routes.push(
			{
				path: ionWebPath + '/:navigationEntity',
				components: {
					layout: vueComponents['AppBase']
				},

				children: [
					{
						path: '',
						name: 'navigationEntityIndex',
						meta: {
							requiresAuth: true,
							setNavigationEntityData: true,
							isNavigationEntity: true,
							viewKey: 'index'
						}
					},

					{
						path: 'add',
						name: 'entityAdd',
						meta: {
							requiresAuth: true,
							setEntityObjectData: true,
							isNavigationEntity: true,
							viewKey: 'add'
						}
					},

					{
						path: 'edit/:id',
						name: 'entityEdit',
						meta: {
							requiresAuth: true,
							setEntityObjectData: true,
							isNavigationEntity: true,
							viewKey: 'edit'
						}
					}
				]
			}
		);

		routes.push(
			{
				path: ionWebPath + '/*',
				name: 'notFound',
				components: {
					layout: vueComponents['AppBase']
				},
				meta: {
					requiresAuth: true,
					error: 'e404'
				}
			}
		);

		return routes;
	}

	setupGuardsAndDataFetching()
	{
		const routeManager = this;

		sc.get('VueRouter').beforeEach((to, from, next) => {

			this.setInitData().then(function() {

				if (to.matched.some(record => record.meta.requiresAuth) &&
					!sc.get('Store').getters.auth.check)
				{
					sc.get('DataProvider').resetInitialData();
					next({ name: 'login' });
					return;
				}

				if (to.matched.some(record => record.meta.requiresGuest) &&
					!sc.get('Store').getters.auth.guest)
				{
					next({ name: 'home' });
					return;
				}

				if (to.matched.some(record => record.meta.redirectsToDefault)) {
					next({
						'name': 'navigationEntityIndex',
						params: {
							'navigationEntity': sc.get('Store').getters.defaultEntityUid
						}
					});

					return;
				}

				routeManager.setAsyncRouteData(to).then(function() {
					routeManager.setEntityConfig(to);
					to.meta.previousQuery = from.query;
					next();
				}).catch(function(errorCode) {
					sc.get('ErrorHandler').handle(errorCode, { next: next });
				});

			});

		});

		sc.get('VueRouter').afterEach((to, from) => {
			if (!to.matched.some(record => record.meta.setEntityObjectData)) {
				sc.get('DataProvider').resetEntityObjectData();
			}
		});
	}

	async setAsyncRouteData(to)
	{
		if (to.matched.some(record => record.meta.setNavigationEntityData)) {
			await this.setNavigationEntityData(to);
		}

		if (to.matched.some(record => record.meta.setEntityObjectData)) {
			await this.setEntityObjectData(to);
		}
	}

	setNavigationEntityData(to)
	{
		return new Promise((resolve, reject) => sc.get('DataProvider').setNavigationEntityData(to.params.navigationEntity, to.query, resolve, reject));
	}

	setEntityObjectData(to)
	{
		return new Promise((resolve, reject) => sc.get('DataProvider').setEntityObjectData(to.params.navigationEntity, to.params.id, resolve, reject));
	}

	setEntityConfig(to)
	{
		if (to.meta.isNavigationEntity) {
			let entityConfig = sc.get('Store').getters.entityConfigs[to.params.navigationEntity]

			if (!entityConfig) {
				console.error("Entity config '" + to.params.navigationEntity + "' not found.");
			}

			sc.get('Store').commit('setEntityConfig', entityConfig);
		}
	}

	async setInitData()
	{
		if (!sc.get('Store').getters.initDataSet) {
			await sc.get('DataProvider').initData();
		}
	}
}

export default RouteManager;