
class DataProvider
{
	initData()
	{
		return new Promise((resolve, reject) => { this.fetchAndSetInitialData(resolve, reject) });
	}

	fetchAndSetInitialData(resolve, reject)
	{
		let dataProvider = this;

		sc.get('Request').make({
			type: 'GET',
			url: sc.get('Request').url('get-initial-data'),
			data: {},
			success: function(data) {
				dataProvider.setInitialData(data);
				dataProvider.markInitDataSetAsTrue();
			},
			error: function(jqXHR, error, errorThrown) {
				console.error("Status: " + jqXHR.status + "; Response text: " + jqXHR.responseText);
			},
			complete: function() {
				if (resolve) {
					resolve();
				}
			}
		});
	}

	setNavigationEntityData(navigationEntity, query, resolve, reject)
	{
		sc.get('Request').make({
			type: 'GET',
			url: sc.get('Request').url(navigationEntity, query),
			data: {},
			success: function(data) {
				sc.get('Store').commit('setNavigationEntityData', data);

				if (resolve) {
					resolve();
				}
			},
			error: function(jqXHR, error, errorThrown) {
				//console.error("Status: " + jqXHR.status + "; Response text: " + jqXHR.responseText);

				if (reject) {
					reject(jqXHR.status);
				}
			},
			complete: function() {

			}
		});
	}

	setEntityObjectData(entity, id, resolve, reject)
	{
		let apiUrl = id ? sc.get('Request').url(entity + '/edit/' + id) : sc.get('Request').url(entity + '/add');

		sc.get('Request').make({
			type: 'GET',
			url: apiUrl,
			data: {},
			success: function(data) {
				sc.get('Store').commit('setEntityObjectData', data);

				if (resolve) {
					resolve();
				}
			},
			error: function(jqXHR, error, errorThrown) {
				//console.error("Status: " + jqXHR.status + "; Response text: " + jqXHR.responseText);

				if (reject) {
					reject(jqXHR.status);
				}
			},
			complete: function() {

			}
		});
	}

	setConstants()
	{
		let body = $('body');
		let ionApiPath = body.attr('data-ionapipath');
		let ionWebPath = body.attr('data-ionwebpath');

		sc.get('Store').commit('setIonApiPath', ionApiPath);
		sc.get('Store').commit('setIonWebPath', ionWebPath);

		body.removeAttr('data-ionapipath');
		body.removeAttr('data-ionwebpath');
	}

	markInitDataSetAsTrue()
	{
		sc.get('Store').commit('markInitDataSet', true);
	}

	resetCsrf()
	{
		return new Promise((resolve, reject) => {

			sc.get('Request').make({
				type: 'GET',
				url: sc.get('Request').url('get-csrf'),
				data: {},
				success: function(data) {
					sc.get('Request').setCsrfToken(data.csrf);
				},
				error: function(jqXHR, error, errorThrown) {
					console.error("Status: " + jqXHR.status + "; Response text: " + jqXHR.responseText);
				},
				complete: function() {
					resolve();
				}
			});

		});
	}

	setInitialData(data)
	{
		// We'll omit setting the ionApiPath and ionWebPath to null and initDataSet
		// to false because both need to be set on first load and never changed

		if (data.user !== undefined) {
			sc.get('Store').commit('setUser', data.user);
		}

		if (data.entityConfigs !== undefined) {
			sc.get('Store').commit('setEntityConfigs', data.entityConfigs);
		}

		if (data.entityConfig !== undefined) {
			sc.get('Store').commit('setEntityConfig', data.entityConfig);
		}

		if (data.navigation !== undefined) {
			sc.get('Store').commit('setNavigation', data.navigation);
		}

		if (data.defaultEntityUid !== undefined) {
			sc.get('Store').commit('setDefaultEntityUid', data.defaultEntityUid);
		}

		if (data.entityObjectData !== undefined) {
			sc.get('Store').commit('setEntityObjectData', data.entityObjectData);
		}

		if (data.entityLocales !== undefined) {
			sc.get('Store').commit('setEntityLocales', data.entityLocales);
		}
	}

	resetAuth() {
		this.setInitialData({ user: {} });
	}

	resetInitialData()
	{
		this.setInitialData(this.getEmptyData());
	}

	resetEntityObjectData()
	{
		sc.get('Store').commit('setEntityObjectData', {});
	}

	getEmptyData()
	{
		return {
			initDataSet: false,
			ionApiPath: null,
			ionWebPrefix: null,
			csrf: null,
			user: {},
			entityConfigs: {},
			entityConfig: {},
			navigation: {},
			navigationEntityData: {},
			defaultEntityUid: '',
			entityObjectData: {},
			entityLocales: []
		}
	}
}

export default DataProvider;

