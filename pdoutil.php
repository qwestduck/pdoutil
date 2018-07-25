<?php

/* PDOUtil 2018.07.25
 * Copyright William Panlener
 *
 * This file is part of PDOUtil.
 *
 * PDOUtil is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, version 2 of the License.
 *
 * PDOUtil is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with PDOUtil.  If not, see <https://www.gnu.org/licenses/>.
 */

require_once('stringutil.php');

/**
 * Helper to generate PDO array strings and binding parameter arrays.
 *
 * PDO does not natively support binding parameter arrays. For example,
 * the query: SELECT * FROM users WHERE user IN ('admin1', 'admin2') cannot
 * be bound directly using: SELECT * FROM users WHERE user IN (:user_array).
 *
 * PDO expects each element of the array to be represented by a unique label
 * such as: SELECT * FROM users WHERE user IN (:user1, :user2) but this requires
 * some foreknowledge of the number of items in the array.
 *
 * This class, takes a prefix and an array of data and generates this portion of
 * the query dynamically. Additionally, it generates an array that can be used to
 * bind values to the query.
 *
 * Example:
 * $prefix = 'pre'
 * $data = ('foo', 'bar')
 *
 * Yields:
 * $pdo_string => ':pre0, :pre1'
 * $pdo_params => ('pre0' => 'foo', 'pre1' => 'bar')
 */
class PDOUtil_ArrayHelper {
	private $prefix;
	private $data;

	private $pdo_string;
	private $pdo_params;

	public function __construct($prefix, $data) {
		$this->prefix = $prefix;
		$this->data = $data;

		$this->pdo_string = "";
		$this->pdo_params = array();

		$this->generate_pdo_string();
		$this->generate_pdo_params();
	}

	/**
	 * Return pdo query string.
         *
         * Example: ':pre0, :pre1'
	 *
	 * @return string
	 */
	public function get_string() {
		return $this->pdo_string;
	}

	/**
	 * Return pdo parameter bindings
         *
         * Example: ('pre0' => 'foo', 'pre1' => 'bar')
         *
         * @return array
	 */
	public function get_params() {
		return $this->pdo_params;
	}

	/**
	 * Generate a pdo query string.
	 */
	private function generate_pdo_string() {
		for($i = 0; $i < count($this->data); $i++) {
			$this->pdo_string .= ":" . $this->prefix . $i . ",";
		}

		$this->pdo_string = rtrim($this->pdo_string, ",");
	}

	/**
	 * Generate pdo parameter bindings.
	 */
	private function generate_pdo_params() {
		$i = 0;
		foreach($this->data as $value) {
			$this->pdo_params[$this->prefix . $i] = $value;
			$i++;
		}
	}
}

/**
 * Extends PDO query syntax to allow labels designated by a square bracket
 * suffix. For example: SELECT * IN(':label[]'). This syntax is used to bind
 * arrays to queries rather than the per-value binding natively provided by
 * PDO.
 */
class PDOUtil {
	private $query;
	private $params;
	private static $data;

	public function __construct($query) {
		$this->query = $query;
		$this->params = array();
	}

	/**
	 * Replace array labels in pdo query string and generate binding parameter array.
	 */
	public function finalize() {
		foreach(self::$data as $d) {
			if (strpos($this->query, $d['label']) !== false) {
				if(StringUtil::endsWith($d['label'], '[]')) {
					$prefix = substr($d['label'], 1, -2);

					$p = new PDOUtil_ArrayHelper($prefix, $d['data']);
					$this->query = str_replace($d['label'], $p->get_string(), $this->query);
					$this->params = array_merge($this->params, $p->get_params());
				} else {
					$prefix = substr($d['label'], 1);

					$this->params[$prefix] = $d['data'];
				}
			}
		}
	}

	/**
	 * Return pdo query string.
	 *
	 * @return string
	 */
	public function get_query() {
		return $this->query;
	}

	/**
	 * Return pdo parameter bindings.
	 *
	 * @return array
	 */
	public function get_params() {
		return $this->params;
	}

	/**
	 * Add data to the static data store indexed by label.
	 *
	 * @param string  $label
	 * @param array  $data
	 */
	public static function add_data($label, $data) {
		self::$data = self::$data ?? array();
	
		if(StringUtil::endsWith($label, '[]')) {
			if(!is_array($data)) {
				throw new Exception('Expected an array but received non-array.');
			}
		} elseif(!is_string($data)) {
			throw new Exception('Expected a string but received non-string.');
		}
	
		array_push(self::$data, array('label' => $label, 'data' => $data));
	}
}
?>
