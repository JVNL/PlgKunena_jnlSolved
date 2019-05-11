<?php
/*
* @author: Team Joomla!NL
* @url: http://www.joomlanl.nl/
* @copyright Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
* @license GNU General Public License version 2 or later; see LICENSE.txt
*/

defined('_JEXEC') or die();

require_once('htmlHandler.php');

//TODO: Check if some of this can be replaced by JavaScript.

class jnlPlgSolvedButtons
{
	public static function AddReopenButton()
	{
		$buttonCode = JFile::read(JPATH_PLUGINS . '/kunena/jnlsolved/template/button_reopen.html');
		if(empty($buttonCode))
			return;

		$regex = '#<div class="btn-toolbar btn-marging">.*</div>#Us';
		jnlPlgSolvedHtmlHandler::PutOnPage($regex, $buttonCode);
	}

	public static function AddSolutionButton($solvedButtonText)
	{
		$buttonCode = JFile::read(JPATH_PLUGINS . '/kunena/jnlsolved/template/button_solved.html');
		if(empty($buttonCode))
			return;

		$regex = '#<div class="btn-toolbar btn-marging">.*</div>#Us';
		jnlPlgSolvedHtmlHandler::PutOnPage($regex, $buttonCode);
	}

	public static function AddModalElements()
	{
		$modalCode = JFile::read(JPATH_PLUGINS . '/kunena/jnlsolved/template/modal.html');
		if(empty($modalCode))
			return;

		$regex = '#<head>.*</head>#Us';
		jnlPlgSolvedHtmlHandler::PutOnPage($regex, $modalCode);
	}
}