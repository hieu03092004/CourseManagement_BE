<?php

namespace App\Database;

use mysqli;

class MySQLConnection
{
    private static $connection = null;

    /**
     * Connect to MySQL database
     *
     * @return mysqli|null
     */
    public static function connect()
    {
        if (self::$connection !== null) {
            return self::$connection;
        }

        $servername = env('DB_HOST');
        $username = env('DB_USERNAME');
        $password = env('DB_PASSWORD');
        $dbname = env('DB_DATABASE');
        $port = env('DB_PORT');

        try {
            self::$connection = new mysqli($servername, $username, $password, $dbname, $port);

            if (self::$connection->connect_error) {
                throw new \Exception("Kết nối thất bại: " . self::$connection->connect_error);
            }

            // Set charset UTF8
            self::$connection->set_charset("utf8mb4");

            // In ra terminal khi connect thành công
            error_log("\n✓ MySQL Connect Successfully!");
            error_log("  Database: {$dbname}");
            error_log("  Host: {$servername}:{$port}\n");

            return self::$connection;
        } catch (\Exception $e) {
            die("Connect Error: " . $e->getMessage());
        }
    }

    /**
     * Close connection
     *
     * @return void
     */
    public static function close()
    {
        if (self::$connection !== null) {
            self::$connection->close();
            self::$connection = null;
            echo "MySQL Connection Closed\n";
        }
    }

    /**
     * Execute query
     *
     * @param string $query
     * @return mixed
     */
    public static function query($query)
    {
        $conn = self::connect();
        return $conn->query($query);
    }
}

