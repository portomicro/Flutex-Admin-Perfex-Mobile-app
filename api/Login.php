<?php

defined('BASEPATH') OR exit('No direct script access allowed');

require __DIR__ . '/API_Controller.php';

class Login extends API_Controller {
    public function __construct() {
        parent::__construct();
        $this->load->model('Authentication_model');
        $this->load->model('Api_model');
        $this->load->model('Staff_model');
    }

    public function login_api()
    {
       header("Access-Control-Allow-Origin: *");

        // API Configuration
        $this->_apiConfig([
            'methods' => ['POST'],
        ]);

        // you user authentication code will go here, you can compare the user with the database or whatever
        $payload = [
            'id' => "Your User's ID",
            'other' => "Some other data"
        ];

        // Load Authorization Library or Load in autoload config file
        $this->load->library('Authorization_Token');

        // generate a token
        $token = $this->authorization_token->generateToken($payload);

        // return data
        $this->api_return(
            [
                'status' => true,
                "result" => [
                    'token' => $token,
                ],
                
            ],
        200);
    }

    public function authentication()
    {
        header("Access-Control-Allow-Origin: *");
        
        // API Configuration
        $this->_apiConfig([
            'methods' => ['POST'],
        ]);
        
        $email    = $this->input->post('email');
        $password = $this->input->post('password', false);
        $payload = $this->Authentication_model->login($email, $password, true, true);
        
        if ($payload) {
            $staff = $this->db->where('email', $email)->get(db_prefix() . 'staff')->row();
            
            // Load Authorization Library or Load in autoload config file
            $this->load->library('Authorization_Token');
            
            $payload = [
                'user' => $staff->staffid,
                'name' => $staff->firstname . ' ' .$staff->lastname,
                'expiration_date' => date('Y-m-d H:i:s', strtotime('+1 year'))
            ];
            $payload['token'] = $this->authorization_token->generateToken($payload);
            $payload['expiration_date'] = to_sql_date($payload['expiration_date'], true);
            
            if (!is_null(get_cookie('sp_session',true))) {
                $key = get_cookie('sp_session', true);
            }
            
            $is_exist = $this->get_user_api($staff->staffid);
            
            if ($is_exist){
                $this->db->where('id', $staff->staffid);
                $this->db->update(db_prefix().'user_api', $payload);
                $insert_user_api = true;
            } else {
                $this->db->insert(db_prefix().'user_api', $payload);
                $insert_user_api = $this->db->insert_id();
            }
            
            if ($insert_user_api) {
                $this->api_return(
                    [
                        'status' => true,
                        'message' => 'Logged in Successfully',
                        "data" => [
                            'token' => $payload['token'],
                            'session' => $key,
                            'staff'  => $staff
                        ],
                    ],
                    200
                );
                die();
            }
        }
        $this->api_return(
            [
                'status' => FALSE,
                'message' => 'Credentials didn\'t match!',
            ],
            400
        );
    }

    public function get_user_api($user = '')
    {
        $this->db->select('*');
        if ('' != $user) {
            $this->db->where('user', $user);
        }

        return $this->db->get(db_prefix() . 'user_api')->result_array();
    }

    /**
     * view method
     *
     * @link [api/user/view]
     * @method POST
     * @return Response|void
     */
    public function view() {
        header("Access-Control-Allow-Origin: *");
        // API Configuration [Return Array: User Token Data]
        $user_data = $this->_apiConfig(['methods' => ['POST'], 'requireAuthorization' => true, ]);
        // return data
        $this->api_return(['status' => true, "result" => ['user_data' => $user_data['token_data']], ], 200);
    }
    
    public function api_key() {
        $this->_APIConfig(['methods' => ['POST'], 'key' => ['header', 'Set API Key'], ]);
    }
}
