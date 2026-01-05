<div class="card-panel mb-4 mt-4 shop-card">
    @if($product->user->user_type == 'seller')
        <div class="d-flex justify-content-between">
            <div class="mt-1 d-flex">
                <!-- Shop Logo -->
                <div>
                    <a href="{{ route('shop.visit', $product->user->shop->slug) }}"
                        class="avatar-md mr-2 overflow-hidden border h-40px rounded-circle shop-logo">
                        <img class="lazyload d-block mx-auto mh-100"
                            src="{{ static_asset('assets/img/placeholder.jpg') }}"
                            data-src="{{ uploaded_asset($product->user->shop->logo) }}"
                            onerror="this.onerror=null;this.src='{{ static_asset('assets/img/placeholder.jpg') }}';">
                    </a>
                </div>
                <!-- Shop Name & Verification status -->
                <div class="mt-1">
                    <p class="mb-1 fw-700 text-dark">{{ $product->user->shop->name }}</p>
                    <div class="text-muted small">
                            @if ($product->user->shop->verification_status == 1)
                            <span class="badge badge-success-soft text-success align-middle">{{translate('Verified seller')}}</span>
                            @else
                            <span class="badge badge-warning-soft text-warning align-middle">{{translate('Not verified')}}</span>
                            @endif
                    </div>
                </div>
            </div>
            <div class="d-flex align-items-center">
                @if(get_setting('product_query_activation') == 1)
                <button class="btn btn-sm btn-outline-brand px-3 rounded-pill"
                    onclick="show_conversation_modal({{ $product->id }})">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16"
                        class="mr-2 has-transition">
                        <g id="Group_23918" data-name="Group 23918" transform="translate(1053.151 256.688)">
                            <path id="Path_3012" data-name="Path 3012"
                                d="M134.849,88.312h-8a2,2,0,0,0-2,2v5a2,2,0,0,0,2,2v3l2.4-3h5.6a2,2,0,0,0,2-2v-5a2,2,0,0,0-2-2m1,7a1,1,0,0,1-1,1h-8a1,1,0,0,1-1-1v-5a1,1,0,0,1,1-1h8a1,1,0,0,1,1,1Z"
                                transform="translate(-1178 -341)"
                                fill="{{ get_setting('secondary_base_color', '#ffc519') }}" />
                            <path id="Path_3013" data-name="Path 3013"
                                d="M134.849,81.312h8a1,1,0,0,1,1,1v5a1,1,0,0,1-1,1h-.5a.5.5,0,0,0,0,1h.5a2,2,0,0,0,2-2v-5a2,2,0,0,0-2-2h-8a2,2,0,0,0-2,2v.5a.5.5,0,0,0,1,0v-.5a1,1,0,0,1,1-1"
                                transform="translate(-1182 -337)"
                                fill="{{ get_setting('secondary_base_color', '#ffc519') }}" />
                            <path id="Path_3014" data-name="Path 3014"
                                d="M131.349,93.312h5a.5.5,0,0,1,0,1h-5a.5.5,0,0,1,0-1" transform="translate(-1181 -343.5)"
                                fill="{{ get_setting('secondary_base_color', '#ffc519') }}" />
                            <path id="Path_3015" data-name="Path 3015"
                                d="M131.349,99.312h5a.5.5,0,1,1,0,1h-5a.5.5,0,1,1,0-1" transform="translate(-1181 -346.5)"
                                fill="{{ get_setting('secondary_base_color', '#ffc519') }}" />
                        </g>
                    </svg>

                    {{ translate('Message Seller') }}
                </button>
                @endif
            </div>
        </div>
        <hr class="my-3">
        <div class="section-footer d-flex justify-content-between mb-0">
            <div>
                <a class="text-brand fw-700" href="#sellerDetails">{{translate('Seller Details')}}</a>
            </div>
            <div>
                <a class="text-brand fw-700" href="{{ route('shop.visit', $product->user->shop->slug) }}">{{translate('Visit Store')}}</a>
            </div>
        </div>
    @else
        <div class="d-flex align-items-center">
            <span class="px-3 fs-16 fw-700 text-dark">{{translate('In House Product')}}</span>

            @if(get_setting('product_query_activation') == 1)
            <button class="btn btn-sm btn-outline-brand px-3 rounded-pill ml-auto"
                onclick="show_conversation_modal({{ $product->id }})">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16"
                    class="mr-2 has-transition">
                    <g id="Group_23918" data-name="Group 23918" transform="translate(1053.151 256.688)">
                        <path id="Path_3012" data-name="Path 3012"
                            d="M134.849,88.312h-8a2,2,0,0,0-2,2v5a2,2,0,0,0,2,2v3l2.4-3h5.6a2,2,0,0,0,2-2v-5a2,2,0,0,0-2-2m1,7a1,1,0,0,1-1,1h-8a1,1,0,0,1-1-1v-5a1,1,0,0,1,1-1h8a1,1,0,0,1,1,1Z"
                            transform="translate(-1178 -341)"
                            fill="{{ get_setting('secondary_base_color', '#ffc519') }}" />
                        <path id="Path_3013" data-name="Path 3013"
                            d="M134.849,81.312h8a1,1,0,0,1,1,1v5a1,1,0,0,1-1,1h-.5a.5.5,0,0,0,0,1h.5a2,2,0,0,0,2-2v-5a2,2,0,0,0-2-2h-8a2,2,0,0,0-2,2v.5a.5.5,0,0,0,1,0v-.5a1,1,0,0,1,1-1"
                            transform="translate(-1182 -337)"
                            fill="{{ get_setting('secondary_base_color', '#ffc519') }}" />
                        <path id="Path_3014" data-name="Path 3014"
                            d="M131.349,93.312h5a.5.5,0,0,1,0,1h-5a.5.5,0,0,1,0-1" transform="translate(-1181 -343.5)"
                            fill="{{ get_setting('secondary_base_color', '#ffc519') }}" />
                        <path id="Path_3015" data-name="Path 3015"
                            d="M131.349,99.312h5a.5.5,0,1,1,0,1h-5a.5.5,0,1,1,0-1" transform="translate(-1181 -346.5)"
                            fill="{{ get_setting('secondary_base_color', '#ffc519') }}" />
                    </g>
                </svg>

                {{ translate('Message Seller') }}
            </button>
            @endif
        </div>
        
        <div class="row no-gutters mt-4">
            <div class="col-sm-3">
                <div class="text-secondary fs-14 fw-400 mt-2 mb-2">{{ translate('Share') }}</div>
            </div>
            <div class="col-sm-9">
                <div class="aiz-share"></div>
            </div>
        </div>

        
    @endif
</div>
