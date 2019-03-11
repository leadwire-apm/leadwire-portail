(function (angular) {
    angular.module('leadwireApp')
        .controller('TmecOverviewController', [
            'TmecService',
            'toastr',
            'MESSAGES_CONSTANTS',
            '$stateParams',
            '$modal',
            OverviewControllerFN,
        ]);

    /**
     * Handle add new application logic
     *
     */
    function OverviewControllerFN (
        TmecService,
        toastr,
        MESSAGES_CONSTANTS,
        $stateParams,
        $modal,
    ) {
        var vm = this;

            /**
     * Handle add new application logic
     *
     */
    function ManageApplicationsCtrlFN (
        ApplicationService,
        CodeService,
        toastr,
        MESSAGES_CONSTANTS,
        $state,
    ) {
        var vm = this;

        vm.flipActivityIndicator = function (key) {
            vm.ui[key] = !vm.ui[key];
        };


        vm.loadApplications = function () {
            vm.flipActivityIndicator('isLoading');
            // should send some criteria
            ApplicationService.all()
                .then(function (applications) {
                    vm.flipActivityIndicator('isLoading');
                    vm.applications = applications;
                })
                .catch(function (error) {
                    vm.flipActivityIndicator('isLoading');
                    vm.applications = [];
                });
        };

        vm.init = function () {
            vm = angular.extend(vm, {
                ui: {
                    isLoading: false
                },
                applications: [],
            });
            vm.loadApplications();
        }
    }
}
})(window.angular);
