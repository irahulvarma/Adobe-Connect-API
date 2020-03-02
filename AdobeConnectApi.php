<?php

Class AdobeConnectApi {
	
	private $token;
	private $url = "<meeting url>";
	private $login = "<account email id>";
	private $password = "<account-password>";
	private $_user_password = "<your-user-password>";
	private $_folder_id = "<your-folder-id>";   //folder-id of shared-training template
	
	private $_timezone_list = array(
									"Pacific/Midway"=> 1,
									"America/Adak"=> 2,
									"Etc/GMT+10"=> 2,
									"Pacific/Marquesas"=> 3,
									"Pacific/Gambier"=> 3,
									"America/Anchorage"=> 3,
									"America/Ensenada"=> 4,
									"Etc/GMT+8"=> 4,
									"America/Los_Angeles"=> 4,
									"America/Denver"=> 10,
									"America/Chihuahua"=> 13,
									"America/Dawson_Creek"=> 15,
									"America/Belize"=> 20,
									"America/Cancun"=> 30,
									"Chile/EasterIsland"=> 33,
									"America/Chicago"=> 33,
									"America/New_York"=> 35,
									"America/Havana"=> 35,
									"America/Bogota"=> 45,
									"America/Caracas"=> 55,
									"America/Santiago"=> 56,
									"America/La_Paz"=> 55,
									"Atlantic/Stanley"=> 56,
									"America/Campo_Grande"=> 56,
									"America/Goose_Bay"=> 56,
									"America/Glace_Bay"=> 56,
									"America/St_Johns"=> 60,
									"America/Araguaina"=> 65,
									"America/Montevideo"=> 70,
									"America/Miquelon"=> 70,
									"America/Godthab"=> 73,
									"America/Argentina/Buenos_Aires"=> 70,
									"America/Sao_Paulo"=> 65,
									"America/Noronha"=> 75,
									"Atlantic/Cape_Verde"=> 83,
									"Atlantic/Azores"=> 80,
									"Europe/Belfast"=> 85,
									"Europe/Dublin"=> 85,
									"Europe/Lisbon"=> 85,
									"Europe/London"=> 85,
									"Africa/Abidjan"=> 100,
									"Europe/Amsterdam"=> 110,
									"Europe/Belgrade"=> 95,
									"Europe/Brussels"=> 105,
									"Africa/Algiers"=> 113,
									"Africa/Windhoek"=> 113,
									"Asia/Beirut"=> 115,
									"Africa/Cairo"=> 120,
									"Asia/Gaza"=> 135,
									"Africa/Blantyre"=> 140,
									"Asia/Jerusalem"=> 135,
									"Europe/Minsk"=> 130,
									"Asia/Damascus"=> 135,
									"Europe/Moscow"=> 145,
									"Africa/Addis_Ababa"=> 155,
									"Asia/Tehran"=> 160,
									"Asia/Dubai"=> 165,
									"Asia/Yerevan"=> 170,
									"Asia/Kabul"=> 175,
									"Asia/Yekaterinburg"=> 180,
									"Asia/Tashkent"=> 185,
									"Asia/Kolkata"=> 190,
									"Asia/Katmandu"=> 193,
									"Asia/Dhaka"=> 195,
									"Asia/Novosibirsk"=> 201,
									"Asia/Rangoon"=> 203,
									"Asia/Bangkok"=> 205,
									"Asia/Krasnoyarsk"=> 207,
									"Asia/Singapore"=> 215,
									"Asia/Hong_Kong"=> 210,
									"Asia/Irkutsk"=> 227,
									"Australia/Perth"=> 227,
									"Australia/Eucla"=> 227,
									"Asia/Tokyo"=> 235,
									"Asia/Seoul"=> 230,
									"Asia/Yakutsk"=> 240,
									"Australia/Adelaide"=> 250,
									"Australia/Darwin"=> 245,
									"Australia/Brisbane"=> 260,
									"Australia/Hobart"=> 265,
									"Asia/Vladivostok"=> 270,
									"Australia/Lord_Howe"=> 270,
									"Etc/GMT-11"=> 280,
									"Asia/Magadan"=> 280,
									"Pacific/Norfolk"=> 280,
									"Asia/Anadyr"=> 285,
									"Pacific/Auckland"=> 290,
									"Etc/GMT-12"=> 285,
									"Pacific/Chatham"=> 300,
									"Pacific/Tongatapu"=> 300,
									"Pacific/Kiritimati"=> 300		
	);
	
	function __construct() {
		//get token with login credentials	
		$for_token = get_headers($this->url."/api/xml?action=login&login=".$this->login."&password=".$this->password);	
		$token1 = explode("BREEZESESSION=",implode("",$for_token));
		$token2 = explode(";",$token1[1]);
		$token = $token2[0];		
		$this->token = $token;
	}
	
	/*function __construct($email_id) {
		//get token with login credentials	
		$for_token = get_headers($this->url."/api/xml?action=login&login=".$email_id."&password=".$this->_user_password);		
		$token1 = explode("BREEZESESSION=",$for_token[2]);
		$token2 = explode(";",$token1[1]);
		$token = $token2[0];		
		$this->token = $token;
	}*/
	
	//for every call
	function getToken() {			
		return $this->token;					
	}
	
	//function to find folders available in adobe connect
	public function getSco(){
		$token = $this->token;
		$get_sco = file_get_contents($this->url."/api/xml?action=sco-shortcuts&session=" . $token);
		$get_sco_obj = simplexml_load_string($get_sco);
		print_r($get_sco_obj);
		
	}
	
	//create user 
	public function createUser($first_name, $last_name, $email,$timezone){
		$token = $this->getToken();
		$first_name = urlencode($first_name);
		$last_name = urlencode($last_name);
		if($last_name == '' ) {
			$last_name = urlencode(".");
		}
		//$email = urlencode($email);
		$create_user = file_get_contents($this->url."/api/xml?action=principal-update&first-name=".$first_name."&last-name=".$last_name."&login=".$email."&password=".$this->_user_password."&type=user&send-email=false&has-children=0&email=".$email."&session=" . $token);
		$create_user_obj = simplexml_load_string($create_user);	
		
		$arr = array();
		if($create_user_obj->status['code'] == "ok"){
		   $principal_id = (int)$create_user_obj->principal['principal-id'];
		   
		   $error = $this->setTimezone($principal_id,$timezone);
		   		
		   return $principal_id;
		} else {
			$get_user = file_get_contents($this->url."/api/xml?action=principal-list&filter-email=".$email."&session=" . $token);
			$get_user_obj = simplexml_load_string($get_user);
			
			if($get_user_obj->status['code'] == "ok"){
				$principal_id = (int)$get_user_obj->{'principal-list'}->principal['principal-id'];
		   
			   $error = $this->setTimezone($principal_id,$timezone);
					
			   return $principal_id;
			}
			
			return false;
		}	
		return false;
	}
	
	//list all users, warning the more the users the more the delay
	public function getUserList(){		
		$token = $this->getToken();
		$principal_list = file_get_contents($this->url."/api/xml?action=principal-list&filter-type=user&session=" . $token);
		$principal_list_obj = simplexml_load_string($principal_list);
		$arr = array();
		foreach($principal_list_obj->{'principal-list'}->principal as $p){
			$arr[] = (int)$p['principal-id'];
		}
		
		return $arr;
		
	}
	
	//list all meetings
	private function getMeetings(){
		$token = $this->getToken();
		$meetings = file_get_contents($this->url."/api/xml?action=report-bulk-objects&filter-type=meeting&session=" . $token);
		$meetings_obj = simplexml_load_string($meetings);
		if($meetings_obj->status['code'] == "ok"){
			foreach($meetings_obj->{'report-bulk-objects'}->row as $r){
				if(isset($r->{'date-created'})){
					$arr[] = (int)$r['sco-id'];
				}
			}
			return $arr;
		}
		
		return "no-data";
	}
	
	//list all user meetings
	private function getUserMeetings(){
		$token = $this->getToken();		
		$my_meeting = file_get_contents($this->url."/api/xml?action=report-my-meetings&session=" . $token);
		$my_meeting_obj = simplexml_load_string($my_meeting);
		if($my_meeting_obj->status['code'] == "ok"){
			foreach($my_meeting_obj->{'my-meetings'}->meeting as $r){				
				$arr[] = (int)$r['sco-id'];				
			}
			return $arr;
		}
		return false;
	}
	
	//create meeting Format date time : 2018-04-26T15:20  // Y-m-d H:i:s
	public function createMeeting($webinar_name, $time_from, $time_to, $urlpath,$host_pri_id){
		$token = $this->getToken();
		$time_from = date('Y-m-d\TH:i', strtotime($time_from));
		$time_to = date('Y-m-d\TH:i', strtotime($time_to));
		$webinar_name = urlencode($webinar_name." ".date('d/M/Y', strtotime($time_from))); 
		$urlpath = urlencode($urlpath);
		$new_meeting = file_get_contents($this->url."/api/xml?action=sco-update&type=meeting&icon=virtual-classroom&name=".$webinar_name."&folder-id=".$this->_folder_id."&date-begin=".$time_from."&date-end=".$time_to."&url-path=".$urlpath."&session=" . $token);
		print_r($new_meetin);

		$new_meeting_obj = simplexml_load_string($new_meeting);		
		print_r($new_meeting_obj); die;
		if($new_meeting_obj->status['code'] == 'ok'){
			$arr['meeting_id'] = (int)$new_meeting_obj->sco['sco-id']; 
			$arr['meeting_url'] = $this->url . (string)$new_meeting_obj->sco->{'url-path'}; 
			$error = $this->addAccessToMeeting($arr['meeting_id']);
			$error = $this->addParticipantToMeeting($host_pri_id,$arr['meeting_id'],'host');
			return $arr;
		}
		return false;
	} 
	
	//update access permission to meeting
	public function addAccessToMeeting($sco_id){
		$token = $this->getToken();
		$permission_ = file_get_contents($this->url."/api/xml?action=permissions-update&acl-id=".$sco_id."&principal-id=public-access&permission-id=denied&session=" . $token);
		$permission_obj = simplexml_load_string($permission_);
		if($permission_obj->status['code'] == 'ok'){
			return true;
		} 
		return false;		
	}
	
	//add participant to meeting with privilege
	public function addParticipantToMeeting($principal_id, $meeting_id, $access_type = 'view'){
		$token = $this->getToken();
		$new_meeting_host = file_get_contents($this->url."/api/xml?action=permissions-update&principal-id=".$principal_id."&acl-id=".$meeting_id."&permission-id=".$access_type."&session=" . $token);
		$new_meeting_host_obj = simplexml_load_string($new_meeting_host);
		if($new_meeting_host_obj->status['code'] == 'ok'){
			if($access_type == 'view') {
				$error = $this->resetPassword($principal_id);
			}
			return true;
		} 
		return false;
	}
	
	//list all attendees with duration with meeting id
	public function getAttendeesWithDuration($meeting_id){
		$token = $this->getToken();
		$duration_call = file_get_contents($this->url."/api/xml?action=report-meeting-attendance&sco-id=".$meeting_id."&session=" . $token);
		$duration_call_obj = simplexml_load_string($duration_call);
		$arr = array();
		if($duration_call_obj->status['code'] == 'ok'){
			foreach($duration_call_obj->{'report-meeting-attendance'}->row as $r){	
				if(!isset($arr[(int)$r['principal-id']])){
					$arr[(int)$r['principal-id']] = 0;
				}	
				
				$date_created = date('Y-m-d H:i:s',strtotime((string)$r->{'date-created'}));
				$date_end = date('Y-m-d H:i:s',strtotime((string)$r->{'date-end'}));
				
				$tot_duration = ceil( (strtotime($date_end) - strtotime($date_created)) / 60 );
				if($tot_duration >= 0) 
					$arr[(int)$r['principal-id']] += ceil( (strtotime($date_end) - strtotime($date_created)) / 60 ) ;				
			}
			
			return $arr;
		} 
		return false;
	}
	
	//delete meeting, which subsequently also deletes meeting
	public function deleteMeeting($meeting_id){
		$token = $this->getToken();
		$concurrency_call = file_get_contents($this->url."/api/xml?action=sco-delete&sco-id=".$meeting_id."&session=" . $token);
		$concurrency_call_obj = simplexml_load_string($concurrency_call);
		if($concurrency_call_obj->status['code'] == 'ok'){
			return true;
		} 
		return false;
	}
	
	//set timezone of the user
	public function setTimezone($principal_id, $timezone){
		$token = $this->getToken();
		$timezone_id = @$this->_timezone_list[$timezone];
		$timezone_call = file_get_contents($this->url."/api/xml?action=acl-preference-update&acl-id=".$principal_id."&time-zone-id=".$timezone_id."&session=" . $token);
		$timezone_call_obj = simplexml_load_string($timezone_call);	
		if($timezone_call_obj->status['code'] == 'ok'){
			return true;
		} 
		return false;	
	}
	
	//get recorded archives of the meeting
	public function getRecordedArchives($meeting_id){
		$token = $this->getToken();
		$archive_call = file_get_contents($this->url."/api/xml?action=sco-expanded-contents&sco-id=".$meeting_id."&filter-icon=archive&session=" . $token);
		$archive_call_obj = simplexml_load_string($archive_call);
		print_r($archive_call_obj);die;
		if($archive_call_obj->status['code'] == 'ok'){
			foreach($archive_call_obj->{'expanded-scos'}->sco as $r){				
				$arr[] = (string)$r->{'url-path'};				
			}
			return $arr;
		} 
		return false;
		
	}
	
	//reset password of the user
	public function resetPassword( $principal_id){
		$token = $this->getToken();
		$password_update = file_get_contents($this->url."/api/xml?action=user-update-pwd&user-id=".$principal_id."&password=".$this->_user_password."&password-verify=".$this->_user_password."&session=" . $token);
		$password_update_obj = simplexml_load_string($password_update);	
		if($password_update_obj->status['code'] == 'ok'){
			return true;
		} 
		return false;	
	}
	
	//redirecting url to meeting
	public function redirectUrl($lgn, $meeting_url){
		$for_token = get_headers($this->url."/api/xml?action=login&login=".$lgn."&password=".$this->_user_password);
		$token1 = explode("BREEZESESSION=",implode("",$for_token));
		$token2 = explode(";",$token1[1]);
		$token = $token2[0];
		return $meeting_url."?session=".$token;	
	}
	
	public function redirectUrlRec($lgn, $rec_url){
		
		$for_token = get_headers($this->url."/api/xml?action=login&login=".$lgn."&password=".$this->_user_password);
		$token1 = explode("BREEZESESSION=",implode("",$for_token));
		$token2 = explode(";",$token1[1]);
		$token = $token2[0];
		return $meeting_url."&session=".$token;
		
		
	}
	
	
}

?>
