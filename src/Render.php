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

use DOMDocument;
use DOMXPath;
use Joomla\CMS\Factory;

/**
 * Safely injects markup into the rendered Kunena page.
 *
 * The original plugin used preg_match_all() + str_replace() on the raw
 * page body. That approach replaces every occurrence of a matched string,
 * so if a template ever produces two identical toolbar snippets it silently
 * duplicates the injected content, and the ungreedy regex can stop at the
 * wrong closing </div> if the markup is nested. Parsing the body as a DOM
 * and inserting real nodes avoids both problems and is not tied to exact
 * whitespace/attribute ordering in the template.
 */
final class Render
{
    /**
     * Set data-jnl-* attributes on <body>, used by jnl_solved.js.
     *
     * @param   array<string, int|string>  $data
     */
    public static function setBodyDataAttributes(array $data): void
    {
        $app  = Factory::getApplication();
        $body = $app->getBody();

        $dom     = self::loadHtml($body);
        $bodyTag = $dom->getElementsByTagName('body')->item(0);

        if ($bodyTag === null) {
            return;
        }

        foreach ($data as $key => $value) {
            $bodyTag->setAttribute('data-jnl-' . $key, (string) $value);
        }

        $app->setBody(self::saveHtml($dom));
    }

    /**
     * Append $html as an extra <li> inside the <ul> that already holds the
     * other action buttons (reply/quote/edit/...), so the new button lines
     * up on the same row instead of wrapping onto its own line below.
     *
     * Falls back to appending directly inside the matched element itself
     * if it has no <ul> child (defensive, in case a template variant
     * doesn't use one).
     *
     * $className is scoped to descendants of an element carrying
     * $ancestorClass. This template reuses "kmessagepadding" for both the
     * per-post action toolbar (inside a ".kmessage" wrapper, one per post
     * -- the only place jnl_solved.js can resolve a message id via
     * closest('.kmessage')) AND the topic-wide toolbar ("Antwoord
     * onderwerp" / "Schrijf uit" / "Favoriet", outside any .kmessage).
     * Without this restriction the button also gets injected into the
     * topic-wide toolbar, where it would silently fail to pick up a
     * message id and doesn't belong anyway.
     */
    public static function appendIntoButtonListByClass(string $ancestorClass, string $className, string $html): void
    {
        if (trim($html) === '') {
            return;
        }

        $app  = Factory::getApplication();
        $body = $app->getBody();

        $dom   = self::loadHtml($body);
        $xpath = new DOMXPath($dom);

        $nodes = $xpath->query(
            '//*[contains(concat(" ", normalize-space(@class), " "), ' . self::xpathLiteral(" {$ancestorClass} ") . ')]'
            . '//*[contains(concat(" ", normalize-space(@class), " "), ' . self::xpathLiteral(" {$className} ") . ')]'
        );

        if ($nodes === false || $nodes->length === 0) {
            return;
        }

        $targets = [];

        foreach ($nodes as $node) {
            $targets[] = $node;
        }

        foreach ($targets as $node) {
            $container = (new DOMXPath($dom))->query('.//ul', $node)->item(0) ?? $node;

            $fragment = self::fragmentFromHtml($dom, $html);

            if ($fragment === null) {
                continue;
            }

            $container->appendChild($fragment);
        }

        $app->setBody(self::saveHtml($dom));
    }

    /**
     * Append $html as the last child of <body> (used for modals).
     */
    public static function appendBeforeBodyEnd(string $html): void
    {
        if (trim($html) === '') {
            return;
        }

        $app  = Factory::getApplication();
        $body = $app->getBody();

        $dom     = self::loadHtml($body);
        $bodyTag = $dom->getElementsByTagName('body')->item(0);

        if ($bodyTag === null) {
            return;
        }

        $fragment = self::fragmentFromHtml($dom, $html);

        if ($fragment === null) {
            return;
        }

        $bodyTag->appendChild($fragment);

        $app->setBody(self::saveHtml($dom));
    }

    private static function fragmentFromHtml(DOMDocument $dom, string $html): ?\DOMDocumentFragment
    {
        $fragment = $dom->createDocumentFragment();

        libxml_use_internal_errors(true);
        $ok = $fragment->appendXML($html);
        libxml_clear_errors();

        return $ok ? $fragment : null;
    }

    private static function loadHtml(string $html): DOMDocument
    {
        $dom = new DOMDocument('1.0', 'UTF-8');

        libxml_use_internal_errors(true);
        $dom->loadHTML(
            '<?xml encoding="UTF-8">' . $html,
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
        );
        libxml_clear_errors();

        return $dom;
    }

    private static function saveHtml(DOMDocument $dom): string
    {
        $html = $dom->saveHTML();

        if ($html === false) {
            return '';
        }

        /*
         * Strip the encoding-hint processing instruction we added in
         * loadHtml(). The hint is deliberately NOT self-closed (it ends in
         * a bare ">" rather than "?" followed by ">") -- that is what makes
         * libxml treat it as an encoding declaration instead of a stray PI
         * in the body, but it also means the closing marker below must
         * match that exact, non-self-closed form.
         *
         * (Using a block comment here on purpose: a "//" line comment
         * containing the two-character PHP close-tag sequence would end
         * PHP mode early and silently break the rest of this file.)
         */
        return preg_replace('/^<\?xml encoding="UTF-8">\s*/', '', $html, 1);
    }

    private static function xpathLiteral(string $value): string
    {
        if (strpos($value, '"') === false) {
            return '"' . $value . '"';
        }

        if (strpos($value, "'") === false) {
            return "'" . $value . "'";
        }

        $parts = explode('"', $value);

        return 'concat("' . implode('", \'"\', "', $parts) . '")';
    }
}
