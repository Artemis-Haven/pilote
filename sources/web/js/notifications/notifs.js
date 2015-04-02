$.fn.exists = function () {
    return this.length !== 0;
}

function notifServerConnection (userId, connexion, page, boardId) {
	var socket = io(connexion);

	socket.on('connect', function () {
		socket.emit('sendUserData', {
			'userId': userId,
			'page' : page,
			'boardId' : boardId
		});
		$("#nodejsState").text("Actif").addClass("text-success").removeClass("text-muted");
	});

	socket.on('notification', function (data) {
		displayNotification(data, true);
	});

	socket.on('newMessage', function (data) {
		if ( $('#messagesList[data-thread="'+data.threadId+'"]').exists() ) {
            $("#messagesList").append(data.htmlMessage);
            $("#messagesList").scrollTop($("#messagesList")[0].scrollHeight);
		} else {
			displayNotification(data.htmlNotification, false);
		}
	});

	if (page=="board") {
		socket.on('board-move-task', function (data) {
			var task = $('#task-'+data.taskId).detach();
			if (data.upperTaskId < 0)
				$('#tList-'+data.tListId+' .blankTask').after(task);
			else
				$('#task-'+data.upperTaskId).after(task);
		});
		socket.on('board-move-tlist', function (data) {
			var tList = $('#tList-'+data.tListId).detach();
			if (data.leftListId < 0)
				$('#tab-'+data.stepId).prepend(tList);
			else
				$('#tList-'+data.leftListId).after(tList);
		});
		socket.on('board-rename-task', function (data) {
			var task = $('#task-'+data.taskId+' .task-header');
				task.text(data.title);
		});
		socket.on('board-rename-tlist', function (data) {
			var tList = $('#tList-'+data.tListId+' .tList-heading > p');
				tList.text(data.title);
		});
		socket.on('board-rename-step', function (data) {
			var step = $('*[data-target="#tab-'+data.stepId+'"] .stepTitle');
				step.text(data.title);
		});
		socket.on('board-new-task', function (data) {
			$('#tList-'+data.tListId+'>.sortableTasksContainer >*:last-child').before(data.taskThumbnail);
		});
		socket.on('board-new-tlist', function (data) {
			$('#tab-'+data.stepId+' >*:last-child').before(data.tList);
        	/* agrandir le Container des listes pour permettre le scroll */
        	$( "#tab-"+data.stepId ).width($("#tab-"+data.stepId+' >*').length*265);
			/* activer les boutons supprimer liste, renommer liste et ajouter tâche */
			$("#renameListBtn-"+data.tListId).click(function(){
				renameList(data.tListId);
			});
			$("#deleteListBtn-"+data.tListId).click(function(){
				deleteList(data.tListId);
			});
        	setSortableTask();
		});
		socket.on('board-remove-task', function (data) {
			$('#task-'+data.taskId).remove();
		});
		socket.on('board-remove-tlist', function (data) {
			$('#tList-'+data.tListId).remove();
        	/* agrandir le Container des listes pour permettre le scroll */
        	$( "#tab-"+data.stepId ).width($("#tab-"+data.stepId+' >*').length*265);
		});
	};
}

function displayNotification (data, storeInMenu) {
	$('#notificationsContainer').append(data);
	if (storeInMenu) {
		$('#notificationsMenu').prepend(data);
		$("#notificationsDropdown").addClass('unread');
	}
	var notif = $('#notificationsContainer > *:last-child');
	notif.fadeTo(500, 0.8).delay(5000).fadeOut(500, function () {
		notif.remove();
	});
}

$( "#loadNextNotifications" ).on('click', function (event) {
	event.stopPropagation();
	var lastNotif = $("#notificationsMenu > .notification:last");
	var lastNotifId = lastNotif.data('id');
	/* requête AJAX */
    $.ajax({
        type: "POST",
        dataType:"json",
        url: Routing.generate('pilote_main_loadNextNotifications'),
        data: 'lastNotifId=' + lastNotifId,
        cache: false,
        success: function(data){
			$(data).insertBefore( "#loadNextNotifications" );
        }
    }); 
});

$( "#removeAllNotifications" ).on('click', function (event) {
	event.stopPropagation();
	$.ajax({
        type: "POST",
        dataType:"json",
        url: Routing.generate('pilote_main_removeAllNotifications'),
        cache: false,
        success: function(data){
			$( "#notificationsMenu > *" ).remove();
			$( "#notificationsMenu").prepend('<li class="dropdown-header text-center">Aucune notification</li>');
        }
    }); 
});

$( "#notificationsDropdown" ).on('shown.bs.dropdown', function () {
	if ( $("#notificationsDropdown").hasClass('unread') ) {
		/* requête AJAX */
		$.ajax({
		    type: "POST",
		    dataType:"json",
		    url: Routing.generate('pilote_main_notificationsRead'),
		    cache: false,
		    success: function(data){
				$( "#notificationsDropdown" ).removeClass('unread');
				$( "#notificationsMenu .notification" ).attr('data-read', "1");
		    }
		});
	};
});

function removeNotification (notif) {
	var id = notif.data('id');
	$('.notification[data-id="'+id+'"]').remove();
	$.ajax({
	    type: "POST",
	    dataType:"json",
	    url: Routing.generate('pilote_main_removeNotification'),
	    data: 'id='+id,
	    cache: false,
	    success: function(data){
			if ( $( "#notificationsMenu .notification[data-read='1']" ).length == 0 ) {
				$( "#notificationsDropdown" ).removeClass('unread');
			}
	    }
	}); 
}