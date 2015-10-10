$(document).ready(function () {
	
	$.ajax({
		type: "POST",
		url: '/pay_mod/kaznachey/kaznachey_result.php',
		data: 'status=ps',
		dataType: "html",
		success: function(res){
			$('#cc_types').hide().before(res);
		 }
	});
		
	 $('body').on('change', '#cc_types', function() {
		set_cc_types();
	 });
	 
	 function set_cc_types(){
		var cc_types = $('#cc_types').val();

		$.ajax({
			type: "POST",
			url: '/pay_mod/kaznachey/kaznachey_result.php',
			data: 'status=ss&cc_types='+cc_types,
			dataType: "html",
		});
	}
	
	set_cc_types();
	
});