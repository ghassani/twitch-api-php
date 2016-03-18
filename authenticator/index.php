<?php

require_once(dirname(__FILE__).'/../vendor/autoload.php');
require_once(dirname(__FILE__).'/../creds.php');

use Spliced\Twitch\Api\Authenticator;
use Guzzle\Http\Client as HttpClient;

session_start();

$authenticator = new Authenticator(TWITCH_CLIENT_ID, TWITCH_CLIENT_SECRET, TWITCH_REDIRECT_URI);

$authenticator->setScope(array(
	'user_read',
	'user_blocks_edit',
	'user_blocks_read',
	'user_follows_edit',
	'channel_read',
	'channel_editor',
	'channel_commercial',
	'channel_stream',
	'channel_subscriptions',
	'user_subscriptions',
	'channel_check_subscription',
	'chat_login',
));

$code = isset($_REQUEST['code']) ? $_REQUEST['code'] : null;

if (!$code) {
	header(sprintf('Location: %s', $authenticator->getAuthorizeUrl()));
	exit;
} else {
	try {
		$tokenResponse = $authenticator->getToken($code);
	} catch (\Exception $e) {
		$error = $e->getMessage();
	}
}

?>
<!doctype html>

<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Twitch Authentication</title>
  <!--[if lt IE 9]>
  <script src="http://html5shiv.googlecode.com/svn/trunk/html5.js"></script>
  <![endif]-->
</head>
	<div>
		<a href="/index.php">Authorize</a>
	</div>
	<hr />
	<div id="access-token">
		<?php if (isset($error)): ?>
			<div style="color:red;"><?php echo $error; ?></div>
		<?php else:?>
			<div>Access Token: <?php echo $tokenResponse['access_token']; ?></div>
			<div>Refresh Token: <?php echo $tokenResponse['refresh_token']; ?></div>
			<div>Scopes:
				<ul>
					<?php foreach ($tokenResponse['scope'] as $scope): ?>
						<li><?php echo $scope; ?></li>
					<?php endforeach; ?>
				</ul>
			</div>
		<?php endif;?>
	</div>
<body>
</body>
</html>
