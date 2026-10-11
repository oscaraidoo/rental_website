<?php use App\Helpers\{Url, Security}; ?>
<div class="row justify-content-center">
<div class="col-12 col-lg-7">
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white fw-bold border-0 py-3">Edit User: <?= Security::e($user['first_name'] . ' ' . $user['last_name']) ?></div>
    <div class="card-body">
        <form method="POST" action="<?= Url::base('admin/users/' . $user['id'] . '/update') ?>">
            <?= Security::csrfField() ?>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-medium">First Name</label>
                    <input type="text" name="first_name" class="form-control" required value="<?= Security::e($user['first_name']) ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-medium">Last Name</label>
                    <input type="text" name="last_name" class="form-control" required value="<?= Security::e($user['last_name']) ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-medium">Email <span class="text-muted fw-normal small">(read-only)</span></label>
                    <input type="email" class="form-control bg-light" value="<?= Security::e($user['email']) ?>" readonly>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-medium">Phone</label>
                    <input type="tel" name="phone" class="form-control" value="<?= Security::e($user['phone'] ?? '') ?>">
                </div>
                <?php if ($_SESSION['user_role'] === 'super_admin'): ?>
                <div class="col-md-6">
                    <label class="form-label fw-medium">Role</label>
                    <select name="role" class="form-select">
                        <?php foreach (['renter'=>'Renter','admin'=>'Administrator','super_admin'=>'Super Admin'] as $v => $l): ?>
                        <option value="<?= $v ?>" <?= $user['role_slug'] === $v ? 'selected' : '' ?>><?= $l ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php endif; ?>
                <div class="col-md-6">
                    <label class="form-label fw-medium">Status</label>
                    <select name="status" class="form-select">
                        <?php foreach (['active'=>'Active','suspended'=>'Suspended','inactive'=>'Inactive'] as $v => $l): ?>
                        <option value="<?= $v ?>" <?= $user['status'] === $v ? 'selected' : '' ?>><?= $l ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="mt-4 d-flex gap-3">
                <button type="submit" class="btn btn-primary px-4">Save Changes</button>
                <a href="<?= Url::base('admin/users') ?>" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
</div>
</div>
