var isCtrl = false;
var isMaj = false;
var keyCode = new Array();

keyCode['shift'] = 16;
keyCode['ctrl'] = 17;
keyCode['enter'] = 13;
keyCode['m'] = 77;
keyCode['s'] = 83;
keyCode['n'] = 78;
keyCode['v'] = 86;
keyCode['p'] = 80;
keyCode['k'] = 75;
keyCode['o'] = 79;
keyCode['space'] = 32;

$(document).ready(function(){

	targetThisEvent($('article section:first'),true);

});


$(document).keyup(function (e) {
if(e.which == keyCode['ctrl']) isCtrl=false;
if(e.which == keyCode['shift']) isMaj=false;
}).keydown(function (e) {
 	//alert(e.which);
    if(e.which == keyCode['ctrl']) isCtrl=true;
    if(e.which == keyCode['shift']) isMaj=true;
    
    if($("input:focus").length==0){
    switch(e.which){
    	
        case keyCode['m']:
                //marque l'élément sélectionné comme lu / non lu
                readTargetEvent();
            return false;
        break;

        case keyCode['s']:
                //marque l'élément sélectionné comme favori / non favori
                switchFavoriteTargetEvent();
            return false;
        break;
        case keyCode['n']:
            //élément suivant (sans l'ouvrir)
            targetNextEvent();
            return false;
        break;
        case keyCode['v']:
            //ouvre l'url de l'élément sélectionné
            openTargetEvent();
            return false;
        break;
        case keyCode['p']:
            //élément précédent (sans l'ouvrir)
            targetPreviousEvent();
            return false;
        break;
        case keyCode['space']:
            if(isMaj){
                //élément précédent (et l'ouvrir)
                targetPreviousEvent();
                openTargetEvent();
            }else{
                //élément suivant (et l'ouvrir)
                targetNextEvent();
                openTargetEvent();
            }
            return false;
        break;
        case keyCode['k']:
            //élément précédent (et l'ouvrir)
            targetPreviousEvent();
            openTargetEvent();
            return false;
        break;
        case keyCode['o']:
        case keyCode['enter']:
            //ouvrir l'élément sélectionné
            openTargetEvent();
            return false;
        break;
    }
        }
});

/* Fonctions de séléctions */

function targetPreviousEvent(){
	targetThisEvent($('.eventSelected').prev(':visible'),true);
}
function targetNextEvent(){

	targetThisEvent($('.eventSelected').next(':visible'),true);
}

function targetThisEvent(event,focusOn){
	target = $(event);
	if(target.prop("tagName")=='SECTION'){
		$('.eventSelected').removeClass('eventSelected');
		target.addClass('eventSelected');
		var id = $('.anchor',target).attr('name');
		if(focusOn)window.location = '#'+id;
	}
}
function openTargetEvent(){
	window.open($('.eventSelected .articleTitle a').attr('href'), '_blank');
}

function readTargetEvent(){
	var buttonElement = $('.eventSelected .readUnreadButton');
	var id = $('.anchor',target).attr('name');
	readThis(buttonElement,id,null,function(){
		targetThisEvent($('.eventSelected').next(),true);
	});
	
	

}

function readAllDisplayedEvents(){
	$('article section').each(function(i,article){
		var buttonElement = $('.readUnreadButton',article);
		var id = $('.anchor',article).attr('name');
		readThis(buttonElement,id);
	});
}

function switchFavoriteTargetEvent(){
	var id = $('.anchor',target).attr('name');
	if($('.favorite',target).html()=='Favoriser'){
		addFavorite($('.favorite',target),id);
	}else{
		removeFavorite($('.favorite',target),id);
	}
}

/* Fonctions de séléctions fin */

function toggleFolder(element,folder){
	feedBloc = $('ul',$(element).parent().parent());

	open = 0;
	if(feedBloc.css('display')=='none') open = 1;
	feedBloc.slideToggle(200);
	$(element).html((!open?'Déplier':'Plier'));
	$.ajax({
				  url: "./action.php?action=changeFolderState",
				  data:{id:folder,isopen:open}
	});
}

function addFavorite(element,id){
	$(element).attr('onclick','removeFavorite(this,'+id+');').html('Défavoriser');
	$.ajax({
				  url: "./action.php?action=addFavorite",
				  data:{id:id}
	});
}

function removeFavorite(element,id){
	$(element).attr('onclick','addFavorite(this,'+id+');').html('Favoriser');
	$.ajax({
				  url: "./action.php?action=removeFavorite",
				  data:{id:id}
	});
}

function renameFolder(element,folder){
	var folderLine = $(element).parent();
	var folderNameCase = $('span',folderLine);
	var value = folderNameCase.html();
	$(element).html('Enregistrer');
	$(element).attr('style','background-color:#0C87C9;');
	$(element).attr('onclick','saveRenameFolder(this,'+folder+')');
	folderNameCase.replaceWith('<span><input type="text" name="folderName" value="'+value+'"/></span>');
}


function saveRenameFolder(element,folder){
	var folderLine = $(element).parent();
	var folderNameCase = $('span',folderLine);
	var value = $('input',folderNameCase).val();
	$(element).html('Renommer');
	$(element).attr('style','background-color:#F16529;');
	$(element).attr('onclick','renameFolder(this,'+folder+')');
	folderNameCase.replaceWith('<span>'+value+'</span>');
	$.ajax({
				  url: "./action.php?action=renameFolder",
				  data:{id:folder,name:value}
	});
}


function renameFeed(element,feed){
	var feedLine = $(element).parent().parent();
	var feedNameCase = $('td:first a',feedLine);
	var feedUrlCase = $('td:first span',feedLine).html();
	var url = feedNameCase.attr('href');
	var value = feedNameCase.html();
	$(element).html('Enregistrer');
	$(element).attr('style','background-color:#0C87C9;');
	$(element).attr('onclick','saveRenameFeed(this,'+feed+',"'+url+'")');
	feedNameCase.replaceWith('<input type="text" name="feedName" value="'+value+'"/>');
}

function saveRenameFeed(element,feed,url){
	var feedLine = $(element).parent().parent();
	var feedNameCase = $('td:first',feedLine);
	var value = $('input',feedNameCase).val();
	$(element).html('Renommer');
	$(element).attr('style','background-color:#F16529;');
	$(element).attr('onclick','renameFeed(this,'+feed+')');
	feedNameCase.replaceWith('<td><a href="'+url+'">'+value+'</a></td>');
	$.ajax({
				  url: "./action.php?action=renameFeed",
				  data:{id:feed,name:value}
	});
}




function changeFeedFolder(element,id){
	var value = $(element).val();
	window.location = "./action.php?action=changeFeedFolder&feed="+id+"&folder="+value;
}


function readThis(element,id,from,callback){
	var hide = ($('#pageTop').html()==''?true:false);
	var parent = $(element).parent().parent();
	if(!parent.hasClass('eventRead')){

		if(hide){ 
					  		parent.fadeOut(200,function(){
					  			if(null!=callback) callback();
					  		}); 
					  	}else{ 
					  		parent.addClass('eventRead');
					  	}
		
		$.ajax({
					  url: "./action.php?action=readContent",
					  data:{id:id},
					  success:function(msg){
					  	if(msg!="") alert('Erreur de lecture : '+msg);
					  }
		});
	}else{

			if(from!='title'){
			
				parent.removeClass('eventRead');
				$.ajax({
							  url: "./action.php?action=unreadContent",
							  data:{id:id}
				});
			}
	}
	
}

//synchronisation manuelle lancée depuis le boutton du menu
function synchronize(code){
	if(code!=''){
	$('article').html('<section>'+
	'<iframe class="importFrame" src="action.php?action=synchronize&format=html&code='+code+'" name="idFrameSynchro" id="idFrameSynchro" width="100%" height="300" ></iframe>'+
	'</section>');
	}else{
		alert('Vous devez être connecté pour synchroniser vos flux');
	}
}