
(function() {
	var id = 0;
	Object.defineProperty(Object.prototype, '__ion_id', {
		get: function () {
			Object.defineProperty(this, '__ion_id', {
				value: ++id,
				writable: false,
				enumerable: false
			});

			return this.__ion_id;
		},
		enumerable: false
	});
})();

require('jquery');
require('bootstrap');
window.Popper = require('popper.js').default;
require('simplebar');
require('lodash');
window.moment = require('moment');
window.tinymce = require('tinymce');
require('tinymce/themes/silver');
require('tinymce/plugins/link');
require('tinymce/plugins/paste');
require('tinymce/plugins/table');

require('../vendors/jquery.nestable.js');
require('../vendors/bootstrap-datepicker.js');
require('../vendors/bootstrap-datepicker.hr.min.js');
require('../vendors/jquery.numeric.min.js');

import VueRouter from 'vue-router';
window.VueRouter = VueRouter;

import Vuex from 'vuex';
window.Vuex = Vuex;

import VueCookie from 'vue-cookie';
window.VueCookie = VueCookie;

window.Vue = require('vue');