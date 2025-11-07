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

    
}
