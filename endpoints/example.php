<?php

class index extends api {
    public function __construct(){
        parent::__construct();
    }
    
    public function GET(){
        $this->result = 'GET Method';
        $this->output();
    }

    public function POST(){
        $validators = [
            'email' => ['required', 'email'],
            'phone' => ['optional']
        ];
        
        if($error = validation($this->input, $validators))
            $this->output($error, 400);

        $this->result = $this->input;
        $this->output($this->result, 201);
    }

    public function PUT(){
        $validators = [
            'email' => ['required', 'email'],
            'phone' => ['optional']
        ];
        
        if($error = validation($this->input, $validators))
            $this->output($error, 400);

        $this->result = $this->input;
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
