/**
 * script
 * @package      CloShare
 */

//Custom touches
//Dom please!
$(function() {
   //General
   $("body").scrollTop(0);
   $('.btn').button();
   $('.button-loading').click(function() {
      $(this).button('loading');
   });
   Modernizr.load({
      test: Modernizr.input.placeholder,
      nope: ASSURI + 'js/fallback/placeholder.js'
   });    

   $html = $spintarget = $("html");
   $viewPort = $('#viewport');
   $sidebar = $("#sidebar");
   $filesSideBarTbar = $("#filesdropdown");
   $norequestArr = new Array("login", "register", "recover");
   scrloaded = new Array("noscr");
   selectedItems = new Array();
   
   $( document ).ajaxStart(function() {
      if ($("body").hasClass("dn")) {
         $("body").removeClass("dn");
         $("html").removeClass("off");
      }     
   });

   $( document ).ajaxSend(function(event, jqXHR, options) {
      $spintarget = $html;
      if (options.noind  != true){
         $spintarget.spin({main: true, top: 0});
      }
   });
   $(document).ajaxComplete(function(event, xhr, settings) {
      if (settings.dataType == 'json') {
         var data = $.parseJSON(xhr.responseText) || 0;
         if (data && (data.auth == 0 || data.auth == 2)) {
            $(".modal").modal("hide");
            $spintarget.blockit({'message': data.message, blockMsgClass: 'alert alert-error'});
            setTimeout(function() {
               $spintarget.spin({main: true, top: 0, exc: 'error'});
               $("#footer").blockit();
               $("#countrd").countdown(4, 's', function() {
                  if (navigator.userAgent.indexOf('Safari') === -1 && navigator.userAgent.indexOf('Chrome') !== -1) {
                     window.location = '/';
                  } else {
                     location.reload(true);
                  }
               });
            }, 150);
         }
      } else {
         $spintarget.spin(false);
      }
   });
   
});
//start for view page
$(document).ready(function() {
   viewport(); //viewport init

   //while window being resized.
   $(window).resize($.throttle(100, function() {
      viewport.call();
   })
           );

   $('.signoutb').on("click", function() {
      window.location.href = "?logout=true";
   });

   $('a[data-toggle="tab"]').on('shown', function(e) {
      var $el = $(this);
      if ($el.data('callback') != "") {
         callbackCall($el);
      }
      if ($el.data('persist') === "ok") {
         $sidebar.unblock();
         sidebarToggle(".upload:not(.dyn)", ".files");
      } else {
         if (!isMob() && !isTablet())
            $html.blockit(); //we need for now!
      }

   });
});


$(function() {

   $viewPort.data('procs', {
      cache: {
         '': $(document).find('li.active a')
      }
   });
   var clickHandler = ('ontouchstart' in document.documentElement ? "touchstart" : "click");

   $("body").on("click", 'a.rpsve', function(e) {

      //e.preventDefault();
      $this = $(this);

      folderInteract($this);
      return false;
   });

   if (clickHandler == 'touchstart') {
      $(window).on("touchmove", function(e) {
         $(this).find("a.rpsve").off("touchstart");
      });
   }

   function folderInteract($this) {

      var state = {},
              id = "list",
              idx = $this.attr('href').replace(/^#/, '');

      state[ id ] = idx;
      $.bbq.pushState(state);
   }

   $(document).on('click', 'a.pg', function(e) {

      e.preventDefault();
      $this = $(this);

      $this.tab('show');

      var state = {},
              id = "page",
              idx = $this.attr('href').replace(/^#/, '');

      state[ id ] = idx;
      $.bbq.pushState(state, 2);

      return false;
   });
   
//   $(document).on('click', 'a.nrm', function(e) {
//
//      e.preventDefault();
//      $this = $(this);
//
//      $this.tab('show');
////      var $pEl = $this.closest('.tabs'),
////      idx = $this.attr('href').replace(/^#/, ''),
////      pageOfParent = $pEl.data("page"),
////      url = window.location.href,
////      state = {edit : idx},
////      merged = jQuery.param.fragment(url, state), 
////      deparamed = jQuery.deparam.fragment(merged);
////      console.log(deparamed);
////      
////      $.bbq.pushState(deparamed ,0);
//      
//      return;
//   });   

   $(window).bind('hashchange', function(e) {
      $('[data-toggle="dropdown"]').parent().removeClass('open');
      $("li.active").each(function() {
         $(this).removeClass("active");
      });

      var winhash = location.hash || 0;

      //check if user logged & window has an hash
//      if (!winhash || $.inArray($.bbq.getState("page"), $norequestArr) > -1 || $.inArray($.bbq.getState("page"), freepArr) == -1) {
//         $.bbq.pushState({'page': 'list'}, 2);
//      }
      var page = $.bbq.getState("page") || 0;

      var list = $.bbq.getState("list") || 0;

      if (list) {
         $element = $(document).find('a[href="#' + list + '"]');
         target = list;
      } else {
         $element = $(document).find('a[href="#' + page + '"]');
         target = page;
      }

      if ((!page && !location.hash) || $(document).find('a[href="#' + page + '"]').length == 0) {
         var ghash = location.hash;

         var $fake = ($(document).find('a[href="' + ghash + '"]').length > 0 ? $(document).find('a[href="' + ghash + '"]').eq(0) : ((ghash.indexOf(',') != -1) ? $('<a href="#page=list&list=' + ghash.replace(/^#/, '') + '"></a>') : 0)),
                 $element = $fake || $("#listpbtn"),
                 page = $element.attr('href'),
                 frag = jQuery.deparam.fragment(page);

         if (!frag.page || frag.page === undefined) {
            delete frag[page.replace(/^#/, '')];
            frag.page = page.replace(/^#/, '');
         }
         $.bbq.pushState(frag, 2);
         return;
      }

      //store current page for after use
      currPage = $.bbq.getState("page", true) || 0;

      //store current folder for after use
      currDir = $.bbq.getState("list", true) || 0;

      currDirID = (currDir ? currDir.split(",")[1] : 1);


      if (currDir) {
         $element = $(document).find('a[href="#list"]')
      }
      
      makebreadcrumb(false);

      if ($element.data('persist') != "ok")
         handleLinks(target, $element, false);
      else {
         handleLinks(target, $element, true);

      }

   });
   $(window).trigger('hashchange');
   setMeters();
});

$(function() {

   $(document).bind('drop dragover', function(e) {
      e.preventDefault();
      return;
   });

   $("#uploadpbtn").bind('drop dragover', function(e) {
      $(this).trigger("click");
   });
});
//functions

function doPageLoad(idx, $el, msg) {
   var msg = $el.data('load-txt') || $("#list").data("load-txt"),
           $target = $('#' + currPage);


   if ($el.data('persist') != "ok") {
      $.ajaxQueue({
         url: '/?' + jQuery.param.fragment(window.location.hash).replace(/^page=/, '') + (currPage == 'upload' ? ('&' + getBrowserSupportParamsUpload()) : ''),
         type: 'GET',
         dataType: 'json',
         beforeSend: function() {
            $spintarget.blockit({message: msg});
         },
         success: function(data) {

            if ($el.data('persist') == "yes") {
               $el.data("persist", "ok");
            }

            var html = data.html || data;
            $target.find(".inner:first").html(html);

            //set events
            var event = jQuery.Event('click');
            event.target = $(document).find('a.rpsve')[0];

            if (data.style) {
               $.each(data.style, function(index, css) {
                  loadstyle(css, function() {
                     if (index + 1 == data.style.length && css.back !== undefined) {
                        if (css.back === "fn") {
                           eval(css.callback);
                        } else {
                           window[css.callback]();
                        }
                     }
                  });
               });
            }

            if (data.script) {
               $.each(data.script, function(index, value) {
                  loadscript(value, function() {
                     if (index + 1 == data.script.length) {
                        setTimeout(function(){
                            $spintarget.spin(false).unblock();
                        },450);
                        
                        if ($el.data('callback') !== "") {
                           callbackCall($el);
                        }
                        if (data.acall) {
                           executer(data);
                        }
                     }
                  });

               });

            } else {

               $spintarget.unblock();
            }
         }
      });
   }
}
function executer(data) {
   $.each(data.acall, function(index, obj) {
      if ('object' == typeof obj || 'string' == typeof obj) {
         if (obj.back === "fn") {
            setTimeout(function() {
               $.globalEval(obj.run);
            }, 150);
         } else {
            window[obj.back](data.acall[index]);
         }
      }
   });
}
function handleTabs(idx, $el, cached) {

   $el.tab('show');

   if (!cached) {
      doPageLoad(idx, $el);
   } else {
      $spintarget.unblock();
   }
   return;
}

function handleLinks(idx, $el, cached) {

   if ($el.attr("data-toggle") == "tab") {

      handleTabs(idx, $el, cached);

   } else {
      var temp = idx.split("|");
      var slug = temp[0],
              dir = temp[1],
              parent = temp[2];
      doPageLoad('list=' + slug + '&dir=' + dir + '&parent=' + parent, $el);
   }
   document.body.scrollTop = 0;
   ("closeSbar" in this) && closeSbar && closeSbar();
   return false;
}

function viewport() {
   //if($sidebar.is(":hidden")) return;
   var $mcontent = $viewPort,
           viewportH = parseInt($(window).height()) - 176;

   if ((!isMob() || (isMob() && currPage == 'upload') )) {
      $mcontent.height(viewportH);
      var $child = $mcontent.find(".viewbox.active"),
              childT = parseInt($child.css("padding-top")) + parseInt($child.css("padding-bottom"));
      $child.height(parseInt(viewportH - childT));

   }
   $viewPort.removeClass("view_port_fixed");


}

function uploadPage() {
   viewport();
   if ($(".file-input-wrapper").length == 0)
      $("input[type=file]").FileInput();
   slideSidebar("open");
}

function listPage() {
   viewport();
}

function settingsPage() {
   viewport();
   slideSidebar("close");
}

function sidebarToggle(sel, hel) {
   var hele = $sidebar.find(hel),
           sele = $sidebar.find(sel),
           helc = hele.length,
           selc = sele.length;

   $.each(hele, function(index, el) {
      var node = $(el);
      node.delay(300).queue(function(next) {
         node.slideUp(300, function() {
            if (index + 1 == helc) {
               $sidebar.find(sel).slideDown(200, function() {
                  $(this).css({"overflow": "inherit", "height": "auto"});
               });
               if (currPage == 'list')
                  reselectFiles();
            }
         });
         node.dequeue();
         next();
      });
   })
}

function slideSidebar(state) {

   if (state == 'open') {
      if ($sidebar.is(":visible"))
         return;
      if (!isMob()) {
         $sidebar.css("width", "auto");

         $("#wrap").stop().animate({"margin-right": 250}, 250);

         setTimeout(function() {
            $sidebar.stop().animate({'width': 251}, 250, function() {
               $sidebar.show();
            });
         }, 120);


      }

   } else {
      if ($sidebar.is(":hidden"))
         return;
     
      $sidebar.unblock();
      
      $sidebar.hide("fast", function(){
          $("#wrap").stop().animate({'margin-right': 0}, 250);  
      });      
   }
}

function makeUploadSideBarNav(data) {
   slideSidebar("open");
   $(data.target).html(data.html);
   
   $(data.target + " select").on('change', function() {
      $("#udir").val($(this).val());
   }).selectpicker( (( isMob() || isTablet() ) ? 'mobile' : null) );
   sidebarToggle(".upload:not(.dyn)", ".files");
}

function makelistSideBarNav(data) {
   var target = $(data.target),
           cont = $(data.container),
           html = $('<option/>'),
           optg = $('<optgroup label="Personal Folders" />'),
           $filt = $("#listShowOpt");

   $.each(data.obj, function(index, value) {
      var clone = html.clone(true);
      $.each(value.attr, function(key, val) {
         clone.attr(key, val);
      });
      clone.html(value.txt);
      if (value.u) {
         optg.append(clone);
      } else {
         cont.append(clone);
      }
   });

   $filt.find("button").toggleClass("disabled", (!data.item || (currDirID > 1 && currDirID < 7)) )

   cont.append(optg);
   target.find(".inner").html(cont);

   target.find("select").on('change', function() {
      location.hash = $(this).val();
      ("closeSbar" in this) && closeSbar && closeSbar();
   }).selectpicker( (( isMob() || isTablet() ) ? 'mobile' : null) );
   target.find(".inner .bootstrap-select").after(data.inject);

   setSideBarEvents(target);
   $sidebar.unblock();
   sidebarToggle(".files:not(.stc)", ".upload:not(.dyn)");

   $sidebar.find(".filter").on('click', function() {
      if ($(this).hasClass("disabled"))
         return;

      var target = $(this).data('target') || 0;
      if (target) {
         $viewPort.find(".preview").not("." + target).hide();
      } else {
         $viewPort.find(".preview").show();
      }
      $.cookie('CL_listft', target);
      $filt.spin(false);
      ("closeSbar" in window) && closeSbar && closeSbar();
      $(window).resize();
   });
   var listfilter = $.cookie('CL_listft') || 0;
   if (listfilter != 0) {
      $filt.spin({boxSize: 'xsmall', main: false, top: 2, left: 55});
      setTimeout(function() {
         $sidebar.find('button[data-target="' + listfilter + '"]').trigger("click");
      }, 1000);
   }
   slideSidebar("open");
   
   setTimeout(function() {
      if (jQuery.isFunction(jQuery.fn.scrload) && currPage == 'list') {
         $viewPort.find("img.tn").scrload('.viewbox.active');
      }
   }, 350); //wait browser;
}

function setMeters() {
   var $meters = $("#meters"),
           $mparent = $meters.closest("div.size-container");
   $.ajaxQueue({
      url: '/',
      type: 'GET',
      noind: true,
      data: {meter: true},
      dataType: 'json',
      beforeSend: function() {
         $mparent.spin({boxSize: 'small', main: false});
      },
      success: function(response) {
         setTimeout(function() {
            $mparent.spin(false);
            $.each(response, function(key, value) {
               $meters.find('.' + key).html('<strong>' + value + '</strong>').removeClass("dnone");
            });
            $meters.removeClass("dnone");
         }, 4000);
      }

   });

}

function setSideBarEvents(target) {
   target.find('.btn.act').click(function(e) {
      e.preventDefault();
      var $this = $(this);
      if ($this.hasClass('disabled'))
         return;
      createBox($this);
   });
}
function makebreadcrumb(obj) {
   if (!obj && !$(document).data("breadcrumb_" + currPage)) {
      return;
   }
   else if (!obj) {
      obj = $(document).data("breadcrumb_" + currPage)
   }
   if (currPage != 'upload') {
      $("#upToolp").addClass("dn").appendTo("#upload");
      $("#upsrtoolbox").addClass("dn").appendTo("#upload");
   }
   var pageTitle = '',
           $breadcrumbCont = $('#breadcrumb'),
           $beadControl = $('<li class="breadcontr navbar fltrt" />'),
           $item = $('<li><a href=""></a></li>'),
           $span = $('<span class="divider"> <i class="icon-caret-right"></i> </span>'),
           $goback = $('<li><a href="javascript:history.back();"><i class="icon-circle-arrow-left icon-large"></i></a></li><li class="divider-vertical"></div>'),
           $selectToolbx = $('<div class="foacts btn-group pull-right" style="text-align: right"><button class="btn" data-action="fi_selectall">Select All Files</button><button class="btn btn-danger" data-action="fi_delete">Delete</button></div>');
   $breadcrumbCont.empty();
   $.each(obj.links, function(index, val) {
      var clone = $item.clone();

      clone.find("a").html(val.title).attr("href", val.to).on('click');

      if (val.active)
         clone.addClass("active well");
      else
         clone.append($span.clone());

      if (val.icon)
         clone.find("a").prepend($('<i class="' + val.icon + '"/>'));

      if (isMob()) {
         if (val.active)
            $breadcrumbCont.append(clone);
      }
      else {
         $breadcrumbCont.append(clone);
      }

      if (val.title)
         pageTitle += ' » ' + val.title;
   });
   if (obj.control) {
      var cbc = $beadControl.clone().html(obj.control.html)
      $breadcrumbCont.append(cbc);
   }
   if (currDir) {
      $breadcrumbCont.prepend($goback);
   }

//        else
   $viewPort.prepend($breadcrumbCont).on("click");

   if (currPage == 'upload') {

      $("#upToolp").appendTo("#breadcrumb").removeClass("dn");
      $("#upsrtoolbox").appendTo("#breadcrumb").removeClass("dn");
   }

   $(document).data("breadcrumb_" + currPage, obj);
   handlePageTitle(pageTitle);
}

function handlePageTitle(newtitle) {
   var otitlev = document.title,
           titstn = SITENAME;

   if ($viewPort.data("otitle") === undefined) {
      $viewPort.data("otitle", otitlev);
   }
   if (newtitle)
      document.title = titstn.strip() + newtitle.strip();
   else
      document.title = $viewPort.data("otitle").strip();
}

function handleBoxFormAjaxResponse(responseText, statusText, xhr, $form, $element) {
   var clsbtn = $element.prev("a");

   if (responseText.result) {
      if ($form.attr("id") == "form_fo_delete") {
         if ($form.find('input[name="value"]:first').val() == currDirID) {
            window.location.replace('#page=' + currPage);
         } else {
            reloadPage(1);
         }
      } else {
         reloadPage(1);
      }
      $form.html($('<div class="control-group" style="margin: 0; float:none"><div class="alert alert-success">' + responseText.message + '</div></div>'));
      $element.remove();
      clsbtn.text("Close");
   } else {
      var alert = $form.find(".alert");

      if ($form.find(".fterr").length == 0) {
         cloth = $('<div class="control-group fterr" style="margin: 0"/>');
         $form.prepend(cloth);
      } else {
         cloth = $form.find(".fterr");
      }
      cloth.append(alert);
      alert.show().find("div:first").html(responseText.message);
      $element.button("reset");
   }
   $(window).resize();
   setMeters();
}
function handleboxForms($element, $form) {
   var options = {
      success: function(responseText, statusText, xhr, $form) {
         handleBoxFormAjaxResponse(responseText, statusText, xhr, $form, $element);
         $spintarget.spin(false);
      },
      dataType: 'json'
   };
   $form.ajaxSubmit(options);
}

function createBox($this) {
   var action = $this.data("action"),
           $box = $("#actionbox div.modal:first").clone(true),
           $actbtn = '';

   var title = $this.data("title") || 0,
           bid = $this.data("item") ? $this.data("item") : $this.data("id"),
           boxid = "box_" + action,
           $form = $box.find("form:first"),
           $html = '<div class="alert alert-error dnone"><div></div></div>\n\
                  <input class="dnone" type="hidden" name="value" value="' + bid + '" />\n\
                  <input class="dnone" type="hidden" name="action" value="' + action + '">', //static sends
           labels = (($this.data("labels") !== undefined) ? $this.data("labels").split("_") : ''),
           content = $this.data("content") || 0;


   switch (action.split("_")[1]) {
      case 'delete':
         $actbtn = $('<a href="" class="btn btn-danger go">Delete</a>');
         $html += '<input class="dnone" type="hidden" name="odir" value="' + $this.data("dir") + '" />';
         break;
      case 'create':
         $html += '<div class="control-group">\n\
                     <input type="text" name="name" class="span3" placeholder="' + $this.data("placeholder") + '">\n\
                     </div>\n\
                     <div class="control-group last">\n\
                     <input type="text" name="desc" class="span3" placeholder="' + $this.data("desc") + '">\n\
                     </div>';
         $actbtn = $('<a href="" class="btn btn-primary disabled go">Create</a>');
         break;
      case 'edit':
         $html += '<div class="control-group">\n\
                     <label for="foname">' + labels[0] + '</label>\n\
                     <input type="text" id="foname" name="name" class="span3" value="' + $this.data("placeholder") + '">\n\
                     </div>\n\
                     <div class="control-group last">\n\
                     <label for="fodesc">' + labels[1] + '</label>\n\
                     <input type="text" id="fodesc" name="desc" class="span3" value="' + $this.data("desc") + '">\n\
                     </div>';
         $actbtn = $('<a href="" class="btn btn-primary disabled go">Save</a>');
         break;
      case 'move':
         $this.data("ajax", "loadFoldersDropdown");
         $html += '<div class="control-group">\n\
                     <span>Current Folder: </span><i class="ul">' + $viewPort.find("#breadcrumb li.active a").text() + '</i>\n\
                     </div>\n\
                     <div id="fo_dropdownlist" class="control-group row-fluid last">\n\
                     <label for="folist">Move To</label>\n\
                     <div style="height: 33px"></div>\
                     <input class="dnone" type="hidden" name="odir" value="' + $this.data("dir") + '" />\
                     </div>';
         $actbtn = $('<a href="" class="btn btn-primary disabled go">Move</a>');
         break;
      case 'share':
         if (title == 0) {
            title = "Share folder <span>" + $this.data("placeholder") + "</span>";
         }
         $html += '<div id="shere_box" class="control-group row-fluid">\n\
                     <div style="min-height: 190px;"></div>\n\
                     </div>';
         $actbtn = false;
         break;
      case 'downloadzip':
         $html += '<div class="control-group" style="text-align: center">\n\
                     <span>Download Folder as zip: </span>\n\
                     </div>\n\
                     <div id="down_box" class="control-group row-fluid" style="text-align: center">\n\
                     <button data-loading-text="Generating download link..." id="downloadBtn" type="button" class="btn btn-primary btn-large" style="margin: 0 !important"><i class="icon-download"></i> Download</button>\n\
                     <div class="alert alert-info dnone retry" style="margin-top: 10px; margin-bottom: 0"><small style="display:block">Your download will start automatically. </small>If not click <small><a href="javascript:;"><strong>here</strong></a> to retry.</small></div>\n\
                     </div>';
         $actbtn = false;
         break;
      default:
         $actbtn = $('<a href="" class="btn btn-danger disabled go">Continue</a>');
         break;
   }

   //set the box id
   $box.attr("id", boxid);
   $form.attr("id", "form_" + action).data("target", $this.attr("id")).append($html);
   if (title)
      $box.find(".box_title").html(title);
   else
      $box.find(".modal-header").remove();

   if (content) {
      if (action.split("_")[1] == 'delete') {
         $form.find(".alert").show().addClass("mp0").find("div:first").html(content);
      } else
         $form.append(content);
   }

   //bind click event to the $actbtn to submit the form;
   if ($actbtn) {
      $actbtn.on('click', function() {
         if (!$(this).hasClass("disabled")) {
            handleboxForms($(this), $form);
            $(this).button("loading");
         }
         return false;
      });
      $box.find(".modal-footer").append($actbtn);
   }

   if (!title) {
      $box.find(".modal-header").remove();
   }


   $box.on('shown', function() {
      var tinput = $form.find("input[type=text]") || 0;
      if (tinput) {
         tinput.eq(0).focus();
      }
   });

   $box.on('shown', function() {
      if ($this.hasClass("ajax")) {
         window[$this.data("ajax")]($this, $form, $box);
      }else{
          $box.find("a.go").disabletoggle(false);
      }
   });
   
   $box.modal("show");
}

function downloadzip($this, $form) {
   $("#downloadBtn").on('click', function() {
      var $this = $(this);
      $this.button("loading");
      $form.find(".retry").show().find("a").on('click', function() {
         createTempIframe('?octet&file=' + $this.data("link"));
      });
      var options = {
         success: function(response, statusText, xhr, $form) {
            createTempIframe('?octet&file=' + response.file);
            $spintarget.spin(false);
            $this.data("link", response.file);
            setTimeout(function() {
               $this.disabletoggle(true);
            }, 80);

         },
         dataType: 'json'
      };
      $form.ajaxSubmit(options);
   });

}

function createTempIframe(source) {
   $spintarget.blockit({message: "Downloading... Please wait!"});
   $spintarget.spin({main: true, top: 0});
   if (isMob() || isTablet() || navigator.userAgent.indexOf("MSIE") > -1) {
      window.location = '/' + source;
      $spintarget.unblock();
      $spintarget.spin(false);
   } else {
      var iframe = $('<iframe>', {
         "src": source,
         "class": 'dn',
         "id": 'octetFrame'
      }).appendTo("body").one('load', function() {
         $spintarget.unblock();
         $spintarget.spin(false);
         setTimeout(function() {
            $("#octetFrame").remove();
         }, 1000);
      });
   }
}

function loadShareBox($this, $form, $box) {
   var target = $('#shere_box').find("div:first"),
           data = $form.find('input[type="hidden"]:first').val()/*selectedItems.join(',')*/;
   $.ajaxQueue({
      url: '/',
      type: 'POST',
      data: {action: $this.data("action"), value: data},
      dataType: 'json',
      beforeSend: function() {
         setTimeout(function() {
            $form.blockit({message: 'Loading Share Options...', simple: true});
         }, 16);
      },
      success: function(response) {
         $spintarget.spin(false);
         $form.unblock();
         target.html(response.html);
         $box.find("a.go").disabletoggle(false);
         $('#shareme').share({
            networks: shareOpts,
            link: response.link,
            url: response.url,
            title: response.title,
            desc: response.description,
            theme: "circle"
         });
      }
   });
}

function loadFoldersDropdown($this, $form, $box) {
   var target = $('#fo_dropdownlist').find("div:first");

   var sdata = {
      action: 'fo_dropdown',
      value: $this.data("id")
   }
   $.ajaxQueue({
      url: '/',
      type: 'POST',
      data: sdata,
      dataType: 'json',
      beforeSend: function() {
         target.blockit({message: 'Loading Folders...', simple: true});
      },
      success: function(response) {
         $box.find("a.go").disabletoggle(false);
         jsontoDropdown(response, target, $this);
         $viewPort.data('fo_dropdownlist', response);
         $spintarget.spin(false);
      }
   });
}
function jsontoDropdown(data, target, $this) {
   var cont = $(data.container),
           html = $('<option/>');

   $.each(data.obj, function(index, value) {
      var clone = html.clone();
      $.each(value.attr, function(key, val) {
         clone.attr(key, ((val != currDirID || val == 1) ? val : 0));
      });
      clone.text(value.txt);
      if (clone.attr("value") == 0 || clone.attr("value") == $this.data("id")) {
         clone.attr("disabled", "disabled");
      }
      cont.append(clone);
   });
   target.html(cont).show();
   target.find("select").selectpicker( (( isMob() || isTablet() ) ? 'mobile' : null) );
}

function reselectFiles() {
   var cook = $.cookie('CL_selectedf') || 0;
   if (cook && $.inArray(currPage, freepArr) > -1) {
      var ncook = (($.cookie('CL_selectedf').indexOf(',')) ? $.cookie('CL_selectedf').split(',') : $.cookie('CL_selectedf'));

      $.each(ncook, function(index, val) {
         if ($viewPort.find("input#" + val).length > 0) {
            var $inPar = $viewPort.find("input#" + val).closest("li");
            if(isMob() || isTablet()){
               $inPar.find("a.check").trigger("click");
            }else{
               $inPar.trigger("click");
            }
            
         } else {
            $filesSideBarTbar.slideUp("fast");
         }
      });

   }
}
//file loader...
$(function() {

   var $list = $("#list"),
           $screl = isMob() ? $(window) : $("#list");

   $screl.scroll($.debounce(250, function() {
      var tp = (isMob() ? $(document).height() - $screl.height() : $list.find("ul.directoryList").height() - $list.height()),
              st = (isMob() ? $screl.scrollTop() + 230 : $list.scrollTop() + 130); // fire -130px before

      if (st >= tp) {
         var page = parseInt($(".pageObj:last").val());
         if (page != 0) {
            if (currPage == 'list')
               $.ajaxSingle({
                  url: '?' + jQuery.param.fragment(window.location.hash).replace(/^page=/, '') + '&pg=' + (page + 1) + (currPage == 'upload' ? ('&' + getBrowserSupportParamsUpload()) : ''),
                  type: 'GET',
                  dataType: 'json',
                  cache: false,
                  success: function(response)
                  {
                     if (response.html) {
                        $list.find("ul.directoryList").append(response.html);
                        $spintarget.spin(false);
                        executer(response);
                        if (jQuery.isFunction(jQuery.fn.scrload))
                           $list.find("img.tn").scrload('div.viewbox.active');

                        $("#sAll").disabletoggle(false);
                     } else
                        return;
                  }
               });
         }
      }
   }));
   //sidebar folder action
   //file-folder hover-click functions
   var $pTarget = '';
   var $foacts = $("#foacts");
   var mobOgfoldobj = ((isMob() || isTablet()) ? 'li.file a.check' : 'li.file');   
   
   $("body").on('click', mobOgfoldobj, function(e) {
      e.preventDefault();
      var $link = ((isMob() || isTablet()) ? $(this).closest("li.file") : $(this)),
              selectedArr = new Array(),
              appd = $('<a title="Deselect File" href="#" class="btn btn-primary check"><i class="icon-ok"></i></a>'),
              parent = $link,
              dsbtn = $("#dsAll"),
              sebtn = $("#sAll"),
              $filesSideBarTbar = $("#filesdropdown");

      if (!isMob() && !isTablet()) {
         appd.on('mouseenter', function(e) {
            e.preventDefault();
            $(this).toggleClass("btn-warning").find("i").toggleClass("icon-minus lh19");
         }).on('mouseleave', function(e) {
            e.preventDefault();
            $(this).toggleClass("btn-warning").find("i").toggleClass("icon-minus lh19");
         });;
      }

      var $chkbox = $link.find("input[type=checkbox]"),
              status = !$chkbox.attr("checked");

      $chkbox.prop("checked", status);

      if (parent.find('a.check.on').length == 0) {
         parent.append(appd.clone(true).removeClass("off").addClass("on"));
         parent.find('a.check.off').hide();
         parent.addClass("selected");
         $link.closest("li").removeClass("hover");
      } else {
         parent.find('a.check.on').remove();
         parent.find('a.check.off').show();
         parent.removeClass("selected");
      }

      if (parent.data("fname") !== undefined)
         parent.find('div.fname').html((status === false) ? '<strong>Select File</strong>' : '<strong>Deselect File</strong>')

      var totalChecked = $("input.echk:checked").length,
              id = $link.find("input").attr('id');

      if (totalChecked > 0) {
         if ($filesSideBarTbar.is(':hidden'))
            $filesSideBarTbar.slideDown("fast");

         //mobile
         ("highlightSBt" in window) && highlightSBt && highlightSBt();

         $filesSideBarTbar.find("h5").text(totalChecked + ' file(s) selected.');

         dsbtn.disabletoggle(false);

         $.removeCookie('CL_selectedf');

         $("input.echk:checked").each(function() {
            selectedArr.push(this.id);
         });
         
         
         selectedItems = selectedArr;
        
         $.cookie('CL_selectedf', selectedArr);

         if (selectedItems.length == $('li.file').length) {
            sebtn.disabletoggle(true);
         } else {
            sebtn.disabletoggle(false);
         }

      } else {
         $filesSideBarTbar.slideUp("fast");
         $.removeCookie('CL_selectedf');
         dsbtn.disabletoggle(true);
      }
      return false;
   });

   $("body").on('click', '.msel', function(e) {
      var $this = $(this);
      if ($this.hasClass("disabled"))
         return;
      
      switch ($this.data("action")) {
         case 'ds':
            $viewPort.find(((isMob() || isTablet()) ? 'li.file.selected a.check' : 'li.file.selected')).trigger("click");
            break;
         case 'sa' :
            $viewPort.find(((isMob() || isTablet()) ? 'li.file:not(.selected) a.check' : "li.file:not(.selected)")).trigger("click");
            $this.disabletoggle(true);
            break;
      }
      return false;
   });
   if (!isMob() && !isTablet()) {
      $("body").on('mouseenter', '.msel', function() {
         var $el = $(this).find("span.title");
         
         if ($(this).hasClass("disabled")) return;
         
         $el.stop().delay(450).animate({'width': (parseInt($el.css("width")) > 0 ? 0 : 102)}, 200);
      }).on('mouseleave', '.msel', function() {
         var $el = $(this).find("span.title");
         if ($(this).hasClass("disabled")) return;
         $el.stop().animate({'width': (parseInt($el.css("width")) > 0 ? 0 : 102)}, 200);
      });
   };

   var mobOgfoldevent = ((isMob() || isTablet()) ? 'click' : 'mouseenter'),
       mobOgfoldeventOut = ((isMob() || isTablet()) ? 'click' : 'mouseleave'),
           mobOgfoldobj  = ((isMob() || isTablet()) ? 'li.preview.folder .ose' : 'li.preview.folder');

   $("body").on(mobOgfoldevent, mobOgfoldobj, function(e) {
      e.stopPropagation();

      var $this = $(this).hasClass("folder") ? $(this) : $(this).closest(".folder"),
              $link = $this.find("a.rpsve"),
              $el = isMob() ? $("#actFolder") : $this,
              href = $link.attr("href").replace(/^#/, '');


      if ($el.find(".foacts").length === 0) {
         var clone = $foacts.clone();
         clone.attr("id", "foacts-" + $link.attr("href").replace(/^#/, ''));
         if ($el.hasClass("em")) {
            clone.find('a[data-action="fo_share"]').addClass("disabled");
         }
         $el.append(clone);
      }
      $el.find(".foacts").slideToggle(60);

      return false;   

   });
   if(!isMob() && !isTablet())
   $("body").on("mouseleave", mobOgfoldobj, function(e) {
      
      e.stopPropagation();

      var $this = $(this).hasClass("folder") ? $(this) : $(this).closest(".folder"),
              $link = $this.find("a.rpsve"),
              $el = isMob() ? $("#actFolder") : $this,
              href = $link.attr("href").replace(/^#/, '');  
      
      $el.find(".foacts").slideToggle(60);
   });

   $("body").on('click', 'a.octet', function(e) {
      e.preventDefault();
      var $this = $(this);
      createTempIframe('?octet&file=' + $this.data("file"));
      return false;
   });

   $("body").on('click', '.foacts .btn', function(e) {
      e.preventDefault();
      e.stopPropagation();
      var $this = $(this);
      if ($this.hasClass("disabled") || $this.hasClass("octet"))return;

      var action = $this.data("action"),
              actionp = action.split("_");

      switch (actionp[0]) {
         case 'fo': //this is a folder
            var $pa = $this.closest("li").find("a.rpsve"),
                    $foact = $this.closest(".foacts"),
                    data = $foact.attr("id").split(","),
                    name = $pa.find('span.fon').attr("title"),
                    data = $this.closest(".foacts").attr("id").split(","),
                    desc = $pa.attr('title');
            $this.attr({
               'data-content': ((actionp[1] === 'delete') ? 'Are you sure you want to delete <i>' + name + '</i> and its all files & sub-directories?' : 0),
               'data-title': $sidebar.find('a[data-action="' + action + '"]').data("title"),
               'data-id': data[1],
               'data-dir': data[1],
               'data-placeholder': name,
               'data-desc': desc || '',
               'data-labels': 'Name_Description'
            });
            break;
         case 'fi':
            var name = $viewPort.find('input#' + selectedItems[0]).data("name");
            if (selectedItems.length > 1) {//multiple files are selected
               name = name + ' & other selected <strong>' + (selectedItems.length - 1) + '</strong> file(s)';
            }
            $this.data({
               "content": ((actionp[1] === 'delete') ? ('Are you sure you want to delete <i>' + name + '</i>?') : 0),
               "id": selectedItems.join(','),
               "dir": currDirID
            });
            break;

         case 'uf':
            var name = $this.data("name");

            $this.data({
               "content": ((actionp[1] === 'delete') ? ('Are you sure you want to delete <i>' + name + '</i>?') : 0)
            });
            break;
      }
      createBox($this);
   });
   
   //search
   sCache = {};
   imap   = {};
   qitems  = [];
   function searchQuery(query, process) {
       
       if(query in sCache){
           process(sCache[query]);
       }else{
         $.ajaxSingle({
            url: '/',
            noind: true,
            type: 'GET',
            dataType: 'json',
            data: {'search': 'key', q: query},
            cache: true,
            beforeSend: function() {
               $('#dsearch').spin({boxSize: 'xsmall', main: false, top: 12});
            },            
            success: function(response)
            {
               $('#dsearch').spin(false);

               $.each(response, function(i, val) {
                  imap[val.name] = val;
                  qitems.push(val.name);
               });
              
               sCache[query] = qitems;
               
               if(response.auth != 0)
                  process(sCache[query]);
            }
         });
     }
     $("body").blockit({baseZ : 10,overlayCSS : {opacity: .5, cursor : 'default'}});
   }

   var typ = $(".search-query").typeahead({
      items: 12,
      minLength: 0,
      source: function(query, process) {
         return searchQuery(query, process);
      },
      sorter: function(items) {         
          return items.sort();
      },
      highlighter: function(item, element) {
         var regex = new RegExp('(' + this.query + ')', 'gi'),
               res = imap[item];
         
         return '<span class="thumbnail tip" data-title="'+res.name+'">' + 
                 (
                 res.thumbnail 
                 ? 
                 ('<img src="'+res.thumbnail+'" />')
                 : 
                 ('<i class="'+res.icon+'"></i>')  
                 ) + '</span> ';
         //'<div class="name">'+res.name.replace(regex, "<strong>$1</strong>") + '</div>';
      },
      
   render: function (items) {
      var that = this

      items = $(items).map(function (i, item) {
        i = $(that.options.item).attr('data-value', item)
        i.find('a').tooltip({html: true, placement: popoverautoplacement, trigger: "hover", title : function(el){
                 return $(this).find(".tip").data("title")
         }}).html(that.highlighter(item, i))        
        return i[0]
      })

      items.first().addClass('active')
      var wi = this.$element.innerWidth()
      this.$menu.width(wi).html(items)
      return this
    },      
      
      updater: function(item) {
         var item = imap[item],
               $a = $('<a class="dn" />');
         //its a file
         if(item.file !== undefined){
            $a.attr({
               "data-file" : item.file,
               "data-id" : "f"+item.id
            });
         if(item.viewable){
            $a.attr({"data-title" : item.name, "class": "vfilebtn dn", "href" : item.url, "data-type" : item.mime});
            $a.appendTo("body");
            $(".vfilebtn").vobox();
         }else{
            createTempIframe('?octet&file=' + $a.data("file"));
         }
         }
         else{
            $.bbq.pushState('#page=list&list=' + item.url);
            }
            
         $a.trigger("click").on("click",function(){
            $(this).remove();
            return;
         });            
      },
      hide: function(){
          $("body").unblock();
          this.$menu.hide()
          this.shown = false
          return this          
      }
      
 });
 

//doc is ready   
});

window.onbeforeunload = function(){
   if(currprocData("uploadinprogress") == 1)
      return "An upload process is running. Do you really want to leave the page?";
}


function callbackCall(element) {
   if (element.data('callback')) {
      var cback = element.data('callback').split(":");
      switch (cback[0]) {
         case 'fn':
            window[cback[1]]();
            break;
         case 'eval':
            eval(cback[1]);
            break;
      }
   }
}

//update Pagelink cache
function updateprocData(newobj) {
   var obj = $viewPort.data("procs");
   $.extend(true, obj, newobj);
   $viewPort.data("procs", obj);
}
function currprocData(el) {
   var data = $viewPort.data("procs") || 0;
   return data ? data[el] : 0;
}
function reloadPage(changed, unblockel, delay) {

   var currentLoad = $.param.fragment(),
           waitt = delay || 1,
           toLoad;
   if (currentLoad.indexOf('reload') > -1) {
      var toLoad = currentLoad.replace('&reload=true', '');
   } else {
      var toLoad = $.param.fragment() + '&reload=true';
   }
   setTimeout(function() {
      if (changed) {
         $.bbq.pushState('#' + toLoad);
      } else {
         $(unblockel).unblock();
         $spintarget.spin(false);
      }
   }, waitt);
}
//Check browser
//Upload
function getBrowserSupportParamsUpload() {
   var params = {
      dragdrop: 0
   }
   if (window.FileReader && Modernizr.draganddrop) {
      params.dragdrop = 1;
   }
   return jQuery.param(params);
}

//copy all attributes
function copyattributes(source, target) {
   var attributes = $(source).prop("attributes");
   $.each(attributes, function() {
      $(target).attr(this.name, this.value);
   });
}

//return css styles of an element
function cssme(a) {
   var o = {};
   var rules = window.getMatchedCSSRules(a.get(0));
   for (var r in rules) {
      o = $.extend(o, css2json(rules[r].style), css2json(a.attr('style')));
   }
   return o;
}
function css2jsonme(css) {
   var s = {};
   if (!css)
      return s;
   if (css instanceof CSSStyleDeclaration) {
      for (var i in css) {
         if ((css[i]).toLowerCase) {
            s[(css[i]).toLowerCase()] = (css[css[i]]);
         }
      }
   }
   else if (typeof css == "string") {
      css = css.split("; ");
      for (var i in css) {
         var l = css[i].split(": ");
         s[l[0].toLowerCase()] = (l[1]);
      }
      ;
   }
   return s;
}

//popover autoplacement
function popoverautoplacement(tip, element) {
   var offset = $(element).offset(),
           height = $(document).outerHeight(),
           width = $(document).outerWidth(),
           vert = 0.5 * height - offset.top,
           vertPlacement = vert > 0 ? 'bottom' : 'top',
           horiz = 0.5 * width - offset.left,
           horizPlacement = horiz > 0 ? 'right' : 'left',
           placement = Math.abs(horiz) > Math.abs(vert) ? horizPlacement : vertPlacement;
   return placement;
}

//return file extension from filename
function filenametoext(filename) {
   var ext = /[.]/.exec(filename);
   return (ext && ext !== undefined && ext !== null) ? /[^.]+$/.exec(filename) : new Array("file");
}
//return file extension from mime/type
function extbymime(mime, filename, retmime) {
   var obj = allFileTypes();

   var trytxtp = filenametoext(filename);
   var tryext = filenametoext(filename)[0].toLowerCase();
   if (retmime) {
      if (obj[tryext] === undefined) {
         var rt = tryext + ' File';
         trytext = 'file';
      } else {
         var rt = obj[tryext];
      }
      return rt;
   } else {
      var extension = {result: tryext};
      if (obj[tryext] === undefined) {
         extension.result = "file";
      } else {
         extension.result = tryext;
      }
      return extension;
   }
}


function returnTypeIcon(ext){
   var icon = 'other';
   $.each(typesObj, function(key, obj){
      if($.inArray(ext, obj) > -1){
         icon = key.split("-")[0];
         return;
      }         
   });
   return icon;
}

//convert images to canvas objects
function ImageToCanvas(img, w, h) {
   var canvas = document.createElement("canvas");
   var context = canvas.getContext('2d');
   canvas.width = parseInt(w);
   canvas.height = parseInt(h);
   img.onload = function() {
      context.drawImage(img, 1, parseInt(h - 34));
   };
   return canvas;
}
//return responsive preview image obj
function responsiveImagePreview(img, w, h) {
   return $(img).css({'position': 'absolute', 'left': 2, 'bottom': 2});

   //return $cont;
}
//formatfilesize
function formatFileSize(bytes) {
   if (typeof bytes !== 'number') {
      return '';
   }
   if (bytes >= 1073741824) {
      return (bytes / 1073741824).toFixed(2) + ' GB';
   }
   if (bytes >= 1048576) {
      return (bytes / 1048576).toFixed(2) + ' MB';
   }
   return (bytes / 1024).toFixed(2) + ' KB';
}

function loadstyle(value, callback) {

   if ($.inArray(value.id, scrloaded) === -1) {
      yepnope.injectCss(value.src, function() {
         if (callback)
            callback.call();

         scrloaded.push(value.id);
      }, {//stylesheet attributes ie => media: "print"
         id: value.id
      }, 30000);
   } else {
      if (callback)
         callback.call();
   }
}

function loadscript(value, callback) {
   callback = (callback && typeof callback != 'undefined') ? callback : {};
   if ($.inArray(value.id, scrloaded) === -1) {
      yepnope.injectJs(value.src, function() {
         setTimeout(function() {
            if (callback){
                callback.call();
            }
         }, 500);//be sure its loaded...for bad timings!
         scrloaded.push(value.id);
      }, {
         charset: "utf-8",
         id: value.id
      }, 30000);
   } else { //dont load script again on folder changes ie... a smart way...
      if (callback){
          callback.call();
      }
   }
}

function allFileTypes(){
   return {
      323: "text/h323",
      '7z': "application/x-7z-compressed",
      aac: "audio/x-aac",
      acx: "application/internet-property-stream",
      ai: "application/postscript",
      aif: "audio/x-aiff",
      aifc: "audio/x-aiff",
      aiff: "audio/x-aiff",
      asf: "video/x-ms-asf",
      asr: "video/x-ms-asf",
      asx: "video/x-ms-asf",
      au: "audio/basic",
      avi: "video/x-msvideo",
      axs: "application/olescript",
      bcpio: "application/x-bcpio",
      bin: "application/octet-stream",
      bmp: "image/bmp",
      cat: "application/vnd.ms-pkiseccat",
      cdf: "application/x-cdf",
      cer: "application/x-x509-ca-cert",
      'class': "application/octet-stream",
      clp: "application/x-msclip",
      cmx: "image/x-cmx",
      cod: "image/cis-cod",
      cpio: "application/x-cpio",
      crd: "application/x-mscardfile",
      crl: "application/pkix-crl",
      crt: "application/x-x509-ca-cert",
      csh: "application/x-csh",
      css: "text/css",
      dcr: "application/x-director",
      der: "application/x-x509-ca-cert",
      dir: "application/x-director",
      dll: "application/x-msdownload",
      dms: "application/octet-stream",
      doc: "application/msword",
      docx: "application/vnd.openxmlformats-officedocument.wordprocessingml.document",
      dvi: "application/x-dvi",
      dxr: "application/x-director",
      eot: "application/vnd.ms-fontobject",
      eps: "application/postscript",
      etx: "text/x-setext",
      evy: "application/envoy",
      exe: "application/octet-stream",
      fif: "application/fractals",
      flr: "x-world/x-vrml",
      gif: "image/gif",
      gtar: "application/x-gtar",
      gz: "application/x-gzip",
      hdf: "application/x-hdf",
      hlp: "application/winhlp",
      hqx: "application/mac-binhex40",
      hta: "application/hta",
      htc: "text/x-component",
      htm: "text/html",
      html: "text/html",
      htt: "text/webviewhtml",
      ico: "image/x-icon",
      ief: "image/ief",
      iii: "application/x-iphone",
      ini: "text/plain",
      ins: "application/x-internet-signup",
      isp: "application/x-internet-signup",
      jfif: "image/pipeg",
      jpe: "image/jpeg",
      jpeg: "image/jpeg",
      jpg: "image/jpeg",
      js: "application/x-javascript",
      latex: "application/x-latex",
      lha: "application/octet-stream",
      log: "text/plain",
      lsf: "video/x-la-asf",
      lsx: "video/x-la-asf",
      lzh: "application/octet-stream",
      m13: "application/x-msmediaview",
      m14: "application/x-msmediaview",
      m2v: "video/mpeg",
      m3u: "audio/x-mpegurl",
      man: "application/x-troff-man",
      mdb: "application/x-msaccess",
      me: "application/x-troff-me",
      mht: "message/rfc822",
      mhtml: "message/rfc822",
      mid: "audio/mid",
      mkv: "video/x-matroska",
      mny: "application/x-msmoney",
      mov: "video/quicktime",
      movie: "video/x-sgi-movie",
      mp2: "video/mpeg",
      mp3: "audio/mpeg",
      mp4: "video/mp4",
      mpa: "video/mpeg",
      mpe: "video/mpeg",
      mpeg: "video/mpeg",
      mpg: "video/mpeg",
      mpp: "application/vnd.ms-project",
      mpv2: "video/mpeg",
      ms: "application/x-troff-ms",
      mvb: "application/x-msmediaview",
      nws: "message/rfc822",
      oda: "application/oda",
      oga: "audio/ogg",
      ogg: "audio/ogg",
      ogv: "video/ogg",
      p10: "application/pkcs10",
      p12: "application/x-pkcs12",
      p7b: "application/x-pkcs7-certificates",
      p7c: "application/x-pkcs7-mime",
      p7m: "application/x-pkcs7-mime",
      p7r: "application/x-pkcs7-certreqresp",
      p7s: "application/x-pkcs7-signature",
      pbm: "image/x-portable-bitmap",
      php: "application/php",
      pdf: "application/pdf",
      pfx: "application/x-pkcs12",
      pgm: "image/x-portable-graymap",
      pko: "application/ynd.ms-pkipko",
      pma: "application/x-perfmon",
      pmc: "application/x-perfmon",
      pml: "application/x-perfmon",
      pmr: "application/x-perfmon",
      pmw: "application/x-perfmon",
      png: "image/png",
      pnm: "image/x-portable-anymap",
      pot: "application/vnd.ms-powerpoint",
      ppm: "image/x-portable-pixmap",
      pps: "application/vnd.ms-powerpoint",
      ppt: "application/vnd.ms-powerpoint",
      prf: "application/pics-rules",
      ps: "application/postscript",
      pub: "application/x-mspublisher",
      qt: "video/quicktime",
      ra: "audio/x-pn-realaudio",
      ram: "audio/x-pn-realaudio",
      ras: "image/x-cmu-raster",
      rar: "application/x-rar-compressed",
      rgb: "image/x-rgb",
      rmi: "audio/mid",
      roff: "application/x-troff",
      rtf: "application/rtf",
      rtx: "text/richtext",
      scd: "application/x-msschedule",
      sct: "text/scriptlet",
      setpay: "application/set-payment-initiation",
      setreg: "application/set-registration-initiation",
      sh: "application/x-sh",
      shar: "application/x-shar",
      sit: "application/x-stuffit",
      snd: "audio/basic",
      spc: "application/x-pkcs7-certificates",
      spl: "application/futuresplash",
      sql: "text/plain",
      src: "application/x-wais-source",
      srt: "text/plain",
      sst: "application/vnd.ms-pkicertstore",
      stl: "application/vnd.ms-pkistl",
      stm: "text/html",
      svg: "image/svg+xml",
      sv4cpio: "application/x-sv4cpio",
      sv4crc: "application/x-sv4crc",
      swf: "application/x-shockwave-flash",
      t: "application/x-troff",
      tar: "application/x-tar",
      tcl: "application/x-tcl",
      tex: "application/x-tex",
      texi: "application/x-texinfo",
      texinfo: "application/x-texinfo",
      tgz: "application/x-compressed",
      tif: "image/tiff",
      tiff: "image/tiff",
      tr: "application/x-troff",
      trm: "application/x-msterminal",
      tsv: "text/tab-separated-values",
      ttf: "application/x-font-ttf",
      txt: "text/plain",
      uls: "text/iuls",
      ustar: "application/x-ustar",
      vcf: "text/x-vcard",
      vrml: "x-world/x-vrml",
      wav: "audio/x-wav",
      wcm: "application/vnd.ms-works",
      wdb: "application/vnd.ms-works",
      wks: "application/vnd.ms-works",
      wma: "audio/x-ms-wma",
      wmf: "application/x-msmetafile",
      wmv: "video/x-ms-wmv",
      woff: "application/x-font-woff",
      wps: "application/vnd.ms-works",
      wri: "application/x-mswrite",
      wrl: "x-world/x-vrml",
      wrz: "x-world/x-vrml",
      xaf: "x-world/x-vrml",
      xbm: "image/x-xbitmap",
      xml: 'application/xml',
      xla: "application/vnd.ms-excel",
      xlc: "application/vnd.ms-excel",
      xlm: "application/vnd.ms-excel",
      xls: "application/vnd.ms-excel",
      xlt: "application/vnd.ms-excel",
      xlw: "application/vnd.ms-excel",
      xof: "x-world/x-vrml",
      xpm: "image/x-xpixmap",
      xwd: "image/x-xwindowdump",
      z: "application/x-compress",
      zip: "application/zip",
      file: "unknown/File"
   };
}