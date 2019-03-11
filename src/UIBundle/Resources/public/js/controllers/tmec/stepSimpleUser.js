(function (angular) {
    angular.module('leadwireApp')
        .controller('StepSimpleUserCtrl', [
            '$modalInstance',
            'TmecService',
            'compagneId',
            'MESSAGES_CONSTANTS',
             StepCtrlFN,
        ]);

    function StepCtrlFN($modalInstance, TmecService, compagneId, MESSAGES_CONSTANTS) {

        var vm = this;

        vm.current = {};
        vm.stepProgress = 0;
        vm.max = 0;

        TmecService.listSteps(compagneId)
        .then(function (steps) {

            angular.forEach(steps, function(value, key) {
                if(value.current === true){
                    vm.current = value;
                   
                    if(value.order > 0){
                        vm.stepProgress = value.order - 1;
                        vm.max = value.order - 1;
                    }
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

            if( vm.current.waiting === true){
                toastr.error(MESSAGES_CONSTANTS.GO_NEXT_STEP);
                return;
            }

            if (vm.stepProgress < vm.stepData.length) {
                vm.stepData[vm.stepProgress].current = false;
                vm.stepProgress++;
                vm.current = vm.stepData[vm.stepProgress];
                if(vm.stepProgress + 1 <= 10)
                vm.stepData[vm.stepProgress].current = true;
            }
        }

        vm.previous = function () {
            if (vm.stepProgress > 0) {
                vm.stepProgress--;
                vm.stepData[vm.stepProgress].current = true;
                vm.current = vm.stepData[vm.stepProgress];

                if(vm.stepProgress + 1 < 10)
                vm.stepData[vm.stepProgress+1].current = false;
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
