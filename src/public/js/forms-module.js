(function(global, angular, $, solaria){
    solaria.controller('FormsModuleController', function($scope, $http){
        $scope.submit = function(){
            $http.post('', $scope.forms[$scope.selectedForm])
                .success(function(redirect){
                    window.location = redirect;
                })
                .error(function(errors){
                    $scope.errors = errors;
                });
        };

        $scope.init = function(){
            $scope.selectedForm = null;
            $scope.forms = contents.forms;
            $scope.users = contents.users;
            if($scope.forms.length)
                $scope.changeActiveForm(0);
        };

        $scope.addForm = function(){
            $scope.forms.push({
                name: 'Nuevo Formulario',
                fields: []
            });
            $scope.selectedForm = $scope.forms.length - 1;
        };

        $scope.changeActiveForm = function($index){
            $scope.selectedForm = $index;
        };

        $scope.removeForm = function($index){
            var forms = angular.copy($scope.forms);
            forms.splice($index, 1);
            $scope.forms = angular.copy(forms);
            $scope.selectedForm = null;
        };

        $scope.$watch('forms[selectedForm].name', function(newValue, oldValue){
            if(!$scope.forms[$scope.selectedForm])
                return false;
            if(newValue !== oldValue)
                $scope.forms[$scope.selectedForm].alias = newValue ? slug(newValue).toLowerCase() : '';
        });
    });

    solaria.controller('FormsModuleResponsesController', function($scope, $http, $uibModal){
        $scope.init = function(){
            $scope.selectedForm = null;
            $scope.results = [];
            $scope.titles = [];
            $scope.forms = contents.forms;
            $scope.users = contents.users;
            $scope.countSelectedResults = 0;
            if($scope.forms.length)
                $scope.changeActiveForm(0);
        };

        $scope.changeActiveForm = function($index){
            $scope.selectedForm = $index;
            $scope.fetching = true;
            fetchResults($scope.forms[$index].id);
        };

        $scope.toggleSelected = function($index){
            $scope.countSelectedResults += $scope.results[$index].selected ? 1 : -1;
        };

        $scope.$watch('forms[selectedForm].selectAllResults', function(newValue){
            $scope.countSelectedResults = newValue ? $scope.results.length : 0;
            angular.forEach($scope.results, function(result){
                result.selected = newValue;
            });
        });

        $scope.deleteResults = function(){
            var resultsToDelete = [];
            angular.forEach($scope.results, function(result){
                if(result.selected)
                    resultsToDelete.push(result.id);
            });
            if(confirm('¿Está seguro que desea borrar los resultados seleccionados?')){
                $http.post(baseUrl + '/backend/modules/forms/delete-results/',{
                    results: resultsToDelete
                }).success(function(response){
                    $scope.changeActiveForm($scope.selectedForm);
                }).error(function(errors){
                    $scope.error = {type: 'danger', message: 'Ha ocurrido un error al asignar lo resultados.'};
                });
            }
        };

        $scope.assignUser = function(){
            $uibModal.open({
                templateUrl:'formResults-assignResults.html',
                controller: 'AssignResultsController',
                resolve: {
                    data: function(){
                        return {
                            form: angular.copy($scope.forms[$scope.selectedForm]),
                            results: angular.copy($scope.results),
                            users: angular.copy($scope.users)
                        };
                    }
                }
            });
        };

        $scope.viewResult = function($index){
            $uibModal.open({
                templateUrl:'formResults-viewResult.html',
                controller: 'ViewResultController',
                resolve: {
                    data: function(){
                        return {
                            form: angular.copy($scope.forms[$scope.selectedForm]),
                            result: angular.copy($scope.results[$index])
                        };
                    }
                }
            });
        };

        var fetchResults = function(form_id){
            $http.get(baseUrl + '/backend/modules/forms/results-contents/' + form_id)
                .success(function(results){
                    $scope.titles = results.titles;
                    $scope.results = results.results;
                    $scope.fetching = false;
                })
                .error(function(errors){
                    $scope.fetching = false;
                });
        };
    });
    solaria.controller('ViewResultController', function($scope, $uibModalInstance, data){
        $scope.result = data.result;
        $scope.form = data.form;

        $scope.ok = function(){
            $uibModalInstance.dismiss('cancel');
        };
    });
    solaria.controller('AssignResultsController', function($scope, $uibModalInstance, $http, data){
        $scope.results = data.results;
        $scope.form = data.form;
        $scope.users = data.users;
        $scope.selectedUserId = null;
        $scope.alert = null;

        $scope.cancel = function(){
            $uibModalInstance.dismiss('cancel');
        };

        $scope.assign = function(){
            var resultsToAssing = [];
            angular.forEach($scope.results, function(result){
                if(result.selected)
                    resultsToAssing.push(result.id);
            });
            $http.post(baseUrl + '/backend/modules/forms/assign-user-results/',{
                user_id: $scope.selectedUserId,
                results: resultsToAssing
            }).success(function(response){
                $scope.alert = {type: 'success', message: response.message};
            }).error(function(errors){
                $scope.alert = {type: 'danger', message: 'Ha ocurrido un error al asignar lo resultados.'};
            });
        };
    });
})(window, angular, jQuery, solaria);