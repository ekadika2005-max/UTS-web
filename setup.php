<?php
// setup.php - Inisialisasi Database
require_once __DIR__ . '/koneksi.php';

try {
    $db->exec("
        CREATE TABLE IF NOT EXISTS kategori (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            nama_kategori TEXT NOT NULL UNIQUE,
            keterangan TEXT
        );

        CREATE TABLE IF NOT EXISTS barang (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            kode_barang TEXT NOT NULL UNIQUE,
            nama_barang TEXT NOT NULL,
            kategori_id INTEGER NOT NULL,
            harga_beli INTEGER NOT NULL,
            harga_jual INTEGER NOT NULL,
            stok INTEGER NOT NULL DEFAULT 0,
            FOREIGN KEY (kategori_id) REFERENCES kategori(id) ON DELETE RESTRICT
        );

        CREATE TABLE IF NOT EXISTS pembelian (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            no_faktur TEXT NOT NULL,
            tanggal TEXT NOT NULL,
            barang_id INTEGER NOT NULL,
            jumlah INTEGER NOT NULL,
            harga_beli INTEGER NOT NULL,
            total_biaya INTEGER NOT NULL,
            suplier TEXT,
            FOREIGN KEY (barang_id) REFERENCES barang(id) ON DELETE RESTRICT
        );

        CREATE TABLE IF NOT EXISTS penjualan (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            no_struk TEXT NOT NULL,
            tanggal TEXT NOT NULL,
            barang_id INTEGER NOT NULL,
            jumlah INTEGER NOT NULL,
            harga_jual INTEGER NOT NULL,
            total_bayar INTEGER NOT NULL,
            laba INTEGER NOT NULL,
            FOREIGN KEY (barang_id) REFERENCES barang(id) ON DELETE RESTRICT
        );
    ");

    // Seeding data kategori jika kosong
    $check = $db->query("SELECT COUNT(*) FROM kategori")->fetchColumn();
    if ($check == 0) {
        $db->exec("
            INSERT INTO kategori (nama_kategori, keterangan) VALUES 
            ('Sembako', 'Kebutuhan pokok beras, minyak, gula, tepung'),
            ('Minuman', 'Kopi, teh, susu, dan air mineral'),
            ('Makanan Ringan', 'Biskuit, keripik, dan mie instan'),
            ('Kebersihan', 'Sabun cuci, pasta gigi, dan sampo');

            INSERT INTO barang (kode_barang, nama_barang, kategori_id, harga_beli, harga_jual, stok) VALUES
            ('BRG001', 'Beras Ramos 5kg', 1, 60000, 68000, 20),
            ('BRG002', 'Minyak Goreng Sawit 1L', 1, 14000, 16500, 35),
            ('BRG003', 'Gula Pasir Kristal 1kg', 1, 13500, 15000, 40),
            ('BRG004', 'Kopi Kapal Api 165g', 2, 11000, 13000, 50),
            ('BRG005', 'Indomie Goreng Original', 3, 2800, 3100, 100),
            ('BRG006', 'Sabun Mandi Lifebuoy', 4, 3500, 4200, 45);
        ");
    }
    echo "Database SQLite berhasil disiapkan.\n";
} catch (PDOException $e) {
    die("Gagal menyiapkan database: " . $e->getMessage());
}
?>