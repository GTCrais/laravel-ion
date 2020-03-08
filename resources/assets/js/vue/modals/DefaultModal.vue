<template>
	<div ref="modal" class="modal fade default-modal" :id="id">
		<div class="modal-container d-flex flex-column justify-content-center">
			<div class="modal-dialog">
				<div class="modal-content animated fadeIn">
					<div class="modal-header">
						<h5 class="modal-title">
							<i class="mr-2 fa" :class="titleIcon"></i>
							{{ title }}
						</h5>
						<button type="button" class="close" data-dismiss="modal" aria-label="Close">
							<span aria-hidden="true">&times;</span>
						</button>
					</div>

					<div class="modal-body">
						<p v-html="text"></p>
					</div>

					<div class="modal-footer">
						<button type="button" class="btn btn-light px-3" :class="{'disabled': formIsSubmitting}" data-dismiss="modal">
							<i class="fa fa-times"></i> {{ cancelText }}
						</button>

						<button type="button" class="btn btn-inverse-primary px-3" :class="{'disabled': formIsSubmitting}" @click="submit">
							<i class="fa" :class="buttonIcon"></i> {{ submitText }}
						</button>
					</div>
				</div>
			</div>
		</div>
	</div>
</template>

<script type="text/babel">

	export default {
		props: [
			'id'
		],

		data: function() {
			return {
				modal: null,
				formIsSubmitting: false,
				titleIcon: 'fa-trash',
				buttonIcon: 'fa-trash',
				data: {}
			}
		},

		mounted: function() {
			this.modal = $(this.$refs.modal);
		},

		methods: {
			setModalData: function(data) {
				this.formIsSubmitting = false;
				this.data = data;

				if (_.includes(['delete'], this.data.action)) {
					this.buttonIcon = 'fa-trash';
				} else {
					this.buttonIcon = 'fa-check';
				}
			},

			submit: function() {
				if (this.data.action === 'delete') {
					this.deleteObject();
				}
			},

			deleteObject: function() {
				if (this.formIsSubmitting) {
					return;
				}

				this.formIsSubmitting = true;

				let comp = this;

				sc.get('Request').make({
					type: 'POST',
					url: comp.actionUrl,
					data: {
						'_method': 'DELETE'
					},

					success: function(data) {
						comp.buttonIcon = 'fa-check';

						eventHub.$emit('entity-deleted', data, comp.data);

						setTimeout(function() {
							comp.modal.modal('hide');
						}, 200);
					},
					error: function(jqXHR, error, errorThrown) {
						comp.formIsSubmitting = false;
						sc.get('ErrorHandler').handle(jqXHR.status, { jqXHR: jqXHR, modal: comp.modal });
					},
					complete: function() {

					}
				});
			}
		},

		computed: {
			title: function() {
				if (_.includes(['delete'], this.data.action)) {
					return "Delete entry";
				}

				return '';
			},

			objectName: function() {
				if (this.data.object && this.data.entityConfig) {
					return this.data.object.display_values[this.data.entityConfig.representedBy];
				}

				return '';
			},

			text: function() {
				if (this.data.action === 'delete') {
					return "Are you sure you want delete entry:<br>" +
							"<strong>" + this.objectName + "?</strong>";
				}

				return '';
			},

			actionUrl: function() {
				if (this.data.action === 'delete') {
					return sc.get('Request').url(this.data.entityConfig.uid + '/delete/' + this.data.object.id, window.location.search);
				}

				return '/';
			},

			cancelText: function() {
				return 'Cancel';
			},

			submitText: function() {
				if (_.includes(['delete'], this.data.action)) {
					return 'Delete';
				}

				return 'Send';
			}
		},

		created: function() {
			eventHub.$on('modal-button-clicked', this.setModalData);
		},

		beforeDestroy: function() {
			eventHub.$off('modal-button-clicked', this.setModalData);
		}
	}
</script>