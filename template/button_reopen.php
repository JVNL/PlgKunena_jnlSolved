<?php

/**
 * @package     PLG_KUNENA_JNLSOLVED
 * @author      Team Joomla!NL
 * @url         https://www.joomlanl.nl/
 * @copyright   Copyright (C) Team Joomla!NL. All rights reserved.
 * @license     GNU General Public License version 3 or later; see LICENSE.txt
 *
 * @var string $label
 */

defined('_JEXEC') or die();
?>
<li>
    <a role="button" class="kbtn kbtn-small openmodal jnlReopenBtn" data-bs-toggle="modal" data-bs-target="#topicReopenModal" rel="nofollow" href="#">
        <i class="fa fa-unlock" aria-hidden="true"></i><span><?php echo htmlspecialchars($label, ENT_QUOTES, 'UTF-8'); ?></span>
    </a>
</li>

