<?php

namespace App\Models;

use App\Database\MySQLConnection;

class LoaiSua
{
    /**
     * Get all loại sữa
     *
     * @return array
     */
    public static function getAll()
    {
        $query = "SELECT * FROM loai_sua";
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
}
