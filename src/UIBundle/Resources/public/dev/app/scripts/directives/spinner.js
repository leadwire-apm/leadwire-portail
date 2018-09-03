angular.module('leadwireApp').directive('spinner', [
    function() {
        return {
            restrict: 'E',
            scope: {
                showLoading: '=isLoading',
                mySize: '=?size',
                myColor: '=?color',
            },
            link: function(scope) {
                scope.fontSize = scope.mySize ? scope.mySize + 'px' : '12px';
                scope.fontColor = scope.myColor ? scope.myColor : null;
            },
            template:
                '<i ng-if="showLoading" class="fa fa-spinner fa-spin" ' +
                'ng-style="{\'font-size\':fontSize,\'color\':fontColor}"></i>',
        };
    },
]);
