<?php
/*
WP Post Signature Page
*/
require_once ABSPATH . WPINC . '/pluggable.php';

$wpps_status = "normal";

if(is_array($_POST) && array_key_exists('wpps_update_options', $_POST) && $_POST['wpps_update_options'] === 'Y') {
	// global
	if (is_array($_GET) && array_key_exists('type', $_GET) && $_GET['type'] === 'global' && current_user_can('activate_plugins')) {

		$wp_post_signature_global = maybe_unserialize(get_option('wp_post_signature_global'));

		if (!is_array($wp_post_signature_global)) {
			$wp_post_signature_global = array();
		}

		if (array_key_exists('signature_global_priority', $_POST)) {
			$wp_post_signature_global['signature_global_priority'] = intval($_POST['signature_global_priority']);
		}

		update_option("wp_post_signature_global", maybe_serialize($wp_post_signature_global));
		$wpps_status = 'update_success';
	}

	// for user
	if (is_array($_GET) && array_key_exists('type', $_GET) && $_GET['type'] === 'user') {

		$current_user_id = get_current_user_id();
		$wp_post_signature = maybe_unserialize(get_option('wp_post_signature'));

		if (!is_array($wp_post_signature)) {
			$wp_post_signature = array();
		}
		$wp_post_signature[$current_user_id] = $_POST;

		$categories = get_categories('hide_empty=0');
		$exclude_cates = array();
		$i = 0;
		foreach ($categories as $category) {
			if (array_key_exists('signature_include_cates', $_POST)
				&& is_array($_POST['signature_include_cates'])
				&& in_array($category->cat_ID, $_POST['signature_include_cates'])){
				// do nothing
			} else {
				$exclude_cates[$i] = $category->cat_ID;
				$i++;
			}
		}
		$wp_post_signature[$current_user_id]['signature_exclude_cates'] = $exclude_cates;

		update_option("wp_post_signature", maybe_serialize($wp_post_signature));
		$wpps_status = 'update_success';
	}
}

if(!class_exists('WPPostSignaturePage')) {
class WPPostSignaturePage {

private function getValue($a, $k)
{
	if (is_array($a) && array_key_exists($k, $a)) {
		return ($a[$k]);
	}
	return NULL;
}

private function getBool($a, $k)
{
	if (is_array($a) && array_key_exists($k, $a)) {
		return ($a[$k] === true);
	}
	return false;
}

private function getStr($a, $k)
{
	if (is_array($a) && array_key_exists($k, $a)) {
		if (is_string($a[$k])) {
			return $a[$k];
		}
	}
	return '';
}

private function getArray($a, $k)
{
	if (is_array($a) && array_key_exists($k, $a)) {
		if (is_array($a[$k])) {
			return $a[$k];
		}
	}
	return array();
}

public function WPPostSignature_Options_Page()
{
	?>

	<div class="wrap">
	<div id="wpps-options">
	<div id="wpps-title"><h2>WP Post Signature</h2></div>
	<?php
	global $wpps_status;
	if($wpps_status == 'update_success')
		$message =__('Configuration updated', 'wp-post-signature') . "<br />";
	else if($wpps_status == 'update_failed')
		$message =__('Error while saving options', 'wp-post-signature') . "<br />";
	else
		$message = '';

	if($message != "") {
	?>
		<div class="updated"><strong><p><?php
		echo $message;
		?></p></strong></div><?php
	} ?>
	<div id="wpps-desc">
	<p><?php _e('This plugin allows you to append a signature after every post. Some variables can be used, such as %post_title%, %post_link%, %bloginfo_name%, %bloginfo_url%, and so on. It supports multiuser.', 'wp-post-signature'); ?></p>
	</div>

	<!--right-->
	<div class="postbox-container" style="float:right;width:300px;">
	<div class="metabox-holder">
	<div class="meta-box-sortables">

	<!--about-->
	<div id="wpps-about" class="postbox">
	<h3 class="hndle"><?php _e('About this plugin', 'wp-post-signature'); ?></h3>
	<div class="inside"><ul>
	<li><a href="http://wordpress.org/extend/plugins/wp-post-signature/"><?php _e('Plugin URI', 'wp-post-signature'); ?></a></li>
	<li><a href="http://www.cbug.org" target="_blank"><?php _e('Author URI', 'wp-post-signature'); ?></a></li>
	</ul></div>
	</div>
	<!--about end-->

	<!-- donate -->
	<div id="wpps-donate" class="postbox">
	<h3 class="hndle"><?php _e('Donate', 'wp-post-signature'); ?></h3>
	<div class="inside">
	<center><a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=WELZBBHQ62URW">
		<img src="https://www.paypalobjects.com/en_GB/i/btn/btn_donate_LG.gif" />
	</a></center>
	</div>
	</div>
	<!-- donate end -->

	<!--others-->
	<!--others end-->

	</div></div></div>
	<!--right end-->

	<!--left-->
	<div class="postbox-container" style="float:none;margin-right:320px;">
	<div class="metabox-holder">
	<div class="meta-box-sortabless">

	<?php if (current_user_can('activate_plugins')) { ?>
	<!--global setting-->
	<div id="wpps-setting-global" class="postbox">
	<h3 class="hndle"><?php _e('Global Settings', 'wp-post-signature'); ?></h3>
	<?php
		$wp_post_signature_global = maybe_unserialize(get_option('wp_post_signature_global'));
	?>
	<form method="post" action="<?php echo get_bloginfo("wpurl"); ?>/wp-admin/options-general.php?page=wp-post-signature&type=global">
	<div style="padding-left: 10px;">
	<input type="hidden" name="wpps_update_options" value="Y">

	<p><?php _e('The order in which this plugin is executed. The lower the earlier.', 'wp-post-signature'); ?></p>
	<select name="signature_global_priority">
		<?php
		$i = 1;
		$saved = intval($this->getValue($wp_post_signature_global, 'signature_global_priority'));
		$saved = ($saved < 1 || $saved > 10 ? 10 : $saved);
		do {
			echo '<option ';
			selected( $saved, $i );
			echo ' value="'.$i.'">'.$i.'</option>';
			$i++;
		} while ($i < 11);
		?>
	</select>

	<p class="submit">
	<input type="submit" name="Submit" value="<?php _e('Save Changes', 'wp-post-signature'); ?>" />
	</p>
	</div>
	</form>
	</div>
	<!--global setting end-->
	<?php } ?>

	<!--setting-->
	<div id="wpps-setting" class="postbox">
	<h3 class="hndle"><?php _e('User Settings', 'wp-post-signature'); ?></h3>
	<?php
		$current_user_id = get_current_user_id();
		$wp_post_signature = maybe_unserialize(get_option('wp_post_signature'));
		$current_signature = array();

		if (is_array($wp_post_signature) && array_key_exists($current_user_id, $wp_post_signature)) {
			$current_signature = $wp_post_signature[$current_user_id];
		}
	?>
	<form method="post" action="<?php echo get_bloginfo("wpurl"); ?>/wp-admin/options-general.php?page=wp-post-signature&type=user">
	<div style="padding-left: 10px;">
	<input type="hidden" name="wpps_update_options" value="Y">

	<p><?php _e('Enter your post signature in the text area below. HTML markup is allowed.', 'wp-post-signature'); ?></p>
	<textarea cols="75" rows="5" name="signature_text"><?php echo stripslashes($this->getStr($current_signature, 'signature_text')); ?></textarea><br />
	<p><?php _e('Will the signature be on or off by default?', 'wp-post-signature'); ?></p>
	<input type="radio" name="signature_switch" value="yes" <?php if($this->getStr($current_signature, 'signature_switch') == 'yes') { echo 'checked="checked"'; } ?> /><?php _e('On', 'wp-post-signature'); ?>
	<input type="radio" name="signature_switch" value="no" <?php if($this->getStr($current_signature, 'signature_switch') == 'no') { echo 'checked="checked"'; } ?> /><?php _e('Off', 'wp-post-signature'); ?><br />

	<p><?php _e('Where should the signature be placed?', 'wp-post-signature'); ?></p>
	<input type="radio" name="signature_pos" value="top" <?php if($this->getStr($current_signature, 'signature_pos') == 'top') { echo 'checked="checked"'; } ?> /><?php _e('Top', 'wp-post-signature'); ?>
	<input type="radio" name="signature_pos" value="bottom" <?php if($this->getStr($current_signature, 'signature_pos') == 'bottom') { echo 'checked="checked"'; } ?> /><?php _e('Bottom', 'wp-post-signature'); ?><br />

	<p><?php _e('Will the signature be appended to the excerpts of posts?', 'wp-post-signature'); ?></p>
	<input type="radio" name="signature_excerpt" value="yes" <?php if($this->getStr($current_signature, 'signature_excerpt') == 'yes') { echo 'checked="checked"'; } ?> /><?php _e('On', 'wp-post-signature'); ?>
	<input type="radio" name="signature_excerpt" value="no" <?php if($this->getStr($current_signature, 'signature_excerpt') == 'no') { echo 'checked="checked"'; } ?> /><?php _e('Off', 'wp-post-signature'); ?><br />

	<p><?php _e('Will the signature be appended to the posts in archive or category list?', 'wp-post-signature'); ?></p>
	<input type="radio" name="signature_list_switch" value="yes" <?php if($this->getStr($current_signature, 'signature_list_switch') == 'yes') { echo 'checked="checked"'; } ?> /><?php _e('Yes', 'wp-post-signature'); ?>
	<input type="radio" name="signature_list_switch" value="no" <?php if($this->getStr($current_signature, 'signature_list_switch') == 'no') { echo 'checked="checked"'; } ?> /><?php _e('No', 'wp-post-signature'); ?>
	<br />

	<p><?php _e('Which types of content should the signature be placed?', 'wp-post-signature'); ?></p>
	<?php
	$post_types = get_post_types();
	foreach ($post_types as $post_type ) {
		$check_status = '';
		if(in_array($post_type, $this->getArray($current_signature, 'signature_include_types'))) {
			$check_status = 'checked="checked"';
		}
		?>
		<input type="checkbox" name="signature_include_types[]" value="<?php echo $post_type; ?>" <?php echo $check_status; ?> /><?php _e($post_type); ?><br />
		<?php
	}
	?>
	<p><a href="javascript:void(0)" onclick="checkAll('signature_include_types[]')"><?php _e('check all', 'wp-post-signature'); ?></a> |
	<a href="javascript:void(0)" onclick="checkReverse('signature_include_types[]')"><?php _e('check reverse', 'wp-post-signature'); ?></a></p>

	<p><?php _e('Which categories should the signature be placed?', 'wp-post-signature'); ?></p>
	<?php
		$categories = get_categories('hide_empty=0');
		foreach ($categories as $category) {
			$opts = '<input type="checkbox" name="signature_include_cates[]" value="' . $category->cat_ID . '"';
			if(in_array($category->cat_ID, $this->getArray($current_signature, 'signature_exclude_cates'))){
				//do nothing
			} else {
				$opts .= 'checked="checked"';
			}
			$opts .= ' />' . $category->cat_name . '<br />';
			echo $opts;
		}
	?>
	<p><a href="javascript:void(0)" onclick="checkAll('signature_include_cates[]')"><?php _e('check all', 'wp-post-signature'); ?></a> |
	<a href="javascript:void(0)" onclick="checkReverse('signature_include_cates[]')"><?php _e('check reverse', 'wp-post-signature'); ?></a></p>

	<script type="text/javascript">
	//全选
	function checkAll(name){
		var names=document.getElementsByName(name);
		var len=names.length;
		if(len>0){
			var i=0;
			for(i=0;i<len;i++)
				names[i].checked=true;
		}
	}

	//反选
	function checkReverse(name){
		var names=document.getElementsByName(name);
		var len=names.length;
		if(len>0){
			var i=0;
			for(i=0;i<len;i++){
				if(names[i].checked)
					names[i].checked=false;
				else
					names[i].checked=true;
			}
		}
	}
	</script>

	<p class="submit">
	<input type="submit" name="Submit" value="<?php _e('Save Changes', 'wp-post-signature'); ?>" />
	</p>
	</div>
	</form>
	</div>
	<!--setting end-->

	<!--others-->
	<div id="wpps-info" class="postbox" style="padding: 10px;">
	<h3 class="hndle"><?php _e('Supported variables:', 'wp-post-signature'); ?></h3>

	<table class="form-table">
	<tr>
	<th scope="row"><b><?php _e('Variable', 'wp-post-signature'); ?></b></th>
	<td><b><?php _e('Description', 'wp-post-signature'); ?></b></td>
	<td><b><?php _e('Sample Value', 'wp-post-signature'); ?></b></td>
	</tr>

	<tr>
	<th scope="row">%post_title%</th>
	<td><?php _e('The post title.', 'wp-post-signature'); ?></td>
	<td>How to use WP Post Signature?</td>
	</tr>

	<tr>
	<th scope="row">%post_link%</th>
	<td><?php _e('The permalink for a post with a custom post type.', 'wp-post-signature'); ?></td>
	<td>http://www.cbug.org/2011/05/08/57.html</td>
	</tr>

	<tr>
	<th scope="row">%post_author%</th>
	<td><?php _e('The post author.', 'wp-post-signature'); ?></td>
	<td>Soli</td>
	</tr>

	<tr>
	<th scope="row">%post_trackback_url%</th>
	<td><?php _e('The post trackback URL.', 'wp-post-signature'); ?></td>
	<td>http://www.cbug.org/2011/10/24/wp-post-signature-v0-1-3-release.html/trackback</td>
	</tr>

	<tr>
	<th scope="row">%post_date%</th>
	<td><?php _e('The post date.', 'wp-post-signature'); ?></td>
	<td>2011-05-09 16:37:15</td>
	</tr>

	<tr>
	<th scope="row">%post_date_gmt%</th>
	<td><?php _e('The post GMT datetime. (GMT = Greenwich Mean Time)', 'wp-post-signature'); ?></td>
	<td>2011-05-09 08:37:15</td>
	</tr>

	<tr>
	<th scope="row">%post_modified%</th>
	<td><?php _e('Date the post was last modified.', 'wp-post-signature'); ?></td>
	<td>2011-05-09 18:37:15</td>
	</tr>

	<tr>
	<th scope="row">%post_modified_gmt%</th>
	<td><?php _e('GMT date the post was last modified. (GMT = Greenwich Mean Time)', 'wp-post-signature'); ?></td>
	<td>2011-05-09 10:37:15</td>
	</tr>

	<tr>
	<th scope="row">%bloginfo_name%</th>
	<td><?php _e('The "Site Title" set in Settings > General.', 'wp-post-signature'); ?></td>
	<td>Soli's blog</td>
	</tr>

	<tr>
	<th scope="row">%bloginfo_description%</th>
	<td><?php _e('The "Tagline" set in Settings > General.', 'wp-post-signature'); ?></td>
	<td>Just another WordPress blog</td>
	</tr>

	<tr>
	<th scope="row">%bloginfo_siteurl%</th>
	<td><?php _e('The "WordPress address (URI)" set in Settings > General.', 'wp-post-signature'); ?></td>
	<td>http://www.example.com/home/wp</td>
	</tr>

	<tr>
	<th scope="row">%bloginfo_url%</th>
	<td><?php _e('The "Site address (URI)" set in Settings > General.', 'wp-post-signature'); ?></td>
	<td>http://www.example.com/home</td>
	</tr>

	<tr>
	<th scope="row">%bloginfo_admin_email%</th>
	<td><?php _e('The "E-mail address" set in Settings > General.', 'wp-post-signature'); ?></td>
	<td>admin@example.com</td>
	</tr>

	<tr>
	<th scope="row">%bloginfo_pingback_url%</th>
	<td><?php _e('The Pingback XML-RPC file URL (xmlrpc.php).', 'wp-post-signature'); ?></td>
	<td>http://www.example.com/home/wp/xmlrpc.php</td>
	</tr>

	<tr>
	<th scope="row">%bloginfo_atom_url%</th>
	<td><?php _e('The Atom feed URL (/feed/atom).', 'wp-post-signature'); ?></td>
	<td>http://www.example.com/home/feed/atom</td>
	</tr>

	<tr>
	<th scope="row">%bloginfo_rdf_url%</th>
	<td><?php _e('The RDF/RSS 1.0 feed URL (/feed/rfd).', 'wp-post-signature'); ?></td>
	<td>http://www.example.com/home/feed/rdf</td>
	</tr>

	<tr>
	<th scope="row">%bloginfo_rss_url%</th>
	<td><?php _e('The RSS 0.92 feed URL (/feed/rss).', 'wp-post-signature'); ?></td>
	<td>http://www.example.com/home/feed/rss</td>
	</tr>

	<tr>
	<th scope="row">%bloginfo_rss2_url%</th>
	<td><?php _e('The RSS 2.0 feed URL (/feed).', 'wp-post-signature'); ?></td>
	<td>http://www.example.com/home/feed</td>
	</tr>

	<tr>
	<th scope="row">%bloginfo_comments_atom_url%</th>
	<td><?php _e('The comments Atom feed URL (/comments/feed).', 'wp-post-signature'); ?></td>
	<td>http://www.example.com/home/comments/feed/atom</td>
	</tr>

	<tr>
	<th scope="row">%bloginfo_comments_rss2_url%</th>
	<td><?php _e('The comments RSS 2.0 feed URL (/comments/feed).', 'wp-post-signature'); ?></td>
	<td>http://www.example.com/home/comments/feed</td>
	</tr>

	</table>
	</div>
	<!--others end-->

	</div></div></div>
	<!--left end-->

	</div>
	</div>
	<?php
}

function WPPostSignature_Menu() {
	add_options_page(__('WP Post Signature'), __('WP Post Signature'), 'publish_posts', 'wp-post-signature', array($this,'WPPostSignature_Options_Page'));
}

} // end of class WPPostSignaturePage
} // end of if(!class_exists('WPPostSignaturePage'))

