angular.module('Feader', ['ngCookies'])
  .config(function ($routeProvider) {
    $routeProvider
      .when('/', {
        redirectTo: '/home'
      })
      /*.when('/admin/departments', {
        templateUrl: 'views/departments.html',
        controller: 'DepartmentController',
        activetab: 'admin/departments'
      })*/
      .when('/home', {
        templateUrl: 'views/home.html',
        controller: 'HomeController',
      })
      .otherwise({
        redirectTo: '/'
      });
    });

