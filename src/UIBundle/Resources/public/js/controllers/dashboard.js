(function(angular) {
    angular.module('leadwireApp').controller('dashboardCtrl', [
        '$sce',
        '$rootScope',
        'DashboardService',
        '$localStorage',
        '$state',
        dashboardCtrl,
    ]);

    function dashboardCtrl($sce, $rootScope, DashboardService, $localStorage, $state) {
        var vm = this;
        vm.applications = $localStorage.applications;
        vm.dashboardLink = $sce.trustAsResourceUrl(
            DashboardService.getDashboard($state.params.tenant, $state.params.id),
        );

        vm.onLoad = function() {
            vm.isLoading = true;
            // DashboardService.fetchDashboardsByAppId($localStorage.selectedAppId)
            //     .then(function(data) {})
            //     .catch(function() {vm.isLoading = false;});
            $rootScope.menus = $localStorage.currentApplicationMenus
        };

        vm.onLoad();
    }
})(window.angular);
