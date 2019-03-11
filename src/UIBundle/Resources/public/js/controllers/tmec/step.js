(function (angular) {
    angular.module('leadwireApp')
        .controller('StepCtrl', [
            '$modalInstance',
            'TmecService',
            'compagneId',
            'MESSAGES_CONSTANTS',
            'UserService',
            '$localStorage',
             StepCtrlFN,
        ]);

    function StepCtrlFN($modalInstance, TmecService, compagneId, MESSAGES_CONSTANTS, UserService, $localStorage) {

        var vm = this;

        vm.isAdmin = UserService.isAdmin($localStorage.user);

        vm.current = {};
        vm.stepProgress = 0;
        vm.compagne = {};

        TmecService.listSteps(compagneId)
        .then(function (steps) {

            angular.forEach(steps, function(value, key) {
                if(value.current === true){
                    vm.current = value;
                   
                    if(value.order > 0)
                    vm.stepProgress = value.order - 1;
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

        TmecService.find(compagneId)
        .then(function (compagne) {
            vm.compagne = compagne;
            vm.compagne.startDate = new Date(vm.compagne.startDate);
            vm.compagne.endDate = new Date(vm.compagne.endDate);
            vm.completed =  vm.compagne.completed || false;

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
            }
        }

        vm.ok = function () {
            var error = 0;

            angular.forEach(vm.stepData, function(step) {
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
