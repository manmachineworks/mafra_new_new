@extends('backend.layouts.app')

@section('content')
    <style>
        :root{
            --primary: #c70a0a;
            --secondary: #212121;
            --border: #e9e9e9;
            --muted: #6b7280;
        }

        .settings-page{
            background: var(--bg);
        }

        .settings-title{
            color: var(--secondary);
            font-weight: 800;
            letter-spacing: .2px;
            margin: 0;
        }

        .settings-subtitle{
            color: var(--muted);
            margin: .25rem 0 0;
            font-size: .92rem;
        }

        .settings-section{
            margin-top: 1.75rem;
        }

        .section-header{
            display:flex;
            align-items:flex-end;
            justify-content:space-between;
            gap: 1rem;
            margin-bottom: .75rem;
        }

        .section-badge{
            color: var(--primary);
            font-weight: 700;
            font-size: .85rem;
        }

        .setting-card{
            border: 1px solid var(--border);
            border-radius: 14px;
            background: var(--bg);
            height: 100%;
            transition: transform .12s ease, box-shadow .12s ease, border-color .12s ease;
            overflow: hidden;
        }

        .setting-card:hover{
            transform: translateY(-1px);
            box-shadow: 0 10px 30px rgba(0,0,0,.06);
        }

        .setting-card.active{
            border-color: rgba(199,10,10,.35);
            box-shadow: 0 10px 30px rgba(199,10,10,.08);
        }

        .setting-card .card-header{
            background: #fff;
            border-bottom: 1px solid var(--border);
            padding: 14px 16px;
        }

        .setting-card .card-body{
            padding: 14px 16px;
        }

        .setting-row{
            display:flex;
            align-items:flex-start;
            justify-content:space-between;
            gap: 12px;
            cursor: pointer;
            user-select: none;
        }

        .setting-name{
            color: var(--secondary);
            font-weight: 800;
            font-size: 1rem;
            margin: 0;
            line-height: 1.3;
        }

        .setting-desc{
            color: var(--muted);
            margin: .35rem 0 0;
            font-size: .9rem;
            line-height: 1.35;
        }

        .setting-meta{
            margin-top: 10px;
        }

        .settings-hint{
            border: 1px solid rgba(199,10,10,.18);
            background: rgba(199,10,10,.06);
            color: var(--secondary);
            padding: 10px 12px;
            border-radius: 10px;
            font-size: .9rem;
        }

        .settings-hint a{
            color: var(--primary);
            font-weight: 800;
            text-decoration: none;
        }
        .settings-hint a:hover{
            text-decoration: underline;
        }

        /* Make toggle match theme a bit more */
        .aiz-switch input:checked + .slider{
            background-color: var(--primary) !important;
        }

        /* Primary buttons/links look */
        .text-primary-theme{ color: var(--primary) !important; }
        .text-secondary-theme{ color: var(--secondary) !important; }
    </style>

    <div class="settings-page">
        {{-- PAGE HEADER --}}
        <div class="mb-3">
            <h3 class="settings-title">System & Business Settings</h3>
            <p class="settings-subtitle">Enable or disable features instantly. Changes take effect immediately.</p>
        </div>

        {{-- SECTION: SYSTEM --}}
        <div class="settings-section">
            <div class="section-header">
                <div>
                    <h4 class="settings-title" style="font-size:1.2rem;">System</h4>
                    <p class="settings-subtitle">Security, performance and operational controls.</p>
                </div>
            </div>

            <div class="row">
                {{-- HTTPS --}}
                <div class="col-lg-4 mb-3">
                    <div class="card setting-card js-card-FORCE_HTTPS">
                        <div class="card-header">
                            <div class="d-flex align-items-center justify-content-between">
                                <span class="text-secondary-theme font-weight-bold">HTTPS Activation</span>
                                <span class="text-primary-theme font-weight-bold">Security</span>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="setting-row" onclick="toggleSetting('FORCE_HTTPS')">
                                <div>
                                    <p class="setting-name mb-0">Force HTTPS</p>
                                    <p class="setting-desc">Redirect all traffic to secure HTTPS connections.</p>
                                </div>
                                <label class="aiz-switch aiz-switch-success mb-0">
                                    <input id="setting-FORCE_HTTPS" type="checkbox"
                                        onchange="updateSettings(this, 'FORCE_HTTPS')"
                                        <?php if (env('FORCE_HTTPS') == 'On') { echo 'checked'; } ?>>
                                    <span class="slider round"></span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Maintenance --}}
                <div class="col-lg-4 mb-3">
                    <div class="card setting-card js-card-maintenance_mode">
                        <div class="card-header">
                            <div class="d-flex align-items-center justify-content-between">
                                <span class="text-secondary-theme font-weight-bold">Maintenance Mode</span>
                                <span class="text-primary-theme font-weight-bold">Ops</span>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="setting-row" onclick="toggleSetting('maintenance_mode')">
                                <div>
                                    <p class="setting-name mb-0">Maintenance Mode</p>
                                    <p class="setting-desc">Temporarily disable the storefront for updates.</p>
                                </div>
                                <label class="aiz-switch aiz-switch-success mb-0">
                                    <input id="setting-maintenance_mode" type="checkbox"
                                        onchange="updateSettings(this, 'maintenance_mode')"
                                        <?php if (get_setting('maintenance_mode') == 1) { echo 'checked'; } ?>>
                                    <span class="slider round"></span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Image Optimization --}}
                <div class="col-lg-4 mb-3">
                    <div class="card setting-card js-card-disable_image_optimization">
                        <div class="card-header">
                            <div class="d-flex align-items-center justify-content-between">
                                <span class="text-secondary-theme font-weight-bold">Images</span>
                                <span class="text-primary-theme font-weight-bold">Performance</span>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="setting-row" onclick="toggleSetting('disable_image_optimization')">
                                <div>
                                    <p class="setting-name mb-0">Disable Image Encoding</p>
                                    <p class="setting-desc">Turn off image optimization (may increase load time).</p>
                                </div>
                                <label class="aiz-switch aiz-switch-success mb-0">
                                    <input id="setting-disable_image_optimization" type="checkbox"
                                        onchange="updateSettings(this, 'disable_image_optimization')"
                                        <?php if (get_setting('disable_image_optimization') == 1) { echo 'checked'; } ?>>
                                    <span class="slider round"></span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- SECTION: BUSINESS --}}
        <div class="settings-section">
            <div class="section-header">
                <div>
                    <h4 class="settings-title" style="font-size:1.2rem;">Business Related</h4>
                    <p class="settings-subtitle">Payments, customer experience, and storefront features.</p>
                </div>
            </div>

            <div class="row">
                {{-- Wallet --}}
                <div class="col-lg-4 mb-3">
                    <div class="card setting-card js-card-wallet_system">
                        <div class="card-header">
                            <span class="text-secondary-theme font-weight-bold">Wallet System</span>
                        </div>
                        <div class="card-body">
                            <div class="setting-row" onclick="toggleSetting('wallet_system')">
                                <div>
                                    <p class="setting-name mb-0">Wallet Activation</p>
                                    <p class="setting-desc">Allow customers to use wallet balance for purchases.</p>
                                </div>
                                <label class="aiz-switch aiz-switch-success mb-0">
                                    <input id="setting-wallet_system" type="checkbox"
                                        onchange="updateSettings(this, 'wallet_system')"
                                        <?php if (get_setting('wallet_system') == 1) { echo 'checked'; } ?>>
                                    <span class="slider round"></span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Coupon --}}
                <div class="col-lg-4 mb-3">
                    <div class="card setting-card js-card-coupon_system">
                        <div class="card-header">
                            <span class="text-secondary-theme font-weight-bold">Coupons</span>
                        </div>
                        <div class="card-body">
                            <div class="setting-row" onclick="toggleSetting('coupon_system')">
                                <div>
                                    <p class="setting-name mb-0">Coupon System</p>
                                    <p class="setting-desc">Enable discounts using coupon codes at checkout.</p>
                                </div>
                                <label class="aiz-switch aiz-switch-success mb-0">
                                    <input id="setting-coupon_system" type="checkbox"
                                        onchange="updateSettings(this, 'coupon_system')"
                                        <?php if (get_setting('coupon_system') == 1) { echo 'checked'; } ?>>
                                    <span class="slider round"></span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Conversation --}}
                <div class="col-lg-4 mb-3">
                    <div class="card setting-card js-card-conversation_system">
                        <div class="card-header">
                            <span class="text-secondary-theme font-weight-bold">Conversation</span>
                        </div>
                        <div class="card-body">
                            <div class="setting-row" onclick="toggleSetting('conversation_system')">
                                <div>
                                    <p class="setting-name mb-0">Conversation Activation</p>
                                    <p class="setting-desc">Enable buyer-seller messaging and inquiries.</p>
                                </div>
                                <label class="aiz-switch aiz-switch-success mb-0">
                                    <input id="setting-conversation_system" type="checkbox"
                                        onchange="updateSettings(this, 'conversation_system')"
                                        <?php if (get_setting('conversation_system') == 1) { echo 'checked'; } ?>>
                                    <span class="slider round"></span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Email Verification --}}
                <div class="col-lg-4 mb-3">
                    <div class="card setting-card js-card-email_verification">
                        <div class="card-header">
                            <span class="text-secondary-theme font-weight-bold">Accounts</span>
                        </div>
                        <div class="card-body">
                            <div class="setting-row" onclick="toggleSetting('email_verification')">
                                <div>
                                    <p class="setting-name mb-0">Email Verification</p>
                                    <p class="setting-desc">Require users to verify email to activate accounts.</p>
                                </div>
                                <label class="aiz-switch aiz-switch-success mb-0">
                                    <input id="setting-email_verification" type="checkbox"
                                        onchange="updateSettings(this, 'email_verification')"
                                        <?php if (get_setting('email_verification') == 1) { echo 'checked'; } ?>>
                                    <span class="slider round"></span>
                                </label>
                            </div>

                            <div class="setting-meta">
                                <div class="settings-hint">
                                    Configure SMTP to ensure emails are delivered.
                                    <a href="{{ route('smtp_settings.index') }}">{{ translate('Configure Now') }}</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Product Query --}}
                <div class="col-lg-4 mb-3">
                    <div class="card setting-card js-card-product_query_activation">
                        <div class="card-header">
                            <span class="text-secondary-theme font-weight-bold">Products</span>
                        </div>
                        <div class="card-body">
                            <div class="setting-row" onclick="toggleSetting('product_query_activation')">
                                <div>
                                    <p class="setting-name mb-0">Product Query</p>
                                    <p class="setting-desc">Allow customers to ask questions on product pages.</p>
                                </div>
                                <label class="aiz-switch aiz-switch-success mb-0">
                                    <input id="setting-product_query_activation" type="checkbox"
                                        onchange="updateSettings(this, 'product_query_activation')"
                                        <?php if (get_setting('product_query_activation') == 1) { echo 'checked'; } ?>>
                                    <span class="slider round"></span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Last Viewed --}}
                <div class="col-lg-4 mb-3">
                    <div class="card setting-card js-card-last_viewed_product_activation">
                        <div class="card-header">
                            <span class="text-secondary-theme font-weight-bold">Personalization</span>
                        </div>
                        <div class="card-body">
                            <div class="setting-row" onclick="toggleSetting('last_viewed_product_activation')">
                                <div>
                                    <p class="setting-name mb-0">Last Viewed Products</p>
                                    <p class="setting-desc">Show recently viewed products to improve conversions.</p>
                                </div>
                                <label class="aiz-switch aiz-switch-success mb-0">
                                    <input id="setting-last_viewed_product_activation" type="checkbox"
                                        onchange="updateSettings(this, 'last_viewed_product_activation')"
                                        <?php if (get_setting('last_viewed_product_activation') == 1) { echo 'checked'; } ?>>
                                    <span class="slider round"></span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Newsletter --}}
                <div class="col-lg-4 mb-3">
                    <div class="card setting-card js-card-newsletter_activation">
                        <div class="card-header">
                            <span class="text-secondary-theme font-weight-bold">Marketing</span>
                        </div>
                        <div class="card-body">
                            <div class="setting-row" onclick="toggleSetting('newsletter_activation')">
                                <div>
                                    <p class="setting-name mb-0">Newsletter</p>
                                    <p class="setting-desc">Enable newsletter signup and marketing campaigns.</p>
                                </div>
                                <label class="aiz-switch aiz-switch-success mb-0">
                                    <input id="setting-newsletter_activation" type="checkbox"
                                        onchange="updateSettings(this, 'newsletter_activation')"
                                        <?php if (get_setting('newsletter_activation') == 1) { echo 'checked'; } ?>>
                                    <span class="slider round"></span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Guest Checkout --}}
                <div class="col-lg-4 mb-3">
                    <div class="card setting-card js-card-guest_checkout_activation">
                        <div class="card-header">
                            <span class="text-secondary-theme font-weight-bold">Checkout</span>
                        </div>
                        <div class="card-body">
                            <div class="setting-row" onclick="toggleSetting('guest_checkout_activation')">
                                <div>
                                    <p class="setting-name mb-0">Guest Checkout</p>
                                    <p class="setting-desc">Allow customers to place orders without registration.</p>
                                </div>
                                <label class="aiz-switch aiz-switch-success mb-0">
                                    <input id="setting-guest_checkout_activation" type="checkbox"
                                        onchange="updateSettings(this, 'guest_checkout_activation')"
                                        <?php if (get_setting('guest_checkout_activation') == 1) { echo 'checked'; } ?>>
                                    <span class="slider round"></span>
                                </label>
                            </div>

                            <div class="setting-meta">
                                <div class="settings-hint">
                                    SMTP is recommended for sending checkout emails reliably.
                                    <a href="{{ route('smtp_settings.index') }}">{{ translate('Configure Now') }}</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Customer Registration Verification --}}
                <div class="col-lg-4 mb-3">
                    <div class="card setting-card js-card-customer_registration_verify">
                        <div class="card-header">
                            <span class="text-secondary-theme font-weight-bold">Verification</span>
                        </div>
                        <div class="card-body">
                            <div class="setting-row" onclick="toggleSetting('customer_registration_verify')">
                                <div>
                                    <p class="setting-name mb-0">Customer Registration Verification</p>
                                    <p class="setting-desc">Add an extra verification step during registration.</p>
                                </div>
                                <label class="aiz-switch aiz-switch-success mb-0">
                                    <input id="setting-customer_registration_verify" type="checkbox"
                                        onchange="updateSettings(this, 'customer_registration_verify')"
                                        <?php if (get_setting('customer_registration_verify') == 1) { echo 'checked'; } ?>>
                                    <span class="slider round"></span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Addons --}}
                @if (addon_is_activated('wholesale'))
                    <div class="col-lg-4 mb-3">
                        <div class="card setting-card js-card-seller_wholesale_product">
                            <div class="card-header">
                                <span class="text-secondary-theme font-weight-bold">Wholesale (Addon)</span>
                            </div>
                            <div class="card-body">
                                <div class="setting-row" onclick="toggleSetting('seller_wholesale_product')">
                                    <div>
                                        <p class="setting-name mb-0">Wholesale Products for Seller</p>
                                        <p class="setting-desc">Allow sellers to add wholesale pricing tiers.</p>
                                    </div>
                                    <label class="aiz-switch aiz-switch-success mb-0">
                                        <input id="setting-seller_wholesale_product" type="checkbox"
                                            onchange="updateSettings(this, 'seller_wholesale_product')"
                                            <?php if (get_setting('seller_wholesale_product') == 1) { echo 'checked'; } ?>>
                                        <span class="slider round"></span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                @if (addon_is_activated('auction'))
                    <div class="col-lg-4 mb-3">
                        <div class="card setting-card js-card-seller_auction_product">
                            <div class="card-header">
                                <span class="text-secondary-theme font-weight-bold">Auction (Addon)</span>
                            </div>
                            <div class="card-body">
                                <div class="setting-row" onclick="toggleSetting('seller_auction_product')">
                                    <div>
                                        <p class="setting-name mb-0">Auction Products for Seller</p>
                                        <p class="setting-desc">Enable sellers to list products as auctions.</p>
                                    </div>
                                    <label class="aiz-switch aiz-switch-success mb-0">
                                        <input id="setting-seller_auction_product" type="checkbox"
                                            onchange="updateSettings(this, 'seller_auction_product')"
                                            <?php if (get_setting('seller_auction_product') == 1) { echo 'checked'; } ?>>
                                        <span class="slider round"></span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        {{-- SECTION: SOCIAL LOGIN --}}
        <div class="settings-section">
            <div class="section-header">
                <div>
                    <h4 class="settings-title" style="font-size:1.2rem;">Social Media Login</h4>
                    <p class="settings-subtitle">Let customers sign in faster using social providers.</p>
                </div>
            </div>

            <div class="row">
                @php
                    $social = [
                        ['key'=>'facebook_login','name'=>'Facebook login','hint'=>'Configure Facebook Client','route'=>route('social_login.index')],
                        ['key'=>'google_login','name'=>'Google login','hint'=>'Configure Google Client','route'=>route('social_login.index')],
                        ['key'=>'twitter_login','name'=>'Twitter login','hint'=>'Configure Twitter Client','route'=>route('social_login.index')],
                        ['key'=>'apple_login','name'=>'Apple login','hint'=>'Configure Apple Client','route'=>route('social_login.index')],
                    ];
                @endphp

                @foreach($social as $s)
                    <div class="col-lg-4 mb-3">
                        <div class="card setting-card js-card-{{ $s['key'] }}">
                            <div class="card-header">
                                <span class="text-secondary-theme font-weight-bold">{{ translate($s['name']) }}</span>
                            </div>
                            <div class="card-body">
                                <div class="setting-row" onclick="toggleSetting('{{ $s['key'] }}')">
                                    <div>
                                        <p class="setting-name mb-0">{{ translate($s['name']) }}</p>
                                        <p class="setting-desc">Enable {{ strtolower($s['name']) }} for quicker access.</p>
                                    </div>
                                    <label class="aiz-switch aiz-switch-success mb-0">
                                        <input id="setting-{{ $s['key'] }}" type="checkbox"
                                            onchange="updateSettings(this, '{{ $s['key'] }}')"
                                            <?php if (get_setting($s['key']) == 1) { echo 'checked'; } ?>>
                                        <span class="slider round"></span>
                                    </label>
                                </div>

                                <div class="setting-meta">
                                    <div class="settings-hint">
                                        {{ translate('You need to configure') }} {{ translate($s['hint']) }} {{ translate('to enable this feature') }}.
                                        <a href="{{ $s['route'] }}">{{ translate('Configure Now') }}</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script type="text/javascript">
        function toggleSetting(type){
            const el = document.getElementById('setting-' + type);
            if(!el) return;
            el.checked = !el.checked;
            // Trigger change so your updateSettings() runs
            el.dispatchEvent(new Event('change'));
        }

        function syncActiveCards(){
            document.querySelectorAll('[id^="setting-"]').forEach((el)=>{
                const type = el.id.replace('setting-','');
                const card = document.querySelector('.js-card-' + CSS.escape(type));
                if(card){
                    card.classList.toggle('active', el.checked);
                }
            });
        }

        document.addEventListener('DOMContentLoaded', function(){
            syncActiveCards();
            document.querySelectorAll('[id^="setting-"]').forEach((el)=>{
                el.addEventListener('change', syncActiveCards);
            });
        });

        function updateSettings(el, type) {

            if('{{ env('DEMO_MODE') }}' == 'On'){
                AIZ.plugins.notify('info', '{{ translate('Data can not change in demo mode.') }}');
                syncActiveCards();
                return;
            }

            var value = $(el).is(':checked') ? 1 : 0;

            $.post('{{ route('business_settings.update.activation') }}', {
                _token: '{{ csrf_token() }}',
                type: type,
                value: value
            }, function(data) {
                if (data == 1) {
                    AIZ.plugins.notify('success', '{{ translate('Settings updated successfully') }}');
                } else {
                    AIZ.plugins.notify('danger', 'Something went wrong');
                }
            });
        }
    </script>
@endsection
