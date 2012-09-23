<?php
class Database
{
	private static $mInstance;
	private $uranium;
	
	public $mongodb;
	
	final private function __construct() { }
	final private function __clone() { }
	
	public function getMeta($mId) {
		if($fails=apc_fetch($_SERVER['REMOTE_ADDR'])) {
			if($fails>20) {
				include('banned.php');
				die($fails);
			}
		}

		if(!$meta=apc_fetch($mId)) { 
			if($meta=$this->db()->meta->findOne(array('_id'=>$mId))) {
				apc_store($meta['_id'],$meta);
				return $meta;
			} else {
				$this->db()->logFail->update(
					array('_id' => $_SERVER['REMOTE_ADDR']),
					array('$push'=>
						array(
							'i' => array(
								'u' => $mId,
								'a' => $_SERVER['HTTP_USER_AGENT'],
							)
						)
					),
					array('upsert' => true));
				apc_store($_SERVER['REMOTE_ADDR'],($fails+=1));
				include('tryagain.php');
				die();
			}
		}
		return $meta;
	}


	public function db() {
		if(!isset($this->uranium)) {
			// file_put_contents('log.txt',date('h:i:s')."-connecting to db\n",FILE_APPEND);	
			try {
				// $db=new Mongo('mongodb://gluon,photon,graviton',array('replicaSet'=>'bosons'));
				$db=new Mongo();
				$this->mongodb=$db;
				//$this->uranium=$db->setSlaveOkay(true);
				$this->uranium=$db->selectDB('uranium');
			} catch (MongoException $e) {
				error_log('connection to mongo failed: '.$e->getMessage());
				die('{"error":"serious"}');
			}
		} else {
			
		}
		return $this->uranium;
	}

	public static function getInstance() {
		return (!self::$mInstance) ? self::$mInstance=new self() : self::$mInstance;
	}
}

function time_milliseconds() {
	return round((microtime(true)*1000));
}

function u_match($type,&$in,&$out) {
	$regex=array(
		'id'=>'/^[a-z]{7}$/',
		'partic'=>'/^[a-zA-Z0-9]{1,15}$/',
		'parent'=>'/^[a-f0-9]{24}$/',
		't'=>'/^(.*){1,15}$/',
		'style'=>'/^[ft]{1}$/',
		'time'=>'/^[0-9]{13}$/',
		'f'=>'/^[a-zA-Z0-9]{21,22}$/',
		'phone'=>'/^[0-9]{10}$/'
	);
	return isset($in) && preg_match($regex[$type],$in) ? (bool) $out=$in : false;
}

function sendSMS($origin,$target,$message) {
	try {

		//
		// ** User/Pass for SMSified
		//

		$sms = new SMSified( $user, $pass);
		$response = $sms->sendMessage($origin, $target, $message);
		//$responseJson = json_decode($response);
		//var_dump($response);    
	}
	catch (SMSifiedException $ex) {
	   //echo $ex->getMessage();
	}
}

function hex2base62($val) { return gmp_strval(gmp_init($val,16),62); }
function print_r_html($arr) { echo '<pre>'.print_r($arr,true).'</pre>'; }
function isset_set(&$i,&$o) { return isset($i) ? (bool) $o=$i : false; }

$validareacodes=array(
205,251,256,334,659,938,	//alabama
907,250,					//alaska
480,520,602,623,928,		//arizona
327,479,501,870,			//arkansas
209,213,310,323,341,369,	//california
408,415,424,442,510,530,
559,562,619,626,627,628,
650,657,661,669,707,714,
747,760,764,805,818,831,
858,909,916,925,935,949,
951,
303,719,720,970,			//colorado
203,475,860,959,			//connecticut
302,						//delaware
202,						//dc
239,305,321,352,386,407,	//florida
561,689,727,754,772,786,
813,850,863,904,941,954,
229,404,470,478,678,706,	//georgia
762,770,912,
808,						//hawaii
208,						//idaho
217,224,309,312,331,447,	//illinois
464,618,630,708,730,773,
779,815,847,872,
219,260,317,574,765,812,	//indiana
319,515,563,641,712,		//iowa
316,620,785,913,			//kansas
270,364,502,606,859,		//kentucky
225,318,337,504,985,		//louisiana
207,						//maine
227,240,301,410,443,667,	//maryland
339,351,413,508,617,774,	//massachusetts
781,857,978,
231,248,269,313,517,586,	//michigan
616,679,734,810,906,947,
989,
218,320,507,612,651,763,	//minnesota
952,
228,601,662,769,			//mississippi
314,417,557,573,636,660,	//missouri
816,975,
406,						//montana
308,402,531,				//nebraska
702,775,					//nevada
603,						//new hampshire
201,551,609,732,848,856,	//new jersey
862,908,973,
505,575,					//new mexico
212,315,347,516,518,585,	//new york
607,631,646,716,718,845,
914,917,929,
252,336,704,828,910,919,	//north carolina
980,984,
701,						//north dekota
216,234,283,330,380,419,	//ohio
440,513,567,614,740,937,
405,539,580,918,			//oklahoma
458,503,541,971,			//oregon
215,267,272,412,445,484,	//pennsylvania
570,582,610,717,724,814,
835,878,
401,						//rhode island
803,843,864,				//south carolina
605,						//south dekota
423,615,731,865,901,931,	//tennessee
210,214,254,281,325,361,	//texas
409,430,432,469,512,682,
713,737,806,817,830,832,
903,915,936,940,956,972,
979,
385,435,801,				//utah
802,						//vermont
276,434,540,571,703,757,	//virginia
804,
206,253,360,425,509,564,	//washington
304,681,
262,274,414,534,608,715,	//wisconsin
920,
307							//wyoming
);