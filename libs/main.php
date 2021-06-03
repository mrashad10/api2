<?php
// Load libraries
    require 'vendor'.DIRECTORY_SEPARATOR.'autoload.php';
    use Medoo\Medoo;
    use Valitron\Validator;
    use Firebase\JWT\JWT;

// Load configs
    if(file_exists('libs'.DIRECTORY_SEPARATOR."config.php")) include_once("config.php");

// Set main variables
    // Set Request variables
        function setRequest(){
            $wwwDir = 'uri:'.str_replace('index.php', "", $_SERVER['SCRIPT_NAME']);
            $uri    = str_replace($wwwDir, "", 'uri:'.$_SERVER['REQUEST_URI']);
            $uri    = explode('/', $uri);
            if(file_exists('endpoints'.DIRECTORY_SEPARATOR.$uri[0])){
                $dir = $uri[0];
                array_shift($uri);
            }else{
                $dir = NULL;
            }

            if(isset($uri[1])){
                $id        = inputCleanUp($uri[1]);
                $id        = explode('?', $id);
                $id        = $id[0];
                $urlInputs = $uri;
                
                array_shift($urlInputs);
                array_shift($urlInputs);
            }else{
                $id = 0;
                $urlInputs = NULL;
            }
            if($_SERVER['REQUEST_METHOD'] == "POST") $id = 0;

            return (object)[
                'verb'      => $_SERVER['REQUEST_METHOD'],
                'dir'       => $dir,
                'endpoint'  => explode('?', $uri[0])[0],
                'token'     => jwtDecode(),
                'id'        => $id,
                'urlInputs' => ($urlInputs)? :[],
                'language'  => @(in_array($_SERVER['HTTP_ACCEPT_LANGUAGE'], ['ar', 'en']))? $_SERVER['HTTP_ACCEPT_LANGUAGE']:$GLOBALS['systemVariables']->defaultLanguage
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
            $string = $GLOBALS['systemVariables']->salt.$password.$extra;
            return hash('sha256', $string);
        }

        function jwtEncode($payload = [], $exp = true){
            $payload['iat'] = time();
            if($exp)
                $payload['exp'] = time() + (60 * $GLOBALS['systemVariables']->tokenLiveTime);

            return ['token' => 'Bearer '.Firebase\JWT\JWT::encode(
                $payload,
                $GLOBALS['systemVariables']->secretKey
            )];
        }

        function jwtDecode(){
            try {
                return Firebase\JWT\JWT::decode(str_replace('Bearer ', '', $_SERVER['HTTP_AUTHORIZATION']), $GLOBALS['systemVariables']->secretKey, ['HS256']);
            } catch (\Throwable $th) {
                return NULL;
            }
        }

    // translations
        function t( $word = null ){
            if(!is_string($word) || !$word) return '';
            $ar = [
                'already registered'          => 'مسجل مسبقاً',
                'contains invalid data'       => 'يحتوي بيانات غير مقبولة',
                'Status'                      => 'الحالة',
                'Time'                        => 'الوقت',
                'Yes'                         => 'نعم',
                'No'                          => 'لا',
                'AM'                          => 'ص',
                'PM'                          => 'م',
                'am'                          => 'ص',
                'pm'                          => 'م',
            ];
            $en = [];

            $words = ($GLOBALS['request']->language == 'ar')? $ar:$en;

            if(isset($words[$word])) $word = $words[$word];
            return $word;
        }

    // Clean the input before use it
        function inputCleanUp($input = NULL, $except = []) {
            if(!$input) return $input;
            if(is_array($input) || is_object($input)){
                $result = [];
                foreach ($input as $key => $row) {
                    $result[$key] = (in_array($key, $except))? $row:inputCleanUp($row);
                }
                $input = $result;
            }else{
                if(is_integer($input) || is_float($input)) return $input;
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
            Valitron\Validator::langDir(__DIR__.DIRECTORY_SEPARATOR.'validator_lang');
            if(file_exists(__DIR__.DIRECTORY_SEPARATOR.'validator_lang'.DIRECTORY_SEPARATOR.$GLOBALS['request']->language.'.php'))
                Valitron\Validator::lang($GLOBALS['request']->language);

            $v = new Valitron\Validator((array)$input);

            // Set the rules
                // uniq custom rule, Check if the value is uniq in a table
                $v->addRule('uniq', function($field, $value, array $params, array $fields) {
                    // Set the input params
                    $table  = $params[0];
                    $field  = (isset($params[1]))? $params[1]:$field;
                    $id     = (isset($params[2]))? (int)$params[2]:0;
                    $status = (isset($params[3]))? (int)$params[3]:0;

                    // Check the database
                    $db = (isset($GLOBALS['dbSettings']) && is_array($GLOBALS['dbSettings']))? new Medoo($GLOBALS['dbSettings']):NULL;
                    $old = $db->get($table, "*", [
                        $field      => $value,
                        "id[!]"     => $id,
                        "status[!]" => $status
                    ]);

                    return ($old)? false:true;
                }, t("already registered"));


                $v->mapFieldsRules($rules);

            // Set custom labels
            $labels['en'] = [
                'name'                => 'Name',
                'passConf'            => 'Password Confirmation',
            ];
            $labels['ar'] = [
                'name'                => 'الاسم',
                'passConf'            => 'تأكيد كلمة السر',
                'username'            => 'اسم المستخدم',
                'password'            => 'كلمة السر',
                'email'               => 'البريد الإلكتروني',
                'phone'               => 'الهاتف',
                'company'             => 'الشركة',
                'status'              => 'الحالة',
            ];

            $labels = $labels[$GLOBALS['request']->language];

            foreach ($newLabels as $key => $value) {
                $labels[$key] = $value;
            }
            $v->labels($labels);

            // Run the validation
            $v->validate();

            // Return the errors if any
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
                $result = NULL;
                foreach ($list as $row) { $result .= $row.','; }
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
            $request = $GLOBALS['request'];

            if(isset($GLOBALS['headers']))
                foreach ($GLOBALS['headers'] as $key => $value)
                    setHeader($key, $value);


            // Manage routes
                $routes   = $GLOBALS['routes'];
                $endpoint = $request->endpoint;
                $method   = $request->verb;

                $path = 'endpoints'.DIRECTORY_SEPARATOR;
                if($request->dir) $path .= $request->dir.DIRECTORY_SEPARATOR;

                if(isset($routes[$endpoint])){
                    $fileName  = $path.$routes[$endpoint][0].'.php';
                    $className = isset($routes[$endpoint][1])?
                                     $routes[$endpoint][1]
                                    :$routes[$endpoint][0];
                }else{
                    $fileName  = $path.$endpoint.'.php';
                    $className = isset($endpoint)?
                                     $endpoint
                                    :NULL;
                }

            // Load file and class
                if(!file_exists($fileName))
                    API::output(NULL, 404);

                include($fileName);

                if (class_exists($className))
                    $api = new $className;
                elseif(class_exists('index'))
                    $api = new index;
                else
                    API::output(NULL, 404);

                if(!method_exists($api, $method))
                    API::output(NULL, 405);
                
                $api->$method();
        }

    // App functions
        // Debug to file
        function debug($message = NULL, $die = FALSE) {
            // Check if it is Medoo object
            if(is_object($message) && isset($message->pdo)) {
                $message = [
                    $message->last().";",
                    $message->error()
                ];
            }
            // if the message is object convert to array
            $message = (is_object($message))? (array)$message:$message;

            // if the message is array use "print_r" else print as string
            $message = (is_array($message))? print_r($message, true):$message;

            // Write the message to the log file
            file_put_contents('debug.log', date('d M g:i:s a') . " " . $message.PHP_EOL , FILE_APPEND | LOCK_EX);
            if($die) die();
        }
        
// Main API class
    class API{
        protected $systemVariables, $db, $request, $id, $urlInputs, $input, $data, $validators, $result;
        public function __construct(){
            // Set the Database connection & default values
            $this->db              = (isset($GLOBALS['dbSettings']) && is_array($GLOBALS['dbSettings']))? new Medoo($GLOBALS['dbSettings']):NULL;
            $this->result          = NULL;
            $this->input           = $GLOBALS['input'];
            $this->systemVariables = $GLOBALS['systemVariables'];
            $this->request         = $GLOBALS['request'];
            $this->urlInputs       = $this->request->urlInputs;
            $this->id              = $this->request->id;

            if(
                   in_array($this->request->verb, ['PUT', 'PATCH', 'DELETE'])
                && !in_array($this->request->endpoint, ['tokens', 'profile'])
                && !$this->id
                ){
                $this->output('No ID!!!!!', 400);
            }
        }

        // Standard HTTP options verb
        public function OPTIONS(){
            $acceptedMethods = 'OPTIONS';
            foreach (get_class_methods($this) as $row)
                if(in_array($row, ['HEAD','GET','POST','PUT','PATCH','DELETE']))
                    $acceptedMethods .= ','.$row;

            setHeader("Access-Control-Allow-Methods", "$acceptedMethods");
            setHeader("Allow", "$acceptedMethods");
        }

        // Echo the result as JSON object
        public function output($result = NULL, $code = 200) {
            $lang = $GLOBALS['request']->language;

            if(isset($this)){
                $lang   = $this->request->language;
                $result = ($result)? :$this->result;
            }

            if ($code == 200 && !$result) $code = 204;

            $result = ($result)? json_encode($result):NULL;
            header("Content-Language: $lang");
            header("Content-Length: " . strlen($result));
            header("Content-Type: application/json; charset=UTF-8");
            
            http_response_code($code);
            die($result);
        }
    }
