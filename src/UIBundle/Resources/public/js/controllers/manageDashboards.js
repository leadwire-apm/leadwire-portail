(function(angular) {
    angular
        .module('leadwireApp')
        .controller('manageDashboardsCtrl', [
            '$sce',
            'ConfigService',
            '$state',
            manageDashboardsCtrlFN
        ]);

    function manageDashboardsCtrlFN($sce, ConfigService, $state) {
        var vm = this;

        if (!!$state.params.tenant) {
            vm.setDashboardLink = $sce.trustAsResourceUrl(
                ConfigService.setDashboard($state.params.tenant)
            );
        }
    }
})(window.angular);
