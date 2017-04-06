var testApp = angular.module('testApp', [
	'ngRoute',
	'ngCookies',
	'appctrlers',
	'appServices'
])
.config(function($routeProvider, $locationProvider) {
	$routeProvider.
	when('/login',{
		templateUrl: 'login.html',
		controller: 'LoginCtrler'
	})
	.when('/AddItem',{
		templateUrl: 'app.html',
		controller: 'appCtrler'
	})
	.when('/',{
		templateUrl: 'list.html',
		controller: 'listCtrler'
	}).when('/ListItems',{
		templateUrl: 'list.html',
		controller: 'listCtrler'
	});
});