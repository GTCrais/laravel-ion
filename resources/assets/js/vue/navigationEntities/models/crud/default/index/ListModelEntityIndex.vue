<template>

	<div class="card">
		<div class="card-body">
			<div class="card-body-header">
				<h5 class="card-title">
					{{ entityConfig.pluralName }}
					<router-link v-if="addActionAllowed" :to="addUrl" class="btn btn-light btn-sm add-entity">
						<i class="fa fa-plus"></i>
					</router-link>
				</h5>

				<div class="card-actions mb-3">
					<div class="quick-search">
						<input class="form-control form-control-sm"
							   type="text"
							   ref="quickSearch"
							   v-model="quickSearchTerm"
							   @input="quickSearch"
							   placeholder="Search..."
						>

						<i class="fa fa-times" @click="clearQuickSearch"></i>
					</div>
				</div>
			</div>

			<div class="object-list table-responsive">
				<table class="table table-striped table-hover">
					<thead>
						<tr>
							<th v-if="entityConfig.structure.position" class="sort-handle-cell"></th>
							<th v-for="(field, key, index) in fields"
								v-if="field.index"
								:key="'index-header-' + key + '-' + index"
							>
								{{ field.label }}
							</th>
							<th class="controls"></th>
						</tr>
					</thead>
					<draggable
							v-model="objects"
							tag="tbody"
							handle=".sort-handle-container"
							@end="dragEnd"
							:move="repositionFinished"
					>
						<tr v-for="(object, key, index) in objects"
							:data-objectid="object.id"
						>
							<td v-if="entityConfig.structure.position" class="sort-handle-cell">
								<div class="sort-handle-container d-flex flex-column justify-content-center">
									<i class="fa fa-ellipsis-v"></i>
								</div>
							</td>
							<td v-for="(field, key2, index2) in fields"
								v-if="field.index"
								:key="'index-field-' + key + '-' + index + '-' + key2 + '-' + index2"
							>

								<router-link
									v-if="field.index.link"
									:to="editUrl(object.id)"
									class="text-underline"
									v-html="object.display_values[field.property]"
								>
								</router-link>

								<table-boolean v-else-if="field.type === 'checkbox'" :value="object.display_values[field.property]"></table-boolean>

								<span v-else>
									{{ object.display_values[field.property] }}
								</span>

							</td>
							<td>
								<a href="#"
								   title="Obriši"
								   data-toggle="modal"
								   data-target="#default-modal"
								   @click="emitDeleteData(object)"
								>
									<i class="fa fa-trash"></i>
								</a>
							</td>
						</tr>
					</draggable>
				</table>
			</div>

			<pagination v-if="showPagination" @go-to-page="goToPage" :paginator="paginator"></pagination>
		</div>
	</div>

</template>

<script type="text/babel">

	import QuickSearchMixin from 'ionMixins/QuickSearchMixin.js';
	import SortableMixin from 'ionMixins/SortableMixin.js';

	export default {
		mixins: [QuickSearchMixin, SortableMixin],

		props: [

		],

		data: function() {
			return {

			}
		},

		mounted: function() {

		},

		methods: {
			editUrl: function(id) {
				return {
					'name': 'entityEdit',
					params: {
						'navigationEntity': this.entityConfig.uid,
						'id': id
					}
				};
			},

			emitDeleteData: function(object) {
				let data = {
					action: 'delete',
					object: object,
					entityConfig: this.entityConfig
				};

				eventHub.$emit('modal-button-clicked', data);
			},

			updateIndexObjects: function(data) {
				this.$store.commit('setNavigationEntityData', data);
			},

			goToPage: function(page) {
				let route = this.$route;
				let query = Object.assign({}, route.query);
				query.page = page;

				this.$router.push({ name: route.name, params: route.params, query: query });
			},

			repositionFinished: function(event, originalEvent) {

			}
		},

		computed: {
			entityConfig: function() {
				return this.$store.getters.entityConfig;
			},

			addActionAllowed: function() {
				return this.$store.getters.navigationEntityData.addActionAllowed;
			},

			paginator: function() {
				return this.$store.getters.navigationEntityData.paginator;
			},

			objects: {
				get: function() {
					return this.paginator?.data;
				},

				set: function(value) {
					let navigationEntityData = this.$store.getters.navigationEntityData;
					navigationEntityData.paginator.data = value;
					this.$store.commit('setNavigationEntityData', navigationEntityData);
				}
			},

			fields: function() {
				return this.entityConfig.fields.index || [];
			},

			addUrl: function() {
				return {
					'name': 'entityAdd',
					params: {
						'navigationEntity': this.entityConfig.uid
					},
					query: Object.assign({}, this.$route.query)
				};
			},

			showPagination: function() {
				return this.objects?.length && this.paginator.last_page > 1;
			}
		},

		created: function() {
			eventHub.$on('entity-deleted', this.updateIndexObjects);
		},

		beforeDestroy: function() {
			eventHub.$off('entity-deleted', this.updateIndexObjects);
		}
	}

</script>