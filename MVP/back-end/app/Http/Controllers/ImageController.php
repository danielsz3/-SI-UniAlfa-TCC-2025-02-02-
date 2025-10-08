<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;


class ImageController extends Controller
{
    public function show($folder, $filename)
    {
        $path = "public/{$folder}/{$filename}";

        if (!Storage::disk('public')->exists("{$folder}/{$filename}")) {
            dd($path);
            return response()->json(['error' => 'Imagem não encontrada.'], 404);
        }

        $file = Storage::disk('public')->get("{$folder}/{$filename}");
        $type = Storage::mimeType($path);

        return response($file, 200)->header('Content-Type', $type);
    }
}
