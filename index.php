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
		<a href="https://mega.nz/#!cSpExJrC!cfj2AIEKnmU8XkOZHqjOSKpE0RIvc2t7-mdoo1vZTJ0">Current Download Link <small>(https://mega.nz/#!cSpExJrC!cfj2AIEKnmU8XkOZHqjOSKpE0RIvc2t7-mdoo1vZTJ0)</small></a>
		
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
	</div>
	
	<script
  src="https://code.jquery.com/jquery-3.3.1.min.js"
  integrity="sha256-FgpCb/KJQlLNfOu91ta32o/NMZxltwRo8QtmkMRdAu8="
  crossorigin="anonymous"></script>
	
	<script>
	var alreadyRefreshed = false;
	
	function removeExistingTable(){
		// Remove the table if there is already one - we don't want duplicates (Thanks https://stackoverflow.com/a/19298575/7641587)
		var existingTBody = document.getElementById("serverTBody");
		if(existingTBody){
			existingTBody.remove();
		}
	}
	
	function getTable(removeFirst){
		if(!alreadyRefreshed){
			removeExistingTable();
		
			//Add a loading message
			var parentDOM = document.createElement("tbody");
			parentDOM.innerHTML = "<tr><td colspan='7'>Loading...</td></tr>";
			
			parentDOM.id = "serverTBody";
			document.getElementById("serverTable").appendChild(parentDOM);
			
			alreadyRefreshed = true;
		}
		
		// Make request to the server to ask each game server for their current status
		var request = new XMLHttpRequest();
		request.open('GET', 'https://server.cyruscook.co.uk/csco/getTable.php?addr=<?php echo(implode(",", $server_addrs)); ?>');
		request.onreadystatechange = function() {
			removeExistingTable();
			
			// When the server replies add it into the table
			
			// Create an element and fill it with the data
			var data = request.responseText;
			var parentDOM = document.createElement("tbody");
			parentDOM.innerHTML = data;
			
			// Mark this data for later retrieval and add it to the table
			parentDOM.id = "serverTBody";
			document.getElementById("serverTable").appendChild(parentDOM);
		}
		request.send();
	}
	
	// Fill the table with data, then set it to refresh every 30 seconds
	getTable();
	setInterval(getTable,30000);
	
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
