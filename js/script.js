/* Author:

*/

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







