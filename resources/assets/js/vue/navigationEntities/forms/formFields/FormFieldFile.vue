<template>

	<div class="form-group row" :class="{ 'has-error': errorMessage }">
		<label :for="id" class="col-form-label" :class="'col-sm-' + labelWidth">{{ formField.label }}</label>
		<div :class="'col-sm-' + inputWidth">
			<vue-dropzone
				:id="id"
				:class="dropzoneClass"
				:options="options"
				:use-custom-slot="true"
				:destroy-dropzone="false"
				ref="dropzone"
				@vdropzone-error="showErrorMessageAndReset"
				@vdropzone-canceled="setErrorMessage"
				@vdropzone-removed-file="fileRemoved"
				@vdropzone-success="fileUploaded"
				@vdropzone-max-files-exceeded="maxFilesExceeded"
				@vdropzone-mounted="addExistingFile"
			>
				Drop an image or click here to upload one...
			</vue-dropzone>

			<label class="error" v-if="errorMessage">
				<i class="fa fa-exclamation"></i>
				{{ errorMessage }}
			</label>
		</div>
	</div>

</template>

<script type="text/babel">

	import FormFieldMixin from 'ionMixins/FormFieldMixin.js'
	import vue2Dropzone from 'vue2-dropzone'

	export default {
		mixins: [FormFieldMixin],

		props: [

		],

		components: {
			vueDropzone: vue2Dropzone
		},

		data: function() {
			return {
				uploadErrorMessage: null,
				skipDeleteFileApiRequest: false
			}
		},

		mounted: function() {

		},

		methods: {
			addExistingFile: function() {
				const data = this.entityObject?.file_fields_data?.[this.localizedProperty];

				if (data?.filename) {
					const _dropzone = this.dropzone.dropzone;
					const file = { name: data.filename, size: null, accepted: true };

					_dropzone.files.push(file);
					_dropzone.emit('addedfile', file);
					_dropzone.emit('thumbnail', file, data.thumbnailUrl);
					_dropzone.emit('complete', file);
					_dropzone._updateMaxFilesReachedClass();

					$(file.previewElement).addClass(['dz-processing', 'dz-success']);
					$(file.previewElement).find('.dz-remove-container').addClass(['d-flex', 'opaque']);
				}
			},

			fileUploaded: function(file, response) {
				this.dropzone.dropzone.emit('thumbnail', file, response.thumbnailUrl);
				this.runFileAddedAnimations(file);
				this.clearErrorMessage();
				this.value = response.filename;
			},

			fileRemoved: function(file, error, xhr) {

				// Only process the removed file if we're NOT handling
				// a file which exceeded the max files limit
				if (!this.skipDeleteFileApiRequest) {
					const comp = this;
					this.setErrorMessage(file, null);

					sc.get('Request').make({
						type: 'POST',
						url: this.getUrl('delete'),
						data: Object.assign({ _method: 'DELETE', filename: this.value }, this.defaultRequestParams),
						success: function(data) {
							comp.value = null;
						},
						error: function(jqXHR, error, errorThrown) {
							comp.showErrorMessageAndReset(file, jqXHR.responseJSON.message, jqXHR);
						},
						complete: function() {

						}
					});
				} else {
					this.setErrorMessage(file, null);
				}

				this.skipDeleteFileApiRequest = false;
			},

			runFileAddedAnimations: function(file) {
				const successContainer = $(file.previewElement).find('.dz-success-mark');
				const removeContainer = $(file.previewElement).find('.dz-remove-container');

				setTimeout(() => {
					successContainer.addClass('opaque');

					setTimeout(() => {
						successContainer.removeClass('opaque');
						removeContainer.addClass('d-flex');

						setTimeout(() => {
							removeContainer.addClass('opaque');
						}, 500);
					}, 750);
				}, 300);
			},

			showErrorMessageAndReset: function(file, message, xhr) {

				// If the uploaded file exceeded the max files limit,
				// we're going to let maxFilesExceeded method handle this
				if (this.dropzone.dropzone.files?.length > this.options.maxFiles) {
					return;
				}

				if (sc.get('ErrorHandler').shouldHandleError(xhr?.status)) {

					sc.get('ErrorHandler').handle(xhr.status);

				} else {
					if (_.isObject(message)) {
						message = message.message || 'An error has occurred. Status code: ' + (xhr.status || 'Unknown');
					}

					this.setErrorMessage(file, message);

					setTimeout(() => {
						// It's likely that our file wasn't created in the backend,
						// so just skip the API delete request
						this.skipDeleteFileApiRequest = true;
						this.dropzone.removeFile(file);
					}, 3000);
				}
			},

			setErrorMessage: function(file, message, xhr) {
				if (message !== undefined) {
					this.errorMessage = message;
				} else {
					this.errorMessage = this.formErrors?.[this.localizedProperty]?.[0] || null;
				}
			},

			maxFilesExceeded: function(file) {
				this.skipDeleteFileApiRequest = true;
				this.dropzone.removeFile(file);
			},

			getUrl: function(action) {
				if (action === 'upload') {
					action = 'upload-file';
				} else if (action === 'delete') {
					action = 'delete-file';
				}

				return sc.get('Request').url(this.entityConfig.uid + '/' + action);
			}
		},

		computed: {
			defaultOptions: function() {
				return {
					url: this.getUrl('upload'),
					paramName: 'file', // This is the default paramName
					headers: {
						'X-CSRF-TOKEN': sc.get('Store').getters.csrf
					},
					params: this.defaultRequestParams,
					createImageThumbnails: false,
					previewTemplate: this.previewTemplate,
					includeStyling: false,
					maxFiles: 1,
					acceptedFiles: this.acceptedFiles
				}
			},

			defaultRequestParams: function() {
				return {
					property: this.formField.property,
					locale: this.locale,
					id: this.entityObject?.id || ''
				};
			},

			dropzone: function() {
				return this.$refs.dropzone;
			},

			dropzoneClass: function() {
				let dzClass = ['single-file'];

				if (this.formField.imageFile) {
					dzClass.push('image-file');
				}

				return dzClass;
			},

			options: function() {
				const options = Object.assign(this.defaultOptions, (this.formField.options || {}));

				if (this.formField.imageFile) {
					options.params.fileType = 'image'
				}

				return options;
			},

			acceptedFiles: function() {
				if (this.formField.imageFile) {
					return 'image/*';
				}

				return null;
			},

			previewTemplate: function() {
				return '<div class="dz-preview dz-file-preview">' +
							'<div class="dz-progress-container d-flex align-items-center">' +
								'<a class="dz-remove" href="#" data-dz-remove><i class="fa fa-times"></i></a>' +
								'<div class="dz-progress flex-fill"><span class="dz-upload" data-dz-uploadprogress></span></div>' +
							'</div>' +
							'<div class="dz-completed-container">' +
								'<div class="dz-image">' +
									'<div class="dz-success-mark d-flex align-items-center justify-content-center">' +
										'<i class="fa fa-check-circle"></i>' +
									'</div>' +
									'<div class="dz-remove-container align-items-center justify-content-center">' +
										'<a class="dz-remove" href="#" data-dz-remove>' +
											'<i class="fa fa-trash"></i>' +
										'</a>' +
									'</div>' +
									'<img data-dz-thumbnail />' +
								'</div>' +
							'</div>' +
						'</div>'
			}
		}
	}
</script>