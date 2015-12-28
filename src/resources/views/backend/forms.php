<script>
    //TODO: cambiar esto a auna llamada por ajax, es mas menos feo
    var contents = <?=json_encode(['forms' => $forms->toArray(), 'users' => $users->toArray()]);?>;
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
        <form ng-submit="submit()" ng-if="selectedForm!==null">
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
                                    <input type="text" class="form-control" ng-model="forms[selectedForm].name">
                                </div>
                                <div class="form-group">
                                    <label for="form-alias">Alias</label>
                                    <input type="text" class="form-control" ng-model="forms[selectedForm].alias" readonly>
                                </div>
                                <div class="form-group">
                                    <label for="form-email">Asignar a usario</label>
                                    <select class="form-control" ng-options="u.id as u.first_name for u in users" ng-model="forms[selectedForm].default_assigned_user_id">
                                        <option value="">Seleccione un usuario</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="form-success-message">Mensaje de éxito</label>
                                    <textarea class="form-control" name="form-success-message" id="form-success-message" ng-model="forms[selectedForm].config.success_message"></textarea>
                                </div>
                            </fieldset>
                        </div>
                    </div>
                    <div class="tab-pane" id="form-fields">
                        <div class="row">
                            <div class="col-sm-4">
                                <div class="form-group">
                                    <legend>
                                        Campo de corre electrónico
                                        <a class="btn btn-xs btn-info" data-toggle="tooltip" data-placement="top" title="" data-original-title="Campo que se utilizará para obtener la dirección de correo electrónico donde se enviará confirmación al cliente"><span class="glyphicon glyphicon-info-sign"></span></a>
                                    </legend>
                                    <select id="client_email" class="form-control" ng-options="field.alias as field.name for field in forms[selectedForm].fields" ng-model="forms[selectedForm].config.client_email_field"></select>
                                </div>
                            </div>
                            <div class="col-sm-8">
                                <div class="form-group">
                                    <legend>
                                        Columnas de resultado
                                        <a class="btn btn-xs btn-info" data-toggle="tooltip" data-placement="top" title="" data-original-title="Campos que serán visibles como columnas en la pantalla de resultados"><span class="glyphicon glyphicon-info-sign"></span></a>
                                    </legend>
                                    <label ng-if="field.name" class="checkbox-inline" ng-repeat="field in forms[selectedForm].fields track by $index">
                                        <input type="checkbox" id="field.alias" ng-model="field.config.showColumn"> {{ field.name }}
                                    </label>
                                </div>
                            </div>
                        </div>
                        <fieldset>
                            <legend>Campos</legend>
                            <layout-fields builder types="['text', 'textarea', 'checkbox', 'radio', 'select', 'richtext']" fields="forms[selectedForm].fields"></layout-fields>
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
                                    <div ui-ace="{mode:'twig', theme:'monokai'}" ng-model="forms[selectedForm].user_email_template"></div>
                                </div>
                                <div class="tab-pane" id="form-template-client">
                                    <div ui-ace="{mode:'twig', theme:'monokai'}" ng-model="forms[selectedForm].client_email_template"></div>
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