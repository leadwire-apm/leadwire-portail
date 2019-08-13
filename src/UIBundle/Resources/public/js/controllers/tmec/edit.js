(function (angular) {
    angular.module('leadwireApp')
        .controller('EditCompagnesController', [
            'TmecService',
            '$stateParams',
            'MESSAGES_CONSTANTS',
            '$state',
            'toastr',
            'ApplicationFactory',
            'UserService',
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
        ApplicationFactory,
        UserService,
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

            vm.users.forEach(element => {
                if (element.id === vm.compagne.user) {
                    vm.compagne.userName = element.username;
                }

                if (element.id === vm.compagne.cp) {
                    vm.compagne.cpName = element.username;
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
            ApplicationFactory.findMyApplications()
            .then(function (applications) {
                vm.applications = applications.data;
            })
            .catch(function (error) {
            });
        }

        
        function loadUsers(){
            UserService.list()
            .then(function (users) {
                vm.users = users;
            })
            .catch(function (err) {
            });
        }

        vm.init = function () {
            vm = angular.extend(vm, {
                ui: {
                    isSaving: false,
                    isLoading: false,
                },
                applications: [],
                users: [],
                compagne: {
                    version: '',
                    description: '',
                    startDate: '',
                    endDate: '',
                    application: '',
                    applicationName: '',
                    user: '',
                    cp: '',
                    testEnvr:'',
                    nTir:'',
                    cpName: '',
                    userName: ''
                },
            });
            vm.loadCompagne($stateParams.id);
            loadApplications();
            loadUsers();
        };

    }
})(window.angular);
