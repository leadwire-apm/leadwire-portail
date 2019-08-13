(function (angular) {
    angular.module('leadwireApp')
        .controller('AddCompagnesController', [
            'TmecService',
            'toastr',
            'MESSAGES_CONSTANTS',
            '$state',
            '$stateParams',
            'ApplicationFactory',
            'UserService',
            AddCompagnesCtrlFN,
        ])
    /**
     * Handle add new compagnes logic
     *
     */
    function AddCompagnesCtrlFN(
        TmecService,
        toastr,
        MESSAGES_CONSTANTS,
        $state,
        $stateParams,
        ApplicationFactory,
        UserService,
    ) {
        var vm = this;

        vm.flipActivityIndicator = function (key) {
            vm.ui[key] = !vm.ui[key];
        };

        vm.save = function () {
            vm.flipActivityIndicator('isSaving');

            vm.applications.forEach(element => {
                if (element.id === vm.compagne.application) {
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

            TmecService.create(vm.compagne)
                .then(function () {
                    vm.flipActivityIndicator('isSaving');
                    toastr.success(MESSAGES_CONSTANTS.SUCCESS);
                    $state.go('app.management.tmecs');
                })
                .catch(function (error) {
                    vm.flipActivityIndicator('isSaving');
                    toastr.error(error.message || MESSAGES_CONSTANTS.ERROR);
                });
        };

        function loadApplications() {
            ApplicationFactory.findMyApplications()
                .then(function (applications) {
                    vm.applications = applications.data;
                })
                .catch(function (error) {
                });
        }

        function loadUsers() {
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
                    testEnvr: '',
                    nTir: '',
                    cpName: '',
                    userName: ''
                },
            });
            loadApplications();
            loadUsers();
        };
    }
})(window.angular);
