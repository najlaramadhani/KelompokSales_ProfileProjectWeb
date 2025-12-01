<?php
/**
 * CRUD.php - Management System untuk Profile Mahasiswa
 * 
 * File ini menangani operasi Create, Read, Update, Delete untuk semua tabel profil
 * Struktur: Tab-based interface dengan form tunggal per tab dan data table
 * Upload: Support untuk file gambar (jpg, png, gif)
 */

include __DIR__ . '/koneksi.php';

// Pastikan koneksi database aktif
$DB = ensureDBConnection($DB);

// ============================================================================
// HELPER FUNCTIONS
// ============================================================================

/**
 * h() - Helper untuk escape HTML output (XSS prevention)
 */
function h($s) { 
    return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); 
}

/**
 * getDataForEdit() - Ambil data record berdasarkan ID untuk edit form
 */
function getDataForEdit($DB, $table, $id_field, $id) {
    $id_safe = mysqli_real_escape_string($DB, $id);
    $table_safe = mysqli_real_escape_string($DB, $table);
    $id_field_safe = mysqli_real_escape_string($DB, $id_field);
    
    $query = "SELECT * FROM $table_safe WHERE $id_field_safe = '$id_safe' LIMIT 1";
    $result = mysqli_query($DB, $query);
    
    if ($result && mysqli_num_rows($result) > 0) {
        return mysqli_fetch_assoc($result);
    }
    return null;
}

/**
 * renderField() - Render form input berdasarkan tipe field
 * 
 * Tipe field yang didukung:
 * - text: input text biasa
 * - textarea: text area untuk teks panjang
 * - number: input angka
 * - date: input tanggal
 * - file: upload file gambar
 * - nim: dropdown dari tabel Biodata
 */
function renderField($DB, $field, $value = '') {
    $name = $field['name'];
    $label = $field['label'];
    $type = isset($field['type']) ? $field['type'] : 'text';
    $val = $value !== null ? $value : '';
    
    echo "<tr>";
    echo "<td align='right' style='width:120px;'><label><b>" . h($label) . "</b></label></td>";
    echo "<td>";
    
    if ($type === 'textarea') {
        // Text area untuk input panjang
        echo "<textarea name='" . h($name) . "' style='width:100%;padding:8px;border:1px solid #ddd;border-radius:4px;height:100px;'>" . h($val) . "</textarea>";
        
    } elseif ($type === 'file') {
        // File upload dengan preview gambar
        if (!empty($val)) {
            echo "<div style='margin-bottom:8px;'>";
            echo "<img src='/KelompokSales_ProfileProjectWeb/uploads/" . h($val) . "' alt='' style='max-width:140px;max-height:90px;border:1px solid #ddd;padding:4px;border-radius:4px;'>";
            echo "</div>";
        }
        echo "<input type='file' name='" . h($name) . "' accept='image/*' style='width:100%;'>";
        
    } elseif ($type === 'number' || $type === 'date') {
        // Input number atau date
        echo "<input type='" . h($type) . "' name='" . h($name) . "' value='" . h($val) . "' style='width:100%;padding:8px;border:1px solid #ddd;border-radius:4px;'>";
        
    } elseif ($type === 'nim') {
        // Dropdown untuk pilih NIM (dari Biodata)
        // Jika ada nilai (edit mode), disable select dan tambahkan hidden input agar nilai tetap dikirim
        $is_disabled = ($val !== '');
        $disabled_attr = $is_disabled ? ' disabled' : '';
        echo "<select name='" . h($name) . "'" . $disabled_attr . " style='width:100%;padding:8px;border:1px solid #ddd;border-radius:4px;'>";
        echo "<option value=''>-- Pilih NIM --</option>";
        
        $res = mysqli_query($DB, "SELECT DISTINCT nim, nama FROM Biodata ORDER BY nama");
        while ($row = mysqli_fetch_assoc($res)) {
            $sel = ($val == $row['nim']) ? 'selected' : '';
            echo "<option value='" . h($row['nim']) . "' $sel>" . h($row['nim'] . ' - ' . $row['nama']) . "</option>";
        }
        echo "</select>";
        if ($is_disabled) {
            // tambahkan hidden input agar nilai nim tetap dikirim saat form disubmit
            echo "<input type='hidden' name='" . h($name) . "' value='" . h($val) . "'>";
        }
        
    } else {
        // Default: text input
        // Jika ini field 'nim' dan ada nilai (edit mode), tampilkan sebagai disabled + hidden input
        if ($name === 'nim' && $val !== '') {
            echo "<input type='text' name='" . h($name) . "' value='" . h($val) . "' disabled style='width:100%;padding:8px;border:1px solid #ddd;border-radius:4px;background:#f5f5f5;'>";
            echo "<input type='hidden' name='" . h($name) . "' value='" . h($val) . "'>";
        } else {
            echo "<input type='text' name='" . h($name) . "' value='" . h($val) . "' style='width:100%;padding:8px;border:1px solid #ddd;border-radius:4px;'>";
        }
    }
    
    echo "</td></tr>";
}

// ============================================================================
// KONFIGURASI TABEL
// ============================================================================

$table_config = array(
    'biodata' => array(
        'table' => 'Biodata',
        'id_field' => 'idBiodata',
        'fields' => array(
            array('name' => 'judul', 'label' => 'Judul', 'type' => 'text'),
            array('name' => 'nim', 'label' => 'NIM', 'type' => 'text'),
            array('name' => 'nama', 'label' => 'Nama', 'type' => 'text'),
            array('name' => 'tempatLahir', 'label' => 'Tempat Lahir', 'type' => 'text'),
            array('name' => 'tanggalLahir', 'label' => 'Tanggal Lahir', 'type' => 'date'),
            array('name' => 'agama', 'label' => 'Agama', 'type' => 'text'),
            array('name' => 'pendidikan', 'label' => 'Pendidikan', 'type' => 'text')
        )
    ),
    
    'pendidikan' => array(
        'table' => 'Pendidikan',
        'id_field' => 'idPendidikan',
        'fields' => array(
            array('name' => 'judul', 'label' => 'Judul', 'type' => 'text'),
            array('name' => 'nim', 'label' => 'NIM', 'type' => 'nim'),
            array('name' => 'institusi', 'label' => 'Institusi', 'type' => 'text'),
            array('name' => 'tahun', 'label' => 'Tahun', 'type' => 'number'),
            array('name' => 'jurusan', 'label' => 'Jurusan', 'type' => 'text')
        )
    ),
    
    'keahlian' => array(
        'table' => 'Keahlian',
        'id_field' => 'idKeahlian',
        'fields' => array(
            array('name' => 'judul', 'label' => 'Judul', 'type' => 'text'),
            array('name' => 'nim', 'label' => 'NIM', 'type' => 'nim'),
            array('name' => 'namaKeahlian', 'label' => 'Nama Keahlian', 'type' => 'text'),
            array('name' => 'imgKeahlian', 'label' => 'Gambar Keahlian', 'type' => 'file')
        )
    ),
    
    'pengalaman' => array(
        'table' => 'Pengalaman',
        'id_field' => 'idPengalaman',
        'fields' => array(
            array('name' => 'judul', 'label' => 'Judul', 'type' => 'text'),
            array('name' => 'nim', 'label' => 'NIM', 'type' => 'nim'),
            array('name' => 'namaPengalaman', 'label' => 'Nama Pengalaman', 'type' => 'text'),
            array('name' => 'tahunMulai', 'label' => 'Tahun Mulai', 'type' => 'number'),
            array('name' => 'tahunSelesai', 'label' => 'Tahun Selesai', 'type' => 'number'),
            array('name' => 'deskripsi', 'label' => 'Deskripsi', 'type' => 'textarea')
        )
    ),
    
    'publikasi' => array(
        'table' => 'Publikasi',
        'id_field' => 'idPublikasi',
        'fields' => array(
            array('name' => 'judul', 'label' => 'Judul', 'type' => 'text'),
            array('name' => 'nim', 'label' => 'NIM', 'type' => 'nim'),
            array('name' => 'judulPublikasi', 'label' => 'Judul Publikasi', 'type' => 'text'),
            array('name' => 'tahunTerbit', 'label' => 'Tahun Terbit', 'type' => 'number'),
            array('name' => 'penerbit', 'label' => 'Penerbit', 'type' => 'text'),
            array('name' => 'namaTag', 'label' => 'Nama Tag', 'type' => 'text'),
            array('name' => 'imgPublikasi', 'label' => 'Gambar Publikasi', 'type' => 'file')
        )
    ),
    
    'aside' => array(
        'table' => 'tblAside',
        'id_field' => 'idAside',
        'fields' => array(
            array('name' => 'nim', 'label' => 'NIM', 'type' => 'nim'),
            array('name' => 'namaAside', 'label' => 'Nama Hobi', 'type' => 'text'),
            array('name' => 'imgAside', 'label' => 'Gambar Hobi', 'type' => 'file')
        )
    ),
    
    'Profile' => array(
        'table' => 'tblProfilFooter',
        'id_field' => 'idProfilFooter',
        'fields' => array(
            array('name' => 'nim', 'label' => 'NIM', 'type' => 'nim'),
            array('name' => 'slogan', 'label' => 'Slogan', 'type' => 'text'),
            array('name' => 'fotoProfile', 'label' => 'Foto Profile', 'type' => 'file'),
            array('name' => 'linkedin', 'label' => 'LinkedIn', 'type' => 'text'),
            array('name' => 'github', 'label' => 'GitHub', 'type' => 'text')
        )
    )
);

// ============================================================================
// AMBIL PARAMETER REQUEST
// ============================================================================

$active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'biodata';
$edit_id = isset($_GET['edit']) ? $_GET['edit'] : null;
$error = null;
$success = null;

// ============================================================================
// HANDLE DELETE REQUEST
// ============================================================================

if (isset($_GET['aksi']) && $_GET['aksi'] === 'hapus' && isset($_GET['table']) && isset($_GET['id']) && isset($_GET['id_field'])) {
    $table = $_GET['table'];
    $id = $_GET['id'];
    $id_field = $_GET['id_field'];
    
    $table_safe = mysqli_real_escape_string($DB, $table);
    $id_field_safe = mysqli_real_escape_string($DB, $id_field);
    $id_safe = mysqli_real_escape_string($DB, $id);
    
    $query = "DELETE FROM $table_safe WHERE $id_field_safe = '$id_safe'";
    if (mysqli_query($DB, $query)) {
        header('Location: CRUD.php?tab=' . urlencode($active_tab) . '&success=dihapus');
        exit();
    } else {
        $error = 'Gagal menghapus: ' . mysqli_error($DB);
    }
}

// ============================================================================
// HANDLE ADD / EDIT REQUEST (dengan file upload)
// ============================================================================

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['table']) && isset($table_config[$active_tab])) {
    $action = isset($_POST['action']) ? $_POST['action'] : 'add';
    $cfg = $table_config[$active_tab];
    $table = $_POST['table'];
    $id_field = $cfg['id_field'];
    
    // Buat folder uploads jika belum ada
    $uploads_dir = __DIR__ . '/uploads';
    if (!is_dir($uploads_dir)) @mkdir($uploads_dir, 0755, true);
    
    // Ambil data lama (jika edit) untuk preserve file field jika tidak ada upload baru
    $existing = null;
    if ($action === 'edit' && isset($_POST[$id_field])) {
        $existing = getDataForEdit($DB, $table, $id_field, $_POST[$id_field]);
    }
    
    // ---- Process file uploads ----
    $file_uploads = array();
    foreach ($cfg['fields'] as $f) {
        if (isset($f['type']) && $f['type'] === 'file') {
            $field_name = $f['name'];
            
            // Check apakah ada file upload untuk field ini
            if (isset($_FILES[$field_name]) && isset($_FILES[$field_name]['error']) && $_FILES[$field_name]['error'] === UPLOAD_ERR_OK) {
                $tmp_name = $_FILES[$field_name]['tmp_name'];
                $orig_name = $_FILES[$field_name]['name'];
                $ext = strtolower(pathinfo($orig_name, PATHINFO_EXTENSION));
                
                // Validasi extension file
                $allowed_ext = array('jpg', 'jpeg', 'png', 'gif');
                if (!in_array($ext, $allowed_ext)) {
                    $error = 'Tipe file tidak diperbolehkan untuk ' . h($field_name) . ' (hanya jpg/png/gif).';
                    break;
                }
                
                // Generate filename unik dengan timestamp + uniqid
                $new_filename = time() . '_' . uniqid() . '.' . $ext;
                
                // Move file ke folder uploads
                if (move_uploaded_file($tmp_name, $uploads_dir . '/' . $new_filename)) {
                    $file_uploads[$field_name] = $new_filename;
                    
                    // Hapus file lama saat edit
                    if ($existing && !empty($existing[$field_name])) {
                        $old_file = $uploads_dir . '/' . $existing[$field_name];
                        if (file_exists($old_file)) {
                            @unlink($old_file);
                        }
                    }
                } else {
                    $error = 'Gagal menyimpan file untuk ' . h($field_name);
                    break;
                }
            }
        }
    }
    
    // ---- Build SQL columns, values, dan updates ----
    if (empty($error)) {
        $cols = array();
        $vals = array();
        $updates = array();
        
        foreach ($cfg['fields'] as $f) {
            $field_name = $f['name'];
            
            if (isset($f['type']) && $f['type'] === 'file') {
                // Field file: hanya update jika ada upload baru
                if (isset($file_uploads[$field_name])) {
                    $v = mysqli_real_escape_string($DB, $file_uploads[$field_name]);
                    $cols[] = "" . mysqli_real_escape_string($DB, $field_name) . "";
                    $vals[] = "'" . $v . "'";
                    $updates[] = "" . mysqli_real_escape_string($DB, $field_name) . "='" . $v . "'";
                }
                // Jika tidak ada upload baru, field file diabaikan (pertahankan yang lama)
                
            } else {
                // Field non-file: ambil dari POST
                if (isset($_POST[$field_name])) {
                    $v = mysqli_real_escape_string($DB, $_POST[$field_name]);
                    $cols[] = "" . mysqli_real_escape_string($DB, $field_name) . "";
                    $vals[] = "'" . $v . "'";
                    $updates[] = "" . mysqli_real_escape_string($DB, $field_name) . "='" . $v . "'";
                }
            }
        }
        
        // ---- Execute query ----
        if ($action === 'add' && !empty($cols)) {
            $table_safe = mysqli_real_escape_string($DB, $table);
            $query = "INSERT INTO $table_safe (" . implode(',', $cols) . ") VALUES (" . implode(',', $vals) . ")";
            
            if (mysqli_query($DB, $query)) {
                header('Location: CRUD.php?tab=' . urlencode($active_tab) . '&success=disimpan');
                exit();
            } else {
                $error = 'Gagal tambah data: ' . mysqli_error($DB);
            }
            
        } elseif ($action === 'edit' && isset($_POST[$id_field]) && !empty($updates)) {
            $id_value = mysqli_real_escape_string($DB, $_POST[$id_field]);
            $table_safe = mysqli_real_escape_string($DB, $table);
            $id_field_safe = mysqli_real_escape_string($DB, $id_field);
            
            $query = "UPDATE $table_safe SET " . implode(',', $updates) . " WHERE $id_field_safe = '$id_value'";
            
            if (mysqli_query($DB, $query)) {
                header('Location: CRUD.php?tab=' . urlencode($active_tab) . '&success=diperbarui');
                exit();
            } else {
                $error = 'Gagal update data: ' . mysqli_error($DB);
            }
        }
    }
}

// ============================================================================
// HTML MARKUP & UI
// ============================================================================
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>CRUD Management</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: Arial, Helvetica, sans-serif; background: #f5f5f5; }
        
        .container { max-width: 1100px; margin: 20px auto; background: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,.08); }
        .header { padding: 18px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: #fff; }
        .header h1 { font-size: 1.8rem; margin-bottom: 4px; }
        .header p { font-size: 0.9rem; opacity: 0.9; }
        
        .tabs { display: flex; flex-wrap: wrap; border-bottom: 2px solid #eee; background: #fafafa; }
        .tab-btn { flex: 1; padding: 12px; cursor: pointer; border: none; background: transparent; color: #666; border-bottom: 3px solid transparent; min-width: 120px; transition: all 0.3s; }
        .tab-btn:hover { background: #f0f0f0; }
        .tab-btn.active { color: #2196F3; border-bottom-color: #2196F3; font-weight: 700; background: #fff; }
        
        .content { padding: 20px; }
        .alert { padding: 12px; border-radius: 6px; margin-bottom: 10px; }
        .alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert-error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        
        .form-section { background: #f9f9f9; padding: 16px; border-radius: 8px; border: 1px solid #eee; margin-bottom: 16px; }
        .form-section h3 { color: #333; margin-bottom: 12px; }
        .form-table { width: 100%; border-collapse: collapse; }
        .form-table td { padding: 8px; vertical-align: top; border-bottom: 1px solid #e0e0e0; }
        .form-table label { font-weight: 600; color: #555; }
        
        .table-section { margin-top: 20px; }
        .table-section h3 { color: #333; margin-bottom: 12px; }
        .data-table { width: 100%; border-collapse: collapse; }
        .data-table th { background: #f5f5f5; color: #333; font-weight: 600; padding: 12px; text-align: left; border: 1px solid #ddd; }
        .data-table td { padding: 10px; border: 1px solid #ddd; }
        .data-table tr:hover { background: #f9f9f9; }
        
        .btn { padding: 8px 12px; border: none; border-radius: 4px; cursor: pointer; text-decoration: none; display: inline-block; margin-right: 4px; }
        .btn-save { background: #4CAF50; color: #fff; }
        .btn-save:hover { background: #45a049; }
        .btn-edit { background: #2196F3; color: #fff; font-size: 0.9rem; }
        .btn-edit:hover { background: #0b7dda; }
        .btn-delete { background: #f44336; color: #fff; font-size: 0.9rem; }
        .btn-delete:hover { background: #da190b; }
        
        .no-data { padding: 16px; color: #777; text-align: center; }
        
        textarea { font-family: Arial, sans-serif; }
        input[type="text"], input[type="number"], input[type="date"], select, textarea { font-size: 0.95rem; }
    </style>
</head>
<body>
<div class="container">
    <!-- HEADER -->
    <div class="header">
        <h1>CRUD Management - Profil</h1>
        <p>Kelola data profil mahasiswa per tab</p>
    </div>
    
    <!-- TABS -->
    <div class="tabs">
        <?php foreach (array_keys($table_config) as $tab): ?>
            <button class="tab-btn <?php echo ($active_tab === $tab) ? 'active' : ''; ?>" onclick="window.location.href='?tab=<?php echo h($tab); ?>'">
                <?php echo h(ucfirst($tab)); ?>
            </button>
        <?php endforeach; ?>
    </div>
    
    <!-- CONTENT -->
    <div class="content">
        <!-- Alert Messages -->
        <?php
        $flash_success = isset($_GET['success']) && $_GET['success'] !== '' ? $_GET['success'] : ($success ?: '');
        if ($flash_success):
        ?>
            <div class="alert alert-success">
                ✓ Data berhasil <?php echo h($flash_success); ?>
            </div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert alert-error">
                ✗ <?php echo h($error); ?>
            </div>
        <?php endif; ?>
        
        <!-- Render Active Tab -->
        <?php
        $cfg = isset($table_config[$active_tab]) ? $table_config[$active_tab] : reset($table_config);
        $table = $cfg['table'];
        $id_field = $cfg['id_field'];
        
        $edit_data = null;
        if ($edit_id && isset($table_config[$active_tab])) {
            $edit_data = getDataForEdit($DB, $table, $id_field, $edit_id);
        }
        ?>
        
        <!-- FORM SECTION -->
        <div class="form-section">
            <h3><?php echo $edit_data ? 'Edit ' . h(ucfirst($active_tab)) : 'Tambah ' . h(ucfirst($active_tab)); ?></h3>
            <form method="POST" action="?tab=<?php echo h($active_tab); ?>" enctype="multipart/form-data">
                <table class="form-table">
                    <?php
                    // Render field form
                    foreach ($cfg['fields'] as $f) {
                        $val = $edit_data && isset($edit_data[$f['name']]) ? $edit_data[$f['name']] : '';
                        renderField($DB, $f, $val);
                    }
                    ?>
                    <tr>
                        <td></td>
                        <td>
                            <button type="submit" class="btn btn-save">
                                <?php echo $edit_data ? 'Perbarui' : 'Simpan'; ?>
                            </button>
                            <?php if ($edit_data): ?>
                                <a href="?tab=<?php echo h($active_tab); ?>" class="btn" style="background:#999;color:#fff;">Batal</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                </table>
                
                <input type="hidden" name="action" value="<?php echo $edit_data ? 'edit' : 'add'; ?>">
                <input type="hidden" name="table" value="<?php echo h($table); ?>">
                <?php if ($edit_data): ?>
                    <input type="hidden" name="<?php echo h($id_field); ?>" value="<?php echo h($edit_id); ?>">
                <?php endif; ?>
            </form>
        </div>
        
        <!-- TABLE SECTION -->
        <div class="table-section">
            <h3>Daftar <?php echo h(ucfirst($active_tab)); ?></h3>
            <?php
            $query = "SELECT * FROM " . mysqli_real_escape_string($DB, $table) . "";
            $result = mysqli_query($DB, $query);
            
            if ($result && mysqli_num_rows($result) > 0) {
                echo "<table class='data-table'>";
                echo "<tr><th style='width:40px;'>No</th>";
                
                // Render header table
                foreach ($cfg['fields'] as $f) {
                    echo "<th>" . h($f['label']) . "</th>";
                }
                echo "<th style='width:200px;'>Aksi</th></tr>";
                
                // Render data rows
                $no = 1;
                while ($row = mysqli_fetch_assoc($result)) {
                    echo "<tr>";
                    echo "<td>" . ($no++) . "</td>";
                    
                    // Render cell data
                    foreach ($cfg['fields'] as $f) {
                        $field_name = $f['name'];
                        $cell_value = isset($row[$field_name]) ? $row[$field_name] : '';
                        
                        if (isset($f['type']) && $f['type'] === 'file') {
                            // File field: tampilkan preview gambar
                            if (!empty($cell_value)) {
                                echo "<td>";
                                echo "<img src='/KelompokSales_ProfileProjectWeb/uploads/" . h($cell_value) . "' style='max-width:120px;max-height:80px;border:1px solid #eee;padding:4px;border-radius:4px;'>";
                                echo "</td>";
                            } else {
                                echo "<td></td>";
                            }
                        } else {
                            // Non-file field: tampilkan text
                            if ($f['type'] === 'textarea') {
                                // Truncate text panjang
                                $cell_value = substr($cell_value, 0, 80) . (strlen($cell_value) > 80 ? '...' : '');
                            }
                            echo "<td>" . h($cell_value) . "</td>";
                        }
                    }
                    
                    // Action buttons
                    $row_id = isset($row[$id_field]) ? $row[$id_field] : '';
                    echo "<td>";
                    echo "<a class='btn btn-edit' href='?tab=" . h($active_tab) . "&edit=" . h($row_id) . "'>Edit</a> ";
                    echo "<a class='btn btn-delete' href='?tab=" . h($active_tab) . "&aksi=hapus&table=" . h($table) . "&id=" . h($row_id) . "&id_field=" . h($id_field) . "' onclick=\"return confirm('Yakin hapus data ini?');\">Hapus</a>";
                    echo "</td>";
                    echo "</tr>";
                }
                echo "</table>";
            } else {
                echo "<div class='no-data'>Belum ada data</div>";
            }
            ?>
        </div>
    </div>
</div>
</body>
</html>