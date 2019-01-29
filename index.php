<?php
//include ('query.php');

$logs = [];

$server_addrs = file('servers.txt', FILE_IGNORE_NEW_LINES);
array_push($logs, "Checking Servers: " . implode(", ", $server_addrs));
$servers = [];

/*
foreach($server_addrs as $server_addr){
    $result = getServer($server_addr);
	print_r($result);
	
	array_push($servers, $result);
	array_push($logs, "Retrieved Server: " . json_encode($result));
}*/
?>
<html>
<head>
	<title>CSCO Servers</title>
	<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.2.1/css/bootstrap.min.css" integrity="sha384-GJzZqFGwb1QTTN6wy59ffF1BuGJpLSa9DkKMp0DgiMDm4iYMj70gZWKYbI706tWS" crossorigin="anonymous">
</head>
<body>
	<div class="container">
		<h1>CSCO Servers</h1>
		<a href="https://www.moddb.com/downloads/mirror/173822/102/0216bfab865e197b3873ab0f05daf510">Current Download Link <small>(https://www.moddb.com/downloads/mirror/173822/102/0216bfab865e197b3873ab0f05daf510)</small></a>
		
		<table class="table table-hover table-bordered" id="serverTable">
			<thead>
				<tr>
					<th scope="col">Name</th>
					<th scope="col">Map</th>
					<th scope="col">Players</th>
					<th scope="col">Password</th>
					<th scope="col">VAC</th>
					<th scope="col">Status</th>
					<th scope="col">IP</th>
				</tr>
			</thead>
		</table>
		
		<p>
			Click on a row to connect to that server. Make sure you have CSCO running first otherwise it will attempt to launch it in CSGO.
		</p>
		<p>
			<small>
				Created by Cyrus. If you would like to make a contribution, please make a fork of the <a href="https://www.github.com/cyruscook/csco_browser">GitHub</a>.
			</small>
		</p>
	</div>
	
	<script
  src="https://code.jquery.com/jquery-3.3.1.min.js"
  integrity="sha256-FgpCb/KJQlLNfOu91ta32o/NMZxltwRo8QtmkMRdAu8="
  crossorigin="anonymous"></script>
	
	<script>
	var alreadyRefreshed = false;
	
	//Cookie functions, thanks https://www.w3schools.com/js/js_cookies.asp
	function setCookie(cname, cvalue, exdays) {
		var d = new Date();
		d.setTime(d.getTime() + (exdays*24*60*60*1000));
		var expires = "expires="+ d.toUTCString();
		document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/";
	}
	
	function getCookie(cname) {
		var name = cname + "=";
		var decodedCookie = decodeURIComponent(document.cookie);
		var ca = decodedCookie.split(';');
		for(var i = 0; i <ca.length; i++) {
			var c = ca[i];
			while (c.charAt(0) == ' ') {
				c = c.substring(1);
			}
			if (c.indexOf(name) == 0) {
				return c.substring(name.length, c.length);
			}
		}
		return "";
	}
	
	function removeExistingTable(){
		// Remove the table if there is already one - we don't want duplicates (Thanks https://stackoverflow.com/a/19298575/7641587)
		var existingTBody = document.getElementById("serverTBody");
		if(existingTBody){
			existingTBody.remove();
		}
	}
	
	function addLoadingMsg(){
		//Add a loading message
		var parentDOM = document.createElement("tbody");
		parentDOM.innerHTML = "<tr><td colspan='7'>Loading...</td></tr>";
		
		parentDOM.id = "serverTBody";
		document.getElementById("serverTable").appendChild(parentDOM);
	}
	
	function getTable(removeFirst){
		console.log("Refreshing Table");
		if(!alreadyRefreshed){
			removeExistingTable();
			addLoadingMsg();
			alreadyRefreshed = true;
		}
		
		// Make request to the server to ask each game server for their current status
		var request = new XMLHttpRequest();
		request.open('GET', 'https://server.cyruscook.co.uk/csco/getTable.php?addr=<?php echo(implode(",", $server_addrs)); ?>');
		request.onreadystatechange = function() {
			var data = request.responseText;
			if(data != ""){
				console.log("Recieved Data from server");
				
				removeExistingTable();
				
				// When the server replies add it into the table
				
				// Create an element and fill it with the data
				var parentDOM = document.createElement("tbody");
				parentDOM.innerHTML = data;
				
				// Mark this data for later retrieval and add it to the table
				parentDOM.id = "serverTBody";
				document.getElementById("serverTable").appendChild(parentDOM);
			}
		}
		request.send();
	}
	
	// Fill the table with data, then set it to refresh every 30 seconds
	getTable();
	var refreshTimer = setInterval(getTable,30000);
	
	function refreshTableFromButton(){
		removeExistingTable();
		alreadyRefreshed = false;
		window.clearTimeout(refreshTimer);
		getTable();
		refreshTimer = setInterval(getTable,30000);
	}
	
	<?php
	foreach($logs as $log){
	    echo "console.log('{$log}');";
	}
	?>
	</script>
	
	<style>
	    th[scope="row"]{
	        cursor: pointer;
	    }
	    td{
	        cursor: pointer !important; 
	    }
	    .status_1{
	        background-color: #7CFC00;
	    }
	    .status_0{
	        background-color: #ED4337;
	    }
	</style>
</body>
</html>
