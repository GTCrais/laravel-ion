<template>
	<i class="table-boolean fa" :class="icon"></i>
</template>

<script type="text/babel">
	export default {
		props: [
			'value'
		],

		data: function() {
			return {

			}
		},

		mounted: function() {

		},

		methods: {

		},

		computed: {
			icon: function() {
				return parseInt(this.value) ? 'fa-check' : 'fa-times';
			}
		}
	}
</script>