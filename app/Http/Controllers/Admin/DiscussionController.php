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
}
