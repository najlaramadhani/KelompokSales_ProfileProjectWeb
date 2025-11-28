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
?>