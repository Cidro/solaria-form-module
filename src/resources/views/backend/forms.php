<script>
    //TODO: cambiar esto a auna llamada por ajax, es mas menos feo
    var contents = <?=json_encode([
            'forms' => $forms->toArray(),
            'users' => $users->toArray(),
            'pages' => $pages->toArray()
        ]);?>;
</script>
<div class="row" ng-controller="FormsModuleController" ng-init="init()">
    <div class="col-sm-3">
        <a ng-click="addForm()" class="btn btn-success" href="#add-form">
            <span class="glyphicon glyphicon-plus"></span>
            Nuevo Formulario
        </a>
        <hr>
        <div ng-cloak class="list-group">
            <a href="#{{form.alias}}" class="list-group-item" ng-click="changeActiveForm($index)" ng-class="{active: selectedForm === $index}" ng-repeat="form in forms track by $index">
                <button type="button" class="btn btn-xs btn-danger pull-right" ng-click="removeForm($index)">
                    <span class="glyphicon glyphicon-trash"></span>
                </button>
                {{ form.name }}
            </a>
        </div>
    </div>
    <div ng-cloak class="col-sm-9">
        <div ng-repeat="form in forms track by $index">
            <form ng-if="$index == selectedForm" ng-submit="submit()">
                <div>
                    <ul class="nav nav-tabs" role="tablist">
                        <li role="presentation" class="active">
                            <a href="#form-config" aria-controls="config" role="tab" data-toggle="tab">
                                <span class="glyphicon glyphicon-cog"></span> Configuración
                            </a>
                        </li>
                        <li role="presentation" class="">
                            <a href="#form-fields" aria-controls="fields" role="tab" data-toggle="tab">
                                <span class="glyphicon glyphicon-list-alt"></span> Campos
                            </a>
                        </li>
                        <li role="presentation" class="">
                            <a href="#form-email-template" aria-controls="email-template" role="tab" data-toggle="tab">
                                <span class="glyphicon glyphicon-edit"></span> Plantillas
                            </a>
                        </li>
                    </ul>
                    <div class="tab-content">
                        <div class="tab-pane active" id="form-config">
                            <div>
                                <fieldset>
                                    <div class="form-group">
                                        <label for="form-name">Nombre</label>
                                        <input type="text" class="form-control" ng-model="form.name">
                                    </div>
                                    <div class="form-group">
                                        <label for="form-alias">Alias</label>
                                        <input type="text" class="form-control" ng-model="form.alias" readonly>
                                    </div>
                                    <div class="form-group">
                                        <label for="form-assigned_user">Usuario Asignado</label>
                                        <ui-select ng-model="form.default_assigned_user_id" theme="bootstrap">
                                            <ui-select-match placeholder="Seleccione un usuario">
                                                <span ng-bind="$select.selected.full_name"></span>
                                            </ui-select-match>
                                            <ui-select-choices repeat="user.id as user in (users | filter: $select.search) track by user.id">
                                                <span ng-bind="user.full_name"></span>
                                            </ui-select-choices>
                                        </ui-select>
                                    </div>
                                    <div class="form-group">
                                        <label for="form-users-assignment">Listas de correo</label>
                                        <div class="row">
                                            <div class="col-sm-12">
                                                <table class="table table-striped">
                                                    <thead>
                                                    <tr>
                                                        <th width="50%">Reglas</th>
                                                        <th width="50%">Usuarios</th>
                                                        <th>&nbsp;</th>
                                                    </tr>
                                                    </thead>
                                                    <tbody>
                                                    <tr ng-repeat="assignmentRule in form.config.assignmentRules track by $index">
                                                        <td>
                                                            <div class="row" ng-repeat="ruleField in assignmentRule.fields track by $index">
                                                                <div class="col-sm-12">
                                                                    <div class="input-group">
                                                                        <select class="form-control" ng-options="f.alias as f.name for f in form.fields" ng-model="ruleField.alias">
                                                                            <option value="">Cualquier campo</option>
                                                                        </select>
                                                                        <div class="input-group-btn">
                                                                            <button type="button" class="btn dropdown-toggle" ng-class="{'btn-success': ruleField.operation == '=', 'btn-warning': ruleField.operation == '!='}" data-toggle="dropdown"><strong>{{ ruleField.operation }}</strong></button>
                                                                            <ul class="dropdown-menu">
                                                                                <li><a href="#" ng-click="changeRuleOperation($event, ruleField, '=')">Igual</a></li>
                                                                                <li><a href="#" ng-click="changeRuleOperation($event, ruleField, '!=')">Distinto</a></li>
                                                                            </ul>
                                                                        </div>
                                                                        <input type="text" class="form-control" ng-model="ruleField.value">
                                                                        <span class="input-group-btn">
                                                                            <button ng-click="removeFieldFromAssignmentRule(assignmentRule, $index)" class="btn btn-danger" type="button">
                                                                                <span class="glyphicon glyphicon-trash"></span>
                                                                            </button>
                                                                        </span>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="row">
                                                                <div class="col-sm-12 text-center">
                                                                    <button type="button" ng-click="addFieldToAssignmentRule($event, assignmentRule)" class="btn btn-default"><span class="glyphicon glyphicon-plus"></span></button>
                                                                </div>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <ui-select multiple tagging tagging-label="(Email sin usuario)" ng-model="assignmentRule.users" theme="bootstrap" sortable="true" ng-disabled="disabled" title="Ingrese los correos asociados">
                                                                <ui-select-match placeholder="Ingrese los correos asociados...">{{$item}}</ui-select-match>
                                                                <ui-select-choices repeat="email in usersEmails | filter:$select.search">
                                                                    {{ email }}
                                                                </ui-select-choices>
                                                            </ui-select>
                                                        </td>
                                                        <td class="vertical-align">
                                                            <button ng-click="removeAssignmentRule(form, $index)" type="button" class="btn btn-danger">
                                                                <span class="glyphicon glyphicon-trash"></span>
                                                            </button>
                                                        </td>
                                                    </tr>
                                                    </tbody>
                                                    <tfoot>
                                                    <tr>
                                                        <td colspan="3" align="center">
                                                            <button type="button" ng-click="addAssignmentRule($event, form)" class="btn btn-default"><span class="glyphicon glyphicon-plus"></span></button>
                                                        </td>
                                                    </tr>
                                                    </tfoot>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="form-success-message">Mensaje</label>
                                        <textarea class="form-control" name="form-success-message" id="form-success-message" ng-model="form.config.success_message"></textarea>
                                    </div>
                                    <div class="form-group">
                                        <label for="form-success-redirec-to-page">Redireccionar a página</label>
                                        <div class="input-group">
                                            <ui-select ng-model="form.config.success_redirect" theme="bootstrap">
                                                <ui-select-match placeholder="Seleccione una página">
                                                    <span ng-bind="$select.selected.title"></span>
                                                </ui-select-match>
                                                <ui-select-choices repeat="page.id as page in (pages | filter: $select.search) track by page.id">
                                                    <span>{{ page.id }} - {{ page.title }}</span>
                                                </ui-select-choices>
                                            </ui-select>
                                            <span class="input-group-btn">
                                              <button type="button" ng-click="form.config.success_redirect = null" class="btn btn-default">
                                                  <span class="glyphicon glyphicon-trash"></span>
                                              </button>
                                            </span>
                                        </div>
                                    </div>
                                </fieldset>
                            </div>
                        </div>
                        <div class="tab-pane" id="form-fields">
                            <div class="row">
                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <legend>
                                            Campo de correo electrónico
                                            <a class="btn btn-xs btn-info" data-toggle="tooltip" data-placement="top" title="" data-original-title="Campo que se utilizará para obtener la dirección de correo electrónico donde se enviará confirmación al cliente"><span class="glyphicon glyphicon-info-sign"></span></a>
                                        </legend>
                                        <select id="client_email" class="form-control" ng-options="field.alias as field.name for field in form.fields" ng-model="form.config.client_email_field"></select>
                                    </div>
                                </div>
                                <div class="col-sm-8">
                                    <div class="form-group">
                                        <legend>
                                            Columnas de resultado
                                            <a class="btn btn-xs btn-info" data-toggle="tooltip" data-placement="top" title="" data-original-title="Campos que serán visibles como columnas en la pantalla de resultados"><span class="glyphicon glyphicon-info-sign"></span></a>
                                        </legend>
                                        <label ng-if="field.name" class="checkbox-inline" ng-repeat="field in form.fields track by $index">
                                            <input type="checkbox" id="field.alias" ng-model="field.config.showColumn"> {{ field.name }}
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <fieldset>
                                <legend>Campos</legend>
                                <layout-fields builder types="['text', 'hidden', 'textarea', 'checkbox', 'radio', 'select', 'richtext', 'file']" fields="form.fields"></layout-fields>
                            </fieldset>
                        </div>
                        <div class="tab-pane" id="form-email-template">
                            <div>
                                <ul class="nav nav-tabs" role="tablist">
                                    <li role="presentation" class="active">
                                        <a href="#form-template-user" aria-controls="user" role="tab" data-toggle="tab">
                                            <span class="glyphicon glyphicon glyphicon-edit"></span> Ejecutivo
                                        </a>
                                    </li>
                                    <li role="presentation">
                                        <a href="#form-template-client" aria-controls="client" role="tab" data-toggle="tab">
                                            <span class="glyphicon glyphicon glyphicon-edit"></span> Cliente
                                        </a>
                                    </li>
                                </ul>
                                <div class="tab-content">
                                    <div class="tab-pane active" id="form-template-user">
                                        <div ui-ace="{mode:'twig', theme:'monokai'}" ng-model="form.user_email_template"></div>
                                    </div>
                                    <div class="tab-pane" id="form-template-client">
                                        <div ui-ace="{mode:'twig', theme:'monokai'}" ng-model="form.client_email_template"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary pull-right">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>