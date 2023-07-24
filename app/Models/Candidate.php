<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use DB;
use Config;

class Candidate extends Model
{
    use HasFactory;
    public $timestamps = FALSE;

    protected $table = "candidates";
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'first_name',
        'last_name',
        'email',
        'contact_no',
        'gender',
        'specialization',
        'address',
        'skill',
        'resume',
        'work_ex_year',
        'candidate_dob',
    ];

    public static function createCandidate($postData)
    {
      $response['status'] = false;
      $response['status_code'] = 201;
      $response['msg'] = 'Error occur during processing your request.';
      $resumeFile = $postData['resume'];
      try {
        $fileName = auth()->id() . '_' . time() . '.'. $resumeFile->extension();
        $td = $resumeFile->move(public_path('resume'), $fileName);
        $postData['resume'] = $fileName;
        $postData['candidate_dob'] = (isset($postData['candidate_dob']) && !empty($postData['candidate_dob'])) ? date("Y-m-d H:i:s", $postData['candidate_dob']) : NULL;

        $candidate = self::create($postData);
        if(isset($candidate->id) && !empty($candidate->id)) {
          $response['status'] = true;
          if(!empty($candidate)) {
            $candidate['resume'] = url('resume') .'/' . $candidate['resume'];
          }
          $response['data'] = $candidate;
        }
      } catch (\Exception $e) {
        $response['status_code'] = 500;
        $response['msg'] = $e->getMessage();
      }

      return $response;
    }

    public static function getCandidates($postData, $candidateId = false, $searchText = false)
    {
      $pageLimit = config('app.page_limit');

      $response['next_page'] = -1;
      $response['next_page_url'] = url('api/candidates?page_no='). '-1';
      $response['pre_page_url'] = url('api/candidates?page_no='). '0';
      $response['prev_page'] = 0;
      $response['per_page'] = $pageLimit;
      $response['from'] = 0;
      $response['to'] = 0;
      $response['total_page'] = 0;
      $postData['page_no'] = isset($postData['page_no']) && !empty($postData['page_no']) ? $postData['page_no'] : 1;
      $response['current_page'] = $postData['page_no'];
      $response['data'] = [];

      $userId = auth()->id();
      $candidates = self::select(['id as candidate_id', 'first_name', 'last_name', 'email', 'contact_no', 'specialization', 'work_ex_year', 'address', 'skill'])
                    ->selectRaw('if(candidate_dob IS NOT NULL, FLOOR(unix_timestamp(candidate_dob)), NULL) as candidate_dob')
                    ->selectRaw('if(created_at IS NOT NULL, FLOOR(unix_timestamp(created_at)), NULL) as created_at')
                    ->selectRaw('if(updated_at IS NOT NULL, FLOOR(unix_timestamp(updated_at)), NULL) as updated_at')
                    ->selectRaw('if(resume IS NOT NULL, CONCAT(?, "/", resume), NULL) as resume', [url('resume')])
                    ->where('user_id', $userId);

      if($candidateId) {
        $candidates->where('id', $candidateId);
      }

      if($searchText) {
        $candidates->whereRaw('first_name like (?)', ["%{$searchText}%"]);
        $candidates->orWhereRaw('last_name like (?)', ["%{$searchText}%"]);
        $candidates->orWhereRaw('REPLACE(CONCAT(first_name, last_name), "", "") like (?)', ["%{$searchText}%"]);
        $candidates->orWhereRaw('email like (?)', ["%{$searchText}%"]);
        $candidates->orWhereRaw('contact_no like (?)', ["%{$searchText}%"]);
        $candidates->orWhereRaw('address like (?)', ["%{$searchText}%"]);
        $candidates->orWhereRaw('specialization like (?)', ["%{$searchText}%"]);
        $candidates->orWhereRaw('skill like (?)', ["%{$searchText}%"]);
      }

      $count = $candidates->count();

      if (isset($postData['page_no']) && !empty($postData['page_no'])) {
        $pageNo = $postData['page_no'];
        $offset = ($postData['page_no'] - 1) * $pageLimit;
        $candidates->offset($offset)->limit(2);
      }

      $candidates = $candidates->get();

      if($count > 0) {
        //  apply pegination
        $response['total_page'] = (int) ceil($count/$pageLimit);
        $response['from'] = ($postData['page_no'] - 1) * $pageLimit + 1;
        $response['to'] = ($response['total_page'] > $postData['page_no']) ?  ($postData['page_no'] * $pageLimit): $count;
        //if next page is within total page
        if (isset($postData['page_no']) && !empty($postData['page_no'])) {
          if (($postData['page_no'] + 1) <= $response['total_page']) {
            $response['next_page'] = $postData['page_no'] + 1;
            $response['prev_page'] = $postData['page_no'] - 1;
            $response['next_page_url'] = url('api/candidates?page_no='). $response['next_page'];
            $response['pre_page_url'] = url('api/candidates?page_no='). $response['prev_page'];
          }
        }

        $response['data'] = $candidates;
      }

      return $response;
    }
}
