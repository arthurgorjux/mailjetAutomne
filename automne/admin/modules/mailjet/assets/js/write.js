$(document).ready(function() {
	// resize the iframe to match content height
	$('#preview').on('load',function(){
		this.height = this.contentWindow.document.body.scrollHeight;
	});
});