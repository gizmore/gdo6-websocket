var socket = null;
var queue = [];

function disconnect() {
	
}

function connect(url) {
	console.log('gdo-websocket-worker::connect()', url);
	var ws = socket = new WebSocket(url);
	ws.binaryType = 'arraybuffer';
	ws.onopen = function() {
		postMessage({cmd:'open'});
	};
	ws.onclose = function() {
		postMessage({cmd:'close'});
	};
	ws.onerror = function(event) {
		postMessage({cmd:'error'});
	};
	ws.onmessage = function(message) {
		if (message.data instanceof ArrayBuffer) {
			postMessage({cmd:'binary', message: message.data});
		}
		else {
			postMessage({cmd:'plaintext', message: message.data});
		}
	};
}
function sendBinary(message) {
	console.log('gdo-websocket-worker::sendBinary()', message);
	socket.send(message);
}
function sendJSON(message) {
	console.log('gdo-websocket-worker::sendJSON()', message);
	socket.send(message);
}
addEventListener('message', function(e) {
  var data = e.data;
  console.log(data);
  switch (data.cmd) {
  case 'connect': return connect(data.url);
  case 'disconnect': return disconnect();
  case 'binary': return sendBinary(data.message);
  case 'plaintext': return sendJSON(data.message);
}}, false);
