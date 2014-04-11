/**
 * Ajouter une étape dans la BdD et, en cas de succès, l'ajouter aussi
 * dans le domaine approprié.
 *
 * @param {number} id L'identifiant du domaine dans lequel ajouter
 * l'étape.
 */
function addStep(id) {
	/* requête AJAX */
    $.ajax({
        type: "POST",
        dataType:"json",
        url: Routing.generate('piltasker_createStep'),
        data: 'domainId=' + id,
        cache: false,
        success: function(data){
        	/* insérer l'onglet de l'étape */
			$('<li><a data-target="#tab-'+data.stepId+'" data-toggle="tab">' +
					'<span class="stepTitle">'+data.stepName+'</span>' +
					'<div class="stepMenu dropdown">' +
						'<a data-toggle="dropdown" class="caret"></a>' +
						'<ul class="dropdown-menu" role="menu">' +
							'<li id="renameStepBtn-'+data.stepId+'" class="renameStepBtn">' +
							'<a><span class="glyphicon glyphicon-edit"></span> Renommer</a></li>' +
							'<li id="deleteStepBtn-'+data.stepId+'" class="deleteStepBtn">' +
							'<a><span class="glyphicon glyphicon-minus"></span> Supprimer</a></li>' +
			'</ul></div></a></li>')
				.insertBefore( $("li>a#addStepBtn-" + id).parent() );
			/* insérer l'étape vide */
			$('#domain-'+id+' .tab-content').append(
				'<div class="tab-pane" id="tab-'+data.stepId + '" style="width:260px;">' +
					'<div id="addListBtn-'+data.stepId+'" class="addListBtn panel">' +
						'<p>Ajouter une nouvelle liste</p> ' +
						'<span class="glyphicon glyphicon-plus"></span>' +
					'</div></div>');
			/* activer les boutons pour supprimer l'étape, renommer l'étape et ajouter une liste */
			$("#deleteStepBtn-"+data.stepId).click(function(){
				deleteStep(data.stepId);
			});
			$("#renameStepBtn-"+data.stepId).click(function(){
				renameStep(data.stepId);
			});
			$("#addListBtn-"+data.stepId).click(function(){
				addList(data.stepId);
			});
			/* activer la nouvelle étape*/
			setActiveStep(data.stepId);
			setSortableTask();
        }
    }); 
};

/**
 * Supprimer une étape dans la BdD et, en cas de succès, la supprimer
 * aussi dans le domaine approprié.
 *
 * @param {number} id L'identifiant de l'étape concernée
 */
function deleteStep(id) {
	/* requête AJAX */
    $.ajax({
        type: "POST",
        dataType:"json",
        url: Routing.generate('piltasker_deleteStep'),
        data: 'stepId=' + id,
        cache: false,
        success: function(data){
        	/* supprimer l'onglet */
        	$('*[data-target="#tab-'+id+'"]').parent().remove();
        	/* supprimer l'étape */
			$('#tab-'+id).remove();
			/* changer l'onglet actif */
			setActiveStep(data.newActiveStep);
        }
    }); 
};

/**
 * Transformer le titre de l'étape en champ texte. Lorsque le
 * focus n'est plus sur le champ texte, récupérer le contenu de
 * celui-ci et faire la modification dans la BdD. 
 * En cas de succès, transformer le champ texte en span avec 
 * le nouveau texte.
 * Sinon, transformer le champ texte en span avec l'ancien
 * texte.
 *
 * @param {number} id L'identifiant de l'étape concernée
 */
function renameStep(id) {
    /* titleBlock sera l'élément contenant le titre */
    titleParag = $('*[data-target="#tab-'+id+'"] > .stepTitle');
	/* on le rend éditable */
    titleParag.attr("contenteditable", "true");
    /* on sauvegarde l'ancien titre au cas où */
    oldTitleText = titleParag.text();
    titleParag.focus();
    preventEnterKey(titleParag);
	/* Lorsque le focus n'est plus sur le champ texte... : */
	titleParag.focusout(function(){
		/* récupérer la nouvelle valeur */
		newTitleText = titleParag.text();
		$.ajax({
			/* requête AJAX */
	        type: "POST",
	        dataType:"json",
	        url: Routing.generate('piltasker_renameStep'),
	        data: { 'stepId' : id, 'newTitle' : newTitleText },
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
 * Désactiver l'étape actuellement active (dans le domaine concerné)
 * et activer l'étape dont l'id est passé en paramêtre.
 * 
 * @param {number} id L'identifiant de l'étape qui va devenir active
 */
function setActiveStep(id) {
	domainId = $( '*[data-target="#tab-' + id + '"]' )
		.parent().parent().attr('id').replace('stepList-', '');
	$('.domain #stepList-' + domainId + ' > li.active').removeClass('active');
	$('*[data-target="#tab-' + id + '"]').parent().addClass('active');
	$('#domain-' + domainId + ' .tab-pane.active').removeClass('active');
	$('#tab-' + id).addClass('active');
}

/**
 * Lors du clic sur un bouton d'ajout d'étape, appeler la
 * fonction addStep avec l'id du domaine concerné en paramètre.
 */
$(".addStepBtn").click(function(){
	addStep($( this ).attr('id').replace('addStepBtn-', ''))
});

/**
 * Lors du clic sur un bouton de suppresion d'étape, appeler la
 * fonction deleteStep avec l'id de l'étape concernée en paramètre.
 */
$(".deleteStepBtn").click(function(){
	deleteStep($( this ).attr('id').replace('deleteStepBtn-', ''))
});

/**
 * Lors du clic sur un bouton de renommage d'étape, appeler la
 * fonction renameStep avec l'id de l'étape concernée en paramètre.
 */
$(".renameStepBtn").click(function(){
	renameStep($( this ).attr('id').replace('renameStepBtn-', ''))
});

