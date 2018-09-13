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
        onLoad();
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

        function onLoad() {
            vm.ui = {
                isSaving: false
            };

            ApplicationTypeFactory.findAll().then(function(response) {
                vm.applicationTypes = response.data;
            });
        }

        function handleAfterSuccess(success) {
            if(success){
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
