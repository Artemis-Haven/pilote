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
 * Serveur Node.JS
 * Il sert à faire circuler les notifications entre le serveur PHP
 * et les clients
 */

var io = require('socket.io').listen(8010);

// Cette variable contient l'ensemble des clients connectés
var connectedClients = {
	connected : [],
	// Ajouter un client à la liste
	add : function (newConnected) {
		if (typeof newConnected === 'object'
			&& newConnected.phpUserId) {
			this.connected.push(newConnected);
		};
	},
	// Supprimer un client de la liste
	remove : function (id) {
		var conn = [];
		this.connected.forEach(function (data) {
			if (data.phpUserId != id) {
				conn.push(data);
			};
		});
		this.connected = conn;
	},
	// Renvoie la liste des identifiants des sessions PHP
	// des clients connectés
	getIdList : function () {
		var conn = [];
		this.connected.forEach(function (data) {
			conn.push(data.phpUserId);
		})
		return conn;
	},
	// Affiche en console la liste des identifiants des 
	// sessions PHP des clients connectés
	printIdList : function () {
    	console.log("Socket.IO connected clients :")
		this.connected.forEach(function (data) {
        	console.log("- " + data.phpUserId);
		})
	},
	// Prend un identifiant de session PHP en paramètre
	// et renvoie l'utilisateur connecté correspondant
	getSocketsForId : function (id) {
		var conn = [];
		this.connected.forEach(function (data) {
			if (data.phpUserId == id) {
				conn.push(data);
			};
		});
		return conn;
	},
	// Renvoie la liste des utilisateurs connectés
	// qui sont sur la bonne page et sur le bon Board
	getSocketsForPageAndBoard : function (page, board) {
		var conn = [];
		this.connected.forEach(function (data) {
			if (data.page == page && data.boardId == board) {
				conn.push(data);
			};
		});
		return conn;
	}
}

// A la connexion d'un client
io.sockets.on('connection', function (socket) {
    socket.emit('connect');
    // Le client envoie des données permettant de l'identifier plus tard
    socket.on('sendUserData', function (data) { 
    	console.log('connection');
    	socket.phpUserId = data.userId;
    	socket.page = data.page;
    	socket.boardId = data.boardId;
    	connectedClients.add(socket);
    });
    
    // Lorsque le serveur PHP envoie une "simple-notification", on la renvoie
    // à tous les clients concernés.
    socket.on('simple-notification', function (data) { 
    	console.log('simple-notification');
    	for (var i = 0; i < data.users.length; i++) {
    		var clients = connectedClients.getSocketsForId(data.users[i]);
    		for (var j = 0; j < clients.length; j++) {
    			clients[j].emit('notification', data.html);
    		};
    	};
    	
    });
    
    // Lorsque le serveur PHP envoie une notif "newMessage", on a renvoie à 
    // tous les utilisateurs concernés
    socket.on('newMessage', function (data) { 
    	console.log('newMessage');
    	for (var i = 0; i < data.users.length; i++) {
    		var clients = connectedClients.getSocketsForId(data.users[i]);
    		for (var j = 0; j < clients.length; j++) {
    			clients[j].emit('newMessage', data);
    			console.log('Message envoyé vers client : ' + data.users[i] + " " + clients[j].phpUserId);
    		};
    	};
    	
    	connectedClients.printIdList();
    });
    
    // Lorsqu'une tâche est déplacée sur un board, on répercute la modifications
    // chez les clients ayant la même page affichée
    socket.on('move-task', function (data) {
    	console.log('move-task');
		var boardClients = connectedClients.getSocketsForPageAndBoard('board', data.boardId);
		for (var i = 0; i < boardClients.length; i++) {
			if (boardClients[i].phpUserId != data.sender) {
				boardClients[i].emit('board-move-task', {
					'taskId': data.taskId, 
					'tListId': data.tListId, 
					'upperTaskId': data.upperTaskId
				});
    		}
    	};
    });
    
    // Lorsqu'une tList est déplacée sur un board, on répercute la modifications
    // chez les clients ayant la même page affichée
    socket.on('move-tlist', function (data) {
    	console.log('move-tlist');
		var boardClients = connectedClients.getSocketsForPageAndBoard('board', data.boardId);
		for (var i = 0; i < boardClients.length; i++) {
			if (boardClients[i].phpUserId != data.sender) {
				boardClients[i].emit('board-move-tlist', {
					'tListId': data.tListId, 
					'stepId': data.stepId, 
					'leftListId': data.leftListId
				});
    		}
    	};
    });

    // Lorsqu'une tâche est renommée sur un board, on répercute la modifications
    // chez les clients ayant la même page affichée
    socket.on('rename-task', function (data) {
    	console.log('rename-task');
		var boardClients = connectedClients.getSocketsForPageAndBoard('board', data.boardId);
		for (var i = 0; i < boardClients.length; i++) {
			if (boardClients[i].phpUserId != data.sender) {
				boardClients[i].emit('board-rename-task', {
					'taskId': data.taskId, 
					'title': data.title
				});
			}
    	};
    });
    
    // Lorsqu'une tList est renommée sur un board, on répercute la modifications
    // chez les clients ayant la même page affichée
    socket.on('rename-tlist', function (data) {
    	console.log('rename-tlist');
		var boardClients = connectedClients.getSocketsForPageAndBoard('board', data.boardId);
		for (var i = 0; i < boardClients.length; i++) {
			if (boardClients[i].phpUserId != data.sender) {
				boardClients[i].emit('board-rename-tlist', {
					'tListId': data.tListId, 
					'title': data.title
				});
			}
    	};
    });
    
    // Lorsqu'une étape est renommée sur un board, on répercute la modifications
    // chez les clients ayant la même page affichée
    socket.on('rename-step', function (data) {
    	console.log('rename-step');
		var boardClients = connectedClients.getSocketsForPageAndBoard('board', data.boardId);
		for (var i = 0; i < boardClients.length; i++) {
			if (boardClients[i].phpUserId != data.sender) {
				boardClients[i].emit('board-rename-step', {
					'stepId': data.stepId, 
					'title': data.title
				});
			}
    	};
    });
    
    // Lorsqu'une tâche est ajoutée sur un board, on répercute la modifications
    // chez les clients ayant la même page affichée
    socket.on('new-task', function (data) {
    	console.log('new-task');
		var boardClients = connectedClients.getSocketsForPageAndBoard('board', data.boardId);
		for (var i = 0; i < boardClients.length; i++) {
			if (boardClients[i].phpUserId != data.sender) {
				boardClients[i].emit('board-new-task', {
					'tListId': data.tListId, 
					'taskThumbnail': data.taskThumbnail
				});
    		}
    	};
    });
    
    // Lorsqu'une tList est ajoutée sur un board, on répercute la modifications
    // chez les clients ayant la même page affichée
    socket.on('new-tlist', function (data) {
    	console.log('new-tlist');
		var boardClients = connectedClients.getSocketsForPageAndBoard('board', data.boardId);
		for (var i = 0; i < boardClients.length; i++) {
			if (boardClients[i].phpUserId != data.sender) {
				boardClients[i].emit('board-new-tlist', {
					'stepId': data.stepId, 
					'tList': data.tList, 
					'tListId': data.tListId
				});
    		}
    	};
    });
    
    // Lorsqu'une tâche est supprimée sur un board, on répercute la modifications
    // chez les clients ayant la même page affichée
    socket.on('remove-task', function (data) {
    	console.log('remove-task');
		var boardClients = connectedClients.getSocketsForPageAndBoard('board', data.boardId);
		for (var i = 0; i < boardClients.length; i++) {
			if (boardClients[i].phpUserId != data.sender) {
				boardClients[i].emit('board-remove-task', {
					'taskId': data.taskId
				});
    		}
    	};
    });
    
    // Lorsqu'une tList est supprimée sur un board, on répercute la modifications
    // chez les clients ayant la même page affichée
    socket.on('remove-tlist', function (data) {
    	console.log('remove-tlist');
		var boardClients = connectedClients.getSocketsForPageAndBoard('board', data.boardId);
		for (var i = 0; i < boardClients.length; i++) {
			if (boardClients[i].phpUserId != data.sender) {
				boardClients[i].emit('board-remove-tlist', {
					'tListId': data.tListId
				});
    		}
    	};
    });

    // Déconnexion du client
    socket.on('disconnect', function () { 
    	console.log('disconnect');
    	connectedClients.remove(socket.phpUserId);
        //console.log('disconnected');
    });

});
