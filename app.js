// UniVent - JavaScript Logic for Prototype Interaction

// Application State
const state = {
  role: 'panitia', // 'panitia' or 'admin'
  events: [
    {
      id: 101,
      name: 'Seminar AI & Machine Learning',
      panitia: 'Himpunan Informatika',
      quota: 200,
      date: '20 - 08 - 2026',
      status: 'Pending', // Pending, Approved, Rejected
      desc: 'Membahas pemanfaatan LLM dan teknologi AI terbaru dalam dunia industri.'
    },
    {
      id: 102,
      name: 'Workshop UI/UX Design',
      panitia: 'Himpunan Informatika',
      quota: 200,
      date: '10 - 07 - 2026',
      status: 'Approved', // Approved in Panitia shows as 'Selesai'
      desc: 'Fokus pada riset pengguna, wireframing, dan prototyping dengan Figma.'
    }
  ]
};

// DOM Elements
const roleSelector = document.getElementById('roleSelector');
const menuPanitia = document.getElementById('menu-panitia');
const menuAdmin = document.getElementById('menu-admin');
const viewSections = document.querySelectorAll('.view-section');
const menuLinks = document.querySelectorAll('.menu-link');

// Modal Elements
const detailsModal = document.getElementById('details-modal');
const modalTitle = document.getElementById('modal-title');
const modalPanitia = document.getElementById('modal-panitia');
const modalDate = document.getElementById('modal-date');
const modalQuota = document.getElementById('modal-quota');
const modalStatus = document.getElementById('modal-status');
const modalDesc = document.getElementById('modal-desc');

// Form & File Input
const createForm = document.getElementById('create-event-form');
const eventPosterInput = document.getElementById('eventPoster');
const fileNameDisplay = document.getElementById('file-name-display');
const btnQuickCreate = document.getElementById('btn-quick-create');

// Initialize Dashboard
document.addEventListener('DOMContentLoaded', () => {
  setupNavigation();
  setupRoleSwitcher();
  setupFormSubmission();
  setupFileInput();
  updateDashboardStats();
});

// View Routing & Navigation
function setupNavigation() {
  menuLinks.forEach(link => {
    link.addEventListener('click', (e) => {
      const targetView = link.getAttribute('data-target');
      if (targetView) {
        e.preventDefault();
        navigateToView(targetView);
      }
    });
  });

  // Quick Create Button in Panitia Dashboard
  if (btnQuickCreate) {
    btnQuickCreate.addEventListener('click', () => {
      navigateToView('buat-event-view');
    });
  }
}

function navigateToView(viewId) {
  // Hide all sections
  viewSections.forEach(section => {
    section.classList.remove('active');
  });

  // Show target section
  const targetSection = document.getElementById(viewId);
  if (targetSection) {
    targetSection.classList.add('active');
  }

  // Update active class on menu items
  menuLinks.forEach(link => {
    link.classList.remove('active');
    if (link.getAttribute('data-target') === viewId) {
      link.classList.add('active');
    }
  });
}

// Role Switcher Setup
function setupRoleSwitcher() {
  roleSelector.addEventListener('change', (e) => {
    const selectedRole = e.target.value;
    state.role = selectedRole;

    if (selectedRole === 'admin') {
      menuPanitia.style.display = 'none';
      menuAdmin.style.display = 'block';
      navigateToView('admin-dashboard-view');
      showToast('Masuk sebagai Admin', 'success');
    } else {
      menuAdmin.style.display = 'none';
      menuPanitia.style.display = 'block';
      navigateToView('panitia-dashboard-view');
      showToast('Masuk sebagai Panitia', 'success');
    }
    updateDashboardStats();
  });
}

// Update Stats counter in views
function updateDashboardStats() {
  // Admin counters
  const adminTotal = state.events.length;
  const adminPending = state.events.filter(e => e.status === 'Pending').length;
  
  const adminTotalEl = document.getElementById('admin-total-events');
  const adminPendingEl = document.getElementById('admin-pending-count');
  
  if (adminTotalEl) adminTotalEl.textContent = adminTotal;
  if (adminPendingEl) adminPendingEl.textContent = adminPending;

  // Panitia counters
  const panitiaEvents = state.events.filter(e => e.panitia === 'Himpunan Informatika');
  const panitiaTotal = panitiaEvents.length;
  const panitiaPending = panitiaEvents.filter(e => e.status === 'Pending').length;

  const panitiaTotalEl = document.getElementById('panitia-total-events');
  const panitiaPendingEl = document.getElementById('panitia-pending-count');

  if (panitiaTotalEl) panitiaTotalEl.textContent = panitiaTotal;
  if (panitiaPendingEl) panitiaPendingEl.textContent = panitiaPending;
}

// Modal View Details
window.viewEventDetails = function(name, panitia, quota, date, status, desc) {
  modalTitle.textContent = name;
  modalPanitia.textContent = panitia;
  modalDate.textContent = date;
  modalQuota.textContent = quota;
  modalDesc.textContent = desc;

  // Status mapping and styling
  modalStatus.textContent = status;
  modalStatus.className = ''; // Reset classes
  if (status === 'Pending') {
    modalStatus.classList.add('status-badge', 'pending');
  } else if (status === 'Approved' || status === 'Selesai') {
    modalStatus.classList.add('status-badge', 'approved');
    modalStatus.textContent = 'Disetujui';
  } else {
    modalStatus.classList.add('status-badge', 'rejected');
    modalStatus.textContent = 'Ditolak';
  }

  detailsModal.showModal();
};

// Admin Table Actions: Approve / Reject
window.handleAdminAction = function(eventId, action) {
  const event = state.events.find(e => e.id === eventId);
  if (!event) return;

  if (action === 'approve') {
    event.status = 'Approved';
    showToast(`Event "${event.name}" disetujui!`, 'success');
  } else if (action === 'reject') {
    event.status = 'Rejected';
    showToast(`Event "${event.name}" ditolak.`, 'info');
  }

  // Refresh tables content
  renderAdminTable();
  renderPanitiaTable();
  updateDashboardStats();
};

// Re-render Tables dynamically to reflect state changes
function renderAdminTable() {
  const tbody = document.querySelector('#admin-approval-table tbody');
  if (!tbody) return;
  tbody.innerHTML = '';

  const pendingEvents = state.events.filter(e => e.status === 'Pending');

  if (pendingEvents.length === 0) {
    tbody.innerHTML = `<tr><td colspan="6" style="text-align: center; color: var(--text-muted); padding: 2rem;">Tidak ada event yang menunggu persetujuan.</td></tr>`;
    return;
  }

  pendingEvents.forEach(event => {
    const row = document.createElement('tr');
    row.id = `event-row-${event.id}`;
    row.innerHTML = `
      <td class="event-name-cell">${event.name}</td>
      <td>${event.panitia}</td>
      <td>${event.quota}</td>
      <td>${event.date}</td>
      <td><span class="status-badge pending">Pending</span></td>
      <td class="actions-cell">
        <button class="btn-sm btn-reject" onclick="handleAdminAction(${event.id}, 'reject')">Tolak</button>
        <button class="btn-sm btn-approve" onclick="handleAdminAction(${event.id}, 'approve')">Setujui</button>
        <button class="btn-detail" onclick="viewEventDetails('${event.name}', '${event.panitia}', '${event.quota}', '${event.date}', '${event.status}', '${event.desc.replace(/'/g, "\\'")}')">Detail &rarr;</button>
      </td>
    `;
    tbody.appendChild(row);
  });
}

function renderPanitiaTable() {
  const tbody = document.querySelector('#panitia-event-table tbody');
  if (!tbody) return;
  tbody.innerHTML = '';

  const panitiaEvents = state.events.filter(e => e.panitia === 'Himpunan Informatika');

  panitiaEvents.forEach(event => {
    const row = document.createElement('tr');
    row.id = `panitia-row-${event.id}`;
    
    let statusClass = 'pending';
    let statusText = 'Pending';

    if (event.status === 'Approved') {
      statusClass = 'approved';
      statusText = 'Selesai';
    } else if (event.status === 'Rejected') {
      statusClass = 'rejected';
      statusText = 'Ditolak';
    }

    row.innerHTML = `
      <td class="event-name-cell">${event.name}</td>
      <td>${event.date}</td>
      <td>${event.quota}</td>
      <td><span class="status-badge ${statusClass}" id="panitia-status-${event.id}">${statusText}</span></td>
      <td>
        <button class="btn-detail" onclick="viewEventDetails('${event.name}', '${event.panitia}', '${event.quota}', '${event.date}', '${event.status}', '${event.desc.replace(/'/g, "\\'")}')">Detail &rarr;</button>
      </td>
    `;
    tbody.appendChild(row);
  });
}

// File Input Name Display
function setupFileInput() {
  if (eventPosterInput) {
    eventPosterInput.addEventListener('change', (e) => {
      if (e.target.files && e.target.files.length > 0) {
        fileNameDisplay.textContent = `File terpilih: ${e.target.files[0].name}`;
      } else {
        fileNameDisplay.textContent = '';
      }
    });
  }
}

// Form Submission handling
function setupFormSubmission() {
  if (createForm) {
    createForm.addEventListener('submit', (e) => {
      e.preventDefault();

      const name = document.getElementById('eventName').value;
      const desc = document.getElementById('eventDesc').value;
      const rawStartDate = document.getElementById('startDate').value; // YYYY-MM-DD
      const quota = parseInt(document.getElementById('eventQuota').value, 10);
      const price = document.getElementById('eventPrice').value;

      // Format date from YYYY-MM-DD to DD - MM - YYYY
      let formattedDate = 'TBD';
      if (rawStartDate) {
        const parts = rawStartDate.split('-');
        if (parts.length === 3) {
          formattedDate = `${parts[2]} - ${parts[1]} - ${parts[0]}`;
        }
      }

      // Add new event object to local state
      const newEvent = {
        id: Date.now(),
        name: name,
        panitia: 'Himpunan Informatika', // Mocked login panitia
        quota: quota,
        date: formattedDate,
        status: 'Pending',
        desc: desc
      };

      state.events.push(newEvent);

      // Reset form
      createForm.reset();
      if (fileNameDisplay) fileNameDisplay.textContent = '';

      // Re-render, update counters, navigate
      renderAdminTable();
      renderPanitiaTable();
      updateDashboardStats();
      navigateToView('panitia-dashboard-view');

      showToast('Event Baru Berhasil Diajukan!', 'success');
    });
  }
}

// Toast Alert Messages
window.showToast = function(message, type = 'info') {
  const toast = document.getElementById('toast');
  const toastMessage = document.getElementById('toast-message');
  
  if (!toast || !toastMessage) return;

  toastMessage.textContent = message;
  
  // Apply visual style
  toast.className = 'toast'; // Reset
  if (type === 'success') {
    toast.classList.add('toast-success');
  }

  // Display it
  toast.classList.add('show');

  // Auto-hide after 3 seconds
  setTimeout(() => {
    toast.classList.remove('show');
  }, 3000);
};

// Alert for unimplemented prototype features
window.showFeatureAlert = function(featureName) {
  showToast(`Fitur "${featureName}" adalah mockup untuk purwarupa ini.`, 'info');
};
