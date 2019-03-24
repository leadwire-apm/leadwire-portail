(function(angular) {
    angular.module('leadwireApp').controller('dashboardCtrl', [
        '$sce',
        '$scope',
        '$rootScope',
        'DashboardService',
        '$localStorage',
        '$state',
        dashboardCtrl,
    ]);

    function dashboardCtrl($sce, $scope, $rootScope, DashboardService, $localStorage, $state) {
        var vm = this;
        vm.applications = $localStorage.applications;
        vm.dashboardLink = DashboardService.getDashboard($state.params.tenant, $state.params.id);
        console.log(vm.dashboardLink);

        vm.onLoad = function() {
            vm.isLoading = true;
            $rootScope.menus = $localStorage.currentApplicationMenus
        };

        $scope.trustSrc = function(src) {
            return $sce.trustAsResourceUrl(src);
        }

        vm.onLoad();
    }
})(window.angular);
