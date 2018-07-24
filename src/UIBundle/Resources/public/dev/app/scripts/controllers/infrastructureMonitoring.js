'use strict';
angular
  .module('leadwireApp')
  .controller('infrastructureMonitoringController', ['$sce', 'ConfigService', controller]);

function controller($sce, ConfigService) {
  var ctrl = this;
  ctrl.link = $sce.trustAsResourceUrl(ConfigService.getUrl("app/kibana#/dashboard/ed770570-5892-11e8-a683-7fb93ba4982c", false));
}

