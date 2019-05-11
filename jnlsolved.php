<?php
/*
* @author: Team Joomla!NL
* @url: http://www.joomlanl.nl/
* @copyright Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
* @license GNU General Public License version 2 or later; see LICENSE.txt
*/

defined('_JEXEC') or die();

require_once('functions/auth.php');
require_once('functions/actions.php');
require_once('functions/buttons.php');

class plgKunenaJnlSolved extends JPlugin
{
	public function __construct(&$subject, $config)
	{
		// do not load if Kunena version is not supported or Kunena is offline
		// also the Kunena version is setted to 5 and up
		if(!(class_exists('KunenaForum') && KunenaForum::isCompatible('5.0')))
			die("Fatal error, the plugin JnlSolved is only compatible with Kunena 5.x! For help please contact the developer.");
		
		parent::__construct ($subject, $config);
		
		// load language file:
		$this->loadLanguage('plg_kunena_jnlsolved.sys', JPATH_ADMINISTRATOR);
		
		// load language strings below to JavaScript
		JText::script('PLG_KUNENA_JNLSOLVED_SOLUTION_POST_TEXT');
		JText::script('PLG_KUNENA_JNLSOLVED_BUTTON_REOPEN_TEXT');
		JText::script('PLG_KUNENA_JNLSOLVED_BUTTON_SOLVED_TEXT');
		JText::script('PLG_KUNENA_JNLSOLVED_TOPIC_SOLVED_TITLE');
		JText::script('JYES');
		JText::script('JNO');
		
		$document = JFactory::getDocument();
		$document->addStyleSheet(JURI::root() . '/plugins/kunena/jnlsolved/css/style_solved.css');
		$document->addScript(JURI::root() . '/plugins/kunena/jnlsolved/js/jnl_solved.js');
	}
	
	public function onAfterRoute()
	{
		$JApplication = JFactory::getApplication();
		$JInput = $JApplication->input;
		
		$app = $JInput->getString('option', null);
		$task = $JInput->getString('jnltask', null);
		$messageId = $JInput->getInt('berid', 0);
		$topicId = $JInput->getInt('id', 0);
		$catId = $JInput->getInt('catid', 0);
		
		$enableForAdmins = (bool)$this->params->get('enable_for_admin', 0);
		$enableForModerators = (bool)$this->params->get('enable_for_moderator', 0);
		$enableForTopicStarter = (bool)$this->params->get('enable_for_topic_starter', 0);
		$lastPostUser = (bool)$this->params->get('last_post', 1);
		$topicSolvedText = JText::_('PLG_KUNENA_JNLSOLVED_TOPIC_SOLVED_TITLE');
		
		$renameTopic = (bool)$this->params->get('topic_solved_rename', 1);
		$lockSolved = (bool)$this->params->get('lock_solved', 1);
		$callback = KunenaRoute::_('index.php?option=com_kunena&view=topic&catid=' . $catId . '&id=' . $topicId . '#' . $messageId);
		
		// is kunena openend or was there a given task?
		if(($app !=='com_kunena') || ($task !== 'reopen' && $task !== 'solved'))
			return;
		
		// topic and category-id given?
		if(empty($topicId) || empty($catId))
			return;
		
		// user authorized to perform solved action?
		if(jnlPlgSolvedAuth::IsUserAuthorized($enableForAdmins, $enableForModerators, $enableForTopicStarter, $lastPostUser, $topicSolvedText) === false)
			return;
		
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
	
		$enableForAdmins = (bool)$this->params->get('enable_for_admin', 0);
		$enableForModerators = (bool)$this->params->get('enable_for_moderator', 0);
		$enableForTopicStarter = (bool)$this->params->get('enable_for_topic_starter', 0);
		$lastPostUser = (bool)$this->params->get('last_post', 1);
		$topicSolvedText = JText::_('PLG_KUNENA_JNLSOLVED_TOPIC_SOLVED_TITLE');
		$solvedButtonText = JText::_('PLG_KUNENA_JNLSOLVED_BUTTON_SOLVED_TEXT');
	
		// kunena topic view loaded?
		if(empty($app) || empty($view))
			return;
	
		if($app !== 'com_kunena' && $view !== 'topic')
			return;
	
		// category-id and topic-id given?
		if(empty($catId) || empty($topicId))
			return;
	
		// html view?
		if($doctype !== 'html')
			return;
		
		// set solved data HTML element
		jnlPlgSolvedActions::SetSolvedElement($topicId, $catId);
	
		// user authorized to see/use solved button?
		if(jnlPlgSolvedAuth::IsUserAuthorized($enableForAdmins, $enableForModerators, $enableForTopicStarter, $lastPostUser, $topicSolvedText) === false)
			return;
	
		// get topicdata
		$topicData = jnlPlgSolvedActions::GetTopicData($topicId);
	
		// topic already marked as solved?
		if(stripos($topicData['subject'], $topicSolvedText) !== false || (bool)$topicData['locked'])
			jnlPlgSolvedButtons::AddReopenButton();
		else
			jnlPlgSolvedButtons::AddSolutionButton($solvedButtonText);
		jnlPlgSolvedButtons::AddModalElements();
	}
}