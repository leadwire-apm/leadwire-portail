(function (angular) {
    angular.module('leadwireApp')
        .controller('TmecOverviewController', [
            'ApplicationService',
            'TmecService',
            'toastr',
            'MESSAGES_CONSTANTS',
            '$stateParams',
            '$modal',
            CtrlOverviewControllerFN,
        ]);

    /**
     * Handle add new application logic
     *
     */
    function CtrlOverviewControllerFN(
        ApplicationService,
        TmecService,
        toastr,
        MESSAGES_CONSTANTS,
        $stateParams,
        $modal,
    ) {

        var vm = this;

        vm.getClass = function (step) {
            if (step.waiting) {
                return "label label-warning";
            } else if (step.current) {
                return "label label-warning";
            }
        }

        vm.flipActivityIndicator = function (key) {
            vm.ui[key] = !vm.ui[key];
        };


        vm.loadApplications = function () {
            vm.flipActivityIndicator('isLoading');
            // should send some criteria
            TmecService.all()
                .then(function (applications) {
                    vm.flipActivityIndicator('isLoading');
                    vm.applications = applications;
                    getCompagnes();
                })
                .catch(function (error) {
                    vm.flipActivityIndicator('isLoading');
                    vm.applications = [];
                });
        };

        var getCompagnes = function () {
            vm.applications.forEach(application => {
                TmecService.list({ "application": application.id, "completed": false })
                    .then(function (compagnes) {
                        compagnes.forEach(compagne => {
                            getSteps(compagne.id, function (steps) {
                                compagne.steps = stepRec(steps);
                            })
                        });
                        application.compagnes = compagnes;
                        vm.flipActivityIndicator('isLoading');
                        console.log(vm.applications)
                    })
                    .catch(function (error) {
                        vm.flipActivityIndicator('isLoading');
                    });
            });
        }

        var getSteps = function (compagneId, cb) {
            TmecService.listSteps(compagneId)
                .then(function (steps) {
                    cb(steps);
                })
                .catch(function (error) {
                    cb([]);
                });
        }

        var stepRec = function (steps) {
            var i = 0;
            steps.forEach(step => {
                if (step.current === true) {
                    i = step.order;
                }
            });

            steps.forEach(step => {
                if (step.order <= i) {
                    step.current = true;
                }
            });
        }

        vm.init = function () {
            vm = angular.extend(vm, {
                ui: {
                    isLoading: false
                },
                applications: [],
            });
            vm.loadApplications();
        }
    }
})(window.angular);
