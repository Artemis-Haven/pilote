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
        url: Routing.generate('pilote_tasker_createComment'),
        data: { 'taskId': id, 'content': dataComment },
        cache: false,
        success: function(data){
        	/* ajouter le commentaire à la tache */
		$(data.comment).prependTo( $(".comment") );
                        
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

