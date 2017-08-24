<p ng-controller="GWFWSNavbarCtrl">
Connection: <span ng-bind="data.connection.state">???</span>
<a class="md-button primary raised" ng-click="connect()"><?= t('btn_connect') ?></a>
</p>
