<?php
/**
 * @version    CVS: 1.1.0
 * @package    Com_Ethaae_units_info
 * @author     Tasos Triantis <tasos.tr@gmail.com>
 * @copyright  2024 Tasos Triantis
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;

use \Joomla\CMS\HTML\HTMLHelper;
use \Joomla\CMS\Factory;
use \Joomla\CMS\Uri\Uri;
use \Joomla\CMS\Router\Route;
use \Joomla\CMS\Language\Text;
use \Joomla\CMS\Session\Session;
use Joomla\Utilities\ArrayHelper;
use \Joomla\CMS\Layout\LayoutHelper;

HTMLHelper::_('bootstrap.tooltip');
HTMLHelper::_('behavior.multiselect');
HTMLHelper::_('formbehavior.chosen', 'select');

$wa = $this->document->getWebAssetManager();
$wa->useStyle('com_ethaae_units_info.list');

//dump($this->item);
$link = HTMLHelper::_('link', $this->item->{'url_'.$this->item->langTag[0]},$this->item->{'url_'.$this->item->langTag[0]}, ['target' => 'helpFrame']);

?>

<?php if($this->item->imggallery) : ?>
    <div class="image-gallery">
        <?php echo $this->item->imggallery; ?>
    </div>
<?php endif; ?>

<div class="article-content">
        <div class="page-header">
            <h1 itemprop="headline" class="title">
                <?php echo $this->item->info->{'title_'.$this->item->langTag[0]}; ?>
            </h1>
        </div>

        <div class="row">
            <div class="col-md-3 unit-logo">
                <div class="image-wraper">
                    <img src="<?php echo $this->item->info->logo; ?>" width="100" alt="<?php echo $this->item->info->{'title_'.$this->item->langTag[0]}; ?>" />
                </div>
            </div>
            <div class="col-md-9">
                <div itemprop="articleBody" class="article-body">
                    <?php echo $this->item->{'description_'.$this->item->langTag[0]}; ?>
                </div>
                <div class="readmore">
                    <?php echo Text::sprintf('COM_ETHAAE_UNITS_INFO_INSTITUTESINFOS_SITE',$link); ?>
                </div>
            </div>
        </div>
</div>

<?php echo LayoutHelper::render('default_reports', array('view' => $this->item->reports), dirname(__FILE__)); ?>


<?php  if(isset($this->item->depts) && count($this->item->depts) >0 ) : ?>

    <?php echo LayoutHelper::render('default_reports_2', array('view' => $this->item->depts), dirname(__FILE__)); ?>

<?php endif;

