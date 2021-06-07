<?php

namespace App\Http\Controllers;

use App\Models\Tweet;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;

class TweetController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $tweets = Tweet::with([
            'user' => fn($query) => $query->withCount([
                'followers as is_followed' => fn($query) 
                => $query->where('follower_id', auth()->user()->id)
            ])
            ->withCasts(['is_followed' => 'boolean'])
        ])
        ->orderBy('created_at', 'DESC')
        ->get();

        return Inertia::render('Tweets/Index', [
            'tweets' => $tweets,
        ]);        
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
        // dd($request);
        
        $request->validate([
            'content' => ['required', 'max:280'],
            'user_id' => ['exists:users,id']
        ]);
        
        Tweet::create([
            'content' => $request->input('content'),
            'user_id' => auth()->user()->id,
        ]);

        return Redirect::route('tweets.index');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    public function profile(User $user)
    {
        $profileUser = $user->loadCount([
            'followings as is_following_you' => fn($q) => $q->where('following_id', auth()->user()->id)
            ->withCasts(['is_following_you' => 'boolean']),
            'followers as is_followed' => fn($q) => $q->where('follower_id', auth()->user()->id)
                ->withCasts(['is_followed' => 'boolean']),

        ]);

        $tweets = $user->tweets;

        return Inertia::render('Tweets/Profile', [
            'profileUser' => $profileUser,
            'tweets' => $tweets,
        ]);

    }

    public function followings()
    {
        $followings = Tweet::with('user')
            ->whereIn('user_id', auth()->user()->followings->pluck('id')->toArray())->latest()->get();
        return Inertia::render('Tweets/Followings', [
            'followings' => $followings,
        ]); 
    }

    public function follows(User $user) 
    {
        auth()->user()->followings()->attach($user->id);

        return redirect()->route('tweets.index');
    }

    public function unfollows(User $user)
    {
        auth()->user()->followings()->detach($user->id);

        return redirect()->back();
    }

}
