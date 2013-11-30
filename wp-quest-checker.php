<?php
define('WP_USE_THEMES', false);
require('../../../wp-blog-header.php');
//AJAX page comes here to gather info from database/update database

//Un-notified Quest Completions
	//GET ALL UN-NOTIFIED QUEST COMPLETIONS
	//Set to notified and echo info about them for JS return.
if(isset($_POST['user'])){
	global $wpdb;
	$userID = $_POST['user'];
	$user_quest_tablename = $wpdb->prefix.'wp_quest_user_quests';
	$quest_tablename = $wpdb->prefix.'wp_quest_quests';
	$user_level_tablename = $wpdb->prefix.'wp_quest_user_levels';
	$level_tablename = $wpdb->prefix.'wp_quest_levels';
	

	$unnotified_quests = $wpdb->get_results("SELECT * FROM $user_quest_tablename WHERE user = $userID AND notified = 0", ARRAY_A);

	$return = array();
	foreach($unnotified_quests as $unq){
		$quest = $unq['quest'];
		$this_user = $unq['user'];
		$this_quest = $wpdb->get_results("SELECT * FROM $quest_tablename WHERE id = $quest", ARRAY_A);
		array_push($return, $this_quest);

		//Uncomment this when JS popups are working, verify it updates and stops double notifications
		//Update them to notified.
		$wpdb->update( 
			$user_quest_tablename, 
			array( 
				'notified' => '1',	// integer
			), 
			array( 'quest' => $quest, 'user' => $this_user), 
			array( 
				'%d',	// value1
				'%d',	// value1
			), 
			array( '%d' ) 
		);
	}
	print json_encode($return);

}

//TODO: (1.2) Un-notified level completions
	//Set to notified and return