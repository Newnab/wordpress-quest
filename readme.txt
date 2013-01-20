=== WordPress Quest ===
Contributors: Newnab
Tags: gamification, quests, levels, rules, points, missions
Requires at least: 3.0.1
Tested up to: 3.5
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

WordPress Quest allows you to add 'Quests' to your site, granting users points and named achievements for certain interactions

== Description ==

WordPress Quest allows you to add 'Quests' to your site, granting users points and named achievements for certain interactions. Want to reward users for visiting a certain page of your site a number of times? It can be done! Hopefully the plugin will expand soon with more options for rules and quests.

== Installation ==

Installation is simple, but there is some configuration to be done to set up your Quests.

1. Upload the wordpress-quest folder to the `/wp-content/plugins/` directory OR install it through the Plugin -> Add New section of the Dashboard
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Set up your rules through the WP Quest dashboard pages - Rules are the basic building blocks of a Quest, a simple condition that must be met to complete
4. Set up your quests through the WP Quest dashboard pages - Quests are essentially a grouping of rules, a collection of all the conditions which must be met to trigger victory.
5. Optionally, set up Levels - If you assign XP values to your Quests you can also set Levels to be awarded based on amount of XP collected by a user.
6. Ensure your WP Quest Settings are set to display the elements you wish to be present (Though this can be overwritten if necessary, see FAQ)
7. Place `<?php wordpress_quest_display(); ?>` in your templates, or use the Widget to display current user quest information

== Frequently Asked Questions ==

= Can I display user Quest details not matching the default? =

Yes! `<?php wordpress_quest_display() ?>` accepts an array of options which overrule the default settings in the dashboard.

The options are as follows:

		`<?php 
		$options = array(
		'user_level' => true //Whether to display user's current level
		'xp' => true, //Whether to display user's current XP
		'distance' => true, //Whether to display distance (in XP) to users next level
		'quest' => true, //Whether to show a list of all completed quests for this user
		'recent-quest' => true, //Whether to show a list of recently completed quests for this user
	 	'last-quest' => true //Whether to show the most recently completed quest for this user
		); ?>`

= Can I display the level/xp/distance/quests of someone who is not the current user? =

Yes, `<?php wordpress_quest_display(); ?>` accepts a second parameter - ID of desired user. If none is given it will automatically try to determine the current user ID and use that.

There is also a third option - A fallback for what you want to display if there is no currently logged in user and no ID has been passed to the function.

The fallback parameter defaults to nothing.

So, in full, your call to the function might look like:
	`<?php wordpress_quest_display($options, $userID, 'No user currently logged in.'); ?>`


== Changelog ==

= 1.0 =
* First Version


