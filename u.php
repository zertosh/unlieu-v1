<?php
require('common.php');
isset_set($_POST['a'],$_a) or die('{"error":"missing a"}');

/*
** only for new unlieu
*/
if($_a=='n') {
	if($fails=apc_fetch($_SERVER['REMOTE_ADDR'])) {	if($fails>20) {	include('banned.php'); die(); } }
	$db=Database::getInstance();
	do {
		for ($newId='', $i=0; $i<7; $i++) {	$newId.=chr(mt_rand(97,122)); }
		if(is_null($db->db()->meta->findOne(array('_id'=>$newId)))) {
			$time=time_milliseconds();
			$newMeta=array(
				'_id'=>$newId,
				't'=>'untitled',	/* title */
				'm'=>$time,			/* creation time */
				'b'=>0,				/* total posts */
				'c'=>0,				/* total comments */
				's'=>0,				/* total attachment size (in KB) */
				'q'=>0,				/* quota used (in KB) */
				'u'=>$time,			/* last update time */
				'v'=>$time,			/* last activity time */
				'p'=>array(),
				'x'=>array(),
			);
			$db->db()->meta->insert($newMeta);
			apc_store($newId,$newMeta);
			break;
		} 
	} while (true);
	header("Location: http://unlieu.com/$newId");
	exit();
}

/*  
** basic sanity check (w/o db)
*/
u_match('id',$_POST['id'],$_id) or die('{"error":"id"}');
switch($_a) {
case 'c':
	u_match('parent',$_POST['parent'],$_parent) or die('{"error":"parent"}');
case 'p':
	u_match('partic',$_POST['partic'],$_partic) or die('{"error":"partic"}');
	u_match('style',$_POST['style'],$_style) or die('{"error":"style"}');
	switch($_style) {
		case 't':
			isset($_POST['content']) or die('{"error":"content"}');
			$_content=trim($_POST['content']);
			$_content=strlen($_content)>0 && strlen($_content)<=140 ? $_content : die('{"error":"size"}');
		break;
	
		case 'f':
			isset($_FILES['file']) or die();
			$_file=$_FILES['file']['size']<=20971520 ? $_FILES['file'] : die('{"error":"size"}');
		break;
		
		default:
		die();
	}
break;

case 's':
	u_match('phone',$_POST['phone'],$_phone) or die('{"error":"phone"}');
	$_areacode=(int) substr($_phone,0,3);
	in_array($_areacode, $validareacodes) or die('{"error":"areacode"}');
	$_phone=$_phone;
case 'j': 
case 'a':
	u_match('partic',$_POST['partic'],$_partic) or die('{"error":"partic"}');
	$_partic=strlen($_partic)<=15 ? $_partic : die('{"error":"size"}');
	session_start();
break;

case 't':
	u_match('t',$_POST['t'],$_t) or die('{"error":"t"}');
	$_t=trim($_t);
break;

default:
	die('{"error":"a"}');
break;
}


$db=Database::getInstance();
$meta=$db->getMeta($_id) or die('{"error":"db"}');

/*  
** advanced sanity check (w/ db)
*/
switch($_a) {
case 'c':
	$_parent=new MongoId($_parent);
	$_postBody=$db->db()->post->findOne(array('_id'=>$_parent))?: die('{"error":"no parent match"}');
case 'p':
	switch($_style) {
		case 'f':
			$meta['s']<=(100*1024) ?: die('{"error":"out of space"}');
			($meta['q']+($_file['size']/1024))<(500*1024)  ?: die('{"error":"quota hit"}');
		break;
	}
case 'j':
	in_array($_partic,$meta['p']) ?: die('{"error":"partic not in unlieu"}');	
break;

case 's':
	sizeof($meta['x'])<21 ?: die('{"error":"too many registered"}');
	in_array($_phone,$meta['x']) ? die('{"error":"already registered"}') : false;
	$_smsDb=$db->db()->sms->findOne(array('_id'=>$_phone));
	if(sizeof($_smsDb['f'])==10) {
		require('smsified.class.php');
		sendSMS(
			$_smsDb['f'][0]['o'],
			$_phone,
			'{you can only follow 10 unlieu\'s, so @leave whichever and then try adding this one again}'
		);
		exit();
	}
	$_AO=array('3476300770', '3476300771', '3476300772', '3476300773', '3476300774', 
		'3476300775', '3476300776', '3476300777', '3476300778', '3476300779');
	for($i=0, $l=sizeof($_smsDb['f']); $i<$l; $i++) {
		unset($_AO[array_search($_smsDb['f'][$i]['o'],$_AO)]);
	}
	$_AO=array_values($_AO);
break;
}

/*  
** s3 file handling
*/
if(isset($_style) && $_style=='f') {
	require 'aws-sdk-1.3.3/sdk.class.php';


		//
		// ** Keys for S3
		//

	$s3=new AmazonS3($key1, $key2);
	$_s3fileId=hex2base62(md5($meta['_id'].microtime()));
	$s3->path_style = true;
	$_s3response=$s3->create_object(
		'attachments.unlieu.com',$_s3fileId.'/'.$_file['name'],
		array('fileUpload'=>$_file['tmp_name'])
	);
	switch($_file['type']) {
		case 'image/jpeg':
			$_preview=createPreview(imagecreatefromjpeg($_file['tmp_name']),$s3);
		break;
		case 'image/png':
			$_preview=createPreview(imagecreatefrompng($_file['tmp_name']),$s3);
		break;
		case 'image/gif':
			$_preview=createPreview(imagecreatefromgif($_file['tmp_name']),$s3);
		break;
	}
	$_preview['s']=round($_file['size']/1024);
	$_preview['s3']=$_s3fileId;
	$_preview['n']=$_file['name'];
	$_content=$_preview;
}

function createPreview($source_image,&$s3) {
	global $meta,$_s3fileId,$_file;
	$p['w']=imagesx($source_image);
	$p['h']=imagesy($source_image);
	$p['W']=$p['w']<=280 ? $p['w'] : 280;
	$p['H']=floor($p['h']*($p['W']/$p['w']));
	$virtual_image=imagecreatetruecolor($p['W'],$p['H']);
	imagecopyresized($virtual_image,$source_image,0,0,0,0,$p['W'],$p['H'],$p['w'],$p['h']);
	$localfilename='.tmp/'.$_s3fileId.'.jpg';
	imagejpeg($virtual_image,$localfilename);
	$s3->path_style = true;
	$response=$s3->create_object(
		'previews.unlieu.com',$_s3fileId.'.jpg',
		array('fileUpload'=>$localfilename,
		'storage'=>AmazonS3::STORAGE_REDUCED,
		'headers'=>array('Cache-Control'=>'max-age=315360000,public'),
		'acl'=>AmazonS3::ACL_PUBLIC)
	);
	unlink($localfilename);
	return $p;
}

/*  
** write to database
*/

$uranium=$db->db();
switch($_a) {

case 'c':
	$time=time_milliseconds();
	$new=array(
		$_style => $_content,
		'm' => $time,
		'p' => $_partic
	);
	$uranium->post->update(
		array('_id'=>$_parent),
		array('$set'=>array('u'=>$time),'$push'=>array('d'=>$new))
	);
	$_size=isset($_content['s'])?(int)$_content['s']:0;
	$uranium->meta->update(
		array('_id'=>$meta['_id']),
		array('$inc'=>array('c'=>1,'s'=>$_size),'$set'=>array('u'=>$time))
	);
	apc_delete($meta['_id']);
	break;

case 'p':
	$time=time_milliseconds();
	$new=array(
		'id' => $meta['_id'],
		'u' => $time,
		'd' => array(array(
			$_style => $_content,
			'm' => $time,
			'p' => $_partic
		))
	);
	$uranium->post->insert($new);
	$_size=isset($_content['s'])?(int)$_content['s']:0;
	$uranium->meta->update(
		array('_id'=>$meta['_id']),
		array('$inc'=>array('b'=>1,'s'=>$_size),'$set'=>array('u'=>$time))
	);
	apc_delete($meta['_id']);		
break;

case 'a':
	$time=time_milliseconds();
	$uranium->meta->update(
		array('_id'=>$meta['_id']),
		array('$push'=>array('p'=>$_partic))
	);
	apc_delete($meta['_id']);
case 'j':
	$_SESSION['id']=$meta['_id'];
	$_SESSION['partic']=$_partic;
	exit();
break;

case 't':
	$time=time_milliseconds();
	$uTitle=htmlentities($_t);
	$uranium->meta->update(
		array('_id'=>$meta['_id']),
		array('$set'=>array('t'=>$uTitle,'v'=>$time))
	);
	apc_delete($meta['_id']);
	exit();
break;

case 's':
	require('smsified.class.php');
	
	$_o=$_AO[mt_rand(0,sizeof($_AO)-1)];
	$new=array(
		'id' => $meta['_id'],
		'p' => $_partic,
		'o' => $_o,
		'w' => 0
	);
	
	$db->db()->sms->update(
		array('_id'=>$_phone),
		array('$push'=>array('f'=>$new)),
		array('upsert' => true)
	);
	$uranium->meta->update(
		array('_id'=>$meta['_id']),
		array('$push'=>array('x'=>$_phone))
	);
	apc_delete($meta['_id']);
	
	sendSMS(
		$new['o'],
		$_phone,'{now following '.$meta['_id'].':"'.$meta['t'].'" reply @stop to unsubscribe, '.
		'@mute to stop msgs for 1hr, @help for help, or reply to comment} unlieu.com');
	exit('{"sms":"'.$new['o'].'"}');
break;

}

/*  
** send sms
*/

if(($sizeSMSlist=sizeof($meta['x']))>0) {
	
	require('smsified.class.php');
	
	switch($_style) {
		case 't':
			$_smscontent=$_content;
		break;
		
		case 'f':
			$_smscontent=(strlen($_preview['n'])<132)? '[file:'.$_preview['n'].']' : '[file:'.substr($_preview['n'],129).'...]';
		break;
	}		
	
	$_smscontent='|'.$_partic.'} '.$_smscontent;
	
	switch($_a) {
		case 'c':
			$_smstitle=isset($_postBody['d'][0]['t']) ? $_postBody['d'][0]['t'] : 'file:'.$_postBody['d'][0]['f']['n'];
			$_smstitle=substr($_smstitle,0,158-strlen($_smscontent));
			strlen($_smstitle)<=20 ?: $_smstitle=substr($_smstitle,0,17).'...'; 
		break;
		
		case 'p':
			$_smstitle="@post";
		break;
	
	}
			
	$_smscontent='{'.$_smstitle.$_smscontent;

	for($i=0, $l=$sizeSMSlist; $i<$l; $i++) {
		$result=$db->db()->sms->findOne(array('_id'=>$meta['x'][$i]));
		for($j=0, $ll=sizeof($result['f']); $j<$ll; $j++) {
			file_put_contents('log.txt',date('h:i:s')."-".$time.'-'.$result['f'][$j]['w']."\n",FILE_APPEND);
			if($result['f'][$j]['id']==$meta['_id'] && $time>$result['f'][$j]['w']) 
				sendSMS($result['f'][$j]['o'],$meta['x'][$i],$_smscontent);
		}	
	}

}


exit('{"status":"ok"}');