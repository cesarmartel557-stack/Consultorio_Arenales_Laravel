var DIAS = ["Dom", "Lun", "Mar", "Mié", "Jue", "Vie", "Sáb"];
var MESES = ["ENE","FEB","MAR","ABR","MAY","JUN","JUL","AGO","SEP","OCT","NOV","DIC"];
// Días de atención: martes(2), jueves(4), viernes(5)
var ATIENDE = { 2: ["14:00","19:00"], 4: ["14:00","19:00"], 5: ["09:00","13:00"] };

var weekOffset = 0;
var selectedKey = null;
var selectedTime = null;

function startOfWeek(d) {
	var x = new Date(d);
	var day = (x.getDay() + 6) % 7; // lunes = 0
	x.setDate(x.getDate() - day);
	x.setHours(0, 0, 0, 0);
	return x;
}
function keyOf(d) { return d.toISOString().slice(0, 10); }

// Disponibilidad pseudo-aleatoria estable por día/horario
function isTaken(key, time) {
	var s = key + time, h = 0;
	for (var i = 0; i < s.length; i++) h = (h * 31 + s.charCodeAt(i)) % 997;
	return h % 5 === 0;
}

function slotsFor(date) {
	var rango = ATIENDE[date.getDay()];
	if (!rango) return [];
	var out = [];
	var start = parseInt(rango[0], 10) * 60 + parseInt(rango[0].slice(3), 10);
	var end = parseInt(rango[1], 10) * 60 + parseInt(rango[1].slice(3), 10);
	for (var m = start; m < end; m += 15) {
		var hh = String(Math.floor(m / 60)).padStart(2, "0");
		var mm = String(m % 60).padStart(2, "0");
		out.push(hh + ":" + mm);
	}
	return out;
}

function renderDays() {
	var base = startOfWeek(new Date());
	base.setDate(base.getDate() + weekOffset * 7);
	var end = new Date(base); end.setDate(end.getDate() + 5);
	document.getElementById("weekLabel").textContent =
		base.getDate() + " al " + end.getDate() + " de " + MESES[end.getMonth()] + " " + end.getFullYear();
	document.getElementById("prevWeek").disabled = weekOffset <= 0;

	var wrap = document.getElementById("days");
	wrap.innerHTML = "";
	var firstAvailable = null;
	for (var i = 0; i < 6; i++) {
		var d = new Date(base); d.setDate(d.getDate() + i);
		var key = keyOf(d);
		var atiende = !!ATIENDE[d.getDay()];
		if (atiende && !firstAvailable) firstAvailable = key;
		var btn = document.createElement("button");
		btn.type = "button";
		btn.className = "day-pill" + (atiende ? "" : " is-off");
		btn.dataset.key = key;
		btn.dataset.dow = d.getDay();
		btn.innerHTML =
			"<small>" + DIAS[d.getDay()] + "</small><b>" + d.getDate() + "</b><i>" +
			MESES[d.getMonth()].toLowerCase() + "</i>" + (atiende ? '<span class="day-dot"></span>' : "");
		btn.addEventListener("click", function () {
			selectedKey = this.dataset.key;
			selectedTime = null;
			paintDays();
			renderSlots();
			updateConfirm();
		});
		wrap.appendChild(btn);
	}
	if (!selectedKey || !wrap.querySelector('[data-key="' + selectedKey + '"]')) {
		selectedKey = firstAvailable;
		selectedTime = null;
	}
	paintDays();
	renderSlots();
	updateConfirm();
}

function paintDays() {
	document.querySelectorAll(".day-pill").forEach(function (b) {
		b.classList.toggle("is-active", b.dataset.key === selectedKey);
	});
}

function renderSlots() {
	var wrap = document.getElementById("slotsWrap");
	wrap.innerHTML = "";
	if (!selectedKey) { wrap.innerHTML = '<div class="no-slots">Elegí un día para ver los horarios.</div>'; return; }
	var d = new Date(selectedKey + "T12:00:00");
	var times = slotsFor(d);
	if (!times.length) {
		wrap.innerHTML = '<div class="no-slots">El profesional no atiende este día. Probá con otro.</div>';
		return;
	}
	var manana = times.filter(function (t) { return parseInt(t, 10) < 13; });
	var tarde = times.filter(function (t) { return parseInt(t, 10) >= 13; });
	[["Mañana", manana], ["Tarde", tarde]].forEach(function (g) {
		if (!g[1].length) return;
		var h = document.createElement("div");
		h.className = "slot-group-title";
		h.textContent = g[0];
		wrap.appendChild(h);
		var grid = document.createElement("div");
		grid.className = "slots";
		g[1].forEach(function (t) {
			var b = document.createElement("button");
			b.type = "button";
			b.className = "slot";
			b.textContent = t;
			if (isTaken(selectedKey, t)) { b.disabled = true; b.title = "No disponible"; }
			b.addEventListener("click", function () {
				selectedTime = t;
				document.querySelectorAll(".slot").forEach(function (s) { s.classList.remove("is-selected"); });
				b.classList.add("is-selected");
				updateConfirm();
			});
			grid.appendChild(b);
		});
		wrap.appendChild(grid);
	});
}

function updateConfirm() {
	var info = document.getElementById("selInfo");
	var btn = document.getElementById("confirmBtn"); // Ahora este es tu <a>

	if (selectedKey && selectedTime) {
		var d = new Date(selectedKey + "T12:00:00");
		info.innerHTML = "Turno seleccionado<b>" + DIAS[d.getDay()] + " " + d.getDate() + " de " +
			MESES[d.getMonth()].toLowerCase() + " · " + selectedTime + " hs</b>";

		// Habilitar el enlace (removemos la clase disabled)
		btn.classList.remove("disabled");

	} else {
		info.innerHTML = "Ningún horario seleccionado<b>—</b>";

		// Deshabilitar el enlace (agregamos la clase disabled)
		btn.classList.add("disabled");
	}
}

document.getElementById("prevWeek").addEventListener("click", function () {
	if (weekOffset > 0) { weekOffset--; selectedKey = null; renderDays(); }
});
document.getElementById("nextWeek").addEventListener("click", function () {
	weekOffset++; selectedKey = null; renderDays();
});
document.getElementById("confirmBtn").addEventListener("click", function () {
	alert("Turno reservado: " + document.getElementById("selInfo").textContent.replace("Turno seleccionado", ""));
});

renderDays();


(function () {
	var oc = document.getElementById("navMenu");
	var burger = document.getElementById("burgerBtn");
	if (!oc || !burger) return;
	oc.addEventListener("show.bs.offcanvas", function () {
		burger.classList.add("is-open");
		document.body.classList.add("menu-open");
		burger.setAttribute("aria-label", "Cerrar menú");
	});
	oc.addEventListener("hide.bs.offcanvas", function () {
		burger.classList.remove("is-open");
		document.body.classList.remove("menu-open");
		burger.setAttribute("aria-label", "Abrir menú");
	});
})();

