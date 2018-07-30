'use strict';

function dashboardCtrl($sce, ConfigService, Application,  $location, $localStorage, $modal, $ocLazyLoad) {

    var ctrl = this;
    var connectedUser = $localStorage.user;

    if (!connectedUser || !connectedUser.email)
    {
        $ocLazyLoad.load({
            name: 'sbAdminApp',
            files: [ 'bundles/ui/app/scripts/controllers/settings.js']
        }).then(function() {
            $modal.open({
                //ariaLabelledBy: 'modal-title',
                //ariaDescribedBy: 'modal-body',
                templateUrl: 'bundles/ui/app/views/profile.html',
                controller: 'settingsCtrl',
                controllerAs: 'ctrl',
                resolve: {
                    isModal: function () {
                        return true;
                    }
                }
            });
        });
    }

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
};

function formDashboardCtrl() {

};

angular
    .module('leadwireApp')
    .controller('dashboardCtrl', ['$sce', 'ConfigService', 'Application', '$location', '$localStorage', '$modal', '$ocLazyLoad', dashboardCtrl])
    .controller('formDashboardCtrl',[formDashboardCtrl ] );