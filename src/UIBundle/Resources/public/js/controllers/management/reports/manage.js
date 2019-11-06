(function (angular) {
    angular.module('leadwireApp')
        .controller('ManageReportController', [
            'toastr',
            'CONFIG',
            'MESSAGES_CONSTANTS',
            '$state',
            ListReportCtrlFN,
        ]);

    /**
     * Handle add new template logic
     *
     */
    function ListReportCtrlFN (
        toastr,
        CONSTANTS,
        MESSAGES_CONSTANTS,
        $state,
    ) {
        var vm = this;

        vm.init = function () {
            console.log('ho ho ho');
            vm = angular.extend(vm, {
                ui: {
                    isLoading: false,
                },
            });

            if (!!$state.params.tenant) {
                vm.setReportLink = $sce.trustAsResourceUrl(
                    ConfigService.setDashboard($state.params.tenant)
                );
            }
        };

    }
})(window.angular);
