'use strict';
angular
  .module('leadwireApp')
  .controller('syntheticMonitoringController', ['ConfigService', '$sce', controller]);

function controller(ConfigService, $sce) {
  var ctrl = this;

  ctrl.link = $sce.trustAsResourceUrl(ConfigService.getUrl ("dashboardTest/6f2dc4e0-8cb1-11e7-b68e-955b0a7f0afb?_g=()", true));
}

