<?php use App\Helpers\{Url, Security}; ?>

<!-- Filters -->
<div class="table-card mb-4">
    <div style="padding:16px 20px">
        <form method="GET" style="display:flex;flex-wrap:wrap;gap:10px;align-items:flex-end">
            <div style="flex:1;min-width:200px">
                <input type="text" name="q" class="form-control"
                       placeholder="Search name, email or phone..."
                       value="<?= Security::e($filters['search']) ?>">
            </div>
            <div style="min-width:140px">
                <select name="role" class="form-select">
                    <option value="">All Roles</option>
                    <option value="landlord" <?= $filters['role']==='landlord'?'selected':'' ?>>Landlords</option>
                    <option value="renter"   <?= $filters['role']==='renter'?'selected':'' ?>>Renters</option>
                    <option value="admin"    <?= $filters['role']==='admin'?'selected':'' ?>>Admins</option>
                </select>
            </div>
            <div style="min-width:140px">
                <select name="status" class="form-select">
                    <option value="">All Statuses</option>
                    <option value="active"               <?= $filters['status']==='active'?'selected':'' ?>>Active</option>
                    <option value="suspended"            <?= $filters['status']==='suspended'?'selected':'' ?>>Suspended</option>
                    <option value="pending_verification" <?= $filters['status']==='pending_verification'?'selected':'' ?>>Pending</option>
                </select>
            </div>
            <button class="btn btn-primary">Filter</button>
            <a href="<?= Url::base('admin/users') ?>" class="btn btn-secondary">Clear</a>
        </form>
    </div>
</div>

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;flex-wrap:wrap;gap:10px">
    <p style="font-size:13px;color:var(--muted);margin:0"><?= $pager->info() ?></p>
    <a href="<?= Url::base('admin/users/create') ?>" class="btn btn-primary btn-sm">
        Add User
    </a>
</div>

<div class="table-card">
    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Verified</th>
                    <th>Registered</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($users)): ?>
            <tr>
                <td colspan="9" style="text-align:center;padding:48px;color:var(--muted)">
                    No users found
                </td>
            </tr>
            <?php else: ?>
            <?php foreach ($users as $i => $user): ?>
            <tr>
                <td style="color:var(--muted)"><?= $pager->offset + $i + 1 ?></td>

                <!-- Name + avatar initial -->
                <td>
                    <div style="display:flex;align-items:center;gap:10px">
                        <div style="width:30px;height:30px;border-radius:50%;
                                    background:var(--black);color:#fff;
                                    display:flex;align-items:center;justify-content:center;
                                    font-size:12px;font-weight:700;flex-shrink:0">
                            <?= strtoupper(substr($user['first_name'], 0, 1)) ?>
                        </div>
                        <span style="font-weight:600">
                            <?= Security::e($user['first_name'] . ' ' . $user['last_name']) ?>
                        </span>
                    </div>
                </td>

                <td style="font-size:13px"><?= Security::e($user['email']) ?></td>
                <td style="font-size:13px;color:var(--muted)"><?= Security::e($user['phone'] ?? '—') ?></td>

                <td>
                    <span class="status-pill"
                          style="background:var(--light);border-color:var(--border);color:var(--dark)">
                        <?= ucfirst($user['role_name']) ?>
                    </span>
                </td>

                <td>
                    <span class="status-pill status-<?= Security::e($user['status']) ?>">
                        <?= ucwords(str_replace('_', ' ', $user['status'])) ?>
                    </span>
                </td>

                <td style="text-align:center;font-size:13px">
                    <?= $user['email_verified'] ? 'Yes' : '<span style="color:var(--muted)">No</span>' ?>
                </td>

                <td style="font-size:12px;color:var(--muted)">
                    <?= date('d M Y', strtotime($user['created_at'])) ?>
                </td>

                <!-- Actions -->
                <td>
                    <div style="display:flex;gap:6px;flex-wrap:wrap">
                        <a href="<?= Url::base('admin/users/' . $user['id'] . '/edit') ?>"
                           class="btn btn-secondary btn-sm">Edit</a>

                        <?php if ($user['status'] !== 'suspended'): ?>
                        <form method="POST"
                              action="<?= Url::base('admin/users/' . $user['id'] . '/suspend') ?>">
                            <?= Security::csrfField() ?>
                            <button class="btn btn-sm"
                                    style="background:#fffbeb;border:1.5px solid #fde68a;color:#92400e"
                                    data-confirm="Suspend this user?">
                                Suspend
                            </button>
                        </form>
                        <?php else: ?>
                        <form method="POST"
                              action="<?= Url::base('admin/users/' . $user['id'] . '/activate') ?>">
                            <?= Security::csrfField() ?>
                            <button class="btn btn-sm"
                                    style="background:#f0fdf4;border:1.5px solid #bbf7d0;color:#166534">
                                Activate
                            </button>
                        </form>
                        <?php endif; ?>

                        <?php if ($user['role_slug'] !== 'super_admin'
                               && (int)$user['id'] !== (int)($_SESSION['user_id'] ?? 0)): ?>
                        <form method="POST"
                              action="<?= Url::base('admin/users/' . $user['id'] . '/delete') ?>">
                            <?= Security::csrfField() ?>
                            <button class="btn btn-sm"
                                    style="background:#fef2f2;border:1.5px solid #fecaca;color:#991b1b"
                                    data-confirm="Permanently delete &quot;<?= Security::e($user['first_name'] . ' ' . $user['last_name']) ?>&quot;? This cannot be undone.">
                                Delete
                            </button>
                        </form>
                        <?php endif; ?>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>

    <?php if ($pager->lastPage > 1): ?>
    <div style="padding:16px 20px;border-top:1px solid var(--border)">
        <?= $pager->render() ?>
    </div>
    <?php endif; ?>
</div>
