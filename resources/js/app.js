const defaultMenus = [
    {
        code: 'A1',
        name: 'Nasi + Ayam Bakar + Sayur + Pisang',
        components: 'Nasi, ayam bakar, sayur, pisang',
        category: 'Sangat Baik',
        scores: { energy: 594, protein: 22.8, fat: 19.2, carbs: 77.6, fiber: 8.2 },
    },
    {
        code: 'A2',
        name: 'Bubur Ayam',
        components: 'Bubur, ayam suwir, sayur',
        category: 'Baik',
        scores: { energy: 430, protein: 22, fat: 12, carbs: 48, fiber: 6 },
    },
    {
        code: 'A3',
        name: 'Nasi + Telur Dadar + Sayur',
        components: 'Nasi, telur dadar, sayur hijau',
        category: 'Sangat Baik',
        scores: { energy: 470, protein: 19, fat: 14, carbs: 52, fiber: 4 },
    },
    {
        code: 'A4',
        name: 'Roti Isi Telur',
        components: 'Roti, telur, sayur',
        category: 'Baik',
        scores: { energy: 450, protein: 20, fat: 15, carbs: 50, fiber: 5 },
    },
    {
        code: 'A5',
        name: 'Bakso + Nasi',
        components: 'Nasi, bakso, kuah, sayur',
        category: 'Cukup',
        scores: { energy: 390, protein: 15, fat: 18, carbs: 43, fiber: 4 },
    },
];

const criteria = [
    { key: 'energy', label: 'Energi', weight: 0.4636, type: 'benefit' },
    { key: 'protein', label: 'Protein', weight: 0.2344, type: 'benefit' },
    { key: 'fat', label: 'Lemak', weight: 0.0804, type: 'cost' },
    { key: 'carbs', label: 'Karbohidrat', weight: 0.1735, type: 'benefit' },
    { key: 'fiber', label: 'Serat', weight: 0.0484, type: 'benefit' },
];

let menus = structuredClone(defaultMenus);
let activeComparison = 'A1';

const formatScore = (value) => Number(value).toFixed(4);

function calculateSawRows() {
    const maxValues = {};
    const minValues = {};

    criteria.forEach((criterion) => {
        const values = menus.map((menu) => Number(menu.scores[criterion.key]));
        maxValues[criterion.key] = Math.max(...values);
        minValues[criterion.key] = Math.min(...values);
    });

    return menus
        .map((menu) => {
            const normalized = {};
            const score = criteria.reduce((total, criterion) => {
                const raw = Number(menu.scores[criterion.key]);
                const value = criterion.type === 'cost'
                    ? minValues[criterion.key] / raw
                    : raw / maxValues[criterion.key];

                normalized[criterion.key] = value;
                return total + value * criterion.weight;
            }, 0);

            return { ...menu, normalized, preference: score };
        })
        .sort((a, b) => b.preference - a.preference);
}

function categoryFor(score) {
    if (score >= 0.86) return 'Sangat Baik';
    if (score >= 0.76) return 'Baik';
    return 'Cukup';
}

function renderMenuTable() {
    const table = document.querySelector('[data-menu-table]');
    if (!table) return;

    const query = document.querySelector('[data-search-menu]')?.value.toLowerCase() ?? '';
    table.innerHTML = menus
        .filter((menu) => `${menu.code} ${menu.name} ${menu.components}`.toLowerCase().includes(query))
        .map((menu) => `
            <tr>
                <td>${menu.code}</td>
                <td>${menu.name}</td>
                <td>${menu.components}</td>
                <td><span class="badge ${menu.category === 'Sangat Baik' ? 'good' : 'muted'}">${menu.category}</span></td>
                <td><button class="table-action" type="button" data-show-menu="${menu.code}">Detail</button></td>
            </tr>
        `)
        .join('');
}

function renderScoreTable() {
    const table = document.querySelector('[data-score-table]');
    if (!table) return;

    table.innerHTML = menus.map((menu) => `
        <tr>
            <td><strong>${menu.code}</strong><span>${menu.name}</span></td>
            ${criteria.map((criterion) => `
                <td>
                    <input
                        class="score-input"
                        type="number"
                        min="1"
                        step="0.1"
                        value="${menu.scores[criterion.key]}"
                        data-score-input="${menu.code}:${criterion.key}"
                    >
                </td>
            `).join('')}
        </tr>
    `).join('');
}

function renderComparison() {
    const selector = document.querySelector('[data-menu-selector]');
    const table = document.querySelector('[data-comparison-table]');
    const chart = document.querySelector('[data-criteria-chart]');
    if (!selector || !table || !chart) return;

    selector.innerHTML = menus.map((menu) => `
        <button type="button" class="menu-option ${menu.code === activeComparison ? 'active' : ''}" data-select-menu="${menu.code}">
            <strong>${menu.code}</strong>
            <span>${menu.name}</span>
        </button>
    `).join('');

    const bestValues = {};
    criteria.forEach((criterion) => {
        const values = menus.map((menu) => Number(menu.scores[criterion.key]));
        bestValues[criterion.key] = criterion.type === 'cost' ? Math.min(...values) : Math.max(...values);
    });

    table.innerHTML = menus.map((menu) => `
        <tr class="${menu.code === activeComparison ? 'selected-row' : ''}">
            <td>${menu.code} - ${menu.name}</td>
            ${criteria.map((criterion) => {
                const value = Number(menu.scores[criterion.key]);
                const isBest = value === bestValues[criterion.key];
                return `<td class="${isBest ? 'best-cell' : ''}">${value}</td>`;
            }).join('')}
        </tr>
    `).join('');

    const selected = menus.find((menu) => menu.code === activeComparison) ?? menus[0];
    chart.innerHTML = criteria.map((criterion) => {
        const max = Math.max(...menus.map((menu) => Number(menu.scores[criterion.key])));
        const value = Number(selected.scores[criterion.key]);
        const width = Math.max((value / max) * 100, 8);
        return `
            <div class="bar-row">
                <span>${criterion.label}</span>
                <div class="bar-track"><i style="width:${width}%"></i></div>
                <strong>${value}</strong>
            </div>
        `;
    }).join('');
}

function renderRanking() {
    const rows = calculateSawRows();
    const rankingTable = document.querySelector('[data-ranking-table]');
    const rankingChart = document.querySelector('[data-ranking-chart]');
    const best = rows[0];

    document.querySelectorAll('[data-best-menu], [data-result-title]').forEach((node) => {
        node.textContent = best.name;
    });
    document.querySelector('[data-best-score]').textContent = `Skor ${formatScore(best.preference)}`;
    document.querySelector('[data-result-detail]').textContent = `${best.name} unggul dengan skor ${formatScore(best.preference)}.`;
    document.querySelector('[data-report-summary]').textContent = `Menu terbaik adalah ${best.name} dengan nilai preferensi ${formatScore(best.preference)}.`;

    if (rankingTable) {
        rankingTable.innerHTML = rows.map((row, index) => `
            <tr class="${index === 0 ? 'rank-one' : ''}">
                <td>${index + 1}</td>
                <td>${row.code} - ${row.name}</td>
                <td>${formatScore(row.preference)}</td>
                <td><span class="badge ${categoryFor(row.preference) === 'Sangat Baik' ? 'good' : 'muted'}">${categoryFor(row.preference)}</span></td>
            </tr>
        `).join('');
    }

    if (rankingChart) {
        const topScore = best.preference;
        rankingChart.innerHTML = rows.map((row) => `
            <div class="bar-row">
                <span>${row.code}</span>
                <div class="bar-track"><i style="width:${Math.max((row.preference / topScore) * 100, 8)}%"></i></div>
                <strong>${formatScore(row.preference)}</strong>
            </div>
        `).join('');
    }
}

function syncInputsToData() {
    document.querySelectorAll('[data-score-input]').forEach((input) => {
        const [code, key] = input.dataset.scoreInput.split(':');
        const menu = menus.find((item) => item.code === code);
        if (menu) {
            menu.scores[key] = Number(input.value || 1);
        }
    });
}

function showSection(sectionName) {
    document.querySelectorAll('[data-section]').forEach((section) => {
        section.classList.toggle('active', section.dataset.section === sectionName);
    });

    document.querySelectorAll('[data-section-link]').forEach((link) => {
        link.classList.toggle('active', link.dataset.sectionLink === sectionName);
    });
}

function bindEvents() {
    document.querySelectorAll('[data-section-link]').forEach((link) => {
        link.addEventListener('click', (event) => {
            event.preventDefault();
            showSection(link.dataset.sectionLink);
            history.replaceState(null, '', `#${link.dataset.sectionLink}`);
        });
    });

    document.querySelector('[data-search-menu]')?.addEventListener('input', renderMenuTable);

    document.addEventListener('input', (event) => {
        if (event.target.matches('[data-score-input]')) {
            syncInputsToData();
            renderComparison();
            renderRanking();
        }
    });

    document.addEventListener('click', (event) => {
        const selectedMenu = event.target.closest('[data-select-menu]');
        if (selectedMenu) {
            activeComparison = selectedMenu.dataset.selectMenu;
            renderComparison();
        }

        const detailButton = event.target.closest('[data-show-menu]');
        if (detailButton) {
            activeComparison = detailButton.dataset.showMenu;
            showSection('perbandingan');
            renderComparison();
        }

        if (event.target.matches('[data-reset-scores]')) {
            menus = structuredClone(defaultMenus);
            renderAll();
        }

        if (event.target.matches('[data-save-scores]')) {
            event.target.textContent = 'Tersimpan';
            setTimeout(() => {
                event.target.textContent = 'Simpan';
            }, 1200);
        }

        if (event.target.matches('[data-print-report]')) {
            window.print();
        }
    });
}

function renderAll() {
    renderMenuTable();
    renderScoreTable();
    renderComparison();
    renderRanking();
}

bindEvents();
renderAll();
showSection(location.hash.replace('#', '') || 'dashboard');
