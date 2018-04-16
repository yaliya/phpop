<?php 

namespace App\Providers;

class Database
{
	private $query = '';

	private $table = '';

	private $values = [];

	private $relations = [

		'withMany' => [], 

		'withOne' => []];

	private static $instance = NULL;

	private static $connection = NULL;

	public static function init($host, $database, $username, $password)
	{
		if(!self::$instance) {
		
			self::$instance = new self();

			$conn = new \PDO('mysql:host='.$host.';dbname='.$database.';charset=utf8', $username, $password);

			$conn->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

			self::$connection = $conn;
		}

		return self::$instance;
	}

	public function raw($query, $values = [])
	{
		$this->query = $query . ' ';

		$this->values = $values;

		return $this;
	}

	public function whereHas($relation, $callback)
	{
		$query = @end(explode('(', $this->query));

		if(strpos($query, 'WHERE EXISTS') !== false) {

			$this->query .= 'AND EXISTS ( SELECT 1 FROM ' . $relation . ' ';
		}
		else {

			$this->query .= 'WHERE EXISTS ( SELECT 1 FROM ' . $relation . ' ';
		}

		call_user_func_array($callback, [$this]);

		$this->query .= ' ) ';

		return $this;
	}

	public function query()
	{
		return new self();
	}

	public function select($fields = "*") 
	{
		$this->query .= 'SELECT ' . $fields . ' ';

		return $this;
	}

	public function from($from) 
	{
		$this->table = $from;

		$this->query .= 'FROM ' . $from . ' ';

		return $this;
	}

	public function append($query, $values = [])
	{
		$this->query .= $query;

		$this->values = array_merge($this->values, $values);

		return $this;
	}

	public function appendAs($callback)
	{
		call_user_func_array($callback, [$this]);

		return $this;
	}

	public function where($field, $value, $plain = false)
	{
		$query = @end(explode('(', $this->query));

		if(strpos($query, 'WHERE') !== false) {
			
			if(!$plain) {

				$this->query .= 'AND ' . $field . '=:' . $field . ' ';

				$this->values[':'.$field] = $value;
			}

			else {

				$this->query .= 'AND ' . $field . '=' . $value . ' ';
			}
		}
		else {

			if(!$plain) {

				$this->query .= 'WHERE ' . $field . '=:' . $field . ' ';

				$this->values[':'.$field] = $value;
			}
			else {

				$this->query .= 'WHERE ' . $field . '=' . $value . ' ';
			}
		}

		return $this;
	}

	public function join($table)
	{
		$this->query .= 'JOIN ' . $table . ' ON ' . $this->table;

		$this->query .= '.' .'id' . '=' . $table . '.' . $this->table.'_id ';

		return $this;
	}

	public function withMany($with, $rel = [])
	{
		$this->relations['withMany'][] = ['table' => $with, 'attributes' => $rel];

		return $this;
	}

	public function withOne($with, $rel = [])
	{
		$this->relations['withOne'][] = ['table' => $with, 'attributes' => $rel];

		return $this;
	}

	private function parseRelations($data)
	{
		foreach($this->relations['withMany'] as $relation) {

			$query = 'SELECT * FROM ' . $relation['table'] . ' WHERE ' . $this->table . '_id = :id';

			$stmt = self::$connection->prepare($query);

			$stmt->execute([':id' => $data['id']]);

			$attribute = isset($relation['attributes']['as']) ? $relation['attributes']['as'] : $relation['table'];

			$callback = isset($relation['attributes']['transform']) ? $relation['attributes']['transform'] : $stmt->fetchAll();

			if(is_callable($callback)) {

				while($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {

					$data[$attribute][] = call_user_func_array($callback, [json_decode(json_encode($row, true))]);
				}
			}
			else {

				$data[$attribute] = $callback;
			}
		}

		foreach($this->relations['withOne'] as $relation) {

			$query = 'SELECT * FROM ' . $relation['table'] . ' WHERE ' . $relation['table'] .'.id = :id';

			$stmt = self::$connection->prepare($query);

			$stmt->execute([':id' => $data[$relation['table'].'_id']]);

			$attribute = isset($relation['attributes']['as']) ? $relation['attributes']['as'] : $relation['table'];

			$callback = isset($relation['attributes']['transform']) ? $relation['attributes']['transform'] : $stmt->fetch(\PDO::FETCH_ASSOC);

			if(is_callable($callback)) {

				$data[$attribute] = call_user_func_array($callback, [json_decode(json_encode($stmt->fetch(\PDO::FETCH_ASSOC), true))]);
			}
			else {

				$data[$attribute] = $callback;
			}
		}

		return $data;
	}

	public function get($tcallback = NULL)
	{
		$stmt = self::$connection->prepare($this->query);

		$stmt->execute($this->values);

		$rdata = [];

		foreach($stmt->fetchAll(\PDO::FETCH_ASSOC) as $data) {

			$data = $this->parseRelations($data);

			if(is_callable($tcallback)) {

				$args = [];

				$args[] = $this;

				$args[] = json_decode(json_encode($data, true));

				$rdata[] = call_user_func_array($tcallback, $args);
			}
			else {

				$rdata[] = $data;	
			}
		}

		return $rdata;
	}

	public function first($tcallback = NULL) {

		$stmt = self::$connection->prepare($this->query);

		$stmt->execute($this->values);

		$data = $stmt->fetch(\PDO::FETCH_ASSOC);

		$data = $this->parseRelations($data);

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

	public function sql()
	{
		return $this->query;
	}
}
