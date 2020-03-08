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
			</div>

			<table class="table table-striped table-hover">
				<thead>
					<tr>
						<th class="sort-handle-cell"></th>
						<th>
							{{ representedByLabel }}
						</th>
					</tr>
				</thead>
			</table>

			<div
				class="dd object-list tree-structure"
				:class="{ 'dd-nochildren': !repositioningEnabled }"
				data-tsid="ts1"
			>

				<tree-structure
					v-if="treeObjects.length"
					:items="treeObjects"
					:depth="0"
					:base-add-url="addUrl"
				></tree-structure>

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
				treeObjects: [],
				treeStructures: {},
				draggedObjectData: {},
				repositioningEnabled: true
			}
		},

		mounted: function() {
			this.setTreeObjects();
			this.$nextTick(() => {
				this.setupTreeStructure();
			});
		},

		methods: {
			setTreeObjects: function() {
				this.treeObjects = Object.assign([], this.objects);
			},

			setupTreeStructure: function() {
				const comp = this;
				const maxDepth = comp.entityConfig.structure.depth?.max;

				$('.tree-structure').each(function(index) {
					let tsid = $(this).attr('data-tsid');

					comp.treeStructures[tsid] = $(this).nestable({
						maxDepth: (maxDepth || maxDepth === 0) ? (maxDepth + 1) : 1000,

						onDragStart: function (list, object) {

							// This method decides when to mark the empty (dashed) container
							// for dropping the item as Valid (white) or Invalid (gray)

							// Set currently dragged item data. We're gonna need this
							// in the 'callback' method when sending the data to the backend
							const listData = comp.treeStructures[tsid].nestable('toArray');
							comp.draggedObjectData = _.find(listData, ['id', parseInt(object.attr('data-id'))]);

							let objectDepth = parseInt(object.attr('data-depth'));

							// All lists that are one level above the list
							// into which the item was dropped
							let oneUpLists = list.find("ol[data-depth='" + (objectDepth - 1) + "']");

							// Initially, mark all lists as Invalid for dropping the item
							list.find("ol.dd-list")
								.removeClass('dd-allowchildren')
								.removeClass('dd-allowsublistchildren');

							if (oneUpLists.length) {

								// If these lists exist, make all of their direct sub-lists
								// as Valid container for the dropped item (via CSS)

								// This has to be done this way because if these "oneUp" lists
								// are initially empty, when the item is dragged into them,
								// the sublist will be created dynamically and won't have
								// the 'dd-allowchildren' class
								oneUpLists.addClass('dd-allowsublistchildren');
							} else {

								// If "oneUp" lists don't exist, then the item's initial parent
								// list was already top level, so just mark all of the same-depth
								// lists as Valid parents

								list.find("ol[data-depth='" + (objectDepth) + "']")
									.addClass('dd-allowchildren');
							}
						},

						beforeDragStop: function(list, object, endLocation) {

							// This method decides whether the location where
							// the item was dropped was Valid or Invalid

							if (!comp.repositioningEnabled) {
								return false;
							}

							let oldDepth = parseInt(object.attr('data-depth'));
							let closestParent = endLocation.closest('li').closest('ol');
							let newDepth = closestParent.length ? (parseInt(closestParent.attr('data-depth')) + 1) : 0;

							if (newDepth === oldDepth) {
								endLocation.attr('data-depth', newDepth);

								return true;
							}

							return false;
						},

						callback: function(list, object) {
							const objectId = parseInt(object.attr('data-id'))
							const data = {
								_method: 'PATCH'
							}

							const listData = comp.treeStructures[tsid].nestable('toArray');
							const objectData = _.find(listData, ['id', objectId]);

							data.parent_id = objectData.parent_id;
							data.below_object_id = _.find(listData, ['left', parseInt(objectData.right) + 1])?.id || null;
							data.above_object_id = _.find(listData, ['right', parseInt(objectData.left) - 1])?.id || null;

							if (objectData.left !== comp.draggedObjectData.left) {
								comp.repositioningEnabled = false;

								sc.get('Request').make({
									type: 'POST',
									url: sc.get('Request').url(comp.entityConfig.uid + '/tree-reposition/' + objectId),
									data: data,
									success: function(data) {
										//console.error(data);
									},
									error: function(jqXHR, error, errorThrown) {
										console.error("Status: " + jqXHR.status + "; Response text: " + jqXHR.responseText);
									},
									complete: function() {
										comp.draggedObjectData = {};
										comp.repositioningEnabled = true;
									}
								});
							}
						}
					});
				});
			},

			emitDeleteData: function(object) {
				let data = {
					action: 'delete',
					object: object,
					entityConfig: this.entityConfig
				};

				eventHub.$emit('modal-button-clicked', data);
			},

			updateIndexObjects: function(responseData, eventData) {
				this.$store.commit('setNavigationEntityData', responseData);
				this.treeStructures[eventData.tsid].nestable('remove', eventData.object.id);
			}
		},

		computed: {
			entityConfig: function() {
				return this.$store.getters.entityConfig;
			},

			addActionAllowed: function() {
				return this.$store.getters.navigationEntityData.addActionAllowed;
			},

			representedByLabel: function() {
				return (_.find(this.entityConfig.fields.all, ['property', this.entityConfig.representedBy]))?.label || 'Could not find matching label';
			},

			objects: function() {
				return this.$store.getters.navigationEntityData.objects;
			},

			fields: function() {
				return this.entityConfig.fields.all || [];
			},

			addUrl: function() {
				return {
					'name': 'entityAdd',
					params: {
						'navigationEntity': this.entityConfig.uid
					},
					query: this.$route.query
				};
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