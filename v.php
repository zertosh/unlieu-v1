<?php
header('Content-Type: text/json');
require('common.php');
//file_put_contents('log+meta.txt',date('h:i:s')."-read".print_r($_POST,true)."\n",FILE_APPEND);
u_match('id',$_POST['id'],$_id) or die('{"error":"id"}');
isset_set($_POST['a'],$_a) or die('{"error":"missing a"}');
$db=Database::getInstance();
$meta=$db->getMeta($_id) or die('{"error":"db"}');

switch($_a) {
	case 'updates':
		u_match('time',$_POST['u'],$_u); $_u=(float) $_u;
		u_match('time',$_POST['v'],$_v); $_v=(float) $_v;
		// file_put_contents('log+meta.txt',date('h:i:s')."-read".$meta['u']."\n",FILE_APPEND);
		if($meta['u']>$_u) {
			$search=$db->db()->post->find(array(
				'id'=>$meta['_id'],
				'u'=>array('$gt'=>$_u))
			);
			$search->sort(array('u'=>-1));
			$json=array();
			while($result=$search->getNext()) {
			//	var_dump($result);
				$return['_id']=$result['_id'];
				$return['u']=$result['u'];
				$resultdsize=sizeof($result['d']);
				for($i=$resultdsize-1; $i>=0; $i--) {
					if($result['d'][$i]['m']>$_u) {
						$return['d'][]=$result['d'][$i];
						//file_put_contents('log+meta.txt',date('h:i:s')."-".$result['d'][$i]."\n",FILE_APPEND);
					} else {
						break;
					}
				}	
				$json[]=$return;
				unset($return);
			}
			
			//exit();
			exit(json_encode($json));
			
		} elseif($meta['v']>$_v) { 
			exit(json_encode(array('t'=>html_entity_decode($meta['t']),'v'=>$meta['v'])));	
		} else {
			die('');
		}
	break;
	
	case 'post':
		u_match('parent',$_POST['parent'],$_parent);
		$_parent=new MongoId($_parent);
		$uranium=$db->db();
		$search=$uranium->post->findOne(
			array('_id'=>$_parent),
			array('id'=>0));
		exit(json_encode($search));
				
	break;
	
	case 'partic':
		exit(json_encode(array('p'=>$meta['p'])));
	break;
}



