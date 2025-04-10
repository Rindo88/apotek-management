<?php
require('../config/config.php');
require('../vendor/fpdf/fpdf.php');

class ReceiptPDF extends FPDF {
    function Header() {
        $this->SetFont('Arial', 'B', 12);
        $this->Cell(0, 5, 'APOTEK SEHAT SEJAHTERA', 0, 1, 'C');
        $this->SetFont('Arial', '', 8);
        $this->Cell(0, 5, 'Jl. Raya Narogong No. 321, Bekasi Timur', 0, 1, 'C');
        $this->Cell(0, 5, 'Telp: +62 81212193921', 0, 1, 'C');
        $this->Cell(0, 5, 'NPWP: 01.234.456.76', 0, 1, 'C');
        $this->Line(10, 30, 200, 30);
        $this->Ln(5);
    }

    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 10, 'Terima kasih atas kunjungan Anda', 0, 0, 'C');
    }
}

if (!isset($_GET['id_transaksi'])) {
    die("ID Transaksi tidak ditemukan.");
}

$id_transaksi = pg_escape_string($conn, $_GET['id_transaksi']);

// Fetch transaction data
$query = "SELECT t.*, p.nama as pembeli_nama, p.alamat as pembeli_alamat, 
          u.nama as kasir_nama, pm.metode_pembayaran, pm.total_pembayaran 
          FROM transaksi t 
          JOIN pembeli p ON t.pembeli_id = p.id 
          JOIN users u ON t.kasir_id = u.id 
          LEFT JOIN pembayaran pm ON t.id = pm.transaksi_id 
          WHERE t.id = $id_transaksi";
$result = pg_query($conn, $query);
$transaksi = pg_fetch_assoc($result);

// Fetch transaction details
$query_detail = "SELECT td.*, o.nama, o.satuan 
                FROM transaksi_detail td 
                JOIN obat o ON td.obat_id = o.id 
                WHERE td.transaksi_id = $id_transaksi";
$result_detail = pg_query($conn, $query_detail);
$details = pg_fetch_all($result_detail);

// Create PDF
$pdf = new ReceiptPDF('P', 'mm', array(80, 200));
$pdf->AddPage();

// Transaction Info
$pdf->SetFont('Arial', '', 8);
$pdf->Cell(0, 5, 'No. Transaksi: ' . $transaksi['id'], 0, 1);
$pdf->Cell(0, 5, 'Tanggal: ' . date('d/m/Y H:i', strtotime($transaksi['created_at'])), 0, 1);
$pdf->Cell(0, 5, 'Kasir: ' . $transaksi['kasir_nama'], 0, 1);
$pdf->Cell(0, 5, 'Pembeli: ' . $transaksi['pembeli_nama'], 0, 1);
$pdf->Ln(5);

// Items
$pdf->SetFont('Arial', 'B', 8);
$pdf->Cell(25, 5, 'Item', 0);
$pdf->Cell(8, 5, 'Qty', 0);
$pdf->Cell(20, 5, 'Harga', 0);
$pdf->Cell(20, 5, 'Total', 0, 1);
$pdf->Line(10, $pdf->GetY(), 70, $pdf->GetY());
$pdf->Ln(2);

$pdf->SetFont('Arial', '', 8);
foreach ($details as $item) {
    $pdf->MultiCell(25, 4, $item['nama'], 0);
    $y = $pdf->GetY();
    $pdf->SetXY(35, $y-4);
    $pdf->Cell(8, 4, $item['jumlah'], 0);
    $pdf->Cell(20, 4, number_format($item['harga_satuan'], 0, ',', '.'), 0);
    $pdf->Cell(20, 4, number_format($item['subtotal'], 0, ',', '.'), 0, 1);
}

$pdf->Line(10, $pdf->GetY(), 70, $pdf->GetY());
$pdf->Ln(2);

// Total
$pdf->SetFont('Arial', 'B', 8);
$pdf->Cell(53, 5, 'Total:', 0);
$pdf->Cell(20, 5, number_format($transaksi['total'], 0, ',', '.'), 0, 1);
$pdf->Cell(53, 5, 'Bayar:', 0);
$pdf->Cell(20, 5, number_format($transaksi['total_pembayaran'], 0, ',', '.'), 0, 1);
$pdf->Cell(53, 5, 'Kembali:', 0);
$pdf->Cell(20, 5, number_format($transaksi['total_pembayaran'] - $transaksi['total'], 0, ',', '.'), 0, 1);
$pdf->Ln(5);

// Payment Info
$pdf->SetFont('Arial', '', 8);
$pdf->Cell(0, 5, 'Metode Pembayaran: ' . $transaksi['metode_pembayaran'], 0, 1, 'C');
$pdf->Ln(5);

// Output PDF
$pdf->Output('Struk_'.$id_transaksi.'.pdf', 'I');
