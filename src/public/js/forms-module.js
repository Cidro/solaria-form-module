(function(global, angular, $, solaria){
    solaria.controller('FormsWidgetController', function($scope, $http){
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
})(window, angular, jQuery, solaria);