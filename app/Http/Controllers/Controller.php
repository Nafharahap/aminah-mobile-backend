<?php

namespace App\Http\Controllers;

use App\Models\Borrower;
use Illuminate\Support\Facades\View;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function __construct()
    {
        $totalPengajuan = Borrower::pending()->count();
        View::share('totalPengajuan', $totalPengajuan);
    }

    protected function makeResponseData($payload, $success = true) {
        $data = array(
            'status' => $success ? 'success' : 'failed',
            'payload' => $payload
        );
        if (!$success) {
            $data['message'] = $payload;
        }

        return $data;
    }

    public function makeJson($payload, $success = true, $responseCode = 200)
    {
        $code = intval($responseCode);
        if ($code < 200 || $code > 500) {
            $code = 500;
        }
        return response()->json($this->makeResponseData($payload, $success), $code);
    }
}
