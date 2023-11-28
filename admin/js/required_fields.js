jQuery(document).ready(function ($) {
	const validateForm = () => {
		const title = $('#title').val().trim();
		const content = tinymce.get('content').getContent().trim();

		if (!title || !content) {
			displayCustomAlert('Title and content are required.');

			if (!title) {
				$('#title').focus();
			} else {
				tinymce.get('content').focus();
			}
			return false;
		}
		return true;
	};

	const displayCustomAlert = (message) => {
		alert('Alert: ' + message);
	};

	$('#post').submit((e) => {
		if (!validateForm()) {
			e.preventDefault();
		}
	});
});
