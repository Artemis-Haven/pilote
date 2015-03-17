var io = require('socket.io').listen(8010);

var connectedClients = {
	connected : [],
	add : function (newConnected) {
		if (typeof newConnected === 'object'
			&& newConnected.phpUserId) {
			this.connected.push(newConnected);
		};
	},
	remove : function (id) {
		var conn = [];
		this.connected.forEach(function (data) {
			if (data.phpUserId != id) {
				conn.push(data);
			};
		});
		this.connected = conn;
	},
	getIdList : function () {
		var conn = [];
		this.connected.forEach(function (data) {
			conn.push(data.phpUserId);
		})
		return conn;
	},
	printIdList : function () {
    	console.log("Socket.IO connected clients :")
		this.connected.forEach(function (data) {
        	console.log("- " + data.phpUserId);
		})
	},
	getSocketsForId : function (id) {
		var conn = [];
		this.connected.forEach(function (data) {
			if (data.phpUserId == id) {
				conn.push(data);
			};
		});
		return conn;
	},
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

io.sockets.on('connection', function (socket) {
    socket.emit('connect');
    socket.on('sendUserData', function (data) { 
    	console.log('connection');
    	socket.phpUserId = data.userId;
    	socket.page = data.page;
    	socket.boardId = data.boardId;
    	connectedClients.add(socket);
    });
    
    socket.on('simple-notification', function (data) { 
    	console.log('simple-notification');
    	for (var i = 0; i < data.users.length; i++) {
    		var clients = connectedClients.getSocketsForId(data.users[i]);
    		for (var j = 0; j < clients.length; j++) {
    			clients[j].emit('notification', data.html);
    		};
    	};
    	
    });
    
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

    socket.on('disconnect', function () { 
    	console.log('disconnect');
    	connectedClients.remove(socket.phpUserId);
        //console.log('disconnected');
    });

});
