<template>

	<div class="row">
		<div class="col-12 col-xl-6">

			<div class="card">
				<div class="card-body">

					<h5 class="card-title">
						{{ entityConfig.pluralName }}
					</h5>

					<hr>

					<entity-form form-type="edit"></entity-form>

				</div>
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

			}
		},

		mounted: function() {

		},

		methods: {

		},

		computed: {
			entityConfig: function() {
				return this.$store.getters.entityConfig;
			}
		}
	}

</script>