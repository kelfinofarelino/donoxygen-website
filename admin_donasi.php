<?php
// admin_donasi.php
session_start();
include 'koneksi.php';

// Pastikan hanya admin yang bisa akses
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: dashboard.php");
    exit;
}

// --- LOGIC: HANDLE PERUBAHAN STATUS ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_id'])) {
    $id = intval($_POST['action_id']);
    $status_baru = $_POST['new_status'];
    $current_tab = $_POST['current_tab'] ?? 'pending';

    // Update status di database
    // Validasi status agar aman
    $allowed_status = ['pending', 'success', 'failed', 'distributed'];
    if (in_array($status_baru, $allowed_status)) {
        $stmt = $conn->prepare("UPDATE donations SET payment_status = ? WHERE id = ?");
        $stmt->bind_param("si", $status_baru, $id);
        $stmt->execute();
    }

    // Redirect kembali ke tab yang sama
    header("Location: admin_donasi.php?tab=" . $current_tab);
    exit;
}

// --- LOGIC: PAGINATION & FILTER ---
$tab = isset($_GET['tab']) ? $_GET['tab'] : 'pending';
$keyword = isset($_GET['cari']) ? mysqli_real_escape_string($conn, $_GET['cari']) : '';

// Tentukan kondisi WHERE berdasarkan Tab
switch ($tab) {
    case 'terkumpul':
        $where_status = "d.payment_status = 'success'";
        $page_title = "Dana Terkumpul (Kas)";
        $page_desc = "Dana yang sudah masuk dan siap untuk disalurkan.";
        break;
    case 'penyaluran':
        $where_status = "d.payment_status = 'distributed'";
        $page_title = "Riwayat Penyaluran";
        $page_desc = "Dana yang sudah disalurkan untuk kegiatan/program.";
        break;
    case 'ditolak':
        $where_status = "d.payment_status = 'failed'";
        $page_title = "Donasi Ditolak";
        $page_desc = "Riwayat donasi yang tidak valid atau dibatalkan.";
        break;
    default: // 'pending'
        $where_status = "d.payment_status = 'pending'";
        $page_title = "Antrian Verifikasi";
        $page_desc = "Daftar donasi baru yang menunggu verifikasi admin.";
        $tab = 'pending'; // Reset jika input aneh
        break;
}

// Pagination Setup
$limit = 10;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$start = ($page - 1) * $limit;

// Base Query
$sqlBase = "SELECT d.*, u.name as donor_name 
            FROM donations d 
            JOIN donors u ON d.donor_id = u.id 
            WHERE $where_status";

if ($keyword) {
    $sqlBase .= " AND (u.name LIKE '%$keyword%' OR d.invoice_number LIKE '%$keyword%')";
}

// Hitung Total Data
$resultTotal = mysqli_query($conn, $sqlBase);
$total_data = mysqli_num_rows($resultTotal);
$total_pages = ceil($total_data / $limit);

// Ambil Data Halaman Ini
$sqlData = $sqlBase . " ORDER BY d.transaction_date DESC LIMIT $start, $limit";
$result = mysqli_query($conn, $sqlData);

// Helper untuk URL pagination agar Tab & Cari tidak hilang
function build_url($p, $t, $k)
{
    return "?page=" . $p . "&tab=" . $t . ($k ? "&cari=" . urlencode($k) : "");
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Kelola Donasi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/admin_donasi.css">
</head>

<body>

    <div class="sidebar">
        <div class="brand">
            <a href="dashboard.php">
                <img src="assets/images/logo-donoxygen.svg" alt="Donoxygen">
            </a>
        </div>

        <div class="small text-white-50 mb-4 px-2" style="margin-top: -10px;">Admin Dashboard</div>

        <a href="admin_donasi.php" class="nav-link active">
            <i class="fa-solid fa-hand-holding-dollar me-3" style="width: 20px;"></i> Kelola Donasi
        </a>

        <a href="admin_artikel.php" class="nav-link">
            <i class="fa-regular fa-newspaper me-3" style="width: 20px;"></i> Kelola Artikel
        </a>

        <div style="margin-top: 40px;"></div>

        <a href="dashboard.php" class="nav-link">
            <i class="fa-solid fa-arrow-left me-3" style="width: 20px;"></i> Kembali ke Web
        </a>
    </div>

    <div class="main-content">
        <div class="d-flex justify-content-between align-items-start mb-4">
            <div>
                <h2 class="fw-bold text-dark mb-1"><?= $page_title ?></h2>
                <p class="text-muted mb-0"><?= $page_desc ?></p>
            </div>

            <form method="GET" class="d-flex gap-2">
                <input type="hidden" name="tab" value="<?= $tab ?>">
                <input type="text" name="cari" class="form-control form-control-sm" placeholder="Cari donatur/ID..." value="<?= htmlspecialchars($keyword) ?>" style="width: 250px;">
                <button class="btn btn-sm btn-dark"><i class="fa-solid fa-magnifying-glass"></i></button>
            </form>
        </div>

        <ul class="nav nav-tabs mb-4">
            <li class="nav-item">
                <a class="nav-link <?= ($tab == 'pending') ? 'active' : '' ?>" href="?tab=pending">
                    <i class="fa-regular fa-clock me-1"></i> Verifikasi
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= ($tab == 'terkumpul') ? 'active' : '' ?>" href="?tab=terkumpul">
                    <i class="fa-solid fa-vault me-1"></i> Terkumpul (Kas)
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= ($tab == 'penyaluran') ? 'active' : '' ?>" href="?tab=penyaluran">
                    <i class="fa-solid fa-paper-plane me-1"></i> Tersalurkan
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= ($tab == 'ditolak') ? 'active' : '' ?>" href="?tab=ditolak">
                    <i class="fa-solid fa-ban me-1"></i> Ditolak
                </a>
            </li>
        </ul>

        <div class="card card-custom p-0 overflow-hidden">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th class="ps-4">Info Donatur</th>
                            <th>Nominal</th>
                            <th>Tanggal</th>
                            <th>Bukti</th>
                            <th class="text-end pe-4">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (mysqli_num_rows($result) > 0): ?>
                            <?php while ($row = mysqli_fetch_assoc($result)): ?>
                                <tr>
                                    <td class="ps-4">
                                        <div class="fw-bold text-dark"><?= htmlspecialchars($row['donor_name']) ?></div>
                                        <div class="small text-muted">#<?= $row['invoice_number'] ?></div>
                                    </td>
                                    <td class="fw-bold text-success">
                                        Rp <?= number_format($row['amount'], 0, ',', '.') ?>
                                    </td>
                                    <td class="small text-secondary">
                                        <?= date('d M Y H:i', strtotime($row['transaction_date'])) ?>
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-outline-secondary btn-action" data-bs-toggle="modal" data-bs-target="#modalBukti<?= $row['id'] ?>">
                                            <i class="fa-regular fa-image"></i>
                                        </button>
                                    </td>
                                    <td class="text-end pe-4">

                                        <?php if ($tab == 'pending'): ?>
                                            <button class="btn btn-sm btn-success btn-action me-1" data-bs-toggle="modal" data-bs-target="#modalVerif<?= $row['id'] ?>">
                                                <i class="fa-solid fa-check"></i> Proses
                                            </button>
                                        <?php elseif ($tab == 'terkumpul'): ?>
                                            <button class="btn btn-sm btn-primary btn-action" data-bs-toggle="modal" data-bs-target="#modalSalur<?= $row['id'] ?>">
                                                <i class="fa-solid fa-share-from-square me-1"></i> Salurkan
                                            </button>
                                        <?php elseif ($tab == 'penyaluran'): ?>
                                            <span class="badge badge-distributed rounded-pill px-3">Tersalurkan</span>
                                        <?php elseif ($tab == 'ditolak'): ?>
                                            <span class="badge bg-secondary rounded-pill px-3">Ditolak</span>
                                        <?php endif; ?>

                                    </td>
                                </tr>

                                <div class="modal fade" id="modalBukti<?= $row['id'] ?>" tabindex="-1">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content">
                                            <div class="modal-header border-0 pb-0">
                                                <h6 class="modal-title fw-bold">Bukti Transfer #<?= $row['invoice_number'] ?></h6>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body text-center">
                                                <?php if (!empty($row['payment_proof']) && file_exists("assets/uploads/" . $row['payment_proof'])): ?>
                                                    <img src="assets/uploads/<?= $row['payment_proof'] ?>" class="img-fluid rounded mb-3" style="max-height:400px;">
                                                <?php else: ?>
                                                    <div class="py-4 bg-light rounded text-muted mb-3">Tidak ada bukti / File rusak</div>
                                                <?php endif; ?>
                                                <div class="d-grid">
                                                    <small class="text-muted">Metode: <?= $row['payment_method'] ?? '-' ?></small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="modal fade" id="modalVerif<?= $row['id'] ?>" tabindex="-1">
                                    <div class="modal-dialog modal-sm modal-dialog-centered">
                                        <div class="modal-content">
                                            <div class="modal-body text-center p-4">
                                                <h6 class="fw-bold mb-3">Verifikasi Donasi?</h6>
                                                <p class="small text-muted mb-4">Pastikan dana sebesar <strong>Rp <?= number_format($row['amount']) ?></strong> sudah masuk ke rekening.</p>
                                                <form method="POST" class="d-grid gap-2">
                                                    <input type="hidden" name="action_id" value="<?= $row['id'] ?>">
                                                    <input type="hidden" name="current_tab" value="<?= $tab ?>">

                                                    <button type="submit" name="new_status" value="success" class="btn btn-success fw-medium">
                                                        <i class="fa-solid fa-check me-1"></i> Terima (Valid)
                                                    </button>
                                                    <button type="submit" name="new_status" value="failed" class="btn btn-outline-danger fw-medium">
                                                        <i class="fa-solid fa-xmark me-1"></i> Tolak (Invalid)
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="modal fade" id="modalSalur<?= $row['id'] ?>" tabindex="-1">
                                    <div class="modal-dialog modal-sm modal-dialog-centered">
                                        <div class="modal-content">
                                            <div class="modal-body text-center p-4">
                                                <div class="mb-3 text-primary"><i class="fa-solid fa-hand-holding-heart fa-3x"></i></div>
                                                <h6 class="fw-bold mb-2">Salurkan Dana?</h6>
                                                <p class="small text-muted mb-4">Status akan berubah menjadi <strong>Tersalurkan</strong> dan mengurangi saldo Kas.</p>
                                                <form method="POST" class="d-grid">
                                                    <input type="hidden" name="action_id" value="<?= $row['id'] ?>">
                                                    <input type="hidden" name="current_tab" value="<?= $tab ?>">

                                                    <button type="submit" name="new_status" value="distributed" class="btn btn-primary fw-medium">
                                                        Ya, Tandai Tersalurkan
                                                    </button>
                                                    <button type="button" class="btn btn-link text-decoration-none text-muted mt-2" data-bs-dismiss="modal">Batal</button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center py-5 text-muted">
                                    <i class="fa-solid fa-inbox fa-2x mb-3 d-block opacity-25"></i>
                                    Tidak ada data di tab ini.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div class="p-3 border-top d-flex justify-content-between align-items-center">
                <small class="text-muted">Menampilkan <?= mysqli_num_rows($result) ?> dari <?= $total_data ?> data</small>
                <nav>
                    <ul class="pagination pagination-sm mb-0">
                        <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                            <a class="page-link" href="<?= build_url($page - 1, $tab, $keyword) ?>">Prev</a>
                        </li>
                        <li class="page-item disabled"><span class="page-link"><?= $page ?></span></li>
                        <li class="page-item <?= ($page >= $total_pages) ? 'disabled' : '' ?>">
                            <a class="page-link" href="<?= build_url($page + 1, $tab, $keyword) ?>">Next</a>
                        </li>
                    </ul>
                </nav>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>