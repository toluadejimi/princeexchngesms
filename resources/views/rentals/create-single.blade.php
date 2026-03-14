<x-app-layout>
    <x-slot name="header">
        <div class="space-y-3">
            <div class="flex items-center justify-between gap-2">
                <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-1.5 text-sm font-medium text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 transition min-h-[44px] items-center">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    <span class="hidden xs:inline">Dashboard</span>
                </a>
                <h2 class="font-bold text-lg sm:text-xl text-slate-800 dark:text-slate-200 leading-tight truncate">
                    {{ $title }}
                </h2>
                <div class="w-14 shrink-0"></div>
            </div>
            @if(isset($otherServerRoute) && isset($otherServerLabel))
            <a href="{{ $otherServerRoute }}" class="flex sm:inline-flex items-center justify-center gap-2 w-full sm:w-auto min-h-[44px] px-4 py-3 sm:py-2 rounded-xl text-sm font-medium bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-200 hover:bg-slate-200 dark:hover:bg-slate-700 border border-slate-200 dark:border-slate-600 transition">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                Switch to {{ $otherServerLabel }}
            </a>
            @endif
        </div>
    </x-slot>

    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-4 sm:py-6" id="rent-form-container"
        data-config="{{ base64_encode(json_encode([
            'serverId' => $server->id,
            'serverType' => $server->type ?? '',
            'showCountry' => $showCountry,
            'isUsa' => !$showCountry,
            'priceDisplay' => $priceDisplay ?? 'USD',
            'usdToNgnRate' => (float) ($usdToNgnRate ?? 0),
            'nairaMarginPercent' => (float) ($nairaMarginPercent ?? 0),
            'nairaMarginAmount' => (float) ($nairaMarginAmount ?? 0),
            'servicesUrl' => route('rentals.services'),
            'countriesUrl' => route('rentals.countries'),
            'poolsUrl' => route('rentals.pools'),
            'priceUrl' => route('rentals.price'),
            'storeUrl' => route('rentals.store'),
            'csrf' => csrf_token(),
        ])) }}"
        x-data="rentFormSingle(document.getElementById('rent-form-container').dataset.config)"
        x-init="init()">
        <p class="text-slate-600 dark:text-slate-400 text-sm sm:text-base mb-6 leading-relaxed">{{ $subtitle }}</p>
        <div class="bg-white dark:bg-slate-900 rounded-2xl sm:rounded-3xl border border-slate-200 dark:border-slate-700 shadow-sm sm:shadow-md overflow-hidden">
            <form @submit.prevent="submit" class="p-4 sm:p-6 lg:p-8">
                <div class="space-y-6 sm:space-y-5">
                    @if($showCountry)
                    <section class="space-y-2">
                        <label class="block text-sm font-semibold text-slate-800 dark:text-slate-200">Country</label>
                        <p x-show="countriesLoading" class="text-sm text-slate-500 dark:text-slate-400">Loading countries...</p>
                        <div x-show="!countriesLoading" class="relative" @click.away="countryOpen = false">
                            <input type="text" x-model="countrySearch" @focus="countryOpen = true" placeholder="Search countries..."
                                class="w-full min-h-[48px] px-4 rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:border-mint-500 focus:ring-2 focus:ring-mint-500/20 text-base"
                                autocomplete="off">
                            <input type="hidden" name="country_code" :value="countryCode" required>
                            <div x-show="countryOpen" x-cloak class="absolute z-20 mt-2 left-0 right-0 max-h-[min(60vh,320px)] overflow-y-auto rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 shadow-xl">
                                <template x-for="(c, i) in filteredCountries" :key="(c.code || '') + '-' + (c.name || '') + '-' + i">
                                    <button type="button" @click="selectCountry(c)" class="w-full text-left px-4 py-3.5 min-h-[48px] flex items-center text-slate-800 dark:text-slate-200 hover:bg-mint-50 dark:hover:bg-mint-900/20 active:bg-mint-100 dark:active:bg-mint-900/30 border-b border-slate-100 dark:border-slate-700 last:border-0 text-base" x-text="c.name"></button>
                                </template>
                                <p x-show="filteredCountries.length === 0 && !countriesLoading" class="px-4 py-4 text-slate-500 dark:text-slate-400 text-sm" x-text="countries.length === 0 ? 'No countries available' : 'No match'"></p>
                            </div>
                        </div>
                        <p x-show="!countriesLoading && countries.length === 0" class="text-sm text-amber-600 dark:text-amber-400">No countries available. Please try again later.</p>
                    </section>
                    @endif

                    {{-- USA: searchable service list + settings gear --}}
                    @if(!$showCountry)
                    <section class="space-y-2">
                        <label class="block text-sm font-semibold text-slate-800 dark:text-slate-200">Service</label>
                        <div class="flex gap-2">
                            <input type="text" x-model="serviceSearch" placeholder="Search services..." class="flex-1 min-h-[48px] px-4 rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:border-mint-500 focus:ring-2 focus:ring-mint-500/20 text-base">
                            <button type="button" @click="applySearch" class="min-h-[48px] px-4 rounded-xl bg-slate-200 dark:bg-slate-700 text-slate-800 dark:text-slate-200 font-medium hover:bg-slate-300 dark:hover:bg-slate-600 active:scale-[0.98] transition">Search</button>
                        </div>
                        <div class="rounded-xl border border-slate-200 dark:border-slate-600 max-h-[min(50vh,280px)] overflow-y-auto bg-slate-50/50 dark:bg-slate-800/50">
                            <template x-for="s in filteredServices" :key="s.code">
                                <button type="button" @click="selectService(s)" :class="serviceCode === s.code ? 'bg-mint-100 dark:bg-mint-900/40 border-l-4 border-mint-500' : 'hover:bg-slate-100 dark:hover:bg-slate-700/50 active:bg-slate-100 dark:active:bg-slate-700'" class="w-full flex justify-between items-center px-4 py-3.5 min-h-[52px] border-b border-slate-100 dark:border-slate-700 last:border-0 text-left transition">
                                    <span x-text="s.name" class="font-medium text-slate-800 dark:text-slate-200"></span>
                                    <span x-text="formatPrice(s.price)" class="text-mint-600 dark:text-mint-400 text-sm font-semibold"></span>
                                </button>
                            </template>
                            <p x-show="filteredServices.length === 0 && serviceCodeReady" class="p-4 text-sm text-slate-500 dark:text-slate-400">No services match your search.</p>
                        </div>
                        <p x-show="!serviceCodeReady" class="text-sm text-slate-500 dark:text-slate-400">Loading services...</p>
                    </section>
                    <div class="flex items-center gap-2">
                        <button type="button" @click="showOptions = !showOptions" class="inline-flex items-center gap-2 min-h-[44px] px-4 py-2 rounded-xl border border-slate-300 dark:border-slate-600 text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 active:scale-[0.98] transition" aria-label="More options">
                            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            <span class="text-sm font-medium">More options</span>
                        </button>
                        <span x-show="showOptions" class="text-xs text-slate-500 dark:text-slate-400">Area code, carrier, number</span>
                    </div>
                    <div x-show="showOptions" x-cloak class="rounded-xl border border-slate-200 dark:border-slate-600 p-4 space-y-4 bg-slate-50 dark:bg-slate-800/50">
                        <div>
                            <div class="flex items-center gap-2 mb-1">
                                <label class="text-sm font-medium text-slate-600 dark:text-slate-400">Area code selection</label>
                                <button type="button" @click="showAreaInfo = !showAreaInfo" class="p-0.5 rounded-full text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 focus:outline-none focus:ring-2 focus:ring-mint-500/50" aria-label="Info">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/></svg>
                                </button>
                            </div>
                            <p x-show="showAreaInfo" x-cloak class="text-xs text-slate-500 dark:text-slate-400 mb-2">Preferred area codes. Increases price by 20%.</p>
                            <input type="text" x-model="areas" placeholder="e.g. 212, 718" class="w-full min-h-[44px] px-4 rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 text-base">
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
                                <label class="inline-flex items-center gap-3 min-h-[44px] px-4 py-2 rounded-xl border border-slate-200 dark:border-slate-600 cursor-pointer hover:bg-slate-100 dark:hover:bg-slate-800/50 transition has-[:checked]:border-mint-500 has-[:checked]:bg-mint-50 dark:has-[:checked]:bg-mint-900/20"><input type="checkbox" x-model="carrierTmo" value="tmo" class="w-5 h-5 rounded border-slate-300 text-mint-500 focus:ring-mint-500"> <span class="text-sm font-medium text-slate-700 dark:text-slate-300">T-Mobile</span></label>
                                <label class="inline-flex items-center gap-3 min-h-[44px] px-4 py-2 rounded-xl border border-slate-200 dark:border-slate-600 cursor-pointer hover:bg-slate-100 dark:hover:bg-slate-800/50 transition has-[:checked]:border-mint-500 has-[:checked]:bg-mint-50 dark:has-[:checked]:bg-mint-900/20"><input type="checkbox" x-model="carrierVz" value="vz" class="w-5 h-5 rounded border-slate-300 text-mint-500 focus:ring-mint-500"> <span class="text-sm font-medium text-slate-700 dark:text-slate-300">Verizon</span></label>
                                <label class="inline-flex items-center gap-3 min-h-[44px] px-4 py-2 rounded-xl border border-slate-200 dark:border-slate-600 cursor-pointer hover:bg-slate-100 dark:hover:bg-slate-800/50 transition has-[:checked]:border-mint-500 has-[:checked]:bg-mint-50 dark:has-[:checked]:bg-mint-900/20"><input type="checkbox" x-model="carrierAtt" value="att" class="w-5 h-5 rounded border-slate-300 text-mint-500 focus:ring-mint-500"> <span class="text-sm font-medium text-slate-700 dark:text-slate-300">AT&T</span></label>
                            </div>
                        </div>
                        <div class="space-y-2">
                            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300">Specific number (optional)</label>
                            <input type="text" x-model="number" placeholder="e.g. 11112223344" class="w-full min-h-[44px] px-4 rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 text-base">
                        </div>
                    </div>
                    @else
                    {{-- Other countries: searchable service dropdown + pool --}}
                    <p x-show="number" class="text-sm text-mint-600 dark:text-mint-400">
                        Reusing number: <span class="font-mono" x-text="number"></span>
                    </p>
                    <section class="space-y-2">
                        <label class="block text-sm font-semibold text-slate-800 dark:text-slate-200">Service</label>
                        <p x-show="!serviceCodeReady" class="text-sm text-slate-500 dark:text-slate-400">Loading services...</p>
                        <div x-show="serviceCodeReady" class="relative" @click.away="serviceOpen = false">
                            <input type="text" x-model="serviceSearch" @focus="serviceOpen = true" placeholder="Search services (e.g. WhatsApp, Telegram)"
                                class="w-full min-h-[48px] px-4 rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:border-mint-500 focus:ring-2 focus:ring-mint-500/20 text-base"
                                autocomplete="off">
                            <input type="hidden" name="service_code" :value="serviceCode" required>
                            <div x-show="serviceOpen" x-cloak class="absolute z-20 mt-2 left-0 right-0 max-h-[min(60vh,320px)] overflow-y-auto rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 shadow-xl">
                                <template x-for="s in filteredServicesOther" :key="s.code">
                                    <button type="button" @click="selectServiceOther(s)" class="w-full text-left px-4 py-3.5 min-h-[48px] hover:bg-mint-50 dark:hover:bg-mint-900/20 active:bg-mint-100 dark:active:bg-mint-900/30 border-b border-slate-100 dark:border-slate-700 last:border-0 text-slate-800 dark:text-slate-200 text-base" x-text="s.name"></button>
                                </template>
                                <p x-show="filteredServicesOther.length === 0" class="px-4 py-4 text-slate-500 dark:text-slate-400 text-sm" x-text="services.length === 0 ? 'No services available' : ((serviceSearch || '').trim() ? 'No match' : 'Type to search (e.g. WhatsApp, Telegram)')"></p>
                            </div>
                        </div>
                    </section>
                    <section class="space-y-2">
                        <label class="block text-sm font-semibold text-slate-800 dark:text-slate-200" x-text="serverType === 'smsconfirmed' ? 'Operator (optional)' : 'Pool (optional)'"></label>
                        <p x-show="poolsLoading" class="text-sm text-slate-500 dark:text-slate-400" x-text="serverType === 'smsconfirmed' ? 'Loading operators...' : 'Loading pools...'"></p>
                        <select x-show="!poolsLoading" x-ref="poolSelect" x-model="poolId" @change="loadPrice()" class="w-full min-h-[48px] px-4 rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 focus:border-mint-500 focus:ring-2 focus:ring-mint-500/20 text-base">
                            <option value="" x-text="serverType === 'smsconfirmed' ? 'No preference (any operator)' : 'No preference (any pool)'"></option>
                        </select>
                    </section>
                    {{-- Live price & success rate (Other Countries) --}}
                    <div x-show="showCountry && (priceLoading || priceNgn > 0 || priceUsd > 0 || successRate > 0)" class="rounded-xl border border-slate-200 dark:border-slate-600 bg-slate-50 dark:bg-slate-800/60 p-4 sm:p-5">
                        <p x-show="priceLoading" class="text-sm text-slate-500 dark:text-slate-400">Checking price...</p>
                        <div x-show="!priceLoading && (priceNgn > 0 || priceUsd > 0 || successRate > 0)" class="space-y-4">
                            <div class="flex items-center justify-between gap-4">
                                <span class="text-sm font-semibold text-slate-700 dark:text-slate-300">Estimated price</span>
                                <span class="text-xl sm:text-2xl font-bold text-mint-600 dark:text-mint-400 tabular-nums" x-text="priceDisplay === 'NGN' && priceNgn > 0 ? ('₦' + priceNgn.toLocaleString()) : ('$' + (priceUsd || 0).toFixed(2))"></span>
                            </div>
                            <div x-show="successRate > 0" class="flex items-center justify-between gap-4">
                                <span class="text-sm font-semibold text-slate-700 dark:text-slate-300">Success rate</span>
                                <div class="flex items-center gap-3">
                                    <div class="w-20 sm:w-24 h-2.5 rounded-full bg-slate-200 dark:bg-slate-700 overflow-hidden" role="progressbar" :aria-valuenow="successRate" aria-valuemin="0" aria-valuemax="100">
                                        <div class="h-full rounded-full transition-all duration-300"
                                            :class="successRate >= 80 ? 'bg-emerald-500' : successRate >= 50 ? 'bg-amber-500' : 'bg-red-500'"
                                            :style="'width:' + Math.min(100, successRate) + '%'"></div>
                                    </div>
                                    <span class="text-sm font-bold tabular-nums min-w-[3ch]"
                                        :class="successRate >= 80 ? 'text-emerald-600 dark:text-emerald-400' : successRate >= 50 ? 'text-amber-600 dark:text-amber-400' : 'text-red-600 dark:text-red-400'"
                                        x-text="successRate + '%'"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    <div class="space-y-3 pt-2">
                        <p x-show="error" x-text="error" class="text-sm text-red-600 dark:text-red-400 font-medium"></p>
                        <p x-show="success" class="text-sm text-mint-600 dark:text-mint-400 font-medium">Rented! Redirecting...</p>
                        <button type="submit" :disabled="loading" class="w-full min-h-[52px] sm:min-h-[48px] inline-flex justify-center items-center px-6 py-4 sm:py-3 rounded-xl bg-gradient-to-r from-mint-500 to-emerald-500 text-white font-semibold text-base shadow-lg shadow-mint-500/25 hover:shadow-xl hover:shadow-mint-500/30 active:scale-[0.99] disabled:opacity-60 disabled:active:scale-100 transition">
                            <span x-show="!loading">Rent number</span>
                            <span x-show="loading" class="inline-flex items-center gap-2"><svg class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Please wait...</span>
                        </button>
                    </div>
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
                serverType: config.serverType || '',
                countryCode: config.showCountry ? '' : 'US',
                serviceCode: '',
                serviceSearch: '',
                services: [],
                countries: [],
                countrySearch: '',
                countryOpen: false,
                showCountry: config.showCountry,
                isUsa: config.isUsa || false,
                countriesLoading: false,
                serviceOpen: false,
                pools: [],
                poolsLoading: false,
                poolId: '',
                priceUrl: config.priceUrl,
                priceLoading: false,
                priceNgn: 0,
                priceUsd: 0,
                successRate: 0,
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
                poolsUrl: config.poolsUrl,
                csrf: config.csrf,
                get filteredCountries() {
                    if (!this.countries || this.countries.length === 0) return [];
                    var q = (this.countrySearch || '').toLowerCase().trim();
                    if (!q) return this.countries;
                    return this.countries.filter(function(c) {
                        return (c.name && c.name.toLowerCase().indexOf(q) !== -1) || (c.code && c.code.toLowerCase().indexOf(q) !== -1);
                    });
                },
                get filteredServicesOther() {
                    if (!this.services || this.services.length === 0) return [];
                    var q = (this.serviceSearch || '').toLowerCase().trim();
                    if (!q) return [];
                    return this.services.filter(function(s) {
                        return (s.name && s.name.toLowerCase().indexOf(q) !== -1) || (s.code && String(s.code).toLowerCase().indexOf(q) !== -1);
                    }).slice(0, 150);
                },
                get filteredServices() {
                    if (!this.services || this.services.length === 0) return [];
                    const q = (this.serviceSearch || '').toLowerCase().trim();
                    if (!q) return this.services;
                    return this.services.filter(s => (s.name && s.name.toLowerCase().includes(q)) || (s.code && s.code.toLowerCase().includes(q)));
                },
                formatPrice(usd) {
                    const p = parseFloat(usd) || 0;
                    if (this.priceDisplay === 'NGN' && this.usdToNgnRate > 0) {
                        let totalNgn = p * this.usdToNgnRate + (this.nairaMarginAmount || 0);
                        totalNgn *= (1 + (this.nairaMarginPercent || 0) / 100);
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
                    if (this.showCountry) {
                        this.loadCountries();
                        if (this.serverType !== 'smsconfirmed') this.loadPools();
                    } else {
                        this.countryCode = 'US';
                        this.loadServices();
                    }
                },
                selectCountry(c) {
                    this.countryCode = c.code;
                    this.countrySearch = c.name || c.code;
                    this.countryOpen = false;
                    this.poolId = '';
                    this.loadServices();
                    this.loadPrice();
                    this.loadPools();
                },
                selectServiceOther(s) {
                    this.serviceCode = s.code;
                    this.serviceSearch = s.name || s.code;
                    this.serviceOpen = false;
                    this.loadPrice();
                },
                async loadPrice() {
                    if (!this.showCountry || !this.countryCode || !this.serviceCode) {
                        this.priceNgn = 0;
                        this.priceUsd = 0;
                        this.successRate = 0;
                        return;
                    }
                    var country = this.countries.find(function(c) { return c.code === this.countryCode; }.bind(this));
                    var countryId = country && (country.provider_id || country.id) ? country.provider_id || country.id : null;
                    if (!countryId) return;
                    this.priceLoading = true;
                    this.priceNgn = 0;
                    this.priceUsd = 0;
                    this.successRate = 0;
                    try {
                        var params = new URLSearchParams({
                            server_id: this.serverId,
                            country_id: countryId,
                            service_code: this.serviceCode
                        });
                        if (this.poolId) params.set('pool_id', this.poolId);
                        var r = await fetch(this.priceUrl + '?' + params.toString(), { credentials: 'same-origin', headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' } });
                        var d = await r.json();
                        this.priceUsd = parseFloat(d.price_usd) || 0;
                        this.priceNgn = parseInt(d.price_ngn, 10) || 0;
                        this.successRate = parseInt(d.success_rate, 10) || 0;
                    } catch (e) {
                        this.priceNgn = 0;
                        this.priceUsd = 0;
                        this.successRate = 0;
                    } finally {
                        this.priceLoading = false;
                    }
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
                        if (!r.ok) {
                            this.countries = [];
                            return;
                        }
                        const d = await r.json();
                        const raw = d && (d.countries ?? d.data?.countries);
                        const list = Array.isArray(raw) ? raw : [];
                        this.countries = list.map(function (c) {
                            var code = (c && (c.code ?? c.short_name ?? c.iso ?? c.iso2 ?? c.id ?? c.ID)) != null ? String(c.code ?? c.short_name ?? c.iso ?? c.iso2 ?? c.id ?? c.ID) : '';
                            return {
                                code: code,
                                name: (c && (c.name ?? c.country ?? c.country_name)) || 'Unknown',
                                provider_id: (c && (c.provider_id ?? c.id ?? c.ID)) != null ? String(c.provider_id ?? c.id ?? c.ID) : code
                            };
                        }).filter(function (c) { return c.code !== ''; });
                    } catch (e) {
                        this.countries = [];
                    } finally {
                        this.countriesLoading = false;
                    }
                },
                async loadServices() {
                    const country = this.countryCode || 'US';
                    try {
                        const r = await fetch(this.servicesUrl + '?server_id=' + this.serverId + '&country_code=' + encodeURIComponent(country), { credentials: 'same-origin', headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' } });
                        const d = r.ok ? await r.json() : {};
                        this.services = Array.isArray(d.services) ? d.services : [];
                    } catch (e) {
                        this.services = [];
                    }
                    this.serviceCodeReady = true;
                    if (this.showCountry && this.countryCode && this.serviceCode) this.loadPrice();
                },
                async loadPools() {
                    this.poolsLoading = true;
                    this.pools = [];
                    try {
                        var url = this.poolsUrl + '?server_id=' + this.serverId;
                        if (this.serverType === 'smsconfirmed' && this.countryCode) {
                            var country = this.countries.find(function(c) { return c.code === this.countryCode; }.bind(this));
                            var countryId = country && (country.provider_id || country.id) ? (country.provider_id || country.id) : '';
                            if (countryId) url += '&country_id=' + encodeURIComponent(countryId);
                        }
                        var r = await fetch(url, { credentials: 'same-origin', headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' } });
                        var d = await r.json();
                        this.pools = Array.isArray(d.pools) ? d.pools : [];
                        var self = this;
                        this.$nextTick(function() {
                            var sel = self.$refs.poolSelect;
                            if (sel) {
                                while (sel.options.length > 1) sel.remove(1);
                                self.pools.forEach(function(p) { sel.add(new Option(p.name, p.id)); });
                            }
                        });
                    } catch (e) {
                        this.pools = [];
                    } finally {
                        this.poolsLoading = false;
                    }
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
                        if (this.showCountry) {
                            form.append('country_code', this.countryCode);
                            const country = this.countries.find(c => c.code === this.countryCode);
                            const providerCountryId = country && (country.provider_id ?? country.id ?? country.ID) != null ? String(country.provider_id ?? country.id ?? country.ID) : '';
                            if (providerCountryId) form.append('country_id', providerCountryId);
                        }
                        if (this.showCountry && this.poolId) form.append('pool_id', this.poolId);
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
