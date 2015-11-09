$(document).ready(function() {
	$('#date').pickadate({
    min: +1,
    format: 'd mmmm yyyy',
    formatSubmit: 'yyyy-mm-dd',
	});
	$('#time').pickatime({
		format: 'H:i',
		clear:'Annuler'
	});
});