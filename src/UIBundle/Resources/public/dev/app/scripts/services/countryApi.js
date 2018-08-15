angular.module('leadwireApp').
    factory('CountryApi', function($http) {
        return {
            getAll: function() {
                return $http.get(
                    'https://restcountries.eu/rest/v2/all?fields=name;callingCodes;flag;alpha2Code',
                    {noheaders: true});
            },
        };
    });
