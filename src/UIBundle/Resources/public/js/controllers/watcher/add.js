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
    }
})(window.angular);
