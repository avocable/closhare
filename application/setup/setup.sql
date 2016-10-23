SET FOREIGN_KEY_CHECKS=0;
-- --------------------------------------------------------
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
-- --------------------------------------------------------
SET time_zone = "+00:00";
-- --------------------------------------------------------
/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
-- --------------------------------------------------------
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
-- --------------------------------------------------------
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
-- --------------------------------------------------------
/*!40101 SET NAMES utf8 */;
-- --------------------------------------------------------

USE `##!DBNAME##`;
--
-- Table structure for table `##!DBPREFIX##_folders`
--
-- --------------------------------------------------------
DROP TABLE IF EXISTS `##!DBPREFIX##_folders`;
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `##!DBPREFIX##_folders` (
  `folder_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `folder_user_id` int(11) NOT NULL,
  `folder_v_path` varchar(255) CHARACTER SET latin1 NOT NULL,
  `folder_name` varchar(255) NOT NULL,
  `folder_parent_id` int(11) NOT NULL,
  `folder_level` int(11) NOT NULL,
  `folder_description` varchar(255) NOT NULL,
  `folder_mime` varchar(255) CHARACTER SET latin1 NOT NULL,
  `folder_icon` varchar(255) CHARACTER SET latin1 NOT NULL,
  `folder_files` int(11) NOT NULL DEFAULT '0',
  `folder_static` varchar(1) CHARACTER SET latin1 COLLATE latin1_bin DEFAULT '0',
  PRIMARY KEY (`folder_id`),
  KEY `user_id` (`folder_user_id`),
  FULLTEXT KEY `folder_search` (`folder_name`,`folder_description`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=9;
-- --------------------------------------------------------
--
-- Dumping data for table `##!DBPREFIX##_folders`
--
-- --------------------------------------------------------
INSERT INTO `##!DBPREFIX##_folders` (`folder_id`, `folder_user_id`, `folder_v_path`, `folder_name`, `folder_parent_id`, `folder_level`, `folder_description`, `folder_mime`, `folder_icon`, `folder_files`, `folder_static`) VALUES
(1, 0, 'root', 'Document Root', -1, 0, 'All files', 'all', '', 0, '1'),
(2, 0, 'images', 'Image Files', 1, 0, 'jpg,png,gif...', 'image', 'icon-picture', 0, '1'),
(3, 0, 'videos', 'Video Files', 1, 0, 'mp4,avi,mov,wmv...', 'video', 'icon-film', 0, '1'),
(4, 0, 'sounds', 'Sound Files', 1, 0, 'mp3,wma,aac...', 'audio', 'icon-music', 0, '1'),
(5, 0, 'documents', 'Document Files', 1, 0, 'txt,pdf,doc...', 'document', 'icon-book', 0, '1'),
(6, 0, 'others', 'Others', 1, 0, 'html,zip,rar...', 'other', 'icon-archive', 0, '1'),
(7, 1, 'Sample-Folder', 'Sample Folder', 1, 1, 'This is your first sample folder.', '', '', 1, '0'),
(8, 1, 'Child-of-Sample-Folder', 'Child of Sample Folder', 7, 2, 'This is your second sample folder.', '', '', 0, '0');

-- --------------------------------------------------------
--
-- Table structure for table `##!DBPREFIX##_settings`
--
DROP TABLE IF EXISTS `##!DBPREFIX##_settings`;
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `##!DBPREFIX##_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `site_name` varchar(50) DEFAULT NULL,
  `site_slogan` varchar(250) NOT NULL,
  `meta_description` varchar(255) NOT NULL,
  `site_email` varchar(40) DEFAULT NULL,
  `site_url` varchar(200) DEFAULT NULL,
  `site_cdn` varchar(100) NOT NULL,
  `use_site_cdn` int(1) NOT NULL DEFAULT '0',
  `bootstrap_skin` enum('default','metro') NOT NULL DEFAULT 'default',
  `compress_js_css` int(1) NOT NULL,
  `register_allowed` tinyint(1) NOT NULL DEFAULT '1',
  `register_password_min_length` varchar(2) CHARACTER SET latin1 NOT NULL DEFAULT '6',
  `register_user_limit` tinyint(1) NOT NULL DEFAULT '0',
  `register_send_welcome_mail` int(11) NOT NULL,
  `register_welcome_mail_template` text NOT NULL,
  `register_terms_template` text NOT NULL,
  `recover_mail_template` text NOT NULL,
  `recover_mail_template_res` text NOT NULL,
  `upload_auto_start` int(1) NOT NULL DEFAULT '1',
  `upload_allowed_file_types` varchar(255) NOT NULL,
  `upload_max_file_size_limit` varchar(55) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL COMMENT 'sets the size limit for single file upload',
  `upload_max_chunk_size_limit` varchar(55) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '8000000' COMMENT 'To upload large files in smaller chunks, set this option to a preferred maximum chunk size',
  `upload_user_default_disk_limit` varchar(55) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL COMMENT 'if disk space of user has not been limited by admin. total disk of specific user uses this value',
  `upload_user_default_up_items` varchar(55) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `upload_concurrent_limit` varchar(2) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '1' COMMENT 'How many files will send to the server at the same time?',
  `upload_preview_max_file_size_limit` varchar(55) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `upload_preview_allowed_hdim` int(11) NOT NULL,
  `upload_preview_allowed_vdim` int(11) NOT NULL,
  `upload_thumb_crop` int(1) NOT NULL DEFAULT '0',
  `upload_thumb_w` varchar(4) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `upload_thumb_h` varchar(4) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `items_per_page` int(5) NOT NULL,
  `share_options` varchar(255) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `google_api_key` varchar(255) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `mailer_method` enum('PHP','SMTP') CHARACTER SET latin1 NOT NULL DEFAULT 'PHP',
  `mailer_smtp_host` varchar(100) DEFAULT NULL,
  `mailer_smtp_user` varchar(55) DEFAULT NULL,
  `mailer_smtp_pass` varchar(55) DEFAULT NULL,
  `mailer_smtp_port` varchar(11) CHARACTER SET latin1 COLLATE latin1_bin DEFAULT NULL,
  `mailer_connection_type` varchar(5) NOT NULL DEFAULT '0',
  `clo_hash` tinyblob NOT NULL,
  `clo_version` varchar(5) CHARACTER SET latin1 COLLATE latin1_bin DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;
-- --------------------------------------------------------
--
-- Dumping data for table `##!DBPREFIX##_settings`
--
INSERT INTO `##!DBPREFIX##_settings` (`id`, `site_name`, `site_slogan`, `meta_description`, `site_email`, `site_url`, `site_cdn`, `use_site_cdn`, `bootstrap_skin`, `compress_js_css`, `register_allowed`, `register_password_min_length`, `register_user_limit`, `register_send_welcome_mail`, `register_welcome_mail_template`, `register_terms_template`, `recover_mail_template`, `recover_mail_template_res`, `upload_auto_start`, `upload_allowed_file_types`, `upload_max_file_size_limit`, `upload_max_chunk_size_limit`, `upload_user_default_disk_limit`, `upload_user_default_up_items`, `upload_concurrent_limit`, `upload_preview_max_file_size_limit`, `upload_preview_allowed_hdim`, `upload_preview_allowed_vdim`, `upload_thumb_crop`, `upload_thumb_w`, `upload_thumb_h`, `items_per_page`, `share_options`, `google_api_key`, `mailer_method`, `mailer_smtp_host`, `mailer_smtp_user`, `mailer_smtp_pass`, `mailer_smtp_port`, `mailer_connection_type`, `clo_hash`, `clo_version`) VALUES
(1, 'CloShare', 'The coolest way to collect & share files online!', 'Upload & Store & Share files online easily...', 'contact@example.com', 'http://www.example.com', 'http://cdn.example.com', 0, 'default', 0, 1, '5', 0, 1, 'Hello, &lt;b&gt;[NAME]&lt;/b&gt;&lt;p&gt;&lt;/p&gt; &lt;br&gt;  We want to say that we are pleased to see you among us!&lt;br&gt; &lt;br&gt;  Now you can easily store/share your files with simple clicks on &lt;a href=&quot;[SITEURL]&quot;&gt;[SITENAME]&lt;/a&gt;.&lt;br&gt;  You can login to your [SITENAME] account using details below.&lt;br&gt; &lt;br&gt;  E-mail: &lt;b&gt;[USERMAIL]&lt;/b&gt;&lt;br&gt;  Password: &lt;b&gt;[USERPASS]&lt;/b&gt;&lt;p&gt;&lt;/p&gt;&lt;br&gt; &lt;a href=&quot;[SITEURL]&quot; target=&quot;_blank&quot;&gt;Click here&lt;/a&gt; to go to your account.&lt;p&gt;&lt;/p&gt;&lt;p align=&quot;right&quot;&gt;[SITENAME].&lt;br&gt;&lt;/p&gt;', '&lt;span style=&quot;font-size: 12px; color: rgb(51, 51, 51);&quot;&gt;&lt;span style=&quot;color:rgb(255,0,0);font-size: 12px;&quot;&gt;The following example for the terms of the service does not reflect the reality  else where&lt;/span&gt; &lt;b style=&quot;font-size:12px;color: rgb(51, 51, 51);&quot;&gt;closhare.xneda.com &lt;/b&gt;&lt;/span&gt;&lt;span class=&quot;tosTitle&quot; style=&quot;color: rgb(51, 51, 51); font-size: 18.6667px;&quot;&gt;&lt;br&gt;Introduction&lt;/span&gt; &lt;br&gt;&lt;br&gt;    Welcome to closhare.xneda.com.  This website is owned and operated by XnedA.  By visiting our website and accessing the information, resources, services, products, and tools we provide, you understand and agree to accept and adhere to the following terms and conditions as stated in this policy (hereafter referred to as ''User Agreement''). &lt;br&gt;&lt;br&gt;    This agreement is in effect as of Oct 10, 2012. &lt;br&gt;&lt;br&gt;    We reserve the right to change this User Agreement from time to time without notice. You acknowledge and agree that it is your responsibility to review this User Agreement periodically to familiarize yourself with any modifications. Your continued use of this site after such modifications will constitute acknowledgment and agreement of the modified terms and conditions. &lt;br&gt;&lt;br&gt;&lt;span class=&quot;tosTitle&quot; style=&quot;font-size:14pt;&quot;&gt;Responsible Use and Conduct&lt;/span&gt; &lt;br&gt;&lt;br&gt;    By visiting our website and accessing the information, resources, services, products, and tools we provide for you, either directly or indirectly (hereafter referred to as ''Resources''), you agree to use these Resources only for the purposes intended as permitted by (a) the terms of this User Agreement, and (b) applicable laws, regulations and generally accepted online practices or guidelines. &lt;br&gt;&lt;br&gt;    Wherein, you understand that: &lt;br&gt;&lt;br&gt;    a. In order to access our Resources, you may be required to provide certain information about yourself (such as identification, contact details,  etc.) as part of the registration  process, or as part of your ability to use the Resources. You agree that any information you provide will always be accurate, correct, and up to date. &lt;br&gt;&lt;br&gt;    b. You are responsible for maintaining the confidentiality of any login information associated with any account you use to access our Resources.  Accordingly, you are responsible for all activities that occur under your account/s. &lt;br&gt;&lt;br&gt;    c. Accessing (or attempting to access) any of our Resources by any means other than through the means we provide, is strictly prohibited. You specifically agree not to access (or attempt to access) any of our Resources through any automated, unethical or unconventional means. &lt;br&gt;&lt;br&gt;    d. Engaging in any activity that disrupts or interferes with our Resources, including the servers and/or networks to which our Resources are located or connected, is strictly prohibited. &lt;br&gt;&lt;br&gt;    e. Attempting to copy, duplicate, reproduce, sell, trade, or resell our Resources is strictly prohibited. &lt;br&gt;&lt;br&gt;    f. You are solely responsible any consequences, losses, or damages that we may directly or indirectly incur or suffer due to any unauthorized activities conducted by you, as explained above, and may incur criminal or civil liability. &lt;br&gt;&lt;br&gt;    g. We may provide various open communication tools on our website, such as blog comments, blog posts, public chat, forums, message boards, newsgroups, product ratings and reviews, various social media services, etc.  You understand that generally we do not pre-screen or monitor the content posted by users of these various communication tools, which means that if you choose to use these tools to submit any type of content to our website, then it is your personal responsibility to use these tools in a responsible and ethical manner.  By posting information or otherwise using any open communication tools as mentioned, you agree that you will not upload, post, share, or otherwise distribute any content that: &lt;br&gt;&lt;br&gt;    i. Is illegal, threatening, defamatory, abusive, harassing, degrading, intimidating, fraudulent, deceptive, invasive, racist, or contains any type of suggestive, inappropriate, or explicit language;&lt;br&gt;    ii. Infringes on any trademark, patent, trade secret, copyright, or other proprietary right of any party;&lt;br&gt;    Iii. Contains any type of unauthorized or unsolicited advertising;&lt;br&gt;    Iiii. Impersonates any person or entity, including any closhare.xneda.com employees or representatives.&lt;br&gt; &lt;br&gt;&lt;br&gt;    We have the right at our sole discretion to remove any content that, we feel in our judgment does not comply with this User Agreement, along with any content that we feel is otherwise offensive, harmful, objectionable, inaccurate, or violates any 3rd party copyrights or trademarks. We are not responsible for any delay or failure in removing such content. If you post content that we choose to remove, you hereby consent to such removal, and consent to waive any claim against us. &lt;br&gt;&lt;br&gt;    h. We do not assume any liability for any content posted by you or any other 3rd party users of our website.  However, any content posted by you using any open communication tools on our website, provided that it doesn''t violate or infringe on any 3rd party copyrights or trademarks, becomes the property of XnedA, and as such, gives us a perpetual, irrevocable, worldwide, royalty-free, exclusive license to reproduce, modify, adapt, translate, publish, publicly display and/or distribute as we see fit.  This only refers and applies to content posted via open communication tools as described, and does not refer to information that is provided as part of the registration  process, necessary in order to use our Resources. &lt;br&gt;&lt;br&gt;        i. You agree to indemnify and hold harmless XnedA and its parent company and affiliates, and their directors, officers, managers, employees, donors, agents, and licensors, from and against all losses, expenses, damages and costs, including reasonable attorneys'' fees, resulting from any violation of this User Agreement or the failure to fulfill any obligations relating to your account incurred by you or any other person using your account. We reserve the right to take over the exclusive defense of any claim for which we are entitled to indemnification under this User Agreement. In such event, you shall provide us with such cooperation as is reasonably requested by us. &lt;br&gt;&lt;br&gt;&lt;span class=&quot;tosTitle&quot; style=&quot;font-size:14pt;&quot;&gt;Limitation of Warranties&lt;/span&gt; &lt;br&gt;&lt;br&gt;    By using our website, you understand and agree that all Resources we provide are &quot;as is&quot; and &quot;as available&quot;.  This means that we do not represent or warrant to you that:&lt;br&gt;i) the use of our Resources will meet your needs or requirements.&lt;br&gt;ii) the use of our Resources will be uninterrupted, timely, secure or free from errors.&lt;br&gt;iii) the information obtained by using our Resources will be accurate or reliable, and&lt;br&gt;iv) any defects in the operation or functionality of any Resources we providewill be repaired or corrected.&lt;br&gt; &lt;br&gt;&lt;br&gt;    Furthermore, you understand and agree that: &lt;br&gt;&lt;br&gt;v) any content downloaded or otherwise obtained through the use of our Resources is done at your own discretion and risk, and that you are solely responsible for any damage to your computer or other devices for any loss of data that may result from the download of such content.&lt;br&gt;vi) no information or advice, whether expressed, implied, oral or written, obtained by you from XnedA or through any Resources we provide shall create any warranty, guarantee, or conditions of any kind, except for those expressly outlined in this User Agreement.&lt;br&gt; &lt;br&gt;&lt;br&gt;&lt;span class=&quot;tosTitle&quot; style=&quot;font-size:14pt;&quot;&gt;Limitation of Liability&lt;/span&gt; &lt;br&gt;&lt;br&gt;        In conjunction with the Limitation of Warranties as explained above, you expressly understand and agree that any claim against us shall be limited to the amount you paid, if any, for use of products and/or services.  XnedA will not be liable for any direct, indirect, incidental, consequential or exemplary loss or damages which may be incurred by you as a result of using our Resources, or as a result of any changes, data loss or corruption, cancellation, loss of access, or downtime to the full extent that applicable limitation of liability laws apply. &lt;br&gt;&lt;br&gt;&lt;span class=&quot;tosTitle&quot; style=&quot;font-size:14pt;&quot;&gt;Copyrights/Trademarks&lt;/span&gt; &lt;br&gt;&lt;br&gt;    All content and materials available on closhare.xneda.com, including but not limited to text, graphics, website name, code, images and logos are the intellectual property of XnedA, and are protected by applicable copyright and trademark law.  Any inappropriate use, including but not limited to the reproduction, distribution, display or transmission of any content on this site is strictly prohibited, unless specifically authorized by XnedA. &lt;br&gt;&lt;br&gt;&lt;span class=&quot;tosTitle&quot; style=&quot;font-size:14pt;&quot;&gt;Termination of Use&lt;/span&gt; &lt;br&gt;&lt;br&gt;    You agree that we may, at our sole discretion, suspend or terminate your access to all or part of our website and Resources with or without notice and for any reason, including, without limitation, breach of this User Agreement. Any suspected illegal, fraudulent or abusive activity may be grounds for terminating your relationship and may be referred to appropriate law enforcement authorities.  Upon suspension or termination, your right to use the Resources we provide will immediately cease, and we reserve the right to remove or delete any information that you may have on file with us, including any account or login information. &lt;br&gt;&lt;br&gt;&lt;span class=&quot;tosTitle&quot; style=&quot;font-size:14pt;&quot;&gt;Governing Law&lt;/span&gt; &lt;br&gt;&lt;br&gt;    This website is controlled by XnedATurkey.  It can be accessed by most countries around the world.  By accessing our website, you agree that the statutes and laws of our state, without regard to the conflict of laws and the United Nations Convention on the International Sales of Goods, will apply to all matters relating to the use of this website and the purchase of any products or services through this site. &lt;br&gt;&lt;br&gt;    Furthermore, any action to enforce this User Agreement shall be brought in the federal or state courts Turkey You hereby agree to personal jurisdiction by such courts, and waive any jurisdictional, venue, or inconvenient forum objections to such courts. &lt;br&gt;&lt;br&gt;&lt;span class=&quot;tosTitle&quot; style=&quot;font-size:14pt;&quot;&gt;Guarantee&lt;/span&gt; &lt;br&gt;&lt;br&gt;    UNLESS OTHERWISE EXPRESSED, XnedA EXPRESSLY DISCLAIMS ALL WARRANTIES AND CONDITIONS OF ANY KIND, WHETHER EXPRESS OR IMPLIED, INCLUDING, BUT NOT LIMITED TO THE IMPLIED WARRANTIES AND CONDITIONS OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NON-INFRINGEMENT. &lt;br&gt;&lt;br&gt;', 'Hello, &lt;b&gt;[NAME]&lt;/b&gt;&lt;br&gt;&lt;br&gt;You have just requested a password reset from [SITENAME].&lt;br&gt;If you think this is on you. Please &lt;a href=&quot;[SITEURL]/?reset=[HASHV]&quot;&gt;click here&lt;/a&gt; to go on.&lt;br&gt;If not please ignore this mail and do nothing.&lt;br&gt;&lt;p align=&quot;right&quot;&gt;[SITENAME].&lt;br&gt;&lt;/p&gt;', 'Hello, &lt;b&gt;[NAME]&lt;/b&gt;&lt;br&gt;&lt;br&gt;Here your new login details you have just set:&lt;br&gt;&lt;br&gt;E-mail: &lt;b&gt;[USERMAIL]&lt;/b&gt;&lt;br&gt;  Password: &lt;b&gt;[USERPASS]&lt;/b&gt;&lt;br&gt;&lt;br&gt;&lt;p align=&quot;right&quot;&gt;[SITENAME].&lt;br&gt;&lt;/p&gt;', 0, 'All', '52428800', '5242880', '2147483648', '0', '1', '20971520', 2592, 1936, 1, '120', '120', 10, 'email,facebook,twitter,linkedin,tumblr,googleplus,pinterest', '', 'PHP', 'smtp.example.com', 'mail@example.com', '123456', '25', '', '', '1.0.1');
-- --------------------------------------------------------
--
-- Table structure for table `##!DBPREFIX##_uploads`
--
DROP TABLE IF EXISTS `##!DBPREFIX##_uploads`;
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `##!DBPREFIX##_uploads` (
  `file_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `file_user_id` int(11) NOT NULL,
  `file_note` tinytext NOT NULL,
  `file_title` varchar(255) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_key` varchar(255) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `file_extension` varchar(15) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `file_folder` int(11) NOT NULL,
  `file_mime_folder` int(11) NOT NULL,
  `file_size` varchar(55) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `file_password` varchar(55) DEFAULT NULL,
  `file_date` datetime NOT NULL,
  UNIQUE KEY `file_id` (`file_id`),
  KEY `file_search` (`file_user_id`,`file_title`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=4 ;
--
-- Dumping data for table `##!DBPREFIX##_uploads`
--
-- --------------------------------------------------------
INSERT INTO `##!DBPREFIX##_uploads` (`file_id`, `file_user_id`, `file_note`, `file_title`, `file_name`, `file_key`, `file_extension`, `file_folder`, `file_mime_folder`, `file_size`, `file_password`, `file_date`) VALUES
(1, 1, '', 'sample3.jpg', 'sample3', '0a2c0c13a', 'jpg', 1, 2, '124429', NULL, '2013-06-25 00:00:18'),
(2, 1, '', 'sample2.jpg', 'sample2', '836a8f043', 'jpg', 7, 2, '124429', NULL, '2013-06-25 00:00:19'),
(3, 1, '', 'sample1.jpg', 'sample1', 'ad91304ca', 'jpg', 1, 2, '124429', NULL, '2013-06-25 00:00:19');
-- --------------------------------------------------------
--
-- Table structure for table `##!DBPREFIX##_users`
--
DROP TABLE IF EXISTS `##!DBPREFIX##_users`;
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `##!DBPREFIX##_users` (
  `user_id` bigint(20) NOT NULL AUTO_INCREMENT COMMENT 'auto incrementing user_id of each user, unique index',
  `user_name` varchar(64) COLLATE utf8_unicode_ci NOT NULL COMMENT 'user''s name',
  `user_password` char(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'user''s password in salted and sha256 hashed format',
  `user_email` text COLLATE utf8_unicode_ci COMMENT 'user''s email',
  `user_role` enum('admin','user') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'user' COMMENT 'Required for user permissions',
  `user_limit` varchar(55) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `user_lastloginTime` datetime NOT NULL,
  `user_lastloginIP` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `user_created` datetime NOT NULL,
  `user_token` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `user_status` enum('active','inactive') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'active' COMMENT 'Set user status inactive or active',
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `user_name` (`user_name`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='user data' AUTO_INCREMENT=2;
--
-- Dumping data for table `##!DBPREFIX##_users`
--
-- --------------------------------------------------------
INSERT INTO `##!DBPREFIX##_users` (`user_id`, `user_name`, `user_password`, `user_email`, `user_role`, `user_limit`, `user_lastloginTime`, `user_lastloginIP`, `user_created`, `user_token`, `user_status`) VALUES
(1, 'John Nedo', 'DMsF8MBP96WnaX_iKUAiFQ', 'nedo@example.com', 'admin', '0', '2013-01-27 14:32:53', '127.0.0.1', '0000-00-00 00:00:00', '', 'active');
-- --------------------------------------------------------
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
-- --------------------------------------------------------
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
-- --------------------------------------------------------
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
