(function (angular) {
    angular.module('leadwireApp')
        .controller('ManageReportController', [
            '$sce',
            'ConfigService',
            '$state',
            ManageReportCtrlFN,
        ]);

    /**
     * Handle add new template logic
     *
     */
    function ManageReportCtrlFN($sce, ConfigService, $state) {
        var vm = this;

        vm = angular.extend(vm, {
            ui: {
                isLoading: false,
            },
        });

        if (!!$state.params.tenant) {
            vm.setReportLink = $sce.trustAsResourceUrl("https://kibana.leadwire.io/app/sentinl#/reports?embed=true");
        }

    }
})(window.angular);
