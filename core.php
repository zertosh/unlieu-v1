<?
session_start();
require_once('common.php');
isset($_GET['id'][6]) or die('missing id');

$db=Database::getInstance();
$meta=$db->getMeta($_GET['id']) or die();

$uranium=$db->db();


$search=$uranium->post->find(
	array('id'=>$meta['_id']),
	array('id'=>0));
$search->sort(array('u'=>-1));
$search->limit(1);
$data=array();
while($doc=$search->getNext()) { array_push($data,$doc); }

$search=$uranium->post->find(
	array('id'=>$meta['_id']),
	array('id'=>0,
		  'd' =>array('$slice'=>1)));
$search->sort(array('u'=>-1));
$search->skip(1);
$cache=array();
while($doc=$search->getNext()) { array_push($cache,$doc); }

$me=(isset($_SESSION['id']) && isset($_SESSION['partic']) && 
	$_SESSION['id']==$meta['_id'] && in_array($_SESSION['partic'],$meta['p'])) ? 
	$_SESSION['partic'] : '';

unset($meta['x']);
?>
<!doctype html>
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="maximum-scale=1.0, user-scalable=0, width=device-width" />
<link rel="stylesheet" type="text/css" href="/style.css" />
<title><?=$meta['t']?> {u}</title>
<script type="text/javascript">
var _u={};
_u.meta=<?=json_encode($meta)?>; 
_u.data=<?=json_encode($data)?>;
_u.cache=<?=json_encode($cache)?>;
_u.me="<?=$me?>";
_u.polltime=1000;
_u.running=false;
</script>
</head>
<body>
<div id="notifier"></div>
<div id="container"> <!-- start container  -->
	<div id="header" class="bubble" data-sort="2147483647000"> <!-- start header  -->
		<div class="shadow top">
			<span style="font-weight:bold; float:left; display:none;" id="mytitle"><?=$meta['t']?></span>
			<button class="option <?=($me!='')?'':'hidden'?>" style="float:right; font-size:large; font-weight:100;" id="me-partic"><?=$me?></button>
		</div>
		<ul>
			<li class="action shadow <?=($me=='')?'':'hidden'?>" id="select-partic">
				<span style="font-weight:bold;">to post or comment pick a name.</span>
				<div class="menu" id="particlist">
				<? foreach($meta['p'] as $_p) { echo '<button class="option">'.$_p.'</button>'; } ?>
				</div>
				<form>
					<span style="font-weight:bold;">type your name: </span>
					<input type="text" id="newpartic" maxlength="15"/>
					<span class="tip">(15)</span>
				</form>
			</li>
			<li class="action updater shadow <?=($me!='')?'':'hidden'?>">
				<div class="hidden" data-tab="title">
					<form action="u.php" method="post" target="postframe">
						<textarea name="t" placeholder="new title..." maxlength="15" data-tip="title" id="newtitletext"><?=$meta['t']?></textarea>
						<input type="hidden" name="a" value="t"/>
					</form>
				</div>
				<div class="hidden" data-tab="notifications">
					<form>
						<p style="font-weight: bold;">text message alerts for new posts/comments. type in your number:</p>
						<input type="text" name="t" placeholder="phone number..."  style="margin:5px 0;" id="newsms" maxlength="10"/>
						<span class="tip">(10-digit U.S. number)</span>
						<input type="hidden" name="a" value="nt"/>
						<p>our phone numbers start with 347-630-077x, so make sure to save whichever one is assigned to you for this unlieu. reply to any message and it'll go under whatever post it came from.</p>
					</form>
				</div>
									
				<div class="text" data-tab="text">
				<form>
					<textarea name="content" placeholder="post..." maxlength="140" data-tip="text"></textarea>
					<input type="hidden" name="style" value="t"/>
					<input type="hidden" name="a" value="p"/>
				</form>
				</div>
				<div class="file hidden" data-tab="file">
				<form class="fileuploader" target="postframe" action="/u.php" method="post" enctype="multipart/form-data">	
					<input type="file" name="file"/>
					<input type="submit" value="attach" class="fileuploader"/>
					<input type="hidden" name="style" value="f"/>
					<input type="hidden" name="a" value="p"/>
				</form>
				</div>
				<div class="menu">
					<button class="option selected" data-tab="text">post</button>
					<button class="option" data-tab="file">file</button>
					<button class="option" data-tab="title">title</button>
					<button class="option" data-tab="notifications">notifications</button>
					<span class="tip hidden" data-tab="file">(20mb max)</span>
					<span class="tip hidden" data-tab="title">(<?=15-strlen($meta['t'])?>)</span>
					<span class="tip" data-tab="text"></span>
				</div>
			</li>
		</ul>
	</div> <!-- end header  -->
<!-- start posts  -->
<!-- end posts  -->
</div> <!-- end container  -->
<iframe name="postframe" id="postframe" style="display:none; height:0; width:0;"></iframe>
<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.6.2/jquery.min.js"></script>
<?=strrpos($_SERVER['HTTP_USER_AGENT'],'iPhone')?'':'<script type="text/javascript" src="http://s3.amazonaws.com/static.unlieu.com/jquery.isotope.min.1.4.110808.js"></script>'?>
<script type="text/javascript" src="/script.js"></script>
<span style="color:#333; font-align:right;">andres suarez © 2011</span>
<!-- <span style="bottom:0; position: absolute; color:#666;">andres suarez © 2011</span> -->
</body>
</html>