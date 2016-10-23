
(function(window, document, $, undefined) {

   $.vobox = function(elem, options) {

      var defaults = {
         useCSS: true,
         initialIndexOnArray: 0,
         hideBarsDelay: 7000,
         videoMaxWidth: 1140,
         vimeoColor: 'CCCCCC',
         beforeOpen: null,
         afterClose: null
      },
      plugin = this,
              elements = [],
              elem = elem,
              selector = elem.selector,
              $selector = $(selector),
              currPlay = {'index' : null , 'type' : null},
              isTouch = document.createTouch !== undefined || ('ontouchstart' in window) || ('onmsgesturechange' in window) || navigator.msMaxTouchPoints,
              supportSVG = !!(window.SVGSVGElement),
              winWidth = window.innerWidth ? window.innerWidth : $(window).width(),
              winHeight = window.innerHeight ? window.innerHeight : $(window).height(),
              html = '<div id="vobox-overlay">\
				<div id="vobox-slider"></div>\
				<div class="navbar" id="vobox-caption">\
                        <div class="navbar-inner">\
                        <div class="container-fluid">\
                        <ul class="nav">\
                        <li id="vobox-title"></li>\
                        </ul>\
                        <ul class="nav pull-right">\
                        <li>\
                        <div class="foacts actions btn-group"></div>\
                        </li>\
                        <li><a id="vobox-close"></a></li>\
                        </ul>\
                        </div>\
                        </div>\
                        </div>\
				<div id="vobox-action">\
					<a id="vobox-prev"></a>\
					<a id="vobox-next"></a>\
				</div>\
		</div>',
            playing = false;

      plugin.settings = {}

      plugin.init = function() {

         plugin.settings = $.extend({}, defaults, options);

         if ($.isArray(elem)) {

            elements = elem;
            ui.target = $(window);
            ui.init(plugin.settings.initialIndexOnArray);

         } else {
            $selector.off("click");

            $selector.click(function(e) {
               elements = [];
               var index, relType, relVal;

               if (!relVal) {
                  relType = 'rel';
                  relVal = $(this).attr(relType);
               }

               if (relVal && relVal !== '' && relVal !== 'nofollow') {
                  $elem = $selector.filter('[' + relType + '="' + relVal + '"]');
               } else {
                  $elem = $(selector);
               }

               $elem.each(function(curr) {

                  var title = null, href = null;

                  if ($(this).attr('title'))
                     title = $(this).attr('title');

                  if ($(this).attr('href'))
                     href = $(this).attr('href');

                  if ($(this).data("type"))
                     type = $(this).data("type");

                  elements.push({
                     href: href,
                     title: title,
                     type: type,
                     loaded: false
                  });
                  
                  if(type == 'video' || type == 'audio'){
                     elements[curr].playing = false;
                  }
               });
               
               index = $elem.index($(this));
               e.preventDefault();
               e.stopPropagation();
               ui.target = $(e.target);
               ui.init(index);
            });
         }
      }

      plugin.revobox = function() {

         if (!$.isArray(elem)) {
            ui.destroy();
            $elem = $(selector);
            ui.actions();
         }
      }

      var ui = {
         init: function(index) {
            if (plugin.settings.beforeOpen)
               plugin.settings.beforeOpen();
            this.target.trigger('vobox-start');
            $.vobox.isOpen = true;
            this.build();
            this.openSlide(index);
            this.openMedia(index);
            this.preloadMedia(index + 1);
            this.preloadMedia(index - 1);
         },
         build: function() {
            var $this = this;

            $('body').prepend(html);

            if ($this.doCssTrans()) {
               $('#vobox-slider').css({
                  '-webkit-transition': 'left 0.4s ease',
                  '-moz-transition': 'left 0.4s ease',
                  '-o-transition': 'left 0.4s ease',
                  '-khtml-transition': 'left 0.4s ease',
                  'transition': 'left 0.4s ease'
               });
               $('#vobox-overlay').css({
                  '-webkit-transition': 'opacity 1s ease',
                  '-moz-transition': 'opacity 1s ease',
                  '-o-transition': 'opacity 1s ease',
                  '-khtml-transition': 'opacity 1s ease',
                  'transition': 'opacity 1s ease'
               });
               $('#vobox-action, #vobox-caption').css({
                  '-webkit-transition': '0.5s',
                  '-moz-transition': '0.5s',
                  '-o-transition': '0.5s',
                  '-khtml-transition': '0.5s',
                  'transition': '0.5s'
               });
            }


            if (supportSVG) {
               var bg = $('#vobox-close').css('background-image');
               bg = bg.replace('png', 'svg');
               $('#vobox-action #vobox-prev,#vobox-action #vobox-next, #vobox-close').css({
                  'background-image': bg
               });
            }

            $.each(elements, function() {
               $('#vobox-slider').append('<div class="slide"></div>');
            });

            $this.setDim();
            $this.actions();
            $this.keyboard();
            $this.gesture();
            $this.animBars();
            $this.resize();

         },
         setDim: function() {

            var width, height, sliderCss = {};

            if ("onorientationchange" in window) {

               window.addEventListener("orientationchange", function() {
                  if (window.orientation == 0) {
                     width = winWidth;
                     height = winHeight;
                  } else if (window.orientation == 90 || window.orientation == -90) {
                     width = winHeight;
                     height = winWidth;
                  }
               }, false);


            } else {

               width = window.innerWidth ? window.innerWidth : $(window).width();
               height = window.innerHeight ? window.innerHeight : $(window).height();
            }

            sliderCss = {
               width: width,
               height: height
            }


            $('#vobox-overlay').css(sliderCss);

         },
         resize: function() {
            var $this = this;

            $(window).resize(function() {
               $this.setDim();
            }).resize();
         },
         supportTransition: function() {
            var prefixes = 'transition WebkitTransition MozTransition OTransition msTransition KhtmlTransition'.split(' ');
            for (var i = 0; i < prefixes.length; i++) {
               if (document.createElement('div').style[prefixes[i]] !== undefined) {
                  return prefixes[i];
               }
            }
            return false;
         },
         doCssTrans: function() {
            if (plugin.settings.useCSS && this.supportTransition()) {
               return true;
            }
         },
         gesture: function() {
            if (isTouch) {
               var $this = this,
                       distance = null,
                       swipMinDistance = 10,
                       startCoords = {},
                       endCoords = {};
               var bars = $('#vobox-caption, #vobox-action');

               bars.addClass('visible-bars');
               $this.setTimeout();

               $('body').bind('touchstart.vobox', function(e) {
                  
                  $(this).addClass('touching');
                  
                  endCoords = e.originalEvent.targetTouches[0];
                  startCoords.pageX = e.originalEvent.targetTouches[0].pageX;

                  $('.touching').bind('touchmove.vobox', function(e) {
                     e.preventDefault();
                     e.stopPropagation();
                     endCoords = e.originalEvent.targetTouches[0];

                  });

                  return false;

               }).bind('touchend.vobox', function(e) {
                  e.preventDefault();
                  e.stopPropagation();
                  
                  if(isModal()) return;

                  distance = endCoords.pageX - startCoords.pageX;

                  if (distance >= swipMinDistance) {

                     // swipeLeft
                     $this.getPrev();
                        $this.showBars();
                        $this.setTimeout();
                  } else if (distance <= -swipMinDistance) {

                     // swipeRight
                     $this.getNext();
                        $this.showBars();
                        $this.setTimeout();                    

                  } else {
                     // tap
                     if (!bars.hasClass('visible-bars')) {
                        $this.showBars();
                        $this.setTimeout();
                     } else {
                        $this.clearTimeout();
                        $this.hideBars();
                     }

                  }
                  
                  $("body").on("click", "#vobox-overlay", function(){
                     $("body").trigger("touchend.vobox");
                  });

                  $('.touching').unbind('touchmove.vobox').removeClass('touching');

               });

            }
         },
         setTimeout: function() {
            if (plugin.settings.hideBarsDelay > 0) {
               var $this = this;
               $this.clearTimeout();
               $this.timeout = window.setTimeout(function() {
                  $this.hideBars()
               },
                       plugin.settings.hideBarsDelay
                       );
            }
         },
         clearTimeout: function() {
            window.clearTimeout(this.timeout);
            this.timeout = null;
         },
         showBars: function() {
            var bars = $('#vobox-caption, #vobox-action');
            if (this.doCssTrans()) {
               bars.addClass('visible-bars');
            } else {
               $('#vobox-caption').animate({top: 0}, 500);
               $('#vobox-action').animate({bottom: 0}, 500);
               setTimeout(function() {
                  bars.addClass('visible-bars');
               }, 1000);
            }
         },
         hideBars: function() {
            var bars = $('#vobox-caption, #vobox-action');
            if (this.doCssTrans()) {
               bars.removeClass('visible-bars');
            } else {
               $('#vobox-caption').animate({top: '-50px'}, 500);
               $('#vobox-action').animate({bottom: '-50px'}, 500);
               setTimeout(function() {
                  bars.removeClass('visible-bars');
               }, 1000);
            }
         },
         animBars: function() {
            var $this = this;
            var bars = $('#vobox-caption, #vobox-action');

            bars.addClass('visible-bars');
            $this.setTimeout();

            $('#vobox-slider').click(function(e) {
               if (!bars.hasClass('visible-bars')) {
                  $this.showBars();
                  $this.setTimeout();
               }
            });

            $('#vobox-action').hover(function() {
               $this.showBars();
               bars.addClass('force-visible-bars');
               $this.clearTimeout();

            }, function() {
               bars.removeClass('force-visible-bars');
               $this.setTimeout();

            });
         },
         keyboard: function() {
            var $this = this;
            $(window).bind('keyup', function(e) {
               e.preventDefault();
               e.stopPropagation();
               if (e.keyCode == 37) {
                  $this.getPrev();
               }
               else if (e.keyCode == 39) {
                  $this.getNext();
               }
               else if (e.keyCode == 27) {
                  $this.closeSlide();
               }
            });
         },
         actions: function() {
            var $this = this;

            if (elements.length < 2) {
               $('#vobox-prev, #vobox-next').hide();
            } else {
               $('#vobox-prev').bind('click touchend', function(e) {
                  e.preventDefault();
                  e.stopPropagation();
                  $this.getPrev();
                  $this.setTimeout();
               });

               $('#vobox-next').bind('click touchend', function(e) {
                  e.preventDefault();
                  e.stopPropagation();
                  $this.getNext();
                  $this.setTimeout();
               });
            }

            $('#vobox-close').bind('click touchend', function(e) {
                  e.preventDefault();
                  e.stopPropagation();               
               $this.closeSlide();
            });
         },
         setSlide: function(index, isFirst) {
            isFirst = isFirst || false;

            var slider = $('#vobox-slider');

            if (this.doCssTrans()) {
               slider.css({left: (-index * 100) + '%'});
            } else {
               slider.animate({left: (-index * 100) + '%'});
            }

            $('#vobox-slider .slide').removeClass('current');
            $('#vobox-slider .slide').eq(index).addClass('current');
            this.setTitle(index);

            if (isFirst) {
               slider.fadeIn();
            }

            $('#vobox-prev, #vobox-next').removeClass('disabled');
            if (index == 0) {
               $('#vobox-prev').addClass('disabled');
            } else if (index == elements.length - 1) {
               $('#vobox-next').addClass('disabled');
            }
         },
         openSlide: function(index) {
            $('html').addClass('vobox');
            $(window).trigger('resize'); // fix scroll bar visibility on desktop
            this.setSlide(index, true);
         },
         preloadMedia: function(index) {
            var $this = this, src = null, type;

            if (elements[index] !== undefined){
               src = elements[index].href;
            }
            $this.openMedia(index);
            
         },
         openMedia: function(index) {
            var $this = this, src = null, type = null;

            if (elements[index] !== undefined) {
               src = elements[index].href;
               type = elements[index].type;
            }

            if (index < 0 || index >= elements.length) {
               return false;
            }

            switch (type) {
               case 'audio':
                  $this.loadMedia(type, src, index);
                  break;
               case 'video':
                  $this.loadMedia(type, src, index);
                  break;
               default:
                  $this.loadImage(type, src, index);
                  break;
            }

         },
         setTitle: function(index, isFirst) {
            var $this = this,
                    title = null;

            $('#vobox-title').empty();

            if (elements[index] !== undefined) {
               $item = $elem.eq(index);
               $parent = $item.closest("li");
               title = $parent.attr("title") || $item.data("title");
               id = $item.data("id")//$parent.find('input[type="checkbox"]:first').attr("id");
               $this.setShare(index, isFirst, id, title);
               $this.setOptions(index, isFirst);
            }

            if (title) {
               $('#vobox-title').append(title);
            }
         },
         setShare: function(index, isFirst, id, title) {
            var sharebtn = null;

            $('#vobox-caption').find("div.actions").empty();
            var $btn = $('<a href="javascript:;" data-title="Share <span>'+title+'</span>" class="btn ajax" data-action="fi_share" data-item="'+id+'" data-ajax="loadShareBox"><i class="icon-white icon-share"></i><span> Share</span></a>');
                    
            
            $('#vobox-caption').find("div.actions").append($btn);
         },
         setOptions: function(index, isFirst) {
            var sharebtn = null;

            $('#vobox-options').empty();
            var $el = $elem.eq(index);
                file = $el.data("file");
            var $downBtn = $('<a href="javascript:;" data-title="" class="btn octet" data-action="fi_download" data-file="'+file+'" data-item="" data-ajax="loadShareBox"><i class="icon-white icon-cloud-download"></i><span> Download</span></a>'),
                $pauseBtn = $('<a href="javascript:;" class="btn"><i class="icon-pause"></i><a>');;
            $('#vobox-caption').find("div.actions").append($downBtn);
            if($("body").data("playing")){
               $('#vobox-caption').find("div.actions").prepend($pauseBtn); 
            }
            
         },
                 
         isVideo: function(type) {

            if (type) {
               if (type == 'video') {
                  return true;
               }
               return false;
            }

         },
         isDoc: function(src) { // feature

            if (type) {
               if (type == 'document') {
                  return true;
               }
               return false;
            }

         },
                 
         callMediaPlayer: function($item, index, type) {
            
            $item.find(type)
            .mediaelementplayer({
               defaultVideoWidth: ((isMob() || isTablet()) ? DevWidth - 50 : '480'),
               defaultVideoHeight: ((isMob() || isTablet()) ? DevHeight - 140 : 270),
               videoWidth: -1,
               videoHeight: -1,
               audioWidth: ((isMob() || isTablet()) ? DevWidth - 50 : '480'),
               audioHeight: 30,
               startVolume: 0.8,
               plugins: ['flash', 'silverlight'],
               pluginPath: ASSURI + 'player/',
               flashName: 'player.swf',
               silverlightName: 'player.xap',
               features: ['playpause', 'progress', 'current', 'duration', 'tracks', 'volume', 'fullscreen'],
               alwaysShowControls: true,
               enableAutosize: true,
               pluginWidth: -1,
               pluginHeight: -1,
               enableKeyboard: true,
               timerRate: 250,
               success: function(media) {
                  media.addEventListener('play', function() {
                     elements[index].playing = true;
                     currPlay = {'index' : index , 'type' : type};
                  }, true);
                  
                  media.addEventListener('pause', function() {
                     elements[index].playing = false;
                     currPlay = {'index' : null , 'type' : null};
                  }, true);                    

               },
               error : function(){
                   console.log("error");
               }
            });
            
            if ($item.find(".download").length > 0) {
               $item.find(".download").prepend($('<div style="position: absolute; bottom: 10px; left: 0; right: 0; font-size: 10px; color: white; text-align: center">Your browser does not support to play this kind of media.</div>'));
               $item.find(".download").find("a").on('click touchend', function(e) {
                  e.preventDefault();
                  e.stopPropagation();
                  return createTempIframe('?octet&file=' + $elem.eq(index).data("file"));
                  return false;
               }).attr("href", '#').prepend('<i class="icon-white icon-cloud-download icon-3x" /> ');
            }
            
            return true;
         },                
                 
                 
         loadMedia: function(type, src , index) {
            var callMed = false,
                $item = $('#vobox-slider .slide').eq(index);
        
            if(!elements[index].loaded){
            var $media = $((type == 'video' ? '<video width="' + (DevWidth*0.8) + '" height="'+(DevHeight - 120)+'" id="player_'+index+'" src="' + src + '" preload="false">' : '<audio id="player" src="' + src + '" preload="false">') + '<object width="566" height="320" type="application/x-shockwave-flash" data="' + ASSURI + 'player/player.swf">' +
                    '<param name="movie" value="' + ASSURI + 'player/player.swf" />' +
                    '<param name="flashvars" value="controls=true&file=' + src + '" /></object>' +
                    (type == 'video' ? '</video>' : '</audio>')
                    );
                       
            $item.append($media);
            
            var callMed = this.callMediaPlayer($item, index ,type);
            if(callMed){
               this.removeInd(index);
               elements[index].loaded = true;
            }
            }else{
               return elements[index];
            }
            return true;
         },
                 
         loadImage: function(type, src, index) {
            var $this = this;
            
            if (type == 'image') {
               if(!elements[index].loaded){
               var img = $('<img>').on('load', function() {
                  $('#vobox-slider .slide').eq(index).html(this);
               });

               img.attr('src', src).on('load',function(){
                  $this.removeInd(index);
                  elements[index].loaded = true;
               });
               }else{
                  return elements[index];
               }
            }
         },
                 
         stopMedia : function(){
            if(currPlay.index !== null){
               var $medObj = $('#vobox-slider .slide').eq(currPlay.index).find(currPlay.type),
                   medSrc  = $medObj.prop("src");
               $medObj[0].player.pause();
            }
            currPlay = {'index' : null, 'type' : null};
            return true;
         },
                 
         getNext: function() {
            var $this = this;
            index = $('#vobox-slider .slide').index($('#vobox-slider .slide.current'));
            if($this.stopMedia()){
            if (index + 1 < elements.length) {
               index++;
               $this.setSlide(index);
               $this.preloadMedia(index + 1);
            }
            else {

               $('#vobox-slider').addClass('rightSpring');
               setTimeout(function() {
                  $('#vobox-slider').removeClass('rightSpring');
               }, 500);
            }
            }
            
         },
         getPrev: function() {
            index = $('#vobox-slider .slide').index($('#vobox-slider .slide.current'));
            if(this.stopMedia()){
            if (index > 0) {
               index--;
               this.setSlide(index);
               this.preloadMedia(index - 1);
            }
            else {

               $('#vobox-slider').addClass('leftSpring');
               setTimeout(function() {
                  $('#vobox-slider').removeClass('leftSpring');
               }, 500);
            }
            }
         },
         closeSlide: function() {
            $('html').removeClass('vobox');
            $(window).trigger('resize');
            this.destroy();
//				$('html').removeClass('vobox');
//                        index = $('#vobox-slider .slide').index($('#vobox-slider .slide.current'));
//                        var to = parseInt($elem.eq(index).offset().top);
//                        
//				$(window).trigger('resize');
//				this.destroy();
//                        window.scrollTo(0,to);                        

         },
         removeInd : function(index){
            $('#vobox-slider .slide').eq(index).css("background-image","none");
         },
         destroy: function() {
            $(window).unbind('keyup');
            $('body').unbind('touchstart.vobox');
            $('body').unbind('touchmove.vobox');
            $('body').unbind('touchend.vobox');
            $('#vobox-slider').unbind();
            $('#vobox-overlay').remove();
            if (!$.isArray(elem)) {
               elem.removeData('_vobox');
            }
            if (this.target)
               this.target.trigger('vobox-destroy');
            $.vobox.isOpen = false;
            if (plugin.settings.afterClose)
               plugin.settings.afterClose();
         }

      };

      plugin.init();

   };

   $.fn.vobox = function(options) {
      if (!$.data(this, "_vobox")) {
         var vobox = new $.vobox(this, options);
         this.data('_vobox', vobox);
      }
      return this.data('_vobox');
   }

}(window, document, jQuery));