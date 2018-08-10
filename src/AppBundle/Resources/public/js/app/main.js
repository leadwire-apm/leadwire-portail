var app = angular.module("AppModule", ["ngRoute", "ui.bootstrap"], function(
	$interpolateProvider
) {
	$interpolateProvider.startSymbol("[[");
	$interpolateProvider.endSymbol("]]");
});

app.config(function($routeProvider) {
	$routeProvider
		.when("/", {
			templateUrl: "/home.html",
			controller: "AppCtrl"
		})
		.otherwise({ redirectTo: "/" });
});

app.controller("AppCtrl", [
	"$scope",
	function($scope) {
		$scope.placeholder = {
			title: "Ahoy matey !",
			description: "Do whatever the f*** you want here",
			bsAlert : "Looks like this is working"
		};
	}
]);
