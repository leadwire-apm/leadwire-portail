(function(angular) {
    angular
        .module('leadwireApp')
        .controller('addApplicationCtrl', [
            'ApplicationFactory',
            'ApplicationService',
            'ApplicationTypeFactory',
            'toastr',
            'MESSAGES_CONSTANTS',
            '$state',
            addApplicationCtrlFN
        ]);

    /**
     * Handle add new application logic
     *
     * @param ApplicationFactory
     * @param ApplicationService
     * @param ApplicationTypeFactory
     * @param toastr
     * @param MESSAGES_CONSTANTS
     * @param $state
     */
    function addApplicationCtrlFN(
        ApplicationFactory,
        ApplicationService,
        ApplicationTypeFactory,
        toastr,
        MESSAGES_CONSTANTS,
        $state
    ) {
        var vm = this;
        vm.saveApp = function() {
            vm.flipActivityIndicator();
            ApplicationFactory.save(vm.application)
                .then(ApplicationService.handleSaveOnSuccess)
                .then(handleAfterSuccess)
                .catch(handleOnFailure);
        };

        vm.flipActivityIndicator = function() {
            vm.ui.isSaving = !vm.ui.isSaving;
        };

        vm.loadApplicationTypes = function() {
            ApplicationTypeFactory.findAll().then(function(response) {
                vm.applicationTypes = response.data;
            });
        };

        vm.onLoad = function() {
            vm = angular.extend(vm, {
                ui: {
                    isSaving: false
                }
            });
            vm.loadApplicationTypes();
        };

        function handleAfterSuccess(success) {
            if (success) {
                vm.flipActivityIndicator();
                $state.go('app.applicationsList');
            }
        }

        function handleOnFailure(error) {
            toastr.error(
                error.message ||
                    MESSAGES_CONSTANTS.ADD_APP_FAILURE ||
                    MESSAGES_CONSTANTS.ERROR
            );
            vm.flipActivityIndicator();
        }
    }
})(window.angular);
