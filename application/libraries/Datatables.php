<?php
/**
 * Libraries Datatables ServerSide For CodeIgniter
 * Started August 18, 2015
 * @todo Cleaning Source Code
 * @link http://github.com/hikmahtiar6/DatatablesCodeIgniterLibrary
 * @version 1.0 Beta 1
 * @author HikmahTiar <hikmahtiar.cool@gmail.com>
 * @license MIT
 *
 */
class Datatables {

	/**
	 * @var $ci for get_instance() CodeIgniter;
	 */
	protected $ci;

	/**
	 * This Function for GET QUERY
	 * @param string $table is table used
	 * @param string $select Checking > running query select
	 * @param array $join Checking > If not null , running query JOIN TABLE of $join = array()
	 * @return string $query
	 */
	private function _query_select_table($table, $select, array $join)
	{
		$this->ci =& get_instance();

		$sql = $this->ci->db;

		if($select == '')
		{
			$query = $sql->select('*');
		} 
		else
		{
			$query = $sql->select($select);	
		}
		
		$query = $sql->from($table);

		if(count($join) > 0)
		{
			foreach($join as $key_join => $val_join)
			{
				$query = $sql->join($key_join, $val_join['on'], $val_join['condition']);
			}
		}

		return $query;

	}

	/**
	 * This Function for Get value rows ALL DATA TABLE
	 * @param $table, $select, $join in function _query_select_table()
	 * @return int $num_rows
	 */
	private function _get_rows_all_data($table, $select, $join)
	{
		$this->ci =& get_instance();

		$sql = $this->ci->db;
		
		$query = $this->_query_select_table($table, $select, $join);

		$get = $query->get();

		$num_rows = $get->num_rows();
		
		//var_dump($query);

		return $num_rows;
	}

	/**
	 * This Function for Get Value Rows Filtered Data Table
	 * @param $table, $select, $join in function _query_select_table()
	 * @param array $columns > variable column used
	 * @param array $search_columns > if used customized search
	 * @var $request_search > Request string of Datatables
	 * @var $query_search > running QUERY SEARCH DATA
	 * @return int $filtered
	 */
	private function _get_rows_filter_data($table, $select, $join, array $columns, array $search_columns)
	{
		$this->ci =& get_instance();

		$sql = $this->ci->db;
		
		$query = $this->_query_select_table($table, $select, $join);

		$request_search = $_REQUEST['search']['value'];

		if($request_search != '')
		{
			$query_search = '(';
			$searching = '';

			if(count($search_columns) > 0)
			{
				foreach($columns as $key_column => $val_column)
				{
					$searching .= ' '.$val_column.' LIKE '.'"%'.$request_search.'%" OR';
				}
			}
			else
			{
				foreach($search_columns as $key_search_column)
				{
					$searching .= ' '.$key_search_column.' LIKE '.'"%'.$request_search.'%" OR';
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
	 * This Function for Get Value Rows Filtered Data Table used
	 * @param $table, $select, $join in function _query_select_table()
	 * @param array $columns > variable column used
	 * @param array $search_columns > if used customized search
	 * @var $request_search > Request string of Datatables
	 * @var $query_search > running QUERY SEARCH DATA
	 * @var order_table > available Request Order of Datatables
	 * @return Array
	 */
	private function _get_rows_filter_order_limit_data($table, $select, $join, $columns, $search_columns)
	{
		$this->ci =& get_instance();

		$sql = $this->ci->db;
		
		$query = $this->_query_select_table($table, $select, $join);

		$request_search = $_REQUEST['search']['value'];

		if($request_search != '')
		{
			$query_search = '(';
			$searching = '';

			if($search_columns == '')
			{
				foreach($columns as $key_column => $val_column)
				{
					$searching .= ' '.$val_column.' LIKE '.'"%'.$request_search.'%" OR';
				}
			}
			else
			{
				foreach($search_columns as $key_search_column)
				{
					$searching .= ' '.$key_search_column.' LIKE '.'"%'.$request_search.'%" OR';
				}
				
			}

			$query_search .= rtrim($searching, 'OR');
			$query_search .= ')';
			$query = $sql->where($query_search);
		}
				
		$query->limit($_REQUEST['length'] , $_REQUEST['start']);

		$order_column = $_REQUEST['columns'];
		$order_table = $_REQUEST['order'];

        foreach($order_table as $val_order)
        {
			$order__ = $_REQUEST['columns'][$val_order['column']]['data'];
            $query->order_by($order__ , $val_order['dir']);
        }

		$get = $query->get();

		return $get->result_array();
	}

	/**
	 * This Function for Get Value Rows Filtered Data Table used
	 * @param $table, $select, $join in function _query_select_table()
	 * @var $rows_all > int num_rows all data
	 * @var $filtered > int num_rows filtered data
	 * @var $get > array for JSON of Datatables ServerSide 
	 * @return Array
	 */
	public function generate($table, $select= '', $join = '', $columns, $search_columns = '', $search_custom = '')
	{
		$this->ci =& get_instance();

		$sql = $this->ci->db;

		$rows_all = $this->_get_rows_all_data($table, $select, $join);

		$filtered = $this->_get_rows_filter_data($table, $select, $join, $columns, $search_columns);

		$get = $this->_get_rows_filter_order_limit_data($table, $select, $join, $columns, $search_columns);

		$data = [];

		foreach($get as $row)
		{
			$dt = [];

			foreach($columns as $key_column => $val_column)
			{
				if(strpos($val_column, '.') !== FALSE)
				{
					$cl = explode('.', $val_column);
					$dt[$key_column] = [
						$row[$cl[1]]
					];
				}
				else
				{
					$dt[$key_column] = [
						$row[$val_column]
					];
				}
			}

			$data[] = $dt;
		}

		$json = [

			'draw' => intval($_REQUEST['draw']),
			'recordsTotal' => intval($rows_all),
			'recordsFiltered' => intval($filtered),
			'data' => $data

		];

		return $json;
	}
}