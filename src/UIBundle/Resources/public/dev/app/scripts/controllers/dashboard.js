'use strict';

function dashboardCtrl(
    $sce, ConfigService, Application, $location, $localStorage, $modal,
    $ocLazyLoad, $rootScope) {

    var ctrl = this;
    var connectedUser = $localStorage.user;

    if (!connectedUser || !connectedUser.email) {
        $ocLazyLoad.load({
            name: 'sbAdminApp',
            files: ['scripts/controllers/settings.js'],
        }).then(function() {
            $modal.open({
                //ariaLabelledBy: 'modal-title',
                //ariaDescribedBy: 'modal-body',
                templateUrl: 'views/profile.html',
                controller: 'SettingsModal',
                controllerAs: 'ctrl',
                resolve: {
                    isModal: function() {
                        return true;
                    },
                },
            });
        });
    }

    Application.findAll().success(function(data) {
        $rootScope.applications = data;
        if (data.length === 0) {
            $location.path('/applications/add');
            console.log('create one application');
        }
    }).error(function(error) {
        console.error(error);
    });
    ctrl.dashboardLink = $sce.trustAsResourceUrl(ConfigService.getDashboard(
        '0827af30-5895-11e8-a683-7fb93ba4982c', false));
};

function formDashboardCtrl() {

};

angular.module('leadwireApp').
    controller('dashboardCtrl', [
        '$sce',
        'ConfigService',
        'Application',
        '$location',
        '$localStorage',
        '$modal',
        '$ocLazyLoad',
        '$rootScope',
        dashboardCtrl]).
    controller('formDashboardCtrl', [formDashboardCtrl]);