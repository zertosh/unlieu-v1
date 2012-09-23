<!DOCTYPE html>
<html lang="en">

<head>
<meta charset="UTF-8" />
<title>unlieu.com</title>
<meta name="viewport" content="maximum-scale=1.0, user-scalable=0, width=device-width" />
<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.6.2/jquery.min.js"></script>
<?=strrpos($_SERVER['HTTP_USER_AGENT'],'iPhone')?'':'<script type="text/javascript" src="http://s3.amazonaws.com/static.unlieu.com/jquery.isotope.min.1.4.110808.js"></script>'?>
<link rel="stylesheet" type="text/css" href="style.css" />
<script type="text/javascript">
$(document).ready(function() {
	if(screen.width>640 && $.isFunction($().isotope)) {
		$('#container').isotope({
			itemSelector : '.bubble',
			layoutMode : 'masonry',
			transformsEnabled : false,
		});
	}
	$('#more').bind('click',function() {
		$('div.info').toggleClass('hidden');
		typeof($().isotope)=='function' ? $('#container').isotope('reLayout') : false;
	});
});
</script>
</head>

<body>
<div id="container">
	<div id="header" class="bubble">
		<div class="top shadow" style="overflow:auto;">
			<span style=""></span>
			<span style="float:right; font-size:20pt; font-weight: 100;">unlieu.com</span>
		</div>
		<ul>
			<li class="updater action shadow">
				<form action="u.php" method="POST" style="float:right;"/>
					<input type="hidden" name="a" value="n" />
					<button class="option" type="submit">create new unlieu</button>
				</form>
				<button class="option" id="more">more...</button>
			</li>
		</ul>
	</div>
	<div class="bubble info hidden">
		<div class="top">
			<span style="">usage</span>
		</div>
		<ul>
			
			<li class="">
			<span style="font-weight:bold;">what is an unlieu?</span><br>
			<span>an unlieu is the collection of posts, comments and files identified by the 7 character id following the unlieu.com/ when you hit the "create new unlieu" button.</span></li>
			<li class="">
			<span style="font-weight:bold;">sharing an unlieu</span><br>
			<span>with just the address of your unlieu, anyone can participate in your discussion without having to signup or register for anything.</span></li>
			<li class="">
			<span style="font-weight:bold;">getting to unlieu</span><br>
			<span>you can use unlieu.com on pretty much any browser, and if you don't have one, you can still use it via text message. check out "notifications" in your unlieu.</span></li>
		</ul>
	</div>
	<div class="bubble info hidden">
		<div class="top">
			<span style="">faq</span>
		</div>
		<ul>
			<li class="">
			<span style="font-weight:bold;">is an unlieu private?</span><br>
			<span>it's as private as you decide to keep your unlieu id. since anyone with the id can see your unlieu, make sure to keep it safe. otherwise the ids are random with over 4.5 billion possible combinations, so it's very unlikely that someone can just randomly end up in yours.</span></li>
			<li>
			<span style="font-weight:bold;">what's a post? what's a comment?</span><br>
			<span>a post is the topic that goes in the pink bubbles, while a comment is the stuff that goes under it. think of unlieu as kinda like a forum. a post is the topic, and a comment is a comment. and either a post or a comment can be text or a file.</span>
			</li>
			<li>
			<span style="font-weight:bold;">are there limits on the amount of posts or comments in an unlieu?</span><br>
			<span>no if it's text. yes if it's an attachment.</span>
			</li>
			<li class="">
			<span style="font-weight:bold;">what are the attachment limits?</span><br>
			<span>bandwidth and storage is expensive, and we're just starting out. so, each attachment is limited to 20mb, and an unlieu can use 100mb total. also, each unlieu has a download quota of 500mb.</span>
			</li>
		</ul>
	</div>
	<div class="bubble info hidden">
		<div class="top">
			<span style="">about</span>
		</div>
		<ul>
			<li class=""><span style="font-weight:bold;">who are we?</span><br>
			<span>so, it's three people, andres suarez, aws shemmeri, and saraa basaria. we're law students at northeastern university school of law in boston. andres does more of the creative design and technology stuff, aws does more of the day-to-day business legal stuff, and saraa does the public relations and marketing things. (btw special thanks go to daniel widrew for helping with all the testing)</span></li>
			<li class=""><span style="font-weight:bold;">what's with the name?</span><br>
			<span>it just sounds cool, sorry, no romantic story here.</span></li>
			<li>
			<span style="font-weight:bold;">where are all the capital letters?</span><br>
			<span>somewhere else, not here. we don't like them.</span>
			</li>
		</ul>
	</div>
	<div class="bubble info hidden">
		<div class="top">
			<span style="">contact</span>
		</div>
		<ul>
			<li><span style="">contact@unlieu.com</span></li>
			<li class="">twitter: @unlieu</li>
		</ul>
	</div>
</div>
<span style="color:#333; font-align:right;">andres suarez © 2011</span>
</body>
</html>