(function (angular) {
    angular.module('leadwireApp')
        .controller('StepCtrl', [
            '$modalInstance',
            'compagneId',
            'TmecService',
             StepCtrlFN,
        ]);

    function StepCtrlFN($modalInstance, TmecService, compagneId) {

        var vm = this;
        console.log(compagneId)
        vm.current = {};
        
        vm.stepData = [
            { id: 1, comment:"", waiting: false, label: "Cadrage" },
            { id: 2, comment:"", waiting: false, label: "Devis" },
            { id: 3, comment:"", waiting: false, label: "CDC" },
            { id: 4, comment:"", waiting: false, label: "R7J" },
            { id: 5, comment:"", waiting: false, label: "Scipts Jdd" },
            { id: 6, comment:"", waiting: false, label: "PP" },
            { id: 7, comment:"", waiting: false, label: "Outils Tperf" },
            { id: 8, comment:"", waiting: false, label: "Tuning" },
            { id: 9, comment:"", waiting: false, label: "Ref" },
            { id: 10, comment:"", waiting: false, label: "Rapport" },
        ];

        vm.stepProgress = 0;

        vm.current = vm.stepData[vm.stepProgress];

        vm.finish = false;

        vm.next = function () {
            if (vm.stepProgress < vm.stepData.length) {
                vm.stepProgress++;
            }
        }

        vm.previous = function () {
            if (vm.stepProgress > 0) {
                vm.stepProgress--;
            }
        }

        vm.finished = function () {
            vm.finish = true;
        }

        vm.ok = function () {
            $modalInstance.close("Ok");
        }

        vm.cancel = function () {
            $modalInstance.dismiss();
        }
    }

})(window.angular);
