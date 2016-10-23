$(function () {
    if($("body").hasClass("dn")){
       $("body").removeClass("dn");
       $("html").removeClass("off");
    }
    $('.btn').button();
    
    var tabs = $('.tabbable'),
    tab_a_selector = '.tabnav a';
    $norequestArr = new Array("login","register","recover");
    tabs.find(tab_a_selector).on('click', function(e) {

        e.preventDefault();
        $that = $(this);
        $that.tab('show');
        
        var state = {},
        id = "page",
        idx = $that.attr('href').replace(/^#/, '');
        // Set the state!
        state[ id ] = idx;
        $.bbq.pushState(state,2);
        $('#footer').trigger('click');
    });

    $(window).bind('hashchange', function(e) {
        
        //login register recover pages
        $('.tabbable').each(function() {
            
            var $this = $(this);
            if(($.inArray($.bbq.getState("page"), $norequestArr) === -1)){
            var idx = "login";
            }else{
            var idx = $.bbq.getState("page", false) || $this.find('li.active a').attr('href').replace(/^#/, '');
            }
            $el = $this.find('.tabnav a[href="#' + idx + '"]');
            
            $el.trigger('click');
        });
        $('.button-loading').button("reset");
    });
    $(window).trigger('hashchange');    
});

$(function () {
        
    $(".form-horizontal input").jqBootstrapValidation(
            {
                preventSubmit: true,
                submitSuccess: function ($form, event) {
                     $(".button-loading").button("loading");
                },
                        
                submitError: function($form, event, errors) {
                    $form.find("button").button('reset');
                },
                filter: function() {
                    return $(this).is(":visible");
                }
            }
    );
});
$(function (){
   $('[data-toggle="modal"]').click(function(e) {
      e.preventDefault();
      var $this = $(this), data = $this.data("action");
         $this.button("loading");
         $.ajaxQueue({
            url: '',
            type: 'GET',
            data: {content: data},
            dataType: 'json',
            success: function(response) {
              $this.button("reset");
              $('<div class="modal hide fade"></div>').append(response.html).modal();
            }
         });

     return false;
   });
    
    
    Modernizr.load({
        test: Modernizr.input.placeholder,
        nope: ASSURI+'js/fallback/placeholder.js'      
    });
   
    
    
});