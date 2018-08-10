'use strict';

function dashboardCtrl(
    $sce, ConfigService, ApplicationFactory, $location, $localStorage, $modal,
    $ocLazyLoad, $rootScope) {

    var ctrl = this;
    var connectedUser = $localStorage.user;

    if (!connectedUser || !connectedUser.email) {
        console.log('i was here');
        $ocLazyLoad.load({
            name: 'sbAdminApp',
            files: [
                $rootScope.ASSETS_BASE_URL + 'scripts/controllers/settings.js'],
        }).then(function() {
            $modal.open({
                //ariaLabelledBy: 'modal-title',
                //ariaDescribedBy: 'modal-body',
                templateUrl: $rootScope.ASSETS_BASE_URL + 'views/profile.html',
                controller: 'SettingsModalCtrl',
                controllerAs: 'ctrl',
                resolve: {
                    isModal: function() {
                        return true;
                    },
                },
            });
        });
    }

    ApplicationFactory.findAll().success(function(data) {
        delete $localStorage.applications;
        $localStorage.applications = data;
        $rootScope.applications = $localStorage.applications;
        if (data.length === 0) {
            $location.path('/applications/add');
        }
    }).error(function(error) {
        console.error(error);
    });
    ctrl.dashboardLink = $sce.trustAsResourceUrl(ConfigService.getDashboard(
        '0827af30-5895-11e8-a683-7fb93ba4982c', false));
}

function formDashboardCtrl() {

}

angular.module('leadwireApp').
    controller('dashboardCtrl', dashboardCtrl).
    controller('formDashboardCtrl', formDashboardCtrl);