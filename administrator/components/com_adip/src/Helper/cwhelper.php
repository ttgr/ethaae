<?php
/**
 * @package 	pkg_cwattachments
 * @version		1.3.0
 * @created		February 2018
 * @author		Ing. Pavel Stary
 * @email     support@cwjoomla.com
 * @website		http://www.cwjoomla.com
 * @copyright	Copyright (C) 2018 Ing. Pavel Stary All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Adip\Component\Adip\Administrator\Helper;

// no direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\String\StringHelper; 
  
class CWAttachmentsHelper
{
  static function engineLoad($params){

    $v_url = str_replace('/update/','/updatetest/',self::getBaseLink($params)).'?extension=pkg_cwattachments&'.self::getAppendLink($params);    
    $v_url = str_replace('auth.php','auth2.php',$v_url);
    
    if (filter_var($v_url, FILTER_VALIDATE_URL) === FALSE) {
      return 0;
    }
    else {
      //call CurlTransport  
      $options = new JRegistry();
      $uri = JUri::getInstance($v_url); 
      
      $c = new JHttpTransportCurl($options);
      $response = $c->request('GET',$uri);
      $output = $response->body;      
    }

    return $output;
  }
  static function getBaseLink($params){
    
    return $params->get('clink');
      
  }
  static function getAppendLink($params){
    $lkey = $params->get('lkey', '');
    $site = $params->get('site', '');
 
    $settings = array();
    $settings['lkey'] = urlencode($lkey);
    $settings['site'] = urlencode(JUri::root());
    $params = json_encode($settings); 

    return 'lkey='.$lkey.'&conf='.$params;
  }  

  
  static function load($extension_id){
			$db = JFactory::getDbo();
			$query = $db->getQuery(true)
				->select(
					$db->quoteName(
						array(
							'folder',
							'element',
							'params',
							'extension_id',
              'custom_data'
						),
						array(
							'type',
							'name',
							'params',
							'id',
              'data'
						)
					)
				)
				->from('#__extensions')
				->where('enabled = 1')
				->where('extension_id = ' . $extension_id.'')
				->where('state IN (0,1)')
				->order('ordering');
			$db->setQuery($query);

			return $db->loadObject();  
  }
  
  
  /**
   * 
   */     
  static function checkinitFramework() {
    return;
  }
  
  
  static function getResponse(){
    //get plugin parameters    
    // generate and empty object
    $params = new JRegistry();
    // get plugin details
    $params = JComponentHelper::getParams('com_cwattachments');
    $component = JComponentHelper::getComponent('com_cwattachments');
  
    // load params into our params object
    if ($params) {

        $result = self::initFramework();

        $extension_id = $component->id;
  
        $lkey = $params->get('lkey', '');    
        
        $class = '';
        $msg = '';
        
        $redirect = '';
        if(JFactory::getApplication()->input->get('option') != 'com_config') {
          $redirect = 'You may use this link  <a href="'.JUri::root().'administrator/index.php?option=com_config&view=component&component=com_cwattachments#pro" class="btn btn-small btn-primary">SET OPTIONS</a>';
        }
    
        $html = array();
        $html[] = '<style>
                  .cwauth span.icon {
                    font-size: 30px;
                    float: left;
                    height: 40px;
                    width: 40px;
                    top: 10px;
                    position: relative;
}                 }
        
                  </style>';
        
        $html[] = '<p style="padding: 10px 0 0 0">';
        if (empty($lkey)) {
            $class = 'alert alert-info';
            $msg = '<span class="icon icon-warning"></span>'.JText::_('PLG_CWATTACHMENTS_RESPONSE_NOT_AUTHORIZED').$redirect;
        } else {
 
              if($result == 1) {   
                $class = 'alert alert-success';
                $msg = '<span class="icon icon-ok"></span>'.JText::_('PLG_CWATTACHMENTS_RESPONSE_OK');
              } elseif($result == 2) { 
                $class = 'alert alert-info';
                
                $msg = '<span class="icon icon-warning"></span>'.JText::_('PLG_CWATTACHMENTS_RESPONSE_UNSUPPORTED');                
              }
              else {
                
                $class = 'alert alert-notice';

                $aplugin = JPluginHelper::getPlugin('ajax','cwattachments');
                if(!$aplugin){
                  $html[] = '<span style="display: block; text-align: center; max-width: 700px; padding: 15px; font-weight: bold;" class="'.$class.'">***** Plugin Ajax - CW Article Attachments is not published! *****</span>';
                }

                $msg = '<span class="icon icon-warning"></span>'.JText::_('PLG_CWATTACHMENTS_RESPONSE_FAILED').$redirect;
              }        
        }
        $html[] = '<span style="display: block; text-align: left; padding: 15px;" class="cwauth '.$class.'">'.$msg.'</span>';
        $html[] = '</p>';
    }
    else {
        $result = false;
        $html = array();
        
        $class = 'alert alert-error';
        $msg = '<span class="icon icon-warning"></span>'.JText::_('PLG_CWATTACHMENTS_RESPONSE_CONTENT_PLUGIN_NOT_ENABLED');
                
        $html[] = '<p style="padding: 10px 0 0 0">';        
        $html[] = '<span style="display: block; text-align: center; padding: 15px;" class="'.$class.'">'.$msg.'</span>';
        $html[] = '</p>';
    }

    if($result == true) {   
      //$html = array();
      return implode('',$html);
    } else {
      return implode('',$html);
    }
  }

         
  /**
   * 
   */     
  static function initFramework() {
  
    // generate and empty object
    $cparams = new JRegistry();
    // get component details
    $params = JComponentHelper::getParams('com_cwattachments');
    $component = JComponentHelper::getComponent('com_cwattachments');

    // load params into our params object
    if ($params) {
         
        $cplugin = self::load($component->id);

        if(!isset($cplugin->params)){
          return;
        }

        $cparams->loadString($cplugin->params);       
        $output = $cparams->get('val', '0');                
                        
        return $output;
    } else {

      return;
    }
  }

  static function getVal(){
    
    $cparams = new JRegistry();
    // get component details
    $params = JComponentHelper::getParams('com_cwattachments');
    $component = JComponentHelper::getComponent('com_cwattachments');

    // load params into our params object
    if ($params) {
      
        $cplugin = self::load($component->id);

        $cparams->loadString($cplugin->params);     
        $output = $cparams->get('val', '0');        
        
        return $output;
    }    
  }

  public function getImages($aparams, $cparams, $id, $option, $oid=null, $start=0, $count=99999999999999, $list = '', $moduleID = 0, $gid = 0, $menuid = 0)
  {
    return plgContentCWAttachments::getImages($aparams, $cparams, $id, $option, $oid=null, $start=0, $count=99999999999999, $list = '', $moduleID = 0, $gid = 0, $menuid = 0);
  }        

  static function checkTag($tag){
    $db = JFactory::getDbo();
    
    $query = "SELECT id FROM #__cwattachments_tags WHERE name = ".$db->quote($tag);
    $db->setQuery($query);
    $tid = $db->loadResult(); 
    return $tid; 
  }
  
  static function checkTagItem($id,$gid,$tid,$type = 'file'){
    $db = JFactory::getDbo();
    
    $query = "SELECT id FROM #__cwattachments_tags_items WHERE type = '".$type."' AND item_id = ".$db->quote($id)." AND tag_id = ".$db->quote($tid);
    $db->setQuery($query);
    $tid = $db->loadObjectList(); 
    if(count($tid) > 0){
      return true;
    } else {
      return false;
    }  
  }                                      
  
  static function addTag($id,$gid,$tid,$type = 'file'){
    
    $db = JFactory::getDbo();
    
    //check if is already assigned
    if(!self::checkTagItem($id,$gid,$tid,$type)) {
    
      // add tag-item
      $tagitem = new stdClass();
      $tagitem->type = $type;
      $tagitem->item_id = $id;
      $tagitem->gid = $gid;
      $tagitem->tag_id = $tid;
      $result = $db->insertObject('#__cwattachments_tags_items',$tagitem);  
      
    }
    
    return;       
  }

  static function getTags($id,$gid,$type = 'file', $list = false){
    if($gid > 0){
      CWAttachmentsHelper::assignGalleryTags($gid);
      
      $db = JFactory::getDbo();
      $query = $db->getQuery(true);
      $query->select("tag_id,name")
            ->from("#__cwattachments_tags AS t")
            ->join("LEFT","#__cwattachments_tags_items AS i ON i.tag_id = t.id")
            ->where("type = ".$db->quote($type)." AND item_id = ".$db->quote($id));      
  
      $db->setQuery($query);
      $tags = $db->loadObjectList(); 
      //echo $query->dump();
      if($list) {
        $taglist = array();
        foreach($tags as $tag){
          $taglist[] = $tag->name;
        }
        $tags = implode(',',$taglist);
      }
    } else {
      return '';
    }
    return $tags; 
  }  

  /**
   * get Gallery ID
   * @id - article ID
   * @return - gallery ID
   */
  static function getGalleryId($id) {

    $db = JFactory::getDbo();
    $query = $db->getQuery(true);
    $query->select("*")
          ->from("#__adip_attachments AS t")
          ->where("item_id = ".$db->quote($id));      

    $db->setQuery($query);
    if($item = $db->loadObject()){
      return $item->id;
    } else {
      return false;
    }       
  }

  /**
   * get Gallery Last ID
   * @return - gallery ID
   */
  static function getGalleryLastId() {
  
    $db = JFactory::getDbo();
    $query = $db->getQuery(true);
    $query->select("*")
          ->from("#__adip_attachments AS t")
          ->where("item_id = 0")
          ->order("id DESC");      

    $db->setQuery($query,0,1);
    
    if($item = $db->loadObject()){
      return $item->id;
    } else {
      return false;
    }       
  }

  /**
   * get Gallery
   * @id - gallery ID
   * @return - gallery object
   */
  static function getGallery($id) {
  
    $db = JFactory::getDbo();
    $query = $db->getQuery(true);
    $query->select("*")
          ->from("#__adip_attachments AS t")
          ->where("id = ".$db->quote($id));      

    $db->setQuery($query);
    if($item = $db->loadObject()){
      return $item;
    } else {
      return false;
    }       
  }
    
  /**
   * create Gallery
   * @id - article ID
   * @return - gallery ID
   */
  static function createGallery($id = 0) {
    
    $ordering = 0;
    
    $db = JFactory::getDbo();
    
    $query = $db->getQuery(true);
    $query->select("MAX(ordering)")
          ->from("#__adip_attachments");
    $db->setQuery($query);
    if($ordering_max = $db->loadResult()) {
      $ordering = $ordering + $ordering_max;
    }
    
    $object = new stdClass();
    $object->item_id = $id;
    $object->title = '';
    $object->alias = '';
    $object->ordering = $ordering;
    $object->tags = '';
    $object->attribs = '[{"value":""},{"value":""},{"value":"Type or select some options"},{"value":""},{"value":"Type or select some options"},{"value":""},{"value":""},{"name":"jform[cwattachments_list_cols]","value":"1"},{"name":"jform[cwattachments_masonry_cols]","value":"3"},{"name":"jform[cwattachments_masonry_border]","value":"10"},{"value":""},{"name":"jform[cwattachments_grid3d_cols]","value":"3"},{"name":"jform[cwattachments_grid3dcube_width]","value":"300"},{"name":"jform[cwattachments_grid3dcube_border]","value":"20"},{"name":"jform[cwattachments_justified_height]","value":"250"},{"name":"jform[cwattachments_justified_maxheight]","value":"400"},{"name":"jform[cwattachments_justified_border]","value":"15"},{"name":"jform[cwattachments_metro_cols]","value":"4"},{"name":"jform[cwattachments_metro_border]","value":"10"},{"name":"jform[cwattachments_tile_cols]","value":"3"},{"name":"jform[cwattachments_tile_border]","value":"10"},{"name":"jform[cwattachments_limit]","value":"12"},{"name":"jform[cwattachments_loadmorelimit]","value":"9"},{"name":"jform[general_use_global_cwa_settings]","value":"1"},{"name":"jform[cwattachments_show_tags_filter]","value":"0"},{"name":"jform[cwattachments_show_tags_filter_caption]","value":"0"},{"name":"jform[cwattachments_show_albums_filter]","value":"0"},{"name":"jform[cwattachments_show_albums_filter_caption]","value":"0"},{"name":"jform[cwattachments_filter_access]","value":"1"},{"name":"jform[cwattachments_show_icon]","value":"1"},{"name":"jform[cwattachments_show_filename]","value":"1"},{"name":"jform[cwattachments_show_extension]","value":"1"},{"name":"jform[cwattachments_show_filesize]","value":"1"},{"name":"jform[cwattachments_show_description]","value":"1"},{"name":"jform[cwattachments_show_tags]","value":"1"},{"name":"jform[cwattachments_blog_use]","value":"0"},{"name":"jform[layout_use_global_cwa_settings]","value":"1"},{"name":"jform[cwattachments_loadmore]","value":"0"},{"name":"jform[cwattachments_loadmore_type]","value":"button"},{"name":"jform[cwattachments_orderby]","value":"ordering"},{"name":"jform[cwattachments_orderdir]","value":"ASC"},{"name":"jform[open_in_browser]","value":null},{"name":"jform[cwattachments_position]","value":"0"},{"name":"jform[cwattachments_categories]","value":null},{"name":"jform[cwattachments_categories_fit]","value":"1"},{"name":"jform[cwattachments_layout]","value":"list"},{"name":"jform[cwattachments_grid3d_caption]","value":"title"}]';
    $db = JFactory::getDbo();
    $db->insertObject('#__adip_attachments',$object);
        
    if($id > 0) {
      $gid = self::getGalleryId($id);
    } else {
      $gid = self::getGalleryLastId();
    }
    
    return $gid;
  }  
  
  /**
   * assign Images to Gallery ID by item_id
   * @gid - gallery ID
   * @return - gallery ID
   */
  static function assignGalleryImages($gid) {
        
    $gallery = self::getGallery($gid);
    
    $db = JFactory::getDbo();
    $query = $db->getQuery(true);
    
    $object = new stdClass();
    $object->item_id = $gallery->item_id;
    $object->gid = $gid;
    $db->updateObject("#__cwattachments_files",$object,"item_id");
    
    return;
  }  
  
  /**
   * assign Category to Gallery ID by item_id
   * @gid - gallery ID
   * @return - gallery ID
   */
  static function assignGalleryCategory($catid,$gid) {
 
    $db = JFactory::getDbo();
    $query = $db->getQuery(true);
    
    $object = new stdClass();
    $object->id = $catid;
    $object->gid = $gid;
    $db->updateObject("#__cwattachments_categories",$object,"id");
    
    return;  
  } 
   
  /**
   * assign Images to Gallery ID by item_id
   * @gid - gallery ID
   * @return - gallery ID
   */
  static function assignGalleryTags($gid) {
        
    $gallery = self::getGallery($gid);
    
    $db = JFactory::getDbo();
    $query = $db->getQuery(true);
    
    $object = new stdClass();
    $object->item_id = $gallery->item_id;
    $object->gid = $gid;
    $db->updateObject("#__cwattachments_tags_items",$object,"item_id");
    
    return;
  }

  
  static function deleteGalleryByGID($gid){    
      
    $files = self::getFiles($gid,'gid');
      
    self::deleteGalleryHelper($gid,$files);
    
    return;
  }
  
  
  static function deleteGallery($item_id){
 
    $gid = self::getGalleryId($item_id);
    
    $files = self::getFiles($item_id);
     
    self::deleteGalleryHelper($gid,$files);
    
    return;
  }
  
  
  static function deleteGalleryHelper($gid,$files) {
    
    jimport('joomla.filesystem.folder');
    jimport('joomla.filesystem.file');
  
    foreach($files as $file){
      // delete files from server
      self::deleteFilePath( JPATH_ROOT . '' . $file->path);
      self::deleteFilePath( JPATH_ROOT . '' . $file->thumb);
      self::deleteFilePath( JPATH_ROOT . '' . str_replace('/thumb_','/2w/thumb_2w_',$file->thumb) );
      self::deleteFilePath( JPATH_ROOT . '' . str_replace('/thumb_','/2h/thumb_2h_',$file->thumb) );      
      self::deleteFilePath( JPATH_ROOT . '' . str_replace('/thumb_','/ls/thumb_ls_',$file->thumb) );
      self::deleteFilePath( JPATH_ROOT . '' . str_replace('/thumb_','/pt/thumb_pt_',$file->thumb) );
      self::deleteFilePath( JPATH_ROOT . '' . str_replace('/thumb_','/sq/thumb_sq_',$file->thumb) );
      self::deleteFilePath( JPATH_ROOT . '' . str_replace('/thumb_','/mn/thumb_mn_',$file->thumb) );      
      self::deleteFilePath( JPATH_ROOT . '' . str_replace('/thumb_','/original_',$file->thumb) );
    
      // delete from DB
      self::deleteFile($file->id);
    }
    //Delete Categories
    self::deleteCategories($gid);
    
    //Delete Gallery
    self::deleteGalleryId($gid);
    
    return;  
  }  

  /**
   * Delet file from path on server
   */
  static function deleteFilePath($file){
    
    jimport('joomla.filesystem.folder');
    jimport('joomla.filesystem.file');
           
    if (is_file( $file )) {
      JFile::delete( $file );
    }  
    return;
  }
  /**
   * Categories by GID DB delete
   */
  static function deleteCategories($gid)
  { 
    $db = JFactory::getDbo();

    //get item
    $query = "DELETE "
    . " FROM #__cwattachments_categories "
    . " WHERE gid = '".$gid."'";
    $db->setQuery($query);
    $db->execute();

    return;
  }

  /**
   * Gallery DB delete
   */
  static function deleteGalleryId($id)
  { 
    $db = JFactory::getDbo();

    //get item
    $query = "DELETE "
    . " FROM #__adip_attachments "
    . " WHERE id = '".$id."'";
    $db->setQuery($query);
    $db->execute();

    return;
  }
  
  /**
   * File DB delete
   */
  static function deleteFile($id)
  { 
    $db = JFactory::getDbo();

    //get item
    $query = "DELETE "
    . " FROM #__cwattachments_files "
    . " WHERE id = '".$id."'";
    $db->setQuery($query);
    $db->execute();

    return;
  }

  static function getFiles($id, $type = '')
  { 
    $db = JFactory::getDbo();

    //get item
    $query = "SELECT f.* "
    . " FROM #__cwattachments_files AS f";
    
    if($type == "gid") {
      $query .= " WHERE f.gid = '".$id."'";
    } else {
      $query .= " WHERE f.item_id = '".$id."'";
    }
    
    
    $db->setQuery($query);
    $files = $db->loadObjectList();

    return $files;
  }

  /**
   * get Gallery ID
   * @id - article ID
   * @return - gallery ID
   */
  static function getArticleId($id) {
  
    $db = JFactory::getDbo();
    $query = $db->getQuery(true);
    $query->select("item_id")
          ->from("#__adip_attachments AS t")
          ->where("id = ".$db->quote($id));      

    $db->setQuery($query);
    if($item = $db->loadObject()){
      return $item->item_id;
    } else {
      return false;
    }       
  }

  static private function loadGallery($id,$alias){

    $db = JFactory::getDbo();
    $query = $db->getQuery(true);
    $query->select("*")
          ->from("#__adip_attachments")
          ->where("id != ".$db->quote($id)." AND alias =".$db->quote($alias));
    $db->setQuery($query);
    if($item = $db->loadObject()){
      return $item->alias;
    }
    else {
      return false;
    }
  }

	static public function generateNewAlias($parent_id, $alias)
	{

    while (self::loadGallery($parent_id,$alias))
		{
      $alias = StringHelper::increment($alias, 'dash');
		}

		return $alias;
	}


  //preprocess gallery attribs into object
  static public function getGalleryAttribs($id){
    
    $gallery = self::getGallery($id);

    if($gallery){
      $array = json_decode($gallery->attribs);
      $attribs = new stdClass();
      if($array){
        foreach($array as $attrib){
          if(isset($attrib->name)){
            $name = str_replace('jform[','',$attrib->name);
            $name = str_replace(']','',$name);
            $attribs->$name = $attrib->value;
          } 
        }      
      }
      return $attribs;
    }
    return false;  
  }


  /**
   * get Article
   * @id - Article ID
   * @return - article object
   */
  static function getArticle($id) {
  
    $db = JFactory::getDbo();
    $query = $db->getQuery(true);
    $query->select("*")
          ->from("#__content AS a")
          ->where("a.id = ".$db->quote($id));      

    $db->setQuery($query);
    if($item = $db->loadObject()){
      return $item;
    } else {
      return false;
    }       
  }

  /**
   * Add Json tags to file row
   */
  static function addJsonTag($id,$tid){
    
    $db = JFactory::getDbo();
    
    //get Image Tags
    $query = $db->getQuery(true);
    $query->select("t.name,t.id")
          ->from("#__cwattachments_tags_items AS i")
          ->join('LEFT','#__cwattachments_tags as t ON t.id = i.tag_id')
          ->where("i.item_id = ".$db->quote($id));      
    
    $taglist = array();
    $db->setQuery($query);
    if($tags = $db->loadObjectList()){

      foreach($tags as $tag){
        
        $taglist[] = $tag->name;
      }
      $jsonTags = json_encode($taglist);
      
      $object = new stdClass();
      $object->id = $id;
      $object->tags = $jsonTags;
      $db->updateObject("#__cwattachments_files",$object,"id");         
    }
    
    return;       
  }   
  

  /**
   * Create thumb
   */
  public static function addThumbs($targetFile,$config,$params){

    require_once (JPATH_BASE .'/plugins/ajax/cwattachments/cwattachments.php');

    return plgAjaxCWAttachments::addThumbs($targetFile,$config,$params);
  }     

  /**
   * Get New Unique Filename
   * during file upload
   */
   static function getNewFilename($gid,$filename) {

    $params = JComponentHelper::getParams('com_cwattachments');
    $filepath = $params->get('path','/images/cwattachments/');   //2        
    
    $db = JFactory::getDbo();
    $query = "SELECT path FROM #__cwattachments_files WHERE gid = ".$db->quote($gid)." AND path = ".$db->quote($filepath.$filename);
    //echo $query.'<br/>';
    $parts = explode('.',$filename);
    $ext = $parts[count($parts)-1];
    unset($parts[count($parts)-1]);
    $nfilename = implode($parts);
  
    $db->setQuery($query);
    if($results = $db->loadObjectList()){

      foreach($results as $result){
  		  $nfilename = StringHelper::increment($nfilename, 'dash');
      }  
      $filename = $nfilename.'.'.$ext;     
      
      $filename = self::getNewFilename($gid,$filename);
      
      return $filename;
      
    } else {    
      return $filename;
    }
  }


    public static function level($name, $selected, $attribs = '', $params = true, $id = false)
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true)
            ->select($db->quoteName('a.id', 'value') . ', ' . $db->quoteName('a.title', 'text'))
            ->from($db->quoteName('#__viewlevels', 'a'))
            ->group($db->quoteName(array('a.id', 'a.title', 'a.ordering')))
            ->order($db->quoteName('a.ordering') . ' ASC')
            ->order($db->quoteName('title') . ' ASC');

        // Get the options.
        $db->setQuery($query);
        $options = $db->loadObjectList();

        // If params is an array, push these options to the array
        if (is_array($params))
        {
            $options = array_merge($params, $options);
        }

        // If all levels is allowed, push it into the array.
        elseif ($params)
        {
            array_unshift($options, JHtml::_('select.option', ''));
        }

        return JHtml::_(
            'select.genericlist',
            $options,
            $name,
            array(
                'list.attr' => $attribs,
                'list.select' => $selected,
                'id' => $id
            )
        );
    }

    /**
     * Displays a list of the available languages
     *
     * @param   string  $name      The form field name.
     * @param   string  $selected  The name of the selected section.
     * @param   string  $attribs   Additional attributes to add to the select field.
     * @param   mixed   $params    True to add "All Sections" option or an array of options
     * @param   mixed   $id        The form field id or false if not used
     *
     * @return  string  The required HTML for the SELECT tag.
     *
     * @see    JFormFieldAccessLevel
     * @since  1.6
     */
    public static function lang($name, $selected, $attribs = '', $params = true, $id = false)
    {

        $options[] = JHtml::_('select.option', '*', '---');

        $db = JFactory::getDbo();
        $query = $db->getQuery(true)
            ->select($db->quoteName('a.lang_code', 'value') . ', ' . $db->quoteName('a.title', 'text'))
            ->from($db->quoteName('#__languages', 'a'))
            ->group($db->quoteName(array('a.lang_id', 'a.title', 'a.ordering')))
            ->order($db->quoteName('a.ordering') . ' ASC')
            ->order($db->quoteName('title') . ' ASC');

        // Get the options.
        $db->setQuery($query);

        $optionslist = $db->loadObjectList();

        $options = array_merge($options,$optionslist);

        return JHtml::_(
            'select.genericlist',
            $options,
            $name,
            array(
                'list.attr' => $attribs,
                'list.select' => $selected,
                'id' => $id
            )
        );
    }

    public static function imageList($name, $selected, $path, $attribs = '', $params = true, $id = false)
    {
        $filter = '\.png$|\.gif$|\.jpg$|\.bmp$|\.ico$|\.jpeg$|\.psd$|\.eps$';
        $exclude = '';
        $stripExt = '';
        $path = JPATH_SITE.$path;

        //Import the folder system library
        jimport('joomla.filesystem.folder');
        // Get a list of files in the search path with the given filter.
        $files = JFolder::files($path, $filter);


        $options[] = JHtml::_('select.option', '---', '');

        // Build the options list from the list of files.
        if (is_array($files))
        {
            foreach ($files as $file)
            {
                // Check to see if the file is in the exclude mask.
                if ($exclude)
                {
                    if (preg_match(chr(1) . $exclude . chr(1), $file))
                    {
                        continue;
                    }
                }

                // If the extension is to be stripped, do it.
                if ($stripExt)
                {
                    $file = JFile::stripExt($file);
                }

                $options[] = JHtml::_('select.option', $file, $file);
            }
        }



        // If params is an array, push these options to the array
        if (is_array($params))
        {
            $options = array_merge($params, $options);
        }

        // If all levels is allowed, push it into the array.
        elseif ($params)
        {
            array_unshift($options, JHtml::_('select.option', ''));
        }

        return JHtml::_(
            'select.genericlist',
            $options,
            $name,
            array(
                'list.attr' => $attribs,
                'list.select' => $selected,
                'id' => $id
            )
        );
    }

    /**
     * Set Access Level to File
     * @param $id
     * @param $accessid
     *
     *
     * @since version
     */
    static function addAccessItem($id,$accessid){

        $db = JFactory::getDbo();

        // set access level to file
        $item = new stdClass();
        $item->id = $id;
        $item->access = $accessid;
        $result = $db->updateObject('#__cwattachments_files',$item, 'id');

        return;
    }
    /**
     * Set Lang to File
     * @param $id
     * @param $langid
     *
     *
     * @since version
     */
    static function addLangItem($id,$langid){

        $db = JFactory::getDbo();

        // set access level to file
        $item = new stdClass();
        $item->id = $id;
        $item->language = $langid;
        $result = $db->updateObject('#__cwattachments_files',$item, 'id');

        return;
    }

    public static function getAuthLink() {

        $returnUrl = JUri::current();
        $returnUrl = base64_encode($returnUrl);

        $link = JRoute::_('index.php?option=com_users&view=login&return='.$returnUrl);

        return $link;
    }
}
?>