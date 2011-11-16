<?php if (!defined('APPLICATION')) exit();

// Define the plugin:
$PluginInfo['RssFeed'] = array(
   'Description' => 'This simple plugin for new discussions and discussions updates',
   'Version' => '1.0',
   'RequiredApplications' => FALSE,
   'RequiredTheme' => FALSE, 
   'RequiredPlugins' => FALSE,
   'HasLocale' => TRUE,
   'RegisterPermissions' => FALSE,
   'Author' => 'xrado',
   'AuthorEmail' => 'radovan.lozej@gmail.com',
   'AuthorUrl' => 'http://www.xrado.si'
);

class RssFeed implements Gdn_IPlugin {

	/// Methods ///
	public function DiscussionsController_Rss_Create($Sender) {

		$db = Gdn::Database();
		$data = $db->SQL()->Query('
			(
				SELECT 
				GDN_Discussion.DiscussionID,
				0 as CommentID,
				GDN_Discussion.Name,
				GDN_Discussion.Body,
				GDN_Discussion. DateInserted,
				GDN_User.Name as User
				FROM GDN_Discussion
				LEFT JOIN GDN_User ON (GDN_Discussion.InsertUserID=GDN_User.UserID)
				ORDER BY DateInserted DESC
				LIMIT 20
			)
			UNION ALL
			(
				SELECT
				GDN_Comment.DiscussionID,
				GDN_Comment.CommentID,
				GDN_Discussion.Name,
				GDN_Comment.Body,
				GDN_Comment.DateInserted,
				GDN_User.Name as User
				FROM `GDN_Comment`
				LEFT JOIN GDN_Discussion ON (GDN_Comment.DiscussionID=GDN_Discussion.DiscussionID)
				LEFT JOIN GDN_User ON (GDN_Comment.InsertUserID=GDN_User.UserID)
				ORDER BY GDN_Comment.DateInserted DESC
				LIMIT 30
			)
			ORDER BY DateInserted DESC
			LIMIT 40
		');
		
		header("Content-Type: application/xml; charset=utf-8"); 
		
		echo '<?xml version="1.0" encoding="UTF-8"?>
			<rss xmlns:content="http://purl.org/rss/1.0/modules/content/" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:atom="http://www.w3.org/2005/Atom" version="2.0">
				<channel>
					<link>'.Url('discussions', TRUE).'</link>
					<title>All Discussions - Kohana Forums</title>
					<description>All Discussions - Kohana Forums</description>
					<language>en-CA</language>
					<generator>KohanaPHP</generator>
					<atom:link href="'.Url('discussions/rss', TRUE).'" rel="self" type="application/rss+xml" />
					';
					
		foreach($data as $d)
		{
			echo '<item>
				<dc:creator>'.$d->User.'</dc:creator>
				<title>'.$d->Name.'</title>
				<link>'.Url('discussion/'.$d->DiscussionID.($d->CommentID ? '/#Item_'.$d->CommentID : ''), TRUE).'</link>
				<guid>'.Url('discussion/'.$d->DiscussionID.($d->CommentID ? '/#Item_'.$d->CommentID : ''), TRUE).'</guid>
				<description><![CDATA['.$d->Body.']]></description>
				<pubDate>'.date('D, d M Y H:i:s O',strtotime($d->DateInserted)).'</pubDate>
			</item>';
		}
		
		echo '</channel></rss>';
		exit;
	}
   
   public function Setup() {
      // No setup required
   }
}
