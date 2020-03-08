<template>

	<div class="form-group row" :class="{ 'has-error': errorMessage }">
		<label :for="id" class="col-form-label" :class="'col-sm-' + labelWidth">{{ formField.label }}</label>
		<div :class="'col-sm-' + inputWidth">
			<tinymce-editor
				:name="name"
				:id="id"
				:init="options"
				v-model="value"
				@onKeyUp="clearErrorMessage"
				ref="input"
			></tinymce-editor>

			<label class="error" v-if="errorMessage">
				<i class="fa fa-exclamation"></i>
				{{ errorMessage }}
			</label>

			<input type="file" ref="fileUploadInput" class="d-none">
		</div>
	</div>

</template>

<script type="text/babel">

	import FormFieldMixin from 'ionMixins/FormFieldMixin.js'

	export default {
		mixins: [FormFieldMixin],

		props: [

		],

		data: function() {
			return {
				defaultOptions: {
					suffix: '.min',
					base_url: '/vendor/ion/css/tinymce',
					skin: 'ion',
					content_css : "/vendor/ion/css/tinymce/skins/ui/ion/content.min.css",
					plugins: 'link paste',
					menubar: false,
					paste_as_text: true,
					entity_encoding: 'raw',
					toolbar: 'undo redo | h3 | bold italic | link unlink',
					contextmenu: false,
					file_picker_types: 'file',
					default_link_target: "_blank",
					convert_urls: false,
					file_picker_callback: this.filePickerCallback,
					init_instance_callback: this.initInstanceCallback
				}
			}
		},

		methods: {
			filePickerCallback: function(callback, value, meta) {

				const comp = this;

				const dialog = $('.tox-dialog');
				const selectFileButton = dialog.find('.tox-browse-url');
				const saveButton = dialog.find('.tox-dialog__footer-end button:last-child');
				const spinnerContainer = $('<div class="tox-uploading-spinner"></div>');
				const spinner = $('<i class="fa fa-circle-o-notch fa-spin"></i>');
				spinnerContainer.append(spinner);

				$(this.$refs.fileUploadInput).click();
				$(this.$refs.fileUploadInput).off('change');
				$(this.$refs.fileUploadInput).on('change', function() {

					const file = this.files[0];

					$(comp.$refs.fileUploadInput).off('change');
					$(comp.$refs.fileUploadInput).val('');

					selectFileButton.addClass('d-none');
					selectFileButton.after(spinnerContainer);
					saveButton.attr('disabled', 'disabled');
					saveButton.addClass('disabled');

					sc.get('Request').make({
						type: 'POST',
						url: comp.getFileUploadUrl(),
						data: comp.composeFileUploadData(file),
						enctype: 'multipart/form-data',
						processData: false,
						contentType: false,
						cache: false,

						success: function(data) {
							callback(data.fileUrl);

							spinner.removeClass(['fa-circle-o-notch', 'fa-spin']).addClass('fa-check');

							setTimeout(() => {
								spinnerContainer.remove();
								selectFileButton.removeClass('d-none');
							}, 1000);
						},
						error: function(jqXHR, error, errorThrown) {
							spinner.removeClass(['fa-circle-o-notch', 'fa-spin']).addClass('fa-exclamation-triangle');

							setTimeout(() => {
								spinnerContainer.remove();
								selectFileButton.removeClass('d-none');

								sc.get('ErrorHandler').handle(jqXHR.status, { jqXHR: jqXHR});
							}, 1000);
						},
						complete: function() {
							saveButton.removeAttr('disabled');
							saveButton.removeClass('disabled');
						}
					});

				});
			},

			initInstanceCallback: function(editor) {
				editor.on('blur', function (e) {
					$(editor.editorContainer).removeClass('editor-focused');
				});

				editor.on('focus', function (e) {
					$(editor.editorContainer).addClass('editor-focused');
				});
			},

			composeFileUploadData: function(file) {
				let data = new FormData();

				data.append('file', file);
				data.append('property', this.formField.property);
				data.append('locale', this.locale);

				return data;
			},

			getFileUploadUrl: function() {
				return sc.get('Request').url(this.entityConfig.uid + '/upload-file');
			}
		},

		computed: {
			options: function() {
				return Object.assign(this.defaultOptions, (this.formField.options || {}));
			}
		}
	}
</script>