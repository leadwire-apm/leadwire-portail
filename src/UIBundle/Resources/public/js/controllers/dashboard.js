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
            $rootScope.menus = $localStorage.currentApplicationMenus
        };

        vm.onLoad();
    }
})(window.angular);
