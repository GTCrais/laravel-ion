
class Request
{
	make(ajaxOptions)
	{
		$.ajax(ajaxOptions);
	}

	url(endpoint, query = '')
	{
		let apiPath = '/' + sc.get('Store').getters.ionApiPath;

		if (!endpoint.startsWith('/')) {
			apiPath += '/';
		}

		let finalQuery;

		if (_.isObject(query)) {
			finalQuery = [];

			for (let [key, value] of Object.entries(query)) {
				finalQuery.push(key + '=' + value);
			}

			finalQuery = finalQuery.length ? '?' + finalQuery.join('&') : '';
		} else {
			finalQuery = query;
		}

		if (finalQuery && !finalQuery.startsWith('?')) {
			finalQuery = '?' + finalQuery;
		}

		return apiPath + endpoint + finalQuery;
	}

	setCsrfToken(csrf)
	{
		if (!csrf) {
			sc.get('Store').commit('setCsrf', $('meta[name="csrf-token"]').attr('content'));
		} else {
			sc.get('Store').commit('setCsrf', csrf);
		}

		$.ajaxSetup({
			headers: {
				'X-CSRF-TOKEN': sc.get('Store').getters.csrf
			}
		});
	}
}

export default Request;