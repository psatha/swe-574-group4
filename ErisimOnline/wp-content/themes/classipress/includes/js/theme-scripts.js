/*
 * ClassiPress theme jQuery functions
 * Written by AppThemes
 * http://www.appthemes.com
 *
 * Left .js uncompressed so it's easier to customize
 */

// <![CDATA[

jQuery(document).ready(function($) {

	/* style the select dropdown menus */
	$('select').selectBox({
		menuTransition: 'fade', // default, slide, fade
		menuSpeed: 'fast'
	});

	/* mouse over main image fade */
    $('.img-main, .post-gallery img').mouseover(function() {
        $(this).stop().animate( { opacity:0.6 }, 200 );
	}).mouseout(function() {
		$(this).stop().animate( { opacity:1 }, 200 );
    });

	/* initialize the image previewer */
	imagePreview();

});


/* Tab Control home main */
jQuery(function($) {
    var tabContainers = $('div.tabcontrol > div');
    tabContainers.hide().filter(':first').show();
    $('div.tabcontrol ul.tabnavig a').click(function () {
        tabContainers.hide();
        tabContainers.filter(this.hash).fadeIn(100);
        $('div.tabcontrol ul.tabnavig a').removeClass('selected');
        $(this).addClass('selected');
        return false;
    }).filter(':first').click();
});

/* Tab Control sidebar */
jQuery(function($) {
    var tabs = [];
    var tabContainers = [];
    $('ul.tabnavig a').each(function () {
        if (window.location.pathname.match(this.pathname)) {
            tabs.push(this);
            tabContainers.push($(this.hash).get(0));
        }
    });
	
	//hide all contrainers except execpt for the one from the URL hash or the first container
	if (window.location.hash != "" && window.location.hash.search('priceblock') >= 0){ 
		$(tabContainers).hide().filter(window.location.hash).show(); 
		//detecting <a> tab using its "href" which should always equal the hash
		$(tabs).filter( function (index) {
		    return $(this).attr('href') == window.location.hash;
		}).addClass('selected'); 
		$('body').scrollTop;
	}
	else { 
		$(tabContainers).hide().filter(':first').show()
		$(tabs).filter(':first').addClass('selected'); 
	}
	
    $(tabs).click(function() {
        // hide all tabs
        $(tabContainers).hide().filter(this.hash).fadeIn(500);
        $(tabs).removeClass('selected');
        $(this).addClass('selected');
        return false;
    });
	$('html').scrollTop(0); //because pageloads with hashes cause page to scroll
});



(function($) {
    this.imagePreview = function(){
        xOffset = 10;
        yOffset = 30;
    
        $('a.preview').hover(function(e){
            adTitle = $(this).find('img').attr('alt');
            $('body').append("<div id='preview'><img src='"+ this.rel +"' alt='' /><p>"+ adTitle +"</p></div>");
            $('#preview')
            .css('top', (e.pageY - xOffset) + 'px')
            .css('left', (e.pageX + yOffset) + 'px')
            .fadeIn('fast');
        },
        function(){
            $('#preview').remove();
        });
        $('a.preview').mousemove(function(e){
            $('#preview')
                .css('top', (e.pageY - xOffset) + 'px')
                .css('left', (e.pageX + yOffset) + 'px');
        });
    };
})(jQuery);


// auto complete the search field with tags
jQuery(document).ready(function($){
	$('#s').autocomplete({
		source: function( request, response ) {
			$.ajax({
				url: ajaxurl + '?action=ajax-tag-search-front&tax=' + theme_scripts_loc.appTaxTag,
				dataType: 'json',
				data: {
					term: request.term
				},
				error: function(XMLHttpRequest, textStatus, errorThrown){
					//alert('ERROR!: '+ errorThrown);
					//alert('ERROR!: '+ textStatus);
					//alert('ERROR!: '+ XMLHttpRequest);
				},
				success: function( data ) {
					response( $.map( data, function( item ) {
						return {
							term: item,
							value: unescapeHtml(item.name)
						}
					}));
				}
			});//end ajax
		},
		minLength: 2
	});
});


// used to unescape any encoded html passed from ajax json_encode (i.e. &amp;)
function unescapeHtml(html) {
      var temp = document.createElement("div");
      temp.innerHTML = html;
      var result = temp.childNodes[0].nodeValue;
      temp.removeChild(temp.firstChild)
      return result;
  }


// highlight search results
jQuery.fn.extend({
	highlight: function(search, insensitive, hclass){
		var regex = new RegExp("(<[^>]*>)|(\\b"+ search.replace(/([-.*+?^${}()|[\]\/\\])/g,"\\$1") +")", insensitive ? "ig" : "g");
		return this.html(this.html().replace(regex, function(a, b, c){
			return (a.charAt(0) == "<") ? a : "<span class=\""+ hclass +"\">" + c + "</span>";
		}));
	}
  });


/* Form Checkboxes Values Function */
function addRemoveCheckboxValues($cbval, $cbGroupVals) {
	var $a;
    if ($cbval.checked==true) {
        $a = document.getElementById($cbGroupVals);
        $a.value += ','+$cbval.value;
        $a.value = $a.value.replace(/^\,/,'');
    } else {
        $a = document.getElementById($cbGroupVals);
        $a.value = $a.value.replace($cbval.value+',','');
        $a.value = $a.value.replace($cbval.value,'');
        $a.value = $a.value.replace(/\,$/,'');
    }
}

// ]]>