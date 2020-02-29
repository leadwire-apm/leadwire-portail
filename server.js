var dotenv     = require('dotenv');
dotenv.config();
var fs = require('fs');
var https = require('https');
var server = https.createServer({
        key: fs.readFileSync(process.env.SSL_KEY),
        cert: fs.readFileSync(process.env.SSL_CERT),
        //ca: fs.readFileSync(process.env.SSL_CA),
        requestCert: false,
        rejectUnauthorized: false
    }),
    io         = require('socket.io')(server),
    logger     = require('winston'),
    port       = process.env.SOCKET_IO_PORT;

io.on('connection', function (socket) {
    socket.on('broadcast', function (data) {
        if (data.event == 'heavy-operations-in-progress') {
            io.sockets.emit('heavy-operation', {
                'status': 'in-progress',
                'message': data.message,
                'user': data.user
            });
        }
        if (data.event == 'heavy-operations-done') {
            io.sockets.emit('heavy-operation', {
                'status': 'done',
                'message': data.message,
                'user': data.user
            });
        }
    });

    socket.on('disconnect', function () {
    });
});

server.listen(port);