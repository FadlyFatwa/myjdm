<?php
 
if (!defined('BASEPATH'))
    exit('No direct script access allowed');
 
/**
 * Ignited Datatables 2.0
 *
 * This is a wrapper class/library based on the native Datatables server-side implementation by Allan Jardine
 * found at http://datatables.net/examples/data_sources/server_side.html for CodeIgniter
 *
 * @package    CodeIgniter
 * @subpackage libraries
 * @category   library
 * @version    2.0 <beta>
 * @author     Vincent Bambico <metal.conspiracy@gmail.com>
 *             Yusuf Ozdemir <yusuf@ozdemir.be>
 *             Alex Fagard <alex@fagardesigns.com>
 * @link       http://ellislab.com/forums/viewthread/160896/
 */
class Datatables {
 
    /**
     * Global container variables for chained argument results
     */
    private $ci;
    private $table;
    private $distinct;
    private $group_by = array();
    private $having = array();
    private $select = array();
    private $joins = array();
    private $columns = array();
    private $where = array();
    private $or_where = array();
    private $where_in = array();
    private $like = array();
    private $or_like = array();
    private $filter = array();
    private $add_columns = array();
    private $edit_columns = array();
    private $unset_columns = array();
    private $post_data = array();
    private $default_order_by = null;
    private $default_order_dir = 'DESC';
 
    /**
     * Copies an instance of CI
     */
    public function __construct() {
        $this->ci = & get_instance();
    }
 
    /**
     * If you establish multiple databases in config/database.php this will allow you to
     * set the database (other than $active_group) - more info: http://ellislab.com/forums/viewthread/145901/#712942
     */
    public function set_database($db_name) {
        $db_data = $this->ci->load->database($db_name, TRUE);
        $this->ci->db = $db_data;
    }
 
    /**
     * Generates the SELECT portion of the query
     *
     * @param string $columns
     * @param bool $backtick_protect
     * @return mixed
     */
     public function select($columns, $backtick_protect = TRUE)
     {
         $this->select['column'] = $columns;
         $this->select['backtick_protect'] = $backtick_protect;
 
         foreach($this->explode(',', $columns) as $val)
         {
           $column = trim(preg_replace('/(.*)\s+as\s+(\w*)/i', '$2', $val));
           $column = preg_replace('/.*\.(.*)/i', '$1', $column); // get name after `.`
           $this->columns[] =  $column;
           $this->select[$column] =  trim(preg_replace('/(.*)\s+as\s+(\w*)/i', '$1', $val));
         }
         $this->ci->db->select($columns, $backtick_protect);
         return $this;
     }
 
    /**
     * Generates the DISTINCT portion of the query
     *
     * @param string $column
     * @return mixed
     */
    public function distinct($column) {
        $this->distinct = $column;
        $this->ci->db->distinct($column);
        return $this;
    }
 
    /**
     * Generates a custom GROUP BY portion of the query
     *
     * @param string $val
     * @return mixed
     */
    public function group_by($val) {
        $this->group_by[] = $val;
        $this->ci->db->group_by($val);
        return $this;
    }
    public function having($val)
    {
      $this->having[] = $val;
      $this->ci->db->having($val);
      return $this;
    }
 
    /**
     * Generates the FROM portion of the query
     *
     * @param string $table
     * @return mixed
     */
    public function from($table) {
        $this->table = $table;
        return $this;
    }
 
    /**
     * Generates the JOIN portion of the query
     *
     * @param string $table
     * @param string $fk
     * @param string $type
     * @return mixed
     */
    public function join($table, $fk, $type = NULL) {
        $this->joins[] = array($table, $fk, $type);
        $this->ci->db->join($table, $fk, $type);
        return $this;
    }
 
    /**
     * Generates the WHERE portion of the query
     *
     * @param mixed $key_condition
     * @param string $val
     * @param bool $backtick_protect
     * @return mixed
     */
    public function where($key_condition, $val = NULL, $backtick_protect = TRUE) {
        $this->where[] = array($key_condition, $val, $backtick_protect);
        $this->ci->db->where($key_condition, $val, $backtick_protect);
        return $this;
    }
 
    /**
     * Generates the WHERE portion of the query
     *
     * @param mixed $key_condition
     * @param string $val
     * @param bool $backtick_protect
     * @return mixed
     */
    public function or_where($key_condition, $val = NULL, $backtick_protect = TRUE) {
        $this->or_where[] = array($key_condition, $val, $backtick_protect);
        $this->ci->db->or_where($key_condition, $val, $backtick_protect);
        return $this;
    }
 
    /**
     * Generates the WHERE IN portion of the query
     *
     * @param mixed $key_condition
     * @param string $val
     * @param bool $backtick_protect
     * @return mixed
     */
    public function where_in($key_condition, $val = NULL) {
        $this->where_in[] = array($key_condition, $val);
        $this->ci->db->where_in($key_condition, $val);
        return $this;
    }
 
    /**
     * Generates the WHERE portion of the query
     *
     * @param mixed $key_condition
     * @param string $val
     * @param bool $backtick_protect
     * @return mixed
     */
    public function filter($key_condition, $val = NULL, $backtick_protect = TRUE) {
        $this->filter[] = array($key_condition, $val, $backtick_protect);
        return $this;
    }
 
    /**
     * Generates a %LIKE% portion of the query
     *
     * @param mixed $key_condition
     * @param string $val
     * @param string $side
     * @return mixed
     */
    public function like($key_condition, $val = NULL, $side = 'both') {
        $this->like[] = array($key_condition, $val, $side);
        $this->ci->db->like($key_condition, $val, $side);
        return $this;
    }
 
    /**
     * Generates the OR %LIKE% portion of the query
     *
     * @param mixed $key_condition
     * @param string $val
     * @param string $side
     * @return mixed
     */
    public function or_like($key_condition, $val = NULL, $side = 'both') {
        $this->or_like[] = array($key_condition, $val, $side);
        $this->ci->db->or_like($key_condition, $val, $side);
        return $this;
    }
 
    /**
     * Sets additional column variables for adding custom columns
     *
     * @modified Alex Fagard
     * @param string $column
     * @param string|array $content
     * @param string $match_replacement
     * @return mixed
     */
    public function add_column($column, $content, $match_replacement = null) {
        $this->add_columns[$column] = array('content' => $content, 'replacement' => $this->explode(',', $match_replacement));
        return $this;
    }
 
    /**
     * Sets additional column variables for editing columns
     *
     * @modified Alex Fagard
     * @param string $column
     * @param string|array $content
     * @param string $match_replacement
     * @return mixed
     */
    public function edit_column($column, $content, $match_replacement = null) {
        $this->edit_columns[$column][] = array('content' => $content, 'replacement' => $this->explode(',', $match_replacement));
        return $this;
    }
 
    /**
     * Unset column
     *
     * @modified Alex Fagard Wasn't unsetting properly in 1.0
     * @param string $column
     * @return mixed
     */
    public function unset_column($column) {
        $column = array_flip(explode(',', $column));
        $this->unset_columns = array_merge($this->unset_columns, $column);
        return $this;
    }
 
    /**
     * Sets the default order by column and direction
     * Used on first draw
     *
     * @param string $column
     * @param string $dir ASC or DESC
     * @return mixed
     */
    public function default_order($column, $dir) {
        $this->default_order_by = $column;
        $this->default_order_dir = $dir;
        return $this;
    }
 
    /**
     * Builds all the necessary query segments and performs the main query based on results set from chained statements
     *
     * @modified Alex Fagard
     * @param array $columns
     * @param string $output
     * @param string $charset
     * @return string
     */
    public function generate($columns = null) {
        $this->post_data = array(
            'start' => $this->ci->input->post('start'),
            'length' => $this->ci->input->post('length'),
            'columns' => $this->ci->input->post('columns'),
            'order' => $this->ci->input->post('order'),
            'search' => $this->ci->input->post('search'),
            'draw' => intval($this->ci->input->post('draw'))
        );
        $this->get_paging();
        $this->get_ordering();
        $this->get_filtering();
        return $this->produce_output();
    }
 
    /**
     * Generates the LIMIT portion of the query
     *
     * @modified Alex Fagard
     * @return void
     */
    private function get_paging() {
        if (!is_null($this->post_data['length']) && $this->post_data['length'] != '-1') {
            $this->ci->db->limit($this->post_data['length'], ($this->post_data['start']) ? $this->post_data['start'] : 0);
        }
    }
 
    /**
     * Generates the ORDER BY portion of the query
     *
     * @modified Alex Fagard
     * @return void
     */
    private function get_ordering() {
        $order = $this->post_data['order'];
        if (!is_null($this->default_order_by) && $this->post_data['draw'] == 1) {
            $this->ci->db->order_by($this->default_order_by, $this->default_order_dir);
        } elseif (!is_null($order)) {
            $columns = $this->post_data['columns'];
            $found = false;
            foreach ($order as $key) {
                if ($columns[$key['column']]['orderable'] !== 'false') {
                    if ($this->check_col_type()) {
                        $this->ci->db->order_by($columns[$key['column']]['data'], $key['dir']);
                    } else {
                        $this->ci->db->order_by($this->columns[$columns['column']], $key['dir']);
                    }
                    $found = true;
                }
            }
            if (!$found && !is_null($this->default_order_by)) {
                $this->ci->db->order_by($this->default_order_by, $this->default_order_dir);
            }
        }
    }
 
    /**
     * Generates a %LIKE% portion of the query
     *
     * @modified Alex Fagard
     * @return void
     */
    private function get_filtering() {
        $search_where = '';
        $search_str = $this->ci->db->escape_like_str(@trim($this->post_data['search']['value']));
        $columns = $this->post_data['columns'];
        $cols = array_values(array_diff($this->columns, $this->unset_columns));
        if ($search_str != '') {
            for ($i = 0; $i < count($columns); $i++) {
                if ($columns[$i]['searchable'] == 'true' && !array_key_exists($columns[$i]['data'], $this->add_columns)) {
                    if ($this->check_col_type()) {
                        $search_where .= $this->select[$columns[$i]['data']] . " LIKE '%" . $search_str . "%' OR ";
                    } else {
                        $search_where .= $this->select[$this->columns[$i]] . " LIKE '%" . $search_str . "%' OR ";
                    }
                }
            }
        }
        $search_where = substr_replace($search_where, '', -3);
        if ($search_where != '') {
            $this->ci->db->where('(' . $search_where . ')');
        }
        foreach ($this->filter as $val) {
            $this->ci->db->where($val[0], $val[1], $val[2]);
        }
    }
 
    /**
     * Compiles the select statement based on the other functions called and runs the query
     *
     * @return result object
     */
    private function get_display_result() {
        return $this->ci->db->get($this->table);
    }
 
    /**
     * Builds final output array
     *
     * @modified Alex Fagard
     * @return array
     */
    private function produce_output() {
        $data = array();
        $result = $this->get_display_result();
        foreach ($result->result_array() as $row_key => $row_val) {
            $data[$row_key] = ($this->check_col_type()) ? $row_val : array_values($row_val);
            foreach ($this->add_columns as $field => $val) {
                if ($this->check_col_type()) {
                    $data[$row_key][$field] = $this->exec_replace($val, $data[$row_key]);
                } else {
                    $data[$row_key][] = $this->exec_replace($val, $data[$row_key]);
                }
            }
            foreach ($this->edit_columns as $mod_key => $mod_val) {
                foreach ($mod_val as $val) {
                    $key = $this->check_col_type() ? $mod_key : array_search($mod_key, $this->columns);
                    if ($key === false || !isset($data[$row_key][$key])) {
                        continue; // make sure we only edit columns that exist!
                    }
                    $data[$row_key][$key] = $this->exec_replace($val, $data[$row_key]);
                }
            }
            $data[$row_key] = array_diff_key($data[$row_key], ($this->check_col_type()) ? $this->unset_columns : array_intersect($this->columns, $this->unset_columns));
            if (!$this->check_col_type()) {
                $data[$row_key] = array_values($data[$row_key]);
            }
        }
        return array(
            'draw' => $this->post_data['draw'],
            'recordsTotal' => $this->get_total_results(),
            'recordsFiltered' => $this->get_total_results(TRUE),
            'data' => $data
        );
    }
 
    /**
     * Get result count
     *
     * @modified Alex Fagard
     * @return integer
     */
    private function get_total_results($filtering = FALSE) {
        if(!empty($this->select))
          $this->ci->db->select($this->select['column'], $this->select['backtick_protect']);
 
        if ($filtering) {
            $this->get_filtering();
        }
        foreach ($this->joins as $val) {
            $this->ci->db->join($val[0], $val[1], $val[2]);
        }
        foreach ($this->where as $val) {
            $this->ci->db->where($val[0], $val[1], $val[2]);
        }
        foreach ($this->or_where as $val) {
            $this->ci->db->or_where($val[0], $val[1], $val[2]);
        }
        foreach ($this->where_in as $val) {
            $this->ci->db->where_in($val[0], $val[1]);
        }
        foreach ($this->group_by as $val) {
            $this->ci->db->group_by($val);
        }
        foreach ($this->like as $val) {
            $this->ci->db->like($val[0], $val[1], $val[2]);
        }
        foreach ($this->or_like as $val) {
            $this->ci->db->or_like($val[0], $val[1], $val[2]);
        }
        foreach($this->having as $val){
            $this->ci->db->having($val);
        }
        if (@strlen($this->distinct) > 0) {
            $this->ci->db->distinct($this->distinct);
            $this->ci->db->select($this->columns);
        }
        //$query = $this->ci->db->get($this->table, NULL, NULL, FALSE);
        /*
         * To be honest I'm not really sure this mod is valid in all
         * circumstances. I don't use group_bys or joins as my tables are simple,
         * so I wouldn't know where to start to see if its working 100%.
         */
        return $this->ci->db->count_all_results($this->table);
    }
 
    /**
     * Runs callback functions and makes replacements
     *
     * Usage:
     * ->add_column('actions', '', 'id, published', $this->datatables_model, 'callback_actions_projects')
     * ->add_column('checkbox', '<input name="u[]" type="checkbox" value="%s">', 'id');
     *
     * @author Alex Fagard
     * @param mixed $custom_val
     * @param mixed $row_data
     * @return string|null $replace_string NULL on failure
     */
    private function exec_replace($custom_val, $row_data) {
        $replace_string = '';
        $replacements = array();
        if (isset($custom_val['replacement']) && is_array($custom_val['replacement']) && count($custom_val['replacement']) > 0) {
            foreach ($custom_val['replacement'] as $replacement) {
                $replacement = trim($replacement);
                $key = $this->check_col_type() ? $replacement : array_search($replacement, $this->columns);
                if ($key === false || !isset($row_data[$key])) {
                    return null;
                }
                $replacements[] = $row_data[$key];
            }
        }
        // callback
        if (is_array($custom_val['content']) && method_exists($custom_val['content'][0], $custom_val['content'][1])) {
            $replace_string = call_user_func_array($custom_val['content'], $replacements);
        // inline
        } else {
            if (count($replacements) > 0) {
                $replace_string = vsprintf($custom_val['content'], $replacements);
            } else {
                $replace_string = $custom_val['content'];
            }
        }
        return $replace_string;
    }
 
    /**
     * Check column type -numeric or column name
     *
     * @return bool
     */
    private function check_col_type() {
        if (isset($this->post_data['columns'][0]['data'])) {
            if (is_numeric($this->post_data['columns'][0]['data'])) {
                return FALSE;
            } else {
                return TRUE;
            }
        }
    }
 
    /**
     * Return the difference of open and close characters
     *
     * @param string $str
     * @param string $open
     * @param string $close
     * @return string $retval
     */
    private function balance_chars($str, $open, $close) {
        $open_count = substr_count($str, $open);
        $close_count = substr_count($str, $close);
        $retval = $open_count - $close_count;
        return $retval;
    }
 
    /**
     * Explode, but ignore delimiter until closing characters are found
     *
     * @modified Alex Fagard
     * @param string $delimiter
     * @param string $str
     * @param string $open
     * @param string $close
     * @return mixed $retval
     */
    private function explode($delimiter, $str, $open = '(', $close = ')') {
        $retval = array();
        $hold = array();
        $balance = 0;
        $parts = explode($delimiter, $str);
        foreach ($parts as $part) {
            $hold[] = $part;
            $balance += $this->balance_chars($part, $open, $close);
            if ($balance < 1) {
                $retval[] = implode($delimiter, $hold);
                $hold = array();
                $balance = 0;
            }
        }
        if (count($hold) > 0) {
            $retval[] = implode($delimiter, $hold);
        }
        // added array filter so no more array ( [0] => )
        return array_filter($retval);
    }
 
    /**
     * Returns the SQL statement of the last query run
     *
     * @return string
     */
    public function last_query() {
        return $this->ci->db->last_query();
    }
 
}
 
/* End of file Datatables.php */
/* Location: ./application/libraries/Datatables.php */
 