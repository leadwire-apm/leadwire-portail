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
                    if(vm.current.date && vm.current.date != ""){
                        vm.current.date = new Date(vm.current.date);
                    }

                    vm.stepProgress = element.order - 1;
                }
            });
        });

        vm.goToStep = function(stepNumber){
            if(stepNumber -1 > vm.stepProgress){
                while(stepNumber -1 > vm.stepProgress)
                vm.next();
            }else if(stepNumber -1 < vm.stepProgress){
                while(stepNumber -1 < vm.stepProgress)
                vm.previous();
            }
        }

        vm.next = function () {
            if (vm.stepProgress < vm.compagne.steps.length) {
                vm.stepProgress++;
                vm.current = vm.compagne.steps[vm.stepProgress];
                if(vm.current.date && vm.current.date != ""){
                    vm.current.date = new Date(vm.current.date);
                }
            }
        }

        vm.previous = function () {
            if (vm.stepProgress > 0) {
                vm.stepProgress--;
                vm.current = vm.compagne.steps[vm.stepProgress];

                if(vm.current.date && vm.current.date != ""){
                    vm.current.date = new Date(vm.current.date);
                }
            }
        }

        vm.updateSelection = function (type) {
            if(type === "waiting"){
                vm.current.waiting = true;
                vm.current.completed = false;
                vm.current.current = false;
            } else if(type === "completed"){
                vm.current.waiting = false;
                vm.current.completed = true;
                vm.current.current = false;
            }else if( type === "current"){
                vm.current.waiting = false;
                vm.current.completed = false;
                vm.current.current = true;
            }
        }

        vm.getCssStyle = function(){
            if(vm.current.completed){
                return 'completed';
            } else if(vm.current.waiting){
                return 'waiting'
            } else if (vm.current.current){
                return 'current';
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
