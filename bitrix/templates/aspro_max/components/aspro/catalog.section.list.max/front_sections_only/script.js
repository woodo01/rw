$(document).ready(function(){
	$('.sections_wrapper .section_info.onhover .section_info_inner').mCustomScrollbarDeferred({
		mouseWheel: {
			scrollAmount: 150,
			preventDefault: true
		}
	})
})