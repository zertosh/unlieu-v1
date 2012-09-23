	<?php
require('common.php');
require('smsified.class.php');

/*  
** sanity check
*/
isset($GLOBALS['HTTP_RAW_POST_DATA']) or die('{"error":"no post data"}');
$sms = json_decode($GLOBALS['HTTP_RAW_POST_DATA']);

isset($sms->inboundSMSMessageNotification->inboundSMSMessage) or die ('{"error":"no sms"}');
$sms=$sms->inboundSMSMessageNotification->inboundSMSMessage;
$mobileNumber=substr($sms->senderAddress,-10);
$thisNumber=substr($sms->destinationAddress,-10);

preg_match('/^[0-9]{10}/',$mobileNumber) or die('{"error":"invalid number"}');

$db=Database::getInstance();

if(($_smsDb=$db->db()->sms->findOne(array('_id'=>$mobileNumber)))==null) {
	sendSMS(
		$thisNumber,
		$mobileNumber,
		'{sorry but your number isn\'t following any unlieu- try unlieu.com it\'s awesome}'
	);
	die('{"error":"no number match"}');
} else {
	$subscribed=array(); 
	for($i=0, $l=sizeof($_smsDb['f']); $i<$l; $i++) { 
		if($_smsDb['f'][$i]['o']==$thisNumber) {
			$_id=$_smsDb['f'][$i]['id'];
			$_partic=$_smsDb['f'][$i]['p'];
		}
		$subscribed[]=$_smsDb['f'][$i]['o'];
	}
	if(!in_array($thisNumber,$subscribed)) {
		sendSMS(
			$thisNumber,
			$mobileNumber,
			'{sorry your unlieu(s) are actually here: '.implode(', ',$subscribed).'}'
		);
		die('{"error":"wrong destination"}');
	}
}

$meta=$db->getMeta($_id) or die('{"error":"db"}');

/*  
** command interpreter
*/
switch(substr($sms->message,0,1)) {
case '@': /* unlieu commands */
	preg_match('/^\@([a-zA-Z]+)\s?(.*)/',$sms->message,$matches);
	$matches[1]=strtolower($matches[1]);
	switch($matches[1]) {
	case 'info':
		$_text='{'.$meta['_id'].':"'.$meta['t'].'": ';
		$_shortParticList=substr(implode(',',$meta['p']),0,158-strlen($_text));
		$_text=$_text.$_shortParticList.'}';
		sendSMS(
			$thisNumber,
			$mobileNumber,
			$_text
		);
		exit();
	break;
	
	case 'mute':
	case 'sleep':
		$waittime=$matches[1]=='mute' ? 1 : 7;
		$uranium=$db->db();
		$endtime=time_milliseconds()+($waittime*60*60*1000);
		$uranium->sms->update(
			array('_id'=>$mobileNumber,'f.id'=>$_id),
			array('$set'=>array('f.$.w'=>$endtime))
		);
		sendSMS(
			$thisNumber,
			$mobileNumber,
			'{'.$meta['_id'].':"'.$meta['t'].'" won\'t bother you for '.$waittime.' hr'.(($waittime==1)?'':'s').', reply @wake to undo} unlieu.com'
		);
		exit();
	break;

	case 'wake':
	case 'unmute':
		$uranium=$db->db();
		$uranium->sms->update(
			array('_id'=>$mobileNumber,'f.id'=>$_id),
			array('$set'=>array('f.$.w'=>0))
		);
		sendSMS(
			$thisNumber,
			$mobileNumber,
			'{ok getting alerts again from '.$meta['_id'].':"'.$meta['t'].'"} unlieu.com'
		);
	break;

	case 'stop':
	case 'leave':
		$uranium=$db->db();
		$uranium->meta->update(
			array('_id'=>$_id),
			array('$pull'=>array('x'=>$mobileNumber))
		);
		$uranium->sms->update(
			array('_id'=>$mobileNumber),
			array('$pull'=>array('f'=>array('id'=>$_id)))
		);		
		sendSMS(
			$thisNumber,
			$mobileNumber,
			'{no longer following '.$meta['_id'].':"'.$meta['t'].'"} unlieu.com'
		);
		apc_delete($_id);
		exit();
		
	break;
	
	case 'help':
		sendSMS(
			$thisNumber,
			$mobileNumber,
			'{reply to comment, '.
			'@post to post, @info for title and participants, '.
			'@mute to stop msgs for 1hr and @sleep for 7hrs, '.
			'@wake to undo either, @stop to stop msgs}'
		);
		exit();
	break;
	
	case 'post':
		$uranium=$db->db();
		$time=time_milliseconds();
		$new=array(
			'id' => $_id,
			'u' => $time,
			'd' => array(array(
				't' => $matches[2],
				'm' => $time,
				'p' => $_partic
			))
		);
		$uranium->post->insert($new);
		$uranium->meta->update(
			array('_id'=>$_id),
			array('$inc'=>array('b'=>1),'$set'=>array('u'=>$time))
		);

		if(($sizeSMSlist=sizeof($meta['x']))>0) {
			$_smscontent='{@post|'.$_partic.'} '.$matches[2];
			for($i=0, $l=$sizeSMSlist; $i<$l; $i++) {
				if($meta['x'][$i]!=$mobileNumber) {
					$result=$db->db()->sms->findOne(array('_id'=>$meta['x'][$i]));
					for($j=0, $ll=sizeof($result['f']); $j<$ll; $j++) {			
						if($result['f'][$j]['id']==$meta['_id'] && $time>$result['f'][$j]['w']) 
							sendSMS(
								$result['f'][$j]['o'],
								$meta['x'][$i],
								$_smscontent
							);
					}	
				}
			}	
		}
		
		apc_delete($_id);
		exit();
	break;
	
	default:
		sendSMS(
			$thisNumber,
			$mobileNumber,
			'{sorry wrong command, try @help} unlieu.com'
		);
		exit();
	break;
	}
break;

default: /* probably a comment reply */
	$uranium=$db->db();
	$search=$uranium->post->find(array('id'=>$_id));
	$search->sort(array('u'=>-1));
	$search->limit(1);
	$_post=$search->getNext();
	
	if($_post) {
		$time=time_milliseconds();
		$new=array(
			't' => $sms->message,
			'm' => $time,
			'p' => $_partic
		);
		$uranium->post->update(
			array('_id'=>$_post['_id']),
			array('$set'=>array('u'=>$time),'$push'=>array('d'=>$new))
		);
		$uranium->meta->update(
			array('_id'=>$_id),
			array('$inc'=>array('c'=>1),'$set'=>array('u'=>$time))
		);

		if(($sizeSMSlist=sizeof($meta['x']))>0) {
			$_smscontent='|'.$_partic.'} '.$sms->message;
			$_smstitle=isset($_post['d'][0]['t']) ? $_post['d'][0]['t'] : 'file:'.$_post['d'][0]['f']['n'];
			$_smstitle=substr($_smstitle,0,158-strlen($_smscontent));
			strlen($_smstitle)<=20 ?: $_smstitle=substr($_smstitle,0,17).'...';
			$_smscontent='{'.$_smstitle.$_smscontent;
			
			for($i=0, $l=$sizeSMSlist; $i<$l; $i++) {
				if($meta['x'][$i]!=$mobileNumber) {
					$result=$db->db()->sms->findOne(array('_id'=>$meta['x'][$i]));
					for($j=0, $ll=sizeof($result['f']); $j<$ll; $j++) {
						file_put_contents('log.txt',date('h:i:s')."-".$time.'-'.$result['f'][$j]['w']."\n",FILE_APPEND);
						if($result['f'][$j]['id']==$meta['_id'] && $time>$result['f'][$j]['w']) 
							sendSMS(
								$result['f'][$j]['o'],
								$meta['x'][$i],
								$_smscontent
							);
					}	
				}
			}	
		}
		apc_delete($_id);
		exit();

	} else {
		sendSMS(
			$thisNumber,
			$mobileNumber,
			'{sorry no nothing to comment on in this unlieu try @post or @help} unlieu.com'
		);
		exit();
	}
	break;
break;
}