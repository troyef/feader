<?php
require 'Config.php';

?>
<!DOCTYPE html>
<html>
    <head>
		<meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Feader - </title>
		<link rel="stylesheet" type="text/css" href="css/tbase.css"/>
		<link rel="stylesheet" type="text/css" href="css/tgrid.css"/>
		<link rel="stylesheet" type="text/css" href="css/feader.css"/>
		<script>
			var FDR = FDR || {};
			FDR.serviceUrl = "<?php echo $CFG->serviceUrl; ?>";
		</script>
		
		<script src="//ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
		<script src="scripts/bootstrap.min.js"></script>
		<script type='text/javascript' src='scripts/knockout-2.2.1.js'></script>
		<script src="scripts/ko_includes/feader.viewModel.js"></script>
		<script src="scripts/feader.js"></script>
		<script src="scripts/FeaderAPI.js"></script>
		<script type="text/javascript" src="https://www.google.com/jsapi"></script>
		<script type="text/javascript">
			
		google.load("feeds", "1");
		google.setOnLoadCallback();
		
		function signinCallback(authResult) {
		  if (authResult['access_token']) {
			loadUserInfo();
		    $('.loginDialog').hide();
		  } else if (authResult['error']) {
		    
		
		  }
		}
		
		function loadUserInfo(){
			gapi.client.load('plus','v1', function(){
				var request = gapi.client.plus.people.get({
				   'userId': 'me'
				 });
				 request.execute(function(resp) {
					console.log('Retrieved profile for:' + resp.displayName);
				   	$(".user_name").text(resp.displayName).show();
					if (typeof resp.image.url != 'undefined'){
						$(".avatar").attr('src',resp.image.url.replace("sz=50","sz=22")).show();
					}
					$("#login").hide();
					
				
					FDR.Feader.InitFeader(resp.id);
					
				 });
			
			});
		}
		
		
        
        $(document).ready(function(){
        	ko.applyBindings(FDR.viewModel);
			
        	FDR.viewModel.setKeyShortcutsOn();
       });

		</script>
        
    </head>
    
    
    <body>
		<header>
		<div class="logo">Feader</div>
		<div class="user_info right">
			<img class="avatar"/>
			<div class="user_name"></div>
			
		</div>
		<div class="clear"></div>
		</header>

		<section id="login">
			<div class="loginDialog">
				
				<span class="loginTxt">Please Sign in using your Google account: </span>
				<span id="signinButton">
				  <span
				    class="g-signin"
				    data-callback="signinCallback"
				    data-clientid="<?php echo $CFG->gClientId; ?>"
				    data-cookiepolicy="single_host_origin"
				    data-requestvisibleactions="http://schemas.google.com/AddActivity"
				    data-scope="https://www.googleapis.com/auth/plus.login">
				  </span>
			
				</span>
			</div>
			
		</section>
		
		<section id="unknownUser">
			<div class="">
				Hmmm... we don't know you in the feader system. If you would like to fix that, send an email to info at tefworks dot com and we'll see what we can do.  Cheers!
			</div>
		</section>
		
		<section id="feeds" data-bind="visible: userKnown">
			<div class="loading"><div class="loadingText">loading feeds</div></div>
			<div class="screenBlanket" data-bind="visible: blanketScreen"></div>
			<div class="feedAddDiv" data-bind="visible: showAddFeed, keyupBubble:false">
				<div class="feedAdd">
					<div class="cancelBtn" data-bind="click: toggleAddFeed "><i class="icon-remove"></i></div>
					Gotta a new feed? Stick it here...</br>
					<input id="addFeedInput" type="text" /><div class="head-button" data-bind="click: submitFeed "><i class="icon-plus"></i></div>
					<div class="addFeedMessage" data-bind="text: addFeedMessage"></div>
				</div>
			</div>
	        <div class="feedHead">
			
				<div class="head-button right" data-bind="click: refreshFeeds "><i class="icon-refresh" title="Refresh"></i></div>
				<div class="head-button right" data-bind=" "><i class="icon-cog"></i></div>
				<div class="head-button right" data-bind="click: toggleAddFeed " title="Add Feed"><i class="icon-plus"></i></div>
			
				<!--<div class="head-button left" data-bind=" "><i class="icon-ok"></i></div>-->
				<div class="head-button left" data-bind="click: toggleSelectAll "><i class="icon-check" title="(De)Select All"></i></div>
				<div class="head-button left" data-bind="click: removeSelected "><i class="icon-trash" title="Remove Selected"></i></div>
				<!--<div class="head-button left" data-bind=" "><i class="icon-star"></i></div>-->
				
				<div class="info_div left selectedCount" data-bind="visible: selectedCount() > 0  ">
					Selected: <span data-bind="text: selectedCount"></span></div>
				<div class="info_div left" data-bind="text: 'Total: ' + feedEntries().length"></div>
				
				<div class="clear"></div>
			</div>
	        <div class="feedContainer" data-bind="foreach: feedEntries">
			
				    <div class="entry" data-bind='css: { isread: showIsRead }, attr: { "data-id": entryId }'>
				
						<div class="right_gutter " >
							<a data-bind="attr: { href: entry.link }" target="_blank" title="Open original">
							    <i class="icon-share-alt" ></i>
							</a>
							<a data-bind="click: removeEntry" title="Remove (and mark as read)">
							    <i class="icon-trash" ></i>
							</a>
							<!--<i class="icon-star" data-bind=" "></i>-->
							<a data-bind="click: markUnread, visible: isRead" title="Mark unread">
							    <i class="icon-repeat" ></i>
							</a>
							<div class="smEntryDate" data-bind="text: localDate"></div>
						
						</div>
						<div class="entryDate" data-bind="text: localDate"></div>
					
						<div class="left_gutter ">
							<div class="save_marker" data-bind="css: { 'icon-check': isSelected }, click: toggleSelected "></div>
							
						</div>
						<div class=" entry_content">
							<div class="feedname " data-bind='text: getFeedName()'></div>
							<div class="entrytitle " data-bind='click: toggleView'>
								<span class="entryName" data-bind="text: entry.title"></span>
								<span class="entrySnip" data-bind="text: entry.contentSnippet, visible: viewSnippet"></span>
								
							</div>
						
							<div class="content" data-bind='html: entry.content, visible: viewContent'></div>
							<a class="f_close" data-bind="click: toggleView,  visible: viewContent" title="Close">
							    <i class="icon-chevron-up" ></i>
							</a>
						</div>
					


						<div class="clear"></div>
					</div>
			
	        </div>
	
			<div class="no_feeds" data-bind="visible: feedEntries().length == 0  ">Nothing to see here.  Move Along.</div>
		</section>
		
		<footer>
			<div class="right_area">
				Created By TefWorks
				<!--<i class="icon-chevron-up right" data-bind=" "></i>-->
			</div>
			
			<div class="left_area">
				<a class="left" href="privacy.html">Privacy Policy</a>
				<a class="left" href="terms.html">Terms of Use</a>
			</div>

			<div class="clear"></div>
		</footer>
        
        
		<script type="text/javascript">
			(function() {
				var po = document.createElement('script'); po.type = 'text/javascript'; po.async = true;
				po.src = 'https://apis.google.com/js/client:plusone.js';
				var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(po, s);
			})();
		</script>
		<?php echo $CFG->gAnalytics; ?>
    </body>
</html>