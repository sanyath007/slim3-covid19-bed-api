<?php

namespace App\Controllers;

use App\Controllers\Controller;
use Illuminate\Database\Capsule\Manager as DB;
use Respect\Validation\Validator as v;
use App\Models\Bed;
use App\Models\Registration;

class BedController extends Controller
{
    public function getAll($request, $response, $args)
    {
        $page = (int)$request->getQueryParam('page');
        $withStatus = (int)$request->getQueryParam('status') === 0 ? false : true ;

        $model = Bed::with('bedType', 'ward', 'regis')
                    ->when($withStatus, function($q) use ($request) {
                        $q->where('bed_status', '1');
                    })
                    ->orderBy('bed_no');

        if ($page) {
            $data = paginate($model, 10, $page, $request);
        } else {
            $data = [
                'items' => $model->get()
            ];
        }

        return $response->withStatus(200)
                ->withHeader("Content-Type", "application/json")
                ->write(json_encode($data, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT |  JSON_UNESCAPED_UNICODE));
    }

    public function getById($request, $response, $args)
    {
        $room = Bed::where('bed_id', $args['id'])->first();
                    
        $data = json_encode($room, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT |  JSON_UNESCAPED_UNICODE);

        return $response->withStatus(200)
                ->withHeader("Content-Type", "application/json")
                ->write($data);
    }

    public function getByWard($request, $response, $args)
    {
        if($args['status'] == '0') {
            $rooms = Bed::where(['ward' => $args['ward']])
                    ->orderBy('bed_no')
                    ->get();
        } else {
            $rooms = Bed::where(['ward' => $args['ward'], 'bed_status' => 0])
                    ->orderBy('bed_no')
                    ->get();
        }
                    
        $data = json_encode($rooms, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT |  JSON_UNESCAPED_UNICODE);

        return $response->withStatus(200)
                ->withHeader("Content-Type", "application/json")
                ->write($data);
    }
    
    public function getBedIsUsed($request, $response, $args)
    {
        $used = Bed::with('regis', 'regis.patient')
                    ->where(['bed_id' => $args['id']])
                    // ->whereNull('dch_date')
                    ->first();

        $data = json_encode([
            'used' => $used,
        ], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT |  JSON_UNESCAPED_UNICODE);

        return $response->withStatus(200)
                ->withHeader("Content-Type", "application/json")
                ->write($data);
    }

    public function store($request, $response, $args)
    {
        $post = (array)$request->getParsedBody();

        try {
            /** Upload image */
            // $img_url = $this->uploadImg();

            $bed = new Bed;
            $bed->bed_no = $post['bed_no'];
            $bed->bed_name = $post['bed_name'];
            $bed->description = $post['description'];
            $bed->bed_type = $post['bed_type'];
            $bed->ward = $post['ward'];
            $bed->bed_status = 0;

            // if ($img_url) {
                // $bed->bed_img_url = $img_url : ;
            // }

            if($bed->save()) {
                $newBedId = $bed->bed_id;

                return $response->withStatus(200)
                        ->withHeader("Content-Type", "application/json")
                        ->write(json_encode([
                            'status' => 1,
                            'message' => 'Insertion successfully!!',
                            'bed' => $bed
                        ], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT |  JSON_UNESCAPED_UNICODE));
            } // end if
        } catch (\Throwable $th) {
            /** Delete new bed if error occurs */
            Bed::find($newBedId)->delete();
            
            /** And set data to client with http status 500 */
            return $response->withStatus(500)
                    ->withHeader("Content-Type", "application/json")
                    ->write(json_encode([
                        'status' => 0,
                        'message' => 'Something went wrong!!'
                    ], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT |  JSON_UNESCAPED_UNICODE));
        } // end trycatch
    }

    private function uploadImg()
    {
        $link = 'http://'.$request->getServerParam('SERVER_NAME').$request->getServerParam('REDIRECT_URL');
        if(preg_match("/^data:image\/(?<extension>(?:png|gif|jpg|jpeg));base64,(?<image>.+)$/", $post['bed_img_url'], $matchings))
        {
            $img_data = file_get_contents($post['bed_img_url']);
            $extension = $matchings['extension'];
            $img_name = uniqid().'.'.$extension;
            $img_url = str_replace('/rooms', '/assets/uploads/'.$img_name, $link);
            $file_to_upload = 'assets/uploads/'.$img_name;

            if(file_put_contents($file_to_upload, $img_data)) {
                return $img_url;
            }
        }

        return false;
    }

    public function update($request, $response, $args)
    {
        $post = (array)$request->getParsedBody();

        $bed = Bed::where('bed_id', $args['id'])->first();
        $bed->bed_no = $post['bed_no'];
        $bed->bed_name = $post['bed_name'];
        $bed->description = $post['description'];
        $bed->bed_type = $post['bed_type'];
        $bed->ward = $post['ward'];
        $bed->bed_status = $post['bed_status'];

        if($bed->save()) {   
            $data = [
                'status' => 1,
                'message' => 'Update successfully!!',
                'bed' => $bed
            ];

            return $response->withStatus(200)
                    ->withHeader("Content-Type", "application/json")
                    ->write(json_encode($data, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT |  JSON_UNESCAPED_UNICODE));
        }
    }

    public function delete($request, $response, $args)
    {
        try {
            $bed = Bed::where('bed_id', $args['id'])->first();
            
            if($bed->delete()) {
                return $response->withStatus(200)
                        ->withHeader("Content-Type", "application/json")
                        ->write(json_encode($bed, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT |  JSON_UNESCAPED_UNICODE));
            }
        } catch (\Exception $ex) {
            return $response->withStatus(500)
                    ->withHeader("Content-Type", "application/json")
                    ->write(json_encode([
                        'message' => 'Something went wrong !!!',
                        'error' => $ex->getMessage()
                    ], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT |  JSON_UNESCAPED_UNICODE));
        }
    }
}
