<template>

	<div class="row">
		<div class="col-12 col-xl-6">

			<div class="card">
				<div class="card-body">

					<h5 class="card-title">
						<router-link :to="parentUrl" class="text-underline">{{ entityConfig.pluralName }}</router-link>
						<i class="fa fa-caret-right caret"></i>
						New entry
					</h5>

					<hr>

					<entity-form
						form-type="add"
						@submission-successful="redirect"
					>
					</entity-form>

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
			redirect: function(object) {
				let route = {
					name: 'entityEdit',
					params: {
						navigationEntity: this.entityConfig.uid,
						id: object.id
					}
				};

				this.$router.push(this.parentUrl);
				//this.$router.push(route);
			}
		},

		computed: {
			entityConfig: function() {
				return this.$store.getters.entityConfig;
			},

			parentUrl: function() {
				return {
					name: 'navigationEntityIndex',
					params: {
						navigationEntity: this.entityConfig.uid
					}
				}
			}
		}
	}
</script>