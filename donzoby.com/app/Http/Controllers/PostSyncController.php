<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\{Post_image, Post_sync};
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PostSyncController extends Controller
{
    public function sync(Request $request)
    {
        try {
            // Log::info('Received data::' . json_encode($request->all()));
            $target_post = null;
            if ($request->has('is_new_post') || $request->has('just_syncing')) {
                // if post already exist for just_syncing action, just update
                $post_exists = Post::find($request->id);
                if ($post_exists) {
                    Log::info('^^^^^^^^^^^^^Just syncing and post exists^^^^^^^^^^');
                    $post_exists->update($request->toArray());
                    $target_post = $post_exists;
                } else {
                    $last_post_id = Post::latest('id')->first()->id;

                    // add dummy (unpublished) posts till $id-1 while last post id + 1 < $request->id
                    while ($last_post_id + 1 < $request->id) {
                        $dummy_post = Post::create([
                            "topic" => "dummy",
                            "content" => "dummy",
                            "status" => "unpublished",
                            "description" => "dummy",
                            "tags" => "dummy",
                            "type"  => "course-series",
                            "subject_id" => $request->subject_id,
                            "author_id" => 1,
                            'comment_count' => 0,
                            'slug' => 'dummy' . $last_post_id + 1,
                        ]);
                        $last_post_id = $dummy_post->id;
                    }
                    // if last post id + 1 is not equal to received id, the post is out of sync, return error
                    if ($last_post_id + 1 != $request->id) {
                        return response()->json([
                            'status' => 'error',
                            'message' => 'Post out of sync. Next post id is ' . ($last_post_id + 1) . ' and received id is ' . $request->id . '.',
                        ], 422);
                    }
                    $post = Post::create($request->toArray() + ['author_id' => 1]);
                    $target_post = $post;
                }
            } else { // it is post edit
                // update the post
                Log::info('::::::::::::::::::::::::RECEIVED POST ID:::::::::::::::::::::::::' . $request->id);
                $post = Post::where('id', $request->id)->first();
                if (!$post) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Post not found. First sync this post then you can edit it.',
                    ], 422);
                }
                $post->update($request->toArray());
                $target_post = $post;
            }
            // if there are uploaded images for the post, process it
            if ($request->has('added_images')) {
                var_dump($request->added_images);
                Log::info('Added images::' . json_encode($request->added_images));
            }
            // process uploaded images
            // ****For some reason (the curl loop for adding multiple images modifies the added_images array into a string containing only one image)
            // ****Down the line, I will need to find a fix for that but for now, the below works
            foreach ($request->file() as $image) {
                Log::info('+++++++++++++++++++IMAGE FILE FOUND+++++++++++++++++++');
                $image->move("images/courses/{$post->subject->slug}", $image->getClientOriginalName());
                // add to post images
                $post->post_images()->create(['link' => "images/courses/{$post->subject->slug}/{$image->getClientOriginalName()}"]);
            }
            // delete images if there are removed_images
            if ($request->has('removed_images')) {
                var_dump($request->removed_images);
                foreach ($request->removed_images as $image) {
                    $db_image = Post_image::where('link', $image)->first();
                    if ($db_image) {
                        $db_image->delete(); // delete from db
                    }

                    Log::info('------Removed image::' . json_encode($request->removed_images));
                    if (file_exists($image)) {
                        Log::info('found image file and about to delete:::' . $image);
                        unlink($image);
                    }
                }
            }
            // update sync status
            $post_sync_array = $request->toArray()['post_sync'];
            $post_sync_array['synced'] = true;
            $target_post->post_syncs()->create($post_sync_array);

            return [
                'status' => 'success',
                'message' => 'post successfully synced',
            ];
        } catch (Exception $e) {
            Log::error('An error occurred while syncing post.');
            Log::error($e);
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while syncing post.',
            ], 500);
        }
    }

    public function update_sync_status(Request $request)
    {
        $request->validate([
            'id' => ['required', 'exists:posts,id'],
        ]);

        $last_sync = Post_sync::where('post_id', $request->id)->latest()->first();
        if (!$last_sync || $last_sync->synced == true) {
            $last_sync = Post_sync::create([
                'post_id' => $request->id,
                'what_changed' => ['all'],
                'change_origin' => 'live',
            ]);
        }
        $last_sync->synced = true;
        $last_sync->sync_attempts += 1;
        $last_sync->save();
        return [
            'status' => 'success',
            'message' => 'post sync status successfully updated',
            'data' => $last_sync,
        ];
    }
}
