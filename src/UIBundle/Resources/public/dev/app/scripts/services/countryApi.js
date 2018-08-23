angular
    .module('leadwireApp')
    .factory('CountryApi', function($http) {
        return {
            getAll: function() {
                return $http.get(
                    'https://restcountries.eu/rest/v2/all?fields=name;callingCodes;flag;alpha2Code',
                    { noheaders: true }
                );
            }
        };
    })
    .service('CountryService', function(CountryApi, $rootScope, $localStorage) {
        var service = this;

        service.loadCountries = function() {
            if (!$localStorage.countries) {
                CountryApi.getAll().then(function(res) {
                    $localStorage.countries = res.data.map(function(country) {
                        return angular.extend(country, {
                            phoneCode: country.callingCodes[0],
                            label:
                                country.alpha2Code +
                                ' ' +
                                country.callingCodes[0]
                        });
                    });
                    $rootScope.countries = $localStorage.countries;
                });
            } else {
                $rootScope.countries = $localStorage.countries;
            }
        };
    });
