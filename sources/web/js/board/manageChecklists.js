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
        url: Routing.generate('pilote_tasker_createChecklist'),
        data: 'taskId=' + id,
        cache: false,
        success: function(data){
        	/* insérer la checklist vide */
        	$('.checklistContainer').append(data['checkList']);
            var checklistTitle = $('.checklist[data-checklistid="'+data['id']+'"] .title');
            preventEnterKey(checklistTitle);
            checklistTitle.focusout(function(e) {
                if ($(this).attr('contenteditable')=="true") {
                    setChecklistTitle(data['id']);
                }
            });
        }
    }); 
};


function renameChecklist(id) {
    /* titleBlock sera l'élément contenant le titre */
    titleBlock = $(".checklist[data-checklistid="+id+"] .title");
    /* on le rend éditable */
    titleBlock.attr("contenteditable", "true");
    /* on sauvegarde l'ancien titre au cas où */
    titleBlock.data('oldTitleText', titleBlock.text());
    titleBlock.focus();
    selectText(titleBlock);
};

function setChecklistTitle(id){
    /* titleBlock sera l'élément contenant le titre */
    titleBlock = $(".checklist[data-checklistid="+id+"] .title");
    /* récupérer la nouvelle valeur */
    newTitleText = titleBlock.text();
    $.ajax({
    /* requête AJAX */
        type: "POST",
        dataType:"json",
        url: Routing.generate('pilote_tasker_renameChecklist'),
        data: { 'checklistId' : id, 'newName' : newTitleText },
        cache: false,
        error: function(data){
            titleBlock.text(titleBlock.data('oldTitleText'));
        }
    });
    titleBlock.attr("contenteditable", "false");
}

function deleteChecklist(id) {
    if (!confirm("Êtes-vous sûrs de vouloir supprimer cette liste ?")) return false;
    $.ajax({
    /* requête AJAX */
        type: "POST",
        dataType:"json",
        url: Routing.generate('pilote_tasker_deleteChecklist'),
        data: { 'checklistId' : id },
        cache: false,
        success: function(data){
            $(".checklist[data-checklistid="+id+"]").remove();
        }
    });
};

/**
 * Ajouter une option de checklist dans la BdD et, en cas de succès, l'ajouter 
 * aussi dans la checklist appropriée.
 *
 * @param {number} id L'identifiant de la checklist dans lequel ajouter
 * l'option.
 */
function addChecklistOption(id) {
	/* Requête AJAX */
    $.ajax({
        type: "POST",
        dataType:"json",
        url: Routing.generate('pilote_tasker_createChecklistOption'),
        data: 'checklistId=' + id,
        cache: false,
        success: function(data){
            var optionsContainer = $('.checklist[data-checkListId='+id+'] .checkListOptionContainer');
        	/* insérer l'option */
            optionsContainer.append(data['checkListOption']);
            var optionText = $('.checkbox[data-checklistOption="'+data['id']+'"] .optionText', optionsContainer);
            preventEnterKey(optionText);
            optionText.focusout(function(e) {
                if ($(this).attr('contenteditable')=="true") {
                    setChecklistOptionText(data['id']);
                }
            });
        }
    }); 
};


function renameChecklistOption(id) {
    /* textBlock sera l'élément contenant le titre */
    textBlock = $(".checkbox[data-checklistoption="+id+"] .optionText");
    /* on le rend éditable */
    textBlock.attr("contenteditable", "true");
    /* on sauvegarde l'ancien titre au cas où */
    textBlock.data('oldText', textBlock.text());
    textBlock.focus();
    selectText(textBlock);
};

function setChecklistOptionText(id){
    /* textBlock sera l'élément contenant le titre */
    textBlock = $(".checkbox[data-checklistoption="+id+"] .optionText");
    /* récupérer la nouvelle valeur */
    newText = textBlock.text();
    $.ajax({
    /* requête AJAX */
        type: "POST",
        dataType:"json",
        url: Routing.generate('pilote_tasker_renameChecklistOption'),
        data: { 'checklistOptionId' : id, 'newName' : newText },
        cache: false,
        error: function(data){
            textBlock.text(textBlock.data('oldText'));
        }
    });
    textBlock.attr("contenteditable", "false");
}


function toggleChecklistOption(id) {
    var checkbox = $(".checkbox[data-checklistoption="+id+"] input[type=checkbox]");
    var value = checkbox.is(':checked');
    $.ajax({
    /* requête AJAX */
        type: "POST",
        dataType:"json",
        url: Routing.generate('pilote_tasker_toggleChecklistOption'),
        data: { 'checklistOptionId' : id, 'value' : value },
        cache: false,
        error: function(data){
            checkbox.prop( "checked", !value );
        }
    });
};


function deleteChecklistOption(id) {
    $.ajax({
    /* requête AJAX */
        type: "POST",
        dataType:"json",
        url: Routing.generate('pilote_tasker_deleteChecklistOption'),
        data: { 'optionId' : id },
        cache: false,
        success: function(data){
            $(".checkbox[data-checklistoption="+id+"]").remove();
        }
    });
};