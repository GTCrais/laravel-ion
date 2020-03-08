<template>
	<li :class="{active: isActive}">

		<router-link :to="route(item.urlPath)" class="waves-effect">
			{{ item.name }}
		</router-link>

	</li>
</template>

<script type="text/babel">
	export default {
		props: [
			'item'
		],

		data: function() {
			return {

			}
		},

		mounted: function() {

		},

		methods: {
			route: function(urlPath) {
				return {
					'name': 'navigationEntityIndex',
					params: {
						'navigationEntity': urlPath
					}
				};
			}
		},

		computed: {
			isActive: function() {
				if (this.$route.meta.isNavigationEntity &&
					this.$route.params.navigationEntity === this.item.urlPath)
				{
					return true;
				}

				return false;
			}
		}
	}
</script>