<?php 
namespace App\Interfaces;

interface TaskServiceInterface {
    public function createTask($request);
    public function updateTask($request, $id);
}