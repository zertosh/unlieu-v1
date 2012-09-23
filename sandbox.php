<html>
<head>
<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1/jquery.min.js"></script>
<title></title>
<style>
</style>
</head>
<body>
<form action="u.php" method="post" enctype="multipart/form-data">
	<input type="text" name="id" value="rqrxuvy"/>
	<input type="text" name="partic" value="saraa"/>
	<input type="text" name="a" value="p"/>
	<input type="text" name="style" value="f"/>
	<input type="text" name="parent" value=""/>

	<input type="file" name="file" />
	<input type="submit">
</form>

<form action="u.php" action="post" onKeyDown="kM(event,this);">
	<textarea ></textarea>
</form>

<?
require('common.php');
apc_clear_cache('user');
echo round((microtime(true)*1000));
echo exec("ulimit -u");
phpinfo();
?>
</body>
<script type="text/javascript">
var _x = function() {

}
</script>
 	
</html>

