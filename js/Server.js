var Server = {};

Server.requestBuffer = [];
Server.sendRequestWaitTimer = -1;
Server.sendRequestWaitTime = 100;

Server.call = function(target, params, successFn, errorFn, context) {
    var request;
    if(typeof(target)!= 'object' || target === null) {
        request = {
            target:target,
            params:params,
            successFn:successFn,
            errorFn:errorFn,
            context:context
        }
    } else {
        request = jQuery.extend({}, target);
    }
    request.params = request.params || [];
    request.successFn = request.successFn || function(){};
    request.errorFn = request.errorFn || Server.defaultErrorFn;
    request.context = request.context || {};

    Server.requestBuffer.push(request);

    if(Server.sendRequestWaitTimer != -1) {
        clearTimeout(Server.sendRequestWaitTimer);
    }

    if(request.delay === -1) {

    } else {
        request.delay = parseInt(request.delay);
        request.delay = request.delay || Server.sendRequestWaitTime;
        Server.sendRequestWaitTimer = setTimeout(function(){
            Server.sendRequests();
        }, request.delay);
    }
};

Server.sendRequests = function() {
    var sentRequestsBuffer = Server.requestBuffer;
    Server.requestBuffer = [];
    clearTimeout(Server.sendRequestWaitTimer);
    var requests = [];

    for(var i=0; i<sentRequestsBuffer.length;i++) {
        requests.push({
            target : sentRequestsBuffer[i].target,
            params : sentRequestsBuffer[i].params
        });
    }
    jQuery.ajax({
        type:'POST',
        url:BASE_URL+'api.php',
        data:{
            requests:JSON.stringify(requests)
        },
        dataType:'json',
        error:function(XMLHttpRequest, textStatus, errorThrown) {
            alert('connection error');
        },
        success:function(responses, textStatus, XMLHttpRequest) {
            if(!responses || !responses.length) alert('connection error');

            for(var i=0; i<sentRequestsBuffer.length; i++) {
                try {
                    if (responses[i].error) {
                        sentRequestsBuffer[i].errorFn.apply(sentRequestsBuffer[i].context, [responses[i].error]);
                    }
                    else {
                        sentRequestsBuffer[i].successFn.apply(sentRequestsBuffer[i].context, [responses[i].response]);
                    }
                } catch (err) {
                    if(typeof(console) != 'undefined' && typeof(console.log) == 'function') {
                        console.log(err);
                    }
                }
            }
        },
        timeout: 30000
    });

};

Server.defaultErrorFn = function(e) {
    alert("Exception: "+e.message);
}
