<?php
/*
 * INSTRUCTIONS LINK https://gist.github.com/mary061079/9b01f6836f325fc3fe68
 ###1. Go to API Console
1. Create project https://console.developers.google.com/
2. In the APIs & auth on the left sidebar go to APIs
   ![](http://content.screencast.com/users/mary0610/folders/Jing/media/92677e95-c727-46aa-8069-033b3f5548b9/2014-10-19_0701.png)
3. Enable there "YouTube Data API v3"
4. Go to "Credentials" on the left sidebar
5. Select "Public API Access", click "New Key"
6. In the popup select "Browser key"
![](http://content.screencast.com/users/mary0610/folders/Jing/media/f08b93db-81fb-4a71-b607-d817dd9a1ec7/2014-10-19_0706.png)
7. In the new popup leave the field "ACCEPT REQUESTS FROM THESE HTTP REFERERS (WEB SITES)" empty
![](http://content.screencast.com/users/mary0610/folders/Jing/media/44bfd3d8-3805-4ac9-969c-215468837fef/2014-10-19_0709.png)
8. Save your API Key

###2. Queries:
1. To get the videos from the channel, first go to:
https://www.googleapis.com/youtube/v3/channels?part=contentDetails&id=UCRiMBJf62RzwEHK28bUf4vg&key={API_KEY}
where `id` is the user youtube channel id. ( You can get it from the browser link of his channel.)

The response will be kind of:
```
{
 "kind": "youtube#channelListResponse",
 "etag": "\"PSjn-HSKiX6orvNhGZvglLI2lvk/B2ZwTUMiUQNuG0cl7GmdffxVHaY\"",
 "pageInfo": {
  "totalResults": 1,
  "resultsPerPage": 1
 },
 "items": [
  {
   "kind": "youtube#channel",
   "etag": "\"PSjn-HSKiX6orvNhGZvglLI2lvk/kZIYxGdC4gjmWrDGsJnDFWVs3k8\"",
   "id": "UCRiMBJf62RzwEHK28bUf4vg",
   "contentDetails": {
    "relatedPlaylists": {
     "likes": "LLRiMBJf62RzwEHK28bUf4vg",
     "uploads": "UURiMBJf62RzwEHK28bUf4vg"
    },
    "googlePlusUserId": "118389999310155560265"
   }
  }
 ]
}
```
2. Take `uploads` value and go to url https://www.googleapis.com/youtube/v3/playlistItems?part=snippet&maxResults=10&playlistId=UURiMBJf62RzwEHK28bUf4vg&key={YOUR_API_KEY}
where `playlistId` is `uploads` from the previous request

__Use explorer to test all this https://developers.google.com/apis-explorer/#p/youtube/v3/__
 */
require_once( dirname( __FILE__) . '/YoutubeChannels.php' );
add_action( 'admin_menu', function() {
    add_menu_page(
	    'Theme Options',
	    'Theme Options',
	    'administrator',
	    'theme-options',
	    array( 'YoutubeChannels', 'theme_options' ),
	    '', 120
    );
});
add_action('admin_init', function() {
    if( $_GET['page'] == 'theme-options' &&
            ( isset ( $_POST['save_channels'] ) || isset ( $_POST['upload_videos_nash_grier'] ) ) ) {
	    YoutubeChannels::process_youtube_videos();
    }
});