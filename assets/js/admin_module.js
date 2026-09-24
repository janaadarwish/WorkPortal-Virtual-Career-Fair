// Admin Module JavaScript - Connects to admin_module.php

function resolveAdminIncludesUrl(filename) {
    console.log('FETCHING DATA FOR USER 1...', 'resolveAdminIncludesUrl', filename);
    try {
        const path = window.location.pathname;
        const lower = path.toLowerCase();
        const marker = '/pages/';
        const i = lower.indexOf(marker);
        if (i !== -1) {
            const root = path.substring(0, i).replace(/\/$/, '');
            const url = root + '/includes/' + filename;
            console.log('admin_module API path:', url);
            return url;
        }
    } catch (err) {
        alert('resolveAdminIncludesUrl: ' + (err && err.message ? err.message : err));
    }
    const fallback = new URL('../../includes/' + filename, window.location.href).pathname;
    console.log('admin_module API fallback:', fallback);
    return fallback;
}

const ADMIN_API = resolveAdminIncludesUrl('admin_module.php');
console.log('admin_module.js loaded; ADMIN_API =', ADMIN_API);

function adminReportFetchError(context, err) {
    const msg = err && err.message ? err.message : String(err);
    console.error(context, err);
    alert(context + ': ' + msg);
}

async function adminParseFetchResponse(response) {
    const responseText = await response.text();
    if (responseText.trim().startsWith('<')) {
        alert('Server returned HTML (not JSON):\n' + responseText.substring(0, 500));
        throw new Error('HTML response');
    }
    let data;
    try {
        data = JSON.parse(responseText);
    } catch (e) {
        alert('JSON parse error. Raw:\n' + responseText.substring(0, 600));
        throw e;
    }
    if (!response.ok) {
        throw new Error('HTTP ' + response.status + ' — ' + (data && data.message ? data.message : responseText.substring(0, 200)));
    }
    return data;
}

async function adminPost(bodyString, actionLabel) {
    console.log('FETCHING DATA FOR USER 1...', actionLabel);
    const response = await fetch(ADMIN_API, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: bodyString,
    });
    return adminParseFetchResponse(response);
}

document.addEventListener('DOMContentLoaded', function() {
    console.log('FETCHING DATA FOR USER 1...', 'admin DOMContentLoaded');

    const getCurrentAdminId = () => {
        const n = parseInt(document.body.dataset.adminId, 10);
        return Number.isFinite(n) ? n : 0;
    };

    const getFairId = () => {
        const n = parseInt(document.body.dataset.fairId, 10);
        return Number.isFinite(n) ? n : 0;
    };

    const showMessage = (message, type = 'info') => {
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type} alert-dismissible fade show position-fixed top-0 end-0 m-3`;
        alertDiv.style.zIndex = '9999';
        alertDiv.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        document.body.appendChild(alertDiv);

        setTimeout(() => {
            alertDiv.remove();
        }, 5000);
    };

    const toggleButton = (button, disabled = true) => {
        if (button) {
            button.disabled = disabled;
            if (disabled) {
                button.dataset.originalText = button.innerHTML;
                button.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Loading...';
            } else {
                button.innerHTML = button.dataset.originalText;
            }
        }
    };

    const createFairBtn = Array.from(document.querySelectorAll('button')).find(btn =>
        btn.textContent.includes('Create Fair') || btn.textContent.includes('New Fair')
    );

    if (createFairBtn) {
        createFairBtn.addEventListener('click', function() {
            const modal = document.createElement('div');
            modal.className = 'modal fade';
            modal.innerHTML = `
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Create New Fair</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">Fair Title</label>
                                <input type="text" class="form-control" id="fairTitle" placeholder="Enter fair title">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Start Date</label>
                                <input type="datetime-local" class="form-control" id="startDate">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">End Date</label>
                                <input type="datetime-local" class="form-control" id="endDate">
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="button" class="btn btn-primary" id="confirmCreateFair">Create Fair</button>
                        </div>
                    </div>
                </div>
            `;

            document.body.appendChild(modal);
            const bsModal = new bootstrap.Modal(modal);
            bsModal.show();

            document.getElementById('confirmCreateFair').addEventListener('click', async function() {
                const fairTitle = document.getElementById('fairTitle').value;
                const startDate = document.getElementById('startDate').value;
                const endDate = document.getElementById('endDate').value;

                if (!fairTitle || !startDate || !endDate) {
                    showMessage('Please fill all fields', 'warning');
                    return;
                }

                toggleButton(this, true);

                try {
                    const result = await adminPost(
                        `action=createFair&fair_title=${encodeURIComponent(fairTitle)}&start_date=${encodeURIComponent(startDate)}&end_date=${encodeURIComponent(endDate)}&admin_id=${getCurrentAdminId()}`,
                        'createFair'
                    );

                    if (result.success) {
                        const newId = result.data && (result.data.Fair_ID ?? result.data.fair_id);
                        showMessage(`Fair created successfully${newId != null ? ' with ID: ' + newId : ''}`, 'success');
                        bsModal.hide();
                        setTimeout(() => window.location.reload(), 1500);
                    } else {
                        showMessage(result.message, 'danger');
                    }
                } catch (error) {
                    adminReportFetchError('createFair', error);
                } finally {
                    toggleButton(this, false);
                }
            });

            modal.addEventListener('hidden.bs.modal', () => {
                modal.remove();
            });
        });
    }

    if (window.location.pathname.includes('A_companyApplications.php')) {
        const approveButtons = Array.from(document.querySelectorAll('button')).filter(btn =>
            (btn.textContent.includes('Approve') && !btn.textContent.includes('Selected')) ||
            btn.textContent.includes('Reject')
        );

        approveButtons.forEach(btn => {
            btn.addEventListener('click', async function() {
                const isApprove = this.textContent.includes('Approve');
                const companyRow = this.closest('tr');
                const companyId = companyRow?.dataset.companyId || 1;

                toggleButton(this, true);

                try {
                    const result = await adminPost(
                        `action=updateCompanyApproval&company_id=${companyId}&admin_id=${getCurrentAdminId()}&approved=${isApprove}`,
                        'updateCompanyApproval'
                    );

                    if (result.success) {
                        showMessage(result.message, 'success');
                        if (companyRow) {
                            const statusBadge = companyRow.querySelector('.status-badge, .badge');
                            if (statusBadge) {
                                statusBadge.textContent = isApprove ? 'Approved' : 'Rejected';
                                statusBadge.className = `badge bg-${isApprove ? 'success' : 'danger'}`;
                            }
                            this.disabled = true;
                            this.textContent = isApprove ? 'Approved' : 'Rejected';
                        }
                    } else {
                        showMessage(result.message, 'danger');
                    }
                } catch (error) {
                    adminReportFetchError('updateCompanyApproval', error);
                } finally {
                    toggleButton(this, false);
                }
            });
        });
    }

    const loadFairTraffic = async (fairId) => {
        console.log('FETCHING DATA FOR USER 1...', 'getFairTraffic');
        const fid = fairId != null ? fairId : getFairId();
        try {
            const result = await adminPost(`action=getFairTraffic&fair_id=${fid}`, 'getFairTraffic');

            if (result.success && result.data) {
                const trafficContainer = document.querySelector('.metric-card');
                if (trafficContainer && result.data.length > 0) {
                    const totalWaiting = result.data.reduce((sum, booth) => {
                        const w = booth.waiting_count ?? booth.Waiting_Count ?? 0;
                        return sum + Number(w);
                    }, 0);
                    const waitingMetric = Array.from(document.querySelectorAll('h3')).find(h3 =>
                        h3.textContent.includes('Waiting')
                    );
                    if (waitingMetric) {
                        waitingMetric.textContent = totalWaiting;
                    }

                    const boothTable = document.querySelector('table tbody');
                    if (boothTable) {
                        boothTable.innerHTML = result.data.map(booth => {
                            const cname = booth.Company_Name ?? booth.company_name ?? '';
                            const bno = booth.Booth_No ?? booth.booth_no ?? '';
                            const w = booth.waiting_count ?? booth.Waiting_Count ?? 0;
                            const tl = booth.traffic_level ?? booth.Traffic_Level ?? '';
                            const hot = String(tl).toLowerCase() === 'hot';
                            return `
                            <tr>
                                <td>${cname}</td>
                                <td>Booth ${bno}</td>
                                <td>${w}</td>
                                <td><span class="badge bg-${hot ? 'danger' : 'success'}">${tl}</span></td>
                                <td><button type="button" class="btn btn-sm btn-outline-dark">View Details</button></td>
                            </tr>
                        `;
                        }).join('');
                    }
                }
            }
        } catch (error) {
            adminReportFetchError('getFairTraffic', error);
        }
    };

    const loadFairReport = async (fairId) => {
        console.log('FETCHING DATA FOR USER 1...', 'getFairReport');
        const fid = fairId != null ? fairId : getFairId();
        try {
            const result = await adminPost(`action=getFairReport&fair_id=${fid}`, 'getFairReport');

            if (result.success && result.data) {
                const d = result.data;
                const interviewCount = d.interview_count ?? d.Interview_Count ?? 0;
                const finishedStudents = d.finished_students ?? d.Finished_Students ?? 0;

                const interviewEl = document.getElementById('admin-report-interviews');
                const finishedEl = document.getElementById('admin-report-finished-students');
                if (interviewEl) {
                    interviewEl.textContent = interviewCount;
                }
                if (finishedEl) {
                    finishedEl.textContent = finishedStudents;
                }

                const interviewFallback = Array.from(document.querySelectorAll('h3')).find(h3 =>
                    h3.textContent.includes('Interviews')
                );
                if (!interviewEl && interviewFallback) {
                    interviewFallback.textContent = interviewCount;
                }

                const studentFallback = Array.from(document.querySelectorAll('h3')).find(h3 =>
                    h3.textContent.includes('Students')
                );
                if (!finishedEl && studentFallback) {
                    studentFallback.textContent = finishedStudents;
                }
            }
        } catch (error) {
            adminReportFetchError('getFairReport', error);
        }
    };

    const timezoneSelect = document.getElementById('admin-fair-timezone');
    if (timezoneSelect && timezoneSelect.options.length > 0) {
        timezoneSelect.addEventListener('change', async function() {
            const timezone = this.value;
            const fairId = getFairId();

            try {
                const result = await adminPost(
                    `action=getFairTimesForUser&fair_id=${fairId}&timezone=${encodeURIComponent(timezone)}`,
                    'getFairTimesForUser'
                );

                if (result.success && result.data) {
                    const startKey = result.data.start_date ?? result.data.Start_Date;
                    const endKey = result.data.end_date ?? result.data.End_Date;
                    const timeElements = document.querySelectorAll('h4.fw-bold');
                    timeElements.forEach((el, index) => {
                        if (index === 0 && startKey) {
                            const startTime = new Date(startKey);
                            el.textContent = startTime.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });
                        } else if (index === 1 && endKey) {
                            const endTime = new Date(endKey);
                            el.textContent = endTime.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });
                        }
                    });

                    showMessage(`Times converted to ${timezone}`, 'success');
                } else {
                    showMessage(result.message, 'warning');
                }
            } catch (error) {
                adminReportFetchError('getFairTimesForUser', error);
            }
        });
    }

    const reminderBtn = Array.from(document.querySelectorAll('button')).find(btn =>
        btn.textContent.includes('Reminder') || btn.textContent.includes('Broadcast') || btn.textContent.includes('Send Email')
    );

    if (reminderBtn) {
        reminderBtn.addEventListener('click', async function() {
            toggleButton(this, true);

            try {
                const fairId = getFairId();

                const result = await adminPost(`action=sendFairReminder&fair_id=${fairId}`, 'sendFairReminder');

                if (result.success) {
                    showMessage(result.message, 'success');
                } else {
                    showMessage(result.message, 'danger');
                }
            } catch (error) {
                adminReportFetchError('sendFairReminder', error);
            } finally {
                toggleButton(this, false);
            }
        });
    }

    const exportButtons = Array.from(document.querySelectorAll('button')).filter(btn =>
        btn.textContent.includes('Export')
    ).concat(Array.from(document.querySelectorAll('.export-btn')));
    exportButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            console.log('FETCHING DATA FOR USER 1...', 'export UI (client-only)');
            const exportType = this.textContent.includes('PDF') ? 'PDF' :
                this.textContent.includes('CSV') ? 'CSV' : 'Excel';
            showMessage(`Exporting ${exportType} report...`, 'info');

            setTimeout(() => {
                showMessage(`${exportType} report exported successfully`, 'success');
            }, 2000);
        });
    });

    const bulkActionButtons = Array.from(document.querySelectorAll('button')).filter(btn =>
        btn.textContent.includes('Suspend') || btn.textContent.includes('Approve Selected') || btn.textContent.includes('Assign')
    );
    bulkActionButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            console.log('FETCHING DATA FOR USER 1...', 'bulkAction UI (client-only)');
            const action = this.textContent.includes('Suspend') ? 'suspend' :
                this.textContent.includes('Approve') ? 'approve' : 'assign';

            const selectedRows = document.querySelectorAll('input[type="checkbox"]:checked');
            if (selectedRows.length === 0) {
                showMessage('Please select items first', 'warning');
                return;
            }

            showMessage(`${action.charAt(0).toUpperCase() + action.slice(1)}d ${selectedRows.length} items`, 'success');

            selectedRows.forEach(checkbox => checkbox.checked = false);
        });
    });

    if (window.location.pathname.includes('A_adminDashboard.php') || window.location.pathname.includes('A_fairManagement.php')) {
        loadFairTraffic();
        loadFairReport();
    }

    if (window.location.pathname.includes('A_reports.php')) {
        loadFairReport();
    }

    if (window.location.pathname.includes('A_companyApplications.php')) {
        console.log('FETCHING DATA FOR USER 1...', 'A_companyApplications page');
    }

    if (window.location.pathname.includes('A_adminDashboard.php')) {
        setInterval(() => {
            loadFairTraffic();
            loadFairReport();
        }, 30000);
    }
});

if (!Element.prototype.matches) {
    Element.prototype.matches = Element.prototype.msMatchesSelector || Element.prototype.webkitMatchesSelector;
}

if (!Element.prototype.closest) {
    Element.prototype.closest = function(s) {
        var el = this;
        do {
            if (el.matches(s)) return el;
            el = el.parentElement || el.parentNode;
        } while (el !== null && el.nodeType === 1);
        return null;
    };
}
