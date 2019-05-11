<?php
/*
* @author: Team Joomla!NL
* @url: http://www.joomlanl.nl/
* @copyright Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
* @license GNU General Public License version 2 or later; see LICENSE.txt
*/

defined('_JEXEC') or die();

class jnlPlgSolvedAuth extends JPlugin
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
		$catId = $JInput->getInt('catid', 0);
		
		if(empty($userId) || empty($topicId) || empty($catId))
			return false;

		$kunenaAccess = KunenaAccess::getInstance();

		if($enableForAdmins && $kunenaAccess->isAdmin($User, $catId))
			return true;
		elseif($enableForModerators && $kunenaAccess->isModerator($User, $catId))
			return true;
		
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
			if(stripos($topicData['subject'], $topicSolvedText) !== false)
				return false;
			
			// topic locked?
			if((bool)$topicData['locked'])
				return false;
			
			// topic starter premitted to see the solved button?
			if((int)$topicData['posts'] >= 2)
			{
				if((($lastPostUser) && ((int)$topicData['first_post_userid'] === $userId) && ((int)$topicData['last_post_userid'] === $userId)) || (($lastPostUser === 0) && ((int)$topicData['first_post_userid'] === $userId)))
					return true;
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
		
		$topicId = $JInput->getInt('id', 0);
		$catId = $JInput->getInt('catid', 0);
		
		$kunenaAccess = KunenaAccess::getInstance();
		
		if($kunenaAccess->isAdmin($User, $catId) || $kunenaAccess->isModerator($User, $catId))
			return true;
		return false;
	}
}