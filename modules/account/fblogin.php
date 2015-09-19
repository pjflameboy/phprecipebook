<br/>
<?php echo $LangUI->_("Login using Facebook");?>:
<fb:login-button></fb:login-button>
<div id="fb-root"></div>
<script>
  window.fbAsyncInit = function() {
	FB.init({
	  appId: '133770926736539',
	  cookie: true,
	  xfbml: true,
	  oauth: true
	});
	FB.Event.subscribe('auth.login', function(response) {
	  window.location = "./fbLogin.php";
	});
	FB.Event.subscribe('auth.logout', function(response) {
	  window.location = "./index.php";
	});

	// Redirect to my login stuff if FB already has SESSION Details
    FB.getLoginStatus(function(response){
        if(response.status === 'connected'){
          window.location = "./fbLogin.php";
        }
    });
  };
  (function() {
	var e = document.createElement('script'); e.async = true;
	e.src = document.location.protocol +
	  '//connect.facebook.net/en_US/all.js';
	document.getElementById('fb-root').appendChild(e);
  }());
</script>

