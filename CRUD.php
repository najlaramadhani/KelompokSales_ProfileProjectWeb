<?php
include __DIR__ . '/koneksi.php';

// Ensure database connection is active
$DB = ensureDBConnection($DB);

function h($s) { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }

$active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'biodata';
$edit_id = isset($_GET['edit']) ? $_GET['edit'] : null;
$error = null; $success = null;

/* Configuration for tabs and fields */
$table_config = array(
    'biodata' => array('table'=>'Biodata','id_field'=>'idBiodata','fields'=>array(
        array('name'=>'judul','label'=>'Judul','type'=>'text'),
        array('name'=>'nim','label'=>'NIM','type'=>'text'),
        array('name'=>'nama','label'=>'Nama','type'=>'text'),
        array('name'=>'tempatLahir','label'=>'Tempat Lahir','type'=>'text'),
        array('name'=>'tanggalLahir','label'=>'Tanggal Lahir','type'=>'date'),
        array('name'=>'agama','label'=>'Agama','type'=>'text'),
        array('name'=>'pendidikan','label'=>'Pendidikan','type'=>'text')
    )),
    'pendidikan' => array('table'=>'Pendidikan','id_field'=>'idPendidikan','fields'=>array(
        array('name'=>'judul','label'=>'Judul','type'=>'text'),
        array('name'=>'nim','label'=>'NIM','type'=>'nim'),
        array('name'=>'institusi','label'=>'Institusi','type'=>'text'),
        array('name'=>'tahun','label'=>'Tahun','type'=>'number'),
        array('name'=>'jurusan','label'=>'Jurusan','type'=>'text')
    )),
    'keahlian' => array('table'=>'Keahlian','id_field'=>'idKeahlian','fields'=>array(
        array('name'=>'judul','label'=>'Judul','type'=>'text'),
        array('name'=>'nim','label'=>'NIM','type'=>'nim'),
        array('name'=>'namaKeahlian','label'=>'Nama Keahlian','type'=>'text'),
        array('name'=>'imgKeahlian','label'=>'Img Keahlian','type'=>'file')
    )),
    'pengalaman' => array('table'=>'Pengalaman','id_field'=>'idPengalaman','fields'=>array(
        array('name'=>'judul','label'=>'Judul','type'=>'text'),
        array('name'=>'nim','label'=>'NIM','type'=>'nim'),
        array('name'=>'namaPengalaman','label'=>'Nama Pengalaman','type'=>'text'),
        array('name'=>'tahunMulai','label'=>'Tahun Mulai','type'=>'number'),
        array('name'=>'tahunSelesai','label'=>'Tahun Selesai','type'=>'number'),
        array('name'=>'deskripsi','label'=>'Deskripsi','type'=>'textarea')
    )),
    'publikasi' => array('table'=>'Publikasi','id_field'=>'idPublikasi','fields'=>array(
        array('name'=>'judul','label'=>'Judul','type'=>'text'),
        array('name'=>'nim','label'=>'NIM','type'=>'nim'),
        array('name'=>'judulPublikasi','label'=>'Judul Publikasi','type'=>'text'),
        array('name'=>'tahunTerbit','label'=>'Tahun Terbit','type'=>'number'),
        array('name'=>'penerbit','label'=>'Penerbit','type'=>'text'),
        array('name'=>'namaTag','label'=>'Nama Tag','type'=>'text'),
        array('name'=>'imgPublikasi','label'=>'Img Publikasi','type'=>'file')
    )),
    'aside' => array('table'=>'tblAside','id_field'=>'idAside','fields'=>array(
        array('name'=>'imgAside','label'=>'Img Aside','type'=>'file'),
        array('name'=>'namaAside','label'=>'Nama Aside','type'=>'text')
    )),
    'konten' => array('table'=>'tblKonten','id_field'=>'idKonten','fields'=>array(
        array('name'=>'judulKonten','label'=>'Judul Konten','type'=>'text'),
        array('name'=>'dataKonten','label'=>'Data Konten','type'=>'textarea')
    )),
    'nav' => array('table'=>'tblNavProfil','id_field'=>'idNav','fields'=>array(
        array('name'=>'namaNav','label'=>'Nama Nav','type'=>'text'),
        array('name'=>'linkNav','label'=>'Link Nav','type'=>'text')
    )),
    'footer' => array('table'=>'tblProfilFooter','id_field'=>'idProfilFooter','fields'=>array(
        array('name'=>'nim','label'=>'NIM','type'=>'nim'),
        array('name'=>'slogan','label'=>'Slogan','type'=>'text'),
        array('name'=>'fotoProfile','label'=>'Foto Profile','type'=>'file'),
        array('name'=>'linkedin','label'=>'LinkedIn','type'=>'text'),
        array('name'=>'github','label'=>'GitHub','type'=>'text')
    ))
);

function getDataForEdit($DB, $table, $id_field, $id) {
    $id_safe = mysqli_real_escape_string($DB, $id);
    $table_safe = mysqli_real_escape_string($DB, $table);
    $id_field_safe = mysqli_real_escape_string($DB, $id_field);
    $q = "SELECT * FROM `$table_safe` WHERE `$id_field_safe` = '$id_safe' LIMIT 1";
    $r = mysqli_query($DB, $q);
    if ($r && mysqli_num_rows($r) > 0) return mysqli_fetch_assoc($r);
    return null;
}

function renderField($DB, $field, $value = '') {
    $name = $field['name'];
    $label = $field['label'];
    $type = isset($field['type']) ? $field['type'] : 'text';
    $val = $value !== null ? $value : '';
    echo "<tr>";
    echo "<td align='right' style='width:120px;'><label><b>" . h($label) . "</b></label></td>";
    echo "<td>";
    if ($type === 'textarea') {
        echo "<textarea name='" . h($name) . "' style='width:100%;padding:8px;border:1px solid #ddd;border-radius:4px;'>" . h($val) . "</textarea>";
    } elseif ($type === 'file') {
        if (!empty($val)) {
            echo "<div style='margin-bottom:8px;'><img src='/KelompokSales_ProfileProjectWeb/uploads/" . h($val) . "' alt='' style='max-width:140px;max-height:90px;border:1px solid #ddd;padding:4px;border-radius:4px;'></div>";
        }
        echo "<input type='file' name='" . h($name) . "' accept='image/*' style='width:100%;'>";
    } elseif ($type === 'number' || $type === 'date') {
        echo "<input type='" . h($type) . "' name='" . h($name) . "' value='" . h($val) . "' style='width:100%;padding:8px;border:1px solid #ddd;border-radius:4px;'>";
    } elseif ($type === 'nim') {
        echo "<select name='" . h($name) . "' style='width:100%;padding:8px;border:1px solid #ddd;border-radius:4px;'><option value=''>-- Pilih NIM --</option>";
        $res = mysqli_query($DB, "SELECT DISTINCT nim, nama FROM Biodata ORDER BY nama");
        while ($row = mysqli_fetch_assoc($res)) {
            $sel = ($val == $row['nim']) ? 'selected' : '';
            echo "<option value='" . h($row['nim']) . "' $sel>" . h($row['nim'] . ' - ' . $row['nama']) . "</option>";
        }
        echo "</select>";
    } else {
        echo "<input type='text' name='" . h($name) . "' value='" . h($val) . "' style='width:100%;padding:8px;border:1px solid #ddd;border-radius:4px;'>";
    }
    echo "</td></tr>";
}

// DELETE
if (isset($_GET['aksi']) && $_GET['aksi'] === 'hapus' && isset($_GET['table']) && isset($_GET['id']) && isset($_GET['id_field'])) {
    $table = $_GET['table'];
    $id = $_GET['id'];
    $id_field = $_GET['id_field'];
    $table_safe = mysqli_real_escape_string($DB, $table);
    $id_field_safe = mysqli_real_escape_string($DB, $id_field);
    $id_safe = mysqli_real_escape_string($DB, $id);
    $q = "DELETE FROM `$table_safe` WHERE `$id_field_safe` = '$id_safe'";
    if (mysqli_query($DB, $q)) {
        header('Location: CRUD.php?tab=' . urlencode($active_tab) . '&success=deleted');
        exit();
    } else {
        $error = 'Gagal menghapus: ' . mysqli_error($DB);
    }
}

// ADD / EDIT with file upload support
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['table']) && isset($table_config[$active_tab])) {
    $action = isset($_POST['action']) ? $_POST['action'] : 'add';
    $cfg = $table_config[$active_tab];
    $table = $_POST['table'];
    $id_field = $cfg['id_field'];

    // ensure uploads dir exists
    $uploads_dir = __DIR__ . '/uploads';
    if (!is_dir($uploads_dir)) @mkdir($uploads_dir, 0755, true);

    // if editing, get existing row to preserve file fields when no new upload
    $existing = null;
    if ($action === 'edit' && isset($_POST[$id_field])) {
        $existing = getDataForEdit($DB, $table, $id_field, $_POST[$id_field]);
    }

    // process file uploads first
    $file_uploads = array();
    foreach ($cfg['fields'] as $f) {
        if (isset($f['type']) && $f['type'] === 'file') {
            $n = $f['name'];
            if (isset($_FILES[$n]) && isset($_FILES[$n]['error']) && $_FILES[$n]['error'] === UPLOAD_ERR_OK) {
                $tmp = $_FILES[$n]['tmp_name'];
                $orig = $_FILES[$n]['name'];
                $ext = strtolower(pathinfo($orig, PATHINFO_EXTENSION));
                $allowed = array('jpg','jpeg','png','gif');
                if (!in_array($ext, $allowed)) {
                    $error = 'Tipe file tidak diperbolehkan untuk ' . h($n) . ' (hanya jpg/png/gif).';
                    break;
                }
                $newname = time() . '_' . uniqid() . '.' . $ext;
                if (move_uploaded_file($tmp, $uploads_dir . '/' . $newname)) {
                    $file_uploads[$n] = $newname;
                    // delete old file when editing
                    if ($existing && !empty($existing[$n]) && file_exists($uploads_dir . '/' . $existing[$n])) {
                        @unlink($uploads_dir . '/' . $existing[$n]);
                    }
                } else {
                    $error = 'Gagal menyimpan file untuk ' . h($n);
                    break;
                }
            }
        }
    }

    // build columns/values/updates including any uploaded files
    $cols = array(); $vals = array(); $updates = array();
    foreach ($cfg['fields'] as $f) {
        $n = $f['name'];
        if (isset($f['type']) && $f['type'] === 'file') {
            if (isset($file_uploads[$n])) {
                $v = mysqli_real_escape_string($DB, $file_uploads[$n]);
                $cols[] = "`" . mysqli_real_escape_string($DB, $n) . "`";
                $vals[] = "'" . $v . "'";
                $updates[] = "`" . mysqli_real_escape_string($DB, $n) . "`='" . $v . "'";
            } else {
                // no new upload -> skip (keep existing on edit)
            }
        } else {
            if (isset($_POST[$n])) {
                $v = mysqli_real_escape_string($DB, $_POST[$n]);
                $cols[] = "`" . mysqli_real_escape_string($DB, $n) . "`";
                $vals[] = "'" . $v . "'";
                $updates[] = "`" . mysqli_real_escape_string($DB, $n) . "`='" . $v . "'";
            }
        }
    }

    if (empty($error)) {
        if ($action === 'add') {
            if (!empty($cols)) {
                $q = "INSERT INTO `" . mysqli_real_escape_string($DB, $table) . "` (" . implode(',', $cols) . ") VALUES (" . implode(',', $vals) . ")";
                if (mysqli_query($DB, $q)) {
                    header('Location: CRUD.php?tab=' . urlencode($active_tab) . '&success=added'); exit();
                } else {
                    $error = 'Gagal tambah: ' . mysqli_error($DB);
                }
            }
        } elseif ($action === 'edit' && isset($_POST[$id_field])) {
            $id_value = mysqli_real_escape_string($DB, $_POST[$id_field]);
            if (!empty($updates)) {
                $q = "UPDATE `" . mysqli_real_escape_string($DB, $table) . "` SET " . implode(',', $updates) . " WHERE `" . mysqli_real_escape_string($DB, $id_field) . "`='" . $id_value . "'";
                if (mysqli_query($DB, $q)) {
                    header('Location: CRUD.php?tab=' . urlencode($active_tab) . '&success=updated'); exit();
                } else {
                    $error = 'Gagal update: ' . mysqli_error($DB);
                }
            }
        }
    }
}

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>CRUD Management</title>
    <style>
        *{box-sizing:border-box;margin:0;padding:0}
        body{font-family:Arial,Helvetica,sans-serif;background:#f5f5f5}
        .container{max-width:1100px;margin:20px auto;background:#fff;border-radius:8px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,.08)}
        .header{padding:18px;background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);color:#fff}
        .tabs{display:flex;flex-wrap:wrap;border-bottom:2px solid #eee;background:#fafafa}
        .tab-btn{flex:1;padding:12px;cursor:pointer;border:none;background:transparent;color:#666;border-bottom:3px solid transparent;min-width:120px}
        .tab-btn.active{color:#2196F3;border-bottom-color:#2196F3;font-weight:700;background:#fff}
        .content{padding:20px}
        .form-section{background:#f9f9f9;padding:16px;border-radius:8px;border:1px solid #eee;margin-bottom:16px}
        .form-table td{padding:6px;vertical-align:middle}
        .data-table{width:100%;border-collapse:collapse}
        .data-table th,.data-table td{border:1px solid #eee;padding:10px;text-align:left}
        .btn{padding:8px 12px;border:none;border-radius:4px;cursor:pointer}
        .btn-save{background:#4CAF50;color:#fff}
        .btn-edit{background:#2196F3;color:#fff}
        .btn-delete{background:#f44336;color:#fff}
        .alert{padding:12px;border-radius:6px;margin-bottom:10px}
        .alert-success{background:#d4edda;color:#155724}
        .alert-error{background:#f8d7da;color:#721c24}
    </style>
</head>
<body>
<div class="container">
    <div class="header"><h1>CRUD Management - Profil</h1><p>Kelola data per tab</p></div>
    <div class="tabs">
        <?php foreach (array_keys($table_config) as $tab): ?>
            <button class="tab-btn <?php echo ($active_tab == $tab) ? 'active' : ''; ?>" onclick="window.location.href='?tab=<?php echo h($tab); ?>'"><?php echo h(ucfirst($tab)); ?></button>
        <?php endforeach; ?>
    </div>
    <div class="content">
        <?php
            $flash_success = isset($_GET['success']) && $_GET['success'] !== '' ? $_GET['success'] : ($success ?: '');
            if ($flash_success):
        ?>
            <div class="alert alert-success"><?php echo h($flash_success); ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo h($error); ?></div>
        <?php endif; ?>

        <?php
        // render active tab content
        $cfg = isset($table_config[$active_tab]) ? $table_config[$active_tab] : reset($table_config);
        $table = $cfg['table']; $id_field = $cfg['id_field'];
        $edit_data = null;
        if ($edit_id && $active_tab && isset($table_config[$active_tab])) {
            $edit_data = getDataForEdit($DB, $table, $id_field, $edit_id);
        }
        ?>

        <div class="form-section">
            <h3><?php echo $edit_data ? 'Edit ' . h(ucfirst($active_tab)) : 'Tambah ' . h(ucfirst($active_tab)); ?></h3>
            <form method="POST" action="?tab=<?php echo h($active_tab); ?>" enctype="multipart/form-data">
                <table class="form-table" style="width:100%">
                    <?php foreach ($cfg['fields'] as $f) {
                        $val = $edit_data && isset($edit_data[$f['name']]) ? $edit_data[$f['name']] : '';
                        renderField($DB, $f, $val);
                    } ?>
                    <tr><td></td><td>
                        <button type="submit" class="btn btn-save"><?php echo $edit_data ? 'Perbarui' : 'Simpan'; ?></button>
                        <?php if ($edit_data): ?>
                            <a class="btn" href="?tab=<?php echo h($active_tab); ?>">Batal</a>
                        <?php endif; ?>
                    </td></tr>
                </table>
                <input type="hidden" name="action" value="<?php echo $edit_data ? 'edit' : 'add'; ?>">
                <input type="hidden" name="table" value="<?php echo h($table); ?>">
                <?php if ($edit_data): ?>
                    <input type="hidden" name="id_field" value="<?php echo h($id_field); ?>">
                    <input type="hidden" name="<?php echo h($id_field); ?>" value="<?php echo h($edit_id); ?>">
                <?php endif; ?>
            </form>
        </div>

        <div class="table-section">
            <h3>Daftar <?php echo h(ucfirst($active_tab)); ?></h3>
            <?php
            $q = "SELECT * FROM `" . mysqli_real_escape_string($DB, $table) . "`";
            $res = mysqli_query($DB, $q);
            if ($res && mysqli_num_rows($res) > 0) {
                echo "<table class='data-table'>";
                echo "<tr><th>No</th>";
                foreach ($cfg['fields'] as $f) echo "<th>" . h($f['label']) . "</th>";
                echo "<th>Aksi</th></tr>";
                $no = 1;
                while ($row = mysqli_fetch_assoc($res)) {
                    echo "<tr>";
                    echo "<td>" . $no++ . "</td>";
                    foreach ($cfg['fields'] as $f) {
                        $fn = $f['name'];
                        $cell = isset($row[$fn]) ? $row[$fn] : '';
                        if (isset($f['type']) && $f['type'] === 'file') {
                            if (!empty($cell)) {
                                echo "<td><img src='/KelompokSales_ProfileProjectWeb/uploads/" . h($cell) . "' style='max-width:120px;max-height:80px;border:1px solid #eee;padding:4px;border-radius:4px;'></td>";
                            } else {
                                echo "<td></td>";
                            }
                        } else {
                            if ($f['type'] === 'textarea') $cell = substr($cell,0,80) . (strlen($cell)>80? '...':'');
                            echo "<td>" . h($cell) . "</td>";
                        }
                    }
                    $row_id = isset($row[$id_field]) ? $row[$id_field] : '';
                    echo "<td><a class='btn btn-edit' href='?tab=" . h($active_tab) . "&edit=" . h($row_id) . "'>Edit</a> ";
                    echo "<a class='btn btn-delete' href='?tab=" . h($active_tab) . "&aksi=hapus&table=" . h($table) . "&id=" . h($row_id) . "&id_field=" . h($id_field) . "' onclick=\"return confirm('Yakin hapus?');\">Hapus</a></td>";
                    echo "</tr>";
                }
                echo "</table>";
            } else {
                echo "<div class='no-data' style='padding:16px;color:#777'>Belum ada data</div>";
            }
            ?>
        </div>

    </div>
</div>
</body>
</html>
