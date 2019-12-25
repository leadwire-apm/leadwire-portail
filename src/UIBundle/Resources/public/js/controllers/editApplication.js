(function(angular) {
    angular
        .module('leadwireApp')
        .controller('applicationEditCtrl', [
            'ApplicationFactory',
            'ApplicationTypeFactory',
            '$stateParams',
            '$state',
            '$rootScope',
            'toastr',
            'MESSAGES_CONSTANTS',
            applicationEditCtrlFN
        ]);

    function applicationEditCtrlFN(
        ApplicationFactory,
        ApplicationTypeFactory,
        $stateParams,
        $state,
        $rootScope,
        toastr,
        MESSAGES_CONSTANTS
    ) {
        var vm = this;
        $rootScope.currentNav = 'settings';


        ApplicationFactory.get($stateParams.id).then(function(res) {
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
})(window.angular);
