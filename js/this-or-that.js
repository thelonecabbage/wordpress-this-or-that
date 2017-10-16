/*global jQuery*/

"use strict";

jQuery(document).ready(function(){
	if( jQuery("#this-or-that").length === 0){
		return false;
	}
	set_thisorthat_links();
	set_keypress();
});

function set_thisorthat_links(){
	jQuery(".this-or-that-btn").click(function(e){
		var link = jQuery(this).attr("href"),
		    h = jQuery(this).parents("#this-or-that").height();
		e.preventDefault();

		if( jQuery(this).parents(".thisthat_column").is(":animated")){
			return false; //Make sure you have to wait till the previous animation stopped
		}

		/* ANIMATION */

		//fix the height of the container
		jQuery(this).parents("#this-or-that").css({ "min-height": h });

		jQuery(this).parents(".this-or-that_item")
			.addClass('selected')
			.animate({ 
				// opacity: "0",
			}, 400, function(){
				jQuery("#this-or-that").animate({ opacity: 0 }, 200);

				// Do an AJAX request to save the 'favorite', simultaniously with retrieving the 'new' items.
				jQuery("#this-or-that").load(link + " #this-or-that .this-or-that-wrapper", function(){

					jQuery("#this-or-that").animate({ opacity: 1 }, 200);
					set_thisorthat_links(); //make sure the links in the loaded HTML
				});
				
				//jQuery(this).css({height: "auto" });
			});
	});
}

function set_keypress(){
	jQuery("body").keydown(function(e) {
		if(e.keyCode == 37) { // left
			jQuery(".this-or-that_item").eq(0).find("a.this-or-that-btn").first().trigger("click");
		}
		if(e.keyCode == 39) { // right
			jQuery(".this-or-that_item").eq(1).find("a.this-or-that-btn").first().trigger("click");
		}
	});
}