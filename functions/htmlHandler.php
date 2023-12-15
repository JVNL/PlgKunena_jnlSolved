<?php
/*
* @author: Team Joomla!NL
* @url: http://www.joomlanl.nl/
* @copyright Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
* @license GNU General Public License version 3 or later; see LICENSE.txt
*/

defined('_JEXEC') or die();

use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Application\WebApplication;

class jnlPlgSolvedHtmlHandler extends CMSPlugin
{
	public static function PutOnPage($regex, $element)
	{
		$body = JFactory::getApplication()->getBody();

		preg_match_all($regex, $body, $matches);
		if(empty($matches[0])) {
			return;
		} else {
			foreach($matches[0] as $originalText) {
				$body = str_replace($originalText, $originalText . $element, $body);
			}
		}
		JFactory::getApplication()->setBody($body);
	}
}