<?php
/**
 * Libraries Datatables ServerSide For CodeIgniter
 * Started August 18, 2015
 * @link https://github.com/hikmahtiar6
 * @version 1.2
 * @author HikmahTiar <hikmahtiar.cool@gmail.com>
 * @license MIT
 *
 */

/******************************
 * Facebook : Hikmah Tiar     *
 * Twitter : @hikmahtiar_     *
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
     * Request data holder.
     *
     * @var array
     */
    protected $request = [];

    /**
     * Library constructor.
     *
     * @return none
     */
    public function __construct()
    {
        $this->ci =& get_instance();
        $this->request = $this->ci->input->post() ? $this->ci->input->post() : $this->ci->input->get();
    }

    /**
     * Function for checking string column.
     *
     * @param string $string
     * @return string $string
     */
    private function _checking_string($string)
    {
        if(strpos($string, '.') !== FALSE)
        {
            $str = explode('.', $string);
            return $str[1];
        }
        
        return $string;
    }

    /**
     * Function character string not allowed search
     *
     * @return array character
     */
    private function _not_allowed_char()
    {
        $character = [
            ':', '=', '>', '<', '"'
        ];

        return $character;
    }

    /**
     * Function for GET QUERY.
     *
     * @param string $table as table used
     * @param string $select Checking > running query select
     * @param array $join Checking > If not null , running query JOIN TABLE of $join = array()
     * @return string $query
     */
    public function query_select_table($table, $select, array $join)
    {
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
     *
     * @param $table, $select, $join in function query_select_table()
     * @return int $num_rows
     */
    private function _get_rows_all_data($table, $select, array $join)
    {
        $sql = $this->ci->db;
        
        $query = $this->query_select_table($table, $select, $join);

        $get = $query->get();

        $num_rows = $get->num_rows();

        return $num_rows;
    }

    /**
     * Function for Get Value Rows Filtered Data Table.
     *
     * @param $table, $select, $join in function query_select_table()
     * @param array $columns > variable column used
     * @param array $search_columns > if used customized search
     * @return int $filtered
     */
    private function _get_rows_filter_data($table, $select, array $join, array $columns, array $search_columns)
    {
        $sql = $this->ci->db;
        
        $query = $this->query_select_table($table, $select, $join);

        $post_search = $this->request['search']['value'];

        $request_search = str_replace($this->_not_allowed_char(), '', $post_search);

        if($request_search != '')
        {
            $query_search = '(';
            $searching = '';

            if(count($search_columns) == 0)
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
     * Function for Get Value Rows Filtered Data Table used.
     *
     * @param $table, $select, $join in function query_select_table()
     * @param array $columns > variable column used
     * @param array $search_columns > if used customized search
     * @return Array
     */
    private function _get_data($table, $select, array $join, $columns, $search_columns)
    {
        $sql = $this->ci->db;
        
        $query = $this->query_select_table($table, $select, $join);

        $post_search = $this->request['search']['value'];

        $request_search = str_replace($this->_not_allowed_char(), '', $post_search);

        if($request_search != '')
        {
            $query_search = '(';
            $searching = '';

            if(count($search_columns) == 0)
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
                
        $query->limit($this->request['length'] , $this->request['start']);

        $order_column = $this->request['columns'];
        $order_table = $this->request['order'];

        foreach($order_table as $val_order)
        {
            $order_name = $this->request['columns'][$val_order['column']]['name'];
            $order_data = $this->request['columns'][$val_order['column']]['data'];

            if($order_name == '')
            {
                $order__ = $columns[$order_data];
            }
            else
            {
                $order__ = $columns[$order_name];
            }
            $query->order_by($order__ , $val_order['dir']);
        }

        $get = $query->get();

        return $get->result_array();
    }

    /**
     * Function for Get Value Rows Filtered Data Table used.
     *
     * @param $table, $select, $join in function query_select_table()
     * @return Array
     */
    public function generate($table, $select= '', $join = [], $columns, $search_columns = [], $search_custom = [], $view_custom = [])
    {
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
            $get = $this->_get_data($table, $select, $join, $columns, $search_columns);
        }

        $data = [];

        foreach($get as $row)
        {
            $dt = [];
            
            foreach($columns as $key_column => $val_column)
            {
                $col = $this->_checking_string($val_column);

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

            'draw' => intval($this->request['draw']),
            'recordsTotal' => intval($rows_all),
            'recordsFiltered' => intval($filtered),
            'data' => $data

        ];

        $output = $this->ci->output;
        $output->set_content_type('application/json');
        $output->set_output(json_encode($json));

        return $output;            
    }
                    
}