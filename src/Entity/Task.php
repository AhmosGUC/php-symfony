<?php
// src/App/Entity/Task.php
namespace App\Entity;

class Task
{
    protected $project;
    public function getProject()
    {
        return $this->project;
    }

    public function setProject($project)
    {
        $this->project = $project;
    }
}
