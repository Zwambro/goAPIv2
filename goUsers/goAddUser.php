<?php
//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);
   //////////////////////////////////#
   //# Name: goAddUser.php                        //#
   //# Description: API to add new user           //#
   //# Version: 0.9                               //#
   //# Copyright: GOAutoDial Ltd. (c) 2011-2015   //#
   //# Written by: Jeremiah Sebastian Samatra     //#
   //#	Updated by: Alexander Jim Abenoja          //#
   //# License: AGPLv2                            //#
   //////////////////////////////////#
    
    include_once ("../goFunctions.php");
	include_once ("../licensed-conf.php");
	
    // POST or GET Variables
       // $values = $_REQUEST['items'];
        $orig_user = mysqli_real_escape_string($link, $_REQUEST['user']);
        $pass = mysqli_real_escape_string($link, $_REQUEST['pass']);
        $orig_full_name = mysqli_real_escape_string($link, $_REQUEST['full_name']);
        $phone_login = mysqli_real_escape_string($link, $_REQUEST['phone_login']);
        $phone_pass = mysqli_real_escape_string($link, $pass);
        $user_group = mysqli_real_escape_string($link, $_REQUEST['user_group']);
        $active = strtoupper($_REQUEST['active']);
        $location = mysqli_real_escape_string($link, $_REQUEST['location_id']);
		
		if(isset($_REQUEST['seats']))
        $seats = mysqli_real_escape_string($link, $_REQUEST['seats']);
		else
		$seats = 1;
		
		$avatar = mysqli_real_escape_string($link, $_REQUEST['avatar']);
		$goUser = $_REQUEST['goUser'];
        $ip_address = $_REQUEST['hostname'];
		
		$log_user = mysqli_real_escape_string($link, $_REQUEST['log_user']);
		$log_group = mysqli_real_escape_string($link, $_REQUEST['log_group']);

    // Default values 
        $defActive = array("Y","N");

    // Error Checking
	if(preg_match('/[\'^£$%&*()}{@#~?><>,|=_+¬-]/', $orig_user)){
		$err_msg = error_handle("41004", "user");
		$apiresults = array("code" => "41004", "result" => $err_msg);
		//$apiresults = array("result" => "Error: Special characters found in user");
	} else {
	if(preg_match('/[\'^£$%&*()}{@#~?><>,|=_+¬-]/', $pass)){
		$err_msg = error_handle("41004", "pass");
		$apiresults = array("code" => "41004", "result" => $err_msg);
		//$apiresults = array("result" => "Error: Special characters found in password");
	} else {
	if(preg_match('/[\'^£$%&*()}{@#~?><>|=+¬]/', $orig_full_name)){
		$err_msg = error_handle("41004", "full_name");
		$apiresults = array("code" => "41004", "result" => $err_msg);
		//$apiresults = array("result" => "Error: Special characters found in full_name");
	} else {
        if($orig_user == null) {
			$err_msg = error_handle("40001");
			$apiresults = array("code" => "40001", "result" => $err_msg);
            //$apiresults = array("result" => "Error: Set a value for User.");
        } else {
        if($pass == null) {
			$err_msg = error_handle("40001");
			$apiresults = array("code" => "40001", "result" => $err_msg);
            //$apiresults = array("result" => "Error: Set a value for password.");
        } else {
        if($orig_full_name == null) {
			$err_msg = error_handle("40001");
			$apiresults = array("code" => "40001", "result" => $err_msg);
            //$apiresults = array("result" => "Error: Set a value for Full name.");
        } else {
        if($user_group == null) {
			$err_msg = error_handle("40001");
			$apiresults = array("code" => "40001", "result" => $err_msg);
            //$apiresults = array("result" => "Error: Set a value for User Group.");
        } else {
			if(!in_array($active,$defActive) && $active != null) {
				$err_msg = error_handle("41006", "active");
				$apiresults = array("code" => "41006", "result" => $err_msg);
				//$apiresults = array("result" => "Error: Default value for active is Y or N only.");
			} else {
				// Check License Seats //
				$license_query = mysqli_query($link, "SELECT user FROM vicidial_users WHERE user NOT IN ('VDAD','VDCL', 'goAPI') AND user_level != '4' ORDER BY user ASC");
				$num_users = mysqli_num_rows($license_query);
				
				// Check if DB Licensed Seats Exists //
				$check_db_license = "SELECT * FROM settings WHERE setting = 'GO_licensed_seats' LIMIT 1;";
        			$exec_check_license = mysqli_query($linkgo, $check_db_license);
        			$num_check_license = mysqli_num_rows($exec_check_license);			
				
				if($num_check_license > 0){
					$fetch_license = mysqli_fetch_array($exec_check_license);
					$licensedSeats = $fetch_license["value"];
				}else{
					$licensedSeats = $config["licensedSeats"];
				}
			
				$error_count = 0;
				$checker = 0;
				if($num_users <= $licensedSeats || $licensedSeats == "0"){
					
					$queryPassHash = "SELECT pass_hash_enabled from system_settings";
					$resultQueryPassHash = mysqli_query($link, $queryPassHash);
					$rPassHash = mysqli_fetch_array($resultQueryPassHash, MYSQLI_ASSOC);
					$pass_hash_enabled = $rPassHash['pass_hash_enabled'];
					
					$get_last = preg_replace("/[^0-9]/","", $orig_user);
					$last_num_user = intval($get_last);
					
					$get_last2 = preg_replace("/[^0-9]/","", $orig_full_name);
					$last_num_name = intval($get_last2);
					
					$arr_user = array();
					//$test = array();
					$add_num = 0;
					for($i=0;$i < $seats;$i++){
						$iterate_number1 = $last_num_user + $add_num;
						$iterate_number2 = $last_num_name + $add_num;
						
						if($iterate_number1 > 0){
							$user = str_replace($last_num_user,$iterate_number1,$orig_user);
						}else{
							$user = $orig_user;
							if($last_num_user === 0 && $seats > 0){
								$orig_user = $orig_user."1";
								$last_num_user = 1;
							}
						}
						
						//array_push($test, $iterate_number1."=".$orig_user);
						
						if($iterate_number2 > 0)
						$full_name = str_replace($last_num_name,$iterate_number2,$orig_full_name);
						else{
							$full_name = $orig_full_name;
							if($last_num_name === 0 && $seats > 0){
								$orig_full_name = $orig_full_name.'1';
								$last_num_name = 1;
							}
						}
						
						$phone_login = $phone_login + $add_num;
						
						$add_num = $add_num + 1;
						
						$groupId = go_get_groupid($goUser);
						if (!checkIfTenant($groupId)) {
							$ulUser = "AND user='$user'";
							$ulug = "WHERE user_group='$user_group'";
						} else {
							$ulUser = "AND user='$user' AND user_group='$groupId'";
							$ulug = "WHERE user_group='$user_group' AND user_group='$groupId'";
						}

						if(!empty($location)){
							$result_location = go_check_location($location, $user_group);
							
							if($result_location < 1){
								$err_msg = error_handle("41006", "location. User group does not exist in the location selected.");
								$apiresults = array("code" => "41006", "result" => $err_msg);
								$location = "";
							}
						}else{
							$location = "";
						}
						
						$query = "SELECT user_group,group_name,forced_timeclock_login FROM vicidial_user_groups $ulug ORDER BY user_group LIMIT 1;";
						$rsltv = mysqli_query($link, $query);
						$countResult = mysqli_num_rows($rsltv);
						
						if($countResult > 0) {
							
							$queryUserCheck = "SELECT user, full_name, user_level, user_group, phone_login, active FROM vicidial_users WHERE user NOT IN ('VDAD','VDCL') AND user_level != '4' AND '$phone_login' = (SELECT phone_login FROM vicidial_users WHERE phone_login = '$phone_login')  $ulUser ORDER BY user ASC LIMIT 1;";
							$rsltvCheck = mysqli_query($link, $queryUserCheck);
							$countCheckResult = mysqli_num_rows($rsltvCheck);
							
							if($countCheckResult <= 0) {
								
								$querygetserverip = "select server_ip from servers;";
								$rsltserverip = mysqli_query($link, $querygetserverip);
								$rServerIP = mysqli_fetch_array($rsltserverip, MYSQLI_ASSOC);
								$server_ip = $rServerIP['server_ip'];
							
								if(strtolower($user_group) == "admin"){
									$user_level = 9;
									$phone_pass = "";
									$phone_login = "";
									$agentcall_manual = 1;
									$agentonly_callbacks = 1;
								} else {
									$user_level = 1;
									$agentcall_manual = 1;
									$agentonly_callbacks = 1;
								}
							
							# generate random phone login
								$x = 0;
								$y = 0;
								while($x == $y){
									$random_digit = mt_rand(1000000000, 9999999999);
									$check_existing_phonelogins_query = "SELECT phone_login FROM vicidial_users WHERE phone_login = '$random_digit';";
									$check_existing_phonelogins_exec_query = mysqli_query($link, $check_existing_phonelogins_query);
									
									if($check_existing_phonelogins_exec_query == true){
										$y = 1;
										$phone_login = $random_digit;
									}
								}
							
								$pass_hash = '';
								if ($pass_hash_enabled > 0) {
									$cwd = $_SERVER['DOCUMENT_ROOT'];
									$pass_hash = exec("{$cwd}/bin/bp.pl --pass=$pass");
									$pass_hash = preg_replace("/PHASH: |\n|\r|\t| /",'',$pass_hash);
									//$pass = '';
								}
								
								$queryUserAdd = "INSERT INTO  vicidial_users (user, pass, user_group, full_name, user_level, phone_login, phone_pass, agentonly_callbacks, agentcall_manual, active, vdc_agent_api_access,pass_hash, agent_choose_ingroups, vicidial_recording, vicidial_transfers, closer_default_blended, scheduled_callbacks) VALUES ('$user', '$pass', '$user_group', '$full_name', '$user_level', '$phone_login', '$phone_pass', '$agentonly_callbacks', '$agentcall_manual', '$active', '1', '$pass_hash', '1', '1', '1', '1', '1');";
								$resultQueryAddUser = mysqli_query($link, $queryUserAdd);
								
								$queryUserID = "SELECT user_id from vicidial_users WHERE user='$user'";
								$resultQueryUserID = mysqli_query($link, $queryUserID);
								$rUserID = mysqli_fetch_array($resultQueryUserID, MYSQLI_ASSOC);
								$userid = $rUserID['user_id'];
								
								if ($active == "N") {
                                    $goactive = "0";
								} else {
                                    $goactive = "1";
								}
								
								$queryUserAddGo = "INSERT INTO users (userid, name, fullname, avatar, role, status, user_group, phone, location_id) VALUES ('$userid', '$user', '$full_name', '$avatar', '$user_level', '$goactive', '$user_group', '$phone_login', '$location')";
								$resultQueryAddUserGo = mysqli_query($linkgo, $queryUserAddGo);
								
							// Admin logs
								//$SQLdate = date("Y-m-d H:i:s");
								//$queryLog = "INSERT INTO go_action_logs (user,ip_address,event_date,action,details,db_query) values('$goUser','$ip_address','$SQLdate','ADD','Added New User $user','INSERT INTO vicidial_users (user,pass,full_name,phone_login,phone_pass,user_group,active) VALUES ($user,$pass,$full_name,$phone_login,$phone_pass,$user_group,$active)');";
								//$rsltvLog = mysqli_query($linkgo, $queryLog);
								$log_id = log_action($linkgo, 'ADD', $log_user, $ip_address, "Added New User: $user", $log_group, $queryUserAdd);
						
								$queryUserCheckAgain = "SELECT user  FROM vicidial_users WHERE user NOT IN ('VDAD','VDCL') AND user_level != '4' $ulUser ORDER BY user ASC LIMIT 1;";
						//		$queryUserCheckAgain = "SELECT user  FROM vicidial_users WHERE user NOT IN ('VDAD','VDCL') AND user_level != '4' AND user='agent093' ORDER BY user ASC LIMIT 1;";
								$rsltvCheckAgain = mysqli_query($link, $queryUserCheckAgain);
								$countCheckResultAgain = mysqli_num_rows($rsltvCheckAgain);
										
								if($countCheckResultAgain > 0) {
									
									$queryg = "SELECT value FROM settings WHERE setting='GO_agent_wss_sip';";
									$rsltg = mysqli_query($linkgo, $queryg);
									$rowg = mysqli_fetch_array($rsltg, MYSQLI_ASSOC);
									$realm = $rowg['value'];
									
									$kamha1fields = '';
									$kamha1values = '';
									if ($pass_hash_enabled > 0) {
										$ha1 = md5("{$phone_login}:{$realm}:{$phone_pass}");
										$ha1b = md5("{$phone_login}@{$realm}:{$realm}:{$phone_pass}");
										$kamha1fields = ", ha1, ha1b";
										$kamha1values = ", '{$ha1}', '{$ha1b}'";
										$phone_pass = '';
									}
									
									$queryd = "SELECT value FROM settings WHERE setting='GO_agent_domain';";
									$rsltd = mysqli_query($linkgo, $queryd);
									$rowd = mysqli_fetch_array($rsltd, MYSQLI_ASSOC);
									$domain = (!is_null($rowd['value']) || $rowd['value'] !== '') ? $rowd['value'] : 'goautodial.com';
									
									$queryInsertUser = "INSERT INTO `phones` (`extension`,  `dialplan_number`,  `voicemail_id`,  `phone_ip`,  `computer_ip`,  `server_ip`,  `login`,  `pass`,  `status`,  `active`,  `phone_type`,  `fullname`,  `company`,  `picture`,  `protocol`,  `local_gmt`,  `outbound_cid`,  `template_id`,  `conf_override`,  `user_group`,  `conf_secret`,  `messages`,  `old_messages`) VALUES ('$phone_login',  '9999$phone_login',  '$phone_login',  '',  '', '$server_ip',  '$phone_login',  '$phone_pass',  'ACTIVE',  '$active',  '',  '$full_name',  '$user_group',  '',  'EXTERNAL',  '-5',  '0000000000',  '--NONE--',  '$conf_override',  '$user_group',  '$phone_pass',  '0',  '0');";
									$resultQueryUser = mysqli_query($link, $queryInsertUser);
									
									$kamailioq = "INSERT INTO subscriber (username, domain, password{$kamha1fields}) VALUES ('$phone_login','$domain','$phone_pass'{$kamha1values});";
									$resultkam = mysqli_query($linkgokam, $kamailioq);
									
									//$return_user = $user." (".$userid.")";
									$return_user = $userid;
									array_push($arr_user, $return_user);
									
								} else {
									$err_msg = error_handle("41004", "user");
									$apiresults = array("code" => "41004", "result" => $err_msg);
									//$apiresults = array("result" => "Error: A problem occured while adding a user. Please Contact the System Administrator.");
								}
								
							} else {
								$error_count = 1;
								$i = $i - 1;
							}
						} else {
							$error_count = 2;
						}
					}
					
					if($error_count == 0){
						$apiresults = array("result" => "success", "user created" => $arr_user);
					}elseif($error_count == 1){
						$err_msg = error_handle("10113");
						$apiresults = array("code" => "10113", "result" => $err_msg);
						//$apiresults = array("result" => "Error: User already exist.");
					}else{
						$err_msg = error_handle("41004", "user_group");
						$apiresults = array("code" => "41004", "result" => $err_msg);
						//$apiresults = array("result" => "Error: Invalid User group");
					}
					
					
				}else{
					$err_msg = error_handle("10004", "seats. Reached Maximum Licensed Seats!");
					$apiresults = array("code" => "10004", "result" => $err_msg);
					//$apiresults = array("result" => "Error: Reached Maximum Licensed Seats!");
				}
			}
	}}}	  
	}}}
    }
?>
