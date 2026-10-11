<?php use App\Helpers\{Url, Security}; ?>
<div class="container py-5">
    <div class="row g-5">
        <div class="col-lg-5">
            <h1 class="fw-bold mb-3">Contact Us</h1>
            <p class="text-muted mb-4">Have a question about a listing, need help with a viewing, or want to know more about listing your property? We're here to help.</p>
            <div class="d-flex flex-column gap-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="bg-primary bg-opacity-10 text-primary rounded-3 d-flex align-items-center justify-content-center" style="width:44px;height:44px"><i class="fa-solid fa-envelope"></i></div>
                    <div><div class="fw-medium small">Email</div><div class="text-muted small">info@rentalwebsite.com</div></div>
                </div>
                <div class="d-flex align-items-center gap-3">
                    <div class="bg-primary bg-opacity-10 text-primary rounded-3 d-flex align-items-center justify-content-center" style="width:44px;height:44px"><i class="fa-solid fa-phone"></i></div>
                    <div><div class="fw-medium small">Phone</div><div class="text-muted small">+233 200 000 000</div></div>
                </div>
                <div class="d-flex align-items-center gap-3">
                    <div class="bg-primary bg-opacity-10 text-primary rounded-3 d-flex align-items-center justify-content-center" style="width:44px;height:44px"><i class="fa-solid fa-location-dot"></i></div>
                    <div><div class="fw-medium small">Address</div><div class="text-muted small">Takoradi, Western Region, Ghana</div></div>
                </div>
                <div class="d-flex align-items-center gap-3">
                    <div class="bg-success bg-opacity-10 text-success rounded-3 d-flex align-items-center justify-content-center" style="width:44px;height:44px"><i class="fa-brands fa-whatsapp"></i></div>
                    <div><div class="fw-medium small">WhatsApp</div><div class="text-muted small">+233 200 000 000</div></div>
                </div>
            </div>
        </div>
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <?= \App\Helpers\Flash::render() ?>
                    <form method="POST" action="<?= Url::base('contact') ?>">
                        <?= Security::csrfField() ?>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-medium">Your Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-medium">Email <span class="text-danger">*</span></label>
                                <input type="email" name="email" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-medium">Phone</label>
                                <input type="tel" name="phone" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-medium">Enquiry Type</label>
                                <select name="type" class="form-select">
                                    <option value="general">General</option>
                                    <option value="landlord_inquiry">Landlord Enquiry</option>
                                    <option value="renter_inquiry">Renter Enquiry</option>
                                    <option value="complaint">Complaint</option>
                                    <option value="support">Technical Support</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-medium">Subject <span class="text-danger">*</span></label>
                                <input type="text" name="subject" class="form-control" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-medium">Message <span class="text-danger">*</span></label>
                                <textarea name="message" class="form-control" rows="5" required minlength="10"></textarea>
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary fw-semibold px-5 py-2">
                                    <i class="fa-solid fa-paper-plane me-2"></i>Send Message
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
