<?php 

namespace App\Providers;

class Database
{

	private $query = '';

	private $table = '';

	private $values = [];

	private $connection = NULL;

	public function __construct($host, $database, $username, $password)
	{
		$this->connection = new \PDO('mysql:host='.$host.';dbname='.$database.';charset=utf8', $username, $password);

		$this->connection->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
	}

	public function raw($query, $values = [])
	{
		$this->query = $query . ' ';

		$this->values = $values;

		return $this;
	}

	public function query()
	{
		$this->table = '';

		$this->query = '';

		$this->values = [];

		return $this;
	}

	public function select($fields = "*") 
	{
		return $this->raw('SELECT ' . $fields);
	}

	public function from($from) 
	{
		$this->table = $from;

		$this->query .= 'FROM ' . $from . ' ';

		return $this;
	}

	public function insert($data)
	{
		$this->values = $data; 

		$this->query = 'INSERT INTO table (';

		$this->query .= implode(',', array_keys($this->values));

		$this->query .= ') VALUES (';

		$values = [];

		foreach($this->values as $param => $value) {

			$values[] = ':' . $param;
		}

		$this->query .= implode(',', $values);

		$this->query .= ')';

		return $this;
	}

	public function into($table, $callback = NULL)
	{
		$this->table = $table;

		$this->query = str_replace('table', $table, $this->query);

		return $this->first($callback);
	}

	public function save($data)
	{
		$this->values = $data;

		$this->query = 'UPDATE table SET ';

		foreach($this->values as $param => $value) {

			$this->query .= $param . '=' . ':' . $param;
		}

		$this->query .= ' ';

		return $this;
	}

	public function on($table, $callback = NULL)
	{
		$this->table = $table;

		$this->query = str_replace('table', $table, $this->query);

		$stmt = $this->connection->prepare($this->query);

		$stmt->execute($this->values);

		return $stmt->rowCount();
	}

	public function append($query, $values = [])
	{
		if(is_callable($query)) {

			call_user_func_array($query, [$this]);

			return $this;
		}

		$this->query .= $query . ' ';

		$this->values = array_merge($this->values, $values);

		return $this;
	}

	public function where($field, $value)
	{
		$plain = false;

		$operator = '=';

		if(func_num_args() == 2) {

			list($field, $value) = func_get_args();
		}

		if(func_num_args() == 3) {

			list($field, $operator, $value) = func_get_args();
		}

		if(func_num_args() == 4) {


			list($field, $operator, $value, $plain) = func_get_args();
		}

		$query = @end(explode('( SELECT', $this->query));

		if(strpos($query, 'WHERE') !== false) {
			
			if(!$plain) {

				// $this->query .= 'AND ' . $this->table . '.' . $field . ' ' . $operator . ' ' . ':' . $field . ' ';

				if(array_key_exists(':'.$field, $this->values)) {

					$attrib = $field . '_' . count($this->values);

					$this->values[$attrib] = $value;

					$this->query .= 'AND ' . $field . ' ' . $operator . ' ' . ':' . $attrib . ' ';
				}
				else {

					$this->values[':'.$field] = $value;

					$this->query .= 'AND ' . $field . ' ' . $operator . ' ' . ':' . $field . ' ';
				}
			}

			else {

				$this->query .= 'AND ' . $field . ' ' . $operator . ' ' . $value . ' ';
			}
		}
		else {

			if(!$plain) {

				$this->query .= 'WHERE ' . $field . ' '. $operator . ' ' . ':' . $field . ' ';

				$this->values[':'.$field] = $value;
			}
			else {

				$this->query .= 'WHERE ' . $field . ' ' . $operator . ' ' . $value . ' ';
			}
		}

		return $this;
	}

	public function orWhere($field, $value)
	{
		$operator = '=';

		if(func_num_args() == 2) {

			list($field, $value) = func_get_args();
		}

		if(func_num_args() == 3) {

			list($field, $operator, $value) = func_get_args();
		}


		$query = @end(explode('(', $this->query));

		if(strpos($query, 'WHERE') !== false) {

			if(array_key_exists(':'.$field, $this->values)) {

				$attrib = $field . '_' . count($this->values);

				$this->values[$attrib] = $value;

				$this->query .= 'OR ' . $field . ' ' . $operator . ' ' . ':' . $attrib . ' ';
			}
			else {

				$this->values[':'.$field] = $value;

				$this->query .= 'OR ' . $field . ' ' . $operator . ' ' . ':' . $field . ' ';
			}
		}
		else {

			$this->query .= 'WHERE ' . $field . ' '. $operator . ' ' . ':' . $field . ' ';

			$this->values[':'.$field] = $value;

		}

		return $this;
	}


	public function whereHas($relation, $callback)
	{
		$this->table = explode('.', $relation)[0];

		if(count(explode('WHERE', $this->query)) > 1 || count(explode('WHERE EXISTS (', $this->query)) > 1) {

			$this->query .= 'AND EXISTS ( SELECT 1 FROM ' . $relation . ' ';
		}
		else {

			$this->query .= 'WHERE EXISTS ( SELECT 1 FROM ' . $relation . ' ';
		}

		call_user_func_array($callback, [$this]);

		$this->query .= ' ) ';

		return $this;
	}
	
	public function orWhereHas($relation, $callback)
	{
		$this->table = explode('.', $relation)[0];

		if(count(explode('WHERE', $this->query)) > 1 || count(explode('WHERE EXISTS (', $this->query)) > 1) {

			$this->query .= 'OR EXISTS ( SELECT 1 FROM ' . $relation . ' ';
		}
		else {

			$this->query .= 'WHERE EXISTS ( SELECT 1 FROM ' . $relation . ' ';
		}

		call_user_func_array($callback, [$this]);

		$this->query .= ' ) ';

		return $this;
	}

	public function whereHasNot($relation, $callback)
	{
		$this->table = explode('.', $relation)[0];

		if(count(explode('WHERE', $this->query)) > 1 || count(explode('WHERE NOT EXISTS (', $this->query)) > 1) {

			$this->query .= 'AND NOT EXISTS ( SELECT 1 FROM ' . $relation . ' ';
		}
		else {

			$this->query .= 'WHERE NOT EXISTS ( SELECT 1 FROM ' . $relation . ' ';
		}

		call_user_func_array($callback, [$this]);

		$this->query .= ' ) ';

		return $this;
	}
	
	public function orWhereHasNot($relation, $callback)
	{
		$this->table = explode('.', $relation)[0];

		if(count(explode('WHERE', $this->query)) > 1 || count(explode('WHERE NOT EXISTS (', $this->query)) > 1) {

			$this->query .= 'OR NOT EXISTS ( SELECT 1 FROM ' . $relation . ' ';
		}
		else {

			$this->query .= 'WHERE NOT EXISTS ( SELECT 1 FROM ' . $relation . ' ';
		}

		call_user_func_array($callback, [$this]);

		$this->query .= ' ) ';

		return $this;
	}

	public function whereIn($attribute, $values)
	{
		$subsets = explode('( SELECT', $this->query);

		$query = @end($subsets);

		if(strpos($query, ')') !== false) {

			$endsets = explode(')', $query);

			$query = $subsets[count($endsets) - count($endsets)];

			if(strpos($query, 'WHERE') !== false) {
				
				$this->query .= 'AND ( ';
			}

			else {

				$this->query .= 'WHERE ( ';
			}	
		}
		else {

			//Subquery
				
			if(strpos($query, 'WHERE') !== false) {
				
				$this->query .= 'AND ( ';
			}

			else {

				$this->query .= 'WHERE ( ';
			}
		}

		$index = 0;

		$operator = '=';

		if(func_num_args() == 2) {

			list($attribute, $values) = func_get_args();
		}

		if(func_num_args() == 3) {

			list($attributes, $operator, $values) = func_get_args();
		}

		//Begin loop values
		foreach($values as $value) {

			//:attribute_0, :attribute_1, :attribute_2
			$param = $attribute . '_' . $index;

			if(array_key_exists(':'.$param, $this->values)) {
				
				$param = $param . '_' . count($this->values);

				$this->values[':'.$param] = $value;
			}
			else {

				$this->values[':'.$param] = $value;
			}

			//append to query attribute = :attribute_0
			$this->query .= $attribute . ' ' . $operator . ' ' . ':' . $param;
			//If not in end of array
			if($index != count($values) - 1) {
				//Appends OR to query
				$this->query .= ' OR ';
			}

			//Index
			$index++;
		}

		//Close query
		$this->query .= ') ';

		return $this;
	}
	
	public function orWhereIn($attribute, $values)
	{
		$subsets = explode('( SELECT', $this->query);

		$query = @end($subsets);

		if(strpos($query, ')') !== false) {

			$endsets = explode(')', $query);

			$query = $subsets[count($endsets) - count($endsets)];

			if(strpos($query, 'WHERE') !== false) {
				
				$this->query .= 'OR ( ';
			}

			else {

				$this->query .= 'WHERE ( ';
			}	
		}
		else {

			//Subquery
				
			if(strpos($query, 'WHERE') !== false) {
				
				$this->query .= 'OR ( ';
			}

			else {

				$this->query .= 'WHERE ( ';
			}
		}

		$index = 0;

		$operator = '=';

		if(func_num_args() == 2) {

			list($attribute, $values) = func_get_args();
		}

		if(func_num_args() == 3) {

			list($attributes, $operator, $values) = func_get_args();
		}

		//Begin loop values
		foreach($values as $value) {

			//:attribute_0, :attribute_1, :attribute_2
			$param = $attribute . '_' . $index;

			if(array_key_exists(':'.$param, $this->values)) {
				
				$param = $param . '_' . count($this->values);

				$this->values[':'.$param] = $value;
			}
			else {

				$this->values[':'.$param] = $value;
			}

			//append to query attribute = :attribute_0
			$this->query .= $attribute . ' ' . $operator . ' ' . ':' . $param;
			//If not in end of array
			if($index != count($values) - 1) {
				//Appends OR to query
				$this->query .= ' OR ';
			}

			//Index
			$index++;
		}

		//Close query
		$this->query .= ') ';

		return $this;
	}
	
	public function whereNotIn($attribute, $values)
	{
		$subsets = explode('( SELECT', $this->query);

		$query = @end($subsets);

		if(strpos($query, ')') !== false) {

			$endsets = explode(')', $query);

			$query = $subsets[count($endsets) - count($endsets)];

			if(strpos($query, 'WHERE') !== false) {
				
				$this->query .= 'AND (';
			}

			else {

				$this->query .= 'WHERE (';
			}	
		}
		else {

			//Subquery
			if(strpos($query, 'WHERE') !== false) {
				
				$this->query .= 'AND (';
			}

			else {

				$this->query .= 'WHERE (';
			}
		}

		$index = 0;

		$operator = '!=';

		if(func_num_args() == 2) {

			list($attribute, $values) = func_get_args();
		}

		//Begin loop values
		foreach($values as $value) {

			//:attribute_0, :attribute_1, :attribute_2
			$param = $attribute . '_' . $index;

			if(array_key_exists(':'.$param, $this->values)) {
				
				$param = $param . '_' . count($this->values);

				$this->values[':'.$param] = $value;
			}
			else {

				$this->values[':'.$param] = $value;
			}

			// //append to query attribute = :attribute_0
			// $this->query .= ':' . $param;
			// //If not in end of array
			// if($index != count($values) - 1) {
			// 	//Appends OR to query
			// 	$this->query .= ',';
			// }
			//append to query attribute = :attribute_0
			$this->query .= $attribute . ' ' . $operator . ' ' . ':' . $param;
			//If not in end of array
			if($index != count($values) - 1) {
				//Appends OR to query
				$this->query .= ' AND ';
			}

			//Index
			$index++;
		}

		//Close query
		$this->query .= ') ';

		return $this;
	}

	public function whereRaw($query, $values = [])
	{
		$qq = @end(explode('(', $this->query));

		if(strpos($qq, 'WHERE') !== false) {

			$this->query .= 'AND ' . $query . ' ';
		}
		else {

			$this->query .= 'WHERE ' . $query . ' ';
		}

		foreach($values as $key => $value) {

			if(array_key_exists($key, $this->values)) {

				$attrib = str_replace(':', '', $key);

				$attrib = ':'.count($this->values).$attrib;

				$this->values[$attrib] = $value;
			}
			else {

				$this->values[$key] = $value;
			}
		}

		return $this;
	}

	public function orderBy($attrib, $value)
	{
		$this->query .= 'ORDER BY ' . $attrib . ' ' . $value . ' ';

		return $this;
	}

	public function limit($start, $end = NULL) {

		$this->query .= 'LIMIT ' . $start . ' ';

		if($end) $this->query .= 'OFFSET ' . $end;

		return $this;
	}

	public function count()
	{
		$query = str_replace('SELECT *', 'SELECT COUNT("*")', $this->query);

		$stmt = $this->connection->prepare($query);

		$stmt->execute($this->values);

		return (int) $stmt->fetchColumn();
	}

	public function get($tcallback = NULL)
	{
		$stmt = $this->connection->prepare($this->query);

		$stmt->execute($this->values);

		$rdata = [];
		
		foreach($stmt->fetchAll(\PDO::FETCH_ASSOC) as $data) {

			if(is_callable($tcallback)) {

				$args = [];

				$args[] = $this;

				$args[] = json_decode(json_encode($data, true));

				$args[] = $stmt->rowCount();

				$rdata[] = call_user_func_array($tcallback, $args);
			}
			else {

				$rdata[] = $data;	
			}
		}

		return $rdata;
	}

	public function first($tcallback = NULL) {

		$stmt = $this->connection->prepare($this->query);

		$stmt->execute($this->values);

		$data = null;

		if($stmt->rowCount() > 0) {

			if(strpos($this->query, 'SELECT') !== false) {

				$data = $stmt->fetch(\PDO::FETCH_ASSOC);
			}

			else if(strpos($this->query, 'INSERT INTO') !== false) {

				$this->values = [];

				$this->query = 'SELECT * FROM ' . $this->table . ' WHERE id=:id';

				$this->values[':id'] = $this->connection->lastInsertId();

				return $this->first($tcallback);
			}

			if(is_callable($tcallback)) {

				$args = [];

				$args[] = $this;

				$args[] = json_decode(json_encode($data, true));

				return call_user_func_array($tcallback, $args);
			}
			else {

				return $data;	
			}
		}

		return NULL;
	}

	public function sql() {

		return $this->query;
	}
}
