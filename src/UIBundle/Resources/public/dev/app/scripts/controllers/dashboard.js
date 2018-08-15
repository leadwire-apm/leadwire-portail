'use strict';

function dashboardCtrl(
    $sce,
    $scope,
    ConfigService,
    ApplicationFactory,
    $location,
    $localStorage,
    $modal,
    $ocLazyLoad,
    $rootScope,
    $state
) {
    var vm = this;
    var connectedUser = angular.extend({}, $localStorage.user);
    if (!connectedUser || !connectedUser.email) {
        $ocLazyLoad
            .load({
                name: 'sbAdminApp',
                files: [
                    $rootScope.ASSETS_BASE_URL +
                        'scripts/controllers/profile.js'
                ]
            })
            .then(function() {
                $modal.open({
                    //ariaLabelledBy: 'modal-title',
                    //ariaDescribedBy: 'modal-body',
                    templateUrl:
                        $rootScope.ASSETS_BASE_URL + 'views/profile.html',
                    controller: 'profileModalCtrl',
                    controllerAs: 'vm',
                    resolve: {
                        isModal: function() {
                            return true;
                        }
                    }
                });
            });
    }

    ApplicationFactory.findAll()
        .success(function(data) {
            delete $localStorage.applications;
            $localStorage.applications = data;
            $rootScope.applications = $localStorage.applications;
        })
        .error(function(error) {
            console.error(error);
        });
    vm.dashboardLink = $sce.trustAsResourceUrl(
        ConfigService.getDashboard(
            $state.params.id,
            false
        )
    );
}

function formDashboardCtrl() {}

angular
    .module('leadwireApp')
    .controller('dashboardCtrl', dashboardCtrl)
    .controller('formDashboardCtrl', formDashboardCtrl);
