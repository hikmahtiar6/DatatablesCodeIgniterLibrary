<?php
/**
 * Libraries Datatables ServerSide For CodeIgniter
 * Started August 18, 2015
 * @link https://github.com/hikmahtiar6
 * @version 1.1
 * @author HikmahTiar <hikmahtiar.cool@gmail.com>
 * @license MIT
 *
 */

/******************************
 * Facebook : Hikmah Tiar     *
 * Twitter : @hikmahtiar_     *
 * Instagram : @hikmahtiar6   *
 * Contact : 0878-7430-5327   *
 ******************************
 */

class DataTables {

    /**
     * CodeIgniter intance.
     *
     * @var CodeIgniter
     */
    protected $ci;

    /**
     * Function for GET QUERY
     *
     * @param string $table as table used
     * @param string $select Checking > running query select
     * @param array $join Checking > If not null , running query JOIN TABLE of $join = array()
     * @return string $query
     */
    public function _query_select_table($table, $select, array $join)
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
     * Function for Get value rows ALL DATA TABLE
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
     * Function for Get Value Rows Filtered Data Table
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
     * Function for Get Value Rows Filtered Data Table used
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
            $ordering = $_REQUEST['columns'][$val_order['column']]['data'];
            $order__ = $columns[$ordering];
            $query->order_by($order__ , $val_order['dir']);
        }

        $get = $query->get();

        return $get->result_array();
    }

    /**
     * Function for Get Value Rows Filtered Data Table used
     * @param $table, $select, $join in function _query_select_table()
     * @var $rows_all > int num_rows all data
     * @var $filtered > int num_rows filtered data
     * @var $get > array for JSON of Datatables ServerSide 
     * @return Array
     */
    public function generate($table, $select= '', $join = [], $columns, $search_columns = [], $search_custom = [], $view_custom = [])
    {
        $this->ci =& get_instance();

        $sql = $this->ci->db;


        if(count($search_custom) > 0)
        {
            $rows_all = $search_custom['rows_all'];
            $filtered = $search_custom['filtered'];
            $get = $search_custom['get'];
        }
        else
        {
            $rows_all = $this->_get_rows_all_data($table, $select, $join);
            $filtered = $this->_get_rows_filter_data($table, $select, $join, $columns, $search_columns);
            $get = $this->_get_rows_filter_order_limit_data($table, $select, $join, $columns, $search_columns);
        }

        $data = [];

        foreach($get as $row)
        {
            $dt = [];
            
            foreach($columns as $key_column => $val_column)
            {
                $col = $this->checking_string($val_column);

                if(isset($view_custom[$key_column]))
                {
                    $result = $view_custom[$key_column]($row[$col]);
                }
                else
                {
                    $result = $row[$col];
                }

                $dt[$key_column] = [
                    $result
                ];
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

    /**
     * Function for checking string column
     * @param string $string
     * @var $str as return string exploded
     * @return string $string
     */
    private function checking_string($string)
    {
        if(strpos($string, '.') !== FALSE)
        {
            $str = explode('.', $string);
            return $str[1];
        }
        
        return $string;
    }
                    
}