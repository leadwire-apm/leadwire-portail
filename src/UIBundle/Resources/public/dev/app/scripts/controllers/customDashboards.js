(function(angular) {
    angular
        .module('leadwireApp')
        .controller('customDashboardsCtrl', [
            '$localStorage',
            '$rootScope',
            controller
        ]);

    function controller($localStorage, $rootScope) {
        var vm = this;

        vm.onLoad = function() {
            vm.loadThemeList();
        };

        $rootScope.$on('context:updated', function() {
            vm.loadThemeList();
        });

        vm.loadThemeList = function() {
            vm.selectedAppUuid = $localStorage.selectedApp.uuid;
            vm.currentUserUuid = $localStorage.user.uuid;
            vm.themeList = Object.keys($localStorage.customMenus.list).reduce(
                function(acc, theme) {
                    acc.push({
                        name: theme,
                        dashboards: $localStorage.customMenus.list[theme]
                    });
                    return acc;
                },
                []
            );
        };

        vm.onLoad();
    }
})(window.angular);
