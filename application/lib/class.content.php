<?php

/**
 * content
 * @package      CloShare v1.00
 * @copyright    2013
 * @license      MIT
 * @author      xneda.com
 * @version $Id: class.content.php UTF-8 , 22-Jun-2013 | 23:48:02 nwdo ε
 */

namespace Nedo;

if (!defined("_SECURE_PHP"))
    die('Direct access to this location is not allowed.');

class Content {

    private $foldersView, $filesView, $currentFolderName, $currentFolderDesc, $files, $scrPage = null;
    private $userfoldersCount, $current_fo_static_id, $current_fo_id, $filesCount, $folderfiles = 0;
    private $breadcrumbArr, $foldersDropdown = array();
    private $dropdownhyrcy, $userdropdownfoldersHtml, $current_fo_v_path, $current_fo_mime = '';
    private $current_fo_static = false;
    private $userdropdownfoldersArray = array(
        'container' => ''
    );
    public $inlineASS = array();
    public $iconpath = null;

    /**
     * Content::__construct()
     * 
     * @return
     */
    //function __construct() {
    //}

    /**
     * Content::getTopNav()
     * 
     * @return navigation HTML
     */
    function getTopNav() {
        global $db, $core, $user, $detect;
        $return = '<div class="navbar navbar-fixed-top">
        <div class="navbar-inner">
        <div id="page" class="container-fluid pagenav">';
        $return.= '<a href="javascript:;" class="brand hidden-phone">' . CLO_SITE_NAME . '</a>';
        if (!$core->isShareUrI()) {

            if ($detect->isMobile()) {


                $return.= '<div class="visible-tablet visible-phone"><button class="btn btn-navbar btn-danger signoutb notab fltrt" style="padding-bottom:0; padding-top:6px; background: red;"><i class="icon-power-off" style="color:white"></i></button>';

                $return.= '<a href="#upload" class="btn btn-navbar fltlft pg mob" data-toggle="tab" data-persist="yes" data-callback="fn:uploadPage" data-load-txt="Please wait while preparing upload screen..." style="padding-bottom:0; padding-top:6px;"><i class="icon-cloud-upload" id="uploadbck"></i></a>
           
           <a href="#list" class="btn btn-navbar fltlft pg mob" id="listpbtn" data-persist="no" data-toggle="tab" data-callback="fn:listPage" data-load-txt="Please wait while getting list of files & folders..." style="padding-bottom:0; padding-top:6px;"><i class="icon-th-large"></i></a>
           
           <a class="btn btn-navbar fltlft notab mob visible-phone" data-target=".nav-collapse.statics" data-toggle="collapse" style="padding-bottom:0; padding-top:6px;"><i class="icon-dashboard" style="color:white"></i></a> 
           
           <a class="btn btn-navbar fltlft notab mob" data-target=".nav-collapse.searcher" data-toggle="collapse" style="padding-bottom:0; padding-top:6px; margin-bottom:3px"><i class="icon-search" style="color:white"></i></a>';

                if ($user->isAdmin()) {
                    $return.= '<a class="btn btn-navbar fltlft notab mob" data-target=".nav-collapse.conf" data-toggle="collapse" style="padding-bottom:0; padding-top:6px;"><i class="icon-cogs" style="color:white"></i></a> ';
                } else {
                    $return.= '<a data-load-txt="Please wait while loading your profile details..." class="btn btn-navbar fltlft pg mob" data-persist="no" data-toggle="tab" href="#profile" style="padding-bottom:0; padding-top:6px;"><i class="icon-user"></i></a>';
                }

                $return.= '<div class="nav-collapse conf">';
                $return.= '<ul class="nav nav-pills tabnav">
                 <li><a href="#profile" data-toggle="tab" data-persist="no" class="pg" data-load-txt="Please wait while loading your profile details...">My Profile</a></li>
                 <li><a href="#users" data-toggle="tab" data-persist="no" class="pg" data-load-txt="Please wait while loading user list...">User List</a></li>
                 <li><a href="#settings" data-toggle="tab" data-persist="no" class="pg" data-load-txt="Please wait while loading settings...">System Config</a></li>
               </ul>';
                $return.= '</div>';
                $return.= '</div>';
            }
        } else {
            $return.= '<ul class="nav pull-right">
          <li>';
            if ($user->logged_in):
                $return.= '<a href="' . CLO_URL . '/?logout=true&return_to=' . (urlencode("http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]")) . '">Sign out</a>';
            else:
                $return.= '<a href="' . CLO_URL . '/#page=login">Sign In</a>';
            endif;

            $return.= '</li></ul>';
        }

        if (!$core->isShareUrI()):
            $return.= '<ul class="nav nav-pills tabnav visible-desktop">';

            $return.= '<li><a href="#upload" id="uploadpbtn" class="pg" data-toggle="tab" data-persist="yes" data-callback="fn:uploadPage" data-load-txt="Please wait while preparing upload screen..."><i class="icon-cloud-upload" id="uploadbck"></i> Upload</a></li>';

            $return.= '<li><a href="#list" class="pg" id="listpbtn" data-persist="no" data-toggle="tab" data-callback="fn:listPage" data-load-txt="Please wait while getting list of files & folders..."><i class="icon-th-large"></i> Files & Folders</a></li>';
            $return.= '</ul>';
            $return.= '
          <ul class="nav pull-right visible-desktop welcomenav">
            <li><button class="btn btn-inverse btn-small signoutb" title="Sign out!"><i class="icon-power-off" style="color:white"></i></button></li>';

            $return.='<li><small style="line-height: 40px; margin-right: 10px; cursor: default" title="' . $user->user_name . '">Hello, ' . trunc($user->user_name, 20, true) . '</small></li>';

            $return.='</ul>

          <ul class="nav nav-pills pull-right">
            
            <li class="dropdown visible-desktop">
              <a data-toggle="dropdown" class="dropdown-toggle notab" href="#"><i class="icon-cogs icon-large"></i><i class="icon-caret-down"></i></a>
              <ul class="dropdown-menu">';
            $return.='
                 <li><a href="#profile" data-toggle="tab" data-persist="no" class="pg" data-load-txt="Please wait while loading your profile details...">My Profile</a></li>
                ';
            if ($user->isAdmin()):
                $return.='
                 <li class="divider" style="margin-bottom: 0;"></li>
                 <li><a href="#users" data-toggle="tab" data-persist="no" class="pg" data-load-txt="Please wait while loading user list...">User List</a></li>
                 <li><a href="#settings" data-toggle="tab" data-persist="no" class="pg" data-load-txt="Please wait while loading settings...">System Config</a></li>';
            endif;
            $return.=
                    '
              </ul>
            </li>';

            $return.='<li class="divider-vertical visible-desktop" style="margin-left:0"></li>';

            $return.='</ul>';

            $return.= '<div class="nav-collapse searcher">'
                    . '<ul class="nav nav-search pull-right">';
            $return.= '<li style="position:releative" id="dsearch">'
                    . '<form class="navbar-search form-search searcform" style="margin:0; margin-top:5px; padding:0;">'
                    . '<input type="text" data-provide="typeahead" class="search-query span4" placeholder="Search files & folders">';
            $return.= '</form></li>';
            $return.= '</ul></div>';

            if ($detect->isMobile() && !$detect->isTablet()) {
                $return.= '<div class="nav-collapse statics  visible-phone visible-tablet">' . $this->getUserLimitHTML() . '</div>';
            }
        endif;
        $return.= '</div></div>'; //navbar-inner -->
        $return.= '</div>';
        return clear($return, false);
    }

    /**
     * Content::getContentbySlug()
     * 
     * @return content
     */
    function getTermsOfUSE() {
        global $core;
        $return = '
                <div class="modal-header">
                <a class="close" data-dismiss="modal">×</a>
                <h3>Terms of Service for ' . $core->site_name . '</h3>
                </div>
                <div class="modal-body">' . htmlspecialchars_decode($core->register_terms_template) . '</div>
                <div class="modal-footer">
                <a href="#" class="btn" data-dismiss="modal">Close</a>
                </div>';

        $core->jsonE["result"] = 1;
        $core->jsonE["html"] = $return;

        return $core->jsonE;
    }

    /**
     * Content::alertMessage()
     * 
     * @return alert
     */
    static function notifyMessage($type = "error", $fade = true, $closebtn = true, $strong = true, $position = 'bottom-left') {

        global $core;
        $return = '<div class="notifications alert alert-' . $type . ' ' . $position . '">';
        $return.= (($closebtn) ? '<a class="close" data-dismiss="alert">×</a>' : '');
        $return.= (($strong) ? '<strong>' . (isset($core->msgs['title']) ? $core->msgs['title'] : '') . '</strong>' : '');
        $return.= addLiItems($core->msgs['message']);
        $return.='</div>';

        return $return;
    }

    /**
     * Content::alertMessage()
     * 
     * @return alert
     */
    static function alertMessage($type = "error", $fade = true, $closebtn = true, $strong = true) {

        global $core;
        $return = '<div class="alert alert-' . $type . '">';
        $return.= (($closebtn) ? '<a class="close" data-dismiss="alert">×</a>' : '');
        $return.= (($strong) ? '<strong>' . (isset($core->msgs['title']) ? $core->msgs['title'] : '') . '</strong>' : '');
        $return.= addLiItems($core->msgs['message']);
        $return.='</div>';

        return $return;
    }

    /**
     * Content::returnMessage()
     * 
     * @return alert
     */
    static function returnMessage() {

        global $core;

        return $core->msgs['message'];
    }

    /**
     * Content::UploadScreenHTML()
     * 
     * @return uploaadScreenHTML
     */
    function UploadScreenHTML($type = 'html5') {
        global $core, $user, $encr, $fhand, $detect;
        $html5 = ((isset($_GET['dragdrop']) && $_GET['dragdrop'] == 1) ? true : false);
        $return = '<div>
            <form id="cloupload" action="/" method="POST" enctype="multipart/form-data">
            <li id="upToolp">';
        if (!$detect->isMobile() || ($detect->isMobile() && $detect->isTablet())):
            $return.= '<a href="javascript:;">' . (((int) $core->upload_auto_start) ? '<strong>Attention!</strong> The upload will begin as soon as you select files.' : '<strong>Info!</strong> Press <i>"Start Upload"</i> after select your files to begin.') . '</a></li>';
        endif;
        $return.= '
            <li id="upsrtoolbox" class="btn-group" ' . ($detect->isMobile() ? ' style="display:block"' : '') . '>'
                . ((!$core->upload_auto_start) ?
                        '<button type="button" id="startupload" class="btn start disabled"><i class="icon-cloud-upload"></i> <span>Start Upload</span></button>' : ''
                ) .
                '<button type="button" id="cancelupload" class="btn cancel disabled"><i class="icon-remove-circle"></i> <span>Cancel Upload</span></button>
             <button type="button" id="clearlist" class="btn clearall disabled"><i class="icon-refresh"></i> <span>Clear List</span></button>';
        if ($detect->isMobile() && !$detect->isTablet()) {
            $return.= '
             <button type="button" style="border: none; background: none;" href="javascript:;" class="btn mob mSideOpener"><i class="icon-indent-left"></i></button>';
        }
        $return.= '</li>                     
             <div id="upload_select_cont">
             <div class="type-selection">'
                . ($html5 ?
                        '<h3 class="visible-desktop visible-tablet" style="margin-bottom: 0; margin-top: 0">Drag files here</h3>
              <i class="icon-plus-sign" style="font-size:124px;"></i>
              <h5 class="visible-desktop visible-tablet" style="margin-top:0;">Or, you can</h5>
              <input id="fileupload" type="file" name="files[]" title="Select files from your ' . ($detect->isMobile() ? 'device' : 'computer') . '" class="btn-primary' . ($detect->isMobile() ? ' btn-large mb' : '') . '" style="opacity:0" multiple>' :
                        '<div class="alert alert-danger medium" style="position:relative; padding-left:35px; text-align:right"><div class="icon-info-sign icon-4x" style="position: absolute; left: 5px;"></div> Your browser does not support multiple upload.<br>Update it to use ' . CLO_SITE_NAME . ' more afficient!</div>
               <i class="icon-plus" style="font-size:124px;"></i>
               <h5 class="visible-desktop visible-tablet" style="margin-top:0;">even so, you can</h5>
               <input type="file" name="files[]"  title="Select your files in traditional way" class="btn-primary" style="opacity:0" multiple>'
                ) .
                '</div>
               <input name="ustamp" type="hidden" value="' . date("dmyHsi") . '">
               <input name="utoken" type="hidden" value="' . $encr->encode($user->userid) . '">
               <input name="uploadme" type="hidden" value="1">
               <input id="udir" name="dir" type="hidden" value="1">
               </div>
               <ul id="files" class="files"></ul>
               </form>
               ';
        $return.='</div>';

        $sidebar = '<div class="row-fluid">';
        $sidebar.= '<h4 class="nav-header" style="position:relative; padding-left:5px"><i class="icon-mail-forward"></i><i class="icon-folder-close-alt"></i>Upload files into</h4>';
        $sidebar.= '<select data-live-search="true" data-size="10" class="span12 show-tick">';

        $sidebar.= '<option value="1" class="bold">Document Root</option>';
        $sidebar.= $this->returnAllFoldersDropdownHtml($fhand->getUserFolders(false), 1);

        $sidebar.= '</select></div>';

        $core->jsonE["html"] = clear($return, true);

        if ($core->compress_js_css) {
            $core->jsonE["style"][] = array('id' => 'upload.css', 'src' => CLO_DEF_ASS_MIN_URI . 'upload.css' . debugASS());
            $core->jsonE["script"] = array(
                array('id' => 'upload.js', 'src' => CLO_DEF_ASS_MIN_URI . 'upload.js' . debugASS())
            );
            if ($detect->isMobile()) {
                $core->jsonE["style"][] = array('id' => 'umobile.css', 'src' => CLO_DEF_ASS_MIN_URI . 'mobile.css' . debugASS());
                $core->jsonE["script"][] = array('id' => 'umobile.js', 'src' => CLO_DEF_ASS_MIN_URI . 'mobile.js' . debugASS());
            }
        } else {

            $core->jsonE["style"][] = array('id' => 'upload.css', 'src' => CLO_DEF_CSS_URI . 'upload.css' . debugASS());
            $core->jsonE["script"][] = array('id' => 'upload.js', 'src' => CLO_DEF_JS_URI . 'upload/upload.js' . debugASS());
            $core->jsonE["style"] = $this->getViewerAssets("css", $core->jsonE["style"]);
            
            $core->jsonE["script"] = $this->getViewerAssets("js", $core->jsonE["script"]);
            
            if ($detect->isMobile()) {
                $core->jsonE["style"][] = array('id' => 'umobile.css', 'src' => CLO_DEF_CSS_URI . 'mobile/hmenu.css' . debugASS());
                $core->jsonE["script"][] = array('id' => 'umobile.js', 'src' => CLO_DEF_JS_URI . 'mobile/mobile.js' . debugASS());
            }
        }

        $core->jsonE["acall"] = array(
            array(
                'back' => 'makebreadcrumb',
                'links' => array(
                    1 => array(
                        "title" => "Upload",
                        "to" => "javascript:;",
                        "parent" => -1,
                        "active" => 1,
                        "icon" => "icon-cloud-upload"
                    )
                ),
                'control' => false
            ),
            array(
                'back' => 'makeUploadSideBarNav',
                'target' => '#uploadFolderSelection',
                'html' => $sidebar
            ),
            array(
                'back' => 'fn',
                'run' => '(function(){' .
                ($detect->isMobile() && !$detect->isTablet() ?
                        'if(jQuery.isFunction(jQuery.fn.sbar) ){
                     $(".mSideOpener").sbar().on("click",function(){
                     $.sbar("toggle", "sbar");
                     $(this).find("i").toggleClass("icon-indent-left").toggleClass("icon-indent-right");
                     $(this).closest(".explain").toggleClass("opened");
                     return false;
                     });
                   }' : '') . '
                   }).call();')
        );
        return $core->jsonE;
    }

    function ListScreenHTML($parent_id = 1, $current_folder) {

        global $core, $user, $encr, $fhand, $detect;

        $html5 = ((isset($_GET['dragdrop']) && $_GET['dragdrop'] == 1) ? true : false);

        $userFolders = $fhand->getUserFolders($parent_id);
        $staticFolders = $fhand->getstaticFolders();

        $countStaticFolders = count($staticFolders); //countByKeyVal($folders, 'folder_static', 1);

        $countUserFolders = count($userFolders); //($totalcount-$countStaticfolders);
        $x = 0;
        //prepare static folder dropdown
        foreach ($staticFolders as $key => $value) {
            $x++;
            if ($value['folder_id'] == $current_folder) {
                $this->currentFolderName = $value['folder_name'];
                $this->currentFolderDesc = $value['folder_description'];
                $this->current_fo_static = $value['folder_static'];
                $this->current_fo_static_id = $value['folder_id'];
                $this->current_fo_mime = $value['folder_mime'];
            }

            if ($value['folder_id'] == 1)
                continue;

            $this->foldersDropdown[$value['folder_id']] = array(
                "attr" => array(
                    'value' => '#page=list&list=' . $value['folder_v_path'] . ',' . $value['folder_id'] . ',' . $value['folder_parent_id']
                ),
                'txt' => $value['folder_name']
            );
            if ($value['folder_id'] == $current_folder) {
                $this->foldersDropdown[$value['folder_id']]['attr']['selected'] = 'selected';
            }
            if ($value['folder_icon']) {
                $this->foldersDropdown[$value['folder_id']]['attr']['data-icon'] = $value['folder_icon'];
            }
        }

        $this->breadcrumbArr = $this->getBreadCrumb($fhand->getAllFolders($staticFolders, $userFolders), $current_folder, "folder_", $current_folder);

        $this->current_fo_id = '<input type="hidden" id="cudir" name="cudir" value="' . $current_folder . '" class="dn"/>';

        return $this->prepareFileAndFolderHierarchy($userFolders, $parent_id, $current_folder, $countUserFolders, $parent_id);
    }

    function prepareFileAndFolderHierarchy($folders, $parent_id = 1, $current_folder, $countUserFolders, $currentFolderparent_id) {
        global $core, $user, $fhand, $detect;
        $page = isset($_GET['pg']) ? $_GET['pg'] : 1;

        if (is_array($folders) && count($folders) > 0) {

            foreach ($folders as $key => $row) {

                $static = $row['folder_static'];
                $subtext = ($static ? ' data-subtext="' . $row['folder_description'] . '"' : '');



                if (($row['folder_parent_id'] == $parent_id)) {

                    $ID = $row['folder_id'];
                    $userfolder = $row['folder_user_id'];
                    $name = $row['folder_name'];
                    $parentID = $row['folder_parent_id'];
                    $subfoldercount = $fhand->countSubFoldersOfFolder($folders, $row['folder_id']);

                    $fhand->setUserFolderFiles($ID, false);

                    if ($row['folder_id'] == $current_folder) {
                        $this->currentFolderName = $row['folder_name'];
                        $this->currentFolderDesc = $row['folder_description'];
                        $this->folderfiles = $row['folder_files'];

                        if ($row['folder_parent_id'] != 1) {
                            $this->foldersDropdown[$row['folder_parent_id']] = $this->getCurrentFolderParent($row['folder_parent_id']);
                        }
                    }

                    $this->foldersDropdown[] = array(
                        "attr" => array(
                            'value' => '#page=list&list=' . $row['folder_v_path'] . ',' . $row['folder_id'] . ',' . $row['folder_parent_id'],
                            'title' => $row['folder_name']
                        ),
                        'txt' => $this->returntys($row['folder_level'], true, false, $row['folder_icon']) . trunc($row['folder_name'], 17),
                        'u' => 1
                    );
                    end($this->foldersDropdown);
                    if ($row['folder_id'] == $current_folder) {
                        $this->foldersDropdown[key($this->foldersDropdown)]['attr']['selected'] = 'selected';
                    }

                    if (!$static && ($parentID == $current_folder)) {
                        $this->foldersView.= '<li class="preview folder' . ($row['folder_files'] == 0 ? ' em' : '') . '">';
                        if ($detect->isMobile()) {
                            $this->foldersView.= '<a href="#" class="btn off ose"><i class="icon-cog"></i></a>';
                        }
                        $this->foldersView.= '
                            <a href="#' . $row['folder_v_path'] . ',' . $row['folder_id'] . ',' . $row['folder_parent_id'] . '" class="rpsve" title="' . $row['folder_description'] . '">';

                        $this->foldersView.= '<span class="foq">';

                        if ($row['folder_files'] > 0) {
                            $this->foldersView.= '
                           <small>' . $row['folder_files'] . ' x <i class="icon-file-alt"></i></small>';
                        }

                        if ($subfoldercount > 0) {
                            $this->foldersView.= '
                           <small>' . $subfoldercount . ' x <i class="icon-folder-close-alt"></i></small>';
                        }

                        if ($row['folder_files'] == 0 && $subfoldercount == 0) {
                            $this->foldersView.= '
                           <small class="e">Empty <i class="icon-info"></i></small>';
                        }

                        $this->foldersView.= '</span>';


                        $this->foldersView.= '<span class="fon" title="' . $name . '">' . trunc($name, ($detect->isMobile() ? 12 : 14)) . '</span>';

                        $this->foldersView.= '<span class="fot icon-mail-forward"></span>';

                        $this->foldersView.= '<span class="foi"></span>';

                        $this->foldersView.= "</a></li>\n";
                    }
                    //}

                    $this->prepareFileAndFolderHierarchy($folders, $key, $current_folder, $countUserFolders, $currentFolderparent_id);
                }
            }
            unset($row);

            //Files of current folder.
            if ($this->files == null) {
                $filesData = $fhand->getUserFiles($current_folder);
                $this->files = $filesData['files'];
                $this->filesCount = $count = count($this->files);

                $i = 0;

                if ($this->files)
                    foreach ($this->files as $key => $file) {
                        $i++;
                        $filename = $file['file_name'] . '.' . $file['file_extension'];
                        $this->filesView.= '<li class="' . ($fhand->getTypeIcon($file, false)) . ' preview file' . ($detect->isMobile() ? ' mb' : '') . '" title="' . $file['file_title'] . ' ' . $file['file_note'] . '">';
                        if ($detect->isMobile()):
                            $this->filesView.= '<a href="#" class="btn check off"><i class="icon-ok"></i></a>';
                        endif;
                        $this->filesView.= '<div class="' . ($fhand->isViewable($file) ? 'file viable' : 'file') . '">';

                        $this->filesView.= $this->createFileViewButton($file);

                        $this->filesView.= '';


                        if ($fhand->isImage($file['file_extension'])) {
                            $this->filesView.= '<img style="visible:hidden" class="tn" data-src="' . $fhand->createPureDirectFileUrl($file, TRUE) . '" />';
                        }

                        $this->filesView.='<div class="fname">' . trunc($filename, ($detect->isMobile() ? 10 : 15)) . '</div>';

                        $this->filesView.= '<input id="f' . $file['file_id'] . '" class="echk dn" type="checkbox" value="1" data-name="' . $file['file_name'] . '.' . $file['file_extension'] . '" />';
                        $this->filesView.= '';

                        $this->filesView.= "</div></li>\n";
                    }
                $this->scrPage = '<input class="pageObj" type="hidden" value="' . ((!$filesData['fetch']) ? '0' : $page) . '" />';
            }
        }
        $return = ($page == 1) ? '<div><ul class="directoryList thumb' . ($this->current_fo_static ? ' ' . $this->current_fo_mime : ($this->filesCount == 0 ? ' empty' : '') ) . '" id="">' : '';

        $return.= ($page == 1) ? $this->foldersView : '';

        $return.= $this->filesView . $this->scrPage . $this->current_fo_id;

        $return.= ($page == 1) ? '</ul></div>' : '';


        //files rolloever toolbar;

        $sidebar = '
            <div id="side_fo_acts">
            <div class="divider"></div>
            <h6 style="margin:0;' . (($this->current_fo_static_id > 1) ? ' color: #CCC' : '') . '">do with <span class="it ul">' . $this->currentFolderName . '</span></h6>
             
            <div class="btn-group">
            
            <a' . (($this->current_fo_static_id > 1) ? '' : ' href="#box_fo_create"') . ' data-placeholder="Folder Name" data-desc="Folder Description (Optional)" class="btn act' . (($this->current_fo_static_id > 1) ? ' disabled' : '') . '" data-title="Create new folder in <span>' . $this->currentFolderName . '</span>" data-action="fo_create" data-id="' . $current_folder . '">Create</a>
               
            <a class="btn act' . (($this->current_fo_static == 1) ? ' disabled' : '') . '"' . (($this->current_fo_static == 1) ? '' : (' href="#box_fo_edit" data-placeholder="' . $this->currentFolderName . '" data-desc="' . $this->currentFolderDesc . '" data-labels="Name_Description" data-id="' . $current_folder . '" ')) . ' data-action="fo_edit" data-title="Edit Folder">Edit Info</a>
                
            <a class="btn act btn-danger' . (($this->current_fo_static) ? ' disabled' : '') . '"' . (($this->current_fo_static == 1) ? '' : (' href="#box_fo_delete" data-content="Do you really want to delete <i>' . $this->currentFolderName . '</i> and all files and folders in it?" data-id="' . $current_folder . '" data-action="fo_delete"')) . '>Delete</a>
                
            <a data-toggle="dropdown" class="btn more dropdown-toggle' . (($this->current_fo_static || !$this->folderfiles) ? ' disabled' : '') . '" data-action="f_allactions"><i class="icon-ellipsis-vertical"></i></a>';
        $sidebar.= (($this->current_fo_static) ? '' : '
            <ul class="dropdown-menu f_allactions pull-right">
            <li><a href="#box_fo_share" data-title="Share folder <span>' . $this->currentFolderName . '</span>" data-id="' . $current_folder . '" class="btn act ajax" data-ajax="loadShareBox" data-action="fo_share"><i class="icon-share-sign"></i> Share Folder</a></li>
            <li><a href="#box_fo_zip" data-id="' . $current_folder . '" class="btn act ajax" data-ajax="downloadzip" data-action="fo_downloadzip"><i class="icon-download-alt"></i> Download as .zip</a></li>
            </ul>
            </div>
            </div>
            ');
        $sidebar.= '</div>';

        $core->jsonE["html"] = clear($return, false);

        $core->jsonE["pg"] = $page;

        if ($core->compress_js_css) {

            $core->jsonE["style"] = array(
                array('id' => 'viewer.css', 'src' => CLO_DEF_ASS_MIN_URI . 'view.css' . debugASS())
            );

            $core->jsonE["script"] = array(
                array('id' => 'components.js', 'src' => CLO_DEF_ASS_MIN_URI . 'components.js' . debugASS())
            );

            if ($detect->isMobile()) {
                $core->jsonE["style"][] = array('id' => 'mobile.css', 'src' => CLO_DEF_ASS_MIN_URI . 'mobile.css' . debugASS());
                $core->jsonE["script"][] = array('id' => 'mobile.js', 'src' => CLO_DEF_ASS_MIN_URI . 'mobile.js' . debugASS());
            }
        } else {

            $core->jsonE["style"] = $this->getViewerAssets("css", $core->jsonE["style"]);
            
            $core->jsonE["script"] = $this->getViewerAssets("js", $core->jsonE["script"]);
            

            if ($detect->isMobile()) {
                $core->jsonE["style"][] = array('id' => 'hmenu.css', 'src' => CLO_DEF_CSS_URI . 'mobile/hmenu.css' . debugASS());
                $core->jsonE["script"][] = array('id' => 'hmenu.js', 'src' => CLO_DEF_JS_URI . 'mobile/hmenu.js' . debugASS());
                $core->jsonE["script"][] = array('id' => 'list.js', 'src' => CLO_DEF_JS_URI . 'mobile/list.js' . debugASS());
                $core->jsonE["script"][] = array('id' => 'mobile.js', 'src' => CLO_DEF_JS_URI . 'mobile/mobile.js' . debugASS());
            }
        }

        $core->jsonE["acall"] = array(
            array(
                'back' => 'makebreadcrumb',
                'links' => $this->breadcrumbArr,
                'control' => array(
                    'html' => clear(($fhand->countUserFiles($current_folder) ? '
                     <div class="btn-group mt0 listControl fltlft">
                     <a href="javascript:;" id="dsAll" class="msel disabled" data-action="ds">'
                                    . (!$detect->isMobile() ? '<span class="title">Deselect All Files</span>' : '') .
                                    '<i class="icon icon-check-empty"></i>
                     </a>
                     <a href="javascript:;" id="sAll" class="msel" data-action="sa">'
                                    . (!$detect->isMobile() ? '<span class="title">Select All Files</span>' : '') .
                                    '<i class="icon icon-check"></i>
                     </a></div>' : '')
                            . ($detect->isMobile() && !$detect->isTablet() ? '<div class="fltlft divider-vertical"></div>
                        <a id="mSideOpener" href="javascript:;" class="fltlft mob"><i class="icon-indent-left"></i></a>' : '')
                            , true)
                )
            ),
            array(
                'back' => 'makelistSideBarNav',
                'target' => '#foldersdropdown',
                'container' => '<select data-live-search="true" data-size="8" class="span12 show-tick"><option value="#page=list" class="bold">Document Root</option></select>',
                'obj' => $this->foldersDropdown,
                'inject' => clear($sidebar),
                'item' => $this->filesCount
            ),
            array(
                'back' => 'fn',
                'run' => '
                  
                  var vibo = $(".vbtn").vobox();
                  ' .
                (($page > 1) ? 'vibo.revobox();' : '')
                . (!$detect->isMobile() ?
                        '$("body").on("mouseenter", "li.preview.file", function(e){
       
       e.stopPropagation();
       var $this = $(this),
       ncont = $this.find("div.fname");
       
       if($this.data("fname") === undefined)
           $this.data("fname", ncont.text());
       
       ncont.html(($this.hasClass("selected") ? "<strong>Deselect File</strong>" : "<strong>Select File</strong>"));
       
       $this.find("img.tn").fadeTo(200,0.55);
       
       var dctext = $this.data("ctext") || 0;
       
       $this.find(".vbtn, .octet").on("mouseenter",function(e){
           e.preventDefault();
           var $this = $(this),
               txt = "View File";
           if($this.hasClass("octet")){
               txt = "Download File";
           }
           if(!dctext && ncont.text() != txt)$this.data("ctext", ncont.html());
           ncont.html("<strong>"+txt+"</strong>");
           $(this).addClass("active");
       }).on("mouseleave", function(){
           $this = $(this);
           ncont.html($this.data("ctext"));
           $this.removeClass("active");
       }
       );       
       return false;
       
   }).on("mouseleave", "li.preview.file", function(e){
       e.preventDefault();
       var $this = $(this);
       $this.find("img.tn").fadeTo(200,1.0);
       $this.find("div.fname").text($this.data("fname"));
   })' : ''


                )
                . ($detect->isMobile() && !$detect->isTablet() ?
                        'if(jQuery.isFunction(jQuery.fn.sbar) ){
                     $("#mSideOpener").sbar().on("click",function(){
                     $.sbar("toggle", "sbar");
                     $(this).find("i").toggleClass("icon-indent-left").toggleClass("icon-indent-right");
                     $(this).closest(".explain").toggleClass("opened");
                     return false;
                     });
                   }' : '') . '
                   '));

        if ($page > 1) {
            //dont sent unnecessary calls on pagination requests
            unset($core->jsonE["acall"][0]);
            unset($core->jsonE["acall"][1]);
        }
        return $core->jsonE;
    }

    function getCurrentFolderParent($folder_id) {
        global $db, $core, $user, $fhand;
        $query = $db->query("SELECT "
                . "\n *"
                . "\n FROM " . $core->dTable
                . "\n WHERE  {$fhand->dpfo}user_id = " . $user->userid . " AND {$fhand->dpfo}id =" . $folder_id
        );
        $row = $db->fetch($query);

        return array(
            "attr" => array(
                'value' => '#page=list&list=' . $row['folder_v_path'] . ',' . $row['folder_id'] . ',' . $row['folder_parent_id'],
                'data-icon' => 'icon-level-up'
            ),
            'txt' => $row['folder_name'],
            'u' => 1
        );
    }

    /**
     * @global type $detect
     * @param type $data
     * @param type $current
     * @param type $prefix
     * @param type $currentAlw
     * @return type
     */
    function getBreadCrumb($data, $current, $prefix, $currentAlw) {
        global $detect;
        foreach ($data as $key => $row) {
            if ($row[$prefix . 'id'] == $current) {
                $this->breadcrumbArr[] = array(
                    'title' => $row[$prefix . 'name'],
                    'to' => (($row['folder_id'] == 1) ? '#page=list' : '#page=list&list=' . $row['folder_v_path'] . ',' . $row['folder_id'] . ',' . $row['folder_parent_id']),
                    'parent' => $row[$prefix . 'parent_id'],
                    'active' => ($row[$prefix . 'id'] == $currentAlw ? 1 : 0),
                    'icon' => ($row[$prefix . 'id'] == 1 ? 'icon-home' : $row[$prefix . 'icon']));
                $this->getBreadCrumb($data, $row[$prefix . 'parent_id'], $prefix, $currentAlw);
            }
            unset($row);
        }
        return array_reverse($this->breadcrumbArr);
    }

    function returnAllFoldersDropdownArray($data, $startfrom, $dir = false) {
        global $fhand, $detect;

        $subdirs = $fhand->getSubFoldersOfFolder($data, $dir);

        $i = 0;
        foreach ($data as $key => $row) {
            $i++;
            if ($i == 1) {
                $this->userdropdownfoldersArray['container'] = '<select id="folist" name="folist" data-style="" data-size="7" class="span11" data-live-search="true"><option value="1" class="bold">Document Root</option></select>';
            }
            $this->userdropdownfoldersArray['obj'][] = array(
                'attr' => array(
                    'value' => $row['folder_id']
                ),
                'txt' => $this->returntys($row['folder_level'], true, true) . $row['folder_name']
            );
            end($this->userdropdownfoldersArray['obj']);

            if (is_array($subdirs) && in_array($row['folder_id'], array_keys($subdirs))) {
                $this->userdropdownfoldersArray['obj'][key($this->userdropdownfoldersArray['obj'])]['attr']['disabled'] = "disabled";
            }
        }
        //unset($row);

        return $this->userdropdownfoldersArray;
    }

    function returnAllFoldersDropdownHtml($data, $current) {
        if (is_array($data) && !empty($data))
            foreach ($data as $key => $row) {
                if ($row['folder_parent_id'] == $current) {

                    $this->userdropdownfoldersHtml.= '<option value="' . $row['folder_id'] . '" ' . ($row['folder_icon'] ? 'data-icon="' . $row['folder_icon'] . '"' : '') . ' title="' . $row['folder_name'] . '">' . $this->returntys($row['folder_level'], true, false) . trunc($row['folder_name'], 17) . '</option>';

                    $this->returnAllFoldersDropdownHtml($data, $row['folder_id']);
                }
                unset($row);
            }
        return $this->userdropdownfoldersHtml;
    }

    function returntys($level, $pretend = false, $nbtospace = false, $icon = false) {
        $a = $b = '';
        if ($pretend) {
            $c = 2;
        } else {
            $c = 0;
        }
        $nb = $nbtospace ? '   ' : '&nbsp;&nbsp;';
        for ($i = $c; $i <= $level; $i++) {
            $a.=".";
            $b.=$nb;
        }
        return $b . $a . ($icon ? ' <i class="' . $icon . '"></i> ' : '');
    }

    function createFileViewButton($file) {
        global $fhand;

        $mime = $fhand->getMimeFromExtension($file['file_extension']);
        $type = $fhand->getFileTypeFromMimeType($mime);

        if ($type != 'unknown' && $fhand->isViewable($file)) {

            $mode = ($type == 'image' ? 'image' : ($type == 'audio' ? 'auido' : 'video') );

            $icon = $fhand->allViewableMimeArr[$type];

            $return = '<a href="' . $fhand->createViewUrI($file) . '" data-id="f' . $file['file_id'] . '" data-file="' . $file['file_key'] . '_' . $file['file_name'] . '.' . $file['file_extension'] . '" data-type="' . $type . '" class="btn vbtn"><i class="icon-' . $icon . '"></i></a>';
        } else {
            $return = '<a href="#" data-file="' . $file['file_key'] . '_' . $file['file_name'] . '.' . $file['file_extension'] . '" class="btn octet"><i class="icon-cloud-download"></i></a>';
        }
        return $return;
    }

    ##share screens
    //user share box

    function getShareBox($elements, $type) {
        global $share;

        return $share->getShareElementParams($elements, $type);
    }

    //view shared items
    function createSharedPage($ItemsHash) {
        global $core, $user, $fhand, $share, $encr;

        $parts = $share->comboutSharedItems($ItemsHash);

        $userInfo = $user->getUserInfo($parts['uid']);

        $username = $userInfo['user_name'];

        $meta = array();

        $itemObj = null;
        //unset
        unset($userInfo);

        $return = $metaDesc = '';

        $uihash = $encr->encode($parts['uid']);

        $x = 0;

        $return = '<div class="row-fluid" id="share" data-hash="' . $uihash . '">';

        foreach ($parts['items'] as $key => $item) {
            $count = count($parts['items']);

            //if item is file
            if ($parts['type'] === 'file') {
                $x++;
                if ($x == 1)
                    $return.= '<ul class="share" style="position: relative" data-pack="' . $encr->encode(return6charsha1($parts['uid'])) . '">';
                $itemObj = $this->createSharePageItems($item, $parts, $x, $count, $username, false);

                $return.= $itemObj['content'];

                $meta[] = $itemObj['meta'];
                $metaDesc.= ($x == 1 ? ' &hArr; ' : ' ') . $itemObj['meta']['description'];
            } else {
                //its a folder
                //get All Folders of user
                $allfolders = $fhand->getUserFolders(false, $parts['uid']);

                //get All Bounded Folders
                $folders = $fhand->getSubFoldersOfFolder($allfolders, $item, true, true, true);

                $metaImgArr = array();

                $a = 0;
                $folder_name = '';
                foreach ($folders as $f => $folder) {
                    $x = 0;
                    $folder_name.= $folder['folder_name'] . ' - ';
                    //get files of folder if it has
                    if ($folder['folder_files'] > 0) {

                        //create single folder share buttons;
                        $SinglefolderShare = $share->getShareElementParams($folder['folder_id'], false, $uihash, $folder['folder_name']);

                        $a++;
                        if ($a >= 1) {
                            $return.= '</ul>
                     <div class="dir_ex" data-share-url="' . $SinglefolderShare['link'] . '" data-share-title="' . $SinglefolderShare['title'] . '" data-share-description="' . $SinglefolderShare['description'] . '" data-share-cls="none"><i class="icon-folder-open fo"></i>' . $folder['folder_name'] . ' - ' . $folder['folder_description'] . '<div class="fltrt"><div class="sfo btn-group"></div></div></div>
                     <ul id="share_' . $a . '" class="share" style="position: relative" data-pack="' . $encr->encode(return6charsha1($parts['uid'])) . '">';
                        }
                        $files = $fhand->getFolderFiles($folder['folder_id'], $parts['uid']);

                        $count = count($files);

                        $count = ($count <= 1 ? ( $count + 1) : (($count > 4) ? ($count - 2) : $count));

                        $b = 0;
                        if (is_array($files))
                            foreach ($files as $k => $item) {
                                $b++;
                                $itemObj = $this->createSharePageItems($item['file_id'], $parts, $x, $count, $username, $folder['folder_name']);

                                $return.= $itemObj['content'];

                                $meta[] = $itemObj['meta'];

                                $metaDesc.= ($b == 1 ? ' &hArr; ' : ' ') . $itemObj['meta']['description'];

                                //start og:meta images for folders/subfolders
                                if ($fhand->isImage($item['file_extension'])) {
                                    if (count($metaImgArr) <= 3)
                                        $metaImgArr[] = array(
                                            "name" => $fhand->getFileName($item, false),
                                            "user_dir" => return6charsha1($parts['uid']),
                                            "resolution" => "320x320"
                                        );
                                }
                            }
                    }
                }

                //be sure the 320x320 version of images created/cached
                if (isset($metaImgArr) && !empty($metaImgArr))
                    $fhand->createMultipleImageViewUrl($metaImgArr);
            }
        }

        $return.= '</ul></div>';

        $meta = $share->createSocialMeta($meta);

        return array(
            "meta" => array(
                "title" => $username . ' shared goods on ' . CLO_SITE_NAME . trunc($metaDesc, 72),
                "description" => (isset($folder_name) ? rtrim($folder_name, " - ") : '') . $metaDesc,
                "extra" => $meta
            ),
            "content" => $return
        );
    }

    function createSharePageItems($item, $parts, $x, $count, $username = false, $folder_name = false) {
        global $core, $fhand, $share, $encr;

        $meta = array();

        $fileID = $core->getNumbersOnly($item);

        $file = $fhand->getFileInfoFromDB($item, $parts['uid']);

        if ($file) {

            $mime = $fhand->getMimeFromExtension($file['file_extension']);

            $type = $fhand->getFileTypeFromMimeType($mime);

            $user_dir = return6charsha1($parts['uid']);

            $src = $fhand->createPureDirectFileUrl($file, false, $user_dir);

            $image = $fhand->isImage($file['file_extension']) ? true : false;

            $viewable = $fhand->isViewable($file);
        }

        $span = (($count <= 4 && $viewable) ? ceil(12 / $count) : 3 );


        $return = '<li data-id="f' . ($file ? $file['file_id'] : ('_deleted_' . $x) ) . '" class="item span' . $span . ' ' . ($fhand->getTypeIcon($file, false)) . '" data-back="span' . $span . '">';

        $return.= '<div class="thumbnail" style="position: relative;">';

        $return.= '<div class="shareit btn-group"> </div>';

        if ($file && $viewable) {
            if ($image) {
                $return.= '<a class="vibtn" href="#">';
                $return.= '<img src="' . $fhand->createImageRequestUrl($file, $user_dir, $parts['uid'], false) . '" />';
                $return.= '</a>';
                $return.= '<a href="#" class="fullscreen icon-fullscreen" style=" font-size: 40px; display:block; z-index:10; position: absolute; right: 10px; top: 5px;" data-target="f'.$file['file_id'].'"></a>';                
            } else {
                //other viewable file types
                if ($type == 'audio' || $type == 'video') {

                    $return.= '<' . $type . ($type == 'audio' ? ' style="margin-top: 60px;"' : '') . ' width="272" height="252" preload="false" src="' . $src . '" controls="controls" preload="false">
                  
                  <object width="272" height="100%" type="application/x-shockwave-flash" data="' . CLO_DEF_ASS_URI . 'player/player.swf">
                  <param name="movie" value="' . CLO_DEF_ASS_URI . 'player/player.swf" />
                  <param name="flashvars" value="controls=true&file=' . $src . '" />
                  </object>
                  </' . $type . '>';
                }
            }

        } else {
            //downloadable files
            if ($file) {
                $return.= '<div class="download" style="min-height: 113px;"><a href="' . $fhand->createPureDirectFileUrl($file, false) . '"><span>Download File</span></a></div>';
            }
        }

        //generate social meta information of this file
        if ($file) {
            $meta = array(
                "card" => "gallery",
                //"title" => $username . ' shared goods on ' . CLO_SITE_NAME,
                "site_name" => CLO_SITE_NAME,
                "type" => $type,
                "url" => $core->current_page_url(),
                "description" => $file['file_title'],
                "folder" => $folder_name,
                "source" => ( $image ? $fhand->createImageRequestUrl($file, $user_dir, $parts['uid'], false) : CLO_DEF_ASS_URI . 'img/social-types/' . $fhand->getTypeIcon($file, false) . '.png')
            );
        }

        if (!$file) {

            $file = array(
                "file_title" => "File has been deleted!"
            );
            $return.= '<div style="min-height: 113px;"></div>';
            $meta = null;
        }

        $return.= '<div class="caption">';
        $return.= '<h5>' . $file['file_title'] . '</h5>';
        $return.= '<p>';
        $return.= '<b>Type: </b>' . $fhand->getTypeIcon($file, false) . ', ';
        $return.= '<b>Size: </b>' . $fhand->formatBytes($file['file_size'], 1, true) . '';
        $return.= '</p>';
        $return.= '</div>';
        $return.= '</div>';
        $return.= '</li>';

        $result = array(
            "meta" => $meta,
            "content" => $return
        );


        return $result;
    }

    ##user screens
    //logged user limits

    function getUserLimitBR($plain = false) {
        global $user, $fhand;

        $totalspace = $fhand->userTotalSpace(false, false);
        $totalSpaceF = $fhand->userTotalSpace(false, true);

        $spaceInUse = $fhand->userSpaceInUse($user->userid, false);
        $spaceInUsePercent = $fhand->calcUserUsagePercentage(false, $totalspace, $spaceInUse);
        $filescount = $fhand->countUserFiles(false);
        $folderscount = $fhand->countUserFolders();

        return array(
            'percent' => $this->createProgressBarHTML($spaceInUsePercent, false, "usage no-round", "margin: 10px 0 0 0"),
            'total' => $totalSpaceF,
            'used' => $plain ? $fhand->formatBytes($spaceInUse, 2, true) : '<i class="icon-hdd"></i> ' . $fhand->formatBytes($spaceInUse, 2, true) . ' / ',
            'nfolders' => ($folderscount > 0 ? $folderscount : 'no') . ($plain ? '' : ' x <i class="icon-folder-close-alt"></i>'),
            'nfiles' => ($filescount > 0 ? $filescount : '0') . ($plain ? '' : ' x <i class="icon-file-alt"></i>')
        );
    }

    function getUserLimitHTML() {

        return '<div class="size-container">
               <div id="meters" class="dnone">
                  <div class="pull-left nums">
                     <div class="used"></div>
                     <div class="total"></div>
                     <div class="clearfix"></div>
                     <div class="nfiles well well-small dnone"></div>
                     <div class="nfolders well well-small last dnone"></div>
                  </div>
                  <div class="pull-left divider-vertical"></div>
                  <div class="percent">
                  </div>
               </div>
            </div>
            <div class="pull-right proc-container">
               <div id="mprogress" class="pull-left bottom">
                  <strong class="fltlft"></strong>
                  <div class="fltrt">
                     <div class="progress progress-info progress-striped active">
                        <div class="bar" style="width: 0%;"></div>
                        <span></span>
                     </div>
                  </div>
               </div>
               <div class="pull-left divider-vertical visible-desktop" style="margin-right: 4px"></div> 
            </div>';
    }

    function createProgressBarHTML($current, $id = false, $class = false, $style = false) {
        $return = '<div'
                . ($id ? 'id="' . $id . '"' : '')
                . ' class="progress' . ($class ? ' ' . $class : '')
                . ($current > 49 ? ($current > 79 ? ' progress-danger' : ' progress-warning') : '') . '"'
                . ($style ? ' style="' . $style . '"' : '')
                . '>';
        $return.= '<div class="bar"style="width: ' . $current . '%;">';
        $return.= '<span' . ($current < 55 ? ' class="bl"' : '') . '>' . $current . '%</span>';
        $return.= '</div></div>';
        return \clear($return);
    }

    ##setting screens
    //user list

    function userListHTML($page, $ipp) {
        global $db, $core, $user, $fhand, $pager;
        $users = $user->getUsers(false, $page, $ipp, 'users');
        $pdata = $user->arrangeUserTableData($users);
        $i = 0;
        $thead = $pdata['th'];
        $tbody = $pdata['body'];

        $return = '
            <table id="user-list-tbl" class="table table-striped">
            <thead>
            <tr>';
        foreach ($thead as $key) {
            if ($key == 'user_token')
                continue;
            $return.='
            <th>' . constant($key) . (defined($key . '_title') ? helpIcon(constant($key . '_title')) : '') . '</th>';
        }
        $return.='
          </tr>
        </thead>
        <tbody>';

        foreach ($tbody as $keys => $val) {

            $return.='<tr>';
            $i++;
            $tbody[$keys]['user_id'] = (($page - 1) * $ipp) + $i;

            foreach ($tbody[$keys] as $key => $value) {

                if ($key == 'user_limit')
                    $value = $fhand->userTotalSpace(false, true, $value);

                if ($key == 'user_status')
                    $value = '<div data-ui="' . $val['user_id'] . '" class="tstatus" data-toggle="buttons-checkbox">'
                            . $user->userStatusSignBtn($val['user_id'] == 1 ? "admin" : $value) . '</div>';

                if ($key == 'disk_usage')
                    $value = $this->createProgressBarHTML($value, false, "usage no-round", false);
                $return.='<td>' . $value . '</td>';
            }

            $return.='</tr>';
        }

        $return.='
        </tbody>
        </table>';

        //print_r($pdata);
        $core->jsonE["html"] = clear($return, true);
        $core->jsonE["script"] = array(
            array('id' => 'noscr')
        );
        $core->jsonE["acall"] = array(
            array(
                'back' => "fn",
                'run' => '(function(){
                    $(".tstatus button").on("click",function(){
                       var $this = $(this);
                       if($this.hasClass("disabled")) return;
                       if($this.hasClass("active")){
                          $this.find("i").removeClass("icon-check").addClass("icon-check-empty");
                          $this.removeClass("btn-success").addClass("btn-warning");
                          
                       }else{
                          $this.find("i").removeClass("icon-check-empty").addClass("icon-check");
                          $this.removeClass("btn-warning").addClass("btn-success");
                       }
                       var acin  = $this.hasClass("i") ? "active" : "inactive";
                           sdata = {action: "uf_status", toggle : acin, value : $this.closest("div").data("ui")};
                            $.ajaxQueue({
                                url: "",
                                type: "POST",
                                data: sdata,
                                dataType: "json",
                                beforeSend: function() {
                                   $spintarget.blockit({message: "Changing user status..."});
                                },
                                success: function(response) {
                                   $spintarget.blockit({message: response.message});
                                   reloadPage(response.result,$spintarget, 1200);
                                }
                            });
                    });
                    }).call();'
            ),
            array(
                'back' => "settingsPage"
            ),
            array(
                'back' => 'makebreadcrumb',
                'links' => array(
                    1 => array(
                        "title" => "System",
                        "to" => "javascript:;",
                        "parent" => 1,
                        "icon" => false
                    ),
                    2 => array(
                        "title" => pl() . "User List",
                        "to" => "javascript:;",
                        "parent" => 1,
                        "active" => 1,
                        "icon" => false
                    )
                ),
                'control' => array(
                    'html' => $pager->display_pages()
                )
            )
        );
        return $core->jsonE;
    }

    function getSettings($case) {
        global $db, $core, $user, $fhand;

        $return = '<form method="post" class="form-horizontal" style="margin: 20px 10px" id="upload-settings-form"  data-action="settings_upload">
  <fieldset class="tab-content">';
        switch ($case) {
            case "upsettings":
                $return.= '   
<div id="upsettings" class="tab-pane' . ($case == 'upsettings' ? ' active' : '') . '">
              <table class="table table-striped"><tbody>';
                $return.= '
                  <tr>
                  <td style="border-top: none">
                  <div class="control-group">
                    <label for="auto_start" class="control-label">Auto start uploads</label>
                    <div class="controls">';
                $return.= $this->createYesNoRadio($core->upload_auto_start, "upload_auto_start");
                $return.='<span class="help-inline">Start uploads as soon as selecting a file.</span>
                    </div>
                  </div>
                  </td>
                  </tr>
                  <tr>
                  <td>
                  <div class="control-group">
                    <label for="file_size_limit" class="control-label">Max file size limit</label>
                    <div class="controls">
                      <input type="text" id="file_size_limit" name="upload_max_file_size_limit[]" class="input-mini" value="' . $fhand->formatBytes($core->upload_max_file_size_limit, 2, false) . '">
                      ';
                $return.= $this->createUnitSelectionRadio("upload_max_file_size_limit", $fhand->getUnitFromBytes($core->upload_max_file_size_limit));
                $return.='
                    <span class="help-inline">' . helpIcon("The file size limit of one file. Note that you must make the right changes on php.ini file. ie: post_max_size = 128MB") . '</span>
                    </div>
                  </div>
                  </td>
                  </tr>
                  <tr>
                  <td>
                  <div class="control-group">
                    <label for="chunk_size_limit" class="control-label">Max chunk size limit</label>
                    <div class="controls">
                      <input type="text" id="chunk_size_limit" name="upload_max_chunk_size_limit[]" class="input-mini" value="' . $fhand->formatBytes($core->upload_max_chunk_size_limit, 2, false) . '">
                      ';
                $return.= $this->createUnitSelectionRadio("upload_max_chunk_size_limit", $fhand->getUnitFromBytes($core->upload_max_chunk_size_limit));
                $return.='
                    <span class="help-inline">' . helpIcon("Sets the data size sent to the server while uploading a file per request. Must not be greater than Max file size limit to take effect. Default 5MB") . '</span>
                    </div>
                  </div>
                  </td>
                  </tr>
                  <tr>
                  <td>
                  <div class="control-group">
                    <label for="default_disk_limit" class="control-label">Default total user space</label>
                    <div class="controls">
                      <input type="text" id="default_disk_limit" name="upload_user_default_disk_limit[]" class="input-mini" value="' . $fhand->formatBytes($core->upload_user_default_disk_limit, 2, false) . '">
                      ';
                $return.= $this->createUnitSelectionRadio("upload_user_default_disk_limit", $fhand->getUnitFromBytes($core->upload_user_default_disk_limit));
                $return.='
                    <span class="help-inline">' . helpIcon("Sets the total disk space per user.") . '</span>
                    </div>
                  </div>
                  </td>
                  </tr>
                  
                  <tr>
                  <td>                  
                  <div class="control-group">
                    <label for="up_items" class="control-label">Max items per upload</label>
                    <div class="controls">
                      <input type="text" id="up_items" name="upload_user_default_up_items" class="input-mini" value="' . $core->upload_user_default_up_items . '">';
                $return.='
                    <span class="help-inline">' . helpIcon("Maximum file items to upload in one go. Set 0 for unlimited") . '</span>
                    </div>
                  </div>    
                  </td>
                  </tr>
                  
                  <tr>
                  <td>                  
                  <div class="control-group">
                    <label for="allowed_hdim" class="control-label">Max preview dimensions</label>
                    <div class="controls">
                      <input type="text" id="allowed_hdim" name="upload_preview_allowed_hdim" class="input-mini" value="' . $core->upload_preview_allowed_hdim . '"> x
                       <input type="text" id="allowed_vdim" name="upload_preview_allowed_vdim" class="input-mini" value="' . $core->upload_preview_allowed_vdim . '">
                      ';
                $return.='
                    <span class="help-inline">' . helpIcon("Maximum image dimensions to show preview of images before upload.") . '</span>
                    </div>
                  </div>    
                  </td>
                  </tr>
                  
                  <tr>
                  <td>
                  <div class="control-group">
                    <label for="preview_file_size_limit" class="control-label">Max thumb creation size</label>
                    <div class="controls">
                      <input type="text" id="preview_file_size_limit" name="upload_preview_max_file_size_limit[]" class="input-mini" value="' . $fhand->formatBytes($core->upload_preview_max_file_size_limit, 2, false) . '">
                      ';
                $return.= $this->createUnitSelectionRadio("upload_preview_max_file_size_limit", $fhand->getUnitFromBytes($core->upload_preview_max_file_size_limit));
                $return.='
                    <span class="help-inline">' . helpIcon("Max image size to create preview thumbnails before upload.") . '</span>
                    </div>
                  </div>
                  </td>
                  </tr>
                  
                  <tr>
                  <td>
                  <div class="control-group">
                    <label for="upload_thumb_crop" class="control-label">Crop Uploaded Images</label>
                    <div class="controls">
                      ';
                $return.= $this->createYesNoRadio($core->upload_thumb_crop, "upload_thumb_crop");
                $return.='
                    <span class="help-inline">Crop images while creating thumbnail.(for both save and preview).</span>
                    </div>
                  </div>
                  </td>
                  </tr>
                  
                  <tr>
                  <td>
                  <div class="control-group">
                    <label for="allowed_file_types" class="control-label">' . pl() . 'Allowed File Types</label>
                    <div class="controls">
                      ';
                $return.= '<input type="text" id="allowed_file_types" name="upload_allowed_file_types" class="input-xxlarge" value="' . (!$user->getUserAllowedFileTypes("html") ? 'All' : $user->getUserAllowedFileTypes("html")) . '">';
                $return.='
                    <span class="help-inline">' . helpIcon("Set it 'all' for all file types.") . '</span>
                    <span class="help-inline">Specify uploadable file types. Enter extensions comma(,) seperated. ie: "<i class="small">gif, jpe?g, png</i>" for image files only.</span>
                    
                    </div>
                  </div>
                  </td>
                  </tr>
                  

                  </tbody>
                  </table>
                  
               </div>';

                break;
            case 'shsettings' :
                $return.= '<div id="shsettings" class="tab-pane' . ($case == 'shsettings' ? ' active' : '') . '">
                  <table class="table table-striped">
                  <tbody>
                  
                  <tr>
                  <td style="border-top: none">
                  <div class="control-group">
                    <label for="share_options" class="control-label">' . pl() . 'Social Sharing Sites</label>
                    <div class="controls">';
                $return.= $this->createDropdownMenu(array("email", "facebook", "twitter", "linkedin", "tumblr", "googleplus", "pinterest"), explode(',', $core->share_options), 'share_options[]', true, array("email", "facebook", "twitter", "linkedin", "tumblr", "google-plus", "pinterest"));
                $return.='
                    <span class="help-inline">' . helpIcon("Choose which social platform(s) can be used to share files.") . '</span>
                    </div>
                  </div>
                  </td>
                  </tr>
                  
                  <tr>
                  <td>
                  <div class="control-group">
                    <label for="google_api_key" class="control-label">Google API KEY</label>
                    <div class="controls">
                      <input type="text" id="google_api_key" name="google_api_key" class="input-xxlarge" value="' . $core->google_api_key . '">
                      ';
                $return.='
                    <span class="help-inline">' . helpIcon("The system will handle the shortening process of shared links with or without it but An API key is highly recommended.") . '</span>
                    <span class="help-inline">Provide an Google API KEY. To retrieve your personal API key you have to log in to the <a target="_blank" href="https://code.google.com/apis/console/">API console</a> and activate URL Shortener API. After that you can find your API key when clicking on the API access. To get it works please check your KEY twice or leave it empty. (Optional)</span>
                    </div>
                  </div>
                  </td>
                  </tr>
                  
                  </tbody>
                  </table>
                  </div>';
                break;
            case 'uisettings' :
                $return.= '<div id="uisettings" class="tab-pane' . ($case == 'uisettings' ? ' active' : '') . '">
                  <table class="table table-striped">
                  <tbody>

                  <tr>
                  <td style="border-top: none">
                  <div class="control-group">
                    <label for="register_allowed" class="control-label">Allow Registrations</label>
                    <div class="controls">';
                $return.= $this->createYesNoRadio($core->register_allowed, "register_allowed");

                $return.='
                    <span class="help-inline">' . helpIcon("Users may sign up your site?") . '</span>
                    </div>
                  </div>
                  </td>
                  </tr>
                  
                  <tr>
                  <td>
                  <div class="control-group">
                    <label for="register_user_limit" class="control-label">Register user limit</label>
                    <div class="controls">
                    <input type="text" id="register_user_limit" name="register_user_limit[]" class="input-mini" value="' . $core->register_user_limit . '">';

                $return.='
                    <span class="help-inline">' . helpIcon("How many users can sign up your site. Set 0 for unlimited.") . '</span>
                    </div>
                  </div>
                  </td>
                  </tr>

                  <tr>
                  <td>
                  <div class="control-group">
                    <label for="register_password_min_length" class="control-label">Password length</label>
                    <div class="controls">
                    <input type="text" id="register_password_min_length" name="register_password_min_length[]" class="input-mini" value="' . $core->register_password_min_length . '">';

                $return.='
                    <span class="help-inline">' . helpIcon("The required password length. (Used for registration form.)") . '</span>
                    </div>
                  </div>
                  </td>
                  </tr>
                  
                  <tr>
                  <td>
                  <div class="control-group">
                    <label for="register_send_welcome_mail" class="control-label">Send Welcome Mail</label>
                    <div class="controls">
                      ';
                $return.= $this->createYesNoRadio($core->register_send_welcome_mail, "register_send_welcome_mail");
                $return.='
                    <span class="help-inline">' . helpIcon('Set this \'yes\' to send a welcome e-mail to new registered user.') . '</span>
                    </div>
                  </div>
                  </td>
                  </tr>
                  
                  <tr>
                  <td>
                  <div class="control-group">
                    <label for="welcome_mail_template" class="control-label">Welcome mail template
                    <br><a href="javascript:;" class="load_default" data-action="mail_welcome_template" data-content="Do you really want to return the default template?">Load Default</a></label></label>
                    
                    <div class="controls">
                    <textarea class="editor" name="register_welcome_mail_template" id="welcome_mail_template" cols="80" rows="5">' . htmlspecialchars_decode($core->register_welcome_mail_template) . '</textarea>';
                $return.='
                    <span class="help-inline">This template will be used for sending welcome e-mail to users. Note: Do not remove tags between [...]</span>
                    </div>
                  </div>
                  </td>
                  </tr>
                  
                  <tr>
                  <td>
                  <div class="control-group">
                    <label for="recover_mail_template" class="control-label">Recovery mail template
                    <br><a href="javascript:;" class="load_default" data-action="mail_recovery_template" data-content="Do you really want to return the default template?">Load Default</a></label>
                    
                    <div class="controls">
                    <textarea class="editor" name="recover_mail_template" id="recover_mail_template" cols="80" rows="5">' . htmlspecialchars_decode($core->recover_mail_template) . '</textarea>';
                $return.='
                    <span class="help-inline">This template will be used for sending password recovery e-mail to users. Note: Do not remove tags between [...]</span>
                    </div>
                  </div>
                  </td>
                  </tr>
                  
<tr>
                  <td>
                  <div class="control-group">
                    <label for="recover_mail_template" class="control-label">Recovery result mail template
                    </label>
                    <div class="controls">
                    <textarea class="editor" name="recover_mail_template_res" id="recover_mail_template_res" cols="80" rows="5">' . htmlspecialchars_decode($core->recover_mail_template_res) . '</textarea>';
                $return.='
                    <span class="help-inline">This template will be used for sending password recovery result (selected password) e-mail to users. Note: Do not remove tags between [...]</span>
                    </div>
                  </div>
                  </td>
                  </tr>

                  
                  <tr>
                  <td>
                  <div class="control-group">
                    <label for="register_terms_template" class="control-label">Terms of service</label>
                    
                    <div class="controls">
                    <textarea class="editor" name="register_terms_template" id="register_terms_template" cols="80" rows="5">' . htmlspecialchars_decode($core->register_terms_template) . '</textarea>';
                $return.='
                    <span class="help-inline">Terms of service for your site.</span>
                    </div>
                  </div>
                  </td>
                  </tr>
                  
                  </tbody>
                  </table>
                 </div>';
                break;

            case 'sysettings' :
                $return.= '                 
                 <div id="sysettings" class="tab-pane' . ($case == 'sysettings' ? ' active' : '') . '">
                  <table class="table table-striped">
                  <tbody>    
                  <tr>
                  <td style="border-top: none">
                  <div class="control-group">
                    <label for="site_name" class="control-label">Site Name</label>
                    <div class="controls">
                      <input type="text" id="site_name" name="site_name" class="input-xxlarge" value="' . $core->site_name . '">
                      ';
                $return.='
                    <span class="help-inline">' . helpIcon("Your site name") . '</span>
                    </div>
                  </div>
                  </td>
                  </tr>
                  
                  <tr>
                  <td>
                  <div class="control-group">
                    <label for="site_slogan" class="control-label">Site Slogan</label>
                    <div class="controls">
                      <input type="text" id="site_slogan" name="site_slogan" class="input-xxlarge" value="' . $core->site_slogan . '">
                      ';
                $return.='
                    <span class="help-inline">' . helpIcon("Your site slogan (description) (used in page title)") . '</span>
                    </div>
                  </div>
                  </td>
                  </tr>
                  

                  <tr>
                  <td>
                  <div class="control-group">
                    <label for="meta_description" class="control-label">Meta Description</label>
                    <div class="controls">
                      <input type="text" id="meta_description" name="meta_description" class="input-xxlarge" value="' . $core->meta_description . '">
                      ';
                $return.='
                    <span class="help-inline">' . helpIcon("Your site description for search engines (used in page meta tag)") . '</span>
                    </div>
                  </div>
                  </td>
                  </tr>
                  
                  <tr>
                  <td>
                  <div class="control-group">
                    <label for="site_url" class="control-label">Site URL</label>
                    <div class="controls">
                      <input type="text" id="site_url" name="site_url" class="input-xxlarge" value="' . $core->site_url . '">
                      ';
                $return.='
                    <span class="help-inline">' . helpIcon("Your site domain or IP without trailing slash / and must begin with http or https protocol. (http://www.example.com)") . '</span>
                    </div>
                  </div>
                  </td>
                  </tr>
                  
                  <tr>
                  <td>
                  <div class="control-group">
                    <label for="use_site_cdn" class="control-label">Use Site CDN</label>
                    <div class="controls">
                      ';
                $return.= $this->createYesNoRadio($core->use_site_cdn, "use_site_cdn");
                $return.='
                    <span class="help-inline">Setting option yes will make site to request asset files from the cdn server.</span>
                    </div>
                  </div>
                  </td>
                  </tr>
                  
                  <tr>
                  <td>
                  <div class="control-group">
                    <label for="site_cdn" class="control-label">Site CDN</label>
                    <div class="controls">
                      <input type="text" id="site_cdn" name="site_cdn" class="input-xxlarge" value="' . $core->site_cdn . '">
                      ';
                $return.='
                    <span class="help-inline">' . helpIcon("Content delivery network of your site without trailing slash / and must begin with http or https protocol. (http://cdn.example.com) (used for asset files)") . '</span>
                    </div>
                  </div>
                  </td>
                  </tr>
                 
                  
                  <tr>
                  <td>
                  <div class="control-group">
                    <label for="site_email" class="control-label">Site Email</label>
                    <div class="controls">
                      <input type="email" id="site_email" name="site_email" class="input-xxlarge" value="' . $core->site_email . '">
                      ';
                $return.='
                    <span class="help-inline">' . helpIcon("Your site email (used for sending registration emails.)") . '</span>
                    </div>
                  </div>
                  </td>
                  </tr>
                  
                  <tr>
                  <td>
                  <div class="control-group">
                    <label for="mailer_method" class="control-label">mailer_method</label>
                    <div class="controls">
                      ';
                $return.= $this->createDropdownMenu(
                        array("PHP", "SMTP" => "showHideSMTPoptions"), $core->mailer_method, "mailer_method", false, false);
                $return.='
                    <span class="help-inline">' . helpIcon("How does system send an e-mail?") . '</span>
                    </div>
                  </div>
                  </td>
                  </tr>
                  
                  <tr class="smtpoptions dn">
                  <td style="border-top: none; background: #FFF">
                  <div class="control-group">
                    <label for="mailer_smtp_host" class="control-label">Smtp Server/Port</label>
                    <div class="controls">
                    
                      <input type="text" id="mailer_smtp_host" name="mailer_smtp_host" class="input-xlarge" value="' . $core->mailer_smtp_host . '"> : 
                      <input style="width:40px" type="number" id="mailer_smtp_port" name="mailer_smtp_port" class="input-small" value="' . $core->mailer_smtp_port . '">
                      ';
                $return.='
                    <span class="help-inline">' . helpIcon("Server Url/IP and port number ie: smtp.gmail.com:587") . '</span>
                    </div>
                  </div>
                  </td>
                  </tr>
                  
                  <tr class="smtpoptions dn">
                  <td style="border-top: none; background: #FFF">
                  <div class="control-group">
                    <label for="mailer_smtp_user" class="control-label">Smtp Username/Password</label>
                    <div class="controls">
                    
                      <input type="text" id="mailer_smtp_user" name="mailer_smtp_user" class="input-large" value="' . $core->mailer_smtp_user . '" placeholder="Username"> 
                      <span class="help-inline">' . helpIcon("ie: smtpuser@gmail.com") . '</span>
                          
                      <input type="text" id="mailer_smtp_pass" name="mailer_smtp_pass" class="input-large" value="' . $core->mailer_smtp_pass . '">
                      ';
                $return.='
                    </div>
                  </div>
                  </td>
                  </tr>
                  
                  <tr class="smtpoptions dn">
                  <td style="border-top: none; background: #FFF">
                  <div class="control-group">
                    <label for="mailer_connection_type" class="control-label">Smtp Connection Type</label>
                    <div class="controls">';

                $return.= $this->createDropdownMenu(array("None", "SSL", "TSL"), $core->mailer_connection_type, "mailer_connection_type", false, false);

                $return.='
                    <span class="help-inline">' . helpIcon("Provide an working Smtp protocol.") . '</span>
                    </div>
                  </div>
                  </td>
                  </tr>
                  
                  <tr class="smtpoptions dn">
                  <td style="border-top: none;">
                  </td>
                  </tr>
                  
                  
                  <tr>
                  <td>
                  <div class="control-group">
                    <label for="bootstrap_skin" class="control-label">Site Skin</label>
                    <div class="controls">
                      ';
                $return.= $this->createDropdownMenu($core->getSkinArray(), $core->bootstrap_skin, "bootstrap_skin", false, false);
                $return.='
                    <input type="hidden" value="0" id="changeSkin" name="changeSkin" />
                    <span class="help-inline">' . helpIcon("Changes the main looks of your site.") . '</span>
                    </div>
                  </div>
                  </td>
                  </tr>
                  
                  <tr>
                  <td>
                  <div class="control-group">
                    <label for="compress_js_css" class="control-label">Compress assets</label>
                    <div class="controls">
                      ';
                $return.= $this->createYesNoRadio($core->compress_js_css, "compress_js_css");
                $return.='
                    <span class="help-inline">To compress asset files (JS,CSS) to save bandwith. Note: Logon screen and ajax requested assets are not effected.</span>
                    </div>
                  </div>
                  </td>
                  </tr>
                  
                  
                  <tr>
                  <td>
                  <div class="control-group">
                    <label for="items_per_page" class="control-label">Items per page</label>
                    <div class="controls">
                      <input type="text" id="items_per_page" name="items_per_page" class="input-mini" value="' . $core->items_per_page . '">
                      ';
                $return.='
                    <span class="help-inline">' . helpIcon("How many items will be listed per page?") . '</span>
                    </div>
                  </div>
                  </td>
                  </tr>
                  
                  </tbody>
                  </table>
                  </div>';
                break;
            case 'serverinfo' :
                $return.= '               
                  <div id="serverinfo" class="tab-pane' . ($case == 'serverinfo' ? ' active' : '') . '">
                  <table class="table table-striped">
                  <tbody>';

                foreach ($core->getServerInfo() as $key => $row) {

                    $return.='<tr>
                  <td style="padding:5px 10px;">
                  <div>
                    <strong>' . $key . ' : </strong>';
                    $return.= '<large><i>' . ( isset($row['value']) ? $row['value'] : ini_get($key) ) . '<i/></large>
                    
                  </div>
                  
                  </td>
                  </tr>';
                }
                $return.= '                 
                  </tbody>
                  </table>';

                $return.= '</div>';
                break;
        }
        $submitBtn = '<button data-loading-text="Updating..." id="save_config" class="btn btn-primary saveconfig">Save Configurations</button>';
        $return.='
            </fieldset>
            </form>';


        $core->jsonE["html"] = clear($return, true);
        $core->jsonE["style"] = array(
            array('id' => 'editor.css', 'src' => CLO_DEF_ASS_MIN_URI . 'editor.css' . debugASS())
        );
        $core->jsonE["script"] = array(
            array('id' => 'editor.js', 'src' => CLO_DEF_ASS_MIN_URI . 'editor.js' . debugASS())
        );
        $core->jsonE["acall"] = array(
            array(
                'back' => 'makebreadcrumb',
                'links' => array(
                    1 => array(
                        "title" => "System",
                        "to" => "javascript:;",
                        "parent" => 1,
                        "icon" => false
                    ),
                    2 => array(
                        "title" => pl() . "Config",
                        "to" => "javascript:;",
                        "parent" => 1,
                        "active" => 1,
                        "icon" => false
                    )
                ),
                'control' => array(
                    'html' => '<ul class="nav nav-tabs fltlft mb0" id="config-selection" data-page="settings" data-query="edit">
                               <li' . ($case == 'upsettings' ? ' class="active"' : '') . '><a href="upsettings" class="nrm" data-toggle="tab">Upload</a></li>
                               <li' . ($case == 'uisettings' ? ' class="active"' : '') . '><a href="uisettings" class="nrm" data-toggle="tab">User</a></li>
                               <li' . ($case == 'sysettings' ? ' class="active"' : '') . '><a href="sysettings" class="nrm" data-toggle="tab">System</a></li>
                               <li' . ($case == 'shsettings' ? ' class="active"' : '') . '><a href="shsettings" class="nrm" data-toggle="tab">Sharing</a></li>
                               <li' . ($case == 'serverinfo' ? ' class="active"' : '') . '><a href="serverinfo" class="nrm" data-toggle="tab">' . pl() . 'Server Info</a></li>
                               </ul>' . $submitBtn
                )
            ),
            array(
                'back' => "fn",
                'run' => '(function(){

   $(document).on("click", "a.nrm", function(e) {

      e.preventDefault();
      $this = $(this);

      $this.tab("show");
      var $pEl = $this.closest(".tabs"),
      idx = $this.attr("href").replace(/^#/, ""),
      pageOfParent = $pEl.data("page"),
      url = window.location.href,
      state = {edit : idx},
      merged = jQuery.param.fragment(url, state), 
      deparamed = jQuery.deparam.fragment(merged);
      
      $.bbq.pushState(deparamed ,0);
      
      return false;
   }); 
//$("#config-selection").find("li.active a").trigger("click");

$(".editor").veditor();
var $skinSelector = $("select[name=\'bootstrap_skin\']"),
changeSk = $("#changeSkin"), 
comprb = $(\'div[data-target="compress_js_css"]\').find("button.active").data("value");

$viewPort.data("styleSt", $skinSelector.val())
$skinSelector.change(function(){
 var $this = $(this);
 if($this.val() != $viewPort.data("styleSt")){
    changeSk.val($this.val());
 }else{
    changeSk.val("0");
 }
});
$("#save_config").on("click",function(){
var $this = $(this),
$comprf = $("#compress_js_css"),
$form = $("#upload-settings-form");
var options = {
data: { action: $form.data("action") },
beforeSubmit : function(){
$this.button("loading");
$spintarget.blockit({message: "Updating upload configs..."});
},
success: function(responseText, statusText, xhr, $form){
$this.button("reset");
$spintarget.blockit({message: responseText.message});
if(comprb != $comprf.val() != 0){
location.reload(true);
}else{
if(changeSk.val() != 0){
$.cookie("CL_chskin", "yes");
if($("#compress_js_css").val() != 0){
$("#appstyle").cssReload(reloadPage(responseText.result,$spintarget, 3500 ),"' . CLO_DEF_ASS_MIN_URI . 'base.css?' . time() . '", "appstyle");
    
}else{
$("#appstyle").cssReload(reloadPage(responseText.result,$spintarget, 3500 ),"' . CLO_DEF_CSS_URI . 'skin/bootstrap-"+(changeSk.val() != 0 ? changeSk.val() : $skinSelector.find(":selected").val())+".css", "appstyle");
    }
}else{
reloadPage(responseText.result,$spintarget, 1200);
}
}
},
dataType:  "json"
};     
$form.ajaxSubmit(options);
}).popover({
trigger : "hover",
html: true,
placement: "bottom",
title : "Note that! <i class=\'icon-info-sign fltrt\'></i>",
content : "Some changes needs a full page refresh.<br>ie: Upload Settings, compress operations.",
delay: { show : 100, hide : 100}
});

$viewPort.find("select").selectpicker("mobile");
Modernizr.load({
test: Modernizr.input.placeholder,
nope: "' . CLO_DEF_ASS_URI . 'js/fallback/placeholder.js' . debugASS() . '"
});
$(".prp button").button().on("click", function(){
var $this = $(this),
vtarget = $this.closest(".prp").data("target");
$("#"+vtarget).val($this.data("value"));
});
$("a.load_default").on("click", function(){
createBox($(this));
});
}).call();'
            ),
            array(
                'back' => "settingsPage"
            )
        );

        return $core->jsonE;
    }

    function getProfile() {
        global $db, $core, $user, $fhand;
        $statics = $this->getUserLimitBR(true);
        $return = '
                 <form method="post" class="form-horizontal" style="margin: 20px 10px" id="profile-settings-form"  data-action="profile_settings">
              <fieldset>
              <table class="table table-condensed" style="padding:0; margin: 0; border: 0">
              <tbody>
              
              <tr>
              <td>
              <strong>My Profile</strong>
              </td>
              </tr>
              <tr>
              <td>
              <table class="table table-striped"><tbody>';
        $return.= '
                
                  <tr>
                  <td style="border-top: none">
                  <div class="control-group">
                    <label for="user_name" class="control-label">Email</label>
                    <div class="controls">';
        $return.= '<input type="text" id="email" name="email" disabled="disabled" value="' . $user->user_email . '">';
        $return.='
                   </div>
                  </div>
                  </td>
                  </tr>

                  <tr>
                  <td style="border-top: none">
                  <div class="control-group">
                    <label for="user_name" class="control-label">Name</label>
                    <div class="controls">';
        $return.= '<input type="text" id="user_name" name="user_name" class="" value="' . $user->user_name . '">';
        $return.='
                   </div>
                  </div>
                  </td>
                  </tr>
                  
                  <tr>
                  <td>
                  <div class="control-group">
                    <label for="user_password" class="control-label">New Password</label>
                    <div class="controls">';
        $return.= '<input type="text" id="user_password" name="user_password" class="" value="">';
        $return.='
                   <span class="help-inline">Leave empty if you don\'t want to change</span>
                   </div>
                  </div>
                  </td>
                  </tr>

                  <tr>
                  <td>
                  <div class="control-group">
                    <label for="user_password" class="control-label">Statistics</label>
                    <div class="controls">';
        $return.= $statics['percent'];
        $return.= 'You Have: <b>' . $statics['nfiles'] . '</b> file(s) and <b>' . $statics['nfolders'] . '</b> folder(s) in total.<br>';
        $return.= 'Total Disk Usage: <b>' . $statics['used'] . '</b> of <b>' . $statics['total'] . '</b>';
        $return.='
                   </div>
                  </div>
                  </td>
                  </tr>    

                  
                  <tr>
                  <td style="border-top: none">
                  <div class="control-group">
                    <div class="controls">';
        $return.= 'Account created on <strong>' . $user->user_created . '</strong>';
        $return.='
                   </div>
                  </div>
                  </td>
                  </tr>
                  
                  <tr>
                  <td style="border-top: none">
                  <div class="control-group">
                    <div class="controls">';
        $return.= 'Last Logged on <strong>' . $user->last_logged_on . ' </strong>from <strong>' . $user->last_logged_from . '(IP)</strong>';
        $return.='
                   </div>
                  </div>
                  </td>
                  </tr>
                  
                  </tbody>
                  </table>
                  </td>
                  </tr>
                  
                  </tbody>
                  </table>';
        $submitBtn = '<button data-loading-text="Updating..." id="save_user_profile" class="btn btn-primary">Update Profile</button>';
        $return.='
            </fieldset>
            </form>';

        $core->jsonE["html"] = clear($return, true);
        $core->jsonE["script"] = array(
            array('id' => 'noscr')
        );
        $core->jsonE["acall"] = array(
            array(
                'back' => 'makebreadcrumb',
                'links' => array(
                    1 => array(
                        "title" => "Profile",
                        "to" => "javascript:;",
                        "parent" => 1,
                        "icon" => false
                    ),
                    2 => array(
                        "title" => pl() . "View / Edit",
                        "to" => "javascript:;",
                        "parent" => 1,
                        "active" => 1,
                        "icon" => false
                    )
                ),
                'control' => array(
                    'html' => $submitBtn
                )
            ),
            array(
                'back' => "fn",
                'run' => '(function(){
                     $("#save_user_profile").on("click",function(){
                         var $this = $(this),
                             $form = $("#profile-settings-form");
                         var options = {
                         data: { action: $form.data("action") },
                         beforeSubmit : function(){
                            $this.button("loading");
                            $spintarget.blockit({message: "Updating your profile..."});
                         },
                         success: function(responseText, statusText, xhr, $form){
                            $this.button("reset");
                            $spintarget.blockit({message: responseText.message});
                            reloadPage(responseText.result,$spintarget, 1200);
                         },
                         dataType:  "json"
                         };     
                         $form.ajaxSubmit(options);
                    });
                    
                     $viewPort.find("select").selectpicker("mobile");
                     Modernizr.load({
                     test: Modernizr.input.placeholder,
                     nope: "' . CLO_DEF_ASS_URI . 'js/fallback/placeholder.js' . debugASS() . '" 
                     });
                     $(".prp button").button().on("click", function(){
                          var $this = $(this),
                              vtarget = $this.closest(".prp").data("target");
                          $("#"+vtarget).val($this.data("value"));
                     });
                     }).call();'
            ),
            array(
                'back' => "settingsPage"
            )
        );

        return $core->jsonE;
    }

    function createDropdownMenu($data, $selected, $name, $multiple, $icon) {
        global $core, $fhand;
        $return = '<select name="' . $name . '" class="span2 show-tick" data-size="5" ' . ($multiple ? 'data-selected-text-format="count>2" multiple' : '') . '>';
        if (is_array($data)) {
            $x = 0;
            $js = false;
            foreach ($data as $key => $value) {

                $row = !is_numeric($key) ? $key : $value;

                $return.='<option ' . ($icon ? 'data-icon="icon-' . $icon[$x] . '"' : '') . (is_array($selected) ? in_array($row, $selected) : ($row == $selected) ? 'selected="selected"' : '') . ' value="' . $row . '">' . $row . '</option>';
                $x++;


                //get javascript for this option
                if (!is_numeric($key)) {
                    $js = $value;
                }
            }
        }
        $return.='</select>';

        if ($js) {
            $return.= $this->$js();
        }
        return $return;
    }

    function createUnitSelectionRadio($unitTarget, $selected) {
        $units = array('B', 'KB', 'MB', 'GB', 'TB');

        $return = '<div data-toggle="buttons-radio" class="btn-group prp" data-target="' . $unitTarget . '">';

        foreach ($units as $value) {

            $return.='<button type="button" data-value="' . $value . '" class="btn btn-mini' . ($value == $selected ? ' active' : '') . '">' . $value . '</button>';
        }

        $return.='</div>';
        $return.='<input type="hidden" id="' . $unitTarget . '" name="' . $unitTarget . '[]" value="' . $selected . '" />';

        return $return;
    }

    function createYesNoRadio($selected, $target) {
        $return = '
            <div data-toggle="buttons-radio" class="btn-group prp" data-target="' . $target . '">
                <button type="button" data-value="1" class="btn' . ($selected == 1 ? ' active' : '') . '">Yes</button>
                <button type="button" data-value="0" class="btn' . ($selected == 0 ? ' active' : '') . '">No</button>';
        $return.='</div>';
        $return.='<input type="hidden" id="' . $target . '" name="' . $target . '[]" value="' . $selected . '" />';

        return $return;
    }

    private function showHideSMTPoptions() {

        $return = '<script>
          jQuery(document).ready(function() {';
        $return.= '$("select[name=\'mailer_method\']").on("change", function(){
          var val = $(this).find(":selected").val();
          if(val == "SMTP"){
             $(".smtpoptions").show();
             }else{
             $(".smtpoptions").hide();
             }
          }).trigger("change");
          });
          </script>';

        return $return;
    }
    
    private function getViewerAssets($type = "js", $otherArr){
        
        $Arr = array();
        
        if($type == "css"){
            
            $Arr = array(
                array('id' => 'player.css', 'src' => CLO_DEF_CSS_URI . 'player/player.css' . debugASS()),
                array('id' => 'viewer.css', 'src' => CLO_DEF_CSS_URI . 'viewer.css' . debugASS())
            );
            
        }else{
            $Arr[] = array('id' => 'imageloader.js', 'src' => CLO_DEF_JS_URI . 'rotors/imageloader.js' . debugASS());
            $Arr[] = array('id' => 'media.js', 'src' => CLO_DEF_JS_URI . 'player/media.js' . debugASS());
            $Arr[] = array('id' => 'share.js', 'src' => CLO_DEF_JS_URI . 'rotors/share.js' . debugASS());
            $Arr[] = array('id' => 'viewer.js', 'src' => CLO_DEF_JS_URI . 'rotors/viewer.js' . debugASS());
        }
        return (is_array($otherArr) && !empty($otherArr)) ? array_merge($otherArr, $Arr) : $Arr;
    }

    public function printCssFiles() {
        global $core, $user;

        ##4 possibility here
        #
       #1 user not logged in and viewing login screen
        #2 user not logged in and viewing shared items screen
        #3 user logged in and viewing members area
        #4 user logged in and viewing shared items screen
        #
       
       $log = $user->logged_in ? true : false;

        $compress = $core->compress_js_css ? true : false;

        $path = $core->compress_js_css ? CLO_DEF_ASS_MIN_URI : CLO_DEF_JS_URI;

        $share = $core->isShareUrI() ? true : false;

        $return = '';

        $base = array(
            "bootstrap-" . $core->bootstrap_skin => array("skin", "appstyle"),
            "bootstrap-responsive" => "",
            "font-awesome" => "",
            "default" => ""
        );

        if ($log && !$share) {
            //application page
            $css = $compress ?
                    array(
                "base" => array("build", "appstyle"),
                "default" => "build",
                "app" => "build"
                    ) :
                    array_merge($base, array(
                        "app" => ""
            ));
        } else if (($share && !$log) || ($share && $log)) {
            //shared items page
            $css = $compress ?
                    array(
                "base" => "build",
                "default" => "build",
                "share" => "build"
                    ) :
                    array_merge($base, array(
                        "player" => "player",
                        "share" => ""
            ));
        } else {
            //user login-register page
            $css = $compress ?
                    array(
                "base" => "build",
                "default" => "build",
                "welcome" => "build"
                    ) :
                    array_merge($base, array(
                        "welcome" => ""
            ));
        }

        foreach ($css as $key => $value) {
            $id = false;
            if (is_array($value)) {
                $id = $value[1];
                $value = $value[0];
            }
            $href = ($value == "" ? "css/" : ($value == "build" ? "build/" : ("css/" . $value . "/") ) );
            $return.= '<link' . ($id ? ' id="' . $id . '"' : '') . ' rel="stylesheet" href="' . CLO_DEF_ASS_URI . $href . $key . '.css' . debugASS() . '">' . "\n";
        }
        return $return;
    }

    public function printJsFiles($header = false) {
        global $core, $user;

        ##4 possibility here
        #
       #1 user not logged in and viewing login screen
        #2 user not logged in and viewing shared items screen
        #3 user logged in and viewing members area
        #4 user logged in and viewing shared items screen
        #
       
       $log = $user->logged_in ? true : false;

        $compress = $core->compress_js_css ? true : false;

        $path = $core->compress_js_css ? CLO_DEF_ASS_MIN_URI : CLO_DEF_JS_URI;

        $share = $core->isShareUrI() ? true : false;

        $return = '';

        //scripts name => folder
        //global / request every page load
        $scripts = $header ? array(
            "jquery" => ($compress ? "build" : "jquery")
                ) : null;


        if ($log && !$share) {

            $script2 = $compress ?
                    array(
                "rotors" => "build",
                "app" => "build"
                    ) :
                    array(
                "jquery.ui" => "jquery",
                "transition" => "bootstrap",
                "alert" => "bootstrap",
                "modal" => "bootstrap",
                "dropdown" => "bootstrap",
                "tab" => "bootstrap",
                "tooltip" => "bootstrap",  
                "popover" => "bootstrap",
                "button" => "bootstrap",
                "collapse" => "bootstrap",
                "carousel" => "bootstrap",
                "typeahead" => "bootstrap",
                "affix" => "bootstrap",        
                "modernizr" => "rotors",
                "blockit" => "rotors",
                "bbq" => "rotors",
                "spinner" => "rotors",
                "selectbox" => "rotors",
                "fileinput" => "rotors",
                "lib" => "",
                "app" => "",
                "library" => "upload",
                "cl.fuploader.iframe-transport" => "upload",
                "cl.fuploader" => "upload",
                "cl.fuploader.process" => "upload",
                "cl.fuploader.image" => "upload",
                "cl.fuploader.validate" => "upload"
            );
        } else if (($share && !$log) || ($share && $log)) {

            $script2 = $compress ?
                    array(
                "pageLoader" => "build",
                "share" => "build"
                    ) :
                    array(
                "pageLoader" => "rotors",
                "bbq" => "rotors",
                "masonry" => "rotors",
                "share" => "rotors",
                "media" => "player",
                "ui.effects" => "rotors",
                "lib" => "",
                "spinner" => "rotors",
                "view" => ""
            );
        } else {
            //user login-register screen
            $script2 = $compress ?
                    array(
                "welcome" => "build"
                    ) :
                    array(
                "transition" => "bootstrap",
                "alert" => "bootstrap",
                "modal" => "bootstrap",
                "dropdown" => "bootstrap",
                "tab" => "bootstrap",
                "tooltip" => "bootstrap",  
                "popover" => "bootstrap",
                "button" => "bootstrap",
                "collapse" => "bootstrap",
                "carousel" => "bootstrap",
                "typeahead" => "bootstrap",
                "affix" => "bootstrap", 
                "modernizr" => "rotors",
                "blockit" => "rotors",
                "bbq" => "rotors",
                "lib" => "",
                "spinner" => "rotors",
                "validate" => "rotors",
                "welcome" => ""
            );
        }


        $scripts = $header ? $scripts : $script2;


        foreach ($scripts as $key => $value) {

            $src = ($value == "" ? "js/" : ($value == "build" ? "build/" : ("js/" . $value . "/") ) );
            $return.= '<script src="' . CLO_DEF_ASS_URI . $src . $key . '.js' . debugASS() . '"></script>' . "\n";
        }
        return $return;
    }

    public function printExtraJS() {

        $inline = $this->inlineASS;

        if (!empty($inline)){
            foreach ($inline as $key => $value) {
                foreach ($value as $k => $ass) {
                    print "\n<script>\n" . $ass . "\n</script>\n";
                }
            }
        }
    }

}