<!-- ══════════════════════════════════════════════
     MINI CART — used inside navbar dropdown
     x-data context: $store.cart, $store.nav
     ══════════════════════════════════════════════ -->
<div class="p-4">

    <!-- Header -->
    <div class="flex items-center justify-between mb-4">
        <h3 class="font-serif font-bold text-vanilla-800 text-base">Mon panier</h3>
        <span class="text-xs font-semibold text-vanilla-500 bg-vanilla-100 px-2 py-1 rounded-full"
              x-text="$store.cart.count + ' article' + ($store.cart.count !== 1 ? 's' : '')">
        </span>
    </div>

    <!-- Empty State -->
    <div x-show="$store.cart.count === 0" class="py-8 text-center">
        <div class="text-4xl mb-3">🌿</div>
        <p class="text-sm font-medium text-vanilla-500">Votre panier est vide</p>
        <a href="<?= url('shop') ?>"
           @click="$store.nav.cartOpen = false"
           class="mt-4 inline-flex items-center gap-1.5 text-xs font-semibold text-forest-600 hover:text-forest-700 transition-colors">
            Explorer la boutique →
        </a>
    </div>

    <!-- Item List -->
    <div x-show="$store.cart.count > 0" class="space-y-3 max-h-64 overflow-y-auto pr-1">
        <template x-for="item in $store.cart.items" :key="item.id">
            <div class="flex items-center gap-3 p-2.5 rounded-xl hover:bg-cream-100 transition-colors group">
                <!-- Image -->
                <div class="w-12 h-14 rounded-lg overflow-hidden bg-cream-200 shrink-0">
                    <img :src="item.image" :alt="item.name"
                         class="w-full h-full object-cover" loading="lazy"
                         onerror="this.src='<?= asset('img/placeholder.jpg') ?>'">
                </div>
                <!-- Info -->
                <div class="flex-1 min-w-0">
                    <p class="text-xs font-semibold text-vanilla-800 leading-snug truncate" x-text="item.name"></p>
                    <p class="text-xs text-vanilla-400 mt-0.5" x-text="'€' + item.price.toFixed(2)"></p>
                    <!-- Qty controls -->
                    <div class="flex items-center gap-1.5 mt-1.5">
                        <button
                            @click.stop="$store.cart.decrement(item.id)"
                            class="w-6 h-6 rounded-md bg-cream-200 flex items-center justify-center
                                   text-vanilla-600 font-bold text-xs hover:bg-vanilla-200 transition-colors">−</button>
                        <span class="text-xs font-bold text-vanilla-700 w-4 text-center" x-text="item.qty"></span>
                        <button
                            @click.stop="$store.cart.increment(item.id)"
                            class="w-6 h-6 rounded-md bg-cream-200 flex items-center justify-center
                                   text-vanilla-600 font-bold text-xs hover:bg-vanilla-200 transition-colors">+</button>
                    </div>
                </div>
                <!-- Line total + remove -->
                <div class="text-right shrink-0">
                    <p class="text-xs font-bold text-vanilla-800" x-text="'€' + (item.price * item.qty).toFixed(2)"></p>
                    <button
                        @click.stop="$store.cart.remove(item.id)"
                        class="mt-1.5 text-vanilla-300 hover:text-red-500 opacity-0 group-hover:opacity-100 transition-all"
                        aria-label="Retirer">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>
        </template>
    </div>

    <!-- Footer -->
    <div x-show="$store.cart.count > 0" class="mt-4 pt-4 border-t border-vanilla-100">
        <div class="flex justify-between items-baseline mb-4">
            <span class="text-xs font-medium text-vanilla-500">Sous-total</span>
            <span class="text-base font-bold font-serif text-vanilla-800"
                  x-text="$store.cart.subtotalFormatted()"></span>
        </div>
        <div class="grid grid-cols-2 gap-2">
            <a href="<?= url('cart') ?>"
               @click="$store.nav.cartOpen = false"
               class="text-center px-3 py-2.5 rounded-xl border border-vanilla-300 text-vanilla-700
                      text-xs font-semibold hover:bg-vanilla-50 transition-all duration-200">
                Voir le panier
            </a>
            <a href="<?= url('checkout') ?>"
               @click="$store.nav.cartOpen = false"
               class="text-center px-3 py-2.5 rounded-xl bg-vanilla-700 text-cream-100
                      text-xs font-semibold hover:bg-vanilla-600 transition-all duration-200">
                Commander →
            </a>
        </div>
    </div>
</div>
