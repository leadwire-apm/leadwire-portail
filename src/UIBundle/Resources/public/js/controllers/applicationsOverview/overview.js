(function (angular) {
    angular.module('leadwireApp')
        .controller('ApplicationOverviewController', ['ApplicationFactory','ApplicationService', ApplicationsOverviewCtrlFN]);

    /**
     * Handle Applications stats
     *
     */
    function ApplicationsOverviewCtrlFN(ApplicationFactory, ApplicationService) {
        
        var vm = this;

        vm.load = function () {
            ApplicationFactory.findMyApplications().then(function (response) {
                vm.applications = response.data;
                vm.applications.forEach(application => {
                    ApplicationService.getApplicationDocumentsCount(application.name).then(function (response){
                        application.count = response.count;
                    })
                });
                
            }).catch(function () {
            });
        };

        vm.init = function () {
            vm = angular.extend(vm, {
                ui: {
                    isSaving: false,
                    isLoading: false,
                },
                applications :[]
            });
            vm.load();
        };

    }
})(window.angular);
