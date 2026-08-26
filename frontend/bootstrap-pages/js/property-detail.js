const urlParams = new URLSearchParams(window.location.search);
const propertyId = urlParams.get('id');

let propertyData = null;
let unitsData = [];
let itemToDelete = null;
let deleteModal = null;

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

async function initPage() {
    if (!propertyId) {
        showAlert('No property ID provided.', 'danger');
        document.getElementById('propHeroTitle').textContent = 'Property Not Found';
        document.getElementById('unitsLoading').classList.add('d-none');
        return;
    }

    document.getElementById('editPropHeaderBtn').href = `property-form.html?id=${propertyId}`;
    document.getElementById('addUnitHeaderBtn').href = `unit-form.html?property_id=${propertyId}`;
    document.getElementById('addUnitBodyBtn').href = `unit-form.html?property_id=${propertyId}`;
    document.getElementById('emptyAddUnitBtn').href = `unit-form.html?property_id=${propertyId}`;

    await Promise.all([
        loadPropertyInfo(),
        loadUnits()
    ]);
}

async function loadPropertyInfo() {
    try {
        const response = await getProperty(propertyId);
        propertyData = response.data || response;

        document.title = `${propertyData.name} - PropManager`;
        document.getElementById('propHeroTitle').textContent = propertyData.name;
        document.getElementById('propHeroType').textContent = propertyData.property_type || 'Residential';
        document.getElementById('propHeroLocation').textContent = `${propertyData.city || ''} ${propertyData.address ? '• ' + propertyData.address : ''}`;

        const statusContainer = document.getElementById('propHeroStatus');
        if (propertyData.status === 'active') {
            statusContainer.innerHTML = '<span class="pm-badge-emerald">Active</span>';
        } else {
            statusContainer.innerHTML = '<span class="pm-badge-slate">Inactive</span>';
        }

        document.getElementById('statBuildingsCount').textContent = propertyData.buildings_count ?? 1;
        document.getElementById('infoCity').textContent = propertyData.city || '-';
        document.getElementById('infoAddress').textContent = propertyData.address || '-';
        document.getElementById('infoManager').textContent = propertyData.manager?.name || 'System Admin';
        document.getElementById('infoDescription').textContent = propertyData.description || 'No description provided for this property.';
    } catch (err) {
        showAlert(`Failed to load property: ${err.message}`, 'danger');
    }
}

async function loadUnits() {
    const loading = document.getElementById('unitsLoading');
    const empty = document.getElementById('unitsEmpty');
    const tbody = document.getElementById('unitsTableBody');

    loading.classList.remove('d-none');
    empty.classList.add('d-none');
    tbody.innerHTML = '';

    try {
        const response = await getPropertyUnits(propertyId);
        unitsData = response.data || response || [];

        loading.classList.add('d-none');

        const totalUnits = unitsData.length;
        const availableUnits = unitsData.filter(u => u.status === 'available').length;
        const minRent = totalUnits > 0 ? Math.min(...unitsData.map(u => Number(u.monthly_rent || 0))) : 0;

        document.getElementById('statTotalUnitsCount').textContent = totalUnits;
        document.getElementById('statAvailableUnitsCount').textContent = availableUnits;
        document.getElementById('statMinRent').textContent = minRent > 0 ? `$${minRent.toLocaleString()}/mo` : '$0';
        document.getElementById('unitsCountSubtitle').textContent = `${totalUnits} total unit(s) registered`;

        if (unitsData.length === 0) {
            empty.classList.remove('d-none');
            return;
        }

        tbody.innerHTML = unitsData.map(unit => {
            let badgeClass = 'badge-available';
            let statusText = 'Available';
            if (unit.status === 'occupied') {
                badgeClass = 'badge-occupied';
                statusText = 'Occupied';
            } else if (unit.status === 'reserved') {
                badgeClass = 'badge-reserved';
                statusText = 'Reserved';
            }

            const rentFormatted = unit.monthly_rent ? `$${Number(unit.monthly_rent).toLocaleString()}/mo` : '-';
            const areaFormatted = unit.area ? `${unit.area} sqm` : '-';

            return `
                <tr>
                    <td class="fw-bold text-dark">${escapeHtml(unit.unit_number)}</td>
                    <td><span class="badge bg-light text-dark border">${escapeHtml(unit.unit_type || 'Unit')}</span></td>
                    <td>${unit.floor !== null && unit.floor !== undefined ? `Floor ${unit.floor}` : '-'}</td>
                    <td>
                        <span class="text-muted">${unit.bedrooms || 0} Bed • ${unit.bathrooms || 0} Bath</span>
                    </td>
                    <td>${escapeHtml(areaFormatted)}</td>
                    <td class="fw-bold text-dark">${rentFormatted}</td>
                    <td><span class="${badgeClass}">${statusText}</span></td>
                    <td class="text-end">
                        <div class="btn-group" role="group">
                            <a href="unit-form.html?id=${unit.id}&property_id=${propertyId}" class="btn btn-outline-secondary btn-sm" title="Edit Unit">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <button type="button" class="btn btn-outline-danger btn-sm" onclick="promptDeleteUnit(${unit.id}, '${escapeJsString(unit.unit_number)}')" title="Delete Unit">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            `;
        }).join('');
    } catch (err) {
        loading.classList.add('d-none');
        showAlert(`Failed to load units: ${err.message}`, 'danger');
    }
}

function promptDeleteUnit(id, unitNum) {
    itemToDelete = { type: 'unit', id, name: unitNum };
    document.getElementById('deleteModalHeading').textContent = 'Delete Unit?';
    document.getElementById('deleteModalText').textContent = `Are you sure you want to delete unit "${unitNum}"?`;
    deleteModal.show();
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
    deleteModal = new bootstrap.Modal(document.getElementById('deleteConfirmModal'));
    initPage();

    document.getElementById('deletePropHeaderBtn').addEventListener('click', () => {
        itemToDelete = { type: 'property', id: propertyId, name: propertyData?.name || 'this property' };
        document.getElementById('deleteModalHeading').textContent = 'Delete Property?';
        document.getElementById('deleteModalText').textContent = `Are you sure you want to delete "${itemToDelete.name}" and all its units? This action cannot be undone.`;
        deleteModal.show();
    });

    document.getElementById('btnExecuteDelete').addEventListener('click', async () => {
        if (!itemToDelete) return;
        deleteModal.hide();

        try {
            if (itemToDelete.type === 'property') {
                await deleteProperty(itemToDelete.id);
                showAlert('Property deleted successfully. Redirecting to properties list...', 'success');
                setTimeout(() => {
                    window.location.href = 'properties.html';
                }, 1000);
            } else if (itemToDelete.type === 'unit') {
                await deleteUnit(itemToDelete.id);
                showAlert(`Unit "${itemToDelete.name}" was deleted successfully.`, 'success');
                await loadUnits();
            }
        } catch (err) {
            showAlert(`Delete failed: ${err.message}`, 'danger');
        }
    });
});
