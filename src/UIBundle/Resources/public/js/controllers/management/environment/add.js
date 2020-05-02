(function (angular) {
    angular.module('leadwireApp')
        .controller('AddEnvironmentController', [
            'EnvironmentService',
            'toastr',
            'MESSAGES_CONSTANTS',
            '$state',
            '$localStorage',
            AddEnvironmentCtrlFN,
        ]);

    /**
     * Handle add new environment
     *
     */
    function AddEnvironmentCtrlFN (
        EnvironmentService,
        toastr,
        MESSAGES_CONSTANTS,
        $state,
        $localStorage,
    ) {
        var vm = this;

        vm.blacklist =["leadwire", "span", "transaction", "error", "metric", "sourcemap", ...$localStorage.listApp];

        $localStorage.envList.reduce(function (p, c, i) {
            p.push(c.name);
            return p;
        }, vm.blacklist)


        vm.flipActivityIndicator = function (key) {
            vm.ui[key] = !vm.ui[key];
        };

        vm.saveEnvironment = function () {
            vm.flipActivityIndicator('isSaving');
            EnvironmentService.create(vm.environment)
                .then(function () {
                    vm.flipActivityIndicator('isSaving');
                    $localStorage.envList.push(vm.environment.name);
                    toastr.success(MESSAGES_CONSTANTS.SUCCESS);
                    $state.go('app.management.environmentList');
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
                environment: {
                    name: ''
                }
            });
        };

    }
})(window.angular);
