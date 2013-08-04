FDRApiService = function(serviceUrl){
	
	var _userId = "";
	var _returnType = "json";
	var _serviceUrl = serviceUrl;
	
	return {
		CheckUser: function(vendorId,success,error){
			var callTarget = "checkUser";
			var params = {vendorid:vendorId};
			
			function onSuccess(data){
				_userId = data.id;
				success.call(this,data)
			}
			
			makeRequest(callTarget, params, true, _returnType, onSuccess, error);
			
		},
		
		GetFeeds: function(success,error){
			var callTarget = "feeds";
			var params = {};
			params['userId'] = _userId;
			
			makeRequest(callTarget, params, true, _returnType, success, error);
			
		},
		
		GetFeed: function(feedId,success,error){
			var callTarget = "feeds/" + feedId;
			var params = {};
			
			makeRequest(callTarget, params, true, _returnType, success, error);
			
		},
		
		PostFeed: function(feedObj,success,error){
			var callTarget = "feed";
			feedObj['userId'] = _userId;
			
			postRequest(callTarget, feedObj, true, _returnType, success, error);	
		},
		
		SetFeedLastEntry: function(feedId,pubDate,success,error){
			var callTarget = "feed/" + feedId + "/pubdate/" + pubDate;
			var params = {};
			
			makeRequest(callTarget, params, true, _returnType, success, error);
		},
		
		SendEntry: function(feedId,pubDate,state,success,error){
			var callTarget = "entry";
			var params = {};
			
			params['feedId'] = feedId;
			params['pubDate'] = pubDate;
			params['state'] = state;
			
			postRequest(callTarget, params, true, _returnType, success, error);
		},
		
		
		MakeRequest : function(callTarget, params, async, returnType, success, error){
			makeRequest(callTarget, params, async, returnType, success, error);
		},
		
		PostRequest : function(callTarget, params, async, returnType, success, error){
			postRequest(callTarget, params, async, returnType, success, error);
		}
		
		
	};
	
	function makeRequest(callTarget, params, async, returnType, success, error){
		success = success || eventSuccess;
		error = error || eventError;
		$.ajax({
	        url: _serviceUrl + callTarget, 
			data: params,
			username: _userId,
			async:async,
			dataType: returnType,
			success: success, 
	        error: error
	    });

	}
	
	function postRequest(callTarget, params, async, returnType, success, error){
		success = success || eventSuccess;
		error = error || eventError;
		$.ajax({
	        url: _serviceUrl + callTarget, 
			data: params,
			type: 'POST',
			username: _userId,
			async:async,
			dataType: returnType,
			success: success, 
	        error: error
	    });

	}
	
	function putRequest(callTarget, params, async, returnType, success, error){
		success = success || eventSuccess;
		error = error || eventError;
		$.ajax({
	        url: _serviceUrl + callTarget, 
			data: params,
			type: 'PUT',
			username: _userId,
			async:async,
			dataType: returnType,
			success: success, 
	        error: error
	    });

	}
	
	function eventSuccess(){
		console.log("Posted a read.");
	}
	
	function eventError(request, status, error){
		console.log(error);	
	}
	
	
};