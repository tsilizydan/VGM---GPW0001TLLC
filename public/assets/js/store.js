/**
 * Vanilla Groupe Madagascar — Alpine.js Global Data Stores
 * Loaded in <head> BEFORE Alpine is initialized.
 */
document.addEventListener('alpine:init', () => {

  // ─────────────────────────────────────────────────────────────
  // CART STORE
  // Persistent via localStorage
  // ─────────────────────────────────────────────────────────────
  Alpine.store('cart', {
    items: JSON.parse(localStorage.getItem('vgm_cart') || '[]'),

    get count() {
      return this.items.reduce((sum, i) => sum + i.qty, 0);
    },

    get subtotal() {
      return this.items.reduce((sum, i) => sum + i.price * i.qty, 0);
    },

    subtotalFormatted() {
      return '€' + this.subtotal.toFixed(2);
    },

    save() {
      localStorage.setItem('vgm_cart', JSON.stringify(this.items));
    },

    add(product) {
      // product = { id, name, price, image, variant? }
      const existing = this.items.find(i => i.id === product.id);
      if (existing) {
        existing.qty += product.qty ?? 1;
      } else {
        this.items.push({ ...product, qty: product.qty ?? 1 });
      }
      this.save();
      Alpine.store('alerts').add(`${product.name} ajouté au panier.`, 'success');
    },

    remove(id) {
      this.items = this.items.filter(i => i.id !== id);
      this.save();
    },

    updateQty(id, qty) {
      const item = this.items.find(i => i.id === id);
      if (!item) return;
      if (qty < 1) { this.remove(id); return; }
      item.qty = qty;
      this.save();
    },

    increment(id) {
      const item = this.items.find(i => i.id === id);
      if (item) { item.qty++; this.save(); }
    },

    decrement(id) {
      const item = this.items.find(i => i.id === id);
      if (!item) return;
      if (item.qty <= 1) { this.remove(id); } else { item.qty--; this.save(); }
    },

    clear() {
      this.items = [];
      this.save();
    },
  });

  // ─────────────────────────────────────────────────────────────
  // MODAL STORE
  // ─────────────────────────────────────────────────────────────
  Alpine.store('modal', {
    active: null,

    open(id) {
      this.active = id;
      document.body.classList.add('overflow-hidden');
    },

    close() {
      this.active = null;
      document.body.classList.remove('overflow-hidden');
    },

    isOpen(id) {
      return this.active === id;
    },
  });

  // ─────────────────────────────────────────────────────────────
  // ALERTS STORE
  // ─────────────────────────────────────────────────────────────
  Alpine.store('alerts', {
    list: [],
    _nextId: 1,

    add(message, type = 'info', duration = 4000) {
      const id = this._nextId++;
      this.list.push({ id, message, type, visible: true });
      if (duration > 0) {
        setTimeout(() => this.dismiss(id), duration);
      }
    },

    dismiss(id) {
      const item = this.list.find(a => a.id === id);
      if (item) item.visible = false;
      setTimeout(() => {
        this.list = this.list.filter(a => a.id !== id);
      }, 400);
    },
  });

  // ─────────────────────────────────────────────────────────────
  // NAVBAR STORE
  // ─────────────────────────────────────────────────────────────
  Alpine.store('nav', {
    mobileOpen: false,
    cartOpen:   false,
    langOpen:   false,
    language:   localStorage.getItem('vgm_lang') || 'FR',
    scrolled:   false,
    languages: ['FR', 'EN', 'ES'],

    toggle()       { this.mobileOpen = !this.mobileOpen; },
    closeAll()     { this.mobileOpen = false; this.cartOpen = false; this.langOpen = false; },
    toggleCart()   { this.cartOpen = !this.cartOpen; this.langOpen = false; },
    toggleLang()   { this.langOpen = !this.langOpen; this.cartOpen = false; },

    setLanguage(lang) {
      this.language = lang;
      localStorage.setItem('vgm_lang', lang);
      this.langOpen = false;
    },

    initScroll() {
      window.addEventListener('scroll', () => {
        this.scrolled = window.scrollY > 20;
      });
    },
  });

});

// ─────────────────────────────────────────────────────────────
// SCROLL REVEAL — minimal IntersectionObserver
// ─────────────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
  const io = new IntersectionObserver(
    (entries) => entries.forEach(e => {
      if (e.isIntersecting) {
        e.target.classList.add('visible');
        io.unobserve(e.target);
      }
    }),
    { threshold: 0.12 }
  );
  document.querySelectorAll('.animate-on-scroll').forEach(el => io.observe(el));
});
