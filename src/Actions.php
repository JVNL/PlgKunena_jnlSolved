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
use Kunena\Forum\Libraries\Forum\Message\KunenaMessageHelper;
use Kunena\Forum\Libraries\Forum\Topic\KunenaTopic;

final class Actions
{
    /**
     * Reopen a topic that was previously marked solved. Uses Kunena's own
     * Topic object so the change goes through Kunena's save()/lock() logic
     * (cache invalidation, category counters, etc.) instead of a bare
     * UPDATE statement that Kunena's own object cache doesn't know about.
     */
    public static function reopen(KunenaTopic $topic, string $solvedMarker): bool
    {
        if (!$topic->exists()) {
            return false;
        }

        if (!Auth::isMarkedSolved($topic, $solvedMarker) && !$topic->locked) {
            return false;
        }

        $topic->subject = trim(str_ireplace($solvedMarker, '', (string) $topic->subject));
        $topic->locked  = 0;
        $topic->save();

        self::clearSolutionRecord((int) $topic->id);

        return true;
    }

    public static function markSolved(
        KunenaTopic $topic,
        string $solvedMarker,
        bool $renameTopic,
        bool $lockTopic,
        int $messageId
    ): bool {
        if (!$topic->exists()) {
            return false;
        }

        if ($renameTopic && !Auth::isMarkedSolved($topic, $solvedMarker)) {
            $topic->subject = trim($solvedMarker . ' ' . $topic->subject);
        }

        if ($lockTopic) {
            $topic->locked = 1;
        }

        $topic->save();

        self::storeSolutionRecord((int) $topic->id, self::resolveMessageId($topic, $messageId));

        return true;
    }

    /**
     * @return int  0 if there is no recorded solution post for this topic.
     */
    public static function getSolutionMessageId(int $topicId): int
    {
        $db = Factory::getContainer()->get('DatabaseDriver');

        $query = $db->getQuery(true)
            ->select($db->quoteName('mesid'))
            ->from($db->quoteName('#__kunena_jnlsolved'))
            ->where($db->quoteName('topicid') . ' = ' . (int) $topicId)
            ->setLimit(1);

        $result = $db->setQuery($query)->loadResult();

        return $result !== null ? (int) $result : 0;
    }

    /**
     * Reject a message id that was tampered with client-side and doesn't
     * actually belong to this topic.
     */
    private static function resolveMessageId(KunenaTopic $topic, int $messageId): int
    {
        if ($messageId <= 0) {
            return 0;
        }

        $message = KunenaMessageHelper::get($messageId);

        if (!$message->exists() || (int) $message->thread !== (int) $topic->id) {
            return 0;
        }

        return $messageId;
    }

    private static function storeSolutionRecord(int $topicId, int $messageId): void
    {
        $db = Factory::getContainer()->get('DatabaseDriver');

        self::clearSolutionRecord($topicId);

        if ($messageId <= 0) {
            return;
        }

        $query = $db->getQuery(true)
            ->insert($db->quoteName('#__kunena_jnlsolved'))
            ->columns([$db->quoteName('mesid'), $db->quoteName('topicid')])
            ->values((int) $messageId . ', ' . (int) $topicId);

        $db->setQuery($query)->execute();
    }

    private static function clearSolutionRecord(int $topicId): void
    {
        $db = Factory::getContainer()->get('DatabaseDriver');

        $query = $db->getQuery(true)
            ->delete($db->quoteName('#__kunena_jnlsolved'))
            ->where($db->quoteName('topicid') . ' = ' . (int) $topicId);

        $db->setQuery($query)->execute();
    }
}
