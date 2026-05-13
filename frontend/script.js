const storageKey = "kim_state_v2";

const state = {
  currentUserId: null,
  users: [],
  trainers: [],
  sessions: [],
  subscriptions: [],
  rooms: [],
  equipment: [],
  plugins: [],
  activity: []
};

const roles = {
  admin: "Administrator",
  trainer: "Antrenor/Terapeut",
  member: "Membru"
};

const sessionTypes = {
  fitness: "Fitness",
  strength: "Forță",
  kineto: "Kinetoterapie",
  mixed: "Mixt"
};

const subscriptionTypes = {
  fitness: "Fitness",
  strength: "Forță",
  kineto: "Kinetoterapie",
  mixed: "Mixt"
};

const navByRole = {
  admin: [
    ["adminDashboardView", "Dashboard administrator"],
    ["usersView", "Utilizatori"],
    ["scheduleView", "Programări și orar"],
    ["subscriptionsView", "Abonamente și plăți"],
    ["trainersView", "Antrenori/Terapeuți"],
    ["resourcesView", "Săli și echipamente"],
    ["reportsView", "Rapoarte"],
    ["pluginsView", "Plugin-uri"],
    ["profileView", "Profil"]
  ],
  trainer: [
    ["trainerDashboardView", "Dashboard specialist"],
    ["scheduleView", "Sesiunile mele"],
    ["trainersView", "Profiluri specialiști"],
    ["resourcesView", "Săli și echipamente"],
    ["reportsView", "Statistici sesiuni"],
    ["pluginsView", "Plugin-uri"],
    ["profileView", "Profil"]
  ],
  member: [
    ["memberDashboardView", "Dashboard membru"],
    ["scheduleView", "Rezervă sesiuni"],
    ["subscriptionsView", "Abonamentul meu"],
    ["trainersView", "Antrenori/Terapeuți"],
    ["profileView", "Profil"]
  ]
};

const viewTitles = {
  adminDashboardView: ["Dashboard administrator", "Administrare completă sală fitness, forță și kinetoterapie"],
  trainerDashboardView: ["Dashboard antrenor/terapeut", "Sesiuni, membri programați și program personal"],
  memberDashboardView: ["Dashboard membru", "Rezervări, abonamente și istoric personal"],
  usersView: ["Utilizatori", "Înregistrare, autentificare, profiluri și roluri"],
  scheduleView: ["Programări și orar", "Vizualizare program, rezervare, creare și conflicte de orar"],
  subscriptionsView: ["Abonamente și plăți", "Activare, suspendare, expirare și istoric abonamente"],
  trainersView: ["Antrenori/Terapeuți", "Specializări, program, import și export CSV/XML"],
  resourcesView: ["Săli și echipamente", "Capacitate, disponibilitate și inventar"],
  reportsView: ["Rapoarte și statistici", "CSV/XML pentru date și PNG/WebP pentru diagrame"],
  pluginsView: ["Plugin-uri", "Extensii simulate pe bază de micro-servicii Web"],
  profileView: ["Profil", "Date personale și istoricul activităților"]
};

const $ = selector => document.querySelector(selector);
const $$ = selector => Array.from(document.querySelectorAll(selector));

function uid(prefix) {
  return `${prefix}_${Date.now()}_${Math.random().toString(16).slice(2)}`;
}

function today() {
  return new Date().toISOString().slice(0, 10);
}

function addDays(days) {
  const d = new Date();
  d.setDate(d.getDate() + days);
  return d.toISOString().slice(0, 10);
}

function currentUser() {
  return state.users.find(user => user.id === state.currentUserId);
}

function currentTrainer() {
  const user = currentUser();
  if (!user) return null;
  return state.trainers.find(trainer => trainer.userId === user.id || trainer.email.toLowerCase() === user.email.toLowerCase());
}

function canAdmin() {
  return currentUser()?.role === "admin";
}

function canManageSessions() {
  return ["admin", "trainer"].includes(currentUser()?.role);
}

function saveState() {
  localStorage.setItem(storageKey, JSON.stringify(state));
}

function loadState() {
  const raw = localStorage.getItem(storageKey);
  if (!raw) {
    seedDemoData();
    return;
  }
  Object.assign(state, JSON.parse(raw));
  if (!state.users?.length) seedDemoData();
}

function seedDemoData() {
  state.currentUserId = null;
  state.users = [
    { id: "u_admin", name: "Administrator KIM", email: "admin@kim.ro", password: "admin123", phone: "0700000000", role: "admin", status: "active", createdAt: addDays(-120), address: "Iași" },
    { id: "u_trainer_1", name: "Radu Enache", email: "radu@kim.ro", password: "trainer123", phone: "0733333333", role: "trainer", status: "active", createdAt: addDays(-100), address: "Iași" },
    { id: "u_trainer_2", name: "Elena Dumitru", email: "elena@kim.ro", password: "trainer123", phone: "0744444444", role: "trainer", status: "active", createdAt: addDays(-90), address: "Iași" },
    { id: "u_member_1", name: "Andrei Popescu", email: "andrei@kim.ro", password: "member123", phone: "0711111111", role: "member", status: "active", createdAt: addDays(-40), address: "Iași" },
    { id: "u_member_2", name: "Maria Ionescu", email: "maria@kim.ro", password: "member123", phone: "0722222222", role: "member", status: "active", createdAt: addDays(-35), address: "Iași" },
    { id: "u_member_3", name: "Vlad Matei", email: "vlad@kim.ro", password: "member123", phone: "0755555555", role: "member", status: "suspended", createdAt: addDays(-22), address: "Iași" }
  ];
  state.trainers = [
    { id: "t_1", userId: "u_trainer_1", name: "Radu Enache", email: "radu@kim.ro", phone: "0733333333", specializations: ["Fitness", "Forță", "Functional training"], schedule: "Luni-Vineri 08:00-16:00", status: "active", bio: "Antrenor specializat în forță și condiționare fizică." },
    { id: "t_2", userId: "u_trainer_2", name: "Elena Dumitru", email: "elena@kim.ro", phone: "0744444444", specializations: ["Kinetoterapie", "Recuperare post-traumatică", "Postură"], schedule: "Luni-Vineri 12:00-20:00", status: "active", bio: "Terapeut cu experiență în recuperare și reeducare posturală." },
    { id: "t_3", userId: "", name: "Mihai Stan", email: "mihai@kim.ro", phone: "0766666666", specializations: ["Forță", "Body recomposition"], schedule: "Marți-Sâmbătă 10:00-18:00", status: "active", bio: "Specialist în creștere musculară și planuri de forță." }
  ];
  state.rooms = [
    { id: "r_1", name: "Sala Fitness A", capacity: 20, availability: "Disponibilă", type: "fitness" },
    { id: "r_2", name: "Zona Forță", capacity: 14, availability: "Disponibilă", type: "strength" },
    { id: "r_3", name: "Cabinet Kinetoterapie", capacity: 4, availability: "Disponibilă", type: "kineto" },
    { id: "r_4", name: "Studio Mixt", capacity: 10, availability: "Disponibilă", type: "mixed" }
  ];
  state.equipment = [
    { id: "e_1", name: "Bandă alergare", roomId: "r_1", quantity: 4, status: "funcțional" },
    { id: "e_2", name: "Rack genuflexiuni", roomId: "r_2", quantity: 2, status: "funcțional" },
    { id: "e_3", name: "Masă kinetoterapie", roomId: "r_3", quantity: 2, status: "funcțional" },
    { id: "e_4", name: "Benzi elastice recuperare", roomId: "r_3", quantity: 10, status: "funcțional" }
  ];
  state.sessions = [
    { id: "s_1", title: "Fitness Circuit", type: "fitness", date: today(), start: "10:00", end: "11:00", trainerId: "t_1", roomId: "r_1", capacity: 12, bookedUserIds: ["u_member_1"], status: "active" },
    { id: "s_2", title: "Recuperare lombară", type: "kineto", date: today(), start: "13:00", end: "14:00", trainerId: "t_2", roomId: "r_3", capacity: 3, bookedUserIds: ["u_member_2"], status: "active" },
    { id: "s_3", title: "Strength Basics", type: "strength", date: addDays(1), start: "18:00", end: "19:00", trainerId: "t_3", roomId: "r_2", capacity: 10, bookedUserIds: [], status: "active" },
    { id: "s_4", title: "Mobility mixt", type: "mixed", date: addDays(2), start: "09:00", end: "10:00", trainerId: "t_2", roomId: "r_4", capacity: 8, bookedUserIds: ["u_member_1", "u_member_2"], status: "active" },
    { id: "s_5", title: "Forță avansat", type: "strength", date: addDays(3), start: "17:00", end: "18:00", trainerId: "t_1", roomId: "r_2", capacity: 8, bookedUserIds: ["u_member_2"], status: "active" }
  ];
  state.subscriptions = [
    { id: "sub_1", userId: "u_member_1", type: "mixed", start: addDays(-10), end: addDays(20), price: 350, status: "active" },
    { id: "sub_2", userId: "u_member_2", type: "kineto", start: addDays(-4), end: addDays(26), price: 420, status: "active" },
    { id: "sub_3", userId: "u_member_1", type: "fitness", start: addDays(-70), end: addDays(-40), price: 180, status: "expired" },
    { id: "sub_4", userId: "u_member_3", type: "strength", start: addDays(-15), end: addDays(15), price: 210, status: "suspended" }
  ];
  state.plugins = [
    { id: "p_1", name: "CSV/XML Export Service", category: "Export date", endpoint: "/plugins/export-service", status: "installed", description: "Simulează exportul de date pentru antrenori, terapeuți și rapoarte." },
    { id: "p_2", name: "Chart Renderer", category: "Diagrame", endpoint: "/plugins/chart-renderer", status: "installed", description: "Generează diagrame front-end și permite export PNG/WebP." },
    { id: "p_3", name: "Email Notification Gateway", category: "Notificări", endpoint: "/plugins/email-gateway", status: "installed", description: "Simulează notificările prin poștă electronică folosind clientul local de email." },
    { id: "p_4", name: "Schedule Conflict Checker", category: "Programări", endpoint: "/plugins/conflict-checker", status: "installed", description: "Verifică suprapunerea rezervărilor și ocuparea sălilor." }
  ];
  state.activity = [
    { id: uid("a"), title: "Date demo inițializate", text: "Au fost create interfețe demo pentru administrator, antrenor/terapeut și membru.", date: new Date().toISOString() }
  ];
  saveState();
}

function init() {
  loadState();
  bindAuth();
  bindGlobalActions();
  bindFilters();
  if (currentUser()) showApp();
  else showAuth();
}

function bindAuth() {
  $$(".auth-tab").forEach(tab => {
    tab.addEventListener("click", () => {
      $$(".auth-tab").forEach(item => item.classList.remove("active"));
      $$(".auth-form").forEach(form => form.classList.remove("active"));
      tab.classList.add("active");
      $(`#${tab.dataset.authTab}Form`).classList.add("active");
    });
  });

  const demo = {
    admin: ["admin@kim.ro", "admin123"],
    trainer: ["radu@kim.ro", "trainer123"],
    member: ["andrei@kim.ro", "member123"]
  };

  $$("[data-demo]").forEach(btn => {
    btn.addEventListener("click", () => {
      const [email, pass] = demo[btn.dataset.demo];
      $("#loginEmail").value = email;
      $("#loginPassword").value = pass;
    });
  });

  $("#loginForm").addEventListener("submit", event => {
    event.preventDefault();
    const email = $("#loginEmail").value.trim().toLowerCase();
    const password = $("#loginPassword").value;
    const user = state.users.find(item => item.email.toLowerCase() === email && item.password === password && item.status === "active");
    if (!user) {
      toast("Autentificare eșuată", "Verifică emailul, parola sau statusul contului.", "error");
      return;
    }
    state.currentUserId = user.id;
    addActivity("Autentificare", `${user.name} a intrat în aplicație.`);
    saveState();
    showApp();
  });

  $("#registerForm").addEventListener("submit", event => {
    event.preventDefault();
    const email = $("#registerEmail").value.trim().toLowerCase();
    if (state.users.some(user => user.email.toLowerCase() === email)) {
      toast("Email existent", "Există deja un cont cu această adresă.", "error");
      return;
    }
    const user = {
      id: uid("u"),
      name: $("#registerName").value.trim(),
      phone: $("#registerPhone").value.trim(),
      email,
      password: $("#registerPassword").value,
      role: $("#registerRole").value,
      status: "active",
      createdAt: today(),
      address: ""
    };
    state.users.push(user);
    if (user.role === "trainer") {
      state.trainers.push({
        id: uid("t"),
        userId: user.id,
        name: user.name,
        email: user.email,
        phone: user.phone,
        specializations: ["General"],
        schedule: "Nedefinit",
        status: "active",
        bio: ""
      });
    }
    state.currentUserId = user.id;
    addActivity("Cont nou", `${user.name} s-a înregistrat ca ${roles[user.role]}.`);
    saveState();
    showApp();
  });
}

function showAuth() {
  $("#authScreen").classList.remove("hidden");
  $("#appShell").classList.add("hidden");
}

function showApp() {
  $("#authScreen").classList.add("hidden");
  $("#appShell").classList.remove("hidden");
  buildRoleNav();
  const firstView = navByRole[currentUser().role][0][0];
  setView(firstView);
  renderAll();
}

function buildRoleNav() {
  const user = currentUser();
  $("#sidebarNav").innerHTML = navByRole[user.role].map(([view, label]) => `<button class="nav-link" data-view="${view}">${escapeHtml(label)}</button>`).join("");
  $$(".nav-link").forEach(btn => btn.addEventListener("click", () => setView(btn.dataset.view)));
}

function bindGlobalActions() {
  $("#logoutBtn").addEventListener("click", () => {
    state.currentUserId = null;
    saveState();
    showAuth();
  });

  $("#resetDemoBtn").addEventListener("click", () => {
    seedDemoData();
    toast("Reset efectuat", "Datele demo au fost reîncărcate.");
    showAuth();
  });

  $("#menuToggle").addEventListener("click", () => $("#sidebar").classList.toggle("open"));
  $("#closeModal").addEventListener("click", closeModal);
  $(".modal-backdrop").addEventListener("click", closeModal);

  document.body.addEventListener("click", event => {
    const jump = event.target.closest("[data-jump]");
    const open = event.target.closest("[data-open]");
    if (jump) setView(jump.dataset.jump);
    if (open?.dataset.open === "session") openSessionForm();
  });

  $("#openUserModal").addEventListener("click", () => openUserForm());
  $("#openSessionModal").addEventListener("click", () => openSessionForm());
  $("#openSubscriptionModal").addEventListener("click", () => openSubscriptionForm());
  $("#openTrainerModal").addEventListener("click", () => openTrainerForm());
  $("#openRoomModal").addEventListener("click", () => openRoomForm());
  $("#openEquipmentModal").addEventListener("click", () => openEquipmentForm());
  $("#openPluginModal").addEventListener("click", () => openPluginForm());
  $("#editOwnProfileBtn").addEventListener("click", () => openOwnProfileForm());

  $("#exportTrainersCsv").addEventListener("click", () => downloadFile("kim_traineri.csv", trainersToCsv(), "text/csv"));
  $("#exportTrainersXml").addEventListener("click", () => downloadFile("kim_traineri.xml", trainersToXml(), "text/xml"));
  $("#importTrainersCsv").addEventListener("change", importTrainersCsv);
  $("#importTrainersXml").addEventListener("change", importTrainersXml);

  $("#exportReportsCsv").addEventListener("click", () => downloadFile("kim_rapoarte.csv", reportsToCsv(), "text/csv"));
  $("#exportReportsXml").addEventListener("click", () => downloadFile("kim_rapoarte.xml", reportsToXml(), "text/xml"));
  $("#exportChartPng").addEventListener("click", () => exportChart("image/png", "kim_diagrama.png"));
  $("#exportChartWebp").addEventListener("click", () => exportChart("image/webp", "kim_diagrama.webp"));
}

function bindFilters() {
  ["userSearch", "userRoleFilter", "userStatusFilter", "sessionSearch", "sessionTypeFilter", "sessionStatusFilter", "sessionDateFilter", "trainerSearch"].forEach(id => {
    $(`#${id}`).addEventListener("input", renderAll);
  });
}

function setView(viewId) {
  $$(".view").forEach(view => view.classList.remove("active"));
  const view = $(`#${viewId}`);
  if (!view) return;
  view.classList.add("active");
  $$(".nav-link").forEach(link => link.classList.toggle("active", link.dataset.view === viewId));
  $("#pageTitle").textContent = viewTitles[viewId]?.[0] || "KIM";
  $("#pageSubtitle").textContent = viewTitles[viewId]?.[1] || "";
  $("#sidebar").classList.remove("open");
  renderAll();
}

function renderAll() {
  const user = currentUser();
  if (!user) return;
  $("#currentUserMini").innerHTML = `<strong>${escapeHtml(user.name)}</strong><span>${escapeHtml(user.email)}</span><span>${escapeHtml(roles[user.role])}</span>`;
  $("#roleBadge").textContent = roles[user.role];
  $("#dateBadge").textContent = new Date().toLocaleDateString("ro-RO");
  renderDashboards();
  renderUsers();
  renderSessions();
  renderSubscriptions();
  renderTrainers();
  renderResources();
  renderReports();
  renderPlugins();
  renderProfile();
  applyPermissions();
}

function applyPermissions() {
  $("#openUserModal").style.display = canAdmin() ? "" : "none";
  $("#openSubscriptionModal").style.display = canAdmin() ? "" : "none";
  $("#openSessionModal").style.display = canManageSessions() ? "" : "none";
  $("#openTrainerModal").style.display = canAdmin() ? "" : "none";
  $("#openRoomModal").style.display = canAdmin() ? "" : "none";
  $("#openEquipmentModal").style.display = canAdmin() ? "" : "none";
  $("#openPluginModal").style.display = canAdmin() ? "" : "none";
  $("#exportTrainersCsv").style.display = canManageSessions() ? "" : "none";
  $("#exportTrainersXml").style.display = canManageSessions() ? "" : "none";
  $("#importTrainersCsv").style.display = canAdmin() ? "" : "none";
  $("#importTrainersXml").style.display = canAdmin() ? "" : "none";
}

function renderDashboards() {
  const activeUsers = state.users.filter(user => user.status === "active").length;
  const bookings = getAllBookings();
  const month = today().slice(0, 7);
  const monthBookings = bookings.filter(item => item.session.date.slice(0, 7) === month).length;
  const activeSubs = state.subscriptions.filter(sub => getSubscriptionStatus(sub) === "active").length;
  const totalCapacity = state.sessions.reduce((sum, session) => sum + Number(session.capacity), 0);
  const totalBooked = state.sessions.reduce((sum, session) => sum + session.bookedUserIds.length, 0);

  $("#adminActiveUsers").textContent = activeUsers;
  $("#adminMonthBookings").textContent = monthBookings;
  $("#adminActiveSubs").textContent = activeSubs;
  $("#adminOccupancy").textContent = totalCapacity ? `${Math.round(totalBooked / totalCapacity * 100)}%` : "0%";

  const upcoming = state.sessions.filter(session => session.date >= today()).sort(compareSessionDate).slice(0, 6);
  $("#adminUpcomingSessions").innerHTML = upcoming.map(sessionListItem).join("") || emptyState("Nu există sesiuni viitoare.");
  $("#adminActivityFeed").innerHTML = state.activity.slice(0, 8).map(item => `<div class="list-item"><strong>${escapeHtml(item.title)}</strong><span>${escapeHtml(item.text)}</span></div>`).join("") || emptyState("Nu există activitate.");

  const trainer = currentTrainer();
  const trainerSessions = trainer ? state.sessions.filter(session => session.trainerId === trainer.id) : [];
  const trainerCapacity = trainerSessions.reduce((sum, session) => sum + Number(session.capacity), 0);
  const trainerBooked = trainerSessions.reduce((sum, session) => sum + session.bookedUserIds.length, 0);
  $("#trainerTotalSessions").textContent = trainerSessions.length;
  $("#trainerTotalBookings").textContent = trainerBooked;
  $("#trainerTodaySessions").textContent = trainerSessions.filter(session => session.date === today()).length;
  $("#trainerOccupancy").textContent = trainerCapacity ? `${Math.round(trainerBooked / trainerCapacity * 100)}%` : "0%";
  $("#trainerAgenda").innerHTML = trainerSessions.sort(compareSessionDate).map(sessionCard).join("") || emptyState("Nu ai sesiuni alocate.");
  const trainerMembers = trainerSessions.flatMap(session => session.bookedUserIds.map(userId => ({ user: getUser(userId), session })));
  $("#trainerBookedMembers").innerHTML = trainerMembers.map(item => `<div class="list-item split"><div><strong>${escapeHtml(item.user?.name || "-")}</strong><span>${escapeHtml(item.session.title)} · ${formatDate(item.session.date)} · ${item.session.start}</span></div><span class="type-pill">${escapeHtml(sessionTypes[item.session.type])}</span></div>`).join("") || emptyState("Nu există membri programați.");

  const user = currentUser();
  const memberSessions = state.sessions.filter(session => session.bookedUserIds.includes(user.id));
  const memberActive = state.subscriptions.find(sub => sub.userId === user.id && getSubscriptionStatus(sub) === "active");
  const availableSessions = state.sessions.filter(session => session.status === "active" && session.date >= today() && session.bookedUserIds.length < Number(session.capacity)).length;
  $("#memberBookings").textContent = memberSessions.length;
  $("#memberActiveSub").textContent = memberActive ? subscriptionTypes[memberActive.type] : "-";
  $("#memberAvailableSessions").textContent = availableSessions;
  $("#memberHistoryCount").textContent = memberSessions.length + state.subscriptions.filter(sub => sub.userId === user.id).length;
  $("#memberRecommendedSessions").innerHTML = state.sessions.filter(session => session.status === "active" && session.date >= today()).sort(compareSessionDate).slice(0, 4).map(sessionCard).join("") || emptyState("Nu există sesiuni disponibile.");
  $("#memberSubscriptionPanel").innerHTML = state.subscriptions.filter(sub => sub.userId === user.id).map(subscriptionListItem).join("") || emptyState("Nu există abonamente în istoric.");
}

function renderUsers() {
  const q = $("#userSearch").value.trim().toLowerCase();
  const role = $("#userRoleFilter").value;
  const status = $("#userStatusFilter").value;
  let users = [...state.users];
  if (role !== "all") users = users.filter(user => user.role === role);
  if (status !== "all") users = users.filter(user => user.status === status);
  if (q) users = users.filter(user => [user.name, user.email, user.phone, roles[user.role], user.status].join(" ").toLowerCase().includes(q));
  $("#usersTable").innerHTML = users.map(user => {
    const activityCount = state.sessions.filter(session => session.bookedUserIds.includes(user.id)).length + state.subscriptions.filter(sub => sub.userId === user.id).length;
    return `<tr>
      <td><strong>${escapeHtml(user.name)}</strong></td>
      <td>${escapeHtml(user.email)}</td>
      <td>${escapeHtml(user.phone)}</td>
      <td>${escapeHtml(roles[user.role])}</td>
      <td><span class="status-pill ${user.status === "active" ? "status-active" : "status-suspended"}">${user.status === "active" ? "Activ" : "Suspendat"}</span></td>
      <td>${activityCount}</td>
      <td><div class="row-actions">${userActions(user)}</div></td>
    </tr>`;
  }).join("") || `<tr><td colspan="7">${emptyState("Nu există utilizatori pentru filtrele selectate.")}</td></tr>`;
  $$("#usersTable [data-user-action]").forEach(btn => btn.addEventListener("click", handleUserAction));
}

function userActions(user) {
  if (!canAdmin()) return `<button class="small-btn" data-user-action="history" data-id="${user.id}">Istoric</button>`;
  return `
    <button class="small-btn" data-user-action="edit" data-id="${user.id}">Editare</button>
    <button class="small-btn" data-user-action="toggle" data-id="${user.id}">${user.status === "active" ? "Suspendă" : "Activează"}</button>
    <button class="small-btn" data-user-action="history" data-id="${user.id}">Istoric</button>
    <button class="small-btn danger" data-user-action="delete" data-id="${user.id}">Șterge</button>
  `;
}

function handleUserAction(event) {
  const id = event.currentTarget.dataset.id;
  const action = event.currentTarget.dataset.userAction;
  const user = getUser(id);
  if (!user) return;
  if (action === "edit") openUserForm(user);
  if (action === "toggle") {
    if (user.id === state.currentUserId) return toast("Acțiune blocată", "Nu poți suspenda contul autentificat.", "error");
    user.status = user.status === "active" ? "suspended" : "active";
    addActivity("Status utilizator modificat", `${user.name}: ${user.status}.`);
    saveState();
    renderAll();
  }
  if (action === "delete") {
    if (user.id === state.currentUserId) return toast("Acțiune blocată", "Nu poți șterge contul autentificat.", "error");
    state.users = state.users.filter(item => item.id !== id);
    state.sessions.forEach(session => session.bookedUserIds = session.bookedUserIds.filter(userId => userId !== id));
    state.subscriptions = state.subscriptions.filter(sub => sub.userId !== id);
    addActivity("Utilizator șters", user.name);
    saveState();
    renderAll();
  }
  if (action === "history") openHistoryModal(user);
}

function renderSessions() {
  const q = $("#sessionSearch").value.trim().toLowerCase();
  const type = $("#sessionTypeFilter").value;
  const status = $("#sessionStatusFilter").value;
  const date = $("#sessionDateFilter").value;
  let sessions = [...state.sessions];
  const user = currentUser();
  const trainer = currentTrainer();

  if (user.role === "trainer" && trainer) sessions = sessions.filter(session => session.trainerId === trainer.id);
  if (type !== "all") sessions = sessions.filter(session => session.type === type);
  if (status !== "all") sessions = sessions.filter(session => session.status === status);
  if (date) sessions = sessions.filter(session => session.date === date);
  if (q) sessions = sessions.filter(session => [session.title, sessionTypes[session.type], getTrainer(session.trainerId)?.name, getRoom(session.roomId)?.name].join(" ").toLowerCase().includes(q));

  $("#scheduleCards").innerHTML = sessions.sort(compareSessionDate).map(sessionCard).join("") || emptyState("Nu există sesiuni pentru filtrele selectate.");
  $$("#scheduleCards [data-session-action], #trainerAgenda [data-session-action], #memberRecommendedSessions [data-session-action]").forEach(btn => btn.addEventListener("click", handleSessionAction));
}

function sessionCard(session) {
  const trainer = getTrainer(session.trainerId);
  const room = getRoom(session.roomId);
  const user = currentUser();
  const booked = session.bookedUserIds.includes(user.id);
  const available = Number(session.capacity) - session.bookedUserIds.length;
  const canEdit = canAdmin() || (user.role === "trainer" && currentTrainer()?.id === session.trainerId);
  return `<div class="session-card">
    <div class="tags">
      <span class="type-pill">${escapeHtml(sessionTypes[session.type])}</span>
      <span class="status-pill ${session.status === "active" ? "status-active" : "status-cancelled"}">${session.status === "active" ? "Activă" : "Anulată"}</span>
    </div>
    <h4>${escapeHtml(session.title)}</h4>
    <div class="meta-grid">
      <span><b>Data:</b> ${formatDate(session.date)} · ${session.start}-${session.end}</span>
      <span><b>Specialist:</b> ${escapeHtml(trainer?.name || "-")}</span>
      <span><b>Sală/Zonă:</b> ${escapeHtml(room?.name || "-")}</span>
      <span><b>Locuri:</b> ${session.bookedUserIds.length}/${session.capacity} rezervate</span>
    </div>
    <div class="row-actions">
      ${session.status === "active" && user.role === "member" ? `<button class="small-btn primary" data-session-action="${booked ? "cancelBooking" : "book"}" data-id="${session.id}">${booked ? "Anulează rezervarea" : "Rezervă"}</button>` : ""}
      ${session.status === "active" && user.role === "admin" ? `<button class="small-btn primary" data-session-action="bookAsAdmin" data-id="${session.id}">Adaugă membru</button>` : ""}
      ${canEdit ? `<button class="small-btn" data-session-action="edit" data-id="${session.id}">Editare</button><button class="small-btn danger" data-session-action="cancel" data-id="${session.id}">Anulează</button>` : ""}
      ${available <= 0 && !booked ? `<span class="status-pill status-expired">Complet</span>` : ""}
    </div>
  </div>`;
}

function handleSessionAction(event) {
  const id = event.currentTarget.dataset.id;
  const action = event.currentTarget.dataset.sessionAction;
  const session = state.sessions.find(item => item.id === id);
  if (!session) return;

  if (action === "book") bookSession(session, currentUser().id);
  if (action === "cancelBooking") cancelBooking(session, currentUser().id);
  if (action === "bookAsAdmin") openAdminBookingModal(session);
  if (action === "edit") openSessionForm(session);
  if (action === "cancel") {
    session.status = "cancelled";
    addActivity("Sesiune anulată", session.title);
    saveState();
    renderAll();
    toast("Sesiune anulată", session.title);
  }
}

function bookSession(session, userId) {
  const user = getUser(userId);
  if (!user) return;
  if (session.bookedUserIds.includes(userId)) return;
  if (session.bookedUserIds.length >= Number(session.capacity)) {
    toast("Sesiune completă", "Nu mai sunt locuri disponibile.", "error");
    return;
  }
  const conflict = state.sessions.find(item => item.id !== session.id && item.status === "active" && item.bookedUserIds.includes(userId) && item.date === session.date && overlaps(item.start, item.end, session.start, session.end));
  if (conflict) {
    toast("Conflict de orar", `${user.name} are deja rezervarea: ${conflict.title}.`, "error");
    return;
  }
  session.bookedUserIds.push(userId);
  addActivity("Rezervare reușită", `${user.name} a rezervat ${session.title}.`);
  saveState();
  renderAll();
  prepareEmail(user, session);
  toast("Rezervare reușită", `${session.title} a fost rezervată.`);
}

function cancelBooking(session, userId) {
  const user = getUser(userId);
  session.bookedUserIds = session.bookedUserIds.filter(id => id !== userId);
  addActivity("Rezervare anulată", `${user?.name || "-"} a anulat rezervarea la ${session.title}.`);
  saveState();
  renderAll();
  toast("Rezervare anulată", "Locul a fost eliberat.");
}

function renderSubscriptions() {
  const visibleSubs = currentUser().role === "member" ? state.subscriptions.filter(sub => sub.userId === currentUser().id) : state.subscriptions;
  $("#subscriptionSummary").innerHTML = Object.keys(subscriptionTypes).map(type => {
    const count = visibleSubs.filter(sub => sub.type === type && getSubscriptionStatus(sub) === "active").length;
    return `<div class="stat-card"><span>${escapeHtml(subscriptionTypes[type])}</span><strong>${count}</strong></div>`;
  }).join("");

  $("#subscriptionsTable").innerHTML = visibleSubs.map(sub => {
    const user = getUser(sub.userId);
    const status = getSubscriptionStatus(sub);
    return `<tr>
      <td>${escapeHtml(user?.name || "-")}</td>
      <td>${escapeHtml(subscriptionTypes[sub.type])}</td>
      <td>${formatDate(sub.start)}</td>
      <td>${formatDate(sub.end)}</td>
      <td>${Number(sub.price).toFixed(2)} lei</td>
      <td><span class="status-pill status-${status}">${subscriptionStatusLabel(status)}</span></td>
      <td><div class="row-actions">${canAdmin() ? `<button class="small-btn" data-sub-action="edit" data-id="${sub.id}">Editare</button><button class="small-btn" data-sub-action="toggle" data-id="${sub.id}">${status === "suspended" ? "Activează" : "Suspendă"}</button><button class="small-btn danger" data-sub-action="delete" data-id="${sub.id}">Șterge</button>` : "Vizualizare"}</div></td>
    </tr>`;
  }).join("") || `<tr><td colspan="7">${emptyState("Nu există abonamente.")}</td></tr>`;

  $$("#subscriptionsTable [data-sub-action]").forEach(btn => btn.addEventListener("click", handleSubscriptionAction));
}

function handleSubscriptionAction(event) {
  const sub = state.subscriptions.find(item => item.id === event.currentTarget.dataset.id);
  const action = event.currentTarget.dataset.subAction;
  if (!sub) return;
  if (action === "edit") openSubscriptionForm(sub);
  if (action === "toggle") {
    sub.status = getSubscriptionStatus(sub) === "suspended" ? "active" : "suspended";
    addActivity("Status abonament modificat", `${getUser(sub.userId)?.name || "-"} - ${subscriptionTypes[sub.type]}`);
    saveState();
    renderAll();
  }
  if (action === "delete") {
    state.subscriptions = state.subscriptions.filter(item => item.id !== sub.id);
    addActivity("Abonament șters", `${getUser(sub.userId)?.name || "-"} - ${subscriptionTypes[sub.type]}`);
    saveState();
    renderAll();
  }
}

function renderTrainers() {
  const q = $("#trainerSearch").value.trim().toLowerCase();
  let trainers = [...state.trainers];
  if (q) trainers = trainers.filter(trainer => [trainer.name, trainer.email, trainer.phone, trainer.schedule, trainer.bio, trainer.specializations.join(" ")].join(" ").toLowerCase().includes(q));
  $("#trainersGrid").innerHTML = trainers.map(trainer => {
    const canEdit = canAdmin() || currentTrainer()?.id === trainer.id;
    return `<div class="trainer-card">
      <div class="tags">
        <span class="status-pill ${trainer.status === "active" ? "status-active" : "status-suspended"}">${trainer.status === "active" ? "Activ" : "Suspendat"}</span>
      </div>
      <h4>${escapeHtml(trainer.name)}</h4>
      <div class="meta-grid">
        <span><b>Email:</b> ${escapeHtml(trainer.email)}</span>
        <span><b>Telefon:</b> ${escapeHtml(trainer.phone)}</span>
        <span><b>Program:</b> ${escapeHtml(trainer.schedule)}</span>
        <span><b>Sesiuni:</b> ${state.sessions.filter(session => session.trainerId === trainer.id).length}</span>
        <span>${escapeHtml(trainer.bio || "")}</span>
      </div>
      <div class="tags">${trainer.specializations.map(item => `<span class="type-pill">${escapeHtml(item)}</span>`).join("")}</div>
      <div class="row-actions">${canEdit ? `<button class="small-btn" data-trainer-action="edit" data-id="${trainer.id}">Editare</button>` : ""}${canAdmin() ? `<button class="small-btn danger" data-trainer-action="delete" data-id="${trainer.id}">Șterge</button>` : ""}</div>
    </div>`;
  }).join("") || emptyState("Nu există antrenori/terapeuți.");

  $$("#trainersGrid [data-trainer-action]").forEach(btn => btn.addEventListener("click", event => {
    const trainer = getTrainer(event.currentTarget.dataset.id);
    if (event.currentTarget.dataset.trainerAction === "edit") openTrainerForm(trainer);
    if (event.currentTarget.dataset.trainerAction === "delete") {
      state.trainers = state.trainers.filter(item => item.id !== trainer.id);
      addActivity("Specialist șters", trainer.name);
      saveState();
      renderAll();
    }
  }));
}

function renderResources() {
  $("#roomsGrid").innerHTML = state.rooms.map(room => `<div class="room-card">
    <span class="type-pill">${escapeHtml(sessionTypes[room.type])}</span>
    <h4>${escapeHtml(room.name)}</h4>
    <div class="meta-grid">
      <span><b>Capacitate:</b> ${room.capacity}</span>
      <span><b>Disponibilitate:</b> ${escapeHtml(room.availability)}</span>
      <span><b>Sesiuni alocate:</b> ${state.sessions.filter(session => session.roomId === room.id).length}</span>
    </div>
    <div class="row-actions">${canAdmin() ? `<button class="small-btn" data-room-action="edit" data-id="${room.id}">Editare</button><button class="small-btn danger" data-room-action="delete" data-id="${room.id}">Șterge</button>` : ""}</div>
  </div>`).join("");

  $("#equipmentList").innerHTML = state.equipment.map(eq => `<div class="list-item split">
    <div><strong>${escapeHtml(eq.name)}</strong><span>${escapeHtml(getRoom(eq.roomId)?.name || "-")} · cantitate ${eq.quantity} · ${escapeHtml(eq.status)}</span></div>
    <div class="row-actions">${canAdmin() ? `<button class="small-btn" data-equipment-action="edit" data-id="${eq.id}">Editare</button><button class="small-btn danger" data-equipment-action="delete" data-id="${eq.id}">Șterge</button>` : ""}</div>
  </div>`).join("");

  $$("#roomsGrid [data-room-action]").forEach(btn => btn.addEventListener("click", event => {
    const room = getRoom(event.currentTarget.dataset.id);
    if (event.currentTarget.dataset.roomAction === "edit") openRoomForm(room);
    if (event.currentTarget.dataset.roomAction === "delete") {
      state.rooms = state.rooms.filter(item => item.id !== room.id);
      addActivity("Sală ștearsă", room.name);
      saveState();
      renderAll();
    }
  }));

  $$("#equipmentList [data-equipment-action]").forEach(btn => btn.addEventListener("click", event => {
    const eq = state.equipment.find(item => item.id === event.currentTarget.dataset.id);
    if (event.currentTarget.dataset.equipmentAction === "edit") openEquipmentForm(eq);
    if (event.currentTarget.dataset.equipmentAction === "delete") {
      state.equipment = state.equipment.filter(item => item.id !== eq.id);
      addActivity("Echipament șters", eq.name);
      saveState();
      renderAll();
    }
  }));
}

function renderReports() {
  const bookings = getAllBookings();
  const now = new Date();
  const todayValue = today();
  const weekStart = new Date(now);
  weekStart.setDate(now.getDate() - now.getDay() + 1);
  const weekStartValue = weekStart.toISOString().slice(0, 10);
  const month = today().slice(0, 7);
  const trainerStats = getTrainerStats();
  $("#reportTodayBookings").textContent = bookings.filter(item => item.session.date === todayValue).length;
  $("#reportWeekBookings").textContent = bookings.filter(item => item.session.date >= weekStartValue).length;
  $("#reportMonthBookings").textContent = bookings.filter(item => item.session.date.slice(0, 7) === month).length;
  $("#reportTopTrainer").textContent = trainerStats[0]?.name || "-";
  $("#reportQuickStats").innerHTML = [
    ["Număr utilizatori activi", state.users.filter(user => user.status === "active").length],
    ["Sesiuni rezervate total", bookings.length],
    ["Top antrenor/terapeut", trainerStats[0]?.name || "-"],
    ["Abonamente fitness", state.subscriptions.filter(sub => sub.type === "fitness").length],
    ["Abonamente forță", state.subscriptions.filter(sub => sub.type === "strength").length],
    ["Abonamente kinetoterapie", state.subscriptions.filter(sub => sub.type === "kineto").length],
    ["Abonamente mixte", state.subscriptions.filter(sub => sub.type === "mixed").length]
  ].map(([label, value]) => `<div class="list-item split"><strong>${escapeHtml(label)}</strong><span>${escapeHtml(value)}</span></div>`).join("");
  drawChart();
}

function renderPlugins() {
  $("#pluginsGrid").innerHTML = state.plugins.map(plugin => `<div class="plugin-card">
    <div class="tags">
      <span class="status-pill status-installed">${escapeHtml(plugin.status)}</span>
      <span class="type-pill">${escapeHtml(plugin.category)}</span>
    </div>
    <h4>${escapeHtml(plugin.name)}</h4>
    <div class="meta-grid">
      <span><b>Endpoint:</b> ${escapeHtml(plugin.endpoint)}</span>
      <span>${escapeHtml(plugin.description)}</span>
    </div>
    <div class="row-actions">${canAdmin() ? `<button class="small-btn" data-plugin-action="edit" data-id="${plugin.id}">Editare</button><button class="small-btn danger" data-plugin-action="delete" data-id="${plugin.id}">Șterge</button>` : "Extensie disponibilă"}</div>
  </div>`).join("");

  $$("#pluginsGrid [data-plugin-action]").forEach(btn => btn.addEventListener("click", event => {
    const plugin = state.plugins.find(item => item.id === event.currentTarget.dataset.id);
    if (event.currentTarget.dataset.pluginAction === "edit") openPluginForm(plugin);
    if (event.currentTarget.dataset.pluginAction === "delete") {
      state.plugins = state.plugins.filter(item => item.id !== plugin.id);
      addActivity("Plugin eliminat", plugin.name);
      saveState();
      renderAll();
    }
  }));
}

function renderProfile() {
  const user = currentUser();
  const userSessions = state.sessions.filter(session => session.bookedUserIds.includes(user.id));
  const userSubs = state.subscriptions.filter(sub => sub.userId === user.id);
  $("#profileContent").innerHTML = `<div class="profile-card">
    <h4>${escapeHtml(user.name)}</h4>
    <div class="meta-grid">
      <span><b>Email:</b> ${escapeHtml(user.email)}</span>
      <span><b>Telefon:</b> ${escapeHtml(user.phone)}</span>
      <span><b>Adresă:</b> ${escapeHtml(user.address || "-")}</span>
      <span><b>Rol:</b> ${escapeHtml(roles[user.role])}</span>
      <span><b>Status:</b> ${user.status === "active" ? "Activ" : "Suspendat"}</span>
      <span><b>Creat la:</b> ${formatDate(user.createdAt)}</span>
    </div>
  </div>
  <div class="profile-card">
    <h4>Istoric activități</h4>
    <div class="list-stack">${userSessions.map(sessionListItem).join("") || emptyState("Nu există sesiuni rezervate.")}</div>
    <h4>Istoric abonamente</h4>
    <div class="list-stack">${userSubs.map(subscriptionListItem).join("") || emptyState("Nu există abonamente.")}</div>
  </div>`;
}

function openModal(title, body) {
  $("#modalTitle").textContent = title;
  $("#modalBody").innerHTML = body;
  $("#modalRoot").classList.remove("hidden");
}

function closeModal() {
  $("#modalRoot").classList.add("hidden");
  $("#modalBody").innerHTML = "";
}

function openUserForm(user = null) {
  if (!canAdmin()) return;
  openModal(user ? "Editare utilizator" : "Adaugă utilizator", `<form id="userForm" class="form-grid">
    <div class="two-col">
      <label>Nume complet<input name="name" value="${attr(user?.name)}" required></label>
      <label>Telefon<input name="phone" value="${attr(user?.phone)}" required></label>
    </div>
    <label>Email<input type="email" name="email" value="${attr(user?.email)}" required></label>
    <label>Adresă<input name="address" value="${attr(user?.address)}"></label>
    <div class="two-col">
      <label>Parolă<input type="password" name="password" value="${attr(user?.password)}" required></label>
      <label>Rol<select name="role">${roleOptions(user?.role)}</select></label>
    </div>
    <label>Status<select name="status"><option value="active" ${user?.status !== "suspended" ? "selected" : ""}>Activ</option><option value="suspended" ${user?.status === "suspended" ? "selected" : ""}>Suspendat</option></select></label>
    <div class="form-actions"><button type="button" class="ghost-btn" data-close>Renunță</button><button class="primary-btn">Salvează</button></div>
  </form>`);
  $("#userForm").addEventListener("submit", event => {
    event.preventDefault();
    const data = Object.fromEntries(new FormData(event.currentTarget).entries());
    const exists = state.users.some(item => item.email.toLowerCase() === data.email.toLowerCase() && item.id !== user?.id);
    if (exists) return toast("Email duplicat", "Există deja un utilizator cu acest email.", "error");
    if (user) Object.assign(user, data);
    else state.users.push({ id: uid("u"), ...data, createdAt: today() });
    syncTrainerFromUser(data, user);
    addActivity(user ? "Utilizator actualizat" : "Utilizator creat", data.name);
    saveState();
    closeModal();
    renderAll();
  });
  $("#userForm [data-close]").addEventListener("click", closeModal);
}

function syncTrainerFromUser(data, user) {
  const role = data.role;
  const userId = user?.id || state.users[state.users.length - 1]?.id;
  if (role !== "trainer") return;
  let trainer = state.trainers.find(item => item.userId === userId || item.email.toLowerCase() === data.email.toLowerCase());
  if (!trainer) {
    state.trainers.push({ id: uid("t"), userId, name: data.name, email: data.email, phone: data.phone, specializations: ["General"], schedule: "Nedefinit", status: data.status, bio: "" });
  } else {
    trainer.userId = userId;
    trainer.name = data.name;
    trainer.email = data.email;
    trainer.phone = data.phone;
    trainer.status = data.status;
  }
}

function openSessionForm(session = null) {
  if (!canManageSessions()) return;
  const trainer = currentTrainer();
  const selectedTrainer = session?.trainerId || (currentUser().role === "trainer" ? trainer?.id : state.trainers[0]?.id);
  openModal(session ? "Editare sesiune" : "Adaugă sesiune", `<form id="sessionForm" class="form-grid">
    <label>Titlu sesiune<input name="title" value="${attr(session?.title)}" required></label>
    <div class="two-col">
      <label>Tip<select name="type">${sessionTypeOptions(session?.type)}</select></label>
      <label>Data<input type="date" name="date" value="${attr(session?.date || today())}" required></label>
    </div>
    <div class="two-col">
      <label>Ora început<input type="time" name="start" value="${attr(session?.start || "10:00")}" required></label>
      <label>Ora final<input type="time" name="end" value="${attr(session?.end || "11:00")}" required></label>
    </div>
    <div class="two-col">
      <label>Specialist<select name="trainerId" ${currentUser().role === "trainer" ? "disabled" : ""}>${trainerOptions(selectedTrainer)}</select></label>
      <label>Sală/Zonă<select name="roomId">${roomOptions(session?.roomId)}</select></label>
    </div>
    <div class="two-col">
      <label>Capacitate<input type="number" min="1" name="capacity" value="${attr(session?.capacity || 10)}" required></label>
      <label>Status<select name="status"><option value="active" ${session?.status !== "cancelled" ? "selected" : ""}>Activă</option><option value="cancelled" ${session?.status === "cancelled" ? "selected" : ""}>Anulată</option></select></label>
    </div>
    <div class="form-actions"><button type="button" class="ghost-btn" data-close>Renunță</button><button class="primary-btn">Salvează</button></div>
  </form>`);
  $("#sessionForm").addEventListener("submit", event => {
    event.preventDefault();
    const data = Object.fromEntries(new FormData(event.currentTarget).entries());
    if (currentUser().role === "trainer") data.trainerId = trainer?.id;
    data.capacity = Number(data.capacity);
    if (!data.trainerId) return toast("Specialist lipsă", "Nu există specialist asociat contului.", "error");
    if (data.start >= data.end) return toast("Interval invalid", "Ora de final trebuie să fie după ora de început.", "error");
    const roomConflict = state.sessions.find(item => item.id !== session?.id && item.status === "active" && data.status === "active" && item.date === data.date && item.roomId === data.roomId && overlaps(item.start, item.end, data.start, data.end));
    if (roomConflict) return toast("Conflict de sală", `Sala este ocupată de ${roomConflict.title}.`, "error");
    const trainerConflict = state.sessions.find(item => item.id !== session?.id && item.status === "active" && data.status === "active" && item.date === data.date && item.trainerId === data.trainerId && overlaps(item.start, item.end, data.start, data.end));
    if (trainerConflict) return toast("Conflict de specialist", `Specialistul are deja sesiunea ${trainerConflict.title}.`, "error");
    if (session) Object.assign(session, data);
    else state.sessions.push({ id: uid("s"), ...data, bookedUserIds: [] });
    addActivity(session ? "Sesiune actualizată" : "Sesiune creată", data.title);
    saveState();
    closeModal();
    renderAll();
  });
  $("#sessionForm [data-close]").addEventListener("click", closeModal);
}

function openAdminBookingModal(session) {
  if (!canAdmin()) return;
  const members = state.users.filter(user => user.role === "member" && user.status === "active");
  openModal("Adaugă membru la sesiune", `<form id="adminBookingForm" class="form-grid">
    <div class="list-item"><strong>${escapeHtml(session.title)}</strong><span>${formatDate(session.date)} · ${session.start}-${session.end}</span></div>
    <label>Membru<select name="userId">${members.map(user => `<option value="${user.id}">${escapeHtml(user.name)}</option>`).join("")}</select></label>
    <div class="form-actions"><button type="button" class="ghost-btn" data-close>Renunță</button><button class="primary-btn">Rezervă</button></div>
  </form>`);
  $("#adminBookingForm").addEventListener("submit", event => {
    event.preventDefault();
    const userId = new FormData(event.currentTarget).get("userId");
    closeModal();
    bookSession(session, userId);
  });
  $("#adminBookingForm [data-close]").addEventListener("click", closeModal);
}

function openSubscriptionForm(sub = null) {
  if (!canAdmin()) return;
  openModal(sub ? "Editare abonament" : "Adaugă abonament", `<form id="subscriptionForm" class="form-grid">
    <label>Utilizator<select name="userId">${memberOptions(sub?.userId)}</select></label>
    <div class="two-col">
      <label>Tip<select name="type">${subscriptionTypeOptions(sub?.type)}</select></label>
      <label>Preț<input type="number" min="0" step="0.01" name="price" value="${attr(sub?.price || 180)}" required></label>
    </div>
    <div class="two-col">
      <label>Data start<input type="date" name="start" value="${attr(sub?.start || today())}" required></label>
      <label>Data expirare<input type="date" name="end" value="${attr(sub?.end || addDays(30))}" required></label>
    </div>
    <label>Status<select name="status"><option value="active" ${sub?.status !== "suspended" ? "selected" : ""}>Activ</option><option value="suspended" ${sub?.status === "suspended" ? "selected" : ""}>Suspendat</option></select></label>
    <div class="form-actions"><button type="button" class="ghost-btn" data-close>Renunță</button><button class="primary-btn">Salvează</button></div>
  </form>`);
  $("#subscriptionForm").addEventListener("submit", event => {
    event.preventDefault();
    const data = Object.fromEntries(new FormData(event.currentTarget).entries());
    data.price = Number(data.price);
    if (data.end < data.start) return toast("Perioadă invalidă", "Data de expirare trebuie să fie după data de start.", "error");
    if (sub) Object.assign(sub, data);
    else state.subscriptions.push({ id: uid("sub"), ...data });
    addActivity(sub ? "Abonament actualizat" : "Abonament creat", `${getUser(data.userId)?.name || "-"} - ${subscriptionTypes[data.type]}`);
    saveState();
    closeModal();
    renderAll();
  });
  $("#subscriptionForm [data-close]").addEventListener("click", closeModal);
}

function openTrainerForm(trainer = null) {
  if (!canAdmin() && currentTrainer()?.id !== trainer?.id) return;
  openModal(trainer ? "Editare specialist" : "Adaugă specialist", `<form id="trainerForm" class="form-grid">
    <div class="two-col">
      <label>Nume<input name="name" value="${attr(trainer?.name)}" required></label>
      <label>Telefon<input name="phone" value="${attr(trainer?.phone)}" required></label>
    </div>
    <label>Email<input type="email" name="email" value="${attr(trainer?.email)}" required></label>
    <label>Specializări, separate prin virgulă<input name="specializations" value="${attr((trainer?.specializations || []).join(", "))}" required></label>
    <label>Program<input name="schedule" value="${attr(trainer?.schedule)}" required></label>
    <label>Descriere<textarea name="bio" rows="3">${escapeHtml(trainer?.bio || "")}</textarea></label>
    <label>Status<select name="status"><option value="active" ${trainer?.status !== "suspended" ? "selected" : ""}>Activ</option><option value="suspended" ${trainer?.status === "suspended" ? "selected" : ""}>Suspendat</option></select></label>
    <div class="form-actions"><button type="button" class="ghost-btn" data-close>Renunță</button><button class="primary-btn">Salvează</button></div>
  </form>`);
  $("#trainerForm").addEventListener("submit", event => {
    event.preventDefault();
    const data = Object.fromEntries(new FormData(event.currentTarget).entries());
    data.specializations = data.specializations.split(",").map(item => item.trim()).filter(Boolean);
    if (trainer) Object.assign(trainer, data);
    else state.trainers.push({ id: uid("t"), userId: "", ...data });
    addActivity(trainer ? "Specialist actualizat" : "Specialist creat", data.name);
    saveState();
    closeModal();
    renderAll();
  });
  $("#trainerForm [data-close]").addEventListener("click", closeModal);
}

function openRoomForm(room = null) {
  if (!canAdmin()) return;
  openModal(room ? "Editare sală/zonă" : "Adaugă sală/zonă", `<form id="roomForm" class="form-grid">
    <label>Denumire<input name="name" value="${attr(room?.name)}" required></label>
    <div class="two-col">
      <label>Capacitate<input type="number" min="1" name="capacity" value="${attr(room?.capacity || 10)}" required></label>
      <label>Tip<select name="type">${sessionTypeOptions(room?.type)}</select></label>
    </div>
    <label>Disponibilitate<input name="availability" value="${attr(room?.availability || "Disponibilă")}" required></label>
    <div class="form-actions"><button type="button" class="ghost-btn" data-close>Renunță</button><button class="primary-btn">Salvează</button></div>
  </form>`);
  $("#roomForm").addEventListener("submit", event => {
    event.preventDefault();
    const data = Object.fromEntries(new FormData(event.currentTarget).entries());
    data.capacity = Number(data.capacity);
    if (room) Object.assign(room, data);
    else state.rooms.push({ id: uid("r"), ...data });
    addActivity(room ? "Sală actualizată" : "Sală creată", data.name);
    saveState();
    closeModal();
    renderAll();
  });
  $("#roomForm [data-close]").addEventListener("click", closeModal);
}

function openEquipmentForm(eq = null) {
  if (!canAdmin()) return;
  openModal(eq ? "Editare echipament" : "Adaugă echipament", `<form id="equipmentForm" class="form-grid">
    <label>Denumire<input name="name" value="${attr(eq?.name)}" required></label>
    <div class="two-col">
      <label>Sală/Zonă<select name="roomId">${roomOptions(eq?.roomId)}</select></label>
      <label>Cantitate<input type="number" min="1" name="quantity" value="${attr(eq?.quantity || 1)}" required></label>
    </div>
    <label>Status<input name="status" value="${attr(eq?.status || "funcțional")}" required></label>
    <div class="form-actions"><button type="button" class="ghost-btn" data-close>Renunță</button><button class="primary-btn">Salvează</button></div>
  </form>`);
  $("#equipmentForm").addEventListener("submit", event => {
    event.preventDefault();
    const data = Object.fromEntries(new FormData(event.currentTarget).entries());
    data.quantity = Number(data.quantity);
    if (eq) Object.assign(eq, data);
    else state.equipment.push({ id: uid("e"), ...data });
    addActivity(eq ? "Echipament actualizat" : "Echipament creat", data.name);
    saveState();
    closeModal();
    renderAll();
  });
  $("#equipmentForm [data-close]").addEventListener("click", closeModal);
}

function openPluginForm(plugin = null) {
  if (!canAdmin()) return;
  openModal(plugin ? "Editare plugin" : "Adaugă plugin", `<form id="pluginForm" class="form-grid">
    <label>Nume plugin<input name="name" value="${attr(plugin?.name)}" required></label>
    <div class="two-col">
      <label>Categorie<input name="category" value="${attr(plugin?.category)}" required></label>
      <label>Status<select name="status"><option value="installed" ${plugin?.status !== "disabled" ? "selected" : ""}>installed</option><option value="disabled" ${plugin?.status === "disabled" ? "selected" : ""}>disabled</option></select></label>
    </div>
    <label>Endpoint micro-serviciu<input name="endpoint" value="${attr(plugin?.endpoint || "/plugins/service")}" required></label>
    <label>Descriere<textarea rows="3" name="description">${escapeHtml(plugin?.description || "")}</textarea></label>
    <div class="form-actions"><button type="button" class="ghost-btn" data-close>Renunță</button><button class="primary-btn">Salvează</button></div>
  </form>`);
  $("#pluginForm").addEventListener("submit", event => {
    event.preventDefault();
    const data = Object.fromEntries(new FormData(event.currentTarget).entries());
    if (plugin) Object.assign(plugin, data);
    else state.plugins.push({ id: uid("p"), ...data });
    addActivity(plugin ? "Plugin actualizat" : "Plugin creat", data.name);
    saveState();
    closeModal();
    renderAll();
  });
  $("#pluginForm [data-close]").addEventListener("click", closeModal);
}

function openOwnProfileForm() {
  const user = currentUser();
  openModal("Editează profilul meu", `<form id="ownProfileForm" class="form-grid">
    <div class="two-col">
      <label>Nume<input name="name" value="${attr(user.name)}" required></label>
      <label>Telefon<input name="phone" value="${attr(user.phone)}" required></label>
    </div>
    <label>Email<input type="email" name="email" value="${attr(user.email)}" required></label>
    <label>Adresă<input name="address" value="${attr(user.address)}"></label>
    <label>Parolă<input type="password" name="password" value="${attr(user.password)}" required></label>
    <div class="form-actions"><button type="button" class="ghost-btn" data-close>Renunță</button><button class="primary-btn">Salvează</button></div>
  </form>`);
  $("#ownProfileForm").addEventListener("submit", event => {
    event.preventDefault();
    const data = Object.fromEntries(new FormData(event.currentTarget).entries());
    Object.assign(user, data);
    const trainer = currentTrainer();
    if (trainer) Object.assign(trainer, { name: data.name, email: data.email, phone: data.phone });
    addActivity("Profil actualizat", user.name);
    saveState();
    closeModal();
    renderAll();
  });
  $("#ownProfileForm [data-close]").addEventListener("click", closeModal);
}

function openHistoryModal(user) {
  const sessions = state.sessions.filter(session => session.bookedUserIds.includes(user.id));
  const subs = state.subscriptions.filter(sub => sub.userId === user.id);
  openModal(`Istoric - ${user.name}`, `<div class="form-grid">
    <h4>Sesiuni rezervate</h4>
    <div class="list-stack">${sessions.map(sessionListItem).join("") || emptyState("Nu există sesiuni.")}</div>
    <h4>Abonamente</h4>
    <div class="list-stack">${subs.map(subscriptionListItem).join("") || emptyState("Nu există abonamente.")}</div>
  </div>`);
}

function sessionListItem(session) {
  return `<div class="list-item split"><div><strong>${escapeHtml(session.title)}</strong><span>${formatDate(session.date)} · ${session.start}-${session.end} · ${escapeHtml(getTrainer(session.trainerId)?.name || "-")} · ${escapeHtml(getRoom(session.roomId)?.name || "-")}</span></div><span class="type-pill">${escapeHtml(sessionTypes[session.type])}</span></div>`;
}

function subscriptionListItem(sub) {
  const status = getSubscriptionStatus(sub);
  return `<div class="list-item split"><div><strong>${escapeHtml(subscriptionTypes[sub.type])}</strong><span>${formatDate(sub.start)} - ${formatDate(sub.end)} · ${Number(sub.price).toFixed(2)} lei</span></div><span class="status-pill status-${status}">${subscriptionStatusLabel(status)}</span></div>`;
}

function roleOptions(selected) {
  return Object.keys(roles).map(key => `<option value="${key}" ${selected === key ? "selected" : ""}>${escapeHtml(roles[key])}</option>`).join("");
}

function sessionTypeOptions(selected) {
  return Object.keys(sessionTypes).map(key => `<option value="${key}" ${selected === key ? "selected" : ""}>${escapeHtml(sessionTypes[key])}</option>`).join("");
}

function subscriptionTypeOptions(selected) {
  return Object.keys(subscriptionTypes).map(key => `<option value="${key}" ${selected === key ? "selected" : ""}>${escapeHtml(subscriptionTypes[key])}</option>`).join("");
}

function trainerOptions(selected) {
  return state.trainers.map(trainer => `<option value="${trainer.id}" ${selected === trainer.id ? "selected" : ""}>${escapeHtml(trainer.name)}</option>`).join("");
}

function roomOptions(selected) {
  return state.rooms.map(room => `<option value="${room.id}" ${selected === room.id ? "selected" : ""}>${escapeHtml(room.name)}</option>`).join("");
}

function memberOptions(selected) {
  return state.users.filter(user => user.role === "member").map(user => `<option value="${user.id}" ${selected === user.id ? "selected" : ""}>${escapeHtml(user.name)}</option>`).join("");
}

function getUser(id) {
  return state.users.find(user => user.id === id);
}

function getTrainer(id) {
  return state.trainers.find(trainer => trainer.id === id);
}

function getRoom(id) {
  return state.rooms.find(room => room.id === id);
}

function getSubscriptionStatus(sub) {
  if (sub.status === "suspended") return "suspended";
  if (sub.end < today()) return "expired";
  return "active";
}

function subscriptionStatusLabel(status) {
  return { active: "Activ", suspended: "Suspendat", expired: "Expirat" }[status] || status;
}

function getAllBookings() {
  return state.sessions.flatMap(session => session.bookedUserIds.map(userId => ({ session, userId })));
}

function getTrainerStats() {
  return state.trainers.map(trainer => ({
    name: trainer.name,
    total: state.sessions.filter(session => session.trainerId === trainer.id).reduce((sum, session) => sum + session.bookedUserIds.length, 0)
  })).sort((a, b) => b.total - a.total);
}

function overlaps(startA, endA, startB, endB) {
  return startA < endB && startB < endA;
}

function compareSessionDate(a, b) {
  return `${a.date} ${a.start}`.localeCompare(`${b.date} ${b.start}`);
}

function formatDate(value) {
  if (!value) return "-";
  return new Date(`${value}T00:00:00`).toLocaleDateString("ro-RO");
}

function addActivity(title, text) {
  state.activity.unshift({ id: uid("a"), title, text, date: new Date().toISOString() });
  state.activity = state.activity.slice(0, 80);
  saveState();
}

function prepareEmail(user, session) {
  const subject = encodeURIComponent(`Confirmare rezervare KIM - ${session.title}`);
  const body = encodeURIComponent(`Bună, ${user.name}!\n\nRezervarea ta a fost confirmată pentru sesiunea "${session.title}", în data de ${formatDate(session.date)}, interval ${session.start}-${session.end}.\n\nKIM - Kineto Web Manager`);
  const link = document.createElement("a");
  link.href = `mailto:${user.email}?subject=${subject}&body=${body}`;
  link.style.display = "none";
  document.body.appendChild(link);
  link.click();
  link.remove();
}

function toast(title, text = "", type = "info") {
  const item = document.createElement("div");
  item.className = `toast ${type === "error" ? "error" : ""}`;
  item.innerHTML = `<strong>${escapeHtml(title)}</strong><span>${escapeHtml(text)}</span>`;
  $("#toastRoot").appendChild(item);
  setTimeout(() => item.remove(), 4200);
}

function emptyState(text) {
  return `<div class="empty-state">${escapeHtml(text)}</div>`;
}

function escapeHtml(value = "") {
  return String(value).replace(/[&<>"']/g, char => ({ "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#039;" }[char]));
}

function attr(value = "") {
  return escapeHtml(value).replace(/"/g, "&quot;");
}

function csvEscape(value) {
  return `"${String(value ?? "").replace(/"/g, '""')}"`;
}

function xmlEscape(value) {
  return String(value ?? "").replace(/[<>&'"]/g, c => ({ "<": "&lt;", ">": "&gt;", "&": "&amp;", "'": "&apos;", '"': "&quot;" }[c]));
}

function trainersToCsv() {
  const rows = [["id", "nume", "email", "telefon", "specializari", "program", "status", "descriere"]];
  state.trainers.forEach(trainer => rows.push([trainer.id, trainer.name, trainer.email, trainer.phone, trainer.specializations.join("|"), trainer.schedule, trainer.status, trainer.bio]));
  return rows.map(row => row.map(csvEscape).join(",")).join("\n");
}

function trainersToXml() {
  return `<?xml version="1.0" encoding="UTF-8"?>\n<traineri>${state.trainers.map(trainer => `\n  <trainer>
    <id>${xmlEscape(trainer.id)}</id>
    <nume>${xmlEscape(trainer.name)}</nume>
    <email>${xmlEscape(trainer.email)}</email>
    <telefon>${xmlEscape(trainer.phone)}</telefon>
    <specializari>${trainer.specializations.map(item => `<specializare>${xmlEscape(item)}</specializare>`).join("")}</specializari>
    <program>${xmlEscape(trainer.schedule)}</program>
    <status>${xmlEscape(trainer.status)}</status>
    <descriere>${xmlEscape(trainer.bio)}</descriere>
  </trainer>`).join("")}\n</traineri>`;
}

function reportsToCsv() {
  const rows = [["indicator", "valoare"]];
  rows.push(["numar_utilizatori_activi", state.users.filter(user => user.status === "active").length]);
  rows.push(["sesiuni_rezervate_total", getAllBookings().length]);
  rows.push(["sesiuni_azi", getAllBookings().filter(item => item.session.date === today()).length]);
  getTrainerStats().forEach(item => rows.push([`top_trainer_${item.name}`, item.total]));
  Object.keys(subscriptionTypes).forEach(type => rows.push([`abonamente_${subscriptionTypes[type]}`, state.subscriptions.filter(sub => sub.type === type).length]));
  return rows.map(row => row.map(csvEscape).join(",")).join("\n");
}

function reportsToXml() {
  return `<?xml version="1.0" encoding="UTF-8"?>\n<rapoarte>
  <utilizatoriActivi>${state.users.filter(user => user.status === "active").length}</utilizatoriActivi>
  <sesiuniRezervateTotal>${getAllBookings().length}</sesiuniRezervateTotal>
  <topTraineri>${getTrainerStats().map(item => `<trainer><nume>${xmlEscape(item.name)}</nume><sesiuni>${item.total}</sesiuni></trainer>`).join("")}</topTraineri>
  <tipuriAbonamente>${Object.keys(subscriptionTypes).map(type => `<abonament><tip>${xmlEscape(subscriptionTypes[type])}</tip><total>${state.subscriptions.filter(sub => sub.type === type).length}</total></abonament>`).join("")}</tipuriAbonamente>
</rapoarte>`;
}

function importTrainersCsv(event) {
  const file = event.target.files[0];
  if (!file) return;
  file.text().then(text => {
    const lines = text.trim().split(/\r?\n/).slice(1);
    lines.forEach(line => {
      const cols = parseCsvLine(line);
      if (cols.length < 7) return;
      const trainer = {
        id: cols[0] || uid("t"),
        name: cols[1] || "",
        email: cols[2] || "",
        phone: cols[3] || "",
        specializations: (cols[4] || "").split("|").map(item => item.trim()).filter(Boolean),
        schedule: cols[5] || "",
        status: cols[6] || "active",
        bio: cols[7] || "",
        userId: ""
      };
      const existing = state.trainers.find(item => item.id === trainer.id);
      if (existing) Object.assign(existing, trainer);
      else state.trainers.push(trainer);
    });
    addActivity("Import CSV", "Antrenorii/terapeuții au fost importați.");
    saveState();
    renderAll();
    toast("Import finalizat", "Datele CSV au fost încărcate.");
    event.target.value = "";
  });
}

function importTrainersXml(event) {
  const file = event.target.files[0];
  if (!file) return;
  file.text().then(text => {
    const doc = new DOMParser().parseFromString(text, "text/xml");
    Array.from(doc.querySelectorAll("trainer")).forEach(node => {
      const trainer = {
        id: node.querySelector("id")?.textContent || uid("t"),
        name: node.querySelector("nume")?.textContent || "",
        email: node.querySelector("email")?.textContent || "",
        phone: node.querySelector("telefon")?.textContent || "",
        specializations: Array.from(node.querySelectorAll("specializare")).map(item => item.textContent),
        schedule: node.querySelector("program")?.textContent || "",
        status: node.querySelector("status")?.textContent || "active",
        bio: node.querySelector("descriere")?.textContent || "",
        userId: ""
      };
      const existing = state.trainers.find(item => item.id === trainer.id);
      if (existing) Object.assign(existing, trainer);
      else state.trainers.push(trainer);
    });
    addActivity("Import XML", "Antrenorii/terapeuții au fost importați.");
    saveState();
    renderAll();
    toast("Import finalizat", "Datele XML au fost încărcate.");
    event.target.value = "";
  });
}

function parseCsvLine(line) {
  const result = [];
  let current = "";
  let quoted = false;
  for (let i = 0; i < line.length; i++) {
    const char = line[i];
    const next = line[i + 1];
    if (char === '"' && quoted && next === '"') {
      current += '"';
      i++;
    } else if (char === '"') {
      quoted = !quoted;
    } else if (char === "," && !quoted) {
      result.push(current);
      current = "";
    } else {
      current += char;
    }
  }
  result.push(current);
  return result;
}

function drawChart() {
  const canvas = $("#reportsCanvas");
  const ctx = canvas.getContext("2d");
  const data = Object.keys(sessionTypes).map(type => ({
    label: sessionTypes[type],
    value: state.sessions.filter(session => session.type === type).reduce((sum, session) => sum + session.bookedUserIds.length, 0)
  }));
  ctx.clearRect(0, 0, canvas.width, canvas.height);
  ctx.fillStyle = "#fbfcfe";
  ctx.fillRect(0, 0, canvas.width, canvas.height);
  ctx.fillStyle = "#102033";
  ctx.font = "800 34px Inter, Arial";
  ctx.fillText("Sesiuni rezervate pe tipuri", 46, 64);
  ctx.fillStyle = "#68758a";
  ctx.font = "500 18px Inter, Arial";
  ctx.fillText("Export disponibil în format PNG și WebP", 46, 96);
  const max = Math.max(1, ...data.map(item => item.value));
  const startX = 90;
  const baseY = 450;
  const barW = 150;
  const gap = 90;
  data.forEach((item, index) => {
    const x = startX + index * (barW + gap);
    const h = Math.max(10, item.value / max * 270);
    ctx.fillStyle = ["#0e7c7b", "#315bc7", "#15825b", "#d9822b"][index];
    roundRect(ctx, x, baseY - h, barW, h, 18);
    ctx.fill();
    ctx.fillStyle = "#102033";
    ctx.font = "900 28px Inter, Arial";
    ctx.textAlign = "center";
    ctx.fillText(item.value, x + barW / 2, baseY - h - 16);
    ctx.fillStyle = "#68758a";
    ctx.font = "800 17px Inter, Arial";
    ctx.fillText(item.label, x + barW / 2, baseY + 38);
  });
  ctx.textAlign = "left";
}

function roundRect(ctx, x, y, w, h, r) {
  ctx.beginPath();
  ctx.moveTo(x + r, y);
  ctx.arcTo(x + w, y, x + w, y + h, r);
  ctx.arcTo(x + w, y + h, x, y + h, r);
  ctx.arcTo(x, y + h, x, y, r);
  ctx.arcTo(x, y, x + w, y, r);
  ctx.closePath();
}

function downloadFile(filename, content, type) {
  const blob = new Blob([content], { type });
  const link = document.createElement("a");
  link.href = URL.createObjectURL(blob);
  link.download = filename;
  document.body.appendChild(link);
  link.click();
  URL.revokeObjectURL(link.href);
  link.remove();
  toast("Export generat", filename);
}

function exportChart(type, filename) {
  drawChart();
  const link = document.createElement("a");
  link.href = $("#reportsCanvas").toDataURL(type, 0.95);
  link.download = filename;
  document.body.appendChild(link);
  link.click();
  link.remove();
  toast("Diagramă exportată", filename);
}

init();
