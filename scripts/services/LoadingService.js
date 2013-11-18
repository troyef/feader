angular.module('LoadingService', [])
    .factory('statusService', function() {
      var service = {
        requestCount: 0,
        isLoading: function() {
          return service.requestCount > 0;
        }
      };
      return service;
    })
    .factory('onStartInterceptor', function(statusService) {
      return function (data, headersGetter) {
        statusService.requestCount++;
        return data;
      };
    })
    .factory('onFinishInterceptor', function(statusService) {
      return function(promise) {
        statusService.requestCount--;
        return promise;
      };
    })
    .config(function($httpProvider) {
       $httpProvider.responseInterceptors.push('onFinishInterceptor');
    })
    .run(function($http, onStartInterceptor) {
       $http.defaults.transformRequest.push(onStartInterceptor);
    })
    .controller('LoadingCtrl', function($scope, statusService) {
       $scope.$watch(function() { return statusService.isLoading(); }, function(value) { $scope.isLoading = value; });
    });
