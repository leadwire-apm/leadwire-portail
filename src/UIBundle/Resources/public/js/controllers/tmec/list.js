(function (angular) {
    angular.module('leadwireApp')
        .controller('ListCompagnesController', [
            'TmecService',
            'toastr',
            'MESSAGES_CONSTANTS',
            '$stateParams',
            '$modal',
            ListCompagnesCtrlFN,
        ]);

    /**
     * Handle add new application logic
     *
     */
    function ListCompagnesCtrlFN(
        TmecService,
        toastr,
        MESSAGES_CONSTANTS,
        $stateParams,
        $modal,
    ) {
        var vm = this;

        vm.applicationId = $stateParams.id;
        vm.all = false;

        vm.openModal = function (id, from) {

            var modalInstance = {}

            if (angular.isDefined(from) && from) {
                modalInstance = $modal.open({
                    size: 'lg',
                    templateUrl: 'tmec/step.html',
                    controller: 'StepCtrl',
                    controllerAs: 'ctrl',
                    resolve: {
                        compagneId: function () {
                            return id;
                        }
                    }
                });

                modalInstance.result.then(function () {
                });
            } else {
                modalInstance = $modal.open({
                    size: 'lg',
                    templateUrl: 'tmec/stepSimpleUser.html',
                    controller: 'StepSimpleUserCtrl',
                    controllerAs: 'ctrl',
                    resolve: {
                        compagneId: function () {
                            return id;
                        }
                    }
                });
            }

        };

        vm.flipActivityIndicator = function (key) {
            vm.ui[key] = !vm.ui[key];
        };

        vm.delete = function (id) {
            swal(MESSAGES_CONSTANTS.SWEET_ALERT_VALIDATION())
                .then(function (willDelete) {
                    if (willDelete) {
                        vm.deleteCompagne(id);
                    } else {
                        swal.close();
                    }
                });
        };

        getAllApplications = function (cb) {
            TmecService.all()
                .then(function (applications) {
                    var appIds = [];
                    applications.forEach(application => {
                        appIds.push(application.id)
                    });
                    cb(appIds)
                })
                .catch(function (error) {
                });
        }

        vm.load = function () {
            vm.flipActivityIndicator('isLoading');

            getAllApplications(function(appIds){
                TmecService.list({ "completed": vm.all, "ids": appIds })
                .then(function (compagnes) {
                    vm.flipActivityIndicator('isLoading');
                    vm.compagnes = compagnes;
                })
                .catch(function (error) {
                    vm.flipActivityIndicator('isLoading');
                });
            })
        };

        vm.deleteCompagne = function (id) {
            TmecService.delete(id)
                .then(function () {
                    toastr.success(MESSAGES_CONSTANTS.SUCCESS);
                })
                .then(vm.load)
                .catch(function () {
                    toastr.success(MESSAGES_CONSTANTS.ERROR);
                });
        };

        vm.init = function () {
            vm = angular.extend(vm, {
                ui: {
                    isSaving: false,
                    isLoading: false,
                },
                compagnes: [],
            });
            vm.load();
        };

    }
})(window.angular);
