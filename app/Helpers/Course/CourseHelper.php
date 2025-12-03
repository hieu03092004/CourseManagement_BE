<?php

namespace App\Helpers\Course;

class CourseHelper
{
    /**
     * Upload image cho course
     *
     * @param \Illuminate\Http\UploadedFile $imageFile
     * @param string|null $deleteOldImage URL của ảnh cũ cần xóa
     * @return string|null URL của ảnh đã upload hoặc null nếu lỗi
     */
    public static function uploadImage($imageFile, $deleteOldImage = null)
    {
        if (!$imageFile || !$imageFile->isValid()) {
            return null;
        }

        $coursesPath = public_path('images/courses');
        if (!file_exists($coursesPath)) {
            mkdir($coursesPath, 0755, true);
        }

        if ($deleteOldImage) {
            $oldImagePath = str_replace(url('/'), '', $deleteOldImage);
            $oldImageFullPath = public_path($oldImagePath);
            if (file_exists($oldImageFullPath)) {
                unlink($oldImageFullPath);
            }
        }

        $imageName = time() . rand(1000, 9999) . '.' . $imageFile->extension();
        $imageFile->move($coursesPath, $imageName);

        return url('images/courses/' . $imageName);
    }
}

