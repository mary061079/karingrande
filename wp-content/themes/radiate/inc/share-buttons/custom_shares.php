<?php
//use  Auctollo\Extensions\Social_Buttons_v_0_1_0\Core as Social_Buttons_Core;

/**
 * Class that provides methods for creating the needed
 * table for the Social Share Buttons extension
 *
 */
class ATF_Custom_Share_Buttons {

	private $wpdb;
	public $services;

	public function __construct() {
		/** If something's not right bail out */
		global $wpdb;
		if ( empty( $wpdb ) ) {
			return;
		}

		$this->wpdb = $wpdb;

		/**
		 * Get implemented services and filter them so we query only the ones we
		 * need in the project
		 */
		$this->services = Core::$button_services;
		unset( $this->services['facebook-subscribe'] ); // does not make sense
		unset( $this->services['facebook-share'] ); // same as facebook
		unset( $this->services['gplus-badge'] ); // does not make sense

		/**
		 * Filters the array of social services slugs which the cron needs to
		 * query in order to retrieve the share counts.
		 *
		 * This is only necessary (and 99% of the cases MANDATORY) when using
		 * the custom share buttons and share counts. In order to minimize the
		 * load, you must filter the array of service slugs and only send the
		 * ones you're interested in getting values from. By default it queries
		 * all supported services.
		 *
		 * @param array $serivces The service slugs
		 *
		 * @return array The filtered $services
		 *
		 * @since 0.1.0
		 */
		$this->services = apply_filters( 'atf_custom_share_buttons_services', $this->services );
	}


	/**
	 * Method called from the file cron.php in the child theme
	 *
	 * There are 2 queues to process. The one which holds latest visited urls
	 * and another one which holds the latest added posts
	 *
	 * @param string $queue - queue to process
	 *
	 * @return object $this
	 */
	public function send_update_from_cron( $post_types = array(), $queue = 'next_to_query_queue', $ga = 'enabled' ) {
		/** If we process the latest visited posts */
		if ( $queue == 'next_to_query_queue' ) {
			/** Clear recently visited queue as we'll now create a new one from next_to_query_queue */
			$this->_clear_queue( 'recently_visited_queue' );

			$next_to_query_queue = $this->_get_queue( 'next_to_query_queue' );

			if ( empty( $next_to_query_queue ) ) {
				return;
			}

			apply_filters( 'before_queue_deleting', $post_types, $next_to_query_queue, $args = array(), $period_in_days = 30 );

			/** Only parse a limited number of links as services have request limitations */
			if ( count( $next_to_query_queue ) > 350 )
				$next_to_query_queue = array_slice( $next_to_query_queue, 0, 350 );

			$this->set_url_share_statistics( $next_to_query_queue );
			/** Clear the queue */
			$this->_clear_queue( 'next_to_query_queue' );
		} else {
			$ids_and_permalinks = $this->get_latest_permalinks_and_ids();
			$this->set_url_share_statistics( $ids_and_permalinks );
			apply_filters( 'before_queue_deleting', $post_types, $ids_and_permalinks, $args = array(), $period_in_days = 30 );
		}

		return $this;
	}


	/**
	 * Save social stats gathered from queried services in the local DB
	 *
	 * @uses do_action - Calls 'atf_update_social_share_count' on the data
	 *       inserted in the local DB.
	 *
	 * @param array $ids_and_permalinks - ids and urls to process.
	 *
	 * @return object $this
	 */
	public function set_url_share_statistics( $ids_and_permalinks = array() ) {
		/** If something went wrong bail out */
		if ( empty( $ids_and_permalinks ) ) {
			return;
		}

		$date = date( 'Y-m-d H:i:s' );

		// TODO - remove this after investigating why it doesnt crawl the homepage
		// maybe hooks are messing the queue
		if ( ! in_array( home_url(), $ids_and_permalinks ) )
			array_push( $ids_and_permalinks, home_url() );

		/** Get shares count for each url and update / insert data in local DB */
		foreach ( $ids_and_permalinks as $id => $url ) {
			foreach ( $this->services as $service_slug => $service_label ) {
				$shares_count = $this->get_url_share_statistics( $url, 0, $service_slug );

				$update_insert_query = $this->wpdb->prepare(
					"INSERT INTO {$this->wpdb->prefix}social_counts (entity_id, entity_url, likes_count, service, cron_updated)
					VALUES ('%d','%s', '%d', '%s', '%s')
					ON DUPLICATE KEY
					UPDATE likes_count = '%d', cron_updated = '%s'",
					$id, $url, $shares_count, $service_slug, $date, $shares_count, $date
				);

				$this->wpdb->query( $update_insert_query );
				/**
				 * Action that returns the inserted values.  This is used only
				 * if using the custom share buttons and share counts.
				 *
				 * After a successfull service query to get the number of
				 * shares for a specific url the database is updated. The values
				 * used to updat the database are returned in this hook.
				 *
				 * @param int    $id           Optional id of the entity that was updated
				 * @param string $url          The url the data was gathered for.
				 * @param int    $shares_count Number of shares
				 * @param string $service      Service slug that was queried
				 * @param string $date         Datetime at which the parsing took place
				 *
				 * @return mixed. The filtered args
				 *
				 * @since 0.1.0
				 */
				do_action( 'atf_update_social_share_count', $id, $url, $shares_count, $service_slug, $date );
			}

			$this->_set_queue( 'recently_visited_queue', $id, $url );
		}

		return $this;
	}

	/**
	 * Calls individual services and returns the share count for the given URL
	 *
	 * Method called by cron job. Should not be called on page load.
	 *
	 * @param  $entity_url - url that gets shared
	 * @param  $entity_id  - post_type ID, optional if entity_url is not missing
	 * @param  $services   - array with service slugs to be queried
	 *
	 * @return int
	 */
	public function get_url_share_statistics( $entity_url = '', $entity_id = 0, $service_slug = '' ) {
		/** No service to call -> throw notice */
		if ( empty( $service_slug ) ) {
			trigger_error( __( 'Method get_url_share_statistics called without specifying a sharing service.', $this->textdomain ) );
			return;
		}

		/** Get 'sanitized' args */
		$entity_data = $this->_get_entity_id_or_url( $entity_url, $entity_id );
		extract( $entity_data );

		/** Get statistics from each service */
		$service_share_count = Social_Buttons_Core::get_count( $service_slug, $entity_url );
		$service_share_count = ( ! is_numeric( $service_share_count ) ) ? 0 : $service_share_count;

		return $service_share_count;
	}


	/**
	 * Retrieves the share count for the specified service(s) from the local
	 * DB.
	 *
	 * @param $entity_url    -  url that gets shared
	 * @param $service_slugs - array with service slugs to be queried. 'all' option returns
	 *                       the sum of all service shares for the given url/id.
	 *
	 * @return array
	 */
	public function get_local_url_share_statistics( $entity_url = '', $service_slugs = 'all' ) {
		$results = array();
		/** Get sum of shares on all services for the given entity */
		if ( $service_slugs == 'all' ) {
			/** Get sum of all shares for the given url. Use limit number of share services to optimize the query */
			$q = "SELECT SUM(likes_count) AS likes_count FROM {$this->wpdb->prefix}social_counts WHERE entity_url LIKE '" . $entity_url . "' LIMIT " . count( $this->services );
		} else {
			/** Get sum of shares for each service for the given entity */
			$service_query = '';
			$service_slugs = is_string( $service_slugs ) ? explode( ',', $service_slugs ) : $service_slugs;

			// Initialize results array
			$results = array_combine(
				$service_slugs,
				array_fill( 0, count( $service_slugs ), 0 )
			);

			foreach ( $service_slugs as $slug ) {
				$service_query .= '"' . $slug . '", ';
			}

			$service_query = rtrim( $service_query, ', ' );
			/** Use limit number of share services to optimize the query */
			$q = "SELECT likes_count, service FROM {$this->wpdb->prefix}social_counts WHERE entity_url LIKE '" . $entity_url . "' AND service IN (" . $service_query . ') LIMIT ' . count( $service_slugs );
		}

		$share_count = $this->wpdb->get_results( $q, 'ARRAY_A' );

		foreach ( $share_count as $k => $v ) {
			$service           = ! isset( $v['service'] ) ? 'all' : $v['service'];
			$results[$service] = $v['likes_count'];
		}

		return $results;
	}


	/**
	 * Get the ids and urls of the latest added posts
	 *
	 * @uses apply_filters() Calls the 'atf_social_share_count_post_types' hook
	 *       on the post_types array that will get into cron
	 *
	 * @return array
	 */
	public function get_latest_permalinks_and_ids() {
		/**
		 * Filter the array holding the post types to query and ad to the
		 * queue for which share statistics will be gathered from the social
		 * sharing services.
		 *
		 * By default the string 'all' is used to query all public post types.
		 * In order to only use specific post types an array of post type slugs
		 * must be used. For example : array( 'post', 'shoes', 'books' );
		 *
		 * @param string $post_types. Default value 'all' - Queries all public
		 *                          post types.
		 *
		 * @return array The filtered $post_types
		 *
		 * @since 0.1.0
		 */
		$post_types = apply_filters( 'atf_social_share_count_post_types', 'any' );

		/** Get latest posts */
		$args = array(
			'posts_per_page' => 50,
			'post_type'      => $post_types,
			'post_status'    => 'publish'
		);
		$permalinks = $post_ids = array();

		$data  = array();
		$posts = new WP_Query( $args );
		if ( $posts->have_posts() ) {
			while ( $posts->have_posts() ) {
				$posts->the_post();
				$data[get_the_ID()] = get_permalink();
			}
		}
		wp_reset_query();

		return $data;
	}


	/**
	 * Setup posts in the queue that will be consumed by cron
	 *
	 * @param  $entity_url - url that gets shared
	 * @param  $entity_id  - post_type ID, optional if entity_url is not missing
	 *
	 * @return object $this
	 */
	public function setup_cron_queue( $entity_url = '', $entity_id = 0 ) {
		/** Do not automatically track all links. This can be done manually. */
		if ( ! is_singular() && ! is_home() ) {
			return;
		}

		/** Get 'sanitized' args */
		$entity_data = $this->_get_entity_id_or_url( $entity_url, $entity_id );
		extract( $entity_data );

		$this->_maybe_add_to_next_to_query_queue( $entity_url, $entity_id );
	}


	/**
	 * Runs the create table query
	 *
	 * @return object $this
	 */
	public function setup_db() {
		/**
		 * SQL query for creating the table that holds the share counts from
		 * different services.
		 *
		 * entity_id = post_type ID - optional as URL is what interests us
		 *               the most.
		 * entity_url = shared URL - mandatory. If not sent one is generated
		 *                from entity_id.
		 *
		 * share_exists - compound key used to identify wether the combination
		 * of url - nr of likes and service already exists in db and needs to
		 * be updated or a new entry needs to be inserted
		 *
		 * Set indexes for id, entity_id and entity_url - fields we're going
		 * to search by
		 */
		$sql = "CREATE TABLE IF NOT EXISTS `{$this->wpdb->prefix}social_counts` (
			`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
			`entity_id` INT NOT NULL DEFAULT '0',
			`entity_url` VARCHAR( 255 ) NOT NULL ,
			`likes_count` INT NOT NULL ,
			`service` VARCHAR( 100 ) NOT NULL ,
			`cron_updated` DATETIME NOT NULL DEFAULT '1000-01-01 00:00:00',
			UNIQUE KEY `share_exists` (`entity_url`, `service`),
			INDEX ( `id`, `entity_id`,  `entity_url` )
			)
			CHARACTER SET utf8 COLLATE utf8_bin;";

		$create_query = $this->wpdb->query( $sql );

		if ( false === $create_query ) {
			trigger_error( __( 'Custom social shares table could not be created', $this->textdomain ) );
		}

		/**
		 * SQL query for creating the table that will hold the queues of posts
		 * that will be parsed by cron
		 *
		 * entity_id = entity ID.
		 * entity_url = shared URL - mandatory. If not sent one is generated
		 * from entity_id.
		 * process_in_queue - name of the queue to which it belongs
		 *
		 * Set entity_id- fields as index
		 */
		$sql = "CREATE TABLE IF NOT EXISTS `{$this->wpdb->prefix}social_counts_queues` (
			`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
			`entity_id` INT NOT NULL DEFAULT '0',
			`entity_url` VARCHAR( 255 ) NOT NULL ,
			`process_in_queue` VARCHAR( 60 ) NOT NULL ,
			INDEX ( `id`, `entity_id` )
			)
			CHARACTER SET utf8 COLLATE utf8_bin;";

		$create_query = $this->wpdb->query( $sql );

		if ( false === $create_query ) {
			trigger_error( __( 'Custom social shares table could not be created', $this->textdomain ) );
		}

		return $this;
	}


	/**
	 * Checks and returns the appropriate values for entity ID and URL
	 *
	 * @param         $entity_url - url that gets shared
	 * @param         $entity_id  - post_type ID, optional if entity_url is not missing
	 *
	 * @global object $post
	 * @return array
	 */
	private function _get_entity_id_or_url( $entity_url, $entity_id ) {
		/** If called from wp_head hook get ID / URL from post global */
		if ( empty( $entity_id ) && empty( $entity_url ) ) {
			global $post;

			if ( ! isset( $post ) || empty( $post ) ) {
				return;
			}

			$entity_id  = $post->ID;
			$entity_url = get_permalink( $post );
		}

		/** If no custom url is sent then get it from the entity ID */
		$entity_url = empty( $entity_url ) ? get_permalink( $post ) : $entity_url;

		return compact( 'entity_url', 'entity_id' );
	}


	/**
	 * Add entity to the queue the cron will parse next.
	 *
	 * Check if entity is worthy of getting into the queue holding the items
	 * that will be queried next to update their shares count
	 *
	 * @param  $entity_url - url that gets shared
	 * @param  $entity_id  - post_type ID, optional if entity_url is not missing
	 *
	 * @return object $this
	 */
	private function _maybe_add_to_next_to_query_queue( $entity_url = '', $entity_id = 0 ) {
		/** Check if entity is already in a queue */
		$entity_queue = $this->_is_entity_in_queue( $entity_id );

		/** If not in a queue */
		if ( ! $entity_queue ) {
			/** Check if entity's shares data was updated recently updated */
			// if ( empty( $entity_id ) )
			// 	$main_where_clause = "`entity_url` = '$entity_url'";
			// else
			// 	$main_where_clause = "`entity_id` = '$entity_id'";

			// $last_updated = $this->wpdb->get_var(
			// 	"
			// 	SELECT `cron_updated`
			// 	FROM {$this->wpdb->prefix}social_counts
			// 	WHERE $main_where_clause
			// 	AND	DATE_SUB(NOW(), INTERVAL 40 MINUTE) <= `cron_updated`
			// 	LIMIT 0, 1
			// 	"
			// );

			// /** Add to queue */
			// if ( empty( $last_updated ) ) {
			$next_to_query_queue[$entity_id] = $entity_url;
			$this->_set_queue( 'next_to_query_queue',  $entity_id, $entity_url );
			// }
		}
		return $this;
	}


	/**
	 * Check if an id is already in a queue and if so return it's queue.
	 *
	 * @param  $enity_id - int
	 *
	 * @return mixed bool / string
	 */
	private function _is_entity_in_queue( $entity_id ) {
		$query = $this->wpdb->prepare(
			"SELECT process_in_queue FROM {$this->wpdb->prefix}social_counts_queues WHERE entity_id = %d LIMIT 1",
			$entity_id
		);

		$queue = $this->wpdb->get_row( $query );
		if ( empty( $queue ) )
			return false;

		return $queue->process_in_queue;
	}


	/**
	 * Associates an entity_id to a queue that will be processed.
	 *
	 * @param  $option_name - string
	 * @param  $entity_id - int
	 * @param  $entity_url - string
	 *
	 * @return bool
	 */
	private function _set_queue( $queue_name = '', $entity_id, $entity_url ) {
		if ( empty( $queue_name ) )
			return array();

		$entity_queue = $this->_is_entity_in_queue( $entity_id );
		/** Only add element if not in a queue */
		if ( ! $entity_queue ) {
			$query = $this->wpdb->prepare(
				"
				INSERT INTO {$this->wpdb->prefix}social_counts_queues (entity_id, entity_url, process_in_queue)
				VALUES ('%d','%s', '%s')",
				$entity_id, $entity_url, $queue_name
			);
		}
		else {
			/** Update element's queue if this changed */
			if ( $entity_queue != $queue_name ) {
				$query = $this->wpdb->prepare(
					"
					UPDATE {$this->wpdb->prefix}social_counts_queues
					SET process_in_queue = '{$queue_name}'
					WHERE entity_id = '{$entity_id}'
					"
				);
			}
		}

		$result = $this->wpdb->query( $query );

		return $result;
	}

	/**
	 * Get all entity ids / urls associated to a queue
	 *
	 * @param  $queue_name - string
	 *
	 * @return array
	 */
	private function _get_queue( $queue_name = '' ) {
		$queue_data = $this->wpdb->get_results(
			$this->wpdb->prepare(
				"SELECT entity_id, entity_url
				FROM {$this->wpdb->prefix}social_counts_queues
				WHERE process_in_queue = %s",
				$queue_name
			)
		);

		$queue = array();
		if ( empty( $queue_data ) )
			return $queue;

		foreach ( $queue_data as $key => $value ) {
			$queue[$value->entity_id] = $value->entity_url;
		}

		return $queue;
	}

	/**
	 * Remove all entries belonging to a queue
	 *
	 * @param  $queue_name - string
	 *
	 * @return bool
	 */
	private function _clear_queue( $queue_name ) {
		$result = $this->wpdb->query( "DELETE FROM {$this->wpdb->prefix}social_counts_queues WHERE process_in_queue LIKE '{$queue_name}'" );
		return $result;
	}
}