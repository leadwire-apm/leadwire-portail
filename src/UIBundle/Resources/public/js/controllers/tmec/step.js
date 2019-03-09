(function (angular) {
    angular.module('leadwireApp')
        .controller('StepCtrl', [
            '$modalInstance',
            'TmecService',
            'compagneId',
             StepCtrlFN,
        ]);

    function StepCtrlFN($modalInstance, TmecService, compagneId) {

        var vm = this;

        vm.current = {};

        vm.stepData = [
            { order: 1,  comment:"", waiting: false, current: false, label: "Cadrage" },
            { order: 2,  comment:"", waiting: false, current: false, label: "Devis" },
            { order: 3,  comment:"", waiting: false, current: false, label: "CDC" },
            { order: 4,  comment:"", waiting: false, current: false, label: "R7J" },
            { order: 5,  comment:"", waiting: false, current: false, label: "Scipts Jdd" },
            { order: 6,  comment:"", waiting: false, current: false, label: "PP" },
            { order: 7,  comment:"", waiting: false, current: false, label: "Outils Tperf" },
            { order: 8,  comment:"", waiting: false, current: false, label: "Tuning" },
            { order: 9,  comment:"", waiting: false, current: false, label: "Ref" },
            { order: 10, comment:"", waiting: false, current: false, label: "Rapport" },
        ];

        vm.stepProgress = 0;
        vm.current = vm.stepData[vm.stepProgress];

        TmecService.listSteps(compagneId)
        .then(function (steps) {
            //vm.stepData = steps;
            console.log(steps);
        })
        .catch(function (error) {
        });

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
