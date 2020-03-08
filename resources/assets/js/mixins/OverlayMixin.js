
const OverlayMixin = {

	methods: {

		removeOverlay: function() {
			$('#pageloader-overlay').fadeOut(300);
		}

	}

}

export default OverlayMixin;

