(function(angular) {
    angular
        .module('leadwireApp')
        .controller('customDashboardsCtrl', [
            '$scope',
            'DashboardService',
            '$localStorage',
            '$rootScope',
            controller
        ]);

    function controller($scope, DashboardService, $localStorage, $rootScope) {
        var vm = this;

        vm.onLoad = function() {
            vm.isLoading = true;
            DashboardService.fetchDashboardsByAppId(
                $rootScope.user.defaultApp.id
            )
                .then(function(data) {
                    vm.loadThemeList.apply(vm, [data.custom]);
                })
                .catch(function() {
                    vm.isLoading = false;
                });
        };

        vm.loadThemeList = function(dashboards) {
            var customDashboards = dashboards || $localStorage.customMenus.list;
            vm.selectedAppUuid = $localStorage.selectedApp.uuid;
            vm.currentUserUuid = $localStorage.user.uuid;
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

        vm.onLoad();
    }
})(window.angular);
