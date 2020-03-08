
require('./bootstrap/libraries.js');
import Ion from './Ion';

Vue.use(VueCookie);
Vue.use(VueRouter);
Vue.use(Vuex);

(function() {
	this.InitIon = function() {
		return new Ion()
	}
}.call(window))


