jQuery(document).ready(function ($) {
	$('#glossary-search').keyup(function () {
		console.log('wtf')
		var searchTerm = $(this).val().toLowerCase();

		$('.glossary-item').each(function () {
			var itemText = $(this).text().toLowerCase();
			if (itemText.indexOf(searchTerm) > -1) {
				$(this).parent().parent().show();
			} else {
				$(this).parent().parent().hide();
			}
		});
	});
});
