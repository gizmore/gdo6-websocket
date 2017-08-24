'use strict';
angular.module('gdo6').
controller('GDOWSNavbarCtrl', function($rootScope, $scope, GDOWebsocketSrvc, GDOErrorSrvc) {
	$scope.data.connection = {
		state: false,
	};
	
	$scope.connect = function() {
		console.log('GDOWSNavbarCtrl.connect()');
		GDOWebsocketSrvc.connect()['catch']($scope.connectionFailed);
	};
	
	$rootScope.$on('gws-ws-open', function(event) {
		console.log('GDOWSNavbarCtrl.$on-gws-ws-open()', event);
		$scope.data.connection.state = true;
	});
	$rootScope.$on('gws-ws-disconnect', function(event) {
		$scope.data.connection.state = false;
	});
	
	$scope.connectionFailed = function(error) {
		GDOErrorSrvc.showError('Cannot connect to websocket server.', 'Websocket');
	};
	
});
