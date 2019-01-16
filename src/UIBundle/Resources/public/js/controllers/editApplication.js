(function(angular) {
    angular
        .module('leadwireApp')
        .controller('applicationEditCtrl', [
            'ApplicationFactory',
            '$stateParams',
            '$state',
            '$rootScope',
            'toastr',
            'MESSAGES_CONSTANTS',
            applicationEditCtrlFN
        ]);

    function applicationEditCtrlFN(
        ApplicationFactory,
        $stateParams,
        $state,
        $rootScope,
        toastr,
        MESSAGES_CONSTANTS
    ) {
        var vm = this;
        $rootScope.currentNav = 'settings';
        vm.ui = {
            isSaving: false,
            isEditing: true
        };

        ApplicationFactory.get($stateParams.id).then(function(res) {
            vm.application = res.data;
        });

        vm.editApp = function() {
            vm.flipActivityIndicator();
            const updatedApp = angular.extend({},vm.application);
            delete updatedApp.invitations;
            delete updatedApp.owner;
            delete updatedApp.type;
            ApplicationFactory.update(vm.application.id, updatedApp)
                .then(function() {
                    vm.flipActivityIndicator();
                    toastr.success(MESSAGES_CONSTANTS.EDIT_APP_SUCCESS);
                    $state.go('app.applicationsList');
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
    }
})(window.angular);
