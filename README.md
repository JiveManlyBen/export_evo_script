export_evo_script
=================

This is a php script that generates an SQL script to import posts from the b2evolution platform to wordpress.

About
=====
When moving from the b2evolution platform to wordpress, there are few options for migrating the data.  RSS can be used, but requires configuration changes and does not pring along other data like permalinks and comments.

This script tries to get a more complete dataset by using SQL to import the posts from b2evoltion.  This script was written using b2evolution 4.0.3 and wordpress 3.5.2.

Backup all data before using this script.  This script comes with no warranty.  You are ultimately responsible for your own data.

Useage
======
The export_evo_script directory should be copied to the plugins directory of the b2evolution install.  The script must then be executed on the command line by including user IDs in both systems.

If the user ID in b2evolution was 4 and the same user in wordpress was 6, the command would be "php export_evo_script.php 4 6".  The script then exports a file named "wp_user_6_posts.sql".  That SQL script should be run on the wordpress database to import the b2evolution posts for that user.
