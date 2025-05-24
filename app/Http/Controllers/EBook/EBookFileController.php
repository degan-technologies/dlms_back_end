<?php

namespace App\Http\Controllers\EBook;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Controllers\Controller;

class EBookFileController extends Controller
{
    public function servePdf($filename)
    {
        $path = storage_path('app/public/ebooks/' . $filename);
        if (!file_exists($path)) {
            abort(404);
        }
        $response = response()->file($path, [
            'Content-Type' => 'application/pdf',
            'Access-Control-Allow-Origin' => '*',
            'Access-Control-Allow-Methods' => 'GET, OPTIONS',
            'Access-Control-Allow-Headers' => 'Origin, Content-Type, Accept, Authorization',
        ]);
        // For OPTIONS preflight requests
        if (request()->getMethod() === 'OPTIONS') {
            $response->headers->set('Access-Control-Allow-Origin', '*');
            $response->headers->set('Access-Control-Allow-Methods', 'GET, OPTIONS');
            $response->headers->set('Access-Control-Allow-Headers', 'Origin, Content-Type, Accept, Authorization');
        }
        return $response;
    }
}
