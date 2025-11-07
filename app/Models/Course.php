<?php

namespace App\Models;

use App\Helpers\DatabaseHelper;
use App\Database\MySQLConnection;
class Course
{
    /**
     *
     * @return array
     */
    public static function getAll()
    {
        $query = "SELECT * FROM COURSES";
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
     * Create a new course
     *
     * @param array $data
     * @return int|false  returns inserted course ID or false if failed
     */
    public static function create(array $data)
    {
        // Build query with prepared statement to avoid SQL injection
        $conn = MySQLConnection::connect();

        $stmt = $conn->prepare("INSERT INTO COURSES 
            (USER_ID, TITLE, DESCRIPTION, TARGET, RESULT, IMAGE, DURATION, UPDATED_AT, PRICE, TYPE, RATING_AVG, TOTAL_STUDENTS, CREATED_AT, DISCOUNT_PERCENT)
            VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), ?, ?, NULL, 0, NOW(), ?)");

        if (!$stmt) {
            return false;
        }

        $stmt->bind_param(
            "isssssidsd",
            $data['USER_ID'],
            $data['TITLE'],
            $data['DESCRIPTION'],
            $data['TARGET'],
            $data['RESULT'],
            $data['IMAGE'],
            $data['DURATION'],
            $data['PRICE'],
            $data['TYPE'],
            $data['DISCOUNT_PERCENT']
        );

        if ($stmt->execute()) {
            return $conn->insert_id;
        }

        return false;
    }

    /**
     * Lấy thông tin khóa học cơ bản + thống kê
     */
    public static function getCourseInfo($courseId)
    {
        $conn = MySQLConnection::connect();

        $query = "
            SELECT 
                c.COURSES_ID,
                c.TITLE,
                c.DESCRIPTION,
                c.RATING_AVG,
                c.TOTAL_STUDENTS,
                COUNT(DISTINCT r.REVIEW_ID) AS TOTAL_REVIEWS
            FROM COURSES c
            LEFT JOIN REVIEW r ON c.COURSES_ID = r.COURSES_ID
            WHERE c.COURSES_ID = ?
            GROUP BY c.COURSES_ID
        ";

        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $courseId);
        $stmt->execute();

        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    /**
     * Lấy danh sách module + lesson + quiz theo khóa học
     */
    public static function getCourseStructure($courseId)
    {
        $conn = MySQLConnection::connect();

        // Lấy module
        $queryModule = "SELECT * FROM COURSE_MODULES WHERE COURSES_ID = ? ORDER BY ORDER_INDEX ASC";
        $stmtModule = $conn->prepare($queryModule);
        $stmtModule->bind_param("i", $courseId);
        $stmtModule->execute();
        $modulesResult = $stmtModule->get_result();

        $modules = [];
        while ($module = $modulesResult->fetch_assoc()) {

            // Lấy bài học thuộc module này
            $queryLesson = "SELECT * FROM LESSON WHERE COURSES_MODULES_ID = ? ORDER BY ORDER_INDEX ASC";
            $stmtLesson = $conn->prepare($queryLesson);
            $stmtLesson->bind_param("i", $module['COURSES_MODULES_ID']);
            $stmtLesson->execute();
            $lessonsResult = $stmtLesson->get_result();

            $lessons = [];
            while ($lesson = $lessonsResult->fetch_assoc()) {

                // Lấy quiz thuộc lesson
                $queryQuiz = "SELECT QUIZ_ID FROM QUIZZ WHERE LESSON_ID = ?";
                $stmtQuiz = $conn->prepare($queryQuiz);
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
            $modules[] = $module;
        }

        return $modules;
    }

    /**
     * Lấy danh sách review của khóa học
     */
    public static function getCourseReviews($courseId)
    {
        $conn = MySQLConnection::connect();

        $query = "
            SELECT 
                u.FULL_NAME,
                r.CONTEXT,
                c.RATING_AVG AS RATING
            FROM REVIEW r
            JOIN USER u ON u.USER_ID = r.USER_ID
            JOIN COURSES c ON c.COURSES_ID = r.COURSES_ID
            WHERE r.COURSES_ID = ?
        ";

        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $courseId);
        $stmt->execute();

        $result = $stmt->get_result();

        $reviews = [];
        while ($row = $result->fetch_assoc()) {
            $reviews[] = $row;
        }

        return $reviews;
    }
}

