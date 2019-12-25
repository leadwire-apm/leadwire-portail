(function (angular) {
    angular.module('leadwireApp')
        .controller('EditEnvironmentController', [
            'EnvironmentService',
            'MESSAGES_CONSTANTS',
            '$state',
            'toastr',
            EditEnvironmentControllerCtrlFN,
        ]);

    /**
     * Handle add new application logic
     *
     */
    function EditEnvironmentControllerCtrlFN (
        EnvironmentService,
        MESSAGES_CONSTANTS,
        $state,
        toastr,
    ) {
        var vm = this;

        vm.flipActivityIndicator = function (key) {
            vm.ui[key] = !vm.ui[key];
        };

        EnvironmentService.find($state.params.id)
                .then(function (environment) {
                    if (environment === null) {
                        throw new Error();
                    }
                    vm.flipActivityIndicator('isLoading');
                    vm.environment = environment;
                })
                .catch(function () {
                    vm.flipActivityIndicator('isLoading');
                    $state.go('app.management.templates');
                });

        vm.editEnvironment = function () {
            vm.flipActivityIndicator('isSaving')
            EnvironmentService.update(vm.environment)
                .then(function () {
                    vm.flipActivityIndicator('isSaving')
                    toastr.success(MESSAGES_CONSTANTS.SUCCESS);
                    $state.go('app.management.environmentList');
                })
                .catch(function () {
                    vm.flipActivityIndicator('isSaving')
                    toastr.error(MESSAGES_CONSTANTS.ERROR);
                });
        };

        vm.init = function () {
            vm = angular.extend(vm, {
                ui: {
                    isSaving: false,
                    isLoading: false,
                },
                environment: {
                    name: '',
                    ip: ''
                }
            });
        };

    }
})(window.angular);
