function toggleFolder(element,folder){
	feedBloc = $('ul',$(element).parent());
	open = 0;
	if(feedBloc.css('display')=='none') open = 1;
	feedBloc.slideToggle(200);
	
	$.ajax({
				  url: "./action.php?action=changeFolderState",
				  data:{id:folder,isopen:open}
	});
}

function addFavorite(element,id){
	$(element).fadeOut(100);
	$.ajax({
				  url: "./action.php?action=addFavorite",
				  data:{id:id}
	});
}

function removeFavorite(element,id){
	$(element).fadeOut(100);
	$.ajax({
				  url: "./action.php?action=removeFavorite",
				  data:{id:id}
	});
}

function renameFolder(element,folder){
	var folderLine = $(element).parent().parent();
	var folderNameCase = $('td:first',folderLine);
	var value = folderNameCase.html();
	$(element).html('Enregistrer');
	$(element).attr('style','background-color:#0C87C9;');
	$(element).attr('onclick','saveRenameFolder(this,'+folder+')');
	folderNameCase.replaceWith('<td><input type="text" name="folderName" value="'+value+'"/></td>');
}


function saveRenameFolder(element,folder){
	var folderLine = $(element).parent().parent();
	var folderNameCase = $('td:first',folderLine);
	var value = $('input',folderNameCase).val();
	$(element).html('Renommer');
	$(element).attr('style','background-color:#F16529;');
	$(element).attr('onclick','renameFolder(this,'+folder+')');
	folderNameCase.replaceWith('<td>'+value+'</td>');
	$.ajax({
				  url: "./action.php?action=renameFolder",
				  data:{id:folder,name:value}
	});
}

function changeFeedFolder(element,id){
	var value = $(element).val();
	window.location = "./action.php?action=changeFeedFolder&feed="+id+"&folder="+value;
}


function readThis(element,id,hide){
	var parent = $(element).parent().parent();
	if(!parent.hasClass('eventRead')){
		
		$.ajax({
					  url: "./action.php?action=readContent",
					  data:{id:id},
					  success:function(){
					  	if(hide){ parent.fadeOut(200); 
					  	}else{ 
					  		parent.addClass('eventRead');
					  	}
					  }
		});
	}else{
		parent.removeClass('eventRead');
	$.ajax({
				  url: "./action.php?action=unreadContent",
				  data:{id:id}
	});
	}
}

