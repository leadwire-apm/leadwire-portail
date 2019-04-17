(function (angular) {
    angular.module('leadwireApp')
        .controller('AddCompagnesController', [
            'TmecService',
            'toastr',
            'MESSAGES_CONSTANTS',
            '$state',
            '$stateParams',
            'ApplicationFactory',
            AddCompagnesCtrlFN,
        ]);

    /**
     * Handle add new compagnes logic
     *
     */
    function AddCompagnesCtrlFN (
        TmecService,
        toastr,
        MESSAGES_CONSTANTS,
        $state,
        $stateParams,
        ApplicationFactory,
    ) {
        var vm = this;

        vm.flipActivityIndicator = function (key) {
            vm.ui[key] = !vm.ui[key];
        };

        vm.save = function () {
           vm.flipActivityIndicator('isSaving');
          
           vm.applications.forEach(element => {
               if(element.id === vm.compagne.application){
                   vm.compagne.applicationName = element.name;
               }
           });

           TmecService.create(vm.compagne)
                .then(function () {
                    vm.flipActivityIndicator('isSaving');
                    toastr.success(MESSAGES_CONSTANTS.SUCCESS);
                    $state.go('app.campagne.tmecs');
                })
                .catch(function (error) {
                    vm.flipActivityIndicator('isSaving');
                    toastr.error(error.message || MESSAGES_CONSTANTS.ERROR);
                });
        };

        function loadApplications(){
            ApplicationFactory.findMyApplications()
            .then(function (applications) {
                vm.applications = applications.data;
            })
            .catch(function (error) {
            });
        }

        vm.init = function () {
            vm = angular.extend(vm, {
                ui: {
                    isSaving: false,
                },
                applications: [],
                compagne: {
                    version: '',
                    description: '',
                    startDate: '',
                    endDate: '',
                    application:'',
                    applicationName:''
                },
            });
            loadApplications();
        };
    }
})(window.angular);
