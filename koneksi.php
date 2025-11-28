<?php
/**
 * koneksi.php
 * File koneksi database menggunakan MySQLi procedural
 */

// Koneksi ke database db_profil
$DB = mysqli_connect("localhost", "root", "", "profileSales");

// Cek koneksi
if (!$DB) {
    die("Gagal koneksi ke database: " . mysqli_connect_error());
}

// Set charset UTF-8
mysqli_set_charset($DB, "utf8");

// Function untuk memastikan koneksi masih aktif
function ensureDBConnection(&$conn) {
    if (!$conn || !mysqli_ping($conn)) {
        $conn = mysqli_connect("localhost", "root", "", "db_profil");
        if (!$conn) {
            die("Gagal reconnect ke database: " . mysqli_connect_error());
        }
        mysqli_set_charset($conn, "utf8");
    }
    return $conn;
}
?>