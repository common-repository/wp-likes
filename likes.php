<?php
/*
Plugin Name: WP likes
Plugin URI: http://blog.aakash.org/2011/12/few-updates-wp-likes/ 
Description: Allows visitors to "like" your posts on the fly if they find it interesting. Now enable/disable on specific categories, head over to <a href='options-general.php?page=wp_likes_settings'>settings</a>. Also available- shortcodes to show top liked posts.
Author: Aakash Bapna
Version: 3.1.1
Author URI: http://www.aakash.org

Copyright 2011 Aakash Bapna  (email : aakash@live.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/
//error_reporting(E_ALL);
//ini_set("display_errors","On");
if (!defined('LIKES_LOADED')) :
  define('LIKES_LOADED',true);
  define("WP_LIKES_URL",get_option('siteurl')."/wp-content/plugins/wp-likes");

require_once("api.php");

#add actions

//add css
add_action("wp_head","wp_likes_render_CSS");
//add js
add_action("wp_head","wp_likes_render_JS");
//add likes to post
if(!strstr($_SERVER['PHP_SELF'],"wp-admin")){ //temp fix for wp 2.8.4 with likes rendering while creating new posts
add_filter("the_content","wp_likes_render_post",1);
}
//install wp-likes
register_activation_hook(__FILE__,"wp_likes_activate");
//uninstall wp-likes
register_deactivation_hook(__FILE__,"wp_likes_deactivate");
// Hook for adding admin menus
add_action('admin_menu', 'wp_likes_add_pages');
// Hook for dashboard widget
add_action('wp_dashboard_setup','wp_likes_dashboard');
// Hook for sidebar widget
add_action('widgets_init', 'wp_likes_sidebar');
// filter for settings link in plugins area
add_filter( 'plugin_action_links', 'wp_likes_plugin_actions', 10, 2 );
#actions-end

function wp_likes_plugin_actions( $links, $file ) {
 	if( $file == 'wp-likes/likes.php' && function_exists( "admin_url" ) ) {
		$settings_link = '<a href="' . admin_url( 'options-general.php?page=wp_likes_settings' ) . '">' . __('Settings') . '</a>';
		array_unshift( $links, $settings_link ); // place before other links
	}
	return $links;
}
function wp_likes_activate(){
wp_likes::install();
if(isset($GLOBALS["super_cache_enabled"])){//ys wp cache is there,clear it so that wp likes appears!
$GLOBALS["super_cache_enabled"]=1;
 wp_cache_clear_cache();
}	
}
function wp_likes_deactivate(){
	if(isset($GLOBALS["super_cache_enabled"])){//ys wp cache is there,clear it to remove wp likes from all pages!
$GLOBALS["super_cache_enabled"]=1;
 wp_cache_clear_cache();
}
}
function wp_likes_render_CSS(){
	return wp_likes_render::CSS();
}
function wp_likes_render_JS(){
	return wp_likes_render::JS();
}
function wp_likes_render_post($content=null){
	return wp_likes_render::post($content);
}
function wp_likes_render_settings(){

return wp_likes_render::settings();
}
function wp_likes_render_dashboard(){

return wp_likes_render::dashboard();
}
function wp_likes_render_sidebar($args){
return wp_likes_render::sidebar($args);
}
function wp_likes_add_pages() {
      add_options_page('WP Likes: settings', 'WP Likes', 8, 'wp_likes_settings', 'wp_likes_render_settings');
  }
  
function wp_likes_dashboard(){
if(function_exists("wp_add_dashboard_widget"))
	wp_add_dashboard_widget('wp_likes_stats', 'Top Liked Posts', 'wp_likes_render_dashboard');		
}

function wp_likes_sidebar(){
if (!function_exists('wp_register_sidebar_widget'))
		return;	
	$widget_ops = array('classname' => 'wp_likes_sidebar', 'description' => "Show top liked and commented posts from your blog in sidebar." );
	wp_register_sidebar_widget('WP-Likes', 'WP Likes', 'wp_likes_render_sidebar', $widget_ops);
    
}

function wp_likes_shortcode($atts,$content,$code){
	return wp_likes_render::shortcode($atts);
}
add_shortcode("wp_likes","wp_likes_shortcode");


//class for rendering in various areas
class wp_likes_render{
public static function settings(){
echo "<h2>Settings</h2>";
echo "<div class='wrap'>";
$settings=new wp_likes_settings();
if(isset($_POST["wp_likes_post"])){
//check if reset is there
if(isset($_POST['wp_likes_reset'])){$settings=wp_likes_settings::restore();
echo "<div class='updated fade' style='background-color: rgb(255, 251, 204);'><p>Settings restored</p></div>";
}
else{
//postback save the options;
$settings->css=strip_tags(stripslashes($_POST["wp_likes_css"]));

if(isset($_POST["wp_likes_showOnPages"]))$settings->showOnPages='true'; else $settings->showOnPages='false';
if(isset($_POST["wp_likes_showOnMainPage"]))$settings->showOnMainPage='true'; else $settings->showOnMainPage='false';
if(isset($_POST["wp_likes_WPSuperCache"]))$settings->WPSuperCache='true'; else $settings->WPSuperCache='false';
if(isset($_POST["wp_likes_customRender"]))$settings->customRender='true'; else $settings->customRender='false';

$settings->catFilterStatus = strip_tags(stripslashes($_POST["wp_likes_catFilterStatus"]));
if(isset($_POST["wp_likes_catFilterList"]))
	$settings->catFilterList = strip_tags(stripslashes($_POST["wp_likes_catFilterList"]));


//get all the texts
foreach($_POST as $key=>$value){
	if(strstr($key,"wp_likes_text-")){
		$exploded_array=explode("-",$key,2);
		$settings->text[$exploded_array[1]]=strip_tags(stripslashes($value));
	}
}
$settings->likeImageUrl=strip_tags($_POST["wp_likes_likeImageUrl"]);
$settings->save();
echo "<div class='updated fade' style='background-color: rgb(255, 251, 204);'><p>Settings saved</p></div>";
}
}
//now display the settings	
	?>
<form method="post">
<?php ?>
<input type="hidden" name="wp_likes_post" value="true"/>
<table class="form-table">

<tr valign="top">
<th scope="row">CSS</th>
<td><textarea name="wp_likes_css" style='width:350px; height:300px;float:left;'><?php echo $settings->css;?></textarea>
<div style="float:left;">
<a href='javascript:void();' id="showDefaultCss">show default</a>
<pre style="display:none;padding:4px;width:350px;background-color:#eee;"><?php echo strip_tags(stripslashes($settings->css_default)) ?></pre>
</div>
<script type="text/javascript">
	jQuery("#showDefaultCss").toggle(function(){
		jQuery(this).text("hide default").next().show();
		
	},function(){
		jQuery(this).text("show default").next().hide();
	})
	jQuery(function() {
	    jQuery("[name=wp_likes_catFilterStatus]").click(function(){
	        if(this.value == "no-use") {
				jQuery("#wp_likes_catFilterList").attr("disabled","disabled").css("background","#f2f2f2");	
			} else {
			   jQuery("#wp_likes_catFilterList").removeAttr("disabled").css("background","#fff").focus();
			}
	    });
	 });
</script>
</td>

</tr>
 <tr valign="top">
<th scope="row">Like button image URL</th>
<td><input type="text" name="wp_likes_likeImageUrl" style='width:300px;' value="<?php echo $settings->likeImageUrl;?>" />
<img src='<?php echo $settings->likeImageUrl;?>' style='vertical-align:middle;' alt='like image'/>
</td>
</tr>
 <tr valign="middle">
<th scope="row">Visible on posts on pages</th>
<td><input type="checkbox" name="wp_likes_showOnPages" value="true" <?php if($settings->showOnPages=='true')echo 'checked=checked';?> /></td>
</tr>
<tr valign="middle">
<th scope="row">Is WP Super Cache plugin activated?</th>
<td><input type="checkbox" name="wp_likes_WPSuperCache" value="true" <?php if($settings->WPSuperCache=='true')echo 'checked=checked';?> /></td>
</tr>
 <tr valign="middle">
<th scope="row">Visible on posts on front page</th>
<td><input type="checkbox" name="wp_likes_showOnMainPage" value="true" <?php if($settings->showOnMainPage=='true')echo 'checked=checked';?> /></td>
</tr>
<tr valign="middle">
<th scope="row">Are you using a function call in theme template to render WP likes?</th>
<td><input type="checkbox" name="wp_likes_customRender" value="true" <?php if($settings->customRender=='true')echo 'checked=checked';?> /> &nbsp;
&nbsp;&nbsp;code: <code>&lt;?php if(function_exists('wp_likes_render_post')) wp_likes_render_post();?&gt;</code></td>
</tr>

<tr valign="middle">
<th scope="row">Category filter</th>
<td>
	<input type="radio" name="wp_likes_catFilterStatus" id="wp_likes_rd1" value="no-use" <?php if($settings->catFilterStatus=='no-use')echo 'checked=checked';?> /><label for="wp_likes_rd1">Don't use</label> &nbsp;
	<input type="radio" name="wp_likes_catFilterStatus" id="wp_likes_rd2" value="show-on" <?php if($settings->catFilterStatus=='show-on')echo 'checked=checked';?> /><label for="wp_likes_rd2">Show only on selected</label> &nbsp;
	<input type="radio" name="wp_likes_catFilterStatus" id="wp_likes_rd3" value="hide-on" <?php if($settings->catFilterStatus=='hide-on')echo 'checked=checked';?> /><label for="wp_likes_rd3">Hide on selected</label>
	<br/>
	Category name list(comma separted): <input type="text" id="wp_likes_catFilterList" name="wp_likes_catFilterList" value="<?php echo $settings->catFilterList; ?>" <?php if($settings->catFilterStatus=='no-use')echo 'disabled style="background:#f2f2f2;"';?> />
</td>
</tr>

<tr valign="top">
<th scope="row">WP Likes Texts</th>
<td><?php foreach($settings->text as $key=>$value)
{?>
	<div>
	<input style='border:dashed 1px #999;width:350px;' type="text" name="wp_likes_text-<?php echo $key;?>" value="<?php echo $value; ?>" />
	<span style="font-size:smaller;">[default: <?php echo $settings->text_default[$key];?> ]</span>
	</div>
<?php }
?>	
</td>
</tr>
</table>

<input type="hidden" name="action" value="wp_likes_post" />
<p class="submit" style='text-align:center'>
<input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
&nbsp;&nbsp;<input type="submit" name="wp_likes_reset" value="<?php _e('reset') ?>" />
</p>
</form>
</div>	
<?php	
}
public static function dashboard(){
	global $wpdb;
	$wpdb->likes=$wpdb->prefix."likes";
	$wpdb->post=$wpdb->prefix."posts";
	$sql="SELECT post_id,p.post_title AS title,p.guid AS url,count(post_id) AS num FROM $wpdb->likes,$wpdb->posts p WHERE p.id=post_id GROUP BY post_id HAVING num>1 ORDER BY num DESC LIMIT 10";
	$results=$wpdb->get_results($sql);
	echo "<table style='text-align:center;margin:auto;' >";
	foreach($results as $row)
	{ echo "<tr>";
			echo "<td style='border:1px solid #ccc;padding:3px;'><a href='$row->url' target='_blank'>$row->title</a></td>
				   <td style='border:1px solid #ccc;padding:3px;'>$row->num</td>";
	  echo "</tr>";		
	
	}
	echo "</table>";
	
} 
public static function post($content=null){

	global $post;
	$postId=$post->ID;
	$settings= new wp_likes_settings(false,true);
	if($settings->showOnPages=='false' && $post->post_type!='post')return $content;
	if($settings->showOnMainPage=='false' && is_front_page())return $content;
	if($settings->customRender=='true' && $content!=null)return $content;
	$cat_list = get_the_category($postId);
	if(!self::checkCategory($settings->catFilterStatus,$settings->catFilterList,$cat_list))
		return $content;
	$likes= new wp_likes;
	$likes->cookieInit();
	
	$likes->post_id=$postId;$hasPersonLiked=false;
	if($likes->session_hash!=null){
	$people=$likes->fetchPeople();
	if(count($people)!=0){
	foreach($people as $person){
		if($person["session_hash"]==$likes->session_hash){$hasPersonLiked=true; break;}
	}}
	}
	else{
		//no session hash, hence directly fetch count
		$likes->fetchCount();
			}
	$returnStr="";	
	if(is_feed()){//this is a call from RSS feed
		//$returnStr.= "</p><div><b>$likes->total_count</b> $settings->likeActors $settings->likeText.</div>";
		return $content;
	}		
	if($content!=null)$returnStr.="</p>"; else $content="";
	$returnStr.="<div class='wp_likes' id='wp_likes_post-$postId'>";

if(!$hasPersonLiked){
		$returnStr.="<a class='like' href=\"javascript:wp_likes.like($postId);\" title='".($likes->total_count?$settings->text[5]:"")."' >";
		$returnStr.="<img src=\"".$settings->likeImageUrl."\" alt='' border='0'/>";
			//$this->text_default[5]="Like";
		$returnStr.= "</a>";
		$returnStr.= "<span class='text'>";
		if($likes->total_count==0) {
			//$this->text_default[7]="Be the first to like.";
			$returnStr.= $likes->getText($likes->total_count, $settings->text[7]); ;
		} elseif($likes->total_count>1){
			//$this->text_default[3]="%NUM% people like this post.";
			$returnStr.=$likes->getText($likes->total_count, $settings->text[3]);	 
		} elseif($likes->total_count==1) {
		
			//$this->text_default[4]="1 person likes this post.";
			$returnStr=$returnStr.$likes->getText($likes->total_count, $settings->text[4]);
		}
			//$this->text_default[6]="Unlike";
		$returnStr.="</span>";
	} else {
		$returnStr.="<a class='liked'><img src=\"".$settings->likeImageUrl."\" alt='' border='0'/></a><span class='text'>";	
		if($likes->total_count==1)
			//$this->text_default[2]="You like this post.";
			$returnStr.= $likes->getText($likes->total_count, $settings->text[2]);
		elseif($likes->total_count==2)
			//$this->text_default[1]="You and 1 person like this post.";	
			$returnStr.=$likes->getText(1, $settings->text[1]);
		else {
			$likes->total_count--;
			//$this->text_default[0]="You and %NUM% people like this post.";
			$returnStr.=$likes->getText($likes->total_count, $settings->text[0]);
		}

		$returnStr.="</span>";
	}	
$returnStr.= "<div class='like' ".($hasPersonLiked?'style="display:none"':"")."><a href=\"javascript:wp_likes.like($postId);\">".$settings->text[5]."</a></div>"
."<div class='unlike' ".($hasPersonLiked?'style="display:block"':"")."><a href=\"javascript:wp_likes.unlike($postId);\">".$settings->text[6]."</a></div>";
$returnStr.="</div>";
	if($settings->customRender=='true') echo $returnStr; 
	else return $content.$returnStr;
}	
public static function JS(){
	$settings= new wp_likes_settings(false,true);
	
?>
<script type="text/javascript">
<!--start wp_likes
/* 
 *author:Aakash Bapna(http://www.aakash.org)
 */

if(typeof jQuery=="undefined"){
	
	var ele=document.createElement("script");
	ele.type="text/javascript";
	ele.src="http://ajax.googleapis.com/ajax/libs/jquery/1.3.2/jquery.min.js";
	document.getElementsByTagName("head")[0].appendChild(ele);
	var interval = setInterval(function() {
      if (typeof jQuery != 'undefined') {
        clearInterval(interval);
        jQuery.noConflict();//prototype and other lib compatability fix.
      }
    }, 50);
	
	
	}
	
	var wp_likes={};
	wp_likes.endpoint="<?php echo WP_LIKES_URL;?>/endpoint.php";
	wp_likes.method="like";
	wp_likes.didPrevFinish=true;
	wp_likes.makeCall=function(post_id,callback,isUnlike){
		if(!this.didPrevFinish)return false;
		if(isUnlike==true)this.method="unlike"; else this.method="like";
		params={};
		params["method"]=this.method;
		params["post_id"]=post_id;
		jQuery.ajax({
			type:"GET",
			url:this.endpoint,
			data:params,
			beforeSend:function(){
			this.didPrevFinish=false;	
			},
			success:function(response){
				if(response.success==true){
					callback(response);
									
				}
				else {
					//error in call
					wp_likes.log("unsuccessfull request, response from server:"+ response)
					
				}
				
			},
			error:function (xhr, ajaxOptions, thrownError){
                wp_likes.log('error in AJAX request.');
				wp_likes.log('xhrObj:'+xhr);
				wp_likes.log('thrownError:'+thrownError);
				wp_likes.log('ajaxOptions:'+ajaxOptions);
                                                },
			complete:function(){
					this.didPrevFinish=true;
			},
			dataType:"json",
			cache:false
			
		})
		
	}
	wp_likes.like=function(post_id){
		wp_likes.log("like click for post- "+post_id);
		jQuery("#wp_likes_post-"+post_id+" a.like").fadeTo(500,.2);
		this.makeCall(post_id,function(response){
			var postDom=jQuery(document.getElementById("wp_likes_post-"+post_id));
			postDom.children("span.text").html(response.likeText);
			var thumbImg=postDom.children("a.like").children("img");
			postDom.children("a.like").attr('title',"").removeAttr('href').text("").addClass("liked").removeClass("like");
			thumbImg.appendTo(postDom.children("a.liked").eq(0));
			postDom.children("a.liked").fadeTo("500",.80);
			postDom.find(".like").hide("fast");
			postDom.children("div.unlike").show("fast")	
	
		},false);
		
	}
    wp_likes.unlike=function(post_id){
	wp_likes.log("unlike click for post- "+post_id);
	jQuery("#wp_likes_post-"+post_id+" a.liked").fadeTo(500,.2);
	this.makeCall(post_id,function(response){
		
		var postDom=jQuery(document.getElementById("wp_likes_post-"+post_id));
		postDom.children("span.text").html(response.likeText);
		postDom.children("a.liked").attr("href","javascript:wp_likes.like("+post_id+")").addClass("like").removeClass("liked").fadeTo("500",1);
		postDom.children("div.unlike").hide("fast")
		postDom.find(".like").show("fast");	
	},true)
	
	
}
	wp_likes.log=function(obj){
		if(typeof console !="undefined")console.log(obj);
	}
// -->
</script>
	
	
<?php	
}
public static function CSS(){
	$settings=new wp_likes_settings(true,false,false);
	
?>

<style type="text/css">
/*
 * WP Likes CSS
 */	
<?php echo $settings->css;?>
	
</style>
<?php	
	
	
}
public static function checkCategory($current_setting, $filter_category_list,$post_categories) {
	if($current_setting == "no-use")
		return true;
	$filter_cat_list = explode(",",strtolower($filter_category_list));
	array_walk($filter_cat_list, create_function('&$val', '$val = trim($val);')); 
	
	$cat_list = array();
	foreach($post_categories as $category) {
		$cat_list[] = strtolower($category->cat_name);
	}
	$result_arr = array_intersect($filter_cat_list,$cat_list);
	if($current_setting == "show-on") {
		//show on filter_cat_list
		if(!empty($result_arr)){
			return true;
		} else {
			return false;
		}		
	} else {
		//hide on filter_cat_list
		if(!empty($result_arr)){
			return false;
		} else {
			return true;
		}
	}
}

public static function sidebar($args=null){
	extract($args);
echo $before_widget;
echo $before_title;
	echo "Top Liked Posts";
echo $after_title;
	
	echo "<div class='wp_likes_widget'><ul>";
	global $wpdb;
	$wpdb->likes=$wpdb->prefix."likes";
	$wpdb->post=$wpdb->prefix."posts";
	$sql="SELECT post_id,p.post_title AS title,count(post_id) AS num FROM $wpdb->likes,$wpdb->posts p WHERE p.id=post_id GROUP BY post_id HAVING num>1 ORDER BY num DESC LIMIT 5";
	$results=$wpdb->get_results($sql);
	foreach($results as $row){
		$link=get_permalink($row->post_id);
		$comment_count=wp_count_comments($row->post_id)->approved;
		echo "<li><a href='$link'>$row->title</a><div>"
			."<img src='".WP_LIKES_URL."/images/like.gif' alt='' title='liked by $row->num'/><span>$row->num</span>";
		if($comment_count>0)echo "&nbsp;&nbsp;"
			."<img src='".WP_LIKES_URL."/images/comments.png' alt='' title='commented by $comment_count'/><span>$comment_count</span>";
		echo "</div></li>";
	}
	echo "</ul></div>";
	echo "Powered by <a href='http://wordpress.org/extend/plugins/wp-likes/' target='_blank'>WP Likes</a>";
echo $after_widget;
}

public static function shortcode($args){
	global $wpdb;
	$date_filter="";
	$limit="LIMIT 5";
	$method="";
	$wpdb->likes=$wpdb->prefix."likes";
	$wpdb->posts=$wpdb->prefix."posts";
	if(isset($args["count"]))
	{
		if($args["count"]=="all")
			$limit="";
		else
			$limit="LIMIT {$args["count"]}";
	}
	if(isset($args["show"]))
	{
		 $method=$args["show"];
	switch($args["show"]){
	case "monthly": $date_range = mktime(0, 0, 0, date("m")-3, date("d"),   date("Y"));
				   $date_filter="and p.post_date>'".date( 'Y-m-d H:i:s', $date_range)."'";
					break;
	case "weekly":  $date_range= mktime(0, 0, 0, date("m"), date("d")-7,   date("Y"));
				    $date_filter="and p.post_date>'".date( 'Y-m-d H:i:s', $date_range)."'";
					break;
	}
		
		
	}
	
	$sql="SELECT p.*,count(post_id)as num FROM $wpdb->likes,$wpdb->posts p WHERE p.id=post_id $date_filter GROUP BY post_id HAVING num>0 ORDER BY num DESC, p.post_date DESC $limit";
	$posts=$wpdb->get_results($sql);
	if(count($posts)<=0)
		return "No posts to display yet.";
	ob_start();
	echo '<div class="wp_likes_posts">';
	foreach($posts as $post){
		$GLOBALS["post"]=$post;
		setup_postdata($post);
		?>
		<div <?php post_class() ?> id="post-<?php the_ID(); ?>">
		        <h2><a href="<?php the_permalink() ?>" rel="bookmark" title="Permanent Link to <?php the_title_attribute(); ?>"><?php the_title(); ?></a></h2>
		        <small><?php the_time('F jS, Y') ?> <!-- by <?php the_author() ?> --></small>
		        <div class="entry">
		          <?php the_content('Read the rest of this entry »'); ?>
		        </div>
		        <p class="postmetadata"><?php the_tags('Tags: ', ', ', '<br />'); ?> Posted in <?php the_category(', ') ?> | <?php edit_post_link('Edit', '', ' | '); ?>  <?php comments_popup_link('No Comments »', '1 Comment »', '% Comments »'); ?></p>
		</div>
	<?php	
	}
	echo "</div>";
	$content = ob_get_contents();
	ob_end_clean();
	return $content;
	
}

}


endif;

?>
