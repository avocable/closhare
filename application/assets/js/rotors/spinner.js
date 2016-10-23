(function($) {
   self;
   $.fn.spin = function(options) {

      var defaults = {
         top: 'auto',
         left: 'auto',
         color: '#000',
         background: 'tr',
         boxClass: 'spin',
         boxSize: 'big',
         zIndex: 99999,
         position: 'absolute',
         width: 64,
         main: false,
         direction: 'ccw',
         exc : false
      };
      
      return this.each(function(){
         var $target = $(this);
      if ('object' == typeof options){
         var o = $.extend({}, defaults, options);
      }
      else{
         var o = defaults;
      }
      
      o.width = (o.boxSize == "small" ? 24 : (o.boxSize == 'xsmall' ? 18 : o.width))
      
      if (options == false) {
         $target.children(".spin").each(function(){
            $(this).removeClass("on");
            if(!$(this).hasClass("frou")){
               
            $(this).slideUp(200, function() {
               $(this).remove();
            });               
               
            }else{
               
               $(this).fadeOut("fast", function() {
                  $(this).remove();
               });   
               
            }
         });
         return;
      }
      if($target.find(" > .spin").length > 0){
         $target.find(" > .spin").remove();
      }
         
      
      var $spin = $('<div/>').addClass(o.boxClass+' '+o.boxSize).css({position: o.position, zIndex: o.zIndex});
      
      if (o.main == false) {
         $spin.addClass("frou");
         if(o.background == 'tr'){
            $spin.addClass("tr");
         }
      }else{
         $spin.css("position", "fixed");
      }
      if(o.direction != 'ccw'){
         $spin.addClass("cw");
      }
      
      if(o.exc){
         $spin.addClass(o.exc);
         
      } 
      
      _showspin = function() {
         if (o.main){
            $target.prepend($spin.slideDown(300, function() {
               $spin.addClass("on");
            }));
         }else{
            $target.prepend($spin.fadeIn(100, function() {
               $spin.addClass("on");
            }));
         }
      }
     
      
         _pos = function() {
            var toff = $target.offset(),
                    tpd = $target.cssGet(["padding-top", "padding-left"]),
                    tpp = $target.parents().first().cssGet(["position"]);
                    tp = parseInt(tpd["padding-top"]),
                    lp = parseInt(tpd["padding-left"]),
                    tw = $target.width(),
                    th = $target.height(),
                    ew = o.width+8,
                    tpo = tpp['position'];
            
            $spin.css({
               'top': (o.top == 'auto' ? (Math.floor(th - ew) / 2) : ((o.main ? -1 : o.top))),
               'left': (o.left == 'auto' ? ((tw - ew)/2) : (toff.left + o.left))
            });
            if(tpo === undefined || tpo == 'static'){
            $spin.css({
               'top': (o.top == 'auto' ? (((th - ew) / 2))  : ((o.main ? -1 : o.top))),
               'left': (o.left == 'auto' ? ((toff.left + (tw - ew) / 2)) : (toff.left + o.left))
            });
            }  
         }
         _pos.call();
         _showspin.call();
        
      });
      $(window).resize(_pos);
   };
})(jQuery);