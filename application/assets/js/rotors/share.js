;(function ( $, window, undefined ) {
    
    var document = window.document;

    $.fn.share = function(method) {
 
        var methods = {

            init : function(options) {
                this.share.settings = $.extend({}, this.share.defaults, options);
                var settings = this.share.settings,
                    networks = this.share.settings.networks,
                    cls = this.share.settings.cls == 'none' ? '' : 'btn'
                    theme = this.share.settings.theme || 'normal',
                    margin = this.share.settings.margin,
                    pageTitle = this.share.settings.title||$(document).attr('title'),
                    pageLink = this.share.settings.link||$(location).attr('href'),
                    pageUrl = this.share.settings.url||$(location).attr('href'),
                    pageDesc = this.share.settings.desc||$(document).find('meta[name="description"]').eq(0).attr("content");
                
                // each instance of this plugin
                return this.each(function(index) {
                    var $element = $(this),
                        id=$element.attr("id"),
                        l=encodeURIComponent(pageLink),
                        u=encodeURIComponent(pageUrl),
                        t=encodeURIComponent(pageTitle),
                        d=pageDesc.substring(0,250),
                        href;
                        
                    // append HTML for each network button
                    for (var item in networks) {
                        item = networks[item];
                        href = helpers.networkDefs[item].url;
                        var pop  = (href != 'mailto:?subject=|t|') ? " pop" : "";
                        var title  = (href === 'mailto:?subject=|t|') ? "Send via "+item : "Share on "+item;
                        var icon = (href === 'mailto:?subject=|t|') ? 'envelope-alt' : (item == 'googleplus' ? 'google-plus' : item)
                        href = href.replace('|l|',l).replace('|u|',u).replace('|d|',d)
                                   .replace('|140|',t.substring(0,130));
                        if(href === 'mailto:?subject=|t|'){
                            href = href.replace('|t|',t+'&body='+l);
                        }
                        
                        
                        
                    
                        $('<a href="'+href+'" title="'+title+'" class="'+cls+' social '+pop+'"><i class="icon-'+icon+'"></i></a>').appendTo($element);
                    }
                    
                    if(theme == 'circle'){
                       
                    var a = 92;
                    var e = 220;
                    var t = 60;
                    var r = $element.find("a.btn").length;
                    var i = e + (r - 1) * t;
                    var s = 0;
                    var o = 154;
                    var f = 154;
                    var pos = $element.find("div.btn").position();
                    var l = $element.find("div.btn").width()-pos.left;
                    var c = $element.find("div.btn").height()-pos.top;
                    var h = (o - l) / 2;
                    var p = (f - c) / 2;
                    
                    var d = 75;
                        var v = 180;
                        var m = v / r;
                        var g = d + m / 2;
                        $element.find("a.btn").each(function () {
                            var n = g / 90 * Math.PI;
                            var r = h + a * Math.cos(n);
                            var i = p + a * Math.sin(n);
                            $(this).css({
                                display: "block",
                                left: h + "px",
                                top: p + "px"
                            }).stop().delay(t * s).animate({
                                left: r + "px",
                                top: i + "px"
                            }, e);
                            g += m;
                            s++
                        });
                    $element.find("div.btn").on('click', function(){
                        var r = $element.find("a.btn").length;
                        var v = 180;
                        var m = v / r;
                        var g = d + m / 2;
                        $element.find("a.btn").each(function (index) {
                            var $this = $(this);
                            var pos = (index < r-1) ? $this.next('a.btn').position() : $element.find("a.btn").eq(0).position();
                            $this.stop().animate({
                                left: pos.left + "px",
                                top: pos.top + "px"
                            }, 160);                           
                        });                       
                    });
                    
                    }else{
                       //regular sharer
                                                                     
                    }   
                                   
                    
                    // customize css
                    $("#"+id+".share-"+theme).css('margin',margin);
                    
                    // bind click
                    $('.pop').click(function(){
                        window.open($(this).attr('href'),'t','toolbar=0,resizable=1,status=0,width=640,height=528');
                        return false;
                    });
                
                });
            
            }        
        }

        var helpers = {
            networkDefs: {
                facebook:{url:'http://www.facebook.com/share.php?u=|l|'},
                twitter:{url:'https://twitter.com/share?url=|l|&text=|140|'},
                linkedin:{url:'http://www.linkedin.com/shareArticle?mini=true&url=|l|&title=|t|&summary=|d|&source=in1.com'},
                in1:{url:'http://www.in1.com/cast?u=|l|',w:'490',h:'529'},
                tumblr:{url:'http://www.tumblr.com/share?v=3&u=|u|'},
                digg:{url:'http://digg.com/submit?url=|l|&title=|t|'},
                googleplus:{url:'https://plusone.google.com/_/+1/confirm?hl=en&url=|l|'},
                reddit:{url:'http://reddit.com/submit?url=|l|'},
                pinterest:{url:'http://pinterest.com/pin/create/button/?url=|u|&media=&description=|d|'},
                posterous:{url:'http://posterous.com/share?linkto=|l|&title=|t|'},
                stumbleupon:{url:'http://www.stumbleupon.com/submit?url=|l|&title=|t|'},
                email:{url:'mailto:?subject=|t|'}
            }
        }
     
        if (methods[method]) {
            return methods[method].apply(this, Array.prototype.slice.call(arguments, 1));
        } else if (typeof method === 'object' || !method) {
            return methods.init.apply(this, arguments);
        } else {
            $.error( 'Method "' +  method + '" does not exist in social plugin');
        }

    }

    $.fn.share.defaults = {
        networks: ['facebook','twitter','linkedin'],
        theme: 'icon',
        autoShow: true,
        margin: '3px'
    }

    $.fn.share.settings = {}
        
})(jQuery, window);
