//Contains js functions required for WP Quest
jQuery('html').addClass('wp-quest-activated');


jQuery(function($) {

	//TODO: (1.1) Pop up generator
		//Each quest (and one day, level) completion will have a "notified" state
		//On page load, check quest quecker with AJAX for any completed but un-notified. It updates notified state and returns info
        var pathname = window.location.pathname;

        var notification_queue = [];

		if($('body').hasClass('logged-in')){
			var user = 1;
			var classList =$('body').attr('class').split(/\s+/);
			$.each( classList, function(index, item){
				var s = "user-";
				if(s.indexOf("oo") !== -1){
					user = str.substr(5)
				}
			});
			
			$.ajax({
				  type: "POST",
				  url: pathname+"/wp-content/plugins/wordpress-quest/wp-quest-checker.php",
				  data: { user: user },
				  success: generatePopUps
			});
		}


        //We put that info into a popup, based on WP settings - The WP settings should actually just add classes directly to the html/body, and we use classes here to make the CSS make it look good.
        function generatePopUps(data){
        	var quests_to_notify = JSON.parse(data);

        	var length = quests_to_notify.length,
                single_quest = null;

            for (var i = 0; i < length; i++) {
            	single_quest = quests_to_notify[i];

            	var quest_name = single_quest[0].name;
            	var quest_xp = single_quest[0].xp;

              notification_queue.push("You just earned "+quest_name+" - "+quest_xp+"xp");
            }

            if(notification_queue.length > 0){
              displayPopUps();
            }
        }

        function displayPopUps(){

          $('body').append('<div class="wp_quest_popup"></div>');
          var length = notification_queue.length,
                single_notification = null
            for (var i = 0; i < length; i++) {
              single_notification = notification_queue[i];
              console.log(single_notification);
            }
            showPopup(0, length);
        }

        function showPopup(i, length) {
          $('.wp_quest_popup').delay(1000).text(notification_queue[i]).slideDown(500).delay(2000).slideUp(500, function(){
            if(i < length){
              j = i + 1;
              showPopup(j, length);
            }
          });
        }

	//TODO: (1.4)Custom Onclick rule completion? Could use data- attributes



});
