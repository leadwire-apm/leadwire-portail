(function (angular) {
    angular.module('leadwireApp')
        .controller('ApplicationOverviewController', ['ApplicationFactory', ApplicationsOverviewCtrlFN]);

    /**
     * Handle Applications stats
     *
     */
    function ApplicationsOverviewCtrlFN(ApplicationFactory) {
        
        var vm = this;

        vm.load = function () {
            ApplicationFactory.findMyApplications().then(function (response) {
                vm.applications = response.data;
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
