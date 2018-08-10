angular.module('leadwireApp').directive('spinner', [
    function() {
        return {
            restrict: 'E',
            scope: {
                showLoading: '=isLoading',
            },
            template: '<i ng-if="showLoading" class="fa fa-spinner fa-spin" style="font-size:12px"></i>',
        };
    }]);
