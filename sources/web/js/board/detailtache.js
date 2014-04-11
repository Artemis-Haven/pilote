function btnappear()
{
    $(".commentBtn").show();
}

function btndisappear()
{
}

function ajouterComment(id) {
    var dataComment=$("#saisieComment").val(); 
    $.ajax({
        type: "POST",
        dataType:"json",
        url: Routing.generate('piltasker_createComment'),
        data: { 'taskId': id, 'content': dataComment },
        cache: false,
        success: function(data){
        	/* ajouter le commentaire à la tache */
		$('<div class="nouvelComment" >' + 
                        '<div class="newComment-User glyphicon glyphicon-user" title="'+data.commentUser+'" ></div>' +
                            '<div class="newComment"><div class="newComment-Data">'+data.commentContent+'</div>'+
                            '<p class="newComment-Time">'+getTime()+'</p></div></div>')
				.prependTo( $(".comment") );
                        
                $("#saisieComment").val("");	
        }
    }); 
    

}

function deleteComment()
{
    $(".suppBt").click(function()
    {
        $("#saisieComment").val("");        
    });
}

/*
function getDateTime(jasondate)
{
    var isZero = function(num)
    {
        if(num < 10)
        {
            num = "0" + num;
        }
        return num;
    };
    var d= new Date(parseInt(jsondate.substr(6)));
    var mouths=new Array("Janvier","Février","Mars","Avril","Mai","Juin","Juillet","Août","Septembre","Octobre","Novembre","Décembre");
    
    return  mouths[d.getMonth() + 1] + " " + isZero(d.getDate()) + " dans " + isZero(d.getHours()) + ":" + isZero(d.getMinutes());
} 
*/

function getTime()
{
    var isZero = function(num)
    {
        if(num < 10)
        {
            num = "0" + num;
        }
        return num;
    };
    var d= new Date();
    var mouths=new Array("Janvier","Février","Mars","Avril","Mai","Juin","Juillet","Août","Septembre","Octobre","Novembre","Décembre");
    
    //return  mouths[d.getMonth() + 1] + " " + isZero(d.getDate()) + " dans " + isZero(d.getHours()) + ":" + isZero(d.getMinutes());
    return  isZero(d.getDate()) + "/" + isZero(d.getMonth() + 1)/* mouths[d.getMonth() + 1]*/ + "/" + d.getFullYear()+ " " + isZero(d.getHours()) + ":" + isZero(d.getMinutes() + ".");
    
} 

