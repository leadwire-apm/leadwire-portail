(function (angular) {
    angular.module('leadwireApp')
        .controller('OverviewController', ['OverviewService', 'toastr', OverviewCtrlFN ]);

    /**
     * Handle clustyer stats
     *
     */
    function OverviewCtrlFN(OverviewService, toastr) {
        var vm = this;

        vm.load = function () {
            OverviewService.getClusterInformations()
            .then(function (stats) {
                vm.stats = stats;
                if(stats.status === "yellow") {
                    vm.border = "border-warning";
                    vm.text = "text-warning"
                } else if(stats.status === "red"){
                    vm.border = "border-danger";
                    vm.text = "text-danger"
                }else if(stats.status === "green"){
                    vm.border = "border-success";
                    vm.text = "text-success"
                }
            })
            .catch(function (error) {
            });
        };

        vm.init = function () {
            vm = angular.extend(vm, {
                ui: {
                    isSaving: false,
                    isLoading: false,
                },
                stats: {},
                border:"border-success",
                text: "text-success"
            });
            vm.load();
        };
    }
})(window.angular);
