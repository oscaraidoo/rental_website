<?php use App\Helpers\{Url, Security}; ?>
<div class="row justify-content-center">
<div class="col-12 col-lg-7">
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white fw-bold border-0 py-3">Create User</div>
    <div class="card-body">
        <form method="POST" action="<?= Url::base('admin/users/create') ?>">
            <?= Security::csrfField() ?>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-medium">First Name</label>
                    <input type="text" name="first_name" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-medium">Last Name</label>
                    <input type="text" name="last_name" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-medium">Email</label>
                    <input type="email" name="email" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-medium">Phone</label>
                    <input type="tel" name="phone" class="form-control">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-medium">Role</label>
                    <select name="role" class="form-select">
                        <option value="renter">Renter</option>
                        <option value="admin">Administrator</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-medium">Password</label>
                    <input type="password" name="password" class="form-control" required minlength="8" autocomplete="new-password">
                </div>
            </div>
            <div class="mt-4 d-flex gap-3">
                <button type="submit" class="btn btn-primary px-4">Create User</button>
                <a href="<?= Url::base('admin/users') ?>" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
</div>
</div>
