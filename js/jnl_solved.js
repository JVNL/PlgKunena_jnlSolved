// Solved plug-in by Team Joomla!NL for Kunena 5
// Website: http://www.joomlanl.nl/
// License: GNU General Public License version 3 or later
// Developer: JulianK92 (http://www.julian.wtf/)

'use strict';
document.addEventListener('DOMContentLoaded', function() {
	var closelink = location.href;
	var solutionId = document.querySelector("#jnlSolvedData").attributes["data-solutionpost"].value;	// can be 0, if so topic has no solution
	var topicId = jQuery("#jnlSolvedData").data("topicid");
	var categorieId = jQuery("#jnlSolvedData").data("categoryid");
	
	// add correct message (forum posts) id's to solved button
	var jnlSolvedBtns = document.querySelectorAll('.jnlSolvedBtn');
	jnlSolvedBtns.forEach(function(btn) {
		var kmessage = btn.closest('.kmessage');
		var idElement = kmessage.querySelector('.kmsg-id');
		var id = idElement.getAttribute('id');
		jQuery(btn).attr('data-message-id', id);
	});

	// mark the solution
	if(solutionId > 0) {
		var kmessages = document.querySelectorAll('.kmessage');
		kmessages.forEach(function(kmessage) {
			var idElement = kmessage.querySelector('.kmsg-id');
			var id = idElement.getAttribute('id');
			if(solutionId === id) {
				kmessage.classList.add("jnlSolvedSolutionPost");
				var body = kmessage.querySelector(".kmsg-body");
				body.innerHTML = "<p class='solutionPost'>Deze post is als oplossing voor dit topic aangewezen:</p>" + body.innerHTML;
			}
		});
	}

	// click event checkbox
	jQuery("#cbCorrectMarked").on('change', function() {
		if(jQuery("#cbCorrectMarked:checked").length === 1) {
			jQuery("#btnCloseModal").attr("data-dismiss", "modal").addClass("jnlSolvedCloseable");
		} else {
			jQuery("#btnCloseModal").removeAttr("data-dismiss").removeClass("jnlSolvedCloseable");
		}
	});

	// click event solution button, set completion link
	jQuery(".jnlSolvedBtn").on('click', function(event) {
		var berichtId = jQuery(event.currentTarget).attr("data-message-id"); // message id
		//console.log("Message id: " + berichtId + ", Topic id: " + topicId + ", Category id: " + categorieId);
		closelink = window.location.origin + window.location.pathname + "?option=com_kunena&view=topic&jnltask=solved&berid=" + berichtId + "&catid=" + categorieId + "&id=" + topicId;
	});

	// click event modal element, if solved
	jQuery("#btnCloseModal").on('click', function() {
		if(jQuery(this).hasClass("jnlSolvedCloseable")) {
			location.href = closelink;
		}
	});
	
	// click event reopen topic
	jQuery("#btnReopenModal").on('click', function(event) {
		window.location = window.location.origin + window.location.pathname + "?option=com_kunena&view=topic&jnltask=reopen&catid=" + categorieId + "&id=" + topicId;
	});
});
