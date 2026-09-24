// Student Module JavaScript - Connects to student_module.php

const STUDENT_API = 'http://localhost/www/includes/student_module.php';
console.log('student_module.js loaded; STUDENT_API =', STUDENT_API);

function reportFetchError(context, err) {
    const msg = err && err.message ? err.message : String(err);
    console.error(context, err);
    alert(context + ': ' + msg);
}

function normalizeStudentProfile(data) {
    if (!data || typeof data !== 'object') {
        return {};
    }
    return {
        F_Name: data.F_Name ?? data.f_name ?? '',
        L_Name: data.L_Name ?? data.L_Nmae ?? data.l_name ?? '',
        Major: data.Major ?? data.major ?? '',
        GPA: data.GPA ?? data.gpa ?? '',
        Bio: data.Bio ?? data.bio ?? '',
        Email: data.Email ?? data.email ?? '',
        Resume_URL: data.Resume_URL ?? data.resume_url ?? '',
    };
}

document.addEventListener('DOMContentLoaded', function() {
    console.log('FETCHING DATA FOR USER 1...', 'DOMContentLoaded student init');
    const currentPage = (window.location.pathname.split('/').pop() || '').toLowerCase();
    const user = loadStoredUser();
    if (user && user.role === 'student' && user.id != null && String(user.id).trim() !== '') {
        document.body.dataset.studentId = String(user.id);
    }

    if (user) {
        applyStudentUserContext(user);
    }

    attachResumeUploadHandler();
    attachVisibilityHandler();
    attachStatusButtons();
    attachQueueClickHandler();
    attachEditProfileHandler();
    attachLeaveQueueHandler();
    attachProfileActionsHandler();
    attachInterviewActionsHandler();
    attachInvitationActionsHandler();
    attachOptimizeProfileHandler();
    attachPrivacySettingsHandler();

    if (currentPage === 's_studentdashboard.php') {
        loadRecommendedBooths();
        loadStudentProfileHeader();
    }

    if (currentPage === 's_profile.php') {
        loadStudentResume();
        loadStudentProfileHeader();
    }

    if (currentPage === 's_chat.php') {
        loadStudentProfileHeader();
    }
});

function loadStoredUser() {
    try {
        return JSON.parse(sessionStorage.getItem('workportal_user') || 'null');
    } catch (_) {
        return null;
    }
}

function applyStudentUserContext(user) {
    const displayName = user.name || 'Student';
    const selectors = ['.user-name', '#student-name', '[data-student-name]', '#dashboard-user-name'];
    selectors.forEach((selector) => {
        const el = document.querySelector(selector);
        if (el) {
            el.innerText = displayName;
        }
    });
}

function getCurrentStudentId() {
    const raw = document.body.dataset.studentId;
    const n = parseInt(raw, 10);
    return Number.isFinite(n) ? n : null;
}

async function parseFetchResponse(response) {
    const responseText = await response.text();
    if (responseText.trim().startsWith('<')) {
        alert('Server returned HTML (not JSON). First 500 chars:\n' + responseText.substring(0, 500));
        throw new Error('Server returned HTML instead of JSON');
    }
    let data;
    try {
        data = JSON.parse(responseText);
    } catch (parseError) {
        alert('JSON parse error. Raw response:\n' + responseText.substring(0, 600));
        throw parseError;
    }
    if (!response.ok) {
        throw new Error('HTTP ' + response.status + ' — ' + (data && data.message ? data.message : responseText.substring(0, 200)));
    }
    return data;
}

async function studentPost(bodyString, actionLabel) {
    console.log('FETCHING DATA FOR USER 1...', actionLabel);
    const response = await fetch(STUDENT_API, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: bodyString,
    });
    return parseFetchResponse(response);
}

async function loadStudentProfileHeader() {
    console.log('FETCHING DATA FOR USER SESSION...', 'loadStudentProfileHeader');
    try {
        const result = await studentPost(
            `action=getStudentProfile`, 
            'getStudentProfile'
        );
        if (result.success && result.data) {
            const d = normalizeStudentProfile(result.data);
            const displayName = [d.F_Name, d.L_Name].filter(Boolean).join(' ').trim() || 'Student';
            const nameEl = document.getElementById('dashboard-user-name');
            const majorEl = document.getElementById('dashboard-user-major');
            if (nameEl) {
                nameEl.textContent = displayName;
            }
            const uiNameSelectors = ['.user-name', '#student-name'];
            uiNameSelectors.forEach((selector) => {
                const el = document.querySelector(selector);
                if (el) {
                    el.textContent = displayName;
                }
            });
            if (majorEl && d.Major) {
                majorEl.textContent = d.Major;
            }
            const h2 = document.querySelector('h2.fw-bold');
            if (h2 && displayName !== 'Student') {
                h2.textContent = displayName;
            }
            const profileMajor = document.querySelector('.profile-header p.text-muted.mb-2');
            if (profileMajor && d.Major) {
                profileMajor.textContent = d.Major + ' Student • Class of 2026';
            }
        }
    } catch (error) {
        reportFetchError('loadStudentProfileHeader', error);
    }
}

function showMessage(message, type = 'info') {
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
}

function toggleButton(button, disabled = true) {
    if (!button) {
        return;
    }
    button.disabled = disabled;
    if (disabled) {
        button.dataset.originalText = button.innerHTML;
        button.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Loading...';
    } else {
        button.innerHTML = button.dataset.originalText || button.innerHTML;
    }
}

function attachResumeUploadHandler() {
    const updateResumeBtn = document.getElementById('btn-update-resume')
        || Array.from(document.querySelectorAll('button')).find(btn => btn.textContent.includes('Update Resume'));
    const replaceResumeBtn = document.getElementById('btn-replace-resume')
        || Array.from(document.querySelectorAll('button')).find(btn => btn.textContent.includes('Replace File'));

    [updateResumeBtn, replaceResumeBtn].forEach(btn => {
        if (!btn) {
            return;
        }
        btn.addEventListener('click', function() {
            const input = document.createElement('input');
            input.type = 'file';
            input.accept = '.pdf';
            input.onchange = async function(e) {
                console.log('FETCHING DATA FOR USER 1...', 'uploadPortfolio');
                const file = e.target.files[0];
                if (!file) {
                    return;
                }
                toggleButton(btn, true);

                const formData = new FormData();
                formData.append('action', 'uploadPortfolio');
                formData.append('student_id', getCurrentStudentId());
                formData.append('resume_file', file);

                try {
                    const response = await fetch(STUDENT_API, {
                        method: 'POST',
                        body: formData,
                    });
                    const text = await response.text();
                    if (text.trim().startsWith('<')) {
                        alert('uploadPortfolio: server returned HTML:\n' + text.substring(0, 500));
                        throw new Error('HTML response from uploadPortfolio');
                    }
                    let result;
                    try {
                        result = JSON.parse(text);
                    } catch (pe) {
                        alert('uploadPortfolio: invalid JSON:\n' + text.substring(0, 500));
                        throw pe;
                    }
                    if (!response.ok) {
                        throw new Error('HTTP ' + response.status + ' — ' + (result.message || text.substring(0, 200)));
                    }
                    if (result.success) {
                        showMessage(result.message, 'success');
                        const resumeName = Array.from(document.querySelectorAll('h6')).find(h6 => h6.textContent.includes('Resume'));
                        if (resumeName) {
                            resumeName.textContent = file.name;
                        }
                    } else {
                        showMessage(result.message, 'danger');
                    }
                } catch (error) {
                    reportFetchError('uploadPortfolio', error);
                } finally {
                    toggleButton(btn, false);
                }
            };
            input.click();
        });
    });
}

function attachVisibilityHandler() {
    if (document.getElementById('profileVisibility')) {
        return;
    }
    const visibilitySelect = document.querySelector('select[name="profileVisibility"]') || document.querySelector('select');
    if (!visibilitySelect || visibilitySelect.options.length === 0) {
        return;
    }
    visibilitySelect.addEventListener('change', async function() {
        const fullAccess = this.value === 'Public (Discovery Mode)';
        try {
            const result = await studentPost(
                `action=getStudentProfileVisibility&student_id=${getCurrentStudentId()}&full_access=${fullAccess}`,
                'getStudentProfileVisibility'
            );
            if (result.success) {
                showMessage('Profile visibility updated', 'success');
            } else {
                showMessage('Failed to update visibility', 'warning');
            }
        } catch (error) {
            reportFetchError('getStudentProfileVisibility', error);
        }
    });
}

function attachStatusButtons() {
    const statusButtons = Array.from(document.querySelectorAll('button')).filter(btn =>
        btn.textContent.includes('Available') || btn.textContent.includes('In-Chat') || btn.textContent.includes('Offline')
    );
    statusButtons.forEach(btn => {
        if (!btn) {
            return;
        }
        btn.addEventListener('click', async function() {
            const status = this.textContent.trim();
            toggleButton(this, true);
            try {
                const result = await studentPost(
                    `action=updateReadyStatus&student_id=${getCurrentStudentId()}&status=${encodeURIComponent(status)}`,
                    'updateReadyStatus'
                );
                if (result.success) {
                    showMessage(result.message, 'success');
                    const statusBadge = Array.from(document.querySelectorAll('.badge')).find(badge => /Waiting|Available|In-Chat|Offline/.test(badge.textContent));
                    if (statusBadge) {
                        statusBadge.textContent = status;
                        statusBadge.className = `badge bg-${status === 'Available' ? 'success' : status === 'In-Chat' ? 'warning' : 'secondary'}`;
                    }
                } else {
                    showMessage(result.message, 'danger');
                }
            } catch (error) {
                reportFetchError('updateReadyStatus', error);
            } finally {
                toggleButton(this, false);
            }
        });
    });
}

function attachQueueClickHandler() {
    document.addEventListener('click', async function(e) {
        if (!e.target.classList.contains('join-queue-btn')) {
            return;
        }

        const company = e.target.dataset.company || 'Company';
        const booth = e.target.dataset.booth || 'TBD';
        const button = e.target;

        toggleButton(button, true);

        try {
            const result = await studentPost(
                `action=joinQueue&student_id=${getCurrentStudentId()}&company_name=${encodeURIComponent(company)}&booth_no=${encodeURIComponent(booth)}`,
                'joinQueue'
            );

            if (result.success) {
                showMessage(result.message, 'success');
                button.textContent = 'In Queue';
                button.classList.remove('btn-outline-success');
                button.classList.add('btn-success');
                button.disabled = true;
            } else {
                showMessage(result.message, 'danger');
            }
        } catch (error) {
            reportFetchError('joinQueue', error);
        } finally {
            toggleButton(button, false);
        }
    });
}

async function loadRecommendedBooths() {
    console.log('FETCHING DATA FOR USER 1...', 'loadRecommendedBooths');
    try {
        const result = await studentPost(
            `action=getRecommendedBooths&student_id=${getCurrentStudentId()}`,
            'getRecommendedBooths'
        );
        if (result.success && Array.isArray(result.data) && result.data.length > 0) {
            const matchContainer = document.querySelector('.row.g-3.mb-4');
            if (!matchContainer) {
                return;
            }
            matchContainer.innerHTML = result.data.slice(0, 4).map((booth, index) => `
                <div class="col-md-6">
                    <div class="card p-3 h-100">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="match-score">${85 + index * 5}% Match</span>
                            <i class="bi bi-bookmark text-muted"></i>
                        </div>
                        <h6 class="fw-bold mb-1">${booth.Company_Name || 'Company'}</h6>
                        <p class="text-muted small mb-3">${booth.Job_Title || 'Role'} • ${booth.Department || 'Department'}</p>
                        <div class="d-flex justify-content-between align-items-center">
                            <small class="text-muted"><i class="bi bi-geo-alt me-1"></i> Booth ${booth.Booth_No || 'TBD'}</small>
                            <button class="btn btn-sm btn-outline-success rounded-pill px-3 join-queue-btn" data-company="${booth.Company_Name || ''}" data-booth="${booth.Booth_No || ''}">Join Queue</button>
                        </div>
                    </div>
                </div>
            `).join('');
        }
    } catch (error) {
        reportFetchError('loadRecommendedBooths', error);
    }
}

async function loadStudentResume() {
    try {
        const response = await fetch('../includes/student_module.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `action=getStudentResume&student_id=${getCurrentStudentId()}`,
        });
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        const result = await response.json();
        if (result.success && result.data) {
            const nameElement = document.querySelector('h2.fw-bold');
            if (nameElement && result.data.F_Name && result.data.L_Name) {
                nameElement.textContent = `${result.data.F_Name} ${result.data.L_Name}`;
            }
            if (result.data.Resume_URL) {
                const cvBtn = Array.from(document.querySelectorAll('button')).find(btn => btn.textContent.includes('CV'));
                if (cvBtn) {
                    cvBtn.onclick = () => window.open(result.data.Resume_URL, '_blank');
                }
            }
        }
    } catch (error) {
        console.error('Failed to load student data:', error);
    }
}

if (!Element.prototype.matches) {
    Element.prototype.matches = Element.prototype.msMatchesSelector || Element.prototype.webkitMatchesSelector;
}





function attachEditProfileHandler() {
    const editProfileBtn = document.getElementById('btn-edit-profile')
        || Array.from(document.querySelectorAll('button')).find(btn =>
            btn.textContent.includes('Edit Profile')
        );
    
    if (editProfileBtn) {
        editProfileBtn.addEventListener('click', function() {
            showEditProfileModal();
        });
    }
}

function attachLeaveQueueHandler() {
    const leaveQueueBtn = document.getElementById('btn-leave-queue')
        || Array.from(document.querySelectorAll('button')).find(btn =>
            btn.textContent.includes('Leave Queue') || btn.textContent.includes('Exit')
        );
    
    if (leaveQueueBtn) {
        leaveQueueBtn.addEventListener('click', async function() {
            if (confirm('Are you sure you want to leave the queue?')) {
                toggleButton(this, true);
                
                try {
                    const result = await studentPost(
                        `action=leaveQueue&student_id=${getCurrentStudentId()}`,
                        'leaveQueue'
                    );

                    if (result.success) {
                        showMessage(result.message, 'success');
                        setTimeout(() => window.location.reload(), 1500);
                    } else {
                        showMessage(result.message, 'danger');
                    }
                } catch (error) {
                    reportFetchError('leaveQueue', error);
                } finally {
                    toggleButton(this, false);
                }
            }
        });
    }
}

function attachProfileActionsHandler() {
    const addSkillBtn = document.getElementById('btn-add-skill')
        || Array.from(document.querySelectorAll('button')).find(btn =>
            btn.textContent.includes('Add Skill')
        );
    
    if (addSkillBtn) {
        addSkillBtn.addEventListener('click', function() {
            const skill = prompt('Enter new skill:');
            if (skill && skill.trim()) {
                addSkillToProfile(skill.trim());
            }
        });
    }
    
    const addProjectBtn = document.getElementById('btn-add-project')
        || Array.from(document.querySelectorAll('button')).find(btn =>
            btn.textContent.includes('Add Project')
        );
    
    if (addProjectBtn) {
        addProjectBtn.addEventListener('click', function() {
            showAddProjectModal();
        });
    }
    
    const cvBtn = document.getElementById('btn-cv-download')
        || Array.from(document.querySelectorAll('button')).find(btn =>
            btn.textContent.includes('CV') && !btn.textContent.includes('Replace')
        );
    
    if (cvBtn) {
        cvBtn.addEventListener('click', async function() {
            await previewResume();
        });
    }
    
    const previewBtn = document.getElementById('btn-preview-recruiter')
        || Array.from(document.querySelectorAll('button')).find(btn =>
            btn.textContent.includes('Preview as Recruiter')
        );
    
    if (previewBtn) {
        previewBtn.addEventListener('click', async function() {
            await previewResume();
        });
    }

    const previewFileBtn = document.getElementById('btn-preview-resume-file');
    if (previewFileBtn) {
        previewFileBtn.addEventListener('click', async function() {
            await previewResume();
        });
    }
}

function attachInterviewActionsHandler() {
    const enterRoomBtn = document.getElementById('btn-enter-interview-room')
        || Array.from(document.querySelectorAll('button')).find(btn =>
            btn.textContent.includes('Enter Interview Room')
        );
    
    if (enterRoomBtn) {
        enterRoomBtn.addEventListener('click', async function() {
            toggleButton(this, true);
            
            try {
                const result = await studentPost(
                    `action=enterInterviewRoom&student_id=${getCurrentStudentId()}`,
                    'enterInterviewRoom'
                );

                if (result.success) {
                    showMessage('Entering interview room...', 'success');
                    setTimeout(() => {
                        window.location.href = 'S_liveSessions.php';
                    }, 1000);
                } else {
                    showMessage(result.message, 'danger');
                }
            } catch (error) {
                reportFetchError('enterInterviewRoom', error);
            } finally {
                toggleButton(this, false);
            }
        });
    }
    
    const chatForm = document.getElementById('chat-form');
    if (chatForm) {
        chatForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            const input = this.querySelector('#msg-input') || this.querySelector('input[type="text"]');
            const message = (input && input.value) ? input.value.trim() : '';
            
            if (message) {
                await sendChatMessage(message);
                input.value = '';
            }
        });
    }
    
    const archiveBtn = document.getElementById('btn-archive-session')
        || Array.from(document.querySelectorAll('button')).find(btn =>
            btn.textContent.includes('Archive')
        );
    
    if (archiveBtn) {
        archiveBtn.addEventListener('click', async function() {
            if (confirm('Archive this chat session?')) {
                toggleButton(this, true);
                
                try {
                    const result = await studentPost(
                        `action=archiveSession&student_id=${getCurrentStudentId()}`,
                        'archiveSession'
                    );

                    if (result.success) {
                        showMessage('Session archived', 'success');
                        setTimeout(() => window.location.reload(), 1000);
                    } else {
                        showMessage(result.message, 'danger');
                    }
                } catch (error) {
                    reportFetchError('archiveSession', error);
                } finally {
                    toggleButton(this, false);
                }
            }
        });
    }
    
    const endSessionBtn = document.getElementById('btn-end-session')
        || Array.from(document.querySelectorAll('button')).find(btn =>
            btn.textContent.includes('End Session')
        );
    
    if (endSessionBtn) {
        endSessionBtn.addEventListener('click', async function() {
            if (confirm('End this interview session?')) {
                toggleButton(this, true);
                
                try {
                    const result = await studentPost(
                        `action=endSession&student_id=${getCurrentStudentId()}`,
                        'endSession'
                    );

                    if (result.success) {
                        showMessage('Session ended', 'success');
                        setTimeout(() => window.location.href = 'S_studentDashboard.php', 1000);
                    } else {
                        showMessage(result.message, 'danger');
                    }
                } catch (error) {
                    reportFetchError('endSession', error);
                } finally {
                    toggleButton(this, false);
                }
            }
        });
    }
}

function attachInvitationActionsHandler() {
    const acceptBtn = document.getElementById('btn-invitation-accept');
    const declineBtn = document.getElementById('btn-invitation-decline');

    const acceptTargets = acceptBtn
        ? [acceptBtn]
        : Array.from(document.querySelectorAll('button')).filter(btn => btn.textContent.includes('Accept'));

    acceptTargets.forEach(btn => {
        btn.addEventListener('click', async function() {
            toggleButton(this, true);

            try {
                const result = await studentPost(
                    `action=acceptInvitation&student_id=${getCurrentStudentId()}`,
                    'acceptInvitation'
                );

                if (result.success) {
                    showMessage('Invitation accepted', 'success');
                    this.textContent = 'Accepted';
                    this.classList.remove('btn-green');
                    this.classList.add('btn-success');
                    this.disabled = true;
                } else {
                    showMessage(result.message, 'danger');
                }
            } catch (error) {
                reportFetchError('acceptInvitation', error);
            } finally {
                toggleButton(this, false);
            }
        });
    });

    const declineTargets = declineBtn
        ? [declineBtn]
        : Array.from(document.querySelectorAll('button')).filter(btn => btn.textContent.includes('Decline'));

    declineTargets.forEach(btn => {
        btn.addEventListener('click', async function() {
            if (confirm('Decline this invitation?')) {
                toggleButton(this, true);

                try {
                    const result = await studentPost(
                        `action=declineInvitation&student_id=${getCurrentStudentId()}`,
                        'declineInvitation'
                    );

                    if (result.success) {
                        showMessage('Invitation declined', 'warning');
                        this.textContent = 'Declined';
                        this.classList.add('btn-secondary');
                        this.disabled = true;
                    } else {
                        showMessage(result.message, 'danger');
                    }
                } catch (error) {
                    reportFetchError('declineInvitation', error);
                } finally {
                    toggleButton(this, false);
                }
            }
        });
    });
}

function attachOptimizeProfileHandler() {
    const runOptimize = async function(btn) {
        toggleButton(btn, true);

        try {
            const result = await studentPost(
                `action=getSkillGap&student_id=${getCurrentStudentId()}&job_id=1`,
                'getSkillGap'
            );

            if (result.success) {
                const gaps = Array.isArray(result.data) ? result.data : [];
                if (gaps.length > 0) {
                    showMessage(`Missing skills: ${gaps.join(', ')}`, 'warning');
                } else {
                    showMessage('Your profile is optimized!', 'success');
                }
            } else {
                showMessage(result.message, 'danger');
            }
        } catch (error) {
            reportFetchError('getSkillGap', error);
        } finally {
            toggleButton(btn, false);
        }
    };

    const optimizeBtn = document.getElementById('btn-optimize-profile')
        || Array.from(document.querySelectorAll('button')).find(btn =>
            btn.textContent.includes('Optimize Profile')
        );
    if (optimizeBtn) {
        optimizeBtn.addEventListener('click', function() {
            runOptimize(this);
        });
    }

    const optimizeNowBtn = document.getElementById('btn-optimize-now');
    if (optimizeNowBtn) {
        optimizeNowBtn.addEventListener('click', function() {
            runOptimize(this);
        });
    }
}

async function addSkillToProfile(skill) {
    try {
        const result = await studentPost(
            `action=addSkill&student_id=${getCurrentStudentId()}&skill=${encodeURIComponent(skill)}`,
            'addSkill'
        );

        if (result.success) {
            showMessage(`Skill "${skill}" added successfully`, 'success');
            const skillsContainer = document.querySelector('.d-flex.flex-wrap.gap-2');
            if (skillsContainer) {
                const skillTag = document.createElement('span');
                skillTag.className = 'skill-tag';
                skillTag.textContent = skill;
                skillsContainer.appendChild(skillTag);
            }
        } else {
            showMessage(result.message, 'danger');
        }
    } catch (error) {
        reportFetchError('addSkill', error);
    }
}

async function sendChatMessage(message) {
    console.log('FETCHING DATA FOR USER 1...', 'sendChatMessage');
    try {
        const result = await studentPost(
            `action=sendChatMessage&student_id=${getCurrentStudentId()}&message=${encodeURIComponent(message)}`,
            'sendChatMessage'
        );

        if (result.success) {
            const chatWindow = document.getElementById('chat-window')
                || document.querySelector('.chat-window, .msg-container');
            if (chatWindow) {
                const messageDiv = document.createElement('div');
                messageDiv.className = 'msg msg-student';
                messageDiv.textContent = message;
                chatWindow.appendChild(messageDiv);
                chatWindow.scrollTop = chatWindow.scrollHeight;
            }
        } else {
            alert('sendChatMessage: ' + (result.message || 'failed'));
            showMessage('Failed to send message', 'danger');
        }
    } catch (error) {
        reportFetchError('sendChatMessage', error);
    }
}

function showEditProfileModal() {
    const modalHtml = `
        <div class="modal fade" id="editProfileModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Profile</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <form id="editProfileForm">
                            <div class="mb-3">
                                <label class="form-label">First Name</label>
                                <input type="text" class="form-control" name="first_name" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Last Name</label>
                                <input type="text" class="form-control" name="last_name" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Major</label>
                                <input type="text" class="form-control" name="major" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">GPA</label>
                                <input type="number" step="0.01" min="0" max="4" class="form-control" name="gpa">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Bio</label>
                                <textarea class="form-control" name="bio" rows="3"></textarea>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-primary" onclick="saveProfile()">Save Changes</button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    document.body.insertAdjacentHTML('beforeend', modalHtml);
    const modal = new bootstrap.Modal(document.getElementById('editProfileModal'));
    modal.show();
    
    // Load current profile data
    loadCurrentProfile();
    
    // Clean up modal on close
    document.getElementById('editProfileModal').addEventListener('hidden.bs.modal', function() {
        this.remove();
    });
}

function showAddProjectModal() {
    const modalHtml = `
        <div class="modal fade" id="addProjectModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Add Project</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <form id="addProjectForm">
                            <div class="mb-3">
                                <label class="form-label">Project Name</label>
                                <input type="text" class="form-control" name="project_name" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Description</label>
                                <textarea class="form-control" name="description" rows="3" required></textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Technologies Used</label>
                                <input type="text" class="form-control" name="technologies" placeholder="e.g., React, Node.js, MongoDB">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Project URL (optional)</label>
                                <input type="url" class="form-control" name="project_url" placeholder="https://github.com/...">
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-primary" onclick="saveProject()">Add Project</button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    document.body.insertAdjacentHTML('beforeend', modalHtml);
    const modal = new bootstrap.Modal(document.getElementById('addProjectModal'));
    modal.show();
    
    // Clean up modal on close
    document.getElementById('addProjectModal').addEventListener('hidden.bs.modal', function() {
        this.remove();
    });
}

async function loadCurrentProfile() {
    try {
        const result = await studentPost(
            `action=getStudentProfile&student_id=${getCurrentStudentId()}`,
            'getStudentProfile_modal'
        );

        if (result.success && result.data) {
            const d = normalizeStudentProfile(result.data);
            const form = document.getElementById('editProfileForm');
            if (form) {
                form.first_name.value = d.F_Name || '';
                form.last_name.value = d.L_Name || '';
                form.major.value = d.Major || '';
                form.gpa.value = d.GPA !== '' && d.GPA != null ? d.GPA : '';
                form.bio.value = d.Bio || '';
            }
        }
    } catch (error) {
        reportFetchError('loadCurrentProfile', error);
    }
}

async function saveProfile() {
    console.log('FETCHING DATA FOR USER 1...', 'updateStudentProfile');
    const form = document.getElementById('editProfileForm');
    const formData = new FormData(form);

    try {
        const response = await fetch(STUDENT_API, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `action=updateStudentProfile&student_id=${getCurrentStudentId()}&${new URLSearchParams(formData).toString()}`
        });

        const result = await parseFetchResponse(response);

        if (result.success) {
            showMessage('Profile updated successfully!', 'success');
            bootstrap.Modal.getInstance(document.getElementById('editProfileModal')).hide();
            updateProfileUI(result.data);
            loadStudentProfileHeader();
        } else {
            showMessage(result.message, 'danger');
        }
    } catch (error) {
        reportFetchError('updateStudentProfile', error);
    }
}

async function saveProject() {
    console.log('FETCHING DATA FOR USER 1...', 'addProject');
    const form = document.getElementById('addProjectForm');
    const formData = new FormData(form);

    try {
        const response = await fetch(STUDENT_API, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `action=addProject&student_id=${getCurrentStudentId()}&${new URLSearchParams(formData).toString()}`
        });

        const result = await parseFetchResponse(response);

        if (result.success) {
            showMessage('Project added successfully!', 'success');
            bootstrap.Modal.getInstance(document.getElementById('addProjectModal')).hide();
            addProjectToUI(result.data);
        } else {
            showMessage(result.message, 'danger');
        }
    } catch (error) {
        reportFetchError('addProject', error);
    }
}

async function previewResume() {
    console.log('FETCHING DATA FOR USER 1...', 'previewResume');
    try {
        const result = await studentPost(
            `action=getStudentResume&student_id=${getCurrentStudentId()}`,
            'getStudentResume_preview'
        );

        if (!result.success) {
            showMessage(result.message || 'Could not load resume.', 'warning');
            return;
        }
        const url = normalizeStudentProfile(result.data).Resume_URL || (result.data && result.data.Resume_URL);
        if (url) {
            window.open(url, '_blank');
        } else {
            showMessage('No resume found. Please upload your resume first.', 'warning');
        }
    } catch (error) {
        reportFetchError('previewResume', error);
    }
}

function updateProfileUI(data) {
    const d = normalizeStudentProfile(data);
    const nameElement = document.querySelector('h2.fw-bold');
    if (nameElement && (d.F_Name || d.L_Name)) {
        nameElement.textContent = `${d.F_Name} ${d.L_Name}`.trim();
    }

    const majorElement = document.querySelector('.profile-header p.text-muted.mb-2') || document.querySelector('p.text-muted');
    if (majorElement && d.Major) {
        majorElement.textContent = `${d.Major} Student • Class of 2026`;
    }
}

function addProjectToUI(projectData) {
    const projectsContainer = document.querySelector('.card.p-4.mb-3');
    if (projectsContainer) {
        const projectHtml = `
            <div class="p-3 border rounded-4 mb-3">
                <h6 class="fw-bold mb-1">${projectData.project_name}</h6>
                <p class="small text-muted mb-2">${projectData.description}</p>
                <div class="d-flex justify-content-between align-items-center">
                    <span class="badge bg-secondary">${projectData.technologies}</span>
                    ${projectData.project_url ? `<a href="${projectData.project_url}" target="_blank" class="btn btn-sm btn-outline-primary">View Project</a>` : ''}
                </div>
            </div>
        `;
        projectsContainer.insertAdjacentHTML('beforeend', projectHtml);
    }
}

function attachPrivacySettingsHandler() {
    // Handle privacy checkboxes
    const resumeShareCheckbox = document.getElementById('resumeShare');
    const hideGPACheckbox = document.getElementById('hideGPA');
    const profileVisibilitySelect = document.getElementById('profileVisibility');
    
    if (resumeShareCheckbox) {
        resumeShareCheckbox.addEventListener('change', async function() {
            await updatePrivacySetting('resume_share', this.checked);
        });
    }
    
    if (hideGPACheckbox) {
        hideGPACheckbox.addEventListener('change', async function() {
            await updatePrivacySetting('hide_gpa', this.checked);
        });
    }
    
    if (profileVisibilitySelect) {
        profileVisibilitySelect.addEventListener('change', async function() {
            await updatePrivacySetting('profile_visibility', this.value);
        });
    }

    const savePrivacyBtn = document.getElementById('btn-save-privacy-preferences');
    if (savePrivacyBtn) {
        savePrivacyBtn.addEventListener('click', async function() {
            const params = new URLSearchParams();
            params.set('action', 'updatePrivacySettings');
            params.set('student_id', String(getCurrentStudentId()));
            if (resumeShareCheckbox) {
                params.set('resume_share', resumeShareCheckbox.checked ? '1' : '0');
            }
            if (hideGPACheckbox) {
                params.set('hide_gpa', hideGPACheckbox.checked ? '1' : '0');
            }
            if (profileVisibilitySelect) {
                params.set('profile_visibility', profileVisibilitySelect.value);
            }
            try {
                const result = await studentPost(params.toString(), 'updatePrivacySettings_batch');
                if (result.success) {
                    showMessage('Privacy settings saved', 'success');
                } else {
                    showMessage(result.message, 'danger');
                }
            } catch (error) {
                reportFetchError('updatePrivacySettings_batch', error);
            }
        });
    }
}

async function updatePrivacySetting(setting, value) {
    console.log('FETCHING DATA FOR USER 1...', 'updatePrivacySettings', setting);
    let encodedValue = value;
    if (setting === 'resume_share' || setting === 'hide_gpa') {
        encodedValue = value ? 1 : 0;
    } else if (setting === 'profile_visibility') {
        encodedValue = encodeURIComponent(value);
    }
    try {
        const result = await studentPost(
            `action=updatePrivacySettings&student_id=${getCurrentStudentId()}&${setting}=${encodedValue}`,
            'updatePrivacySettings'
        );

        if (result.success) {
            showMessage('Privacy settings updated', 'success');
        } else {
            showMessage(result.message, 'danger');
        }
    } catch (error) {
        reportFetchError('updatePrivacySettings', error);
    }
}

if (!Element.prototype.closest) {
    Element.prototype.closest = function(s) {
        var el = this;
        do {
            if (el.matches(s)) {
                return el;
            }
            el = el.parentElement || el.parentNode;
        } while (el !== null && el.nodeType === 1);
        return null;
    };
}
