FDR.ApiService = function(){
	
	var _returnType = "json";
	var _serviceUrl = "http://troymac/sites/feader/api/";
	
	return {
		GetFeeds: function(success,error = eventError){
			var callTarget = "feeds";
			
			makeRequest(callTarget, null, true, _returnType, success, error);
			
		},
		
		GetFeed: function(feedId,success,error){
			var callTarget = "Rest/Item.aspx";
			var params = {};
			
			params['function'] = "GetItems";
			if (ItemIds !== null && TypeIds !== null){
				params['ItemIds'] = ItemIds;
				params['TypeIds'] = TypeIds;
			} else {
				error.call(this,null,null,"Item :: GetItems - No ItemIds value and/or no TypeIds value provided.");
				return;
			}
			if (Versions !== null){
				params['Versions'] = Versions;
			}
			
			makeRequest(callTarget, params, true, _returnType, success, error);
			
		},
		
		PostScreenServerEvents: function(xml, UserID, success, error){
			var callTarget = "Rest/Event.aspx";
			var params = {};
			
			params['function'] = "PostScreenServerEvents";
			//params['function'] = "EchoScreenServerEvents";
			if (UserID !== null){
				params['UserID'] = UserID;
			} else {
				error.call(this,null,null,"Event :: PostScreenServerEvents - No UserId value provided.");
				return;
			}
			params['XML'] = btoa((new XMLSerializer()).serializeToString(xml));
			
			postRequest(callTarget, params, true, _returnType, success, error);
			
			
		},
		
		Registration: function(xml, success, error){
			var callTarget = "Rest/User.aspx";
			var params = {};
			
			params['function'] = "Registration";
			params['XML'] = btoa((new XMLSerializer()).serializeToString(xml));
			
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
		$.ajax({
	        url: _serviceUrl + callTarget, 
			data: params,
			async:async,
			dataType: returnType,
			success: success, 
	        error: error,
			headers: { "Accept-Encoding" : "gzip" }
	    });

	}
	
	function postRequest(callTarget, params, async, returnType, success, error){
		$.ajax({
	        url: _serviceUrl + callTarget, 
			data: params,
			type: 'POST',
			async:async,
			dataType: returnType,
			success: success, 
	        error: error,
			headers: { "Accept-Encoding" : "gzip" }
	    });

	}
	
	function eventError(request, status, error){
		console.log(error);
		
	}
	
	
}();