<?php

namespace App\Http\Controllers;

use App\Models\Banner;
use Illuminate\Http\Request;

class BannerController extends Controller
{
    public function index()
    {
        $banners = Banner::all();
        return response()->json($banners);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'description' => 'required|string|max:255',
            'nom_pictures' => 'nullable|string|max:255',
            'banner_app' => 'required|string|max:255',
        ]);

        $banner = Banner::create($validated);
        return response()->json($banner, 201);
    }

    public function show(Banner $banner)
    {
        return response()->json($banner);
    }

    public function update(Request $request, Banner $banner)
    {
        $validated = $request->validate([
            'description' => 'sometimes|required|string|max:255',
            'nom_pictures' => 'nullable|string|max:255',
            'banner_app' => 'sometimes|required|string|max:255',
        ]);

        $banner->update($validated);
        return response()->json($banner);
    }

    public function destroy(Banner $banner)
    {
        $banner->delete();
        return response()->json(null, 204);
    }

    public function getByApp($app)
    {
        $banners = Banner::where('banner_app', $app)->get();
        return response()->json($banners);
    }
}
