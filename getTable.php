<?php	

	include("query.php");
	
	//Allow the website to request us
	$origin = $_SERVER['HTTP_ORIGIN'];
	$allowed_domains = [
		'http://www.cyruscook.co.uk',
		'http://cyruscook.co.uk',
		'https://www.cyruscook.co.uk',
		'https://cyruscook.co.uk'
	];
	
	if (in_array($origin, $allowed_domains)){  
		header("Access-Control-Allow-Origin: " . $origin);
	} else{
		header("Access-Control-Allow-Origin: https://www.cyruscook.co.uk");
	}
	header("Access-Control-Allow-Methods: GET, POST");
	header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");	


	function debug($msg){
		//echo("<script>console.log('" . htmlentities($msg) . "');</script>");
		return true;
	};
	
	function console_log($msg){
		echo("<script>console.log('" . $msg . "');</script>");
	}

	if(!isset($_GET['addr'])){
		die("No IP Provided");
	}
	$addr = $_GET['addr'];
	$addrs = explode(",", $addr);
	$addrs = array_slice(array_unique($addrs), 0, 20);
	debug("Address: " . $addr);

	foreach($addrs as $addr){
		$serverQuery = new serverQuery($addr);
		$serverQuery->queryServer();
		$serverQuery->parseServerReponse();
	
		$server = $serverQuery->getServerInformation();
		
		$ip = explode(":", $addr)[0];
		$port = explode(":", $addr)[1];
		
		$server['ip'] = $ip;
		$server['port'] = $port;
		
		$server['status'] = 1;
		
		if(!$server){
			$server['status'] = 0;
		}
		
		
		// Make HTML safe
		array_walk_recursive($server, function (&$value) {
		    $value = htmlentities($value);
		});
		
		console_log(json_encode($server));
		
		$statustext = "Not Responding"; 
	    if($server['status'] == 1){
	        $statustext = "Online";
	    }
	    
	    $vacimage = "<img src='https://pngimage.net/wp-content/uploads/2018/06/vac-png-7.png' style='height: 20%; width: 20%;'></img>";
	    if($server['vac'] == 0){
	        $vacimage = "";
	    }
	    
	    $passimage = "<img src='https://cdn.pixabay.com/photo/2016/03/07/05/42/lock-1241639_960_720.png' style='height: 20%; width: 20%;'></img>";
	    if($server['pass'] == 0){
	        $passimage = "";
	    }
		
		$players = $server['players'];
		if($_GET['showBots'] != "true"){
			$players = $players - $server['bots'];
		}
	    
		echo "<tr onclick='window.location=\"steam://connect/{$server['ip']}:{$server['port']}\"'>
<th scope='row'>{$server['hostname']}</th>
<td>{$server['map']}</td>
<td>{$players}/{$server['maxplayers']}</td>
<td style='text-align: center;'>{$passimage}</td>
<td style='text-align: center;'>{$vacimage}</td>
<td class='status_{$server['status']}'>{$statustext}</td>
<td><a>{$server['ip']}:{$server['port']}</a></td>
</tr>";

	}
	
	// Add a refresh button at the bottom
	echo "<tr><td colspan='7' style='padding:0;'><button type='button' class='btn btn-outline-secondary' onclick='refreshTableFromButton();' style='width:100%; height: 100%;'>Refresh</button></td></tr>";
?>
