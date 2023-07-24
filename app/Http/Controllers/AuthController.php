<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Candidate;
use Validator;

class AuthController extends Controller
{
    public function __construct()
    {
        $this->middleware('authenticate', ['except' => ['login', 'register']]);
    }

    /**
     * @api {post} register Register a new User
     * @apiName registerUser
     * @apiGroup User
     * @apiVersion 1.0.0
     *
     * @apiParam {String} first_name First Name of the user
     * @apiParam {String} name Name of the user
     * @apiParam {String} email Email of the user
     * @apiParam {String} password Password of the user
     * @apiParam {String} password_confirmation Password of the user
     *
     * @apiSuccessExample {json} Response
     *  HTTP 200 OK
      *     {
      *         "data": {
      *             "name": "sujeet kumar",
      *             "email": "sk141158+1@gmail.com",
      *             "updated_at": "2023-07-22T11:01:02.000000Z",
      *             "created_at": "2023-07-22T11:01:02.000000Z",
      *             "id": 2
      *         },
      *         "success": true,
      *         "data_msg": "User created successfully."
      *     }
     *
     * {
     *  "success":false,
     *  "error":{
     *      "code":3000,
     *      "msg":"A user with the same email already exists."
     *  }
     * }
     *
     * @apiErrorExample {json} Error-Response:
     *  HTTP 400
     * {
     *  "success":false,
     *  "error":{
     *      "code":1000,
     *      "msg":"The name field is required."
     *    }
     * }
     */
    public function register()
    {
        $validator = Validator::make(request()->all(), [
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|confirmed|min:5',
        ]);

        if($validator->fails()){
            $errors = $validator->errors()->toJson();
            $response = setResponseData(array(), false, 1000, $errors);
            return response()->json($response, 400);
        }

        $postData = request()->all();
        $user = User::createUser($postData);
        $credentials = request(['email', 'password']);
        $token = auth()->setTTL(1200)->attempt($credentials);

        if(!empty($token)) {
          $user['token'] = $token;
        }

        $response = setResponseData($user, true, false, false, trans('auth.user_created'));
        return response()->json($response, 201);
    }
    /**
     * @api {post} login login to a user
     * @apiName login
     * @apiGroup User
     * @apiVersion 1.0.0
     *
     * @apiParam {String} email Email of the user
     * @apiParam {String} password Password of the user
     *
     * @apiSuccessExample {json} Response
     *  HTTP 200 OK
    *     {
    *         "data": {
    *             "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vMTI3LjAuMC4xOjgwMDAvYXBpL2xvZ2luIiwiaWF0IjoxNjkwMDkyNDMwLCJleHAiOjE2OTAwOTYwMzAsIm5iZiI6MTY5MDA5MjQzMCwianRpIjoiTjZybFB4dFZJOGxOMFJYNSIsInN1YiI6IjEiLCJwcnYiOiIyM2JkNWM4OTQ5ZjYwMGFkYjM5ZTcwMWM0MDA4NzJkYjdhNTk3NmY3In0.Hb9IeUSj_jNtcy3tL4wonVWiDkXyAP8YOsq0KOg4LcQ"
    *         },
    *         "success": true,
    *         "data_msg": "User created successfully."
    *     }
     *
     * {
     *  "success":false,
     *  "error":{
     *      "code":1000,
     *      "msg":"invalide user name or password."
     *  }
     * }
     *
     * @apiErrorExample {json} Error-Response:
     *  HTTP 400
     * {
     *  "success":false,
     *  "error":{
     *      "code":1000,
     *      "msg":"The name field is required."
     *    }
     * }
     */
    public function login()
    {
        $validator = Validator::make(request()->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if($validator->fails()){
            $errors = $validator->errors()->toJson();
            $response = setResponseData(array(), false, 1000, $errors);
            return response()->json($response, 400);
        }

        $postData = request()->all();
        $credentials = request(['email', 'password']);
        if (! $token = auth()->setTTL(1200)->attempt($credentials)) {
          $response = setResponseData(array(), false, 1000, 'Invalid email or password.');
          return response()->json($response, 401);
        }

        $response = setResponseData(['token' => $token], true, false, false, trans('auth.user_created'));
        return response()->json($response, 201);
    }
    /**
     * @api {post} candidates Create New candidate
     * @apiName candidates
     * @apiGroup User
     * @apiVersion 1.0.0
     *
     * @apiParam {String} first_name First Name of Candidate
     * @apiParam {String} last_name First Name of Candidate
     * @apiParam {String} email Email of Candidate
     * @apiParam [integer] gender gender of Candidate
     * @apiParam [String] contact_number contact_number of Candidate
     * @apiParam [String] specialization specialization of Candidate
     * @apiParam [String] address address of Candidate
     * @apiParam [String] skill skill of Candidate
     * @apiParam [integer] work_ex_year work_ex_year of Candidate
     * @apiParam [integer] candidate_dob candidate_dob of Candidate
     * @apiParam [file] resume resume of Candidate
     *
     * @apiSuccessExample {json} Response
     *  HTTP 200 OK
      *     {
      *         "data": {
      *             "data": {
      *                 "first_name": "sujeet",
      *                 "last_name": "kumar",
      *                 "email": "sk1481158+12@gmail.com",
      *                 "gender": "1",
      *                 "specialization": "software developer",
      *                 "address": "vill uttra",
      *                 "skill": "java, php",
      *                 "work_ex_year": "3",
      *                 "candidate_dob": "2010-07-23 03:47:51",
      *                 "resume": "http://127.0.0.1:8000/resume/1_1690092739.pdf",
      *                 "user_id": 1,
      *                 "id": 13
      *             }
      *         },
      *         "success": true,
      *         "data_msg": "Candidate added successfully."
      *     }
     *
     *
     * @apiErrorExample {json} Error-Response:
     *  HTTP 400
     * {
     *  "success":false,
     *  "error":{
     *      "code":1000,
     *      "msg":"The name field is required."
     *    }
     * }
     */
    public function createCandidate()
    {
        $validator = Validator::make(request()->all(), [
            'first_name' => 'required|max:40',
            'last_name' => 'required|max:40',
            'gender' => 'numeric|min:1|max:2',
            'contact_number' => 'min:10|max:14',
            'email' => 'required|email|max:100|unique:candidates',
            'specialization' => 'max:200',
            'work_ex_year' => 'numeric|min:0',
            'candidate_dob' => 'numeric',
            'address' => "string",
            'skill' => 'string',
            'resume' => 'required|mimes:pdf,doc,docx,jpeg,png,jpg'
        ]);

        if($validator->fails()){
            $errors = $validator->errors()->toJson();
            $response = setResponseData(array(), false, 1000, $errors);
            return response()->json($response, 400);
        }

        $postData = request()->all();

        $user = auth()->user();
        $statusCode = 200;
        if(isset($user->id) && !empty($user->id)) {
          $postData['user_id'] = $user->id;
          $candidate = Candidate::createCandidate($postData);
          $statusCode = $candidate['status_code'];
          if($candidate['status']) {
            $response = setResponseData(['data' => $candidate['data']], true, false, false, trans('auth.candidate_created'));
          } else {
            $response = setResponseData(array(), false, $statusCode, $candidate['msg']);
          }
        } else {
          $response = setResponseData(array(), false, 404, 'Record not found');
        }

        return response()->json($response, $statusCode);
    }
    /**
     * @api {get} candidates get candidates listing
     * @apiName candidates
     * @apiGroup User
     * @apiVersion 1.0.0
     *
     * @apiSuccessExample {json} Response
     *  HTTP 200 OK
      *     {
      *         "data": {
      *             "next_page": 2,
      *             "next_page_url": "http://127.0.0.1:8000/api/candidates?page_no=2",
      *             "pre_page_url": "http://127.0.0.1:8000/api/candidates?page_no=0",
      *             "prev_page": 0,
      *             "per_page": "10",
      *             "from": 1,
      *             "to": 10,
      *             "total_page": 2,
      *             "current_page": 1,
      *             "data": [
      *                 {
      *                     "candidate_id": 1,
      *                     "first_name": "sujeet",
      *                     "last_name": "kumar",
      *                     "email": "sk1481158@gmail.com",
      *                     "contact_no": null,
      *                     "specialization": "software developer",
      *                     "work_ex_year": 3,
      *                     "address": "vill uttra",
      *                     "skill": "java, php",
      *                     "candidate_dob": 1279837071,
      *                     "created_at": 1690065681,
      *                     "updated_at": 1690065681,
      *                     "resume": "http://127.0.0.1:8000/resume/1_1690085481.pdf"
      *                 },
      *                 {
      *                     "candidate_id": 2,
      *                     "first_name": "sujeet",
      *                     "last_name": "kumar",
      *                     "email": "sk1481158+1@gmail.com",
      *                     "contact_no": null,
      *                     "specialization": "software developer",
      *                     "work_ex_year": 3,
      *                     "address": "vill uttra",
      *                     "skill": "java, php",
      *                     "candidate_dob": 1279837071,
      *                     "created_at": 1690065773,
      *                     "updated_at": 1690065773,
      *                     "resume": "http://127.0.0.1:8000/resume/1_1690085573.pdf"
      *                 }
      *             ]
      *         },
      *         "success": true,
      *         "data_msg": "Process successfully."
      *     }
     *
     *
     */
    public function getCandidates()
    {
      $statusCode = 500;
      $postData = request()->all();
      try {
        $candidates = Candidate::getCandidates($postData);
        $response = setResponseData($candidates, true, false, false, trans('auth.general_sucess'));
        $statusCode = 200;
      } catch (\Exception $e) {
          $response = setResponseData(array(), false, 500, 'Error occured while processing request');
      }

      return response()->json($response, $statusCode);
    }
    /**
     * @api {get} candidates/{id} get candidates by id
     * @apiName getCandidatesBySearch
     * @apiGroup User
     * @apiVersion 1.0.0
     *
     * @apiSuccessExample {json} Response
     *  HTTP 200 OK
      *     {
      *         "data": {
      *             "candidate_id": 1,
      *             "first_name": "sujeet",
      *             "last_name": "kumar",
      *             "email": "sk1481158@gmail.com",
      *             "contact_no": null,
      *             "specialization": "software developer",
      *             "work_ex_year": 3,
      *             "address": "vill uttra",
      *             "skill": "java, php",
      *             "candidate_dob": 1279837071,
      *             "created_at": 1690065681,
      *             "updated_at": 1690065681,
      *             "resume": "http://127.0.0.1:8000/resume/1_1690085481.pdf"
      *         },
      *         "success": true,
      *         "data_msg": "Process successfully."
      *     }
     *
     *
     */
    public function getCandidatesBySearch($id)
    {
      $statusCode = 500;
      $postData = request()->all();

      $candidateId = $searchText = false;
      if(is_numeric($id)) {
        $candidateId = $id;
      } else {
        $searchText = str_replace(' ', '',trim($id));
      }

      try {
        $candidates = Candidate::getCandidates($postData, $candidateId, $searchText);
        if($candidateId) {
            if(empty($candidates['data'])) {
              $response = setResponseData(array(), false, 1026, 'Record not found');
            } else {
              $response = setResponseData($candidates['data'][0], true, false, false, trans('auth.general_sucess'));
            }
        } else {
          $response = setResponseData($candidates, true, false, false, trans('auth.general_sucess'));
        }

        $statusCode = 200;
      } catch (\Exception $e) {
          $response = setResponseData(array(), false, 500, 'Error occured while processing request');
      }

      return response()->json($response, $statusCode);
    }

}
