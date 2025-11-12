<?php

namespace App\Models;

use App\Database\MySQLConnection;

class CourseModule
{
    /**
     * Lấy tất cả module
     *
     * @return array
     */
    public static function getAll()
    {
        $query = "SELECT * FROM COURSE_MODULES ORDER BY ORDER_INDEX ASC";
        $result = MySQLConnection::query($query);

        if (!$result) {
            return [];
        }

        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }

        return $data;
    }

    /**
     * Lấy module theo ID
     *
     * @param int $id
     * @return array|false
     */
    public static function find($id)
    {
        $conn = MySQLConnection::connect();
        $stmt = $conn->prepare("SELECT * FROM COURSE_MODULES WHERE COURSES_MODULES_ID = ?");
        if (!$stmt) return false;

        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    /**
     * Tạo module mới
     *
     * @param array $data
     * @return int|false
     */
    public static function create(array $data)
    {
        $conn = MySQLConnection::connect();

        $stmt = $conn->prepare("INSERT INTO COURSE_MODULES (COURSES_ID, ORDER_INDEX, TITLE) VALUES (?, ?, ?)");
        if (!$stmt) return false;

        $stmt->bind_param("iis", $data['COURSES_ID'], $data['ORDER_INDEX'], $data['TITLE']);

        if ($stmt->execute()) {
            return $conn->insert_id;
        }

        return false;
    }

    /**
     * Cập nhật module
     *
     * @param int $id
     * @param array $data
     * @return bool
     */
    public static function update($id, array $data)
    {
        $conn = MySQLConnection::connect();

        $stmt = $conn->prepare("UPDATE COURSE_MODULES SET COURSES_ID = ?, ORDER_INDEX = ?, TITLE = ? WHERE COURSES_MODULES_ID = ?");
        if (!$stmt) return false;

        $stmt->bind_param("iisi", $data['COURSES_ID'], $data['ORDER_INDEX'], $data['TITLE'], $id);
        return $stmt->execute();
    }

    /**
     * Xóa module
     *
     * @param int $id
     * @return bool
     */
    public static function delete($id)
    {
        $conn = MySQLConnection::connect();

        $stmt = $conn->prepare("DELETE FROM COURSE_MODULES WHERE COURSES_MODULES_ID = ?");
        if (!$stmt) return false;

        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }

    /**
     * Lấy tất cả module theo khóa học
     *
     * @param int $courseId
     * @return array
     */
    public static function getByCourseId($courseId)
    {
        $conn = MySQLConnection::connect();
        $stmt = $conn->prepare("SELECT * FROM COURSE_MODULES WHERE COURSES_ID = ? ORDER BY ORDER_INDEX ASC");
        if (!$stmt) return [];

        $stmt->bind_param("i", $courseId);
        $stmt->execute();
        $result = $stmt->get_result();

        $modules = [];
        while ($row = $result->fetch_assoc()) {
            $modules[] = $row;
        }

        return $modules;
    }


    /**
     * Lấy chi tiết module kèm danh sách lesson + quiz
     *
     * @param int $moduleId
     * @return array|false
     */
    public static function getModuleStructure($moduleId)
    {
        $conn = MySQLConnection::connect();

        // Lấy thông tin module
        $stmtModule = $conn->prepare("SELECT * FROM COURSE_MODULES WHERE COURSES_MODULES_ID = ?");
        $stmtModule->bind_param("i", $moduleId);
        $stmtModule->execute();
        $moduleResult = $stmtModule->get_result();
        $module = $moduleResult->fetch_assoc();

        if (!$module) {
            return false; // Module không tồn tại
        }

        // Lấy danh sách lesson của module này
        $stmtLesson = $conn->prepare("
        SELECT LESSON_ID, COURSES_MODULES_ID, TITLE, ORDER_INDEX, VIDEO_URL
        FROM LESSON
        WHERE COURSES_MODULES_ID = ?
        ORDER BY ORDER_INDEX ASC
    ");
        $stmtLesson->bind_param("i", $moduleId);
        $stmtLesson->execute();
        $lessonsResult = $stmtLesson->get_result();

        $lessons = [];
        while ($lesson = $lessonsResult->fetch_assoc()) {

            // Lấy quiz của lesson
            $stmtQuiz = $conn->prepare("SELECT QUIZ_ID FROM QUIZZ WHERE LESSON_ID = ?");
            $stmtQuiz->bind_param("i", $lesson['LESSON_ID']);
            $stmtQuiz->execute();
            $quizResult = $stmtQuiz->get_result();

            $quizzes = [];
            while ($quiz = $quizResult->fetch_assoc()) {
                $quizzes[] = $quiz;
            }

            $lesson['QUIZZES'] = $quizzes;
            $lessons[] = $lesson;
        }

        $module['LESSONS'] = $lessons;

        return $module;
    }
}
