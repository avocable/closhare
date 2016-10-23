/**
 * script
 * @package      CloShare
 */

//Custom touches
//Dom please!
$(function() {
    //General calls.
    $('.btn').button();
    $('.button-loading').click(function() {
        $(this).button('loading');
    });
 $viewPort = $('#viewport');
 $sidebar  = $("#sidebar");
 $filesSideBarTbar = $("#filesdropdown");
 $norequestArr = new Array("login","register","recover","view");
 
 $(document).ajaxComplete(function( event, xhr, settings ) {
    if(settings.dataType == 'json'){
    var data = $.parseJSON(xhr.responseText);
    if(data.auth == 0){
        $viewPort.unblock();
        $.blockUI({message: 'You need to login to continue...Redirecting...'});
        window.location = '';
    }
    }
 });

});

//start for view page
$(document).ready(function() {
    viewport(); //viewport init

    //while window being resized.
    $(window).resize(function() {
        viewport.call();
    });

    $('#signoutb').on("click", function() {
        window.location.href = "?logout=true";
    });

    $('a[data-toggle="tab"]').on('shown', function(e) {
        //$(e.target ).find(".viewbox").find("div:first").hide(); // previous tab
        //$(e.relatedTarget).find(".viewbox").find("div:first").hide(); // previous tab
        var $el = $(this);
        if ($el.data('callback') != "") {
            callbackCall($el);
        }
        if ($el.data('persist') === "ok") {
            $sidebar.unblock();
            sidebarToggle(".upload:not(.dyn)", ".files:not(.dyn)");
        }        

    });
});


$(function() {
    
  $('.linksc').each(function(){
    $(this).data( 'procs', {
      cache: {
        // If url is '' (no fragment), display this div's content.
        '': $(this).find('tab-pane.active')
      }
    });
  });    
    $viewPort.data('procs', {
      cache: {
        // If url is '' (no fragment), display this div's content.
        '': $(document).find('li.active a')
      }
    });

    $(document).on('click', 'a.rpsve', function(e) {

        e.preventDefault();
        $this = $(this);
        
        var state = {},       
        
        id  = "list"//$this.closest('.linksc').attr('id'),
        
        idx = $this.attr('href').replace(/^#/, '');
                
        state[ id ] = idx;
        $.bbq.pushState(state); 
        
        return false;
    });
    
    $(document).on('click', 'a.pg', function(e) {

        e.preventDefault();
        $this = $(this);
        
        var state = {},       
        
        id  = "page",
        
        idx = $this.attr('href').replace(/^#/, '');
        
      
        state[ id ] = idx;
        $.bbq.pushState(state); 
        
//        if(idx !== 'list'){
//             $.bbq.removeState(['list']);
//        }                
        return false;
    });    

    $(window).bind('hashchange', function(e) {
        
        //link containers
        //console.log($.param.fragment());
        var page = $.bbq.getState("page");
        
        var list = $.bbq.getState("list") || 0;
        
        if(list){
            $element = $(document).find('a[href="#' + list + '"]');
            target = list;
        }else{
            $element = $(document).find('a[href="#' + page + '"]');
            target = page;
        }
        if(list && page != 'list'){
             $.bbq.removeState(['list']);
             return;
        } 
        
        
        if((!page && !location.hash) || $(document).find('a[href="#' + page + '"]').length === 0){
            $element = $(document).find("li.active:first a")
            page = $element.attr('href').replace(/^#/, '');
            state = {}
            state["page"] = page;
            $.bbq.pushState(state);
            return;
        }
        //$element.trigger("click");
        if($.bbq.getState("view") == undefined && ($.inArray(page, $norequestArr) === -1) )
            handleLinks(target,$element,false);
        else
             handleLinks(target,$element,true);
        
        //store current page for after use
        currPage = $.bbq.getState(this.id, true).page;
        //store current folder for after use
        currDir = $.bbq.getState(this.id, true).list;
        
//        if(currPage == 'list'){
//            sidebarToggle(".files:not(.dyn)", ".upload:not(.dyn)");
//        }else if(currPage == 'upload'){
//            sidebarToggle(".upload:not(.dyn)", ".files:not(.dyn)");
//        }else{
//            $sidebar.blockit();            
//        }
        
    });
    $(window).trigger('hashchange');    
});

$(function() {
    
    $(document).bind('drop dragover', function (e) {
            e.preventDefault();
            return;
    });
    
    $("#uploadpbtn").bind('drop dragover', function (e) {
            $(this).trigger("click");
    });
    
});
//functions

function doPageLoad(idx, $el, msg) {

    var msg = $el.data('load-txt') || $("#list").data("load-txt"),
            $target = $('.viewbox.active').first();
    if ($el.data('persist') != "ok") {
        $.ajaxQueue({
            url: '?' + idx + '&' + getBrowserSupportParamsUpload(),
            type: 'GET',
            dataType: 'json',
            cache: false,
            beforeSend: function() {
               $viewPort.blockit({message: msg}); //no need so far now!
            },
            success: function(data) {

                if ($el.data('persist') == "yes") {
                    $el.data("persist", "ok");
                }

                var html = data.html || data;
                
                if($el.hasClass("folder")){
                var cont = $('<div id="'+idx+'"/>');
                
                cont.html(html);
                
                $target.after(cont);
                }else{
                    $target.html(html);
                }
                var event = jQuery.Event('click');
                event.target = $(document).find('a.rpsve')[0];

                if (data.script) {
                    $.getScript(data.script, function(data, textStatus, jqxhr) {
                        $viewPort.unblock();
                    });

                } else {
                    $viewPort.unblock();
                }
                if ($el.data('callback') !== "") {
                    callbackCall($el);
                }
                if (data.acall) {
                    $.each(data.acall, function(index, obj) {
                        window[obj.back](data.acall[index]);
                    });
                }
            }
        });
    }
}
function handleTabs(idx, $el, cached){
    
    //console.log("tab link");
    $el.tab('show');
    
    //$el = $('.linksc').find("li.active a");
    if(!cached){
        doPageLoad(idx, $el);
    }else{
        setTimeout(function(){
            $viewPort.unblock();
        },200);        
    } 
    return
}

function handleLinks(idx, $el, cached) {

    if ($el.attr("data-toggle") == "tab") {
        
        handleTabs(idx, $el, cached);

    } else {
        var temp = idx.split("|");
        var slug = temp[0],
            dir  = temp[1],
            parent = temp[2];
            doPageLoad('list='+slug+'&dir='+dir+'&parent='+parent, $el);
    }
}

function viewport(fitElements) {
    var $mcontent = $viewPort,
    viewportH = parseInt($(window).height()) - 171;
    if(fitElements){
        $.each(fitElements, function(index,item){
            var childT = parseInt($(item.element).css("padding-top"))+parseInt($(item.element).css("padding-bottom"));
            $(item.element).height(parseInt(viewportH-parseInt(childT)));
        });
    }else{
    $mcontent.height(viewportH);
    var $child = $mcontent.find(".viewbox.active"),
    childT = parseInt($child.css("padding-top"))+parseInt($child.css("padding-bottom"));
    $child.stop().height(parseInt(viewportH - childT));
    }
}

function uploadPage() {
    if($(".file-input-wrapper").length == 0)$("input[type=file]").bootstrapFileInput();
    $sidebar.blockit({"message":"", overlayCSS:{'cursor':'not-allowed'}});
    $("#breadcrumb").remove();    
    var items = {items:{element: "#upload"}};
    viewport(items);
}

function listPage() {
    $sidebar.blockit({"message":"", overlayCSS:{'cursor':'not-allowed'}});
    makebreadcrumb(false);
    var items = {items:{element: "#list"}};
    viewport(items);
}

function sidebarToggle(sel, hel){
    $sidebar.find(hel).slideUp(300, function(){
       $sidebar.find(sel).slideDown(400);
    });
}

function makeUploadSideBarNav(data){
    $(data.target).html(data.html);
    $(data.target+" select").on('change', function(){
        location.hash = $(this).val();
    }).selectpicker();
    $sidebar.unblock();
    sidebarToggle(".upload:not(.dyn)", ".files:not(.dyn)");
}

function makelistSideBarNav(data){
    var target = $(data.target),
        cont   = $(data.container),
        html   = $('<option/>');
    
    $.each(data.obj, function(index, value){
       var clone = html.clone(true);
       $.each(value.attr, function(key, val){
          clone.attr(key, val);
       });
       clone.text(value.txt);
       cont.append(clone);
    });

    target.find(".inner").html(cont);
    
    target.find("select").on('change', function(){
       location.hash = $(this).val();
    }).selectpicker();
    target.find(".inner").append(data.inject);
    
    setSideBarEvents(target);
    $sidebar.unblock();
    sidebarToggle(".files:not(.dyn)", ".upload:not(.dyn)");
}

function setSideBarEvents(target){
      target.find('.btn.act').click(function(e) {
      e.preventDefault();
      var $this = $(this);
      if($this.hasClass('disabled')) return;
      createBox($this);
      console.log("Btn Act clickced");
   }); 
}
function makebreadcrumb(obj){
        
        if(!obj && !$(document).data("breadcrumb")){
            return;
        }
        else if(!obj){
            obj = $(document).data("breadcrumb")
        }
        var $breadcrumbCont = $('<ul id="breadcrumb" class="alert alert-info explain pull-left navbar breadcrumb" />'),
            $item = $('<li><a href=""></a></li>'),
            $span = $('<span class="divider"> <i class="icon-caret-right"></i> </span>');
            $goback = $('<li><a href="javascript:history.back();"><i class="icon-circle-arrow-left icon-large"></i></a></li><li class="divider-vertical"></div>');
        
        $.each(obj.links, function(index,val){
            var clone = $item.clone();
        
            clone.find("a").html(val.title).attr("href", val.to).on('click');
            
            if(val.active)
                clone.addClass("active well");
            else
                clone.append($span.clone());
            
            if(val.icon)
                clone.find("a").prepend($('<i class="'+val.icon+'"/>'));            
       
            $breadcrumbCont.append(clone);
        });
        if(currDir){
            $breadcrumbCont.prepend($goback);
        }
        
        if($("#breadcrumb").length > 0){
            $("#breadcrumb").remove(); 
            }
//        else
            $viewPort.prepend($breadcrumbCont).on("click");
            
            $(document).data("breadcrumb", obj);
}

function callbackCall(element) {
    if(element.data('callback')){
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
function updateprocData(newobj){
    var obj = $viewPort.data("procs");
    $.extend(true, obj.cache, newobj);
    $viewPort.data("procs", obj);
}

//Check browser
//Upload
function getBrowserSupportParamsUpload(){
    var params = {
        dragdrop: 0
    }
    if(window.FileReader && Modernizr.draganddrop){
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
//return file extension from filename
function filenametoext(filename){
    var ext = /[.]/.exec(filename);
    return (ext && ext !== undefined && ext !== null) ? /[^.]+$/.exec(filename) : null;
}
//return file extension from mime/type
function extbymime(mime, filename,retmime) {
    var obj = {
        323 : "text/h323",
        '7z'  : "application/x-7z-compressed",
        aac : "audio/x-aac",
        acx : "application/internet-property-stream",
        ai  : "application/postscript",
        aif : "audio/x-aiff",
        aifc: "audio/x-aiff",
        aiff: "audio/x-aiff",
        asf : "video/x-ms-asf",
        asr : "video/x-ms-asf",
        asx : "video/x-ms-asf",
        au  : "audio/basic",
        avi : "video/x-msvideo",
        axs : "application/olescript",
        bas : "text/plain",
        bcpio: "application/x-bcpio",
        bin  : "application/octet-stream",
        bmp  : "image/bmp",
        cat  : "application/vnd.ms-pkiseccat",
        cdf  : "application/x-cdf",
        cer  : "application/x-x509-ca-cert",
        'class': "application/octet-stream",
        clp  : "application/x-msclip",
        cmx  : "image/x-cmx",
        cod  : "image/cis-cod",
        cpio : "application/x-cpio",
        crd  : "application/x-mscardfile",
        crl  : "application/pkix-crl",
        crt  : "application/x-x509-ca-cert",
        csh  : "application/x-csh",
        css  : "text/css",
        dcr  : "application/x-director",
        der  : "application/x-x509-ca-cert",
        dir  : "application/x-director",
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
        ins: "application/x-internet-signup",
        isp: "application/x-internet-signup",
        jfif: "image/pipeg",
        jpe: "image/jpeg",
        jpeg: "image/jpeg",
        jpg: "image/jpeg",
        js: "application/x-javascript",
        latex: "application/x-latex",
        lha: "application/octet-stream",
        lsf: "video/x-la-asf",
        lsx: "video/x-la-asf",
        lzh: "application/octet-stream",
        m13: "application/x-msmediaview",
        m14: "application/x-msmediaview",
        m3u: "audio/x-mpegurl",
        man: "application/x-troff-man",
        mdb: "application/x-msaccess",
        me: "application/x-troff-me",
        mht: "message/rfc822",
        mhtml: "message/rfc822",
        mid: "audio/mid",
        mny: "application/x-msmoney",
        mov: "video/quicktime",
        movie: "video/x-sgi-movie",
        mp2: "video/mpeg",
        mp3: "audio/mpeg",
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
        p10: "application/pkcs10",
        p12: "application/x-pkcs12",
        p7b: "application/x-pkcs7-certificates",
        p7c: "application/x-pkcs7-mime",
        p7m: "application/x-pkcs7-mime",
        p7r: "application/x-pkcs7-certreqresp",
        p7s: "application/x-pkcs7-signature",
        pbm: "image/x-portable-bitmap",
        php: "php File",
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
        src: "application/x-wais-source",
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
        xml: ['application/xml', 'text/xml'],
        xla: "application/vnd.ms-excel",
        xlc: "application/vnd.ms-excel",
        xlm: "application/vnd.ms-excel",
        xls: "application/vnd.ms-excel",
        xlt: "application/vnd.ms-excel",
        xlw: "application/vnd.ms-excel",
        xof: "x-world/x-vrml",
        xpm: "image/x-xpixmap",
        xwd : "image/x-xwindowdump",
        z   : "application/x-compress",
        zip : "application/zip",
        file: "unknown/File"
    };
    
    var trytxtp = filenametoext(filename);
    var tryext = (trytxtp && trytxtp !== null && filenametoext(filename)[0] !== null) ? filenametoext(filename)[0].toLowerCase() : 'file';
    if(retmime){
        //console.log(tryext)
        if(obj[tryext] === undefined){
            var rt = tryext+' File';
            trytext = 'file';
        }else{
            var rt = obj[tryext];
        }
        return rt ;
    }else{
    var extension = {result : tryext};
    if(obj[tryext] === undefined){
        extension.result = "file";
    }else{
        extension.result = tryext;
    }
    return extension;
    }
}

//make default previews for files which couldnt be rendered.
function makefilepreview(mime, container,filename,w,h) {
    var ext = extbymime(mime,filename);  
    var url = ASSURI+'icons/file_extension_'+ext.result+'.png';
    var img =  new Image();
    img.src = url;
    if(Modernizr.canvas){
        var prv = ImageToCanvas(img,w,h);
    }else{
        var prv = responsiveImagePreview(img,w,h);
    }
    return prv;
}

//convert images to canvas objects
function ImageToCanvas(img,w,h) {
    var canvas = document.createElement("canvas");
    var context = canvas.getContext('2d');
    canvas.width = parseInt(w);
    canvas.height = parseInt(h);
    img.onload = function() {
        context.drawImage(img, 1, parseInt(h-34));
      };
    return canvas;
}
//return responsive preview image obj
function responsiveImagePreview(img,w,h){
    //var $cont = $('<div style="width: '+w+'px; height: '+ h +'px; position: absolute; top:0; left:0;z-index:2" />');
    return $(img).css({'position': 'absolute', 'left' : 2, 'bottom' : 2});
    
    //return $cont;
}
//formatfilesize
function formatFileSize(bytes) {
    if (typeof bytes !== 'number') {
        return '';
    }
    if (bytes >= 1000000000) {
        return (bytes / 1000000000).toFixed(2) + ' GB';
    }
    if (bytes >= 1000000) {
        return (bytes / 1000000).toFixed(2) + ' MB';
    }
    return (bytes / 1000).toFixed(2) + ' KB';
}

//popover autoplacement
function popoverautoplacement(tip, element) {
        var offset = $(element).offset();
        height = $(document).outerHeight();
        width = $(document).outerWidth();
        vert = 0.5 * height - offset.top;
        vertPlacement = vert > 0 ? 'bottom' : 'top';
        horiz = 0.5 * width - offset.left;
        horizPlacement = horiz > 0 ? 'right' : 'left';
        placement = Math.abs(horiz) > Math.abs(vert) ?  horizPlacement : vertPlacement;
        return placement;
}
//update content dynamicaly
function updateContent(url, target, content) {
    if (content) {
        target.html(content);
    } else {
        $.ajaxQueue({
            url: '?' + href + '&' + getBrowserSupportParamsUpload(),
            type: 'GET',
            dataType: 'json',
            cache: false,
            beforeSend: function() {
                $viewPort.blockit({message: msg});
            },
            success: function(data) {
                if ($el.data('persist') == "yes") {
                    $el.data("persist", "ok");
                }
            }
        });
    }
}

function renderResponse(response, xcase, target){
    switch (xcase){
        case 'html':
            $(target).html(response);
            break;
        case 'fn':
            window['']();
            break;
    }
}
function handleBoxFormAjaxResponse(responseText, statusText, xhr, $form){
    var clsbtn = $form.closest("div.modal").find(".modal-footer a.btn").first(),
        subbtn = clsbtn.next("a");
    
    if(responseText.result){
        $form.html($('<div class="control-group span3" style="margin: 0; float:none"><div class="alert alert-success">'+responseText.message+'</div></div>'));
        subbtn.remove();
        clsbtn.text("Close");
        window.location.replace('#'+$.param.fragment()+'&reload');
    }else{
        var alert = $form.find(".alert");
            
        if($form.find(".fterr").length == 0){
            cloth = $('<div class="control-group span3 fterr" style="margin: 0"/>');
            $form.prepend(cloth);
        }else{
            cloth = $form.find(".fterr");
        }
        cloth.append(alert);
        alert.show().find("div:first").html(responseText.message);
        subbtn.button("reset");
    }
}
function handleboxForms($element, $form) {
    var options = { 
        //target:        $form,   
        //beforeSubmit:  console.log("sending"),  // pre-submit callback 
        success:       handleBoxFormAjaxResponse,  // post-submit callback 
 
        // other available options: 
        //url:       url         // override for form's 'action' attribute 
        //type:      type        // 'get' or 'post', override for form's 'method' attribute 
        dataType:  'json'        // 'xml', 'script', or 'json' (expected server response type) 
        //clearForm: true        // clear all form fields after successful submit 
        //resetForm: true        // reset the form after successful submit 
 
        // $.ajax options can be used here too, for example: 
        //timeout:   3000 
    };     
    
    $form.ajaxSubmit(options);
    console.log("submitted")
}

function createBox($this, type) {
    var action = $this.data("action"),
        $box   = $("#actionbox div.modal:first").clone(),
        title  = $this.data("title") || 0,
        bid    = $this.data("id"),
        boxid  = "box_" + action,
        $form  = $box.find("form:first"),
        $html  = '<div class="alert alert-error dnone"><div></div></div>\n\
                  <input class="dnone" type="hidden" name="value" value="' + bid + '" />\n\
                  <input class="dnone" type="hidden" name="action" value="' + action + '">', //static sends
        labels = (($this.data("labels") !== undefined) ? $this.data("labels").split("_") : '');
        content=  $this.data("content") || 0;

    switch (action.split("_")[1]) {
        case 'delete':
            $actbtn = $('<a href="" class="btn btn-danger go">Delete</a>');
            break;
        case 'create':
            $html+= '<div class="control-group">\n\
                     <input type="text" name="name" class="span3" placeholder="' + $this.data("placeholder") + '">\n\
                     </div>\n\
                     <div class="control-group last">\n\
                     <input type="text" name="desc" class="span3" placeholder="' + $this.data("desc") + '">\n\
                     </div>';
            $actbtn = $('<a href="" class="btn btn-primary go">Create</a>');
            break;
        case 'edit':
            $html+= '<div class="control-group">\n\
                     <label for="foname">'+labels[0]+'</label>\n\
                     <input type="text" id="foname" name="name" class="span3" value="' + $this.data("placeholder") + '">\n\
                     </div>\n\
                     <div class="control-group last">\n\
                     <label for="fodesc">'+labels[1]+'</label>\n\
                     <input type="text" id="fodesc" name="desc" class="span3" value="' + $this.data("desc") + '">\n\
                     </div>';
            $actbtn = $('<a href="" class="btn btn-primary go">Save</a>');
            break;
        case 'copy':
            $html+= '<div class="control-group">\n\
                     <label for="foname">'+labels[0]+'</label>\n\
                     <input type="text" id="foname" name="name" class="span3" value="' + $this.data("placeholder") + '">\n\
                     </div>\n\
                     <div class="control-group last">\n\
                     <label for="fodesc">'+labels[1]+'</label>\n\
                     <input type="text" id="fodesc" name="desc" class="span3" value="' + $this.data("desc") + '">\n\
                     </div>';
            $actbtn = $('<a href="" class="btn btn-primary go">Save</a>');
            break;
        case 'move':
            $html+= '<div class="control-group">\n\
                     <label for="foname">'+labels[0]+'</label>\n\
                     <input type="text" id="foname" name="name" class="span3" value="' + $this.data("placeholder") + '">\n\
                     </div>\n\
                     <div class="control-group last">\n\
                     <label for="fodesc">'+labels[1]+'</label>\n\
                     <input type="text" id="fodesc" name="desc" class="span3" value="' + $this.data("desc") + '">\n\
                     </div>';
            $actbtn = $('<a href="" class="btn btn-primary go">Save</a>');
            break; 
        case 'share':
            $html+= '<div class="control-group">\n\
                     <label for="foname">'+labels[0]+'</label>\n\
                     <input type="text" id="foname" name="name" class="span3" value="' + $this.data("placeholder") + '">\n\
                     </div>\n\
                     <div class="control-group last">\n\
                     <label for="fodesc">'+labels[1]+'</label>\n\
                     <input type="text" id="fodesc" name="desc" class="span3" value="' + $this.data("desc") + '">\n\
                     </div>';
            $actbtn = $('<a href="" class="btn btn-primary go">Save</a>');
            break;        
        
    }

    //set the box id
    $box.attr("id", boxid);
    $form.attr("id", "form_" + action).data("target", $this.attr("id")).append($html);
    if(title)
    $box.find(".box_title").html(title);
    else
    $box.find(".modal-header").remove();

    if(content)
        $form.append(content);

    //bind click event to the $actbtn to submit the form;
    $box.on('click', 'a.go', function() {
        if(!$(this).hasClass("disabled")){
            handleboxForms($(this), $form);
            $(this).button("loading");
        }
            return false;
    });

    $box.find(".modal-footer").append($actbtn);


    if (!title) {
        $box.find(".modal-header").remove();
    }
    
    $box.modal().on('shown', function () {
        $box.find("input:visible:first").focus();
    });
    
    Modernizr.load({
        test: Modernizr.input.placeholder,
        nope: ASSURI+'js/fallback/placeholder.js'      
    });
    
}

//FORMS
$(function() {

    prettyPrint();

    $(".form-horizontal input").jqBootstrapValidation(
            {
                preventSubmit: true,
                submitError: function($form, event, errors) {
                    var sbtn = $form.find("button");
                    sbtn.button('reset');
                    $('html, body').animate({
                        scrollTop: $(".container").offset().top
                    }, 350, 'linear', function() {
                    });
                },
                filter: function() {
                    return $(this).is(":visible");
                }
            }
    );

    $("form").each(function(e, el) {
        var $this = $(this);
        if ($this.find(".button-loading").length > 0) {
            $this.on("submit", function() {
                $this.find(".button-loading").button("loading");
            });
        }
    });

// Support for AJAX loaded modal window.
// Focuses on first input textbox after it loads the window.
$('[data-toggle="modal"]').on('click', function(e) {
        e.preventDefault();
        var $this = $(this);
        var url = $this.attr("href");
        var identity = $this.prop("href").split("=")[1];
        if (url.indexOf('#') == 0) {
            $(url).modal('open');
        }
        else if ($('[data-modal-id="' + identity + '"]').length > 0) {
            $('[data-modal-id="' + identity + '"]').modal("show");
        } else {
            $this.button("loading");
            $.getJSON("",{ rc : url}, function(data) {
                $('<div class="modal hide fade" data-modal-id="' + identity + '">' + data + '</div>').modal();
            }).success(function() {
                $this.button("reset");
                $('input:text:visible:first').focus();
            });
        }
    });
    return false;

});

//sidebar folder actions
$(function() {

   //file-folder hover-click functions
   var $pTarget = '';
   var $foacts = $("#foacts");
   $viewPort.on('click', '.selectable', function(e) {
       e.preventDefault();
       var $this = $(this),
       $link = $this.find("a");

       var $chkbox = $link.find("input[type=checkbox]");
       var status  = !$chkbox.attr("checked");
       $chkbox.prop("checked", status);
       $link.toggleClass("selected");
       var totalChecked = $("input.fileOptCheck:checked").length;
       
       if(totalChecked){
           $filesSideBarTbar.removeClass("nottbor").slideDown("fast")
                   .find("h5").text(totalChecked+' file(s) selected.');
           $filesSideBarTbar.find("button")
       }else{
           $filesSideBarTbar.slideUp("fast", function(){
               $(this).addClass("nottbor");
           });
       }
       
       $.bbq.pushState({"view": $link.attr("href").replace(/^#/, '')});
       return false;
       
   }).on('hover', 'li.preview', function(e){
       e.preventDefault();
       
       var $this = $(this),
       $link = $this.find("a");
       
       //if($this.hasClass("folder")){
           if($this.find("div.foacts").length === 0){
               var clone = $foacts.clone();
               clone.attr("id", "foacts_"+$link.attr("href").replace(/^#/, ''));
               $this.append(clone);
           }
           $this.find(".foacts").slideToggle(60);
       //}   
       
   });
   
   $(document).on('click', '.foacts .btn', function(e) {
      e.preventDefault();
      $this = $(this);
      var action = $this.data("action");
      var data = $this.closest(".foacts").attr("id").split("|");
      
      
      console.log("Action Icon clicked");
      
   });

});
   
///for after use
$(function() {
   $("#folderIconSelect").selectpicker(); //user interface....
    
});
