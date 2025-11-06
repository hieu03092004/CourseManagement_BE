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

    public function delete($id)
    {
        $this->deleteDiscussionTree($id);

        return response()->json([
            'message' => 'Xoá discussion thành công'
        ]);
    }

    private function deleteDiscussionTree($id)
    {
        $children = Discussion::where('parent_id', $id)->get();

        foreach ($children as $child) {
            $this->deleteDiscussionTree($child->discussion_id);
        }

        ParentDiscussion::where('parent_id', $id)->delete();

        Discussion::where('discussion_id', $id)->delete();
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

    //lấy 3 discussion cha (parent_id = null)
    public function showparent($quizId)
    {
        $discussion = Discussion::where('quiz_id', $quizId)
            ->whereNull('parent_id')
            ->orderByDesc('discussion_id')
            ->limit(3)
            ->get();

        return response()->json([
            'data' => $discussion
        ]);
    }

    //lấy all discussion cha (parent_id = null)
    public function showallparent($quizId)
    {
        $discussion = Discussion::where('quiz_id', $quizId)
            ->whereNull('parent_id')
            ->orderByDesc('discussion_id')
            ->get();

        return response()->json([
            'data' => $discussion
        ]);
    }

    //lấy 3 discuss con
    public function showchild($quizId, $parentId)
    {
        $discussion = Discussion::where('quiz_id', $quizId)
            ->where('parent_id', $parentId)
            ->orderByDesc('discussion_id')
            ->limit(3)
            ->get();

        return response()->json([
            'data' => $discussion
        ]);
    }

    //lấy all discuss con
    public function showchildren($quizId, $parentId)
    {
        $discussion = Discussion::where('quiz_id', $quizId)
            ->where('parent_id', $parentId)
            ->orderByDesc('discussion_id')
            ->get();

        return response()->json([
            'data' => $discussion
        ]);
    }
}
