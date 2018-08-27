(function(angular) {
    angular
        .module('leadwireApp')
        .controller('dashboardCtrl', [
            '$sce',
            '$scope',
            'ConfigService',
            'ApplicationFactory',
            '$location',
            '$localStorage',
            '$modal',
            '$ocLazyLoad',
            '$rootScope',
            '$state',
            dashboardCtrl
        ]);

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
        vm.applications = $localStorage.applications;
        var connectedUser = angular.extend({}, $localStorage.user);
        if (!connectedUser || !connectedUser.email) {
            $ocLazyLoad
                .load({
                    name: 'sbAdminApp',
                    files: [
                        $rootScope.ASSETS_BASE_URL +
                            'scripts/controllers/profileModal.js'
                    ]
                })
                .then(function() {
                    $modal.open({
                        ariaLabelledBy: 'Update-user',
                        ariaDescribedBy: 'User-form',
                        templateUrl:
                            $rootScope.ASSETS_BASE_URL + 'views/profile.html',
                        controller: 'profileModalCtrl',
                        controllerAs: 'ctrl'
                    });
                });
        }

        if (!!$state.params.id){
            vm.dashboardLink = $sce.trustAsResourceUrl(
                ConfigService.getDashboard($state.params.id, false)
            );
        }
    }
})(window.angular);
