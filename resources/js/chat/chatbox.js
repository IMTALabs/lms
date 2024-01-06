import { fetchEventSource } from "@microsoft/fetch-event-source";
import MarkdownIt from "markdown-it";
import hljs from "highlight.js";
import "highlight.js/styles/panda-syntax-dark.css";

(async function () {
    const md = MarkdownIt();

    // Inject the CSS
    const style = document.createElement("style");
    style.innerHTML = `
        @import url("https://fonts.googleapis.com/css2?family=Inter:wght@100;200;300;400;500;600;700;800;900&family=JetBrains+Mono:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800&display=swap");
        .imta-hidden {
            display: none;
        }
        .chat-bubble {
            position: fixed;
            bottom: 0;
            right: 0;
            flex-direction: column;
            z-index: 9999;
        }
        #chat-popup {
            font-family: "Inter", sans-serif;
            transition: all 0.3s;
            overflow: hidden;
            z-index: 9999;
            position: fixed;
            bottom: 5rem;
            right: 0.5rem;
            height: 80vh;
        }
        @media (max-width: 768px) {
            #chat-popup {
                height: 100vh;
                max-height: 100vh;
                transition: all 0.3s;
                overflow: hidden;
                z-index: 9999;
            }
        }
        #chat-submit:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
        .imta-reply {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        .imta-reply pre {
            border-radius: 10px;
            font-family: "JetBrains Mono", monospace;
            font-weight: 600;
            font-size: 12px;
        }
        .imta-reply code:not([class]) {
            background-color: #f3f4f6;
            padding: 2px 4px;
            border-radius: 4px;
            font-size: 12px;
            border: 1px solid #d5d5d5;
            color: #f46d6d;
        }
        .imta-reply ul,
        .imta-reply ol {
            padding-left: 20px;
            margin: 10px 0;
        }
        .imta-reply ul > li {
            list-style-type: disc;
        }
        .imta-reply ol > li {
            list-style-type: decimal;
        }
        .theme1 {
            background-color: #185b48;
            color: white;
            transition: all 0.2s ease-in-out;
        }
        .theme2 {
            background-color: black;
            color: white;
            transition: all 0.2s ease-in-out;
        }
        .theme3 {
            background-color: #622222;
            color: white;
            transition: all 0.2s ease-in-out;
        }
        .scale-up-br {
            -webkit-animation: scale-up-br 0.4s cubic-bezier(0.39, 0.575, 0.565, 1) both;
            animation: scale-up-br 0.4s cubic-bezier(0.39, 0.575, 0.565, 1) both;
        }
        @keyframes scale-up-br {
            0% {
                -webkit-transform: scale(0.5);
                transform: scale(0.5);
                -webkit-transform-origin: 100% 100%;
                transform-origin: 100% 100%;
            }
            100% {
                -webkit-transform: scale(1);
                transform: scale(1);
                -webkit-transform-origin: 100% 100%;
                transform-origin: 100% 100%;
            }
        }
        .scale-up-tl {
            -webkit-animation: scale-up-tl 0.4s cubic-bezier(0.39, 0.575, 0.565, 1) both;
            animation: scale-up-tl 0.4s cubic-bezier(0.39, 0.575, 0.565, 1) both;
        }
        @keyframes scale-up-tl {
            0% {
                -webkit-transform: scale(0.5);
                transform: scale(0.5);
                -webkit-transform-origin: 0% 0%;
                transform-origin: 0% 0%;
            }
            100% {
                -webkit-transform: scale(1);
                transform: scale(1);
                -webkit-transform-origin: 0% 0%;
                transform-origin: 0% 0%;
            }
        }
    `;

    document.head.appendChild(style);

    // Create chat widget container
    const chatWidgetContainer = document.createElement("div");
    chatWidgetContainer.id = "chat-widget-container";
    document.body.appendChild(chatWidgetContainer);

    // Inject the HTML
    chatWidgetContainer.innerHTML = `
        <div id="chat-bubble" class="imta-w-16 imta-h-16 imta-fixed imta-bottom-2 imta-right-2 imta-bg-gray-800 imta-rounded-full imta-flex imta-items-center imta-justify-center imta-cursor-pointer imta-text-3xl">
            <svg xmlns="http://www.w3.org/2000/svg" class="imta-w-10 imta-h-10 imta-text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
            </svg>
        </div>
        <div id="chat-popup" class="imta-hidden scale-up-br imta-w-full imta-max-w-xl imta-fixed imta-bottom-0 imta-right-0 imta-max-h-full imta-bg-white imta-rounded-md imta-shadow-md imta-flex imta-flex-col imta-transition-all imta-text-sm">
            <div id="chat-header" class="imta-flex imta-justify-between imta-items-center imta-py-2 imta-px-4 imta-bg-gray-800 imta-text-white ">
                <h3 class="imta-m-0 imta-text-lg imta-leading-none imta-flex imta-gap-1">
                    <svg class="-imta-translate-y-0.5" width="20" height="20" viewBox="0 0 24 24">
                        <path fill="currentColor" d="M10.5 15.5c0 .37-.1.7-.28 1c-.34-.59-.98-1-1.72-1s-1.38.41-1.72 1c-.17-.3-.28-.63-.28-1c0-1.1.9-2 2-2s2 .9 2 2M23 15v3c0 .55-.45 1-1 1h-1v1c0 1.11-.89 2-2 2H5a2 2 0 0 1-2-2v-1H2c-.55 0-1-.45-1-1v-3c0-.55.45-1 1-1h1c0-3.87 3.13-7 7-7h1V5.73c-.6-.34-1-.99-1-1.73c0-1.1.9-2 2-2s2 .9 2 2c0 .74-.4 1.39-1 1.73V7h1c3.87 0 7 3.13 7 7h1c.55 0 1 .45 1 1m-2 1h-2v-2c0-2.76-2.24-5-5-5h-4c-2.76 0-5 2.24-5 5v2H3v1h2v3h14v-3h2zm-5.5-2.5c-1.1 0-2 .9-2 2c0 .37.11.7.28 1c.34-.59.98-1 1.72-1s1.38.41 1.72 1c.18-.3.28-.63.28-1a2 2 0 0 0-2-2" />
                    </svg>
                    <span class="imta-font-semibold imta-text-sm">ImtaBot</span>
                </h3>
                <button id="close-popup" class="imta-bg-transparent imta-border-none imta-text-white imta-cursor-pointer">
                    <svg xmlns="http://www.w3.org/2000/svg" class="imta-h-5 imta-w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <div id="imta-chat-content" class="imta-overflow-y-auto imta-flex-1">
                <div class="imta-mt-4">
                    <div class="imta-flex imta-justify-center imta-items-center imta-gap-2 ">
                        <svg class="-imta-translate-y-0.5" width="50" height="50" viewBox="0 0 24 24">
                            <path fill="currentColor" d="M10.5 15.5c0 .37-.1.7-.28 1c-.34-.59-.98-1-1.72-1s-1.38.41-1.72 1c-.17-.3-.28-.63-.28-1c0-1.1.9-2 2-2s2 .9 2 2M23 15v3c0 .55-.45 1-1 1h-1v1c0 1.11-.89 2-2 2H5a2 2 0 0 1-2-2v-1H2c-.55 0-1-.45-1-1v-3c0-.55.45-1 1-1h1c0-3.87 3.13-7 7-7h1V5.73c-.6-.34-1-.99-1-1.73c0-1.1.9-2 2-2s2 .9 2 2c0 .74-.4 1.39-1 1.73V7h1c3.87 0 7 3.13 7 7h1c.55 0 1 .45 1 1m-2 1h-2v-2c0-2.76-2.24-5-5-5h-4c-2.76 0-5 2.24-5 5v2H3v1h2v3h14v-3h2zm-5.5-2.5c-1.1 0-2 .9-2 2c0 .37.11.7.28 1c.34-.59.98-1 1.72-1s1.38.41 1.72 1c.18-.3.28-.63.28-1a2 2 0 0 0-2-2" />
                        </svg>
                        <h1 class="imta-font-bold imta-text-2xl">ImtaBot</h1>
                    </div>
                </div>
                <div class="imta-flex imta-flex-col imta-items-center">
                    <p class="imta-text-center imta-p-2">Chọn kiểu hội thoại</p>
                    <ul class="imta-flex imta-justify-around imta-items-center imta-mb-4 imta-p-1 imta-border-[1px] imta-rounded-md imta-border-gray-300 imta-font-semibold imta-py-1">
                        <li id="li__style">
                            <button class="button__style imta-py-2 imta-px-4 imta-rounded-md imta-p-2 imta-flex-1 imta-inline button_active" id="theme1">Sáng tạo</button>
                        </li>
                        <li id="li__style">
                            <button class="button__style imta-p-2 imta-flex-1 imta-px-4 imta-rounded-md imta-p-2 imta-inline" id="theme2">Cân bằng</button>
                        </li>
                        <li id="li__style">
                            <button class="button__style imta-p-2 imta-flex-1 imta-px-4 imta-rounded-md imta-p-2 imta-inline" id="theme3">Chính xác</button>
                        </li>
                    </ul>
                </div>
                <div id="chat-messages" class="imta-flex-1 imta-p-3 imta-text-wrap">
    
                </div>
            </div>
            <div id="chat-input-container" class="imta-p-4 imta-border-t imta-border-gray-200">
                <div class="imta-flex imta-space-x-2 imta-items-center imta-justify-end imta-w-full ">
                    <div class="imta-more_chat imta-flex imta-items-center imta-bg-gray-800 imta-p-2 imta-rounded-full">
                        <button class="imta-w-6 imta-h-6 imta-flex imta-justify-center imta-items-center" id="new-chat">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="imta-w-5 imta-h-5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                            </svg>
                        </button>
                    </div>
                    <textarea type="text" id="chat-input" class="focus:imta-grow imta-ease-linear imta-resize-none imta-border imta-border-gray-300 imta-rounded-md imta-px-2 imta-py-2 imta-transition-all imta-outline-none imta-w-5/6" placeholder="Type your message..."></textarea>
                    <div class="imta-text-center imta-flex imta-flex-col imta-justify-center">
                        <p id="rangeText" class="imta-pb-1 imta-text-xs">
                        <p>
                            <button id="chat-submit" class="imta-bg-gray-800 imta-text-white imta-mb-1 imta-rounded-md imta-p-2 imta-cursor-pointer">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="imta-w-6 imta-h-6">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 12 3.269 3.125A59.769 59.769 0 0 1 21.485 12 59.768 59.768 0 0 1 3.27 20.875L5.999 12Zm0 0h7.5" />
                                </svg>
                            </button>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    `;

    // Add event listeners
    const chatInput = document.getElementById("chat-input");
    const chatSubmit = document.getElementById("chat-submit");
    const chatMessages = document.getElementById("chat-messages");
    const chatBubble = document.getElementById("chat-bubble");
    const newChatBtn = document.getElementById("new-chat");
    const closePopup = document.getElementById("close-popup");
    const moreChatDiv = document.querySelector(".imta-more_chat");
    const button__style = document.querySelectorAll(".button__style");
    const rangeText = document.querySelector("#rangeText");
    const chatHeader = document.querySelector("#chat-header");
    const chatMessage = document.querySelectorAll("#imta-chat-message");
    chatInput.addEventListener("focus", function () {
        moreChatDiv.style.display = "none";
    });
    const styles = ["theme1", "theme2", "theme3"];
    const defaultStyle = localStorage.getItem("chat_style") || "theme2";
    changeChatStyle(defaultStyle);

    function changeChatStyle(style) {
        localStorage.setItem("chat_style", style);
        button__style.forEach((btn) => {
            btn.classList.remove(...styles);
        });
        Array.from(document.querySelectorAll(".imta-send")).forEach((ele) => {
            ele.classList.remove(...styles);
            ele.classList.add(style);
        });
        chatSubmit.classList.remove(...styles);
        chatSubmit.classList.add(style);
        moreChatDiv.classList.remove(...styles);
        moreChatDiv.classList.add(style);
        document.querySelector(`#${ style }`).classList.add(style);
        chatMessage.forEach((ele) => {
            ele.classList.remove(...styles);
        });

        chatHeader.classList.remove(...styles);

        chatMessage.forEach((ele) => {
            ele.classList.add(style);
        });

        chatHeader.classList.add(style);
    }

    button__style.forEach((button, index) => {
        const style = styles[index];

        document.addEventListener("DOMContentLoaded", function () {
            button.addEventListener("click", (e) => {
                changeChatStyle(style);
            });
        });
    });

    let maxCharacter = 1000;

    const len = chatInput.value.length;
    chatInput.addEventListener("input", (e) => {
        let len = e.target.value.length;
        if (len > maxCharacter) {
            e.target.value = e.target.value.slice(0, maxCharacter);
            len = maxCharacter;
        }
        rangeText.innerHTML = `${ len }/${ maxCharacter }`;
    });
    rangeText.innerHTML = `${ len }/${ maxCharacter }`;

    chatInput.addEventListener("blur", function () {
        moreChatDiv.style.display = "flex";
    });

    chatSubmit.addEventListener("click", async function () {

        const message = chatInput.value.trim();
        if (!message) return;

        chatMessages.scrollTop = chatMessages.scrollHeight;

        chatInput.value = "";

        await onUserRequest(message);

    });

    chatInput.addEventListener("keyup", function (event) {
        if (event.key === "Enter" && !event.shiftKey) {
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

    newChatBtn.addEventListener("click", function () {
        if (window.ctrl) {
            window.ctrl.abort();
        }
        document.getElementById("chat-input").toggleAttribute("disabled", true);
        document.getElementById("chat-submit").toggleAttribute("disabled", true);
        chatMessages.innerHTML = `
            <div class="imta-flex imta-justify-center imta-items-center imta-gap-1">
                Đang tạo mới hội thoại
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"><circle cx="18" cy="12" r="0" fill="currentColor"><animate attributeName="r" begin=".67" calcMode="spline" dur="1.5s" keySplines="0.2 0.2 0.4 0.8;0.2 0.2 0.4 0.8;0.2 0.2 0.4 0.8" repeatCount="indefinite" values="0;2;0;0"/></circle><circle cx="12" cy="12" r="0" fill="currentColor"><animate attributeName="r" begin=".33" calcMode="spline" dur="1.5s" keySplines="0.2 0.2 0.4 0.8;0.2 0.2 0.4 0.8;0.2 0.2 0.4 0.8" repeatCount="indefinite" values="0;2;0;0"/></circle><circle cx="6" cy="12" r="0" fill="currentColor"><animate attributeName="r" begin="0" calcMode="spline" dur="1.5s" keySplines="0.2 0.2 0.4 0.8;0.2 0.2 0.4 0.8;0.2 0.2 0.4 0.8" repeatCount="indefinite" values="0;2;0;0"/></circle></svg>
            </div>
        `;

        fetch(`${ appUrl }api/chatbot/new`, {
            method: "POST",
            headers: {
                "x-api-key": "IMTATEST",
                "x-csrf-token": document.querySelector("meta[name='csrf-token']").getAttribute("content"),
                "content-type": "application/json"
            },
        }).then(response => response.json()).then((data) => {
            // ...
        }).finally(() => {
            chatMessages.innerHTML = "";
            document.getElementById("chat-input").toggleAttribute("disabled", false);
            document.getElementById("chat-input").focus();
            document.getElementById("chat-submit").toggleAttribute("disabled", false);
        });
    });

    async function onUserRequest(message) {
        // Handle user request here
        console.log("User request:", message);

        // Display user message
        const messageElement = document.createElement("div");
        messageElement.className = "imta-flex imta-justify-end imta-mb-3";
        const currentStyle = localStorage.getItem("chat_style") || "theme2";
        messageElement.innerHTML = `
            <div class="imta-send imta-bg-gray-800 imta-text-white imta-rounded-lg imta-py-2 imta-px-4 imta-max-w-[70%] ${currentStyle}">
                ${ message.split("\n").join("<br>") }
            </div>
        `;
        chatMessages.appendChild(messageElement);
        chatMessages.scrollTop = chatMessages.scrollHeight;

        chatInput.value = "";
        rangeText.innerHTML = `0/${ maxCharacter }`;

        // Reply to the user
        setTimeout(async function () {
            document.getElementById("chat-input").toggleAttribute("disabled", true);
            document.getElementById("chat-submit").toggleAttribute("disabled", true);
            await reply(message);
        }, 100);
    }

    async function reply(message) {
        const chatMessages = document.getElementById("chat-messages");
        const chatContent = document.getElementById("imta-chat-content");
        const replyElement = document.createElement("div");
        const id = `chat-reply-${ Math.random().toString(36).substr(2, 9) }`;
        let raw = "";
        replyElement.className = "imta-flex imta-mb-3";
        replyElement.innerHTML = `
            <div class="imta-reply imta-bg-gray-200 imta-text-black imta-rounded-lg imta-py-2 imta-px-4 imta-max-w-[70%]"
                 id="${ id }">
                <div class="imta-flex imta-items-center imta-gap-1">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"><circle cx="18" cy="12" r="0" fill="currentColor"><animate attributeName="r" begin=".67" calcMode="spline" dur="1.5s" keySplines="0.2 0.2 0.4 0.8;0.2 0.2 0.4 0.8;0.2 0.2 0.4 0.8" repeatCount="indefinite" values="0;2;0;0"/></circle><circle cx="12" cy="12" r="0" fill="currentColor"><animate attributeName="r" begin=".33" calcMode="spline" dur="1.5s" keySplines="0.2 0.2 0.4 0.8;0.2 0.2 0.4 0.8;0.2 0.2 0.4 0.8" repeatCount="indefinite" values="0;2;0;0"/></circle><circle cx="6" cy="12" r="0" fill="currentColor"><animate attributeName="r" begin="0" calcMode="spline" dur="1.5s" keySplines="0.2 0.2 0.4 0.8;0.2 0.2 0.4 0.8;0.2 0.2 0.4 0.8" repeatCount="indefinite" values="0;2;0;0"/></circle></svg>
                </div>
            </div>
        `;
        chatMessages.appendChild(replyElement);
        chatContent.scrollTop = chatContent.scrollHeight;

        window.ctrl = new AbortController();
        await fetchEventSource(`${ appUrl }api/chatbot/chat`, {
            method: "POST",
            headers: {
                "x-api-key": "IMTATEST",
                "x-csrf-token": document.querySelector("meta[name='csrf-token']").getAttribute("content"),
                "content-type": "application/json"
            },
            body: JSON.stringify({
                query: message
            }),
            onerror() {
                document.getElementById(id).innerHTML = `Đã có lỗi xảy ra. Vui lòng thử lại sau.`;
                document.getElementById("chat-input").toggleAttribute("disabled", false);
                document.getElementById("chat-input").focus();
                document.getElementById("chat-submit").toggleAttribute("disabled", false);
            },
            onmessage(event) {
                if (event.data.trim() === "<END_STREAM_SSE>") {
                    document.getElementById("chat-input").toggleAttribute("disabled", false);
                    document.getElementById("chat-input").focus();
                    document.getElementById("chat-submit").toggleAttribute("disabled", false);
                    return;
                }
                event.data = event.data === "" ? "\n" : event.data;
                raw += `${ event.data.replace(/\{.*?\}/, "") }`;
                document.getElementById(id).innerHTML = md.render(raw);
                document.getElementById(id).querySelectorAll("pre code").forEach((el) => {
                    hljs.highlightElement(el);
                });
                chatContent.scrollTop = chatContent.scrollHeight;
            },
            signal: ctrl.signal,
        });
    }
})();