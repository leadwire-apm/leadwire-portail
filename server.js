var server     = require('http').createServer('localhost'),
    io         = require('socket.io')(server),
    logger     = require('winston'),
    port       = 8000;

io.on('connection', function (socket) {
    socket.on('broadcast', function (data) {
        if (data.event == 'heavy-operations-in-progress') {
            io.sockets.emit('heavy-operation', {
                'status': 'in-progress',
                'message': data.message
            });
        }
        if (data.event == 'heavy-operations-done') {
            io.sockets.emit('heavy-operation', {
                'status': 'done',
                'message': data.message
            });
        }
    });

    socket.on('disconnect', function () {
    });
});

server.listen(port);