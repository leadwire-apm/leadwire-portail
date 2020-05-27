(function (angular) {
    angular.module('leadwireApp')
        .service('ApplicationService', function (
            ApplicationFactory,
            $localStorage,
            DashboardService,
            $rootScope,
            MESSAGES_CONSTANTS,
            toastr,
        ) {
            var service = {};

            /**
             * update default app
             * @param app
             */
            service.setAppAsDefault = function (app) {
                var updatedApp = {
                    id: app.id,
                    is_default: true,
                };

                ApplicationFactory.update(app.id, updatedApp)
                    .then(function () {
                        toastr.success('Application Updated');
                    });
            };

            /**
             * what to do after saving app successfully
             *
             * @param response
             * @returns {boolean}
             */
            service.handleSaveOnSuccess = function (response) {
                if (response.data !== false && response.status === 200) {
                    //add app to LocalStorage so we can find it in the top menu
                    (
                        $localStorage.applications ||
                        ($localStorage.applications = [])
                    ).push(response.data);
                    $localStorage.listApp.push(response.data.name);

                    toastr.success(MESSAGES_CONSTANTS.ADD_APP_SUCCESS);
                    return true;
                } else {
                    var message =
                        response.data && response.data.message
                            ? response.data.message
                            : MESSAGES_CONSTANTS.ADD_APP_FAILURE ||
                            MESSAGES_CONSTANTS.ERROR;
                    throw new Error(message);
                }
            };

            service.all = function () {
                return ApplicationFactory.findAll()
                    .then(function (response) {
                        return response.data;
                    })
                    .catch(function (error) {
                        throw new Error(error);
                    });
            };

            service.allMinimalist = function () {
                return ApplicationFactory.findAllMinimalist()
                    .then(function (response) {
                        return response.data;
                    })
                    .catch(function (error) {
                        throw new Error(error);
                    });
            };

            service.toggleEnabled = function (applicationId) {
                return ApplicationFactory.toggleEnabled(applicationId)
                    .then(function (response) {
                        return response.data;
                    })
                    .catch(function (err) {
                        throw new Error(err);
                    });
            };

            service.find = function (id) {
                return ApplicationFactory.get(id)
                    .then(function (res) {
                        return res.data;
                    })
                    .catch(function (err) {
                        throw new Error(err);
                    });

            };

            service.findMinimalist = function (id) {
                return ApplicationFactory.get(id, 'minimalist')
                    .then(function (res) {
                        return res.data;
                    })
                    .catch(function (err) {
                        throw new Error(err);
                    });

            };
            service.getStats = function (applicationId) {
                return ApplicationFactory.stats(applicationId)
                    .then(function (response) {
                        return response.data;
                    })
                    .catch(function (err) {
                        throw new Error(err);
                    });
            };

            service.delete = function (applicationId) {
                return ApplicationFactory.delete(applicationId)
                    .then(function (response) {
                        return response.data;
                    })
                    .catch(function (err) {
                        throw new Error(err);
                    });
            };

            service.remove = function (applicationId) {
                return ApplicationFactory.remove(applicationId)
                    .then(function (response) {
                        return response.data;
                    })
                    .catch(function (err) {
                        throw new Error(err);
                    });
            };

            service.applyChanges = function (applicationId) {
                return ApplicationFactory.applyChanges(applicationId)
                    .then(function (response) {
                        return response.data;
                    })
                    .catch(function (err) {
                        throw new Error(err);
                    });
            }

            service.getApplicationDocumentsCount = function (appName) {
                return ApplicationFactory.getApplicationDocumentsCount(appName, $localStorage.selectedEnv.name)
                    .then(function (response) {
                        return response.data;
                    })
                    .catch(function (err) {
                        throw new Error(err);
                    });
            }

            service.getApplicationReports = function (appName, envName) {
                return ApplicationFactory.getApplicationReports(appName, envName)
                    .then(function (response) {
                        return response.data;
                    })
                    .catch(function (err) {
                        throw new Error(err);
                    });
            }

            service.deleteApplicationReport = function (id, _index) {
                return ApplicationFactory.deleteApplicationReport(id, _index)
                    .then(function (response) {
                        return response.data;
                    })
                    .catch(function (err) {
                        throw new Error(err);
                    });
            }

            service.updateDashbaords =  function (id, dashboards) {
                return ApplicationFactory.updateDashbaords(id, dashboards)
                    .then(function (response) {
                        return response.data;
                    })
                    .catch(function (err) {
                        throw new Error(err);
                    });
            }
            
            service.grantUser = function(appid, userid) {
                return ApplicationFactory.grantUser(appid, userid)
                    .then(function (response) {
                        return response.data;
                    })
                    .catch(function (err, rr) {
                        throw new Error(err);
                    });
            }

            service.revokePermission = function(appid, userid) {
                return ApplicationFactory.revokePermission(appid, userid)
                    .then(function (response) {
                        return response.data;
                    })
                    .catch(function (err, rr) {
                        throw new Error(err);
                    });
            }

            return service;
        });
})(window.angular);
