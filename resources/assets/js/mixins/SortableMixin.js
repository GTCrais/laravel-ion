
var SortableMixin = {

	methods: {
		dragEnd: function(event) {
			if (event.oldIndex === event.newIndex) {
				return;
			}

			let direction;

			if (event.oldIndex < event.newIndex) {
				direction = 'down';
			} else {
				direction = 'up';
			}

			let parent = $(event.srcElement);
			let belowObjectId = null;
			let aboveObjectId = null;

			if (direction === 'up') {
				let belowItem = parent.find("tr:eq(" + (event.newIndex + 1) + ")");
				belowObjectId = belowItem.attr('data-objectid');
			} else {
				let aboveItem = parent.find("tr:eq(" + (event.newIndex - 1) + ")");
				aboveObjectId = aboveItem.attr('data-objectid');
			}

			sc.get('Request').make({
				type: 'POST',
				url: sc.get('Request').url(this.entityConfig.uid + '/reposition/' + $(event.clone).attr('data-objectid')),
				data: {
					below_object_id: belowObjectId,
					above_object_id: aboveObjectId,
					direction: direction,
					_method: 'PATCH'
				},
				success: function(data) {

				},
				error: function(jqXHR, error, errorThrown) {
					console.error("Status: " + jqXHR.status + "; Response text: " + jqXHR.responseText);
				},
				complete: function() {

				}
			});
		}
	}

}

export default SortableMixin;

