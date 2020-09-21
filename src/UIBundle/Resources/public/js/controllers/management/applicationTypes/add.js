(function (angular) {
    angular.module('leadwireApp')
        .controller('AddApplicationTypeController', [
            'ApplicationTypeService',
            'MonitoringSetService',
            'toastr',
            'MESSAGES_CONSTANTS',
            '$state',
            'socket',
            '$rootScope',
            AddApplicationTypeCtrlFN,
        ]);

    /**
     * Handle add new application logic
     *
     */
    function AddApplicationTypeCtrlFN (
        ApplicationTypeService,
        MonitoringSetService,
        toastr,
        MESSAGES_CONSTANTS,
        $state,
        socket,
        $rootScope
    ) {
        var vm = this;

        socket.on('heavy-operation', function(data) {
            if (data.user != $rootScope.user.id) {
                return;
            }

            if (data.status == "in-progress") {
                if ($('#toast-container').hasClass('toast-message') == false) {
                    toastr.info(
                        data.message + '...',
                        "Operation in progress",
                        {
                            timeOut: 0,
                            extendedTimeOut: 0,
                            closeButton: true,
                            onClick: null,
                            preventDuplicates: true
                        }
                    );
                } else {
                    $('.toast-message').html(data.message + '...');
                }
            }
            if (data.status == "done") {
                toastr.clear();
            }
        });

        vm.flipActivityIndicator = function (key) {
            vm.ui[key] = !vm.ui[key];
        };

        vm.loadMonitoringSets = function() {
            MonitoringSetService.listValid()
            .then(function(monitoringSets) {
                vm.availableMonitoringSets = monitoringSets;
                $('.selectpicker').append(vm.availableMonitoringSets.map(function(v,k){return '<option value="' + v.id + '">'+v.name+'</option>'}));
                $('.selectpicker').selectpicker('refresh');
            });
        };

        vm.saveAppType = function () {
            vm.flipActivityIndicator('isSaving');
            vm.applicationType.monitoringSets = vm.applicationType.monitoringSets.map(function (ms) {return {'id': ms};});
            ApplicationTypeService.create(vm.applicationType)
                .then(function () {
                    vm.flipActivityIndicator('isSaving');
                    toastr.success(MESSAGES_CONSTANTS.SUCCESS);
                    $state.go('app.management.applicationTypes');
                })
                .catch(function (error) {
                    vm.flipActivityIndicator('isSaving');
                    toastr.error(error.message || MESSAGES_CONSTANTS.ERROR);

                });
        };

        vm.init = function () {
            vm = angular.extend(vm, {
                ui: {
                    isSaving: false,
                },
                applicationType: {
                    name: '',
                    description: '',
                    installation: '',
                    monitoringSets: []
                },
                availableMonitoringSets: [],
            });
            vm.loadMonitoringSets();
        };

    }
})(window.angular);
