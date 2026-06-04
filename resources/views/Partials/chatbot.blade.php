<div id="chatbot-widget">
    <button id="chatbot-toggle" type="button" title="Chatbot Bantuan">
        <i class="bi bi-chat-dots-fill"></i>
    </button>

    <div id="chatbot-panel">
        <div class="chatbot-header">
            <div class="chatbot-header-info">
                <div class="chatbot-title">Asisten SIAPABAJA</div>
                <div class="chatbot-subtitle">Bantuan sistem & arsip PBJ</div>
            </div>

            <button id="chatbot-close" type="button" aria-label="Tutup chatbot">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <div id="chatbot-messages">
            <div class="chatbot-message bot">
                Halo, selamat datang di SIAPABAJA. Silakan pilih topik bantuan atau ketik pertanyaan Anda.
            </div>

            <div class="chatbot-quick-buttons">
                @if(Auth::check())
                    @if(Auth::user()->role === 'super_admin')
                        <!-- Tombol khusus Super Admin -->
                        <button type="button" data-question="Jelaskan menu aplikasi">Menu Aplikasi</button>
                        <button type="button" data-question="Jelaskan hak akses super admin">Hak Akses Super Admin</button>
                        <button type="button" data-question="Bagaimana cara tambah akun Unit/PPK?">Tambah Akun</button>
                        <button type="button" data-question="Bagaimana cara kelola menu?">Kelola Menu</button>
                    @elseif(Auth::user()->role === 'ppk')
                        <!-- Tombol khusus PPK -->
                        <button type="button" data-question="Jelaskan menu aplikasi">Menu Aplikasi</button>
                        <button type="button" data-question="Jelaskan hak akses PPK">Hak Akses PPK</button>
                        <button type="button" data-question="Bagaimana cara upload dokumen pengadaan?">Upload Dokumen</button>
                        <button type="button" data-question="Saya lupa password">Lupa Password</button>
                    @elseif(Auth::user()->role === 'unit')
                        <!-- Tombol khusus Unit -->
                        <button type="button" data-question="Jelaskan menu aplikasi">Menu Aplikasi</button>
                        <button type="button" data-question="Jelaskan hak akses unit">Hak Akses Unit</button>
                        <button type="button" data-question="Bagaimana cara upload dokumen pengadaan?">Upload Dokumen</button>
                        <button type="button" data-question="Saya lupa password">Lupa Password</button>
                    @else
                        <!-- Fallback jika role lain yang tidak dikenal -->
                        <button type="button" data-question="Apa itu SIAPABAJA?">Apa itu SIAPABAJA?</button>
                        <button type="button" data-question="Jelaskan menu aplikasi">Menu Aplikasi</button>
                        <button type="button" data-question="Jelaskan hak akses user">Hak Akses User</button>
                        <button type="button" data-question="Bagaimana cara mencari arsip publik?">Pencarian Arsip Publik</button>
                    @endif
                @else
                    <!-- Tombol khusus untuk Pengunjung Publik / Belum Login -->
                    <button type="button" data-question="Apa itu SIAPABAJA?">Apa itu SIAPABAJA?</button>
                    <button type="button" data-question="Jelaskan menu aplikasi">Menu Aplikasi</button>
                    <button type="button" data-question="Jelaskan hak akses user">Hak Akses User</button>
                    <button type="button" data-question="Bagaimana cara mencari arsip publik?">Pencarian Arsip Publik</button>
                @endif
            </div>
        </div>

        <div class="chatbot-input-area">
            <input id="chatbot-input" type="text" placeholder="Tulis pertanyaan..." autocomplete="off">
            <button id="chatbot-send" type="button">
                <i class="bi bi-send-fill"></i>
            </button>
        </div>
    </div>
</div>

<style>
    :root {
        --chatbot-blue: #184f61;
        --chatbot-blue-dark: #123f4e;
        --chatbot-yellow: #f6c100;
        --chatbot-yellow-soft: #fff8d8;
        --chatbot-border: #e5e7eb;
        --chatbot-text: #1f2937;
        --chatbot-muted: #64748b;
    }

    #chatbot-widget,
    #chatbot-widget * {
        box-sizing: border-box;
    }

    #chatbot-widget {
        position: fixed;
        right: 48px;
        bottom: 48px;
        z-index: 99999;
        width: 60px;
        height: 60px;
        font-family: "Nunito", Arial, sans-serif;
    }

    #chatbot-toggle {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        border: 3px solid var(--chatbot-yellow);
        background: linear-gradient(135deg, var(--chatbot-blue), var(--chatbot-blue-dark));
        color: #ffffff;
        cursor: grab;
        box-shadow: 0 16px 34px rgba(15, 23, 42, 0.28);
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 0;
        outline: none;
        transition: transform .25s ease, box-shadow .25s ease;
    }

    #chatbot-toggle i {
        font-size: 26px;
        line-height: 1;
        display: block;
        pointer-events: none;
    }

    #chatbot-toggle:hover {
        transform: translateY(-3px) scale(1.04);
        box-shadow: 0 20px 42px rgba(15, 23, 42, 0.34);
    }

    #chatbot-toggle:active {
        transform: scale(.97);
    }

    #chatbot-panel {
        position: absolute;
        width: 360px;
        height: 460px;
        min-width: 320px;
        min-height: 390px;
        max-width: calc(100vw - 48px);
        max-height: calc(100vh - 48px);
        background: #ffffff;
        border-radius: 22px;
        border: 1px solid rgba(24, 79, 97, 0.16);
        box-shadow: 0 24px 54px rgba(15, 23, 42, 0.30);
        overflow: hidden;
        resize: both;
        display: flex;
        flex-direction: column;
        opacity: 0;
        visibility: hidden;
        pointer-events: none;
        transform: translateY(14px) scale(.965);
        transform-origin: bottom right;
        transition: opacity .28s ease, transform .28s ease, visibility .28s ease;
    }

    #chatbot-panel.is-open {
        opacity: 1;
        visibility: visible;
        pointer-events: auto;
        transform: translateY(0) scale(1);
    }

    #chatbot-widget.is-dragging #chatbot-panel {
        transition: none;
    }

    .chatbot-header {
        flex: 0 0 auto;
        background: linear-gradient(135deg, var(--chatbot-blue), var(--chatbot-blue-dark));
        color: #ffffff;
        padding: 15px 16px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-bottom: 4px solid var(--chatbot-yellow);
        gap: 12px;
    }

    .chatbot-header-info {
        min-width: 0;
    }

    .chatbot-title {
        font-size: 15px;
        font-weight: 800;
        letter-spacing: .2px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .chatbot-subtitle {
        font-size: 11px;
        opacity: .9;
        margin-top: 2px;
        font-weight: 500;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    #chatbot-close {
        width: 33px;
        height: 33px;
        border-radius: 50%;
        background: rgba(255, 255, 255, .14);
        border: 1px solid rgba(255, 255, 255, .24);
        color: #ffffff;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 0;
        flex: 0 0 auto;
        transition: background .24s ease, transform .28s ease;
    }

    #chatbot-close:hover {
        background: rgba(255, 255, 255, .24);
        transform: rotate(90deg);
    }

    #chatbot-close i {
        font-size: 13px;
        line-height: 1;
        pointer-events: none;
    }

    #chatbot-messages {
        flex: 1 1 auto;
        min-height: 0;
        padding: 18px 18px 14px;
        overflow-y: auto;
        overflow-x: hidden;
        background:
            radial-gradient(circle at top left, rgba(246, 193, 0, .13), transparent 34%),
            linear-gradient(180deg, #f8fafc, #ffffff);
    }

    #chatbot-messages::after {
        content: "";
        display: block;
        clear: both;
    }

    #chatbot-messages::-webkit-scrollbar {
        width: 7px;
    }

    #chatbot-messages::-webkit-scrollbar-thumb {
        background: rgba(24, 79, 97, .25);
        border-radius: 999px;
    }

    .chatbot-message {
        max-width: 78%;
        padding: 11px 13px;
        margin-bottom: 12px;
        border-radius: 16px;
        font-size: 13px;
        line-height: 1.5;
        word-break: normal;
        overflow-wrap: anywhere;
        white-space: normal;
        clear: both;
        animation: chatbotBubbleIn .25s ease both;
    }

    .chatbot-message.bot {
        background: #ffffff;
        color: var(--chatbot-text);
        border: 1px solid var(--chatbot-border);
        float: left;
        border-bottom-left-radius: 7px;
        box-shadow: 0 8px 18px rgba(15, 23, 42, .06);
    }

    .chatbot-message.user {
        background: linear-gradient(135deg, var(--chatbot-blue), var(--chatbot-blue-dark));
        color: #ffffff;
        float: right;
        border-bottom-right-radius: 7px;
        box-shadow: 0 9px 18px rgba(24, 79, 97, .22);
    }

    .chatbot-quick-buttons {
        clear: both;
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-top: 14px;
        padding: 2px 0 8px;
    }

    .chatbot-quick-buttons button {
        border: 1px solid rgba(246, 193, 0, .72);
        background: var(--chatbot-yellow-soft);
        color: var(--chatbot-blue);
        border-radius: 999px;
        padding: 8px 12px;
        font-size: 12px;
        cursor: pointer;
        font-weight: 700;
        font-family: inherit;
        max-width: 100%;
        transition: background .24s ease, transform .24s ease, box-shadow .24s ease;
    }

    .chatbot-quick-buttons button:hover {
        background: var(--chatbot-yellow);
        transform: translateY(-2px);
        box-shadow: 0 10px 18px rgba(246, 193, 0, .20);
    }

    .chatbot-input-area {
        flex: 0 0 auto;
        min-height: 72px;
        border-top: 1px solid var(--chatbot-border);
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 13px;
        background: #ffffff;
    }

    #chatbot-input {
        flex: 1;
        min-width: 0;
        border: 1px solid #d9e2e7;
        border-radius: 999px;
        padding: 11px 14px;
        font-size: 13px;
        outline: none;
        font-family: inherit;
        color: var(--chatbot-text);
        background: #f8fafc;
        transition: border .24s ease, box-shadow .24s ease, background .24s ease;
    }

    #chatbot-input:focus {
        border-color: var(--chatbot-blue);
        background: #ffffff;
        box-shadow: 0 0 0 4px rgba(24, 79, 97, .1);
    }

    #chatbot-send {
        width: 43px;
        height: 43px;
        border: none;
        border-radius: 50%;
        background: var(--chatbot-yellow);
        color: var(--chatbot-blue);
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 0;
        flex: 0 0 auto;
        box-shadow: 0 9px 18px rgba(246, 193, 0, .30);
        transition: transform .24s ease, box-shadow .24s ease;
    }

    #chatbot-send:hover {
        transform: translateY(-2px);
        box-shadow: 0 13px 24px rgba(246, 193, 0, .36);
    }

    #chatbot-send i {
        font-size: 15px;
        line-height: 1;
        pointer-events: none;
    }

    .chatbot-typing {
        clear: both;
        float: left;
        background: #ffffff;
        border: 1px solid var(--chatbot-border);
        border-radius: 999px;
        padding: 9px 12px;
        margin-bottom: 12px;
        font-size: 12px;
        color: var(--chatbot-muted);
        box-shadow: 0 8px 18px rgba(15, 23, 42, .06);
        animation: chatbotBubbleIn .25s ease both;
    }

    .chatbot-typing::after {
        content: " •";
        animation: chatbotDots 1s infinite;
    }

    @keyframes chatbotBubbleIn {
        from {
            opacity: 0;
            transform: translateY(9px) scale(.98);
        }
        to {
            opacity: 1;
            transform: translateY(0) scale(1);
        }
    }

    @keyframes chatbotDots {
        0% { content: " •"; }
        33% { content: " • •"; }
        66% { content: " • • •"; }
        100% { content: " •"; }
    }

    @media (max-width: 480px) {
        #chatbot-widget {
            right: 32px;
            bottom: 36px;
        }

        #chatbot-panel {
            width: min(330px, calc(100vw - 36px));
            height: 430px;
            min-width: min(300px, calc(100vw - 36px));
            max-width: calc(100vw - 36px);
            max-height: calc(100vh - 36px);
        }

        .chatbot-message {
            max-width: 84%;
        }
    }
</style>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        const widget = document.getElementById("chatbot-widget");
        const toggle = document.getElementById("chatbot-toggle");
        const panel = document.getElementById("chatbot-panel");
        const closeBtn = document.getElementById("chatbot-close");
        const input = document.getElementById("chatbot-input");
        const sendBtn = document.getElementById("chatbot-send");
        const messages = document.getElementById("chatbot-messages");
        const defaultMessagesHTML = messages.innerHTML;

        let isDragging = false;
        let moved = false;
        let suppressClick = false;
        let offsetX = 0;
        let offsetY = 0;
        let startX = 0;
        let startY = 0;
        let animationFrame = null;

        let panelHorizontal = "left";
        let panelVertical = "above";

        const safeMargin = 32;
        const panelGap = 16;
        const dragThreshold = 8;

        const savedMessages = sessionStorage.getItem("chatbotMessages");

        if (savedMessages) {
            messages.innerHTML = savedMessages;
        }

        function saveMessages() {
            sessionStorage.setItem("chatbotMessages", messages.innerHTML);
        }

        function scrollToBottom() {
            messages.scrollTop = messages.scrollHeight;
        }

        function addMessage(text, sender) {
            const div = document.createElement("div");
            div.className = "chatbot-message " + sender;
            div.textContent = text;
            messages.appendChild(div);
            scrollToBottom();
            saveMessages();
        }

        const chatUrl = "{{ Auth::check() ? route('chatbot.ask') : route('chatbot.public') }}";
        const csrfToken = "{{ csrf_token() }}";

        async function showTypingAndReply(userText) {
            const typing = document.createElement("div");
            typing.className = "chatbot-typing";
            typing.textContent = "Asisten sedang mengetik";
            messages.appendChild(typing);
            scrollToBottom();

            try {
                const response = await fetch(chatUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({ query: userText })
                });

                const data = await response.json();
                typing.remove();

                if (response.ok && data.success) {
                    addMessage(data.answer, "bot");
                } else {
                    addMessage(data.answer || "Maaf, terjadi kesalahan pada server chatbot.", "bot");
                }
            } catch (error) {
                console.error("Chatbot Error:", error);
                typing.remove();
                addMessage("Maaf, koneksi ke server chatbot terputus.", "bot");
            }
        }

        function sendMessage(text = null) {
            const userText = text || input.value.trim();
            if (!userText) return;

            addMessage(userText, "user");
            input.value = "";
            showTypingAndReply(userText);
        }

        function setDefaultPosition() {
            widget.style.left = "auto";
            widget.style.top = "auto";
            widget.style.right = "48px";
            widget.style.bottom = "48px";
        }

        function clampWidgetPosition() {
            const rect = widget.getBoundingClientRect();

            let left = rect.left;
            let top = rect.top;

            left = Math.max(safeMargin, Math.min(left, window.innerWidth - widget.offsetWidth - safeMargin));
            top = Math.max(safeMargin, Math.min(top, window.innerHeight - widget.offsetHeight - safeMargin));

            widget.style.left = left + "px";
            widget.style.top = top + "px";
            widget.style.right = "auto";
            widget.style.bottom = "auto";
        }

        function getPanelRectByPosition(widgetRect, panelWidth, panelHeight, horizontal, vertical) {
            const left = horizontal === "right"
                ? widgetRect.left
                : widgetRect.right - panelWidth;

            const top = vertical === "below"
                ? widgetRect.bottom + panelGap
                : widgetRect.top - panelHeight - panelGap;

            return {
                left: left,
                right: left + panelWidth,
                top: top,
                bottom: top + panelHeight
            };
        }

        function chooseHorizontalPosition(widgetRect, panelWidth, panelHeight) {
            let rect = getPanelRectByPosition(widgetRect, panelWidth, panelHeight, panelHorizontal, panelVertical);

            if (rect.left < 0) {
                panelHorizontal = "right";
            } else if (rect.right > window.innerWidth) {
                panelHorizontal = "left";
            }
        }

        function chooseVerticalPosition(widgetRect, panelWidth, panelHeight) {
            const aboveRect = getPanelRectByPosition(widgetRect, panelWidth, panelHeight, panelHorizontal, "above");
            const belowRect = getPanelRectByPosition(widgetRect, panelWidth, panelHeight, panelHorizontal, "below");

            const aboveFits = aboveRect.top >= 0;
            const belowFits = belowRect.bottom <= window.innerHeight;

            if (panelVertical === "above" && !aboveFits && belowFits) {
                panelVertical = "below";
                return;
            }

            if (panelVertical === "below" && !belowFits && aboveFits) {
                panelVertical = "above";
                return;
            }

            if (!aboveFits && !belowFits) {
                return;
            }

            if (aboveFits && belowFits) {
                return;
            }
        }

        function updatePanelPosition() {
            const widgetRect = widget.getBoundingClientRect();
            const panelWidth = panel.offsetWidth || 360;
            const panelHeight = panel.offsetHeight || 460;

            chooseHorizontalPosition(widgetRect, panelWidth, panelHeight);
            chooseVerticalPosition(widgetRect, panelWidth, panelHeight);

            panel.style.left = "auto";
            panel.style.right = "auto";
            panel.style.top = "auto";
            panel.style.bottom = "auto";

            if (panelHorizontal === "right") {
                panel.style.left = "0px";
                panel.style.transformOrigin = panelVertical === "below" ? "top left" : "bottom left";
            } else {
                panel.style.right = "0px";
                panel.style.transformOrigin = panelVertical === "below" ? "top right" : "bottom right";
            }

            if (panelVertical === "below") {
                panel.style.top = (widget.offsetHeight + panelGap) + "px";
            } else {
                panel.style.bottom = (widget.offsetHeight + panelGap) + "px";
            }
        }

        function requestPanelUpdate() {
            if (animationFrame) {
                cancelAnimationFrame(animationFrame);
            }

            animationFrame = requestAnimationFrame(function () {
                updatePanelPosition();
            });
        }

        toggle.addEventListener("mousedown", function (e) {
            isDragging = true;
            moved = false;
            suppressClick = false;

            startX = e.clientX;
            startY = e.clientY;

            offsetX = e.clientX - widget.getBoundingClientRect().left;
            offsetY = e.clientY - widget.getBoundingClientRect().top;

            widget.classList.add("is-dragging");
            toggle.style.cursor = "grabbing";
        });

        document.addEventListener("mousemove", function (e) {
            if (!isDragging) return;

            const distance = Math.abs(e.clientX - startX) + Math.abs(e.clientY - startY);

            if (distance < dragThreshold) return;

            moved = true;

            let left = e.clientX - offsetX;
            let top = e.clientY - offsetY;

            left = Math.max(safeMargin, Math.min(left, window.innerWidth - widget.offsetWidth - safeMargin));
            top = Math.max(safeMargin, Math.min(top, window.innerHeight - widget.offsetHeight - safeMargin));

            widget.style.left = left + "px";
            widget.style.top = top + "px";
            widget.style.right = "auto";
            widget.style.bottom = "auto";

            requestPanelUpdate();
        });

        document.addEventListener("mouseup", function () {
            if (!isDragging) return;

            isDragging = false;
            widget.classList.remove("is-dragging");
            toggle.style.cursor = "grab";

            if (moved) {
                suppressClick = true;
                setTimeout(function () {
                    suppressClick = false;
                    moved = false;
                }, 120);
            }
        });

        toggle.addEventListener("click", function () {
            if (suppressClick) return;

            clampWidgetPosition();
            updatePanelPosition();

            panel.classList.toggle("is-open");

            setTimeout(function () {
                updatePanelPosition();
                scrollToBottom();
            }, 40);
        });

        closeBtn.addEventListener("click", function () {
            panel.classList.remove("is-open");
            setTimeout(function() {
                sessionStorage.removeItem("chatbotMessages");
                messages.innerHTML = defaultMessagesHTML;
            }, 300);
        });

        sendBtn.addEventListener("click", function () {
            sendMessage();
        });

        input.addEventListener("keydown", function (e) {
            if (e.key === "Enter") {
                sendMessage();
            }
        });

        messages.addEventListener("click", function (e) {
            if (e.target.tagName === "BUTTON" && e.target.parentElement.classList.contains("chatbot-quick-buttons")) {
                sendMessage(e.target.dataset.question);
            }
        });

        window.addEventListener("resize", function () {
            clampWidgetPosition();
            requestPanelUpdate();
        });

        if ("ResizeObserver" in window) {
            const resizeObserver = new ResizeObserver(function () {
                requestPanelUpdate();
                scrollToBottom();
            });

            resizeObserver.observe(panel);
        }

        setDefaultPosition();
        updatePanelPosition();
        scrollToBottom();
    });
</script>