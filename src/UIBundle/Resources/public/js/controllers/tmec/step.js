(function (angular) {
    angular.module('leadwireApp')
        .controller('StepCtrl', [
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
        vm.compagne = {};

        TmecService.find(compagneId)
        .then(function (compagne) {
            vm.compagne = compagne;
            vm.compagne.startDate = new Date(vm.compagne.startDate);
            vm.compagne.endDate = new Date(vm.compagne.endDate);
            vm.completed =  vm.compagne.completed || false;
            vm.compagne.steps.forEach(function(element){
                if(element.current){
                    vm.current = element;
                    vm.stepProgress = element.order - 1;
                }
            });
        });

        vm.next = function () {

            if( vm.current.waiting === true){
                toastr.error(MESSAGES_CONSTANTS.GO_NEXT_STEP);
                return;
            }

            if (vm.stepProgress < vm.compagne.steps.length) {
                vm.compagne.steps[vm.stepProgress].completed = true;
                vm.compagne.steps[vm.stepProgress].current = false;
                vm.stepProgress++;
                vm.current = vm.compagne.steps[vm.stepProgress];

                if(vm.stepProgress + 1 <= 10){
                    vm.compagne.steps[vm.stepProgress].current = true;
                }
            }
        }

        vm.previous = function () {
            if (vm.stepProgress > 0) {
                vm.compagne.steps[vm.stepProgress].completed = false;
                vm.stepProgress--;
                vm.compagne.steps[vm.stepProgress].current = true;
                vm.current = vm.compagne.steps[vm.stepProgress];

                if(vm.stepProgress + 1 < 10)
                vm.compagne.steps[vm.stepProgress+1].current = false;
            }
        }

        vm.finished = function () {
            if(vm.completed === true){
                swal(MESSAGES_CONSTANTS.COMPAGNE_VALIDATE)
                .then(function (willFinish) {
                    if (willFinish) {
                        vm.compagne.completed = true;
                    } else {
                        swal.close();
                    }
                });
            } else {
                vm.completed = false;
                vm.compagne.completed = false;
            }
        }

        vm.ok = function () {
            var error = 0;

            angular.forEach(vm.compagne.steps, function(step) {
                TmecService.updateStep(step)
                .then(function () {
                })
                .catch(function () {
                    error++;
                });
            });

            TmecService.update(vm.compagne)
            .then(function () {
            })
            .catch(function () {
                err++;
            });

            if(error > 0){
                toastr.error(MESSAGES_CONSTANTS.ERROR);
            }else{
                toastr.success(MESSAGES_CONSTANTS.SUCCESS);
            }
            
            $modalInstance.close("Ok");
        }

        vm.cancel = function () {
            $modalInstance.dismiss();
        }
    }

})(window.angular);
