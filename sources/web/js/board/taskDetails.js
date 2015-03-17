/* Zone des commentaires */


function btnappear()
{
    $(".commentBtn").show();
}

function addComment() {
    var content = $("#commentArea").val(); 
    var id = $("#commentArea").data('taskid'); 
    if (content == "") {return false;};
    $.ajax({
        type: "POST",
        dataType:"json",
        url: Routing.generate('pilote_tasker_createComment'),
        data: { 'taskId': id, 'content': content },
        cache: false,
        success: function(data){
        	/* ajouter le commentaire à la tache */
            $(data.comment).prependTo( $(".comments") );
            $("#commentArea").val("");	
        }
    });
}

function deleteComment(id)
{
    $.ajax({
        type: "POST",
        dataType:"json",
        url: Routing.generate('pilote_tasker_deleteComment'),
        data: { 'commentId': id},
        cache: false,
        success: function(data){
            $("#commentsContainer .comments").find("[data-commentid='" + id + "']").remove();
        }
    });
}
    