(function (angular) {
    angular.module('leadwireApp')
        .controller('ManageReportController', [
            '$sce',
            '$sessionStorage',
            '$scope',
            'CONFIG',
            ManageReportCtrlFN,
        ]);

    function ManageReportCtrlFN($sce, $sessionStorage, $scope, CONFIG) {
        var vm = this;
        vm.refresh = $sessionStorage.refresh || "0";

        $scope.startDate = $sessionStorage.date ? moment($sessionStorage.date.startDate) : moment();
        $scope.endDate = $sessionStorage.date ? moment($sessionStorage.date.endDate) : moment();

        if (vm.refresh !== "0") {
            var startDate = $scope.startDate.toISOString();
            var endDate = $scope.endDate.toISOString();
            var mode = $sessionStorage.mode
            var from = getInterval($sessionStorage.chosenLabel);
            var to = "now";
            var autoRefresh = "!f";
            var ref = (parseInt(vm.refresh) * 1000).toString()
            if (mode === "absolute") {
                from = startDate;
                to = endDate;
            }
            vm.setReportLink = $sce.trustAsResourceUrl(`${CONFIG.LEADWIRE_KIBANA_HOST}/app/sentinl#/reports?embed=true&_g=(refreshInterval:(pause:${autoRefresh},value:${ref}),time:(from:'${from}',mode:${mode},to:'${to}'))`);
            $scope.$apply()
        } else {
            vm.setReportLink = $sce.trustAsResourceUrl(`${CONFIG.LEADWIRE_KIBANA_HOST}/app/sentinl#/reports?embed=true&_g=(refreshInterval:(pause:!t,value:${vm.refresh * 1000}),time:(from:now-15m,mode:quick,to:now))`);
            $scope.$apply()
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

        $scope.sync = function () {

            var startDate = $('#range').data('daterangepicker').startDate.toISOString();
            var endDate = $('#range').data('daterangepicker').endDate.toISOString();
            var mode = "absolute";//quick
            var from = "now-15m";
            var to = "now";
            var autoRefresh = "!t";

            $sessionStorage.date = $('#range').data('daterangepicker');
            $sessionStorage.refresh = vm.refresh;
            $sessionStorage.chosenLabel = $('#range').data('daterangepicker').chosenLabel;
            $sessionStorage.mode = mode;

            if ($('#range').data('daterangepicker').chosenLabel === null) {
                mode = "absolute";
                from = startDate;
                to = endDate;
            } else {
                mode = "quick";
                from = getInterval($('#range').data('daterangepicker').chosenLabel);
                to = "now";
                $sessionStorage.mode = mode;
            }
            if (vm.refresh !== "0") {
                autoRefresh = "!f";
            }

            var ref = (parseInt(vm.refresh) * 1000).toString()
            vm.setReportLink = $sce.trustAsResourceUrl(`${CONFIG.LEADWIRE_KIBANA_HOST}/app/sentinl#/reports?embed=true&_g=(refreshInterval:(pause:${autoRefresh},value:${ref}),time:(from:'${from}',mode:${mode},to:'${to}'))`);
        }

    }
})(window.angular);
