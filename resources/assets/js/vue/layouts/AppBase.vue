<template>

	<div>

		<app-navigation></app-navigation>

		<app-header @logout="logout"></app-header>

		<div class="clearfix"></div>

		<div class="content-wrapper">
			<div class="container-fluid">

				<transition
					name="content-fade"
					mode="out-in"
				>

					<component :is="mainComponent"></component>

				</transition>

			</div>
			<!-- End container-fluid-->
		</div><!--End content-wrapper-->

		<!--Start Back To Top Button-->
		<a href="#"
		   class="back-to-top"
		   ref="scrollToTop"
		   @click.prevent="scrollToTop"
		>
			<i class="fa fa-angle-double-up"></i>
		</a>
		<!--End Back To Top Button-->

		<!--Start footer-->
		<footer class="footer">
			<div class="container">
				<div class="text-center">
					Built with Ion CMS
				</div>
			</div>
		</footer>
		<!--End footer-->

		<right-sidebar></right-sidebar>

		<default-modal id="default-modal"></default-modal>
	</div><!--End wrapper-->

</template>

<script type="text/babel">

	import OverlayMixin from 'ionMixins/OverlayMixin.js';

	export default {
		mixins: [OverlayMixin],

		props: [

		],

		data: function() {
			return {
				mainComponent: null
			}
		},

		mounted: function() {
			this.removeOverlay();
			this.setupScrollToTop();
			this.setMainComponent();
		},

		watch: {
			'$route': function(to, from) {
				this.setMainComponent();
			}
		},

		methods: {
			logout: function() {

				sc.get('Auth').logout();

			},

			setupScrollToTop: function() {
				const comp = this;

				$(window).on("scroll", function(){
					if ($(this).scrollTop() > 300) {
						$(comp.$refs.scrollToTop).fadeIn();
					} else {
						$(comp.$refs.scrollToTop).fadeOut();
					}
				});
			},

			scrollToTop: function() {
				$("html, body").animate({ scrollTop: 0 }, 300);
			},

			setMainComponent: function() {
				this.mainComponent = sc.get('ViewMapper').getViewComponentName('main');
			}
		},

		computed: {

		},
	}
</script>