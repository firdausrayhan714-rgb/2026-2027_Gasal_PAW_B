-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Server version:               8.0.30 - MySQL Community Server - GPL
-- Server OS:                    Win64
-- HeidiSQL Version:             12.1.0.6537
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


-- Dumping database structure for penjualan
CREATE DATABASE IF NOT EXISTS `penjualan` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci */ /*!80016 DEFAULT ENCRYPTION='N' */;
USE `penjualan`;

-- Dumping structure for table penjualan.barang
CREATE TABLE IF NOT EXISTS `barang` (
  `id` int NOT NULL AUTO_INCREMENT,
  `kode_barang` varchar(10) COLLATE utf8mb4_general_ci NOT NULL,
  `nama_barang` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `harga` int NOT NULL,
  `stok` int NOT NULL DEFAULT '0',
  `supplier_id` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_kode_barang` (`kode_barang`),
  KEY `idx_barang_supplier` (`supplier_id`),
  CONSTRAINT `fk_barang_supplier` FOREIGN KEY (`supplier_id`) REFERENCES `supplier` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table penjualan.barang: ~11 rows (approximately)
INSERT INTO `barang` (`id`, `kode_barang`, `nama_barang`, `harga`, `stok`, `supplier_id`) VALUES
	(1, 'BRG001', 'Beras Premium 5 kg', 68000, 100, 1),
	(2, 'BRG002', 'Minyak Goreng 2 L', 36000, 80, 1),
	(3, 'BRG003', 'Gula Pasir 1 kg', 17000, 120, 2),
	(4, 'BRG004', 'Kopi Bubuk 200 g', 22000, 60, 3),
	(5, 'BRG005', 'Teh Celup isi 25', 12000, 90, 3),
	(6, 'BRG006', 'Mi Instan Goreng', 3500, 400, 4),
	(7, 'BRG007', 'Sabun Mandi Batang', 4500, 150, 4),
	(8, 'BRG008', 'Deterjen Bubuk 800 g', 21000, 70, 5),
	(9, 'BRG009', 'Susu UHT 1 L', 19000, 50, 2),
	(10, 'BRG010', 'Telur Ayam 1 kg', 29000, 40, 5),
	(11, 'BRG011', 'Tisu Wajah 250 lembar', 9000, 60, 4);

-- Dumping structure for table penjualan.pelanggan
CREATE TABLE IF NOT EXISTS `pelanggan` (
  `id` varchar(20) COLLATE utf8mb4_general_ci NOT NULL,
  `nama` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `jenis_kelamin` enum('L','P') COLLATE utf8mb4_general_ci NOT NULL,
  `telp` varchar(12) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `alamat` text COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table penjualan.pelanggan: ~5 rows (approximately)
INSERT INTO `pelanggan` (`id`, `nama`, `jenis_kelamin`, `telp`, `alamat`) VALUES
	('PLG001', 'Budi Santoso', 'L', '081234567801', 'Jl. Kenanga No. 1, Bangkalan'),
	('PLG002', 'Siti Aminah', 'P', '081234567802', 'Jl. Mawar No. 7, Bangkalan'),
	('PLG003', 'Ahmad Fauzi', 'L', '081234567803', 'Jl. Melati No. 15, Sampang'),
	('PLG004', 'Dewi Lestari', 'P', '081234567804', 'Jl. Anggrek No. 4, Surabaya'),
	('PLG005', 'Rudi Hartono', 'L', '081234567805', 'Jl. Flamboyan No. 9, Pamekasan');

-- Dumping structure for table penjualan.pembayaran
CREATE TABLE IF NOT EXISTS `pembayaran` (
  `id` int NOT NULL AUTO_INCREMENT,
  `waktu_bayar` datetime NOT NULL,
  `total` int NOT NULL,
  `metode` enum('TUNAI','TRANSFER','EDC') COLLATE utf8mb4_general_ci NOT NULL,
  `transaksi_id` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_pembayaran_transaksi` (`transaksi_id`),
  CONSTRAINT `fk_pembayaran_transaksi` FOREIGN KEY (`transaksi_id`) REFERENCES `transaksi` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table penjualan.pembayaran: ~10 rows (approximately)
INSERT INTO `pembayaran` (`id`, `waktu_bayar`, `total`, `metode`, `transaksi_id`) VALUES
	(1, '2026-10-01 10:00:00', 206000, 'TRANSFER', 1),
	(2, '2026-10-03 10:00:00', 94000, 'EDC', 2),
	(3, '2026-10-05 10:00:00', 112000, 'TUNAI', 3),
	(4, '2026-10-08 10:00:00', 483000, 'TRANSFER', 4),
	(5, '2026-10-10 10:00:00', 73000, 'EDC', 5),
	(6, '2026-10-12 10:00:00', 467000, 'TUNAI', 6),
	(7, '2026-10-15 10:00:00', 80000, 'TRANSFER', 7),
	(8, '2026-10-18 10:00:00', 153000, 'EDC', 8),
	(9, '2026-10-22 10:00:00', 224000, 'TUNAI', 9),
	(10, '2026-10-25 10:00:00', 306000, 'TRANSFER', 10);

-- Dumping structure for table penjualan.supplier
CREATE TABLE IF NOT EXISTS `supplier` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nama` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `telp` varchar(12) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `alamat` text COLLATE utf8mb4_general_ci,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table penjualan.supplier: ~5 rows (approximately)
INSERT INTO `supplier` (`id`, `nama`, `telp`, `alamat`) VALUES
	(1, 'PT Sumber Makmur', '031555010101', 'Jl. Raya Darmo No. 12, Surabaya'),
	(2, 'CV Aneka Jaya', '031555020202', 'Jl. Rungkut Industri No. 5, Surabaya'),
	(3, 'UD Maju Terus', '0324555030', 'Jl. Trunojoyo No. 21, Bangkalan'),
	(4, 'PT Nusantara Abadi', '0341555040', 'Jl. Ijen No. 8, Malang'),
	(5, 'CV Sentosa Prima', '0321555050', 'Jl. Pahlawan No. 3, Mojokerto');

-- Dumping structure for table penjualan.transaksi
CREATE TABLE IF NOT EXISTS `transaksi` (
  `id` int NOT NULL AUTO_INCREMENT,
  `waktu_transaksi` date NOT NULL,
  `keterangan` text COLLATE utf8mb4_general_ci,
  `total` int NOT NULL DEFAULT '0',
  `pelanggan_id` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_transaksi_pelanggan` (`pelanggan_id`),
  CONSTRAINT `fk_transaksi_pelanggan` FOREIGN KEY (`pelanggan_id`) REFERENCES `pelanggan` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table penjualan.transaksi: ~10 rows (approximately)
INSERT INTO `transaksi` (`id`, `waktu_transaksi`, `keterangan`, `total`, `pelanggan_id`) VALUES
	(1, '2026-10-01', 'Belanja bulanan', 206000, 'PLG001'),
	(2, '2026-10-03', 'Kebutuhan dapur', 94000, 'PLG002'),
	(3, '2026-10-05', 'Belanja mingguan', 112000, 'PLG003'),
	(4, '2026-10-08', 'Stok warung', 483000, 'PLG004'),
	(5, '2026-10-10', 'Belanja harian', 73000, 'PLG005'),
	(6, '2026-10-12', 'Pesanan acara keluarga', 467000, 'PLG001'),
	(7, '2026-10-15', 'Belanja mingguan', 80000, 'PLG002'),
	(8, '2026-10-18', 'Kebutuhan kos', 153000, 'PLG003'),
	(9, '2026-10-22', 'Belanja bulanan', 224000, 'PLG004'),
	(10, '2026-10-25', 'Stok warung', 306000, 'PLG005');

-- Dumping structure for table penjualan.transaksi_detail
CREATE TABLE IF NOT EXISTS `transaksi_detail` (
  `transaksi_id` int NOT NULL,
  `barang_id` int NOT NULL,
  `harga` int NOT NULL,
  `qty` int NOT NULL,
  PRIMARY KEY (`transaksi_id`,`barang_id`),
  KEY `idx_detail_barang` (`barang_id`),
  CONSTRAINT `fk_detail_barang` FOREIGN KEY (`barang_id`) REFERENCES `barang` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_detail_transaksi` FOREIGN KEY (`transaksi_id`) REFERENCES `transaksi` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table penjualan.transaksi_detail: ~28 rows (approximately)
INSERT INTO `transaksi_detail` (`transaksi_id`, `barang_id`, `harga`, `qty`) VALUES
	(1, 1, 68000, 2),
	(1, 2, 36000, 1),
	(1, 3, 17000, 2),
	(2, 2, 36000, 2),
	(2, 4, 22000, 1),
	(3, 5, 12000, 2),
	(3, 6, 3500, 20),
	(3, 7, 4500, 4),
	(4, 1, 68000, 5),
	(4, 3, 17000, 5),
	(4, 10, 29000, 2),
	(5, 6, 3500, 10),
	(5, 9, 19000, 2),
	(6, 1, 68000, 3),
	(6, 2, 36000, 3),
	(6, 3, 17000, 4),
	(6, 10, 29000, 3),
	(7, 4, 22000, 2),
	(7, 5, 12000, 3),
	(8, 6, 3500, 30),
	(8, 7, 4500, 6),
	(8, 8, 21000, 1),
	(9, 1, 68000, 1),
	(9, 8, 21000, 2),
	(9, 9, 19000, 6),
	(10, 2, 36000, 4),
	(10, 3, 17000, 6),
	(10, 5, 12000, 5);

-- Dumping structure for table penjualan.user
CREATE TABLE IF NOT EXISTS `user` (
  `id_user` tinyint NOT NULL AUTO_INCREMENT,
  `username` varchar(30) COLLATE utf8mb4_general_ci NOT NULL,
  `password` varchar(35) COLLATE utf8mb4_general_ci NOT NULL,
  `nama` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `alamat` varchar(150) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `hp` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `level` tinyint(1) NOT NULL DEFAULT '2',
  PRIMARY KEY (`id_user`),
  UNIQUE KEY `uq_username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table penjualan.user: ~5 rows (approximately)
INSERT INTO `user` (`id_user`, `username`, `password`, `nama`, `alamat`, `hp`, `level`) VALUES
	(1, 'admin', '0192023a7bbd73250516f069df18b500', 'Administrator', 'Jl. Kampus No. 1, Bangkalan', '081300000001', 1),
	(2, 'kasir1', 'de28f8f7998f23ab4194b51a6029416f', 'Kasir Satu', 'Jl. Kampus No. 2, Bangkalan', '081300000002', 2),
	(3, 'kasir2', 'de28f8f7998f23ab4194b51a6029416f', 'Kasir Dua', 'Jl. Kampus No. 3, Bangkalan', '081300000003', 2),
	(4, 'gudang', 'cbb7449d78314665f9e7c7dd0a18a68a', 'Staf Gudang', 'Jl. Kampus No. 4, Bangkalan', '081300000004', 3),
	(5, 'owner', '5be057accb25758101fa5eadbbd79503', 'Pemilik Toko', 'Jl. Kampus No. 5, Bangkalan', '081300000005', 1);

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
