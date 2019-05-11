<?php
/*
* @author: Team Joomla!NL
* @url: http://www.joomlanl.nl/
* @copyright Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
* @license GNU General Public License version 3 or later; see LICENSE.txt
*/

defined('_JEXEC') or die();

class jnlPlgSolvedHtmlHandler extends JPlugin
{
	public static function PutOnPage($regex, $element)
	{
		$body = JResponse::getBody();

		preg_match_all($regex, $body, $matches);
		if(empty($matches[0]))
			return;
		else
		{
			foreach($matches[0] as $originalText)
			{
				$body = str_replace($originalText, $originalText . $element, $body);
			}
		}
		JResponse::setBody($body);
	}
}