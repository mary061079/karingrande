<?php
class YoutubeChannels {

	public static $instance;

	function __construct() {
		add_action( 'admin_head', array( __CLASS__, 'admin_init' ) );
	}

	public static function getInstance(){
		if( !isset(self::$instance)){
			self::$instance = new YoutubeChannels();
		}
		return self::$instance;
	}

	static function admin_init() {
		wp_enqueue_style( 'youtube', get_template_directory_uri() . '/inc/youtube/style.css' );
	}

	function get_hint_channels() {
		return array(
			'Cameron Dallas' => 'UCUKxWDTKylMaLjbNe3pOpCg',
			'Nash Grier' => 'UCRiMBJf62RzwEHK28bUf4vg',
			'Ariana Grande' => 'UC0VOyT2OCBKdQhF3BAbZ-1g',
			'Shawn Mendes' => 'UCAvCL8hyXjSUHKEGuUPr1BA',
			'Tilishious' => 'UCoad3Pn4PDJEZymwD6nmq9g',
			'Henk Babois' => 'UCWztiOCRDjpnZGtaWWUENUg',
			'Alec Trael' => 'UCliEz4Nz0ro3vUrtcTVbozg',
		);
	}

	function get_hint_channels_html() {
		$channels = $this->get_hint_channels();

		$html = '<table class="channels">
		<tr>
			<th><b>' . __( 'Name' ). '</th>
			<th>' . __( 'Channel' ). '</th>
		</tr>';
		foreach ( $channels as $name => $channel ) {
			$html .= '<tr>
				<td>' . $name . '</td>
				<td>' . $channel . '</td>
			</tr>';
		}
		return $html . '</table>';
	}

	static function theme_options() {
		$nash = get_option( 'nash_grier_channel' );
		$api_key = get_option( 'youtube_api_key' );?>
		<div class="wrap">
			<h2><?php _e( 'Theme Options', 'karingrande' ) ?></h2>
			<?php if ( isset( $_GET['no_new'] ) ) {
				echo '<p class="error">' . __( 'No new videos uploaded for ' . $_GET['no_new'], 'karingrande' ) . '</p>';
			}?>
			<h3><?php _e( 'Youtube Channels IDs', 'karingrande' ) ?></h3>
			<?php echo YoutubeChannels::getInstance()->get_hint_channels_html();	?>
			<form action="<?php echo admin_url( '/admin.php?page=theme-options&no_new' ) ?>" method="post">
				<fieldset>
					<h3><?php _e( 'Upload from youtube channel', 'karingrande' ) ?></h3>
					<label>
						<?php _e( 'Youtube API KEY', 'karingrande' ) ?>
						<input type="text" name="youtube_api_key" value="<?php echo $api_key ?>" />
					</label><br />
					<ul class="youtube_settings">
						<li>
							<label>
								<?php _e( 'Insert Channel ID', 'karingrande' ) ?><br />
								<input type="text" name="channel_id" value="<?php echo $nash ?>" />
							</label>
						</li>
						<li>
							<label>
								<?php _e( 'Select Category for New Videos', 'karingrande' ) ?><br />
								<select name="category">
									<option><?php _e( 'Select category', 'karingrande' )?></option>
									<?php
									$categories = get_categories( 'hide_empty=0' );
									foreach( $categories as $category ) {?>
										<option value="<?php echo $category->term_id?>"><?php echo $category->name ?></option>
									<?php } ?>
								</select>
							</label>
						</li>
					</ul>
				</fieldset>
				<input type="submit" value="<?php _e( 'Upload Videos', 'karingrande' ) ?>" name="upload_videos"/>
			</form>
		</div>
	<? }


	static function process_youtube_videos() {
		if ( isset ( $_POST['youtube_api_key'] ) && !empty( $_POST['youtube_api_key'] ) ) {
			update_option( 'youtube_api_key', $_POST['youtube_api_key'] );
		}
		/** uploading videos */
		if ( isset ( $_POST['channel_id'] ) && !empty( $_POST['channel_id'] ) && !empty( $_POST['category'] ) ) {
			$gapi_url = 'https://www.googleapis.com/youtube/v3/';
			$channel_id = $_POST['channel_id'];
			$youtube_api_key = get_option( 'youtube_api_key' );
			if ( !empty( $cnannel_id ) && !empty( $youtube_api_key ) ) {
				$url = $gapi_url . 'channels?part=contentDetails&id=' . $channel_id . '&key=' . $youtube_api_key;
				$channel_info = wp_remote_get( $url );
				if ( is_wp_error( $channel_info ) ) {
					echo 'Channel query error: ' . $channel_info->get_error_message();
					return;
				}
				if ( empty ( $channel_info['body'] ) ) {
					echo 'No channel info returned';
					return;
				}
				$channel = json_decode( $channel_info['body'] );
				$playlistID = $channel->items[0]->contentDetails->relatedPlaylists->uploads;
				$vurl = $gapi_url . 'playlistItems?part=snippet&maxResults=10&playlistId=' . $playlistID . '&key=' . $youtube_api_key;
				$videos = wp_remote_get( $vurl );

				if ( is_wp_error( $videos ) ) {
					echo 'Videos query error: ' . $videos->get_error_message();
					return;
				}
				if ( empty ( $videos['body'] ) ) {
					echo 'No videos returned';
					return;
				}
				$videos = json_decode( $videos['body'] );
				//category 6
				$last_videos = get_option( 'videos_from_' . $channel_id );
				$i = 0;
				$saved_videos = array();
				foreach( $videos->items as $video ) {
					if ( in_array( $video->snippet->resourceId->videoId, $last_videos ) ) {
						continue;
					}
					/**we don't want to upload videos once more */

					$youtube_link = 'https://www.youtube.com/watch?v=' . $video->snippet->resourceId->videoId;
					$args = array(
						'post_title' => $video->snippet->title,
						'post_content' => $youtube_link,
						'post_name' => sanitize_title( $video->snippet->title ),
						'post_status' => 'publish',
						'post_category' => array( 6 )
					);
					$post_id = wp_insert_post( $args );
					update_post_meta( $post_id, '_yoast_wpseo_focuskw', $video->snippet->title );
					update_post_meta( $post_id, '_yoast_wpseo_title', $video->snippet->title );
					update_post_meta( $post_id, '_yoast_wpseo_metadesc', $video->snippet->title );
					$saved_videos[] = $video->snippet->resourceId->videoId;
					$i++;
				}

				update_option( 'videos_from_' . $channel_id, array_unique( array_merge( $videos ) ) );
				$channels = self::get_hint_channels();
				$whose_channel = array_search( $channel_id, $channels );
				if ( $i == 0 ) {
					wp_redirect( admin_url( '/admin.php?page=theme-options&no_new=' . $whose_channel ) );
				} else {
					wp_redirect( admin_url( '/admin.php?page=theme-options&updated=' . $whose_channel ) );
				}
				exit;
			}
		}
	}
}

new YoutubeChannels();