<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

use function App\Helpers\deleteFile;
use function App\Helpers\returnData;
use function App\Helpers\returnSuccessMessage;
use function App\Helpers\returnValidationError;
use function App\Helpers\uploadFile;

class PostController extends Controller
{
    public function index(Request $request)
    {
        $posts = Post::where('user_id', $request->user()->id)->get();

        return returnData('data', $posts, 'My Posts List');
    }

    public function all_posts(Request $request)
    {
        $posts = Post::where(function($q) use ($request) {
            if ($request->type) {
                $q->where('type', $request->type);
            }else {
                $q->where('type', 1);
            }
            if ($request->searchText) {
                $q->where('title', "LIKE", "%$request->searchText%")->orWhere('description', "LIKE", "%$request->searchText%");
            }
        })->get();

        return returnData('data', $posts, 'All User Posts List');
    }

    public function store(Request $request)
    {
        $rules = [
            "title"          => "required|string|max:255",
            "description"    => "required|string",
            "image"          => "required|image|mimes:png,jpg,svg|max:2048",
            "type"           => "required|in:1,2",
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return returnValidationError("N/A", $validator);
        }

        $data = [
            'title'         => $request->title,
            'description'   => $request->description,
            'type'          => $request->type ?? 1,
            'user_id'       => $request->user()->id,
            'image'         => $request->image ? uploadFile('images/posts', $request->image, 'posts') : 'default.png',
        ];

        $post = Post::create($data);

        return returnData('data', $post, 'Post Created Successfully');
    }

    public function show(Post $post)
    {
        return returnData('data', $post, 'Show Post');
    }

    public function update(Request $request, Post $post)
    {
        $rules = [
            "title"          => "required|string|max:255",
            "description"    => "required|string",
            "image"          => "required|image|mimes:png,jpg,svg|max:2048",
            "type"           => "required|in:1,2",
        ];

        $validator = Validator::make($request->all(), $rules, [
            "type.in"   => 'Type In Public and Private',
        ]);

        if ($validator->fails()) {
            return returnValidationError("N/A", $validator);
        }

        $data = [
            'title'         => $request->title,
            'description'   => $request->description,
            'type'          => $request->type,
            'user_id'       => $request->user()->id,
            'image'         => $request->image ? uploadFile('images/posts', $request->image, 'posts') : $post->image,
        ];

        $post->update($data);

        return returnData('data', $post, 'Post Updated Successfully');
    }

    public function destroy(Post $post)
    {
        if ($post->image != 'default.png') {
            deleteFile('posts', $post->id, 'image');
        }
        $post->delete();

        return returnSuccessMessage('Post Deleted Successfully');
    }
}
