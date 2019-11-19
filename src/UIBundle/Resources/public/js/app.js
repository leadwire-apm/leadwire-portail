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
    'simplemde',
    'ng.jsoneditor',
    'rx',
], function ($interpolateProvider) {
    $interpolateProvider.startSymbol('[[');
    $interpolateProvider.endSymbol(']]');
})
    .constant('COLORS', {
        default: '#e2e2e2',
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
    })
    .constant('CONFIG', {
        APP_VERSION: APP_VERSION,
        BASE_URL: BASE_URL,
        ASSETS_BASE_URL: ASSETS_BASE_URL,
        UPLOAD_URL: UPLOAD_URL,
        DOWNLOAD_URL: DOWNLOAD_URL,
        GITHUB_CLIENT_ID: GITHUB_CLIENT_ID,
        KIBANA_BASE_URL: KIBANA_BASE_URL,
        DATE_DEFAULT_FORMAT: DATE_DEFAULT_FORMAT,
        EN_DATE_FORMAT: EN_DATE_FORMAT,
        FR_DATE_FORMAT: FR_DATE_FORMAT,
        TAX: TAX,
        LOGIN_METHOD: LOGIN_METHOD,
        COMPAGNE_ENABLED: COMPAGNE_ENABLED,
        JENKINS_URL: JENKINS_URL,
        STRIPE_ENABLED: STRIPE_ENABLED,
    })
    .constant('MESSAGES_CONSTANTS', {
        ERROR: 'Something went wrong,please try again.',
        SUCCESS: 'Operation successfully completed\n.',
        EDIT_APP_SUCCESS: 'Your app has been updated successfully.',
        ACTIVATE_APP_SUCCESS: 'Your app has been activated successfully.',
        ACTIVATE_APP_FAILURE:
            'You have entered an invalid Code, your app has not been activated.',
        INVITE_USER_SUCCESS: 'The invitation has been sent successfully.',
        INVITE_USER_VALIDATION: 'This email has been already invited.',
        INVITATION_ACCEPTED: 'The invitation has been accepted',
        DELETE_APP_SUCCESS: 'The app has been deleted.',
        REMOVE_APP_SUCCESS: 'The app has been removed.',
        DELETE_INVITATION_SUCCESS: 'The invitation has been deleted.',
        CODE_COPIED: 'The code was copied successfully',
        ADD_APP_SUCCESS:
            'Your app has been added successfully. You need to activate your app',
        LOGIN_SUCCESS: function (provider) {
            return 'You have successfully signed in with ' + provider;
        },
        GO_NEXT_STEP: 'The state must not be waiting, please uncheck the box before continuing',
        COMPAGNE_VALIDATE: 'If you validate the companion will be archived',
        LOGOUT_SUCCESS: 'You have been logged out.',
        SWEET_ALERT_DELETE_MODE: {
            title: 'Are you sure?',
            text: 'Once deleted, you will not be able to recover this App!',
            icon: 'warning',
            buttons: true,
            dangerMode: true,
        },
        SWEET_ALERT_REMOVE_MODE: {
            title: 'Are you sure?',
            text: 'Once removed, you will not be able to recover this App!',
            icon: 'warning',
            buttons: true,
            dangerMode: true,
        },
        SWEET_ALERT_VALIDATION: function (text) {
            return {
                title: 'Are you sure?',
                text: text,
                className: 'text-center',
                icon: 'warning',
                buttons: true,
                dangerMode: true,
            };
        },
        SWEET_ALERT_WITH_INPUT: function (text) {
            return {
                text: text,
                content: 'input',
                button: {
                    text: 'Submit',
                    closeModal: false,
                },
            };
        },

    });
