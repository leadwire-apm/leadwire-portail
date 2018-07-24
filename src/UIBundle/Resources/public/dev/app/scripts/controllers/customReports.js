'use strict';
angular
  .module('leadwireApp')
  .controller('customReportsController', ['$sce', 'ConfigService', controller]);

function controller($sce, ConfigService) {
  var ctrl = this;
  ctrl.link = $sce.trustAsResourceUrl(ConfigService.baseUrl + "dashboardsTest?_g=()");
}

