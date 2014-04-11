/**
 * Ajouter une checklist dans la BdD et, en cas de succès, l'ajouter aussi
 * dans la tâche appropriée.
 *
 * @param {number} id L'identifiant de la tâche dans lequel ajouter
 * la checklist.
 */
function addChecklist(id) {
	/* Requête AJAX */
    $.ajax({
        type: "POST",
        dataType:"json",
        url: Routing.generate('piltasker_createChecklist'),
        data: 'taskId=' + id,
        cache: false,
        success: function(data){
        	/* insérer la checklist vide */
        	$('<div id="checklist-'+data.id+'">'+
				'<h4><span class="glyphicon glyphicon-list"></span> '+data.name+'</h4>'+
				'<p id="addChecklistOptionBtn-'+data.id+'">Ajouter une ligne...</p></p>'+
			'</div>').appendTo('.checklistContainer');

			/* activer les boutons pour ajouter une option */
			/*$("#addChecklistOptionBtn-"+data.id).click(function(){
				addChecklistOption(data.id);
			});*/

            /* Lors du clic sur le titre de la checklist, elle devient éditable */
            $("#checklist-"+data.id+" .title").click(function(){
                renameChecklist(id);
            });
        }
    }); 
};


function renameChecklist(id) {
    /* titleBlock sera l'élément contenant le titre */
    titleBlock = $("#checklist-"+id+" .title");
    /* on le rend éditable */
    titleBlock.attr("contenteditable", "true");
    /* on sauvegarde l'ancien titre au cas où */
    oldTitleText = titleBlock.text();
    titleBlock.focus();
    preventEnterKey(titleBlock);
    /* Lorsque le focus n'est plus sur le titre... : */
    titleBlock.focusout(function(){
        /* récupérer la nouvelle valeur */
        newTitleText = titleBlock.text();
        $.ajax({
        /* requête AJAX */
            type: "POST",
            dataType:"json",
            url: Routing.generate('piltasker_renameChecklist'),
            data: { 'checklistId' : id, 'newName' : newTitleText },
            cache: false,
            success: function(data){
                /* transformer le champ texte en paragraphe */
                titleBlock.attr("contenteditable", "false");
            },
            error: function(data){
                titleBlock.attr("contenteditable", "false");
                titleBlock.text(oldTitleText);
            }
        });
    });
};

/**
 * Ajouter une option de checklist dans la BdD et, en cas de succès, l'ajouter 7
 * aussi dans la checklist appropriée.
 *
 * @param {number} id L'identifiant de la checklist dans lequel ajouter
 * l'option.
 */
/*function addChecklistOption(id) {
	/* Requête AJAX */
    /*$.ajax({
        type: "POST",
        dataType:"json",
        url: Routing.generate('piltasker_createChecklistOption'),
        data: 'checklistId=' + id,
        cache: false,
        success: function(data){
        	/* insérer l'option */
        	/*$('<p id="checklistOption-'+data.id+'" class="checkbox">'+
					'<label><input type="checkbox">'+data.optionText+'</label>'+
				'</p>').insertBefore('#addChecklistOptionBtn-'+id);
        }
    }); 
};


function renameChecklistOption(id) {
    /* titleBlock sera l'élément contenant le titre */
    /*titleBlock = $("#checklistOption-"+id+" input");
    /* on le rend éditable */
    /*titleBlock.attr("contenteditable", "true");
    /* on sauvegarde l'ancien titre au cas où */
    /*oldTitleText = titleBlock.text();
    titleBlock.focus();
    preventEnterKey(titleBlock);
    /* Lorsque le focus n'est plus sur le titre... : */
    /*titleBlock.focusout(function(){
        /* récupérer la nouvelle valeur */
        /*newTitleText = titleBlock.text();
        $.ajax({
        /* requête AJAX */
            /*type: "POST",
            dataType:"json",
            url: Routing.generate('piltasker_renameChecklistOption'),
            data: { 'checklistOptionId' : id, 'newName' : newTitleText },
            cache: false,
            success: function(data){
                /* transformer le champ texte en paragraphe */
                /*titleBlock.attr("contenteditable", "false");
            },
            error: function(data){
                titleBlock.attr("contenteditable", "false");
                titleBlock.text(oldTitleText);
            }
        });
    });
};*/