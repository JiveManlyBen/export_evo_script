<?php
	if((!is_numeric($_SERVER['argc']) || $_SERVER['argc'] == 0)) die( 'Please, do not access this page directly.' );

	require_once dirname(__FILE__).'/../../conf/_config.php';

	if (!isset($argv[2]) || !is_numeric($argv[1]) || !is_numeric($argv[2])) {
		die("The b2evo and wordpress user IDs must be supplied and numeric.\n");
	}
	
	$evo_user_id = $argv[1];
	$wp_user_id = $argv[2];

	function clean_varchar($string_content) {
		$string_content = str_replace('’', '&rsquo;', $string_content);
		$string_content = str_replace('“', '&ldquo;', $string_content);
		$string_content = str_replace('”', '&rdquo;', $string_content);
		$string_content = str_replace('\'', '\\\'', $string_content);
		return "'".$string_content."'";
	}

	function get_post_insert($wp_user_id, $post) {
		$post_sql = "INSERT INTO wp_posts (post_author, post_date, post_date_gmt, ".
			"post_content, post_title, post_excerpt, ".
			"post_status, comment_status, ping_status, post_password, post_name, ".
			"to_ping, pinged, post_modified, post_modified_gmt, ".
			"post_content_filtered, post_parent, guid, menu_order, ".
			"post_type, post_mime_type, comment_count) \n".
			"VALUES (". $wp_user_id .", '". $post['post_datestart'] ."', '". $post['post_datestart'] ."', ".
			clean_varchar($post['post_content']) .", ". clean_varchar($post['post_title']) .", ". clean_varchar($post['post_excerpt']) .", ".
			"'". $post['post_status'] ."', '". $post['post_comment_status'] ."', 'open', '', '".$post['post_urltitle'] ."', ".
			"'', '', '". $post['post_datemodified'] ."', '". $post['post_datemodified'] ."', ".
			"'', 0, '', 0, ".
			"'post', '', 0);\n\n";
		$post_sql .= "SET @wp_post_id = LAST_INSERT_ID();\n\n";
		$post_sql .= "UPDATE wp_posts SET guid = CONCAT(@wp_siteurl, '/?p=', @wp_post_id) WHERE ID = @wp_post_id;\n\n";
		return $post_sql;
	}

	try {
		$dbh = new PDO('mysql:dbname='.$db_config['name'].';host='.$db_config['host'], 
			$db_config['user'], 
			$db_config['password'], 
			array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));

                $sql = "SELECT ".
                        "post_id, post_datestart, post_content, ".
                        "post_title, post_excerpt, CASE WHEN post_status = 'published' THEN 'publish' ELSE 'draft' END AS post_status, ".
                        "post_comment_status, post_urltitle, post_datemodified  ".
                        "FROM evo_items__item ".
                        "WHERE post_creator_user_ID = :evo_user_id ".
			"ORDER BY post_datestart";

		$sth = $dbh->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
		$sth->execute(array(':evo_user_id' => $evo_user_id));
		$posts = $sth->fetchAll();
		$filename = dirname(__FILE__).'/wp_user_'.$wp_user_id.'_posts.sql';
		$f = @fopen($filename, 'w');
		fwrite($f, "SET @wp_siteurl = (SELECT option_value FROM wp_options WHERE option_name = 'siteurl' LIMIT 1);\n\n");
		foreach ($posts as $post) {
			fwrite($f, get_post_insert($wp_user_id, $post));
			print $post['post_id'] .": ". $post['post_datestart'] ." - ". $post['post_title'] ."\n";
		} 
		print "\n" . sizeof($posts) . " posts for b2evo user " . $evo_user_id . " have been written to " . $filename . "\n";
	} catch (PDOException $e) {
    		echo 'Database connection failed: ' . $e->getMessage();
	}
	$dbh = null;
	fclose($f);
?>
