<?php
/**
 * Hack for earlier versions of WordPress to give plugin support
 *
 * @link      http://cfgeoplugin.com/
 * @since      4.0.0
 *
 * @package    CF_Geoplugin
 * @subpackage CF_Geoplugin/admin
 */
 
 
/**
* update_termmeta_cache() function for lower versions of wordpress below 4.4.0
* @link      https://developer.wordpress.org/reference/functions/update_termmeta_cache/
* @version   1.0.0
*/
if(!function_exists("update_termmeta_cache")):
	function update_termmeta_cache( $term_ids ) {
		// Bail if term meta table is not installed.
		if ( get_option( 'db_version' ) < 34370 ) {
			return;
		}
	 
		return update_meta_cache( 'term', $term_ids );
	}
endif;
/**
* wp_list_pluck() function for lower versions of wordpress below 3.1.0
* @link      https://developer.wordpress.org/reference/functions/wp_list_pluck/
* @version   1.0.0
*/
if(!function_exists("wp_list_pluck")):
	function wp_list_pluck( $list, $field, $index_key = null ) {
		if ( ! $index_key ) {
			/*
			 * This is simple. Could at some point wrap array_column()
			 * if we knew we had an array of arrays.
			 */
			foreach ( $list as $key => $value ) {
				if ( is_object( $value ) ) {
					$list[ $key ] = $value->$field;
				} else {
					$list[ $key ] = $value[ $field ];
				}
			}
			return $list;
		}
	 
		/*
		 * When index_key is not set for a particular item, push the value
		 * to the end of the stack. This is how array_column() behaves.
		 */
		$newlist = array();
		foreach ( $list as $value ) {
			if ( is_object( $value ) ) {
				if ( isset( $value->$index_key ) ) {
					$newlist[ $value->$index_key ] = $value->$field;
				} else {
					$newlist[] = $value->$field;
				}
			} else {
				if ( isset( $value[ $index_key ] ) ) {
					$newlist[ $value[ $index_key ] ] = $value[ $field ];
				} else {
					$newlist[] = $value[ $field ];
				}
			}
		}
	 
		return $newlist;
	}
endif;
/**
* WP_Meta_Query() class for lower versions of wordpress below 3.2.0
* @link      https://developer.wordpress.org/reference/classes/wp_meta_query/
* @version   1.0.0
*/
if(!class_exists("WP_Meta_Query")):
	class WP_Meta_Query {
		/**
		 * Array of metadata queries.
		 *
		 * See WP_Meta_Query::__construct() for information on meta query arguments.
		 *
		 * @since 3.2.0
		 * @access public
		 * @var array
		 */
		public $queries = array();
	 
		/**
		 * The relation between the queries. Can be one of 'AND' or 'OR'.
		 *
		 * @since 3.2.0
		 * @access public
		 * @var string
		 */
		public $relation;
	 
		/**
		 * Database table to query for the metadata.
		 *
		 * @since 4.1.0
		 * @access public
		 * @var string
		 */
		public $meta_table;
	 
		/**
		 * Column in meta_table that represents the ID of the object the metadata belongs to.
		 *
		 * @since 4.1.0
		 * @access public
		 * @var string
		 */
		public $meta_id_column;
	 
		/**
		 * Database table that where the metadata's objects are stored (eg $wpdb->users).
		 *
		 * @since 4.1.0
		 * @access public
		 * @var string
		 */
		public $primary_table;
	 
		/**
		 * Column in primary_table that represents the ID of the object.
		 *
		 * @since 4.1.0
		 * @access public
		 * @var string
		 */
		public $primary_id_column;
	 
		/**
		 * A flat list of table aliases used in JOIN clauses.
		 *
		 * @since 4.1.0
		 * @access protected
		 * @var array
		 */
		protected $table_aliases = array();
	 
		/**
		 * A flat list of clauses, keyed by clause 'name'.
		 *
		 * @since 4.2.0
		 * @access protected
		 * @var array
		 */
		protected $clauses = array();
	 
		/**
		 * Whether the query contains any OR relations.
		 *
		 * @since 4.3.0
		 * @access protected
		 * @var bool
		 */
		protected $has_or_relation = false;
	 
		/**
		 * Constructor.
		 *
		 * @since 3.2.0
		 * @since 4.2.0 Introduced support for naming query clauses by associative array keys.
		 *
		 * @access public
		 *
		 * @param array $meta_query {
		 *     Array of meta query clauses. When first-order clauses or sub-clauses use strings as
		 *     their array keys, they may be referenced in the 'orderby' parameter of the parent query.
		 *
		 *     @type string $relation Optional. The MySQL keyword used to join
		 *                            the clauses of the query. Accepts 'AND', or 'OR'. Default 'AND'.
		 *     @type array {
		 *         Optional. An array of first-order clause parameters, or another fully-formed meta query.
		 *
		 *         @type string $key     Meta key to filter by.
		 *         @type string $value   Meta value to filter by.
		 *         @type string $compare MySQL operator used for comparing the $value. Accepts '=',
		 *                               '!=', '>', '>=', '<', '<=', 'LIKE', 'NOT LIKE',
		 *                               'IN', 'NOT IN', 'BETWEEN', 'NOT BETWEEN', 'REGEXP',
		 *                               'NOT REGEXP', 'RLIKE', 'EXISTS' or 'NOT EXISTS'.
		 *                               Default is 'IN' when `$value` is an array, '=' otherwise.
		 *         @type string $type    MySQL data type that the meta_value column will be CAST to for
		 *                               comparisons. Accepts 'NUMERIC', 'BINARY', 'CHAR', 'DATE',
		 *                               'DATETIME', 'DECIMAL', 'SIGNED', 'TIME', or 'UNSIGNED'.
		 *                               Default is 'CHAR'.
		 *     }
		 * }
		 */
		public function __construct( $meta_query = false ) {
			if ( !$meta_query )
				return;
	 
			if ( isset( $meta_query['relation'] ) && strtoupper( $meta_query['relation'] ) == 'OR' ) {
				$this->relation = 'OR';
			} else {
				$this->relation = 'AND';
			}
	 
			$this->queries = $this->sanitize_query( $meta_query );
		}
	 
		/**
		 * Ensure the 'meta_query' argument passed to the class constructor is well-formed.
		 *
		 * Eliminates empty items and ensures that a 'relation' is set.
		 *
		 * @since 4.1.0
		 * @access public
		 *
		 * @param array $queries Array of query clauses.
		 * @return array Sanitized array of query clauses.
		 */
		public function sanitize_query( $queries ) {
			$clean_queries = array();
	 
			if ( ! is_array( $queries ) ) {
				return $clean_queries;
			}
	 
			foreach ( $queries as $key => $query ) {
				if ( 'relation' === $key ) {
					$relation = $query;
	 
				} elseif ( ! is_array( $query ) ) {
					continue;
	 
				// First-order clause.
				} elseif ( $this->is_first_order_clause( $query ) ) {
					if ( isset( $query['value'] ) && array() === $query['value'] ) {
						unset( $query['value'] );
					}
	 
					$clean_queries[ $key ] = $query;
	 
				// Otherwise, it's a nested query, so we recurse.
				} else {
					$cleaned_query = $this->sanitize_query( $query );
	 
					if ( ! empty( $cleaned_query ) ) {
						$clean_queries[ $key ] = $cleaned_query;
					}
				}
			}
	 
			if ( empty( $clean_queries ) ) {
				return $clean_queries;
			}
	 
			// Sanitize the 'relation' key provided in the query.
			if ( isset( $relation ) && 'OR' === strtoupper( $relation ) ) {
				$clean_queries['relation'] = 'OR';
				$this->has_or_relation = true;
	 
			/*
			 * If there is only a single clause, call the relation 'OR'.
			 * This value will not actually be used to join clauses, but it
			 * simplifies the logic around combining key-only queries.
			 */
			} elseif ( 1 === count( $clean_queries ) ) {
				$clean_queries['relation'] = 'OR';
	 
			// Default to AND.
			} else {
				$clean_queries['relation'] = 'AND';
			}
	 
			return $clean_queries;
		}
	 
		/**
		 * Determine whether a query clause is first-order.
		 *
		 * A first-order meta query clause is one that has either a 'key' or
		 * a 'value' array key.
		 *
		 * @since 4.1.0
		 * @access protected
		 *
		 * @param array $query Meta query arguments.
		 * @return bool Whether the query clause is a first-order clause.
		 */
		protected function is_first_order_clause( $query ) {
			return isset( $query['key'] ) || isset( $query['value'] );
		}
	 
		/**
		 * Constructs a meta query based on 'meta_*' query vars
		 *
		 * @since 3.2.0
		 * @access public
		 *
		 * @param array $qv The query variables
		 */
		public function parse_query_vars( $qv ) {
			$meta_query = array();
	 
			/*
			 * For orderby=meta_value to work correctly, simple query needs to be
			 * first (so that its table join is against an unaliased meta table) and
			 * needs to be its own clause (so it doesn't interfere with the logic of
			 * the rest of the meta_query).
			 */
			$primary_meta_query = array();
			foreach ( array( 'key', 'compare', 'type' ) as $key ) {
				if ( ! empty( $qv[ "meta_$key" ] ) ) {
					$primary_meta_query[ $key ] = $qv[ "meta_$key" ];
				}
			}
	 
			// WP_Query sets 'meta_value' = '' by default.
			if ( isset( $qv['meta_value'] ) && '' !== $qv['meta_value'] && ( ! is_array( $qv['meta_value'] ) || $qv['meta_value'] ) ) {
				$primary_meta_query['value'] = $qv['meta_value'];
			}
	 
			$existing_meta_query = isset( $qv['meta_query'] ) && is_array( $qv['meta_query'] ) ? $qv['meta_query'] : array();
	 
			if ( ! empty( $primary_meta_query ) && ! empty( $existing_meta_query ) ) {
				$meta_query = array(
					'relation' => 'AND',
					$primary_meta_query,
					$existing_meta_query,
				);
			} elseif ( ! empty( $primary_meta_query ) ) {
				$meta_query = array(
					$primary_meta_query,
				);
			} elseif ( ! empty( $existing_meta_query ) ) {
				$meta_query = $existing_meta_query;
			}
	 
			$this->__construct( $meta_query );
		}
	 
		/**
		 * Return the appropriate alias for the given meta type if applicable.
		 *
		 * @since 3.7.0
		 * @access public
		 *
		 * @param string $type MySQL type to cast meta_value.
		 * @return string MySQL type.
		 */
		public function get_cast_for_type( $type = '' ) {
			if ( empty( $type ) )
				return 'CHAR';
	 
			$meta_type = strtoupper( $type );
	 
			if ( ! preg_match( '/^(?:BINARY|CHAR|DATE|DATETIME|SIGNED|UNSIGNED|TIME|NUMERIC(?:\(\d+(?:,\s?\d+)?\))?|DECIMAL(?:\(\d+(?:,\s?\d+)?\))?)$/', $meta_type ) )
				return 'CHAR';
	 
			if ( 'NUMERIC' == $meta_type )
				$meta_type = 'SIGNED';
	 
			return $meta_type;
		}
	 
		/**
		 * Generates SQL clauses to be appended to a main query.
		 *
		 * @since 3.2.0
		 * @access public
		 *
		 * @param string $type              Type of meta, eg 'user', 'post'.
		 * @param string $primary_table     Database table where the object being filtered is stored (eg wp_users).
		 * @param string $primary_id_column ID column for the filtered object in $primary_table.
		 * @param object $context           Optional. The main query object.
		 * @return false|array {
		 *     Array containing JOIN and WHERE SQL clauses to append to the main query.
		 *
		 *     @type string $join  SQL fragment to append to the main JOIN clause.
		 *     @type string $where SQL fragment to append to the main WHERE clause.
		 * }
		 */
		public function get_sql( $type, $primary_table, $primary_id_column, $context = null ) {
			if ( ! $meta_table = _get_meta_table( $type ) ) {
				return false;
			}
	 
			$this->table_aliases = array();
	 
			$this->meta_table     = $meta_table;
			$this->meta_id_column = sanitize_key( $type . '_id' );
	 
			$this->primary_table     = $primary_table;
			$this->primary_id_column = $primary_id_column;
	 
			$sql = $this->get_sql_clauses();
	 
			/*
			 * If any JOINs are LEFT JOINs (as in the case of NOT EXISTS), then all JOINs should
			 * be LEFT. Otherwise posts with no metadata will be excluded from results.
			 */
			if ( false !== strpos( $sql['join'], 'LEFT JOIN' ) ) {
				$sql['join'] = str_replace( 'INNER JOIN', 'LEFT JOIN', $sql['join'] );
			}
	 
			/**
			 * Filters the meta query's generated SQL.
			 *
			 * @since 3.1.0
			 *
			 * @param array  $clauses           Array containing the query's JOIN and WHERE clauses.
			 * @param array  $queries           Array of meta queries.
			 * @param string $type              Type of meta.
			 * @param string $primary_table     Primary table.
			 * @param string $primary_id_column Primary column ID.
			 * @param object $context           The main query object.
			 */
			return apply_filters_ref_array( 'get_meta_sql', array( $sql, $this->queries, $type, $primary_table, $primary_id_column, $context ) );
		}
	 
		/**
		 * Generate SQL clauses to be appended to a main query.
		 *
		 * Called by the public WP_Meta_Query::get_sql(), this method is abstracted
		 * out to maintain parity with the other Query classes.
		 *
		 * @since 4.1.0
		 * @access protected
		 *
		 * @return array {
		 *     Array containing JOIN and WHERE SQL clauses to append to the main query.
		 *
		 *     @type string $join  SQL fragment to append to the main JOIN clause.
		 *     @type string $where SQL fragment to append to the main WHERE clause.
		 * }
		 */
		protected function get_sql_clauses() {
			/*
			 * $queries are passed by reference to get_sql_for_query() for recursion.
			 * To keep $this->queries unaltered, pass a copy.
			 */
			$queries = $this->queries;
			$sql = $this->get_sql_for_query( $queries );
	 
			if ( ! empty( $sql['where'] ) ) {
				$sql['where'] = ' AND ' . $sql['where'];
			}
	 
			return $sql;
		}
	 
		/**
		 * Generate SQL clauses for a single query array.
		 *
		 * If nested subqueries are found, this method recurses the tree to
		 * produce the properly nested SQL.
		 *
		 * @since 4.1.0
		 * @access protected
		 *
		 * @param array $query Query to parse, passed by reference.
		 * @param int   $depth Optional. Number of tree levels deep we currently are.
		 *                     Used to calculate indentation. Default 0.
		 * @return array {
		 *     Array containing JOIN and WHERE SQL clauses to append to a single query array.
		 *
		 *     @type string $join  SQL fragment to append to the main JOIN clause.
		 *     @type string $where SQL fragment to append to the main WHERE clause.
		 * }
		 */
		protected function get_sql_for_query( &$query, $depth = 0 ) {
			$sql_chunks = array(
				'join'  => array(),
				'where' => array(),
			);
	 
			$sql = array(
				'join'  => '',
				'where' => '',
			);
	 
			$indent = '';
			for ( $i = 0; $i < $depth; $i++ ) {
				$indent .= "  ";
			}
	 
			foreach ( $query as $key => &$clause ) {
				if ( 'relation' === $key ) {
					$relation = $query['relation'];
				} elseif ( is_array( $clause ) ) {
	 
					// This is a first-order clause.
					if ( $this->is_first_order_clause( $clause ) ) {
						$clause_sql = $this->get_sql_for_clause( $clause, $query, $key );
	 
						$where_count = count( $clause_sql['where'] );
						if ( ! $where_count ) {
							$sql_chunks['where'][] = '';
						} elseif ( 1 === $where_count ) {
							$sql_chunks['where'][] = $clause_sql['where'][0];
						} else {
							$sql_chunks['where'][] = '( ' . implode( ' AND ', $clause_sql['where'] ) . ' )';
						}
	 
						$sql_chunks['join'] = array_merge( $sql_chunks['join'], $clause_sql['join'] );
					// This is a subquery, so we recurse.
					} else {
						$clause_sql = $this->get_sql_for_query( $clause, $depth + 1 );
	 
						$sql_chunks['where'][] = $clause_sql['where'];
						$sql_chunks['join'][]  = $clause_sql['join'];
					}
				}
			}
	 
			// Filter to remove empties.
			$sql_chunks['join']  = array_filter( $sql_chunks['join'] );
			$sql_chunks['where'] = array_filter( $sql_chunks['where'] );
	 
			if ( empty( $relation ) ) {
				$relation = 'AND';
			}
	 
			// Filter duplicate JOIN clauses and combine into a single string.
			if ( ! empty( $sql_chunks['join'] ) ) {
				$sql['join'] = implode( ' ', array_unique( $sql_chunks['join'] ) );
			}
	 
			// Generate a single WHERE clause with proper brackets and indentation.
			if ( ! empty( $sql_chunks['where'] ) ) {
				$sql['where'] = '( ' . "\n  " . $indent . implode( ' ' . "\n  " . $indent . $relation . ' ' . "\n  " . $indent, $sql_chunks['where'] ) . "\n" . $indent . ')';
			}
	 
			return $sql;
		}
	 
		/**
		 * Generate SQL JOIN and WHERE clauses for a first-order query clause.
		 *
		 * "First-order" means that it's an array with a 'key' or 'value'.
		 *
		 * @since 4.1.0
		 * @access public
		 *
		 * @global wpdb $wpdb WordPress database abstraction object.
		 *
		 * @param array  $clause       Query clause, passed by reference.
		 * @param array  $parent_query Parent query array.
		 * @param string $clause_key   Optional. The array key used to name the clause in the original `$meta_query`
		 *                             parameters. If not provided, a key will be generated automatically.
		 * @return array {
		 *     Array containing JOIN and WHERE SQL clauses to append to a first-order query.
		 *
		 *     @type string $join  SQL fragment to append to the main JOIN clause.
		 *     @type string $where SQL fragment to append to the main WHERE clause.
		 * }
		 */
		public function get_sql_for_clause( &$clause, $parent_query, $clause_key = '' ) {
			global $wpdb;
	 
			$sql_chunks = array(
				'where' => array(),
				'join' => array(),
			);
	 
			if ( isset( $clause['compare'] ) ) {
				$clause['compare'] = strtoupper( $clause['compare'] );
			} else {
				$clause['compare'] = isset( $clause['value'] ) && is_array( $clause['value'] ) ? 'IN' : '=';
			}
	 
			if ( ! in_array( $clause['compare'], array(
				'=', '!=', '>', '>=', '<', '<=',
				'LIKE', 'NOT LIKE',
				'IN', 'NOT IN',
				'BETWEEN', 'NOT BETWEEN',
				'EXISTS', 'NOT EXISTS',
				'REGEXP', 'NOT REGEXP', 'RLIKE'
			) ) ) {
				$clause['compare'] = '=';
			}
	 
			$meta_compare = $clause['compare'];
	 
			// First build the JOIN clause, if one is required.
			$join = '';
	 
			// We prefer to avoid joins if possible. Look for an existing join compatible with this clause.
			$alias = $this->find_compatible_table_alias( $clause, $parent_query );
			if ( false === $alias ) {
				$i = count( $this->table_aliases );
				$alias = $i ? 'mt' . $i : $this->meta_table;
	 
				// JOIN clauses for NOT EXISTS have their own syntax.
				if ( 'NOT EXISTS' === $meta_compare ) {
					$join .= " LEFT JOIN $this->meta_table";
					$join .= $i ? " AS $alias" : '';
					$join .= $wpdb->prepare( " ON ($this->primary_table.$this->primary_id_column = $alias.$this->meta_id_column AND $alias.meta_key = %s )", $clause['key'] );
	 
				// All other JOIN clauses.
				} else {
					$join .= " INNER JOIN $this->meta_table";
					$join .= $i ? " AS $alias" : '';
					$join .= " ON ( $this->primary_table.$this->primary_id_column = $alias.$this->meta_id_column )";
				}
	 
				$this->table_aliases[] = $alias;
				$sql_chunks['join'][] = $join;
			}
	 
			// Save the alias to this clause, for future siblings to find.
			$clause['alias'] = $alias;
	 
			// Determine the data type.
			$_meta_type = isset( $clause['type'] ) ? $clause['type'] : '';
			$meta_type  = $this->get_cast_for_type( $_meta_type );
			$clause['cast'] = $meta_type;
	 
			// Fallback for clause keys is the table alias. Key must be a string.
			if ( is_int( $clause_key ) || ! $clause_key ) {
				$clause_key = $clause['alias'];
			}
	 
			// Ensure unique clause keys, so none are overwritten.
			$iterator = 1;
			$clause_key_base = $clause_key;
			while ( isset( $this->clauses[ $clause_key ] ) ) {
				$clause_key = $clause_key_base . '-' . $iterator;
				$iterator++;
			}
	 
			// Store the clause in our flat array.
			$this->clauses[ $clause_key ] =& $clause;
	 
			// Next, build the WHERE clause.
	 
			// meta_key.
			if ( array_key_exists( 'key', $clause ) ) {
				if ( 'NOT EXISTS' === $meta_compare ) {
					$sql_chunks['where'][] = $alias . '.' . $this->meta_id_column . ' IS NULL';
				} else {
					$sql_chunks['where'][] = $wpdb->prepare( "$alias.meta_key = %s", trim( $clause['key'] ) );
				}
			}
	 
			// meta_value.
			if ( array_key_exists( 'value', $clause ) ) {
				$meta_value = $clause['value'];
	 
				if ( in_array( $meta_compare, array( 'IN', 'NOT IN', 'BETWEEN', 'NOT BETWEEN' ) ) ) {
					if ( ! is_array( $meta_value ) ) {
						$meta_value = preg_split( '/[,\s]+/', $meta_value );
					}
				} else {
					$meta_value = trim( $meta_value );
				}
	 
				switch ( $meta_compare ) {
					case 'IN' :
					case 'NOT IN' :
						$meta_compare_string = '(' . substr( str_repeat( ',%s', count( $meta_value ) ), 1 ) . ')';
						$where = $wpdb->prepare( $meta_compare_string, $meta_value );
						break;
	 
					case 'BETWEEN' :
					case 'NOT BETWEEN' :
						$meta_value = array_slice( $meta_value, 0, 2 );
						$where = $wpdb->prepare( '%s AND %s', $meta_value );
						break;
	 
					case 'LIKE' :
					case 'NOT LIKE' :
						$meta_value = '%' . $wpdb->esc_like( $meta_value ) . '%';
						$where = $wpdb->prepare( '%s', $meta_value );
						break;
	 
					// EXISTS with a value is interpreted as '='.
					case 'EXISTS' :
						$meta_compare = '=';
						$where = $wpdb->prepare( '%s', $meta_value );
						break;
	 
					// 'value' is ignored for NOT EXISTS.
					case 'NOT EXISTS' :
						$where = '';
						break;
	 
					default :
						$where = $wpdb->prepare( '%s', $meta_value );
						break;
	 
				}
	 
				if ( $where ) {
					if ( 'CHAR' === $meta_type ) {
						$sql_chunks['where'][] = "$alias.meta_value {$meta_compare} {$where}";
					} else {
						$sql_chunks['where'][] = "CAST($alias.meta_value AS {$meta_type}) {$meta_compare} {$where}";
					}
				}
			}
	 
			/*
			 * Multiple WHERE clauses (for meta_key and meta_value) should
			 * be joined in parentheses.
			 */
			if ( 1 < count( $sql_chunks['where'] ) ) {
				$sql_chunks['where'] = array( '( ' . implode( ' AND ', $sql_chunks['where'] ) . ' )' );
			}
	 
			return $sql_chunks;
		}
	 
		/**
		 * Get a flattened list of sanitized meta clauses.
		 *
		 * This array should be used for clause lookup, as when the table alias and CAST type must be determined for
		 * a value of 'orderby' corresponding to a meta clause.
		 *
		 * @since 4.2.0
		 * @access public
		 *
		 * @return array Meta clauses.
		 */
		public function get_clauses() {
			return $this->clauses;
		}
	 
		/**
		 * Identify an existing table alias that is compatible with the current
		 * query clause.
		 *
		 * We avoid unnecessary table joins by allowing each clause to look for
		 * an existing table alias that is compatible with the query that it
		 * needs to perform.
		 *
		 * An existing alias is compatible if (a) it is a sibling of `$clause`
		 * (ie, it's under the scope of the same relation), and (b) the combination
		 * of operator and relation between the clauses allows for a shared table join.
		 * In the case of WP_Meta_Query, this only applies to 'IN' clauses that are
		 * connected by the relation 'OR'.
		 *
		 * @since 4.1.0
		 * @access protected
		 *
		 * @param  array       $clause       Query clause.
		 * @param  array       $parent_query Parent query of $clause.
		 * @return string|bool Table alias if found, otherwise false.
		 */
		protected function find_compatible_table_alias( $clause, $parent_query ) {
			$alias = false;
	 
			foreach ( $parent_query as $sibling ) {
				// If the sibling has no alias yet, there's nothing to check.
				if ( empty( $sibling['alias'] ) ) {
					continue;
				}
	 
				// We're only interested in siblings that are first-order clauses.
				if ( ! is_array( $sibling ) || ! $this->is_first_order_clause( $sibling ) ) {
					continue;
				}
	 
				$compatible_compares = array();
	 
				// Clauses connected by OR can share joins as long as they have "positive" operators.
				if ( 'OR' === $parent_query['relation'] ) {
					$compatible_compares = array( '=', 'IN', 'BETWEEN', 'LIKE', 'REGEXP', 'RLIKE', '>', '>=', '<', '<=' );
	 
				// Clauses joined by AND with "negative" operators share a join only if they also share a key.
				} elseif ( isset( $sibling['key'] ) && isset( $clause['key'] ) && $sibling['key'] === $clause['key'] ) {
					$compatible_compares = array( '!=', 'NOT IN', 'NOT LIKE' );
				}
	 
				$clause_compare  = strtoupper( $clause['compare'] );
				$sibling_compare = strtoupper( $sibling['compare'] );
				if ( in_array( $clause_compare, $compatible_compares ) && in_array( $sibling_compare, $compatible_compares ) ) {
					$alias = $sibling['alias'];
					break;
				}
			}
	 
			/**
			 * Filters the table alias identified as compatible with the current clause.
			 *
			 * @since 4.1.0
			 *
			 * @param string|bool $alias        Table alias, or false if none was found.
			 * @param array       $clause       First-order query clause.
			 * @param array       $parent_query Parent of $clause.
			 * @param object      $this         WP_Meta_Query object.
			 */
			return apply_filters( 'meta_query_find_compatible_table_alias', $alias, $clause, $parent_query, $this ) ;
		}
	 
		/**
		 * Checks whether the current query has any OR relations.
		 *
		 * In some cases, the presence of an OR relation somewhere in the query will require
		 * the use of a `DISTINCT` or `GROUP BY` keyword in the `SELECT` clause. The current
		 * method can be used in these cases to determine whether such a clause is necessary.
		 *
		 * @since 4.3.0
		 *
		 * @return bool True if the query contains any `OR` relations, otherwise false.
		 */
		public function has_or_relation() {
			return $this->has_or_relation;
		}
	}
endif;
/**
* WP_Term_Query() class for lower versions of wordpress below 4.6.0
* @link      https://developer.wordpress.org/reference/classes/WP_Term_Query/
* @version   1.0.0
*/
if(!class_exists("WP_Term_Query")):
	class WP_Term_Query {
	 
		/**
		 * SQL string used to perform database query.
		 *
		 * @since 4.6.0
		 * @access public
		 * @var string
		 */
		public $request;
	 
		/**
		 * Metadata query container.
		 *
		 * @since 4.6.0
		 * @access public
		 * @var object WP_Meta_Query
		 */
		public $meta_query = false;
	 
		/**
		 * Metadata query clauses.
		 *
		 * @since 4.6.0
		 * @access protected
		 * @var array
		 */
		protected $meta_query_clauses;
	 
		/**
		 * SQL query clauses.
		 *
		 * @since 4.6.0
		 * @access protected
		 * @var array
		 */
		protected $sql_clauses = array(
			'select'  => '',
			'from'    => '',
			'where'   => array(),
			'orderby' => '',
			'limits'  => '',
		);
	 
		/**
		 * Query vars set by the user.
		 *
		 * @since 4.6.0
		 * @access public
		 * @var array
		 */
		public $query_vars;
	 
		/**
		 * Default values for query vars.
		 *
		 * @since 4.6.0
		 * @access public
		 * @var array
		 */
		public $query_var_defaults;
	 
		/**
		 * List of terms located by the query.
		 *
		 * @since 4.6.0
		 * @access public
		 * @var array
		 */
		public $terms;
	 
		/**
		 * Constructor.
		 *
		 * Sets up the term query, based on the query vars passed.
		 *
		 * @since 4.6.0
		 * @since 4.6.0 Introduced 'term_taxonomy_id' parameter.
		 * @access public
		 *
		 * @param string|array $query {
		 *     Optional. Array or query string of term query parameters. Default empty.
		 *
		 *     @type string|array $taxonomy               Taxonomy name, or array of taxonomies, to which results should
		 *                                                be limited.
		 *     @type string       $orderby                Field(s) to order terms by. Accepts term fields ('name',
		 *                                                'slug', 'term_group', 'term_id', 'id', 'description'),
		 *                                                'count' for term taxonomy count, 'include' to match the
		 *                                                'order' of the $include param, 'meta_value', 'meta_value_num',
		 *                                                the value of `$meta_key`, the array keys of `$meta_query`, or
		 *                                                'none' to omit the ORDER BY clause. Defaults to 'name'.
		 *     @type string       $order                  Whether to order terms in ascending or descending order.
		 *                                                Accepts 'ASC' (ascending) or 'DESC' (descending).
		 *                                                Default 'ASC'.
		 *     @type bool|int     $hide_empty             Whether to hide terms not assigned to any posts. Accepts
		 *                                                1|true or 0|false. Default 1|true.
		 *     @type array|string $include                Array or comma/space-separated string of term ids to include.
		 *                                                Default empty array.
		 *     @type array|string $exclude                Array or comma/space-separated string of term ids to exclude.
		 *                                                If $include is non-empty, $exclude is ignored.
		 *                                                Default empty array.
		 *     @type array|string $exclude_tree           Array or comma/space-separated string of term ids to exclude
		 *                                                along with all of their descendant terms. If $include is
		 *                                                non-empty, $exclude_tree is ignored. Default empty array.
		 *     @type int|string   $number                 Maximum number of terms to return. Accepts ''|0 (all) or any
		 *                                                positive number. Default ''|0 (all).
		 *     @type int          $offset                 The number by which to offset the terms query. Default empty.
		 *     @type string       $fields                 Term fields to query for. Accepts 'all' (returns an array of
		 *                                                complete term objects), 'ids' (returns an array of ids),
		 *                                                'id=>parent' (returns an associative array with ids as keys,
		 *                                                parent term IDs as values), 'names' (returns an array of term
		 *                                                names), 'count' (returns the number of matching terms),
		 *                                                'id=>name' (returns an associative array with ids as keys,
		 *                                                term names as values), or 'id=>slug' (returns an associative
		 *                                                array with ids as keys, term slugs as values). Default 'all'.
		 *     @type bool         $count                  Whether to return a term count (true) or array of term objects
		 *                                                (false). Will take precedence over `$fields` if true.
		 *                                                Default false.
		 *     @type string|array $name                   Optional. Name or array of names to return term(s) for.
		 *                                                Default empty.
		 *     @type string|array $slug                   Optional. Slug or array of slugs to return term(s) for.
		 *                                                Default empty.
		 *     @type int|array    $term_taxonomy_id       Optional. Term taxonomy ID, or array of term taxonomy IDs,
		 *                                                to match when querying terms.
		 *     @type bool         $hierarchical           Whether to include terms that have non-empty descendants (even
		 *                                                if $hide_empty is set to true). Default true.
		 *     @type string       $search                 Search criteria to match terms. Will be SQL-formatted with
		 *                                                wildcards before and after. Default empty.
		 *     @type string       $name__like             Retrieve terms with criteria by which a term is LIKE
		 *                                                `$name__like`. Default empty.
		 *     @type string       $description__like      Retrieve terms where the description is LIKE
		 *                                                `$description__like`. Default empty.
		 *     @type bool         $pad_counts             Whether to pad the quantity of a term's children in the
		 *                                                quantity of each term's "count" object variable.
		 *                                                Default false.
		 *     @type string       $get                    Whether to return terms regardless of ancestry or whether the
		 *                                                terms are empty. Accepts 'all' or empty (disabled).
		 *                                                Default empty.
		 *     @type int          $child_of               Term ID to retrieve child terms of. If multiple taxonomies
		 *                                                are passed, $child_of is ignored. Default 0.
		 *     @type int|string   $parent                 Parent term ID to retrieve direct-child terms of.
		 *                                                Default empty.
		 *     @type bool         $childless              True to limit results to terms that have no children.
		 *                                                This parameter has no effect on non-hierarchical taxonomies.
		 *                                                Default false.
		 *     @type string       $cache_domain           Unique cache key to be produced when this query is stored in
		 *                                                an object cache. Default is 'core'.
		 *     @type bool         $update_term_meta_cache Whether to prime meta caches for matched terms. Default true.
		 *     @type array        $meta_query             Optional. Meta query clauses to limit retrieved terms by.
		 *                                                See `WP_Meta_Query`. Default empty.
		 *     @type string       $meta_key               Limit terms to those matching a specific metadata key.
		 *                                                Can be used in conjunction with `$meta_value`.
		 *     @type string       $meta_value             Limit terms to those matching a specific metadata value.
		 *                                                Usually used in conjunction with `$meta_key`.
		 * }
		 */
		public function __construct( $query = '' ) {
			$this->query_var_defaults = array(
				'taxonomy'               => null,
				'orderby'                => 'name',
				'order'                  => 'ASC',
				'hide_empty'             => true,
				'include'                => array(),
				'exclude'                => array(),
				'exclude_tree'           => array(),
				'number'                 => '',
				'offset'                 => '',
				'fields'                 => 'all',
				'count'                  => false,
				'name'                   => '',
				'slug'                   => '',
				'term_taxonomy_id'       => '',
				'hierarchical'           => true,
				'search'                 => '',
				'name__like'             => '',
				'description__like'      => '',
				'pad_counts'             => false,
				'get'                    => '',
				'child_of'               => 0,
				'parent'                 => '',
				'childless'              => false,
				'cache_domain'           => 'core',
				'update_term_meta_cache' => true,
				'meta_query'             => '',
			);
	 
			if ( ! empty( $query ) ) {
				$this->query( $query );
			}
		}
	 
		/**
		 * Parse arguments passed to the term query with default query parameters.
		 *
		 * @since 4.6.0
		 * @access public
		 *
		 * @param string|array $query WP_Term_Query arguments. See WP_Term_Query::__construct()
		 */
		public function parse_query( $query = '' ) {
			if ( empty( $query ) ) {
				$query = $this->query_vars;
			}
	 
			$taxonomies = isset( $query['taxonomy'] ) ? (array) $query['taxonomy'] : null;
	 
			/**
			 * Filters the terms query default arguments.
			 *
			 * Use {@see 'get_terms_args'} to filter the passed arguments.
			 *
			 * @since 4.4.0
			 *
			 * @param array $defaults   An array of default get_terms() arguments.
			 * @param array $taxonomies An array of taxonomies.
			 */
			$this->query_var_defaults = apply_filters( 'get_terms_defaults', $this->query_var_defaults, $taxonomies );
	 
			$query = wp_parse_args( $query, $this->query_var_defaults );
	 
			$query['number'] = absint( $query['number'] );
			$query['offset'] = absint( $query['offset'] );
	 
			// 'parent' overrides 'child_of'.
			if ( 0 < intval( $query['parent'] ) ) {
				$query['child_of'] = false;
			}
	 
			if ( 'all' == $query['get'] ) {
				$query['childless'] = false;
				$query['child_of'] = 0;
				$query['hide_empty'] = 0;
				$query['hierarchical'] = false;
				$query['pad_counts'] = false;
			}
	 
			$query['taxonomy'] = $taxonomies;
	 
			/**
			 * Filters the terms query arguments.
			 *
			 * @since 3.1.0
			 *
			 * @param array $args       An array of get_terms() arguments.
			 * @param array $taxonomies An array of taxonomies.
			 */
			$this->query_vars = apply_filters( 'get_terms_args', $query, $taxonomies );
	 
			/**
			 * Fires after term query vars have been parsed.
			 *
			 * @since 4.6.0
			 *
			 * @param WP_Term_Query $this Current instance of WP_Term_Query.
			 */
			do_action( 'parse_term_query', $this );
		}
	 
		/**
		 * Sets up the query for retrieving terms.
		 *
		 * @since 4.6.0
		 * @access public
		 *
		 * @param string|array $query Array or URL query string of parameters.
		 * @return array|int List of terms, or number of terms when 'count' is passed as a query var.
		 */
		public function query( $query ) {
			$this->query_vars = wp_parse_args( $query );
			return $this->get_terms();
		}
	 
		/**
		 * Get terms, based on query_vars.
		 *
		 * @param 4.6.0
		 * @access public
		 *
		 * @global wpdb $wpdb WordPress database abstraction object.
		 *
		 * @return array
		 */
		public function get_terms() {
			global $wpdb;
	 
			$this->parse_query( $this->query_vars );
			$args = $this->query_vars;
	 
			// Set up meta_query so it's available to 'pre_get_terms'.
			$this->meta_query = new WP_Meta_Query();
			$this->meta_query->parse_query_vars( $args );
	 
			/**
			 * Fires before terms are retrieved.
			 *
			 * @since 4.6.0
			 *
			 * @param WP_Term_Query $this Current instance of WP_Term_Query.
			 */
			do_action( 'pre_get_terms', $this );
	 
			$taxonomies = $args['taxonomy'];
	 
			// Save queries by not crawling the tree in the case of multiple taxes or a flat tax.
			$has_hierarchical_tax = false;
			if ( $taxonomies ) {
				foreach ( $taxonomies as $_tax ) {
					if ( is_taxonomy_hierarchical( $_tax ) ) {
						$has_hierarchical_tax = true;
					}
				}
			}
	 
			if ( ! $has_hierarchical_tax ) {
				$args['hierarchical'] = false;
				$args['pad_counts'] = false;
			}
	 
			// 'parent' overrides 'child_of'.
			if ( 0 < intval( $args['parent'] ) ) {
				$args['child_of'] = false;
			}
	 
			if ( 'all' == $args['get'] ) {
				$args['childless'] = false;
				$args['child_of'] = 0;
				$args['hide_empty'] = 0;
				$args['hierarchical'] = false;
				$args['pad_counts'] = false;
			}
	 
			/**
			 * Filters the terms query arguments.
			 *
			 * @since 3.1.0
			 *
			 * @param array $args       An array of get_terms() arguments.
			 * @param array $taxonomies An array of taxonomies.
			 */
			$args = apply_filters( 'get_terms_args', $args, $taxonomies );
	 
			// Avoid the query if the queried parent/child_of term has no descendants.
			$child_of = $args['child_of'];
			$parent   = $args['parent'];
	 
			if ( $child_of ) {
				$_parent = $child_of;
			} elseif ( $parent ) {
				$_parent = $parent;
			} else {
				$_parent = false;
			}
	 
			if ( $_parent ) {
				$in_hierarchy = false;
				foreach ( $taxonomies as $_tax ) {
					$hierarchy = _get_term_hierarchy( $_tax );
	 
					if ( isset( $hierarchy[ $_parent ] ) ) {
						$in_hierarchy = true;
					}
				}
	 
				if ( ! $in_hierarchy ) {
					return array();
				}
			}
	 
			$orderby = $this->parse_orderby( $this->query_vars['orderby'] );
			if ( $orderby ) {
				$orderby = "ORDER BY $orderby";
			}
	 
			$order = $this->parse_order( $this->query_vars['order'] );
	 
			if ( $taxonomies ) {
				$this->sql_clauses['where']['taxonomy'] = "tt.taxonomy IN ('" . implode( "', '", array_map( 'esc_sql', $taxonomies ) ) . "')";
			}
	 
			$exclude      = $args['exclude'];
			$exclude_tree = $args['exclude_tree'];
			$include      = $args['include'];
	 
			$inclusions = '';
			if ( ! empty( $include ) ) {
				$exclude = '';
				$exclude_tree = '';
				$inclusions = implode( ',', wp_parse_id_list( $include ) );
			}
	 
			if ( ! empty( $inclusions ) ) {
				$this->sql_clauses['where']['inclusions'] = 't.term_id IN ( ' . $inclusions . ' )';
			}
	 
			$exclusions = array();
			if ( ! empty( $exclude_tree ) ) {
				$exclude_tree = wp_parse_id_list( $exclude_tree );
				$excluded_children = $exclude_tree;
				foreach ( $exclude_tree as $extrunk ) {
					$excluded_children = array_merge(
						$excluded_children,
						(array) get_terms( $taxonomies[0], array(
							'child_of' => intval( $extrunk ),
							'fields' => 'ids',
							'hide_empty' => 0
						) )
					);
				}
				$exclusions = array_merge( $excluded_children, $exclusions );
			}
	 
			if ( ! empty( $exclude ) ) {
				$exclusions = array_merge( wp_parse_id_list( $exclude ), $exclusions );
			}
	 
			// 'childless' terms are those without an entry in the flattened term hierarchy.
			$childless = (bool) $args['childless'];
			if ( $childless ) {
				foreach ( $taxonomies as $_tax ) {
					$term_hierarchy = _get_term_hierarchy( $_tax );
					$exclusions = array_merge( array_keys( $term_hierarchy ), $exclusions );
				}
			}
	 
			if ( ! empty( $exclusions ) ) {
				$exclusions = 't.term_id NOT IN (' . implode( ',', array_map( 'intval', $exclusions ) ) . ')';
			} else {
				$exclusions = '';
			}
	 
			/**
			 * Filters the terms to exclude from the terms query.
			 *
			 * @since 2.3.0
			 *
			 * @param string $exclusions `NOT IN` clause of the terms query.
			 * @param array  $args       An array of terms query arguments.
			 * @param array  $taxonomies An array of taxonomies.
			 */
			$exclusions = apply_filters( 'list_terms_exclusions', $exclusions, $args, $taxonomies );
	 
			if ( ! empty( $exclusions ) ) {
				// Must do string manipulation here for backward compatibility with filter.
				$this->sql_clauses['where']['exclusions'] = preg_replace( '/^\s*AND\s*/', '', $exclusions );
			}
	 
			if ( ! empty( $args['name'] ) ) {
				$names = (array) $args['name'];
				foreach ( $names as &$_name ) {
					// `sanitize_term_field()` returns slashed data.
					$_name = stripslashes( sanitize_term_field( 'name', $_name, 0, reset( $taxonomies ), 'db' ) );
				}
	 
				$this->sql_clauses['where']['name'] = "t.name IN ('" . implode( "', '", array_map( 'esc_sql', $names ) ) . "')";
			}
	 
			if ( ! empty( $args['slug'] ) ) {
				if ( is_array( $args['slug'] ) ) {
					$slug = array_map( 'sanitize_title', $args['slug'] );
					$this->sql_clauses['where']['slug'] = "t.slug IN ('" . implode( "', '", $slug ) . "')";
				} else {
					$slug = sanitize_title( $args['slug'] );
					$this->sql_clauses['where']['slug'] = "t.slug = '$slug'";
				}
			}
	 
			if ( ! empty( $args['term_taxonomy_id'] ) ) {
				if ( is_array( $args['term_taxonomy_id'] ) ) {
					$tt_ids = implode( ',', array_map( 'intval', $args['term_taxonomy_id'] ) );
					$this->sql_clauses['where']['term_taxonomy_id'] = "tt.term_taxonomy_id IN ({$tt_ids})";
				} else {
					$this->sql_clauses['where']['term_taxonomy_id'] = $wpdb->prepare( "tt.term_taxonomy_id = %d", $args['term_taxonomy_id'] );
				}
			}
	 
			if ( ! empty( $args['name__like'] ) ) {
				$this->sql_clauses['where']['name__like'] = $wpdb->prepare( "t.name LIKE %s", '%' . $wpdb->esc_like( $args['name__like'] ) . '%' );
			}
	 
			if ( ! empty( $args['description__like'] ) ) {
				$this->sql_clauses['where']['description__like'] = $wpdb->prepare( "tt.description LIKE %s", '%' . $wpdb->esc_like( $args['description__like'] ) . '%' );
			}
	 
			if ( '' !== $parent ) {
				$parent = (int) $parent;
				$this->sql_clauses['where']['parent'] = "tt.parent = '$parent'";
			}
	 
			$hierarchical = $args['hierarchical'];
			if ( 'count' == $args['fields'] ) {
				$hierarchical = false;
			}
			if ( $args['hide_empty'] && !$hierarchical ) {
				$this->sql_clauses['where']['count'] = 'tt.count > 0';
			}
	 
			$number = $args['number'];
			$offset = $args['offset'];
	 
			// Don't limit the query results when we have to descend the family tree.
			if ( $number && ! $hierarchical && ! $child_of && '' === $parent ) {
				if ( $offset ) {
					$limits = 'LIMIT ' . $offset . ',' . $number;
				} else {
					$limits = 'LIMIT ' . $number;
				}
			} else {
				$limits = '';
			}
	 
	 
			if ( ! empty( $args['search'] ) ) {
				$this->sql_clauses['where']['search'] = $this->get_search_sql( $args['search'] );
			}
	 
			// Meta query support.
			$join = '';
			$distinct = '';
	 
			// Reparse meta_query query_vars, in case they were modified in a 'pre_get_terms' callback.
			$this->meta_query->parse_query_vars( $this->query_vars );
			$mq_sql = $this->meta_query->get_sql( 'term', 't', 'term_id' );
			$meta_clauses = $this->meta_query->get_clauses();
	 
			if ( ! empty( $meta_clauses ) ) {
				$join .= $mq_sql['join'];
				$this->sql_clauses['where']['meta_query'] = preg_replace( '/^\s*AND\s*/', '', $mq_sql['where'] );
				$distinct .= "DISTINCT";
	 
			}
	 
			$selects = array();
			switch ( $args['fields'] ) {
				case 'all':
					$selects = array( 't.*', 'tt.*' );
					break;
				case 'ids':
				case 'id=>parent':
					$selects = array( 't.term_id', 'tt.parent', 'tt.count', 'tt.taxonomy' );
					break;
				case 'names':
					$selects = array( 't.term_id', 'tt.parent', 'tt.count', 't.name', 'tt.taxonomy' );
					break;
				case 'count':
					$orderby = '';
					$order = '';
					$selects = array( 'COUNT(*)' );
					break;
				case 'id=>name':
					$selects = array( 't.term_id', 't.name', 'tt.count', 'tt.taxonomy' );
					break;
				case 'id=>slug':
					$selects = array( 't.term_id', 't.slug', 'tt.count', 'tt.taxonomy' );
					break;
			}
	 
			$_fields = $args['fields'];
	 
			/**
			 * Filters the fields to select in the terms query.
			 *
			 * Field lists modified using this filter will only modify the term fields returned
			 * by the function when the `$fields` parameter set to 'count' or 'all'. In all other
			 * cases, the term fields in the results array will be determined by the `$fields`
			 * parameter alone.
			 *
			 * Use of this filter can result in unpredictable behavior, and is not recommended.
			 *
			 * @since 2.8.0
			 *
			 * @param array $selects    An array of fields to select for the terms query.
			 * @param array $args       An array of term query arguments.
			 * @param array $taxonomies An array of taxonomies.
			 */
			$fields = implode( ', ', apply_filters( 'get_terms_fields', $selects, $args, $taxonomies ) );
	 
			$join .= " INNER JOIN $wpdb->term_taxonomy AS tt ON t.term_id = tt.term_id";
	 
			$where = implode( ' AND ', $this->sql_clauses['where'] );
	 
			$pieces = array( 'fields', 'join', 'where', 'distinct', 'orderby', 'order', 'limits' );
	 
			/**
			 * Filters the terms query SQL clauses.
			 *
			 * @since 3.1.0
			 *
			 * @param array $pieces     Terms query SQL clauses.
			 * @param array $taxonomies An array of taxonomies.
			 * @param array $args       An array of terms query arguments.
			 */
			$clauses = apply_filters( 'terms_clauses', compact( $pieces ), $taxonomies, $args );
	 
			$fields = isset( $clauses[ 'fields' ] ) ? $clauses[ 'fields' ] : '';
			$join = isset( $clauses[ 'join' ] ) ? $clauses[ 'join' ] : '';
			$where = isset( $clauses[ 'where' ] ) ? $clauses[ 'where' ] : '';
			$distinct = isset( $clauses[ 'distinct' ] ) ? $clauses[ 'distinct' ] : '';
			$orderby = isset( $clauses[ 'orderby' ] ) ? $clauses[ 'orderby' ] : '';
			$order = isset( $clauses[ 'order' ] ) ? $clauses[ 'order' ] : '';
			$limits = isset( $clauses[ 'limits' ] ) ? $clauses[ 'limits' ] : '';
	 
			if ( $where ) {
				$where = "WHERE $where";
			}
	 
			$this->sql_clauses['select']  = "SELECT $distinct $fields";
			$this->sql_clauses['from']    = "FROM $wpdb->terms AS t $join";
			$this->sql_clauses['orderby'] = $orderby ? "$orderby $order" : '';
			$this->sql_clauses['limits']  = $limits;
	 
			$this->request = "{$this->sql_clauses['select']} {$this->sql_clauses['from']} {$where} {$this->sql_clauses['orderby']} {$this->sql_clauses['limits']}";
	 
			// $args can be anything. Only use the args defined in defaults to compute the key.
			$key = md5( serialize( wp_array_slice_assoc( $args, array_keys( $this->query_var_defaults ) ) ) . serialize( $taxonomies ) . $this->request );
			$last_changed = wp_cache_get( 'last_changed', 'terms' );
			if ( ! $last_changed ) {
				$last_changed = microtime();
				wp_cache_set( 'last_changed', $last_changed, 'terms' );
			}
			$cache_key = "get_terms:$key:$last_changed";
			$cache = wp_cache_get( $cache_key, 'terms' );
			if ( false !== $cache ) {
				if ( 'all' === $_fields ) {
					$cache = array_map( 'get_term', $cache );
				}
	 
				$this->terms = $cache;
				return $this->terms;
			}
	 
			if ( 'count' == $_fields ) {
				return $wpdb->get_var( $this->request );
			}
	 
			$terms = $wpdb->get_results( $this->request );
			if ( 'all' == $_fields ) {
				update_term_cache( $terms );
			}
	 
			// Prime termmeta cache.
			if ( $args['update_term_meta_cache'] ) {
				$term_ids = wp_list_pluck( $terms, 'term_id' );
				update_termmeta_cache( $term_ids );
			}
	 
			if ( empty( $terms ) ) {
				wp_cache_add( $cache_key, array(), 'terms', DAY_IN_SECONDS );
				return array();
			}
	 
			if ( $child_of ) {
				foreach ( $taxonomies as $_tax ) {
					$children = _get_term_hierarchy( $_tax );
					if ( ! empty( $children ) ) {
						$terms = _get_term_children( $child_of, $terms, $_tax );
					}
				}
			}
	 
			// Update term counts to include children.
			if ( $args['pad_counts'] && 'all' == $_fields ) {
				foreach ( $taxonomies as $_tax ) {
					_pad_term_counts( $terms, $_tax );
				}
			}
	 
			// Make sure we show empty categories that have children.
			if ( $hierarchical && $args['hide_empty'] && is_array( $terms ) ) {
				foreach ( $terms as $k => $term ) {
					if ( ! $term->count ) {
						$children = get_term_children( $term->term_id, $term->taxonomy );
						if ( is_array( $children ) ) {
							foreach ( $children as $child_id ) {
								$child = get_term( $child_id, $term->taxonomy );
								if ( $child->count ) {
									continue 2;
								}
							}
						}
	 
						// It really is empty.
						unset( $terms[ $k ] );
					}
				}
			}
	 
			$_terms = array();
			if ( 'id=>parent' == $_fields ) {
				foreach ( $terms as $term ) {
					$_terms[ $term->term_id ] = $term->parent;
				}
			} elseif ( 'ids' == $_fields ) {
				foreach ( $terms as $term ) {
					$_terms[] = $term->term_id;
				}
			} elseif ( 'names' == $_fields ) {
				foreach ( $terms as $term ) {
					$_terms[] = $term->name;
				}
			} elseif ( 'id=>name' == $_fields ) {
				foreach ( $terms as $term ) {
					$_terms[ $term->term_id ] = $term->name;
				}
			} elseif ( 'id=>slug' == $_fields ) {
				foreach ( $terms as $term ) {
					$_terms[ $term->term_id ] = $term->slug;
				}
			}
	 
			if ( ! empty( $_terms ) ) {
				$terms = $_terms;
			}
	 
			// Hierarchical queries are not limited, so 'offset' and 'number' must be handled now.
			if ( $hierarchical && $number && is_array( $terms ) ) {
				if ( $offset >= count( $terms ) ) {
					$terms = array();
				} else {
					$terms = array_slice( $terms, $offset, $number, true );
				}
			}
	 
			wp_cache_add( $cache_key, $terms, 'terms', DAY_IN_SECONDS );
	 
			if ( 'all' === $_fields ) {
				$terms = array_map( 'get_term', $terms );
			}
	 
			$this->terms = $terms;
			return $this->terms;
		}
	 
		/**
		 * Parse and sanitize 'orderby' keys passed to the term query.
		 *
		 * @since 4.6.0
		 * @access protected
		 *
		 * @global wpdb $wpdb WordPress database abstraction object.
		 *
		 * @param string $orderby_raw Alias for the field to order by.
		 * @return string|false Value to used in the ORDER clause. False otherwise.
		 */
		protected function parse_orderby( $orderby_raw ) {
			$_orderby = strtolower( $orderby_raw );
			$maybe_orderby_meta = false;
			if ( 'count' == $_orderby ) {
				$orderby = 'tt.count';
			} elseif ( 'name' == $_orderby ) {
				$orderby = 't.name';
			} elseif ( 'slug' == $_orderby ) {
				$orderby = 't.slug';
			} elseif ( 'include' == $_orderby && ! empty( $this->query_vars['include'] ) ) {
				$include = implode( ',', array_map( 'absint', $this->query_vars['include'] ) );
				$orderby = "FIELD( t.term_id, $include )";
			} elseif ( 'term_group' == $_orderby ) {
				$orderby = 't.term_group';
			} elseif ( 'description' == $_orderby ) {
				$orderby = 'tt.description';
			} elseif ( 'none' == $_orderby ) {
				$orderby = '';
			} elseif ( empty( $_orderby ) || 'id' == $_orderby || 'term_id' === $_orderby ) {
				$orderby = 't.term_id';
			} else {
				$orderby = 't.name';
	 
				// This may be a value of orderby related to meta.
				$maybe_orderby_meta = true;
			}
	 
			/**
			 * Filters the ORDERBY clause of the terms query.
			 *
			 * @since 2.8.0
			 *
			 * @param string $orderby    `ORDERBY` clause of the terms query.
			 * @param array  $args       An array of terms query arguments.
			 * @param array  $taxonomies An array of taxonomies.
			 */
			$orderby = apply_filters( 'get_terms_orderby', $orderby, $this->query_vars, $this->query_vars['taxonomy'] );
	 
			// Run after the 'get_terms_orderby' filter for backward compatibility.
			if ( $maybe_orderby_meta ) {
				$maybe_orderby_meta = $this->parse_orderby_meta( $_orderby );
				if ( $maybe_orderby_meta ) {
					$orderby = $maybe_orderby_meta;
				}
			}
	 
			return $orderby;
		}
	 
		/**
		 * Generate the ORDER BY clause for an 'orderby' param that is potentially related to a meta query.
		 *
		 * @since 4.6.0
		 * @access public
		 *
		 * @param string $orderby_raw Raw 'orderby' value passed to WP_Term_Query.
		 * @return string
		 */
		protected function parse_orderby_meta( $orderby_raw ) {
			$orderby = '';
	 
			// Tell the meta query to generate its SQL, so we have access to table aliases.
			$this->meta_query->get_sql( 'term', 't', 'term_id' );
			$meta_clauses = $this->meta_query->get_clauses();
			if ( ! $meta_clauses || ! $orderby_raw ) {
				return $orderby;
			}
	 
			$allowed_keys = array();
			$primary_meta_key = null;
			$primary_meta_query = reset( $meta_clauses );
			if ( ! empty( $primary_meta_query['key'] ) ) {
				$primary_meta_key = $primary_meta_query['key'];
				$allowed_keys[] = $primary_meta_key;
			}
			$allowed_keys[] = 'meta_value';
			$allowed_keys[] = 'meta_value_num';
			$allowed_keys   = array_merge( $allowed_keys, array_keys( $meta_clauses ) );
	 
			if ( ! in_array( $orderby_raw, $allowed_keys, true ) ) {
				return $orderby;
			}
	 
			switch( $orderby_raw ) {
				case $primary_meta_key:
				case 'meta_value':
					if ( ! empty( $primary_meta_query['type'] ) ) {
						$orderby = "CAST({$primary_meta_query['alias']}.meta_value AS {$primary_meta_query['cast']})";
					} else {
						$orderby = "{$primary_meta_query['alias']}.meta_value";
					}
					break;
	 
				case 'meta_value_num':
					$orderby = "{$primary_meta_query['alias']}.meta_value+0";
					break;
	 
				default:
					if ( array_key_exists( $orderby_raw, $meta_clauses ) ) {
						// $orderby corresponds to a meta_query clause.
						$meta_clause = $meta_clauses[ $orderby_raw ];
						$orderby = "CAST({$meta_clause['alias']}.meta_value AS {$meta_clause['cast']})";
					}
					break;
			}
	 
			return $orderby;
		}
	 
		/**
		 * Parse an 'order' query variable and cast it to ASC or DESC as necessary.
		 *
		 * @since 4.6.0
		 * @access protected
		 *
		 * @param string $order The 'order' query variable.
		 * @return string The sanitized 'order' query variable.
		 */
		protected function parse_order( $order ) {
			if ( ! is_string( $order ) || empty( $order ) ) {
				return 'DESC';
			}
	 
			if ( 'ASC' === strtoupper( $order ) ) {
				return 'ASC';
			} else {
				return 'DESC';
			}
		}
	 
		/**
		 * Used internally to generate a SQL string related to the 'search' parameter.
		 *
		 * @since 4.6.0
		 * @access protected
		 *
		 * @global wpdb $wpdb WordPress database abstraction object.
		 *
		 * @param string $string
		 * @return string
		 */
		protected function get_search_sql( $string ) {
			global $wpdb;
	 
			$like = '%' . $wpdb->esc_like( $string ) . '%';
	 
			return $wpdb->prepare( '((t.name LIKE %s) OR (t.slug LIKE %s))', $like, $like );
		}
	}
endif;
/**
* Alias of get_terms() functionality for lower versions of wordpress
* @link      https://developer.wordpress.org/reference/functions/get_terms/
* @version   1.0.0
*/
if(!function_exists("cf_geo_get_terms")) :
	function cf_geo_get_terms( $args = array(), $deprecated = '' ) {
		global $wpdb;
	 
		$term_query = new WP_Term_Query();
	 
		/*
		 * Legacy argument format ($taxonomy, $args) takes precedence.
		 *
		 * We detect legacy argument format by checking if
		 * (a) a second non-empty parameter is passed, or
		 * (b) the first parameter shares no keys with the default array (ie, it's a list of taxonomies)
		 */
		$_args = wp_parse_args( $args );
		$key_intersect  = array_intersect_key( $term_query->query_var_defaults, (array) $_args );
		$do_legacy_args = $deprecated || empty( $key_intersect );
	 
		if ( $do_legacy_args ) {
			$taxonomies = (array) $args;
			$args = wp_parse_args( $deprecated );
			$args['taxonomy'] = $taxonomies;
		} else {
			$args = wp_parse_args( $args );
			if ( isset( $args['taxonomy'] ) && null !== $args['taxonomy'] ) {
				$args['taxonomy'] = (array) $args['taxonomy'];
			}
		}
	 
		if ( ! empty( $args['taxonomy'] ) ) {
			foreach ( $args['taxonomy'] as $taxonomy ) {
				if ( ! taxonomy_exists( $taxonomy ) ) {
					return new WP_Error( 'invalid_taxonomy', __( 'Invalid taxonomy.' ) );
				}
			}
		}
	 
		$terms = $term_query->query( $args );
	 
		// Count queries are not filtered, for legacy reasons.
		if ( ! is_array( $terms ) ) {
			return $terms;
		}
	 
		/**
		 * Filters the found terms.
		 *
		 * @since 2.3.0
		 * @since 4.6.0 Added the `$term_query` parameter.
		 *
		 * @param array         $terms      Array of found terms.
		 * @param array         $taxonomies An array of taxonomies.
		 * @param array         $args       An array of cf_geo_get_terms() arguments.
		 * @param WP_Term_Query $term_query The WP_Term_Query object.
		 */
		return apply_filters( 'get_terms', $terms, $term_query->query_vars['taxonomy'], $term_query->query_vars, $term_query );
	}
endif;