(function (angular) {
    angular.module('leadwireApp').controller('dashboardCtrl', [
        '$sce',
        '$scope',
        '$rootScope',
        'DashboardService',
        '$localStorage',
        '$state',
        '$stateParams',
        dashboardCtrl,
    ]);

    function dashboardCtrl($sce, $scope, $rootScope, DashboardService, $localStorage, $state, $stateParams) {
        var vm = this;

        trustSrc = function (src) {
            return $sce.trustAsResourceUrl(src);
        }

        vm.refresh = $localStorage.refresh || "0";
        vm.applications = $localStorage.applications;

        $scope.startDate = $localStorage.date ? moment($localStorage.date.startDate) : moment();
        $scope.endDate = $localStorage.date ? moment($localStorage.date.endDate) : moment();

        vm.onLoad = function () {
            vm.isLoading = true;
            $rootScope.menus = $localStorage.currentApplicationMenus;
            $scope.$watch(
                function () {
                    $el = document.querySelector('#L' + $stateParams.id.replace(/-/g, ""));
                    return $el;
                },
                function (newValue, oldValue) {
                    if (newValue != null) {
                        newValue.parentNode.parentNode.parentNode.childNodes[1].querySelector('a').click();
                    }
                }
            );

            if (vm.refresh !== "0") {
                var startDate = $scope.startDate.toISOString();
                var endDate = $scope.endDate.toISOString();
                var mode = $localStorage.mode
                var from = getInterval($localStorage.chosenLabel);
                var to = "now";
                var autoRefresh = "!f";
                var ref = (parseInt(vm.refresh) * 1000).toString()
                if (mode === "absolute") {
                    from = startDate;
                    to = endDate;
                }
                vm.dashboardLink = trustSrc(DashboardService.getDashboard($state.params.id, $state.params.tenant) + `?embed=true&_g=(refreshInterval:(pause:${autoRefresh},value:${ref}),time:(from:'${from}',mode:${mode},to:'${to}'))`);
                $scope.$apply()
            } else {
                vm.dashboardLink = trustSrc(DashboardService.getDashboard($state.params.id, $state.params.tenant) + `?embed=true&_g=(refreshInterval:(pause:!t,value:${vm.refresh * 1000}),time:(from:now-15m,mode:quick,to:now))`);
            }

        };

        vm.goToEditDashboard = function(){
            window.open(DashboardService.getDashboard($state.params.id, $state.params.tenant) + `?_g=(refreshInterval:(pause:!t,value:${vm.refresh * 1000}),time:(from:now-15m,mode:quick,to:now))`);
        }

        vm.isAdmin = function () {
            var access = false;
            var user = $localStorage.user;
            var selectedApp = $localStorage.selectedApp;
            access = user.roles.indexOf("ROLE_SUPER_ADMIN") >= 0 || user.roles.indexOf("ROLE_ADMIN") >= 0;
            if(!access){
                Object.keys(user.acl).forEach(element => {
                    if (user.acl[element][selectedApp.id].ACCESS === "ADMIN" || user.acl[element][selectedApp.id].ACCESS === "EDITOR") {
                        access = true;
                    }
                });
                
            }
            return access;
        }

        function getInterval(int) {
            switch (int) {
                case "Last 15 minutes":
                    return "now-15m";
                case "Last 30 minutes":
                    return "now-30m";
                case "Today":
                    return "now/d";
                case "This Week":
                    return "now/w";
                case "Last 7 Days":
                    return "now-7d";
                case "Last 30 Days":
                    return "now-30d";
                case "Last Year":
                    return "now-1y";
                default:
                    return "now-15m";
            }
        }

        $scope.sync = function () {

            var startDate = $('#range').data('daterangepicker').startDate.toISOString();
            var endDate = $('#range').data('daterangepicker').endDate.toISOString();
            var mode = "absolute";//quick
            var from = "now-15m";
            var to = "now";
            var autoRefresh = "!t";

            $localStorage.date = $('#range').data('daterangepicker');
            $localStorage.refresh = vm.refresh;
            $localStorage.chosenLabel = $('#range').data('daterangepicker').chosenLabel;
            $localStorage.mode = mode;

            if ($('#range').data('daterangepicker').chosenLabel === null) {
                mode = "absolute";
                from = startDate;
                to = endDate;
            } else {
                mode = "quick";
                from = getInterval($('#range').data('daterangepicker').chosenLabel);
                to = "now";
                $localStorage.mode = mode;
            }
            if (vm.refresh !== "0") {
                autoRefresh = "!f";
            }

            var ref = (parseInt(vm.refresh) * 1000).toString()

            vm.dashboardLink = trustSrc(DashboardService.getDashboard($state.params.id, $state.params.tenant) + `?_g=(refreshInterval:(pause:${autoRefresh},value:${ref}),time:(from:'${from}',mode:${mode},to:'${to}'))`);
        }

        $scope.options = {
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
                'This Week': [moment().startOf('isoWeek'), moment()],
                'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                'Last Year': [moment().subtract(12, 'month'), moment()]

            }
        };

        vm.onLoad();
    }
})(window.angular);
