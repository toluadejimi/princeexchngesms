<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-800 dark:text-slate-200 leading-tight">
            {{ $title }}
        </h2>
    </x-slot>

    <div class="max-w-2xl mx-auto sm:px-6 lg:px-8" id="rent-form-container"
        data-config="{{ base64_encode(json_encode([
            'serverId' => $server->id,
            'showCountry' => $showCountry,
            'isUsa' => !$showCountry,
            'priceDisplay' => $priceDisplay ?? 'USD',
            'usdToNgnRate' => (float) ($usdToNgnRate ?? 0),
            'nairaMarginPercent' => (float) ($nairaMarginPercent ?? 0),
            'nairaMarginAmount' => (float) ($nairaMarginAmount ?? 0),
            'servicesUrl' => route('rentals.services'),
            'countriesUrl' => route('rentals.countries'),
            'storeUrl' => route('rentals.store'),
            'csrf' => csrf_token(),
        ])) }}"
        x-data="rentFormSingle(document.getElementById('rent-form-container').dataset.config)"
        x-init="init()">
        <p class="text-slate-600 dark:text-slate-400 mb-4">{{ $subtitle }}</p>
        <div class="bg-white/80 dark:bg-slate-900/80 backdrop-blur rounded-2xl border border-slate-200 dark:border-slate-800 shadow-glass p-6">
            <form @submit.prevent="submit">
                <div class="space-y-4">
                    @if($showCountry)
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Country</label>
                        <p x-show="countriesLoading" class="text-sm text-slate-500 dark:text-slate-400 mb-1">Loading countries...</p>
                        <select x-model="countryCode" @change="loadServices" class="w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-200 shadow-sm focus:border-mint-500 focus:ring-mint-500"
                                :disabled="countriesLoading" required>
                            <option value="">Select country</option>
                            <template x-for="c in countries" :key="c.code">
                                <option :value="c.code" x-text="c.name"></option>
                            </template>
                        </select>
                        <p x-show="!countriesLoading && countries.length === 0" class="text-sm text-amber-600 dark:text-amber-400 mt-1">No countries available. Please try again later.</p>
                    </div>
                    @endif

                    {{-- USA: searchable service list + settings gear --}}
                    @if(!$showCountry)
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Service</label>
                        <div class="flex gap-2 mb-2">
                            <input type="text" x-model="serviceSearch" placeholder="Search services..." class="flex-1 rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-200 shadow-sm focus:border-mint-500 focus:ring-mint-500">
                            <button type="button" @click="applySearch" class="px-4 py-2 rounded-lg bg-slate-200 dark:bg-slate-700 text-slate-800 dark:text-slate-200 font-medium hover:bg-slate-300 dark:hover:bg-slate-600 transition">Search</button>
                        </div>
                        <div class="border border-slate-200 dark:border-slate-700 rounded-lg max-h-56 overflow-y-auto bg-slate-50 dark:bg-slate-800/50">
                            <template x-for="s in filteredServices" :key="s.code">
                                <div @click="selectService(s)" :class="serviceCode === s.code ? 'bg-mint-100 dark:bg-mint-900/30 border-mint-500' : 'hover:bg-slate-100 dark:hover:bg-slate-700/50'" class="flex justify-between items-center px-3 py-2 border-b border-slate-100 dark:border-slate-700 last:border-0 cursor-pointer transition">
                                    <span x-text="s.name" class="font-medium"></span>
                                    <span x-text="formatPrice(s.price)" class="text-mint-600 dark:text-mint-400 text-sm"></span>
                                </div>
                            </template>
                            <p x-show="filteredServices.length === 0 && serviceCodeReady" class="p-3 text-sm text-slate-500 dark:text-slate-400">No services match your search.</p>
                        </div>
                        <p x-show="!serviceCodeReady" class="text-sm text-slate-500 dark:text-slate-400 mt-1">Loading services...</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <button type="button" @click="showOptions = !showOptions" class="inline-flex items-center gap-2 px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-600 text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition" aria-label="More options">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            <span class="text-sm font-medium">More options</span>
                        </button>
                        <span x-show="showOptions" class="text-xs text-slate-500 dark:text-slate-400">Area code, carrier, specific number</span>
                    </div>
                    <div x-show="showOptions" x-cloak class="rounded-lg border border-slate-200 dark:border-slate-700 p-4 space-y-4 bg-slate-50 dark:bg-slate-800/30">
                        <div>
                            <div class="flex items-center gap-2 mb-1">
                                <label class="text-sm font-medium text-slate-600 dark:text-slate-400">Area code selection</label>
                                <button type="button" @click="showAreaInfo = !showAreaInfo" class="p-0.5 rounded-full text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 focus:outline-none focus:ring-2 focus:ring-mint-500/50" aria-label="Info">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/></svg>
                                </button>
                            </div>
                            <p x-show="showAreaInfo" x-cloak class="text-xs text-slate-500 dark:text-slate-400 mb-2">Preferred area codes. Increases price by 20%.</p>
                            <input type="text" x-model="areas" placeholder="e.g. 212, 718" class="w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-200 shadow-sm text-sm">
                        </div>
                        <div>
                            <div class="flex items-center gap-2 mb-1">
                                <label class="text-sm font-medium text-slate-600 dark:text-slate-400">Carrier selection</label>
                                <button type="button" @click="showCarrierInfo = !showCarrierInfo" class="p-0.5 rounded-full text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 focus:outline-none focus:ring-2 focus:ring-mint-500/50" aria-label="Info">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/></svg>
                                </button>
                            </div>
                            <p x-show="showCarrierInfo" x-cloak class="text-xs text-slate-500 dark:text-slate-400 mb-2">Preferred carrier. Increases price by 20%.</p>
                            <div class="flex flex-wrap gap-3">
                                <label class="inline-flex items-center gap-1.5 cursor-pointer"><input type="checkbox" x-model="carrierTmo" value="tmo"> <span class="text-sm">T-Mobile</span></label>
                                <label class="inline-flex items-center gap-1.5 cursor-pointer"><input type="checkbox" x-model="carrierVz" value="vz"> <span class="text-sm">Verizon</span></label>
                                <label class="inline-flex items-center gap-1.5 cursor-pointer"><input type="checkbox" x-model="carrierAtt" value="att"> <span class="text-sm">AT&T</span></label>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-600 dark:text-slate-400 mb-1">Specific number (optional, e.g. 11112223344)</label>
                            <input type="text" x-model="number" placeholder="11112223344" class="w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-200 shadow-sm text-sm">
                        </div>
                    </div>
                    @else
                    {{-- Other countries: simple service dropdown --}}
                    <p x-show="number" class="text-sm text-mint-600 dark:text-mint-400">
                        Reusing number: <span class="font-mono" x-text="number"></span>
                    </p>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Service</label>
                        <select x-model="serviceCode" @change="onServiceSelect" :disabled="!serviceCodeReady" class="w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-200 shadow-sm focus:border-mint-500 focus:ring-mint-500" required>
                            <option value="">Select service</option>
                            <template x-for="s in services" :key="s.code">
                                <option :value="s.code" :data-price="s.price" x-text="s.name + ' — ' + formatPrice(s.price)"></option>
                            </template>
                        </select>
                    </div>
                    @endif

                    <p x-show="error" x-text="error" class="text-sm text-red-600 dark:text-red-400"></p>
                    <p x-show="success" class="text-sm text-mint-600 dark:text-mint-400">Rented! Redirecting...</p>

                    <button type="submit" :disabled="loading" class="w-full inline-flex justify-center items-center px-4 py-3 rounded-lg bg-gradient-to-r from-mint-500 to-blue-500 text-white font-medium shadow-neon-mint hover:shadow-lg disabled:opacity-50 transition">
                        <span x-show="!loading">Rent Number</span>
                        <span x-show="loading">Please wait...</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function rentFormSingle(configOrEncoded) {
            var raw = typeof configOrEncoded === 'string'
                ? (function(){ try { return atob(configOrEncoded); } catch(e) { return '{}'; } })()
                : null;
            var config = raw !== null ? (function(){ try { return JSON.parse(raw); } catch(e) { return {}; } })() : (configOrEncoded || {});
            return {
                serverId: config.serverId,
                countryCode: config.showCountry ? '' : 'US',
                serviceCode: '',
                serviceSearch: '',
                services: [],
                countries: [],
                showCountry: config.showCountry,
                isUsa: config.isUsa || false,
                countriesLoading: false,
                priceDisplay: config.priceDisplay || 'USD',
                usdToNgnRate: config.usdToNgnRate || 0,
                nairaMarginPercent: config.nairaMarginPercent || 0,
                nairaMarginAmount: config.nairaMarginAmount || 0,
                serviceCodeReady: false,
                loading: false,
                error: '',
                success: false,
                showOptions: false,
                showAreaInfo: false,
                showCarrierInfo: false,
                areas: '',
                carrierTmo: false,
                carrierVz: false,
                carrierAtt: false,
                number: '',
                servicesUrl: config.servicesUrl,
                countriesUrl: config.countriesUrl,
                storeUrl: config.storeUrl,
                csrf: config.csrf,
                get filteredServices() {
                    if (!this.services || this.services.length === 0) return [];
                    const q = (this.serviceSearch || '').toLowerCase().trim();
                    if (!q) return this.services;
                    return this.services.filter(s => (s.name && s.name.toLowerCase().includes(q)) || (s.code && s.code.toLowerCase().includes(q)));
                },
                formatPrice(usd) {
                    const p = parseFloat(usd) || 0;
                    if (this.priceDisplay === 'NGN' && this.usdToNgnRate > 0) {
                        let totalNgn = p * this.usdToNgnRate;
                        if (this.nairaMarginPercent) totalNgn *= (1 + this.nairaMarginPercent / 100);
                        totalNgn += (this.nairaMarginAmount || 0);
                        return '₦' + Math.round(totalNgn).toLocaleString();
                    }
                    return '$' + p.toFixed(2);
                },
                init() {
                    var params = new URLSearchParams(window.location.search);
                    var reuseNumber = params.get('number') || '';
                    if (reuseNumber) {
                        this.number = reuseNumber.replace(/\D/g, '');
                        this.showOptions = true;
                    }
                    if (this.showCountry) this.loadCountries();
                    else { this.countryCode = 'US'; this.loadServices(); }
                },
                applySearch() {
                    this.serviceSearch = this.serviceSearch.trim();
                },
                selectService(s) {
                    this.serviceCode = s.code;
                },
                onServiceSelect() {},
                async loadCountries() {
                    this.countriesLoading = true;
                    this.countries = [];
                    try {
                        const r = await fetch(this.countriesUrl + '?server_id=' + this.serverId, {
                            credentials: 'same-origin',
                            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                        });
                        const d = await r.json();
                        this.countries = Array.isArray(d.countries) ? d.countries : [];
                        if (this.countries.length) this.countryCode = this.countries[0].code;
                        this.loadServices();
                    } catch (e) {
                        this.countries = [];
                    } finally {
                        this.countriesLoading = false;
                    }
                },
                async loadServices() {
                    const country = this.countryCode || 'US';
                    const r = await fetch(this.servicesUrl + '?server_id=' + this.serverId + '&country_code=' + encodeURIComponent(country), { headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' } });
                    const d = await r.json();
                    this.services = d.services || [];
                    this.serviceCodeReady = true;
                },
                getCarriersParam() {
                    const v = [];
                    if (this.carrierTmo) v.push('tmo');
                    if (this.carrierVz) v.push('vz');
                    if (this.carrierAtt) v.push('att');
                    return v.length ? v.join(',') : '';
                },
                async submit() {
                    this.error = '';
                    this.loading = true;
                    try {
                        const form = new FormData();
                        form.append('_token', this.csrf);
                        form.append('server_id', this.serverId);
                        form.append('service_code', this.serviceCode);
                        if (this.showCountry) form.append('country_code', this.countryCode);
                        if (this.isUsa && this.areas) form.append('areas', this.areas.replace(/\s/g,''));
                        if (this.isUsa && this.getCarriersParam()) form.append('carriers', this.getCarriersParam());
                        if (this.number) form.append('number', this.number.replace(/\D/g,''));
                        const r = await fetch(this.storeUrl, { method: 'POST', body: form, headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' } });
                        const d = await r.json();
                        if (!r.ok) throw new Error(d.message || 'Request failed');
                        this.success = true;
                        if (d.redirect) window.location.href = d.redirect;
                    } catch (e) {
                        this.error = e.message;
                    } finally {
                        this.loading = false;
                    }
                }
            };
        }
    </script>
</x-app-layout>
