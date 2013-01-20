<?php
/*
Plugin Name: WordPress Quest
Plugin URI: http://adamburt.com/work/
Description: WordPress Quest adds quests or achievements to your site based on user interaction. Gamification gathers you more page clicks and reduces bounce rates, and most importantly, it's fun!
Version: 1.0
Author: Carbine
Author URI: http://www.adamburt.com
License: GPL2
*/
?>
<?php
/*  Copyright 2012  Adam Burt (email : Adam@adamburt.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/
?>
<?php

// TODO: (1.1+)
// Use PDO rather than MySQL if available. Generally, security needs to be better and there should be less queries.

//Upon activation, create tables
global $wp_quest_db_version;
$wp_quest_db_version = "1.0";

function wp_quest_install() {
   global $wpdb;
   global $wp_quest_db_version;

   $tablenames = array(
	   	array('name' => 'levels', 'sql' => "  
	   		`id` int(11) NOT NULL AUTO_INCREMENT,
		  `name` varchar(100) NOT NULL,
		  `xp_req` int(11) NOT NULL,
		  `icon_path` varchar(200) NOT NULL,
		  PRIMARY KEY (`id`)"),
	   	array('name' => 'quests', 'sql' => "  
	   		`id` int(11) NOT NULL AUTO_INCREMENT,
		  `name` varchar(100) NOT NULL,
		  `rule_1` int(11) NOT NULL DEFAULT '0',
		  `rule_2` int(11) NOT NULL DEFAULT '0',
		  `rule_3` int(11) NOT NULL DEFAULT '0',
		  `rule_4` int(11) NOT NULL DEFAULT '0',
		  `rule_5` int(11) NOT NULL DEFAULT '0',
		  `rule_6` int(11) NOT NULL DEFAULT '0',
		  `rule_7` int(11) NOT NULL DEFAULT '0',
		  `rule_8` int(11) NOT NULL DEFAULT '0',
		  `rule_9` int(11) NOT NULL DEFAULT '0',
		  `rule_10` int(11) NOT NULL DEFAULT '0',
		  `xp` int(11) NOT NULL DEFAULT '0',
		  `icon_path` varchar(200) NOT NULL,
		  PRIMARY KEY (`id`)"),
	   	array('name' => 'rules', 'sql' => "
	   		`id` int(11) NOT NULL AUTO_INCREMENT,
		  `name` varchar(100) NOT NULL,
		  `type` varchar(100) NOT NULL,
		  `page` varchar(100) NOT NULL DEFAULT '0',
		  `num` int(11) NOT NULL DEFAULT '0',
		  PRIMARY KEY (`id`)"),
	   	array('name' => 'user_levels', 'sql' => "
	   		`id` int(11) NOT NULL AUTO_INCREMENT,
	   		`user` int(11) NOT NULL,
		  `level` int(11) NOT NULL,
		  `current_xp` int(11) NOT NULL DEFAULT '0',
		  `notified` int(11) NOT NULL DEFAULT '0',
		  PRIMARY KEY (`id`)
		  "),
	   	array('name' => 'user_quests', 'sql' => "
	   		`id` int(11) NOT NULL AUTO_INCREMENT,
	   		`user` int(11) NOT NULL,
		  `quest` int(11) NOT NULL,
		  `time_completed` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
		  `notified` int(11) NOT NULL DEFAULT '0',
		  PRIMARY KEY (`id`)
		  "),
	   	array('name' => 'user_rules', 'sql' => "
	   		`id` int(11) NOT NULL AUTO_INCREMENT,
		  `user` int(11) NOT NULL,
		  `rule` int(11) NOT NULL,
		  `time_completed` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
		  `partial` int(1) NOT NULL DEFAULT '0',
		  `partial_num` int(1) NOT NULL DEFAULT '0',
		  PRIMARY KEY (`id`)
		  ")
   	);

   require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

   foreach($tablenames as $tn){
	   $table_name = $wpdb->prefix . "wp_quest_".$tn['name'];
	   $commands = $tn['sql'];
	   $sql = "CREATE TABLE IF NOT EXISTS $table_name ($commands);";
	   
   	   dbDelta($sql);
	}
 
   add_option("wp_quest_db_version", $wp_quest_db_version);
}

function register_wp_quest_settings() {
	//register our settings
		//Display defaults
		register_setting( 'wpq-display-group', 'level' );
		register_setting( 'wpq-display-group', 'xp' );
		register_setting( 'wpq-display-group', 'distance' );
		register_setting( 'wpq-display-group', 'quest' );
		register_setting( 'wpq-display-group', 'recent-quest' );
		register_setting( 'wpq-display-group', 'last-quest' );

		//TODO: Notification defaults
		/*register_setting( 'wpq-notification-group', 'position' );
		register_setting( 'wpq-notification-group', 'dfq' );//Default for quest
		register_setting( 'wpq-notification-group', 'dfl' );//Default for level */
	
	//No databases? No problem! Let's make those bad boys now!
	wp_quest_install();
}
register_activation_hook(__FILE__,'register_wp_quest_settings');

function wp_quest_menu() {
	//Add top level menu
	add_menu_page('WP Quest', 'WP Quest', 'administrator', 'wp-quest', wp_quest_top_level_redirect,   plugins_url('wp-quest/images/icon.png'), 22);
		//Sub menus
			//Rules
			add_submenu_page( 'wp-quest', 'Rules', 'Rules', 'administrator', 'wp-quest', wp_quest_rules );
			//Quests
			add_submenu_page( 'wp-quest', 'Quests', 'Quests', 'administrator', 'wp-quest-quests', wp_quest_quests );
			//Levels
			add_submenu_page( 'wp-quest', 'Levels', 'Levels', 'administrator', 'wp-quest-levels', wp_quest_levels );
			//Settings
			add_submenu_page( 'wp-quest', 'Settings', 'Settings', 'administrator', 'wp-quest-settings', wp_quest_settings );
			//Analytics
			add_submenu_page( 'wp-quest', 'Analytics', 'Analytics', 'administrator', 'wp-quest-analytics', wp_quest_analytics );
	
	//call register settings function
	add_action( 'admin_init', 'register_wp_quest_settings' );
}
add_action('admin_menu', 'wp_quest_menu');

function wp_quest_top_level_redirect(){
	//Redirect to first level page
}

function wp_quest_rules(){
	global $wpdb;
	//List of rules
	$tablename = $wpdb->prefix.'wp_quest_rules';

	$error = "";

	//Posted? let's add it!
	if($_POST['submit']){
		//VALIDATE FOR
			//Fields empty
			//Strings less than 100
			//Numeric num
		if(isset($_POST['nor']) && $_POST['nor'] != "" && strlen($_POST['nor'] < 101)){
			$nor = $_POST['nor'];
		} else {
			$error .= "<p>Please enter a rule name (Less than 100 characters)</p>";
		}
		//TODO: (1.3) Check rule types against actual allowed types
		if(isset($_POST['rule-type']) && $_POST['rule-type'] != ""){
			$rule_type = $_POST['rule-type'];
		} else {
			$error .= "<p>Error: Rule Type Incorrect</p>";
		}
		if(isset($_POST['on-page']) && $_POST['on-page'] != "" && strlen($_POST['on-page'] < 101)){
			$on_page = $_POST['on-page'];
		} else {
			$error .= "<p>Error: Page not supported.</p>";
		}
		if(isset($_POST['number']) && $_POST['number'] != "" && is_numeric($_POST['number'])){
			$number = $_POST['number'];
		} else {
			$error .= "<p>Error: Please enter a number (numerically, e.g 1 or 200)</p>";
		}
		//TODO (1.3) ALSO VALIDATE FOR
			//If login rule type, page is irrelevant.


		if($error == ""){
			$wpdb->insert( 
				$tablename, 
				array( 
					'name' => $nor, 
					'type' => $rule_type,
					'page' => $on_page,
					'num' => $number
				), 
				array( 
					'%s', 
					'%s',
					'%s',
					'%d' 
				) 
			);
		}
	}
	//TODO: (1.2) Delete rules (Without breaking Quests)
	$rule_query = mysql_query("SELECT * FROM $tablename");
	echo "<h2>Rules</h2>";
	if(mysql_num_rows($rule_query) > 0){
		echo "<table class='widefat'>
		<tr><th scope='col' class='column-name'>Rule name</th><th scope='col' class='column-name'>Rule type</th><th scope='col' class='column-name'>On Page</th><th scope='col' class='column-name'>Number of times required</th></tr>";
		while ($row = mysql_fetch_array($rule_query)){
			echo "<tr><td>".$row['name']."</td><td>".$row['type']."</td><td>".$row['page']."</td><td>".$row['num']."</td><tr>";
		}
		echo "</table>";
	} else {
		echo "<p>No rules so far. Why not add one now?</p>";
	}
	
	//New rule form
		//Name
	?>
	<h3>Add New Rule</h3>
	<?php if($error != ""){ ?>
	<div class="error errortext"><?php echo $error; ?></div>
	<?php } ?>
	<form id="rule-add" method="post" action="">
	<table class="form-table">

			<tr>
				<th><label for="nor">Name of Rule</label></th>

				<td>
					<input type="text" name="nor" id="nor" value="" class="regular-text" /><br />
					<span class="description">Please enter a name for your rule</span>
				</td>
			</tr>
	        <tr>
				<th><label for="rule-type">Rule Type</label></th>

				<td>
					<select name="rule-type" id="rule-type">
						<option value="visit">Visits</option>
						<option value="comment" disabled>Comments - Coming Soon</option>
						<option value="login" disabled>Logins - Coming</option>
					</select><br />
					<span class="description">Please select the type of rule you would like to add</span>
				</td>
			</tr>
	        <tr>
				<th><label for="on-page">On Page</label></th>

				<td>
					<?php 
						$pages = get_pages();
						$posts = get_posts();
					?>
					<select name="on-page" id="on-page">
						<option value="0">Any</option>
						<optgroup label="Pages">
						<?php foreach ($pages as $page){ ?> 
							<option value="<?php echo $page->ID; ?>"><?php echo $page->post_title; ?></option>
						<?php } ?>
						</optgroup>
						<optgroup label="Posts">
						<?php foreach ($posts as $post){ ?> 
							<option value="<?php echo $post->ID; ?>"><?php echo $post->post_title; ?></option>
						<?php } ?>
						</optgroup>
					</select><br />
					<span class="description">Please select the page you wish this rule to be applied to. For example, setting a Visit rule on Homepage will trigger victory when a user visits a homepage.</span>
				</td>
			</tr>
			 <tr>
				<th><label for="number">Number</label></th>

				<td>
						<input type="text" name="number" id="number" value="" class="regular-text" /><br />
					<span class="description">The number of times this condition must be met to trigger victory.</span>
				</td>
			</tr>
		</table>
		<p><input type="submit" value="Save Rule" class="button-primary" id="submit" name="submit"></p>
	</form>
	<?php


}

function wp_quest_quests(){
	global $wpdb;
	//List of quests
	$tablename = $wpdb->prefix.'wp_quest_quests';
	$rule_table = $wpdb->prefix.'wp_quest_rules';

	$error = "";

	//Posted? let's add it!
	if($_POST['submit']){
		//VALIDATE FOR
			//Fields empty
			//Strings less than 100
			//Numeric num
		$to_add = array();
		if(isset($_POST['noq']) && $_POST['noq'] != "" && strlen($_POST['noq'] < 101)){
			$to_add['name'] = $_POST['noq'];
		} else {
			$error .= "<p>Please enter a quest name (Less than 100 characters)</p>";
		}

		$i = 0;
		$rule_set = false;
		while($i < 10){
			$plusone = $i + 1;
			if($_POST['rules'][$i]){ $rule_set = true; }
			$to_add['rule_'.$plusone] = $_POST['rules'][$i];
			$i++;
		}
		if(!$rule_set){
			$error .= "<p>Please select at least one rule to add to your quest.</p>";
		}
		if(isset($_POST['xp']) && $_POST['xp'] != "" && is_numeric($_POST['xp'])){
			$to_add['xp'] = $_POST['xp'];
		} else {
			$error .= "<p>Error: Please enter a numerical value for XP</p>";
		}
		//TODO (1.2) Add iconpath 

		if($error == ""){
			$wpdb->insert( 
				$tablename, 
				$to_add, 
				array( 
					'%s', 
					'%s',
					'%s',
					'%s', 
					'%s',
					'%s',
					'%s', 
					'%s',
					'%s',
					'%s',
					'%s',
					'%d' 
				) 
			);
		}
	}
	//TODO (1.2) Delete quests
	$quest_query = mysql_query("SELECT * FROM $tablename");
	echo "<h2>Quests</h2>";
	if(mysql_num_rows($quest_query) > 0){
		echo "<table class='widefat'>
		<tr><th scope='col' class='column-name'>Quest name</th><th scope='col' class='column-name'>Quest Rules</th><th scope='col' class='column-name'>XP</th><th scope='col' class='column-name'>Path to icon</th></tr>";
		while ($row = mysql_fetch_array($quest_query)){
			echo "<tr><td>".$row['name']."</td><td>";
			
			$i = 1;
			while($i < 11){
				if($row['rule_'.$i] > 0){
					if($i > 1){ echo ", "; }
					$rule_id = $row['rule_'.$i];
					$rule_fetch = mysql_query("SELECT * FROM $rule_table WHERE id = $rule_id");
					while($rule = mysql_fetch_array($rule_fetch)){
						echo $rule['name'];
					}
				}
				$i++;
			}
			echo "</td><td>".$row['xp']."</td><td>";
			if($row['icon_path'] != 0) { echo $row['icon_path']; } else { echo "None"; }
			echo "</td><tr>";
		}
		echo "</table>";
	} else {
		echo "<p>No Quests so far. Why not add one now?</p>";
	}	
	

	//Quest form
	?>
	<h3>Add New Quest</h3>
	<?php if($error != ""){ ?>
	<div class="error errortext"><?php echo $error; ?></div>
	<?php } ?>
	<form id="quest-add" method="post" action="">
	<table class="form-table">

			<tr>
				<th><label for="noq">Name of Quest</label></th>

				<td>
					<input type="text" name="noq" id="noq" value="" class="regular-text" /><br />
					<span class="description">Please enter a name for your quest</span>
				</td>
			</tr>
	        <tr>
				<th><label for="rules">Rules</label></th>

				<td>
						<?php 
						$rule_query = mysql_query("SELECT * FROM $rule_table");
								if(mysql_num_rows($rule_query) > 0){
									while ($row = mysql_fetch_array($rule_query)){
										echo "<label><input type='checkbox' name='rules[]' value='".$row['id']."'> ".$row['name']."</label><br />";
									}
								} else {
									echo "<p>No rules to add. Visit the <a href='".get_bloginfo('url')."/wp-admin/admin.php?page=wp-quest'>Rules</a> page to add some now.</p>";
								}
							?>
					<br />
					<span class="description">Please select the rules that need to be fulfilled to complete this quest</span>
				</td>
			</tr>
			 <tr>
				<th><label for="xp">XP</label></th>

				<td>
						<input type="text" name="xp" id="xp" value="" class="regular-text" /><br />
					<span class="description">Please enter the amount of XP associated with completing this quest.</span>
				</td>
			</tr>
		</table>
		<p><input type="submit" value="Save Quest" class="button-primary" id="submit" name="submit"></p>
	</form>
	<?php

}

function wp_quest_levels(){
	global $wpdb;
	//List of levels
	$tablename = $wpdb->prefix.'wp_quest_levels';
	$error = "";

	//Posted? let's add it!
	if($_POST['submit']){
		//VALIDATE FOR
			//Fields empty
			//Strings less than 100
			//Numeric num
		$to_add = array();
		if(isset($_POST['nol']) && $_POST['nol'] != "" && strlen($_POST['nol'] < 101)){
			$to_add['name'] = $_POST['nol'];
		} else {
			$error .= "<p>Please enter a level name (Less than 100 characters)</p>";
		}
		if(isset($_POST['xp']) && $_POST['xp'] != "" && is_numeric($_POST['xp'])){
			$to_add['xp_req'] = $_POST['xp'];
		} else {
			$error .= "<p>Error: Please enter a numerical value for XP</p>";
		}
		//TODO (1.2) Add iconpath 

		if($error == ""){
			$wpdb->insert( 
				$tablename, 
				$to_add, 
				array( 
					'%s', 
					'%d' 
				) 
			);
		}
	}
	$level_query = mysql_query("SELECT * FROM $tablename");
	echo "<h2>Levels</h2>";
	if(mysql_num_rows($level_query) > 0){
		echo "<table class='widefat'>
		<tr><th scope='col' class='column-name'>Level name</th><th scope='col' class='column-name'>XP Requirement</th><th scope='col' class='column-name'>Path to icon</th></tr>";
		while ($row = mysql_fetch_array($level_query)){
			echo "<tr><td>".$row['name']."</td><td>".$row['xp_req']."</td><td>".$row['icon_path']."</td><tr>";
		}
		echo "</table>";
	} else {
		echo "<p>No levels so far. Why not add one now?</p>";
	}


	//Levels form
		?>
	<h3>Add New Level</h3>
	<?php if($error != ""){ ?>
	<div class="error errortext"><?php echo $error; ?></div>
	<?php } ?>
	<form id="level-add" method="post" action="">
	<table class="form-table">

			<tr>
				<th><label for="nol">Name of Level</label></th>

				<td>
					<input type="text" name="nol" id="nol" value="" class="regular-text" /><br />
					<span class="description">Please enter a name for your level</span>
				</td>
			</tr>
			 <tr>
				<th><label for="xp">XP</label></th>

				<td>
						<input type="text" name="xp" id="xp" value="" class="regular-text" /><br />
					<span class="description">Please enter the amount of XP required to unlock this level.</span>
				</td>
			</tr>
		</table>
		<p><input type="submit" value="Save Level" class="button-primary" id="submit" name="submit"></p>
	</form>
	<?php

}

function wp_quest_settings(){
	//Overall settings
		//Display - Which elements to display on full
		//TODO: JS pop up - Position, colour
	?>
	<h2>Settings</h2>
	<h3>Display Settings</h3>
	<form id="display-settings" method="post" action="options.php">
	<?php settings_fields( 'wpq-display-group' ); 
	?>
	<table class="form-table">

	        <tr>
				<th>Elements to display</th>

				<td>
					<label><input type="checkbox" value="1" name="level" <?php if(get_option('level') > 0) { echo "checked"; } ?>> User Level</label><br />
					<label><input type="checkbox" value="1" name="xp" <?php if(get_option('xp') > 0) { echo "checked"; } ?>> XP </label><br />
					<label><input type="checkbox" value="1" name="distance" <?php if(get_option('distance') > 0) { echo "checked"; } ?>> Distance to next level </label><br />
					<label><input type="checkbox" value="1" name="quest" <?php if(get_option('quest') > 0) { echo "checked"; } ?>> List of Quests</label><br />
					<label><input type="checkbox" value="1" name="recent-quest" <?php if(get_option('recent-quest') > 0) { echo "checked"; } ?>> Recent Quests</label><br />
					<label><input type="checkbox" value="1" name="last-quest" <?php if(get_option('last-quest') > 0) { echo "checked"; } ?>> Last Quest Completed</label><br />
					<br />
					<span class="description">Please select the elements you wish to be displayed for a user - PLEASE NOTE: These are default options which can be overwritten.</span>
				</td>
			</tr>
			 <tr>
		</table>
		<p><input type="submit" value="Save Display Settings" class="button-primary" id="submit" name="submit"></p>
	</form>
	<!-- TODO: (1.1)
	<h3>Notification Settings</h3>
	<form id="popup-settings" method="post"  action="options.php">
	<?php settings_fields( 'wpq-notification-group' ); ?>
	<table class="form-table">

			<tr>
				<th><label for="position">Position</label></th>

				<td>
					<select name="position" id="position">
						<option value="0" <?php if(get_option('position') == 0){ echo "Selected"; } ?>>Do not display notifications</option>
						<option value="1" <?php if(get_option('position') == 1){ echo "Selected"; } ?>>Top Left</option>
						<option value="2" <?php if(get_option('position') == 2){ echo "Selected"; } ?>>Top Centre</option>
						<option value="3" <?php if(get_option('position') == 3){ echo "Selected"; } ?>>Top Right</option>

						<option value="4" <?php if(get_option('position') == 4){ echo "Selected"; } ?>>Middle Left</option>
						<option value="5" <?php if(get_option('position') == 5){ echo "Selected"; } ?>>Middle Centre</option>
						<option value="6" <?php if(get_option('position') == 6){ echo "Selected"; } ?>>Middle Right</option>

						<option value="7" <?php if(get_option('position') == 7){ echo "Selected"; } ?>>Bottom Left</option>
						<option value="8" <?php if(get_option('position') == 8){ echo "Selected"; } ?>>Bottom Centre</option>
						<option value="9" <?php if(get_option('position') == 9){ echo "Selected"; } ?>>Bottom Right</option>
					</select><br />
					<span class="description">Please choose a position for your notification pop ups.</span>
				</td>
			</tr>
	        <tr>
				<th><label for="dfq">Default text for Quest Completion</label></th>

				<td>
					<input type="text" name="dfq" id="dfq" value="<?php if(get_option('dfq') != ""){ echo get_option('dfq'); } else { echo "Quest Complete: "; } ?>" class="regular-text" /><br />
					<span class="description">The text that will be displayed immediately before the Quest name when a User completes one.</span>
				</td>
			</tr>
			<tr>
				<th><label for="dfl">Default text for Level Up</label></th>

				<td>
					<input type="text" name="dfl" id="dfl" value="<?php if(get_option('dfl') != ""){ echo get_option('dfl'); } else { echo "Level Up! You are now a "; } ?>" class="regular-text" /><br />
					<span class="description">The text that will be displayed immediately before the level name when a User levels up.</span>
				</td>
			</tr>
		</table>
		<p><input type="submit" value="Save Quest" class="button-primary" id="submit" name="submit"></p>
	</form> -->
	<?php
}

function wp_quest_analytics(){
	//TODO (1.2)
	//Analytics
		//List of Quests, with stats for them
	echo '<h2>Quest Analytics</h2><p>Quest Analytics are coming soon, rest assured your data is being saved!</p>';

}

//Add stylesheet for WordPress Quest
add_action( 'wp_enqueue_scripts', 'prefix_add_wp_quest_stylesheet' );
function prefix_add_wp_quest_stylesheet() {
	wp_register_style( 'wp_quest-style', plugins_url('wp-quest-styles.css', __FILE__) );
    wp_enqueue_style( 'wp_quest-style' );
}

//Quest Display (Widget)
function wordpress_quest_display($options = array(), $user = "current", $fallback = ""){
	global $current_user;
	global $wpdb;
	
    get_currentuserinfo();
	//if(current) this user, if not, use the ID provided.
	if($user == "current"){
		if(!is_user_logged_in()) {
         	//no user logged in
         	$userID = false;
      	} else {
			$userID = $current_user->ID;
		}
	} else {
		$userID = $user;
	}

	if ($userID) { 
		//TODO
		//Fetch database rows related to this User ID
		$user_quest_tablename = $wpdb->prefix.'wp_quest_user_quests';
		$user_level_tablename = $wpdb->prefix.'wp_quest_user_levels';
		$quest_tablename = $wpdb->prefix.'wp_quest_quests';
		$level_tablename = $wpdb->prefix.'wp_quest_levels';
		//TODO: Make sure this are fetching MOST RECENT FIRST
		$user_quest_data = $wpdb->get_results("SELECT * FROM $user_quest_tablename WHERE user = $userID ORDER BY time_completed DESC", ARRAY_A);
		$user_level_data = $wpdb->get_row("SELECT * FROM $user_level_tablename WHERE user = $userID");
		$current_level_id = $user_level_data->level;
		$user_current_level = $wpdb->get_row("SELECT * FROM $level_tablename WHERE id = $current_level_id");
		$current_xp = $user_level_data->current_xp;
		//TODO: Ensure this is fetching LOWEST XP REQ FIRST
		$user_next_level = $wpdb->get_row("SELECT * FROM $level_tablename WHERE xp_req > $current_xp ORDER BY xp_req ASC");
		$distance = $user_next_level->xp_req - $current_xp;
		
		//TODO
		//Get default options
		$defaults = array(
		'user_level' => get_option('level'),
		'xp' => get_option('xp'),
		'distance' => get_option('distance'),
		'quest' => get_option('quest'),
		'recent-quest' => get_option('recent-quest'),
	 	'last-quest' => get_option('last-quest')
		);

		//Merge in any passed options
		$to_show = array_merge($defaults, $options);

		//Display the widget!
		//TODO: Fallbacks for what happens if they are logged in but have nothing so far.
		echo $before_widget;
			echo '<div class="wordpress-quest">';
				echo $before_title;
					echo '<h3 class="widget-title">'.$current_user->user_login.'</h3>';
				echo $after_title;
				if($to_show['user_level']){
					echo '<div class="wp_quest_current_user_level"><span class="wp_quest_display_section_title">Level: </span><span class="wp_quest_current_user_level_val">';
					echo $user_current_level->name;
					echo '</span></div>';
				}
				if($to_show['xp']){
					echo '<div class="wp_quest_current_user_xp"><span class="wp_quest_display_section_title">Current XP: </span><span class="wp_quest_current_user_xp_val">'.$current_xp.'</span></div>';
				}
				if($to_show['distance']){
					echo '<div class="wp_quest_current_user_distance"><span class="wp_quest_display_section_title">Distance to Next Level: </span><span class="wp_quest_current_user_distance_val">';
					if($distance > 0){
						echo $distance;
					} else {
						echo "0";
					}
					echo '</span></div>';
				}
				if($to_show['quest']){
					echo '<div class="wp_quest_current_user_all_quests"><span class="wp_quest_display_section_title">All Completed Quests: </span>';
						$i = 1;
						foreach($user_quest_data as $uqd){
							if($i != 1){ echo "<span class='wp_quest_level_list_comma'>, </span>"; }
							$this_quest_id = $uqd['quest'];
							$this_quest = $wpdb->get_row("SELECT * FROM $quest_tablename WHERE id = $this_quest_id");
							echo '<span class="wp_quest_current_user_individual_quest">'.$this_quest->name.'</span>';
							$i++;
						}
					echo '</div>';
				}
				if($to_show['recent-quest']){
					//TODO: 1.1 Add option to modify this number, perhaps remove last-quest and quest all together and just pass a number for how many quests to show (Could be set to 999 or 1 or anything inbetween)
					echo '<div class="wp_quest_current_user_recent_quests"><span class="wp_quest_display_section_title">Recent Quests: </span>';
						$i = 1;
						foreach($user_quest_data as $uqd){
							if($i > 5){ break; }
							if($i != 1){ echo "<span class='wp_quest_level_list_comma'>, </span>"; }
							$this_quest_id = $uqd['quest'];
							$this_quest = $wpdb->get_row("SELECT * FROM $quest_tablename WHERE id = $this_quest_id");
							echo '<span class="wp_quest_current_user_individual_quest">'.$this_quest->name.'</span>';
							$i++;

						}
					echo '</div>';
				}
				if($to_show['last-quest']){
					echo '<div class="wp_quest_current_user_last_quests"><span class="wp_quest_display_section_title">Last Completed Quest: </span>';
						$last_quest = $user_quest_data[0];
						$this_quest_id = $last_quest['quest'];
						$this_quest = $wpdb->get_row("SELECT * FROM $quest_tablename WHERE id = $this_quest_id");
						echo '<span class="wp_quest_current_user_individual_quest">'.$this_quest->name.'</span>';
					echo '</div>';
				}
			echo '</div>';
		echo $after_widget;
	} else {   
		echo $fallback;
	}

}
wp_register_sidebar_widget('wp_quest_display', 'WordPress Quest Display', 'wordpress_quest_display', array('description' => "Displays Quest status to the current user."));
//TODO (1.1): Make better widget controls where the user can set this widget to behave differently to default if they want to
register_widget_control('wp_quest_display', function(){ echo 'This widget uses the options set in <a href="'.get_bloginfo('url').'/wp-admin/admin.php?page=wp-quest-settings">WordPress Quest Settings</a>';});

function add_wp_quest_javascript(){
    wp_register_script( 'wp_quest_scripts', 'wp_quest_scripts.js');
    wp_enqueue_script( 'wp_quest_scripts' );
}


//Add our JS
function queue_wp_quest_script() {
	wp_enqueue_script('wp_quest-funct', plugins_url('wp-quest-functions.js', __FILE__), array('jquery') );
}    
add_action('wp_enqueue_scripts', 'queue_wp_quest_script');

/* TODO: (1.1) Quest Types 
function wp_quest_checkers_login(){
	wp_quest_checkers("login");
}
function wp_quest_checkers_comment(){
	wp_quest_checkers("comment");
} */
function wp_quest_checkers_visit(){
	wp_quest_checkers("visit");
}
function wp_quest_checkers($type = "visit"){

	global $post;
	global $current_user;
	global $wpdb;
	get_currentuserinfo();

	$user_rules_tablename = $wpdb->prefix.'wp_quest_user_rules';
	$rules_tablename = $wpdb->prefix.'wp_quest_rules';
	$user_quest_tablename = $wpdb->prefix.'wp_quest_user_quests';
	$quest_tablename = $wpdb->prefix.'wp_quest_quests';
	$user_level_tablename = $wpdb->prefix.'wp_quest_user_levels';
	$level_tablename = $wpdb->prefix.'wp_quest_levels';

	$xp_increase = false;
	$xp_val = 0;

	//Is this person even logged in? If not, do nothing.
	if(is_user_logged_in()) {
		//Check for rule completion
			//Get current page and user
			$current_page = $post->ID;
			$userID = $current_user->ID;
			//Get user rules (Not Partials)
			$completed_rules = "SELECT * FROM $user_rules_tablename WHERE user = $userID AND partial = 0";

			//Get rules (exclude ones we've got) for this page (or any) to check against
			$rule_fetch = $wpdb->get_results("SELECT * FROM $rules_tablename WHERE (page = 0 OR page = $current_page) AND id NOT IN ('$completed_rules') AND type = \"$type\"", ARRAY_A);

			//Update user rules
				//Foreach rule			
				foreach($rule_fetch as $rf){
					//If num > 0, look for it in users
					$rule = $rf['id'];
					if($rf['num'] > 0){
						$partial_rules = $wpdb->get_results("SELECT * FROM $user_rules_tablename WHERE user = $userID AND partial = '1' AND rule = $rule", ARRAY_A);	
						if(count($partial_rules) > 0){
							//Does exist? +1 to partial
							$this_user_rule = $partial_rules[0];
							$new_partial_num = $this_user_rule['partial_num'] + 1;

							//If this rule's new partial num = the requirement, remove partial
							if($rf['num'] == $new_partial_num){
								$wpdb->update( 
									$user_rules_tablename, 
									array( 
										'partial' => '0',	// integer
									), 
									array( 'rule' => $rule, 'user' => $userID ), 
									array( 
										'%d',	// value1
									), 
									array( '%d' ) 
								);
							} else {
								//Update this rule with + 1
								$wpdb->update( 
									$user_rules_tablename, 
									array( 
										'partial_num' => $new_partial_num	// integer (number) 
									), 
									array( 'rule' => $rule, 'user' => $userID ), 
									array( 
										'%d'	// value
									), 
									array( '%d' ) 
								);
							}
						} else {
							//Doesnt exist yet? add it as a partial
							$rule_check = $wpdb->get_results("SELECT * FROM $user_rules_tablename WHERE user = $userID AND rule = $rule", ARRAY_A);	
							if(count($rule_check) == 0){
								$wpdb->insert( 
									$user_rules_tablename, 
									array( 
										'user' => $userID, 
										'rule' => $rf['id'],
										'partial' => '1',
										'partial_num' => '1'
									), 
									array( 
										'%d', 
										'%d',
										'%d',
										'%d'
									) 
								);
							}
						}
						

					} else {
						//If num = 0, add it to user rules
						$rule_check = $wpdb->get_results("SELECT * FROM $user_rules_tablename WHERE user = $userID AND rule = $rule", ARRAY_A);	
						if(count($rule_check) == 0){
							$wpdb->insert( 
								$user_rules_tablename, 
								array( 
									'user' => $userID, 
									'rule' => $rf['id'],
								), 
								array( 
									'%d', 
									'%d' 
								) 
							);
						}
					}
				}

		//Check for quest completion
			//Fetch user rules again and check against quests, see if any are complete.
			$completed_quests = "SELECT * FROM $user_quest_tablename WHERE user = $userID";
			$quest_fetch = $wpdb->get_results("SELECT * FROM $quest_tablename WHERE id NOT IN ('$completed_quests')", ARRAY_A);

			foreach($quest_fetch as $qf){
				//Loop through rules
				$i = 1;
				$rules_completed = array();
				//TODO: Make sure we haven't got this Quest already before doing all the below. Atm is adding lots of XP
				while($i < 11){
					//echo $qf['name']." - ";
					if($qf['rule_'.$i] > 0){
						//echo "rule ".$i." worth verifying - ";
						//Check if User has completed this rule
						$this_rule = $qf['rule_'.$i];
						$rule_lookup = $wpdb->get_results("SELECT * FROM $user_rules_tablename WHERE (rule = $this_rule) AND (user = $userID)", ARRAY_A);
						if(count($rule_lookup) > 0){
							//echo "rule completed";
							$rules_completed[] = $i;
						}
					} else {
						//echo "rule ".$i." not worth checking";
						$rules_completed[] = $i;
					}
					$i++;
					//echo "<br />";
				}


				if(count($rules_completed) == 10){
					$quest = $qf['id'];
					//Update user quests with this quest
					$quest_check = $wpdb->get_results("SELECT * FROM $user_quest_tablename WHERE (user = $userID) AND (quest = $quest)", ARRAY_A);	
					if(count($quest_check) == 0){
						$wpdb->insert( 
							$user_quest_tablename, 
							array( 
								'user' => $userID, 
								'quest' => $quest,
							), 
							array( 
								'%d', 
								'%d' 
							) 
						);
					
						//If XP is greater than zero, update user XP in Levels too
						if($qf['xp'] > 0){
							$xp_increase = true;
							//Does user_level exist? If so, update
							$user_level_lookup = $wpdb->get_results("SELECT * FROM $user_level_tablename WHERE user = $userID", ARRAY_A);
							if(count($user_level_lookup) > 0){
								$user_level = $user_level_lookup[0];
								$new_xp = $user_level['current_xp'] + $qf['xp'];
								$xp_val = $new_xp;
								$wpdb->update( 
									$user_level_tablename, 
									array( 
										'current_xp' => $new_xp	// integer (number) 
									), 
									array( 'user' => $userID ), 
									array( 
										'%d'	// value
									), 
									array( '%d' ) 
								);
							} else {
								$xp_val = $qf['xp'];
								//If not, add user with current XP
								$wpdb->insert( 
									$user_level_tablename, 
									array( 
										'user' => $userID, 
										'current_xp' => $qf['xp'],
									), 
									array( 
										'%d', 
										'%d' 
									) 
								);
							}
						}
					}
				}
			}	
			
			
		//Check for level completion
		if($xp_increase){
			//Check current user XP against next level XP (If XP has increased)
			$user_level_lookup = $wpdb->get_results("SELECT * FROM $user_level_tablename WHERE user = $userID", ARRAY_A);
			if(count($user_level_lookup) > 0){
				$user_level = $user_level_lookup[0];
				$current_level = $user_level['level'];
				$xp_val = $user_level['current_xp'];
				//TODO: Ensure this is fetching HIGHEST XP REQ FIRST
				$level_user_should_be = $wpdb->get_row("SELECT * FROM $level_tablename WHERE xp_req < $xp_val ORDER BY xp_req DESC");
				if($current_level != $level_user_should_be->id){
					//Update user_level for this user (Dont add new)
					$wpdb->update( 
						$user_level_tablename, 
						array( 
							'level' => $level_user_should_be->id	// integer (number) 
						), 
						array( 'user' => $userID ), 
						array( 
							'%d'	// value
						), 
						array( '%d' ) 
					);
				}
			}
			
			
		}
	} else {
		//Not logged in, do nothing for now
		//TODO: (One day) Set a popup letting non-logged in people know what they could have won?
	}

}
//TODO: (1.1) Quest type: add_action('wp_login', 'wp_quest_checkers_login');
//TODO: (1.1) Quest type: add_action('comment_post', 'wp_quest_checkers_comment');
add_action('wp_head', 'wp_quest_checkers_visit');

?>
