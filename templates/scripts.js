$(document).ready(function () {
	$('ul.matrix li').click(function () {
		console.log(this);
		$(this).find('input').prop('checked', true);
	});
});