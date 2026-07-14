<?php

/**
 * @package     PLG_KUNENA_JNLSOLVED
 * @author      Team Joomla!NL
 * @url         https://www.joomlanl.nl/
 * @copyright   Copyright (C) Team Joomla!NL. All rights reserved.
 * @license     GNU General Public License version 3 or later; see LICENSE.txt
 *
 * @var string $warningTitle
 * @var string $solveBodyPart1
 * @var string $solveBodyPart2
 * @var string $confirmLabel
 * @var string $closeTopic
 * @var string $reopenBody
 * @var string $yes
 * @var string $no
 *
 * NOTE: this markup is parsed as XML when injected into the page (see
 * src/Render.php), so it must be well-formed XHTML. Use a literal "×"
 * character instead of the &times; named entity: named HTML entities are
 * not defined in XML and would break the fragment parser.
 */

defined('_JEXEC') or die();

$esc = static fn (string $value): string => htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
?>
<div class="modal fade" id="topicSolvedModal" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="btn btn-danger" data-bs-dismiss="modal">&#215;</button>
                <h4 class="modal-title"><?php echo $esc($warningTitle); ?></h4>
            </div>
            <div class="modal-body">
                <p><?php echo $esc($solveBodyPart1); ?></p>
                <p><?php echo $esc($solveBodyPart2); ?></p>
            </div>
            <div class="modal-footer">
                <div class="custom-control custom-switch">
                    <input type="checkbox" class="custom-control-input" id="cbCorrectMarked" />
                    <label class="custom-control-label" for="cbCorrectMarked"><?php echo $esc($confirmLabel); ?></label>
                </div>
                <button type="button" class="btn btn-success" id="btnCloseModal"><?php echo $esc($closeTopic); ?></button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="topicReopenModal" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="btn btn-danger" data-bs-dismiss="modal">&#215;</button>
                <h4 class="modal-title"><?php echo $esc($warningTitle); ?></h4>
            </div>
            <div class="modal-body">
                <p><?php echo $esc($reopenBody); ?></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-success" id="btnReopenModal"><?php echo $esc($yes); ?></button>
                <button type="button" class="btn btn-danger" data-bs-dismiss="modal"><?php echo $esc($no); ?></button>
            </div>
        </div>
    </div>
</div>
