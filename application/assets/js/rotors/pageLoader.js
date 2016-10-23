var pageLoader = {
	overlay: "",
	loadBar: "",
	preloader: "",
	items: new Array(),
	doneStatus: 0,
	doneNow: 0,
      targetEl : "body",
	selectorPreload: "body",
      attachTo : "body",
	ieLoadFixTime: 1000,
	ieTimeout: "",
		
	init: function() {
		if (navigator.userAgent.match(/MSIE (\d+(?:\.\d+)+(?:b\d*)?)/) == "MSIE 6.0,6.0") {
			//break if IE6			
			return false;
		}
		if (pageLoader.selectorPreload == "body") {
			pageLoader.spawnLoader();
			pageLoader.getImages(pageLoader.selectorPreload);
			pageLoader.createPreloading();
		} else {
			$(document).ready(function() {
				pageLoader.spawnLoader();
				pageLoader.getImages(pageLoader.selectorPreload);
				pageLoader.createPreloading();
			});
		}
		
		pageLoader.ieTimeout = setTimeout("pageLoader.ieLoadFix()", pageLoader.ieLoadFixTime);
	},
	
	ieLoadFix: function() {
		var ie = navigator.userAgent.match(/MSIE (\d+(?:\.\d+)+(?:b\d*)?)/) || 0;
            if(ie)
		if (ie[0].match("MSIE")) {
			while ((100 / pageLoader.doneStatus) * pageLoader.doneNow < 100) {
				pageLoader.imgCallback();
			}
		}
	},
	
	imgCallback: function() {
		pageLoader.doneNow ++;
		pageLoader.animateLoader();
	},
	
	getImages: function(selector) {
		var everything = $(selector).find("*:not(script)").each(function() {
			var url = "",
                  cssbg = $(this).css("background-image"),
                  reg = /\s*?[ \t\n]url\('.+?'\);/i;
                  
			if (cssbg != "none" && reg.test(cssbg)  ) {
				var url = $(this).css("background-image");
			} else if (typeof($(this).prop("src")) != "undefined" && $(this).prop("tagName").toLowerCase() == "img") {
				var url = $(this).prop("src");
			}
			
			url = url.replace("url(\"", "");
			url = url.replace("url(", "");
			url = url.replace("\")", "");
			url = url.replace(")", "");
			
			if (url.length > 0) {
				pageLoader.items.push(url);
			}
		});
	},
	
	createPreloading: function() {
		pageLoader.preloader = $("<div></div>").appendTo(pageLoader.selectorPreload);
		$(pageLoader.preloader).css({
			height: "0px",
			width: "0px",
			overflow: "hidden"
		});
		
		var length = pageLoader.items.length; 
		pageLoader.doneStatus = length;
		
		for (var i = 0; i < length; i++) {
			var imgLoad = $("<img></img>");
			$(imgLoad).prop("src", pageLoader.items[i]);
			$(imgLoad).unbind("load");
			$(imgLoad).bind("load", function() {
				pageLoader.imgCallback();
			});
			$(imgLoad).appendTo($(pageLoader.preloader));
		}
	},

	spawnLoader: function() {            
		pageLoader.loadBar = $("<div/>");
            
		$(pageLoader.attachTo).after(pageLoader.loadBar);
		$(pageLoader.loadBar).addClass("pageLoader");
		
		$(pageLoader.loadBar).css({
			position: "absolute",
			top: "0",
			width: "0%"
		});
	},
	
	animateLoader: function() {
		var perc = (100 / pageLoader.doneStatus) * pageLoader.doneNow;
		if (perc > 99) {
			$(pageLoader.loadBar).stop().animate({
				width: perc + "%"
			}, 500, "easeInOutExpo", function() { 
				pageLoader.doneLoad();
			});
		} else {
			$(pageLoader.loadBar).stop().animate({
				width: perc + "%"
			}, 500, "easeInOutExpo", function() { });
		}
	},
	
	doneLoad: function() {
		//prevent IE from calling the fix
		clearTimeout(pageLoader.ieTimeout);
		
		$(pageLoader.loadBar).remove();
	}
}