const urlParams = new URLSearchParams(window.location.search);
const unitId = urlParams.get('id');
let propertyId = urlParams.get('property_id');
const isEdit = Boolean(unitId);

function showAlert(message, type = 'success') {
    const container = document.getElementById('alertContainer');
    const inlineContainer = document.getElementById('inlineFormAlertContainer');

    const alertHtml = `
        <div class="alert alert-${type} alert-dismissible fade show rounded-3 shadow-sm" role="alert">
            <i class="bi ${type === 'success' ? 'bi-check-circle-fill' : 'bi-exclamation-triangle-fill'} me-2"></i>
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;

    container.innerHTML = alertHtml;
    if (inlineContainer) {
        inlineContainer.innerHTML = alertHtml;
        inlineContainer.classList.remove('d-none');
    }

    window.scrollTo({ top: 0, behavior: 'smooth' });
}

function clearErrors() {
    document.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
    const inlineContainer = document.getElementById('inlineFormAlertContainer');
    if (inlineContainer) inlineContainer.classList.add('d-none');
}

function displayErrors(errors) {
    clearErrors();
    for (const [field, msg] of Object.entries(errors)) {
        let id = '';
        if (field === 'property_id' || field === 'building_id') id = 'propertySelect';
        else if (field === 'unit_number') id = 'unitNumber';
        else if (field === 'unit_type') id = 'unitType';
        else if (field === 'floor') id = 'unitFloor';
        else if (field === 'monthly_rent') id = 'monthlyRent';
        else if (field === 'bedrooms') id = 'unitBedrooms';
        else if (field === 'bathrooms') id = 'unitBathrooms';
        else if (field === 'area') id = 'unitArea';
        else if (field === 'status') id = 'unitStatus';

        const input = document.getElementById(id);
        if (input) {
            input.classList.add('is-invalid');
            const feedback = input.nextElementSibling;
            if (feedback) feedback.textContent = Array.isArray(msg) ? msg[0] : msg;
        }
    }
}

async function loadPropertiesList(selectedId) {
    const select = document.getElementById('propertySelect');
    try {
        const response = await getProperties();
        const props = response.data || response || [];

        select.innerHTML = '<option value="" disabled>Select a property...</option>';
        props.forEach(p => {
            const isSel = selectedId && String(p.id) === String(selectedId);
            select.add(new Option(`${p.name} (${p.city || 'No City'})`, p.id, false, isSel));
        });

        if (!selectedId && props.length > 0) {
            select.selectedIndex = 1;
            propertyId = select.value;
        }
    } catch (err) {
        showAlert(`Error loading properties: ${err.message}`, 'danger');
    }
}

async function init() {
    if (propertyId) {
        document.getElementById('topBackBtn').href = `property-detail.html?id=${propertyId}`;
        document.getElementById('cancelFormBtn').href = `property-detail.html?id=${propertyId}`;
    }

    await loadPropertiesList(propertyId);

    if (isEdit) {
        document.title = 'Edit Unit - PropManager';
        document.getElementById('breadcrumbActive').textContent = 'Edit Unit';
        document.getElementById('formHeaderTitle').textContent = 'Edit Unit';
        document.getElementById('formHeaderSubtitle').textContent = `Update unit #${unitId}`;
        document.getElementById('btnSubmitUnitText').textContent = 'Update Unit';

        try {
            const response = await getUnit(unitId);
            const unit = response.data || response;

            if (unit.property_id) {
                propertyId = unit.property_id;
                document.getElementById('propertySelect').value = unit.property_id;
                document.getElementById('topBackBtn').href = `property-detail.html?id=${unit.property_id}`;
                document.getElementById('cancelFormBtn').href = `property-detail.html?id=${unit.property_id}`;
            }

            document.getElementById('unitNumber').value = unit.unit_number || '';
            document.getElementById('unitFloor').value = unit.floor ?? 0;
            document.getElementById('monthlyRent').value = unit.monthly_rent || '';
            document.getElementById('unitBedrooms').value = unit.bedrooms ?? 0;
            document.getElementById('unitBathrooms').value = unit.bathrooms ?? 0;
            document.getElementById('unitArea').value = unit.area || '';
            document.getElementById('unitStatus').value = unit.status || 'available';

            const typeSelect = document.getElementById('unitType');
            let found = false;
            for (let i = 0; i < typeSelect.options.length; i++) {
                if (typeSelect.options[i].value.toLowerCase() === (unit.unit_type || '').toLowerCase()) {
                    typeSelect.selectedIndex = i;
                    found = true;
                    break;
                }
            }
            if (!found && unit.unit_type) {
                typeSelect.add(new Option(unit.unit_type, unit.unit_type, true, true));
            }
        } catch (err) {
            showAlert(`Failed to load unit: ${err.message}`, 'danger');
        }
    }
}

document.addEventListener('DOMContentLoaded', () => {
    init();

    document.getElementById('unitForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        clearErrors();

        const selectedPropId = document.getElementById('propertySelect').value;
        const unit_number = document.getElementById('unitNumber').value.trim();
        const unit_type = document.getElementById('unitType').value;
        const floor = document.getElementById('unitFloor').value;
        const monthly_rent = document.getElementById('monthlyRent').value;
        const bedrooms = document.getElementById('unitBedrooms').value;
        const bathrooms = document.getElementById('unitBathrooms').value;
        const area = document.getElementById('unitArea').value;
        const status = document.getElementById('unitStatus').value;

        let hasClientError = false;
        if (!selectedPropId) { document.getElementById('propertySelect').classList.add('is-invalid'); hasClientError = true; }
        if (!unit_number) { document.getElementById('unitNumber').classList.add('is-invalid'); hasClientError = true; }
        if (!unit_type) { document.getElementById('unitType').classList.add('is-invalid'); hasClientError = true; }
        if (!monthly_rent || Number(monthly_rent) < 0) { document.getElementById('monthlyRent').classList.add('is-invalid'); hasClientError = true; }

        if (hasClientError) return;

        const payload = {
            property_id: parseInt(selectedPropId, 10),
            unit_number,
            unit_type,
            floor: floor !== '' ? parseInt(floor, 10) : 0,
            monthly_rent: parseFloat(monthly_rent),
            bedrooms: bedrooms !== '' ? parseInt(bedrooms, 10) : 0,
            bathrooms: bathrooms !== '' ? parseInt(bathrooms, 10) : 0,
            area: area !== '' ? parseFloat(area) : null,
            status,
        };

        const btn = document.getElementById('btnSubmitUnit');
        const spinner = document.getElementById('submitSpinner');
        btn.disabled = true;
        spinner.classList.remove('d-none');

        try {
            if (isEdit) {
                await updateUnit(unitId, payload);
                showAlert('Unit updated successfully! Redirecting...', 'success');
                setTimeout(() => {
                    window.location.href = `property-detail.html?id=${selectedPropId}`;
                }, 600);
            } else {
                await createUnit(payload);
                showAlert('Unit created successfully! Redirecting...', 'success');
                setTimeout(() => {
                    window.location.href = `property-detail.html?id=${selectedPropId}`;
                }, 600);
            }
        } catch (err) {
            btn.disabled = false;
            spinner.classList.add('d-none');
            if (err.errors && Object.keys(err.errors).length > 0) {
                displayErrors(err.errors);
                showAlert('Please review the highlighted errors.', 'danger');
            } else {
                showAlert(err.message || 'An error occurred while saving the unit.', 'danger');
            }
        }
    });
});
