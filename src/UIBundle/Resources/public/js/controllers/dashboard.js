(function(angular) {
    angular.module('leadwireApp').controller('dashboardCtrl', [
        '$sce',
        '$scope',
        'DashboardService',
        '$localStorage',
        '$state',
        dashboardCtrl,
    ]);

    function dashboardCtrl($sce, $scope, DashboardService, $localStorage, $state) {
        var vm = this;
        vm.applications = $localStorage.applications;

        vm.dashboardLink = $sce.trustAsResourceUrl(
            DashboardService.getDashboard($state.params.tenant, $state.params.id),
        );
    }
})(window.angular);
