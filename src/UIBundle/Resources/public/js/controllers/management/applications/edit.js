(function (angular, moment) {
    angular.module('leadwireApp')
        .controller('ManageApplicationsEditController', [
            'ApplicationTypeFactory',
            'ApplicationFactory',
            'toastr',
            '$stateParams',
            'MESSAGES_CONSTANTS',
            '$state',
            '$scope',
            'DashboardService',
            ManageApplicationsEditCtrlFN,
        ]);

    /**
     * Handle add new application logic
     *
     */
    function ManageApplicationsEditCtrlFN (
        ApplicationTypeFactory,
        ApplicationFactory,
        toastr,
        $stateParams,
        MESSAGES_CONSTANTS,
        $state,
        $scope,
        DashboardService,
    ) {
        var vm = this;

        vm.icons = [{"name":"fas fa-wrench", "div":"<i class='fas fa-wrench'></i>"}, 
        {"name":"fas fa-wrench", "div":'<i class="fad fa-angel"></i>'}, 
        {"name":"fa fa-table2", "div":'<i class="fad fa-angel"></i>'}];


        ApplicationFactory.get($stateParams.id, 'edit').then(function(res) {
            vm.application = res.data;
        });

        vm.loadApplicationTypes = function () {
            ApplicationTypeFactory.findAll()
                .then(function (response) {
                    vm.applicationTypes = response.data;
                });
        };

        vm.getDashboardByTheme = function(name){
            return vm.dashboardsList[name];
        }

        /**
         * get dashboards list
         */
        DashboardService.fetchDashboardsListByAppId($stateParams.id).then(function(dashboardsList){
            vm.dashboardsList = dashboardsList;
            vm.dashboardsNameList = Object.keys(dashboardsList);
            $scope.$apply();
        })

        vm.updateDashboards = function() {
            ApplicationFactory.updateDashbaords(vm.application.id, vm.dashboardsList)
            .then(function() {
                vm.flipActivityIndicator();
                toastr.success(MESSAGES_CONSTANTS.EDIT_APP_SUCCESS);
                $state.go('app.management.applications');
            })
            .catch(function(error) {
                vm.flipActivityIndicator();
                toastr.error(
                    error.message ||
                        MESSAGES_CONSTANTS.EDIT_APP_FAILURE ||
                        MESSAGES_CONSTANTS.ERROR
                );
            });
        }
        

    
        vm.editApp = function() {
            vm.flipActivityIndicator();
            const updatedApp = angular.extend({},vm.application);
            delete updatedApp.invitations;
            delete updatedApp.owner;

            ApplicationFactory.update(vm.application.id, updatedApp)
                .then(function() {
                    vm.updateDashboards();
                })
                .catch(function(error) {
                    vm.flipActivityIndicator();
                    toastr.error(
                        error.message ||
                            MESSAGES_CONSTANTS.EDIT_APP_FAILURE ||
                            MESSAGES_CONSTANTS.ERROR
                    );
                });
        };

        vm.flipActivityIndicator = function() {
            vm.ui.isSaving = !vm.ui.isSaving;
        };

        vm.onLoad = function () {
            vm = angular.extend(vm, {
                ui : {
                    isSaving: false,
                    isEditing: true,
                },
            });
            vm.loadApplicationTypes();
        };
    }
})(window.angular, window.moment);
