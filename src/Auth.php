<?php

/**
 * @package     PLG_KUNENA_JNLSOLVED
 * @author      Team Joomla!NL
 * @url         https://www.joomlanl.nl/
 * @copyright   Copyright (C) Team Joomla!NL. All rights reserved.
 * @license     GNU General Public License version 3 or later; see LICENSE.txt
 */

namespace JoomlaNL\Plugin\Kunena\JnlSolved;

defined('_JEXEC') or die();

use Joomla\CMS\Factory;
use Kunena\Forum\Libraries\Access\KunenaAccess;
use Kunena\Forum\Libraries\Forum\Topic\KunenaTopic;

final class Auth
{
    /**
     * Is the current visitor allowed to mark this topic solved / reopen it?
     *
     * Admins/moderators are only allowed when the corresponding option is
     * enabled. The topic starter additionally has to be the actual first
     * poster, the topic may not already be solved or locked, and (depending
     * on configuration) must have posted the last reply too.
     */
    public static function isAuthorizedForAction(
        KunenaTopic $topic,
        bool $enableForAdmins,
        bool $enableForModerators,
        bool $enableForTopicStarter,
        bool $requireLastPostByStarter,
        string $solvedMarker
    ): bool {
        $user = Factory::getApplication()->getIdentity();

        if ($user === null || $user->guest || !$topic->exists()) {
            return false;
        }

        $access = KunenaAccess::getInstance();

        if ($enableForAdmins && $access->isAdmin($user, (int) $topic->category_id)) {
            return true;
        }

        if ($enableForModerators && $access->isModerator($user, (int) $topic->category_id)) {
            return true;
        }

        if (!$enableForTopicStarter) {
            return false;
        }

        // Already solved or locked: nothing left for the topic starter to do.
        if (self::isMarkedSolved($topic, $solvedMarker) || (bool) $topic->locked) {
            return false;
        }

        // Need at least a question + a reply before "solved" makes sense.
        if ((int) $topic->posts < 2) {
            return false;
        }

        $isStarter = (int) $topic->first_post_userid === (int) $user->id;

        if (!$isStarter) {
            return false;
        }

        if ($requireLastPostByStarter) {
            return (int) $topic->last_post_userid === (int) $user->id;
        }

        return true;
    }

    public static function isAdminOrModerator(KunenaTopic $topic): bool
    {
        $user = Factory::getApplication()->getIdentity();

        if ($user === null || $user->guest) {
            return false;
        }

        $access = KunenaAccess::getInstance();

        return $access->isAdmin($user, (int) $topic->category_id)
            || $access->isModerator($user, (int) $topic->category_id);
    }

    /**
     * NOTE: this MUST use `!== false`. stripos() returns the match
     * *position*, and the marker is always prepended at position 0, so a
     * plain truthy check (`if (stripos(...))`) silently fails for exactly
     * the common case. The original plugin had this bug in one of its two
     * call sites; this helper exists so there is only one place to get it
     * right.
     */
    public static function isMarkedSolved(KunenaTopic $topic, string $solvedMarker): bool
    {
        return stripos((string) $topic->subject, $solvedMarker) !== false;
    }
}
