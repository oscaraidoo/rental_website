// Shared UI utilities: formatting, favorites (localStorage), toasts, card rendering.

const FAV_KEY = "rental_favorites";

function formatPrice(value) {
  return "$" + value.toLocaleString("en-US");
}

function formatDate(iso) {
  const d = new Date(iso + "T00:00:00");
  return d.toLocaleDateString("en-US", { month: "short", day: "numeric", year: "numeric" });
}

function getFavorites() {
  try {
    return JSON.parse(localStorage.getItem(FAV_KEY)) || [];
  } catch (e) {
    return [];
  }
}

function isFavorite(id) {
  return getFavorites().includes(id);
}

function toggleFavorite(id) {
  const favs = getFavorites();
  const idx = favs.indexOf(id);
  let added = false;
  if (idx === -1) {
    favs.push(id);
    added = true;
  } else {
    favs.splice(idx, 1);
  }
  localStorage.setItem(FAV_KEY, JSON.stringify(favs));
  updateFavCount();
  return added;
}

function updateFavCount() {
  const badge = document.querySelector("[data-fav-count]");
  if (!badge) return;
  const count = getFavorites().length;
  badge.textContent = count;
  badge.style.display = count > 0 ? "inline-block" : "none";
  const link = badge.closest("a");
  if (link) {
    link.title = count > 0 ? count + " saved rental" + (count > 1 ? "s" : "") : "No saved rentals";
  }
}

// ---------- Toasts: every outcome is announced ----------

function ensureToastRegion() {
  let region = document.querySelector(".toast-region");
  if (!region) {
    region = document.createElement("div");
    region.className = "toast-region";
    region.setAttribute("role", "status");
    region.setAttribute("aria-live", "polite");
    document.body.appendChild(region);
  }
  return region;
}

function showToast(message, type) {
  const region = ensureToastRegion();
  const toast = document.createElement("div");
  toast.className = "toast" + (type ? " toast-" + type : "");
  toast.textContent = message;
  region.appendChild(toast);
  setTimeout(() => toast.remove(), 3500);
}

// ---------- Shared markup ----------

function favButtonSvg(active) {
  return active
    ? '<svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 21s-7.5-4.7-10-9.3C.4 8.6 2.3 5 5.8 5c2 0 3.4 1.1 4.2 2.4h4C14.8 6.1 16.2 5 18.2 5 21.7 5 23.6 8.6 22 11.7 19.5 16.3 12 21 12 21z" transform="scale(0.95) translate(0.6,0)"/></svg>'
    : '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M12 20.5s-7-4.4-9.4-8.6C1 9.2 2.7 6 5.9 6c1.9 0 3.2 1 4.1 2.3h4C14.9 7 16.2 6 18.1 6c3.2 0 4.9 3.2 3.3 5.9C19 16.1 12 20.5 12 20.5z"/></svg>';
}

function listingCard(item) {
  const saved = isFavorite(item.id);
  const card = document.createElement("article");
  card.className = "card";
  card.innerHTML =
    '<div class="card-media">' +
      '<div class="photo" style="background:' + item.tint + '">' + item.type + "</div>" +
      '<span class="card-badge">Available ' + formatDate(item.available) + "</span>" +
      '<button class="fav-btn" type="button" data-fav="' + item.id + '" aria-pressed="' + saved + '" aria-label="Save ' + item.title + '">' + favButtonSvg(saved) + "</button>" +
    "</div>" +
    '<div class="card-body">' +
      '<div class="card-price">' + formatPrice(item.price) + " <span>/ month</span></div>" +
      '<h3 class="card-title"><a href="listing.html?id=' + item.id + '" style="color:inherit">' + item.title + "</a></h3>" +
      '<div class="card-location">' +
        '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M12 21s-7-5.3-7-11a7 7 0 0 1 14 0c0 5.7-7 11-7 11z"/><circle cx="12" cy="10" r="2.5"/></svg>' +
        item.neighborhood + ", " + item.city +
      "</div>" +
      '<div class="card-facts">' +
        "<span>" + item.beds + " bed" + (item.beds > 1 ? "s" : "") + "</span>" +
        "<span>" + item.baths + " bath" + (item.baths > 1 ? "s" : "") + "</span>" +
        "<span>" + item.area.toLocaleString("en-US") + " sq ft</span>" +
      "</div>" +
    "</div>";
  return card;
}

function renderListings(container, items) {
  container.innerHTML = "";
  items.forEach((item) => container.appendChild(listingCard(item)));
}

// Delegated favorite handling: works on every page
document.addEventListener("click", (event) => {
  const btn = event.target.closest("[data-fav]");
  if (!btn) return;
  const id = btn.getAttribute("data-fav");
  const added = toggleFavorite(id);
  const item = LISTINGS.find((l) => l.id === id);
  const active = added;
  btn.setAttribute("aria-pressed", String(active));
  btn.innerHTML = favButtonSvg(active);
  showToast(
    added
      ? "Saved \"" + (item ? item.title : "rental") + "\" to your favorites."
      : "Removed \"" + (item ? item.title : "rental") + "\" from your favorites.",
    added ? "success" : null
  );
});

document.addEventListener("DOMContentLoaded", updateFavCount);
