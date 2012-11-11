// JavaScript Document
(function($) {	
	var sorter = $('#sort_order');
	var reseter = $('#reset_me');
	var reloader = $('#reload_me');
 		sorter.live("change",function() {
			if(sorter.val() != "") {
				$('#sort_form').submit();
			}
		});
 		reseter.click(function() {
			sorter.find('option:first').attr('selected', 'selected');
			//$('#sort_form').submit();

		});
 		reloader.click(function() {
			$('#sort_form').submit();
		});
		if(jQuery().selectBox) {
			$(window).load(function() {
				$('#sort_order').selectBox("destroy");
			});
		}
})(jQuery);

