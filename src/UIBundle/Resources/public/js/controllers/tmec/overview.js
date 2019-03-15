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

            var label = "label label-danger";

            if (step.current  || step.completed) {
                label = "label label-success";
            } 
            
            if (step.waiting) {
                label = "label label-warning";
            } 

            return label;
        }

        vm.flipActivityIndicator = function (key) {
            vm.ui[key] = !vm.ui[key];
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


        vm.loadApplications = function () {
            vm.flipActivityIndicator('isLoading');

            getAllApplications(function(appIds){
                TmecService.list({ "completed": vm.all, "ids": appIds })
                .then(function (compagnes) {
                    vm.flipActivityIndicator('isLoading');
                    vm.compagnes = compagnes;
                    console.log(compagnes)
                })
                .catch(function (error) {
                    vm.flipActivityIndicator('isLoading');
                });
            })
        };

        vm.init = function () {
            vm = angular.extend(vm, {
                ui: {
                    isLoading: false
                },
                compagnes: [],
            });
            vm.loadApplications();
        }
    } 
})(window.angular);
