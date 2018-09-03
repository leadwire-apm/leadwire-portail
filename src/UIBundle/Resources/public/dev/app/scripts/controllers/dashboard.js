(function(angular) {
    angular
        .module('leadwireApp')
        .controller('dashboardCtrl', [
            '$sce',
            '$scope',
            'ConfigService',
            '$localStorage',
            '$state',
            'UserService',
            dashboardCtrl
        ]);

    function dashboardCtrl(
        $sce,
        $scope,
        ConfigService,
        $localStorage,
        $state,
        UserService
    ) {
        var vm = this;
        vm.applications = $localStorage.applications;
        UserService.handleFirstLogin();


        if (!!$state.params.id){
            vm.dashboardLink = $sce.trustAsResourceUrl(
                ConfigService.getDashboard($state.params.id, false)
            );
        }
    }
})(window.angular);
