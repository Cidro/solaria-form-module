<div class="row">
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
        <div ng-if="selectedForm!==null">
            <form ng-submit="submit()">
                <fieldset>
                    <div class="form-group">
                        <label for="form-name">Nombre</label>
                        <input type="text" class="form-control" ng-model="forms[selectedForm].name">
                    </div>
                    <div class="form-group">
                        <label for="form-alias">Alias</label>
                        <input type="text" class="form-control" ng-model="forms[selectedForm].alias" readonly>
                    </div>
                </fieldset>
                <fieldset>
                    <legend>Campos</legend>
                    <layout-fields builder types="['text', 'textarea', 'checkbox', 'radio', 'select']" fields="forms[selectedForm].fields"></layout-fields>
                </fieldset>
                <button type="submit" class="btn btn-primary pull-right">Guardar</button>
            </form>
        </div>
        <div>
            <pre>{{ forms|json }}</pre>
            <pre>{{ selectedForm|json }}</pre>
        </div>
    </div>
</div>