(function(angular) {
    angular
        .module('leadwireApp')
        .controller('customDashboardsCtrl', ['$localStorage', controller]);

    function controller($localStorage) {
        var vm = this;
        vm.themeList = Object.keys($localStorage.customMenus.list).reduce(function(
            acc,
            theme
        ) {
            acc.push({
                name: theme,
                dashboards: $localStorage.customMenus.list[theme]
            });
            return acc;
        },
        []);
        console.log(vm.themeList)
    }
})(window.angular);
