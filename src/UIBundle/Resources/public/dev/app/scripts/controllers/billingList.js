(function(angular) {
    'use strict';
    angular
        .module('leadwireApp')
        .controller('billingListCtrl', ['UserService', controller]);

    function controller(UserService) {
        UserService.getSubscriptions().then(function(response) {
            console.log(response.data);
        });
    }
})(window.angular);
