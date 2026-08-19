// Filtros (visual)
document.querySelectorAll("#filtros .filter-btn").forEach(function (b) {
	b.addEventListener("click", function () {
		document.querySelectorAll("#filtros .filter-btn").forEach(function (x) { x.classList.remove("is-active"); });
		b.classList.add("is-active");
	});
});

// Select: mantener label flotante cuando hay valor
var obra = document.getElementById("obra");
obra.addEventListener("change", function () {
	obra.classList.toggle("has-value", !!obra.value);
});

// Validación simple
var form = document.getElementById("turnoForm");
function setInvalid(el, bad) { el.closest(".field").classList.toggle("is-invalid", bad); }

form.addEventListener("submit", function (e) {
	e.preventDefault();
	var nombre = document.getElementById("nombre");
	var apellido = document.getElementById("apellido");
	var tel = document.getElementById("telefono");
	var mail = document.getElementById("mail");

	var bad = false;
	[[nombre, nombre.value.trim().length > 1], [apellido, apellido.value.trim().length > 1],
	[tel, /^[0-9+()\s-]{6,20}$/.test(tel.value.trim())],
	[mail, /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(mail.value.trim())],
	[obra, !!obra.value]].forEach(function (p) {
		var ok = p[1]; setInvalid(p[0], !ok); if (!ok) bad = true;
	});
	if (bad) { form.querySelector(".field.is-invalid input, .field.is-invalid select").focus(); return; }

	document.getElementById("okBox").style.display = "block";
	form.querySelector('button[type="submit"]').disabled = true;
});

form.querySelectorAll("input").forEach(function (i) {
	i.addEventListener("input", function () { setInvalid(i, false); });
});
obra.addEventListener("change", function () { setInvalid(obra, false); });


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

