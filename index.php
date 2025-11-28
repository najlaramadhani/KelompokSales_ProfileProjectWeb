<?php
/**
 * index.php
 * Halaman utama dengan navbar dropdown Personil dari tabel Biodata
 */

include __DIR__ . '/koneksi.php';

// Ambil daftar personil dari tabel Biodata
$personils = [];
$query = "SELECT DISTINCT nim, nama FROM Biodata ORDER BY nama";
$result = mysqli_query($DB, $query);

if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $personils[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Beranda - Profil Personil</title>
    <link rel="stylesheet" href="style_girly.css">
    <style>
        /* Navbar styling */
        .site-nav {
            background: #fff;
            padding: 0.6rem 1rem;
            border-bottom: 1px solid #f0e6ed;
        }

        .site-nav .menu {
            display: flex;
            gap: 1rem;
            align-items: center;
            list-style: none;
        }

        .site-nav a {
            color: #6b4d5e;
            text-decoration: none;
            padding: 0.5rem 0.75rem;
            border-radius: 6px;
            transition: all 0.3s ease;
        }

        .site-nav .menu li:hover > a {
            background: #faf5f7;
        }

        /* Dropdown styling */
        .dropdown {
            position: relative;
        }

        .dropdown-menu {
            position: absolute;
            left: 0;
            top: 100%;
            background: #fff;
            border: 1px solid #f0e6ed;
            padding: 0.25rem 0;
            border-radius: 6px;
            min-width: 200px;
            box-shadow: 0 6px 18px rgba(107, 77, 94, 0.08);
            display: none;
            z-index: 50;
        }

        .dropdown:hover .dropdown-menu {
            display: block;
        }

        .dropdown-menu a {
            display: block;
            padding: 0.5rem 0.8rem;
            color: #6b4d5e;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .dropdown-menu a:hover {
            background: #f5e6e8;
            color: #4a2c3e;
        }

        @media (max-width: 768px) {
            .site-nav .menu {
                flex-wrap: wrap;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation Header -->
    <header class="site-nav">
        <div class="container" style="display: flex; align-items: center; justify-content: space-between;">
            <div>
                <a href="index.php" style="font-weight: 700; color: #6b4d5e;">Home</a>
            </div>
            <ul class="menu">
                <li>
                    <a href="index.php">Home</a>
                </li>
                <li>
                    <a href="#">Penjualan</a>
                </li>
                <li class="dropdown">
                    <a href="#">About ▾</a>
                    <div class="dropdown-menu">
                        <strong style="display: block; padding: 0.4rem 0.8rem; color: #8b6f84;">
                            Personil
                        </strong>
                        
                        <?php if (count($personils) === 0): ?>
                            <div style="padding: 0.4rem 0.8rem; color: #999;">
                                (Belum ada personil)
                            </div>
                        <?php else: ?>
                            <?php foreach ($personils as $p): ?>
                                <a href="profile.php?nim=<?php echo urlencode($p['nim']); ?>">
                                    <?php echo htmlspecialchars($p['nama']); ?>
                                </a>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </li>
            </ul>
        </div>
    </header>

    <!-- Main Content -->
    <div class="container" style="padding: 2rem;">
        <main style="background: #fff; padding: 2rem; border-radius: 12px;">
            <h2>Selamat datang</h2>
            <p>
                Gunakan menu <strong>About → Personil</strong> untuk melihat profil personil. 
                Klik nama untuk membuka detail profil.
            </p>
        </main>
    </div>
</body>
</html>
