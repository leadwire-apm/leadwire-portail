(function (angular) {
    angular.module('leadwireApp')
        .controller('ListCompagnesController', [
            'TmecService',
            'toastr',
            'MESSAGES_CONSTANTS',
            '$state',
            '$stateParams',
            '$uibModal',
            ListCompagnesCtrlFN,
        ])
        .controller('ModalContentCtrl',[
            '$scope',
            '$uibModalInstance',
            ModalContentCtrlFN,
        ])

    function ModalContentCtrlFN($scope, $uibModalInstance){
        $scope.stepData = [
            { step: 1,  waiting: false, label:"Cadrage" },
            { step: 2,  waiting: false, label:"Devis"  },
            { step: 3,  waiting: false,  label:"CDC"  },
            { step: 4,  waiting: false, label:"R7J"  },
            { step: 5,  waiting: false, label:"Scipts Jdd"  },
            { step: 6,  waiting: false,  label:"PP"  },
            { step: 7,  waiting: false,  label:"Outils Tperf"},
            { step: 8,  waiting: false, label:"Tuning"},
            { step: 9,  waiting: false,  label:"Ref"},
            { step: 10, waiting: false, label:"Rapport"},
        ];
        
        $scope.stepProgress = 3;
        $scope.finish = false;
        
        $scope.next = function(){
            if($scope.stepProgress < $scope.stepData.length){
              $scope.stepProgress ++;
          }
        }
        
        $scope.previous = function(){
            if($scope.stepProgress > 0){
              $scope.stepProgress --;
          }
        }
        
        $scope.finished = function(){
            $scope.finish = true;
        }
    
      $scope.ok = function(){
        $uibModalInstance.close("Ok");
      }
       
      $scope.cancel = function(){
        $uibModalInstance.dismiss();
      } 
    }

    /**
     * Handle add new application logic
     *
     */
    function ListCompagnesCtrlFN (
        TmecService,
        toastr,
        MESSAGES_CONSTANTS,
        $state,
        $stateParams,
        $uibModal,
    ) {
        var vm = this;

        vm.applicationId = $stateParams.id;

        vm.openModal = function() {
            
            var modalInstance =  $uibModal.open({
              templateUrl: "tmec/tmecModal.html",
              controller: "ModalContentCtrl",
              size: 'lg',
            });
            
            modalInstance.result.then(function(response){
            });
            
        };


        vm.flipActivityIndicator = function (key) {
            vm.ui[key] = !vm.ui[key];
        };

        vm.delete = function (id) {
            swal(MESSAGES_CONSTANTS.SWEET_ALERT_VALIDATION())
                .then(function (willDelete) {
                    if (willDelete) {
                        vm.deleteCompagne(id);
                    } else {
                        swal.close();
                    }
                });
        };

        vm.load = function () {
            vm.flipActivityIndicator('isLoading');
            // should send some criteria
            TmecService.list({"application": vm.applicationId})
                .then(function (compagnes) {
                    vm.flipActivityIndicator('isLoading');
                    vm.compagnes = compagnes;
                })
                .catch(function (error) {
                    vm.flipActivityIndicator('isLoading');

                });
        };

        vm.updateType = function (id) {

        };

        vm.deleteCompagne = function (id) {
            TmecService.delete(id)
                .then(function () {
                    toastr.success(MESSAGES_CONSTANTS.SUCCESS);
                })
                .then(vm.load)
                .catch(function () {
                    toastr.success(MESSAGES_CONSTANTS.ERROR);
                });
        };

        vm.init = function () {
            vm = angular.extend(vm, {
                ui: {
                    isSaving: false,
                    isLoading: false,
                },
                compagnes: [],
            });
            vm.load();
        };

    }
})(window.angular);
