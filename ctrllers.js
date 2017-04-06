var appctrlers = angular.module('appctrlers', []);

/* CONTROLLERS STARTS HERE */
appctrlers.controller('LoginCtrler', ['$scope', '$rootScope', '$http', '$location', '$cookies', 'CommonActionsService', function(scope, rootScope, http, $location, cookies, commonServices) {
	scope.LoginValidate = function(){
		if(scope.inputUsername == '' || scope.inputPassword == ''){
			commonServices.alert('Either or both username and password is missing', 'danger');
			return false;
		}

		var data = {'action':'LoginValidate', 'username':scope.inputUsername, 'password':scope.inputPassword};
		var url = './api.php';

		var promise = commonServices.AjaxRequest(url,data);
		promise.then(function(response) {
			if(response.data.status==0 || ( response.data.status==1 && response.data.code=='Session Exist' )){
				cookies.put('bapp_usrID', response.data.userID);
				cookies.put('bapp_loginTime', response.data.userLoggedTime);
				cookies.put('bapp_usrToken', response.data.userToken);
				commonServices.alert('User logged in successfully', 'success');
				$location.path("/AddItem");
			}else{
				scope.inputUsername=scope.inputPassword='';
				commonServices.alert(response.data.Message, 'danger');
			}
		}, function(error) {
			console.log(error);
		});
	}
}])
.controller('appCtrler', ['$scope', '$rootScope', '$http', '$location', '$cookies', 'CommonActionsService', function(scope, rootScope, http, $location, cookies, commonServices) {
	scope.AddItem = function(){

		scope.usrID = cookies.get('bapp_usrID');
		scope.usrloginTime = cookies.get('bapp_loginTime');
		scope.usrToken = cookies.get('bapp_usrToken');

		var data = {'action':'addBook2Lib', 'bname':scope.inputBName, 'bdesc':scope.inputBDesc,'baname':scope.inputBAName, 'bhyplink':scope.inputBDlink, 'bprice':scope.inputBPrice,'bcategory':scope.inputBCategory, 'brating':scope.inputBRating, 'activeusrID':scope.usrID,'activeusrTime':scope.usrloginTime,'activeusrToken':scope.usrToken,'bImg':scope.inputBImg};
		var url = './api.php';

		var promise = commonServices.AjaxRequest(url,data);
		promise.then(function(response) {
			console.log(response);
		});
	}
}])
.controller('listCtrler', ['$scope', '$rootScope', '$http', '$location', '$cookies', 'CommonActionsService', function(scope, rootScope, http, $location, cookies, commonServices) {
	scope.FetchItem = function(){

		var data = {'action':'fetchBookList'};
		var url = './api.php';

		var promise = commonServices.AjaxRequest(url,data);
		promise.then(function(response) {
			scope.data = response.data;
			console.log(response);
		});
	}
	scope.FetchItem();
}]);
/* CONTROLLERS ENDS HERE */


/* SERVICES STARTS HERE */
angular.module('appServices', [])
.service('CommonActionsService', function($http,$q){
	this.AjaxRequest = function(postURL, postData) {
        var defered = $q.defer();
        $http.post(postURL, postData).then(function(response){
            defered.resolve(response);
        },function(response){
            defered.reject(response);
        });
        return defered.promise;
    };
	this.alert = function(message, type){
        duration = 5000;
        return  $.notify({           
                message: '<strong>'+message+'</strong>'
              },{
                type: type,
                placement: {
                    from: 'top',
                    align: 'right'
                },
                allow_dismiss: true,
                delay: duration,
				showProgressbar:false,
                animate: {
                    enter: 'animated fadeInRight',
                    exit: 'animated fadeOutRight'
                }
          });
    };
});
/* SERVICES ENDS HERE */