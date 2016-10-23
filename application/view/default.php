<?php

/**
 * default
 * @package      CloShare v1.00
 * @copyright    2013
 * @license      MIT
 * @author      xneda.com
 * @version $Id: default.php UTF-8 , 22-Jun-2013 | 15:25:03 nwdo ε
 */
if (!defined("_SECURE_PHP"))
    die('Direct access to this location is not allowed.');
?>
<div class="row-fluid">
    <div id="viewport" class="view_port tab-content cont well view_port_fixed">
       <ul id="breadcrumb" class="explain pull-left breadcrumb"></ul>
       
       <div class="tab-pane viewbox" id="upload"><div class="inner"></div></div>
       <div class="tab-pane active viewbox linksc" id="list"><div class="inner"></div></div>
        
<?php if($user->isAdmin()):?>
        <div class="tab-pane viewbox settings" id="users"><div class="inner"></div></div>
        <div class="tab-pane viewbox settings" id="settings"><div class="inner"></div></div>
<?php endif;?>
        <div class="tab-pane viewbox settings" id="profile"><div class="inner"></div></div>
    </div>
    <div id="sbar" class="<?php echo ($detect->isMobile() && !$detect->isTablet() ? 'dn' : '');?>">
        <div id="sidebar" class="sidebar-nav">
            <div class="navbar">
                <ul>
                    <li class="files well dnone">
                       <div id="listShowOpt" class="row-fluid">
                           <h4 class="pull-left nav-header" style="position: relative">Show :</h4>
                        <div class="btn-group pull-right listShowOpt" data-toggle="buttons-radio">
                            <button class="btn filter" data-target="file">Only Files</button>
                            <button class="btn active filter">All</button>
                        </div>
                       </div>
                    </li>
                    <li id="foldersdropdown" class="files well dnone">
                       <div class="row-fluid">
                          <h4 class="nav-header">Folders <i class="icon-question-sign tip" title="To move a folder to another location, you can use the toolbar which shows up when rollover it."></i></h4>
                          <div class="inner"></div>
                    </li>
                    
                    <li id="filesdropdown" class="files well dnone dyn stc">
                        <div class="row-fluid">
                        <div class="pull-left" style="padding:5px;">   
                            <h5></h5>
                        
                        <h6 class="span3 pull-left hidden-phone ml0 mt0">Do what?</h6>
                        <div class="foacts btn-group span9 pull-right ml0" style="text-align: right">
                            <button class="btn ajax" data-action="fi_move" data-ajax="loadFoldersDropdown">Move</button>
                            <button class="btn btn-danger" data-action="fi_delete">Delete</button>
                        </div>
                         
                        <div class="foacts btn-group span12 pull-right" style="width: 100%; margin-left: 0;">
                            <button id="shareOp" data-title="Share selected file(s)" class="btn btn-primary ajax" data-action="fi_share" style="width: 100%; padding-left: 0; padding-right: 0;" data-ajax="loadShareBox">Share <i class="icon-white icon-share-sign"></i></button>
                        </div>
                         </div>
                        </div>
                    </li>
                    
                    <li id="uploadFolderSelection" class="upload well dnone"></li>
                    <li class="upload well dnone info">
                       <div class="alert alert-info span12">
                          <span class="label label-info fltrt" style="width:100%;">Upload Quick Info</span>
                          <div class="clearfix"></div>
                          <ul>
                             <li>Max file size: <b><?php echo $user->getUserFileUploadSizeLimit();?></b></li>
                             <li>Max items: <b><?php echo $user->getUserFileUploadItems();?>*</b></li>
                             <li>File types: <b><?php echo (!$user->getUserAllowedFileTypes("html") ? 'All' : $user->getUserAllowedFileTypes("html"));?></b></li>
                          </ul>
                          <span class="help-inline small">*Maximum amount of files in a queue per one upload session.</span>
                       </div>
                    </li>
                </ul>               
            </div>
        </div>
    </div>

</div>