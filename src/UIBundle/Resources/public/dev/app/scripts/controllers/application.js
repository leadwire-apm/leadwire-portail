angular.module('leadwireApp').
    controller('formApplicationCtrl',
        formApplicationCtrlFN).controller('applicationListCtrl',
    applicationListCtrlFN);

formApplicationCtrlFN.$inject = [
    '$sce',
    'ConfigService',
    'Application',
    '$location',
    '$localStorage',
    '$rootScope',
];

function formApplicationCtrlFN(
    $sce,
    ConfigService,
    Application,
    $location,
    $localStorage,
    $rootScope,
) {
    this.title = 'Hello World';
    $rootScope.currentNav = 'application';

}

function applicationListCtrlFN(
    $rootScope,
    Application
) {
    $rootScope.currentNav = 'settings';
    Application.findAll().then(function(data) {
        console.log(data)
    })

}