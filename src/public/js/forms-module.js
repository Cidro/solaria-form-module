(function (global, angular, $, solaria) {
    var moduleBaseUrl = baseUrl.replace(/\/+$/g, "") + '/backend/modules/forms/';
    solaria.controller('FormsModuleController', function ($scope, $http, $solariaMessenger, $timeout) {
        var getUsersEmails = function (users) {
            var emails = [];
            for (var i in users) {
                emails.push(users[i].email);
            }
            return emails;
        };
        $scope.submit = function () {
            $http.post('', $scope.forms[$scope.selectedForm])
                .success(function (saved_form) {
                    $scope.forms[$scope.selectedForm].id = saved_form.id;
                    $solariaMessenger.showMessage('Formulario grabado', 'success');
                })
                .error(function (errors) {
                    $solariaMessenger.showErrors(errors);
                });
        };

        $scope.init = function () {
            $scope.selectedForm = null;
            $scope.forms = contents.forms;
            $scope.users = contents.users;
            $scope.pages = contents.pages;
            $scope.usersEmails = getUsersEmails(contents.users);
            if ($scope.forms.length)
                $scope.changeActiveForm(0);
        };

        $scope.addForm = function () {
            $scope.forms.push({
                name: 'Nuevo Formulario',
                fields: [],
                config: {}
            });
            $scope.selectedForm = $scope.forms.length - 1;
        };

        $scope.changeActiveForm = function ($index) {
            $scope.selectedForm = $index;
            if (($scope.forms[$scope.selectedForm].config instanceof Array) && !$scope.forms[$scope.selectedForm].config.length)
                $scope.forms[$scope.selectedForm].config = {};

            $timeout(function () {
                $('[data-toggle="tooltip"]').tooltip();
            });
        };

        $scope.removeForm = function ($index) {
            if (confirm('¿Está seguro que desea eliminar el formulario?')) {
                $http.get(moduleBaseUrl + 'delete-form/' + $scope.forms[$index].id)
                    .success(function (response) {
                        $solariaMessenger.showMessage(response.message, 'success');
                        var forms = angular.copy($scope.forms);
                        forms.splice($index, 1);
                        $scope.forms = angular.copy(forms);
                        $scope.selectedForm = null;
                    });
            }
        };

        $scope.$watch('forms[selectedForm].name', function (newValue, oldValue) {
            if (!$scope.forms[$scope.selectedForm])
                return false;
            if (newValue !== oldValue)
                $scope.forms[$scope.selectedForm].alias = newValue ? slug(newValue).toLowerCase() : '';
        });

        $scope.removeFieldFromAssignmentRule = function (assignmentRule, $index) {
            assignmentRule.fields.splice($index, 1);
        };

        $scope.addFieldToAssignmentRule = function ($event, assignmentRule) {
            assignmentRule.fields.push({ alias: '', value: '', operation: '=' });
        };

        $scope.removeUserFromAssignmentRule = function (assignmentRule, $index) {
            assignmentRule.users.splice($index, 1);
        };

        $scope.addUserToAssignmentRule = function ($event, assignmentRule) {
            assignmentRule.users.push({ id: null });
        };

        $scope.removeAssignmentRule = function (form, $index) {
            form.config.assignmentRules.splice($index, 1);
        };

        $scope.addAssignmentRule = function ($event, form) {
            var userAssignmentRule = {
                users: [],
                fields: [{ alias: '', value: '', operation: '=' }]
            };
            if (!form.config.assignmentRules)
                form.config.assignmentRules = [];
            form.config.assignmentRules.push(userAssignmentRule);
        };

        $scope.changeRuleOperation = function ($event, assignmentRule, operation) {
            assignmentRule.operation = operation;
            $event.preventDefault();
        };

        $scope.toggleAttachment = function (field, type) {
            var form = $scope.forms[$scope.selectedForm];
            if (Object.prototype.toString.call(form.config[type + '_email_attachments']) != '[object Array]') {
                form.config[type + '_email_attachments'] = [];
            }
            var index = form.config[type + '_email_attachments'].indexOf(field.alias);

            if (index < 0) {
                form.config[type + '_email_attachments'].push(field.alias);
            } else {
                form.config[type + '_email_attachments'].splice(index, 1);
            }
        };

        $scope.fileFilter = function (field) {
            return field.type.type == 'file';
        };
    });

    solaria.controller('FormsModuleResponsesController', function ($scope, $rootScope, $http, $uibModal) {
        $scope.init = function () {
            $scope.selectedForm = null;
            $scope.results = [];
            $scope.titles = [];
            $scope.forms = contents.forms;
            $scope.users = contents.users;
            $scope.lastFetch = null;
            $scope.countSelectedResults = 0;
            if ($scope.forms.length)
                $scope.changeActiveForm(0);
        };

        $scope.changeActiveForm = function ($index) {
            $scope.selectedForm = $index;
            initializeFilters($index);
            fetchResults($scope.forms[$index].id);
        };

        var initializeFilters = function ($index) {
            if (!$scope.forms[$index].filters) {
                var dateTo = new Date(),
                    dateFrom = new Date();
                dateFrom.setMonth(dateTo.getMonth() - 1);
                $scope.forms[$index].filters = {
                    searchQuery: '',
                    dataFormat: 'dd-MM-yyyy',
                    dataToday: dateTo,
                    dateFrom: { opened: false, value: dateFrom },
                    dateTo: { opened: false, value: dateTo }
                };
            }
        };

        $scope.toggleSelected = function ($index) {
            $scope.countSelectedResults += $scope.results[$index].selected ? 1 : -1;
        };

        $scope.$watch('forms[selectedForm].selectAllResults', function (newValue) {
            $scope.countSelectedResults = newValue ? $scope.results.length : 0;
            angular.forEach($scope.results, function (result) {
                result.selected = newValue;
            });
        });

        $scope.deleteResults = function () {
            var resultsToDelete = [];
            angular.forEach($scope.results, function (result) {
                if (result.selected)
                    resultsToDelete.push(result.id);
            });
            if (confirm('¿Está seguro que desea borrar los resultados seleccionados?')) {
                $http.post('delete-results', {
                    results: resultsToDelete
                }).success(function (response) {
                    $scope.changeActiveForm($scope.selectedForm);
                }).error(function (errors) {
                    $scope.error = { type: 'danger', message: 'Ha ocurrido un error al asignar lo resultados.' };
                });
            }
        };

        $scope.assignUser = function () {
            $uibModal.open({
                templateUrl: 'formResults-assignResults.html',
                controller: 'AssignResultsController',
                resolve: {
                    data: function () {
                        return {
                            form: angular.copy($scope.forms[$scope.selectedForm]),
                            results: angular.copy($scope.results),
                            users: angular.copy($scope.users)
                        };
                    }
                }
            });
        };

        $scope.viewResult = function (result) {
            $uibModal.open({
                templateUrl: 'formResults-viewResult.html',
                controller: 'ViewResultController',
                size: 'lg',
                resolve: {
                    data: function () {
                        return {
                            form: angular.copy($scope.forms[$scope.selectedForm]),
                            result: angular.copy(result)
                        };
                    }
                }
            });
        };

        $scope.showDateFilter = function (src) {
            src.opened = !src.opened;
        };

        $scope.applyFilters = function ($index) {
            fetchResults($scope.forms[$index].id);
        };

        $scope.downloadResults = function ($index) {
            var filters = getFilters($index),
                form_id = $scope.forms[$index].id,
                timezoneoffset = '&tzoffset=' + new Date().getTimezoneOffset();
            window.open(moduleBaseUrl + 'download-results/' + form_id + filters + timezoneoffset);
        };

        var getFilters = function ($index) {
            var filter = {
                'date-from': $scope.forms[$index].filters.dateFrom.value.toISOString(),
                'date-to': $scope.forms[$index].filters.dateTo.value.toISOString()
            };
            return '?' + $.param(filter);
        };

        var fetchResults = function (form_id) {
            var filters = getFilters($scope.selectedForm);
            $scope.fetching = true;
            $scope.lastFetch = filters;
            $http.get(moduleBaseUrl + 'results-contents/' + form_id + filters)
                .success(function (results) {
                    if(filters === $scope.lastFetch) {
                        $scope.titles = results.titles;
                        $scope.results = normalizeResults(results.results);
                        $scope.fetching = false;
                    }
                })
                .error(function (errors) {
                    $scope.fetching = false;
                });
        };

        var normalizeResults = function (results) {
            for (var i in results) {
                results[i].fechaLocal = moment.utc(results[i].fecha, 'DD-MM-YYYY H:mm:ss')
                    .local()
                    .format('YYYY-MM-DD H:mm:ss');
            }
            return results;
        };
        $rootScope.$on('results-updated', function (e) {
            fetchResults($scope.forms[$scope.selectedForm].id);
            e.stopPropagation();
        });
    });
    solaria.controller('ViewResultController', function ($scope, $uibModalInstance, data) {
        $scope.result = data.result;
        $scope.form = data.form;

        $scope.ok = function () {
            $uibModalInstance.dismiss('cancel');
        };

        $scope.getFileUrl = function (fileName) {
            return moduleBaseUrl + 'get-file?file-name=' + fileName;
        };
    });
    solaria.controller('AssignResultsController', function ($scope, $rootScope, $uibModalInstance, $http, $solariaMessenger, data) {
        $scope.loading = false;
        $scope.results = data.results;
        $scope.form = data.form;
        $scope.users = data.users;
        $scope.selectedUser = {};
        $scope.alert = null;

        $scope.cancel = function () {
            $uibModalInstance.dismiss('cancel');
        };

        $scope.assign = function () {
            if (!$scope.selectedUser.id) {
                $scope.loading = false;
                $solariaMessenger.showMessage("Debe seleccionar un usuario", 'danger');
                return false;
            }

            $scope.loading = true;

            var resultsToAssing = [];
            angular.forEach($scope.results, function (result) {
                if (result.selected)
                    resultsToAssing.push(result.id);
            });

            $http.post('assign-user-results', {
                user_id: $scope.selectedUser.id,
                results: resultsToAssing
            }).success(function (response) {
                $solariaMessenger.showMessage(response.message, 'success');
                $rootScope.$emit('results-updated');
                $scope.cancel();
            }).error(function (response, code) {
                $solariaMessenger.showMessage(response.error || code, 'danger');
                $scope.loading = false;
            });
        };
    });

    solaria.directive('formFieldMapper', function () {
        return {
            restrict: 'E',
            templateUrl: baseUrl.replace(/\/+$/g, "") + '/modules/forms/templates/form-field-mapper.html',
            scope: { formFields: '=', fieldMappings: '=' },
            controller: function ($scope) {
                if (!$scope.fieldMappings)
                    $scope.fieldMappings = [];

                $scope.addFieldMapping = function () {
                    $scope.fieldMappings.push({
                        wsParamName: '',
                        data: {
                            type: 'string',
                            value: '',
                            id: null
                        }
                    });
                };

                $scope.changeDataType = function (field, type) {
                    field.data.type = type;
                    field.data.value = '';
                    field.data.id = null;
                };

                $scope.removeMapping = function ($index) {
                    $scope.fieldMappings.splice($index, 1);
                }
            }
        }
    });
})(window, angular, jQuery, solaria);
