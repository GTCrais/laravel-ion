<template>

	<div class="right-sidebar">
		<div class="right-sidebar-content">

			<i class="fa fa-times close-settings" @click="close"></i>

			<p>Settings</p>

			<hr>

			<!--Some settings here-->

		</div>
	</div>

</template>

<script type="text/babel">

	export default {
		props: [

		],

		data: function() {
			return {

			}
		},

		mounted: function() {

		},

		methods: {
			open: function() {
				$(".right-sidebar").addClass("right-toggled");
			},

			close: function() {
				$(".right-sidebar").removeClass("right-toggled");
			},

			range: function(start, end) {
				return _.range(start, end);
			}
		},

		computed: {

		},

		created: function() {
			eventHub.$on('openSettings', this.open);
		},

		beforeDestroy: function() {
			eventHub.$off('openSettings', this.open);
		}
	}
</script>