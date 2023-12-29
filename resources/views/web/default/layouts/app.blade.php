<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">

@php
    $rtlLanguages = !empty($generalSettings['rtl_languages']) ? $generalSettings['rtl_languages'] : [];

    $isRtl = ((in_array(mb_strtoupper(app()->getLocale()), $rtlLanguages)) or (!empty($generalSettings['rtl_layout']) and $generalSettings['rtl_layout'] == 1));
@endphp

<head>
    @include('web.default.includes.metas')
    <title>{{ $pageTitle ?? '' }}{{ !empty($generalSettings['site_name']) ? (' | '.$generalSettings['site_name']) : '' }}</title>

    <!-- General CSS File -->
    <link rel="stylesheet" href="/assets/default/vendors/sweetalert2/dist/sweetalert2.min.css">
    <link rel="stylesheet" href="/assets/default/vendors/toast/jquery.toast.min.css">
    <link rel="stylesheet" href="/assets/default/vendors/simplebar/simplebar.css">
    <link rel="stylesheet" href="/assets/default/css/app.css">
    <link rel="stylesheet" href="/vendor/basement/basement.bundle.min.css">

    @if($isRtl)
        <link rel="stylesheet" href="/assets/default/css/rtl-app.css">
    @endif

    @stack('styles_top')
    @stack('scripts_top')

    <style>
        {!! !empty(getCustomCssAndJs('css')) ? getCustomCssAndJs('css') : '' !!}

        {!! getThemeFontsSettings() !!}

        {!! getThemeColorsSettings() !!}
    </style>


    @if(!empty($generalSettings['preloading']) and $generalSettings['preloading'] == '1')
        @include('admin.includes.preloading')
    @endif
</head>

<body class="@if($isRtl) rtl @endif">

    <div id="app"
         class="{{ (!empty($floatingBar) and $floatingBar->position == 'top' and $floatingBar->fixed) ? 'has-fixed-top-floating-bar' : '' }}">
        @if(!empty($floatingBar) and $floatingBar->position == 'top')
            @include('web.default.includes.floating_bar')
        @endif

        @if(!isset($appHeader))
            @include('web.default.includes.top_nav')
            @include('web.default.includes.navbar')
        @endif

        @if(!empty($justMobileApp))
            @include('web.default.includes.mobile_app_top_nav')
        @endif

        @yield('content')

        @if(!isset($appFooter))
            @include('web.default.includes.footer')
        @endif

        @include('web.default.includes.advertise_modal.index')

        @if(!empty($floatingBar) and $floatingBar->position == 'bottom')
            @include('web.default.includes.floating_bar')
        @endif
    </div>
    <!-- Template JS File -->
    <script src="/assets/default/js/app.js"></script>
    <script src="/assets/default/vendors/feather-icons/dist/feather.min.js"></script>
    <script src="/assets/default/vendors/moment.min.js"></script>
    <script src="/assets/default/vendors/sweetalert2/dist/sweetalert2.min.js"></script>
    <script src="/assets/default/vendors/toast/jquery.toast.min.js"></script>
    <script type="text/javascript" src="/assets/default/vendors/simplebar/simplebar.min.js"></script>
    @if(Auth::check())
        <script src="https://cdn.tailwindcss.com"></script>
        <script>
            tailwind.config = {
                prefix: "imta-"
            };
        </script>
        <script type="module">
            import {
                Remarkable
            } from "https://cdn.skypack.dev/remarkable";

            const md = new Remarkable();

            (function () {
                // Inject the CSS
                const style = document.createElement("style");
                style.innerHTML = `
                @import url('https://fonts.googleapis.com/css2?family=Inter:wght@100;200;300;400;500;600;700;800;900&display=swap');
                .imta-hidden {
                    display: none;
                }
                #chat-widget-container {
                    position: fixed;
                    bottom: 20px;
                    right: 20px;
                    flex-direction: column;
                    z-index: 9999;
                    font-family: 'Inter', sans-serif;
                }
                #chat-popup {
                    height: 70vh;
                    max-height: 70vh;
                    transition: all 0.3s;
                    overflow: hidden;
                    z-index: 9999;
                }
                @media (max-width: 768px) {
                    #chat-popup {
                        position: fixed;
                        top: 0;
                        right: 0;
                        bottom: 0;
                        left: 0;
                        width: 100%;
                        height: 100%;
                        max-height: 100%;
                        border-radius: 0;
                        z-index: 9999;
                    }
                }
                #chat-submit:disabled {
                    opacity: 0.5;
                    cursor: not-allowed;
                }
                `;

                document.head.appendChild(style);

                // Create chat widget container
                const chatWidgetContainer = document.createElement("div");
                chatWidgetContainer.id = "chat-widget-container";
                document.body.appendChild(chatWidgetContainer);

                // Inject the HTML
                chatWidgetContainer.innerHTML = `
                <div id="chat-bubble" class="imta-w-16 imta-h-16 imta-bg-gray-800 imta-rounded-full imta-flex imta-items-center imta-justify-center imta-cursor-pointer imta-text-3xl">
                  <svg xmlns="http://www.w3.org/2000/svg" class="imta-w-10 imta-h-10 imta-text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                  </svg>
                </div>
                <div id="chat-popup" class="imta-hidden imta-absolute imta-bottom-20 imta-right-0 imta-w-96 imta-bg-white imta-rounded-md imta-shadow-md imta-flex imta-flex-col imta-transition-all imta-text-sm">
                  <div id="chat-header" class="imta-flex imta-justify-between imta-items-center imta-p-4 imta-bg-gray-800 imta-text-white imta-rounded-t-md">
                    <h3 class="imta-m-0 imta-text-lg">Chatbot</h3>
                    <button id="close-popup" class="imta-bg-transparent imta-border-none imta-text-white imta-cursor-pointer">
                      <svg xmlns="http://www.w3.org/2000/svg" class="imta-h-6 imta-w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                      </svg>
                    </button>
                  </div>
                  <div id="chat-messages" class="imta-flex-1 imta-p-4 imta-overflow-y-auto"></div>
                  <div id="chat-input-container" class="imta-p-4 imta-border-t imta-border-gray-200">
                    <div class="imta-flex imta-space-x-4 imta-items-center">
                      <input type="text" id="chat-input" class="imta-flex-1 imta-border imta-border-gray-300 imta-rounded-md imta-px-4 imta-py-2 imta-outline-none imta-w-3/4" placeholder="Type your message...">
                      <button id="chat-submit" class="imta-bg-gray-800 imta-text-white imta-rounded-md imta-px-4 imta-py-2 imta-cursor-pointer">Send</button>
                    </div>
                  </div>
                </div>
            `;

                // Add event listeners
                const chatInput = document.getElementById("chat-input");
                const chatSubmit = document.getElementById("chat-submit");
                const chatMessages = document.getElementById("chat-messages");
                const chatBubble = document.getElementById("chat-bubble");
                const chatPopup = document.getElementById("chat-popup");
                const closePopup = document.getElementById("close-popup");

                chatSubmit.addEventListener("click", function () {

                    const message = chatInput.value.trim();
                    if (!message) return;

                    chatMessages.scrollTop = chatMessages.scrollHeight;

                    chatInput.value = "";

                    onUserRequest(message);

                });

                chatInput.addEventListener("keyup", function (event) {
                    if (event.key === "Enter") {
                        chatSubmit.click();
                    }
                });

                chatBubble.addEventListener("click", function () {
                    togglePopup();
                });

                closePopup.addEventListener("click", function () {
                    togglePopup();
                });

                function togglePopup() {
                    const chatPopup = document.getElementById("chat-popup");
                    chatPopup.classList.toggle("imta-hidden");
                    if (!chatPopup.classList.contains("imta-hidden")) {
                        document.getElementById("chat-input").focus();
                    }
                }

                function onUserRequest(message) {
                    // Handle user request here
                    console.log("User request:", message);

                    // Display user message
                    const messageElement = document.createElement("div");
                    messageElement.className = "imta-flex imta-justify-end imta-mb-3";
                    messageElement.innerHTML = `
                  <div class="imta-bg-gray-800 imta-text-white imta-rounded-lg imta-py-2 imta-px-4 imta-max-w-[70%]">
                    ${ message }
                  </div>
                `;
                    chatMessages.appendChild(messageElement);
                    chatMessages.scrollTop = chatMessages.scrollHeight;

                    chatInput.value = "";

                    // Reply to the user
                    setTimeout(function () {
                        reply(message);
                        document.getElementById("chat-input").toggleAttribute("disabled", true);
                        document.getElementById("chat-submit").toggleAttribute("disabled", true);
                    }, 100);
                }

                function reply(message) {
                    const chatMessages = document.getElementById("chat-messages");
                    const replyElement = document.createElement("div");
                    const id = `chat-reply-${ Math.random().toString(36).substr(2, 9) }`;
                    replyElement.className = "imta-flex imta-mb-3";
                    replyElement.innerHTML = `
                        <div class="imta-bg-gray-200 imta-text-black imta-rounded-lg imta-py-2 imta-px-4 imta-max-w-[70%]"
                             id="${ id }">
                            <div class="imta-flex imta-items-center imta-gap-1">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"><circle cx="18" cy="12" r="0" fill="currentColor"><animate attributeName="r" begin=".67" calcMode="spline" dur="1.5s" keySplines="0.2 0.2 0.4 0.8;0.2 0.2 0.4 0.8;0.2 0.2 0.4 0.8" repeatCount="indefinite" values="0;2;0;0"/></circle><circle cx="12" cy="12" r="0" fill="currentColor"><animate attributeName="r" begin=".33" calcMode="spline" dur="1.5s" keySplines="0.2 0.2 0.4 0.8;0.2 0.2 0.4 0.8;0.2 0.2 0.4 0.8" repeatCount="indefinite" values="0;2;0;0"/></circle><circle cx="6" cy="12" r="0" fill="currentColor"><animate attributeName="r" begin="0" calcMode="spline" dur="1.5s" keySplines="0.2 0.2 0.4 0.8;0.2 0.2 0.4 0.8;0.2 0.2 0.4 0.8" repeatCount="indefinite" values="0;2;0;0"/></circle></svg>
                            </div>
                        </div>
                    `;
                    chatMessages.appendChild(replyElement);
                    chatMessages.scrollTop = chatMessages.scrollHeight;

                    const source = new EventSource(
                        `http:///34.16.32.114:9000/chat?user_id={{ Auth::id() }}&session_id=1&mode=course&query=${ message }`
                    );

                    let raw = "";
                    source.onmessage = (event) => {
                        if (event.data.trim() === "<END_STREAM_SSE>") {
                            source.close();
                            document.getElementById("chat-input").toggleAttribute("disabled", false);
                            document.getElementById("chat-submit").toggleAttribute("disabled", false);
                            return;
                        }
                        raw += `${ event.data.replace(/\{.*?\}/, '')}`;
                        document.getElementById(id).innerHTML = md.render(raw);
                    };
                }
            })();
        </script>
    @endif

    @if(empty($justMobileApp) and checkShowCookieSecurityDialog())
        @include('web.default.includes.cookie-security')
    @endif


    <script>
        var deleteAlertTitle = '{{ trans('public.are_you_sure') }}';
        var deleteAlertHint = '{{ trans('public.deleteAlertHint') }}';
        var deleteAlertConfirm = '{{ trans('public.deleteAlertConfirm') }}';
        var deleteAlertCancel = '{{ trans('public.cancel') }}';
        var deleteAlertSuccess = '{{ trans('public.success') }}';
        var deleteAlertFail = '{{ trans('public.fail') }}';
        var deleteAlertFailHint = '{{ trans('public.deleteAlertFailHint') }}';
        var deleteAlertSuccessHint = '{{ trans('public.deleteAlertSuccessHint') }}';
        var forbiddenRequestToastTitleLang = '{{ trans('public.forbidden_request_toast_lang') }}';
        var forbiddenRequestToastMsgLang = '{{ trans('public.forbidden_request_toast_msg_lang') }}';
    </script>

    @if(session()->has('toast'))
        <script>
            (function () {
                "use strict";

                $.toast({
                    heading: '{{ session()->get('toast')['title'] ?? '' }}',
                    text: '{{ session()->get('toast')['msg'] ?? '' }}',
                    bgColor: '@if(session()->get('toast')['status'] == 'success') #43d477 @else #f63c3c @endif',
                    textColor: "white",
                    hideAfter: 10000,
                    position: "bottom-right",
                    icon: '{{ session()->get('toast')['status'] }}'
                });
            })(jQuery);
        </script>
    @endif

    @stack('styles_bottom')
    @stack('scripts_bottom')

    <script src="/assets/default/js/parts/main.min.js"></script>

    <script>
        @if(session()->has('registration_package_limited'))
        (function () {
            "use strict";

            handleLimitedAccountModal('{!! session()->get('registration_package_limited') !!}');
        })(jQuery);

        {{ session()->forget('registration_package_limited') }}
        @endif

        {!! !empty(getCustomCssAndJs('js')) ? getCustomCssAndJs('js') : '' !!}
    </script>
</body>
</html>
