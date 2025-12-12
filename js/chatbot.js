(() => {
  // Basic styles for the floating chatbot
  const style = document.createElement("style");
  style.textContent = `
    .chatbot-launch { position: fixed; bottom: 20px; right: 20px; z-index: 9999; background:#4a6cf7;color:#fff;border:none;border-radius:24px;padding:12px 16px;cursor:pointer;box-shadow:0 8px 24px rgba(0,0,0,0.2); }
    .chatbot-panel { position: fixed; bottom: 80px; right: 20px; width: 320px; max-height: 70vh; background:#fff; color:#111; border-radius:12px; box-shadow:0 8px 24px rgba(0,0,0,0.2); display:flex; flex-direction:column; overflow:hidden; z-index:9999; }
    .chatbot-header { padding:12px; background:#4a6cf7; color:#fff; font-weight:600; display:flex; justify-content:space-between; align-items:center;}
    .chatbot-messages { flex:1; padding:12px; overflow-y:auto; font-size:14px; gap:8px; display:flex; flex-direction:column;}
    .chatbot-input { display:flex; border-top:1px solid #eee; }
    .chatbot-input input { flex:1; border:none; padding:10px; font-size:14px; }
    .chatbot-input button { border:none; background:#4a6cf7; color:#fff; padding:0 14px; cursor:pointer; }
    .chat-msg.user { align-self:flex-end; background:#e6ecff; padding:8px 10px; border-radius:10px; max-width: 90%; }
    .chat-msg.bot { align-self:flex-start; background:#f6f6f6; padding:8px 10px; border-radius:10px; max-width: 90%; }
    @media (max-width: 480px) {
      .chatbot-panel { right: 10px; width: 90vw; }
      .chatbot-launch { right: 10px; }
    }
  `;
  document.head.appendChild(style);

  const btn = document.createElement("button");
  btn.className = "chatbot-launch";
  btn.textContent = "Chat";
  document.body.appendChild(btn);

  const panel = document.createElement("div");
  panel.className = "chatbot-panel";
  panel.style.display = "none";
  panel.innerHTML = `
    <div class="chatbot-header">
      <span>Auction Helper</span>
      <button id="chatClose" style="background:transparent;border:none;color:#fff;font-size:16px;cursor:pointer;">×</button>
    </div>
    <div class="chatbot-messages" id="chatMessages"></div>
    <div class="chatbot-input">
      <input id="chatInput" placeholder="Ask about auctions..." />
      <button id="chatSend">Send</button>
    </div>
  `;
  document.body.appendChild(panel);

  const messagesEl = panel.querySelector("#chatMessages");
  const inputEl = panel.querySelector("#chatInput");

  const addMsg = (text, who = "bot") => {
    const div = document.createElement("div");
    div.className = `chat-msg ${who}`;
    div.textContent = text;
    messagesEl.appendChild(div);
    messagesEl.scrollTop = messagesEl.scrollHeight;
  };

  const fetchAuctions = async () => {
    const res = await fetch("../api/api.php?action=auctions");
    const data = await res.json();
    if (!data.success) throw new Error(data.error || "Failed to load auctions");
    return data.auctions || [];
  };

  const handleQuestion = async (q) => {
    const text = q.toLowerCase();
    try {
      const auctions = await fetchAuctions();
      if (!auctions.length) return "No active auctions right now.";

      // Most bids / top auctions
      if (
        text.includes("most bids") ||
        text.includes("top") ||
        text.includes("most auctioned")
      ) {
        const sorted = [...auctions].sort(
          (a, b) => (b.total_bids || 0) - (a.total_bids || 0)
        );
        const top = sorted
          .slice(0, 3)
          .map(
            (a) =>
              `${a.year} ${a.make} ${a.model} — ${
                a.total_bids || 0
              } bids, $${parseFloat(a.current_price).toLocaleString()}`
          )
          .join(" | ");
        return top || "No bids yet.";
      }

      // Highest current price
      if (
        text.includes("highest") ||
        text.includes("expensive") ||
        text.includes("price")
      ) {
        const max = auctions.reduce(
          (m, a) =>
            parseFloat(a.current_price) > parseFloat(m.current_price || 0)
              ? a
              : m,
          {}
        );
        if (!max.auction_id) return "No auctions found.";
        return `Highest current: ${max.year} ${max.make} ${
          max.model
        } at $${parseFloat(max.current_price).toLocaleString()} with ${
          max.total_bids || 0
        } bids.`;
      }

      // Default summary
      const top = [...auctions]
        .sort((a, b) => (b.total_bids || 0) - (a.total_bids || 0))
        .slice(0, 3);
      return `Top auctions: ${top
        .map(
          (a) =>
            `${a.year} ${a.make} ${a.model} ($${parseFloat(
              a.current_price
            ).toLocaleString()}, ${a.total_bids || 0} bids)`
        )
        .join(" | ")}`;
    } catch (e) {
      console.error(e);
      return "Sorry, I couldn't load auctions right now.";
    }
  };

  btn.onclick = () => {
    panel.style.display = "flex";
    inputEl.focus();
  };
  panel.querySelector("#chatClose").onclick = () => {
    panel.style.display = "none";
  };
  panel.querySelector("#chatSend").onclick = async () => {
    const val = inputEl.value.trim();
    if (!val) return;
    addMsg(val, "user");
    inputEl.value = "";
    addMsg("Thinking...", "bot");
    const reply = await handleQuestion(val);
    messagesEl.lastChild.textContent = reply;
  };
  inputEl.addEventListener("keydown", (e) => {
    if (e.key === "Enter") panel.querySelector("#chatSend").click();
  });

  addMsg("Hi! Ask me about current auctions, most bids, or highest prices.");
})();
