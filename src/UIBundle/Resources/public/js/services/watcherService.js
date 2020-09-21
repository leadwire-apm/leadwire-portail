(function (angular) {
    angular.module('leadwireApp')
        .service('WatcherService', [
            'WatcherFactory',
            WatcherServiceFN,
        ]);

    function WatcherServiceFN(
        WatcherFactory,
    ) {

        var service = this;

        service.saveOrUpdate = function (data) {
            return WatcherFactory.saveOrUpdate(data)
            .then(function (response) {
                if(response.status === 200)
                    return response.data;
                else    
                    throw new Error(response.data.message);
            })
            .catch(function (err) {
                throw new Error(err);
            });
        }

        service.list = function (appId, envId) {
            return WatcherFactory.list(appId, envId)
            .then(function (response) {
                if(response.status === 200)
                    return response.data;
                else    
                    throw new Error(response.data.message);
            })
            .catch(function (err) {
                throw new Error(err);
            });
        }

        service.delete = function (id, data) {
            return WatcherFactory.delete(id, data)
            .then(function (response) {
                if(response.status === 200)
                    return response.data;
                else    
                    throw new Error(response.data.message);
            })
            .catch(function (err) {
                throw new Error(err);
            });
        }

        service.execute = function (id, data) {
            return WatcherFactory.execute(id, data)
            .then(function (response) {
                if(response.status === 200)
                    return response.data;
                else    
                    throw new Error(response.data.message);
            })
            .catch(function (err) {
                throw new Error(err);
            });
        }
    }
})(window.angular);
