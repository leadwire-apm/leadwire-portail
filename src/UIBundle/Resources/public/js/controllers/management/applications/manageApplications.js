(function (angular) {
    angular.module('leadwireApp')
        .controller('ManageApplicationsController', [
            'ApplicationService',
            'CodeService',
            'toastr',
            'MESSAGES_CONSTANTS',
            '$state',
            ManageApplicationsCtrlFN,
        ]);

    /**
     * Handle add new application logic
     *
     */
    function ManageApplicationsCtrlFN (
        ApplicationService,
        CodeService,
        toastr,
        MESSAGES_CONSTANTS,
        $state,
    ) {
        var vm = this;

        vm.flipActivityIndicator = function (key) {
            vm.ui[key] = !vm.ui[key];
        };

        vm.handleOnDelete = function (application) {
            swal(MESSAGES_CONSTANTS.SWEET_ALERT_VALIDATION())
                .then(function (willDelete) {
                    if (willDelete) {
                        vm.deleteApplication(application.id);
                    } else {
                        swal.close();
                    }
                });

        };

        vm.handleOnToggleStatus = function (application) {
            swal(MESSAGES_CONSTANTS.SWEET_ALERT_VALIDATION())
                .then(function (willToggle) {
                    if (willToggle) {
                        vm.toggleApplicationStatus(application.id);
                    } else {
                        swal.close();
                    }
                });
        };

        vm.generateCode = function () {
            swal(MESSAGES_CONSTANTS.SWEET_ALERT_VALIDATION())
                .then(function (willGenerate) {
                    if (willGenerate) {
                        CodeService.create()
                            .then(function () {
                                toastr.success(MESSAGES_CONSTANTS.SUCCESS);
                            })
                            .catch(function () {
                                toastr.error(MESSAGES_CONSTANTS.ERROR);
                            });
                    } else {
                        swal.close();
                    }
                });
        };

        vm.loadApplications = function () {
            vm.flipActivityIndicator('isLoading');
            // should send some criteria
            ApplicationService.all()
                .then(function (applications) {
                    vm.flipActivityIndicator('isLoading');
                    vm.applications = applications;
                })
                .catch(function (error) {
                    vm.flipActivityIndicator('isLoading');
                    // TODO : REMOVE THIS
                    vm.applications = [
                        {
                            id: 1, name: 'App 1', owner: {
                                name: 'Owner 1',
                            },
                            active: true,
                        },
                        {
                            id: 2, name: 'App 2', owner: {
                                name: 'Owner 2',
                            },
                            active: false,
                        },
                    ];
                });
        };

        vm.toggleApplicationStatus = function (id) {
            vm.flipActivityIndicator('isSaving');
            return ApplicationService.toggleStatus(id)
                .then(function () {
                    vm.flipActivityIndicator('isSaving');
                    toastr.success(
                        MESSAGES_CONSTANTS.SUCCESS);
                })
                .then(vm.loadApplications)
                .catch(function (err) {
                    vm.flipActivityIndicator('isSaving');
                    toastr.error(MESSAGES_CONSTANTS.ERROR);
                });
        };

        vm.deleteApplication = function (id) {
            vm.flipActivityIndicator('isDeleting' + id);
            ApplicationService.delete(id)
                .then(function () {
                    vm.flipActivityIndicator('isDeleting' + id);
                    toastr.success(MESSAGES_CONSTANTS.SUCCESS);
                })
                .then(vm.loadApplications)
                .catch(function (err) {
                    vm.flipActivityIndicator('isDeleting' + id);
                    toastr.error(MESSAGES_CONSTANTS.ERROR);
                });
        };

        vm.init = function () {
            vm = angular.extend(vm, {
                ui: {
                    isSaving: false,
                    isLoading: false,
                },
                applications: [],
            });
            vm.loadApplications();
        };

    }
})(window.angular);
