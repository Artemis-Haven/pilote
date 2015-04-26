/*

Copyright (C) 2015 Hamza Ayoub, Valentin Chareyre, Sofian Hamou-Mamar, 
Alain Krok, Wenlong Li, Rémi Patrizio, Yamine Zaidou

________________________________

This file is part of Pilote.

    Pilote is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    Pilote is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with Pilote.  If not, see <http://www.gnu.org/licenses/>.

*/

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
    