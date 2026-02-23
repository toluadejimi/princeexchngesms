<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-800 dark:text-slate-200 leading-tight">
            {{ __('Rent a Number') }}
        </h2>
    </x-slot>

    <div class="max-w-2xl mx-auto sm:px-6 lg:px-8" x-data="rentForm()">
        <div class="bg-white/80 dark:bg-slate-900/80 backdrop-blur rounded-2xl border border-slate-200 dark:border-slate-800 shadow-glass p-4 sm:p-6">
            <form @submit.prevent="submit">
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Region</label>
                        <select x-model="serverId" @change="onServerChange" class="w-full min-h-[48px] rounded-xl border-slate-300 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-200 shadow-sm focus:border-mint-500 focus:ring-mint-500 text-base" required>
                            <option value="">Select region</option>
                            @foreach($servers as $s)
                                <option value="{{ $s->id }}">{{ $s->display_name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div x-show="showCountry" x-cloak>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Country</label>
                        <select x-model="countryCode" @change="loadServices" class="w-full min-h-[48px] rounded-xl border-slate-300 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-200 shadow-sm focus:border-mint-500 focus:ring-mint-500 text-base">
                            <option value="">Select country</option>
                            <template x-for="c in countries" :key="c.code">
                                <option :value="c.code" x-text="c.name"></option>
                            </template>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Service</label>
                        <select x-model="serviceCode" :disabled="!serviceCodeReady" class="w-full min-h-[48px] rounded-xl border-slate-300 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-200 shadow-sm focus:border-mint-500 focus:ring-mint-500 text-base" required>
                            <option value="">Select service</option>
                            <template x-for="s in services" :key="s.code">
                                <option :value="s.code" x-text="s.name + ' — $' + (s.price || 0).toFixed(2)"></option>
                            </template>
                        </select>
                    </div>

                    <p x-show="error" x-text="error" class="text-sm text-red-600 dark:text-red-400"></p>
                    <p x-show="success" class="text-sm text-mint-600 dark:text-mint-400">Rented! Redirecting...</p>

                    <button type="submit" :disabled="loading" class="w-full min-h-[48px] inline-flex justify-center items-center px-4 py-3 rounded-xl bg-gradient-to-r from-mint-500 to-blue-500 text-white font-medium shadow-neon-mint hover:shadow-lg disabled:opacity-50 transition active:scale-[0.98]">
                        <span x-show="!loading">Rent Number</span>
                        <span x-show="loading">Please wait...</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function rentForm() {
            const servers = @json($servers->keyBy('id'));
            return {
                serverId: '',
                countryCode: '',
                serviceCode: '',
                services: [],
                countries: [],
                showCountry: false,
                serviceCodeReady: false,
                loading: false,
                error: '',
                success: false,
                get isUsaOnly() {
                    return this.serverId && servers[this.serverId] && servers[this.serverId].type === 'usa_only';
                },
                onServerChange() {
                    this.serviceCode = '';
                    this.services = [];
                    this.countries = [];
                    this.serviceCodeReady = false;
                    if (!this.serverId) { this.showCountry = false; return; }
                    const s = servers[this.serverId];
                    this.showCountry = s && s.type === 'multi_country';
                    if (this.showCountry) {
                        this.loadCountries();
                    } else {
                        this.countryCode = 'US';
                        this.loadServices();
                    }
                },
                async loadCountries() {
                    if (!this.serverId) return;
                    const r = await fetch(`{{ route('rentals.countries') }}?server_id=${this.serverId}`, { headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' } });
                    const d = await r.json();
                    this.countries = d.countries || [];
                    if (this.countries.length) this.countryCode = this.countries[0].code;
                    this.loadServices();
                },
                async loadServices() {
                    if (!this.serverId) return;
                    const country = this.countryCode || 'US';
                    const r = await fetch(`{{ route('rentals.services') }}?server_id=${this.serverId}&country_code=${country}`, { headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' } });
                    const d = await r.json();
                    this.services = d.services || [];
                    this.serviceCodeReady = true;
                },
                async submit() {
                    this.error = '';
                    this.loading = true;
                    try {
                        const form = new FormData();
                        form.append('_token', '{{ csrf_token() }}');
                        form.append('server_id', this.serverId);
                        form.append('service_code', this.serviceCode);
                        if (this.showCountry) form.append('country_code', this.countryCode);
                        const r = await fetch('{{ route("rentals.store") }}', { method: 'POST', body: form, headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' } });
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
