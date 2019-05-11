// Solved plug-in by Team Joomla!NL for Kunena 5
// Website: http://www.joomlanl.nl/
// License: GNU General Public License version 2 or later
// Developer: JulianK92 (http://www.julian.wtf/)

'use strict';
document.addEventListener('DOMContentLoaded', function() {
	var closelink = location.href;
	var solutionId = jQuery("#jnlSolvedData").data("solutionpost"); // can be 0, if so topic has no solution
	var topicId = jQuery("#jnlSolvedData").data("topicid");
	var categorieId = jQuery("#jnlSolvedData").data("categoryid");
	var solvedTopicText = Joomla.JText._('PLG_KUNENA_JNLSOLVED_TOPIC_SOLVED_TITLE');

	// language strings
	jQuery(".PLG_KUNENA_JNLSOLVED_BUTTON_REOPEN_TEXT").text(Joomla.JText._('PLG_KUNENA_JNLSOLVED_BUTTON_REOPEN_TEXT'));
	jQuery(".PLG_KUNENA_JNLSOLVED_BUTTON_SOLVED_TEXT").text(Joomla.JText._('PLG_KUNENA_JNLSOLVED_BUTTON_SOLVED_TEXT'));
	jQuery(".PLG_KUNENA_JNLSOLVED_YES").text(Joomla.JText._('JYES'));
	jQuery(".PLG_KUNENA_JNLSOLVED_NO").text(Joomla.JText._('JNO'));
	
	// add correct message (forum posts) id's to solved button
	jQuery(".jnlSolvedBtn").each(function(key, value){
		var id = jQuery(value).closest(".vertical-layout-message").find(".kmessage-post-header a").attr('id');
		jQuery(value).attr('data-berichtid', id);
	});

	// mark the solution
	if(solutionId > 0) {
		jQuery(".vertical-layout-message").find(".kmessage-post-header a").each(function(key, value) {
			if(solutionId === parseInt(jQuery(value).attr("id"))) {
				var solutionPost = jQuery(value).closest(".vertical-layout-message");
				jQuery(solutionPost).addClass("jnlSolvedSolutionPost");
				jQuery(solutionPost).find(".kmessage").prepend("<p class=oplossing>" + Joomla.JText._('PLG_KUNENA_JNLSOLVED_SOLUTION_POST_TEXT') + "</p>");
			}
		});
	}

	// click event checkbox
	jQuery("#cbCorrectMarked").on('change', function() {
		if(jQuery("#cbCorrectMarked:checked").length === 1)
			jQuery("#btnCloseModal").attr("data-dismiss", "modal").addClass("jnlSolvedCloseable");
		else
			jQuery("#btnCloseModal").removeAttr("data-dismiss").removeClass("jnlSolvedCloseable");
	});

	// click event solution button, set completion link
	jQuery(".jnlSolvedBtn").on('click', function(event) {
		var berichtId = jQuery(event.currentTarget).attr("data-berichtid"); // message id
		console.log("Message id: " + berichtId + ", Topic id: " + topicId + ", Category id: " + categorieId);
		closelink = "index.php?option=com_kunena&view=topic&jnltask=solved&berid=" + berichtId + "&catid=" + categorieId + "&id=" + topicId;
	});

	// click event modal element, if solved
	jQuery("#btnCloseModal").on('click', function() {
		if(jQuery(this).hasClass("jnlSolvedCloseable"))
			location.href = closelink;
	});
	
	// click event reopen topic
	jQuery("#btnReopenModal").on('click', function(event) {
		window.location = "index.php?option=com_kunena&view=topic&jnltask=reopen&catid=" + categorieId + "&id=" + topicId;
	});
});
