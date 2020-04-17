(function (angular) {
    angular.module('leadwireApp')
        .controller('AddWatcherCtrl', ['$modalInstance', AddWatcherCtrl]);

    /**
     * Handle clustyer stats
     *
     */
    function AddWatcherCtrl($modalInstance) {
        var vm = this;
    
        vm.watcher = {'subject': 'Lead Wire Website Report'};

        vm.ok = function () {
            $modalInstance.close("Ok");
        }

        vm.cancel = function () {
            $modalInstance.dismiss();
        }

        vm.startDate;
        vm.endDate;

        vm.dashboardsList = [{"title": "test 1"}, {"title": "test 2"}]

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

            console.log("#########", from, to);
        }
    }
})(window.angular);
