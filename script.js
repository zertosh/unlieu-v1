$(document).ready(function() {
	
	_u.map=new Object();
		
	for(var i in _u.data) {
		_u.map[_u.data[i]._id.$id]="data";
		$('#container').append(_u.fn.buildPost(_u.data[i]));
	}

	for(var i in _u.cache) {
		_u.map[_u.cache[i]._id.$id]="cache";
		$('#container').append(_u.fn.buildPost(_u.cache[i],false));
	}

	if(screen.width>640 && $.isFunction($().isotope)) {
		$('#container').isotope({
			itemSelector : '.bubble',
			layoutMode : 'masonry',
			transformsEnabled : false,
			getSortData: { time : function(elem) { return parseInt(elem.attr("data-sort")); } },
			sortBy: 'time',
			sortAscending : false
		});
	}

	$('textarea').live({
		'focus keyup':function() {
			var $this=$(this);
			tmaxlength=$this.attr('maxlength');
			ttip=$this.attr('data-tip');
			tLen=this.value.length;
			$this.closest('ul').children('li.new').removeClass('new');
			tLen>=tmaxlength ? this.value=this.value.substring(0,tmaxlength) : false;
			tLen=this.value.length;
			$(this).parent().parent().parent().find('.tip').filter('[data-tab="'+ttip+'"]').text('('+(tmaxlength-tLen)+')');
			
		},
		'blur':function() {
			this.value.length==0 ? $(this).parent().parent().parent().find('.tip').filter('[data-tab="'+ttip+'"]').text('') : false;
		},
		'keydown':function(e) {
			var $this=$(this);
			tmaxlength=$this.attr('maxlength');
			if(this.value.length>tmaxlength) { return false; }
			if(e.which==13 || e.keyCode==13) {
				
				if(this.value.length==0) { return; }
				dataArr=$(this).parent().serializeArray();
				this.value='';
				dataArr.push({name:"id",value:_u.meta._id},{name:"partic",value:_u.me});
				$.ajax({ 
					type:"post",url:"/u.php",dataType:"json",data:dataArr,
					success: function(data) {
						
					}
				});
				e.preventDefault();
			}
		}
	});
	
	$('.updater .menu button').live('click',function() {
		$this=$(this);
		if(!$this.hasClass('selected')) {
			selTabName=$this.attr('data-tab');
			$selTabSiblings=$this.siblings();
			$this.
				siblings('.option').
				removeClass('selected').
				end().
				addClass('selected').
				parent().
				siblings().
				filter('div').
				addClass('hidden').
				filter('div[data-tab="'+selTabName+'"]').
				removeClass('hidden');
			$this.
				siblings('.tip').
				addClass('hidden').
				filter('[data-tab="'+selTabName+'"]').
				removeClass('hidden');
			
			if($this.text()=='title') {
				$('#newtitletext').select();
			}
			
			_u.fn.bubbleReLayout();
		} else {
			if($this.text()=='comment' || $this.text()=='post' ) {
				var e = jQuery.Event("keydown");
				e.which = 13;
				$this.parent().parent().find('.text textarea').trigger(e);
				var e = jQuery.Event("keyup");
				e.which = 13;
				$this.parent().parent().find('.text textarea').trigger(e);
			}
		}
	});
	
	
	$('#me-partic').bind('click',function() {
		$.ajax({
			type:"post",
			url:"/v.php",
			dataType:"json",
			data:{id:_u.meta._id,a:'partic'},
			success: function(data) {
				if(typeof(data.p)!='undefined') {
					var html=[];
					for(var i=0, l=data.p.length; i<l; i++) {
						html.push('<button class="option">'+data.p[i]+'</button>');
					}
					$('#particlist').html(html.join(''));
					$('.updater, #me-partic').addClass('hidden');
					$('#select-partic').removeClass('hidden');
					_u.me='';
					_u.fn.bubbleReLayout();
				}
			}
		});		
	});
	
	$('#particlist button').live('click',function() {
		var newparticname=$(this).text();;
		$.ajax({
			type:"post",
			url:"/u.php",
			dataType:"json",
			data:{id:_u.meta._id,partic:newparticname,a:'j'},
			success: function(data) {
				_u.me=newparticname;
				$('#me-partic').text(_u.me);
				$('#select-partic').addClass('hidden');
				$('.updater, #me-partic').removeClass('hidden');
				_u.fn.bubbleReLayout();
			}
		});
	});
	
	$('#newpartic').bind({
		'keydown':function(e) { 
			if(e.keyCode!=13) { return;	}
			var newName=this.value;
			var $this=$(this);
			$.ajax({ 
				type:"post",
				url:"/u.php",
				dataType:"json",
				data:[{name:"id",value:_u.meta._id},{name:"partic",value:newName},{name:"a",value:"a"}],
				success: function(data) {
					_u.me=newName;
					$('#newpartic').val('');
					$this.siblings('.tip').text('(15)');
					$('#me-partic').text(_u.me);
					$('#select-partic').addClass('hidden');
					$('.updater, #me-partic').removeClass('hidden');
					_u.fn.bubbleReLayout();
				}
			});
			event.preventDefault();
		},
		'keyup':function(e) {
			var $this=$(this);
			this.value=this.value.replace(/[^-_a-zA-Z0-9]/g,'');
			$this.siblings('.tip').text('('+(15-this.value.length)+')');
		}
	});
	
	$('#newsms').bind({
		'keyup':function(e) {
			this.value=this.value.replace(/[^0-9]/g,'');
			this.value.length>=10 ? this.value=this.value.substring(0,10) : false;

		},
		'keydown':function(e) {
			if(this.value.length>10) { return false; }
			if(e.keyCode!=13) { return;	}
			if(this.value.length<10) { return false; }
			var newsms=this.value;
			var $this=$(this);
			$.ajax({ 
				type:"post",
				url:"/u.php",
				dataType:"json",
				data:[
					{name:"id",value:_u.meta._id},
					{name:"partic",value:_u.me},
					{name:"a",value:"s"},
					{name:"phone",value:newsms}],
				success: function(data) {
					console.log(data);
					if(typeof(data.sms)!='undefined') {
						alert("check your phone, you'll get a text confirmation from:"+data.sms);
						$this.val('');
					} else if(typeof(data.error!='undefined')) {
						if(data.error=='phone') { alert('invalid phone number'); }
						else if(data.error=='areacode') { alert('invalid area code'); }
					} else {
					
					}
					
				}
			});
			e.preventDefault();	
		}
	});
	
	$('input.fileuploader').live('click',function(e) {
		var $this=$(this);
		if($(this).siblings('[type="file"]').val()!='') {
			var $form=$(this).parent();
			var $inputId=$('<input type="hidden" name="id" value="'+_u.meta._id+'"></input>');
			var $inputPartic=$('<input type="hidden" name="partic" value="'+_u.me+'"></input>');
			$form.append($inputId,$inputPartic);
			$('#postframe').load(function() {
				$inputId.remove();
				$inputPartic.remove();
				$form.get(0).reset();
			});
			$this.submit();
		} else {
			e.preventDefault();
		}
	});
	
	$(window).bind({
		'focus':function() { _u.polltime=1000; },
		'blur':function() { _u.polltime=5000; }
	});
	
	$('.bubble>ul>li:not(:first-child) .clicker').live('click',function(e) {
		e.stopPropagation();
		$this=$(this).parent();
		$this.hasClass('closed') ? $this.removeClass('closed').prevAll().show() : $this.addClass('closed').prevAll().removeClass('closed').hide();
		_u.fn.bubbleReLayout();
	});
	
	$('.bubble>ul>li.closed').live('click',function(e) {
		e.stopPropagation();
		$this=$(this);
		$this.hasClass('closed') ? $this.removeClass('closed').prevAll().show() : $this.addClass('closed').prevAll().removeClass('closed').hide();
		_u.fn.bubbleReLayout();
	});
	
	$('.post.bubble>.top').live('click',function() {
		$thisparent=$(this).parent();
		parentId=$thisparent.attr('data-id');
		if(_u.map[parentId]=='data') {
			$thisparent.toggleClass('closed-bubble');
			_u.fn.bubbleReLayout();
		} else {
			_u.fn.loadPost(parentId,this);
		}
	});
	
	_u.fn.poll();
});

_u.fn={

	parseContent: function(d,parentId) {
		if(typeof(d.t)!='undefined') {
			var html=[
				'<span class="name">',d.p,': </span>',
				'<span class="content"> ',d.t,' </span>',
				'<span class="time"> ',new Date(d.m).toSimpleFormat(),'</span>',
				'<span class="clicker"></span>'
			];
		} else if(typeof(d.f)!='undefined') {
			if(typeof(d.f.w)!='undefined') {
				var html=[
					'<span class="name">',d.p,'</span>',
					'<span class="time"> ',new Date(d.m).toSimpleFormat(),'</span>',
					'<div class="pic" style="height:',d.f.H,'px; width:',d.f.W,'px;"><img src="http://s3.amazonaws.com/previews.unlieu.com/',d.f.s3,'.jpg" /></div>',
					'<a href="/d.php?id=',_u.meta._id,'&f=',d.f.s3,'&p=',parentId,'"> ',d.f.n,' </a><span class="size"> (',d.f.s,'kb)</span>',
					'<span class="clicker"></span>'
					];
			} else {
				var html=[
				'<span class="name">',d.p,': </span>',
				'<a href="/d.php?id=',_u.meta._id,'&f=',d.f.s3,'&p=',parentId,'"> ',d.f.n,' </a><span class="size"> (',d.f.s,'kb)</span>',
				'<span class="time"> ',new Date(d.m).toSimpleFormat(),'</span>',
				'<span class="clicker"></span>'];
			}

		}
		return html.join('');
	},

	
	loadPost: function(id,t,isOld) {
		isOld=typeof(isOld)=='undefined' ? false:isOld; 
		$.ajax({
			url:'/v.php',
			type:'post',
			dataType:'json',
			data:{id:_u.meta._id,parent:id,a:'post'},
			success:function(data) {
				_u.map[data._id.$id]="data";
				var html=[];
				for(var i=1, l=(data.d.length-5); i<l; i++) {
					html.push('<li style="display:none;">',_u.fn.parseContent(data.d[i],data._id.$id),'</li>');
				}
					data.d.length>6 ? html.push('<li class="closed">',_u.fn.parseContent(data.d[data.d.length-5],data._id.$id),'</li>') : false;
					data.d.length==6 ? html.push('<li ',isOld ?'class="new"':'','>',_u.fn.parseContent(data.d[data.d.length-5],data._id.$id),'</li>') : false;
				for(var i=(data.d.length>=6 ? data.d.length-4 : 1)  , l=data.d.length; i<l; i++) {
					html.push('<li ',isOld ?'class="new"':'','>',_u.fn.parseContent(data.d[i],data._id.$id),'</li>');
				}	

				$('#post-'+data._id.$id).removeClass('closed-bubble');
				$('#updater-'+data._id.$id).before(html.join(''));
				_u.fn.bubbleReLayout();
			}
		});
	
	},
	
	buildPost: function(postData,isLoaded) {
		var parentId=postData._id.$id;
		var html=[
			'<div class="post bubble ',_u.map[parentId]=='data'?'':'closed-bubble','" data-sort="'+postData.u+'" data-id="'+parentId+'" id="post-',parentId,'">',
			'<div class="top" id="top-',parentId,'">',
			_u.fn.parseContent(postData.d[0],parentId),
			'</div>',
			'<ul id="ul-'+parentId+'">'
		];
		
		if(_u.map[parentId]=='data') {
			for(var i=1, l=(postData.d.length-5); i<l; i++) {
				html.push('<li style="display:none;">',_u.fn.parseContent(postData.d[i],parentId),'</li>');
			}
				postData.d.length>6 ? html.push('<li class="closed">',_u.fn.parseContent(postData.d[postData.d.length-5],parentId),'</li>') : false;
				postData.d.length==6 ? html.push('<li>',_u.fn.parseContent(postData.d[postData.d.length-5],parentId),'</li>') : false;
			for(var i=(postData.d.length>=6 ? postData.d.length-4 : 1)  , l=postData.d.length; i<l; i++) {
				html.push('<li>',_u.fn.parseContent(postData.d[i],parentId),'</li>');
			}	
		}
	
		html.push(
			_u.fn.buildUpdater(parentId),
			'</ul>',
			'</div>'
		); return html.join('');
	},
	
	buildParticMenu: function() {
		var html=[];
		for(var i in _u.meta.p) { 
			html.push('<button class="option">',_u.meta.p[i],'</button>');
		};
		return html.join('');
	},
	
	buildUpdater: function(uParent) {
		var html=[
			'<li class="action updater ',_u.me==''?'hidden':'','" id="updater-'+uParent+'">',
			'<div class="text" data-tab="text">',
			'<form>',
			'<textarea name="content" placeholder="comment..." maxlength="140" data-tip="text"></textarea>',
			'<input type="hidden" name="parent" value="'+uParent+'"/>',
			'<input type="hidden" name="style" value="t"/>',
			'<input type="hidden" name="a" value="c"/>',
			'</form>',
			'</div>',
			'<div class="file hidden" data-tab="file">',
			'<form class="fileuploader" target="postframe" action="/u.php" method="post" enctype="multipart/form-data">',		
			'<input type="file" name="file"/>',
			'<input type="submit" value="attach" class="fileuploader"/>',
			'<input type="hidden" name="parent" value="'+uParent+'"/>',
			'<input type="hidden" name="style" value="f"/>',
			'<input type="hidden" name="a" value="c"/>',
			'</form>',
			'</div>',
			'<div class="menu">',
			'<button class="option selected" data-tab="text">comment</button>',
			'<button class="option" data-tab="file">file</button>',
			'<span class="tip hidden" data-tab="file">(20mb max)</span>',
			'<span class="tip" data-tab="text"></span>',
			'</div>',
			'</li>'
		]; return html.join('');
	},
	
	bubbleReLayout: function() {
		typeof($().isotope)=='function' ? $('#container').isotope('reLayout') : false;
	},
	
	refreshMeta: function() {
		$.getJSON('/g.php',{id:_u.meta.id,get:'meta'},function(data) { _u.meta=data; });
	},

	poll: function(repeat) {
		$.ajax({
			url:'/v.php',
			type:'post',
			dataType:'json',
			data:{id:_u.meta._id,v:_u.meta.v,u:_u.meta.u,a:'updates'},
			error:function(jqXHR, textStatus, errorThrown) {
				setTimeout(_u.fn.poll,_u.polltime);
				
			},
			success:function(data) {
				if(data==null) {
					setTimeout(_u.fn.poll,_u.polltime);
					return;
				}
				var html=[];
				if(typeof(data.error)!='undefined') {
					/* console.log('error:'+data.error); */
					return;
				}
				if (typeof(data.t)!='undefined' && typeof(data.v)!='undefined') {
					_u.meta.v<data.v ? _u.meta.v=data.v : false;
					_u.meta.t=data.t;
					document.title=_u.meta.t+' {u}';
					$('#mytitle').text(_u.meta.t);
					$('#newtitletext').val(_u.meta.t);
				} else {
					for(var i in data) {
						if(typeof(data[i].u)!='undefined') {
							_u.meta.u<data[i].u ? _u.meta.u=data[i].u : false;
						}
						if(typeof(_u.map[data[i]._id.$id])=='undefined') {
							_u.map[data[i]._id.$id]="data";
							data[i].d.reverse();
							$('#header').after(_u.fn.buildPost(data[i]));
							typeof($().isotope)=='function' ? $ ('#container').isotope('reloadItems').isotope({sortBy:'time',sortAscending:false}):false;
						} else if(_u.map[data[i]._id.$id]=="data") {
							for(var t=data[i].d.length-1; t>=0; t--) {
								$('#updater-'+data[i]._id.$id).before('<li class="new">'+_u.fn.parseContent(data[i].d[t],data[i]._id.$id)+'</li>');
								$('#post-'+data[i]._id.$id).removeClass('closed-bubble');
								$('#post-'+data[i]._id.$id).attr('data-sort',data[0].u);
								typeof($().isotope)=='function' ? $ ('#container').isotope('reloadItems').isotope({sortBy:'time',sortAscending:false}):false;
							}
						} else if(_u.map[data[i]._id.$id]=="cache") {
							_u.map[data[i]._id.$id]="data";					
							_u.fn.loadPost(data[i]._id.$id,$('#post-'+data[i]._id.$id+' .top').get(),true);
							$('#post-'+data[i]._id.$id).attr('data-sort',data[0].u);
							typeof($().isotope)=='function' ? $ ('#container').isotope('reloadItems').isotope({sortBy:'time',sortAscending:false}):false;						
						}
					}
				}
	
				setTimeout(_u.fn.poll,_u.polltime);
			}
		});
	}
};


Date.prototype.monthNames=['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
Date.prototype.getToday=new Date().toDateString();
Date.prototype.getYesterday=function() {
	var d=new Date();
	d.setDate(d.getDate()-1);
	return d.toDateString();
}();
Date.prototype.toSimpleFormat = function() {
	return (this.toDateString()==this.getYesterday ? 'yesterday at ' : (this.toDateString()!=this.getToday ? this.monthNames[this.getMonth()]+' '+this.getDate()+' at ' : '')) +
		(this.getHours()%12 || 12) + ':' +
		(this.getMinutes()<10 ? '0':'')+this.getMinutes()+
		(this.getHours()<12 ? 'am':'pm');
}