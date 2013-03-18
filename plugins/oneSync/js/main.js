
function onesync_validate(feed){
	if(confirm('Etes vous sûr de vouloir synchroniser uniquement ce flux?'))window.location='action.php?action=syncronyzeOne&feed='+feed;
}