'use strict';
angular
  .module('leadwireApp')
  .controller('realUserMonitoringController', ['$sce', 'ConfigService', controller]);

function controller($sce,ConfigService) {

  var ctrl = this;
  ctrl.link = $sce.trustAsResourceUrl(ConfigService.getUrl("app/kibana#/dashboard/31d93c70-5892-11e8-a683-7fb93ba4982c?_g=()", true));

}
