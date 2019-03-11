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
    function CtrlOverviewControllerFN (
        ApplicationService,
        TmecService,
        toastr,
        MESSAGES_CONSTANTS,
        $stateParams,
        $modal,
    ) {
    
        var vm = this;

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

        var getCompagnes = function(){
            vm.applications.forEach(application => {
                TmecService.list({"application": application.id, "completed": false})
                .then(function (compagnes) {
                    compagnes.forEach(compagne => {
                        compagne.steps = getSteps(compagne.id)
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

        var getSteps = function(compagneId){
            TmecService.listSteps(compagneId)
            .then(function (steps) {
                return steps;
            })
            .catch(function (error) {
                return [];
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
