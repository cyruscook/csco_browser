<?php
    //Thank You https://forum.facepunch.com/f/nerds/ozeg/PHP-Source-Server-Querying/1/
	
	class serverQuery{
		// Server Info
		private $serverIp = NULL;
		private $serverPort = NULL;
		
		// Server Connection / Query
		private $serverConnection = NULL;
		private $serverResponse = NULL;
		private $players = NULL;
		
		// Server Information
		private $serverInformation = array();
		private $challengeCode = NULL;
		private $playerData = array();
		private $serverSettings = array();
		
		public function __construct($server){
			$server = explode(":", $server);
			$this->serverIp = $server[0];
			$this->serverPort = $server[1];
		}

		

		public function queryServer(){
			// Open a Connection to the Server
			$this->serverConnection = pfsockopen("udp://" . $this->serverIp, $this->serverPort, $errorNumber, $errorString, 1);
			stream_set_blocking($this->serverConnection, 1);
			if(!$this->serverConnection){
				return false;
			} else {
				// Send Query to Server For General Details
				$this->serverResponse = substr($this->query("\xFF\xFF\xFF\xFF\x54Source Engine Query\0"), 2);
				
				// Get the Challenge Code for a Player and Rules Query
				$this->challengeCode = substr($this->query("\xFF\xFF\xFF\xFF\x57"), 1);
				$this->players = $this->query("\xFF\xFF\xFF\xFF\x55" . $this->challengeCode);
				$this->serverSettings = $this->parseRules($this->query("\xFF\xFF\xFF\xFF\x56" . $this->challengeCode));
				fclose($this->serverConnection);
				return true;
			}
		}

		private function query($queryData){
			// Query Function -- Removes 'Junk' [\xFF\xFF\xFF\xFF] -- Returns Data
			fwrite($this->serverConnection, $queryData);
			fread($this->serverConnection, 4);
			$serverStatus = stream_get_meta_data($this->serverConnection);
			return fread($this->serverConnection, $serverStatus['unread_bytes']);
		}
		
		public function parseServerReponse(){

			/*
				All of These Values are directory from the Developer Wiki
				(http://developer.valvesoftware.com/wiki/Server_Queries)
			*/

			$this->serverInformation['hostname'] = trim(str_replace("\x01", "", $this->getString($this->serverResponse)));
			$this->serverInformation['map'] = $this->getString($this->serverResponse);
			$this->serverInformation['dir'] = $this->getString($this->serverResponse);
			$this->serverInformation['description'] = $this->getString($this->serverResponse);
			$this->serverInformation['appid'] = $this->getShort($this->serverResponse);
			$this->serverInformation['players'] = $this->getByte($this->serverResponse);
			$this->serverInformation['maxplayers'] = $this->getByte($this->serverResponse);
			$this->serverInformation['bots'] = $this->getByte($this->serverResponse);
			$this->serverInformation['type'] = (chr($this->getByte($this->serverResponse)) == "d") ? "Dedicated" : "Listen";
			$this->serverInformation['os'] = (chr($this->getByte($this->serverResponse)) == "l") ? "Linux" : "Windows";
			$this->serverInformation['pass'] = $this->getByte($this->serverResponse);
			$this->serverInformation['vac'] = $this->getByte($this->serverResponse);
			$this->serverInformation['version'] = $this->getString($this->serverResponse);

			// Removes First Byte of Player Response and Uses The Next Byte as the Counter
			$this->getByte($this->players);
			$count = $this->getByte($this->players);
			
			// Itterate Through and Get Cooresponding Values
			for($x = 0; $x < $count; $x++){
				$this->playerData[$x]['id'] = $this->getByte($this->players);
				$this->playerData[$x]['name'] = $this->getString($this->players);
				$this->playerData[$x]['kills'] = $this->getLong($this->players);
				$playerTime = round($this->getFloat($this->players));
				$this->playerData[$x]['time'] = round($playerTime / 3600) . ":" . round(($playerTime % 3600) / 60) . ":" . round($playerTime % 60);
			}
			return true;
		}



		private function getByte(&$source){
			$byte = ord(substr($source, 0, 1));
			$source = substr($source, 1);
			return $byte;
		}

		private function getLong(&$source){
			$long = unpack("L", substr($source, 0, 4));
			$source = substr($source, 4);
			return $long[1];
		}

		private function getShort(&$source){
			$short = unpack("S", substr($source, 0, 2));
			$source = substr($source, 2);
			return $short[1];
		}

		private function getChar(&$source){
			$char = substr($source, 0, 1);
			$source = substr($source, 1);
			return $char;
		}

		private function getFloat(&$source){
			$float = unpack("f", substr($source, 0, 4));
			$source = substr($source, 4);
			return $float[1];
		}
		
		private function getString(&$source){
			$string = "";
			$loop = true;
			while($loop){
				$stringPart = $this->getChar($source);
				if(ord($stringPart) != 0){
					$string .= $stringPart;
				} else {
					$loop = false;
				}
			}
			return $string;
		}
		
		private function parseRules($rules){
			$newRules = array();
			$this->getByte($rules);
			
			if($this->getByte($rules) != 0){
				$rules = explode(chr(0), $rules);
				for($x = 1; $x < count($rules) - 1; $x += 2){
					$newRules[$rules[$x]] = $rules[$x + 1];
				}
			}
			return $newRules;	
		}


		// Data access Functions
		public function getServerInformation(){
			return $this->serverInformation;
		}
		
		public function getPlayerInformation(){
			return $this->playerData;
		}
		
		public function getServerSettings(){
			return $this->serverSettings;
		}
	}
?>
