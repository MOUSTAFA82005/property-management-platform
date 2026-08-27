const urlParams = new URLSearchParams(window.location.search);
const propertyId = urlParams.get('id');
const isEdit = Boolean(propertyId);

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
        if (field === 'name') id = 'propertyName';
        else if (field === 'property_type') id = 'propertyType';
        else if (field === 'city') id = 'propertyCity';
        else if (field === 'address') id = 'propertyAddress';
        else if (field === 'status') id = 'propertyStatus';
        else if (field === 'description') id = 'propertyDescription';

        const input = document.getElementById(id);
        if (input) {
            input.classList.add('is-invalid');
            const feedback = input.nextElementSibling;
            if (feedback) feedback.textContent = Array.isArray(msg) ? msg[0] : msg;
        }
    }
}

async function init() {
    if (isEdit) {
        document.title = 'Edit Property - PropManager';
        document.getElementById('breadcrumbActive').textContent = 'Edit Property';
        document.getElementById('formHeaderTitle').textContent = 'Edit Property';
        document.getElementById('formHeaderSubtitle').textContent = `Update property #${propertyId}`;
        document.getElementById('btnSubmitText').textContent = 'Update Property';

        try {
            const response = await getProperty(propertyId);
            const prop = response.data || response;

            document.getElementById('propertyName').value = prop.name || '';
            document.getElementById('propertyCity').value = prop.city || '';
            document.getElementById('propertyAddress').value = prop.address || '';
            document.getElementById('propertyStatus').value = prop.status || 'active';
            document.getElementById('propertyDescription').value = prop.description || '';

            const typeSelect = document.getElementById('propertyType');
            let found = false;
            for (let i = 0; i < typeSelect.options.length; i++) {
                if (typeSelect.options[i].value.toLowerCase() === (prop.property_type || '').toLowerCase()) {
                    typeSelect.selectedIndex = i;
                    found = true;
                    break;
                }
            }
            if (!found && prop.property_type) {
                typeSelect.add(new Option(prop.property_type, prop.property_type, true, true));
            }
        } catch (err) {
            showAlert(`Failed to load property: ${err.message}`, 'danger');
        }
    }
}

document.addEventListener('DOMContentLoaded', () => {
    init();

    document.getElementById('propertyForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        clearErrors();

        const name = document.getElementById('propertyName').value.trim();
        const property_type = document.getElementById('propertyType').value;
        const city = document.getElementById('propertyCity').value.trim();
        const address = document.getElementById('propertyAddress').value.trim();
        const status = document.getElementById('propertyStatus').value;
        const description = document.getElementById('propertyDescription').value.trim();

        let hasClientError = false;
        if (!name) { document.getElementById('propertyName').classList.add('is-invalid'); hasClientError = true; }
        if (!property_type) { document.getElementById('propertyType').classList.add('is-invalid'); hasClientError = true; }
        if (!city) { document.getElementById('propertyCity').classList.add('is-invalid'); hasClientError = true; }
        if (!address) { document.getElementById('propertyAddress').classList.add('is-invalid'); hasClientError = true; }

        if (hasClientError) return;

        const payload = {
            name,
            property_type,
            city,
            address,
            status,
            description: description || null,
        };

        const btn = document.getElementById('btnSubmitForm');
        const spinner = document.getElementById('submitSpinner');
        btn.disabled = true;
        spinner.classList.remove('d-none');

        try {
            if (isEdit) {
                await updateProperty(propertyId, payload);
                showAlert('Property updated successfully! Redirecting...', 'success');
                setTimeout(() => {
                    window.location.href = `property-detail.html?id=${propertyId}`;
                }, 600);
            } else {
                const result = await createProperty(payload);
                const newProp = result.data || result;
                const savedId = newProp?.id;
                showAlert('Property created successfully! Redirecting...', 'success');
                setTimeout(() => {
                    if (savedId) {
                        window.location.href = `property-detail.html?id=${savedId}`;
                    } else {
                        window.location.href = 'properties.html';
                    }
                }, 600);
            }
        } catch (err) {
            btn.disabled = false;
            spinner.classList.add('d-none');
            if (err.errors && Object.keys(err.errors).length > 0) {
                displayErrors(err.errors);
                showAlert('Please review the highlighted errors.', 'danger');
            } else {
                showAlert(err.message || 'An error occurred while saving the property.', 'danger');
            }
        }
    });
});
