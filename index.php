<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Print Shop Mobile Logger</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <h1>Print Shop Mobile Logger</h1>
    </header>

    <div class="container">
        <!-- Quick Tap Presets -->
        <div class="card">
            <div class="card-title">
                <span>Quick Presets</span>
                <button type="button" onclick="addNewPresetPrompt()" class="btn-secondary">+ Custom</button>
            </div>
            <div class="preset-grid" id="presetContainer"></div>
        </div>

        <!-- Fast Entry Form -->
        <div class="card">
            <form id="measurementForm">
                <div class="form-group">
                    <label for="label">Section / Job Name</label>
                    <input type="text" id="label" name="label" required placeholder="e.g. Side Face">
                </div>

                <div class="row">
                    <div class="form-group" style="flex: 1.5;">
                        <label for="material">Material</label>
                        <select id="material" name="material" required onchange="updateDefaultPricing()">
                            <option value="Frontlit Banner" data-price="4000" data-mode="linear">Banner (4k RWF/lm)</option>
                            <option value="Die-Cut Sticker" data-price="6000" data-mode="area">Sticker (6k RWF/m²)</option>
                            <option value="Backlit Banner / Lightbox" data-price="6000" data-mode="area">Lightbox (6k RWF/m²)</option>
                            <option value="Perforated Window Film (One-Way)" data-price="6000" data-mode="area">One-Way (6k RWF/m²)</option>
                            <option value="Reflective Film" data-price="10000" data-mode="area">Reflector (10k RWF/m²)</option>
                            <option value="Formboard / Foam Board" data-price="5000" data-mode="area">Formboard (5k RWF/m²)</option>
                            <option value="Cast Vinyl (Vehicle Wrap)" data-price="8000" data-mode="area">Vehicle Wrap (8k RWF/m²)</option>
                        </select>
                    </div>
                    <div class="form-group" style="flex: 1;">
                        <label for="unit_price" id="priceLabel">Rate (RWF)</label>
                        <input type="number" id="unit_price" name="unit_price" value="4000" required inputmode="numeric">
                    </div>
                </div>

                <input type="hidden" id="pricing_mode" name="pricing_mode" value="linear">

                <div class="row">
                    <div class="form-group" style="flex: 0.8;">
                        <label for="qty">Qty</label>
                        <input type="number" id="qty" name="qty" value="1" min="1" required inputmode="numeric">
                    </div>
                    <div class="form-group" style="flex: 1.1;">
                        <label for="width">Width</label>
                        <input type="text" id="width" name="width" required placeholder="e.g. 2m or 50cm" inputmode="decimal">
                    </div>
                    <div class="form-group" style="flex: 1.1;">
                        <label for="height">Height</label>
                        <input type="text" id="height" name="height" required placeholder="e.g. 1m or 20cm" inputmode="decimal">
                    </div>
                </div>
                <button type="submit">⚡ Add Item</button>
            </form>
        </div>

        <!-- Live Items Table -->
        <div class="card" style="padding: 0.2rem;">
            <table>
                <thead>
                    <tr>
                        <th>Item</th>
                        <th style="text-align:center;">Qty</th>
                        <th style="text-align:right;">Total</th>
                        <th style="text-align:center;">Del</th>
                    </tr>
                </thead>
                <tbody id="itemTableBody"></tbody>
            </table>
        </div>
    </div>

    <!-- Sticky Bottom Bar -->
    <div class="sticky-footer">
        <div>
            <span style="font-size:0.65rem; color:var(--text-muted);">TOTAL</span>
            <span class="total-val" id="grandTotalDisplay">0 RWF</span>
        </div>
        <div style="display: flex; gap: 0.4rem;">
            <button type="button" id="clearBtn" class="btn-danger" style="padding: 0.4rem 0.6rem; font-size: 0.8rem; min-height: auto;">Clear</button>
            <form action="api.php" method="GET" target="_blank" style="margin:0;">
                <input type="hidden" name="action" value="generate_pdf">
                <button type="submit" class="btn-success" style="padding: 0.4rem 0.6rem; font-size: 0.8rem; min-height: auto;">PDF Quote</button>
            </form>
        </div>
    </div>

    <script src="script.js"></script>
</body>
</html>