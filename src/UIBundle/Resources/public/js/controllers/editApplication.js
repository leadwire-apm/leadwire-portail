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
            'socket',
            applicationEditCtrlFN
        ]);

    function applicationEditCtrlFN(
        ApplicationFactory,
        ApplicationTypeFactory,
        $stateParams,
        $state,
        $rootScope,
        toastr,
        MESSAGES_CONSTANTS,
        socket
    ) {
        var vm = this;

        socket.on('heavy-operation', function(data) {
            if (data.user != $rootScope.user.id) {
                return;
            }

            if (data.status == "in-progress") {
                if ($('#toast-container').hasClass('toast-top-right') == false) {
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
