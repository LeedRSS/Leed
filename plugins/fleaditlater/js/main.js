

function fleadItLater(id,state,elem){
$.ajax({
					  url: "./action.php?action=fleadItLater",
					  data:{id:id,state:state},
					  success:function(msg){
					  	if(msg!=""){
					  		alert('Erreur de lecture : '+msg);
					  	}else{
					  		if(state=='delete'){
					  			$(elem).parent().fadeOut(300);
					  		}else{
					  			$(elem).fadeOut(300);
					  		}
					  	}
					  }
		});
}

