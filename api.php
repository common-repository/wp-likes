<?php


class wp_likes{
//data members
public $method;
public $user_name;
public $user_id;
public $session_hash;
public $post_id;
public $total_count;
//plugin dir, both should be same;
public $plugin_path;


//member functions start	
public function wp_likes($req=null,$user_id=null){
	
$this->method=isset($req["method"])? ($req["method"]=="unlike"? "unlike":"like"):null;	

//user info	
$this->user_id=$user_id;
$this->user_name=null;

//set plugin dir

if(defined('WP_LIKES_URL'))
$this->plugin_path=WP_LIKES_URL;
 else if($this->method==null)$this->plugin_path=get_option('siteurl')."/wp-content/plugins/wp-likes";
	
//post info
$this->total_count=0;
$this->post_id=isset($req["post_id"])? (int)(mysql_escape_string($req["post_id"])):null;

//inits
$this->cookieInit();

}	


public function fetchPeople(){
	global $wpdb;
	$wpdb->likes=$wpdb->prefix."likes";
	
	$people=$wpdb->get_results("SELECT session_hash FROM $wpdb->likes WHERE post_id=$this->post_id",ARRAY_A);
	$this->total_count=count($people);
	return $people;
}

public function fetchCount(){
	global $wpdb;
	$wpdb->likes=$wpdb->prefix."likes";
	
	$count=$wpdb->get_var("SELECT count(*) FROM $wpdb->likes WHERE post_id=$this->post_id");
	$this->total_count=$count;
	return $count;
	
	
	
}
public function cookieInit(){
	
if(!isset($_COOKIE["wp_likes_sh"])){

//cookie not there 
//set null session if method is null
if($this->method==null)	$this->session_hash=null;
else{
$time = time();	
setcookie("wp_likes_sh",md5($this->getIP().$time),$time+31104000,"/");
//print($_SERVER["REMOTE_ADDR"]);
//print (md5($_SERVER["REMOTE_ADDR"]));
$this->session_hash=md5($this->getIP().$time);
}
}
else $this->session_hash=mysql_escape_string($_COOKIE["wp_likes_sh"]);

}

public function like(){
	if($this->method=="like" && $this->post_id>0){
	global $wpdb;
	$wpdb->likes=$wpdb->prefix."likes";
	$sql="INSERT INTO $wpdb->likes (id,post_id,person_id,at_time,session_hash) VALUES(LAST_INSERT_ID(),$this->post_id,$this->user_id,now(),'$this->session_hash')";
	//echo $sql;
	$wpdb->query($sql);
	}
	else {throw new Exception("invalid params- method=$this->method and post_id=$this->post_id");}
	
	}
	
public function unlike(){
	if($this->method=="unlike" && $this->post_id>0){
	global $wpdb;
	$wpdb->likes=$wpdb->prefix."likes";
	$sql="DELETE FROM $wpdb->likes WHERE post_id=$this->post_id AND session_hash='$this->session_hash' LIMIT 1";
	//echo $sql;
	$wpdb->query($sql);
	}
	else {throw new Exception("invalid params");}	
}

public function getText($count, $text) {
	global $wp_likes_replace_count;
	$wp_likes_replace_count = $count; 
	if(!function_exists('wp_likes_preg_cb')) {
		function wp_likes_preg_cb($matches) {
			global $wp_likes_replace_count;
			$new_count = $wp_likes_replace_count;
			if( isset($matches[2]) ){
				$operator = $matches[3];
				$num = $matches[4];
				$new_count = $operator == "+"? $wp_likes_replace_count + $num : $wp_likes_replace_count - $num;		
			}
			return "<b>$new_count</b>";
		}
	}

	$text = preg_replace_callback(
	        '/(%NUM(([+-])([0-9]+))?%)/',
	        "wp_likes_preg_cb",
	        $text);
	return $text;
}

private function getIP(){
	if (isset($_SERVER['HTTP_X_CLUSTER_CLIENT_IP']) === TRUE)
    	return $_SERVER['HTTP_X_CLUSTER_CLIENT_IP'];
	elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR']) === TRUE)
    	return $_SERVER['HTTP_X_FORWARDED_FOR'];
	else return $_SERVER['REMOTE_ADDR'];

}
public static function install(){

global $wpdb;
  
   $table_name = $wpdb->prefix . "likes";
   if($wpdb->get_var("show tables like '$table_name'") != $table_name) {
   
   
   
		$charset_collate = '';
		if ( version_compare(mysql_get_server_info(), '4.1.0', '>=') ) {
			if (!empty($wpdb->charset)) {
				$charset_collate .= " DEFAULT CHARACTER SET $wpdb->charset";
			}
			if (!empty($wpdb->collate)) {
			$charset_collate .= " COLLATE $wpdb->collate";
			}
		}

      
      $sql = "CREATE TABLE " . $table_name . " (
	   `id` int(10) unsigned NOT NULL auto_increment,
       `post_id` int(10) unsigned NOT NULL,
  	   `person_id` int(10) default NULL,
       `at_time` datetime default NULL,
       `session_hash` char(32) NOT NULL,
        PRIMARY KEY  (`id`),
        UNIQUE KEY `key_post_id-sh_pair` (`post_id`,`session_hash`),
        KEY `key_post_id` (`post_id`)
	 )ENGINE=InnoDB AUTO_INCREMENT=1 $charset_collate ;";

      require_once(ABSPATH."/wp-admin/includes/upgrade.php");
      dbDelta($sql);

}
//restore css
wp_likes_settings::restore(true,false,false);
}
	

}
class wp_likes_settings{
	private $css;
	private $options;
	public $text;
	public $text_default;
	public $css_default;
	public function __construct($loadCss=true,$loadOptions=true,$loadText=true){
		$this->options=array();
		$this->options["showOnPages"]='false';
		$this->options["showOnMainPage"]='true';
		$this->options["likeImageUrl"]=WP_LIKES_URL."/images/like.png";
		$this->options["catFilterStatus"]="no-use";
		$this->text_default=array();
		$this->text_default[0]="You and %NUM% people like this post.";
		$this->text_default[1]="You and 1 person like this post.";
		$this->text_default[2]="You like this post.";
		$this->text_default[3]="%NUM% people like this post.";
		$this->text_default[4]="1 person likes this post.";
		$this->text_default[5]="Like";
		$this->text_default[6]="Unlike";
		$this->text_default[7]="Be the first to like.";
		$this->text=$this->text_default;
		$this->options["WPSuperCache"]="false";
		$this->options["customRender"]="false";
		$WP_LIKES_URL=WP_LIKES_URL;
		$this->css=$this->css_default = <<<EOD
.wp_likes {
	margin-top:30px;
}
.wp_likes span.text{ 
   margin-left: 5px;
}
.wp_likes a.like img,a.liked img{
	vertical-align:middle;
    opacity:.8;
	filter: alpha(opacity = 80);
    margin-right:3px;
}
.wp_likes a.like img:hover{
        opacity:1;
		filter: alpha(opacity = 100);
}
.wp_likes a.like:hover{
text-decoration:none;
}
.wp_likes div.unlike{
	display:none;
}
/*sidebar widget css*/
.wp_likes_widget div img{
border:0px;
margin-left:15px;
margin-right:3px;
width:10px;
vertical-align:middle;	
}
.wp_likes_widget div span{
font-weight:bold;	
}
EOD;
if($loadCss)$this->loadCss();
if($loadOptions)$this->loadOptions();
if($loadText)$this->loadText();
	}
public function __get($var){
		if($var=="css"){
		return $this->css;		
		}else{
			 return $this->options[$var];
		  }
	}
public function __set($var,$value){
	if($var=="css"){
		$this->css=$value;
	}
	else {
		
		$this->options[$var]=$value;
		
	}
		
	}	  	
public function loadCss(){
		$var_db=get_option("wp_likes_css");
		if($var_db)$this->css=$var_db; else 
		  {
		  add_option("wp_likes_css",$this->css);
		  }	
}
public function loadOptions(){
	$options=get_option("wp_likes_options");
	if($options){
		foreach($options as $key=>$value){
		$this->options[$key]=$value;	
		}
		}
	else add_option("wp_likes_options",$this->options);
}		

public function loadText(){
	$texts=get_option("wp_likes_text");
	if($texts)$this->text = $texts + $this->text_default;
	else add_option("wp_likes_text",$this->text);
	
}
public function save(){
	update_option("wp_likes_css",$this->css);
	update_option("wp_likes_options",$this->options);
	update_option("wp_likes_text",$this->text);	
}		
public static function restore($css=true,$options=true,$texts=true){
	$settings= new wp_likes_settings(!$css,!$options,!$texts);
	$settings->save();
	return $settings;
}		

}
?>