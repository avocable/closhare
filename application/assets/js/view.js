/**
 * view.js
 */
$(document).ready(function() {

   if ($("html").hasClass("off")) {
      $("html").removeClass("off");
   }
   container = $('.share');
   var msnry, $imgs = $("li.item img"), imgArr = new Array();
   typeClasses = ["image", "audio", "video", "document", "other"];

   msnry = container.masonry({
      itemSelector: '.item',
      'gutter': 16
   });

   pageLoader.attachTo = "#page";
   pageLoader.selectorPreload = "#wrap";
   pageLoader.init();

   $('video,audio').each(function() {
      var $this = $(this),
              cwid = $this.width(),
              wliwid = $this.closest("li").width();
      if (cwid > wliwid) {
         $this.width(wliwid - 10);
      }
      $this.mediaelementplayer({
         defaultVideoWidth: ((cwid > wliwid) ? wliwid - 10 : false),
         defaultVideoHeight: 270,
         videoWidth: -1,
         videoHeight: -1,
         audioWidth: $this.closest("li").width() - 20,
         audioHeight: 30,
         startVolume: 0.6,
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
         success: function(player, node) {
         }
      });
   });

   var timeoutId;
   container.find("li.item").hover(function(e) {
      e.preventDefault();
      var $this = $(this),
              $shrit = $this.find("div.shareit");

      if (!$this.data("shareit")) {

         if (!timeoutId) {

            timeoutId = window.setTimeout(function() {

               timeoutId = null;

               $shrit.fadeIn("fast").spin({boxSize: 'xsmall', top: 12});

               $.ajax({
                  url: '/',
                  type: 'POST',
                  data: {shareit: $this.data("id"), uihash: $("#share").data("hash")},
                  dataType: 'json',
                  cache: true,
                  success: function(response) {
                     $this.find("div.shareit").spin(false).share({
                        networks: shareOpts,
                        link: response.link,
                        url: response.url,
                        title: response.title,
                        desc: response.description,
                        theme: "normal",
                     });
                  }
               });
               $this.data("shareit", "ok");
            }, 800);
         }
      } else {
         $shrit.fadeIn("fast");
      }
   },
           function() {
              var $this = $(this);
              if (timeoutId) {
                 window.clearTimeout(timeoutId);
                 timeoutId = null;
              }
              else {
                 $this.find("div.shareit").fadeOut("fast");
              }
           });


   imagesLoaded(container, runAfterComplete);

   if ($(".download").length > 0) {

      $(".download").each(function() {

         var $el = $(this),
                 $this = $el.find("a"),
                 link = $this.prop('href');
         //$el.width($el.closest(".thumbnail").width());
         if ($el.parents("div:first").hasClass("mejs-mediaelement")) {
            $el.prepend($('<div style="position: absolute; bottom: 0; left: 0; right: 0; font-size: 10px; color: white; text-align: center">Your browser does not support to play this kind of media.</div>'))
         }

         $this.attr("href", 'javascript:;').prepend($('<i class="icon-download icon-5x" />'));
         $this.on('click', function() {
            downloader('/?octet&file=' + $(".share").eq(0).data("pack") + '__' + link.split("/")[4]);
            return false;
         });
      });
   }

   $(".vibtn").on('click', function(e) {
      e.preventDefault();
      var $this = $(this),
              $el = $this.find("img:first"),
              $li = $this.closest("li.item");

      if ($li.data("back") == 'span12')
         return;

      $el.toggleClass("active", 400);

      if ($li.hasClass($li.data("back"))) {
         $li.css("z-index", "1029").switchClass($li.data("back"), "span12", 400, runAfterComplete);
      } else {
         $li.css("z-index", "inherit").switchClass("span12", $li.data("back"), 400, runAfterComplete);
      }

      return false;
   });

   $("div.dir_ex").each(function() {
      var $this = $(this),
              $sfo = $this.find("div.sfo");
      $sfo.share({
         networks: shareOpts,
         url: $this.data("share-url"),
         title: $this.data("share-title"),
         desc: $this.data("share-description"),
         theme: "normal",
         cls: $this.data("share-cls")
      });
   });
   
   
    $("a.fullscreen").on('click', function(){
        var that = $(this),
        $target = $('li[data-id="' + that.data("target") + '"]')
        if(!that.hasClass("on")){
            fullscreen($target[0]);
            that.addClass("on");
        }else{
            fullscreen(false);
            that.removeClass("on");
        }
    });

});

function runAfterComplete() {
   var $el = $(this).length > 0 ? $(this) : false;

   container.masonry();
   if ($el.is("li"))
      setTimeout(function() {
         var top = parseInt($el.offset().top) - 60;
         $("html, body").stop().animate({scrollTop: top});
      }, 1000);
}

function downloader(source) {
   console.log(source)
   var iframe = $('<iframe>', {
      "src": source,
      "class": 'dnone',
      "id": 'octetFrame'
   }).appendTo("body").one('load', function() {
      setTimeout(function() {
         $("#octetFrame").remove();
      }, 1000);
   });

}

function removeIcons($item) {
   $.each(typeClasses, function(index, name) {
      $item.cloesest("li").removeClass(name);
   });
}