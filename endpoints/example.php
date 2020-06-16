<?php

class index extends api {
    public function __construct(){
        parent::__construct();
        $this->allow();
    }
    
    public function GET(){
        $this->result = 'GET Method';
        $this->output();
    }

    public function POST(){
        $this->result = 'POST Method';
        $this->output();
    }

    public function PUT(){
        $this->result = 'PUT Method';
        $this->output();
    }
    
    public function PATCH(){
        $this->result = 'PATCH Method';
        $this->output();
    }
    
    public function DELETE(){
        $this->result = 'DELETE Method';
        $this->output();
    }
}
