<?php

class M_Model extends CI_Model
{
    public function getById($table,$id)
    {
        $this->db->select('*');
        $this->db->from($table);
        $this->db->where('id',$id);
        $res=$this->db->get();

        return $res->result();
    }
}