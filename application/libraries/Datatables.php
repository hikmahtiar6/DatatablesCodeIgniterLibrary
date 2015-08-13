<?php
/**
 * Libraries Datatables ServerSide Simple For CodeIgniter
 * August 2015
 * @link http://github.com/hikmahtiar6/DatatablesCodeIgniter
 * @version 1.0.0
 * @author HikmahTiar <hikmahtiar.cool@gmail.com>
 * @license HT --- http://hikmahtiar6.github.io
 */
class Datatables {
	public $ci;

	public function simple($table, $select = '', $columns, $search_column)
	{
		$this->ci =& get_instance();
		$request = $_REQUEST;
		// $sql is variable $this->db
		// then, $sql used for functions that use $this->db ($this->db replaced with $sql)
		$sql = $this->ci->db;

		$no = 0;


		// get num_rows all data in table used
		// get filtered on data showing
		$rows_all = $sql->count_all_results($table);
		$filtered = $rows_all;

		// QUERY 1
		($select == '') ? $query = $sql->select('*') :  $query = $sql->select($select);
		$query = $sql->from('test');
		$query = $sql->where('1=1');

		if($request['search']['value'] != '')
		{
			$query_search = '(';
			$searching = '';

			foreach($search_column as $col)
			{
				$searching .= ' '.$col.' LIKE '.'"%'.$request['search']['value'].'%" OR';
			}
			$query_search .= rtrim($searching, 'OR');
			$query_search .= ')';
			$query = $sql->where($query_search);
		}

		$get = $query->get();
		$filtered = $get->num_rows();

		// QUERY2
		($select == '') ? $query2 = $sql->select('*') :  $query2 = $sql->select($select);
		$query2 = $sql->from('test');
		$query2 = $sql->where('1=1'); 
				
		if($request['search']['value'] != '')
		{
			$query_search = '(';
			$searching = '';
			foreach($search_column as $col)
			{
				$searching .= ' '.$col.' LIKE '.'"%'.$request['search']['value'].'%" OR';
			}
			$query_search .= rtrim($searching, 'OR');
			$query_search .= ')';
			$query2 = $sql->where($query_search);
		}
		$query2->limit($request['length'] , $request['start']);
		$query2->order_by($columns[$request['order'][0]['column']] , $request['order'][0]['dir']);
		$get = $query2->get();

		$data = [];

		foreach($get->result_array() as $row)
		{
			$dt = [];

			$dt[] = $no + ($request['start'] + 1);
			foreach($columns as $col)
			{
				$dt[] = $row[$col];
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

	public function simple_dt()
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
}