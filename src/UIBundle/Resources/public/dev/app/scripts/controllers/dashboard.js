'use strict';

function dashboardCtrl($sce, ConfigService, Application,  $location, $localStorage) {

    var ctrl = this;
    var connectedUser = $localStorage.user;

    if (!connectedUser || !connectedUser.email)
    {
        $location.path('/settings');
    }
    else {
        Application.findAll()
            .success(function(data) {
                console.warn('application' , data);
                if (!data.length )
                {
                    $location.path('/dashboards/add');
                    console.log("create one application");
                }
            })
            .error(function(error) {
                console.error(error);
            });
        ctrl.dashboardLink = $sce.trustAsResourceUrl(ConfigService.getUrl("app/kibana#/dashboard/0827af30-5895-11e8-a683-7fb93ba4982c"));

    }
};

function formDashboardCtrl() {

};

angular
    .module('leadwireApp')
    .controller('dashboardCtrl', ['$sce', 'ConfigService', 'Application', '$location', '$localStorage', dashboardCtrl])
    .controller('formDashboardCtrl',[formDashboardCtrl ] );