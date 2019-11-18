<?php 
namespace App\Interfaces;

interface TaskRepositoryInterface {
    public function insert($data);
    public function update($id, $data);
}