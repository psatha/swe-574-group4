// JavaScript Document
(function($) {	
	var sorter = $('#mcp_aps_order');
	var reseter = $('#mcp_aps_reset_me');
	var reloader = $('#mcp_aps_reload_me');
	var exsold = $('#mcp_aps_ex_sold');
	var soldbox = $('#mcp_box1');
	var exexp = $('#mcp_aps_ex_exp');
	var exbox = $('#mcp_box2');
		function checkBox1() {
			$(exsold).is(':checked') ? soldbox.val(1) : soldbox.val(0) ;
		}
		function checkBox2() {
			$(exexp).is(':checked') ? exbox.val(1) : exbox.val(0) ;
		}
		exsold.click(checkBox1);
		exexp.click(checkBox2);
 		sorter.live("change",function() {
			if(sorter.val() != "") {
				$('#mcp_aps_order_form').submit();
			}
		});
 		reseter.click(function() {
			sorter.find('option:first').attr('selected', 'selected');
			soldbox.val(0) ;
			//$('#sort_form').submit();

		});
 		reloader.click(function() {
			$('#mcp_aps_order_form').submit();
		});
		if(jQuery().selectBox) {
			$(window).load(function() {
				$(sorter).selectBox("destroy");
			});
		}
		checkBox1() ;
})(jQuery);
