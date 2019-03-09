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
        vm.stepProgress = 0;

        TmecService.listSteps(compagneId)
        .then(function (steps) {

            angular.forEach(steps, function(value, key) {
                if(value.current === true){
                    vm.current = value;
                    vm.stepProgress = value.order;
                }
            });

            if(vm.stepProgress === 0){
                steps[0].current = true;
            }
            vm.stepData = steps;
            console.log(vm.stepData);
        })
        .catch(function (error) {
        });

        vm.next = function () {
            if (vm.stepProgress < vm.stepData.length) {
                vm.stepData[vm.stepProgress].current = false;
                vm.stepProgress++;
                vm.stepData[vm.stepProgress].current = true;
            }
        }

        vm.previous = function () {
            if (vm.stepProgress > 0) {
                vm.stepProgress--;
                vm.stepData[vm.stepProgress].current = false;
                vm.stepData[vm.stepProgress-1].current = true;
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
