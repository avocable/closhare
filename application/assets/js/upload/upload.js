   $(function() {
      uploadButton = $('<button type="button"/>')
              .addClass('btn startstop dnone')
              .prop('disabled', true)
              .text('Processing...')
              .on('click', function() {
         var $this = $(this),
                 data = $this.data();
         $this
                 .unbind('click')
                 .text('Abort')
                 .on('click', function() {
            data.abort().done(function() {
               $this.remove();
            });
         });
         setTimeout(function() {
            data.submit().always(function() {
               //$this.remove();
            });
         }, 230);
      });

      clearButton = $('<button type="button"/>')
              .addClass('btn clear dnone')
              .on('click', function() {
         var $this = $(this),
                 data = $this.data() || false;

         if (data) {
            data.abort();
            data.context.remove();
         }
      });

      removeButton = $('<button type="button"/>')
              .addClass('btn btn-danger btn-small remove').attr("title", "Remove").append('<i class="icon-remove"/>')
              .on('click', function() {
         var $this = $(this),
                 data = $this.closest("li").find("button.startstop").data() || false;
         if (data) {
            data.abort();
         }
         if ($('#files').find("li").length == 1) {
            $toolBarclearbtn.trigger("click");

         } else {
            if (data)
               removeNode(data.context.find("li"));
           else
               removeNode($this.closest("li"));
         }
      });

      $uploadv = $('#upload');
      toolBar = $('<div class="toolbar"><a href="" class="icon-cancel cancel"></a></div>');
      $maintoolbox = $('#upsrtoolbox');
      $maintoolboxbtns = $('#upsrtoolbox button');
      $filesList = $('#files');
      autostart = uOptions['autoUpload'] || false;
      $toolBarstartbtn = autostart ? undefined : $("#startupload");
      
      $toolBarcancelbtn = $("#cancelupload");
      $toolBarclearbtn = $("#clearlist");
      confsend = false;
      $sign = {
         'success': $('<button type="button" class="btn btn-success btn-small ok"><i class="icon-ok-sign"></i></button>'),
         'abort': $('<button type="button" class="btn btn-warning btn-small abort"><i class="icon-minus-sign"></i></button>'),
         'error': $('<button type="button" class="btn btn-danger btn-small error"><i class="icon-exclamation-sign"></i></button>')
      }

      $typeSelector = $('#upload_select_cont');
      $mainprogress = $('#mprogress');
      $mxcounter = 0;
      //width of $maintoolbox;
      if (isMob()) {

         var widthmaintoolbox = $viewPort.outerWidth(),
                 sw = parseInt($toolBarstartbtn.innerWidth()),
                 aw = parseInt($toolBarcancelbtn.innerWidth()),
                 cw = parseInt($toolBarclearbtn.innerWidth()),
                 atw = widthmaintoolbox - (sw + aw + cw + 40);
         if (atw > 0) {
            var tp = ((atw) / ($maintoolboxbtns.length * 2)),
                    $els = $maintoolbox.find("button"),
                    it = $els.length;
            $els.each(function() {
               $(this).css({'padding-left': tp, 'padding-right': tp})
            });
         }
      }

      jqXHR = $('#cloupload').fileupload(
              uOptions
              ).on('fileuploaddragover', function(e, data) { 
         e.stopPropagation();

         var upsbtn = $typeSelector.find('.file-input-wrapper');

         if (!upsbtn.data("title"))
            upsbtn.data("title", upsbtn.text());

         upsbtn.find("span").text("Drop files to upload here.");

         upsbtn.stop(true, true).addClass("btn-large");

         $typeSelector.find('h3').css('color', '#666');

         $typeSelector.stop().addClass("on").fadeTo("fast", .9);
         return false;
      }).on('fileuploaddrop', function() {
         $uploadv.trigger("dragleave");
      }).on('fileuploaddragmove', function(e) {
         e.preventDefault();
         return false;
      }).on('fileuploadadd', function(e, data) {
         $typeSelector.fadeTo("fast", 0.5);
         data.context = $('<div/>').appendTo('#files');

         $.each(data.files, function(index, file) {
            
            
            var node = $('<li>\
                           <div class="ctt file thumbnail">\
                           <a href="javascript:;" style="display:block; z-index:2; position: absolute; width: 118px; height: 118px;">\
                           <div class="proc"></div>\
                           <div class="preview">\
                           <div class="fname"></div>\
                           </div>\
                           <div class="perc"></div>\
                           </a>\
                           </div>\n\
                           </li>');
            var previewContainer = node.find("div.ctt"),
                    canvascontainer = previewContainer.find(".preview");
            if (!index) {
               var uptbtn = uploadButton.clone(true).data(data),
                       rmbtn = removeButton.clone(true).data(data);

               node
                       .append(uptbtn)
                       .append(clearButton.clone(true).data(data))
                       .append(rmbtn)

               node.addClass("processing").appendTo(data.context);
               $maintoolbox.show();

            }
         });

         $toolBarclearbtn.disabletoggle(false);
         if(!autostart)
            $toolBarstartbtn.disabletoggle(false);
         
      }).on('fileuploadprocessalways', function(e, data) {
         var index = data.index,
                 file = data.files[index],
                 node = $(data.context.children()[index]);
         node.removeClass('processing');
         
         if(isAndroid && file.size > data.maxChunkSize){
             uOptions.forceIframeTransport = true; 
         }else{
             uOptions.forceIframeTransport = false;
         }
         var ftypeIcon = returnTypeIcon(filenametoext(file.name)[0].toLowerCase());

         node.addClass(ftypeIcon);
         
         var previewContainer = node.find("div.ctt").popover(
                 {
                    html: true,
                    placement: popoverautoplacement,
                    trigger: 'hover',
                    title: 'File Info',
                    content: '<div><strong>Name: </strong><span>' + file.name + '</span></div><div><strong>Size: </strong><span>' + ((file.size >= 0 && file.size != undefined) ? formatFileSize(file.size) : "not suported.") + '</span></div>\n\
                     <div><strong>Mime type: </strong><span>' + extbymime(null, file.name, 1) + '</span></div>'
                 }),
         canvascontainer = previewContainer.find(".preview");
         filename = previewContainer.find("div.fname");
        
         filename.text(file.name.trunc(14, '...'));

         canvascontainer
                 .prepend(file.preview);

         previewContainer
                 .find("a").prepend(filename).append(canvascontainer);
         node
                 .prepend(previewContainer);


         if (file.error) {
            node.addClass("error");
            filename.html('<strong>' + file.error + '</strong>');
            node.find("button.startstop").remove();
            if (file.errcode && file.errcode != 1 && file.errcode != 5 && file.errcode != 3) {//not valid file so remove it after a while
               setTimeout(function() {
                  removeNode(node, data, index);
               }, 4000);
            }
            previewContainer.removeData("popover").off("hover").popover({
               html: true,
               trigger: 'hover',
               placement: popoverautoplacement,
               title: 'Error',
               content: '<div>The file:<i>' + file.name + '</i> has an error.<br>' + file.errorE + '</div>'
            });
            
            if (!autostart && file.errcode != 2 && file.errcode != 3 && checkUpArea() <= 1) {
                  $toolBarstartbtn.disabletoggle(true);
             }
         }
         if (index + 1 === data.files.length) {
            data.context.find('button:not(.remove)')
                    .prop('disabled', !!data.files.error);
         }
         if (checkUpArea() == 0) {
            resetuploadArea();
         }

      }).bind('fileuploadsend', function(e, data) {
         if( (isMob() && !confsend) || (isTablet() && !confsend) ){
            var r = confirm("Please confirm to continue.");
            if (r != true) {
               $toolBarcancelbtn.trigger("click");
            }else{
             if(isAndroid){
                  $spintarget.spin({main: true, top: 0});
                  confsend = true;
             }
             
            }
         }
      }).on('fileuploadstart', function() {

         $sidebar.blockit({message: ''});
         $toolBarcancelbtn.disabletoggle(false);

         $toolBarclearbtn.disabletoggle(true);

         updateprocData({'uploadinprogress': 1});

      }).bind('fileuploadprogress', function(e, data) {

         $.each(data.files, function(index, file) {

            var $file = $(data.context.children()[index]);

            var progress = parseInt(data.loaded / data.total * 100, 10);

            var $el = $file.find('.proc');

            $file.find('.perc').show().html(progress + "<span>%</span>");

            $el.show().css({'width': (100 - progress) + '%'});
         });

      }).on('fileuploadprogressall', function(e, data) {
         var progress = parseInt(data.loaded / data.total * 100, 10);
         $mainprogress.find('.bar').css(
                 'width',
                 progress + '%'
                 );

         if ($mxcounter == 0) {
            $mainprogress.find("strong").text('Upload in progress...');
         }
         if (currPage != 'upload') {
            $mainprogress.show();
            $mainprogress.find("span").text(progress + '%');
            $mxcounter++;
         }else{
            $mainprogress.hide();
         }

         if (data.loaded == data.total) {

            $toolBarcancelbtn.disabletoggle(true);

            if (window.FileReader && Modernizr.draganddrop) {
            } else {
               var clprhtml = $toolBarclearbtn.html();
               $toolBarclearbtn.html("").append('<i class="icon-time icon-2x"></i><span/>').find("span").countdown(9, ' s remaining. (upload history will be cleared)', function() {
                  $(this).trigger("click");
                  $toolBarclearbtn.html(clprhtml);
               });
            }

            completeProg(1);
            confsend = false;
            $toolBarclearbtn.disabletoggle(false);
         }
      }).on('fileuploaddone', function(e, data) {
         $.each(data.result.files, function(index, file) {
            var node = $(data.context.children()[index]);

            node.find(".ctt").popover('hide').popover('disable').off("hover"); //remove popover
            
            if (!file.error) {

               if (file.thumbnail_url) {
                  node.find(".preview").css({'background': 'url("' + file.thumbnail_url + '") 0 0 no-repeat'});
               }

               finalizerogress(node, $sign.success, "success");
               
               if(file.viewable){
                  node.find("a").addClass("uvbtn").data({"id": file.id, "file": file.key+'_'+file.name+'.'+file.extension, "type": file.type.split("/")[0], "title" : data.files[index].name});

               //prepare for quick view of uploaded medi files
                  $("a.uvbtn").vobox();
                  var filelink = 
                  node.find("a").prop("href", file[(screen.width+'x'+screen.height)+'_url']);
               }

               node.find(".preview").stop().fadeTo("slow", 0.6).stop().fadeTo("fast", 1.0);
               node.tooltip({html: true, placement: 'bottom', title: "Uploaded successfully", trigger: "hover"});
            } else {
               node.addClass("error").tooltip({html: true, placement: 'bottom', title: file.error, trigger: "hover"});
               finalizerogress(node, $sign.error, "error");
               $toolBarcancelbtn.disabletoggle(true);
               if (file.error === 'auth') {
                  $filesList.find('button.startstop').each(function() {
                     var $el = $(this);
                     $el.click();
                  });
               }
            }
         });

      }).on('fileuploadfail', function(e, data) {

         $.each(data.files, function(index, file) {
            var node = $(data.context.children()[index]);

            node.delay(0).queue(function(next) {
               var abort = ((data.errorThrown == "abort") ? true : false);

               file.error = (abort ? 'Operation Canceled!' :  'Upload failed.<br>' + data.jqXHR.statusText);

               if (data._progress.loaded > 0){
                     $.ajaxQueue({
                        url: '/?cancelme=true&file=' + data.files[index].name,
                        noind: true,
                        async: false,
                        dataType: 'json',
                        type: 'DELETE'
                     });
               }
                  
               if (abort) {
                  finalizerogress(node, $sign.abort, "abort");
                  completeProg(2);
               } else {
                  finalizerogress(node, $sign.error, "error");
                  completeProg(0);
               }

               node.off("hover").tooltip({html: true, placement: 'bottom', title: file.error, trigger: "hover"});                  
                  
               node.dequeue();
               next();
            });

         });
         if ($('#files').find("li").length <= 1) {
            $toolBarcancelbtn.disabletoggle(true);
         }
         $toolBarclearbtn.disabletoggle(false);


      }).prop('disabled', !$.support.fileInput)
              .parent().addClass($.support.fileInput ? undefined : 'disabled');

      $uploadv.on('dragleave', function(e) {
         var upsselection = $('#upload_select_cont');
         var upsbtn = upsselection.find('.file-input-wrapper');
         upsbtn.find("span").text(upsbtn.data("title"));
         if (!upsbtn.hasClass("mb"))
            upsbtn.stop(true, true).removeClass("btn-large");

         upsselection.find('h3').css('color', '');
         resetuploadArea(true);
      });

      //toolbox
      $maintoolbox.button();

      if(!autostart){
         $toolBarstartbtn.on('click', function(e) {
            var $this = $(this);
            if (!$this.hasClass('disabled')) {
               $this.disabletoggle(true);
               $filesList.find('button.startstop').trigger("click");
            }
            return false;
         });
      }
      
      $toolBarcancelbtn.on('click', function(e) {
         var $this = $(this);
         if (!$this.hasClass('disabled')) {
            $this.disabletoggle(true);
            $filesList.find('button.startstop').each(function() {
               var $el = $(this);
               setTimeout(function() {
                  $el.click();
               }, 80);
            });
         }
         return false;
      });

      $toolBarclearbtn.on('click', function(e) {
         var $this = $(this);
         if ($this.hasClass('disabled'))
            return;
         $this.disabletoggle(true);
         
         if(!autostart)
            $toolBarstartbtn.disabletoggle(true);

         $uploadv.trigger("dragleave");
         resetuploadArea(false, false);
         $filesList.find('button.clear').click();
         return false;
      });

      //android xhr dependency
      //$('#cloupload').fileupload('option', 'maxChunkSize', !navigator.userAgent.match(/Android/i) && 100000);
   });
   function checkUpArea(data) {
      var res = $filesList.find("li").length || 0;

      if ($filesList.find("li").length == 0) {
         resetuploadArea(false, false);
      } else {
      }
      return res;
   }
   function removeNode(node, data, index) {
      node.stop().hide("slow", function() {
         $(this).closest("div").remove();
         if (data)
            delete data.files[index];

         checkUpArea(data);
      });

   }
   function completeProg(result) {
      var rclass = 'progress-info active';
      if (result == 1) {
         addclass = "progress-success";
         mntext = "Upload Completed!";
         setTimeout(function() {
            $mainprogress.hide();
         }, 4000);
         setMeters();
      } else if (result == 2) {
         addclass = "progress-warning";
         mntext = "Upload Canceled!";
      } else {
         addclass = "progress-danger";
         mntext = "Upload Failed!";
      }

      $mainprogress.find(".progress").removeClass(rclass).addClass(addclass);
      $mainprogress.find("strong").text(mntext);
      $mxcounter = 0;
      if (currPage == 'list') {
         handleTabs(currPage, $('a[href="#' + currPage + '"]'));
      }
      $sidebar.unblock();
      $spintarget.spin(false);
      updateprocData({'uploadinprogress': 0});
   }
   function finalizerogress(node, sign, cl) {
      node.addClass(cl).find(".startstop").remove();
      node.find("button.remove").replaceWith(sign.clone(true));
      if(cl == "success"){
         setTimeout(function(){
            node.find(".perc").fadeOut("slow", function(){
               $(this).remove();
            });
         },4000);
      }
   }

   function resetuploadArea(leave, over) {
      setTimeout(function() {
         var to = (over || $('#files').find("li").length) > 0 ? 0.5 : 1;
         if ($typeSelector.hasClass("on") || (!leave && !over))
            $typeSelector.stop().fadeTo("fast", to).removeClass("on");
         if (leave) {
            $typeSelector.removeClass("over");
         }
      }, 300);
   }

   function createaddmorebtntotoolbar(remove) {
      if (remove) {
         $("#addmorebtn").remove();
      } else {
         var btn = $('<button id="addmorebtn" class="btn btn-small addmore"/>');
         btn.html('<i class="icon-plus-sign icon-large"></i> Add More Files');
         btn.on('click', function() {
            $(this).blur();
            $("#fileupload").trigger("click");
         });
         if ($("#addmorebtn").length == 0)
            $("#upsrtoolbox").prepend(btn);
      }
   }