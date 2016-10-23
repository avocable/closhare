/**
 * list
 * @package      CloShare v1.00
 */
function closeSbar(){
   if(isMob() && !isTablet()){
      var result = $.sbar('close', 'sbar');
      $(".view_port .explain").toggleClass("opened", result);
   }
}

function highlightSBt(){
   $("#mSideOpener").addClass("highlight");
}

$(function() {
    var offset = 220,
    duration = 200,
    $el = $('#goUp');
    $(window).on('scroll', function() {
       var st = $(this).scrollTop();
        if (st > offset) {
            $el.fadeIn(duration);
        }
        else {
            $el.fadeOut(duration);
        }     
    });
    
    $el.on('click', function(event) {
        event.preventDefault();
        $('html, body').animate({scrollTop: 0}, duration);
        return false;
    });
});