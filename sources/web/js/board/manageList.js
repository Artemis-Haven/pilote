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

/**
 * Ajouter une liste de tâches dans la BdD et, en cas de succès, l'ajouter 
 * aussi dans l'étape appropriée.
 *
 * @param {number} id L'identifiant de l'étape dans laquelle ajouter
 * la liste de tâches.
 */
function addList(id) {
	/* requête AJAX */
    $.ajax({
        type: "POST",
        dataType:"json",
        url: Routing.generate('pilote_tasker_createTList'),
        data: 'stepId=' + id,
        cache: false,
        success: function(data){
        	/* agrandir le Container des listes pour permettre le scroll */
        	$( "#tab-"+id ).width((data.nbrOfLists+1)*265);
        	/* insérer la liste vide */
			$(data.tList).insertBefore( "#addListBtn-" + id );
			/* activer les boutons supprimer liste, renommer liste et ajouter tâche */
			$("#renameListBtn-"+data.tListId).click(function(){
				renameList(data.tListId);
			});
			$("#deleteListBtn-"+data.tListId).click(function(){
				deleteList(data.tListId);
			});
			/* rendre triable la liste */
			setSortableTask();
        }
    }); 
};

/**
 * Supprimer une liste dans la BdD et, en cas de succès, la supprimer
 * aussi dans l'étape appropriée.
 *
 * @param {number} id L'identifiant de la liste concernée
 */
function deleteList(id) {
    if (!confirm("Êtes-vous sûrs de vouloir supprimer cette liste de tâches ?")) return false;
    $.ajax({
        type: "POST",
        dataType:"json",
        url: Routing.generate('pilote_tasker_deleteTList'),
        data: 'tListId=' + id,
        cache: false,
        success: function(data){
        	/* supprimer la liste */
			$('#tList-'+id).remove();
			/* réduire le Container des listes */
        	$( "#tab-"+data.stepId ).width((data.nbrOfLists+1)*265);
        }
    }); 
};

/**
 * Transformer le titre de la liste en champ texte. Lorsque le
 * focus n'est plus sur le champ texte, récupérer le contenu de
 * celui-ci et faire la modification dans la BdD. 
 * En cas de succès, transformer le champ texte en paragraphe avec 
 * le nouveau texte.
 * Sinon, transformer le champ texte en paragraphe avec l'ancien
 * texte.
 *
 * @param {number} id L'identifiant de la liste de tâches concernée
 */
function renameList(id) {
    /* titleBlock sera l'élément contenant le titre */
    titleParag = $('#tList-'+ id +' > .tList-heading > p');
    /* on le rend éditable */
    titleParag.attr("contenteditable", "true");
    /* on sauvegarde l'ancien titre au cas où */
    oldTitleText = titleParag.text();
    titleParag.focus();
    selectText(titleParag);

	/* Lorsque le focus n'est plus sur le champ texte... : */
	titleParag.focusout(function(){
		/* récupérer la nouvelle valeur */
		newTitleText = titleParag.text();
		$.ajax({
		/* requête AJAX */
	        type: "POST",
	        dataType:"json",
	        url: Routing.generate('pilote_tasker_renameTList'),
	        data: { 'tListId' : id, 'newTitle' : newTitleText },
	        cache: false,
	        success: function(data){
                /* transformer le champ texte en paragraphe */
                titleParag.attr("contenteditable", "false");
	        },
	        error: function(data){
                titleParag.attr("contenteditable", "false");
                titleParag.text(oldTitleText);
	        }
	    });
	});
};

/**
 * Lors du clic sur un bouton d'ajout de liste, appeler la
 * fonction addList avec l'id de l'étape concernée en paramètre.
 */
$(".addListBtn").click(function(){
	addList($( this ).attr('id').replace('addListBtn-', ''))
});

/**
 * Lors du clic sur un bouton de suppresion de liste, appeler la
 * fonction deleteList avec l'id de la liste concernée en paramètre.
 */
$(".deleteListBtn").click(function(){
	deleteList($( this ).attr('id').replace('deleteListBtn-', ''));
});

/**
 * Lors du clic sur un bouton de renommage de liste, appeler la
 * fonction renameList avec l'id de la liste concernée en paramètre.
 */
$(".renameListBtn").click(function(){
	renameList($( this ).attr('id').replace('renameListBtn-', ''));
});