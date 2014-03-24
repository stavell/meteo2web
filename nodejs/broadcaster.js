var BroadcastClients = {};

webSocketServer = new require('ws').Server;
var wss = new webSocketServer({port: 10080});

wss.on('connection', function(ws) {
    ws.on('message', function(message) {
        //todo: unsubscribe
        BroadcastClients[message] = BroadcastClients[message] || [];
        BroadcastClients[message].push(this);
        ws.send("Registered as user "+BroadcastClients[message].length+" for ["+message+"]");
    });
});

var webServer = new require('http').createServer(function (req, res) {
    if(res.socket.remoteAddress == '127.0.0.1' && req.method == 'POST') {

        var fullBody = '';
        req.on('data', function(chunk) {
            fullBody += chunk.toString();
        });

        req.on('end', function() {
            res.writeHead(200, "OK", {'Content-Type': 'text/html'});
            res.end();

            processNotification(req, fullBody);
        });
    }
});

webServer.listen(8001);

var processNotification = function(request, data) {
    for(var group in BroadcastClients){
        for(var client in BroadcastClients[group]) {
            BroadcastClients[group][client].send(data);
        }
    }
}
