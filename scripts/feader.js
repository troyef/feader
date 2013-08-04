var FDR = FDR || {};

FDR.Feader = function(){
	
	var entryId = 0;
	var feedList;
	var loadingCount = 0;
	
	var settings = {
		showSnippet: false,
		removeOnClose: false,
		entriesPerFeed: 50,
		viewOneEntryAtATime: true,
	};
	
	function InitFeader(userId){
		FDR.ApiService = FDRApiService();
		FDR.ApiService.CheckUser(userId, onSuccess,onProblem);
		
		
		function onSuccess(data){
			FDR.viewModel.applySettings(data);
			
			FDR.ApiService.GetFeeds(loadFeedList);
			//FDR.ApiService.GetSettings(loadFeeds);

			
		}
		
		function onProblem(){
			alert('there was a problem');
		}
		

	}
	
	return {
		InitFeader : InitFeader,
		feedList : function(){
			return feedList;
		},
		addFeed : function(feedObj){
			feedList[feedObj.id] = feedObj;
		}
		
		
		
	};
	
	
	function loadFeedList(_feedList){
		feedList = _feedList;
		FDR.viewModel.loadFeeds = loadFeeds;
		loadFeeds();
	}
	
	function loadFeeds(){
		loadingCount++;
		loadingToggle();
		
		var reqCount = 10;

		function loadFeed(feedObj,result,total){
			if (!result.error) {
			    var feed = result.feed;  
				for (var j = 0; j < result.feed.entries.length; j++) {
					var entry = feed.entries[j];
					var pubDate = Number(new Date(entry.publishedDate).getTime());
					//only pull from last 30 days
					if (pubDate > (pubDate - (30 * 24 * 60 * 60 * 1000)) 
						&& pubDate > Number(feedObj.lastEntry) 
							&& feedObj.entries.indexOf(pubDate) === -1
								&& feedObj.read_entries.indexOf(String(pubDate)) === -1){
						feedObj.entries.push(pubDate);
						FDR.viewModel.addFeedEntry(ko.observable(new Entry(feed.entries[j],feedObj.id)));
					}	
				}
				feedObj.entries.sort(function(a,b){return a-b});
				/*if (result.feed.entries.length === 0 || feedObj.entries.length === 0){
					feedObj.lastEntry = new Date().getTime();
					FDR.ApiService.SetFeedLastEntry(feedObj);
				} else */

				if ((feedObj.entries.length + feedObj.read_entries.length) === total + reqCount && feedObj.entries.length < settings.entriesPerFeed){
					var newfeed = new google.feeds.Feed(feedObj.url);
					newfeed.setNumEntries(total + (2 * reqCount));
					loadingCount++;
					newfeed.load(function(result) {
					    loadFeed(feedObj,result,total + (2 * reqCount));
					});
				}
				loadingCount--;
			}
		}

		$.each( feedList, function( key, value ) {
			var feedObj = value;
			feedObj.entries = feedObj.entries || [];
			var feed = new google.feeds.Feed(feedObj.url);
			feed.setNumEntries(reqCount);
			loadingCount++;
			feed.load(function(result) {
			    loadFeed(feedObj,result,0);
			});
		});
		
		loadingCount--;

	}
	
	
	
	function loadingToggle(){
		$(".loading").fadeOut(1000);
		if (loadingCount > 0){
			$(".loading").fadeIn(1000,null,loadingToggle);
		}
	}
	
	function setEntryRead(entry){
		if (feedList[entry.feedId].saved_entries.indexOf(String(entry.pubDate)) === -1){
			if (feedList[entry.feedId].entries.indexOf(entry.pubDate) == 0){
				feedList[entry.feedId].lastEntry = entry.pubDate;
				feedList[entry.feedId].entries.shift();
				feedList[entry.feedId].read_entries.push(String(entry.pubDate));
				
				feedList[entry.feedId].read_entries.sort(function(a,b){return Number(b)-Number(a)});
				FDR.ApiService.SetFeedLastEntry(entry.feedId,feedList[entry.feedId].read_entries[0]);
			} else {
				feedList[entry.feedId].entries.splice(feedList[entry.feedId].entries.indexOf(entry.pubDate),1);
				feedList[entry.feedId].read_entries.push(String(entry.pubDate));
				FDR.ApiService.SendEntry(entry.feedId,entry.pubDate,0);
			}
		}

	}
	
	function setEntryNotRead(entry){
		if (feedList[entry.feedId].saved_entries.indexOf(String(entry.pubDate)) === -1){
			if (feedList[entry.feedId].entries.length == 0){
				feedList[entry.feedId].read_entries.splice(feedList[entry.feedId].read_entries.indexOf(entry.pubDate),1);
				feedList[entry.feedId].lastEntry = entry.pubDate - 1;
				feedList[entry.feedId].entries.push(entry.pubDate);
				FDR.ApiService.SetFeedLastEntry(entry.feedId,entry.pubDate-1);
				FDR.ApiService.SendEntry(entry.feedId,entry.pubDate,1);
			} else {
				feedList[entry.feedId].read_entries.splice(feedList[entry.feedId].read_entries.indexOf(entry.pubDate),1);
				feedList[entry.feedId].entries.push(entry.pubDate);
				feedList[entry.feedId].entries.sort(function(a,b){return a-b});
				FDR.ApiService.SendEntry(entry.feedId,entry.pubDate,1);
			}
		}

	}
	
	function Entry(entryObj,feedId){
		var self = this;
		self.entry = entryObj;
		self.feedId = feedId;
		self.viewState = ko.observable(0);
		self.isRead = ko.observable(false);
		self.pubDate = Number(new Date(self.entry.publishedDate).getTime());
		self.isSelected = ko.observable(false);
		
		self.entryId = feedId + "_" + self.pubDate;

		self.getFeedName = ko.computed(function() {
			var name = feedList[self.feedId].name;
			return (name.length > 25) ? name.substring(0,21) + "..." : name;
		}, self);

		self.viewSnippet = ko.computed(function() {
			return (FDR.viewModel.showSnippet() && self.viewState() !== 2);
		}, self);

		self.viewContent = ko.computed(function() {
			return (self.viewState() === 2);
		}, self);

		self.markUnread = function(){
			setEntryNotRead(self);
			self.isRead(false);
		}

		self.markRead = function(){
			if (!self.isRead()){
				self.isRead(true);
				setEntryRead(self);
			}

		}
		
		self.localDate = ko.computed(function(){
			return new Date(self.entry.publishedDate).toLocaleString();
		});

		self.showIsRead = ko.computed(function(){
			return (self.isRead() && self.viewState() === 0);
		});

		self.toggleView = function(){
			if (self.viewState() === 0){
				if (settings.viewOneEntryAtATime){
					FDR.viewModel.closeAllEntries();
				}
				self.markRead();
				self.viewState(2);
				var offset = 0;
				if(typeof window.pageYOffset!= 'undefined'){
					offset = window.pageYOffset;
				} else {
					var body= document.body;
					var doc= document.documentElement;
					doc = (doc.clientHeight)? doc: body;
			        offset = doc.scrollTop;
			    }
				
				if ($("[data-id = '" + self.entryId + "']").offset().top < offset){
					$('html, body').animate({
				         scrollTop: $("[data-id = '" + self.entryId + "']").offset().top - 50
				     }, 200);
				}
			} else {
				self.viewState(0);
			}

		};
		
		self.toggleSelected = function(){
			self.isSelected(!self.isSelected());
		}



	}
	
	
}();
