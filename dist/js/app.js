var app = angular.module('app', ['ngRoute']);

app.config(function ($routeProvider, $locationProvider) {
    $routeProvider
            .when('/', {
                templateUrl: 'pages/home.html',
                controller: 'homeController'
            })

            .when('/dashboard/:greenhouseId', {
                templateUrl: 'pages/dashboard.html',
                controller: 'dashboardController'
            })

            .when('/presets/:greenhouseId', {
                templateUrl: 'pages/presets.html',
                controller: 'presetsController'
            })

            .when('/add-preset', {
                templateUrl: 'pages/addpreset.html',
                controller: 'addPresetsController'
            })

            .when('/actions/:greenhouseId', {
                templateUrl: 'pages/actions.html',
                controller: 'actionsController'
            })
            .when('/statistics/:greenhouseId', {
                templateUrl: 'pages/statistics.html',
                controller: 'statisticsController'
            });



    $locationProvider.html5Mode({
        enabled: true,
        requireBase: false
    });
});

app.factory('apiFactory', function ($http, $q) {

    var api_prefix = "http://multlayermngmnt.com/api";

    function getGreenhouses() {
        return ($http.get(api_prefix + '/greenhouse')
                .then(handleSuccess, handleError));
    }

    function getGreenhouse(id) {
        return ($http.get(api_prefix + '/greenhouse/' + id)
                .then(handleSuccess, handleError));
    }

    function getGreenhouseMonitoring(id) {
        return ($http.get(api_prefix + '/data/' + id)
                .then(handleSuccess, handleError));
    }

    function getGreenhouseThresholds(id) {
        return ($http.get(api_prefix + '/options/' + id)
                .then(handleSuccess, handleError));
    }

    function setGreenhouseThresholds(id, thresholds) {
        return ($http.post(api_prefix + '/options', thresholds)
                .then(handleSuccess, handleError));
    }

    function getPresets() {
        return ($http.get(api_prefix + '/presets')
                .then(handleSuccess, handleError));
    }

    function getPreset(id) {
        return ($http.get(api_prefix + '/preset/' + id)
                .then(handleSuccess, handleError));
    }

    function addPreset(data) {
        return ($http.post(api_prefix + '/presets/add', data)
                .then(handleSuccess, handleError));
    }

    function getDailyData(greenhouseId, key, date) {
        return ($http.get(api_prefix + '/data/daily/' + greenhouseId + '/' + key + '/' + date)
                .then(handleSuccess, handleError));
    }

    function getMonthlyData(greenhouseId, key, date) {
        return ($http.get(api_prefix + '/data/monthly/' + greenhouseId + '/' + key + '/' + date)
                .then(handleSuccess, handleError));
    }

    function getActions(greenhouseId, page) {
        return ($http.get(api_prefix + '/action/' + greenhouseId + '?page=' + page)
                .then(handleSuccess, handleError));
    }

    function performAction(greenhouseId, action, on) {
        return ($http.post(api_prefix + '/action/perform/' + greenhouseId + '/' + action + '/' + on)
                .then(handleSuccess, handleError));
    }

    function handleSuccess(response) {
        return response;
    }

    function handleError(response) {
        if (!angular.isObject(response.data) || !response.data.message) {
            return($q.reject("An unknown error occurred."));
        }
        return($q.reject(response.data.message));
    }

    return({
        getGreenhouses: getGreenhouses,
        getGreenhouse: getGreenhouse,
        getGreenhouseMonitoring: getGreenhouseMonitoring,
        getGreenhouseThresholds: getGreenhouseThresholds,
        setGreenhouseThresholds: setGreenhouseThresholds,
        getPresets: getPresets,
        getPreset: getPreset,
        addPreset: addPreset,
        getDailyData: getDailyData,
        getActions: getActions,
        performAction: performAction,
        getMonthlyData: getMonthlyData

    });
});


app.controller('mainController', function ($scope, apiFactory) {
    $scope.greenhouses = [];
    apiFactory.getGreenhouses().then(function (succ) {
        $scope.greenhouses = succ.data;
    });

});

app.controller('homeController', function ($scope, apiFactory) {
    $scope.page_class = 'home';

    $scope.greenhouses = [];
    apiFactory.getGreenhouses().then(function (succ) {
        $scope.greenhouses = succ.data;
    });

});

app.controller('dashboardController', function ($scope, $routeParams, apiFactory) {
    $scope.page_class = 'dashboard';
    $scope.showSuccess = false;

    apiFactory.getGreenhouse($routeParams.greenhouseId).then(function (succ) {
        $scope.greenhouse = succ.data.greenhouse;
    });

    apiFactory.getGreenhouseMonitoring($routeParams.greenhouseId).then(function (succ) {
        $scope.monitoring = succ.data;
    });

    setInterval(function () {
        apiFactory.getGreenhouseMonitoring($routeParams.greenhouseId).then(function (succ) {
            $scope.monitoring = succ.data;
        });
    }, 30000);

    apiFactory.getGreenhouseThresholds($routeParams.greenhouseId).then(function (succ) {
        var thresholds = {};
        angular.forEach(succ.data.options, function (value) {
            thresholds[value.key] = {
                "maxValue": parseFloat(value.maxValue),
                "minValue": parseFloat(value.minValue)
            };
        });
        $scope.thresholds = thresholds;

    });

    $scope.submit = function ($event) {

        $scope.$broadcast('show-errors-check-validity');

        if ($scope.form.$invalid) {
            return;
        }
        var data = {
            greenhouse_id: $scope.greenhouse.greenhouse_id,
            options: []
        };

        angular.forEach($scope.thresholds, function (value, key) {
            data.options.push({
                key: key,
                maxValue: parseFloat(value.maxValue),
                minValue: parseFloat(value.minValue)
            });
        });

        apiFactory.setGreenhouseThresholds($routeParams.greenhouseId, data).then(function (succ) {
            swal("Thresholds Updated", "", "success");
        });

        $event.preventDefault();
    };
});

app.controller('presetsController', function ($scope, $routeParams, apiFactory) {
    $scope.page_class = 'setproduct';

    $scope.selectedPreset = "0";

    apiFactory.getGreenhouse($routeParams.greenhouseId).then(function (succ) {
        $scope.greenhouse = succ.data.greenhouse;
    });

    apiFactory.getPresets().then(function (succ) {
        $scope.presets = succ.data;
    });

    $scope.loadPreset = function () {
        apiFactory.getPreset($scope.selectedPreset).then(function (succ) {
            var thresholds = {};
            angular.forEach(succ.data, function (value) {
                thresholds[value.key] = {
                    "maxValue": parseFloat(value.maxValue),
                    "minValue": parseFloat(value.minValue)
                };
            });
            $scope.thresholds = thresholds;
        });
    };

    $scope.submit = function ($event) {

        $scope.$broadcast('show-errors-check-validity');

        if ($scope.form.$invalid) {
            return;
        }

        var data = {
            greenhouse_id: $scope.greenhouse.greenhouse_id,
            options: []
        };

        angular.forEach($scope.thresholds, function (value, key) {
            data.options.push({
                key: key,
                maxValue: value.maxValue,
                minValue: value.minValue
            });
        });

        apiFactory.setGreenhouseThresholds($routeParams.greenhouseId, data).then(function (succ) {
            swal("Thresholds Updated", "", "success");

        });

        $event.preventDefault();
    };
});

app.controller('addPresetsController', function ($scope, apiFactory) {

    $scope.page_class = 'addPreset';

    $scope.submit = function ($event) {

        $scope.$broadcast('show-errors-check-validity');

        if ($scope.form.$invalid) {
            return;
        }

        var data = {
            options: [],
            name: $scope.name
        };

        angular.forEach($scope.thresholds, function (value, key) {
            data.options.push({
                key: key,
                maxValue: parseFloat(value.maxValue),
                minValue: parseFloat(value.minValue)
            });
        });

        apiFactory.addPreset(data).then(function (succ) {
            swal("Thresholds Added", "", "success");

        });

        $event.preventDefault();
    };
});

app.controller('actionsController', function ($scope, $routeParams, apiFactory) {

    $scope.page_class = 'actions';

    $scope.timelines = [];
    $scope.page = 1;

    apiFactory.getGreenhouse($routeParams.greenhouseId).then(function (succ) {
        $scope.greenhouse = succ.data.greenhouse;
    });

    getActions($routeParams.greenhouseId, $scope.page);

    function getActions(id, page) {
        apiFactory.getActions($routeParams.greenhouseId, $scope.page).then(function (succ) {
            angular.forEach(succ.data.actions, function (timeline) {
                switch (timeline.action) {
                    case "pump1":
                    case "pump2":
                        timeline.badge = 'images/pump.svg';
                        break;
                    case  "fan" :
                        timeline.badge = 'images/fan.svg';
                        break;
                    case  "light" :
                        timeline.badge = 'images/light.svg';
                        break;
                    case  "heater" :
                        timeline.badge = 'images/heater.svg';
                        break;
                    case  "steamer" :
                        timeline.badge = 'images/steamer.svg';
                        break;
                    default:
                        timeline.badge = 'images/action.svg';
                }
            });
            $scope.timelines = $scope.timelines.concat(succ.data.actions);
            $scope.page++;
            $("#load-more-actions").removeClass("disabled");

        });
    }

    $scope.loadMoreActions = function () {
        $("#load-more-actions").addClass("disabled");
        getActions($routeParams.greenhouseId, $scope.page);
    };

    $scope.performAction = function (action, on) {
        apiFactory.performAction($routeParams.greenhouseId, action, on).then(function (succ) {
            swal('action send', '', "success");
        });
    };

});

app.controller('statisticsController', function ($scope, $routeParams, apiFactory) {

    $scope.keys = [
        "airHumidity",
        "temperature",
        "soilHumidity",
        "luminance",
        "electricity",
        "water"
    ];

    var graphs = {};
    $scope.dates = {};
    $scope.months = {};

    angular.forEach($scope.keys, function (value) {

        if (value === "electricity" || value === "water") {
            graphs[value] = Morris.Bar({
                element: value + '-chart',
                xkey: 'time',
                ykeys: ['value'],
                labels: ['value'],
                resize: true,
                hideHover: 'always'

            });
        } else if (value === "soilHumidity") {

            graphs[value] = Morris.Line({
                element: value + '-chart',
                xkey: 'time',
                ykeys: ['value1', 'value2'],
                labels: ['pot 1', 'pot 2'],
                resize: true,
                hideHover: 'always'
            });

        } else {
            graphs[value] = Morris.Line({
                element: value + '-chart',
                xkey: 'time',
                ykeys: ['value'],
                labels: ['value'],
                resize: true,
                hideHover: 'always'
            });
        }

        $scope.dates[value] = Date.today();
        $scope.months[value] = Date.today();

        if (value === "electricity" || value === "water") {
            getMonthly($routeParams.greenhouseId, value, $scope.dates[value].toString('MM'));
        } else {
            getDaily($routeParams.greenhouseId, value, $scope.dates[value].toString('yyyy-MM-dd'));
        }

    });

    $scope.page_class = 'statistics';

    apiFactory.getGreenhouse($routeParams.greenhouseId).then(function (succ) {
        $scope.greenhouse = succ.data.greenhouse;
    });

    function getDaily(greenhouseId, key, date) {
        apiFactory.getDailyData(greenhouseId, key, date).then(function (succ) {
            graphs[key].setData(succ.data);
        });
    }

    function getMonthly(greenhouseId, key, date) {
        apiFactory.getMonthlyData(greenhouseId, key, date).then(function (succ) {
            graphs[key].setData(succ.data);
        });
    }

    $scope.dateChanged = function (key) {
        var date = $scope.dates[key].toString('yyyy-MM-dd');
        getDaily($routeParams.greenhouseId, key, date);

    };

    $scope.monthChanged = function (key) {
        var month = $scope.months[key].toString('MM');
        getMonthly($routeParams.greenhouseId, key, month);
    };



});

app.directive('menuUpdatedDirective', function () {
    return function (scope, element, attrs) {
        if (scope.$last) {
            $('#side-menu').metisMenu();
        }
    };
});

app.directive('lowerThan', [
    function () {

        var link = function ($scope, $element, $attrs, ctrl) {

            var validate = function (viewValue) {
                var comparisonModel = $attrs.lowerThan;

                if (!viewValue || !comparisonModel) {
                    // It's valid because we have nothing to compare against
                    ctrl.$setValidity('lowerThan', true);
                }

                // It's valid if model is lower than the model we're comparing against
                ctrl.$setValidity('lowerThan', parseInt(viewValue, 10) <= parseInt(comparisonModel, 10));
                return viewValue;
            };

            ctrl.$parsers.unshift(validate);
            ctrl.$formatters.push(validate);

            $attrs.$observe('lowerThan', function (comparisonModel) {
                return validate(ctrl.$viewValue);
            });

        };

        return {
            require: 'ngModel',
            link: link
        };

    }
]);

app.directive('showErrors', function () {
    return {
        restrict: 'A',
        require: '^form',
        link: function (scope, el, attrs, formCtrl) {
            // find the text box element, which has the 'name' attribute
            var inputEl = el[0].querySelector("[name]");
            // convert the native text box element to an angular element
            var inputNgEl = angular.element(inputEl);
            // get the name on the text box so we know the property to check
            // on the form controller
            var inputName = inputNgEl.attr('name');

            // only apply the has-error class after the user leaves the text box
            inputNgEl.bind('blur', function () {
                el.toggleClass('has-error', formCtrl[inputName].$invalid);
            });

            scope.$on('show-errors-check-validity', function () {
                el.toggleClass('has-error', formCtrl[inputName].$invalid);
            });
        }
    };
});