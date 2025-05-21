(function($) {
	// Move the Offcanvas Toggle button in the Header
	$(document).ready(function() {
		$("div.g-offcanvas-toggle:not(.offcanvas-toggle-particle)").prependTo($("#g-header"));
	});

	// Fixed Footer
	$(document).ready(function() {
		if( $('.fixed-footer').length ) {
			fixedFooter();

			$(window).resize(function() {
				fixedFooter();
			});
		}

		function fixedFooter() {
			var siteContainer = $("#g-container-site");
			var footerContainer = $(".fixed-footer");
			var footerHeight = $(footerContainer).height();

			$(siteContainer).css("margin-bottom", footerHeight);
		}
	});

	// Header Shadow
	$(document).ready(function() {
		if( $('#g-header').length ) {
			$('<div class="g-header-shadow"></div>').appendTo($("#g-header .g-container"));
		}
	});

	// Copyright Border
	$(document).ready(function() {
		if( $('#g-copyright').length ) {
			$('<div class="g-copyright-border"></div>').prependTo($("#g-copyright .g-container"));
		}
	});

	$(document).ready(function() {
		const currentYear = new Date().getFullYear();
		$('#copyright-year').text(currentYear);
	});

})(jQuery);



/*  jQuery Nice Select - v1.0
    https://github.com/hernansartorio/jquery-nice-select
    Made by Hernán Sartorio  */
!function(e){e.fn.niceSelect=function(){this.hide(),this.each(function(){var s=e(this);if(!s.next().hasClass("nice-select")){s.after('<div class="nice-select '+(s.attr("class")||"")+(s.attr("disabled")?"disabled":'" tabindex="0')+'"><span class="current"></span><ul class="list"></ul></div>');var t=s.next(),i=s.find("option"),n=s.find("option:selected");t.find(".current").html(n.data("display")||n.text()),i.each(function(){var s=e(this).data("display");t.find("ul").append('<li class="option'+(e(this).is(":selected")?" selected":"")+(e(this).is(":disabled")?" disabled":"")+'" data-value="'+e(this).val()+'"'+(s?' data-display="'+s:"")+'">'+e(this).text()+"</li>")})}}),e(document).off(".nice_select"),e(document).on("click.nice_select",".nice-select",function(s){var t=e(this);e(".nice-select").not(t).removeClass("open"),t.toggleClass("open"),t.hasClass("open")?(t.find(".option"),t.find(".focus").removeClass("focus"),t.find(".selected").addClass("focus")):t.focus()}),e(document).on("click.nice_select",function(s){0===e(s.target).closest(".nice-select").length&&e(".nice-select").removeClass("open").find(".option")}),e(document).on("click.nice_select",".nice-select .option:not(.disabled)",function(s){var t=e(this),i=t.closest(".nice-select");i.find(".selected").removeClass("selected"),t.addClass("selected");var n=t.data("display")||t.text();i.find(".current").text(n),i.prev("select").val(t.data("value")).trigger("change")}),e(document).on("keydown.nice_select",".nice-select",function(s){var t=e(this),i=e(t.find(".focus")||t.find(".list .option.selected"));if(32==s.keyCode||13==s.keyCode)return t.hasClass("open")?i.trigger("click"):t.trigger("click"),!1;if(40==s.keyCode){if(t.hasClass("open")){var n=i.nextAll(".option:not(.disabled)").first();n.length>0&&(t.find(".focus").removeClass("focus"),n.addClass("focus"))}else t.trigger("click");return!1}if(38==s.keyCode){if(t.hasClass("open")){var c=i.prevAll(".option:not(.disabled)").first();c.length>0&&(t.find(".focus").removeClass("focus"),c.addClass("focus"))}else t.trigger("click");return!1}if(27==s.keyCode)t.hasClass("open")&&t.trigger("click");else if(9==s.keyCode&&t.hasClass("open"))return!1})}}(jQuery);

(function($) {
	$(document).ready(function() {
		$('.solidres-module select').niceSelect();
		$('#solidres select').niceSelect();                
	});
})(jQuery);