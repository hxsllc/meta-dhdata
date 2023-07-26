<?php

namespace App\Http\Controllers;

use App\Models\Image;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class MetaImageController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $data = $request->json()->all();

        // Create a new instance of the Image model and set its attributes
        $image = new Image();
        $image->metascripta_id = $data['metascripta_id'];
        $image->frame = $data['frame'];
        $image->format = $data['format'];
        $image->width = $data['width'];
        $image->height = $data['height'];

        // Save the image record
        if ($image->save()) {
            return response()->json(['message' => 'Image saved successfully'], Response::HTTP_OK);
        } else {
            return response()->json(['message' => 'Failed to save image'], Response::HTTP_BAD_REQUEST);
        }
    }
}
