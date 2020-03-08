<template>
	<ol class="dd-list"
		:data-depth="depth"
		ref="list"
	>
		<li
			v-for="item in items"
			class="dd-item"
			:data-id="item.id"
			:data-depth="depth"
		>
			<div class="sort-handle-cell">
				<div class="dd-handle sort-handle-container d-flex flex-column justify-content-center">
					<i class="fa fa-ellipsis-v"></i>
				</div>
			</div>
			<div class="dd-content">
				<router-link :to="editUrl(item.id)" class="text-underline">
					{{ item.display_values[entityConfig.representedBy] }}
				</router-link>
			</div>
			<div class="controls">
				<a href="#"
				   v-if="item.allowed_actions.delete"
				   title="Delete"
				   data-toggle="modal"
				   data-target="#default-modal"
				   @click="emitDeleteData(item)"
				>
					<i class="fa fa-trash"></i>
				</a>

				<a href="#" v-else class="disabled" @click.prevent>
					<i class="fa fa-trash"></i>
				</a>

				<router-link v-if="depth < maxDepth" :to="addUrl(item.id)" title="Add">
					<i class="fa fa-plus-square-o"></i>
				</router-link>

				<a href="#" v-else class="disabled" @click.prevent>
					<i class="fa fa-plus-square-o"></i>
				</a>
			</div>

			<tree-structure
				v-if="itemChildren(item).length"
				:items="itemChildren(item)"
				:depth="depth + 1"
				:base-add-url="baseAddUrl"
			></tree-structure>
		</li>
	</ol>
</template>

<script>
	export default {
		props: [
			'depth',
			'items',
			'baseAddUrl'
		],

		data: function() {
			return {

			}
		},

		mounted: function() {

		},

		methods: {
			itemChildren: function(item) {
				return item[this.entityConfig.structure.childrenMethod] || [];
			},

			addUrl: function(itemId) {
				return {
					'name': 'entityAdd',
					params: {
						'navigationEntity': this.entityConfig.uid
					},
					query: this.constructAddUrlQuery(itemId)
				};
			},

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
					tsid: this.tsid,
					entityConfig: this.entityConfig
				};

				eventHub.$emit('modal-button-clicked', data);
			},

			constructAddUrlQuery(itemId) {
				return Object.assign(
					{},
					this.$route.query,
					sc.get('ParentParamsHelper').constructParentParamObject(
						this.entityConfig.uid,
						itemId
					)
				)
			}
		},

		computed: {
			entityConfig: function() {
				return this.$store.getters.entityConfig;
			},

			maxDepth: function() {
				const maxDepth = this.entityConfig.structure.depth?.max;

				return (maxDepth || maxDepth === 0) ? maxDepth : 1000;
			},

			tsid: function() {
				return $(this.$refs.list).closest('.tree-structure').attr('data-tsid');
			}
		}
	}
</script>