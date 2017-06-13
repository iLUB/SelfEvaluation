function ilFileDeleteClass(field_id, file_id_prefix) {

	var files = document.getElementById('ilFileDeleteList').getElementsByClassName('submit');

	this.init = function () {
		for (var i = 0; i < files.length; i++) {
			if (files[i].value == '+') {
				$(files[i]).click(addFile);
			} else {
				$(files[i]).click(removeFile);
			}
		}
	};

	function removeFile() {
		// Add file ID to field_id
		var file_id = this.id.substring(file_id_prefix.length);
		var input_field = document.getElementById(field_id);
		var values = $.parseJSON(input_field.value);
		if (values == null) {
			values = [];
		}
		values.push(file_id);
		input_field.value = JSON.stringify(values);

		// Remove HTML
		this.parentElement.parentElement.remove();

		// No files left: hide form entry
		if (files.length == 0) {
			document.getElementById('il_prop_cont_delete_file').parentElement.style.display="none";
		}
	}


	function addFile() {
		// Remove file ID from field_id
		var file_id = this.id.substring(file_id_prefix.length);
		var input_field = document.getElementById(field_id);
		var values = $.parseJSON(input_field.value);
		findAndRemove(values, file_id);
		input_field.value = JSON.stringify(values);

		// Reset Style
		this.value = 'X';
		var $row = $(this).closest('.ilFileDelete');
		$row.removeClass('ilFileDelete');

		// Reset Event Handler
		$(this).off('click');
		$(this).click(removeFile);
	}

	function findAndRemove(array, value) {
		$.each(array, function(index, result) {
			if(result == value) {
				//Remove from array
				array.splice(index, 1);
			}
		});
	}
}