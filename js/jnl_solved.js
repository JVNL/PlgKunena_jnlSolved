// Solved plug-in by Team Joomla!NL for Kunena 5
// Website: http://www.joomlanl.nl/
// License: GNU General Public License version 3 or later
// Developer: JulianK92 (http://www.julian.wtf/)

'use strict';

function attachLanguageStrings() {
	jQuery(".PLG_KUNENA_JNLSOLVED_BUTTON_REOPEN_TEXT").text(Joomla.JText._('PLG_KUNENA_JNLSOLVED_BUTTON_REOPEN_TEXT'));
	jQuery(".PLG_KUNENA_JNLSOLVED_BUTTON_SOLVED_TEXT").text(Joomla.JText._('PLG_KUNENA_JNLSOLVED_BUTTON_SOLVED_TEXT'));
	jQuery(".PLG_KUNENA_JNLSOLVED_YES").text(Joomla.JText._('JYES'));
	jQuery(".PLG_KUNENA_JNLSOLVED_NO").text(Joomla.JText._('JNO'));
}

function markSolution(solutionId) {
	if(solutionId > 0) {
		jQuery(".vertical-layout-message").find(".kmessage-post-header a").each(function(key, value) {
			if(solutionId === parseInt(jQuery(value).attr("id"))) {
				var solutionPost = jQuery(value).closest(".vertical-layout-message");
				jQuery(solutionPost).addClass("jnlSolvedSolutionPost");
				jQuery(solutionPost).find(".kmessage").prepend("<p class='solutionPost'>" + Joomla.JText._('PLG_KUNENA_JNLSOLVED_SOLUTION_POST_TEXT') + "</p>");
			}
		});
	}
}

function attachMessageIdToSolvedButton() {
	jQuery(".jnlSolvedBtn").each(function(key, value){
		var id = jQuery(value).closest(".vertical-layout-message").find(".kmessage-post-header a").attr('id');
		jQuery(value).attr('data-message-id', id);
	});
}

document.addEventListener('DOMContentLoaded', function() {
	var closeLink = location.href; 
	var solutionId = jQuery("#jnlSolvedData").data("solutionpost"); // can be 0, if so topic has no solution
	var topicId = jQuery("#jnlSolvedData").data("topicid");
	var categoryId = jQuery("#jnlSolvedData").data("categoryid");

	attachLanguageStrings();
	markSolution(solutionId);
	attachMessageIdToSolvedButton();

	// Attach click event checkbox
	jQuery("#cbCorrectMarked").on('change', function() {
		if(jQuery("#cbCorrectMarked:checked").length === 1)
			jQuery("#btnCloseModal").attr("data-dismiss", "modal").addClass("jnlSolvedCloseable");
		else
			jQuery("#btnCloseModal").removeAttr("data-dismiss").removeClass("jnlSolvedCloseable");
	});

	// Attach click event solution button, set completion link
	jQuery(".jnlSolvedBtn").on('click', function(event) {
		var messageId = jQuery(event.currentTarget).attr("data-message-id"); // message id
		closeLink = "index.php?option=com_kunena&view=topic&jnltask=solved&messageId=" + messageId + "&categoryId=" + categoryId + "&id=" + topicId;
	});

	// Attach click event modal element, if solved
	jQuery("#btnCloseModal").on('click', function() {
		if(jQuery(this).hasClass("jnlSolvedCloseable"))
			location.href = closeLink;
	});
	
	// Attach click event reopen topic
	jQuery("#btnReopenModal").on('click', function(event) {
		window.location = "index.php?option=com_kunena&view=topic&jnltask=reopen&categoryId=" + categoryId + "&id=" + topicId;
	});
});
