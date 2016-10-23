/**
 * content
 * @package      CloShare
*/

(function($) {
  
  $.fn.scrload = function(container, threshold) {
 
    var $w = $(container) || $(window),
        th = threshold || 0,
        retina = window.devicePixelRatio > 1,
        attrib = retina? "data-src-retina" : "data-src",
        images = this,
        loaded = [],
        inview,
        source;

    this.one("scrload", function() {
      var source = this.getAttribute(attrib),
          source = source || this.getAttribute("data-src");
      if (source){
          var img = new Image();
          var $this = $(this);
          $(img).load(function () {
              $this.removeAttr(attrib).attr("src", source);
              $this.show("fast");
              
          }).attr("src", source);

      }
    });

    function scrload() {
      inview = images.filter(function() {
        var $e = $(this),
            wt = $w.scrollTop(),
            wb = wt + $w.height(),
            et = $e.offset(),
            eb = et + $e.outerHeight();

          return (eb >= wt - parseInt(th)) || (et.top <= wb + parseInt(th));
      });
      
      
      (function showi(i) {
          if (i >= inview.length) return i;
          $(inview[i]).trigger("scrload");
          setTimeout(function(){
              showi(i+1);   
          },50);
      })(0);
      
  }

        $w.scroll( $.debounce( 250, scrload ) );
        $(window).resize( $.debounce( 250, scrload ) );
        scrload();
        
        return this;        

  };
  //fire first load of images in the viewPort
  $viewPort.find("img.tn").scrload('.viewbox.active');
  
})(window.jQuery);