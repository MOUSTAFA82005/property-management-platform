let allProperties = [];
let propertyToDelete = null;
let deleteModalInstance = null;

const imagePool = [
    'https://images.unsplash.com/photo-1545324418-cc1a3fa10c00?auto=format&fit=crop&w=800&q=80',
    'https://images.unsplash.com/photo-1512917774080-9991f1c4c750?auto=format&fit=crop&w=800&q=80',
    'https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?auto=format&fit=crop&w=800&q=80',
    'https://images.unsplash.com/photo-1577495508048-b635879837f1?auto=format&fit=crop&w=800&q=80',
    'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?auto=format&fit=crop&w=800&q=80',
    'https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?auto=format&fit=crop&w=800&q=80',
];

function getPropertyImage(id) {
    return imagePool[(id || 0) % imagePool.length];
}

function showAlert(message, type = 'success') {
    const container = document.getElementById('alertContainer');
    container.innerHTML = `
        <div class="alert alert-${type} alert-dismissible fade show rounded-3 shadow-sm" role="alert">
            <i class="bi ${type === 'success' ? 'bi-check-circle-fill' : 'bi-exclamation-triangle-fill'} me-2"></i>
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    setTimeout(() => {
        const el = container.querySelector('.alert');
        if (el) bootstrap.Alert.getOrCreateInstance(el).close();
    }, 5000);
}

async function fetchAndDisplayProperties() {
    const loading = document.getElementById('propertiesLoading');
    const empty = document.getElementById('propertiesEmpty');
    const grid = document.getElementById('propertiesGrid');

    loading.classList.remove('d-none');
    empty.classList.add('d-none');
    grid.classList.add('d-none');

    try {
        const response = await getProperties();
        allProperties = response.data || response || [];

        populateCitiesFilter(allProperties);
        updateStats(allProperties);
        applyFiltersAndRender();
    } catch (err) {
        loading.classList.add('d-none');
        showAlert(`Error loading properties: ${err.message}`, 'danger');
    }
}

function updateStats(list) {
    const totalProps = list.length;
    const totalUnits = list.reduce((sum, p) => sum + (p.units_count ?? (p.units?.length || 0)), 0);

    document.getElementById('listingsSubtitle').textContent = `${totalProps} properties available`;
    document.getElementById('heroStatProps').textContent = totalProps > 0 ? `${totalProps.toLocaleString()}+` : '0';
    document.getElementById('heroStatUnits').textContent = totalUnits > 0 ? `${totalUnits.toLocaleString()}+` : '0';
}

function populateCitiesFilter(list) {
    const citySelect = document.getElementById('liveCitySelect');
    const cities = [...new Set(list.map(p => p.city).filter(Boolean))];

    citySelect.innerHTML = '<option value="">All Cities</option>';
    cities.forEach(city => {
        const opt = new Option(city, city);
        citySelect.add(opt);
    });
}

function applyFiltersAndRender() {
    const query = (document.getElementById('liveSearchInput').value || document.getElementById('filterLocation').value || '').toLowerCase().trim();
    const typeFilter = document.getElementById('liveTypeSelect').value || document.getElementById('filterType').value;
    const cityFilter = document.getElementById('liveCitySelect').value;
    const statusFilter = document.getElementById('liveStatusSelect').value;
    const priceFilter = document.getElementById('filterPrice').value;
    const sortBy = document.getElementById('liveSortSelect').value;

    let filtered = allProperties.filter(p => {
        const matchesQuery = !query ||
            (p.name && p.name.toLowerCase().includes(query)) ||
            (p.city && p.city.toLowerCase().includes(query)) ||
            (p.address && p.address.toLowerCase().includes(query)) ||
            (p.property_type && p.property_type.toLowerCase().includes(query));

        const matchesType = !typeFilter || (p.property_type && p.property_type.toLowerCase().includes(typeFilter.toLowerCase()));
        const matchesCity = !cityFilter || p.city === cityFilter;
        const matchesStatus = !statusFilter || p.status === statusFilter;

        let matchesPrice = true;
        const rent = Number(p.from_price || 0);
        if (priceFilter === '0-1500') matchesPrice = rent <= 1500;
        else if (priceFilter === '1500-3000') matchesPrice = rent >= 1500 && rent <= 3000;
        else if (priceFilter === '3000-5000') matchesPrice = rent >= 3000 && rent <= 5000;
        else if (priceFilter === '5000+') matchesPrice = rent >= 5000;

        return matchesQuery && matchesType && matchesCity && matchesStatus && matchesPrice;
    });

    filtered.sort((a, b) => {
        if (sortBy === 'price_low') return (a.from_price || 0) - (b.from_price || 0);
        if (sortBy === 'price_high') return (b.from_price || 0) - (a.from_price || 0);
        if (sortBy === 'units') return (b.units_count || 0) - (a.units_count || 0);
        return (a.name || '').localeCompare(b.name || '');
    });

    renderGrid(filtered);
}

function renderGrid(list) {
    const loading = document.getElementById('propertiesLoading');
    const empty = document.getElementById('propertiesEmpty');
    const grid = document.getElementById('propertiesGrid');

    loading.classList.add('d-none');

    if (list.length === 0) {
        empty.classList.remove('d-none');
        grid.classList.add('d-none');
        return;
    }

    empty.classList.add('d-none');
    grid.classList.remove('d-none');

    grid.innerHTML = list.map(item => {
        const buildingsCount = item.buildings_count ?? 1;
        const unitsCount = item.units_count ?? (item.units?.length || 0);
        const availableCount = item.available_units_count ?? (item.units ? item.units.filter(u => u.status === 'available').length : 0);
        const fromPrice = item.from_price ? `$${Number(item.from_price).toLocaleString()}` : '$0';
        const statusLabel = item.status === 'active'
            ? (availableCount > 0 ? `${availableCount} Available` : 'Active')
            : 'Inactive';
        const statusClass = item.status === 'active' ? 'active' : 'inactive';
        const imgSrc = getPropertyImage(item.id);

        return `
            <div class="col-12 col-md-6 col-lg-4">
                <div class="pm-card">
                    <div class="pm-card-img-wrapper">
                        <img src="${imgSrc}" alt="${escapeHtml(item.name)}" loading="lazy">
                        <span class="pm-badge-type">${escapeHtml(item.property_type || 'Residential')}</span>
                        <span class="pm-badge-status ${statusClass}">${escapeHtml(statusLabel)}</span>
                    </div>
                    <div class="pm-card-body">
                        <h3 class="pm-card-title">${escapeHtml(item.name)}</h3>
                        <div class="pm-card-location">
                            <i class="bi bi-geo-alt"></i>
                            <span>${escapeHtml(item.city || 'Location')} ${item.address ? '• ' + escapeHtml(item.address) : ''}</span>
                        </div>
                        <div class="pm-card-stats-box">
                            <div>
                                <div class="pm-card-stat-num">${buildingsCount}</div>
                                <div class="pm-card-stat-label">Buildings</div>
                            </div>
                            <div>
                                <div class="pm-card-stat-num">${unitsCount}</div>
                                <div class="pm-card-stat-label">Units</div>
                            </div>
                            <div>
                                <div class="pm-card-stat-num available">${availableCount}</div>
                                <div class="pm-card-stat-label">Available</div>
                            </div>
                        </div>
                        <div class="pm-card-footer">
                            <div>
                                <span class="pm-price-label">From</span>
                                <span class="pm-price-value">${fromPrice}<small>/mo</small></span>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <div class="dropdown">
                                    <button class="btn btn-light btn-sm rounded-circle p-2" type="button" data-bs-toggle="dropdown" aria-expanded="false" title="Actions">
                                        <i class="bi bi-three-dots-vertical"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
                                        <li><a class="dropdown-item" href="property-form.html?id=${item.id}"><i class="bi bi-pencil me-2"></i>Edit</a></li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li><button class="dropdown-item text-danger" onclick="promptDelete(${item.id}, '${escapeJsString(item.name)}')"><i class="bi bi-trash me-2"></i>Delete</button></li>
                                    </ul>
                                </div>
                                <a href="property-detail.html?id=${item.id}" class="btn btn-pm-primary btn-sm px-3">
                                    View Details
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }).join('');
}

function promptDelete(id, name) {
    propertyToDelete = { id, name };
    const modalMsg = document.getElementById('deleteModalMessage');
    modalMsg.textContent = `Are you sure you want to delete property "${name}"? This action cannot be undone.`;
    if (!deleteModalInstance) {
        deleteModalInstance = new bootstrap.Modal(document.getElementById('deleteModal'));
    }
    deleteModalInstance.show();
}

function escapeHtml(str) {
    if (str === null || str === undefined) return '';
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

function escapeJsString(str) {
    if (str === null || str === undefined) return '';
    return String(str).replace(/'/g, "\\'").replace(/"/g, '\\"');
}

document.addEventListener('DOMContentLoaded', () => {
    deleteModalInstance = new bootstrap.Modal(document.getElementById('deleteModal'));
    fetchAndDisplayProperties();

    document.getElementById('confirmDeleteBtn').addEventListener('click', async () => {
        if (!propertyToDelete) return;
        const { id, name } = propertyToDelete;
        try {
            if (deleteModalInstance) deleteModalInstance.hide();
            await deleteProperty(id);
            showAlert(`Property "${name}" was deleted successfully.`, 'success');
            await fetchAndDisplayProperties();
        } catch (err) {
            showAlert(`Failed to delete property: ${err.message}`, 'danger');
        }
    });

    document.getElementById('btnSearchProperties').addEventListener('click', () => {
        applyFiltersAndRender();
        document.getElementById('properties-section').scrollIntoView({ behavior: 'smooth' });
    });
    document.getElementById('liveSearchInput').addEventListener('input', applyFiltersAndRender);
    document.getElementById('liveTypeSelect').addEventListener('change', applyFiltersAndRender);
    document.getElementById('liveCitySelect').addEventListener('change', applyFiltersAndRender);
    document.getElementById('liveStatusSelect').addEventListener('change', applyFiltersAndRender);
    document.getElementById('liveSortSelect').addEventListener('change', applyFiltersAndRender);
});
