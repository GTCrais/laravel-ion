<template>

	<div class="row">
		<div class="col-12 col-xl-6">

			<div class="card">
				<div class="card-body">

					<h5 class="card-title">
						<router-link :to="parentUrl" class="text-underline">{{ entityConfig.pluralName }}</router-link>
						<i class="fa fa-caret-right caret"></i>
						<span v-if="entityObject"> {{ entityObject.display_values[entityConfig.representedBy] }} </span>
					</h5>

					<hr>

					<entity-form
						form-type="edit"
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
				this.$router.push(this.parentUrl);
			}
		},

		computed: {
			entityConfig: function() {
				return this.$store.getters.entityConfig;
			},

			entityObjectData: function() {
				return this.$store.getters.entityObjectData;
			},

			entityObject: function() {
				return this.entityObjectData.object;
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