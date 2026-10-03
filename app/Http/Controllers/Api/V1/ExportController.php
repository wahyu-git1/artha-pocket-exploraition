<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Expense;
use App\Models\IncomeReceipt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class ExportController extends BaseController
{
    public function transactions(Request $request)
    {
        $userId = $request->user()->id;
        $from = $request->query('from', date('Y-m-01'));
        $to = $request->query('to', date('Y-m-t'));
        $type = $request->query('type', 'all'); // 'all', 'expense', 'income'
        $format = strtolower($request->query('format', 'excel')); // 'excel' or 'csv'

        $rows = [];
        $totalExpense = 0;
        $totalIncome = 0;

        // 1. Fetch Expenses
        if ($type === 'all' || $type === 'expense') {
            $expenses = Expense::where('user_id', $userId)
                ->whereBetween('spent_at', [$from, $to])
                ->with(['category', 'income'])
                ->orderBy('spent_at')
                ->get();

            foreach ($expenses as $e) {
                $totalExpense += $e->amount;
                $rows[] = [
                    'date'     => $e->spent_at ? $e->spent_at->format('Y-m-d') : '',
                    'type'     => 'Pengeluaran',
                    'item'     => $e->item,
                    'category' => $e->category->name ?? 'Umum',
                    'wallet'   => $e->income->name ?? 'Dompet Utama',
                    'amount'   => (int) $e->amount,
                    'is_income'=> false,
                    'note'     => $e->note ?? '',
                ];
            }
        }

        // 2. Fetch Incomes (Receipts)
        if ($type === 'all' || $type === 'income') {
            $receipts = IncomeReceipt::where('user_id', $userId)
                ->whereBetween('received_at', [$from, $to])
                ->with(['category', 'income'])
                ->orderBy('received_at')
                ->get();

            foreach ($receipts as $r) {
                $totalIncome += $r->amount;
                $rows[] = [
                    'date'     => $r->received_at ? $r->received_at->format('Y-m-d') : '',
                    'type'     => 'Pemasukan',
                    'item'     => $r->note ?: 'Pemasukan',
                    'category' => $r->category->name ?? 'Pemasukan',
                    'wallet'   => $r->income->name ?? 'Dompet',
                    'amount'   => (int) $r->amount,
                    'is_income'=> true,
                    'note'     => $r->note ?? '',
                ];
            }
        }

        // Sort by date ascending
        usort($rows, function ($a, $b) {
            return strcmp($a['date'], $b['date']);
        });

        $netCashFlow = $totalIncome - $totalExpense;

        if ($format === 'excel' || $format === 'xls' || $format === 'xlsx') {
            return $this->exportExcel($rows, $from, $to, $totalIncome, $totalExpense, $netCashFlow);
        }

        return $this->exportCsv($rows, $from, $to, $totalIncome, $totalExpense, $netCashFlow);
    }

    private function exportExcel(array $rows, string $from, string $to, int $totalIncome, int $totalExpense, int $netCashFlow)
    {
        $fileName = "Laporan_Finansial_{$from}_sd_{$to}.xls";

        $headers = [
            "Content-Type"        => "application/vnd.ms-excel; charset=utf-8",
            "Content-Disposition" => "attachment; filename=\"{$fileName}\"",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $callback = function () use ($rows, $from, $to, $totalIncome, $totalExpense, $netCashFlow) {
            $out = fopen('php://output', 'w');

            // XML Spreadsheet 2003 Header with rich formatting
            fwrite($out, '<?xml version="1.0"?>' . "\n");
            fwrite($out, '<?mso-application progid="Excel.Sheet"?>' . "\n");
            fwrite($out, '<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet"' . "\n");
            fwrite($out, ' xmlns:o="urn:schemas-microsoft-com:office:office"' . "\n");
            fwrite($out, ' xmlns:x="urn:schemas-microsoft-com:office:excel"' . "\n");
            fwrite($out, ' xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet"' . "\n");
            fwrite($out, ' xmlns:html="http://www.w3.org/TR/REC-html40">' . "\n");

            // Styles Definition
            fwrite($out, ' <Styles>' . "\n");
            fwrite($out, '  <Style ss:ID="Default" ss:Name="Normal"><Font ss:FontName="Calibri" ss:Size="11" ss:Color="#102A43"/></Style>' . "\n");
            fwrite($out, '  <Style ss:ID="Title"><Font ss:FontName="Calibri" ss:Size="16" ss:Bold="1" ss:Color="#1F4E79"/></Style>' . "\n");
            fwrite($out, '  <Style ss:ID="Subtitle"><Font ss:FontName="Calibri" ss:Size="11" ss:Italic="1" ss:Color="#6B7C93"/></Style>' . "\n");
            fwrite($out, '  <Style ss:ID="KpiLabel"><Font ss:FontName="Calibri" ss:Size="10" ss:Bold="1" ss:Color="#475569"/><Interior ss:Color="#F1F5F9" ss:Pattern="Solid"/></Style>' . "\n");
            fwrite($out, '  <Style ss:ID="KpiIncome"><Font ss:FontName="Calibri" ss:Size="11" ss:Bold="1" ss:Color="#16A34A"/><Interior ss:Color="#F1F5F9" ss:Pattern="Solid"/><NumberFormat ss:Format="#,##0"/></Style>' . "\n");
            fwrite($out, '  <Style ss:ID="KpiExpense"><Font ss:FontName="Calibri" ss:Size="11" ss:Bold="1" ss:Color="#DC2626"/><Interior ss:Color="#F1F5F9" ss:Pattern="Solid"/><NumberFormat ss:Format="#,##0"/></Style>' . "\n");
            fwrite($out, '  <Style ss:ID="KpiNet"><Font ss:FontName="Calibri" ss:Size="11" ss:Bold="1" ss:Color="#1F4E79"/><Interior ss:Color="#F1F5F9" ss:Pattern="Solid"/><NumberFormat ss:Format="#,##0"/></Style>' . "\n");
            fwrite($out, '  <Style ss:ID="Header"><Font ss:FontName="Calibri" ss:Size="11" ss:Bold="1" ss:Color="#FFFFFF"/><Interior ss:Color="#1F4E79" ss:Pattern="Solid"/><Alignment ss:Horizontal="Center"/></Style>' . "\n");
            fwrite($out, '  <Style ss:ID="DataDate"><Alignment ss:Horizontal="Center"/></Style>' . "\n");
            fwrite($out, '  <Style ss:ID="DataIncomeType"><Font ss:Bold="1" ss:Color="#16A34A"/><Alignment ss:Horizontal="Center"/></Style>' . "\n");
            fwrite($out, '  <Style ss:ID="DataExpenseType"><Font ss:Bold="1" ss:Color="#DC2626"/><Alignment ss:Horizontal="Center"/></Style>' . "\n");
            fwrite($out, '  <Style ss:ID="DataCurrencyIncome"><Font ss:Bold="1" ss:Color="#16A34A"/><NumberFormat ss:Format="#,##0"/></Style>' . "\n");
            fwrite($out, '  <Style ss:ID="DataCurrencyExpense"><Font ss:Bold="1" ss:Color="#DC2626"/><NumberFormat ss:Format="#,##0"/></Style>' . "\n");
            fwrite($out, ' </Styles>' . "\n");

            // Worksheet
            fwrite($out, ' <Worksheet ss:Name="Laporan Keuangan">' . "\n");
            fwrite($out, '  <Table>' . "\n");
            fwrite($out, '   <Column ss:Width="40"/>' . "\n");  // No
            fwrite($out, '   <Column ss:Width="90"/>' . "\n");  // Tanggal
            fwrite($out, '   <Column ss:Width="100"/>' . "\n"); // Tipe
            fwrite($out, '   <Column ss:Width="180"/>' . "\n"); // Item
            fwrite($out, '   <Column ss:Width="130"/>' . "\n"); // Kategori
            fwrite($out, '   <Column ss:Width="130"/>' . "\n"); // Dompet
            fwrite($out, '   <Column ss:Width="110"/>' . "\n"); // Nominal
            fwrite($out, '   <Column ss:Width="160"/>' . "\n"); // Catatan

            // Title Rows
            fwrite($out, '   <Row><Cell ss:StyleID="Title"><Data ss:Type="String">LAPORAN KEUANGAN CATATDUIT</Data></Cell></Row>' . "\n");
            fwrite($out, '   <Row><Cell ss:StyleID="Subtitle"><Data ss:Type="String">Periode: ' . htmlspecialchars($from) . ' s/d ' . htmlspecialchars($to) . '</Data></Cell></Row>' . "\n");
            fwrite($out, '   <Row/>' . "\n"); // blank

            // KPI Summary Box in Excel
            fwrite($out, '   <Row>' . "\n");
            fwrite($out, '    <Cell ss:StyleID="KpiLabel"><Data ss:Type="String">Total Pemasukan</Data></Cell>' . "\n");
            fwrite($out, '    <Cell ss:StyleID="KpiIncome"><Data ss:Type="Number">' . $totalIncome . '</Data></Cell>' . "\n");
            fwrite($out, '   </Row>' . "\n");
            fwrite($out, '   <Row>' . "\n");
            fwrite($out, '    <Cell ss:StyleID="KpiLabel"><Data ss:Type="String">Total Pengeluaran</Data></Cell>' . "\n");
            fwrite($out, '    <Cell ss:StyleID="KpiExpense"><Data ss:Type="Number">' . $totalExpense . '</Data></Cell>' . "\n");
            fwrite($out, '   </Row>' . "\n");
            fwrite($out, '   <Row>' . "\n");
            fwrite($out, '    <Cell ss:StyleID="KpiLabel"><Data ss:Type="String">Arus Kas Bersih (Net)</Data></Cell>' . "\n");
            fwrite($out, '    <Cell ss:StyleID="KpiNet"><Data ss:Type="Number">' . $netCashFlow . '</Data></Cell>' . "\n");
            fwrite($out, '   </Row>' . "\n");
            fwrite($out, '   <Row/>' . "\n"); // blank

            // Table Header
            fwrite($out, '   <Row>' . "\n");
            fwrite($out, '    <Cell ss:StyleID="Header"><Data ss:Type="String">No</Data></Cell>' . "\n");
            fwrite($out, '    <Cell ss:StyleID="Header"><Data ss:Type="String">Tanggal</Data></Cell>' . "\n");
            fwrite($out, '    <Cell ss:StyleID="Header"><Data ss:Type="String">Tipe</Data></Cell>' . "\n");
            fwrite($out, '    <Cell ss:StyleID="Header"><Data ss:Type="String">Item / Keterangan</Data></Cell>' . "\n");
            fwrite($out, '    <Cell ss:StyleID="Header"><Data ss:Type="String">Kategori</Data></Cell>' . "\n");
            fwrite($out, '    <Cell ss:StyleID="Header"><Data ss:Type="String">Dompet</Data></Cell>' . "\n");
            fwrite($out, '    <Cell ss:StyleID="Header"><Data ss:Type="String">Nominal (Rp)</Data></Cell>' . "\n");
            fwrite($out, '    <Cell ss:StyleID="Header"><Data ss:Type="String">Catatan</Data></Cell>' . "\n");
            fwrite($out, '   </Row>' . "\n");

            // Data Rows
            $no = 1;
            foreach ($rows as $r) {
                $typeStyle = $r['is_income'] ? 'DataIncomeType' : 'DataExpenseType';
                $amtStyle  = $r['is_income'] ? 'DataCurrencyIncome' : 'DataCurrencyExpense';

                fwrite($out, '   <Row>' . "\n");
                fwrite($out, '    <Cell><Data ss:Type="Number">' . $no++ . '</Data></Cell>' . "\n");
                fwrite($out, '    <Cell ss:StyleID="DataDate"><Data ss:Type="String">' . htmlspecialchars($r['date']) . '</Data></Cell>' . "\n");
                fwrite($out, '    <Cell ss:StyleID="' . $typeStyle . '"><Data ss:Type="String">' . htmlspecialchars($r['type']) . '</Data></Cell>' . "\n");
                fwrite($out, '    <Cell><Data ss:Type="String">' . htmlspecialchars($r['item']) . '</Data></Cell>' . "\n");
                fwrite($out, '    <Cell><Data ss:Type="String">' . htmlspecialchars($r['category']) . '</Data></Cell>' . "\n");
                fwrite($out, '    <Cell><Data ss:Type="String">' . htmlspecialchars($r['wallet']) . '</Data></Cell>' . "\n");
                fwrite($out, '    <Cell ss:StyleID="' . $amtStyle . '"><Data ss:Type="Number">' . $r['amount'] . '</Data></Cell>' . "\n");
                fwrite($out, '    <Cell><Data ss:Type="String">' . htmlspecialchars($r['note']) . '</Data></Cell>' . "\n");
                fwrite($out, '   </Row>' . "\n");
            }

            fwrite($out, '  </Table>' . "\n");
            fwrite($out, ' </Worksheet>' . "\n");
            fwrite($out, '</Workbook>' . "\n");

            fclose($out);
        };

        return Response::stream($callback, 200, $headers);
    }

    private function exportCsv(array $rows, string $from, string $to, int $totalIncome, int $totalExpense, int $netCashFlow)
    {
        $fileName = "Laporan_Finansial_{$from}_sd_{$to}.csv";
        $headers = [
            "Content-Type"        => "text/csv; charset=utf-8",
            "Content-Disposition" => "attachment; filename=\"{$fileName}\"",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $callback = function () use ($rows, $from, $to, $totalIncome, $totalExpense, $netCashFlow) {
            $file = fopen('php://output', 'w');
            // Write UTF-8 BOM so Excel opens CSV cleanly
            fputs($file, "\xEF\xBB\xBF");

            // Header summary
            fputcsv($file, ['LAPORAN KEUANGAN CATATDUIT']);
            fputcsv($file, ['Periode', "{$from} s/d {$to}"]);
            fputcsv($file, ['Total Pemasukan', $totalIncome]);
            fputcsv($file, ['Total Pengeluaran', $totalExpense]);
            fputcsv($file, ['Arus Kas Bersih', $netCashFlow]);
            fputcsv($file, []); // blank line

            // Table headers
            fputcsv($file, ['No', 'Tanggal', 'Tipe', 'Item / Keterangan', 'Kategori', 'Dompet', 'Nominal', 'Catatan']);

            $no = 1;
            foreach ($rows as $r) {
                fputcsv($file, [
                    $no++,
                    $r['date'],
                    $r['type'],
                    $r['item'],
                    $r['category'],
                    $r['wallet'],
                    $r['amount'],
                    $r['note']
                ]);
            }

            fclose($file);
        };

        return Response::stream($callback, 200, $headers);
    }
}
