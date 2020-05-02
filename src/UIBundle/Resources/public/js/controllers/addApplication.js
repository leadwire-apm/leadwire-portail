(function (angular) {
    angular
        .module('leadwireApp')
        .controller('addApplicationCtrl', [
            'ApplicationFactory',
            'ApplicationService',
            'ApplicationTypeFactory',
            'EnvironmentService',
            'toastr',
            'MESSAGES_CONSTANTS',
            '$state',
            'socket',
            '$rootScope',
            '$localStorage',
            addApplicationCtrlFN,
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
        EnvironmentService,
        toastr,
        MESSAGES_CONSTANTS,
        $state,
        socket,
        $rootScope,
        $localStorage,
    ) {
        var vm = this;

        vm.blacklist = $localStorage.applications.reduce(function (p, c, i) {
            p.push(c.name);
            return p;
        }, ["leadwire", "span", "transaction", "error", "metric", "sourcemap"])

        $localStorage.envList.reduce(function (p, c, i) {
            p.push(c.name);
            return p;
        }, vm.blacklist)

        socket.on('heavy-operation', function (data) {
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

        vm.saveApp = function () {
            vm.flipActivityIndicator();
            ApplicationFactory.save(vm.application)
                .then(ApplicationService.handleSaveOnSuccess)
                .then(handleAfterSuccess)
                .catch(handleOnFailure);
        };

        vm.flipActivityIndicator = function () {
            vm.ui.isSaving = !vm.ui.isSaving;
        };

        vm.loadApplicationTypes = function () {
            ApplicationTypeFactory.findAll()
                .then(function (response) {
                    vm.applicationTypes = response.data;
                });
        };

        vm.onLoad = function () {
            vm = angular.extend(vm, {
                ui: {
                    isSaving: false,
                },
            });
            vm.loadApplicationTypes();

            EnvironmentService.getDefault()
                .then(function (response) {
                    if (response == null) {
                        $('.panel').addClass('inactive');
                        $('.create-env').css('display', 'block');
                    }
                });
        };

        function handleAfterSuccess(success) {
            if (success) {
                $rootScope.$broadcast("new:app", {});
                vm.flipActivityIndicator();
                $state.go('app.applicationsList');
            }
        }

        function handleOnFailure(error) {
            toastr.error(
                error.message || MESSAGES_CONSTANTS.ADD_APP_FAILURE ||
                MESSAGES_CONSTANTS.ERROR,
            );
            vm.flipActivityIndicator();
        }
    }
})(window.angular);
