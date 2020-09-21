(function (angular) {
    angular.module('leadwireApp')
        .controller('ManageApplicationsController', [
            'ApplicationService',
            'toastr',
            'MESSAGES_CONSTANTS',
            '$scope',
            ManageApplicationsCtrlFN,
        ]);

    /**
     * Handle add new application logic
     *
     */
    function ManageApplicationsCtrlFN (
        ApplicationService,
        toastr,
        MESSAGES_CONSTANTS,
        $scope
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

        vm.applyChanges = function(applicationId) {
            swal(MESSAGES_CONSTANTS.SWEET_ALERT_VALIDATION('This will update ElasticSearch indexes and Kibana dashboard for the current application.'))
                .then(function (willUpdate) {
                    if (willUpdate) {
                        vm.doApplyChanges(applicationId);
                    } else {
                        swal.close();
                    }
                });
        }

        vm.doApplyChanges = function(applicationId) {
            vm.flipActivityIndicator('isApplyingChanges' + applicationId);
            ApplicationService.applyChanges(applicationId)
            .then(function () {
                vm.flipActivityIndicator('isApplyingChanges' + applicationId);
                toastr.success(MESSAGES_CONSTANTS.SUCCESS);
            })
            .then(vm.loadApplications)
            .catch(function(err) {
                vm.flipActivityIndicator('isApplyingChanges' + applicationId);
                toastr.error(MESSAGES_CONSTANTS.ERROR);
            });
        }
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
                    vm.applications = [];
                });
        };

        vm.toggleApplicationStatus = function (id) {
            vm.flipActivityIndicator('isSaving');
            return ApplicationService.toggleEnabled(id)
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
                allApplications: false,
            });
            vm.loadApplications();
            $scope.$emit('update:title', "Admin settings");
        };

    }
})(window.angular);
