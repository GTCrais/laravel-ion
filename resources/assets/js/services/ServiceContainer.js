
import * as Cookies from 'tiny-cookie';

import Auth from './Auth.js';
import ComponentManager from './ComponentManager.js';
import DataProvider from './DataProvider.js';
import ErrorHandler from './ErrorHandler.js';
import MixinProvider from "./MixinProvider";
import NavigationManager from '../helpers/NavigationManager.js';
import ParentParamsHelper from '../helpers/ParentParamsHelper.js';
import Request from './Request.js';
import RouteManager from './RouteManager.js';
import StoreManager from './StoreManager.js';
import ValueChecker from '../helpers/ValueChecker.js';
import ViewMapper from '../helpers/ViewMapper.js';

class ServiceContainer
{
	constructor()
	{
		this.services = {};
	}

	registerServices()
	{
		this.services.Auth = new Auth();
		this.services.ComponentManager = new ComponentManager();
		this.services.Cookies = Cookies;
		this.services.DataProvider = new DataProvider();
		this.services.ErrorHandler = new ErrorHandler();
		this.services.MixinProvider = new MixinProvider();
		this.services.NavigationManager = new NavigationManager();
		this.services.Request = new Request();
		this.services.ParentParamsHelper = new ParentParamsHelper();
		this.services.RouteManager = new RouteManager();
		this.services.Store = null;
		this.services.StoreManager = new StoreManager();
		this.services.ValueChecker = new ValueChecker();
		this.services.ViewMapper = new ViewMapper();
		this.services.VueRouter = null;
	}

	bootServices()
	{
		this.services.Store = this.services.StoreManager.setupStore();
		this.services.Request.setCsrfToken();
		this.services.DataProvider.setConstants();
		this.services.MixinProvider.registerMixins();

		let components = this.services.ComponentManager.registerComponents();
		this.services.VueRouter = new VueRouter({
			mode: 'history',
			routes: this.services.RouteManager.getRoutes(components)
		});

		this.services.RouteManager.setupGuardsAndDataFetching();
	}

	getOrNull(service)
	{
		return this.get(service, false);
	}

	get(service, fail = true)
	{
		if (this.services[service]) {
			return this.services[service];
		} else {
			if (fail) {
				throw "Service '" + service + "' hasn't been registered.";
			} else {
				return null;
			}
		}
	}

	set(serviceName, service)
	{
		this.services[serviceName] = service;
	}
}

export default ServiceContainer;