(function (angular) {
    angular.module('leadwireApp')
        .controller('ApplicationOverviewController', [ ApplicationsOverviewCtrlFN]);

    /**
     * Handle Applications stats
     *
     */
    function ApplicationsOverviewCtrlFN() {
        var vm = this;

        vm.load = function () {
        };



        vm.init = function () {
            vm = angular.extend(vm, {
                ui: {
                    isSaving: false,
                    isLoading: false,
                }
            });
            vm.load();
        };

    }
})(window.angular);
