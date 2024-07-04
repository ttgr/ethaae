<?php

/**
 * Kunena Component
 *
 * @package         Kunena.Template.Aurelia
 * @subpackage      BBCode
 *
 * @copyright       Copyright (C) 2008 - 2024 Kunena Team. All rights reserved.
 * @license         https://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link            https://www.kunena.org
 **/

namespace Kunena\Forum\Site;

\defined('_JEXEC') or die();

$attachment = $this->attachment;
$location   = $attachment->getUrl();

if (!$attachment->isVideo()) {
    return;
}
?>
<div class="clearfix"></div>
<?php if (!$attachment->inline) { ?>
<video width="15%" src="<?php echo $location; ?>" controls>
    Your browser does not support the <code>video</code> element.
</video>
<p><?php echo $attachment->getShortName(); ?> <a href="<?php echo $location; ?>" data-bs-toggle="tooltip" title="Download" download> <i
                class="icon icon-download"></i></a></p>
<div class="clearfix"></div>
<?php 
} else {
?>
<video width="100%" src="<?php echo $location; ?>" controls>
    Your browser does not support the <code>video</code> element.
</video>
<p><?php echo $attachment->getShortName(); ?> <a href="<?php echo $location; ?>" data-bs-toggle="tooltip" title="Download" download> <i
                class="icon icon-download"></i></a></p>
<div class="clearfix"></div>
<?php 
} 
?>