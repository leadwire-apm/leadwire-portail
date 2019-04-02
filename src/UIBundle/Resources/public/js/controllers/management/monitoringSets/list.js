(function (angular) {
    angular.module('leadwireApp')
        .controller('ListMonitoringSetController', [
            'MonitoringSetService',
            'toastr',
            'MESSAGES_CONSTANTS',
            ListMonitoringSetCtrlFN,
        ]);

    function ListMonitoringSetCtrlFN (
        MonitoringSetService,
        toastr,
        MESSAGES_CONSTANTS,
    ) {
        var vm = this;

        vm.flipActivityIndicator = function (key) {
            vm.ui[key] = !vm.ui[key];
        };

        vm.handleOnDelete = function (id) {
            swal(MESSAGES_CONSTANTS.SWEET_ALERT_VALIDATION())
                .then(function (willDelete) {
                    if (willDelete) {
                        vm.deleteMonitoringSet(id);
                    } else {
                        swal.close();
                    }
                });

        };

        vm.loadMonitoringSet = function () {
            vm.flipActivityIndicator('isLoading');
            // should send some criteria
            MonitoringSetService.list()
                .then(function (monitoringSets) {
                    vm.flipActivityIndicator('isLoading');
                    vm.monitoringSets = monitoringSets;
                })
                .catch(function (error) {
                    vm.flipActivityIndicator('isLoading');

                });
        };

        vm.deleteMonitoringSet = function (id) {
            MonitoringSetService.delete(id)
                .then(function () {
                    toastr.success(MESSAGES_CONSTANTS.SUCCESS);
                })
                .then(vm.loadMonitoringSet)
                .catch(function () {
                    toastr.success(MESSAGES_CONSTANTS.ERROR);
                });
        };

        vm.init = function () {
            vm = angular.extend(vm, {
                ui: {
                    isSaving: false,
                    isLoading: false,
                },
                monitoringSets: [],
            });
            vm.loadMonitoringSet();
        };

    }
})(window.angular);
