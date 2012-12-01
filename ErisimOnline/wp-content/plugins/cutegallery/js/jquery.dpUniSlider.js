/*
 * jQuery DP UniSlider v1.0
 *
 * Copyright 2012, Diego Pereyra
 *
 * @Web: http://www.dpereyra.com
 * @Email: info@dpereyra.com
 *
 * Depends:
 * jquery.js
 */
 
 (function($){
	function UniSlider(element, options) {
		this.slider = $(element);
		
		this.settings = $.extend({}, $.fn.dpUniSlider.defaults, options);

			/* global variables */
			this.$container = $(this.slider).css('position','relative'),
			this.isIE = $.browser.msie && $.browser.version < 9,
			this.$slides = $('> li', this.$container),
			this.slidesTotal = this.$slides.length,
			this.animateTimer = null,
			this.windowWidth = $(window).width(),
			this.$currentSlide = this.$slides.eq('0'),
			this.currentSlideNum = ( this.settings.preselectSlideNum > 0 ? (this.settings.preselectSlideNum - 1) : 0 ),
			this.firstSlide = true,
			this.arrowsHeight = 0,
			this.outerHeight = 0,
			this.$pauseHoverElement = '',
			this.$autoLoader_icon = '',
			this.$nextSlide = $(), 
			this.no_draggable = false,
			this.hasTouch = false,
			this.downEvent = "mousedown.rs",
			this.moveEvent = "mousemove.rs",
			this.upEvent = "mouseup.rs",
			this.isDragging = false,
			this.successfullyDragged = false,
			this.startTime = 0,
			this.startMouseX = 0,
			this.startMouseY = 0,
			this.currentDragPosition = 0,
			this.lastDragPosition = 0,
			this.accelerationX = 0,
			this.tx = 0;
			
			// Touch support
			if("ontouchstart" in window) {
						
				this.hasTouch = true;
				this.downEvent = "touchstart.rs";
				this.moveEvent = "touchmove.rs";
				this.upEvent = "touchend.rs";
			} 
			
			this.init();
			
	}
	
	UniSlider.prototype = {
		init : function(){
			var me = this;
			
			if ( !this.isIE ) {
				this.$slides.css( {'position':'absolute','top':'0','left':'0','opacity':'0','zIndex':'3','visibility':'hidden','display':'block'} );
			} else {
				this.$slides.css( {'position':'absolute','top':'0','left':this.windowWidth,'zIndex':'3','display':'block'} );
			}
			
			this.goToSlide(this.currentSlideNum);
			
			// Create a wrapper
			$wrapper = $('<div />').addClass('dpUniSlider_wrapper');
			$(this.$container).before($wrapper);
			$wrapper.append($(this.$container));
			
			this.$pauseHoverElement = $wrapper;
			
			/* set container height */
			this.$container.css( 'height', this.$currentSlide.innerHeight() );
			
			this.$container.addClass('dpUniSlider_container');
			this.$slides.addClass('dpUniSlider_slide');
			
			this.$slides.each(this._manageSlides);
			
			
			/* Display Arrows */
			if(this.settings.showArrows) {
				var left_arrow = $('<a />').addClass('dpUniSlider_larrow').attr({href: '#'}),
					right_arrow = $('<a />').addClass('dpUniSlider_rarrow').attr({href: '#'});
					
				this.$container.parent().append(left_arrow);
				this.$container.parent().append(right_arrow);
				
				this.arrowsHeight = left_arrow.height();
				
				$(left_arrow).click(function(e) { e.preventDefault(); me._leftArrowActions(me); });
				
				$(right_arrow).click(function(e) { e.preventDefault(); me._rightArrowActions(me); });
				
				if ( !this.isIE ){ 
					$('.dpUniSlider_larrow, .dpUniSlider_rarrow', this.$container.parent()).hover(function(){
						$(this).stop(true,true).fadeTo( 'fast', .5 );
					}, function(){
						$(this).stop(true,true).fadeTo( 'fast', .9 );
					});
				}
				
				if ( this.slidesTotal !== 1 ) $( '.dpUniSlider_larrow, .dpUniSlider_rarrow', this.$container.parent() ).show();
			}
			
			/* Display Navigation */
			if(this.settings.showNavigation) {
				var navigation = $('<ul />').addClass('dpUniSlider_nav');
				
				for(var i = 1; i <= this.$slides.length; i++) {
					this._createNavigation(i, navigation, me);
				}
				
				if ( !this.isIE ){ 
					$(navigation).hover(function(){
						$(this).stop(true,true).fadeTo( 'fast', .8 );
					}, function(){
						$(this).stop(true,true).fadeTo( 'fast', .9 );
					});
				}
				
				this.$container.parent().append(navigation);

				$(this.$container.parent().find('ul.dpUniSlider_nav li')[this.currentSlideNum]).addClass('active');
				
				if ( this.slidesTotal !== 1 ) { 
					$( navigation ).show();
					
					if(this.settings.navPosition == '') { this.settings.navPosition = 'bottom-center'; }
					
					if(this.settings.navPosition == 'top-center' || this.settings.navPosition == 'bottom-center') {
						$( navigation ).css({
							marginLeft: -(navigation.outerWidth() / 2)+'px'
						}); 
					}
					
					if(this.settings.navPosition == 'top-left' || this.settings.navPosition == 'bottom-left') {
						$( navigation ).css({
							left: '10px'	
						});
					}
					
					if(this.settings.navPosition == 'top-right' || this.settings.navPosition == 'bottom-right') {
						$( navigation ).css({
							right: '10px',
							left: 'auto'	
						});
					}
					
					if(this.settings.navPosition == 'top-right' || this.settings.navPosition == 'top-center' || this.settings.navPosition == 'top-left') {
						$( navigation ).css({
							bottom: 'auto',
							top: '10px'	
						});
					}
				}

			}
				
			/* autoload options */
			this.setAutoAnimation();			
			
			if(this.settings.autoSlide && !this.$pauseHoverElement.find('.dpUniSlider_autoLoader').length && this.settings.showAutoSlideIcon) {
					this.$autoLoader_icon = $('<div />').addClass( 'dpUniSlider_autoLoader' );
					this.$pauseHoverElement.append(this.$autoLoader_icon);
					this.$autoLoader_icon.stop().fadeIn('fast');
			}
			
			/* touch support */
			if(this.slidesTotal !== 1 && this.settings.draggable) {
				this.$container.addClass('isDraggable');
				this.$container.bind(this.downEvent, function(e) { 	

					if(!this.no_draggable) {
						me.startDrag(e); 	
					} else if(!me.hasTouch) {							
						e.preventDefault();
					}								
				});	
			}
			
		},
		_manageSlides : function(i){
			/* Set Background */
			var $background = $(this).find("img[data-unislider-type='background']");
			if($background.length) {
				$(this).css({ backgroundImage: 'url('+$background.attr('src')+')' });
				$background.remove();
			}
			
			/* Set Caption */
			var $caption = $(this).find("span[data-unislider-type='caption']");
			if($caption.length) {
				caption_position = (typeof $($caption).data('unislider-position') !== 'undefined' ? $($caption).data('unislider-position') : 'top-center');
				
				var caption_text = $('<span />').addClass('dpUniSlider_caption').html($caption.html());
				$(this).append(caption_text);
				$caption.remove();
				
				if(caption_position == 'top-center' || caption_position == 'bottom-center') {
					caption_text.css({
						marginLeft: -(caption_text.outerWidth() / 2)+'px'	
					});
				}
				
				if(caption_position == 'top-left' || caption_position == 'bottom-left') {
					caption_text.css({
						left: '10px'	
					});
				}
				
				if(caption_position == 'top-right' || caption_position == 'bottom-right') {
					caption_text.css({
						right: '10px',
						left: 'auto'	
					});
				}
				
				if(caption_position == 'bottom-right' || caption_position == 'bottom-center' || caption_position == 'bottom-left') {
					caption_text.css({
						bottom: '10px',
						top: 'auto'	
					});
				}
				
			}
		},
		_leftArrowActions : function(me){
			me.clearAutoTimer();
			me.goToSlide( me.currentSlideNum - 1 );
			return false;
		},
		_rightArrowActions : function(me){
			me.clearAutoTimer();
			me.goToSlide( me.currentSlideNum + 1 );
			return false;
		},
		_createNavigation : (function(i, navigation, me) {
			navigation.append($('<li />').click(function() {
					me.goToSlide( i - 1 );
				})
			);
		}),
		goToSlide : function( nextSlideNum, autoSliding, id ){

			if ( autoSliding && this.settings.pauseOnHover && this.$pauseHoverElement.is('.dpUniSlider_ishovered') ) {
				this.clearAutoTimer();
				this.setAutoAnimation();
				return;
			}

			var directionMod = (this.currentSlideNum - nextSlideNum),
				startOffset = this.settings.startOffset * directionMod,
				endOffset = this.settings.endOffset * directionMod,
				currentstartOffset = this.windowWidth * directionMod + ( this.settings.slideOpacity * -directionMod ),
				nextstartOffset = this.windowWidth * directionMod,
				currentSlideAnimation = !this.isIE ? { 'left': currentstartOffset+'px','opacity':'0' } : { 'left': currentstartOffset+'px' },
				nextSlideAnimation = !this.isIE ? {'left': endOffset + 'px', 'opacity':'1'} : {'left': endOffset + 'px'},
				hideAutoloadIcon = false;
		
			/* OnSlidePrev and OnSlideNext Events */
			if ( directionMod > 0 ) { 
				if(typeof this.settings.onSlidePrev === 'function' && !this.firstSlide) { this.settings.onSlidePrev(nextSlideNum + 1); }
			} else { 
				if(typeof this.settings.onSlideNext === 'function' && !this.firstSlide) { this.settings.onSlideNext(nextSlideNum + 1); } 
			}
			
			/* Loop Hook */
			if(!this.settings.loop) {
				if(((nextSlideNum + 1) == this.slidesTotal) && this.settings.autoSlide) { this.clearAutoTimer();  }
				if((nextSlideNum + 1) > this.slidesTotal) { return; }

				if((this.slidesTotal - 1) == nextSlideNum) { 
					$( '.dpUniSlider_rarrow', this.$container.parent() ).hide().height(0); 
					hideAutoloadIcon = true;
				} else { 
					$( '.dpUniSlider_rarrow', this.$container.parent() ).height(this.arrowsHeight).show(); 
				}
			
				(nextSlideNum == 0) ? $( '.dpUniSlider_larrow', this.$container.parent() ).hide().height(0) : $( '.dpUniSlider_larrow', this.$container.parent() ).height(this.arrowsHeight).show(); 

			}
			
			if ( nextSlideNum < 0 ) nextSlideNum = this.slidesTotal-1;
			if ( nextSlideNum >= this.slidesTotal ) nextSlideNum = 0;
			
			this.currentSlideNum = nextSlideNum;
			
			this.$nextSlide = this.$slides.eq( nextSlideNum );
			
			if(this.settings.showNavigation) {
				$(this.$container.parent().find('ul.dpUniSlider_nav li')).removeClass('active');
				$(this.$container.parent().find('ul.dpUniSlider_nav li')[nextSlideNum]).addClass('active');
			}
			
			/* OnSlideBeforeMove event */
			if(typeof this.settings.onSlideBeforeMove === 'function' && !this.firstSlide) { this.settings.onSlideBeforeMove(); }
			
			this.no_draggable = true;
			
			var me = this;
			
			this.$currentSlide.css('zIndex','3').animate( 
				{ 'left': -startOffset + 'px' }, 
				this.settings.slideTransitionSpeed
			).animate( 
				currentSlideAnimation, 
				this.settings.slideTransitionSpeed, 
				function(){
				
					UniSlider.prototype._currentSlideTransition(me);
				
					me.$nextSlide.css({
						'left': -nextstartOffset+'px',
						'zIndex':'4',
						'visibility':'visible'
					}).animate( 
						nextSlideAnimation, 
						me.settings.slideTransitionSpeed 
					).animate( 
						{'left': '0'}, 
						me.settings.slideTransitionSpeed, 
						function(){
							
							me._nextSlideTransition(hideAutoloadIcon);
							
						} 
					);
				});
		},
		_currentSlideTransition : function(me) {
			$(me.$slides).css('visibility', 'hidden');

			if(me.settings.autoSlide && me.settings.showAutoSlideIcon) {
				me.$autoLoader_icon.fadeOut('fast');
			}
			
			me.outerHeight = 0;

			// Load children animations
			$(me.$nextSlide).children().each(function(i) { me._loadChildrenAnimations(i, this, me); });
			
			/* Set Height */
			if(me.settings.fixedHeight != '' && me.settings.fixedHeight > 0) {
				me.$container.height(me.settings.fixedHeight);
				me.$slides.height(me.settings.fixedHeight);
			} else {
				me.changeContainerHeight( me.$nextSlide );
			}
			
			me.$currentSlide.css('left', me.windowWidth+'px');
		},
		_nextSlideTransition : function(hideAutoloadIcon) {
			var self = this;
			this.$nextSlide.css('visibility', 'visible');
			this.$currentSlide = this.$nextSlide;

			this.clearAutoTimer();
			this.no_draggable = false;
			this.setAutoAnimation();
			
			/* OnAfterSlideMove event */
			if(typeof this.settings.onSlideAfterMove === 'function' && !this.firstSlide) { this.settings.onSlideAfterMove(); }
			
			this.$pauseHoverElement.unbind('mouseenter mouseleave');

			if(!hideAutoloadIcon && this.settings.autoSlide && this.settings.showAutoSlideIcon) {
				this.$autoLoader_icon.fadeIn('fast');
				
				if(this.settings.pauseOnHover) {
					
					this.$pauseHoverElement.hover(function(){
						$(this).addClass('dpUniSlider_ishovered');
						
						if(self.settings.showAutoSlideIcon) {
							if(self.$autoLoader_icon.is(':animated')) { self.$autoLoader_icon.stop().css('display', 'none'); return; }
							self.$autoLoader_icon.stop().fadeOut('fast');
						}
					}, function(){
						$(this).removeClass('dpUniSlider_ishovered');
						
						if(self.settings.showAutoSlideIcon) {
							if(self.$autoLoader_icon.is(':animated')) { self.$autoLoader_icon.stop().css({display: 'block', opacity: '1'}); return; }
							self.$autoLoader_icon.stop().fadeIn('fast');
						}
					});
				}
			}
			
			this.firstSlide = false;
		},
		_loadChildrenAnimations : function(i, element, me) {

			var unislider_settings 	= 	$(element).data('unislider-settings');
			var unislider_left 		= 	(typeof unislider_settings !== 'undefined' ? me.addPx(unislider_settings.left) : '');
			var unislider_right 	= 	(typeof unislider_settings !== 'undefined' ? me.addPx(unislider_settings.right) : '');
			var unislider_top 		= 	(typeof unislider_settings !== 'undefined' ? me.addPx(unislider_settings.top) : '');
			var unislider_align 	= 	(typeof unislider_settings !== 'undefined' ? unislider_settings.align : '');
			var unislider_width 	= 	(typeof unislider_settings !== 'undefined' ? me.addPx(unislider_settings.width) : '');
			var unislider_speed 	= 	me.settings.elementsDelayTransitionSpeed;
			
			$(element).not("span.dpUniSlider_caption").css({
				position: 'absolute',
				display: 'block',
				top: 0
			});	

			if(unislider_left == 'center') { 
				$(element).css({left: -($(element).width())+'px', opacity: 0});
				
				if(me.settings.elementsDelayTransition) {
					$(element).delay(unislider_speed * i).animate({left: '50%', marginLeft: -($(element).outerWidth() / 2)+'px', opacity: 1}, 1000);
				} else {
					$(element).css({left: '50%', marginLeft: -($(element).outerWidth() / 2)+'px', opacity: 1});
				}
			} else if(unislider_left != '' && typeof unislider_left != 'undefined') { 
				$(element).css({left: -($(element).width())+'px', opacity: 0});
				
				if(me.settings.elementsDelayTransition) {
					$(element).delay(unislider_speed * i).animate({left: unislider_left, opacity: 1}, 1000); 
				} else {
					$(element).css({left: unislider_left, opacity: 1});
				}
			}

			if(unislider_right == 'center') { 
				$(element).css('right', -($(element).width())+'px');
				
				if(me.settings.elementsDelayTransition) {
					$(element).delay(unislider_speed * i).animate({right: '50%', marginRight: -($(element).outerWidth() / 2)+'px'}, 1000);
				} else {
					$(element).css({right: '50%', marginRight: -($(element).outerWidth() / 2)+'px'});
				}
			} else if(unislider_right != '' && typeof unislider_right != 'undefined') { 
				$(element).css('right', -($(element).width())+'px');
				
				if(me.settings.elementsDelayTransition) {
					$(element).delay(unislider_speed * i).animate({right : unislider_right}, 1000); 
				} else {
					$(element).css({right : unislider_right});
				}
			}
			
			if(unislider_top != '') { $(element).css('top', unislider_top); }
			
			if(unislider_width != '') { $(element).css('width', unislider_width); }
			
			if(unislider_align != '') { $(element).css('text-align', unislider_align); }

			if(($(element).outerHeight() + parseInt($(element).css('top').replace('px', ''), 10)) > me.outerHeight) { 
				me.outerHeight = ($(element).outerHeight() + parseInt($(element).css('top').replace('px', ''), 10)); 
			}

			$(me.$nextSlide).height( me.outerHeight );
		},
		changeContainerHeight : function( element, callback_function ){
			var newHeight = element.innerHeight(),
				containerHeight = this.$container.innerHeight();
			if ( containerHeight === newHeight ){
				if ( callback_function instanceof Function ) callback_function.call( this );
				return;
			}

			$(this.$container).animate( { 'height': newHeight }, 'fast', function(){
				if ( callback_function instanceof Function ) callback_function.call( this );
			} );
		},
		setAutoAnimation : function(){
			if ( this.settings.autoSlide ) { var self = this; this.animateTimer = setTimeout( function(){ self.autoNext(self); }, this.settings.autoSlideSpeed ); }
		},
		clearAutoTimer : function(){
			if ( this.settings.autoSlide && typeof this.animateTimer !== 'undefined' ) clearTimeout( this.animateTimer );
		},
		autoNext : function(self){
			self.goToSlide( self.currentSlideNum + 1, true );
		},
		addPx : function(val){
			if(val != '' && !isNaN(val)) {
				val+= "px";
			}
			return val;
		},
		
		// Start dragging the slide
		startDrag:function(e) {
			if(!this.isDragging) {					
				var point;
				if(this.hasTouch) {
					//parsing touch event
					var currTouches = e.originalEvent.touches;
					if(currTouches && currTouches.length > 0) {
						point = currTouches[0];
					}					
					else {	
						return false;						
					}
				} else {
					point = e;		
					
					if (e.target) el = e.target;
					else if (e.srcElement) el = e.srcElement;
					
					if(el.toString() !== "[object HTMLEmbedElement]") {	
						e.preventDefault();						
					}
				}

				this.isDragging = true;
				
				var self = this;
				
				$(document).bind(this.moveEvent, function(e) { if(!self.hasTouch) { e.preventDefault();	} self.moveDrag(e); });
				$(document).bind(this.upEvent, function(e) { self.releaseDrag(e); });		

				
				startPos = this.tx = parseInt(this.$slides.css("left"), 10);	
				
				this.successfullyDragged = false;
				this.accelerationX = this.tx;
				this.startTime = (e.timeStamp || new Date().getTime());
				this.startMouseX = point.clientX;
				this.startMouseY = point.clientY;
			}
			return false;	
		},				
		moveDrag:function(e) {	
			
			var point;
			if(this.hasTouch) {	
				
				var touches = e.originalEvent.touches;
				// If touches more then one, so stop sliding and allow browser do default action
				
				if(touches.length > 1) {
					return false;
				}
				
				point = touches[0];	
			
				e.preventDefault();				
			} else {
				point = e;
				e.preventDefault();		
			}

			// Helps find last direction of drag move
			this.lastDragPosition = this.currentDragPosition;
			var distance = point.clientX - this.startMouseX;
			if(this.lastDragPosition != distance) {
				this.currentDragPosition = distance;
			}

			if(distance != 0)
			{	
				if(!this.settings.loop) {
					if(this.currentSlideNum == 0) {			
						if(distance > 0) {
							distance = Math.sqrt(distance) * 5;
						}			
					} else if(this.currentSlideNum == (this.slidesTotal -1)) {		
						if(distance < 0) {
							distance = -Math.sqrt(-distance) * 5;
						}	
					}
				}
				/* OnDrag Event */
				if(typeof this.settings.onDrag === 'function') { this.settings.onDrag(); }
				
				this.$container.addClass('isDragging');
				this.$slides.css("left", distance);		
				
			}	
			
			var timeStamp = (e.timeStamp || new Date().getTime());
			if (timeStamp - this.startTime > 350) {
				this.startTime = timeStamp;
				this.accelerationX = this.tx + distance;						
			}
			
				
			return false;		
		},
		releaseDrag:function(e) {

			if(this.isDragging) {	
				var self = this;
				this.isDragging = false;			
				this.$container.removeClass('isDragging');
				
				var endPos = parseInt(this.$slides.css('left'), 10);

				$(document).unbind(this.moveEvent).unbind(this.upEvent);					

				if(endPos == this._startPos) {						
					this.successfullyDragged = false;
					return;
				} else {
					this.successfullyDragged = true;
				}
				
				var dist = (this.accelerationX - endPos);		
				var duration =  Math.max(40, (e.timeStamp || new Date().getTime()) - this.startTime);
				// For nav speed calculation F=ma :)
				var v0 = Math.abs(dist) / duration;	
				
				
				var newDist = this.$slides.width() - Math.abs(startPos - endPos);
				var newDuration = Math.max((newDist * 1.08) / v0, 200);
				newDuration = Math.min(newDuration, 600);
	
				function returnToCurrent() {						
					newDist = Math.abs(startPos - endPos);
					newDuration = Math.max((newDist * 1.08) / v0, 200);
					newDuration = Math.min(newDuration, 500);

					$(self.$slides).animate(
						{left: 0}, 
						'fast'
					);
				}
				
				/* OnDragRelease Event */
				if(typeof this.settings.onDragRelease === 'function') { this.settings.onDragRelease(); }
				
				// calculate slide move direction
				if((startPos - this.settings.dragOffset) > endPos) {		

					if(this.lastDragPosition < this.currentDragPosition || (!this.settings.loop && (this.currentSlideNum == (this.slidesTotal -1)))) {	
						returnToCurrent();
						return false;					
					}

					this.goToSlide(this.currentSlideNum + 1);
				} else if((startPos + this.settings.dragOffset) < endPos) {	

					if(this.lastDragPosition > this.currentDragPosition || (!this.settings.loop && (this.currentSlideNum == 0))) {
						returnToCurrent();
						return false;
					}
					this.goToSlide(this.currentSlideNum - 1);

				} else {
					returnToCurrent();
				}
			}

			return false;
		}
	}
	
	$.fn.dpUniSlider = function(options) {    	
		return this.each(function(){
			var dpUniSlider = new UniSlider($(this), options);
			$(this).data("dpUniSlider", dpUniSlider);
		});
	};
	
	$.fn.dpUniSlider.defaults = { 
		autoSlideSpeed: 5000,
		autoSlide: false,
		pauseOnHover: false,
		showAutoSlideIcon: true,
		loop: true,
		showArrows: true,
		showNavigation: true,
		draggable: true,
		navPosition: 'bottom-center',
		fixedHeight: '',
		preselectSlideNum: 0,
		elementsDelayTransition: true,
		elementsDelayTransitionSpeed: 500,
		slideTransitionSpeed: 200,
		startOffset: 110,
		endOffset: 80,
		dragOffset: 50,
		slideOpacity: 800,
		onSlideBeforeMove: function(){},
		onSlideAfterMove: function(){},
		onSlidePrev: function(){},
		onSlideNext: function(){},
		onDrag: function(){},
		onDragRelease: function(){}
	}
	
	$.fn.dpUniSlider.settings = {}
})(jQuery);