var dotenv     = require('dotenv');
dotenv.config();
var server     = require('http').createServer('${process.env.APP_DOMAIN}'),
    io         = require('socket.io')(server),
    logger     = require('winston'),
    port       = process.env.SOCKET_IO_PORT;

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