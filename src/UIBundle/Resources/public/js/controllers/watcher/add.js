(function (angular) {
    angular.module('leadwireApp')
        .directive('emailValidator', emailValidator)
        .controller('AddWatcherCtrl', ['$modalInstance', 'DashboardService', '$scope', AddWatcherCtrl]);

    /**
     * Handle clustyer stats
     *
     */
    function AddWatcherCtrl($modalInstance, DashboardService, $scope) {

        var vm = this;
        var _url = "";

        vm.ui = {
            isSaving: false,
            isLoading: false,
        };
        vm.dashboardsList = [];
        vm.ui.isLoading = true;
        vm.watcher = { 
            'subject': 'Lead Wire Website Report',
            'delay': 10000,
            "res": "1280x900",
            "body" : "LEADWIRE Screenshot Report"
        };

        /**
         * get dashboards list
         */
        DashboardService.fetchDashboardsListByAppId($modalInstance.appId).then(function (dashboardsList) {
            Object.keys(dashboardsList).forEach(function (key) {
                dashboardsList[key].forEach(function (element) {
                    vm.dashboardsList.push({ ...element, key })
                })
            })
            vm.ui.isLoading = false;
            $scope.$apply();
        })

        getDashboardById = function(){
            var el = null;
            vm.dashboardsList.forEach(function(element){
                if( element.id === vm.dashboard)
                    el =  element;
            });

            return el;
        }

        vm.ok = function () {
            vm.watcher.url = `http://localhost:8008/app/kibana?security_tenant=${getDashboardById().tenant}#/dashboard/${getDashboardById().id}?embed=true${_url}`;
            $modalInstance.close("Ok");
        }

        vm.cancel = function () {
            $modalInstance.dismiss();
        }

        vm.startDate;
        vm.endDate;

        vm.options = {
            locale: { cancelLabel: 'Clear' },
            showDropdowns: true,
            singleDatePicker: false,
            alwaysShowCalendars: true,
            showCustomRangeLabel: false,
            linkedCalendars: false,
            minDate: "01/01/2010",
            //maxDate: moment(),
            timePicker: true,
            timePicker24Hour: true,
            ranges: {
                'Last 15 minutes': [moment().subtract(15, 'minutes'), moment()],
                'Last 30 minutes': [moment().subtract(30, 'minutes'), moment()],
                'Today': [moment(), moment()],
                'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                'Last Year': [moment().subtract(12, 'month'), moment()]
            }
        };

        function getInterval(int) {
            switch (int) {
                case "Last 15 minutes":
                    return "now-15m";
                case "Last 30 minutes":
                    return "now-30m";
                case "Today":
                    return "now/d";
                case "Last 7 Days":
                    return "now/w";
                case "Last 30 Days":
                    return "now-30d";
                case "Last Year":
                    return "now-1y";
                default:
                    return "now-15m";
            }
        }

        vm.sync = function () {

            var startDate = $('#range').data('daterangepicker').startDate.toISOString();
            var endDate = $('#range').data('daterangepicker').endDate.toISOString();
            var from = "now-15m";
            var to = "now"

            if ($('#range').data('daterangepicker').chosenLabel === null) {
                from = startDate;
                to = endDate;
            } else {
                from = getInterval($('#range').data('daterangepicker').chosenLabel);
                to = "now";
            }

            _url = `&_g=(time:(from:${from},to:${to}))`
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
