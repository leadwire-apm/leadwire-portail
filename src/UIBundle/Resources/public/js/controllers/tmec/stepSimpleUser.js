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

        TmecService.find(compagneId)
        .then(function (compagne) {
            vm.stepData = compagne.steps;
            vm.stepData.forEach(function(element){
                if(element.current){
                    vm.current = element;
                    vm.stepProgress = element.order - 1;
                    vm.max = element.order - 1;
                }
            });
        });

        vm.goToStep = function(stepNumber){
            if(stepNumber -1 > vm.stepProgress && stepNumber < vm.max){
                while(stepNumber -1 > vm.stepProgress)
                vm.next();
            }else if(stepNumber -1 < vm.stepProgress){
                while(stepNumber -1 < vm.stepProgress)
                vm.previous();
            }
        }


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
