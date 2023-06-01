<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\Post;
use Illuminate\Support\Str;

use Illuminate\Http\Request;

class DashboardPostController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

        return view('dashboard.posts.index',[
            'posts' => Post::where('user_id', auth()->user()->id)->get()
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view ('dashboard.posts.create',[
            'categories' => Category::all()
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validateData = $request->validate([
                'title'=> 'required|max:255',
                'slug' => 'required|unique:posts',
                'category_id' => 'required',
                'image' => 'image|file|max:1024',
                'body' => 'required'
        ]);
        if ($request->file('image')) {
            $validateData['image'] = $request->file('image')->store('post-images');
        }
        $validateData['user_id'] = auth()->user()->id;
        $validateData['excerpt'] = Str::limit(strip_tags($request->body),200);

        Post::create($validateData);

        return redirect('/dashboard/posts')->with('success','News Post has been added!');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Post $post)
    {
        return view('dashboard.posts.show',[
            'post' => $post
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Post $post)
    {
        return view ('dashboard.posts.edit',[
            'post' => $post,
            'categories' => Category::all()
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Post $post)
    {
        $rules = [
            'title'=> 'required|max:255',
            'category_id' => 'required',
            'image' => 'file|max:1024',
            'body' => 'required',
    ];


    if($request->slug !=$post->slug) {
        $rules['slug'] = 'required|unique:posts';
    }
    $validateData = $request->validate($rules);

    if ($request->file('image')) {
        if($request->oldImage) {
            Storage::delete($request->oldImage);
        }
        $validateData['image'] = $request->file('image')->store('post-images');
    }

    $validateData['user_id'] = auth()->user()->id;
    $validateData['excerpt'] = Str::limit(strip_tags($request->body),200);

    Post::where('id',$post->id)
        ->update($validateData);

    return redirect('/dashboard/posts')->with('success',' Post has been updated');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Post $post)
    {
        if($post->image) {
            Storage::delete($post->image);
        }
        Post::destroy($post->id);

        return redirect('/dashboard/posts')->with('success','Post has been deleted');
    }


    // contoh penggunaan sluggable
    // public function checkSlug(Request $request) {
    //     $slug = SlugService::createSlug(Post::class,'slug',$request->title);
    //     return response()->json(['slug' => $slug])
    // }
    // sluggable
}