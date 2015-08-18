<?php
/**
 * Libraries Datatables ServerSide For CodeIgniter
 * August 2015
 * @link http://github.com/hikmahtiar6/DatatablesCodeIgniterLibrary
 * @version 0.2
 * @author HikmahTiar <hikmahtiar.cool@gmail.com>
 * @license MIT --- http://hikmahtiar6.github.io
 */
class Datatables {
	protected $ci;

	/**
	 * This function for get recored one table
	 * @param num_rows int
	 */
	private function _get_rows_all($table, $select)
	{
		$this->ci =& get_instance();

		$sql = $this->ci->db;

		($select == '') ? $query = $sql->select('*') :  $query = $sql->select($select);	
		$query = $sql->from($table);
		$get = $sql->get();
		$num_rows = $get->num_rows();

		return $num_rows;
	}

	/**
	 * This function for get record join table
	 * @param num_rows int
	 */
	private function _get_rows_all_join($table, $select, $join)
	{
		$this->ci =& get_instance();

		$sql = $this->ci->db;

		($select == '') ? $query = $sql->select('*') :  $query = $sql->select($select);	
		$query = $sql->from($table);

		foreach($join as $joined => $val)
		{
			$query = $sql->join($joined, $val['on'], $val['condition']);
		}

		$get = $sql->get();
		$num_rows = $get->num_rows();

		return $num_rows;
	}

	/**
	 * This function get filter data one table
	 * @param num_rows int
	 */
	private function _get_filter($table, $select, $columns, $search_column)
	{
		$this->ci =& get_instance();

		$sql = $this->ci->db;

		$request = $_REQUEST;

		($select == '') ? $query = $sql->select('*') :  $query = $sql->select($select);
		$query = $sql->from($table);
		//$query = $sql->where('1=1');

		if($request['search']['value'] != '')
		{
			$query_search = '(';
			$searching = '';

			if($search_column == '')
			{
				foreach($columns as $col => $col_val)
				{
					$searching .= ' '.$col_val.' LIKE '.'"%'.$request['search']['value'].'%" OR';
				}
			}
			else
			{
				foreach($search_column as $col)
				{
					$searching .= ' '.$col.' LIKE '.'"%'.$request['search']['value'].'%" OR';
				}
				
			}

			$query_search .= rtrim($searching, 'OR');
			$query_search .= ')';
			$query = $sql->where($query_search);
		}

		$get = $query->get();
		$filtered = $get->num_rows();

		return $filtered;
	}

	/**
	 * This function for get filter data JOIN table
	 * @param num_rows int
	 */
	private function _get_filter_join($table, $select, $columns, $search_column, $join)
	{
		$this->ci =& get_instance();

		$sql = $this->ci->db;

		$request = $_REQUEST;

		($select == '') ? $query = $sql->select('*') :  $query = $sql->select($select);
		$query = $sql->from($table);

		foreach($join as $joined => $val)
		{
			$query = $sql->join($joined, $val['on'], $val['condition']);
		}

		//$query = $sql->where('1=1');

		if($request['search']['value'] != '')
		{
			$query_search = '(';
			$searching = '';

			if($search_column == '')
			{
				foreach($columns as $col => $col_val)
				{
					$searching .= ' '.$col_val.' LIKE '.'"%'.$request['search']['value'].'%" OR';
				}
			}
			else
			{
				foreach($search_column as $col)
				{
					$searching .= ' '.$col.' LIKE '.'"%'.$request['search']['value'].'%" OR';
				}
				
			}

			$query_search .= rtrim($searching, 'OR');
			$query_search .= ')';
			$query = $sql->where($query_search);
		}

		$get = $query->get();
		$filtered = $get->num_rows();

		return $filtered;
	}

	/**
	 * This function for get data one table
	 * @param data Array
	 */
	private function _get_data($table, $select, $columns, $search_column)
	{
		$this->ci =& get_instance();

		$sql = $this->ci->db;

		$request = $_REQUEST;

		($select == '') ? $query2 = $sql->select('*') :  $query2 = $sql->select($select);
		$query2 = $sql->from($table);
		//$query2 = $sql->where('1=1');

		if($request['search']['value'] != '')
		{
			$query_search = '(';
			$searching = '';

			if($search_column == '')
			{
				foreach($columns as $col => $col_val)
				{
					$searching .= ' '.$col_val.' LIKE '.'"%'.$request['search']['value'].'%" OR';
				}
			}
			else
			{
				foreach($search_column as $col)
				{
					$searching .= ' '.$col.' LIKE '.'"%'.$request['search']['value'].'%" OR';
				}
				
			}

			$query_search .= rtrim($searching, 'OR');
			$query_search .= ')';
			$query2 = $sql->where($query_search);
		}
				
		$query2->limit($request['length'] , $request['start']);
		$query2->order_by($columns[$request['order'][0]['column']] , $request['order'][0]['dir']);
		$get = $query2->get();

		return $get->result_array();
	}

	/**
	 * This function for get data one table
	 * @param data Array
	 */
	private function _get_data_join($table, $select, $columns, $search_column, $join)
	{
		$this->ci =& get_instance();

		$sql = $this->ci->db;

		$request = $_REQUEST;

		($select == '') ? $query2 = $sql->select('*') :  $query2 = $sql->select($select);
		$query2 = $sql->from($table);

		foreach($join as $joined => $val)
		{
			$query2 = $sql->join($joined, $val['on'], $val['condition']);
		}

		//$query2 = $sql->where('1=1'); 

		if($request['search']['value'] != '')
		{
			$query_search = '(';
			$searching = '';

			if($search_column == '')
			{
				foreach($columns as $col => $col_val)
				{
					$searching .= ' '.$col_val.' LIKE '.'"%'.$request['search']['value'].'%" OR';
				}
			}
			else
			{
				foreach($search_column as $col)
				{
					$searching .= ' '.$col.' LIKE '.'"%'.$request['search']['value'].'%" OR';
				}
				
			}

			$query_search .= rtrim($searching, 'OR');
			$query_search .= ')';
			$query2 = $sql->where($query_search);
		}
		
		$query2->limit($request['length'] , $request['start']);
		$query2->order_by($columns[$request['order'][0]['column']] , $request['order'][0]['dir']);
		$get = $query2->get();

		return $get->result_array();
	}

	/**
	 * This function for generate Datatables
	 */
	public function generate($table, $select = '', $columns, $search_column = '', $search_custom = '',  $join = '')
	{
		$this->ci =& get_instance();

		// $sql is variable $this->db
		// then, $sql used for functions that use $this->db ($this->db replaced with $sql)
		$sql = $this->ci->db;

		$request = $_REQUEST;

		// get num_rows all data in table used
		// get filtered on data showing

		if($join == '')
		{
			$rows_all = $this->_get_rows_all($table, $select);
			if($search_custom == '')
			{
				$filtered = $this->_get_filter($table, $select, $columns, $search_column, $search_custom);
				$get = $this->_get_data($table, $select, $columns, $search_column, $search_custom);
			}
			else
			{
				
				$filtered = $search_custom['filtered'];
				$get = $search_custom['get'];
				
			}
		} 
		else
		{
			$rows_all = $this->_get_rows_all_join($table, $select, $join); 
			if($search_custom == '')
			{
				$filtered = $this->_get_filter_join($table, $select, $columns, $search_column, $search_custom, $join);
				$get = $this->_get_data_join($table, $select, $columns, $search_column, $search_custom, $join);
			}
			else
			{
				
				$filtered = $search_custom['filtered'];
				$get = $search_custom['get'];
				
			}
		}

		$data = [];

		foreach($get as $row)
		{
			$dt = [];

			foreach($columns as $col)
			{
				if(strpos($col, '.') !== FALSE)
				{
					$cl = explode('.', $col);
					$dt[] = [
						$row[$cl[1]]
					];
				}
				else
				{
					$dt[] = [
						$row[$col]
					];
				}
			}

			$data[] = $dt;
		}

		$json = [

			'draw' => intval($request['draw']),
			'recordsTotal' => intval($rows_all),
			'recordsFiltered' => intval($filtered),
			'data' => $data

		];

		return $json;
	}

	/**
	 * This function for generate with Column Number Datatables
	 */
	public function generate_with_numbering($table, $select = '', $columns, $search_column = '', $search_custom = '',  $join = '')
	{
		$this->ci =& get_instance();

		// $sql is variable $this->db
		// then, $sql used for functions that use $this->db ($this->db replaced with $sql)
		$sql = $this->ci->db;

		$request = $_REQUEST;

		$no = 0;
		// get num_rows all data in table used
		// get filtered on data showing

		if($join == '')
		{
			$rows_all = $this->_get_rows_all($table, $select);
			if($search_custom == '')
			{
				$filtered = $this->_get_filter($table, $select, $columns, $search_column, $search_custom);
				$get = $this->_get_data($table, $select, $columns, $search_column, $search_custom);
			}
			else
			{
				$filtered = $search_custom['filtered'];
				$get = $search_custom['get'];
			}
		} 
		else
		{
			$rows_all = $this->_get_rows_all_join($table, $select, $join); 
			if($search_custom == '')
			{
				$filtered = $this->_get_filter_join($table, $select, $columns, $search_column, $search_custom, $join);
				$get = $this->_get_data_join($table, $select, $columns, $search_column, $search_custom, $join);
			}
			else
			{
				$filtered = $search_custom['filtered'];
				$get = $search_custom['get'];
				
			}
		}

		$data = [];

		foreach($get as $row)
		{
			$dt = [];

			$dt[] = $no + ($request['start'] + 1);
			foreach($columns as $col)
			{
				if(strpos($col, '.') !== FALSE)
				{
					$cl = explode('.', $col);
					$dt[] = [
						$row[$cl[1]]
					];
				}
				else
				{
					$dt[] = [
						$row[$col]
					];
				}
			}

			$no++;
			$data[] = $dt;
		}

		$json = [

			'draw' => intval($request['draw']),
			'recordsTotal' => intval($rows_all),
			'recordsFiltered' => intval($filtered),
			'data' => $data

		];

		return $json;
	}

	/*public function simple_dt()
	{
		$qq = 'SELECT * FROM test WHERE 1=1 ';

		if($request['search']['value'] != '')
		{
			$qq .= ' AND ( ';
			$hasil = '';
			foreach($column as $col)
			{
				$hasil .= rtrim(' '.$col.' LIKE '.'"%'.$request['search']['value'].'%" OR ');
			}
			$qq .= rtrim($hasil, 'OR');
			$qq .= ')';
		}


		$get = $sql->query($qq);
		$filtered = $get->num_rows();
		if($request['order'][0]['column'] > 0)
		{
			$qq .= 'ORDER BY '.$column[$request['order'][0]['column']].' '.$request['order'][0]['dir'];
		}

		$qq .= ' LIMIT '.$request['start'].' , '.$request['length'] ;

		$get = $sql->query($qq);
	}
	*/
}