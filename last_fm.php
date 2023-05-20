<?php
	//assert_options(ASSERT_ACTIVE, 1);
	//assert_options(ASSERT_BAIL, 1);
	//assert_options(ASSERT_QUIET_EVAL, 1);
	//error_reporting(E_ALL);
	//ini_set('display_errors', 1);
	//I used these pretty heavily in testing - if firefox tells you "this image contains errors" you may want to comment 
	//out the header content type png line below & uncomment the lines above to see what's actually going on. php is
	//notoriously difficult to debug.
	
	$last_fm_user_id='Micolithe'; //replace with your User ID. Or don't, if you care about what I'm listening to but that would be strange.
	$social_media_str='cohost.org/micolithe'; //replace with your cohost, twitter, whatever. you might even have room for two depending on how long your username is.
	$last_fm_api_key='generate and insert your API key here';
	//You can get a last fm API key by going to https://www.last.fm/api/account/create
	
	
	//I fucking hate PHP so much oh my god
    $url='https://ws.audioscrobbler.com/2.0/?method=user.getrecenttracks&user='.$last_fm_user_id.'&api_key='.$last_fm_api_key.'&limit=1';
	$x=simplexml_load_file($url); //the way last fm returned the JSON didnt play nicely with PHP. XML was a little better, weirdly enough.
	if ($x===false){
		echo("There has been porblem");
	}
	$artist=$x->recenttracks->track[0]->artist;
	$album=$x->recenttracks->track[0]->album;
	$track=$x->recenttracks->track[0]->name;
	$art=$x->recenttracks->track[0]->image[1]; 
	//Album art returns four sizes for album art. 0 - small 34x34, 1 = medium 64x64, 2 = large 174x174,  3 = extralarge 300x300.
	//I should probably search for the tag with the attribute size=medium to make this safer but it seems like it returns them in a consistent order
	//This is a problem for future micolithe
	$unix_ts=0;
	$formatted_date="???";
	//currently playing tracks do not have a date key in the API.
	$lastfm_art_format=substr($art,-4);
	if (array_key_exists('date',get_object_vars($x->recenttracks->track[0]))){
		$unix_ts=$x->recenttracks->track[0]->date->attributes()->uts;
		$formatted_date=date('Y-m-d g:i:s a',intval($unix_ts));  
		//if there is a date parse it
	}else{
		$formatted_date="Playing right now!"; 
		//if there isn't just say now
	}
	if ($lastfm_art_format=='jpeg' || $lastfm_art_format=='.jpg'){ 
	//images can be png or jpg, which have different image creation functions
		$albumimage=imagecreatefromjpeg($art);
	}
	elseif($lastfm_art_format=='.png'){
		$albumimage=imagecreatefrompng($art);
	}
	else{
		$albumimage=imagecreatefrompng('missing_album_art.png');  
		//i don't think this would ever happen since last fm seems to return placeholder art, but just in case
	}
	header("Content-type: image/png");  //force this php file to behave like an image :)
	$img=imagecreatefrompng('last_fm_bg.png');
	$blacktext=imagecolorallocate($img,0,0,0);
	imagealphablending($img,true);
	//$albumimage=imagecreatefromjpeg($art);
	imagesavealpha($img,true);
	imagecopymerge($img,$albumimage,16,55,0,0,64,64,100);
	imagettftext($img,10,0,90,50,$blacktext,'./arial.ttf',wordwrap($artist,50));  //ok the order of imagettftext's args is a little confusing
	imagettftext($img,10,0,90,80,$blacktext,'./arial.ttf',wordwrap($album,50));  //the order is:
	imagettftext($img,10,0,90,110,$blacktext,'./arial.ttf',wordwrap($track,50)); //image, font size, rotation angle, x position (from left), y position (from top), text color, true type font, string
	//i don't think the wordwrapping works. this is a problem i have yet to solve.
	//also i can't distrubute arial legally, since it's microsoft's, so find arial.ttf on your windows computer and put it in the same directory as this stuff.
	imagettftext($img,8,0,90,140,$blacktext,'./arial.ttf',$formatted_date);
	imagettftext($img,6,0,10,160,$blacktext,'./arial.ttf',$social_media_str."  |  last.fm/".$last_fm_user_id);
	imagepng($img);  //slambo
	imagedestroy($img); //wangjangle
?>