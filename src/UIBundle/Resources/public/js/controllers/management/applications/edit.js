(function (angular, moment) {
    angular.module('leadwireApp')
        .controller('ManageApplicationsEditController', [
            'ApplicationTypeFactory',
            'ApplicationFactory',
            'toastr',
            '$stateParams',
            'MESSAGES_CONSTANTS',
            '$state',
            ManageApplicationsEditCtrlFN,
        ]);

    /**
     * Handle add new application logic
     *
     */
    function ManageApplicationsEditCtrlFN (
        ApplicationTypeFactory,
        ApplicationFactory,
        toastr,
        $stateParams,
        MESSAGES_CONSTANTS,
        $state,
    ) {
        var vm = this;

        ApplicationFactory.get($stateParams.id, 'edit').then(function(res) {
            vm.application = res.data;
        });

        vm.loadApplicationTypes = function () {
            ApplicationTypeFactory.findAll()
                .then(function (response) {
                    vm.applicationTypes = response.data;
                });
        };

        vm.editApp = function() {
            vm.flipActivityIndicator();
            const updatedApp = angular.extend({},vm.application);
            delete updatedApp.invitations;
            delete updatedApp.owner;
            ApplicationFactory.update(vm.application.id, updatedApp)
                .then(function() {
                    vm.flipActivityIndicator();
                    toastr.success(MESSAGES_CONSTANTS.EDIT_APP_SUCCESS);
                    $state.go('app.management.applications');
                })
                .catch(function(error) {
                    vm.flipActivityIndicator();
                    toastr.error(
                        error.message ||
                            MESSAGES_CONSTANTS.EDIT_APP_FAILURE ||
                            MESSAGES_CONSTANTS.ERROR
                    );
                });
        };

        vm.flipActivityIndicator = function() {
            vm.ui.isSaving = !vm.ui.isSaving;
        };

        vm.onLoad = function () {
            vm = angular.extend(vm, {
                ui : {
                    isSaving: false,
                    isEditing: true
                },
            });
            vm.loadApplicationTypes();
        };
    }
})(window.angular, window.moment);