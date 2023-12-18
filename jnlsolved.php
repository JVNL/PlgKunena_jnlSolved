<?php
/*
* @author: Team Joomla!NL
* @url: http://www.joomlanl.nl/
* @copyright Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
* @license GNU General Public License version 3 or later; see LICENSE.txt
*/

defined('_JEXEC') or die();

use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Registry\Registry;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Language\Text;
use Kunena\Forum\Libraries\Forum\KunenaForum;
use Kunena\Forum\Libraries\Route\KunenaRoute;

require_once('functions/auth.php');
require_once('functions/actions.php');
require_once('functions/buttons.php');

class plgKunenaJnlSolved extends CMSPlugin
{
	public function __construct(&$subject, $config)
	{
		// do not load if Kunena version is not supported or Kunena is offline
		// also the Kunena version is setted to 6 and up
		if (!(class_exists('Kunena\Forum\Libraries\Forum\KunenaForum') && KunenaForum::enabled() && KunenaForum::isCompatible('6.0'))) {
			return;
		}
		
		parent::__construct ($subject, $config);
		
		$document = JFactory::getDocument();
		$document->addStyleSheet(JURI::root() . 'plugins/kunena/jnlsolved/css/style_solved.css?v=1.0.0');
		$document->addScript(JURI::root() . 'plugins/kunena/jnlsolved/js/jnl_solved.js?v=1.0.0');
	}
	
	public function onBeforeRender()
	{
		$JApplication = JFactory::getApplication();
		$JInput = $JApplication->input;
		
		$app = $JInput->getString('option', null);
		$task = $JInput->getString('jnltask', null);

		// is kunena openend or was there a given task?
		if(($app !== 'com_kunena') || ($task !== 'reopen' && $task !== 'solved')) {
			return;
		}

		$messageId = $JInput->getInt('berid', 0);
		$topicId = $JInput->getInt('id', 0);
		$catId = $JInput->getInt('catid', 0);
		
		$plugin = PluginHelper::getPlugin('kunena','jnlsolved');
		$params = new Registry($plugin->params);
		$enableForAdmins = (bool)$params->get('enable_for_admin', 0);
		$enableForModerators = (bool)$params->get('enable_for_moderator', 0);
		$enableForTopicStarter = (bool)$params->get('enable_for_topic_starter', 0);
		$lastPostUser = (bool)$params->get('last_post', 1);
		$topicSolvedText = "[opgelost]";
		
		$renameTopic = (bool)$params->get('topic_solved_rename', 1);
		$lockSolved = (bool)$params->get('lock_solved', 1);
		$callback = KunenaRoute::_('index.php?option=com_kunena&view=topic&catid=' . $catId . '&id=' . $topicId . '#' . $messageId);
		
		// topic and category-id given?
		if(empty($topicId) || empty($catId)) {
			return;
		}

		// user authorized to perform solved action?
		if(jnlPlgSolvedAuth::IsUserAuthorized($enableForAdmins, $enableForModerators, $enableForTopicStarter, $lastPostUser, $topicSolvedText) === false) {
			return;
		}

		// okay what do you want to do :P
		switch ($task)
		{
			case 'reopen':
				// preform additional administrator/moderator check in function ReopenTopic
				jnlPlgSolvedActions::ReopenTopic($topicId, $topicSolvedText);
				$JApplication->redirect($callback);
				break;
			case 'solved':
				jnlPlgSolvedActions::SolvedTopic($topicId, $topicSolvedText, $renameTopic, $lockSolved, $messageId);
				$JApplication->redirect($callback);
				break;
		}
	}

	public function onAfterRender()
	{
		$JInput = JFactory::getApplication()->input;
		
		$app = $JInput->getString('option', null);
		$view = $JInput->getString('view', null);
		$catId = $JInput->getInt('catid', 0);
		$topicId = $JInput->getInt('id', 0);
		$document = JFactory::getDocument();
		$doctype = $document->getType();
	
		$plugin = PluginHelper::getPlugin('kunena','jnlsolved');
		$params = new Registry($plugin->params);
		$enableForAdmins = (bool)$params->get('enable_for_admin', 0);
		$enableForModerators = (bool)$params->get('enable_for_moderator', 0);
		$enableForTopicStarter = (bool)$params->get('enable_for_topic_starter', 0);
		$lastPostUser = (bool)$params->get('last_post', 1);
		$topicSolvedText = "[opgelost]";
	
		// kunena topic view loaded?
		if(empty($app) || empty($view)) {
			return;
		}
	
		if($app !== 'com_kunena' && $view !== 'topic') {
			return;
		}

		// category-id and topic-id given?
		if(empty($catId) || empty($topicId)) {
			return;
		}

		// html view?
		if($doctype !== 'html') {
			return;
		}
		
		// set solved data HTML element
		jnlPlgSolvedActions::SetSolvedElement($topicId, $catId);
	
		// user authorized to see/use solved button?
		if(jnlPlgSolvedAuth::IsUserAuthorized($enableForAdmins, $enableForModerators, $enableForTopicStarter, $lastPostUser, $topicSolvedText) === false) {
			return;
		}

		// get topicdata
		$topicData = jnlPlgSolvedActions::GetTopicData($topicId);
	
		// topic already marked as solved?
		if(stripos($topicData['subject'], $topicSolvedText) !== false || (bool)$topicData['locked']) {
			jnlPlgSolvedButtons::AddReopenButton();
		} else {
			jnlPlgSolvedButtons::AddSolutionButton($solvedButtonText);
		}

		jnlPlgSolvedButtons::AddModalElements();
	}
}