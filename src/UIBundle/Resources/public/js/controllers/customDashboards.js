(function(angular) {
    angular
        .module('leadwireApp')
        .controller('customDashboardsCtrl', [
            '$scope',
            'DashboardService',
            '$sessionStorage',
            'ConfigService',
            '$state',
            controller
        ]);

    function controller($scope, DashboardService, $sessionStorage, ConfigService, $state) {
        var vm = this;

        vm.onLoad = function() {
            vm.isLoading = true;
            DashboardService.fetchDashboardsByAppId(
                $sessionStorage.selectedAppId
            )
                .then(function(data) {
                    vm.loadThemeList.apply(vm, [data.custom]);
                })
                .catch(function() {
                    vm.isLoading = false;
                });
        };

        vm.loadThemeList = function(dashboards) {
            var customDashboards = dashboards || $sessionStorage.customMenus.list;
            vm.selectedApp = $sessionStorage.selectedApp;
            vm.currentUser = $sessionStorage.user;
            $scope.$apply(function() {
                vm.isLoading = false;
                vm.themeList = Object.keys(customDashboards).reduce(function(
                    acc,
                    theme
                ) {
                    acc.push({
                        name: theme,
                        dashboards: customDashboards[theme]
                    });
                    return acc;
                },
                []);
            });
        };

        vm.openShredDashboars = function(type){
            window.open(ConfigService.setDashboard(type),'_blank');
        }

        vm.onLoad();
    }
})(window.angular);
