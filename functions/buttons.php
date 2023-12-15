<?php
/*
* @author: Team Joomla!NL
* @url: http://www.joomlanl.nl/
* @copyright Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
* @license GNU General Public License version 3 or later; see LICENSE.txt
*/

defined('_JEXEC') or die();

use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Application\WebApplication;
use Joomla\Filesystem\File;

require_once('htmlHandler.php');

class jnlPlgSolvedButtons extends CMSPlugin
{
	public static function AddReopenButton()
	{
		$buttonCode = file_get_contents(JPATH_PLUGINS . '/kunena/jnlsolved/template/button_reopen.html');
		if(empty($buttonCode)) {
			return;
		}

		$regex = '#<div class="btn-toolbar btn-marging kmessagepadding">.*</div>#Us';
		jnlPlgSolvedHtmlHandler::PutOnPage($regex, $buttonCode);
	}

	public static function AddSolutionButton($solvedButtonText)
	{
		$buttonCode = file_get_contents(JPATH_PLUGINS . '/kunena/jnlsolved/template/button_solved.html');
		if(empty($buttonCode)) {
			return;
		}
		
		$regex = '#<div class="btn-toolbar btn-marging kmessagepadding">.*</div>#Us';
		jnlPlgSolvedHtmlHandler::PutOnPage($regex, $buttonCode);
	}

	public static function AddModalElements()
	{
		$modalCode = file_get_contents(JPATH_PLUGINS . '/kunena/jnlsolved/template/modal.html');
		if(empty($modalCode)) {
			return;
		}

		$regex = '#<head>.*</head>#Us';
		jnlPlgSolvedHtmlHandler::PutOnPage($regex, $modalCode);
	}
}