$(document).ready(function () {
	$('ul.matrix li').click(function () {
		$(this).find('input').prop('checked', true);
	});
});
