(function (angular) {
    angular.module('leadwireApp')
        .controller('ListCompagnesController', [
            'TmecService',
            'toastr',
            'MESSAGES_CONSTANTS',
            '$state',
            '$stateParams',
            '$modal',
            ListCompagnesCtrlFN,
        ])
        .controller('ModalContentCtrl',[
            '$modalInstance',
            ModalContentCtrlFN,
        ])

    function ModalContentCtrlFN($modalInstance){
        var vm = this;
        vm.stepData = [
            { id: 1,  waiting: false, label:"Cadrage" },
            { id: 2,  waiting: false, label:"Devis"  },
            { id: 3,  waiting: false,  label:"CDC"  },
            { id: 4,  waiting: false, label:"R7J"  },
            { id: 5,  waiting: false, label:"Scipts Jdd"  },
            { id: 6,  waiting: false,  label:"PP"  },
            { id: 7,  waiting: false,  label:"Outils Tperf"},
            { id: 8,  waiting: false, label:"Tuning"},
            { id: 9,  waiting: false,  label:"Ref"},
            { id: 10, waiting: false, label:"Rapport"},
        ];
        
        vm.stepProgress = 3;
        vm.finish = false;
        
        vm.next = function(){
            if(vm.stepProgress < vm.stepData.length){
              vm.stepProgress ++;
          }
        }
        
        vm.previous = function(){
            if(vm.stepProgress > 0){
              vm.stepProgress --;
          }
        }
        
        vm.finished = function(){
            vm.finish = true;
        }
    
      vm.ok = function(){
        $modalInstance.close("Ok");
      }
       
      vm.cancel = function(){
        $modalInstance.dismiss();
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
        $modal,
    ) {
        var vm = this;

        vm.applicationId = $stateParams.id;

        vm.openModal = function() {

            var modalInstance = $modal.open({
                size: 'lg',
                templateUrl: 'tmec/tmecModal.html',
                controller: 'ModalContentCtrl',
                controllerAs: 'ctrl',
            });

            modalInstance.result.then(function () {
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
