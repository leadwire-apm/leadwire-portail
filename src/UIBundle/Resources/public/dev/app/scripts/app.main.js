"use strict";

angular
  .module("leadwireApp")
  .run(function($rootScope, MenuFactory, $localStorage, CONFIG,$state) {
    $rootScope.menus = $localStorage.currentMenu;
    $rootScope.applications = $localStorage.applications;
    $rootScope.ASSETS_BASE_URL = CONFIG.ASSETS_BASE_URL;
    $rootScope.DOWNLOAD_URL = CONFIG.DOWNLOAD_URL;
    $rootScope.UPLOAD_URL = CONFIG.UPLOAD_URL;
    $rootScope.$watch("applications", function(newVal, oldVal) {
      $localStorage.applications = newVal;
      $rootScope.applications = newVal;
    });

    // $rootScope.$on('$stateChangeStart', function(evt, to, params) {
    //     if (to.redirectTo) {
    //         evt.preventDefault();
    //         console.log(to)
    //         $state.p(to.redirectTo, params, { location: 'replace' });
    //     }
    // });

})
  .controller("AppCtrl", [
    "$scope",
    "$rootScope",
    "$auth",
    "$location",
    "$http",
    "$localStorage",
    "ApplicationService",
    "MESSAGES_CONSTANTS",
    "toastr",
    function AppCtrl(
      $scope,
      $rootScope,
      $auth,
      $location,
      $http,
      $localStorage,
      AppService,
      MESSAGES_CONSTANTS,
      toastr
    ) {
      $scope.mobileView = 767;

      $scope.app = {
        name: "leadwire",
        author: "Nyasha",
        version: "1.0.0",
        year: new Date().getFullYear(),
        layout: {
          isSmallSidebar: false,
          isChatOpen: false,
          isFixedHeader: true,
          isFixedFooter: false,
          isBoxed: false,
          isStaticSidebar: false,
          isRightSidebar: false,
          isOffscreenOpen: false,
          isConversationOpen: false,
          isQuickLaunch: false,
          sidebarTheme: "",
          headerTheme: ""
        },
        isMessageOpen: false,
        isConfigOpen: false
      };

      $rootScope.user = $localStorage.user;
      $scope.applications = $localStorage.applications;
      //$localStorage.selectedApp =

      $scope.$on("user:updated", function(event, data) {
        $rootScope.user = data;
      });
      $scope.$on("update-image", function(event, data) {
        $scope.$broadcast("reload-src", data);
      });

      $scope.$on("new-application", function(event, newApp) {
        if (angular.isUndefined($localStorage.applications)) {
          $localStorage.applications = [];
          $rootScope.applications = [];
        }
        $localStorage.applications.push(newApp);
        $rootScope.applications.push(newApp);
      });

      if (angular.isDefined($localStorage.layout)) {
        $scope.app.layout = $localStorage.layout;
      } else {
        $localStorage.layout = $scope.app.layout;
      }

      $scope.$watch(
        "app.layout",
        function() {
          $localStorage.layout = $scope.app.layout;
        },
        true
      );

      $scope.getRandomArbitrary = function() {
        return Math.round(Math.random() * 100);
      };

      $scope.setDefaultApp = function(app) {
        AppService.setAppAsDefault(app);
      };

      $scope.logout = function() {
        delete $localStorage.user;
        $auth.logout().then(function() {
          toastr.info(MESSAGES_CONSTANTS.LOGOUT_SUCCESS);
          $location.path("/login");
        });
      };
    }
  ]);
