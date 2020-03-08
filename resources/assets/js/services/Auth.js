
class Auth
{
	login(authData, resolve = null, reject = null) {
		sc.get('Request').make({
			type: 'POST',
			url: sc.get('Request').url('login'),
			data: {
				email: authData.email,
				password: authData.password
			},
			success: function(data) {
				sc.get('DataProvider').setInitialData(data);

				if (resolve) {
					resolve();
				}
			},
			error: function(jqXHR, error, errorThrown) {
				if (reject) {
					reject(jqXHR);
				}
			},
			complete: function() {}
		});
	}

	logout()
	{
		sc.get('Request').make({
			type: 'POST',
			url: sc.get('Request').url('logout'),
			data: {},
			success: function(data) {
				sc.get('DataProvider').resetAuth();
				sc.get('VueRouter').push({ 'name': 'login' });
			},
			error: function(jqXHR, error, errorThrown) {
				sc.get('ErrorHandler').handle(jqXHR.status, { jqXHR: jqXHR })
			},
			complete: function() {

			}
		});
	}
}

export default Auth;