/**
 * Admin jQuery functions
 * Written by AppThemes
 *
 * http://www.appthemes.com
 *
 * Built for use with the jQuery library
 *
 *
 */

// <![CDATA[

jQuery(document).ready(function($) {

    /* initialize the tooltip feature */
    $("td.titledesc a").easyTooltip();

    /* admin option pages tabs */
    $("div#tabs-wrap").tabs( {
        fx: {opacity: 'toggle', duration: 200},
        selected: theme_scripts_admin.setTabIndex, // set in theme-functions.php
        show: function() {
            var newIdx = $('div#tabs-wrap').tabs('option', 'selected');
            $('#setTabIndex').val(newIdx); // hidden field
        }
    });

    /* strip out all the auto classes since they create a conflict with the calendar */
    $('#tabs-wrap').removeClass('ui-tabs ui-widget ui-widget-content ui-corner-all')
    $('ul.ui-tabs-nav').removeClass('ui-tabs-nav ui-helper-reset ui-helper-clearfix ui-widget-header ui-corner-all')
    $('div#tabs-wrap div').removeClass('ui-tabs-panel ui-widget-content ui-corner-bottom')

    $(".delete_button").click(function(el){

	var id = $(this).attr("rel");
	var textField = $("#" + id);
	var imagePreview = $("#" + id + "_image img");
	
	// Remove Text, Image and Button
	$(textField).val("");
	$(imagePreview).hide();
	$(this).hide();

	// Add New Help Text
	$(textField).siblings("small").text("Remember to Save Changes to clear your logo.").css("fontWeight", "bold");

    });
});


function set_cp_curr_pay_type_symbol(currCode) {
	switch(currCode){
		case 'CHF':currSymbol = "Fr";break;
		case 'CZK':currSymbol = "Kč";break;
		case 'DKK':currSymbol = "kr";break;
		case 'HUF':currSymbol = "Ft";break;
		case 'HUF':currSymbol = "₪";break;
		case 'EUR':currSymbol = "€";break;
		case 'GBP':currSymbol = "£";break;
		case 'JPY':currSymbol = "¥";break;
		case 'MYR':currSymbol = "RM";break;
		case 'NOK':currSymbol = "kr";break;
		case 'PHP':currSymbol = "₱";break;
		case 'PLN':currSymbol = "zł";break;
		case 'SEK':currSymbol = "kr";break;
		case 'THB':currSymbol = "฿";break;
		default:currSymbol = "$";
	}
	jQuery('#cp_curr_pay_type_symbol').val(currSymbol);
	return currSymbol;
}


// ]]>
