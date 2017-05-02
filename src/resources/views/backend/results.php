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
                </div>
                {{ form.name }}
            </a>
        </div>
    </div>
    <div ng-cloak ng-if="forms.length" class="col-sm-9">
        <div class="form-group">
            <?php if(Auth::user()->can('module_forms_delete_form_results', null)): ?>
                <button class="btn btn-sm btn-danger" ng-click="deleteResults()" ng-disabled="countSelectedResults==0">
                    <span class="glyphicon glyphicon-remove"></span> Borrar
                </button>
            <?php endif; ?>
            <?php if(Auth::user()->can('module_forms_assign_user_results', null)): ?>
            <button class="btn btn-sm btn-primary" ng-click="assignUser()" ng-disabled="countSelectedResults==0">
                <span class="glyphicon glyphicon-share"></span> Asignar
            </button>
            <?php endif; ?>
        </div>
        <div class="form-group">
            <form class="form-inline">
                <fieldset>
                    <legend>Filtros</legend>
                    <div class="form-group">
                        <label for="filter-search-query">Búsqueda</label>
                        <input class="form-control" id="filter-search-query" type="text" ng-model="forms[selectedForm].filters.searchQuery">
                    </div>
                    <div class="form-group">
                        <label for="filter-date-from">Fecha Desde</label>
                        <div class="input-group">
                            <input type="text" readonly class="form-control" uib-datepicker-popup="{{forms[selectedForm].filters.dataFormat}}" ng-model="forms[selectedForm].filters.dateFrom.value" is-open="forms[selectedForm].filters.dateFrom.opened" max-date="forms[selectedForm].filters.dateTo.value" close-text="Close" alt-input-formats="altInputFormats" />
                            <span class="input-group-btn">
                                <button type="button" class="btn btn-default" ng-click="showDateFilter(forms[selectedForm].filters.dateFrom)"><i class="glyphicon glyphicon-calendar"></i></button>
                            </span>
                        </div>
                        <label for="filter-date-from">Fecha Hasta</label>
                        <div class="input-group">
                            <input type="text" readonly class="form-control" uib-datepicker-popup="{{forms[selectedForm].filters.dataFormat}}" ng-model="forms[selectedForm].filters.dateTo.value" is-open="forms[selectedForm].filters.dateTo.opened" min-date="forms[selectedForm].filters.dateFrom.value" max-date="forms[selectedForm].filters.dataToday" close-text="Close" alt-input-formats="altInputFormats" />
                            <span class="input-group-btn">
                                <button type="button" class="btn btn-default" ng-click="showDateFilter(forms[selectedForm].filters.dateTo)"><i class="glyphicon glyphicon-calendar"></i></button>
                            </span>
                        </div>
                    </div>
                    <button type="button" ng-click="applyFilters(selectedForm)" class="btn btn-primary">
                        <i class="glyphicon glyphicon-filter"></i> Filrar
                    </button>
                    <button type="button" ng-click="downloadResults(selectedForm)" class="btn btn-success">
                        <i class="glyphicon glyphicon-save"></i> Descargar Excel
                    </button>
                </fieldset>
            </form>
        </div>
        <table class="table table-striped" ng-if="titles.length">
            <thead>
                <tr>
                    <td class="text-center" colspan="{{titles.length + 5}}">
                        <dir-pagination-controls></dir-pagination-controls>
                    </td>
                </tr>
                <tr>
                    <th width="10"><input type="checkbox" ng-model="forms[selectedForm].selectAllResults"></th>
                    <th>Id</th>
                    <th>Responsable</th>
                    <th ng-repeat="title in titles">{{title.name}}</th>
                    <th colspan="2">Fecha</th>
                </tr>
            </thead>
            <tbody ng-if="fetching">
                <tr>
                    <td colspan="{{titles.length + 5}}">
                        <div class="text-center">
                            <button type="button" class="btn btn-xs btn-default">
                                <span class="glyphicon glyphicon-refresh spinner"></span>
                            </button>
                        </div>
                    </td>
                </tr>
            </tbody>
            <tbody ng-if="!fetching">
                <tr ng-if="!results.length">
                    <th class="text-center alert alert-warning" colspan="{{titles.length + 5}}">
                        No se han encontrador registros para los filtros seleccionados
                    </th>
                </tr>
                <tr dir-paginate="result in results | filter: forms[selectedForm].filters.searchQuery | itemsPerPage: 25 track by $index ">
                    <td><input type="checkbox" ng-model="result.selected" ng-change="toggleSelected($index)" /></td>
                    <td>{{ result.id }}</td>
                    <td>{{ result.user.full_name }}</td>
                    <td ng-repeat="title in titles">{{result[title.alias]}}</td>
                    <td nowrap>{{result['fechaLocal']}}</td>
                    <td>
                        <a href="#ver-resultado-{{result['id']}}" class="btn btn-xs btn-success" ng-click="viewResult(result)"><span class="glyphicon glyphicon-eye-open"></span></a>
                    </td>
                </tr>
            </tbody>
            <tfoot>
                <tr>
                    <td class="text-center" colspan="{{titles.length + 5}}">
                        <dir-pagination-controls></dir-pagination-controls>
                    </td>
                </tr>
            </tfoot>
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
                <ui-select ng-model="selectedUser.id" theme="bootstrap">
                    <ui-select-match placeholder="Seleccione un usuario">
                        <span ng-bind="$select.selected.full_name"></span>
                    </ui-select-match>
                    <ui-select-choices repeat="user.id as user in (users | filter: $select.search) track by user.id">
                        <span ng-bind="user.full_name"></span>
                    </ui-select-choices>
                </ui-select>
            </div>
        </div>
        <div class="modal-footer">
            <button ng-show="alert == null" class="btn btn-warning" type="button" ng-click="cancel()">Cancelar</button>
            <button ng-show="alert == null" ng-disabled="loading" class="btn btn-success" type="button" ng-click="assign()">Asignar</button>
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
            <h4 ng-if="result.user">Responsable: {{ result.user.full_name }}</h4>
            <div class="table-responsive">
                <table class="table table-striped table-bordered">
                    <tbody>
                        <tr>
                            <th>Fecha</th>
                            <td>{{ result.fecha }}</td>
                        </tr>
                        <tr ng-repeat="field in form.fields">
                            <th width="20%">{{ field.name }}</th>
                            <td ng-if="field.type == 'file'"><a target="_blank" href="{{ getFileUrl(result[field.alias]) }}">{{ result[field.alias] }}</a></td>
                            <td ng-if="field.type == 'hidden'">
                                <div ng-if="field.config.dataType != 'json'">{{ result[field.alias] }}</div>
                                <pre ng-if="field.config.dataType == 'json'">{{ result[field.alias] | json }}</pre>
                            </td>
                            <td ng-if="field.type != 'file' && field.type != 'hidden'">{{ result[field.alias] }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="table-responsive">
                <table class="table table-bordered" ng-if="result.ip">
                    <thead>
                    <tr>
                        <th colspan="2">Auditoría</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr>
                        <th>Ip</th>
                        <td>{{ result.ip }}</td>
                    </tr>
                    <tr>
                        <th>User Agent</th>
                        <td>{{ result.user_agent }}</td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-warning" type="button" ng-click="ok()">Aceptar</button>
        </div>
    </div>
</script>
