var app = angular.module(
    "TranslationAppModule",
    ["ngRoute", "ui.bootstrap"],
    function($interpolateProvider) {
        $interpolateProvider.startSymbol("[[");
        $interpolateProvider.endSymbol("]]");
    }
);

app.config(function($routeProvider) {
    $routeProvider
        .when("/", {
            templateUrl: "/list.html",
            controller: "TranslationAppCtrl"
        })
        .otherwise({ redirectTo: "/" });
});

app.controller("TranslationAppCtrl", [
    "$scope",
    "$sce",
    "$http",
    function($scope, $sce, $http) {
        var self = this;

        $scope.uiState = {
            newLang: {
                isOpen: false
            }
        };

        $scope.pristineRows = [];

        $scope.editing = [];
        $scope.creating = [];

        self.boilerplates = {
            translation: {
                key: "",
                values: {}
            }
        };

        self.xhr = {
            getAvailableLanguages: function() {
                $http
                    .get(Routing.generate("get_available_languages"))
                    .then(function(response) {
                        $scope.availableLanguages = response.data;
                    });
            },
            getTranslations: function() {
                $http
                    .get(Routing.generate("list_translation_entries"))
                    .then(function(response) {
                        $scope.translations = response.data;
                        $scope.translations.forEach(function(translation) {
                            if (_.isArray(translation.values)) {
                                translation.values = {};
                            }
                        });
                    });
            }
        };

        $scope.edit = function(idx) {
            $scope.pristineRows[idx] = angular.copy($scope.translations[idx]);
            $scope.editing[idx] = true;
        };

        $scope.delete = function(idx) {
            $http
                .delete(
                    Routing.generate("delete_translation_entry", {
                        id: $scope.translations[idx].id
                    }),
                    {}
                )
                .then(function(response) {
                    $scope.translations.splice(idx, 1);
                });
        };

        $scope.save = function(idx) {
            if ($scope.editing[idx]) {
                $http
                    .put(
                        Routing.generate("update_translation_entry", {
                            id: $scope.translations[idx].id
                        }),
                        $scope.translations[idx]
                    )
                    .then(function(response) {
                        $scope.editing[idx] = false;
                    });
            } else {
                $http
                    .post(
                        Routing.generate("new_translation_entry"),
                        $scope.translations[idx]
                    )
                    .then(function(response) {
                        $scope.creating[idx] = false;
                    });
            }
        };

        $scope.cancel = function(idx) {
            $scope.editing[idx] = false;
            $scope.translations[idx] = angular.copy($scope.pristineRows[idx]);
        };

        $scope.newKey = function() {
            var translation = angular.copy(self.boilerplates.translation);
            $scope.availableLanguages.forEach(function(lang) {
                translation.values[lang] = "";
            });

            $scope.translations.push(translation);
            $scope.creating[$scope.translations.length - 1] = true;
        };

        $scope.newLang = function(lang) {
            if (!$scope.availableLanguages.includes(lang)) {
                $scope.availableLanguages.push(lang);

                $scope.translations.forEach(function(row) {
                    row.values[lang] = null;
                });

                $http.post(Routing.generate('add_new_language', {'language':lang})).then(function(response){});
            }

            $scope.uiState.newLang.isOpen = false;
        };

        self.xhr.getAvailableLanguages();
        self.xhr.getTranslations();
    }
]);
