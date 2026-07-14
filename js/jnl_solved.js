// Solved plug-in by Team Joomla!NL for Kunena 6
// Website: https://www.joomlanl.nl/
// License: GNU General Public License version 3 or later

'use strict';

document.addEventListener('DOMContentLoaded', function () {
	var body = document.body;

	// Plugin only injects these on a Kunena topic view; if they're missing
	// we're not on a page this plugin should touch.
	if (!body || typeof body.dataset.jnlTopicid === 'undefined') {
		return;
	}

	var solutionId = body.dataset.jnlSolutionpost; // '0' if topic has no solution yet
	var topicId = body.dataset.jnlTopicid;
	var categoryId = body.dataset.jnlCategoryid;
	var csrfToken = body.dataset.jnlToken;
	var closelink = location.href;

	// Attach the right message (forum post) id to each solved button.
	var jnlSolvedBtns = document.querySelectorAll('.jnlSolvedBtn');
	jnlSolvedBtns.forEach(function (btn) {
		var kmessage = btn.closest('.kmessage');
		if (!kmessage) {
			return;
		}
		var idElement = kmessage.querySelector('.kmsg-id');
		if (!idElement) {
			return;
		}
		btn.setAttribute('data-message-id', idElement.getAttribute('id'));
	});

	// Highlight the post that was marked as the solution.
	if (solutionId && solutionId !== '0') {
		document.querySelectorAll('.kmessage').forEach(function (kmessage) {
			var idElement = kmessage.querySelector('.kmsg-id');
			if (!idElement) {
				return;
			}
			if (idElement.getAttribute('id') === solutionId) {
				kmessage.classList.add('jnlSolvedSolutionPost');
				var msgBody = kmessage.querySelector('.kmsg-body');
				if (msgBody) {
					var note = document.createElement('p');
					note.className = 'solutionPost';
					note.textContent = body.dataset.jnlSolutionlabel || '';
					msgBody.prepend(note);
				}
			}
		});
	}

	function withToken(url) {
		if (!csrfToken) {
			return url;
		}
		return url + '&' + encodeURIComponent(csrfToken) + '=1';
	}

	var confirmCheckbox = document.getElementById('cbCorrectMarked');
	if (confirmCheckbox) {
		confirmCheckbox.addEventListener('change', function () {
			var closeBtn = document.getElementById('btnCloseModal');
			if (!closeBtn) {
				return;
			}
			if (confirmCheckbox.checked) {
				closeBtn.classList.add('jnlSolvedCloseable');
			} else {
				closeBtn.classList.remove('jnlSolvedCloseable');
			}
		});
	}

	jnlSolvedBtns.forEach(function (btn) {
		btn.addEventListener('click', function (event) {
			var messageId = event.currentTarget.getAttribute('data-message-id');
			closelink = withToken(
				window.location.origin + window.location.pathname +
				'?option=com_kunena&view=topic&jnltask=solved&berid=' + encodeURIComponent(messageId) +
				'&catid=' + encodeURIComponent(categoryId) + '&id=' + encodeURIComponent(topicId)
			);
		});
	});

	var closeModalBtn = document.getElementById('btnCloseModal');
	if (closeModalBtn) {
		closeModalBtn.addEventListener('click', function () {
			if (closeModalBtn.classList.contains('jnlSolvedCloseable')) {
				location.href = closelink;
			}
		});
	}

	var reopenBtn = document.getElementById('btnReopenModal');
	if (reopenBtn) {
		reopenBtn.addEventListener('click', function () {
			location.href = withToken(
				window.location.origin + window.location.pathname +
				'?option=com_kunena&view=topic&jnltask=reopen' +
				'&catid=' + encodeURIComponent(categoryId) + '&id=' + encodeURIComponent(topicId)
			);
		});
	}
});
