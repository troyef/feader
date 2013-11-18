angular.module('Feader').controller('LoginController', function($scope, $location) {

      $scope.test2 = 'test2';
      $scope.showLogin = false;
      
      $scope.signinCallback = function(authResult) {
        
        console.log(authResult);
        if (authResult['access_token']) {
        	gapi.client.load('plus','v1', function(){
    				var request = gapi.client.plus.people.get({
    				   'userId': 'me'
    				 });
    				 request.execute(function(resp) {
    					console.log('Retrieved profile for:' + resp.displayName);
    				   	$scope.$apply(function(){$scope.username = resp.displayName;});
    					if (typeof resp.image.url != 'undefined'){
    						$scope.$apply(function(){$scope.avatarSrc = resp.image.url.replace("sz=50","sz=22");});
    					}
    					
    				 });
		
    			});
          $scope.$apply(function(){$scope.showLogin = false;})
        

        } else if (authResult['error']) {
          $scope.$apply(function(){$scope.showLogin = true;})
          gapi.signin.render();
        }
      }; 
      

      
        

});