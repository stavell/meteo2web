
var webSocketServer = new require('ws').Server({port: 10080});



var webServer = require('http').createServer(function (req, res) {

    if(res.socket.remoteAddress == '127.0.0.1' && req.method == 'POST') {

        var fullBody = '';

        req.on('data', function(chunk) {
            fullBody += chunk.toString();
        });

        req.on('end', function() {
            res.writeHead(200, "OK", {'Content-Type': 'text/html'});
            res.end();


            notifyAll(fullBody);
        });

    }

});

server.listen(8001);



var notifyAll = function(req){
    for(var i in wss.clients) wss.clients[i].send(req);
    console.log('clients count:'+wss.clients.length)
}