'use strict';

function dashboardCtrl($sce, ConfigService, Application,  $location, $localStorage, $rootScope) {

    var ctrl = this;
    var connectedUser = $localStorage.user;

    if (!connectedUser || !connectedUser.email)
    {
        $location.path('/settings');
    }

    Application.findAll()
        .success(function(data) {
            $rootScope.applications = data;
            if (data.length == 0)
            {
                $location.path('/applications/add');
                console.log("create one application");
            }
        })
        .error(function(error) {
        console.error(error);
    });
    ctrl.dashboardLink = $sce.trustAsResourceUrl(ConfigService.getUrl("app/kibana#/dashboard/0827af30-5895-11e8-a683-7fb93ba4982c"));
};

angular
    .module('leadwireApp')
    .controller('dashboardCtrl', ['$sce', 'ConfigService', 'Application', '$location', '$localStorage', '$rootScope', dashboardCtrl])
