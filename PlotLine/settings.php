<?php
include_once("vendor/autoload.php");
use Helpers\Auth;
use Libs\Database\MySQL;
use Libs\Database\NovelaTable;

$currentUser = Auth::check();
$db = new MySQL();
$novelaTable = new NovelaTable($db);

$worksCount = $novelaTable->getWorksCount($currentUser->id) ?? 0;
$currentPage = 'settings.php';
?>

<?php include 'components/navbar.php'; ?>

<style>
    body { background-color: #f1f5f9; color: #1e293b; }
    
    /* Layout */
    .settings-container { max-width: 1000px; padding-top: 3rem; padding-bottom: 5rem; }
    
    /* Sidebar Styling */
    .settings-sidebar .nav-link {
        color: #64748b;
        font-weight: 500;
        padding: 12px 16px;
        border-radius: 10px;
        transition: all 0.2s ease;
        border: 1px solid transparent;
        margin-bottom: 8px;
    }
    .settings-sidebar .nav-link:hover { background: #e2e8f0; color: #0f172a; }
    .settings-sidebar .nav-link.active { 
        background: white !important; 
        color: #0076fc !important; 
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        border-color: #e2e8f0;
    }
    .settings-sidebar .nav-link.text-danger:hover { background: #fff1f2; }

    /* Card Styling */
    .settings-card { 
        background: white; 
        border-radius: 20px; 
        border: 1px solid #e2e8f0; 
        box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        overflow: hidden;
    }
    .card-header-custom { padding: 1.5rem 2rem; border-bottom: 1px solid #f1f5f9; }
    .card-body-custom { padding: 2rem; }

    /* Form Elements */
    .form-label { font-size: 0.85rem; font-weight: 700; color: #475569; margin-bottom: 8px; }
    .form-control { 
        padding: 10px 14px; 
        border-radius: 10px; 
        border: 1px solid #e2e8f0; 
        background: #f8fafc;
    }
    .form-control:focus { 
        background: white; 
        border-color: #0076fc; 
        box-shadow: 0 0 0 4px rgba(0, 118, 252, 0.1); 
    }

    /* Danger Zone */
    .danger-zone-box {
        border: 1px solid #fee2e2;
        background: #fffafa;
        border-radius: 15px;
        padding: 1.5rem;
    }

    /* Buttons */
    .btn-save { padding: 10px 24px; border-radius: 10px; font-weight: 600; }
</style>

<div class="container settings-container">
    <div class="row g-4">
        
        <div class="col-lg-3 col-md-4 settings-sidebar">
            <h3 class="fw-bold mb-4 px-2">Settings</h3>
            <div class="nav flex-column nav-pills" id="v-pills-tab" role="tablist">
                <button class="nav-link active" data-bs-toggle="pill" data-bs-target="#account" type="button">
                    <i class="bi bi-shield-lock me-2"></i> Account Security
                </button>
                <button class="nav-link" data-bs-toggle="pill" data-bs-target="#notifications" type="button">
                    <i class="bi bi-bell me-2"></i> Preferences
                </button>
                <div class="my-3 border-bottom mx-3"></div>
                <button class="nav-link text-danger" data-bs-toggle="pill" data-bs-target="#danger" type="button">
                    <i class="bi bi-trash3 me-2"></i> Danger Zone
                </button>
            </div>
        </div>

        <div class="col-lg-9 col-md-8">
            <div class="tab-content" id="v-pills-tabContent">
                
                <div class="tab-pane fade show active" id="account" role="tabpanel">
                    <div class="settings-card mb-4">
                        <div class="card-header-custom">
                            <h5 class="fw-bold m-0">Email Address</h5>
                            <p class="text-muted small m-0">Update the email associated with your account.</p>
                        </div>
                        <div class="card-body-custom">
                            <form action="_actions/update-account.php" method="POST">
                                <div class="row align-items-end g-3">
                                    <div class="col-md-8">
                                        <label class="form-label">CURRENT EMAIL</label>
                                        <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($currentUser->email) ?>">
                                    </div>
                                    <div class="col-md-4">
                                        <button class="btn btn-primary btn-save w-100" type="submit">Update Email</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="settings-card">
                        <div class="card-header-custom">
                            <h5 class="fw-bold m-0">Change Password</h5>
                            <p class="text-muted small m-0">Ensure your account is using a long, random password to stay secure.</p>
                        </div>
                        <div class="card-body-custom">
                            <form action="_actions/change-password.php" method="POST">
                                <div class="mb-4">
                                    <label class="form-label">CURRENT PASSWORD</label>
                                    <input type="password" name="old_password" class="form-control" placeholder="••••••••">
                                </div>
                                <div class="row g-3 mb-4">
                                    <div class="col-md-6">
                                        <label class="form-label">NEW PASSWORD</label>
                                        <input type="password" name="new_password" class="form-control" placeholder="Minimum 8 characters">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">CONFIRM PASSWORD</label>
                                        <input type="password" name="confirm_password" class="form-control" placeholder="Repeat new password">
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-dark btn-save px-5">Save New Password</button>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="tab-pane fade" id="notifications" role="tabpanel">
                    <div class="settings-card">
                        <div class="card-header-custom">
                            <h5 class="fw-bold m-0">Preferences & Notifications</h5>
                            <p class="text-muted small m-0">Manage how you interact with the community.</p>
                        </div>
                        <div class="card-body-custom">
                            <div class="d-flex align-items-center justify-content-between py-3 border-bottom">
                                <div>
                                    <h6 class="fw-bold mb-1">New Followers</h6>
                                    <p class="text-muted small mb-0">Get an email whenever someone follows your profile.</p>
                                </div>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="emailNotif" checked style="width: 2.5em; height: 1.25em;">
                                </div>
                            </div>
                            <div class="d-flex align-items-center justify-content-between py-3">
                                <div>
                                    <h6 class="fw-bold mb-1">Story Comments</h6>
                                    <p class="text-muted small mb-0">Get notified when someone reviews your work.</p>
                                </div>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="commentNotif" checked style="width: 2.5em; height: 1.25em;">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="tab-pane fade" id="danger" role="tabpanel">
                    <div class="settings-card">
                        <div class="card-header-custom">
                            <h5 class="fw-bold text-danger m-0">Delete Account</h5>
                        </div>
                        <div class="card-body-custom">
                            <div class="danger-zone-box d-flex align-items-center gap-3">
                                <div class="bg-danger bg-opacity-10 p-3 rounded-circle">
                                    <i class="bi bi-exclamation-triangle text-danger fs-4"></i>
                                </div>
                                <div>
                                    <p class="mb-0 small text-dark">Once you delete your account, there is no going back. All your data including <strong><?= $worksCount ?> stories</strong> will be permanently removed.</p>
                                </div>
                            </div>
                            <button class="btn btn-danger btn-save mt-4" data-bs-toggle="modal" data-bs-target="#deleteModal">Deactivate Account</button>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-body p-5 text-center">
                <i class="bi bi-exclamation-octagon-fill text-danger mb-3" style="font-size: 4rem;"></i>
                <h3 class="fw-bold">Are you sure?</h3>
                <p class="text-muted px-4">This will permanently delete your PlotLine account and remove all your published content.</p>
                <div class="d-grid gap-2 mt-4">
                    <a href="_actions/delete-user.php" class="btn btn-danger py-2 rounded-pill fw-bold">Delete My Account</a>
                    <button class="btn btn-link text-secondary text-decoration-none fw-bold" data-bs-dismiss="modal">I changed my mind</button>
                </div>
            </div>
        </div>
    </div>
</div>
