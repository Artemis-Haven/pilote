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
        url: Routing.generate('pilote_tasker_createTask'),
        data: 'tListId=' + id,
        cache: false,
        success: function(data){
        	/* ajouter la tâche à sa place */
			$(data.taskThumbnail).insertBefore( "#addTaskBtn-" + id );
			/* rendre triable la nouvelle tâche */
			setSortableTask();
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
    route = Routing.generate('pilote_tasker_getTaskDetails');
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

            /* Changer le titre de la tâche lorsque l'on clique ailleurs */
            $(".modal-taskTitle b").focusout(function (e) {
                if ($(this).attr('contenteditable')=="true") 
                    setTaskTitle(id);
            });

            /* Lorsque l'on quitte l'éditeur (à moins que ce se soit pour cliquer
                sur un élément de la barre d'outils), on quitte le mode d'édition */
            $(".taskDetailsSection .modal-taskContent").focusout(function (e){
                if( !($('.btn-toolbar').is(':hover')) ) {
                    $("#editor").attr("contenteditable", "false");
                    updateTaskContent(id, $('#editor').html());
                    $('.btn-toolbar').hide();
                }
            });

            /* Changer le titre de la case à cocher lorsque l'on clique ailleurs */
            $(".checkbox .optionText").focusout(function(e) {
                if ($(this).attr('contenteditable')=="true") {
                    setChecklistOptionText($(this).parent().data('checklistoption'));
                }
            });

            /* Changer le titre de la checklist lorsque l'on clique ailleurs */
            $(".checklist .title").focusout(function(e) {
                if ($(this).attr('contenteditable')=="true") {
                    setChecklistTitle($(this).closest(".checklist").data('checklistid'));
                }
            });

            preventEnterKey($(".modal-taskTitle b"));
            preventEnterKey($(".checklist .title"));
            preventEnterKey($(".checkbox .optionText"));
            preventEnterKey($('.taskList > .tList-heading > p'));
            preventEnterKey($('.stepTitle'));

            /*******************/
            /*   PROGRESSION   */
            /*******************/

            /* Charger le SLIDER pour la PROGRESSION (même s'il est caché) */
            $( "#progressSlider" ).slider({
                value: $("#progressBtn").data("progress"),
                range: "min",
                min: 0,
                max: 100,
                step: 10,
                slide: function( event, ui ) {
                    /* A chaque changement, màj la valeur affichée */
                    $( "#progressValue" ).html( ui.value );
                    $('#progressBtn').data('progress', ui.value);
                },
                stop: function( event, ui ) {
                    /* MàJ la valeur dans la base de données */
                    setProgress(id, ui.value);
                }
            });

            /* Lors du choix d'un fichier (bouton JOINDRE UN FICHIER), valider
            automatiquement le formulaire */
            /* Lors de la validation du formulaire JOINDRE UN FICHIER, envoyer
            ce formulaire par une requête AJAX au lieu de rafraîchir la page */
            uploadFile(id);

            /* Générer les DatePickers */
            createDatePickers(id);
        }
    }); 
};


function renameTask(id) {
    /* titleBlock sera l'élément contenant le titre */
    var titleBlock = $(".modal-taskTitle b");
    if (titleBlock.attr('contenteditable') != 'true') {
        /* on le rend éditable */
        titleBlock.attr("contenteditable", "true");
        /* on sauvegarde l'ancien titre au cas où */
        titleBlock.data('oldTitleText', titleBlock.text());
        titleBlock.focus();
        selectText(titleBlock);
        titleBlock.keydown(function(e){ limitCharCount(titleBlock, 60, e); });
    };
};

function setTaskTitle(id){
    /* titleBlock sera l'élément contenant le titre */
    var titleBlock = $(".modal-taskTitle b");
    /* récupérer la nouvelle valeur */
    var newTitleText = titleBlock.text();
    /* si la nouvelle valeur est vide, on remet l'ancienne valeur */
    if (newTitleText.replace(" ", "").length < 2) {
        titleBlock.text(titleBlock.data('oldTitleText'));
    } else {
        $.ajax({
        /* requête AJAX */
            type: "POST",
            dataType:"json",
            url: Routing.generate('pilote_tasker_renameTask'),
            data: { 'taskId' : id, 'newTitle' : newTitleText },
            cache: false,
            success: function(data){
                /* met à jour la miniature de la tâche */
                $('#task-'+id+' .task-header').text(newTitleText);

                // A faire juste sur la page du Gantt
                if (typeof gantt != 'undefined') {
                    gantt.getTask("t"+id).text = newTitleText;
                    gantt.refreshTask("t"+id);
                };
                if ($('#calendarView').length) {
                    calTask = $('#calendarView').fullCalendar('clientEvents', id)[0];
                    calTask.title = newTitleText;
                    $('#calendarView').fullCalendar('updateEvent', calTask);
                }
            },
            error: function(data){
                titleBlock.text(titleBlock.data('oldTitleText'));
            }
        });
    }
    /* transformer le champ texte en paragraphe */
    titleBlock.attr("contenteditable", "false");
}

/**
 * Supprimer une tâche dans la BdD et, en cas de succès, la supprimer
 * aussi dans la liste appropriée.
 *
 * @param {number} id L'identifiant de la tâche concernée
 */
function deleteTask(id) {
    if (!confirm("Êtes-vous sûrs de vouloir supprimer cette tâche ?")) return false;
	/* requête AJAX */
    $.ajax({
        type: "POST",
        url: Routing.generate('pilote_tasker_deleteTask'),
        data: 'taskId=' + id,
        cache: false,
        success: function(data){
        	/* fermer la fenêtre */
        	$('#modalTask').modal('hide');
        	/* supprimer la tâche */
			$('#task-'+id).remove();

            // A faire juste sur la page du Gantt
            if (typeof gantt != 'undefined') {
                gantt.deleteTask("t"+id);
            };
        }
    }); 
};

function updateTaskContent(id, newContent) {
    $.ajax({
    /* requête AJAX */
        type: "POST",
        url: Routing.generate('pilote_tasker_updateTaskContent'),
        data: { 'taskId' : id, 'newContent' : newContent },
        cache: false,
    });
};

function setContentZoneEditable(id) {
    /* Rend la zone éditable */
    $("#editor").wysiwyg();
    $("#editor").focus();
    $('.btn-toolbar').show();
    /* Corrige un bug avec les liste déroulantes de la barre d'outils */
    fixDropdownInputIssue();
}

/**
 * Lors de la fermeture de la fenêtre modale, effacer son contenu.
 */
$('#modalTask').on('hidden.bs.modal', function () {
	$('.taskDetailsSection').attr('id', '');
    $('.taskDetailsSection .modal-taskTitle').empty();
    $('.taskDetailsSection .modal-taskList').empty();
    $('.taskDetailsSection .modal-body article').empty();
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
        if (event.keyCode == 10 || event.keyCode == 13) {
            event.preventDefault();
            obj.focusout();
            return false;
        }
    });
}
preventEnterKey($('.taskList > .tList-heading > p'));
preventEnterKey($('.stepTitle'));

function limitCharCount(block, max, e) {   
    if(e.which != 8 && e.keyCode != 10 && e.keyCode != 13 && block.text().length > max)
    {
       e.preventDefault();
    }
}

function selectText(elt){
   var doc = document;
   var element = elt[0];
   console.log(elt, element);
   if (doc.body.createTextRange) {
       var range = document.body.createTextRange();
       range.moveToElementText(element);
       range.select();
   } else if (window.getSelection) {
       var selection = window.getSelection();        
       var range = document.createRange();
       range.selectNodeContents(element);
       selection.removeAllRanges();
       selection.addRange(range);
   }
};

/**
 * Permet d'assigner un utilisateur à une tâche 
 */
function assign(taskId, memberId) {
    $.ajax({
        type: "POST",
        dataType:"json",
        url: Routing.generate('pilote_tasker_assign'),
        data: { 'taskId' : taskId, 'memberId' : memberId },
        cache: false,
        success: function(data){
            $('#assignBtnName').html(data.name);
            $('#task-'+taskId+' .taskInfos').html(data.infos);
        }
    }); 
};

/**
 * Affiche ou masque la zone de la barre de progression.
 * On remet le compteur à zéro lorsqu'on le désactive.
 */
function toggleProgress (taskId) {
    var activate = ( $('#progressBtn').data('progressactivated') == false );

    $.ajax({
        type: "POST",
        dataType:"json",
        url: Routing.generate('pilote_tasker_progress_activate'),
        data: { 'taskId' : taskId, 'activate' : activate },
        cache: false,
        success: function(data){
            if (activate) {
                $('#progressSliderContainer').show();
                $('#progressBtn').data('progressactivated', "1");
            } else {
                $('#progressSliderContainer').hide();
                $('#progressBtn').data('progressactivated', "0");
                $('#progressBtn').data('progress', 0);
                $( "#progressSlider" ).slider( "option", "value", 0 );
                $( "#progressValue" ).html( 0 );

                // A faire juste sur la page du Gantt
                if (typeof gantt != 'undefined') {
                    gantt.getTask("t"+taskId).progress = 0;
                    gantt.refreshTask("t"+taskId);
                }
            }
            $('#task-'+taskId+' .taskInfos').html(data.infos);
        }
    });

};

/**
 * Envoyer au serveur la nouvelle valeur de la progression d'une tâche
 */
function setProgress (taskId, value) {
    $.ajax({
        type: "POST",
        dataType:"json",
        url: Routing.generate('pilote_tasker_progress_update'),
        data: { 'taskId' : taskId, 'value' : value },
        cache: false,
        success: function (data) {
            // A faire juste sur la page du Gantt
            if (typeof gantt != 'undefined') {
                gantt.getTask("t"+taskId).progress = value/100;
                gantt.refreshTask("t"+taskId);
            }
            $('#task-'+taskId+' .taskInfos').html(data.infos);
        }
    });
}

/**
 * Formulaire d'upload de pièce jointe à une tâche.
 * Valider le formulaire dès le choix du fichier.
 * Le formulaire est validé en AJAX (avec un plugin jQuery)
 * et la vue est mise à jour si tout s'est bien passé
 */
function uploadFile (taskId) {
    $("#fileUpload > input[type=file]").change(function(){
        $('#fileUpload').submit();
    });
    $('#fileUpload').ajaxForm({
        data: { 'taskId': taskId}, 
        dataType: 'json',
        success: function(data){
            $("#fileUploadGroup").html(data.documentThumbnail);
            $('#task-'+taskId+' .taskInfos').html(data.infos);
        }
    });
}

/**
 * Supprimer la pièce jointe à la tâche
 */
function deleteFile (taskId) {
    if (confirm("Voulez-vous vraiment supprimer cette pièce jointe ?")) {
        $.ajax({
            type: "POST",
            dataType:"json",
            url: Routing.generate('pilote_tasker_fileUpload_delete'),
            data: { 'taskId' : taskId},
            cache: false,
            success: function(data){
                $("#fileUploadGroup").html(data.documentThumbnail);
                uploadFile(taskId);
                $('#task-'+taskId+' .taskInfos').html(data.infos);
            }
        });
    };
}

/**
 * Réglages et traductions pour les DatePickers.
 * Utilisé pour les dates de début et de fin des tâches
 */
$.datepicker.setDefaults({
    defaultDate: "+1w",
    numberOfMonths: 2,
    regional: "fr",
    dateFormat: "dd/mm/yy",
    firstDay: 1, 
    monthNamesShort: [ "Jan", "Fév", "Mar", "Avr", "Mai", "Jui", "Jui", "Aoû", "Sep", "Oct", "Nov", "Déc" ],
    monthNames: [ "Janvier", "Février", "Mars", "Avril", "Mai", "Juin", "Juillet", "Août", "Septembre", "Octobre", "Novembre", "Décembre" ],
    dayNames: [ "Dimanche", "Lundi", "Mardi", "Mercredi", "Jeudi", "Vendredi", "Samedi" ],
    dayNamesMin: [ "Di", "Lu", "Ma", "Me", "Je", "Ve", "Sa" ],
    closeText: 'Fermer',
    prevText: 'Précédent',
    nextText: 'Suivant',
    currentText: 'Aujourd\'hui',
    showButtonPanel: true,
});

/**
 * Générer les DatePickers pour les dates de début et de fin des tâches.
 * Si la date de fin est nulle, alors la date de début est désactivée.
 */
function createDatePickers (taskId) {
    $( "#startDate" ).datepicker({
        onClose: function( selectedDate ) {
            $( "#endDate" ).datepicker( "option", "minDate", selectedDate );
            setStartEndDates(taskId, selectedDate, $("#endDate").val());
        }
    });
    $( "#endDate" ).datepicker({
        onClose: function( selectedDate ) {
            $( "#startDate" ).datepicker( "option", "maxDate", selectedDate );
            if (selectedDate=="") {
                $( "#startDate" ).prop('disabled', true);
                $( "#startDate" ).val('');
                $( "#startDate" ).parent().addClass("disabled");
                setStartEndDates(taskId, null, null);
            } else {
                $( "#startDate" ).prop('disabled', false);
                $( "#startDate" ).parent().removeClass("disabled");
                setStartEndDates(taskId, $("#startDate").val(), selectedDate);
            }
        }
    });
    $("#ui-datepicker-div").wrap('<div class="datepickersDiv" />');
}

/**
 * Afficher ou masquer la zone des dates de début et de fin.
 * Les dates sont mises à NULL lorsqu'on les désactive.
 */
function toggleDates(id) {
    var activate = ( $('#toggleDatesBtn').data('datesactivated') == false );
    if (activate) {
        $('#datepickersContainer').show();
        $('#toggleDatesBtn').data('datesactivated', "1");
        $( "#startDate" ).val("");
        $( "#endDate" ).val("");
    } else {
        $('#datepickersContainer').hide();
        $('#toggleDatesBtn').data('datesactivated', "0");
        setStartEndDates(id, null, null);
    }
}

/**
 * Envoyer au serveur les nouvelles valeurs des dates de début et de fin
 */
function setStartEndDates (taskId, startDate, endDate) {
    $.ajax({
        type: "POST",
        dataType:"json",
        url: Routing.generate('pilote_tasker_setDates'),
        data: { 'taskId' : taskId, 'startDate': startDate, 'endDate': endDate},
        cache: false,
        success: function (data) {
            // A faire juste sur la page du Gantt
            if (typeof gantt != 'undefined') {
                var ganttTask = gantt.getTask("t"+taskId);
                if (startDate == null && endDate == null) {
                    // En cas de suppression des dates, 
                    // on supprime la tâche du Gantt
                    gantt.removeTask("t"+taskId);
                } else if (startDate == "") {
                    // Si la tâche est/devient un jalon
                    var ganttStartDate = gantt.date.parseDate(endDate,"%d/%m/%Y");
                    ganttTask.progress = 0;
                    ganttTask.type = "myMilestone";
                } else {
                    // Si la tâche est/devient une simple tâche
                    var ganttStartDate = gantt.date.parseDate(startDate,"%d/%m/%Y");
                    if (ganttTask.type = "myMilestone") {
                        ganttTask.type = "task";
                        ganttTask.progress = $('#progressBtn').data('progress')/100;
                    };
                }
                var ganttEndDate = gantt.date.add(gantt.date.parseDate(endDate,"%d/%m/%Y"), 1, 'day');
                ganttTask.start_date = ganttStartDate;
                ganttTask.end_date = ganttEndDate;
                gantt.refreshTask("t"+taskId);
            }
            $('#task-'+taskId+' .taskInfos').html(data.infos);
        },
        error: function() {
            if (startDate != null) $( "#startDate" ).val('');
            if (endDate != null) $( "#endDate" ).val('');
        }
    });
}

function setLabel (taskId, key, color, text) {
    $.ajax({
        type: "POST",
        dataType:"json",
        url: Routing.generate('pilote_tasker_label'),
        data: { 'taskId' : taskId, 'label' : key},
        cache: false,
        success: function(data){
            $('#labelBtn .glyphicon').css('color', color);
            if (color == "#333333") {
                $('#task-'+taskId+' .infoLabel').hide();
            } else {
                $('#task-'+taskId+' .infoLabel').show();
            }
            $('#task-'+taskId+' .infoLabel').css('background-color', color);
            $('#labelBtnName').text(text);
        }
    });
}