var FDR = FDR || {};

FDR.viewModel = function(){
	var self = this;
	
	self.userKnown = ko.observable(false);
	
	self.loadFeeds;
	self.showSnippet;
	self.removeOnClose;
	self.viewOneEntryAtATime;

	
	self.displayName = ko.observable("");
	self.userImage = ko.observable("");
	
	self.blanketScreen = ko.observable(false);
	
	function setKeyShortcutsOff(){
		$('body').off('keyup');
	}
	self.setKeyShortcutsOn = function(){
		if (!self.showAddFeed()){
			$('body').on('keyup',function(e){
				//alert(e.keyCode);
			   	if(e.keyCode == 78){
			       	// user has pressed n
			       	FDR.viewModel.moveNextEntry();
			   	}
				if(e.keyCode == 82){
			       	// user has pressed r
			       	FDR.viewModel.refreshFeeds();
			   	}
				if(e.keyCode == 67){
			       	// user has pressed c
			       	FDR.viewModel.closeAllEntries();
			   	}
			});
		}
		
	}
	
	
	self.showAddFeed = ko.observable(false);
	self.toggleAddFeed = function(){
		self.showAddFeed(!self.showAddFeed());
		if (self.showAddFeed()){
			setKeyShortcutsOff();
			self.blanketScreen(true);
		} else {
			self.setKeyShortcutsOn();
			self.blanketScreen(false);
		}
	};
	self.addFeedMessage = ko.observable("");
	self.submitFeed = function(inputObj){
		self.addFeedMessage("");
		var feedUrl = $("#addFeedInput").val();
		var feed = new google.feeds.Feed(feedUrl);
		feed.load(function(result) {
		    if (result.status.code !== 200){
				self.addFeedMessage("There appears to be a problem with that link. Why don't you try again...");
			}
			else {
				$("#addFeedInput").val("")
				self.addFeedMessage("That's a good link!  Just a moment and we'll add it to the mix.");
				
				var feedObj = {
					"feedUrl": result.feed.feedUrl,
					"siteUrl": result.feed.link,
					"name": result.feed.title,
					"lastUpdate": Number(new Date().getTime()) - (14 * 24 * 60 * 60 * 1000)
				};
				FDR.ApiService.PostFeed(feedObj,function(data){
					FDR.Feader.addFeed(data);
					self.loadFeeds();
				});
				
			}
		});
		
	};
	
	self.feedEntries = ko.observableArray();
	//var feeds = feedList;
	
	self.selectedCount = ko.computed(function(){
		var cnt = 0;
		$.each(self.feedEntries(),function(index,item){
			if (item().isSelected())
				cnt++;
		});
		return cnt;
	});
	
	self.sortEntries = function(){
		self.feedEntries.sort(function(left, right) { 
			return left().pubDate == right().pubDate ? 0 : (left().pubDate > right().pubDate ? -1 : 1) 
		});
	};
	
	self.addFeedEntry = function(entry){
		self.feedEntries.push(entry);
		self.feedEntries.sort(function(left, right) { 
			return left().pubDate == right().pubDate ? 0 : (left().pubDate > right().pubDate ? -1 : 1) 
		});
	};
	
	self.removeEntry = function(entry){
		entry.markRead();
		feedEntries.remove(function(item) { 
			return (item() === entry); 
		})	
	};
	
	self.removeSelected = function(){
		feedEntries.remove(function(item) { 
			return (item().isSelected()); 
		})	
	};
	
	self.refreshFeeds = function(){
		feedEntries.remove(function(item) { return item().isRead() });
		self.loadFeeds();
	};
	
	
	self.applySettings = function(settings){
		self.showSnippet = ko.observable(settings.showSnippet);
		self.removeOnClose = ko.observable(settings.removeOnClose);
		self.viewOneEntryAtATime = ko.observable(settings.viewOneEntryAtATime);
		
	};
	
	self.loadUserInfo = function(userInfo){
		self.displayName(userInfo.displayName);
		self.userImage(userInfo.image.url);
	};
	
	self.allSelected = false;
	self.toggleSelectAll = function(){
		self.allSelected = !self.allSelected;
		$.each(self.feedEntries(),function(index,item){
			item().isSelected(self.allSelected);
		});
	}
	
	self.closeAllEntries = function(){
		$.each(self.feedEntries(),function(index,item){
			item().viewState(0);
		});
	}
	
	self.moveNextEntry = function(){
		var next = false;
		$.each(self.feedEntries(),function(index,item){
			if (next){
				item().toggleView();
				return false;
			} else {
				if (item().viewState() === 2){
					item().toggleView();
					next = true;
				}
			}
		});
		if (!next)
			self.feedEntries()[0]().toggleView();
	
	}
	
	
	return this;
	
	
}();

