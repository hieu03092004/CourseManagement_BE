<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Discussion;
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

        return response()->json([
            'message' => 'Tạo discussion thành công',
            'data' => $discussion
        ]);
    }

    public function delete($discussionId)
    {
        $discussion = Discussion::findOrFail($discussionId);

        $this->deleteRecursive($discussion);

        return response()->json([
            'message' => 'Xoá discussion thành công'
        ]);
    }

    private function deleteRecursive($discussion)
    {
        foreach ($discussion->children as $child) {
            $this->deleteRecursive($child);
        }

        $discussion->delete();
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

    public function showByQuiz($quizId)
    {
        $discussions = Discussion::with('children')
            ->where('quiz_id', $quizId)
            ->whereNull('parent_id')
            ->get();

        return response()->json([
            'message' => 'Lấy discussion thành công',
            'data' => $discussions
        ]);
    }

    // lấy 3 bình luận (cha) mới nhất theo quiz
    public function getParentByQuiz($quizId)
    {
        $discussions = Discussion::where('quiz_id', $quizId)
            ->whereNull('parent_id')
            ->orderByDesc('discussion_id')
            ->take(3)
            ->get();

        return response()->json([
            'message' => 'OK',
            'data' => $discussions
        ]);
    }

    // lấy ALL bình luận cha theo quiz
    public function getAllParentByQuiz($quizId)
    {
        $discussions = Discussion::where('quiz_id', $quizId)
            ->whereNull('parent_id')
            ->orderByDesc('discussion_id')
            ->get();

        return response()->json([
            'message' => 'OK',
            'data' => $discussions
        ]);
    }

    // lấy 3 bình luận con của 1 cha
    public function getChildByParent($parentId)
    {
        $children = Discussion::where('parent_id', $parentId)
            ->orderByDesc('discussion_id')
            ->take(3)
            ->get();

        return response()->json([
            'message' => 'OK',
            'data' => $children
        ]);
    }

    // lấy ALL bình luận con của 1 cha
    public function getAllChildByParent($parentId)
    {
        $children = Discussion::where('parent_id', $parentId)
            ->orderByDesc('discussion_id')
            ->get();

        return response()->json([
            'message' => 'OK',
            'data' => $children
        ]);
    }

    // đếm tổng số lượng bình luận của 1 quiz (bao gồm cả cha + con)
    public function countDiscussionByQuiz($quizId)
    {
        $count = Discussion::where('quiz_id', $quizId)->count();

        return response()->json([
            'message' => 'OK',
            'count'   => $count
        ]);
    }

    // đếm tổng số lượng trả lời của 1 cha
    public function countReplyByParent($parentId)
    {
        $count = Discussion::where('parent_id', $parentId)->count();

        return response()->json([
            'message' => 'OK',
            'count'   => $count
        ]);
    }
}
