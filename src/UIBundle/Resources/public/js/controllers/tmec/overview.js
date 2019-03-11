(function (angular) {
    angular.module('leadwireApp')
        .controller('TmecOverviewController', [
            'ApplicationService',
            'TmecService',
            'toastr',
            'MESSAGES_CONSTANTS',
            '$stateParams',
            '$modal',
            CtrlOverviewControllerFN,
        ]);

    /**
     * Handle add new application logic
     *
     */
    function CtrlOverviewControllerFN (
        ApplicationService,
        TmecService,
        toastr,
        MESSAGES_CONSTANTS,
        $stateParams,
        $modal,
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
})(window.angular);
