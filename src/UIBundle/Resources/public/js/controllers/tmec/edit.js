(function (angular) {
    angular.module('leadwireApp')
        .controller('EditCompagnesController', [
            'TmecService',
            '$stateParams',
            'MESSAGES_CONSTANTS',
            '$state',
            'toastr',
            EditCompagnesCtrlFN,
        ]);

    /**
     * Handle add new application logic
     *
     */
    function EditCompagnesCtrlFN (
        TmecService,
        $stateParams,
        MESSAGES_CONSTANTS,
        $state,
        toastr,
    ) {
        var vm = this;

        vm.flipActivityIndicator = function (key) {
            vm.ui[key] = !vm.ui[key];
        };

        vm.loadCompagne = function (id) {
            TmecService.find(id)
                .then(function (compagne) {
                    vm.compagne = compagne;
                    vm.compagne.startDate = new Date(vm.compagne.startDate);
                    vm.compagne.endDate = new Date(vm.compagne.endDate);
                });

        };

        vm.edit = function () {
            vm.flipActivityIndicator('isSaving');

            vm.applications.forEach(element => {
                if(element.id === vm.compagne.application){
                    vm.compagne.applicationName = element.name;
                }
            });

            TmecService.update(vm.compagne)
                .then(function () {
                    vm.flipActivityIndicator('isSaving')
                    toastr.success(MESSAGES_CONSTANTS.SUCCESS);
                    $state.go('app.management.tmecs', {
                        id: vm.compagne.applicationId,
                    });
                })
                .catch(function () {
                    vm.flipActivityIndicator('isSaving')
                    toastr.error(MESSAGES_CONSTANTS.ERROR);
                });
        };

        function loadApplications(){
            TmecService.all()
            .then(function (applications) {
                vm.applications = applications;
            })
            .catch(function (error) {
            });
        }

        vm.init = function () {
            vm = angular.extend(vm, {
                ui: {
                    isSaving: false,
                    isLoading: false,
                },
                applications: [],
                compagne: {
                    version: '',
                    description: '',
                    startDate: '',
                    endDate: '',
                    application: '',
                    applicationName: ''
                },
            });
            vm.loadCompagne($stateParams.id);
            loadApplications();
        };

    }
})(window.angular);
