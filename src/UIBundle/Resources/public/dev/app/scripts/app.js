'use strict';

/**
 * @ngdoc overview
 * @name leadwireApp
 * @description
 * # leadwireApp
 *
 * Main module of the application.
 */
angular.module('leadwireApp', [
    'ui.router',
    'ngAnimate',
    'ui.bootstrap',
    'oc.lazyLoad',
    'ngStorage',
    'ngSanitize',
    'ui.utils',
    'ngTouch',
    'ngCookies',
    'satellizer',
    'toastr',
]).constant('COLORS', {
    'default': '#e2e2e2',
    primary: '#09c',
    success: '#2ECC71',
    warning: '#ffc65d',
    danger: '#d96557',
    info: '#4cc3d9',
    white: 'white',
    dark: '#4C5064',
    border: '#e4e4e4',
    bodyBg: '#e0e8f2',
    textColor: '#6B6B6B',
}).constant('CONFIG', {
    'BASE_URL': 'http://localhost:9000/',
    'UPLOAD_URL': 'http://localhost:9000/uploads/',
    'SWEETALERT_DANGER_MODE':{},
}).constant('MESSAGES_CONSTANTS', {
    ERROR: 'Something went wrong,please try again',
    EDIT_APP_SUCCESS: 'Your app has been updated successfully',
    INVITE_USER_SUCCESS: 'The invitation has been sent successfully',
    DELETE_APP_SUCCESS: 'The app has been deleted',
    DELETE_INVITATION_SUCCESS: 'The invitation has been deleted',
    ADD_APP_SUCCESS: 'Your app has been added successfully',
    LOGIN_SUCCESS: function(provider) {
        return 'You have successfully signed in with ' + provider;
    },
    LOGOUT_SUCCESS: 'You have been logged out',
}).run([
    '$rootScope','$localStorage', function($rootScope,$localStorage) {
        $rootScope.$watch('applications', function(newVal,oldVal) {
            $localStorage.applications = newVal;
        });
    }]);
