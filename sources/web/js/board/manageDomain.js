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
 * Script gérant toute les actions concernant les domaines affichés dans
 * la vue du Board
 */

/**
 * Ajouter un domaine dans la BdD et, en cas de succès, l'ajouter aussi
 * dans le board approprié.
 *
 * @param {number} id L'identifiant du board dans lequel ajouter
 * le domaine.
 */
function addDomain(id) {
	/* Requête AJAX */
    $.ajax({
        type: "POST",
        dataType:"json",
        url: Routing.generate('pilote_tasker_createDomain'),
        data: 'boardId=' + id,
        cache: false,
        success: function(data){
        	/* insérer le domaine vide */
        	$(data.domain).appendTo('.boardSection .panel-group');

			/* activer les boutons pour ajouter une étape, supprimer un domaine et renommer le domaine */
			$("#addStepBtn-"+data.domainId).click(function(){
				addStep(data.domainId);
			});
			$("#deleteDomainBtn-"+data.domainId).click(function(){
				deleteDomain(data.domainId);
			});
			$("#renameDomainBtn-"+data.domainId).click(function(){
				renameDomain(data.domainId);
			});
			$('#domain-'+data.domainId+' .addDomainBtn').click(function(){
				addDomain(id);
			});
        }
    }); 
};

/**
 * Supprimer un domaine dans la BdD et, en cas de succès, le supprimer
 * aussi dans le board approprié.
 *
 * @param {number} id L'identifiant du domaine concerné
 */
function deleteDomain(id) {
    if ($(".boardSection > #accordion > .domain").length == 1) {
    	alert("Le board doit contenir au moins un domaine.");
    	return false;
    };
    if (!confirm("Êtes-vous sûrs de vouloir supprimer ce domaine ?")) return false;
	/* Requête AJAX */
    $.ajax({
        type: "POST",
        dataType:"json",
        url: Routing.generate('pilote_tasker_deleteDomain'),
        data: 'domainId=' + id,
        cache: false,
        success: function(){
        	/* supprimer le domaine */
        	$('#domain-' + id).remove();
        }
    }); 
};

/**
 * Transformer le titre du domaine en champ texte. Lorsque le
 * focus n'est plus sur le champ texte, récupérer le contenu de
 * celui-ci et faire la modification dans la BdD. 
 * En cas de succès, transformer le champ texte en lien avec 
 * le nouveau texte.
 * Sinon, transformer le champ texte en lien avec l'ancien
 * texte.
 *
 * @param {number} id L'identifiant du domaine concerné
 */
function renameDomain(id) {
    /* titleBlock sera l'élément contenant le titre */
    titleParag = $('a[href="#domainPanel-'+id+'"]');
    /* on le rend éditable */
    titleParag.attr("contenteditable", "true");
    /* on sauvegarde l'ancien titre au cas où */
    oldTitleText = titleParag.text();
    titleParag.focus();
    selectText(titleParag);

	/* Lorsque le focus n'est plus sur le champ texte... : */
	titleParag.focusout(function(){
        titleParag.attr("contenteditable", "false");
		/* récupérer la nouvelle valeur */
		newTitleText = titleParag.text();
		$.ajax({
		/* requête AJAX */
	        type: "POST",
	        dataType:"json",
	        url: Routing.generate('pilote_tasker_renameDomain'),
	        data: { 'domainId' : id, 'newTitle' : newTitleText },
	        cache: false,
	        error: function(data){
                titleParag.text(oldTitleText);
	        }
	    });
	});
};

/**
 * Lors du clic sur un bouton d'ajout de domaine, appeler la
 * fonction addDomain avec l'id du board concerné en paramètre.
 */
$(".addDomainBtn").click(function(){
	addDomain($( this ).attr('id').replace('addDomainBtn-', ''))
});

/**
 * Lors du clic sur un bouton de suppresion de domaine, appeler la
 * fonction deleteDomain avec l'id du domaine concernée en paramètre.
 */
$(".deleteDomainBtn").click(function(){
	deleteDomain($( this ).attr('id').replace('deleteDomainBtn-', ''))
});

/**
 * Lors du clic sur un bouton de renommage de domaine, appeler la
 * fonction renameDomain avec l'id du domaine concernée en paramètre.
 */
$(".renameDomainBtn").click(function(){
	renameDomain($( this ).attr('id').replace('renameDomainBtn-', ''))
});
