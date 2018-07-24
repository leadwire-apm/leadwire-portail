'use strict';

function dashboardCtrl($sce, ConfigService, Application) {
  
  var ctrl = this;
    Application.findAll().success(function(data) {
      console.log(data);
    })
        .error(function(error) {
          console.error(error);
        });
    ctrl.dashboardLink = $sce.trustAsResourceUrl(ConfigService.getUrl("app/kibana#/dashboard/0827af30-5895-11e8-a683-7fb93ba4982c"));
}

angular
  .module('leadwireApp')
  .controller('dashboardCtrl', ['$sce', 'ConfigService', 'Application', dashboardCtrl]);
