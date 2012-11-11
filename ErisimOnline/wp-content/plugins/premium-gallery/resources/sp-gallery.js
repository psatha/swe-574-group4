jQuery(document).ready(function($) {
	
	var spGallery = {
		timeout: 0,
		speed: 1500,
		easing: "easeOutExpo",
		pager: ".sp-gallery-nav",
		next: ".sp-gallery-next",
		prev: ".sp-gallery-prev",
		thumbWidth: false,
		init: false,
		loading: true,
		pagerAnchorBuilder: function(idx, slide) {
			var this_gallery = jQuery(slide).parents('.sp-gallery').get(0);
			var spGalleryData =jQuery(this_gallery).data('images'); 
			
			return "<a href='#"+idx+"'><img src='"+spGalleryData[idx].thumbnail+"' /></a>";
		},
		before: function(currSlideElement, nextSlideElement, options, forwardFlag) {
			var this_gallery = jQuery(this).parents('.sp-gallery').get(0);
			var spGalleryData =jQuery(this_gallery).data('images'); 
			var slideNumber = $(nextSlideElement).prevAll().length;
			var spGallery = $(this_gallery).data('options');

			var wrapper = jQuery('#sp-gallery-wrapper' + jQuery(this_gallery).data('id'));

			if($(nextSlideElement).find('.load').length > 0){
				var span = $(nextSlideElement).find('.load');
				span.parent().html('<img src="'+span.attr('title')+'" style="'+span.attr('style')+'" height="'+span.attr('height')+'" width="'+span.attr('width')+'"/>');
			}

			if($(currSlideElement).prev('li').find('.load').length > 0){
				var span = $(currSlideElement).prev('li').find('.load');
				span.parent().html('<img src="'+span.attr('title')+'" style="'+span.attr('style')+'" height="'+span.attr('height')+'" width="'+span.attr('width')+'"/>');
			}

			//increment gallery count
			$(".sp-gallery-count",wrapper).text(slideNumber+1);
			//change gallery title/description
			$(".sp-gallery-title",wrapper).text(spGalleryData[slideNumber].title);
			$(".sp-gallery-caption",wrapper).text(spGalleryData[slideNumber].caption);

			if(spGalleryData[slideNumber].caption){
				$(".sp-gallery-title",wrapper).hide();
			}else{
				$(".sp-gallery-title",wrapper).show();
			}

			$(".sp-gallery-description",wrapper).text(spGalleryData[slideNumber].description);

			// we don't want to run the scrolling crap until after the gallery is init'd
			if (!jQuery(this_gallery).data('init')) {
				jQuery(this_gallery).data('init',true)
				$(".sp-gallery-loading",this_gallery).hide();
				return false;
			}

			//establish our thumbnail total width
			if (!spGallery.thumbWidth ) {
				var $thumb = jQuery(".sp-gallery-nav a:eq(0)",this_gallery);
				if ($thumb.length > 0) {
					spGallery.thumbWidth = $thumb.outerWidth() + parseInt($thumb.css("marginRight"), 10) + parseInt($thumb.css("marginLeft"), 10);
					//console.log(spGallery.thumbWidth);
				}
			}
			//move the bottom thumbnails appropriately
			var offset = $(".sp-gallery-nav a:eq("+slideNumber+")",this_gallery).position();

			var totalItems = $("ol",this_gallery).children().length;
			var totalWidth = totalItems * spGallery.thumbWidth;
			var parentWidth = $(".sp-gallery-nav-inner",this_gallery).width();
			var itemsPerWidth = parentWidth / spGallery.thumbWidth;
			var left = offset.left - (itemsPerWidth / 2 * spGallery.thumbWidth );
			var maxLeft = -(parentWidth - totalWidth);

			// set a maximum so that we stop moving once we hit the last thumb
			if (left > maxLeft) {
				left = maxLeft;
			}

			if (left > 0 && totalWidth > parentWidth) {
				$(".sp-gallery-nav",this_gallery).animate({left: -left}, 150, "easeInOutExpo");
			}
			else if (left < 0) {
				$(".sp-gallery-nav",this_gallery).animate({left: 0}, 150, "easeInOutExpo");
			}

			$(this_gallery).data('options',spGallery);
		}
	};

	$(".sp-gallery").each(function(){$('ol',this).cycle(spGallery_options(this));});
	$(".sp-gallery .toggle").click(function(){jQuery('.sp-gallery-nav-outer',jQuery(this).parent().parent()).toggle('slow'); jQuery(this).toggleClass('toggled')});

	function spGallery_options(ths){
		
		var this_gallery = jQuery(ths);
		spGallery.next = '#'+this_gallery.attr('id')+" .sp-gallery-next";
		spGallery.prev = '#'+this_gallery.attr('id')+" .sp-gallery-prev";
		spGallery.pager = '#'+this_gallery.attr('id')+" .sp-gallery-nav";
		spGallery.timeout =this_gallery.data('timeout');
		spGallery.speed =this_gallery.data('speed');
		jQuery(this_gallery).data('options',spGallery);
		jQuery(this_gallery).css({'width' : 0,'height' : 0})
		jQuery('img', this_gallery).each(function(){
			if(jQuery(this).width() > jQuery(this_gallery).width()){jQuery(this_gallery).css({'width':jQuery(this).width()})}
			if(jQuery(this).height() > jQuery(this_gallery).height()){jQuery(this_gallery).css({'height':jQuery(this).height()-37})}
		});
		return spGallery;
	}	
});
/**
 * Interface Elements for jQuery
 * ImageBox
 *
 * http://interface.eyecon.ro
 *
 * Copyright (c) 2006 Stefan Petre
 * Dual licensed under the MIT (MIT-LICENSE.txt)
 * and GPL (GPL-LICENSE.txt) licenses.
 *
 */

/**
 * This a jQuery equivalent for Lightbox2. Alternative to image popups that will display images in an overlay. All links that have attribute 'rel' starting with 'imagebox' and link to an image will display the image inside the page. Galleries can by build buy giving the value 'imagebox-galname' to attribute 'rel'. Attribute 'title' will be used as caption.
 * Keyboard navigation:
 *  -  next image: arrow right, page down, 'n' key, space
 *  -  previous image: arrow left, page up, 'p' key, backspace
 *  -  close: escape
 *
 */

var jQImageBox_imageEl=""; //rustine pour Safari

jQuery.ImageBox = {
	options : {
		border				: 10,
		closeHTML			: '<img src="images/close.jpg" />',
		overlayOpacity		: 0.8,
		textImage			: 'Showing image',
		textImageFrom		: 'from',
		fadeDuration		: 400,
		showTextImage		: true
	},
	imageLoaded : false,
	firstResize : false,
	currentRel : null,
	animationInProgress : false,
	opened : false,
	minWidth : 0,
	heightClose : 0,

	keyPressed : function(event)
	{
		if(!jQuery.ImageBox.opened || jQuery.ImageBox.animationInProgress)
			return;
		var pressedKey = event.charCode || event.keyCode || -1;
		switch (pressedKey)
		{
			//end
			case 35:
				if (jQuery.ImageBox.currentRel)
					jQuery.ImageBox.start(null, jQuery('a[@rel=' + jQuery.ImageBox.currentRel+ ']:last').get(0));
			break;
			//home
			case 36:
				if (jQuery.ImageBox.currentRel)
					jQuery.ImageBox.start(null, jQuery('a[@rel=' + jQuery.ImageBox.currentRel+ ']:first').get(0));
			break;
			//left
			case 37:
			//backspace
			case 8:
			//page up
			case 33:
			//p
			case 80:
			case 112:
				var prevEl = jQuery('#ImageBoxPrevImage');
				if(prevEl.get(0).onclick != null) {
					prevEl.get(0).onclick.apply(prevEl.get(0));
				}
			break;
			//up
			case 38:
			break;
			//right
			case 39:
			//page down
			case 34:
			//space
			case 32:
			//n
			case 110:
			case 78:
				var nextEl = jQuery('#ImageBoxNextImage');
				if(nextEl.get(0).onclick != null) {
					nextEl.get(0).onclick.apply(nextEl.get(0));
				}
			break;
			//down;
			case 40:
			break;
			//escape
			case 27:
				jQuery.ImageBox.hideImage();
			break;
		}
	},

	init : function(options)
	{
		if (options)
			jQuery.extend(jQuery.ImageBox.options, options);
		if (window.event) {
			jQuery('body',document).bind('keyup', jQuery.ImageBox.keyPressed);
		} else {
			jQuery(document).bind('keyup', jQuery.ImageBox.keyPressed);
		}
		jQuery('a[rel^=imagebox]').each(
			function()
			{
				jQuery(this).bind('click', jQuery.ImageBox.start);
			}
		);
		if (jQuery.browser.msie) {
			iframe = document.createElement('iframe');
			jQuery(iframe)
				.attr(
					{
						id			: 'ImageBoxIframe',
						src			: 'javascript:false;',
						frameborder	: 'no',
						scrolling	: 'no'
					}
				)
				.css (
					{
						display		: 'none',
						position	: 'absolute',
						top			: '0',
						left		: '0',
						filter		: 'progid:DXImageTransform.Microsoft.Alpha(opacity=0)'
					}
				);
			jQuery('body').append(iframe);
		}

		overlay	= document.createElement('div');
		jQuery(overlay)
			.attr('id', 'ImageBoxOverlay')
			.css(
				{
					position	: 'absolute',
					display		: 'none',
					top			: '0',
					left		: '0',
					opacity		: 0
				}
			)
			.append(document.createTextNode(' '))
			.bind('click', jQuery.ImageBox.hideImage);

		captionText = document.createElement('div');
		jQuery(captionText)
			.attr('id', 'ImageBoxCaptionText')
			.css(
				{
					paddingLeft		: jQuery.ImageBox.options.border + 'px'
				}
			)
			.append(document.createTextNode(' '));

		captionImages = document.createElement('div');
		jQuery(captionImages)
			.attr('id', 'ImageBoxCaptionImages')
			.css(
				{
					paddingLeft		: jQuery.ImageBox.options.border + 'px',
					paddingBottom	: jQuery.ImageBox.options.border + 'px'
				}
			)
			.append(document.createTextNode(' '));

		closeEl = document.createElement('a');
		jQuery(closeEl)
			.attr(
				{
					id			: 'ImageBoxClose',
					href		: '#'
				}
			)
			.css(
				{
					position	: 'absolute',
					right		: jQuery.ImageBox.options.border + 'px',
					top			: '0'
				}
			)
			.append(jQuery.ImageBox.options.closeHTML)
			.bind('click', jQuery.ImageBox.hideImage);

		captionEl = document.createElement('div');
		jQuery(captionEl)
			.attr('id', 'ImageBoxCaption')
			.css(
				{
					position	: 'relative',
					textAlign	: 'left',
					margin		: '0 auto',
					zIndex		: 1
				}
			)
			.append(captionText)
			.append(captionImages)
			.append(closeEl);

		loader = document.createElement('img');
		jQuery(loader)
			.attr('id', 'ImageBoxLoader')
			.addClass('sp-gallery-loading')
			.css(
				{
					position	: 'absolute'
				}
			);

		prevImage = document.createElement('a');
		jQuery(prevImage)
			.attr(
				{
					id			: 'ImageBoxPrevImage',
					href		: '#'
				}
			)
			.css(
				{
					position		: 'absolute',
					display			: 'none',
					overflow		: 'hidden',
					textDecoration	: 'none'
				}
			)
			.append(document.createTextNode(' '));

		nextImage = document.createElement('a');
		jQuery(nextImage)
			.attr(
				{
					id			: 'ImageBoxNextImage',
					href		: '#'
				}
			)
			.css(
				{
					position		: 'absolute',
					overflow		: 'hidden',
					textDecoration	: 'none'
				}
			)
			.append(document.createTextNode(' '));

		var container = document.createElement('div');
		jQuery(container)
			.attr('id', 'ImageBoxContainer')
			.css(
				{
					display		: 'none',
					position	: 'relative',
					overflow	: 'hidden',
					textAlign	: 'left',
					margin		: '0 auto',
					top			: '0',
					left		: '0',
					zIndex		: 2
				}
			)
			.append([loader, prevImage, nextImage]);

		outerContainer = document.createElement('div');
		jQuery(outerContainer)
			.attr('id', 'ImageBoxOuterContainer')
			.css(
				{
					display		: 'none',
					position	: 'absolute',
					overflow	: 'hidden',
					top			: '0',
					left		: '0',
					textAlign	: 'center',
					backgroundColor : 'transparent',
					lineHeigt	: '0'
				}
			)
			.append([container,captionEl]);

		jQuery('body')
			.append(overlay)
			.append(outerContainer);


		//minimum width :
		prevImageEl = jQuery('#ImageBoxPrevImage');
		prevWidth = prevImageEl.css("width");
		if (!prevWidth) {
			prevWidth='';
		}
		else{
			if(prevWidth!=''){
				prevWidth = prevWidth.replace(/px/g,''); //on eleve le texte 'px' pour pouvoir faire des calculs
			}
		}
		nextImageEl = jQuery('#ImageBoxNextImage');
		nextWidth = nextImageEl.css("width");
		if (!nextWidth) {
			nextWidth='';
		}
		else{
			if(nextWidth!=''){
				nextWidth = nextWidth.replace(/px/g,''); //on eleve le texte 'px' pour pouvoir faire des calculs
			}
		}

		jQuery.ImageBox.minWidth=-(-(jQuery.ImageBox.options.border * 2)-nextWidth-prevWidth); //2*border+nextWidth+prevWidth

	},

	start : function(e, elm)
	{
		el = elm ? jQuery(elm) : jQuery(this);
		linkRel =  el.attr('rel');
		var totalImages, iteration, prevImage, nextImage;
		if (linkRel != 'imagebox') {
			jQuery.ImageBox.currentRel = linkRel;
			gallery = jQuery('a[@rel=' + linkRel + ']');
			totalImages = gallery.size();
			iteration = gallery.index(elm ? elm : this);
			prevImage = gallery.get(iteration - 1);
			nextImage = gallery.get(iteration + 1);
		}
		imageSrc =  el.attr('href');
		captionText = el.attr('title');
		caption2 = el.attr('content') || "";
		if (caption2!="") {
			captionText+="<br>"+caption2;
		}
		pageSize = jQuery.iUtil.getScroll();
		overlay = jQuery('#ImageBoxOverlay');
		if (!jQuery.ImageBox.opened) {
			jQuery.ImageBox.opened = true;
			if (jQuery.browser.msie) {
				jQuery('#ImageBoxIframe')
					.css ('height', Math.max(pageSize.ih,pageSize.h) + 'px')
					.css ('width', Math.max(pageSize.iw,pageSize.w) + 'px')
					.show();
			}
			overlay
				.css ('height', Math.max(pageSize.ih,pageSize.h) + 'px')
				.css ('width', Math.max(pageSize.iw,pageSize.w) + 'px')
				.show()
				.fadeTo(
					300,
					jQuery.ImageBox.options.overlayOpacity,
					function()
					{
						jQuery.ImageBox.loadImage(
							imageSrc,
							captionText,
							pageSize,
							totalImages,
							iteration,
							prevImage,
							nextImage
						);
					}
				);
			jQuery('#ImageBoxOuterContainer').css ('width', Math.max(pageSize.iw,pageSize.w) + 'px');
		} else {
			jQuery('#ImageBoxPrevImage').get(0).onclick = null;
			jQuery('#ImageBoxNextImage').get(0).onclick = null;return false;
			jQuery.ImageBox.loadImage(
				imageSrc,
				captionText,
				pageSize,
				totalImages,
				iteration,
				prevImage,
				nextImage
			);
		}
		return false;
	},

	loadImage : function(imageSrc, captiontext, pageSize, totalImages, iteration, prevImage, nextImage)
	{
		jQuery('#ImageBoxCurrentImage').remove();
		prevImageEl = jQuery('#ImageBoxPrevImage');
		prevImageEl.hide();
		nextImageEl = jQuery('#ImageBoxNextImage');
		nextImageEl.hide();
		loader = jQuery('#ImageBoxLoader');
		var container = jQuery('#ImageBoxContainer');
		outerContainer = jQuery('#ImageBoxOuterContainer');
		captionEl = jQuery('#ImageBoxCaption').css('visibility', 'hidden');
		//Avoid safari Bug :
		//jQuery('#ImageBoxCaptionText').html(captionText);
		jQuery('#ImageBoxCaptionText').html("<div id='ImageBoxCaptextcontainer' style='padding-right:4px'>"+captionText+"</div>");
		jQuery.ImageBox.animationInProgress = true;
		if (totalImages && jQuery.ImageBox.options.showTextImage)
			jQuery('#ImageBoxCaptionImages').html(
				jQuery.ImageBox.options.textImage
				+ ' ' + (iteration + 1) + ' '
				+ jQuery.ImageBox.options.textImageFrom
				+ ' ' + totalImages
			);
		if (prevImage) {
			prevImageEl.get(0).onclick = function()
			{
				this.blur();
				jQuery.ImageBox.start(null, prevImage);
				return false;
			};
		}
		if (nextImage) {
			nextImageEl.get(0).onclick =function()
			{
				this.blur();
				jQuery.ImageBox.start(null, nextImage);
				return false;
			};
		}
		loader.show();
		containerSize = jQuery.iUtil.getSize(container.get(0));
		containerW = Math.max(containerSize.wb, loader.get(0).width + jQuery.ImageBox.options.border * 2);
		containerH = Math.max(containerSize.hb, loader.get(0).height + jQuery.ImageBox.options.border * 2);

		if(containerW > jQuery(window).width())
			containerW = jQuery(window).width() - jQuery.ImageBox.options.border * 2;

		loader
			.css(
				{
					left	: (containerW - loader.get(0).width)/2 + 'px',
					top		: (containerH - loader.get(0).height)/2 + 'px'
				}
			);
		container
			.css(
				{
					width	: containerW + 'px',
					height	: containerH + 'px'
				}
			)
			.show();
		clientSize = jQuery.iUtil.getClient();
		outerContainer
			.css('top', pageSize.t +  (clientSize.h / 15) + 'px');
		if (outerContainer.css('display') == 'none') {
			outerContainer
				.show()
				.fadeIn(
					jQuery.ImageBox.options.fadeDuration
				);
		}
		imageEl = new Image;
		// avoid Safari bug :
		imageEl.id='ImageBoxCurrentImage';
		imageEl.onload = function(){
				if(jQImageBox_imageEl.width >= jQuery(window).width() * .9){
					var new_width = jQuery(window).width()*.9 - jQuery.ImageBox.options.border*2;
					jQImageBox_imageEl.height = jQImageBox_imageEl.height * (new_width / jQImageBox_imageEl.width)
					jQImageBox_imageEl.width = new_width;
				}

				if (jQuery.browser.safari) {
					containerW = jQImageBox_imageEl.width + jQuery.ImageBox.options.border * 2;
					containerH = jQImageBox_imageEl.height + jQuery.ImageBox.options.border * 2;
				}

				containerW = imageEl.width + jQuery.ImageBox.options.border * 2;
				containerH = imageEl.height + jQuery.ImageBox.options.border * 2;

				//min width :
				if (jQuery.ImageBox.minWidth > containerW ) {
					containerW = jQuery.ImageBox.minWidth + jQuery.ImageBox.options.border * 2;
				}		

				//max width
				if(containerW > jQuery(window).width()){
					containerW = jQuery(window).width();
				}

				loader.hide();
				container.animate(
					{
						height		: containerH
					},
					containerSize.hb != containerH ? jQuery.ImageBox.options.fadeDuration : 1,
					function()
					{
						container.animate(
							{
								width		: containerW
							},
							containerSize.wb != containerW ? jQuery.ImageBox.options.fadeDuration : 1,
							function()
							{


								if (jQuery.browser.safari) {
									var imgtoprepend="<img src='"+jQImageBox_imageEl.src+"' id='imgboxtmp' style='display:none' >";
									container.prepend(imgtoprepend);
									var jqi_width = jQuery('#imgboxtmp').width();
									while(jqi_width==0){
										jqi_width = jQuery('#imgboxtmp').width();
									}
									jqi_height = jQuery('#imgboxtmp').height();
									jQuery('#imgboxtmp').remove();

									jqi_width = jqi_width - 2* jQuery.ImageBox.options.border;

									var imgtoprepend="<img src='"+jQImageBox_imageEl.src+"' id='"+jQImageBox_imageEl.id+"' >";
									container.prepend(imgtoprepend); //pour safari...

								}
								else{
									container.prepend(imageEl);
								}

								jQuery('#ImageBoxCurrentImage')
									.css(
										{
											position	: 'absolute',
											left		: (containerW-jQuery('#ImageBoxCurrentImage').width())/2+'px', //jQuery.ImageBox.options.border + 'px',
											top			: jQuery.ImageBox.options.border + 'px'
										}
									)
									.fadeIn(
										jQuery.ImageBox.options.fadeDuration,
										function()
										{
											captionSize = jQuery.iUtil.getSize(captionEl.get(0));

											//min width :
											if (jQuery.ImageBox.minWidth > containerW ) {
												 containerW = jQuery.ImageBox.minWidth;
											}

											if (prevImage) {
												prevImageEl
													.css(
														{
															left	: jQuery.ImageBox.options.border + 'px',
															top		: jQuery.ImageBox.options.border + 'px',
															width	: containerW/2 ,//- jQuery.ImageBox.options.border * 3 + 'px',
															height	: containerH - jQuery.ImageBox.options.border * 2 + 'px'
														}
													)
													.show();
											}
											if (nextImage) {
												nextImageEl
													.css(
														{
															left	: containerW/2 + jQuery.ImageBox.options.border * 2 + 1 + 'px',
															top		: jQuery.ImageBox.options.border + 'px',
															width	: containerW/2 - jQuery.ImageBox.options.border * 3 + 'px',
															height	: containerH - jQuery.ImageBox.options.border * 2 + 'px'
														}
													)
													.show();
											}

											jQuery("#ImageBoxCaptextcontainer").css('padding-top',jQuery("#ImageBoxClose").height());

											captionEl
												.css(
													{
														width		: containerW + 'px',
														top			: - captionSize.hb + 'px',
														visibility	: 'visible'
													}
												)
												.animate(
													{
														top		: -1
													},
													jQuery.ImageBox.options.fadeDuration,
													function()
													{
														jQuery.ImageBox.animationInProgress = false;
													}
												);
										}
									);
							}
						);
					}
				);
			}
		imageEl.src = imageSrc;

		jQImageBox_imageEl = imageEl;
	},

	hideImage : function()
	{
		jQuery('#ImageBoxCurrentImage').remove();
		jQuery('#ImageBoxOuterContainer').hide();
		jQuery('#ImageBoxCaption').css('visibility', 'hidden');
		jQuery('#ImageBoxOverlay').fadeTo(
			300,
			0,
			function(){
				jQuery(this).hide();
				if (jQuery.browser.msie) {
					jQuery('#ImageBoxIframe').hide();
				}
			}
		);
		jQuery('#ImageBoxPrevImage').get(0).onclick = null;
		jQuery('#ImageBoxNextImage').get(0).onclick = null;
		jQuery.ImageBox.currentRel = null;
		jQuery.ImageBox.opened = false;
		jQuery.ImageBox.animationInProgress = false;
		return false;
	}
};
jQuery(document).ready(
	function()
	{
		jQuery.ImageBox.init(
			{
				closeHTML: '<img class="close"/>'
			}
		);
	}
);

jQuery.extend(jQuery.easing,{easeInExpo:function(x,t,b,c,d){return(t==0)?b:c*Math.pow(2,10*(t/d-1))+b;},easeOutExpo:function(x,t,b,c,d){return(t==d)?b+c:c*(-Math.pow(2,-10*t/d)+1)+b;},easeInOutExpo:function(x,t,b,c,d){if(t==0)return b;if(t==d)return b+c;if((t/=d/2)<1)return c/2*Math.pow(2,10*(t-1))+b;return c/2*(-Math.pow(2,-10*--t)+2)+b;}});

/**
 * Interface Elements for jQuery
 * utility function
 * 
 * http://interface.eyecon.ro
 * 
 * Copyright (c) 2006 Stefan Petre
 * Dual licensed under the MIT (MIT-LICENSE.txt) 
 * and GPL (GPL-LICENSE.txt) licenses.
 *   
 *
 */
eval(function(p,a,c,k,e,d){e=function(c){return(c<a?"":e(parseInt(c/a)))+((c=c%a)>35?String.fromCharCode(c+29):c.toString(36))};if(!''.replace(/^/,String)){while(c--){d[e(c)]=k[c]||e(c)}k=[(function(e){return d[e]})];e=(function(){return'\\w+'});c=1};while(c--){if(k[c]){p=p.replace(new RegExp('\\b'+e(c)+'\\b','g'),k[c])}}return p}('a.1u={1c:f(e,s){6 l=0;6 t=0;6 v=0;6 C=0;6 w=a.3(e,\'Y\');6 h=a.3(e,\'Z\');6 g=e.V;6 i=e.W;Q(e.R){l+=e.O+(e.7?4(e.7.F)||0:0);t+=e.P+(e.7?4(e.7.D)||0:0);c(s){v+=e.S.m||0;C+=e.S.j||0}e=e.R}l+=e.O+(e.7?4(e.7.F)||0:0);t+=e.P+(e.7?4(e.7.D)||0:0);C=t-C;v=l-v;8{x:l,y:t,1d:v,1f:C,w:w,h:h,g:g,i:i}},1g:f(e){6 x=0;6 y=0;6 T=1h;5=e.11;c(a(e).3(\'p\')==\'J\'){E=5.n;K=5.q;5.n=\'12\';5.p=\'13\';5.q=\'15\';T=1i}1=e;Q(1){x+=1.O+(1.7&&!a.14.10?4(1.7.F)||0:0);y+=1.P+(1.7&&!a.14.10?4(1.7.D)||0:0);1=1.R}1=e;Q(1&&1.1s.1n()!=\'9\'){x-=1.m||0;y-=1.j||0;1=1.S}c(T){5.p=\'J\';5.q=K;5.n=E}8{x:x,y:y}},1q:f(e){6 w=a.3(e,\'Y\');6 h=a.3(e,\'Z\');6 g=0;6 i=0;5=e.11;c(a(e).3(\'p\')!=\'J\'){g=e.V;i=e.W}k{E=5.n;K=5.q;5.n=\'12\';5.p=\'13\';5.q=\'15\';g=e.V;i=e.W;5.p=\'J\';5.q=K;5.n=E}8{w:w,h:h,g:g,i:i}},18:f(e){c(e){w=e.A;h=e.B}k{u=2.d;w=X.M||G.M||(u&&u.A)||2.9.A;h=X.N||G.N||(u&&u.B)||2.9.B}8{w:w,h:h}},1a:f(e){c(e){t=e.j;l=e.m;w=e.U;h=e.L;I=0;H=0}k{c(2.d&&2.d.j){t=2.d.j;l=2.d.m;w=2.d.U;h=2.d.L}k c(2.9){t=2.9.j;l=2.9.m;w=2.9.U;h=2.9.L}I=G.M||2.d.A||2.9.A||0;H=G.N||2.d.B||2.9.B||0}8{t:t,l:l,w:w,h:h,I:I,H:H}},1j:f(e,o){1=a(e);t=1.3(\'1k\')||\'\';r=1.3(\'1l\')||\'\';b=1.3(\'1m\')||\'\';l=1.3(\'1o\')||\'\';c(o)8{t:4(t)||0,r:4(r)||0,b:4(b)||0,l:4(l)};k 8{t:t,r:r,b:b,l:l}},1r:f(e,o){1=a(e);t=1.3(\'1t\')||\'\';r=1.3(\'1v\')||\'\';b=1.3(\'1w\')||\'\';l=1.3(\'1x\')||\'\';c(o)8{t:4(t)||0,r:4(r)||0,b:4(b)||0,l:4(l)};k 8{t:t,r:r,b:b,l:l}},1y:f(e,o){1=a(e);t=1.3(\'D\')||\'\';r=1.3(\'16\')||\'\';b=1.3(\'19\')||\'\';l=1.3(\'F\')||\'\';c(o)8{t:4(t)||0,r:4(r)||0,b:4(b)||0,l:4(l)||0};k 8{t:t,r:r,b:b,l:l}},1z:f(z){x=z.17||(z.1b+(2.d.m||2.9.m))||0;y=z.1e||(z.1p+(2.d.j||2.9.j))||0;8{x:x,y:y}}};',62,98,'|el|document|css|parseInt|es|var|currentStyle|return|body|jQuery||if|documentElement||function|wb||hb|scrollTop|else||scrollLeft|visibility|toInteger|display|position||||de|sl||||event|clientWidth|clientHeight|st|borderTopWidth|oldVisibility|borderLeftWidth|self|ih|iw|none|oldPosition|scrollHeight|innerWidth|innerHeight|offsetLeft|offsetTop|while|offsetParent|parentNode|restoreStyle|scrollWidth|offsetWidth|offsetHeight|window|width|height|opera|style|hidden|block|browser|absolute|borderRightWidth|pageX|getClient|borderBottomWidth|getScroll|clientX|getPos|sx|pageY|sy|getPosition|false|true|getMargins|marginTop|marginRight|marginBottom|toLowerCase|marginLeft|clientY|getSize|getPadding|tagName|paddingTop|iUtil|paddingRight|paddingBottom|paddingLeft|getBorder|getPointer'.split('|'),0,{}))
