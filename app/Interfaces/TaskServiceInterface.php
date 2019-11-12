<?php 
namespace App\Interfaces;

interface TaskServiceInterface {
    public function createTask($data);
    public function editTask($data);
}