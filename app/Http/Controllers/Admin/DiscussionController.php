<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Discussion;
use App\Models\ParentDiscussion;
use Illuminate\Http\Request;

class DiscussionController extends Controller
{
    public function create(Request $request)
    {

        $discussion = Discussion::create([
            'user_id' => $request->user_id,
            'parent_id' => $request->parent_id,
            'quiz_id' => $request->quiz_id,
            'context' => $request->context
        ]);

        ParentDiscussion::create([
            'parent_id' => $discussion->discussion_id
        ]);

        return response()->json([
            'message' => 'Tạo discussion thành công',
            'data' => $discussion
        ]);
    }

    public function delete($discussionId)
    {
        $discussion = Discussion::findOrFail($discussionId);

        if (!$discussion->parent_id) {
            Discussion::where('parent_id', $discussionId)->delete();

            ParentDiscussion::where('parent_id', $discussionId)->delete();
        }

        $discussion->delete();

        return response()->json([
            'message' => 'Xoá discussion thành công'
        ]);
    }


    public function edit(Request $request, $discussionId)
    {
        $discussion = Discussion::findOrFail($discussionId);

        $discussion->update([
            'context' => $request->context ?? $discussion->context
        ]);

        return response()->json([
            'message' => 'Cập nhật discussion thành công',
            'data' => $discussion
        ]);
    }
}
