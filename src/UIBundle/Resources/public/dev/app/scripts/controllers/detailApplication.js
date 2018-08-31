(function(angular, swal) {
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
        vm.getApp = function() {
            ApplicationFactory.get($stateParams.id).then(function(res) {
                vm.app = res.data;
            });
        };
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
                    vm.getApp();
                    vm.flipActivityIndicator();
                    vm.invitedUser.email = '';
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
            var body = document.createElement('h5');
            body.innerText =
                'Once deleted, you will not be able to recover this Invitation!';
            body.className = 'text-center';

            swal({
                title: 'Are you sure?',
                className: 'text-center',
                content: body,
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
                            vm.getApp();
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

        function init() {
            $rootScope.currentNav = 'settings';
            vm.ui = {
                isSaving: false
            };
            vm.invitedUser = {};
            vm.DOWNLOAD_URL = CONFIG.DOWNLOAD_URL;
            vm.getApp();
        }
    }
})(window.angular, window.swal);