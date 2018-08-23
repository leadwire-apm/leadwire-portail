(function(angular) {
    angular
        .module('leadwireApp')
        .controller('applicationDetailCtrl', [
            'ApplicationFactory',
            'Invitation',
            '$stateParams',
            '$rootScope',
            'CONFIG',
            'toastr',
            'MESSAGES_CONSTANTS',
            applicationDetailCtrlFN
        ]);

    function applicationDetailCtrlFN(
        ApplicationFactory,
        Invitation,
        $stateParams,
        $rootScope,
        CONFIG,
        toastr,
        MESSAGES_CONSTANTS
    ) {
        var vm = this;
        init();

        vm.handleInviteUser = function() {
            vm.flipActivityIndicator();
            Invitation.save({
                email: vm.invitedUser.email,
                app: {
                    id: vm.app.id
                }
            })
                .then(function() {
                    toastr.success(MESSAGES_CONSTANTS.INVITE_USER_SUCCESS);
                    getApp();
                    vm.flipActivityIndicator();
                })
                .catch(function(error) {
                    vm.flipActivityIndicator();
                    toastr.error(
                        error.message ||
                            MESSAGES_CONSTANTS.INVITE_USER_FAILURE ||
                            MESSAGES_CONSTANTS.ERROR
                    );
                });
        };

        vm.deleteInvitation = function(id) {
            swal({
                title: 'Are you sure?',
                text:
                    'Once deleted, you will not be able to recover this Invitation!',
                icon: 'warning',
                buttons: true,
                dangerMode: true
            }).then(function(willDelete) {
                if (willDelete) {
                    Invitation.remove(id)
                        .then(function() {
                            swal.close();
                            toastr.success(
                                MESSAGES_CONSTANTS.DELETE_INVITATION_SUCCESS
                            );
                            getApp();
                        })
                        .catch(function(error) {
                            swal.close();
                            toastr.error(
                                error.message ||
                                    MESSAGES_CONSTANTS.DELETE_INVITATION_FAILURE ||
                                    MESSAGES_CONSTANTS.ERROR
                            );
                        });
                } else {
                    swal('Your Invitation is safe!');
                }
            });
        };

        vm.flipActivityIndicator = function(suffix) {
            suffix = typeof suffix !== 'undefined' ? suffix : '';
            vm.ui['isSaving' + suffix] = !vm.ui['isSaving' + suffix];
        };

        function getApp() {
            ApplicationFactory.get($stateParams.id).then(function(res) {
                vm.app = res.data;
            });
        }

        function init() {
            $rootScope.currentNav = 'settings';
            vm.ui = {
                isSaving: false
            };
            vm.DOWNLOAD_URL = CONFIG.DOWNLOAD_URL;
            getApp();
        }
    }
})(window.angular);
