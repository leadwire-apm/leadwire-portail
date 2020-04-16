(function (angular) {
    angular.module('leadwireApp')
        .controller('ApplicationOverviewController', ['ApplicationFactory', 'ApplicationService', ApplicationsOverviewCtrlFN]);

    /**
     * Handle Applications stats
     *
     */
    function ApplicationsOverviewCtrlFN(ApplicationFactory, ApplicationService) {

        var vm = this;

        var SI_SYMBOL = [" Transactions", 
                           " k Transactions", 
                           " M Transactions", 
                           " G Transactions", 
                           " T Transactions", 
                           " P Transactions", 
                           " E Transactions"];

        vm.abbreviateNumber = function (number) {

            // what tier? (determines SI symbol)
            var tier = Math.log10(number) / 3 | 0;

            // if zero, we don't need a suffix
            if (tier == 0) return  " 0 Transactions";

            // get suffix and determine scale
            var suffix = SI_SYMBOL[tier];
            var scale = Math.pow(10, tier * 3);

            // scale the number
            var scaled = number / scale;

            // format number and add suffix
            return (scaled.toFixed(1) + suffix);
        }

        vm.load = function () {
            ApplicationFactory.findMyApplications().then(function (response) {
                vm.applications = response.data;
                vm.applications.forEach(application => {
                    ApplicationService.getApplicationDocumentsCount(application.name).then(function (response) {
                        if(response)
                            application.count = vm.abbreviateNumber(response.count);
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
                applications: []
            });
            vm.load();
        };

    }
})(window.angular);
