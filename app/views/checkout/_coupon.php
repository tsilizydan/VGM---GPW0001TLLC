<!-- Coupon input partial (Alpine, AJAX) -->
<div
    x-data="{
        code: '<?= e($coupon['code'] ?? '') ?>',
        msg: '<?= addslashes(e($coupon['message'] ?? '')) ?>',
        discount: <?= (float)($coupon['discount'] ?? 0) ?>,
        loading: false,
        async apply() {
            this.loading = true;
            try {
                const res = await fetch('<?= locale_url('checkout/coupon') ?>', {
                    method: 'POST',
                    headers: {'Content-Type':'application/json'},
                    body: JSON.stringify({ code: this.code })
                });
                const data = await res.json();
                this.msg = data.message ?? '';
                if (data.success) {
                    this.discount = data.discount ?? 0;
                    $store.alerts.add(this.msg, 'success');
                } else {
                    this.discount = 0;
                    $store.alerts.add(this.msg, 'error');
                }
            } finally { this.loading = false; }
        },
        async remove() {
            await fetch('<?= locale_url('checkout/coupon') ?>', {
                method: 'POST',
                headers: {'Content-Type':'application/json'},
                body: JSON.stringify({ code: '' })
            });
            this.code = ''; this.msg = ''; this.discount = 0;
        }
    }"
    class="glass-card p-4 rounded-2xl"
>
    <label class="form-label mb-2 flex items-center gap-1.5">
        🏷️ Code promo
    </label>
    <div class="flex gap-2">
        <input type="text" x-model="code"
               placeholder="BIENVENUE10"
               class="form-input flex-1 uppercase tracking-widest text-sm"
               @keydown.enter.prevent="apply()"
               :disabled="discount > 0">
        <button type="button"
                @click="discount > 0 ? remove() : apply()"
                :disabled="loading"
                class="btn-primary btn btn-sm text-xs px-4 disabled:opacity-60">
            <span x-show="!loading"  x-text="discount > 0 ? '✕ Retirer' : 'Appliquer'"></span>
            <span x-show="loading">…</span>
        </button>
    </div>
    <p x-show="msg !== ''"
       :class="discount > 0 ? 'text-forest-600' : 'text-red-500'"
       x-text="msg" class="text-xs mt-1.5 font-semibold"></p>
    <p x-show="discount > 0" class="text-xs text-forest-600 mt-1">
        Économie : <strong x-text="'-' + discount.toFixed(2).replace('.',',') + ' €'"></strong>
    </p>
</div>
