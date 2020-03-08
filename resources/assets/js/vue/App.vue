<template>

	<div id="wrapper"
		 :class="{ 'toggled': menuToggled }"
	>

		<transition
				name="content-fade"
				mode="out-in"
		>
			<router-view name="layout"></router-view>
		</transition>

	</div>

</template>

<script type="text/babel">
	export default {
		props: [

		],

		data: function() {
			return {
				menuToggled: false
			}
		},

		mounted: function() {

		},

		methods: {
			toggleMenu: function() {
				this.menuToggled = !this.menuToggled;
			}
		},

		computed: {

		},

		created: function() {
			eventHub.$on('menu-toggled', this.toggleMenu);
		},

		beforeDestroy: function() {
			eventHub.$off('menu-toggled', this.toggleMenu);
		}
	}
</script>