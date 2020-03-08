<template>

	<div>

		<div class="row">
			<div class="col-lg-12">

				<transition
					name="content-fade"
					mode="out-in"
				>

					<component :is="mainComponent"></component>

				</transition>

			</div>
		</div>

	</div>

</template>

<script type="text/babel">
	export default {
		props: [

		],

		data: function() {
			return {
				mainComponent: null
			}
		},

		mounted: function() {
			this.setMainComponent();
		},

		watch: {
			'$route': function(to, from) {
				this.setMainComponent();
			}
		},

		methods: {
			setMainComponent: function() {
				this.mainComponent = sc.get('ViewMapper').getViewComponentName(this.$route.meta.viewKey);
			}
		},

		computed: {

		}
	}
</script>