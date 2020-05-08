(function (angular) {
    angular.module('leadwireApp')
        .directive('emailValidator', emailValidator)
        .controller('AddWatcherCtrl', [
            '$modalInstance',
            'DashboardService',
            'WatcherService',
            'toastr',
            'MESSAGES_CONSTANTS',
            '$scope', AddWatcherCtrl]);

    /**
     * Handle clustyer stats
     *
     */
    function AddWatcherCtrl(
        $modalInstance,
        DashboardService,
        WatcherService,
        toastr,
        MESSAGES_CONSTANTS,
        $scope) {

        var vm = this;
        var _url = "";

        vm.ui = {
            isSaving: false,
            isLoading: false,
        };
        vm.dashboardsList = [];
        vm.ui.isLoading = true;
        vm.startDate;
        vm.endDate;

        vm.watcher = {
            'subject': 'Lead Wire Website Report',
            'delay': 10000,
            "res": "1280x900",
            "body": "LEADWIRE Screenshot Report",
            "fromDate": "",
            "toDate": "now",
            "envId": $modalInstance.envId,
            "appId": $modalInstance.appId,
            "enabled": true
        };

        vm.timerangeList = [
            {key:"", value:"use dashboard defaults"},
            {key:"now-15m", value:"Last 15 minutes"},
            {key:"now-30m", value:"Last 30 minutes"},
            {key:"now-4h", value:"Last 4 hours"},
            {key:"now/d", value:"Today "},
            {key:"now/w", value:"Last 7 Days "},
            {key:"now-30d", value:"Last 30 Days"},
            {key:"now-1y", value:"Last Year"},
        ];

        if ($modalInstance.watcher) {
            vm.watcher = $modalInstance.watcher;
        }

        var tenant = `${$modalInstance.envName +"-app-"+ $modalInstance.appName}`;

        /**
         * get dashboards list
         */
        DashboardService.fetchDashboardsAllListByAppId($modalInstance.appId).then(function (dashboardsList) {
            Object.keys(dashboardsList).forEach(function (k) {
                Object.keys(dashboardsList[k]).forEach(function (key) {
                    dashboardsList[k][key].forEach(function (element) {
                        vm.dashboardsList.push({ ...element, key, 'group': k })
                    })
                })
            })
            vm.ui.isLoading = false;
            $scope.$apply();
        })

        vm.setTenant = function () {
            vm.dashboardsList.map( el => {
                if(el.id === vm.watcher.dashboard){
                    if(el.group === 'custom')
                        tenant = `${$modalInstance.envName +"-shared-"+ $modalInstance.appName}`;
                    else
                        tenant = `${$modalInstance.envName +"-app-"+ $modalInstance.appName}`;
                }
            })

        }

        vm.ok = function () {
            vm.watcher.url = `http://localhost:8008/app/kibana?security_tenant=${tenant}#/dashboard/${vm.watcher.dashboard}?embed=true${_url}`;
            if(vm.watcher.fromDate !== ""){
                vm.watcher.url += `&_g=(time:(from:${vm.watcher.fromDate},to:now))`;
            }
            WatcherService.saveOrUpdate(vm.watcher)
                .then(function (response) {
                    toastr.success(MESSAGES_CONSTANTS.SUCCESS);
                    $modalInstance.close("Ok");
                })
                .catch(function (err) {
                    toastr.error(err.message);
                })
        }

        vm.cancel = function ($event) {
            $modalInstance.close("Ok");
            $event.stopPropagation();
        }
    }

    function emailValidator() {
        return {
            require: 'ngModel',
            link: function (scope, element, attrs, ctrl) {
                var emailsRegex = /^[\W]*([\w+\-.%]+@[\w\-.]+\.[A-Za-z]{2,4}[\W]*,{1}[\W]*)*([\w+\-.%]+@[\w\-.]+\.[A-Za-z]{2,4})[\W]*$/;
                ctrl.$parsers.unshift(function (viewValue) {
                    if (emailsRegex.test(viewValue)) {
                        ctrl.$setValidity('emailValidator', true);
                        return viewValue;
                    } else {
                        ctrl.$setValidity('emailValidator', false);
                        return undefined;
                    }
                });
            }
        }
    }

})(window.angular);
