"use strict";

const $ = (id) => document.getElementById(id);
const esc = (s) => String(s ?? "").replace(/[&<>"']/g, (c) => ({"&":"&amp;","<":"&lt;",">":"&gt;",'"':"&quot;","'":"&#39;"}[c]));
const uuid = () => crypto.randomUUID ? crypto.randomUUID() : Math.random().toString(36).slice(2);
const today = () => new Date().toISOString().slice(0, 10);
const fmtDate = (d) => d ? new Date(d).toLocaleDateString("ro-RO", {day:"2-digit",month:"short",year:"numeric"}) : "-";
const fmtTime = (t) => t ?? "";
const clamp = (v, lo, hi) => Math.min(Math.max(v, lo), hi);

function toast(title, msg = "", type = "ok") {
  const root = $("toastRoot");
  const el = document.createElement("div");
  el.className = "toast" + (type === "error" ? " error" : "");
  el.innerHTML = `<strong>${esc(title)}</strong><span>${esc(msg)}</span>`;
  root.prepend(el);
  setTimeout(() => el.remove(), 3800);
}

const SEED = {
  users: [
    {id:"u1", name:"Admin KIM", email:"admin@kim.ro", phone:"0700000001", role:"admin", password:"admin123", status:"active", joinDate:"2024-01-10"},
    {id:"u2", name:"Radu Ionescu", email:"radu@kim.ro", phone:"0700000002", role:"trainer", password:"trainer123", status:"active", joinDate:"2024-01-15", specialization:"Fitness & Forță", schedule:"Lun-Vin 08:00-16:00"},
    {id:"u3", name:"Elena Popescu", email:"elena@kim.ro", phone:"0700000003", role:"trainer", password:"trainer123", status:"active", joinDate:"2024-02-01", specialization:"Kinetoterapie", schedule:"Mar-Sâm 10:00-18:00"},
    {id:"u4", name:"Andrei Marin", email:"andrei@kim.ro", phone:"0700000004", role:"member", password:"member123", status:"active", joinDate:"2024-03-01"},
    {id:"u5", name:"Maria Dumitrescu", email:"maria@kim.ro", phone:"0700000005", role:"member", password:"member123", status:"active", joinDate:"2024-03-15"},
    {id:"u6", name:"Bogdan Radu", email:"bogdan@kim.ro", phone:"0700000006", role:"member", password:"member123", status:"suspended", joinDate:"2024-02-20"},
  ],
  sessions: [
    {id:"s1", title:"Fitness General", type:"fitness", trainer:"u2", room:"r1", date:today(), time:"09:00", duration:60, capacity:12, booked:["u4","u5"], status:"active", description:"Antrenament complet de fitness pentru toate nivelurile."},
    {id:"s2", title:"Forță & Condiționare", type:"strength", trainer:"u2", room:"r2", date:today(), time:"11:00", duration:75, capacity:8, booked:["u4"], status:"active", description:"Program de forță progresiv cu greutăți libere."},
    {id:"s3", title:"Kinetoterapie Coloană", type:"kineto", trainer:"u3", room:"r3", date:today(), time:"14:00", duration:50, capacity:4, booked:["u5"], status:"active", description:"Recuperare și corectare posturală."},
    {id:"s4", title:"Cardio Intensiv", type:"mixed", trainer:"u2", room:"r1", date:today(), time:"17:00", duration:45, capacity:15, booked:[], status:"active", description:"Sesiune de cardio HIIT pentru arderea caloriilor."},
    {id:"s5", title:"Yoga & Stretching", type:"fitness", trainer:"u3", room:"r3", date:today(), time:"08:00", duration:60, capacity:10, booked:["u4","u5"], status:"cancelled", description:"Relaxare și flexibilitate."},
  ],
  subscriptions: [
    {id:"ab1", userId:"u4", type:"Premium", start:"2025-05-01", end:"2025-07-31", price:320, status:"active"},
    {id:"ab2", userId:"u5", type:"Standard", start:"2025-04-01", end:"2025-06-30", price:200, status:"active"},
    {id:"ab3", userId:"u6", type:"Basic", start:"2024-12-01", end:"2025-01-31", price:120, status:"expired"},
  ],
  rooms: [
    {id:"r1", name:"Sala Fitness", capacity:20, equipment:"Aparate cardio, greutăți"},
    {id:"r2", name:"Sala Forță", capacity:10, equipment:"Bare olimpice, bănci, rack-uri"},
    {id:"r3", name:"Sala Kineto", capacity:6, equipment:"Saltele, benzi, mingi medicinale"},
  ],
  equipment: [
    {id:"e1", name:"Bicicletă ergometrică", qty:5, room:"r1"},
    {id:"e2", name:"Bandă de alergat", qty:4, room:"r1"},
    {id:"e3", name:"Bară olimpică 20kg", qty:6, room:"r2"},
    {id:"e4", name:"Saltea kinetoterapie", qty:8, room:"r3"},
  ],
  plugins: [
    {id:"p1", name:"Notificări Email", description:"Trimite email-uri automate pentru rezervări și abonamente.", status:"installed", version:"1.2.0"},
    {id:"p2", name:"Export Rapoarte PDF", description:"Generează rapoarte PDF descărcabile.", status:"installed", version:"2.0.1"},
    {id:"p3", name:"Calendar Sync", description:"Sincronizare cu Google Calendar / Outlook.", status:"pending", version:"0.9.5"},
    {id:"p4", name:"Chatbot Asistență", description:"Asistent virtual pentru membri.", status:"pending", version:"1.0.0"},
  ],
  activity: [
    {id:"a1", date:today(), action:"Rezervare adăugată", user:"Andrei Marin", detail:"Fitness General"},
    {id:"a2", date:today(), action:"Abonament activat", user:"Maria Dumitrescu", detail:"Standard - 3 luni"},
    {id:"a3", date:today(), action:"Sesiune creată", user:"Radu Ionescu", detail:"Forță & Condiționare"},
    {id:"a4", date:today(), action:"Utilizator suspendat", user:"Bogdan Radu", detail:"Motiv: nerespectare regulament"},
  ]
};

const API_BASE = "../";

const Api = {
  async get(endpoint) {
    try {
      const resp = await fetch(API_BASE + endpoint, {
        method: "GET",
        headers: { "Accept": "application/json", "X-Requested-With": "XMLHttpRequest" }
      });
      if (!resp.ok) throw new Error(`HTTP ${resp.status}`);
      return await resp.json();
    } catch {
      return null; // fallback pe Store local
    }
  },
  async post(endpoint, data) {
    try {
      const resp = await fetch(API_BASE + endpoint, {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          "Accept": "application/json",
          "X-Requested-With": "XMLHttpRequest"
        },
        body: JSON.stringify(data)
      });
      if (!resp.ok) throw new Error(`HTTP ${resp.status}`);
      return await resp.json();
    } catch {
      return null; // fallback pe Store local
    }
  },
  async loginRequest(email, password) {
    return await this.post("login.php", { email, password });
  },
  async fetchUsers() {
    return await this.get("pages/admin/useri/register_member.php?action=list");
  },
  async fetchSessions() {
    return await this.get("pages/member/sessions/get_slots.php");
  },
  async fetchTrainers() {
    return await this.get("pages/member/sessions/get_trainers.php");
  },
  async bookSession(sessionId) {
    return await this.post("pages/member/sessions/process_booking.php", { session_id: sessionId });
  },
  async fetchStats() {
    return await this.get("pages/admin/rapoarte/statistics.php?format=json");
  }
};

const Store = {
  _k: (key) => `kim_${key}`,
  get(key) {
    try { return JSON.parse(localStorage.getItem(this._k(key))); } catch { return null; }
  },
  set(key, val) {
    try { localStorage.setItem(this._k(key), JSON.stringify(val)); } catch {}
  },
  init() {
    if (!this.get("seeded")) {
      Object.entries(SEED).forEach(([k, v]) => this.set(k, v));
      this.set("seeded", true);
    }
  },
  reset() {
    ["users","sessions","subscriptions","rooms","equipment","plugins","activity","seeded"].forEach(k => localStorage.removeItem(this._k(k)));
    this.init();
  },
  getAll(key) { return this.get(key) ?? []; },
  save(key, arr) { this.set(key, arr); },
  addItem(key, item) {
    const arr = this.getAll(key);
    arr.push(item);
    this.save(key, arr);
  },
  updateItem(key, id, patch) {
    const arr = this.getAll(key).map(x => x.id === id ? {...x, ...patch} : x);
    this.save(key, arr);
  },
  deleteItem(key, id) {
    this.save(key, this.getAll(key).filter(x => x.id !== id));
  },
  findOne(key, id) { return this.getAll(key).find(x => x.id === id); },
  logActivity(action, user, detail) {
    this.addItem("activity", {id: uuid(), date: today(), action, user, detail});
  }
};

const Auth = {
  current: null,
  async loginAsync(email, password) {
    const serverResp = await Api.loginRequest(email.trim(), password);
    if (serverResp && serverResp.success && serverResp.user) {
      const u = serverResp.user;
      Store.updateItem("users", u.id, u);
      this.current = u;
      sessionStorage.setItem("kim_session", u.id);
      return true;
    }
    return this.login(email, password);
  },
  login(email, password) {
    // XSS-safe: esc() folosit la randare; parole comparate plaintext doar în demo
    const u = Store.getAll("users").find(x =>
      x.email.toLowerCase() === email.trim().toLowerCase() && x.password === password
    );
    if (!u) return false;
    if (u.status === "suspended") { toast("Cont suspendat", "Contactați administratorul.", "error"); return false; }
    this.current = u;
    sessionStorage.setItem("kim_session", u.id);
    return true;
  },
  logout() {
    this.current = null;
    sessionStorage.removeItem("kim_session");
  },
  restore() {
    const id = sessionStorage.getItem("kim_session");
    if (id) this.current = Store.findOne("users", id);
    return !!this.current;
  },
  is(role) { return this.current?.role === role; },
  can(roles) { return roles.includes(this.current?.role); }
};

const NAV = {
  admin: [
    {id:"adminDashboardView", label:"🏠 Dashboard"},
    {id:"usersView", label:"👥 Utilizatori"},
    {id:"scheduleView", label:"📅 Orar & Programări"},
    {id:"subscriptionsView", label:"💳 Abonamente"},
    {id:"trainersView", label:"🏋️ Specialiști"},
    {id:"resourcesView", label:"🏢 Săli & Echipamente"},
    {id:"reportsView", label:"📊 Rapoarte"},
    {id:"pluginsView", label:"🔌 Plugin-uri"},
    {id:"profileView", label:"👤 Profil"},
  ],
  trainer: [
    {id:"trainerDashboardView", label:"🏠 Dashboard"},
    {id:"scheduleView", label:"📅 Programul meu"},
    {id:"profileView", label:"👤 Profil"},
  ],
  member: [
    {id:"memberDashboardView", label:"🏠 Dashboard"},
    {id:"scheduleView", label:"📅 Sesiuni disponibile"},
    {id:"subscriptionsView", label:"💳 Abonamentul meu"},
    {id:"profileView", label:"👤 Profil"},
  ]
};

const PAGE_TITLES = {
  adminDashboardView: ["Dashboard", "Panou de control administrator"],
  trainerDashboardView: ["Dashboard", "Panou specialist"],
  memberDashboardView: ["Dashboard", "Panou membru"],
  usersView: ["Utilizatori", "Gestiune conturi și roluri"],
  scheduleView: ["Orar & Programări", "Sesiuni disponibile"],
  subscriptionsView: ["Abonamente", "Gestiune abonamente și plăți"],
  trainersView: ["Specialiști", "Antrenori și terapeuți"],
  resourcesView: ["Resurse", "Săli și echipamente"],
  reportsView: ["Rapoarte", "Statistici și exporturi"],
  pluginsView: ["Plugin-uri", "Funcționalități extinse"],
  profileView: ["Profil", "Informații cont"],
};

let currentView = null;

document.addEventListener("DOMContentLoaded", () => {
  Store.init();
  if (Auth.restore()) {
    showApp();
  } else {
    showAuth();
  }
  bindGlobal();
});

function showAuth() {
  $("authScreen").classList.remove("hidden");
  $("appShell").classList.add("hidden");
}

function showApp() {
  $("authScreen").classList.add("hidden");
  $("appShell").classList.remove("hidden");
  buildNav();
  renderUserMini();
  const role = Auth.current.role;
  const firstView = NAV[role]?.[0]?.id ?? "adminDashboardView";
  navigateTo(firstView);
  $("roleBadge").textContent = roleLabel(role);
  $("dateBadge").textContent = new Date().toLocaleDateString("ro-RO", {weekday:"short",day:"numeric",month:"short"});
}

function roleLabel(r) {
  return {admin:"Administrator", trainer:"Specialist", member:"Membru"}[r] ?? r;
}

function buildNav() {
  const nav = $("sidebarNav");
  nav.innerHTML = "";
  const links = NAV[Auth.current.role] ?? [];
  links.forEach(item => {
    const btn = document.createElement("button");
    btn.className = "nav-link";
    btn.textContent = item.label;
    btn.dataset.view = item.id;
    btn.addEventListener("click", () => {
      navigateTo(item.id);
      $("sidebar").classList.remove("open");
    });
    nav.appendChild(btn);
  });
}

function renderUserMini() {
  const u = Auth.current;
  $("currentUserMini").innerHTML = `
    <strong>${esc(u.name)}</strong>
    <span>${esc(u.email)}</span>
    <span>${roleLabel(u.role)}</span>`;
}

function navigateTo(viewId) {
  document.querySelectorAll(".view").forEach(v => v.classList.remove("active"));
  document.querySelectorAll(".nav-link").forEach(b => b.classList.remove("active"));
  const el = $(viewId);
  if (el) el.classList.add("active");
  const btn = document.querySelector(`[data-view="${viewId}"]`);
  if (btn) btn.classList.add("active");
  const [title, sub] = PAGE_TITLES[viewId] ?? [viewId, ""];
  $("pageTitle").textContent = title;
  $("pageSubtitle").textContent = sub;
  currentView = viewId;
  renderView(viewId);
}

function renderView(id) {
  const renderers = {
    adminDashboardView: renderAdminDashboard,
    trainerDashboardView: renderTrainerDashboard,
    memberDashboardView: renderMemberDashboard,
    usersView: renderUsers,
    scheduleView: renderSchedule,
    subscriptionsView: renderSubscriptions,
    trainersView: renderTrainers,
    resourcesView: renderResources,
    reportsView: renderReports,
    pluginsView: renderPlugins,
    profileView: renderProfile,
  };
  const fn = renderers[id];
  if (fn) Promise.resolve(fn()).catch(console.error);
}

function bindGlobal() {
  document.querySelectorAll("[data-auth-tab]").forEach(btn => {
    btn.addEventListener("click", () => {
      document.querySelectorAll(".auth-tab,.auth-form").forEach(x => x.classList.remove("active"));
      btn.classList.add("active");
      $(`${btn.dataset.authTab}Form`).classList.add("active");
    });
  });

  document.querySelectorAll("[data-demo]").forEach(btn => {
    const creds = {admin:["admin@kim.ro","admin123"], trainer:["radu@kim.ro","trainer123"], member:["andrei@kim.ro","member123"]};
    btn.addEventListener("click", () => {
      const [e, p] = creds[btn.dataset.demo];
      $("loginEmail").value = e; $("loginPassword").value = p;
    });
  });

  $("loginForm").addEventListener("submit", async e => {
    e.preventDefault();
    const email = $("loginEmail").value.trim();
    const pass  = $("loginPassword").value;
    const btn   = e.target.querySelector("[type=submit]");
    btn.textContent = "Se conectează...";
    btn.disabled = true;
    const ok = await Auth.loginAsync(email, pass);
    btn.textContent = "Intră în aplicație";
    btn.disabled = false;
    if (ok) { showApp(); toast("Bun venit!", `Autentificat ca ${Auth.current.name}`); }
    else toast("Eroare", "Email sau parolă incorectă.", "error");
  });

  $("registerForm").addEventListener("submit", e => {
    e.preventDefault();
    const name  = $("registerName").value.trim();
    const phone = $("registerPhone").value.trim();
    const email = $("registerEmail").value.trim();
    const pass  = $("registerPassword").value;
    const role  = $("registerRole").value;
    if (Store.getAll("users").find(u => u.email.toLowerCase() === email.toLowerCase())) {
      toast("Eroare", "Email deja înregistrat.", "error"); return;
    }
    const newUser = {id:uuid(), name, phone, email, password:pass, role, status:"active", joinDate:today()};
    Store.addItem("users", newUser);
    toast("Cont creat!", "Poți acum să te autentifici.");
    document.querySelector("[data-auth-tab='login']").click();
  });

  $("logoutBtn").addEventListener("click", () => { Auth.logout(); showAuth(); toast("La revedere!", ""); });

  $("resetDemoBtn").addEventListener("click", () => { Store.reset(); showApp(); toast("Date resetate", "Datele demo au fost restaurate."); });

  $("menuToggle").addEventListener("click", () => $("sidebar").classList.toggle("open"));

  $("closeModal").addEventListener("click", closeModal);
  document.querySelector(".modal-backdrop").addEventListener("click", closeModal);

  document.body.addEventListener("click", e => {
    const jump = e.target.closest("[data-jump]");
    if (jump) navigateTo(jump.dataset.jump);
  });
}

function openModal(title, bodyHTML, onSubmit) {
  $("modalTitle").textContent = title;
  $("modalBody").innerHTML = bodyHTML;
  $("modalRoot").classList.remove("hidden");
  if (onSubmit) {
    const form = $("modalBody").querySelector("form");
    if (form) form.addEventListener("submit", ev => { ev.preventDefault(); onSubmit(ev); });
  }
}
function closeModal() { $("modalRoot").classList.add("hidden"); }

function renderAdminDashboard() {
  const users    = Store.getAll("users");
  const sessions = Store.getAll("sessions");
  const subs     = Store.getAll("subscriptions");
  const activity = Store.getAll("activity");

  $("adminActiveUsers").textContent   = users.filter(u=>u.status==="active").length;
  $("adminMonthBookings").textContent = sessions.reduce((acc,s)=>acc+(s.booked?.length??0),0);
  $("adminActiveSubs").textContent    = subs.filter(s=>s.status==="active").length;
  const occ = sessions.length ? Math.round(sessions.reduce((a,s)=>a+(s.booked?.length??0)/Math.max(s.capacity,1),0)/sessions.length*100) : 0;
  $("adminOccupancy").textContent     = occ + "%";

  const upcoming = sessions.filter(s=>s.status==="active").slice(0,4);
  $("adminUpcomingSessions").innerHTML = upcoming.length
    ? upcoming.map(s => `<div class="list-item split"><div><strong>${esc(s.title)}</strong><span>${fmtDate(s.date)} ${s.time} · ${esc(s.type)}</span></div><span class="status-pill status-active">${s.booked?.length??0}/${s.capacity}</span></div>`).join("")
    : `<div class="empty-state">Nicio sesiune programată.</div>`;

  $("adminActivityFeed").innerHTML = activity.slice(-5).reverse().map(a =>
    `<div class="list-item"><strong>${esc(a.action)}</strong><span>${esc(a.user)} · ${esc(a.detail)} · ${fmtDate(a.date)}</span></div>`
  ).join("") || `<div class="empty-state">Nicio activitate.</div>`;
}

function renderTrainerDashboard() {
  const me = Auth.current;
  const allSessions = Store.getAll("sessions");
  const mine = allSessions.filter(s=>s.trainer===me.id);
  const todaySess = mine.filter(s=>s.date===today() && s.status==="active");
  const allBooked = mine.reduce((a,s)=>a+(s.booked?.length??0),0);
  const occ = mine.length ? Math.round(mine.reduce((a,s)=>a+(s.booked?.length??0)/Math.max(s.capacity,1),0)/mine.length*100) : 0;

  $("trainerTotalSessions").textContent = mine.length;
  $("trainerTotalBookings").textContent = allBooked;
  $("trainerTodaySessions").textContent = todaySess.length;
  $("trainerOccupancy").textContent     = occ + "%";

  $("trainerAgenda").innerHTML = mine.length
    ? mine.slice(0,6).map(s => sessionCard(s, "trainer")).join("")
    : `<div class="empty-state">Nu ai sesiuni create.</div>`;

  const users = Store.getAll("users");
  const bookedIds = [...new Set(mine.flatMap(s=>s.booked??[]))];
  $("trainerBookedMembers").innerHTML = bookedIds.length
    ? bookedIds.map(uid => { const u = Store.findOne("users",uid); return u ? `<div class="list-item split"><div><strong>${esc(u.name)}</strong><span>${esc(u.email)}</span></div></div>` : ""; }).join("")
    : `<div class="empty-state">Niciun membru înscris.</div>`;

  document.querySelector("[data-open='session']")?.addEventListener("click", () => openSessionForm());
}

function renderMemberDashboard() {
  const me = Auth.current;
  const sessions = Store.getAll("sessions");
  const subs     = Store.getAll("subscriptions");
  const myBookings = sessions.filter(s=>s.booked?.includes(me.id));
  const available  = sessions.filter(s=>s.status==="active" && !s.booked?.includes(me.id) && (s.booked?.length??0)<s.capacity);
  const mySub      = subs.filter(s=>s.userId===me.id && s.status==="active")[0];

  $("memberBookings").textContent        = myBookings.length;
  $("memberActiveSub").textContent       = mySub?.type ?? "-";
  $("memberAvailableSessions").textContent = available.length;
  $("memberHistoryCount").textContent    = myBookings.length;

  $("memberRecommendedSessions").innerHTML = available.slice(0,4).map(s => sessionCard(s, "member")).join("") || `<div class="empty-state">Nicio sesiune disponibilă.</div>`;

  $("memberSubscriptionPanel").innerHTML = mySub
    ? `<div class="list-item"><strong>${esc(mySub.type)}</strong><span>Activ · ${fmtDate(mySub.start)} – ${fmtDate(mySub.end)}</span><span>Preț: ${mySub.price} RON</span></div>`
    : `<div class="list-item"><span>Nu ai un abonament activ.</span><button class="small-btn primary" onclick="navigateTo('subscriptionsView')">Alege abonament</button></div>`;
}

function sessionCard(s, role) {
  const statusCls = s.status === "active" ? "status-active" : "status-cancelled";
  const trainer   = Store.findOne("users", s.trainer);
  const room      = Store.findOne("rooms", s.room);
  const spotsFree = (s.capacity - (s.booked?.length??0));
  let actions = "";
  if (role === "member" && s.status === "active" && spotsFree > 0 && !s.booked?.includes(Auth.current.id)) {
    actions = `<button class="small-btn primary" onclick="bookSession('${s.id}')">Rezervă loc</button>`;
  } else if (role === "member" && s.booked?.includes(Auth.current.id)) {
    actions = `<button class="small-btn danger" onclick="cancelBooking('${s.id}')">Anulează rezervare</button>`;
  } else if (role === "trainer" || role === "admin") {
    actions = `<button class="small-btn" onclick="editSessionForm('${s.id}')">Editează</button>
               <button class="small-btn danger" onclick="deleteSession('${s.id}')">Șterge</button>`;
  }
  return `<div class="session-card">
    <div style="display:flex;justify-content:space-between;align-items:flex-start">
      <h4>${esc(s.title)}</h4>
      <span class="type-pill">${esc(s.type)}</span>
    </div>
    <div class="meta-grid">
      <span>📅 <b>${fmtDate(s.date)}</b> la <b>${fmtTime(s.time)}</b> · ${s.duration} min</span>
      <span>👤 <b>${esc(trainer?.name ?? "?")}</b></span>
      <span>🏢 <b>${esc(room?.name ?? "?")}</b></span>
      <span>👥 <b>${s.booked?.length??0}/${s.capacity}</b> locuri</span>
    </div>
    <span class="status-pill ${statusCls}">${s.status === "active" ? "Activ" : "Anulat"}</span>
    <div class="row-actions">${actions}</div>
  </div>`;
}

function bookSession(sId) {
  const me = Auth.current;
  const subs = Store.getAll("subscriptions");
  const activeSub = subs.find(s=>s.userId===me.id && s.status==="active");
  if (!activeSub) { toast("Abonament necesar", "Trebuie să ai un abonament activ pentru a rezerva.", "error"); return; }
  const s = Store.findOne("sessions", sId);
  if (!s) return;
  if (s.booked?.includes(me.id)) { toast("Deja rezervat", "Ești deja înscris la această sesiune.", "error"); return; }
  if ((s.booked?.length??0) >= s.capacity) { toast("Sesiune plină", "Nu mai sunt locuri disponibile.", "error"); return; }
  Store.updateItem("sessions", sId, {booked: [...(s.booked??[]), me.id]});
  Store.logActivity("Rezervare adăugată", me.name, s.title);
  toast("Rezervare confirmată!", `Te-ai înscris la "${s.title}".`);
  renderView(currentView);
}

function cancelBooking(sId) {
  const me = Auth.current;
  const s  = Store.findOne("sessions", sId);
  if (!s) return;
  Store.updateItem("sessions", sId, {booked: (s.booked??[]).filter(id=>id!==me.id)});
  Store.logActivity("Rezervare anulată", me.name, s.title);
  toast("Rezervare anulată", `Ai anulat "${s.title}".`);
  renderView(currentView);
}

function renderUsers() {
  const render = (filter = "all", statusFilter = "all", search = "") => {
    let users = Store.getAll("users");
    if (filter !== "all") users = users.filter(u=>u.role===filter);
    if (statusFilter !== "all") users = users.filter(u=>u.status===statusFilter);
    if (search) {
      const q = search.toLowerCase();
      users = users.filter(u => [u.name,u.email,u.phone,u.role].some(f=>String(f).toLowerCase().includes(q)));
    }
    $("usersTable").innerHTML = users.map(u => `<tr>
      <td><strong>${esc(u.name)}</strong></td>
      <td>${esc(u.email)}</td>
      <td>${esc(u.phone ?? "-")}</td>
      <td><span class="status-pill status-active">${roleLabel(u.role)}</span></td>
      <td><span class="status-pill ${u.status==='active'?'status-active':'status-suspended'}">${u.status==='active'?'Activ':'Suspendat'}</span></td>
      <td><button class="small-btn" onclick="viewUserHistory('${u.id}')">Istoric</button></td>
      <td class="row-actions">
        <button class="small-btn primary" onclick="editUserForm('${u.id}')">Editează</button>
        <button class="small-btn ${u.status==='active'?'danger':''}" onclick="toggleUserStatus('${u.id}')">${u.status==='active'?'Suspendă':'Activează'}</button>
        <button class="small-btn danger" onclick="deleteUser('${u.id}')">Șterge</button>
      </td>
    </tr>`).join("") || `<tr><td colspan="7" class="empty-state">Niciun utilizator găsit.</td></tr>`;
  };

  render();
  $("userSearch").addEventListener("input", () => render($("userRoleFilter").value, $("userStatusFilter").value, $("userSearch").value));
  $("userRoleFilter").addEventListener("change", () => render($("userRoleFilter").value, $("userStatusFilter").value, $("userSearch").value));
  $("userStatusFilter").addEventListener("change", () => render($("userRoleFilter").value, $("userStatusFilter").value, $("userSearch").value));

  $("openUserModal").addEventListener("click", () => openUserForm());
}

function openUserForm(userId = null) {
  const u = userId ? Store.findOne("users", userId) : null;
  openModal(u ? "Editează utilizator" : "Adaugă utilizator", `
    <form class="form-grid" id="userForm">
      <div class="two-col">
        <label>Nume complet<input name="name" value="${esc(u?.name??'')}" required></label>
        <label>Telefon<input name="phone" value="${esc(u?.phone??'')}" required></label>
      </div>
      <label>Email<input type="email" name="email" value="${esc(u?.email??'')}" required></label>
      <div class="two-col">
        <label>Parolă<input type="password" name="password" value="${esc(u?.password??'')}"></label>
        <label>Rol<select name="role">
          <option value="member" ${u?.role==='member'?'selected':''}>Membru</option>
          <option value="trainer" ${u?.role==='trainer'?'selected':''}>Antrenor/Terapeut</option>
          <option value="admin" ${u?.role==='admin'?'selected':''}>Administrator</option>
        </select></label>
      </div>
      <div class="form-actions">
        <button type="button" class="ghost-btn" onclick="closeModal()">Anulează</button>
        <button type="submit" class="primary-btn">${u ? "Salvează" : "Adaugă"}</button>
      </div>
    </form>
  `, (e) => {
    const fd = new FormData(e.target);
    const data = Object.fromEntries(fd.entries());
    if (u) {
      Store.updateItem("users", u.id, data);
      toast("Salvat", `Utilizatorul ${data.name} a fost actualizat.`);
    } else {
      Store.addItem("users", {...data, id:uuid(), status:"active", joinDate:today()});
      Store.logActivity("Utilizator adăugat", Auth.current.name, data.name);
      toast("Adăugat", `${data.name} a fost creat.`);
    }
    closeModal();
    renderUsers();
  });
}
const editUserForm = (id) => openUserForm(id);

function toggleUserStatus(id) {
  const u = Store.findOne("users", id);
  const newStatus = u.status === "active" ? "suspended" : "active";
  Store.updateItem("users", id, {status: newStatus});
  Store.logActivity(newStatus==="suspended"?"Utilizator suspendat":"Utilizator activat", Auth.current.name, u.name);
  toast("Status actualizat", `${u.name} este acum ${newStatus==="active"?"activ":"suspendat"}.`);
  renderUsers();
}

function deleteUser(id) {
  if (!confirm("Ești sigur că vrei să ștergi acest utilizator?")) return;
  const u = Store.findOne("users", id);
  Store.deleteItem("users", id);
  Store.logActivity("Utilizator șters", Auth.current.name, u?.name ?? id);
  toast("Șters", "Utilizatorul a fost eliminat.");
  renderUsers();
}

function viewUserHistory(id) {
  const u = Store.findOne("users", id);
  const sessions = Store.getAll("sessions").filter(s=>s.booked?.includes(id));
  const subs = Store.getAll("subscriptions").filter(s=>s.userId===id);
  openModal(`Istoric – ${u?.name ?? id}`, `
    <div class="form-grid">
      <h4 style="margin:0">Sesiuni rezervate (${sessions.length})</h4>
      ${sessions.length ? sessions.map(s=>`<div class="list-item"><strong>${esc(s.title)}</strong><span>${fmtDate(s.date)} ${s.time}</span></div>`).join("") : `<div class="empty-state">Nicio sesiune.</div>`}
      <h4 style="margin:0">Abonamente (${subs.length})</h4>
      ${subs.length ? subs.map(s=>`<div class="list-item split"><div><strong>${esc(s.type)}</strong><span>${fmtDate(s.start)} – ${fmtDate(s.end)}</span></div><span class="status-pill ${s.status==='active'?'status-active':'status-expired'}">${s.status}</span></div>`).join("") : `<div class="empty-state">Niciun abonament.</div>`}
    </div>`);
}

function renderSchedule() {
  const role = Auth.current.role;
  const canEdit = role === "admin" || role === "trainer";
  if (!canEdit) { $("openSessionModal").classList.add("hidden"); } else { $("openSessionModal").classList.remove("hidden"); }

  const render = () => {
    let sessions = Store.getAll("sessions");
    if (role === "trainer") sessions = sessions.filter(s=>s.trainer===Auth.current.id);
    const search = $("sessionSearch").value.toLowerCase();
    const type   = $("sessionTypeFilter").value;
    const status = $("sessionStatusFilter").value;
    const date   = $("sessionDateFilter").value;
    if (search) sessions = sessions.filter(s=>[s.title,s.type,s.description].some(f=>String(f).toLowerCase().includes(search)));
    if (type !== "all") sessions = sessions.filter(s=>s.type===type);
    if (status !== "all") sessions = sessions.filter(s=>s.status===status);
    if (date) sessions = sessions.filter(s=>s.date===date);
    $("scheduleCards").innerHTML = sessions.length
      ? sessions.map(s=>sessionCard(s, role)).join("")
      : `<div class="empty-state">Nicio sesiune găsită.</div>`;
  };

  render();
  ["sessionSearch","sessionTypeFilter","sessionStatusFilter","sessionDateFilter"].forEach(id => $(id).addEventListener("input", render));
  $("openSessionModal").onclick = () => openSessionForm();
}

function openSessionForm(sessionId = null) {
  const s = sessionId ? Store.findOne("sessions", sessionId) : null;
  const users = Store.getAll("users").filter(u=>u.role==="trainer"||u.role==="admin");
  const rooms = Store.getAll("rooms");
  openModal(s ? "Editează sesiune" : "Adaugă sesiune", `
    <form class="form-grid" id="sessionForm">
      <label>Titlu<input name="title" value="${esc(s?.title??'')}" required></label>
      <div class="two-col">
        <label>Tip<select name="type">
          ${["fitness","strength","kineto","mixed"].map(t=>`<option value="${t}" ${s?.type===t?'selected':''}>${t}</option>`).join("")}
        </select></label>
        <label>Specialist<select name="trainer">
          ${users.map(u=>`<option value="${u.id}" ${s?.trainer===u.id?'selected':''}>${esc(u.name)}</option>`).join("")}
        </select></label>
      </div>
      <div class="two-col">
        <label>Data<input type="date" name="date" value="${s?.date??today()}" required></label>
        <label>Ora<input type="time" name="time" value="${s?.time??'09:00'}" required></label>
      </div>
      <div class="two-col">
        <label>Durată (min)<input type="number" name="duration" value="${s?.duration??60}" min="10" max="240" required></label>
        <label>Capacitate<input type="number" name="capacity" value="${s?.capacity??10}" min="1" max="50" required></label>
      </div>
      <label>Sală<select name="room">
        ${rooms.map(r=>`<option value="${r.id}" ${s?.room===r.id?'selected':''}>${esc(r.name)}</option>`).join("")}
      </select></label>
      <label>Descriere<textarea name="description" rows="2">${esc(s?.description??'')}</textarea></label>
      <div class="form-actions">
        <button type="button" class="ghost-btn" onclick="closeModal()">Anulează</button>
        <button type="submit" class="primary-btn">${s ? "Salvează" : "Creează"}</button>
      </div>
    </form>
  `, (e) => {
    const fd = new FormData(e.target);
    const data = {...Object.fromEntries(fd.entries()), duration:+fd.get("duration"), capacity:+fd.get("capacity")};
    if (s) {
      Store.updateItem("sessions", s.id, data);
      toast("Sesiune actualizată", data.title);
    } else {
      Store.addItem("sessions", {...data, id:uuid(), booked:[], status:"active"});
      Store.logActivity("Sesiune creată", Auth.current.name, data.title);
      toast("Sesiune creată", data.title);
    }
    closeModal();
    renderView(currentView);
  });
}
const editSessionForm = (id) => openSessionForm(id);

function deleteSession(id) {
  if (!confirm("Ștergi această sesiune?")) return;
  const s = Store.findOne("sessions", id);
  Store.deleteItem("sessions", id);
  Store.logActivity("Sesiune ștearsă", Auth.current.name, s?.title ?? id);
  toast("Sesiune ștearsă");
  renderView(currentView);
}

function renderSubscriptions() {
  const role = Auth.current.role;
  let subs = Store.getAll("subscriptions");
  if (role === "member") subs = subs.filter(s=>s.userId===Auth.current.id);

  const counts = {active:0, suspended:0, expired:0};
  subs.forEach(s => { counts[s.status] = (counts[s.status]??0)+1; });
  $("subscriptionSummary").innerHTML = Object.entries(counts).map(([k,v])=>`<div class="stat-card"><span>${k}</span><strong>${v}</strong></div>`).join("");

  const users = Store.getAll("users");
  $("subscriptionsTable").innerHTML = subs.map(s => {
    const u = Store.findOne("users", s.userId);
    return `<tr>
      <td>${esc(u?.name??s.userId)}</td>
      <td>${esc(s.type)}</td>
      <td>${fmtDate(s.start)}</td>
      <td>${fmtDate(s.end)}</td>
      <td>${s.price} RON</td>
      <td><span class="status-pill status-${s.status}">${s.status}</span></td>
      <td class="row-actions">
        ${s.status==="active"?`<button class="small-btn danger" onclick="suspendSub('${s.id}')">Suspendă</button>`:""}
        ${s.status==="suspended"?`<button class="small-btn primary" onclick="activateSub('${s.id}')">Activează</button>`:""}
        <button class="small-btn danger" onclick="deleteSub('${s.id}')">Șterge</button>
      </td>
    </tr>`;
  }).join("") || `<tr><td colspan="7" class="empty-state">Niciun abonament.</td></tr>`;

  $("openSubscriptionModal").onclick = () => openSubForm();
}

function openSubForm() {
  const members = Store.getAll("users").filter(u=>u.role==="member");
  openModal("Adaugă abonament", `
    <form class="form-grid" id="subForm">
      <label>Membru<select name="userId">
        ${members.map(u=>`<option value="${u.id}">${esc(u.name)}</option>`).join("")}
      </select></label>
      <label>Tip<select name="type">
        <option>Basic</option><option>Standard</option><option>Premium</option>
      </select></label>
      <div class="two-col">
        <label>Data start<input type="date" name="start" value="${today()}" required></label>
        <label>Data expirare<input type="date" name="end" required></label>
      </div>
      <label>Preț (RON)<input type="number" name="price" value="200" min="0" required></label>
      <div class="form-actions">
        <button type="button" class="ghost-btn" onclick="closeModal()">Anulează</button>
        <button type="submit" class="primary-btn">Adaugă</button>
      </div>
    </form>
  `, (e) => {
    const fd = new FormData(e.target);
    const data = {...Object.fromEntries(fd.entries()), price:+fd.get("price")};
    Store.addItem("subscriptions", {...data, id:uuid(), status:"active"});
    Store.logActivity("Abonament adăugat", Auth.current.name, `${data.type} pt ${Store.findOne("users",data.userId)?.name}`);
    toast("Abonament adăugat");
    closeModal();
    renderSubscriptions();
  });
}

const suspendSub  = (id) => { Store.updateItem("subscriptions",id,{status:"suspended"}); toast("Abonament suspendat"); renderSubscriptions(); };
const activateSub = (id) => { Store.updateItem("subscriptions",id,{status:"active"}); toast("Abonament activat"); renderSubscriptions(); };
const deleteSub   = (id) => { if(confirm("Ștergi abonamentul?")){ Store.deleteItem("subscriptions",id); toast("Șters"); renderSubscriptions(); } };

async function renderTrainers() {
  const render = (q="") => {
    let trainers = Store.getAll("users").filter(u=>u.role==="trainer"||u.role==="admin"||u.role==="kineto");
    if (q) trainers = trainers.filter(u=>[u.name,u.email,u.specialization,u.schedule].some(f=>String(f||"").toLowerCase().includes(q.toLowerCase())));
    $("trainersGrid").innerHTML = trainers.map(u=>`<div class="trainer-card">
      <h4>${esc(u.name)}</h4>
      <div class="meta-grid">
        <span>📧 ${esc(u.email)}</span>
        <span>📱 ${esc(u.phone??"-")}</span>
        ${u.specialization?`<span>🎯 ${esc(u.specialization)}</span>`:""}
        ${u.schedule?`<span>🕐 ${esc(u.schedule)}</span>`:""}
      </div>
      <span class="status-pill ${u.status==='active'?'status-active':'status-suspended'}">${u.status==='active'?'Activ':'Suspendat'}</span>
      <div class="row-actions">
        <button class="small-btn primary" onclick="editUserForm('${u.id}')">Editează</button>
        <button class="small-btn danger" onclick="deleteUser('${u.id}')">Șterge</button>
      </div>
    </div>`).join("") || `<div class="empty-state">Niciun specialist.</div>`;
  };

  await loadTrainersFromDB(); // aduce specialiștii reali din MySQL; dacă serverul nu răspunde, rămâne pe datele demo
  render();
  $("trainerSearch").addEventListener("input", e=>render(e.target.value));
  $("openTrainerModal").onclick = () => openTrainerForm();
  bindTrainerImportExport();
}

async function loadTrainersFromDB() {
  const [fitness, kineto] = await Promise.all([
    Api.get("pages/member/sessions/get_trainers.php?type=fitness"),
    Api.get("pages/member/sessions/get_trainers.php?type=kineto")
  ]);
  if (!fitness && !kineto) return; // niciun răspuns de la PHP -> păstrăm datele locale

  const rows = [
    ...(fitness || []).map(r => ({...r, _kind: "Antrenor"})),
    ...(kineto  || []).map(r => ({...r, _kind: "Kinetoterapeut"}))
  ];

  let users = Store.getAll("users").filter(u => !String(u.id).startsWith("db-")); // scoatem importurile anterioare din DB
  rows.forEach(r => {
    users.push({
      id: "db-" + r.id,
      name: `${r.nume ?? ""} ${r.prenume ?? ""}`.trim() || "Specialist",
      email: "",
      phone: "-",
      role: "trainer",
      status: "active",
      specialization: `${r._kind} (din baza de date)`,
      joinDate: today()
    });
  });
  Store.save("users", users); // datele reale intră în store și apar în interfață
}

function openTrainerForm() {
  openModal("Adaugă specialist", `
    <form class="form-grid" id="trainerForm">
      <div class="two-col">
        <label>Nume<input name="name" required></label>
        <label>Telefon<input name="phone" required></label>
      </div>
      <label>Email<input type="email" name="email" required></label>
      <label>Parolă<input type="password" name="password" required></label>
      <label>Specializare<input name="specialization"></label>
      <label>Program<input name="schedule"></label>
      <div class="form-actions">
        <button type="button" class="ghost-btn" onclick="closeModal()">Anulează</button>
        <button type="submit" class="primary-btn">Adaugă</button>
      </div>
    </form>
  `, (e) => {
    const fd = new FormData(e.target);
    const data = Object.fromEntries(fd.entries());
    Store.addItem("users", {...data, id:uuid(), role:"trainer", status:"active", joinDate:today()});
    Store.logActivity("Specialist adăugat", Auth.current.name, data.name);
    toast("Specialist adăugat", data.name);
    closeModal();
    renderTrainers();
  });
}

function bindTrainerImportExport() {
  $("exportTrainersCsv").onclick = () => {
    const trainers = Store.getAll("users").filter(u=>u.role==="trainer");
    const csv = ["Nume,Email,Telefon,Specializare,Program,Status",
      ...trainers.map(u=>`"${u.name}","${u.email}","${u.phone??''}","${u.specialization??''}","${u.schedule??''}","${u.status}"`)
    ].join("\n");
    downloadFile("specialisti.csv", csv, "text/csv;charset=utf-8;");
    toast("Export CSV", "Fișierul a fost descărcat.");
  };
  $("exportTrainersXml").onclick = () => {
    const trainers = Store.getAll("users").filter(u=>u.role==="trainer").map(u=>({
      nume: u.name, email: u.email, telefon: u.phone??'',
      specializare: u.specialization??'', program: u.schedule??'', status: u.status
    }));
    downloadFile("specialisti.json", JSON.stringify({specialisti: trainers}, null, 2), "application/json");
    toast("Export JSON", "Fișierul a fost descărcat.");
  };
  $("importTrainersCsv").onchange = (e) => {
    const file = e.target.files[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = (ev) => {
      const lines = ev.target.result.split("\n").slice(1);
      let added = 0;
      lines.forEach(line => {
        const [name,email,phone,specialization,schedule,status] = line.split(",").map(x=>x?.replace(/^"|"$/g,"").trim());
        if (!name||!email) return;
        if (Store.getAll("users").find(u=>u.email===email)) return;
        Store.addItem("users", {id:uuid(),name,email,phone:phone||"",specialization,schedule,status:status||"active",role:"trainer",password:"import123",joinDate:today()});
        added++;
      });
      toast("Import CSV", `${added} specialiști adăugați.`);
      renderTrainers();
    };
    reader.readAsText(file);
    e.target.value = "";
  };
  $("importTrainersXml").onchange = (e) => {
    const file = e.target.files[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = (ev) => {
      try {
        const parsed = JSON.parse(ev.target.result);
        const list = parsed.specialisti ?? parsed;
        if (!Array.isArray(list)) throw new Error("Format invalid");
        let added = 0;
        list.forEach(u => {
          const email = u.email ?? u.Email;
          if (!email || Store.getAll("users").find(x=>x.email===email)) return;
          Store.addItem("users", {
            id:uuid(),
            name: u.nume ?? u.name ?? u.Nume ?? "",
            email,
            phone: u.telefon ?? u.phone ?? "",
            specialization: u.specializare ?? u.specialization ?? "",
            schedule: u.program ?? u.schedule ?? "",
            status: u.status ?? "active",
            role:"trainer", password:"import123", joinDate:today()
          });
          added++;
        });
        toast("Import JSON", `${added} specialiști adăugați.`);
        renderTrainers();
      } catch { toast("Eroare", "Fișier JSON invalid.", "error"); }
    };
    reader.readAsText(file);
    e.target.value = "";
  };
}

function renderResources() {
  const rooms = Store.getAll("rooms");
  $("roomsGrid").innerHTML = rooms.map(r=>`<div class="room-card">
    <h4>${esc(r.name)}</h4>
    <div class="meta-grid">
      <span>👥 Capacitate: <b>${r.capacity}</b></span>
      <span>🏋️ ${esc(r.equipment)}</span>
    </div>
    <div class="row-actions">
      <button class="small-btn primary" onclick="editRoomForm('${r.id}')">Editează</button>
      <button class="small-btn danger" onclick="deleteRoom('${r.id}')">Șterge</button>
    </div>
  </div>`).join("") || `<div class="empty-state">Nicio sală.</div>`;

  const equip = Store.getAll("equipment");
  $("equipmentList").innerHTML = equip.map(e=>`<div class="list-item split">
    <div><strong>${esc(e.name)}</strong><span>Cantitate: ${e.qty} · Sală: ${esc(rooms.find(r=>r.id===e.room)?.name??'?')}</span></div>
    <div class="row-actions">
      <button class="small-btn primary" onclick="editEquipmentForm('${e.id}')">Edit</button>
      <button class="small-btn danger" onclick="deleteEquipment('${e.id}')">Șterge</button>
    </div>
  </div>`).join("") || `<div class="empty-state">Niciun echipament.</div>`;

  $("openRoomModal").onclick = () => openRoomForm();
  $("openEquipmentModal").onclick = () => openEquipmentForm();
}

function openRoomForm(id = null) {
  const r = id ? Store.findOne("rooms", id) : null;
  openModal(r ? "Editează sală" : "Adaugă sală", `
    <form class="form-grid">
      <label>Nume sală<input name="name" value="${esc(r?.name??'')}" required></label>
      <label>Capacitate<input type="number" name="capacity" value="${r?.capacity??10}" min="1" required></label>
      <label>Echipamente<input name="equipment" value="${esc(r?.equipment??'')}"></label>
      <div class="form-actions">
        <button type="button" class="ghost-btn" onclick="closeModal()">Anulează</button>
        <button type="submit" class="primary-btn">${r?"Salvează":"Adaugă"}</button>
      </div>
    </form>
  `, (e) => {
    const fd = new FormData(e.target);
    const data = {...Object.fromEntries(fd.entries()), capacity:+fd.get("capacity")};
    if (r) { Store.updateItem("rooms",r.id,data); toast("Sală actualizată"); }
    else { Store.addItem("rooms",{...data,id:uuid()}); toast("Sală adăugată"); }
    closeModal(); renderResources();
  });
}
const editRoomForm = (id) => openRoomForm(id);
const deleteRoom = (id) => { if(confirm("Ștergi sala?")){ Store.deleteItem("rooms",id); toast("Sală ștearsă"); renderResources(); } };

function openEquipmentForm(id = null) {
  const eq = id ? Store.findOne("equipment", id) : null;
  const rooms = Store.getAll("rooms");
  openModal(eq?"Editează echipament":"Adaugă echipament", `
    <form class="form-grid">
      <label>Denumire<input name="name" value="${esc(eq?.name??'')}" required></label>
      <div class="two-col">
        <label>Cantitate<input type="number" name="qty" value="${eq?.qty??1}" min="1" required></label>
        <label>Sală<select name="room">${rooms.map(r=>`<option value="${r.id}" ${eq?.room===r.id?'selected':''}>${esc(r.name)}</option>`).join("")}</select></label>
      </div>
      <div class="form-actions">
        <button type="button" class="ghost-btn" onclick="closeModal()">Anulează</button>
        <button type="submit" class="primary-btn">${eq?"Salvează":"Adaugă"}</button>
      </div>
    </form>
  `, (e) => {
    const fd = new FormData(e.target);
    const data = {...Object.fromEntries(fd.entries()), qty:+fd.get("qty")};
    if (eq) { Store.updateItem("equipment",eq.id,data); toast("Echipament actualizat"); }
    else { Store.addItem("equipment",{...data,id:uuid()}); toast("Echipament adăugat"); }
    closeModal(); renderResources();
  });
}
const editEquipmentForm = (id) => openEquipmentForm(id);
const deleteEquipment = (id) => { if(confirm("Ștergi echipamentul?")){ Store.deleteItem("equipment",id); toast("Echipament șters"); renderResources(); } };

async function renderReports() {
  const liveStats = await Api.fetchStats();
  const sessions = liveStats?.sessions ?? Store.getAll("sessions");

  const today_ = today();
  const weekStart = (() => { const d = new Date(); d.setDate(d.getDate()-d.getDay()+1); return d.toISOString().slice(0,10); })();
  const monthStart = today_.slice(0,8)+"01";

  const todayB = sessions.reduce((a,s)=>a+(s.date===today_?s.booked?.length??0:0), 0);
  const weekB  = sessions.reduce((a,s)=>a+(s.date>=weekStart?s.booked?.length??0:0), 0);
  const monthB = sessions.reduce((a,s)=>a+(s.date>=monthStart?s.booked?.length??0:0), 0);

  const trainerBookings = {};
  sessions.forEach(s=>{
    trainerBookings[s.trainer] = (trainerBookings[s.trainer]??0) + (s.booked?.length??0);
  });
  const topTrainerId = Object.entries(trainerBookings).sort((a,b)=>b[1]-a[1])[0]?.[0];
  const topTrainer = Store.findOne("users", topTrainerId);

  $("reportTodayBookings").textContent = todayB;
  $("reportWeekBookings").textContent  = weekB;
  $("reportMonthBookings").textContent = monthB;
  $("reportTopTrainer").textContent    = topTrainer?.name ?? "-";

  $("reportQuickStats").innerHTML = [
    {label:"Total sesiuni", val: sessions.length},
    {label:"Sesiuni active", val: sessions.filter(s=>s.status==="active").length},
    {label:"Total utilizatori", val: Store.getAll("users").length},
    {label:"Abonamente active", val: Store.getAll("subscriptions").filter(s=>s.status==="active").length},
  ].map(x=>`<div class="list-item split"><span>${esc(x.label)}</span><strong>${x.val}</strong></div>`).join("");

  drawChart(sessions);

  $("exportReportsCsv").onclick = () => {
    const csv = ["Data,Sesiune,Tip,Rezervari,Capacitate",
      ...sessions.map(s=>`"${s.date}","${s.title}","${s.type}","${s.booked?.length??0}","${s.capacity}"`)
    ].join("\n");
    downloadFile("raport.csv", csv, "text/csv");
    toast("Export CSV", "Raportul a fost descărcat.");
  };
  $("exportReportsXml").onclick = () => {
    const data = {
      exportat: new Date().toISOString(),
      total_sesiuni: sessions.length,
      sesiuni: sessions.map(s => ({
        data: s.date, titlu: s.title, tip: s.type,
        rezervari: s.booked?.length??0, capacitate: s.capacity, status: s.status
      }))
    };
    downloadFile("raport.json", JSON.stringify(data, null, 2), "application/json");
    toast("Export JSON", "Raportul a fost descărcat.");
  };
  $("exportChartPng").onclick  = () => exportCanvas("png");
  $("exportChartWebp").onclick = () => exportCanvas("webp");
}

function drawChart(sessions) {
  const canvas = $("reportsCanvas");
  const ctx = canvas.getContext("2d");
  const W = canvas.width, H = canvas.height;
  ctx.clearRect(0, 0, W, H);

  const typeCounts = {};
  sessions.forEach(s => { typeCounts[s.type] = (typeCounts[s.type]??0) + (s.booked?.length??0); });
  const types = Object.keys(typeCounts);
  const vals  = types.map(t=>typeCounts[t]);
  const max   = Math.max(...vals, 1);
  const colors = ["#0e7c7b","#315bc7","#d9822b","#15825b","#d64545"];
  const barW = clamp((W-100)/Math.max(types.length,1)-24, 30, 200);
  const gap  = 24;
  const chartH = H - 100;
  const startX = 70;

  ctx.strokeStyle = "#e5eaf2"; ctx.lineWidth = 1;
  for (let i=0; i<=5; i++) {
    const y = 40 + (chartH/5)*(5-i);
    ctx.beginPath(); ctx.moveTo(startX, y); ctx.lineTo(W-20, y); ctx.stroke();
    ctx.fillStyle = "#68758a"; ctx.font = "12px Inter,Arial,sans-serif";
    ctx.fillText(Math.round(max/5*i), 8, y+5);
  }
  ctx.fillStyle = "#102033"; ctx.font = "bold 14px Inter,Arial,sans-serif";
  ctx.fillText("Rezervări pe tip sesiune", startX, 20);

  types.forEach((t, i) => {
    const x = startX + i*(barW+gap);
    const barHeight = (vals[i]/max)*chartH;
    const y = 40 + chartH - barHeight;
    ctx.fillStyle = colors[i%colors.length];
    ctx.beginPath();
    ctx.roundRect(x, y, barW, barHeight, [8,8,0,0]);
    ctx.fill();
    ctx.fillStyle = "#fff"; ctx.font = "bold 13px Inter,Arial,sans-serif";
    ctx.fillText(vals[i], x+barW/2-8, y+18);
    ctx.fillStyle = "#102033"; ctx.font = "12px Inter,Arial,sans-serif";
    ctx.fillText(t, x, 40+chartH+18);
  });
}

function exportCanvas(fmt) {
  const canvas = $("reportsCanvas");
  const link = document.createElement("a");
  link.download = `grafic.${fmt}`;
  link.href = canvas.toDataURL(`image/${fmt}`);
  link.click();
  toast(`Export ${fmt.toUpperCase()}`, "Graficul a fost descărcat.");
}

function renderPlugins() {
  const plugins = Store.getAll("plugins");
  $("pluginsGrid").innerHTML = plugins.map(p=>`<div class="plugin-card">
    <div style="display:flex;justify-content:space-between;align-items:flex-start">
      <h4>${esc(p.name)}</h4>
      <span class="status-pill status-${p.status==='installed'?'installed':'pending'}">${p.status==='installed'?'Instalat':'În așteptare'}</span>
    </div>
    <p style="color:var(--muted);font-size:14px;margin:0">${esc(p.description)}</p>
    <div class="meta-grid"><span>Versiune: <b>${esc(p.version)}</b></span></div>
    <div class="row-actions">
      ${p.status==='pending'?`<button class="small-btn primary" onclick="installPlugin('${p.id}')">Instalează</button>`:`<button class="small-btn danger" onclick="uninstallPlugin('${p.id}')">Dezinstalează</button>`}
      <button class="small-btn danger" onclick="deletePlugin('${p.id}')">Șterge</button>
    </div>
  </div>`).join("") || `<div class="empty-state">Niciun plugin.</div>`;

  $("openPluginModal").onclick = () => openPluginForm();
}

function openPluginForm() {
  openModal("Adaugă plugin", `
    <form class="form-grid">
      <label>Nume<input name="name" required></label>
      <label>Descriere<textarea name="description" rows="2"></textarea></label>
      <label>Versiune<input name="version" value="1.0.0" required></label>
      <div class="form-actions">
        <button type="button" class="ghost-btn" onclick="closeModal()">Anulează</button>
        <button type="submit" class="primary-btn">Adaugă</button>
      </div>
    </form>
  `, (e) => {
    const fd = new FormData(e.target);
    Store.addItem("plugins", {...Object.fromEntries(fd.entries()), id:uuid(), status:"pending"});
    toast("Plugin adăugat");
    closeModal();
    renderPlugins();
  });
}

const installPlugin   = (id) => { Store.updateItem("plugins",id,{status:"installed"}); toast("Plugin instalat"); renderPlugins(); };
const uninstallPlugin = (id) => { Store.updateItem("plugins",id,{status:"pending"}); toast("Plugin dezinstalat"); renderPlugins(); };
const deletePlugin    = (id) => { if(confirm("Ștergi plugin-ul?")){ Store.deleteItem("plugins",id); toast("Plugin șters"); renderPlugins(); } };

function renderProfile() {
  const u = Auth.current;
  const subs = Store.getAll("subscriptions").filter(s=>s.userId===u.id);
  const sessions = Store.getAll("sessions").filter(s=>s.booked?.includes(u.id));
  $("profileContent").innerHTML = `
    <div class="profile-card">
      <div class="logo-mark" style="width:56px;height:56px;font-size:22px">${esc(u.name[0])}</div>
      <div>
        <h4>${esc(u.name)}</h4>
        <span class="status-pill status-active">${roleLabel(u.role)}</span>
      </div>
      <div class="meta-grid">
        <span>📧 ${esc(u.email)}</span>
        <span>📱 ${esc(u.phone??"-")}</span>
        <span>📅 Înregistrat: ${fmtDate(u.joinDate)}</span>
        ${u.specialization?`<span>🎯 ${esc(u.specialization)}</span>`:""}
      </div>
    </div>
    <div style="display:grid;gap:16px">
      <div class="profile-card">
        <h4>Abonamente (${subs.length})</h4>
        ${subs.length ? subs.map(s=>`<div class="list-item split"><div><strong>${esc(s.type)}</strong><span>${fmtDate(s.start)} – ${fmtDate(s.end)}</span></div><span class="status-pill status-${s.status}">${s.status}</span></div>`).join("") : `<div class="empty-state">Niciun abonament.</div>`}
      </div>
      <div class="profile-card">
        <h4>Sesiuni rezervate (${sessions.length})</h4>
        ${sessions.length ? sessions.map(s=>`<div class="list-item"><strong>${esc(s.title)}</strong><span>${fmtDate(s.date)} la ${s.time}</span></div>`).join("") : `<div class="empty-state">Nicio rezervare.</div>`}
      </div>
    </div>`;

  $("editOwnProfileBtn").onclick = () => editProfileModal(u);
}

function editProfileModal(u) {
  openModal("Editează profilul", `
    <form class="form-grid">
      <div class="two-col">
        <label>Nume<input name="name" value="${esc(u.name)}" required></label>
        <label>Telefon<input name="phone" value="${esc(u.phone??'')}"></label>
      </div>
      <label>Email<input type="email" name="email" value="${esc(u.email)}" required></label>
      <label>Parolă nouă (opțional)<input type="password" name="password" placeholder="Lasă gol pentru a nu schimba"></label>
      <div class="form-actions">
        <button type="button" class="ghost-btn" onclick="closeModal()">Anulează</button>
        <button type="submit" class="primary-btn">Salvează</button>
      </div>
    </form>
  `, (e) => {
    const fd = new FormData(e.target);
    const patch = {name: fd.get("name"), phone: fd.get("phone"), email: fd.get("email")};
    if (fd.get("password")) patch.password = fd.get("password");
    Store.updateItem("users", u.id, patch);
    Auth.current = {...Auth.current, ...patch};
    toast("Profil actualizat");
    closeModal();
    renderUserMini();
    renderProfile();
  });
}

function downloadFile(filename, content, mimeType) {
  const blob = new Blob([content], {type: mimeType});
  const url = URL.createObjectURL(blob);
  const a = document.createElement("a");
  a.href = url; a.download = filename; a.click();
  setTimeout(() => URL.revokeObjectURL(url), 1000);
}

Object.assign(window, {
  bookSession, cancelBooking, editSessionForm, deleteSession,
  editUserForm, deleteUser, toggleUserStatus, viewUserHistory,
  suspendSub, activateSub, deleteSub,
  editRoomForm, deleteRoom, editEquipmentForm, deleteEquipment,
  installPlugin, uninstallPlugin, deletePlugin,
  closeModal, navigateTo,
});
