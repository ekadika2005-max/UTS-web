<?php
// api.php - Backend untuk menghandle AJAX request dari Frontend
header('Content-Type: application/json');
require_once __DIR__ . '/koneksi.php';

$action = $_GET['action'] ?? '';

try {
    if ($action === 'get_dashboard') {
        // Hitung total jenis barang
        $totalBarang = $db->query("SELECT COUNT(*) FROM barang")->fetchColumn();
        
        // Hitung total unit stok
        $totalStok = $db->query("SELECT SUM(stok) FROM barang")->fetchColumn() ?: 0;
        
        // Hitung total omset penjualan
        $totalOmset = $db->query("SELECT SUM(total_bayar) FROM penjualan")->fetchColumn() ?: 0;
        
        // Hitung total laba
        $totalLaba = $db->query("SELECT SUM(laba) FROM penjualan")->fetchColumn() ?: 0;

        echo json_encode([
            'status' => 'success',
            'data' => [
                'total_barang' => $totalBarang,
                'total_stok' => $totalStok,
                'formatted_omset' => 'Rp ' . number_format($totalOmset, 0, ',', '.'),
                'formatted_laba' => 'Rp ' . number_format($totalLaba, 0, ',', '.')
            ]
        ]);
        exit;
    } 
    
    elseif ($action === 'get_barang') {
        // Ambil data barang beserta nama kategorinya
        $stmt = $db->query("
            SELECT b.*, k.nama_kategori 
            FROM barang b 
            JOIN kategori k ON b.kategori_id = k.id
        ");
        $barangList = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $row['formatted_harga_beli'] = 'Rp ' . number_format($row['harga_beli'], 0, ',', '.');
            $row['formatted_harga_jual'] = 'Rp ' . number_format($row['harga_jual'], 0, ',', '.');
            $barangList[] = $row;
        }

        echo json_encode([
            'status' => 'success',
            'data' => $barangList
        ]);
        exit;
    } 
    
    elseif ($action === 'transaksi_jual') {
        $input = json_decode(file_get_contents('php://input'), true);
        $barangId = $input['barang_id'] ?? null;
        $jumlahBeli = intval($input['jumlah'] ?? 0);

        if (!$barangId || $jumlahBeli <= 0) {
            echo json_encode(['status' => 'error', 'message' => 'Data transaksi tidak valid.']);
            exit;
        }

        // Ambil data barang berdasarkan ID
        $stmt = $db->prepare("SELECT * FROM barang WHERE id = ?");
        $stmt->execute([$barangId]);
        $barang = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$barang) {
            echo json_encode(['status' => 'error', 'message' => 'Barang tidak ditemukan.']);
            exit;
        }

        if ($barang['stok'] < $jumlahBeli) {
            echo json_encode(['status' => 'error', 'message' => 'Stok barang tidak mencukupi!']);
            exit;
        }

        // Hitung total bayar dan laba
        $totalBayar = $barang['harga_jual'] * $jumlahBeli;
        $labaSatuan = $barang['harga_jual'] - $barang['harga_beli'];
        $totalLaba = $labaSatuan * $jumlahBeli;
        $noStruk = 'TRX-' . date('YmdHis');
        $tanggal = date('Y-m-d H:i:s');

        // Mulai transaksi database
        $db->beginTransaction();

        // 1. Masukkan ke tabel penjualan
        $insert = $db->prepare("INSERT INTO penjualan (no_struk, tanggal, barang_id, jumlah, harga_jual, total_bayar, laba) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $insert->execute([$noStruk, $tanggal, $barangId, $jumlahBeli, $barang['harga_jual'], $totalBayar, $totalLaba]);

        // 2. Kurangi stok barang
        $stokBaru = $barang['stok'] - $jumlahBeli;
        $update = $db->prepare("UPDATE barang SET stok = ? WHERE id = ?");
        $update->execute([$stokBaru, $barangId]);

        $db->commit();

        echo json_encode(['status' => 'success', 'message' => 'Transaksi penjualan berhasil disimpan!']);
        exit;
    }

    echo json_encode(['status' => 'error', 'message' => 'Aksi tidak dikenali.']);
} catch (Exception $e) {
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>