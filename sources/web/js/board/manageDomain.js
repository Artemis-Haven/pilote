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
	/* récupérer le nom */
	titleParag = $('a[href="#domainPanel-'+id+'"]');
	titleText = titleParag.text();
	/* transformer le lien en champ texte */
	titleParag.replaceWith(
		'<input id="renameDomainField-'+ id +'" type="text" class="renameDomainField" value="'+titleText+'"></input>');
	$('#renameDomainField-'+ id ).focus();
    selectText(titleBlock);
	/* Lorsque le focus n'est plus sur le champ texte... : */
	$('#renameDomainField-'+ id ).focusout(function(){
		/* récupérer la nouvelle valeur */
		newTitleText = $('#renameDomainField-'+ id ).val();
		$.ajax({
	        /* requête AJAX */
	        type: "POST",
	        dataType:"json",
	        url: Routing.generate('pilote_tasker_renameDomain'),
	        data: { 'domainId' : id, 'newTitle' : newTitleText },
	        cache: false,
	        success: function(data){
				/* transformer le champ texte en lien */
				$('#renameDomainField-'+ id ).replaceWith('<a data-toggle="collapse" data-parent="#accordion" ' +
					'href="#domainPanel-'+id+'">' + newTitleText + '</a>');
				/* changer les infobulles au passage de la souris sur les boutons */
				$('#renameDomainBtn-'+id).prop('title', 'Renommer "'+newTitleText+'"');
				$('#deleteDomainBtn-'+id).prop('title', 'Supprimer "'+newTitleText+'"');
	        },
	        error: function(data){
				$('#renameDomainField-'+ id ).replaceWith('<a data-toggle="collapse" data-parent="#accordion" ' +
					'href="#domainPanel-'+id+'">' + titleText + '</a>');
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
