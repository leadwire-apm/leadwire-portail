(function(angular) {
    angular
        .module('leadwireApp')
        .controller('dashboardCtrl', [
            '$sce',
            '$scope',
            'ConfigService',
            '$localStorage',
            '$state',
            dashboardCtrl
        ]);

    function dashboardCtrl($sce, $scope, ConfigService, $localStorage, $state) {
        var vm = this;
        vm.applications = $localStorage.applications;

        if (!!$state.params.id) {
            vm.dashboardLink = $sce.trustAsResourceUrl(
                ConfigService.getDashboard("app_", $state.params.id, false)
            );
        }
    }
})(window.angular);
