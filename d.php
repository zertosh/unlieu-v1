<?
require('common.php');
u_match('id',$_GET['id'],$_id) or die('{"error":"id"}');
u_match('f',$_GET['f'],$_f) or die('{"error":"f"}');
u_match('parent',$_GET['p'],$_parent) or die('{"error":"p"}');

$db=Database::getInstance();
$meta=$db->getMeta($_GET['id']) or die();

$meta['q']<=(500*1024) ?: die('{"error":"quota hit"}');

$_parent=new MongoId($_parent);
$search=$db->db()->post->findOne(array('_id'=>$_parent))?: die('{"error":"no parent match"}');

for($i=0, $l=sizeof($search['d']); $i<$l; $i++) {
	if(isset($search['d'][$i]['f']) && $search['d'][$i]['f']['s3']==$_f) {
		require 'aws-sdk-1.3.3/sdk.class.php';


		//
		// ** Keys for S3
		//


		$s3=new AmazonS3( $key1, $key2 );
		$s3->path_style = true;
		$response = $s3->get_object_url('attachments.unlieu.com',$_f.'/'.$search['d'][$i]['f']['n'],'20 minutes');
		$uranium=$db->db();
		$uranium->meta->update(
			array('_id'=>$meta['_id']),
			array('$inc'=>array('q'=>$search['d'][$i]['f']['s']))
		);
		$meta['q']+=$search['d'][$i]['f']['s'];
		apc_store($meta['_id'],$meta);
		header("Location: ".$response);
		exit();
	}
}
die('{"error":"file not found"}');
?>