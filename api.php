<?php
session_start();

if (!isset($_SESSION['truck_measurements'])) {
    $_SESSION['truck_measurements'] = [];
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';

function normalizeDimension($val) {
    $val = trim(strtolower($val));
    if (strpos($val, 'cm') !== false) {
        $num = floatval(str_replace('cm', '', $val));
        return $num / 100.0;
    }
    if (strpos($val, 'm') !== false) {
        return floatval(str_replace('m', '', $val));
    }
    $num = floatval($val);
    return ($num > 5.0) ? $num / 100.0 : $num;
}

if ($action === 'fetch') {
    header('Content-Type: application/json');
    calculateAndReturn();
} 
elseif ($action === 'add') {
    header('Content-Type: application/json');
    $label = trim($_POST['label'] ?? 'Unnamed Job Item');
    $material = trim($_POST['material'] ?? 'Standard Vinyl');
    $pricing_mode = trim($_POST['pricing_mode'] ?? 'area');
    $qty = max(1, intval($_POST['qty'] ?? 1));
    $unit_price = floatval($_POST['unit_price'] ?? 4000);
    $raw_w = $_POST['width'] ?? '0';
    $raw_h = $_POST['height'] ?? '0';

    $width_m = normalizeDimension($raw_w);
    $height_m = normalizeDimension($raw_h);
    
    $area = round($qty * $width_m * $height_m, 4);

    // Calculate cost based on mode: linear meter uses width (or height if width is 1), area uses square meters
    if ($pricing_mode === 'linear') {
        $linear_meters = max($width_m, $height_m); // Uses the primary run dimension
        $total_cost = round($qty * $linear_meters * $unit_price, 2);
    } else {
        $total_cost = round($area * $unit_price, 2);
    }

    $_SESSION['truck_measurements'][] = [
        'id' => uniqid(),
        'label' => htmlspecialchars($label, ENT_QUOTES, 'UTF-8'),
        'material' => htmlspecialchars($material, ENT_QUOTES, 'UTF-8'),
        'pricing_mode' => $pricing_mode,
        'qty' => $qty,
        'unit_price' => $unit_price,
        'width_m' => $width_m,
        'height_m' => $height_m,
        'width_cm' => round($width_m * 100, 1),
        'height_cm' => round($height_m * 100, 1),
        'area' => $area,
        'total_cost' => $total_cost
    ];

    calculateAndReturn();
} 
elseif ($action === 'delete') {
    header('Content-Type: application/json');
    $id = $_POST['id'] ?? '';
    $_SESSION['truck_measurements'] = array_values(array_filter($_SESSION['truck_measurements'], function($item) use ($id) {
        return $item['id'] !== $id;
    }));
    calculateAndReturn();
} 
elseif ($action === 'clear') {
    header('Content-Type: application/json');
    $_SESSION['truck_measurements'] = [];
    calculateAndReturn();
} 
elseif ($action === 'generate_pdf') {
    $items = $_SESSION['truck_measurements'];
    $grand_total_cost = 0;

    foreach ($items as &$item) {
        if (!isset($item['pricing_mode'])) $item['pricing_mode'] = 'area';
        if (!isset($item['material'])) $item['material'] = 'Standard Vinyl';
        if (!isset($item['unit_price'])) $item['unit_price'] = 4000;
        if (!isset($item['total_cost'])) {
            $item['total_cost'] = ($item['pricing_mode'] === 'linear') 
                ? max($item['width_m'], $item['height_m']) * $item['qty'] * $item['unit_price']
                : $item['area'] * $item['unit_price'];
        }
        $grand_total_cost += $item['total_cost'];
    }
    unset($item);
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Print Shop Job Quotation Report</title>
        <style>
            body { font-family: Arial, sans-serif; color: #1e293b; padding: 2rem; max-width: 900px; margin: 0 auto; }
            h1 { text-align: center; font-size: 1.5rem; margin-bottom: 0.2rem; color: #2563eb; text-transform: uppercase; }
            .subtitle { text-align: center; font-size: 0.9rem; color: #64748b; margin-bottom: 2rem; }
            table { width: 100%; border-collapse: collapse; margin-bottom: 1.5rem; font-size: 0.9rem; }
            th, td { border: 1px solid #cbd5e1; padding: 0.75rem; text-align: left; }
            th { background: #f1f5f9; font-weight: 600; }
            .text-center { text-align: center; }
            .text-right { text-align: right; }
            .total-row { font-weight: bold; background: #f8fafc; }
            .print-btn { display: block; width: 100%; padding: 0.85rem; background: #2563eb; color: white; border: none; border-radius: 8px; font-size: 1rem; font-weight: bold; cursor: pointer; margin-top: 1rem; }
            @media print {
                .print-btn { display: none; }
                body { padding: 0; }
            }
        </style>
    </head>
    <body onload="window.print()">
        <h1>Print Shop Quotation & Measurement Report</h1>
        <div class="subtitle">Generated on: <?php echo date('Y-m-d H:i'); ?></div>

        <table>
            <thead>
                <tr>
                    <th>Job Section & Material</th>
                    <th class="text-center">Qty</th>
                    <th class="text-center">Dimensions</th>
                    <th class="text-right">Rate</th>
                    <th class="text-right">Total (RWF)</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($items)): ?>
                    <tr><td colspan="5" class="text-center" style="color: #64748b;">No items recorded.</td></tr>
                <?php else: foreach ($items as $item): ?>
                    <tr>
                        <td>
                            <strong><?php echo htmlspecialchars($item['label']); ?></strong><br>
                            <small style="color:#64748b;"><?php echo htmlspecialchars($item['material']); ?> (<?php echo $item['pricing_mode'] === 'linear' ? 'Linear Meter' : 'm²'; ?>)</small>
                        </td>
                        <td class="text-center"><?php echo $item['qty']; ?></td>
                        <td class="text-center"><?php echo $item['width_m']; ?>m × <?php echo $item['height_m']; ?>m</td>
                        <td class="text-right"><?php echo number_format($item['unit_price']); ?> RWF</td>
                        <td class="text-right"><strong><?php echo number_format($item['total_cost']); ?> RWF</strong></td>
                    </tr>
                <?php endforeach; endif; ?>
                <tr class="total-row">
                    <td colspan="4" class="text-right">GRAND TOTAL:</td>
                    <td class="text-right" style="color: #2563eb; font-size: 1.1rem;"><?php echo number_format($grand_total_cost); ?> RWF</td>
                </tr>
            </tbody>
        </table>

        <button class="print-btn" onclick="window.print()">Print / Save as PDF</button>
    </body>
    </html>
    <?php
    exit;
}

function calculateAndReturn() {
    $items = $_SESSION['truck_measurements'];
    $grand_total_cost = 0;

    foreach ($items as &$item) {
        if (!isset($item['pricing_mode'])) $item['pricing_mode'] = 'area';
        if (!isset($item['material'])) $item['material'] = 'Standard Vinyl';
        if (!isset($item['unit_price'])) $item['unit_price'] = 4000;
        if (!isset($item['total_cost'])) {
            $item['total_cost'] = ($item['pricing_mode'] === 'linear') 
                ? max($item['width_m'], $item['height_m']) * $item['qty'] * $item['unit_price']
                : $item['area'] * $item['unit_price'];
        }
        $grand_total_cost += $item['total_cost'];
    }
    unset($item);

    echo json_encode([
        'status' => 'success',
        'items' => $items,
        'grand_total_cost' => round($grand_total_cost, 2)
    ]);
    exit;
}
?>