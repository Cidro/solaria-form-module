<script>
    //TODO: cambiar esto a auna llamada por ajax, es mas menos feo
    var contents = <?=json_encode(['forms' => $forms->toArray(), 'users' => $users->toArray()]);?>;
</script>
<div class="row" ng-controller="FormsModuleResponsesController" ng-init="init()">
    <div class="col-sm-3">
        <div ng-cloak class="list-group">
            <a href="#{{form.alias}}" class="list-group-item" ng-click="changeActiveForm($index)" ng-class="{active: selectedForm === $index}" ng-repeat="form in forms track by $index">
                <div class="btn-group pull-right" role="group">
                    <button ng-if="fetching && selectedForm == $index" type="button" class="btn btn-xs btn-default">
                        <span class="glyphicon glyphicon-refresh spinner"></span>
                    </button>
                    <button type="button" class="btn btn-xs btn-danger" ng-click="removeForm($index)">
                        <span class="glyphicon glyphicon-trash"></span>
                    </button>
                </div>
                {{ form.name }}
            </a>
        </div>
    </div>
    <div ng-cloak class="col-sm-9">
        <div class="form-group">
            <button class="btn btn-sm btn-danger" ng-click="deleteResults()" ng-disabled="countSelectedResults==0">
                <span class="glyphicon glyphicon-remove"></span> Borrar
            </button>
            <button class="btn btn-sm btn-primary" ng-click="assignUser()" ng-disabled="countSelectedResults==0">
                <span class="glyphicon glyphicon-share"></span> Asignar
            </button>
        </div>
        <table class="table table-striped" ng-if="titles.length">
            <thead>
                <tr>
                    <th width="10"><input type="checkbox" ng-model="forms[selectedForm].selectAllResults"></th>
                    <th ng-repeat="title in titles">{{title.name}}</th>
                    <th colspan="2">Fecha</th>
                </tr>
            </thead>
            <tbody>
                <tr ng-repeat="result in results track by $index">
                    <td><input type="checkbox" ng-model="result.selected" ng-change="toggleSelected($index)" /></td>
                    <td ng-repeat="title in titles">{{result[title.alias]}}</td>
                    <td>{{result['fecha']}}</td>
                    <td>
                        <a href="#ver-resultado-{{result['id']}}" class="btn btn-xs btn-success" ng-click="viewResult($index)"><span class="glyphicon glyphicon-eye-open"></span></a>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
<script type="text/ng-template" id="formResults-assignResults.html">
    <div class="for-results-modal">
        <div class="modal-header">
            <h3 class="modal-title">Asignar resultados</h3>
        </div>
        <div class="modal-body">
            <div ng-show="alert != null" class="alert alert-{{ alert.type }}">
                {{ alert.message }}
            </div>
            <div ng-show="alert == null"  class="form-group">
                <label for="assign-user">Usuario</label>
                <select id="assign-user" class="form-control" ng-options="u.id as u.first_name for u in users" ng-model="selectedUserId">
                    <option value="">Seleccione un usuario</option>
                </select>
            </div>
        </div>
        <div class="modal-footer">
            <button ng-show="alert == null" class="btn btn-warning" type="button" ng-click="cancel()">Cancelar</button>
            <button ng-show="alert == null" class="btn btn-success" type="button" ng-click="assign()">Asignar</button>
            <button ng-show="alert != null" class="btn btn-primary" type="button" ng-click="cancel()">Aceptar</button>
        </div>
    </div>
</script>
<script type="text/ng-template" id="formResults-viewResult.html">
    <div class="for-results-modal">
        <div class="modal-header">
            <h3 class="modal-title">Resultado de Formulario</h3>
        </div>
        <div class="modal-body">
            <table class="table table-striped table-bordered">
                <tbody>
                    <tr>
                        <th>Fecha</th>
                        <td>{{ result.fecha }}</td>
                    </tr>
                    <tr ng-repeat="field in form.fields">
                        <th width="20%">{{ field.name }}</th>
                        <td>{{ result[field.alias] }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="modal-footer">
            <button class="btn btn-warning" type="button" ng-click="ok()">Aceptar</button>
        </div>
    </div>
</script>