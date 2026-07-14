<?php

/**
 * @package     PLG_KUNENA_JNLSOLVED
 * @author      Team Joomla!NL
 * @url         https://www.joomlanl.nl/
 * @copyright   Copyright (C) Team Joomla!NL. All rights reserved.
 * @license     GNU General Public License version 3 or later; see LICENSE.txt
 */

defined('_JEXEC') or die();

use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Uri\Uri;
use Joomla\Registry\Registry;
use JoomlaNL\Plugin\Kunena\JnlSolved\Actions;
use JoomlaNL\Plugin\Kunena\JnlSolved\Auth;
use JoomlaNL\Plugin\Kunena\JnlSolved\Buttons;
use JoomlaNL\Plugin\Kunena\JnlSolved\Render;
use Kunena\Forum\Libraries\Forum\KunenaForum;
use Kunena\Forum\Libraries\Forum\Topic\KunenaTopicHelper;
use Kunena\Forum\Libraries\Route\KunenaRoute;

JLoader::registerNamespace('JoomlaNL\\Plugin\\Kunena\\JnlSolved', __DIR__ . '/src');

class plgKunenaJnlSolved extends CMSPlugin
{
    /** @var string  bundled asset version, bump when css/js change */
    private const ASSET_VERSION = '2.0.0';

    /**
     * Without this, CMSPlugin::__construct() never calls loadLanguage()
     * and every Text::_('PLG_KUNENA_JNLSOLVED_...') call just prints the
     * raw constant name instead of the translated string.
     *
     * @var bool
     */
    protected $autoloadLanguage = true;

    public function __construct(&$subject, $config)
    {
        // Do not load if Kunena is missing, offline, or too old for this
        // plugin's use of the Kunena 6 Topic/Message API.
        if (!$this->kunenaIsReady()) {
            return;
        }

        parent::__construct($subject, $config);
    }

    /**
     * Handles the actual "mark solved" / "reopen" requests coming from the
     * front-end buttons, and registers the plugin's CSS/JS.
     *
     * NOTE: asset loading used to live in the constructor, calling
     * Factory::getApplication()->getDocument()->addStyleSheet() directly.
     * Kunena's own code imports the 'kunena' plugin group (which
     * instantiates this class) from places that run before the
     * application's Document object exists -- e.g. access-rights checks
     * during routing. At that point getDocument() returns null and the
     * chained ->addStyleSheet() call is a fatal error. onBeforeRender()
     * only fires once the application is fully bootstrapped, so it's a
     * safe place to touch the document -- with a defensive null/type
     * check regardless, in case some other app type ends up triggering
     * the 'kunena' group too.
     */
    public function onBeforeRender()
    {
        if (!$this->kunenaIsReady()) {
            return;
        }

        $app   = Factory::getApplication();
        $input = $app->input;

        if ($input->getCmd('option') !== 'com_kunena') {
            return;
        }

        $this->loadAssets();

        $task = $input->getCmd('jnltask');

        if ($task !== 'reopen' && $task !== 'solved') {
            return;
        }

        $topicId = $input->getInt('id', 0);
        $catId   = $input->getInt('catid', 0);

        if ($topicId <= 0 || $catId <= 0) {
            return;
        }

        // Anti-CSRF: the buttons append Session::getFormToken() as a GET
        // parameter (see jnl_solved.js). Without this check a crafted link
        // could make a logged-in visitor unknowingly close or reopen a
        // topic just by loading a page. Fail silently (no state change,
        // no error page leaking details) rather than acting on a forged
        // request.
        if (Session::checkToken('get') === false) {
            return;
        }

        $topic = KunenaTopicHelper::get($topicId);

        if (!$topic->exists() || (int) $topic->category_id !== $catId) {
            return;
        }

        $params = $this->getPluginParams();

        $authorized = $task === 'reopen'
            ? Auth::isAdminOrModerator($topic)
            : Auth::isAuthorizedForAction(
                $topic,
                $params->enableForAdmins,
                $params->enableForModerators,
                $params->enableForTopicStarter,
                $params->requireLastPostByStarter,
                $params->solvedMarker
            );

        if (!$authorized) {
            return;
        }

        $messageId = $input->getInt('berid', 0);
        $callback  = KunenaRoute::_('index.php?option=com_kunena&view=topic&catid=' . $catId . '&id=' . $topicId . '#' . $messageId);

        if ($task === 'reopen') {
            Actions::reopen($topic, $params->solvedMarker);
        } else {
            Actions::markSolved($topic, $params->solvedMarker, $params->renameTopic, $params->lockSolved, $messageId);
        }

        $app->redirect($callback);
    }

    /**
     * Injects the solved/reopen buttons, the confirmation modals, and the
     * data attributes the front-end script needs.
     */
    public function onAfterRender()
    {
        if (!$this->kunenaIsReady()) {
            return;
        }

        $app      = Factory::getApplication();
        $input    = $app->input;
        $document = $app->getDocument();

        if ($input->getCmd('option') !== 'com_kunena' || $input->getCmd('view') !== 'topic') {
            return;
        }

        if ($document === null || $document->getType() !== 'html') {
            return;
        }

        $topicId = $input->getInt('id', 0);
        $catId   = $input->getInt('catid', 0);

        if ($topicId <= 0 || $catId <= 0) {
            return;
        }

        $topic = KunenaTopicHelper::get($topicId);

        if (!$topic->exists()) {
            return;
        }

        // These attributes drive the "mark this post as the solution" green
        // highlight for every visitor, independent of whether the current
        // visitor is allowed to change the solved state.
        Render::setBodyDataAttributes([
            'solutionpost'  => Actions::getSolutionMessageId($topicId),
            'categoryid'    => $catId,
            'topicid'       => $topicId,
            'token'         => Session::getFormToken(),
            'solutionlabel' => \Joomla\CMS\Language\Text::_('PLG_KUNENA_JNLSOLVED_SOLUTION_POST_LABEL'),
        ]);

        $params = $this->getPluginParams();

        if (!Auth::isAuthorizedForAction(
            $topic,
            $params->enableForAdmins,
            $params->enableForModerators,
            $params->enableForTopicStarter,
            $params->requireLastPostByStarter,
            $params->solvedMarker
        )) {
            return;
        }

        if (Auth::isMarkedSolved($topic, $params->solvedMarker) || (bool) $topic->locked) {
            Buttons::addReopenButton();
        } else {
            Buttons::addSolvedButton();
        }

        Buttons::addModals();
    }

    private function loadAssets(): void
    {
        $document = Factory::getApplication()->getDocument();

        if ($document === null || $document->getType() !== 'html') {
            return;
        }

        $document->addStyleSheet(Uri::root() . 'plugins/kunena/jnlsolved/css/style_solved.css?v=' . self::ASSET_VERSION);
        $document->addScript(Uri::root() . 'plugins/kunena/jnlsolved/js/jnl_solved.js?v=' . self::ASSET_VERSION);
    }

    private function kunenaIsReady(): bool
    {
        return class_exists(KunenaForum::class)
            && KunenaForum::enabled()
            && KunenaForum::isCompatible('6.0');
    }

    private function getPluginParams(): object
    {
        $plugin = PluginHelper::getPlugin('kunena', 'jnlsolved');
        $params = new Registry($plugin->params ?? '');

        return (object) [
            'enableForAdmins'          => (bool) $params->get('enable_for_admin', 1),
            'enableForModerators'      => (bool) $params->get('enable_for_moderator', 1),
            'enableForTopicStarter'    => (bool) $params->get('enable_for_topic_starter', 1),
            'requireLastPostByStarter' => (bool) $params->get('last_post', 1),
            'renameTopic'              => (bool) $params->get('topic_solved_rename', 1),
            'lockSolved'               => (bool) $params->get('lock_solved', 1),
            'solvedMarker'             => (string) $params->get('topic_solved_text', '[opgelost]'),
        ];
    }
}
