<?php

namespace App\Http\Controllers\Api;

use App\Models\Comment;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\CommentResource;
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Gate;

class CommentController extends Controller
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
        //get comments
        $comments = Comment::all();

        //return collection of comments as a resource
        if($comments){
            return new CommentResource(true, 'Comments retrieved successfully', $comments);
        }else{
            return new CommentResource(false, 'Comments retrieved failed');
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
        $data = $request->only('body', 'post_id');
        $validator = Validator::make($data, [
            'body' => 'required',
            'post_id' => 'required'
        ]);
        //Send failed response if request is not valid
        if ($validator->fails()) {
            return response()->json(['error' => $validator->messages()], 200);
        }

        //Request is valid, create new data
        $comment = Comment::create([
            'body'    => $request->body,
            'post_id' => $request->post_id,
            'user_id' => $this->user->id
        ]);
        if($comment){
            //Data created, return success response
            return new CommentResource(true, 'Comment created successfully!', $comment);
        }else{
            return new CommentResource(false, 'Comment created failed!');
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Comment  $comment
     * @return \Illuminate\Http\Response
     */
    public function show(Comment $comment)
    {
        //return single data as a resource
        if ($comment) {
            return new CommentResource(true, 'Comment found!', $comment);
        } else {
            return new CommentResource(false, 'Comment not found!');
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Comment  $comment
     * @return \Illuminate\Http\Response
     */
    public function edit(Comment $comment)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Comment  $comment
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Comment $comment)
    {
        if (Gate::forUser($this->user->role)->allows('isRole', $comment->user->role)) {
            //Validate data
            $data = $request->only('body', 'post_id');
            $validator = Validator::make($data, [
                'body' => 'required',
                'post_id' => 'required'
            ]);
            //Send failed response if request is not valid
            if ($validator->fails()) {
                return response()->json(['error' => $validator->messages()], 200);
            }

            //Request is valid, update data
            $ok = $comment->update([
                'body'    => $request->body,
                'post_id' => $request->post_id,
                'user_id' => $this->user->id
            ]);
            if($ok){
                //Data updated, return success response
                return new CommentResource(true, 'Comment updated successfully!', $comment);
            }else{
                return new CommentResource(false, 'Comment updated failed!');
            }
        }
        if (Gate::forUser($this->user->role)->denies('isRole', $comment->user->role)) {
            return response()->json([
                'success' => false,
                'message' => 'Can not delete data.'
            ], 400);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Comment  $comment
     * @return \Illuminate\Http\Response
     */
    public function destroy(Comment $comment)
    {
        if (Gate::forUser($this->user->role)->allows('isRole', $comment->user->role)) {
            $ok = $comment->delete();
            if($ok){
                return new CommentResource(true, 'Comment deleted successfully!', null);
            }else{
                return new CommentResource(false, 'Comment deleted failed!');
            }
        }
        if (Gate::forUser($this->user->role)->denies('isRole', $comment->user->role)) {
            return response()->json([
                'success' => false,
                'message' => 'Can not delete data.'
            ], 400);
        }
    }
}
