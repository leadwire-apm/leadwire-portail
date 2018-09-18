(function(angular) {
    angular.module('leadwireApp').controller('dashboardCtrl', [
        '$sce',
        '$scope',
        'ConfigService',
        '$localStorage',
        '$state',
        dashboardCtrl,
    ]);

    function dashboardCtrl($sce, $scope, ConfigService, $localStorage, $state) {
        var vm = this;
        vm.applications = $localStorage.applications;

        if (!!$state.params.id) {
            var tenant = $state.params.tenant || 'app_';
            vm.dashboardLink = $sce.trustAsResourceUrl(
                ConfigService.getDashboard(tenant, $state.params.id, false),
            );
        }
    }
})(window.angular);
