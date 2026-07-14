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

use Joomla\CMS\Language\Text;

final class Buttons
{
    /** Ancestor that scopes matches to a single post's toolbar (see Render::appendIntoButtonListByClass). */
    private const MESSAGE_CLASS = 'kmessage';

    /** Class used by the Kunena template to wrap a message's action toolbar. */
    private const TOOLBAR_CLASS = 'kmessagepadding';

    public static function addSolvedButton(): void
    {
        Render::appendIntoButtonListByClass(self::MESSAGE_CLASS, self::TOOLBAR_CLASS, self::renderTemplate('button_solved', [
            'label' => Text::_('PLG_KUNENA_JNLSOLVED_BUTTON_SOLVE'),
        ]));
    }

    public static function addReopenButton(): void
    {
        Render::appendIntoButtonListByClass(self::MESSAGE_CLASS, self::TOOLBAR_CLASS, self::renderTemplate('button_reopen', [
            'label' => Text::_('PLG_KUNENA_JNLSOLVED_BUTTON_REOPEN'),
        ]));
    }

    public static function addModals(): void
    {
        Render::appendBeforeBodyEnd(self::renderTemplate('modal', [
            'warningTitle'    => Text::_('PLG_KUNENA_JNLSOLVED_MODAL_WARNING_TITLE'),
            'solveBodyPart1'  => Text::_('PLG_KUNENA_JNLSOLVED_MODAL_SOLVE_BODY_1'),
            'solveBodyPart2'  => Text::_('PLG_KUNENA_JNLSOLVED_MODAL_SOLVE_BODY_2'),
            'confirmLabel'    => Text::_('PLG_KUNENA_JNLSOLVED_MODAL_CONFIRM_LABEL'),
            'closeTopic'      => Text::_('PLG_KUNENA_JNLSOLVED_MODAL_CLOSE_BUTTON'),
            'reopenBody'      => Text::_('PLG_KUNENA_JNLSOLVED_MODAL_REOPEN_BODY'),
            'yes'             => Text::_('JYES'),
            'no'              => Text::_('JNO'),
        ]));
    }

    /**
     * @param   array<string, string>  $vars
     */
    private static function renderTemplate(string $name, array $vars): string
    {
        $file = __DIR__ . '/../template/' . $name . '.php';

        if (!is_file($file)) {
            return '';
        }

        $render = static function (string $__file, array $__vars): string {
            extract($__vars, EXTR_SKIP);
            ob_start();
            include $__file;

            return (string) ob_get_clean();
        };

        return $render($file, $vars);
    }
}
