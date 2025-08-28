<?php
$secret = trim(file_get_contents('/etc/myiabots/gh_webhook_secret'));
echo "SECRET: " . $secret;
