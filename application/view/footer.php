<?php

/**
 * footer
 * @package      CloShare v1.00
 * @copyright    2013
 * @license      MIT
 * @author      xneda.com
 * @version $Id: footer.php UTF-8 , 22-Jun-2013 | 04:21:33 nwdo ε
 */
if (!defined("_SECURE_PHP"))
    die('Direct access to this location is not allowed.');
?>
</div>

</div>

<div id="footer" class="navbar navbar-fixed-bottom">
   <div class="navbar-inner">
      <div class="container-fluid">
         <div class="pull-left bottom">
            <div class="copy"> &copy; <?php echo date("Y"); ?><a href="<?php echo $core->site_url; ?>"> <?php echo sanitise($core->site_name) ?></a>.</div>
         </div>
<?php if ($user->logged_in && !$core->isShareUrI() && (!$detect->isMobile() || $detect->isTablet())): print $content->getUserLimitHTML(); endif;?>
      </div>
   </div>
</div>

<?php if($user->logged_in && !$core->isShareUrI()):?>
<div id="foacts" class="foacts">
    <div class="btn-group">
        <a href="#" class="btn btn-inverse<?php echo $detect->isMobile() ? '' : ' btn-small';?> icon-copy ajax" data-action="fo_move" title="Move"></a>
        <a href="#" class="btn btn-inverse<?php echo $detect->isMobile() ? '' : ' btn-small btn-inverse';?> icon-edit"  data-action="fo_edit" title="Edit"></a>        
        <a href="#" class="foshare btn btn-inverse btn-small mark icon-share ajax" data-ajax="loadShareBox" data-action="fo_share" title="Share"></a>
    </div>
    <div class="btn-group pull-right">
       <a href="#" class="btn<?php echo $detect->isMobile() ? '' : ' btn-small';?> btn-danger icon-trash" data-action="fo_delete" title="Delete"></a>
    </div>
</div>

<div class="hide" id="actionbox">
    <div class="modal hide fade" tabindex="-1" data-width="320" data-focus-on="input:first" data-backdrop="static">
        <div class="modal-header">
            <h5 class="box_title"></h5>
        </div>

        <div class="modal-body">
            <form class="form-horizontal actform" data-async data-target="" action="" method="POST">

            </form>
        </div>

        <div class="modal-footer">
            <a href="#" class="btn" data-dismiss="modal">Cancel</a>
        </div>
    </div>
</div>
<?php endif;?>
<input id="device" type="hidden" class="dnone" value="<?php echo (($detect->isMobile() && !$detect->isTablet()) ? 'mobile' : ($detect->isTablet() ? 'tablet' : 'nor'));?>" />
<?php if($detect->isMobile() && !$core->isShareUrI()):?>

<!--<div id="actfootbox" style="position: fixed; z-index: 7; left: 0; right: 0; bottom: 0; height: 40px; background: #FAFAFA; border-top: 1px solid #AAA;">
   <div id="infoFoot" style="width: 50%; float:left; display: block">info</div>
   <div id="actFolder" style="width: 50%; float:left; display: block"></div>
</div>-->

<button id="goUp" class="btn btn-warning"><i class="icon-chevron-sign-up"></i></button>

<?php endif;?>
<?php
/**
 * 
 * load required js scripts.
 */
?>
<?php if($core->isShareUrI()):?>
<?php print $content->printJsFiles(true);?>
<?php endif;?>
<script>
var typesObj = <?php echo $fhand->createJSTypesArrays();?>;
var SITENAME = '<?php echo CLO_SITE_NAME?>', ASSURI = '<?php echo CLO_DEF_ASS_URI?>', ASSURIX = '<?php echo CLO_DEF_ASS_MIN_URI?>', DevWidth = ((window.innerWidth > 0) ? window.innerWidth : screen.width), DevHeight = ((window.innerHeight > 0) ? window.innerHeight : screen.height), shareOpts = <?php echo json_encode(explode(",", $core->share_options));?>;
<?php if(!$core->isShareUrI()):?>
<?php if($user->logged_in):?>
var uOptions = {
   dataType: "json",
   noind: true,
   autoUpload: <?php echo sanitise((int) $core->upload_auto_start); ?>,
   maxFileSize: <?php echo $user->getUserFileUploadSizeLimit(true); ?>,
   loadImageMaxFileSize: <?php echo sanitise($core->upload_preview_max_file_size_limit); ?>,
   maxNumberOfFiles: <?php echo ($user->getUserFileUploadItems() == 'N/A' ? undefined : $user->getUserFileUploadItems());?>,
   maxChunkSize: <?php echo $core->upload_max_chunk_size_limit;?>,
   forceIframeTransport: <?php echo ($detect->isAndroidOS()) ? 1 : 0;?>,
   allowedTypes: <?php echo (!$user->getUserAllowedFileTypes("js") ? 0 : "'".$user->getUserAllowedFileTypes("js")."'");?>,
   imageMaxWidth: <?php echo sanitise((int) $core->upload_preview_allowed_hdim); ?>,
   imageMaxHeight: <?php echo sanitise((int) $core->upload_preview_allowed_vdim); ?>,
   previewCrop: true
},
isIOS = <?php echo ($detect->isiOS()) ? 1 : 0;?>,
isAndroid = <?php echo ($detect->isAndroidOS()) ? 1 : 0;?>,
UIDIR = '<?php echo return6charsha1($user->userid)?>', freepArr = new Array("list", "upload","profile"<?php if($user->isAdmin()):?>,"users", "settings"<?php endif;?>) , currPage = '';
<?php endif;?>
<?php endif;?>  
</script>
<?php print $content->printJsFiles(false);?>
<?php if($user->logged_in):?>
<img src="<?php echo CLO_DEF_ASS_URI.'img/ind_big.gif'?>" class="dn" />
<img src="<?php echo CLO_DEF_ASS_URI.'img/ind_big_tr.gif'?>" class="dn" />
<img src="<?php echo CLO_DEF_ASS_URI.'img/ind_small_tr.gif'?>" class="dn" />
<img src="<?php echo CLO_DEF_ASS_URI.'img/ind_xsmall_tr.gif'?>" class="dn" />
<?php else:?>
<img src="<?php echo CLO_DEF_ASS_URI.'img/ind_xsmall_tr.gif'?>" class="dn" />
<img src="<?php echo CLO_DEF_ASS_URI.'img/ind_xsmall.black.gif'?>" class="dn" />
<?php endif;?>
<?php if($user->isAdmin()):?>
    <?php $core->versionCheck();?>
<?php endif;?>
</body>
</html>