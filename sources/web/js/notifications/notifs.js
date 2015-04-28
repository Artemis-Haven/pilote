/*

Copyright (C) 2015 Rémi Patrizio

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
 * Gestion des notifications côté clients
 *
 * Connexion au serveur Node.JS, affichage des notifications à l'écran
 * et mise à jour de la vue en cas de modifications.
 */

$.fn.exists = function () {
    return this.length !== 0;
}

/**
 * Connection au serveur Node.JS
 * @param  userId    : Identifiant de session PHP de l'utilisateur courant
 * @param  connexion : URL et port de connexion au serveur Node.JS
 * @param  page      : Type de la page sur laquelle est l'utilisateur (ex : 'calendar')
 * @param  boardId   : Id du board sur lequel est l'utilisateur (s'il y en a un)
 */
function notifServerConnection (userId, connexion, page, boardId) {
	var socket = io(connexion);

	// Connexion au serveur
	socket.on('connect', function () {
		socket.emit('sendUserData', {
			'userId': userId,
			'page' : page,
			'boardId' : boardId
		});
		$("#nodejsState").text("Actif").addClass("text-success").removeClass("text-muted");
	});

	// Réception d'une notification du serveur
	socket.on('notification', function (data) {
		displayNotification(data, true);
	});

	// Réception d'un nouveau message du serveur
	socket.on('newMessage', function (data) {
		if ( $('#messagesList[data-thread="'+data.threadId+'"]').exists() ) {
            $("#messagesList").append(data.htmlMessage);
            $("#messagesList").scrollTop($("#messagesList")[0].scrollHeight);
		} else {
			displayNotification(data.htmlNotification, false);
		}
	});

	// Application des modifications de la vue du Board
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

/**
 * Affichage d'une notification dans la vue et, si
 * storeInMenu vaut TRUE, ajout dans le menu des notifications
 * @param  data        : Données de la notification
 * @param  storeInMenu : vaut TRUE s'il faut l'ajouter au menu
 * des notifications, FALSE sinon
 */
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

// Au clic sur le bouton "Afficher plus de notifications"
// du menu des notifications, charger les 5 suivantes.
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

// Au clic sur le bouton "Supprimer toutes les notifications"
// du menu des notifications, effectuer l'action
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

// A l'affichage du menu des notifications, considérer les 
// notifs affichées comme étant lues
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

/**
 * Supprimer la notification concernée, à la fois
 * dans la vue et dans la base de données
 * @param  notif : le DIV de la notification
 */
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