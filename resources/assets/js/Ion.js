
import ServiceContainer from "./services/ServiceContainer";

class Ion
{
	constructor()
	{
		this.bootingCallbacks = [];
		this.boot();
	}

	booting(callback)
	{
		this.bootingCallbacks.push(callback);
	}

	boot()
	{
		window.eventHub = new Vue();

		this.serviceContainer = new ServiceContainer();

		window.serviceContainer = this.serviceContainer;
		window.sc = this.serviceContainer;

		serviceContainer.registerServices();
		serviceContainer.bootServices();
	}

	runBootingCallbacks()
	{
		this.bootingCallbacks.forEach(callback => callback());
		this.bootingCallbacks = [];
	}

	launch()
	{
		this.runBootingCallbacks();

		this.app = new Vue({

			el: '#vue-wrapper',
			router: serviceContainer.get('VueRouter'),
			store: serviceContainer.get('Store')

		});
	}
}

export default Ion;