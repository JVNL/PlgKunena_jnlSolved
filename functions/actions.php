<?php
/*
* @author: Team Joomla!NL
* @url: http://www.joomlanl.nl/
* @copyright Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
* @license GNU General Public License version 3 or later; see LICENSE.txt
*/

defined('_JEXEC') or die();

use Joomla\CMS\Plugin\CMSPlugin;

require_once('auth.php');
require_once('htmlHandler.php');

class jnlPlgSolvedActions extends CMSPlugin
{
	/*
	* Method to reopen a topic which has been marked as solved.
	*/
	public static function ReopenTopic($topicId, $topicSolvedText)
	{
		$Dbo = JFactory::getDBO();
		$topicData = jnlPlgSolvedActions::GetTopicData($topicId);
		if($topicData != null)
		{
			if(stripos($topicData['subject'], $topicSolvedText) || (bool)$topicData['locked'])
			{
				if(jnlPlgSolvedAuth::IsAdminOrModerator())
				{
					$newTopicName = str_replace($topicSolvedText." ", "", $topicData['subject']);
					
					$Dbo->setQuery("UPDATE #__kunena_topics
					SET locked = 0, subject = " . $Dbo->quote($newTopicName, true) . "
					WHERE id = " . (int)$topicId . " LIMIT 1");
					$Dbo->execute();
					
					$Dbo->setQuery("DELETE
					FROM #__kunena_jnlsolved
					WHERE topicid = " . (int)$topicId);
					$Dbo->execute();
				}
			}
		}
	}
	
	/*
	* Method to handle the topic which has been marked as solved.
	*/
	public static function SolvedTopic($topicId, $topicSolvedText, $renameTopic, $lockSolved, $messageId)
	{
		$Dbo = JFactory::getDBO();
		$topicData = jnlPlgSolvedActions::GetTopicData($topicId);
		if($topicData != null)
		{
			// add "[solved]" to topic:
			if($renameTopic)
			{
				$Dbo->setQuery("UPDATE #__kunena_topics
				SET subject = " . $Dbo->quote($topicSolvedText . ' ' . $topicData['subject'], true) . "
				WHERE id = " . (int)$topicId . " LIMIT 1");
				$Dbo->execute();
			}
			
			// lock solved topics
			if($lockSolved)
			{
				$Dbo->setQuery("UPDATE #__kunena_topics
				SET	locked = 1
				WHERE id = " . (int)$topicId . " LIMIT 1");
				$Dbo->execute();
			}
			
			// topic solution tracking
			$Dbo->setQuery("INSERT INTO #__kunena_jnlsolved (mesid, topicid)
			VALUES (" . (int)$messageId . ", " . (int)$topicId . ")");
			$Dbo->execute();
		}
	}
	
	/*
	* Method to get topic data(id, subject, locked).
	*/
	public static function GetTopicData($topicId)
	{
		$Dbo = JFactory::getDBO();
		$Dbo->setQuery('SELECT kt.subject, kt.id, kt.locked
		FROM #__kunena_topics kt
		WHERE kt.id = ' . (int)$topicId . ' LIMIT 1');
		$Dbo->execute();
		return $Dbo->loadAssoc();
	}

	/*
	* Method which makes a HTML element for the topic with solved data.
	*/
	public static function SetSolvedElement($topicId, $categoryId)
	{
		$Dbo = JFactory::getDBO();

		$Dbo->setQuery('SELECT kj.mesid
		FROM #__kunena_jnlsolved kj
		WHERE kj.topicid = ' . (int)$topicId . ' LIMIT 1');
		$Dbo->execute();

		$topicData = $Dbo->loadAssoc();
		$solutionId = $topicData != null && $topicData["mesid"] != null ? $topicData["mesid"] : 0;

		$regex = '#<head>.*</head>#Us';
		$element = " <span id='jnlSolvedData' data-solutionpost='" . $solutionId . "' data-categoryid='" . $categoryId . "' data-topicid='" . $topicId . "'><span/>";
		jnlPlgSolvedHtmlHandler::PutOnPage($regex, $element);
	}
}