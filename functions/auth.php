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
use Joomla\CMS\User\User;
use Joomla\CMS\Input\Input;
use Kunena\Forum\Libraries\Access\KunenaAccess;

class jnlPlgSolvedAuth extends CMSPlugin
{
	/*
	* Method to check if the user is authorized to use the plugin.
	*/
	public static function IsUserAuthorized($enableForAdmins, $enableForModerators, $enableForTopicStarter, $lastPostUser, $topicSolvedText)
	{
		$Dbo = JFactory::getDBO();
		$User = JFactory::getUser();
		$JInput = JFactory::getApplication()->input;
		
		$userId = (int)$User->id;
		$topicId = $JInput->getInt('id', 0);
		$categoryId = $JInput->getInt('catid', 0);
		
		if(empty($userId) || empty($topicId) || empty($categoryId)) {
			return false;
		}

		$kunenaAccess = KunenaAccess::getInstance();

		if($enableForAdmins && $kunenaAccess->isAdmin($User, $categoryId)) {
			return true;
		} elseif($enableForModerators && $kunenaAccess->isModerator($User, $categoryId)) {
			return true;
		}
		
		//before wasting more time, is topicstarter premited to see the solved button?
		if ($enableForTopicStarter) {
			// get data for all checks down below
			$Dbo = JFactory::getDBO();
			$Dbo->setQuery('SELECT kt.subject, kt.locked, kt.first_post_userid, kt.posts, kt.last_post_userid, kt.id
			FROM #__kunena_topics kt
			WHERE kt.id = ' . (int)$topicId);
			$Dbo->execute();
			$topicData = $Dbo->loadAssoc();
			
			// topic already marked as [solved]?
			if(stripos($topicData['subject'], $topicSolvedText) !== false) {
				return false;
			}
			
			// topic locked?
			if((bool)$topicData['locked']) {
				return false;
			}
			
			// topic starter premitted to see the solved button?
			if((int)$topicData['posts'] >= 2)
			{
				$fUid = (int)$topicData['first_post_userid'];
				$lUid = (int)$topicData['last_post_userid'];
				
				if($fUid === $userId && (($lastPostUser && $lUid === $userId) || !$lastPostUser)) {
					return true;
				}
			}
		}
		return false;
	}
	
	/*
	* Method to check if the user is an administrator or a moderator.
	*/
	public static function IsAdminOrModerator()
	{
		$JInput = JFactory::getApplication()->input;
		$User = JFactory::getUser();
		$categoryId = $JInput->getInt('catid', 0);
		$kunenaAccess = KunenaAccess::getInstance();
		
		if($kunenaAccess->isAdmin($User, $categoryId) || $kunenaAccess->isModerator($User, $categoryId)) {
			return true;
		}

		return false;
	}
}