<?php
// Dashboard
function chatnox_configuration() {
?>
<div class="wrap">

<?php

$chatnoxerror = ""; $chatnoxgotologin = 0; $chatnoxmessage = "";$chatnox_show_login = true;
$saved_account_id  = chatnox_livechat_get_options(CHATNOX_DB_USER_ACCOUNT_ID);

// echo "<script>console.log(id = ".$saved_account_id.");</script>";


// Existing account 
if (!isset($_GET["action"]) && !isset($_POST["action"]) && isset($saved_account_id) && $saved_account_id != false) {

    $chatnox_show_login = false;

    // Get group names from account_id
	
	$userdata = array("action" => "get_groups", "account_id" => chatnox_livechat_get_options(CHATNOX_DB_USER_ACCOUNT_ID), "slot_id" => chatnox_livechat_get_options(CHATNOX_DB_SLOT_NAME));
	$logindata = json_encode($userdata);
			$loginresult = chatnox_post_request(CHATNOX_LOGIN_URL, $logindata);

			try {
				$loginresult = json_decode($loginresult);
			  }
			catch(Exception $e) {
                echo "Exception = ".$e->getMessage();
				// $loginresult = json_decode($loginresult, true);
			}

            $status = $loginresult->status;
			if($status == "error"){
				$chatnoxgotologin = 1;
				$chatnox_show_login = true;
				$chatnoxerror = "<b style='color: rgb(186, 58, 58);'>".$loginresult->message."</b>";
			} else {

                $widgets = $loginresult->response->widgets;


?>
	
	   <div id="icon-options-general" class="icon32"><br></div><h2>Your ChatNox Account is Connected</h2>
				<br>
				<div style="background:#FFFEEB;padding:25px;border:1px solid #eee;">
				<span style="float:right;"><a href="admin.php?page=chatnox_configuration&amp;action=deactivate">Deactivate</a></span>
				ChatNox Account Name : <b><?php echo chatnox_livechat_get_options(CHATNOX_DB_USER_NAME); ?></b> <div style="display:inline-block;background:#444;color:#fff;font-size:10px;text-transform:uppercase;padding:3px 8px;-moz-border-radius:5px;-webkit-border-radius:5px;"><?php echo $loginresult->response->plan;?></div>

                <br><br><b>Linked ChatNox Group/Department ID - '<?php echo chatnox_livechat_get_options(CHATNOX_DB_SLOT_NAME);?>' </b><div style="display:inline-block;background:#444;color:#fff;font-size:10px;text-transform:uppercase;padding:3px 8px;-moz-border-radius:5px;-webkit-border-radius:5px;"><?php echo $loginresult->response->installed_group_name;?></div>

				<form method="post" action="admin.php?page=chatnox_configuration" enctype="multipart/form-data">
				    <input type="hidden" value="install_widget" name="action" />
					<p>
					Select Group to Change:
					<br>
					     <select style="vertical-align: middle;padding: 7px;min-width: 200px;height: 40px;" name="widget_id">
						     <?php
					              for ($x=0; $x<sizeof($widgets); $x++) {
								     echo "<option value=".$widgets[$x]->id.">". $widgets[$x]->name ."</option>";
								  }  
					          ?>
						 </select>
					     <input type="submit" value="Change Group" class="chatnox_btn_orange" style="vertical-align: middle;" />
					</p>
				</form>


				<br><br>To start using ChatNox chat, launch our dashboard for access to all features, including widget customization!
				<br><br><a href="<?php echo CHATNOX_DOMAIN_URL;?>/?utm_source=wp&amp;utm_medium=link&amp;utm_campaign=wp%2Bdashboard&amp;username=<?php echo chatnox_livechat_get_options(CHATNOX_DB_USER_NAME); ?>" style="text-decoration:none;" target="_blank" data-popup="true"><div class="chatnox_btn_orange">Launch Dashboard</div></a>&nbsp;&nbsp;(This will open up a new browser tab)

		</div>
<?php
			}
}


// Deactivate plugin
if (isset($_GET["action"]) && $_GET["action"]=="deactivate") {
     // Delete all options for db
	 delete_option( CHATNOX_DB_SLOT_NAME );
	 delete_option( CHATNOX_DB_USER_ACCOUNT_ID );
	 $chatnoxgotologin = 1;
}

// Activate plugin
else if (isset($_POST["action"]) && $_POST["action"]=="install_widget") {
    chatnox_livechat_save_options(CHATNOX_DB_SLOT_NAME,  $_POST["widget_id"] );
?>
	
	   <div id="icon-options-general" class="icon32"><br></div><h2>Congratulations! ChatNox is now Connected to Wordpress!</h2>
				<br>
				<div style="background:#FFFEEB;padding:25px;border:1px solid #eee;">
				<span style="float:right;"><a href="admin.php?page=chatnox_configuration&amp;action=deactivate">Deactivate</a></span>
				Currently activated ChatNox account: <b><?php echo chatnox_livechat_get_options(CHATNOX_DB_USER_NAME); ?></b> 

                <br>To start using ChatNox live-chat, launch the dashboard for access to all features, including widget customization!
				<br><br><a href="<?php echo CHATNOX_DOMAIN_URL;?>/?utm_source=wp&amp;utm_medium=link&amp;utm_campaign=wp%2Bdashboard&amp;username=<?php echo chatnox_livechat_get_options(CHATNOX_DB_USER_NAME); ?>" style="text-decoration:none;" target="_blank" data-popup="true"><div class="chatnox_btn_orange">Launch Dashboard</div></a><br>(This will open up a new browser tab)

		</div>
<?php
}

// Login
else if (isset($_POST["action"]) && $_POST["action"]=="signin") {
    
	// Get login credentials
	$username =  $_POST["chatnoxusername"];
	$password =  $_POST["chatnoxpassword"];

	if ($username != "" || $password != "") {

			$logindata = array("action" => "signin", "username" => $username, "password" => $password);			
			$logindata = json_encode($logindata);
			$loginresult = chatnox_post_request(CHATNOX_LOGIN_URL, $logindata);

			
			try {
				$loginresult = json_decode($loginresult);
			  }
			catch(Exception $e) {
                echo "Exception = ".$e->getMessage();
				// $loginresult = json_decode($loginresult, true);
			}

            $status = $loginresult->status;
			if($status == "error"){
				$chatnoxgotologin = 1;
				$chatnoxerror = "<b style='color: rgb(186, 58, 58);'>".$loginresult->message."</b>";
			} else {

				// Set accountId in DB
				$account_id = $loginresult->response->account_id;

				if(isset($account_id)){
					 chatnox_livechat_save_options(CHATNOX_DB_USER_ACCOUNT_ID, $account_id);
					 chatnox_livechat_save_options(CHATNOX_DB_USER_NAME, $username);
				}
					 

                $widgets = $loginresult->response->widgets;

				// if(sizeof($widgets) > 1){
					   ?>
				<div id="icon-options-general" class="icon32"><br></div><h2>Connect Your ChatNox Account</h2>
				<br>
				<div style="background:#FFFEEB;padding:25px;border:1px solid #eee;">
				<span style="float:right;"><a href="admin.php?page=chatnox_configuration&amp;action=deactivate">Deactivate</a></span>
				Your ChatNox Account Name : <b><?php echo chatnox_livechat_get_options(CHATNOX_DB_USER_NAME); ?></b> <div style="display:inline-block;background:#444;color:#fff;font-size:10px;text-transform:uppercase;padding:3px 8px;-moz-border-radius:5px;-webkit-border-radius:5px;"><?php echo $loginresult->response->plan;?></div>

				<form method="post" action="admin.php?page=chatnox_configuration" enctype="multipart/form-data">
				    <input type="hidden" value="install_widget" name="action" />
					<p>
					Select the Chat Group to Install &nbsp;(Use default if you are not sure):
					<br>
					     <select style="vertical-align: middle;padding: 7px;min-width: 200px;height: 40px;" name="widget_id">
						     <?php
					              for ($x=0; $x<sizeof($widgets); $x++) {
								     echo "<option value=".$widgets[$x]->id.">". $widgets[$x]->name ."</option>";
								  }  
					          ?>
						 </select>
					     <input type="submit" value="Install Group" class="chatnox_btn_orange" style="vertical-align: middle;" />
					</p>
				</form>

				<br><br>To start using ChatNox live-chat, launch the ChatNox dashboard for access to all features, including widget customization!
				<br><br><a href="<?php echo CHATNOX_DOMAIN_URL;?>/?utm_source=wp&amp;utm_medium=link&amp;utm_campaign=wp%2Bdashboard&amp;username=<?php echo chatnox_livechat_get_options(CHATNOX_DB_USER_NAME); ?>" style="text-decoration:none;" target="_blank" data-popup="true"><div class="chatnox_btn_orange">Launch Dashboard</div></a>&nbsp;&nbsp;(This will open up a new browser tab)


				</div>
					   

					   <?php
				// }else {
					//    echo "Single one";
				// }

			}

		}
		else {
			$chatnoxgotologin = 1;
			$chatnoxerror = "<b style='color: rgb(186, 58, 58);'>Could not log in to ChatNox. Please check your login details.</b>";
		}

}

if((!isset($_POST["action"]) || (isset($_GET["action"]) && isset($_GET["action"]) == "deactivate") || $chatnoxgotologin == 1) && $chatnox_show_login){
?>
<div id="icon-options-general" class="icon32"><br/></div><h2>Connect Your ChatNox Account</h2>
<div id="existingform">
	<div class="metabox-holder">
		<div class="postbox">
			<h3 class="hndle"><span>Link Your ChatNox Account</span></h3>
			<div style="padding:10px;">    
<form method="post" action="admin.php?page=chatnox_configuration">
	<input type="hidden" name="action" value="signin">
    <?php if(isset($chatnoxerror)) echo $chatnoxerror;?>
	<table class="form-table">

			<tr valign="top">
			<th scope="row">ChatNox Username (E-mail)</th>
			<td><input type="text" name="chatnoxusername" value="<?php echo chatnox_livechat_get_options(CHATNOX_DB_USER_NAME); ?>" /></td>
			</tr>

			<tr valign="top">
			<th scope="row">ChatNox Password</th>
			<td><input type="password" name="chatnoxpassword" value=""/></td>
			</tr>

	 </table>
		<br/>
		The ChatNox chat widget will display on your blog after your account is connected.
		<br/>
		<p class="submit">
		<input type="submit" class="button-primary" value="<?php _e('Connect') ?>" />
		&nbsp;If you do not have a ChatNox account, Get a free ChatNox account from our website.  <a href="<?php echo CHATNOX_DASHBOARD_LINK; ?>" target="_blank" data-popup="true">Sign up now</a>.
		</p>

</form>
     </div>
    </div>
   </div>
</div>
</div>


<?php
} 
?>



</div>

<?php
}
?>
