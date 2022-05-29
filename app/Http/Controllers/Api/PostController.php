<?php

namespace App\Http\Controllers\Api;

use App\Models\Post;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\PostResource;
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Gate;

class PostController extends Controller
{
    protected $user;
 
    public function __construct()
    {
        $this->user = JWTAuth::parseToken()->authenticate();
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //get posts
        $posts = Post::where('title','like', '%' . request('query') . '%')->paginate(5);

        //return collection of posts as a resource
        if($posts){
            return new PostResource(true, 'Posts retrieved successfully', $posts);
        }else{
            return new PostResource(false, 'Posts retrieved failed');
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //Validate data
        $data = $request->only('title', 'content');
        $validator = Validator::make($data, [
            'title' => 'required|string',
            'content' => 'required'
        ]);
        //Send failed response if request is not valid
        if ($validator->fails()) {
            return response()->json(['error' => $validator->messages()], 200);
        }

        //Request is valid, create new data
        $post = Post::create([
            'title'   => $request->title,
            'content' => $request->content,
            'user_id' => $this->user->id
        ]);
        if($post){
            //Data created, return success response
            return new PostResource(true, 'Post created successfully!', $post);
        }else{
            return new PostResource(false, 'Post created failed!');
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Post  $post
     * @return \Illuminate\Http\Response
     */
    public function show(Post $post)
    {
        //return single data as a resource
        if ($post) {
            return new PostResource(true, 'Post found!', $post);
        } else {
            return new PostResource(false, 'Post not found!');
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Post  $post
     * @return \Illuminate\Http\Response
     */
    public function edit(Post $post)
    {
        // 
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Post  $post
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Post $post)
    {
        if (Gate::forUser($this->user->role)->allows('isRole', $post->user->role)) {
            //Validate data
            $data = $request->only('title', 'content');
            $validator = Validator::make($data, [
                'title' => 'required|string',
                'content' => 'required'
            ]);
            //Send failed response if request is not valid
            if ($validator->fails()) {
                return response()->json(['error' => $validator->messages()], 200);
            }

            //Request is valid, update data
            $ok = $post->update([
                'title'   => $request->title,
                'content' => $request->content,
                'user_id' => $this->user->id
            ]);
            if($ok){
                //Data updated, return success response
                return new PostResource(true, 'Post updated successfully!', $post);
            }else{
                return new PostResource(false, 'Post updated failed!');
            }
        }
        if (Gate::forUser($this->user->role)->denies('isRole', $post->user->role)) {
            return response()->json([
                'success' => false,
                'message' => 'Post, can not updated data.'
            ], 400);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Post  $post
     * @return \Illuminate\Http\Response
     */
    public function destroy(Post $post)
    {  
        if (Gate::forUser($this->user->role)->allows('isRole', $post->user->role)) {
            $ok = $post->delete();
            if($ok){
                return new PostResource(true, 'Post deleted successfully!', null);
            }else{
                return new PostResource(false, 'Post deleted failed!');
            }
        }
        if (Gate::forUser($this->user->role)->denies('isRole', $post->user->role)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Can not delete data.'
                ], 400);
        }
    }
}
