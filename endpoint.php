<?php 

/*
 * endpoint for ajax requests
 */
header('Content-type: text/json');
if(!isset($_REQUEST["method"]) || !isset($_REQUEST["post_id"]))exit();

require_once("api.php");
require_once('../../../wp-config.php');
require_once('../../../wp-includes/wp-db.php');
require_once('../../../wp-includes/pluggable.php');

$user_id=wp_get_current_user()->ID;

$responseObj=array();
$responseObj["uid"]=$user_id;
$responseObj["success"]=true;

try{
$likes=new wp_likes($_REQUEST,$user_id);
$settings= new wp_likes_settings(false,true);
$likeText="";
switch($likes->method)
{
	case "like":$likes->like();
				$likes->fetchCount();
				if($likes->total_count==1)
					//$this->text_default[2]="You like this post.";
				$likeText= $likes->getText($likes->total_count, $settings->text[2]);
				elseif($likes->total_count==2)
					//$this->text_default[1]="You and 1 person like this post.";	
					$likeText=$likes->getText(1, $settings->text[1]);
				else 
					{ $likes->total_count--;
					//$this->text_default[0]="You and %NUM% people like this post.";
					$likeText=$likes->getText($likes->total_count, $settings->text[0]);}

	break;
	case "unlike":$likes->unlike();
				  $likes->fetchCount();
				  if($likes->total_count>1){
					//$this->text_default[3]="%NUM% people like this post.";
					$likeText=$likes->getText($likes->total_count, $settings->text[3]);
				  } elseif($likes->total_count==1) {
						//$this->text_default[4]="1 person likes this post.";
						$likeText=$likes->getText($likes->total_count, $settings->text[4]);
				  } else if($like->total_count==0) {
						$likeText = $likes->getText($likes->total_count, $settings->text[7]);
				}	
	break;
	default: throw new Exception("invalid method");
	
	
}
if($settings->WPSuperCache=="true"){//update the WP super cache
if(isset($GLOBALS["super_cache_enabled"])){//ys wp cache is there!
$GLOBALS["super_cache_enabled"]=1;
$responseObj["cacheCleared"]=true;
wp_cache_post_change((int)$_REQUEST["post_id"]);
}
}
$responseObj["likeText"]=$likeText;
}
catch(Exception $e){
	
$responseObj["success"]=false;
$responseObj["error_txt"]=$e->getMessage();	

}

echo json_encode($responseObj);

?>