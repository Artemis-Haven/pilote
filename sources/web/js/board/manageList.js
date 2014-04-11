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
        url: Routing.generate('piltasker_createTList'),
        data: 'stepId=' + id,
        cache: false,
        success: function(data){
        	/* agrandir le Container des listes pour permettre le scroll */
        	$( "#tab-"+id ).width((data.nbrOfLists+1)*260);
        	/* insérer la liste vide */
			$('<div id="tList-'+data.tListId+'" class="taskList panel">' +
					'<div class="panel-heading"><p>'+data.tListName+'</p>' + 
						'<div class="listMenu dropdown">' + 
							'<a data-toggle="dropdown" href="#" class="caret"></a>' + 
							'<ul class="dropdown-menu" role="menu">' + 
								'<li id="renameListBtn-'+data.tListId+'" class="renameListBtn">' +
								'<a><span class="glyphicon glyphicon-edit"></span> Renommer</a></li> ' + 
								'<li id="deleteListBtn-'+data.tListId+'" class="deleteListBtn">' +
								'<a><span class="glyphicon glyphicon-minus"></span> Supprimer</a></li>' + 
					'</ul></div></div>' +
					'<article class="sortableTasksContainer"></article>' +
					'<div class="blankTask"></div>' +
					'<div id="addTaskBtn-'+data.tListId+'" class="addTaskBtn">Ajouter une tâche...</div>' +
				'</div>')
				.insertBefore( "#addListBtn-" + id );
			/* activer les boutons supprimer liste, renommer liste et ajouter tâche */
			$("#renameListBtn-"+data.tListId).click(function(){
				renameList(data.tListId);
			});
			$("#deleteListBtn-"+data.tListId).click(function(){
				deleteList(data.tListId);
			});
			$("#addTaskBtn-"+data.tListId).click(function(){
				addTask(data.tListId);
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
    $.ajax({
        type: "POST",
        dataType:"json",
        url: Routing.generate('piltasker_deleteTList'),
        data: 'tListId=' + id,
        cache: false,
        success: function(data){
        	/* supprimer la liste */
			$('#tList-'+id).remove();
			/* réduire le Container des listes */
        	$( "#tab-"+data.stepId ).width((data.nbrOfLists+1)*260);
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
    titleParag = $('#tList-'+ id +' > .panel-heading > p');
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
	        url: Routing.generate('piltasker_renameTList'),
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