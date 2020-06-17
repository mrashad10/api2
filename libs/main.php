<?php
// Load libraries
    require 'vendor'.DIRECTORY_SEPARATOR.'autoload.php';
    use Medoo\Medoo;
    use Valitron\Validator;

// Load configs
    if(file_exists('libs'.DIRECTORY_SEPARATOR."config.php")) include_once("config.php");

// Set main variables
    // Set Request variables
        function setRequest(){
            $wwwDir = str_replace('index.php', "", $_SERVER['SCRIPT_NAME']);
            $uri = str_replace($wwwDir,"",$_SERVER['REQUEST_URI']);
            $uri = explode('/', $uri);
            if(isset($uri[1])){
                $id = inputSecure($uri[1]);
                $id = explode('?', $id);
                $id = $id[0];
                $urlInputs = $uri;
                array_shift($urlInputs);
                array_shift($urlInputs);
            }else{
                $id = 0;
                $urlInputs = NULL;
            }
            return (object)[
                'verb' => $_SERVER['REQUEST_METHOD'],
                'endpoint' => explode('?', $uri[0])[0],
                'id' => $id,
                'urlInputs' => $urlInputs
            ];
        }
        $request = setRequest();

    // Set Inputs
        $input = NULL;
        $input = json_decode(file_get_contents("php://input"));
        if(!$input) parse_str($_SERVER['QUERY_STRING'], $input);
        if(!$input) $input = $_REQUEST;
        
        $input = (is_array($input))? (object)$input:$input;




// Global Functions
    // Generate password hash using salt and extra data if provided
        function passwordHash($password = NULL, $extra = NULL){
            $string = $GLOBALS['salt'].$password.$extra;
            return hash('sha256', $string);
        }

    // Clean the input before use it
        function inputSecure($input = NULL, $except = []) {
            if(!$input) return $input;
            if(is_array($input) || is_object($input)){
                $result = [];
                foreach ($input as $key => $row) {
                    $result[$key] = (in_array($key, $except))? $row:inputSecure($row);
                }
                $input = $result;
            }else{
                $input = trim($input);
                $input = strip_tags($input);
                $input = filter_var($input, FILTER_SANITIZE_STRING);
                $input = stripslashes($input);
                $input = htmlspecialchars($input, ENT_QUOTES, 'utf-8');
            }
            return $input;
        }

    // Validation
        function validation($input, $rules, $newLabels = []){
            Valitron\Validator::langDir(__DIR__.'/validator_lang');
            // Valitron\Validator::lang('ar');

            $v = new Valitron\Validator((array)$input);

            // Set the rules
                // uniq custom rule, Check if the value is uniq in a table
                $v->addRule('uniq', function($field, $value, array $params, array $fields) {
                    // Set the input params
                    $table = $params[0];
                    $field = (isset($params[1]))? $params[1]:$field;
                    $id = (isset($params[2]))? (int)$params[2]:0;

                    // Check the database
                    $db = (isset($GLOBALS['dbSettings']) && is_array($GLOBALS['dbSettings']))? new Medoo($GLOBALS['dbSettings']):NULL;
                    $old = $db->get($table, "*", [
                        $field => $value,
                        "id[!]" => $id,
                    ]);

                    return ($old)? false:true;
                }, 'must be uniq');

            $v->mapFieldsRules($rules);

            // Set custom labels
            $labels = [
                'name' => 'Name',
                'helper' => 'Help URL',
                'totalCoupon' => '',
                'passConf' => 'Password Confirmation',
            ];
            foreach ($newLabels as $key => $value) {
                $labels[$key] = $value;
            }
            $v->labels($labels);

            // Run the validation
            $v->validate();

            // Return the errors if there any
            if($v->errors()) {
                foreach ($v->errors() as $row) {
                    foreach ($row as $err) { $result[] = $err; }
                }
                return $result;
            }
        }

    // Create string value for header
        function headerString($list = NULL){
            if(!$list) return NULL;
            if(is_string($list)){
                $list = str_replace(' ', '', $list);
                $list = explode(',', $list);
            }
            if(is_array($list)){
                foreach ($list as $row) { @$result .= $row.','; }
                return trim($result, ',');
            }else{
                return NULL;
            }
        }

    // Set HTTP headers
        function setHeader($header = NULL, $list = NULL){
            if(!$header || !$list) return NULL;
            $value = headerString($list);
            if($value) header("$header: $value");
        }
    // Load required class
        function run() {
            if(isset($GLOBALS['headers'])) {
                foreach ($GLOBALS['headers'] as $key => $value) {
                    setHeader($key, $value);
                }
            }

            $request = $GLOBALS['request'];

            // Manage routes
                $routes = $GLOBALS['routes'];
                $endpoint = $request->endpoint;
                $method = $request->verb;

                if(isset($routes[$endpoint])){
                    $fileName = 'endpoints'.DIRECTORY_SEPARATOR.$routes[$endpoint][0].'.php';
                    $className = isset($routes[$endpoint][1])? $routes[$endpoint][1]:$routes[$endpoint][0];
                }else{
                    $fileName = 'endpoints'.DIRECTORY_SEPARATOR.$endpoint.'.php';
                    $className = isset($endpoint)? $endpoint:NULL;
                }

            // Load file and class
                if(!file_exists($fileName)) die(http_response_code(404));
                include($fileName);

                if (class_exists($className)) {
                    $api = new $className;
                }elseif(class_exists('index')){
                    $api = new index;
                }else{
                    die(http_response_code(404));
                }

                if(!method_exists($api, $method)) die(http_response_code(405));
                $api->$method();
        }

    // App functions
        // Debug to file
        function debug($message = NULL) {
            $message = (is_object($message))? (array)$message:$message;
            $message = (is_array($message))? print_r($message, true):$message;
            file_put_contents('debug.log', $message.PHP_EOL , FILE_APPEND | LOCK_EX);
        }


// Main API class
    class api{
        protected $id, $urlInputs, $db, $input, $result;
        public function __construct(){
            $this->result = NULL;
            $this->input = $GLOBALS['input'];
            $this->urlInputs = $GLOBALS['request']->urlInputs;
            $this->id = $GLOBALS['request']->id;

            // Set the Database connection
            $this->db = (isset($GLOBALS['dbSettings']) && is_array($GLOBALS['dbSettings']))? new Medoo($GLOBALS['dbSettings']):NULL;
        }

        // Get Token from header
        function getToken(){
            $token = getallheaders();
            if(isset($token['Authorization'])){
                $token = $token['Authorization'];
            }elseif(isset($token['authorization'])){
                $token = $token['authorization'];
            }else{
                $token = NULL;
            }
            $token = ($token)? explode("token ", $token):NULL;
            $token = (is_array($token) && isset($token[1]))? $token[1]:NULL;
            return $token;
        }

        // Standard HTTP options verb
        public function OPTIONS(){
            $list = ['HEAD','GET','POST','PUT','PATCH','OPTIONS','DELETE']; // List of standard verbs
            $classMethods = get_class_methods($this);
            foreach ($classMethods as $row) {
                if(in_array($row, $list)) @$acceptedMethods .= $row.',';
            }
            $acceptedMethods = trim($acceptedMethods, ',');
            setHeader("Access-Control-Allow-Methods", "$acceptedMethods");
            setHeader("Allow", "$acceptedMethods");
        }

        // Echo the result as JSON object
        protected function output($result = NULL) {
            header('Content-Type: application/json; charset=UTF-8');
            $result = ($result)? $result:$this->result;
            
            die(json_encode($result));
        }
    }
