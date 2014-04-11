/**
 * Ajouter une tâche dans la BdD et, en cas de succès, l'ajouter aussi
 * dans la liste appropriée.
 *
 * @param {number} id L'identifiant de la liste dans laquelle ajouter
 * la tâche.
 */
function addTask(id) {
	/* requête AJAX */
    $.ajax({
        type: "POST",
        dataType:"json",
        url: Routing.generate('piltasker_createTask'),
        data: 'tListId=' + id,
        cache: false,
        success: function(data){
        	/* ajouter la tâche à sa place */
			$('<div id="task-'+data.taskId+'" class="task" data-toggle="modal" data-target="#modalTask">'+
					'<p class="task-header">'+data.taskName+'</p></div>')
				.insertBefore( "#addTaskBtn-" + id );
			/* rendre triable la nouvelle tâche */
			setSortableTask();
			/* rendre cliquable la nouvelle tâche */
			$("#task-"+data.taskId).click(function(){getTaskDetails(data.taskId);});
        }
    }); 
};

/**
 * Récupérer les informations sur la tâche concernée dans la BdD
 * et, en cas de succès, les insérer dans la fenêtre modale.
 *
 * @param {number} id L'identifiant de la tâche concernée
 */
function getTaskDetails(id) {
    route = Routing.generate('piltasker_getTaskDetails');
	/* requête AJAX */
    $.ajax({
        type: "POST",
        url: route,
        data: 'taskId=' + id,
        cache: false,
        success: function(data){
            /* donner l'id de la tâche à la fenêtre */
            $('.taskDetailsSection').attr('id', "modalTask-"+id);
            /* placer les infos de la tâche dans la fenêtre */
            $('.taskDetailsSection .modal-dialog').html(data);                     

            /* Cache la toolbar par défaut */
            $('.btn-toolbar').hide();
            /* Rend éditable le contenu de la tâche lors du clic sur celui-ci */
            $('.taskDetailsSection .modal-taskContent').click(function(){
                setContentZoneEditable(id)
            });

            /* Lors du clic sur le titre de la tâche, dans le détail de la
            tâche en haut, elle devient éditable */
            $(".modal-taskTitle b").click(function(){
                renameTask(id);
            });

            /* Lors du clic sur le titre de la checklist, il devient éditable */
            $(".checklist .title").click(function(){
                renameChecklist($(this).parent().parent().attr('id').replace('checklist-',''));
            });

            /* Lors du clic sur une option d'une checklist, elle devient éditable */
            /*$(".checklist .checkbox").click(function(){
                renameChecklistOption($(this).attr('id').replace('checklistOption-',''));
            });*/

            /* Lors du clic sur le bouton de suppresion de tâche, 
            appeler la fonction deleteTask avec l'id de la tâche 
            concernée en paramètre. */
            $("#modalTask-"+id+" .deleteTaskBtn").click(function(){
                deleteTask(id);
            });

            /* Lors du clic sur le bouton d'ajout de checklist, 
            appeler la fonction addChecklist avec l'id de la tâche 
            concernée en paramètre. */
            $("#addChecklistBtn-"+id).click(function(){
                addChecklist(id);
            });
            
             $(".postBt").click(function(){
                   ajouterComment(id);   
             });
            deleteComment();
        }
    }); 
};


function renameTask(id) {
    /* titleBlock sera l'élément contenant le titre */
    titleBlock = $(".modal-taskTitle b");
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
            url: Routing.generate('piltasker_renameTask'),
            data: { 'taskId' : id, 'newTitle' : newTitleText },
            cache: false,
            success: function(data){
                /* transformer le champ texte en paragraphe */
                titleBlock.attr("contenteditable", "false");
                /* met à jour la miniature de la tâche */
                $('#task-'+id+' .task-header').text(newTitleText);
            },
            error: function(data){
                titleBlock.attr("contenteditable", "false");
                titleBlock.text(oldTitleText);
            }
        });
    });
};

/**
 * Supprimer une tâche dans la BdD et, en cas de succès, la supprimer
 * aussi dans la liste appropriée.
 *
 * @param {number} id L'identifiant de la tâche concernée
 */
function deleteTask(id) {
	/* requête AJAX */
    $.ajax({
        type: "POST",
        url: Routing.generate('piltasker_deleteTask'),
        data: 'taskId=' + id,
        cache: false,
        success: function(data){
        	/* fermer la fenêtre */
        	$('#modalTask').modal('hide');
        	/* supprimer la tâche */
			$('#task-'+id).remove();
        }
    }); 
};

function updateTaskContent(id, newContent) {
    $.ajax({
    /* requête AJAX */
        type: "POST",
        url: Routing.generate('piltasker_updateTaskContent'),
        data: { 'taskId' : id, 'newContent' : newContent },
        cache: false,
        error: function(oldContent){
            $('#editor').html("Erreur : "+oldContent);
            alert("erreur");
        }
    });
};

function setContentZoneEditable(id) {
    /* Rend la zone éditable */
    $("#editor").wysiwyg();
    $("#editor").focus();
    /* rend le clic en dehors de la fenetre inefficace */
    /*$('#modalTask').on('hide.bs.modal', function(){return false} );*/
    /*$('#modalTask').modal({
        backdrop: 'static',
        keyboard: false
    });*/
    /*$('#modalTask').removeData("modal").modal({backdrop: 'static', keyboard: false});*/
    /* fait apparaître la toolbar */
    $('.btn-toolbar').show();
    /* Corrige un bug avec les liste déroulantes de la barre d'outils */
    fixDropdownInputIssue();
    $(".taskDetailsSection .modal-taskContent").focusout(function(){
        if(!($('.btn-toolbar').is(':hover'))) {
            $("#editor").attr("contenteditable", "false");
            updateTaskContent(id, $('#editor').html());
            $('.btn-toolbar').hide();
            /*$('#modalTask').modal({
                backdrop: true,
                keyboard: true
            });*/
        }
    });
}

/**
 * Lors de la fermeture de la fenêtre modale, effacer son contenu.
 */
$('#modalTask').on('hidden.bs.modal', function () {
	$('.taskDetailsSection').attr('id', '');
    /*$('.taskDetailsSection .modal-title').empty();*/
    $('.taskDetailsSection .modal-taskTitle').empty();
    $('.taskDetailsSection .modal-taskContent').empty();
    $('.taskDetailsSection .modal-taskList').empty();
});

/**
 * Lors du clic sur un bouton d'ajout de tâche, appeler la
 * fonction addTask avec l'id de la liste concernée en paramètre.
 */
$(".addTaskBtn").click(function(){
	addTask($( this ).attr('id').replace('addTaskBtn-', ''));   
});

/**
 * Lors du clic sur un bouton de suppresion de tâche, appeler la
 * fonction deleteTask avec l'id de la tâche concernée en paramètre.
 */
/*$(".deleteTaskBtn").click(function(){
	deleteTask($('.taskDetailsSection .modal').attr('id').replace("modalTask-", ''));
});*/


/**
 * Lors du clic sur une tâche, appeler la fonction getTaskDetails 
 * avec l'id de la tâche concernée en paramètre.
 */
$(".task").click(function(){
	getTaskDetails($( this ).attr('id').replace('task-', ''));
});

/**
 * Permet de cliquer dans un champ texte étant dans une liste déroulante,
 * sans que la liste ne se ferme en perdant le focus.
 */
function fixDropdownInputIssue() {
  $('.dropdown-menu input').click(function(e) {
    e.stopPropagation();
    });
}

function preventEnterKey(obj) {
    obj.keypress(function(event){
        if (event.keyCode == 10 || event.keyCode == 13) 
            event.preventDefault();
    });
}
