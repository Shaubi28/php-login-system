<?php session_start(); require_once 'config.php'; require_once 'load_settings.php'; if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) { header("Location: index.php"); exit(); } $current_page = 'messages'; $username = $_SESSION['username']; ?> <!DOCTYPE html> <html lang="en"> <head> <meta charset="UTF-8"> <meta name="viewport" content="width=device-width, initial-scale=1.0"> <title>Messages - Modal CRUD</title> <!-- Bootstrap 5 CSS + Icons --> <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet"> <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"> <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"> <style> body { background: #e2ebee; display: flex; min-height: 100vh; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; overflow: hidden; } .main-content { flex: 1; padding: 32px 0 0 0; min-width: 0; overflow: auto; display: flex; flex-direction: column; align-items: center; } .crud-container { width: 100%; max-width: 900px; margin: 0 auto; padding: 0 1rem; } .card-custom { border: none; border-radius: 1.25rem; box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05), 0 8px 10px -6px rgba(0, 0, 0, 0.02); } .table-custom th { background: #f8fafc; font-weight: 600; border-bottom: 1px solid #e2e8f0; } .avatar { width: 36px; height: 36px; background: #eef2ff; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; color: #4f46e5; font-weight: 600; } .btn-sm-rounded { border-radius: 2rem; padding: 0.3rem 0.8rem; } .modal-content { border: none; border-radius: 1rem; } .modal-header { border-bottom: 1px solid #edf2f7; background: #ffffff; border-radius: 1rem 1rem 0 0; } .toast-notification { position: fixed; top: 20px; right: 20px; z-index: 9999; min-width: 260px; } @media (max-width: 900px) { .crud-container { max-width: 100%; } } </style> </head> <body> <?php include 'sidebar.php'; ?> <div class="main-content"> <div class="crud-container"> <!-- Header + Add Button --> <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3"> <div> <h1 class="display-6 fw-semibold"><i class="bi bi-people-fill text-primary me-2"></i>Contact Manager</h1> <p class="text-secondary mt-1">Full CRUD with Bootstrap modals (Add / Edit / Delete) + Database</p> </div> <button class="btn btn-primary btn-lg shadow-sm px-4" id="openAddModalBtn"> <i class="bi bi-plus-circle me-2"></i>New Contact </button> </div> <!-- Contacts Table --> <div class="card card-custom"> <div class="card-body p-0"> <div class="table-responsive"> <table class="table table-hover table-custom align-middle mb-0"> <thead> <tr> <th style="width: 35%">Name</th> <th style="width: 35%">Email</th> <th style="width: 20%">Phone</th> <th style="width: 10%" class="text-center">Actions</th> </tr> </thead> <tbody id="contactsTableBody"> <tr> <td colspan="4" class="text-center py-5 text-muted"> <i class="bi bi-inbox fs-1 d-block mb-2"></i>No contacts yet. Click "New Contact" to add. </td> </tr> </tbody> </table> </div> </div> <div class="card-footer bg-transparent d-flex justify-content-between align-items-center py-3 px-4 border-0"> <span class="text-muted small"><i class="bi bi-database"></i> Data saved in database</span> <button id="resetDemoBtn" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-repeat me-1"></i>Reset Demo</button> </div> </div> </div> <!-- ========= MODAL: ADD / EDIT CONTACT ========= --> <div class="modal fade" id="contactFormModal" tabindex="-1" aria-labelledby="contactFormModalLabel" aria-hidden="true"> <div class="modal-dialog modal-dialog-centered"> <div class="modal-content"> <div class="modal-header"> <h5 class="modal-title fw-bold" id="contactFormModalLabel"> <i class="bi bi-person-plus me-2 text-primary"></i><span id="modalTitle">Add Contact</span> </h5> <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button> </div> <div class="modal-body p-4"> <form id="contactForm" novalidate> <div class="mb-3"> <label for="contactName" class="form-label fw-semibold">Full Name *</label> <input type="text" class="form-control form-control-lg" id="contactName" placeholder="e.g., Maria Gonzalez" required> <div class="invalid-feedback">Name is required.</div> </div> <div class="mb-3"> <label for="contactEmail" class="form-label fw-semibold">Email Address *</label> <input type="email" class="form-control form-control-lg" id="contactEmail" placeholder="name@example.com" required> <div class="invalid-feedback">Valid email is required.</div> </div> <div class="mb-4"> <label for="contactPhone" class="form-label fw-semibold">Phone Number *</label> <input type="text" class="form-control form-control-lg" id="contactPhone" placeholder="+1 234 567 8900" required> <div class="invalid-feedback">Phone number is required.</div> </div> <div class="d-flex justify-content-end gap-2"> <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">Cancel</button> <button type="submit" class="btn btn-primary px-5" id="modalSubmitBtn"> <i class="bi bi-save me-1"></i> Save </button> </div> </form> </div> </div> </div> </div> <!-- ========= MODAL: DELETE CONFIRMATION ========= --> <div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-labelledby="deleteConfirmModalLabel" aria-hidden="true"> <div class="modal-dialog modal-dialog-centered"> <div class="modal-content"> <div class="modal-header bg-danger text-white border-0"> <h5 class="modal-title fw-bold" id="deleteConfirmModalLabel"> <i class="bi bi-exclamation-triangle-fill me-2"></i>Confirm Delete </h5> <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button> </div> <div class="modal-body p-4"> <p class="fs-5 mb-2">Are you sure you want to delete <strong id="deleteContactName"></strong>?</p> <p class="text-secondary mb-0">This action cannot be undone.</p> </div> <div class="modal-footer border-0 pt-0 pb-3"> <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button> <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Delete Permanently</button> </div> </div> </div> </div> <!-- Bootstrap JS Bundle (includes Popper) --> <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script> <script>
// ---------- DATA ----------
let contacts = [];
let currentEditId = null; // null = add mode, else editing id
let pendingDeleteId = null; // store id for deletion

// DOM elements
const tbody = document.getElementById('contactsTableBody');
const resetDemoBtn = document.getElementById('resetDemoBtn');
const openAddModalBtn = document.getElementById('openAddModalBtn');

// Modal form elements
const contactModal = new bootstrap.Modal(document.getElementById('contactFormModal'));
const deleteModal = new bootstrap.Modal(document.getElementById('deleteConfirmModal'));
const contactForm = document.getElementById('contactForm');
const modalTitleSpan = document.getElementById('modalTitle');
const modalSubmitBtn = document.getElementById('modalSubmitBtn');
const contactNameInput = document.getElementById('contactName');
const contactEmailInput = document.getElementById('contactEmail');
const contactPhoneInput = document.getElementById('contactPhone');
const deleteContactNameSpan = document.getElementById('deleteContactName');
const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');

// Helper: show floating notification
function showNotification(message, type = 'success') {
    const notifDiv = document.createElement('div');
    notifDiv.className = `alert alert-${type} alert-dismissible fade show shadow-sm`;
    notifDiv.style.position = 'fixed';
    notifDiv.style.top = '20px';
    notifDiv.style.right = '20px';
    notifDiv.style.zIndex = '9999';
    notifDiv.style.minWidth = '280px';
    notifDiv.style.borderLeft = `4px solid ${type === 'success' ? '#28a745' : type === 'danger' ? '#dc3545' : '#0d6efd'}`;
    notifDiv.innerHTML = `<i class="bi bi-${type === 'success' ? 'check-circle-fill' : type === 'danger' ? 'exclamation-triangle-fill' : 'info-circle-fill'} me-2"></i> ${message} <button type="button" class="btn-close btn-sm" data-bs-dismiss="alert"></button>`;
    document.body.appendChild(notifDiv);
    setTimeout(() => {
        notifDiv.classList.remove('show');
        setTimeout(() => notifDiv.remove(), 300);
    }, 3000);
}

// API functions
async function fetchContacts() {
    try {
        const response = await fetch('contacts_api.php');
        if (!response.ok) throw new Error('Failed to fetch contacts');
        contacts = await response.json();
        return true;
    } catch (error) {
        console.error('Error fetching contacts:', error);
        showNotification('Failed to load contacts from database', 'danger');
        return false;
    }
}

async function createContact(contactData) {
    try {
        const formData = new FormData();
        formData.append('name', contactData.name);
        formData.append('email', contactData.email);
        formData.append('phone', contactData.phone);

        const response = await fetch('contacts_api.php', {
            method: 'POST',
            body: formData
        });

        if (!response.ok) throw new Error('Failed to create contact');
        const result = await response.json();
        return result.status === 'success';
    } catch (error) {
        console.error('Error creating contact:', error);
        return false;
    }
}

async function updateContact(id, contactData) {
    try {
        const formData = new FormData();
        formData.append('id', id);
        formData.append('name', contactData.name);
        formData.append('email', contactData.email);
        formData.append('phone', contactData.phone);

        const response = await fetch('contacts_api.php', {
            method: 'PUT',
            body: new URLSearchParams(formData)
        });

        if (!response.ok) throw new Error('Failed to update contact');
        const result = await response.json();
        return result.status === 'updated';
    } catch (error) {
        console.error('Error updating contact:', error);
        return false;
    }
}

async function deleteContact(id) {
    try {
        const formData = new FormData();
        formData.append('id', id);

        const response = await fetch('contacts_api.php', {
            method: 'DELETE',
            body: new URLSearchParams(formData)
        });

        if (!response.ok) throw new Error('Failed to delete contact');
        const result = await response.json();
        return result.status === 'deleted';
    } catch (error) {
        console.error('Error deleting contact:', error);
        return false;
    }
}

// Render table (READ)
function renderTable() {
    if (!tbody) return;
    if (contacts.length === 0) {
        tbody.innerHTML = `<tr>
            <td colspan="4" class="text-center py-5 text-muted">
                <i class="bi bi-inbox fs-1 d-block mb-2"></i>No contacts found.<br>Click "New Contact" to add.
            </td>
        </tr>`;
        return;
    }
    let html = '';
    contacts.forEach(contact => {
        html += `<tr>
            <td class="fw-semibold">
                <div class="d-flex align-items-center gap-2">
                    <span class="avatar"><i class="bi bi-person fs-6"></i></span>
                    ${escapeHtml(contact.name)}
                </div>
            </td>
            <td><i class="bi bi-envelope me-2 text-secondary"></i>${escapeHtml(contact.email)}</td>
            <td><i class="bi bi-telephone me-2 text-secondary"></i>${escapeHtml(contact.phone)}</td>
            <td class="text-center">
                <button class="btn btn-sm btn-outline-primary btn-sm-rounded edit-btn" data-id="${contact.id}">
                    <i class="bi bi-pencil-square"></i> Edit
                </button>
                <button class="btn btn-sm btn-outline-danger btn-sm-rounded ms-1 delete-btn" data-id="${contact.id}">
                    <i class="bi bi-trash3"></i> Del
                </button>
            </td>
        </tr>`;
    });
    tbody.innerHTML = html;
}

function escapeHtml(str) {
    if (!str) return '';
    return str.replace(/[&<>]/g, function(m) {
        if (m === '&') return '&amp;';
        if (m === '<') return '&lt;';
        if (m === '>') return '&gt;';
        return m;
    });
}

// Validate modal form
function validateForm() {
    let isValid = true;
    const name = contactNameInput.value.trim();
    const email = contactEmailInput.value.trim();
    const phone = contactPhoneInput.value.trim();

    if (!name) {
        contactNameInput.classList.add('is-invalid');
        isValid = false;
    } else {
        contactNameInput.classList.remove('is-invalid');
    }

    if (!email) {
        contactEmailInput.classList.add('is-invalid');
        isValid = false;
    } else {
        const emailPattern = /^[^\s@]+@([^\s@.,]+\.)+[^\s@.,]{2,}$/;
        if (!emailPattern.test(email)) {
            contactEmailInput.classList.add('is-invalid');
            isValid = false;
        } else {
            contactEmailInput.classList.remove('is-invalid');
        }
    }

    if (!phone) {
        contactPhoneInput.classList.add('is-invalid');
        isValid = false;
    } else {
        contactPhoneInput.classList.remove('is-invalid');
    }

    return isValid;
}

// Reset modal form fields and validation
function resetModalForm() {
    contactNameInput.value = '';
    contactEmailInput.value = '';
    contactPhoneInput.value = '';
    contactNameInput.classList.remove('is-invalid');
    contactEmailInput.classList.remove('is-invalid');
    contactPhoneInput.classList.remove('is-invalid');
}

// Open Add Modal
function openAddModal() {
    currentEditId = null;
    resetModalForm();
    modalTitleSpan.innerText = 'Add Contact';
    modalSubmitBtn.innerHTML = '<i class="bi bi-save me-1"></i> Save';
    contactModal.show();
}

// Open Edit Modal with pre-filled data
function openEditModal(contactId) {
    const contact = contacts.find(c => c.id == contactId);
    if (!contact) {
        showNotification('Contact not found', 'danger');
        return;
    }
    currentEditId = contact.id;
    contactNameInput.value = contact.name;
    contactEmailInput.value = contact.email;
    contactPhoneInput.value = contact.phone;
    // clear validation styles
    contactNameInput.classList.remove('is-invalid');
    contactEmailInput.classList.remove('is-invalid');
    contactPhoneInput.classList.remove('is-invalid');
    modalTitleSpan.innerText = 'Edit Contact';
    modalSubmitBtn.innerHTML = '<i class="bi bi-pencil-square me-1"></i> Update';
    contactModal.show();
}

// Handle form submit (Add or Update)
async function handleFormSubmit(event) {
    event.preventDefault();
    if (!validateForm()) {
        showNotification('Please fill all fields correctly.', 'warning');
        return;
    }

    const name = contactNameInput.value.trim();
    const email = contactEmailInput.value.trim();
    const phone = contactPhoneInput.value.trim();

    let success = false;
    if (currentEditId === null) {
        // CREATE
        success = await createContact({ name, email, phone });
        if (success) {
            showNotification(`✅ "${name}" added successfully!`, 'success');
            contactModal.hide();
        } else {
            showNotification('Failed to add contact', 'danger');
            return;
        }
    } else {
        // UPDATE
        success = await updateContact(currentEditId, { name, email, phone });
        if (success) {
            showNotification(`✏️ "${name}" updated successfully.`, 'success');
            contactModal.hide();
        } else {
            showNotification('Failed to update contact', 'danger');
            return;
        }
    }

    resetModalForm();
    currentEditId = null;
    await fetchContacts(); // Refresh data
    renderTable();
}

// Open Delete Confirmation Modal
function openDeleteModal(contactId) {
    const contact = contacts.find(c => c.id == contactId);
    if (!contact) {
        showNotification('Contact not found', 'danger');
        return;
    }
    pendingDeleteId = contact.id;
    deleteContactNameSpan.innerText = contact.name;
    deleteModal.show();
}

// Perform Delete
async function performDelete() {
    if (pendingDeleteId === null) return;

    const success = await deleteContact(pendingDeleteId);
    if (success) {
        const contactToDelete = contacts.find(c => c.id == pendingDeleteId);
        showNotification(`🗑️ "${contactToDelete.name}" has been deleted.`, 'danger');
        deleteModal.hide();
        pendingDeleteId = null;
        await fetchContacts(); // Refresh data
        renderTable();
    } else {
        showNotification('Failed to delete contact', 'danger');
    }
}

// Event delegation for Edit / Delete buttons (table dynamic)
function attachTableEvents() {
    document.getElementById('contactsTableBody')?.addEventListener('click', (e) => {
        const editBtn = e.target.closest('.edit-btn');
        if (editBtn) {
            const id = parseInt(editBtn.getAttribute('data-id'));
            if (!isNaN(id)) {
                e.preventDefault();
                openEditModal(id);
            }
            return;
        }
        const deleteBtn = e.target.closest('.delete-btn');
        if (deleteBtn) {
            const id = parseInt(deleteBtn.getAttribute('data-id'));
            if (!isNaN(id)) {
                e.preventDefault();
                openDeleteModal(id);
            }
            return;
        }
    });
}

// Initialize app
async function init() {
    // Load data from database
    await fetchContacts();
    renderTable();
    attachTableEvents();

    // Add button listener
    if (openAddModalBtn) {
        openAddModalBtn.addEventListener('click', openAddModal);
    }

    // Form submit
    if (contactForm) {
        contactForm.addEventListener('submit', handleFormSubmit);
    }

    // Reset demo button - disabled for database version
    if (resetDemoBtn) {
        resetDemoBtn.style.display = 'none'; // Hide reset button since we use database
    }

    // Confirm delete button
    if (confirmDeleteBtn) {
        confirmDeleteBtn.addEventListener('click', performDelete);
    }

    // When modal is closed manually, reset form state
    document.getElementById('contactFormModal')?.addEventListener('hidden.bs.modal', () => {
        resetModalForm();
        currentEditId = null;
        modalTitleSpan.innerText = 'Add Contact';
        modalSubmitBtn.innerHTML = '<i class="bi bi-save me-1"></i> Save';
    });

    document.getElementById('deleteConfirmModal')?.addEventListener('hidden.bs.modal', () => {
        pendingDeleteId = null;
    });
}

// Start everything after DOM ready
document.addEventListener('DOMContentLoaded', init);
</script> </div> </body> </html>