<!DOCTYPE html>
<html>
<head></head>
<body>
  <script>
  var match;
  var token;
  if(window.location.hash && (match=window.location.hash.match(/#access_token=([^&]+)/))) {
    token = match[1];
    var expires = 86400;
    if(match=window.location.hash.match(/expires_in=(\d+)/)) {
      expires = match[1];
    }
    if(window.opener && window.opener.parent) {
      window.opener.parent.oauthCallback(token, expires);
    } else {
      window.parent.oauthCallback(token, expires);
    }
    window.close();
  }
  </script>
</body>
</html>