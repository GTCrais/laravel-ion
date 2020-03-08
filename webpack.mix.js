let mix = require('laravel-mix');

mix.disableSuccessNotifications();

mix.webpackConfig({
	resolve: {
		alias: {
			'ionMixins': path.resolve(__dirname, 'resources/assets/js/mixins')
		}
	}
});

mix.js('resources/assets/js/App.js', 'dist/js/app.js')
	.extract()
	.autoload({
		jquery: ['$', 'window.jQuery', 'jQuery'],
		lodash: ['window._']
	})
	.stylus('resources/assets/css/app.styl', 'dist/css')
	.styles('resources/assets/css/tinymce/skins/ui/ion/skin.css', 'dist/css/tinymce/skins/ui/ion/skin.min.css')
	.styles('resources/assets/css/tinymce/skins/ui/ion/content.css', 'dist/css/tinymce/skins/ui/ion/content.min.css')
	.styles([
		'resources/assets/css/vendor/simplebar.css',
		'resources/assets/css/vendor/bootstrap.css',
		'resources/assets/css/vendor/animate.css',
		'resources/assets/css/vendor/icons.css',
		'resources/assets/css/vendor/sidebar-menu.css',
		'resources/assets/css/vendor/bootstrap-datepicker.css',
		'resources/assets/css/vendor/app-style.css',
		'resources/assets/css/vendor/jquery.nestable.min.css',
		'resources/assets/css/vendor/vue-select.css',
	], 'dist/css/vendor.css');
