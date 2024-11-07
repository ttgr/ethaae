<?php
/**
 * @package       Triantis.Plugin
 * @subpackage    bie.plg_expoimagegallery
 * @copyright     Copyright (C) 2012 bie-paris.org, Inc. All rights reserved.
 * @license       GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

use Joomla\String\StringHelper;

use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\CMSPlugin;



/**
 * Example Folder Plugin
 *
 * @package      Joomla.Plugin
 * @subpackage   Folder.example
 * @since        1.5
 */
class plgContentExpoimagegallery extends CMSPlugin
{
    /**
     * Self instance
     *
     * @var plgFolderExample
     */
    public static $_self ;
    
    
     var $plg_tag    = "expoimggal";  


    
    // Content Events
    // ======================================================================================
    
    
    /**
     * Example prepare content method
     *
     * Method is called by the view
     *
     * @param    string    The context of the content being passed to the plugin.
     * @param    object    The content object.  Note $article->text is also available
     * @param    object    The content params
     * @param    int       The 'page' number
     * @since    1.6
     */
    public function onContentPrepare($context, &$article, &$params, $page=0)
    {
        $not_allowed_contexts = array('com_content.category', 'com_content.article', 'com_content.featured');

        if (in_array($context, $not_allowed_contexts))
        {
            return true;
        }

        if(StringHelper::strpos($article->text, $this->plg_tag) === false) return;

		// expression to search for
                //
		$regex = "#{".$this->plg_tag."}(.*?){/".$this->plg_tag."}#s";

		// find all instances of the plugin and put them in $matches
		preg_match_all($regex,$article->text,$matches);
                
		// Number of plugins
		$count = count($matches[1]);
                
                   
		// Plugin only processes if there are any instances of the plugin in the text
		if(!$count) return; 

                
                
                //JPlugin::loadLanguage('plg_content_expoid', JPATH_SITE);
                $renderer = JFactory::getDocument()->loadRenderer('module');
                $module = &JModuleHelper::getModule( 'amazingslider');
                $options  = array('style' => 'raw');
                
                foreach ($matches[1] as $tagcontent) { 
                    
                    $pars       = explode(":", $tagcontent);
                    $path       = $pars[0];
                    $width      = (isset($pars[1])) ? $pars[1] : $this->pluginParams->get('width');
                    $height     = (isset($pars[2])) ? $pars[2] : $this->pluginParams->get('height');
                    $isfull     = (isset($pars[3])) ? $pars[3] : $this->pluginParams->get('is_full');
                    $navstyle   = (isset($pars[4])) ? $pars[4] : 'none';
                    $arrowstyle = (isset($pars[5])) ? $pars[5] : 'mouseover';
                    $autoplay   = (isset($pars[6])) ? $pars[6] : 1;
                    $is_second  = (isset($pars[7])) ? $pars[7] : 0;
                    $id         = ($is_second) ? rand(2,1000) : 1;
                    $read_more  = (isset($pars[8])) ? $pars[8] : 0;
                    
                    $mod_param    = array(
                        "module_id" => $id,
                        "width" => $width,
                        "height" => $height,
                        "mfolder" => $path,
                        "folder_path" => $this->pluginParams->get('folder_path'),
                        "is_full" => $isfull,
                        "from_url" => 0,
                        "module_tag" => "div",
                        "bootstrap_size" => 0,
                        "header_tag"=>"h3",
                        "header_class"=>"",
                        "style"=>0,
                        "navstyle"=>$navstyle,
                        "arrowstyle"=>$arrowstyle,
                        "autoplay"=>$autoplay,
                        "is_second"=>$is_second,
                        "read_more"=>$read_more
                    );
                    

                    
                    $mod_params = array('params' => json_encode($mod_param));
                    $content = $renderer->render($module, $mod_params, $options);
                    
                    $article->text = str_replace( "{".$this->plg_tag."}".$tagcontent."{/".$this->plg_tag."}", $content, $article->text );                 
                }        
    }
    

    
    
}
