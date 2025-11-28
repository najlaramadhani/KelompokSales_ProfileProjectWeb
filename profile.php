<?php
include __DIR__ . '/koneksi.php';

// Helper: Escape HTML output
function h($s) {
    return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}

// Ambil NIM dari URL parameter
$nim = isset($_GET['nim']) ? trim($_GET['nim']) : '';

// Inisialisasi variabel data
$profil_footer = null;
$biodata = null;
$pendidikans = [];
$keahlians = [];
$pengalamans = [];
$publikasis = [];
$asides = [];

// Jika NIM tersedia, ambil data dari database
if ($nim !== '') {
    $nim_safe = mysqli_real_escape_string($DB, $nim);

    // 1. Ambil data dari tblProfilFooter (untuk header foto dan footer info)
    $query = "SELECT * FROM tblProfilFooter WHERE nim = '$nim_safe' LIMIT 1";
    $result = mysqli_query($DB, $query);
    if ($result && mysqli_num_rows($result) > 0) {
        $profil_footer = mysqli_fetch_assoc($result);
    }

    // 2. Ambil data dari Biodata (untuk informasi umum)
    $query = "SELECT * FROM Biodata WHERE nim = '$nim_safe' LIMIT 1";
    $result = mysqli_query($DB, $query);
    if ($result && mysqli_num_rows($result) > 0) {
        $biodata = mysqli_fetch_assoc($result);
    }

    // 3. Ambil data dari Pendidikan
    $query = "SELECT * FROM Pendidikan WHERE nim = '$nim_safe' ORDER BY tahun DESC";
    $result = mysqli_query($DB, $query);
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $pendidikans[] = $row;
        }
    }

    // 4. Ambil data dari Keahlian
    $query = "SELECT * FROM Keahlian WHERE nim = '$nim_safe'";
    $result = mysqli_query($DB, $query);
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $keahlians[] = $row;
        }
    }

    // 5. Ambil data dari Pengalaman
    $query = "SELECT * FROM Pengalaman WHERE nim = '$nim_safe' ORDER BY tahunMulai DESC";
    $result = mysqli_query($DB, $query);
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $pengalamans[] = $row;
        }
    }

    // 6. Ambil data dari Publikasi
    $query = "SELECT * FROM Publikasi WHERE nim = '$nim_safe' ORDER BY tahunTerbit DESC";
    $result = mysqli_query($DB, $query);
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $publikasis[] = $row;
        }
    }

    // 7. Ambil data dari tblAside (untuk hobi)
    $query = "SELECT * FROM tblAside";
    $result = mysqli_query($DB, $query);
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $asides[] = $row;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile Mahasiswa</title>
    <link rel="stylesheet" href="style_girly.css">
</head>
<body>
    <!-- Header Section -->
    <header>
        <div class="header-content">
            <div class="profile-photo">
                <?php if ($profil_footer && !empty($profil_footer['fotoProfile'])): ?>
                    <img src="/KelompokSales_ProfileProjectWeb/uploads/<?php echo h($profil_footer['fotoProfile']); ?>" alt="Foto Profile">
                <?php else: ?>
                    <img src="hai.jpg" alt="Foto Profile">
                <?php endif; ?>
            </div>
            <div class="header-text">
                <h1>Profile Mahasiswa</h1>
            </div>
        </div>
    </header>

    <!-- Main Content Wrapper -->
    <div class="content-wrapper">
        <!-- Navigation Sidebar (Left) -->
        <nav>
            <h3>Navigasi</h3>
            <ul>
                <li>
                    <a href="#" onclick="showSection('biodata')" class="nav-link active">Biodata</a>
                </li>
                <li>
                    <a href="#" onclick="showSection('pendidikan')" class="nav-link">Pendidikan</a>
                </li>
                <li>
                    <a href="#" onclick="showSection('pengalaman')" class="nav-link">Pengalaman</a>
                </li>
                <li>
                    <a href="#" onclick="showSection('keahlian')" class="nav-link">Keahlian</a>
                </li>
                <li>
                    <a href="#" onclick="showSection('publikasi')" class="nav-link">Publikasi</a>
                </li>
            </ul>
        </nav>

        <!-- Main Container -->
        <div class="container">
            <!-- Main Content Section -->
            <main>
                <!-- SECTION: Biodata -->
                <section id="biodata" class="content-section active">
                    <h2>Biodata</h2>
                    <div class="info-grid">
                        <div class="info-item">
                            <span class="label">Nama:</span>
                            <span class="value">
                                <?php echo $biodata ? h($biodata['nama']) : '-'; ?>
                            </span>
                        </div>
                        <div class="info-item">
                            <span class="label">NIM:</span>
                            <span class="value">
                                <?php echo $nim ? h($nim) : '-'; ?>
                            </span>
                        </div>
                        <div class="info-item">
                            <span class="label">Tempat Lahir:</span>
                            <span class="value">
                                <?php echo $biodata ? h($biodata['tempatLahir']) : '-'; ?>
                            </span>
                        </div>
                        <div class="info-item">
                            <span class="label">Tanggal Lahir:</span>
                            <span class="value">
                                <?php echo $biodata ? h($biodata['tanggalLahir']) : '-'; ?>
                            </span>
                        </div>
                        <div class="info-item">
                            <span class="label">Agama:</span>
                            <span class="value">
                                <?php echo $biodata ? h($biodata['agama']) : '-'; ?>
                            </span>
                        </div>
                        <div class="info-item">
                            <span class="label">Pendidikan:</span>
                            <span class="value">
                                <?php echo $biodata ? h($biodata['pendidikan']) : '-'; ?>
                            </span>
                        </div>
                    </div>
                </section>

                <!-- SECTION: Pendidikan -->
                <section id="pendidikan" class="content-section">
                    <h2>Pendidikan</h2>
                    <div class="education-timeline">
                        <?php if (count($pendidikans) > 0): ?>
                            <?php foreach ($pendidikans as $pd): ?>
                                <div class="edu-card">
                                    <div class="edu-icon">📘</div>
                                    <div class="edu-content">
                                        <h3><?php echo h($pd['judul']); ?></h3>
                                        <p class="edu-period"><?php echo h($pd['tahun']); ?></p>
                                        <p class="edu-degree">
                                            <?php echo h($pd['institusi']); ?> — 
                                            <?php echo h($pd['jurusan']); ?>
                                        </p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="alert">Belum ada data pendidikan.</div>
                        <?php endif; ?>
                    </div>
                </section>

                <!-- SECTION: Pengalaman -->
                <section id="pengalaman" class="content-section">
                    <h2>Pengalaman</h2>
                    <?php if (count($pengalamans) > 0): ?>
                        <?php foreach ($pengalamans as $pg): ?>
                            <div class="experience-item">
                                <h3><?php echo h($pg['namaPengalaman']); ?></h3>
                                <p class="period">
                                    <?php echo h($pg['tahunMulai']) . ' - ' . h($pg['tahunSelesai']); ?>
                                </p>
                                <p><?php echo nl2br(h($pg['deskripsi'])); ?></p>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="alert">Belum ada pengalaman.</div>
                    <?php endif; ?>
                </section>

                <!-- SECTION: Keahlian -->
                <section id="keahlian" class="content-section">
                    <h2>Keahlian</h2>
                    <div class="skills-container">
                        <?php if (count($keahlians) > 0): ?>
                            <?php foreach ($keahlians as $k): ?>
                                <div class="skill-item">
                                    <div class="skill-icon">🔧</div>
                                    <div class="skill-name">
                                        <?php echo h($k['namaKeahlian']); ?>
                                    </div>
                                    <?php if (!empty($k['imgKeahlian'])): ?>
                                        <img src="/KelompokSales_ProfileProjectWeb/uploads/<?php echo h($k['imgKeahlian']); ?>" 
                                             alt="<?php echo h($k['namaKeahlian']); ?>" 
                                             style="max-width: 120px; margin-left: 0.8rem; border-radius: 8px;">
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="alert">Belum ada data keahlian.</div>
                        <?php endif; ?>
                    </div>
                </section>

                <!-- SECTION: Publikasi -->
                <section id="publikasi" class="content-section">
                    <h2>Publikasi</h2>
                    <div class="publications-container">
                        <?php if (count($publikasis) > 0): ?>
                            <?php foreach ($publikasis as $pb): ?>
                                <div class="publication-card">
                                    <div class="publication-image">
                                        <?php if (!empty($pb['imgPublikasi'])): ?>
                                            <img src="/KelompokSales_ProfileProjectWeb/uploads/<?php echo h($pb['imgPublikasi']); ?>" 
                                                 alt="<?php echo h($pb['judulPublikasi']); ?>">
                                        <?php else: ?>
                                            <img src="paper.jpg" alt="Publikasi">
                                        <?php endif; ?>
                                    </div>
                                    <div class="publication-details">
                                        <h3><?php echo h($pb['judulPublikasi']); ?></h3>
                                        <p class="period"><?php echo h($pb['tahunTerbit']); ?></p>
                                        <p class="publication-venue">
                                            <?php echo h($pb['penerbit']); ?>
                                        </p>
                                        <div class="publication-tags">
                                            <span class="tag">
                                                <?php echo h($pb['namaTag']); ?>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="alert">Belum ada publikasi.</div>
                        <?php endif; ?>
                    </div>
                </section>
            </main>

            <!-- Aside Section (Right Sidebar) -->
            <aside>
                <h3>Hobi</h3>
                <div class="hobi-list">
                    <?php if (count($asides) > 0): ?>
                        <?php foreach ($asides as $aside): ?>
                            <div class="hobi-item">
                                <?php if (!empty($aside['imgAside'])): ?>
                                    <span class="hobi-icon" 
                                          style="background-image: url('/KelompokSales_ProfileProjectWeb/uploads/<?php echo h($aside['imgAside']); ?>'); 
                                                  width: 40px; height: 40px; background-size: contain; 
                                                  background-repeat: no-repeat; display: inline-block;"></span>
                                <?php else: ?>
                                    <span class="hobi-icon">📌</span>
                                <?php endif; ?>
                                <span class="hobi-text">
                                    <?php echo h($aside['namaAside']); ?>
                                </span>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="hobi-item">
                            <span class="hobi-icon">📌</span>
                            <span class="hobi-text">Tidak ada data</span>
                        </div>
                    <?php endif; ?>
                </div>
            </aside>
        </div>
    </div>

    <!-- Footer Section -->
    <footer>
        <div class="footer-content">
            <!-- Left Column: Social Media -->
            <div class="footer-left">
                <?php if ($profil_footer && !empty($profil_footer['linkedin'])): ?>
                    <a href="<?php echo h($profil_footer['linkedin']); ?>" 
                       target="_blank" 
                       class="social-link-vertical" 
                       title="LinkedIn">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M19 0h-14c-2.761 0-5 2.239-5 5v14c0 2.761 2.239 5 5 5h14c2.762 0 5-2.239 5-5v-14c0-2.761-2.238-5-5-5zm-11 19h-3v-11h3v11zm-1.5-12.268c-.966 0-1.75-.79-1.75-1.764s.784-1.764 1.75-1.764 1.75.79 1.75 1.764-.783 1.764-1.75 1.764zm13.5 12.268h-3v-5.604c0-3.368-4-3.113-4 0v5.604h-3v-11h3v1.765c1.396-2.586 7-2.777 7 2.476v6.759z" />
                        </svg>
                        <span>LinkedIn</span>
                    </a>
                <?php endif; ?>

                <?php if ($profil_footer && !empty($profil_footer['github'])): ?>
                    <a href="<?php echo h($profil_footer['github']); ?>" 
                       target="_blank" 
                       class="social-link-vertical" 
                       title="GitHub">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z" />
                        </svg>
                        <span>GitHub</span>
                    </a>
                <?php endif; ?>
            </div>

            <!-- Center Column: Copyright -->
            <div class="footer-center">
                <p>&copy; 2025 All Rights Reserved.</p>
            </div>

            <!-- Right Column: Slogan -->
            <div class="footer-right">
                <?php if ($profil_footer && !empty($profil_footer['slogan'])): ?>
                    <p><?php echo h($profil_footer['slogan']); ?></p>
                <?php endif; ?>
            </div>
        </div>
    </footer>

    <!-- JavaScript untuk navigasi section -->
    <script>
        function showSection(sectionId) {
            // Sembunyikan semua section
            var sections = document.getElementsByClassName("content-section");
            for (var i = 0; i < sections.length; i++) {
                sections[i].classList.remove("active");
            }

            // Hapus active class dari semua nav-link
            var navLinks = document.getElementsByClassName("nav-link");
            for (var i = 0; i < navLinks.length; i++) {
                navLinks[i].classList.remove("active");
            }

            // Tampilkan section yang dipilih
            var element = document.getElementById(sectionId);
            if (element) {
                element.classList.add('active');
            }

            // Tandai nav-link yang aktif
            if (event && event.target) {
                event.target.classList.add('active');
            }

            return false;
        }
    </script>
</body>
</html>