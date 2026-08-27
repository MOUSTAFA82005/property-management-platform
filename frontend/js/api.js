/**
 * PropManager API Service
 * Interacts with Laravel API endpoints at http://127.0.0.1:8000/api or http://localhost:8000/api
 */

const API_BASE_URL = (window.location.hostname === 'localhost')
    ? 'http://localhost:8000/api'
    : 'http://127.0.0.1:8000/api';

/**
 * Authentication Helpers
 */
function getAuthToken() {
    return localStorage.getItem('pm_auth_token') || null;
}

function getAuthUser() {
    const userStr = localStorage.getItem('pm_auth_user');
    try {
        return userStr ? JSON.parse(userStr) : null;
    } catch {
        return null;
    }
}

function setAuth(token, user = null) {
    if (token) localStorage.setItem('pm_auth_token', token);
    if (user) localStorage.setItem('pm_auth_user', JSON.stringify(user));
}

function clearAuth() {
    localStorage.removeItem('pm_auth_token');
    localStorage.removeItem('pm_auth_user');
}

function isAuthenticated() {
    return Boolean(getAuthToken() || getAuthUser());
}

/**
 * Standard HTTP request wrapper with JSON, timeout, and error handling
 */
async function request(endpoint, options = {}) {
    const url = `${API_BASE_URL}${endpoint}`;
    const controller = new AbortController();
    const timeoutId = setTimeout(() => controller.abort(), 12000);

    const token = getAuthToken();
    const defaultHeaders = {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        ...(token ? { 'Authorization': `Bearer ${token}` } : {}),
    };

    const config = {
        ...options,
        signal: controller.signal,
        headers: {
            ...defaultHeaders,
            ...options.headers,
        },
    };

    if (config.body && typeof config.body === 'object') {
        config.body = JSON.stringify(config.body);
    }

    try {
        const response = await fetch(url, config);
        clearTimeout(timeoutId);
        
        let data = null;
        const contentType = response.headers.get('content-type');
        if (contentType && contentType.includes('application/json')) {
            data = await response.json();
        }

        if (!response.ok) {
            if (response.status === 401) {
                clearAuth();
                const authErr = new Error('You must be logged in to perform this action.');
                authErr.isAuthError = true;
                authErr.status = 401;
                throw authErr;
            }

            const errorMessage = data?.message || `Request failed with status ${response.status}`;
            const error = new Error(errorMessage);
            error.status = response.status;
            error.errors = data?.errors || {};
            error.data = data;
            throw error;
        }

        return data;
    } catch (err) {
        clearTimeout(timeoutId);
        if (err.name === 'AbortError') {
            const timeoutErr = new Error(`Request timed out. Please ensure the backend server is running at ${API_BASE_URL}`);
            timeoutErr.isTimeout = true;
            throw timeoutErr;
        }
        if (err.message && err.message.includes('Failed to fetch')) {
            const fetchErr = new Error(`Unable to connect to the backend server at ${API_BASE_URL}. Please ensure 'php artisan serve' is running.`);
            fetchErr.isConnectionError = true;
            throw fetchErr;
        }
        console.error(`API Error on [${options.method || 'GET'}] ${url}:`, err);
        throw err;
    }
}

// Property API Methods
async function getProperties() {
    return request('/properties', { method: 'GET' });
}

async function getProperty(id) {
    return request(`/properties/${id}`, { method: 'GET' });
}

async function createProperty(data) {
    return request('/properties', {
        method: 'POST',
        body: data,
    });
}

async function updateProperty(id, data) {
    return request(`/properties/${id}`, {
        method: 'PUT',
        body: data,
    });
}

async function deleteProperty(id) {
    return request(`/properties/${id}`, {
        method: 'DELETE',
    });
}

// Unit API Methods
async function getUnits(propertyId = null) {
    if (propertyId) {
        return request(`/properties/${propertyId}/units`, { method: 'GET' });
    }
    return request('/units', { method: 'GET' });
}

async function getPropertyUnits(propertyId) {
    return request(`/properties/${propertyId}/units`, { method: 'GET' });
}

async function getUnit(id) {
    return request(`/units/${id}`, { method: 'GET' });
}

async function createUnit(data) {
    return request('/units', {
        method: 'POST',
        body: data,
    });
}

async function updateUnit(id, data) {
    return request(`/units/${id}`, {
        method: 'PUT',
        body: data,
    });
}

async function deleteUnit(id) {
    return request(`/units/${id}`, {
        method: 'DELETE',
    });
}

// Export to global window object
window.API = {
    BASE_URL: API_BASE_URL,
    getAuthToken,
    getAuthUser,
    setAuth,
    clearAuth,
    isAuthenticated,
    getProperties,
    getProperty,
    createProperty,
    updateProperty,
    deleteProperty,
    getUnits,
    getPropertyUnits,
    getUnit,
    createUnit,
    updateUnit,
    deleteUnit,
};

window.getAuthToken = getAuthToken;
window.getAuthUser = getAuthUser;
window.setAuth = setAuth;
window.clearAuth = clearAuth;
window.isAuthenticated = isAuthenticated;

window.getProperties = getProperties;
window.getProperty = getProperty;
window.createProperty = createProperty;
window.updateProperty = updateProperty;
window.deleteProperty = deleteProperty;
window.getUnits = getUnits;
window.getPropertyUnits = getPropertyUnits;
window.getUnit = getUnit;
window.createUnit = createUnit;
window.updateUnit = updateUnit;
window.deleteUnit = deleteUnit;
