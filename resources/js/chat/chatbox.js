import { fetchEventSource } from "@microsoft/fetch-event-source";
import MarkdownIt from "markdown-it";
import hljs from "highlight.js";
import "highlight.js/styles/panda-syntax-dark.css";

(async function () {
    const md = MarkdownIt();

    // Inject the CSS
    const style = document.createElement("style");
    style.innerHTML = `
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@100;200;300;400;500;600;700;800;900&family=JetBrains+Mono:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800&display=swap');
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
        .imta-reply p {
            margin: 10px 0;
        }
        .imta-reply pre {
            border-radius: 10px;
            font-family: 'JetBrains Mono', monospace;
            font-weight: 600;
            font-size: 12px;
            margin: 10px 0;
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
        <div id="chat-popup" class="imta-hidden imta-absolute imta-bottom-20 imta-right-0 imta-w-[100rem] imta-max-w-2xl imta-bg-white imta-rounded-md imta-shadow-md imta-flex imta-flex-col imta-transition-all imta-text-sm">
            <div id="chat-header" class="imta-flex imta-justify-between imta-items-center imta-p-4 imta-bg-gray-800 imta-text-white imta-rounded-t-md">
                <h3 class="imta-m-0 imta-text-lg imta-leading-none imta-flex imta-gap-1">
                    <svg class="-imta-translate-y-0.5" width="20" height="20" viewBox="0 0 24 24"><path fill="currentColor" d="M10.5 15.5c0 .37-.1.7-.28 1c-.34-.59-.98-1-1.72-1s-1.38.41-1.72 1c-.17-.3-.28-.63-.28-1c0-1.1.9-2 2-2s2 .9 2 2M23 15v3c0 .55-.45 1-1 1h-1v1c0 1.11-.89 2-2 2H5a2 2 0 0 1-2-2v-1H2c-.55 0-1-.45-1-1v-3c0-.55.45-1 1-1h1c0-3.87 3.13-7 7-7h1V5.73c-.6-.34-1-.99-1-1.73c0-1.1.9-2 2-2s2 .9 2 2c0 .74-.4 1.39-1 1.73V7h1c3.87 0 7 3.13 7 7h1c.55 0 1 .45 1 1m-2 1h-2v-2c0-2.76-2.24-5-5-5h-4c-2.76 0-5 2.24-5 5v2H3v1h2v3h14v-3h2zm-5.5-2.5c-1.1 0-2 .9-2 2c0 .37.11.7.28 1c.34-.59.98-1 1.72-1s1.38.41 1.72 1c.18-.3.28-.63.28-1a2 2 0 0 0-2-2"/></svg>
                    <span class="imta-font-semibold">ImtaBot</span>
                </h3>
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
                    <button id="chat-submit" class="imta-bg-gray-800 imta-text-white imta-rounded-md imta-px-4 imta-py-2 imta-cursor-pointer">Gửi</button>
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

    chatSubmit.addEventListener("click", async function () {

        const message = chatInput.value.trim();
        if (!message) return;

        chatMessages.scrollTop = chatMessages.scrollHeight;

        chatInput.value = "";

        await onUserRequest(message);

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

    async function onUserRequest(message) {
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
        setTimeout(async function () {
            document.getElementById("chat-input").toggleAttribute("disabled", true);
            document.getElementById("chat-submit").toggleAttribute("disabled", true);
            await reply(message);
        }, 100);
    }

    async function reply(message) {
        const chatMessages = document.getElementById("chat-messages");
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
        chatMessages.scrollTop = chatMessages.scrollHeight;

        await fetchEventSource(`${ appUrl }api/chatbot/chat`, {
            method: "POST",
            headers: {
                "x-api-key": "IMTATEST",
                "x-csrf-token": document.querySelector("meta[name='csrf-token']").getAttribute("content"),
                "content-type": "application/json"
            },
            body: JSON.stringify({
                query: message,
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
                chatMessages.scrollTop = chatMessages.scrollHeight;
            }
        });
    }
})();