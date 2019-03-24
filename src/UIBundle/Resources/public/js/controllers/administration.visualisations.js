'use strict';
angular
  .module('leadwireApp')
  .controller('administrationVisualisationsController', ['$sce', 'ConfigService', controller]);

function controller($sce,ConfigService) {
  var ctrl = this;
  ctrl.link = $sce.trustAsResourceUrl(ConfigService.getUrl("app/kibana#/visualize?_g=()",true));
}

