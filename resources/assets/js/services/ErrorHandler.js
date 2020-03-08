
class ErrorHandler
{
	constructor()
	{
		// 401 - Unauthenticated user
		// 403 - Authenticated user doesn't have permission
		// 404 - Route / Resource not found
		// 419 - CSRF token mismatch

		this.handleableErrorCodes = [401, 403, 404, 419];
	}

	handle(errorCode, data = {})
	{
		if (this.shouldHandleError(errorCode)) {

			let next = data.next;
			let ionWebPath = sc.get('Store').getters.prefixedIonWebPath;

			if (data.modal) {
				data.modal.modal('hide');
			}

			if (data.removeSelect) {
				$('span.select2-container').remove();
			}

			if (errorCode === 403) {

				if (next) {
					next({ name: 'unauthorized' });
				} else {
					sc.get('VueRouter').push({ path: ionWebPath + '/unauthorized' })
				}

			} else if (errorCode === 404) {

				if (next) {
					next({ path: ionWebPath + '/404' });
				} else {
					sc.get('VueRouter').push({ path: ionWebPath + '/404' })
				}

			} else {

				// We first reset auth so we can
				// be redirected to the login page
				sc.get('DataProvider').resetAuth();

				// Then we request for the new
				// CSRF token and redirect to Login
				sc.get('DataProvider').resetCsrf().then(function(){
					if (next) {
						next({name: 'login'});
					} else {
						sc.get('VueRouter').push({name: 'login'})
					}
				}).catch(function() {
					if (next) {
						next({name: 'login'});
					} else {
						sc.get('VueRouter').push({name: 'login'})
					}
				});

				// After we've been redirected to Login, the
				// Login component will reset the initial data.
				// We do this after being redirected so that UI
				// doesn't twitch beforehand

			}

			return;
		}

		if (data.jqXHR) {
			console.error("Status: " + data.jqXHR.status + "; Response text: " + data.jqXHR.responseText);
		} else {
			console.error("Error code: " + errorCode);
		}
	}

	shouldHandleError(errorCode)
	{
		return _.includes(this.handleableErrorCodes, errorCode);
	}
}

export default ErrorHandler;