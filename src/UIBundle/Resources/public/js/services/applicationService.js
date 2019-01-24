(function (angular) {
    angular.module('leadwireApp').service('ApplicationService', function (
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

            ApplicationFactory.update(app.id, updatedApp).then(function () {
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
            return ApplicationFactory.findAll().then(function (response) {
                return response.data;
            }).catch((error) => {
                return [];
            });
        };

        service.toggleStatus = function (applicationId) {
            return new Promise(function (resolve, reject) {
                ApplicationFactory.toggleStatus(applicationId).
                    then(function (response) {
                        resolve(response.data);
                    }).
                    catch((err) => {
                        reject(err);
                    });
            });
        };

        service.delete = function (applicationId) {
            return new Promise(function (resolve, reject) {
                ApplicationFactory.remove(applicationId).
                    then(function (response) {
                        resolve(response.data);
                    }).
                    catch((err) => {
                        reject(err);
                    });
            });

        };

        return service;
    });
})(window.angular);
