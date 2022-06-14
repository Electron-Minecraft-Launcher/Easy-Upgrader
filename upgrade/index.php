<script src="https://cdnjs.cloudflare.com/ajax/libs/axios/1.0.0-alpha.1/axios.min.js"></script>
<script>
	/**
	 * This function make requests to 'upgrader.php' file to upgrade the web application.
	 * @param string j The version of the web application that the user wants to install.
	 */
	async function upgradeToVersion(j) {
		axios.get('/upgrader.php?version=' + j)
			.then(function(response) {
				if (response.status == 200) {
					console.log('Upgraded to version ' + j);
					document.getElementById('counter').innerHTML = document.getElementById('counter').innerHTML - 1;
					if (document.getElementById('counter').innerHTML == 0) {
						window.location.href = './?success=true';
					}
				} else {
					console.log('Error while upgrading to version ' + j + ': ' + response.data);
				}
			})
			.catch(function(error) {
				console.log('Error while upgrading to version ' + j + ': ' + error.response.data);
			})
	}
</script>

<?php

// +---------------------------------------------------------------------+
// |     PLEASE READ THE INSTRUCTIONS BELOW BEFORE USING THIS SCRIPT     |
// +---------------------------------------------------------------------+

/**
 * This file is the main part of the easy-upgrader-client.
 * It will download the 'upgrader.php' file from the easy-upgrader-server website to the root of THIS (the client) website.
 * Then, it will GET the 'upgrader.php' file with the new version variable to upgrade your web application.
 * At the end, it will delete the 'upgrader.php' file.
 * 
 * You NEED to link (include()) this file to another PHP file to get some functions like the current version of your web application.
 */


/**
 * @var string Please indicate the address of your easy-upgrader-server website
 */
$easy_upgrader_server_url = 'http://server.easy-upgrader.off/'; // Don't forget the trailing slash.

/**
 * @var string Please indicate the RELATIVE path from this file to the root of your website (eg. '../', '../../', etc.)
 */
$upgrader_php_path = '../'; // Don't forget the trailing slash.

/**
 * Please indicate the relative path from this file to the file that contains the function 'get_current_version()'.
 */
include('../include.php');

$i = 0;
$updates = [];
foreach (get_versions()[0] as $key => $value) {
	if ($value->version == get_current_version()) {
		break;
	}
	array_push($updates, $value->version);
	$i++;
}

if ($i == 0 && !isset($_GET['success'])) {
	echo 'No updates available.';
	echo '<meta http-equiv="refresh" content="5;url=/">';
	if (file_exists('../upgrader.php'))
		unlink($upgrader_php_path . 'upgrader.php');
	exit;
} else if ($i == 0 && isset($_GET['success']) && $_GET['success'] == 'true') {
	echo 'Up to date.';
	if (file_exists('../upgrader.php'))
		unlink($upgrader_php_path . 'upgrader.php');
	echo '<meta http-equiv="refresh" content="5;url=/">';
	exit;
}

fopen($upgrader_php_path . 'upgrader.php', 'w');
file_put_contents(
	$upgrader_php_path . 'upgrader.php',
	file_get_contents($easy_upgrader_server_url . 'versions/files/upgrader.php')
);

$version = get_versions()[0];

echo '<span id="counter">' . $i . '</span> upgrade(s) remaining.';

echo '<script>
	async function upgrade() {';

foreach ($updates as $key => $value) {
?>

	await upgradeToVersion('<?= $value; ?>');

<?php
}

echo '	}
	upgrade();
</script>';







function get_versions()
{

	global $easy_upgrader_server_url;

	try {
		$ch = curl_init($easy_upgrader_server_url . "versions/");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		$data = json_decode(curl_exec($ch));

		if (!curl_errno($ch)) {
			switch ($http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE)) {
				case 200:
					curl_close($ch);
					return [$data, true];
					break;
				default:
					curl_close($ch);
					return ['Unable to check the versions of the EML AdminTool', false];
			}
		} else {
			curl_close($ch);
			return ['Unable to check the versions of the EML AdminTool', false];
		}
	} catch (Exception $e) {
		return ['Unable to check the versions of the EML AdminTool', false];
	}
}

?>