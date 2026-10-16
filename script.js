const defaultPresets = [
    { name: "Stickers", material: "Die-Cut Sticker", price: 6000, mode: "area" },
    { name: "Banners", material: "Frontlit Banner", price: 4000, mode: "linear" },
    { name: "Lightbox", material: "Backlit Banner / Lightbox", price: 6000, mode: "area" },
    { name: "Oneway", material: "Perforated Window Film (One-Way)", price: 6000, mode: "area" },
    { name: "Reflectors", material: "Reflective Film", price: 10000, mode: "area" },
    { name: "Formboard", material: "Formboard / Foam Board", price: 5000, mode: "area" },
    { name: "Side Face", material: "Cast Vinyl (Vehicle Wrap)", price: 8000, mode: "area" },
    { name: "Back Top", material: "Cast Vinyl (Vehicle Wrap)", price: 8000, mode: "area" },
    { name: "Doors Logo", material: "Die-Cut Sticker", price: 6000, mode: "area" }
];

document.addEventListener('DOMContentLoaded', () => {
    loadPresets();
    fetchItems();
    updateDefaultPricing();

    document.getElementById('measurementForm').addEventListener('submit', (e) => {
        e.preventDefault();
        const formData = new FormData(e.target);
        formData.append('action', 'add');

        fetch('api.php', { method: 'POST', body: formData })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    renderTable(data.items, data.grand_total_cost);
                    document.getElementById('label').value = '';
                    document.getElementById('width').value = '';
                    document.getElementById('height').value = '';
                    document.getElementById('qty').value = '1';
                    document.getElementById('label').focus();
                }
            });
    });

    document.getElementById('clearBtn').addEventListener('click', () => {
        if (confirm('Clear all items?')) {
            const formData = new FormData();
            formData.append('action', 'clear');
            fetch('api.php', { method: 'POST', body: formData })
                .then(res => res.json())
                .then(data => renderTable(data.items, data.grand_total_cost));
        }
    });
});

function updateDefaultPricing() {
    const select = document.getElementById('material');
    const selectedOption = select.options[select.selectedIndex];
    const price = selectedOption.getAttribute('data-price') || 4000;
    const mode = selectedOption.getAttribute('data-mode') || 'area';

    document.getElementById('unit_price').value = price;
    document.getElementById('pricing_mode').value = mode;
    document.getElementById('priceLabel').innerText = mode === 'linear' ? "Rate/lm" : "Rate/m²";
}

function getStoredPresets() {
    const custom = localStorage.getItem('print_shop_mobile_presets');
    if (custom) {
        try { return JSON.parse(custom); } catch(e) { return defaultPresets; }
    }
    return defaultPresets;
}

function loadPresets() {
    const container = document.getElementById('presetContainer');
    container.innerHTML = '';
    const presets = getStoredPresets();

    presets.forEach(p => {
        const btn = document.createElement('div');
        btn.className = 'preset-chip';
        btn.innerText = p.name;
        btn.onclick = () => selectPreset(p.name, p.material, p.price, p.mode);
        container.appendChild(btn);
    });
}

function addNewPresetPrompt() {
    const name = prompt("Enter preset name:");
    if (!name || !name.trim()) return;

    const material = document.getElementById('material').value;
    const price = document.getElementById('unit_price').value;
    const mode = document.getElementById('pricing_mode').value;

    let presets = getStoredPresets();
    presets.push({ name: name.trim(), material: material, price: price, mode: mode });
    localStorage.setItem('print_shop_mobile_presets', JSON.stringify(presets));
    loadPresets();
}

function selectPreset(label, material, price, mode) {
    document.getElementById('label').value = label;
    const materialSelect = document.getElementById('material');
    for (let i = 0; i < materialSelect.options.length; i++) {
        if (materialSelect.options[i].value === material) {
            materialSelect.selectedIndex = i;
            break;
        }
    }
    document.getElementById('unit_price').value = price || 4000;
    document.getElementById('pricing_mode').value = mode || 'area';
    updateDefaultPricing();
    document.getElementById('width').focus();
}

function fetchItems() {
    fetch('api.php?action=fetch')
        .then(res => res.json())
        .then(data => renderTable(data.items, data.grand_total_cost));
}

function deleteItem(id) {
    const formData = new FormData();
    formData.append('action', 'delete');
    formData.append('id', id);
    fetch('api.php', { method: 'POST', body: formData })
        .then(res => res.json())
        .then(data => renderTable(data.items, data.grand_total_cost));
}

function renderTable(items, grandTotalCost) {
    const tbody = document.getElementById('itemTableBody');
    tbody.innerHTML = '';

    if (items.length === 0) {
        tbody.innerHTML = `<tr><td colspan="4" style="text-align:center; color:var(--text-muted); padding: 1rem;">No items recorded.</td></tr>`;
    } else {
        items.forEach(item => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>
                    <strong style="color:var(--text);">${item.label}</strong><br>
                    <span style="font-size:0.7rem; color:var(--accent);">${item.material} (${item.width_m}×${item.height_m}m)</span>
                </td>
                <td style="text-align:center; font-weight:600;">${item.qty}</td>
                <td style="text-align:right; font-weight:600; color:var(--accent);">${Number(item.total_cost).toLocaleString()} RWF</td>
                <td style="text-align:center;"><button onclick="deleteItem('${item.id}')" class="btn-danger" style="padding:2px 6px; font-size:0.75rem; min-height:auto; width:auto;">✕</button></td>
            `;
            tbody.appendChild(tr);
        });
    }

    document.getElementById('grandTotalDisplay').innerText = `${Number(grandTotalCost).toLocaleString()} RWF`;
}