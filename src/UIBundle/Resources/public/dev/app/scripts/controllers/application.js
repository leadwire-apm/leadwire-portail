(function(angular, swal) {
    angular
        .module('leadwireApp')
        .controller('applicationListCtrl', [
            '$scope',
            '$rootScope',
            'ApplicationFactory',
            'toastr',
            'MESSAGES_CONSTANTS',
            '$localStorage',
            '$modal',
            applicationListCtrlFN
        ]);

    function applicationListCtrlFN(
        $scope,
        $rootScope,
        ApplicationFactory,
        toastr,
        MESSAGES_CONSTANTS,
        $localStorage,
        $modal
    ) {
        var vm = this;
        init();
        vm.deleteApp = function(id) {
            swal(MESSAGES_CONSTANTS.SWEET_ALERT_DELETE_MODE).then(function(
                willDelete
            ) {
                if (willDelete) {
                    ApplicationFactory.remove(id)
                        .then(function() {
                            getApps();
                            swal.close();
                            toastr.success(
                                MESSAGES_CONSTANTS.DELETE_APP_SUCCESS
                            );
                        })
                        .catch(function(error) {
                            swal.close();

                            toastr.error(
                                error.message ||
                                    MESSAGES_CONSTANTS.DELETE_APP_FAILURE ||
                                    MESSAGES_CONSTANTS.ERROR
                            );
                        });
                } else {
                    swal('Your App is safe!');
                }
            });
        };

        vm.flipActivityIndicator = function(suffix) {
            suffix = typeof suffix !== 'undefined' ? suffix : '';
            vm.ui['isDeleting' + suffix] = !vm.ui['isDeleting' + suffix];
        };

        function getApps() {
            // get all
            ApplicationFactory.findAll().then(function(response) {
                vm.apps = response.data;
                $localStorage.applications = response.data;
            });
        }

        function init() {
            vm.ui = {
                isDeleting: false
            };
            getApps();
        }

        vm.enableApp = function(selectedApp) {
            $modal.open({
                templateUrl:
                    $rootScope.ASSETS_BASE_URL +
                    'views/application/enable.html',
                controller: function($modalInstance, $state) {
                    var modalVM = this;
                    modalVM.enable = function() {
                        ApplicationFactory.activate(
                            selectedApp.id,
                            modalVM.activationCode
                        )
                            .then(function(response) {
                                if (response.data) {
                                    toastr.success(
                                        MESSAGES_CONSTANTS.ACTIVATE_APP_SUCCESS
                                    );
                                    var updatedApp = angular.extend(
                                        selectedApp,
                                        {
                                            isEnabled: true
                                        }
                                    );
                                    $scope.$emit('activate:app', updatedApp);
                                    vm.apps = vm.apps.map(function(currentApp) {
                                        return currentApp.id !== selectedApp.id
                                            ? currentApp
                                            : updatedApp;
                                    });
                                    $state.go('app.applicationDetail', {
                                        id: selectedApp.id
                                    });
                                    $modalInstance.close();
                                } else {
                                    toastr.error(
                                        MESSAGES_CONSTANTS.ACTIVATE_APP_FAILURE
                                    );
                                }
                            })
                            .catch(function(error) {
                                toastr.error(
                                    error.message ||
                                        MESSAGES_CONSTANTS.EDIT_APP_FAILURE ||
                                        MESSAGES_CONSTANTS.ERROR
                                );
                            });
                    };
                },
                controllerAs: 'ctrl'
            });
        };
    }
})(window.angular, window.swal);
